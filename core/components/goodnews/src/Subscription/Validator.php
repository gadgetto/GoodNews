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

/**
 * Base class which handles custom validation.
 *
 * @package goodnews
 * @subpackage subscription
 */

class Validator
{
    /** @var modX $modx A reference to the modX instance */
    public $modx = null;

    /** @var Subscription $subscription A reference to the Subscription instance */
    public $subscription = null;

    /** @var array $errors A collection of all the processed errors so far */
    public $errors = [];

    /** @var array $errorsRaw A collection of all the non-processed errors so far */
    public $errorsRaw = [];

    /** @var array $fields A collection of all the validated fields so far */
    public $fields = [];

    /**
     * The constructor for the Validator class.
     *
     * @param Subscription &$subscription A reference to the Subscription class instance
     * @param array $config Optional. An array of configuration parameters
     * @return Validator
     */
    public function __construct(Subscription &$subscription, array $config = [])
    {
        $this->subscription = &$subscription;
        $this->modx = &$subscription->modx;
        $this->config = array_merge([
            'placeholderPrefix'            => '',
            'validationErrorBulkTpl'       => '<li>[[+error]]</li>',
            'validationErrorBulkSeparator' => "\n",
            'validationErrorMessage'       => $this->modx->lexicon('goodnews.validator_form_error'),
            'use_multibyte'                => (bool)$this->modx->getOption('use_multibyte', null, false),
            'encoding'                     => $this->modx->getOption('modx_charset', null, 'UTF-8'),
            'customValidators'             => !empty($this->subscription->config['customValidators'])
                ? explode(',', $this->subscription->config['customValidators'])
                : [],
        ], $config);
    }

    /**
     * Validates an array of fields. Returns the field names and values, with
     * the field names stripped of their validators.
     *
     * The key names can be in this format:
     *
     * name:validator=param:anotherValidator:oneMoreValidator=`param`
     *
     * @access public
     * @param Dictionary $dictionary The fields to validate.
     * @param string $validationFields
     * @return array An array of field name => value pairs.
     */
    public function validateFields(Dictionary $dictionary, array $validationFields = '')
    {
        $keys = $dictionary->toArray();
        $this->fields = $keys;

        // Process the list of fields that will be validated
        $validationFields = explode(',', $validationFields);
        $fieldValidators = [];

        foreach ($validationFields as $idx => $v) {
            // Allow multi-line definitions
            $v = trim(ltrim($v), ' ');
            // Explode into list separated by :
            $key = explode(':', $v);
            if (!empty($key[0])) {
                $field = $key[0];
                // Remove the field name from validator list
                array_splice($key, 0, 1);
                $fieldValidators[$field] = $key;
                // Prevent someone from bypassing a required field by removing it from the form
                if (!isset($this->fields[$field]) && strpos($field, '.') === false) {
                    $keys[$field] = !empty($this->fields[$v]) ? $this->fields[$v] : '';
                }
            }
        }

        /** @var string|array $v */
        foreach ($keys as $k => $v) {
            // Is an array field, ie contact[name]
            if (is_array($v) && !isset($_FILES[$k]) && is_string($k) && intval($k) == 0 && $k !== 0) {
                $isCheckbox = false;
                foreach ($v as $key => $val) {
                    if (!is_string($key)) {
                        $isCheckbox = true;
                        continue;
                    }
                    $subKey = $k . '.' . $key;
                    $this->validateFieldsHelper($subKey, $val, $fieldValidators);
                }
                if ($isCheckbox) {
                    $this->validateFieldsHelper($k, $v, $fieldValidators);
                }
            } else {
                $this->validateFieldsHelper($k, $v, $fieldValidators);
            }
        }
        // Remove fields that have . in name
        foreach ($this->fields as $field => $v) {
            if (strpos($field, '.') !== false || strpos($field, ':')) {
                unset($this->fields[$field]);
            }
        }

        // Add fields back into dictionary
        foreach ($this->fields as $k => $v) {
            $dictionary->set($k, $v);
        }

        return $this->fields;
    }

