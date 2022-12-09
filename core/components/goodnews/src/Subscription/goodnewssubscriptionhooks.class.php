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
 * Base class for hooks handling.
 *
 * @package goodnews
 */

class GoodNewsSubscriptionHooks {
    /** @var array $errors A collection of all the processed errors so far. */
    public $errors = array();
    
    /** @var array $hooks A collection of all the processed hooks so far. */
    public $hooks = array();
    
    /** @var array $fields An array of key->name pairs storing the fields passed */
    public $fields = array();
    
    /** @var modX $modx A reference to the modX instance. */
    public $modx = null;
    
    /** @var GoodNewsSubscription $goodnewssubscription A reference to the GoodNewsSubscription instance. */
    public $goodnewssubscription;
    
    /** @var GoodNewsSubscriptionController $controller A reference to the GoodNewsSubscriptionController controller. */
    public $controller;

    /**
     * The constructor for the GoodNewsSubscriptionHooks class
     *
     * @param GoodNewsSubscription &$goodnewssubscription A reference to the GoodNewsSubscription class instance.
     * @param GoodNewsSubscriptionController &$controller A reference to the current controller.
     * @param array $config An array of configuration parameters.
     */
    function __construct(GoodNewsSubscription &$goodnewssubscription, GoodNewsSubscriptionController &$controller, array $config = array()) {
        $this->goodnewssubscription =& $goodnewssubscription;
        $this->modx =& $goodnewssubscription->modx;
        $this->controller =& $controller;
        $this->config = array_merge(array(),$config);
    }

    /**
     * Loads an array of hooks. If one fails, will not proceed.
     *
     * @access public
     * @param array $hooks The hooks to run.
     * @param array $fields The fields and values of the form
     * @param array $options An array of options to pass to the hook.
     * @return array An array of field name => value pairs.
     */
    public function loadMultiple($hooks, $fields, array $options = array()) {
        if (empty($hooks)) return array();
        if (is_string($hooks)) $hooks = explode(',', $hooks);

        $this->hooks = array();
        $this->fields =& $fields;
        
        foreach ($hooks as $hook) {
            $hook = trim($hook);
            $success = $this->load($hook, $this->fields, $options);
            if (!$success) return $this->hooks;
            /* dont proceed if hook fails */
        }
        return $this->hooks;
    }

    /**
     * Load a hook. Stores any errors for the hook to $this->errors.
     *
     * @access public
     * @param string $hookName The name of the hook. May be a Snippet name.
     * @param array $fields The fields and values of the form.
     * @param array $options An array of options to pass to the hook.
     * @param array $customProperties Any other custom properties to load into a custom hook.
     * @return boolean True if hook was successful.
     */
    public function load($hookName, $fields = array(), array $options = array(), array $customProperties = array()) {
        $success = false;
        if (!empty($fields)) $this->fields =& $fields;
        $this->hooks[] = $hookName;

        $reserved = array(
            'load',
            '_process',
            '__construct',
            'getErrorMessage',
            'addError',
            'getValue',
            'getValues',
            'setValue',
            'setValues'
        );
        
        if (method_exists($this, $hookName) && !in_array($hookName, $reserved)) {
            /* built-in hooks */
            $success = $this->$hookName($this->fields);

        } else if ($snippet = $this->modx->getObject('modSnippet', array('name' => $hookName))) {
            /* custom snippet hook */
            $properties = array_merge($this->goodnewssubscription->config, $options);
            $properties['goodnewssubscription'] =& $this->goodnewssubscription;
            $properties['hook'] =& $this;
            $properties['fields'] =& $this->fields;
            $properties['errors'] =& $this->errors;
            $success = $snippet->process($properties);

        } else {
            /* search for a file-based hook */
            $this->modx->parser->processElementTags('', $hookName, true, true);
            if (file_exists($hookName)) {
                $success = $this->_loadFileBasedHook($hookName, $customProperties);
            } else {
                /* no hook found */
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not find hook "'.$hookName.'".');
                $success = false;
            }
        }

        if (is_array($success) && !empty($success)) {
            $this->errors = array_merge($this->errors, $success);
            $success = false;
        } else if ($success != true) {
            $this->errors[$hookName] .= ' '.$success;
            $success = false;
        }
        return $success;
    }

