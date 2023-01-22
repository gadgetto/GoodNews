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

use MODX\Revolution\modSnippet;
use MODX\Revolution\Mail\modMail;
use Bitego\GoodNews\Service\StopForumSpam;

/**
 * Base class for hooks handling.
 *
 * @package goodnews
 * @subpackage subscription
 */

class Hooks
{
    /** @var modX $modx A reference to the modX instance */
    public $modx = null;

    /** @var Subscription $subscription A reference to the Subscription instance */
    public $subscription = null;

    /** @var object $controller A reference to the current controller instance */
    public $controller = null;

    /** @var array $config An array of configuration properties */
    public $config = [];

    /** @var array $errors A collection of all the processed errors so far */
    public $errors = [];

    /** @var array $hooks A collection of all the processed hooks so far */
    public $hooks = [];

    /** @var array $fields An array of key->name pairs storing the fields passed */
    public $fields = [];

    /**
     * The constructor for the Hooks class.
     *
     * @param Subscription &$subscription A reference to the Subscription class instance
     * @param object &$controller A reference to the current controller
     * @param array $config An array of configuration parameters
     */
    public function __construct(Subscription &$subscription, &$controller, array $config = [])
    {
        $this->modx = &$subscription->modx;
        $this->subscription = &$subscription;
        $this->controller = &$controller;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Loads an array of hooks. If one fails, will not proceed.
     *
     * @access public
     * @param mixed|array $hooks The hooks to run.
     * @param array &$fields The fields and values of the form
     * @param array $options An array of options to pass to the hook.
     * @return array An array of field name => value pairs.
     */
    public function loadMultiple($hooks, &$fields, array $options = [])
    {
        if (empty($hooks)) {
            return [];
        }
        if (is_string($hooks)) {
            $hooks = explode(',', $hooks);
        }

        $this->hooks = [];
        $this->fields = &$fields;

        foreach ($hooks as $hook) {
            $hook = trim($hook);
            $success = $this->load($hook, $this->fields, $options);
            if (!$success) {
                // Dont proceed if hook fails
                return $this->hooks;
            }
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
    public function load($hookName, &$fields = [], array $options = [], array $customProperties = [])
    {
        $success = false;
        if (!empty($fields)) {
            $this->fields = &$fields;
        }
        $this->hooks[] = $hookName;

        $reserved = [
            '__construct',
            'load',
            'processPlaceholders',
            'getErrorMessage',
            'addError',
            'getValue',
            'getValues',
            'setValue',
            'setValues'
        ];

        if (method_exists($this, $hookName) && !in_array($hookName, $reserved)) {
            // Built-in hooks
            $success = $this->$hookName($this->fields);
        } elseif ($snippet = $this->modx->getObject(modSnippet::class, ['name' => $hookName])) {
            // Custom Snippet hook
            $properties = array_merge($this->subscription->config, $options);
            $properties['subscription'] = &$this->subscription;
            $properties['hook'] = &$this;
            $properties['fields'] = &$this->fields;
            $properties['errors'] = &$this->errors;
            $success = $snippet->process($properties);
        } else {
            // Search for a file-based hook
            $this->modx->parser->processElementTags('', $hookName, true, true);
            if (file_exists($hookName)) {
                $success = $this->loadFileBasedHook($hookName, $customProperties);
            } else {
                // No hook found
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not find hook "' . $hookName . '".');
                $success = false;
            }
        }

        if (is_array($success) && !empty($success)) {
            $this->errors = array_merge($this->errors, $success);
            $success = false;
        } elseif ($success != true) {
            $this->errors[$hookName] .= ' ' . $success;
            $success = false;
        }
        return $success;
    }

    /**
     * Attempt to load a file-based hook by agiven name.
     *
     * @param string $path The absolute path of the hook file
     * @param array $customProperties An array of custom properties to run with the hook
     * @return boolean True if the hook succeeded
     */
    private function loadFileBasedHook(string $path, array $customProperties = [])
    {
        $scriptProperties = array_merge($this->subscription->config, $customProperties);
        $subscription = &$this->subscription;
        $hook = &$this;
        $fields = $this->fields;
        $errors = &$this->errors;
        try {
            $success = include $path;
        } catch (\Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] ' . $e->getMessage());
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
    public function getErrorMessage(string $delim = "\n")
    {
        return implode($delim, $this->errors);
    }

    /**
     * Adds an error to the stack.
     *
     * @access private
     * @param string $key The field to add the error to.
     * @param string $value The error message.
     * @return string The added error message with the error wrapper.
     */
    public function addError(string $key, $value)
    {
        $this->errors[$key] .= $value;
        return $this->errors[$key];
    }

    /**
     * See if there are any errors in the stack.
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Get all errors for this current request
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Sets the value of a field.
     *
     * @param string $key The field name to set
     * @param mixed $value The value to set to the field
     * @return mixed The set value.
     */
    public function setValue(string $key, $value)
    {
        $this->fields[$key] = $value;
        return $this->fields[$key];
    }

    /**
     * Sets an associative array of field name and values.
     *
     * @param array $values A key/name pair of fields and values to set
     */
    public function setValues($values)
    {
        foreach ($values as $key => $value) {
            $this->setValue($key, $value);
        }
    }

    /**
     * Gets the value of a field.
     *
     * @param string $key The field name to get
     * @return mixed The value of the key, or null if non-existent
     */
    public function getValue(string $key)
    {
        if (array_key_exists($key, $this->fields)) {
            return $this->fields[$key];
        }
        return null;
    }

    /**
     * Gets an associative array of field name and values.
     *
     * @return array $values A key/name pair of fields and values
     */
    public function getValues()
    {
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
    public function redirect(array $fields = [])
    {
        if (empty($this->subscription->config['redirectTo'])) {
            return false;
        }
        $url = $this->modx->makeUrl($this->subscription->config['redirectTo'], '', '', 'abs');
        return $this->modx->sendRedirect($url);
    }

    /**
     * Send an email of the form.
     *
     * Properties:
     * - emailTpl - The chunk name of the chunk that will be the email template
     *
     * This will send the values of the form as placeholders
     * - emailTo - A comma separated list of email addresses to send to
     * - emailToName - A comma separated list of names to pair with addresses
     * - emailFrom - The From: email address. Defaults to either the email
     *     field or the emailsender setting
     * - emailFromName - The name of the From: user
     * - emailSubject - The subject of the email
     * - emailHtml - Boolean, if true, email will be in HTML mode
     *
     * @access public
     * @param array $fields An array of cleaned POST fields
     * @return boolean True if email was successfully sent
     */
    public function email(array $fields = [])
    {
        $tpl = $this->modx->getOption('emailTpl', $this->subscription->config, '');
        $emailHtml = $this->modx->getOption('emailHtml', $this->subscription->config, true);
        $emailFrom = $this->modx->getOption('emailFrom', $this->subscription->config, '');
        if (empty($emailFrom)) {
            $emailFrom = !empty($fields['email'])
                ? $fields['email']
                : $this->modx->getOption('emailsender');
        }

        $emailFrom = $this->processPlaceholders($emailFrom, $fields);
        $emailFromName = $this->modx->getOption('emailFromName', $this->subscription->config, $emailFrom);
        $emailFromName = $this->processPlaceholders($emailFromName, $fields);

        if (
            !empty($fields['subject']) &&
            $this->modx->getOption('emailUseFieldForSubject', $this->subscription->config, true)
        ) {
            $subject = $fields['subject'];
        } else {
            $subject = $this->modx->getOption('emailSubject', $this->subscription->config, '');
        }
        $subject = $this->processPlaceholders($subject, $fields);

        $emailTo = $this->modx->getOption('emailTo', $this->subscription->config, '');
        $emailToName = $this->modx->getOption('emailToName', $this->subscription->config, $emailTo);
        if (empty($emailTo)) {
            $this->errors['emailTo'] = $this->modx->lexicon('goodnews.email_no_recipient');
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] ' . $this->modx->lexicon('goodnews.email_no_recipient')
            );
            return false;
        }

        // Compile message
        if (empty($tpl)) {
            $tpl = 'email';
            $f = '';
            foreach ($fields as $k => $v) {
                if ($k == 'nospam') {
                    continue;
                }
                if (is_array($v) && !empty($v['name']) && isset($v['error']) && $v['error'] == UPLOAD_ERR_OK) {
                    $v = $v['name'];
                }
                $f .= '<strong>' . $k . '</strong>: ' . $v . '<br>' . "\n";
            }
            $fields['fields'] = $f;
        }
        $message = $this->subscription->getChunk($tpl, $fields);

        // Load mail service
        $mail = $this->modx->services->get('mail');
        $mail->set(modMail::MAIL_BODY, $emailHtml ? nl2br($message) : $message);
        $mail->set(modMail::MAIL_FROM, $emailFrom);
        $mail->set(modMail::MAIL_FROM_NAME, $emailFromName);
        $mail->set(modMail::MAIL_SENDER, $emailFrom);
        $mail->set(modMail::MAIL_SUBJECT, $subject);

        // Handle file fields
        foreach ($fields as $k => $v) {
            if (is_array($v) && !empty($v['tmp_name']) && isset($v['error']) && $v['error'] == UPLOAD_ERR_OK) {
                $type = !empty($v['type'])
                    ? $v['type']
                    : 'application/octet-stream';
                $mail->mailer->AddAttachment($v['tmp_name'], $v['name'], 'base64', $type);
            }
        }

        // Add to: with support for multiple addresses
        $emailTo = explode(',', $emailTo);
        $emailToName = explode(',', $emailToName);
        $numAddresses = count($emailTo);
        for ($i = 0; $i < $numAddresses; $i++) {
            $etn = !empty($emailToName[$i]) ? $emailToName[$i] : '';
            if (!empty($etn)) {
                $etn = $this->processPlaceholders($etn, $fields);
            }
            $emailTo[$i] = $this->processPlaceholders($emailTo[$i], $fields);
            $mail->address('to', $emailTo[$i], $etn);
        }

        $emailReplyTo = $this->modx->getOption('emailReplyTo', $this->subscription->config, $emailFrom);
        $emailReplyTo = $this->processPlaceholders($emailReplyTo, $fields);
        $emailReplyToName = $this->modx->getOption('emailReplyToName', $this->subscription->config, $emailFromName);
        $emailReplyToName = $this->processPlaceholders($emailReplyToName, $fields);
        $mail->address('reply-to', $emailReplyTo, $emailReplyToName);

        $emailCC = $this->modx->getOption('emailCC', $this->subscription->config, '');
        if (!empty($emailCC)) {
            $emailCCName = $this->modx->getOption('emailCCName', $this->subscription->config, '');
            $emailCC = explode(',', $emailCC);
            $emailCCName = explode(',', $emailCCName);
            $numAddresses = count($emailCC);
            for ($i = 0; $i < $numAddresses; $i++) {
                $etn = !empty($emailCCName[$i]) ? $emailCCName[$i] : '';
                if (!empty($etn)) {
                    $etn = $this->processPlaceholders($etn, $fields);
                }
                $emailCC[$i] = $this->processPlaceholders($emailCC[$i], $fields);
                $mail->address('cc', $emailCC[$i], $etn);
            }
        }

        $emailBCC = $this->modx->getOption('emailBCC', $this->subscription->config, '');
        if (!empty($emailBCC)) {
            $emailBCCName = $this->modx->getOption('emailBCCName', $this->subscription->config, '');
            $emailBCC = explode(',', $emailBCC);
            $emailBCCName = explode(',', $emailBCCName);
            $numAddresses = count($emailBCC);
            for ($i = 0; $i < $numAddresses; $i++) {
                $etn = !empty($emailBCCName[$i]) ? $emailBCCName[$i] : '';
                if (!empty($etn)) {
                    $etn = $this->processPlaceholders($etn, $fields);
                }
                $emailBCC[$i] = $this->processPlaceholders($emailBCC[$i], $fields);
                $mail->address('bcc', $emailBCC[$i], $etn);
            }
        }

        $mail->setHTML($emailHtml);

        $sent = $mail->send();
        $mail->reset([
            modMail::MAIL_CHARSET => $this->modx->getOption('mail_charset', null, 'UTF-8'),
            modMail::MAIL_ENCODING => $this->modx->getOption('mail_encoding', null, '8bit'),
        ]);

        if (!$sent) {
            $this->errors[] = $this->modx->lexicon('goodnews.email_not_sent');
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] ' . $this->modx->lexicon('goodnews.email_not_sent'));
        }

        return $sent;
    }

    /**
     * Process placeholders.
     *
     * @access public
     * @param string $str The string to process
     * @param array $placeholders An array of placeholder fields
     * @return string $str
     */
    public function processPlaceholders(string $str, array $placeholders = [])
    {
        foreach ($placeholders as $k => $v) {
            if (!is_object($v)) {
                $str = str_replace('[[+' . $k . ']]', $v, $str);
            }
        }
        return $str;
    }

    /**
     * Ensure the a field passes a spam filter.
     *
     * Properties:
     * - spamEmailFields - The email fields to check. (comma-delimited list)
     * - spamCheckIp     - Check IP adress? (boolean)
     *
     * @param array $fields An array of cleaned POST fields
     * @return bool True if email was successfully sent
     */
    public function spam(array $fields = [])
    {
        $passed = true;
        $spamFields = '';

        $spamEmailFields = $this->modx->getOption('spamEmailFields', $this->subscription->config, 'email');
        $checkIp = $this->modx->getOption('spamCheckIp', $this->subscription->config, true);

        $emails = explode(',', $spamEmailFields);
        $ip = $checkIp ? $_SERVER['REMOTE_ADDR'] : '';

        $sfspam = new StopForumSpam($this->modx);

        foreach ($emails as $email) {
            $spamResult = $sfspam->check($ip, $fields[$email]);
            if (!empty($spamResult)) {
                foreach ($spamResult as $value) {
                    $spamFields .= $value . $this->modx->lexicon('goodnews.spam_marked') . "\n<br>";
                }
                $this->addError(
                    $email,
                    $this->modx->lexicon('goodnews.spam_blocked', ['fields' => $spamFields])
                );
                $passed = false;
            }
        }
        return $passed;
    }
}