    /**
     * Helper method for validating fields.
     *
     * @param string $k
     * @param string $v
     * @param array $fieldValidators
     * @return void
     */
    private function validateFieldsHelper($k, $v, array $fieldValidators = [])
    {
        $key = explode(':', $k);
        $stripTags = strpos($k, 'allowTags') === false;

        if (isset($fieldValidators[$k])) {
            foreach ($fieldValidators[$k] as $fv) {
                if (strpos($fv, 'allowTags') !== false) {
                    $stripTags = false;
                }
            }
        }

        // Strip tags by default
        if ($stripTags && !is_array($v)) {
            $v = strip_tags($v);
        }

        // Handle checkboxes/radios with empty hiddens before that are field[] names
        if (is_array($v) && !isset($_FILES[$key[0]]) && empty($v[0])) {
            array_splice($v, 0, 1);
        }

        // Loop through validators and execute the old way, for backwards compatibility
        $validators = count($key);
        if ($validators > 1) {
            $this->fields[$key[0]] = $v;
            for ($i = 1; $i < $validators; $i++) {
                $this->validate($key[0], $v, $key[$i]);
            }
        } else {
            $this->fields[$k] = $v;
        }

        // Do new way of validation, which is more secure
        if (!empty($fieldValidators[$k])) {
            foreach ($fieldValidators[$k] as $validator) {
                $this->validate($k, $v, $validator);
            }
        }
    }

    /**
     * Strips validators from an array of fields.
     *
     * @param array $keys The data to strip
     * @return array
     */
    public function stripValidators(array $keys = [])
    {
        $fields = [];
        foreach ($keys as $k => $v) {
            $key = explode(':', $k);
            $validators = count($key);
            if ($validators > 1) {
                $fields[$key[0]] = $v;
            } else {
                $fields[$k] = $v;
            }
        }
        return $fields;
    }

