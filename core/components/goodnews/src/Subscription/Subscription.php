<?php

/**
 * This file is part of the GoodNews package.
 *
 * @copyright bitego (Martin Gartner)
 * @license GNU General Public License v2.0 (and later)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitego\GoodNews\Subscription;

use MODX\Revolution\modX;
use MODX\Revolution\modChunk;
use MODX\Revolution\Mail\modMail;
use Bitego\GoodNews\Subscription\Validator;
use Bitego\GoodNews\Subscription\Hooks;
use Bitego\GoodNews\Subscription\Dictionary;

/**
 * Main Subscription class.
 *
 * @package goodnews
 * @subpackage subscription
 */

class Subscription
{
    /** @var modX $modx A reference to the modX instance */
    public $modx = null;

    /** @var Confirm | RequestLinks | Subscription | Unsubscription | UpdateProfile $controller */
    public $controller = null;

    /** @var Validator $validator */
    public $validator = null;

    /** @var Dictionary $dictionary */
    public $dictionary = null;

    /** @var array $config An array of configuration properties */
    public $config = [];

    /** @var Hooks $preHooks */
    public $preHooks = null;

    /** @var Hooks $postHooks */
    public $postHooks = null;

    /**
     * The constructor for the Subscription class.
     *
     * @param modX &$modx A reference to the modX instance
     * @param array $config An array of configuration properties
     */
    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx = &$modx;
        $corePath = $this->modx->getOption(
            'goodnews.core_path',
            $config,
            $this->modx->getOption(
                'core_path',
                null,
                MODX_CORE_PATH
            ) . 'components/goodnews/'
        );
        $this->config = array_merge([
            'corePath'        => $corePath,
            'modelPath'       => $corePath . 'src/Model/',
            'controllersPath' => $corePath . 'src/Controllers/Subscription/',
            'processorsPath'  => $corePath . 'src/Processors/Subscription/',
            'chunksPath'      => $corePath . 'elements/chunks/',
        ], $config);
        $this->modx->lexicon->load('goodnews:frontend');
    }

    /**
     * Load a Subscription controller by a given name.
     * (used by Snippets)
     *
     * @param string $controller
     * @return Controller object|null
     */
    public function loadController(string $controller)
    {
        $classPath = $this->config['controllersPath'] . $controller . '.php';
        $className = $controller;

        if (file_exists($classPath)) {
            if (!class_exists($className)) {
                $className = require_once $classPath;
            }
            if (class_exists($className)) {
                $this->controller = new $className($this, $this->config);
            } else {
                $this->modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[GoodNews] Could not load controller ' . $className . ' at ' . $classPath
                );
            }
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not find controller file: ' . $classPath);
        }
        return $this->controller;
    }

    /**
     * Loads the Validator class.
     *
     * @access public
     * @param array $config An array of configuration parameters for the Validator class
     * @return Validator An instance of the Validator class
     */
    public function loadValidator(array $config = [])
    {
        $this->validator = new Validator($this, $config);
        return $this->validator;
    }

    /**
     * Loads the Hooks class.
     *
     * @access public
     * @param string $type The name of the Hooks service to load (preHooks, postHooks)
     * @param array $config array An array of configuration parameters for the hooks class
     * @return Hooks An instance of the Hooks class
     */
    public function loadHooks(string $type, array $config = [])
    {
        $this->$type = new Hooks($this, $config);
        return $this->$type;
    }

    /**
     * Load the Dictionary class and gather $_POST params.
     *
     * @access public
     * @return Dictionary An instance of the Dictionary class
     */
    public function loadDictionary()
    {
        $this->dictionary = new Dictionary($this);
        // Load POST parameters
        $this->dictionary->gather();
        return $this->dictionary;
    }

    /**
     * Sends an email based on the specified information and templates.
     *
     * @access public
     * @param string $email The email to send to
     * @param string $subject The subject of the email
     * @param array $properties A collection of properties
     * @return array
     */
    public function sendEmail(string $email, string $subject, array $properties = [])
    {
        $msg = $this->getChunk($properties['tpl'], $properties, $properties['tplType']);

        $msgAlt = '';
        if (!empty($properties['tplAlt'])) {
            $msgAlt = $this->getChunk($properties['tplAlt'], $properties, $properties['tplType']);
        }

        $mail = $this->modx->services->get('mail');
        $mail->set(modMail::MAIL_BODY, $msg);
        $mail->set(modMail::MAIL_FROM, $this->modx->getOption('emailsender'));
        $mail->set(modMail::MAIL_FROM_NAME, $this->modx->getOption('site_name'));
        $mail->set(modMail::MAIL_SENDER, $this->modx->getOption('emailsender'));
        $mail->set(modMail::MAIL_SUBJECT, $subject);
        $mail->set(modMail::MAIL_CHARSET, $this->modx->getOption('mail_charset'));
        $mail->set(modMail::MAIL_ENCODING, $this->modx->getOption('mail_encoding'));
        if (!empty($msgAlt)) {
            $mail->set(modMail::MAIL_BODY_TEXT, $msgAlt);
        }
        $mail->address('to', $email, $email);
        $mail->address('reply-to', $this->modx->getOption('emailsender'));
        $mail->setHTML(true);

        $sent = $mail->send();
        if (!$sent) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[GoodNews] Subscription::sendEmail - Email could not be sent to ' .
                $email .
                ' -- Error: ' .
                $mail->mailer->ErrorInfo
            );
        }
        $mail->reset();
        return $sent;
    }

    /**
     * Helper function to get a chunk or tpl by different methods.
     *
     * @access public
     * @param string $name The name of the tpl/chunk.
     * @param array $properties The properties to use for the tpl/chunk.
     * @param string $type The type of tpl/chunk. Can be embedded, modChunk, file, or inline. Defaults to modChunk.
     * @return string The processed tpl/chunk.
     */
    public function getChunk(string $name, array $properties, string $type = 'modChunk')
    {
        $output = '';
        switch ($type) {
            case 'embedded':
                $this->modx->setPlaceholders($properties);
                break;

            case 'modChunk':
                $output .= $this->modx->getChunk($name, $properties);
                break;

            case 'file':
                $name = str_replace([
                    '{base_path}',
                    '{assets_path}',
                    '{core_path}',
                ], [
                    $this->modx->getOption('base_path'),
                    $this->modx->getOption('assets_path'),
                    $this->modx->getOption('core_path'),
                ], $name);
                $output .= file_get_contents($name);
                $this->modx->setPlaceholders($properties);
                break;

            case 'inline':
            default:
                /* Default is inline, meaning the tpl content was provided directly in the property */
                $chunk = $this->modx->newObject(modChunk::class);
                $chunk->setContent($name);
                $chunk->setCacheable(false);
                $output .= $chunk->process($properties);
                break;
        }
        return $output;
    }

    /**
     * Encodes an array/string of params for URL transmission
     *
     * @access public
     * @param array|string $params
     * @return string
     */
    public function encodeParams($params)
    {
        if (is_array($params)) {
            $params = serialize($params);
        } else {
            $params = serialize(array($params));
        }
        return $this->base64UrlEncode($params);
    }

    /**
     * Decode a serialized, encoded URL param string
     *
     * @access public
     * @param string $params
     * @return array
     */
    public function decodeParams($params)
    {
        return unserialize($this->base64UrlDecode($params));
    }

    /**
     * Encodes a string for URL safe transmission
     *
     * @access public
     * @param string $str
     * @return string
     */
    public function base64UrlEncode($str)
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    /**
     * Decodes an URL safe encoded string
     *
     * @access public
     * @param string $str
     * @return string
     */
    public function base64UrlDecode($str)
    {
        return base64_decode(str_pad(strtr($str, '-_', '+/'), strlen($str) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Process MODX event results.
     *
     * @access public
     * @param array $rs
     * @return string
     */
    public function getEventResult($rs)
    {
        $success = '';
        if (is_array($rs)) {
            foreach ($rs as $msg) {
                if (!empty($msg)) {
                    $success .= $msg . "\n";
                }
            }
        } else {
            $success = $rs;
        }
        return $success;
    }
}
