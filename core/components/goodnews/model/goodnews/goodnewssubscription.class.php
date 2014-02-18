<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 * Based on code from Login add-on
 * Copyright 2010 by Shaun McCormick <shaun@modx.com>
 * Modified by bitego - 10/2013
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * Main GoodNewsSubscription class
 *
 * @package goodnews
 */

class GoodNewsSubscription {

    /** @var GoodNewsSubscriptionController $controller */
    public $controller;
    
    /**
     * The constructor for the GoodNewsSubscription class.
     *
     * @param modX &$modx A reference to the modX instance.
     * @param array $config An array of configuration parameters.
     * @return GoodNewsSubscription
     */
    function __construct(modX &$modx, array $config = array()) {
        $this->modx =& $modx;
        $corePath = $modx->getOption('goodnews.core_path', $config, $modx->getOption('core_path', null, MODX_CORE_PATH).'components/goodnews/');
        $this->config = array_merge(array(
            'corePath'        => $corePath,
            'chunksPath'      => $corePath.'chunks/',
            'controllersPath' => $corePath.'controllers/web/',
            'modelPath'       => $corePath.'model/',
            'processorsPath'  => $corePath.'processors/web/',
        ), $config);
        $this->modx->lexicon->load('goodnews:frontend');
    }

    /**
     * Load a controller by a given name.
     *
     * @param string $controller
     * @return null|Controller object
     */
    public function loadController($controller) {
        if ($this->modx->loadClass('GoodNewsSubscriptionController', $this->config['modelPath'].'goodnews/', true, true)) {
            $classPath = $this->config['controllersPath'].strtolower($controller).'.class.php';
            $className = 'GoodNewsSubscription'.$controller.'Controller';

            if (file_exists($classPath)) {
                if (!class_exists($className)) {
                    $className = require_once $classPath;
                }
                if (class_exists($className)) {
                    $this->controller = new $className($this, $this->config);
                } else {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not load controller: '.$className.' at '.$classPath);
                }
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not load controller file: '.$classPath);
            }
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not load GoodNewsSubscriptionController class.');
        }
        return $this->controller;
    }

    /**
     * Loads the Validator class.
     *
     * @access public
     * @param string $type The name to give the service on the GoodnewsSubscription object
     * @param array $config An array of configuration parameters for the GoodnewsSubscriptionValidator class
     * @return GoodnewsSubscriptionValidator An instance of the GoodnewsSubscriptionValidator class.
     */
    public function loadValidator($type = 'validator', $config = array()) {
        if (!$this->modx->loadClass('GoodnewsSubscriptionValidator', $this->config['modelPath'].'goodnews/', true, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not load Validator class.');
            return false;
        }
        $this->$type = new GoodnewsSubscriptionValidator($this, $config);
        return $this->$type;
    }

    /**
     * Sends an email based on the specified information and templates.
     *
     * @access public
     * @param string $email The email to send to.
     * @param string $name The name of the user to send to.
     * @param string $subject The subject of the email.
     * @param array $properties A collection of properties.
     * @return array
     */
    public function sendEmail($email, $subject, $properties = array()) {
        $msg = $this->getChunk($properties['tpl'], $properties, $properties['tplType']);
        
        $msgAlt = '';
        if (!empty($properties['tplAlt'])) {
            $msgAlt = $this->getChunk($properties['tplAlt'], $properties, $properties['tplType']);
        }

        $this->modx->getService('mail', 'mail.modPHPMailer');
        $this->modx->mail->set(modMail::MAIL_BODY, $msg);
        $this->modx->mail->set(modMail::MAIL_FROM, $this->modx->getOption('emailsender'));
        $this->modx->mail->set(modMail::MAIL_FROM_NAME, $this->modx->getOption('site_name'));
        $this->modx->mail->set(modMail::MAIL_SENDER, $this->modx->getOption('emailsender'));
        $this->modx->mail->set(modMail::MAIL_SUBJECT, $subject);
        if (!empty($msgAlt)) {
            $this->modx->mail->set(modMail::MAIL_BODY_TEXT, $msgAlt);
        }
        $this->modx->mail->address('to', $email, $email);
        $this->modx->mail->address('reply-to', $this->modx->getOption('emailsender'));
        $this->modx->mail->setHTML(true);
                
        $sent = $this->modx->mail->send();
        $this->modx->mail->reset();

        return $sent;
    }

    /**
     * Generates a random password.
     *
     * @access public
     * @param integer $length The length of the generated password.
     * @return string The newly-generated password.
     */
    public function generatePassword($length = 8) {
        $pword = '';
        $charmap = '0123456789bcdfghjkmnpqrstvwxyz';
        $i = 0;
        while ($i < $length) {
            $char = substr($charmap, rand(0, strlen($charmap) - 1), 1);
            if (!strstr($pword, $char)) {
                $pword .= $char;
                $i++;
            }
        }
        return $pword;
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
    public function getChunk($name, $properties, $type = 'modChunk') {
        $output = '';
        switch ($type) {
            case 'embedded':
                $this->modx->setPlaceholders($properties);
                break;
                
            case 'modChunk':
                $output .= $this->modx->getChunk($name, $properties);
                break;
                
            case 'file':
                $name = str_replace(array(
                    '{base_path}',
                    '{assets_path}',
                    '{core_path}',
                ),array(
                    $this->modx->getOption('base_path'),
                    $this->modx->getOption('assets_path'),
                    $this->modx->getOption('core_path'),
                ), $name);
                $output .= file_get_contents($name);
                $this->modx->setPlaceholders($properties);
                break;
                
            case 'inline':
            default:
                /* default is inline, meaning the tpl content was provided directly in the property */
                $chunk = $this->modx->newObject('modChunk');
                $chunk->setContent($name);
                $chunk->setCacheable(false);
                $output .= $chunk->process($properties);
                break;
        }
        return $output;
    }

    /**
     * Loads the Hooks class.
     *
     * @access public
     * @param string $type The name of the Hooks service to load
     * @param array $config array An array of configuration parameters for the hooks class
     * @return GoodNewsSubscriptionHooks An instance of the GoodNewsSubscriptionHooks class.
     */
    public function loadHooks($type, $config = array()) {
        if (!$this->modx->loadClass('GoodNewsSubscriptionHooks', $this->config['modelPath'].'goodnews/', true, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not load Hooks class.');
            return false;
        }
        $this->$type = new GoodNewsSubscriptionHooks($this, $config);
        return $this->$type;
    }

    /**
     * Encodes an array/string of params for URL transmission
     *
     * @param array|string $params
     * @return string
     */
    public function encodeParams($params) {
        if (is_array($params)) {
            $params = serialize($params);
        } else {
            $params = serialize(array($params));
        }
        return strtr(base64_encode($params), '+/=', '-_,');
    }

    /**
     * Decode a serialized, encoded param string
     * 
     * @param string $params
     * @return array
     */
    public function decodeParams($params) {
        return unserialize(base64_decode(strtr($params, '-_,', '+/=')));
    }
    
    /**
     * Process MODx event results
     *
     * @param array $rs
     * @return string
     */
    public function getEventResult($rs) {
        $success = '';
        if (is_array($rs)) {
            foreach ($rs as $msg) {
                if (!empty($msg)) {
                    $success .= $msg."\n";
                }
            }
        } else {
            $success = $rs;
        }
        return $success;
    }
}
