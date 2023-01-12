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

namespace Bitego\GoodNews\Controllers\Subscription;

use Bitego\GoodNews\Controllers\Subscription\Base;

/**
 * Controller class which handles the request of secure links of users.
 *
 * @package goodnews
 * @subpackage controllers
 */
class RequestLinks extends Base
{
    /** @var boolean $success */
    public $success = false;

    /**
     * Load default properties for this controller.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setDefaultProperties([
            'unsubscribeResourceId'    => '',
            'profileResourceId'        => '',
            'submittedResourceId'      => '',
            'requestLinksEmailSubject' => $this->modx->lexicon('goodnews.requestlinks_email_subject'),
            'requestLinksEmailTpl'     => 'sample.GoodNewsRequestLinksEmailChunk',
            'requestLinksEmailTplAlt'  => '',
            'requestLinksEmailTplType' => 'modChunk',
            'errTpl'                   => '<span class="error">[[+error]]</span>',
            'emailField'               => 'email',
            'sendUnauthorizedPage'     => false,
            'submitVar'                => 'goodnews-requestlinks-btn',
            'successMsg'               => $this->modx->lexicon('goodnews.requestlinks_success'),
            'validate'                 => '',
            'placeholderPrefix'        => '',
        ]);
    }

    /**
     * Handle the GoodNewsRequestLinks snippet business logic.
     *
     * @return string
     */
    public function process()
    {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');

        // Set Dictionary instance and load POST array
        /** @var Dictionary $dictionary */
        if (!$this->hasPost()) {
            return '';
        }
        // Set Validator instance and validate fields
        $fields = $this->validate();

        $this->dictionary->reset();
        $this->dictionary->fromArray($fields);

        $this->validateEmail();

        if ($this->validator->hasErrors()) {
            $this->modx->toPlaceholders($this->validator->getErrors(), $placeholderPrefix . 'error');
            $this->modx->setPlaceholder($placeholderPrefix . 'validation_error', true);
        } else {
            $result = $this->runProcessor('RequestLinks');
            if ($result !== true) {
                $this->modx->setPlaceholder($placeholderPrefix . 'error.message', $result);
            } else {
                $successMsg = $this->getProperty('successMsg');
                $this->modx->setPlaceholder($placeholderPrefix . 'success.message', $successMsg);
                $this->success = true;
            }
        }

        // Preserve field values if form loads again (no redirect in processor!)
        $this->modx->setPlaceholders($this->dictionary->toArray(), $placeholderPrefix);
        return '';
    }

    /**
     * Validate the form fields.
     *
     * @return array
     */
    protected function validate()
    {
        $this->validator = $this->subscription->loadValidator();
        $fields = $this->validator->validateFields($this->dictionary, $this->getProperty('validate', ''));
        foreach ($fields as $k => $v) {
            $fields[$k] = str_replace(array('[',']'), array('&#91;','&#93;'), $v);
        }
        return $fields;
    }

    /**
     * Validate the email address, and ensure it is not empty.
     *
     * @return boolean
     */
    protected function validateEmail()
    {
        $emailField = $this->getProperty('emailField', 'email');

        $email = $this->dictionary->get($emailField);
        $success = true;

        // Ensure email field isn't empty
        if (empty($email) && !$this->validator->hasErrorsInField($emailField)) {
            $this->validator->addError($emailField, $this->modx->lexicon('goodnews.validator_field_required'));
            $success = false;
        }
        return $success;
    }
}
