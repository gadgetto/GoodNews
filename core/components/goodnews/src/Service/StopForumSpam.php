<?php

/**
 * This file is part of the GoodNews package.
 *
 * @copyright Shaun McCormick (original code) / Sterc (modified for MODX3)
 *
 * Parts of code from:
 * https://github.com/Sterc/FormIt/blob/develop/core/components/formit/src/FormIt/Service/StopForumSpam.php
 *
 * @copyright bitego (Martin Gartner)
 * @license GNU General Public License v2.0 (and later)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitego\GoodNews\Service;

use MODX\Revolution\modX;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Service class for spam checks (for spam Hook).
 *
 * @package goodnews
 * @subpackage service
 */

class StopForumSpam
{
    /** @var modX $modx A reference to the modX instance */
    public $modx = null;

    /** @var array $config */
    public $config = [];

    /**
     * StopForumSpam constructor.
     *
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;
        $this->config = array_merge([
            'host' => 'https://api.stopforumspam.com/',
            'path' => 'api',
            'method' => 'GET',
        ], $config);
    }

    /**
     * Check for spammer.
     *
     * @param string $ip
     * @param string $email
     * @param string $username
     * @return array An array of errors
     */
    public function check(string $ip = '', string $email = '', string $username = '')
    {
        $params = [];
        if (!empty($ip)) {
            if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0'])) {
                $ip = '72.179.10.158';
            }
            $params['ip'] = $ip;
        }

        if (!empty($email)) {
            $params['email'] = $email;
        }
        if (!empty($username)) {
            $params['username'] = $username;
        }
        if (empty($params)) {
            return;
        }

        $xml = $this->request($params);
        $i = 0;
        $errors = [];

        foreach ($xml->appears as $result) {
            if ((string)$result === 'yes') {
                $errors[] = ucfirst($xml->type[$i]);
            }

            $i++;
        }

        return $errors;
    }

    /**
     * Make a request to stopforumspam.com.
     *
     * @param array $params An array of parameters to send
     * @return mixed The return SimpleXML object, or false if none
     */
    public function request(array $params = [])
    {
        $client = $this->modx->services->get(ClientInterface::class);
        $factory = $this->modx->services->get(RequestFactoryInterface::class);

        $uri = $this->config['host'] . $this->config['path'];
        if (strtoupper($this->config['method']) == 'GET') {
            $uri .= (strpos($uri, '?') > 0) ? '&' : '?';
            $uri .= http_build_query($params);
        }

        $request = $factory->createRequest($this->config['method'], $uri);

        if (strtoupper($this->config['method']) == 'POST') {
            $request->getBody()->write(json_encode($params));
        }

        try {
            $response = $client->sendRequest($request)->withHeader('Accept', 'text/xml');
        } catch (ClientExceptionInterface $e) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] StopForumSpam - could not load response from: ' . $this->config['host']
            );
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] StopForumSpam - error: ' . $e->getMessage()
            );
            return true;
        }

        $responseXml = $this->toXml($response->getBody()->getContents());

        return $responseXml;
    }

    /**
     * Interprets the response string of XML into an object.
     *
     * @return SimpleXMLElement
     */
    private function toXml($response)
    {
        $xml = null;

        try {
            $xml = simplexml_load_string($response);
        } catch (\Exception $e) {
            $this->modx->log(
                xPDO::LOG_LEVEL_ERROR,
                '[GoodNews] StopForumSpam - could not parse XML response from provider: ' . $response
            );
        }
        if (!$xml) {
            $xml = new \SimpleXMLElement(
                '<?xml version="1.0" encoding="UTF-8"?><error><message>' .
                $this->modx->lexicon('provider_err_blank_response') .
                '</message></error>'
            );
        }
        return $xml;
    }
}
