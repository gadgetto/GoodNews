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
 * Class which handles subscription process of users.
 *
 * @package goodnews
 * @subpackage controllers
 */

class GoodNewsSubscriptionSubscriptionController extends GoodNewsSubscriptionController {
    /** @var boolean $success */
    public $success = false;
    
    /**
     * Load default properties for this controller.
     *
     * @return void
     */
    public function initialize() {
        $this->modx->lexicon->load('goodnews:frontend');
        $this->setDefaultProperties(array(
            'activation'               => true,
            'activationttl'            => 180,
            'activationEmail'          => '',
            'activationEmailSubject'   => $this->modx->lexicon('goodnews.activation_email_subject'),
            'activationEmailTpl'       => 'sample.GoodNewsActivationEmailTpl',
            'activationEmailTplAlt'    => '',
            'activationEmailTplType'   => 'modChunk',
            'activationResourceId'     => '',
            'submittedResourceId'      => '',
            'sendSubscriptionEmail'    => false,
            'subscriptionEmailSubject' => $this->modx->lexicon('goodnews.subscription_email_subject'),
            'subscriptionEmailTpl'     => 'sample.GoodNewsSubscriptionEmailTpl',
            'subscriptionEmailTplAlt'  => '',
            'subscriptionEmailTplType' => 'modChunk',
            'errTpl'                   => '<span class="error">[[+error]]</span>',
            'useExtended'              => false,
            'excludeExtended'          => '',
            'emailField'               => 'email',
            'persistParams'            => '',
            'preHooks'                 => '',
            'postHooks'                => '',
            'redirectBack'             => '',
            'redirectBackParams'       => '',
            'submitVar'                => 'goodnews-subscription-btn',
            'successMsg'               => '',
            'usergroups'               => '',
            'usergroupsField'          => 'usergroups',
            'validate'                 => '',
            'grpFieldsetTpl'           => 'sample.GoodNewsGrpFieldsetTpl',
            'grpFieldTpl'              => 'sample.GoodNewsGrpFieldTpl',
            'grpNameTpl'               => 'sample.GoodNewsGrpNameTpl',
            'grpFieldHiddenTpl'        => 'sample.GoodNewsGrpFieldHiddenTpl',
            'catFieldTpl'              => 'sample.GoodNewsCatFieldTpl',
            'catFieldHiddenTpl'        => 'sample.GoodNewsCatFieldHiddenTpl',
            'groupsOnly'               => false,
            'includeGroups'            => '',
            'defaultGroups'            => '',
            'defaultCategories'        => '',
            'sort'                     => 'name',
            'dir'                      => 'ASC',
            'grpCatPlaceholder'        => 'grpcatfieldsets',
            'placeholderPrefix'        => '',
        ));
    }

    /**
     * Handle the GoodNewsSubscription snippet business logic.
     *
     * @return string
     */
    public function process() {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $groupsOnly        = $this->getProperty('groupsOnly', false);

        if (!$this->hasPost()) {
            $this->generateGrpCatFields();
            return '';
        }

        if (!$this->loadDictionary()) { return ''; }
        
        $fields = $this->validateFields();
        
        $this->dictionary->reset();
        $this->dictionary->fromArray($fields);
        
        // Synchronize categories with groups
        // (A category cant be selected without its parent group!)
        if (!$groupsOnly) { $this->selectParentGroupsByCategories(); }
     
        // Username is created automatically
        $this->generateUsername();
        
        // Password is created automatically
        $this->generatePassword();
        
        // Get the subscribers IP adress
        $this->getSubscriberIP();
        
        // Email is entered by subscriber
        $this->validateEmail();

        if ($this->validator->hasErrors()) {
            $this->modx->toPlaceholders($this->validator->getErrors(), $placeholderPrefix.'error');
            $this->modx->setPlaceholder($placeholderPrefix.'validation_error', true);
        } else {

            $this->loadPreHooks();

            // Process hooks
            if ($this->preHooks->hasErrors()) {
                $this->modx->toPlaceholders($this->preHooks->getErrors(), $placeholderPrefix.'error');
                $errorMsg = $this->preHooks->getErrorMessage();
                $this->modx->setPlaceholder($placeholderPrefix.'error.message', $errorMsg);
            } else {
            
                // If everything is ok, go ahead and execute the Subscription processor
                $result = $this->runProcessor('Subscription');
                if ($result !== true) {
                    $this->modx->setPlaceholder($placeholderPrefix.'error.message', $result);
                } else {
                    $this->success = true;
                }
            }
        }

        $selectedGroups = $this->dictionary->get('gongroups');
        $selectedCategories = $this->dictionary->get('goncategories');

        $this->generateGrpCatFields($selectedGroups, $selectedCategories);

        // Preserve field values if form loads again (no redirect in subscription processor!)
        $this->modx->setPlaceholders($this->dictionary->toArray(), $placeholderPrefix);
        return '';
    }