    /**
     * Validates a field based on a custom rule, if specified.
     *
     * @access public
     * @param string $key The key of the field
     * @param mixed $value The value of the field
     * @param string $type Optional. The type of the validator to apply. Can
     *     either be a method name of lgnValidator or a Snippet name.
     * @return boolean True if validation was successful. If not, will store
     *     error messages to $this->errors.
     */
    public function validate($key, $value, $type = '')
    {
        /** @var boolean|array $validated */
        $validated = false;

        /** @var boolean $hasParams */
        $hasParams = $this->config['use_multibyte']
            ? mb_strpos($type, '=', 0, $this->config['encoding'])
            : strpos($type, '=');

        /** @var string|null $param The parameter value, if one is set */
        $param = null;
        if ($hasParams !== false) {
            $len = $this->config['use_multibyte']
                ? mb_strlen($type, $this->config['encoding'])
                : strlen($type);

            $s = $this->config['use_multibyte']
                ? mb_substr($type, $hasParams + 1, $len, $this->config['encoding'])
                : substr($type, $hasParams + 1, $len);

            $param = str_replace(['`', '^'], '', $s);

            $type = $this->config['use_multibyte']
                ? mb_substr($type, 0, $hasParams, $this->config['encoding'])
                : substr($type, 0, $hasParams);
        }

        /** @var array $invNames An array of invalid hook names to skip */
        $invNames = [
            'validate',
            'validateFields',
            'addError',
            '__construct'
        ];

        $customValidators = is_string($this->config['customValidators'])
            ? explode(',', $this->config['customValidators'])
            : $this->config['customValidators'];

        if (method_exists($this, $type) && !in_array($type, $invNames)) {
            // Built-in validator!
            $validated = $this->$type($key, $value, $param);

        // Only allow specified validators to prevent brute force execution of unwanted snippets
        } elseif (in_array($type, $customValidators)) {
            // Attempt to grab custom validator (Snippet)
            /** @var modSnippet|null $snippet */
            $snippet = $this->modx->getObject(modSnippet::class, array('name' => $type));
            if ($snippet) {
                /* custom snippet validator */
                $props = array_merge($this->subscription->config, [
                    'key'       => $key,
                    'value'     => $value,
                    'param'     => $param,
                    'type'      => $type,
                    'validator' => &$this,
                    'errors'    => &$this->errors,
                ]);
                $validated = $snippet->process($props);
            } else {
                // No validator found
                $this->modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[GoodNews] Could not find validator "' . $type . '" for field "' . $key . '".'
                );
                $validated = true;
            }
        } else {
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[GoodNews] Validator "' . $type . '" for field "' . $key .
                '" was not specified in the customValidators property.'
            );
            $validated = true;
        }

        // Handle return value errors
        if (!empty($validated)) {
            if (is_array($validated)) {
                foreach ($validated as $key => $errMsg) {
                    $this->addError($key, $errMsg);
                }
                $validated = false;
            } elseif ($validated !== '1' && $validated !== 1 && $validated !== true) {
                $this->addError($key, $validated);
                $validated = false;
            }
        }

        return $validated;
    }

    /**
     * Adds an error to the stack.
     *
     * @access private
     * @param string $key The field to add the error to
     * @param string $value The error message
     * @return string The added error message with the error wrapper
     */
    public function addError(string $key, $value)
    {
        $errTpl = $this->modx->getOption(
            'errTpl',
            $this->subscription->config,
            '<span class="error">[[+error]]</span>'
        );

        $this->errorsRaw[$key] = $value;
        if (!isset($this->errors[$key])) {
            $this->errors[$key] = '';
        }
        $this->errors[$key] .= ' ' . str_replace('[[+error]]', $value, $errTpl);
        return $this->errors[$key];
    }

    /**
     * Check to see if a field has errors already
     *
     * @param string $key
     * @return boolean
     */
    public function hasErrorsInField($key)
    {
        return array_key_exists($key, $this->errors) && !empty($this->errors[$key]);
    }

    /**
     * Check to see if there are any validator errors in the stack.
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Get all errors in the stack.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get all raw errors in the stack (errors without the wrapper).
     * @return array
     */
    public function getRawErrors()
    {
        return $this->errorsRaw;
    }

    /**
     * Check for a custom error message, otherwise use a lexicon entry.
     *
     * @param string $field
     * @param string $parameter
     * @param string $lexiconKey
     * @param array $properties
     * @return null|string
     */
    protected function getErrorMessage($field, $parameter, $lexiconKey, array $properties = [])
    {
        if (!empty($this->subscription->config[$field . '.' . $parameter])) {
            $message = $this->subscription->config[$field . '.' . $parameter];
            $this->modx->lexicon->set($lexiconKey, $message);
            $this->modx->lexicon($lexiconKey, $properties);
        } elseif (!empty($this->subscription->config[$parameter])) {
            $message = $this->subscription->config[$parameter];
            $this->modx->lexicon->set($lexiconKey, $message);
            $this->modx->lexicon($lexiconKey, $properties);
        } else {
            $message = $this->modx->lexicon($lexiconKey, $properties);
        }
        return $message;
    }

    /**
     * Process the errors that have occurred and setup the appropriate placeholders.
     *
     * @return void
     */
    public function processErrors()
    {
        $this->modx->toPlaceholders($this->getErrors(), $this->config['placeholderPrefix'] . 'error');

        $errs = [];
        foreach ($this->getRawErrors() as $field => $err) {
            $err = $field . ': ' . $err;
            $errs[] = str_replace('[[+error]]', $err, $this->config['validationErrorBulkTpl']);
        }
        $errs = implode($this->config['validationErrorBulkSeparator'], $errs);

        $validationErrorMessage = str_replace('[[+errors]]', $errs, $this->config['validationErrorMessage']);
        $this->modx->setPlaceholder(
            $this->config['placeholderPrefix'] . 'validation_error',
            true
        );
        $this->modx->setPlaceholder(
            $this->config['placeholderPrefix'] . 'validation_error_message',
            $validationErrorMessage
        );
    }

    /**
     * Resets the validator
     * @return void
     */
    public function reset()
    {
        $this->errors = [];
        $this->errorsRaw = [];
    }

    /**
     * Validator: Checks to see if field is required.
     * (Don't change method name!)
     *
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @return boolean
     */
    public function required(string $key, $value)
    {
        $success = false;
        if (is_array($value) && isset($_FILES[$key])) {
            // Handling file uploads
            $success = !empty($value['tmp_name']) && isset($value['error']) && $value['error'] == UPLOAD_ERR_OK
                ? true
                : false;
        } else {
            $success = !empty($value)
                ? true
                : false;
        }
        return $success
            ? true
            : $this->getErrorMessage($key, 'vTextRequired', 'goodnews.validator_field_required', [
                'field' => $key,
                'value' => is_array($value) ? implode(',', $value) : $value,
            ]);
    }

    /**
     * Validator: Checks to see if field is blank.
     * (Don't change method name!)
     *
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @return boolean
     */
    public function blank(string $key, $value)
    {
        return empty($value)
            ? true
            : $this->getErrorMessage($key, 'vTextBlank', 'goodnews.validator_field_not_empty', [
                'field' => $key,
                'value' => $value,
            ]);
    }

    /**
     * Validator: Checks to see if passwords match.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param string $param The parameter passed into the validator that
     *     contains the field to check the password against
     * @return boolean
     */
    public function password_confirm(string $key, $value, $param = 'password_confirm')
    {
        if (empty($value)) {
            return $this->modx->lexicon('goodnews.validator_password_not_confirmed');
        }
        $confirm = !empty($this->fields[$param]) ? $this->fields[$param] : '';
        if ($confirm != $value) {
            return $this->getErrorMessage($key, 'vTextPasswordConfirm', 'goodnews.validator_password_dont_match', [
                'field' => $key,
                'password' => $value,
                'password_confirm' => $confirm,
            ]);
        }
        return true;
    }

    /**
     * Validator: Checks to see if field value is an actual email address.
     * (Don't change method name!)
     *
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @return boolean
     */
    public function email(string $key, $value)
    {
        // Allow empty emails, :required should be used to prevent blank field
        if (empty($value)) {
            return true;
        }

        // Validate length and @
        $pattern = "^[^@]{1,64}\@[^\@]{1,255}$";
        $condition = $this->config['use_multibyte']
            ? @mb_ereg($pattern, $value)
            : @ereg($pattern, $value);

        if (!$condition) {
            return $this->getErrorMessage(
                $key,
                'vTextEmailInvalid',
                'goodnews.validator_email_invalid',
                [
                    'field' => $key,
                    'value' => $value,
                ]
            );
        }

        $email_array = explode("@", $value);
        $local_array = explode(".", $email_array[0]);

        for ($i = 0; $i < sizeof($local_array); $i++) {
            $pattern = "^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$";
            $condition = $this->config['use_multibyte']
                ? @mb_ereg($pattern, $local_array[$i])
                : @ereg($pattern, $local_array[$i]);

            if (!$condition) {
                return $this->getErrorMessage(
                    $key,
                    'vTextEmailInvalid',
                    'goodnews.validator_email_invalid',
                    [
                        'field' => $key,
                        'value' => $value,
                    ]
                );
            }
        }
        // Validate domain
        $pattern = "^\[?[0-9\.]+\]?$";
        $condition = $this->config['use_multibyte']
            ? @mb_ereg($pattern, $email_array[1])
            : @ereg($pattern, $email_array[1]);

        if (!$condition) {
            $domain_array = explode(".", $email_array[1]);
            if (sizeof($domain_array) < 2) {
                return $this->getErrorMessage(
                    $key,
                    'vTextEmailInvalidDomain',
                    'goodnews.validator_email_invalid_domain',
                    [
                        'field' => $key,
                        'value' => $value,
                    ]
                );
            }

            for ($i = 0; $i < sizeof($domain_array); $i++) {
                $pattern = "^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$";
                $condition = $this->config['use_multibyte']
                    ? @mb_ereg($pattern, $domain_array[$i])
                    : @ereg($pattern, $domain_array[$i]);

                if (!$condition) {
                    return $this->getErrorMessage(
                        $key,
                        'vTextEmailInvalidDomain',
                        'goodnews.validator_email_invalid_domain',
                        [
                            'field' => $key,
                            'value' => $value,
                        ]
                    );
                }
            }
        }
        return true;
    }

    /**
     * Validator: Checks to see if field value is shorter than $param.
     * (Don't change method name!)
     *
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param int $param The minimum length the field can be
     * @return boolean
     */
    public function minLength(string $key, $value, $param = 0)
    {
        $v = $this->config['use_multibyte'] ? mb_strlen($value, $this->config['encoding']) : strlen($value);
        if ($v < $param) {
            return $this->getErrorMessage($key, 'vTextMinLength', 'goodnews.validator_min_length', [
                'length' => $param,
                'field'  => $key,
                'value'  => $value,
            ]);
        }
        return true;
    }

    /**
     * Validator: Checks to see if field value is longer than $param.
     * (Don't change method name!)
     *
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param int $param The maximum length the field can be
     * @return boolean
     */
    public function maxLength(string $key, $value, $param = 999)
    {
        $v = $this->config['use_multibyte'] ? mb_strlen($value, $this->config['encoding']) : strlen($value);
        if ($v > $param) {
            return $this->getErrorMessage($key, 'vTextMaxLength', 'goodnews.validator_max_length', [
                'length' => $param,
                'field'  => $key,
                'value'  => $value,
            ]);
        }
        return true;
    }

    /**
     * Validator: Checks to see if field value is less than $param.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param int $param The minimum value the field can be
     * @return boolean
     */
    public function minValue(string $key, $value, $param = 0)
    {
        if ((float)$value < (float)$param) {
            return $this->getErrorMessage($key, 'vTextMinValue', 'goodnews.validator_min_value', [
                'field'       => $key,
                'passedValue' => $value,
                'value'       => $param,
            ]);
        }
        return true;
    }

    /**
     * Validator: Checks to see if field value is greater than $param.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param int $param The maximum value the field can be
     * @return boolean
     */
    public function maxValue(string $key, $value, $param = 0)
    {
        if ((float)$value > (float)$param) {
            return $this->getErrorMessage($key, 'vTextMaxValue', 'goodnews.validator_max_value', [
                'field'       => $key,
                'passedValue' => $value,
                'value'       => $param,
            ]);
        }
        return true;
    }

    /**
     * Validator: See if field contains a certain value.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param string $expr The regular expression to check against the field
     * @return boolean
     */
    public function contains(string $key, $value, $expr = '')
    {
        if (!preg_match('/' . $expr . '/i', $value)) {
            return $this->getErrorMessage($key, 'vTextContains', 'goodnews.validator_contains', [
                'field'       => $key,
                'passedValue' => $value,
                'value'       => $expr,
            ]);
        }
        return true;
    }

    /**
     * Validator: Strip a string from the value.
     * (Don't change method name!)
     *
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param string $param The value to strip from the field
     * @return void
     */
    public function strip(string $key, $value, $param = '')
    {
        $this->fields[$key] = str_replace($param, '', $value);
    }

    /**
     * Validator: Strip all tags in the field. The parameter can be a string of allowed tags.
     * (Don't change method name!)
     *
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param string $allowedTags A comma-separated list of tags to allow in the field's value
     * @return boolean
     */
    public function stripTags(string $key, $value, $allowedTags = '')
    {
        $this->fields[$key] = strip_tags($value, $allowedTags);
        return true;
    }

    /**
     * Validator: Strip all tags in the field. The parameter can be a string of allowed tags.
     * (Don't change method name!)
     *
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param string $allowedTags A comma-separated list of tags to allow
     *     in the field's value. Leave blank to allow all.
     * @return boolean
     */
    public function allowTags(string $key, $value, $allowedTags = '')
    {
        if (empty($allowedTags)) {
            return true;
        }
        $this->fields[$key] = strip_tags($value, $allowedTags);
        return true;
    }

    /**
     * Validator: Validates value between a range, specified by min-max.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param string $ranges The range the value should reside in
     * @return boolean
     */
    public function range(string $key, $value, $ranges = '0-1')
    {
        $range = explode('-', $ranges);
        if (count($range) < 2) {
            return $this->modx->lexicon('goodnews.validator_range_invalid');
        }
        if ($value < $range[0] || $value > $range[1]) {
            return $this->getErrorMessage($key, 'vTextRange', 'goodnews.validator_range', [
                'min'    => $range[0],
                'max'    => $range[1],
                'field'  => $key,
                'value'  => $value,
                'ranges' => $ranges,
            ]);
        }
        return true;
    }

    /**
     * Validator: Checks to see if the field is a number.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @return boolean
     */
    public function isNumber(string $key, $value)
    {
        if (!is_numeric(trim($value))) {
            return $this->getErrorMessage($key, 'vTextIsNumber', 'goodnews.validator_not_number', [
                'field' => $key,
                'value' => $value,
             ]);
        }
        return true;
    }

    /**
     * Validator: Checks to see if the field is a valid date. Allows for date formatting as well.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param string $format The format of the date (default: ISO date)
     * @return boolean
     */
    public function isDate(string $key, $value, $format = '%Y-%m-%d')
    {
        $ts = false;
        if (!empty($value)) {
            $ts = strtotime($value);
        }
        if ($ts === false || empty($value)) {
            return $this->getErrorMessage($key, 'vTextIsDate', 'goodnews.validator_not_date', [
                'format' => $format,
                'field'  => $key,
                'value'  => $value,
            ]);
        }
        if (!empty($format)) {
            $this->fields[$key] = strftime($format, $ts);
        }
        return true;
    }

    /**
     * Validator: Checks to see if a string is all lowercase.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @return boolean
     */
    public function islowercase(string $key, $value)
    {
        $v = $this->config['use_multibyte'] ? mb_strtolower($value, $this->config['encoding']) : strtolower($value);
        return strcmp($v, $value) == 0
            ? true
            : $this->getErrorMessage($key, 'vTextIsLowerCase', 'goodnews.validator_not_lowercase', [
            'field' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Validator: Checks to see if a string is all uppercase.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @return boolean
     */
    public function isuppercase(string $key, $value)
    {
        $v = $this->config['use_multibyte'] ? mb_strtoupper($value, $this->config['encoding']) : strtoupper($value);
        return strcmp($v, $value) == 0
            ? true
            : $this->getErrorMessage($key, 'vTextIsUpperCase', 'goodnews.validator_not_uppercase', [
            'field' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Validator: Checks a string for regular expression.
     * (Don't change method name!)
     *
     * @access public
     * @param string $key The name of the field
     * @param string $value The value of the field
     * @param string $expression The regexp to use
     * @return boolean
     */
    public function regexp(string $key, $value, $expression)
    {
        preg_match($expression, $value, $matches);
        return !empty($matches) && !empty($matches[0]) == true
            ? true
            : $this->getErrorMessage($key, 'vTextRegexp', 'goodnews.validator_not_regexp', [
            'field'  => $key,
            'value'  => $value,
            'regexp' => $expression,
        ]);
    }
}