    /**
     * Attempt to load a file-based hook given a name
     * @param string $path The absolute path of the hook file
     * @param array $customProperties An array of custom properties to run with the hook
     * @return boolean True if the hook succeeded
     */
    private function _loadFileBasedHook($path, array $customProperties = array()) {
        $scriptProperties = array_merge($this->goodnewssubscription->config, $customProperties);
        $goodnewssubscription =& $this->goodnewssubscription;
        $hook =& $this;
        $fields = $this->fields;
        $errors =& $this->errors;
        try {
            $success = include $path;
        } catch (Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] '.$e->getMessage());
        }
        return $success;
    }

    /**
     * Gets the error messages compiled into a single string.
     *
     * @access public
     * @param string $delim The delimiter between each message.
     * @return string The concatenated error message
     */
    public function getErrorMessage($delim = "\n") {
        return implode($delim,$this->errors);
    }
    
    /**
     * Adds an error to the stack.
     *
     * @access private
     * @param string $key The field to add the error to.
     * @param string $value The error message.
     * @return string The added error message with the error wrapper.
     */
    public function addError($key, $value) {
        $this->errors[$key] .= $value;
        return $this->errors[$key];
    }

    /**
     * See if there are any errors in the stack.
     *
     * @return boolean
     */
    public function hasErrors() {
        return !empty($this->errors);
    }

    /**
     * Get all errors for this current request
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }


    /**
     * Sets the value of a field.
     *
     * @param string $key The field name to set.
     * @param mixed $value The value to set to the field.
     * @return mixed The set value.
     */
    public function setValue($key, $value) {
        $this->fields[$key] = $value;
        return $this->fields[$key];
    }

    /**
     * Sets an associative array of field name and values.
     *
     * @param array $values A key/name pair of fields and values to set.
     */
    public function setValues($values) {
        foreach ($values as $key => $value) {
            $this->setValue($key, $value);
        }
    }

    /**
     * Gets the value of a field.
     *
     * @param string $key The field name to get.
     * @return mixed The value of the key, or null if non-existent.
     */
    public function getValue($key) {
        if (array_key_exists($key, $this->fields)) {
            return $this->fields[$key];
        }
        return null;
    }

    /**
     * Gets an associative array of field name and values.
     *
     * @return array $values A key/name pair of fields and values.
     */
    public function getValues() {
        return $this->fields;
    }

    /**
     * Redirect to a specified URL.
     *
     * Properties needed:
     * - redirectTo - the ID of the Resource to redirect to.
     *
     * @param array $fields An array of cleaned POST fields
     * @return boolean False if unsuccessful.
     */
    public function redirect(array $fields = array()) {
        if (empty($this->goodnewssubscription->config['redirectTo'])) return false;

        $url = $this->modx->makeUrl($this->goodnewssubscription->config['redirectTo'], '', '', 'abs');
        return $this->modx->sendRedirect($url);
    }

    /**
     * Send an email of the form.
     *
     * Properties:
     * - emailTpl - The chunk name of the chunk that will be the email template.
     * This will send the values of the form as placeholders.
     * - emailTo - A comma separated list of email addresses to send to
     * - emailToName - A comma separated list of names to pair with addresses.
     * - emailFrom - The From: email address. Defaults to either the email
     * field or the emailsender setting.
     * - emailFromName - The name of the From: user.
     * - emailSubject - The subject of the email.
     * - emailHtml - Boolean, if true, email will be in HTML mode.
     *
     * @access public
     * @param array $fields An array of cleaned POST fields
     * @return boolean True if email was successfully sent.
     */
    public function email(array $fields = array()) {
        $tpl = $this->modx->getOption('emailTpl', $this->goodnewssubscription->config,'');
        $emailHtml = $this->modx->getOption('emailHtml', $this->goodnewssubscription->config,true);

        /* get from name */
        $emailFrom = $this->modx->getOption('emailFrom', $this->goodnewssubscription->config,'');
        if (empty($emailFrom)) {
            $emailFrom = !empty($fields['email']) ? $fields['email'] : $this->modx->getOption('emailsender');
        }
        $emailFrom = $this->_process($emailFrom, $fields);
        $emailFromName = $this->modx->getOption('emailFromName', $this->goodnewssubscription->config, $emailFrom);
        $emailFromName = $this->_process($emailFromName, $fields);

        /* get subject */
        if (!empty($fields['subject']) && $this->modx->getOption('emailUseFieldForSubject', $this->goodnewssubscription->config, true)) {
            $subject = $fields['subject'];
        } else {
            $subject = $this->modx->getOption('emailSubject', $this->goodnewssubscription->config, '');
        }
        $subject = $this->_process($subject, $fields);

        /* check email to */
        $emailTo = $this->modx->getOption('emailTo', $this->goodnewssubscription->config, '');
        $emailToName = $this->modx->getOption('emailToName', $this->goodnewssubscription->config, $emailTo);
        if (empty($emailTo)) {
            $this->errors['emailTo'] = $this->modx->lexicon('goodnews.email_no_recipient');
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] '.$this->modx->lexicon('goodnews.email_no_recipient'));
            return false;
        }

        /* compile message */
        if (empty($tpl)) {
            $tpl = 'email';
            $f = '';
            foreach ($fields as $k => $v) {
                if ($k == 'nospam') continue;
                if (is_array($v) && !empty($v['name']) && isset($v['error']) && $v['error'] == UPLOAD_ERR_OK) {
                    $v = $v['name'];
                }
                $f .= '<strong>'.$k.'</strong>: '.$v.'<br />'."\n";
            }
            $fields['fields'] = $f;
        }
        $message = $this->goodnewssubscription->getChunk($tpl, $fields);

        /* load mail service */
        $this->modx->getService('mail', 'mail.modPHPMailer');
        $this->modx->mail->set(modMail::MAIL_BODY, $emailHtml ? nl2br($message) : $message);
        $this->modx->mail->set(modMail::MAIL_FROM, $emailFrom);
        $this->modx->mail->set(modMail::MAIL_FROM_NAME, $emailFromName);
        $this->modx->mail->set(modMail::MAIL_SENDER, $emailFrom);
        $this->modx->mail->set(modMail::MAIL_SUBJECT, $subject);

        /* handle file fields */
        foreach ($fields as $k => $v) {
            if (is_array($v) && !empty($v['tmp_name']) && isset($v['error']) && $v['error'] == UPLOAD_ERR_OK) {
                $this->modx->mail->mailer->AddAttachment($v['tmp_name'], $v['name'], 'base64', !empty($v['type']) ? $v['type'] : 'application/octet-stream');
            }
        }

        /* add to: with support for multiple addresses */
        $emailTo = explode(',', $emailTo);
        $emailToName = explode(',', $emailToName);
        $numAddresses = count($emailTo);
        for ($i=0; $i < $numAddresses; $i++) {
            $etn = !empty($emailToName[$i]) ? $emailToName[$i] : '';
            if (!empty($etn)) $etn = $this->_process($etn, $fields);
            $emailTo[$i] = $this->_process($emailTo[$i], $fields);
            $this->modx->mail->address('to', $emailTo[$i], $etn);
        }

        /* reply to */
        $emailReplyTo = $this->modx->getOption('emailReplyTo', $this->goodnewssubscription->config, $emailFrom);
        $emailReplyTo = $this->_process($emailReplyTo, $fields);
        $emailReplyToName = $this->modx->getOption('emailReplyToName', $this->goodnewssubscription->config, $emailFromName);
        $emailReplyToName = $this->_process($emailReplyToName, $fields);
        $this->modx->mail->address('reply-to', $emailReplyTo, $emailReplyToName);

        /* cc */
        $emailCC = $this->modx->getOption('emailCC', $this->goodnewssubscription->config, '');
        if (!empty($emailCC)) {
            $emailCCName = $this->modx->getOption('emailCCName', $this->goodnewssubscription->config, '');
            $emailCC = explode(',', $emailCC);
            $emailCCName = explode(',', $emailCCName);
            $numAddresses = count($emailCC);
            for ($i=0; $i < $numAddresses; $i++) {
                $etn = !empty($emailCCName[$i]) ? $emailCCName[$i] : '';
                if (!empty($etn)) $etn = $this->_process($etn, $fields);
                $emailCC[$i] = $this->_process($emailCC[$i], $fields);
                $this->modx->mail->address('cc', $emailCC[$i], $etn);
            }
        }

        /* bcc */
        $emailBCC = $this->modx->getOption('emailBCC', $this->goodnewssubscription->config, '');
        if (!empty($emailBCC)) {
            $emailBCCName = $this->modx->getOption('emailBCCName', $this->goodnewssubscription->config, '');
            $emailBCC = explode(',', $emailBCC);
            $emailBCCName = explode(',', $emailBCCName);
            $numAddresses = count($emailBCC);
            for ($i=0; $i < $numAddresses; $i++) {
                $etn = !empty($emailBCCName[$i]) ? $emailBCCName[$i] : '';
                if (!empty($etn)) $etn = $this->_process($etn, $fields);
                $emailBCC[$i] = $this->_process($emailBCC[$i], $fields);
                $this->modx->mail->address('bcc',$emailBCC[$i], $etn);
            }
        }

        /* set HTML */
        $this->modx->mail->setHTML($emailHtml);

        /* send email */
        $sent = $this->modx->mail->send();
        $this->modx->mail->reset(array(
            modMail::MAIL_CHARSET => $this->modx->getOption('mail_charset', null, 'UTF-8'),
            modMail::MAIL_ENCODING => $this->modx->getOption('mail_encoding', null, '8bit'),
        ));

        if (!$sent) {
            $this->errors[] = $this->modx->lexicon('goodnews.email_not_sent');
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] '.$this->modx->lexicon('goodnews.email_not_sent'));
        }

        return $sent;
    }

    /**
     *
     * @access public
     * @param string $str
     * @param array $placeholders An array of placeholder fields fields
     * @return string $str
     */
    public function _process($str, array $placeholders = array()) {
        foreach ($placeholders as $k => $v) {
            if (!is_object($v)) {
                $str = str_replace('[[+'.$k.']]', $v, $str);
            }
        }
        return $str;
    }

    /**
     * Ensure a field passes a spam filter.
     *
     * Properties:
     * - spamEmailFields - The email fields to check. A comma-delimited list.
     *
     * @access public
     * @param array $fields An array of cleaned POST fields
     * @return boolean True if email was successfully sent.
     */
    public function spam(array $fields = array()) {
        $passed = true;
        $spamEmailFields = $this->modx->getOption('spamEmailFields', $this->goodnewssubscription->config, 'email');
        $emails = explode(',',$spamEmailFields);
        if ($this->modx->loadClass('stopforumspam.StopForumSpam', $this->goodnewssubscription->config['modelPath'], true, true)) {
            $sfspam = new StopForumSpam($this->modx);
            foreach ($emails as $email) {
                $spamResult = $sfspam->check($_SERVER['REMOTE_ADDR'], $fields[$email]);
                if (!empty($spamResult)) {
                    $spamFields = implode($this->modx->lexicon('goodnews.spam_marked')."\n<br />", $spamResult);
                    $this->errors[$email] = $this->modx->lexicon('goodnews.spam_blocked', array(
                        'fields' => $spamFields,
                    ));
                    $passed = false;
                }
            }
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Couldnt load StopForumSpam class.');
        }
        return $passed;
    }
}