    /**
     * Load any pre-subscription hooks.
     *
     * @return void
     */
    public function loadPreHooks() {
        $preHooks  = $this->getProperty('preHooks', '');
        $submitVar = $this->getProperty('submitVar', 'goodnews-subscription-btn');
        
        $this->loadHooks('preHooks');
        
        if (!empty($preHooks)) {
            $fields = $this->dictionary->toArray();
            // Do pre-register hooks
            $this->preHooks->loadMultiple($preHooks, $fields, array(
                'submitVar' => $submitVar,
            ));
            $values = $this->preHooks->getValues();
            if (!empty($values)) {
                $this->dictionary->fromArray($values);
            }
        }
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
     * Validate the email address, and ensure it is not empty or already taken.
     * MODx allow_multiple_emails setting is ignored -> we never let subscribe an email address more then once!
     *
     * @return boolean
     */
    public function validateEmail() {
        $emailField = $this->getProperty('emailField', 'email');
        
        $email = $this->dictionary->get($emailField);
        $success = true;

        // First ensure email field isn't empty
        if (empty($email) && !$this->validator->hasErrorsInField($emailField)) {
            $this->validator->addError($emailField, $this->modx->lexicon('goodnews.validator_field_required'));
            $success = false;
            
        } else {
            $emailTaken = $this->modx->getObject('modUserProfile', array('email' => $email));
            if ($emailTaken) {
                $this->validator->addError($emailField, $this->modx->lexicon('goodnews.validator_email_taken', array('email' => $email)));
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Generate a new unique username based on email address.
     * 
     * @access public
     * @return string $newusername
     */
    public function generateUsername() {
        // Username is generated from userid part of email address
        $emailField = $this->getProperty('emailField', 'email');
        $email = $this->dictionary->get($emailField);
        $parts = explode('@', $email);
        $usernamepart = $parts[0];

        // Add counter (john.doe_1, martin_2, ...) if username already exists
        $counter = 0;
        $newusername = $usernamepart;
        while ($this->usernameExists($newusername)) {
            $newusername = $usernamepart.'_'.$counter;
            $counter++;
        }
        $this->dictionary->set('username', $newusername);
        return $newusername;
    }

    /**
     * Check if a user(name) already exists.
     * 
     * @access public
     * @param string $username
     * @return boolean
     */
    public function usernameExists($username) {
        
        $usernameExists = false;
        
        $user = $this->modx->getObject('modUser', array('username' => $username));
		if (is_object($user)) {
    		$usernameExists = true;
        }
        return $usernameExists;
    }

    /**
     * Automatically generate a password for the user.
     *
     * @return string $password
     */
    public function generatePassword() {
        $classKey = $this->dictionary->get('class_key');
        if (empty($classKey)) $classKey = 'modUser';
        
        $user = $this->modx->newObject($classKey);
        $password = $user->generatePassword();
        $this->dictionary->set('password', $password);
        return $password;
    }

    /**
     * Helper function to get the "real" IP address of a subscriber.
     *
     * @access public
     * @return string $ip The IP address (or string 'unknown')
     */
    public function getSubscriberIP() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // validate IP
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        $this->dictionary->set('ip', $ip);
                        return $ip;
                    }
                }
            }
        }
        // If no IP could be determined
        $ip = 'unknown';
        $this->dictionary->set('ip', $ip);
        return $ip;
    }

}
return 'GoodNewsSubscriptionSubscriptionController';
