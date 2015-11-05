<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
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
 * Class which handles the request of secure links of users.
 *
 * @package goodnews
 * @subpackage controllers
 */

class GoodNewsSubscriptionRequestLinksController extends GoodNewsSubscriptionController {
    /** @var boolean $success */
    public $success = false;
    
    /**
     * Load default properties for this controller.
     *
     * @return void
     */
    public function initialize() {
        $this->modx->lexicon->load('goodnews:frontend');
        
        // todo: in all controllers fix the default properties array handling!
        //       If the input arrays have the same string keys, then the later value for that 
        //       key will overwrite the previous one.
        //       Therefore: the setDefaultProperties method always will take the values from snippet settings and ignore these settings here!
        //       This method should be named addDefaultProperties
        $this->setDefaultProperties(array(
            'unsubscribeResourceId'    => '',
            'profileResourceId'        => '',
            'submittedResourceId'      => '',
            'requestLinksEmailSubject' => $this->modx->lexicon('goodnews.requestlinks_email_subject'),
            'requestLinksEmailTpl'     => 'sample.GoodNewsRequestLinksEmailTpl',
            'requestLinksEmailTplAlt'  => '',
            'requestLinksEmailTplType' => 'modChunk',
            'errTpl'                   => '<span class="error">[[+error]]</span>',
            'emailField'               => 'email',
            'sendUnauthorizedPage'     => false,
            'submitVar'                => 'goodnews-requestlinks-btn',
            'successMsg'               => '',
            'validate'                 => '',
            'placeholderPrefix'        => '',
        ));
    }

    /**
     * Handle the GoodNewsRequestLinks snippet business logic.
     *
     * @return string
     */
    public function process() {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');

        if (!$this->hasPost()) { return ''; }
        if (!$this->loadDictionary()) { return ''; }
        
        $fields = $this->validateFields();
        
        $this->dictionary->reset();
        $this->dictionary->fromArray($fields);
        
        $this->validateEmail();

        if ($this->validator->hasErrors()) {
            $this->modx->toPlaceholders($this->validator->getErrors(), $placeholderPrefix.'error');
            $this->modx->setPlaceholder($placeholderPrefix.'validation_error', true);
        } else {
            $result = $this->runProcessor('RequestLinks');
            if ($result !== true) {
                $this->modx->setPlaceholder($placeholderPrefix.'error.message', $result);
            } else {
                $successMsg = $this->getProperty('successMsg', $this->modx->lexicon('goodnews.requestlinks_success')); // workaround -> see todo above!
                $this->modx->setPlaceholder($placeholderPrefix.'success.message', $successMsg);
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
    public function validateFields() {
        $this->loadValidator();
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
    public function validateEmail() {
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
return 'GoodNewsSubscriptionRequestLinksController';
