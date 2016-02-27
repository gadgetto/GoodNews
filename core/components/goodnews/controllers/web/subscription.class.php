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
 * Class which handles subscription process of users.
 *
 * @package goodnews
 * @subpackage controllers
 */

class GoodNewsSubscriptionSubscriptionController extends GoodNewsSubscriptionController {
    
    const SEARCH_BY_USERNAME = 'username';
    const SEARCH_BY_EMAIL    = 'email';
    
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
            'activation'                 => true,
            'activationttl'              => 180,
            'activationEmail'            => '',
            'activationEmailSubject'     => $this->modx->lexicon('goodnews.activation_email_subject'),
            'activationEmailTpl'         => 'sample.GoodNewsActivationEmailTpl',
            'activationEmailTplAlt'      => '',
            'activationEmailTplType'     => 'modChunk',
            'activationResourceId'       => '',
            'submittedResourceId'        => '',
            'sendSubscriptionEmail'      => false,
            'unsubscribeResourceId'      => '',
            'profileResourceId'          => '',
            'subscriptionEmailSubject'   => $this->modx->lexicon('goodnews.subscription_email_subject'),
            'subscriptionEmailTpl'       => 'sample.GoodNewsSubscriptionEmailTpl',
            'subscriptionEmailTplAlt'    => '',
            'subscriptionEmailTplType'   => 'modChunk',
            'reSubscriptionEmailSubject' => $this->modx->lexicon('goodnews.resubscription_email_subject'),
            'reSubscriptionEmailTpl'     => 'sample.GoodNewsReSubscriptionEmailTpl',
            'reSubscriptionEmailTplAlt'  => '',
            'reSubscriptionEmailTplType' => 'modChunk',
            'errTpl'                     => '<span class="error">[[+error]]</span>',
            'useExtended'                => false,
            'excludeExtended'            => '',
            'emailField'                 => 'email',
            'usernameField'              => 'username',
            'passwordField'              => 'password',
            'persistParams'              => '',
            'preHooks'                   => '',
            'postHooks'                  => '',
            'redirectBack'               => '',
            'redirectBackParams'         => '',
            'submitVar'                  => 'goodnews-subscription-btn',
            'successMsg'                 => '',
            'usergroups'                 => '',
            'usergroupsField'            => 'usergroups',
            'validate'                   => '',
            'grpFieldsetTpl'             => 'sample.GoodNewsGrpFieldsetTpl',
            'grpFieldTpl'                => 'sample.GoodNewsGrpFieldTpl',
            'grpNameTpl'                 => 'sample.GoodNewsGrpNameTpl',
            'grpFieldHiddenTpl'          => 'sample.GoodNewsGrpFieldHiddenTpl',
            'catFieldTpl'                => 'sample.GoodNewsCatFieldTpl',
            'catFieldHiddenTpl'          => 'sample.GoodNewsCatFieldHiddenTpl',
            'groupsOnly'                 => false,
            'includeGroups'              => '',
            'defaultGroups'              => '',
            'defaultCategories'          => '',
            'sort'                       => 'name',
            'dir'                        => 'ASC',
            'grpCatPlaceholder'          => 'grpcatfieldsets',
            'placeholderPrefix'          => '',
            'errorPage'                  => false,
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
        $userID = false;

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
             
        // Get the subscribers IP address
        $this->getSubscriberIP();

        $emailField = $this->getProperty('emailField', 'email');
        $email = $this->dictionary->get($emailField);
        
        // Email address is entered by subscriber
        if ($this->validateEmail($emailField, $email)) {
            
            // Is email address already in use? (existing MODX user!)
            // $userID is either false or holds the MODX user ID and
            // if an email address is existing more than once, a validator error is added.
            $userID = $this->emailExists($emailField, $email);
        }

        if ($this->validator->hasErrors()) {
            $this->modx->toPlaceholders($this->validator->getErrors(), $placeholderPrefix.'error');
            $this->modx->setPlaceholder($placeholderPrefix.'validation_error', true);
        } else {

            // Process hooks
            $this->loadPreHooks();

            if ($this->preHooks->hasErrors()) {
                $this->modx->toPlaceholders($this->preHooks->getErrors(), $placeholderPrefix.'error');
                $errorMsg = $this->preHooks->getErrorMessage();
                $this->modx->setPlaceholder($placeholderPrefix.'error.message', $errorMsg);
            } else {

                // There are 2 cases where an email address already exists:
                //
                //   1) an existing (active) GoodNews Subscriber
                //      Here we let the subscriber update his subscription profile
                //
                //   2) a MODX user (active) without GoodNews subscriptions:
                //      Here we let the user add subscriptions to his existing MODX account so he gets a GoodNews susbcriber
                if ($userID) {
                    
                    $userLoaded = false;
                    if ($this->getUserById($userID)) {
                        if ($this->getProfile()) {
                            $userLoaded = true;
                        }        
                    }
                    if (!$userLoaded) {
                        $this->redirectAfterFailure();
                    }

                    // An existing GoodNews Subscriber
                    if ($this->getSubscriberMeta($userID)) {
                    
                        // Execute the ReSubscription processor
                        // An existing Subscriber gets the same front-end reaction as a new Subscriber (Privacy!) but:
                        //  - no new MODX user is created
                        //  - a re-subscription mail is sent (including the secure links to edit/cancel subscription)
                        $result = $this->runProcessor('ReSubscription');
                    
                    // A MODX user without GoodNews subscriptions
                    } else {
                        
                        // Execute the ModxUserSubscription processor
                        // An existing MODX user gets the same front-end reaction as a new Subscriber (Privacy!) but:
                        //  - no new MODX user is created
                        //  - a Subscription profile is created
                        //  - a subscription success mail is sent (if enabled)
                        $result = $this->runProcessor('ModxUserSubscription');
                    }
                
                // A new Subscriber
                } else {
                    
                    $this->_setUsername();
                    $this->_setPassword();

                    // Execute the Subscription processor:
                    //  - a new MODX user is created
                    //  - a Subscription profile is created
                    //  - an activation mail is sent (if double opt-in is enabled)
                    //  - a subscription success mail is sent (if enabled)
                    $result = $this->runProcessor('Subscription');
                }
                
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
     * Validate the form fields.
     *
     * @access public
     * @return array $fields
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
     * @access public
     * @param string $emailField
     * @param string $email
     * @return boolean
     */
    public function validateEmail($emailField, $email) {
        $success = true;
        if (empty($email) && !$this->validator->hasErrorsInField($emailField)) {
            $this->validator->addError($emailField, $this->modx->lexicon('goodnews.validator_field_required'));
            $success = false;
        }
        return $success;
    }

    /**
     * Check if username is submitted via form or needs to be auto-generated.
     *  - if submitted via form - check if already exists
     *  - cleanup expired activations
     * 
     * @access private
     * @return boolean $success
     */
    private function _setUsername() {
        $usernameField = $this->getProperty('usernameField', 'username');
        $username = $this->dictionary->get($usernameField);
        
        $success = false;
        
        if (empty($username) && !$this->validator->hasErrorsInField($usernameField)) {
            $this->generateUsername();
            $success = true;
        } else {
            if ($this->usernameExists($username)) {
                $this->validator->addError($usernameField, $this->modx->lexicon('goodnews.validator_username_taken'));
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
     * @return boolean $exists
     */
    public function usernameExists($username) {
        $exists = false;
        
        $this->removeExpired($username, self::SEARCH_BY_USERNAME);
        
        $user = $this->modx->getObject('modUser', array('username' => $username));
		if (is_object($user)) {
    		$exists = true;
        }
        return $exists;
    }

    /**
     * Check if password is submitted via form or needs to be auto-generated.
     *
     * @access private
     * @return boolean $success
     */
    private function _setPassword() {
        $passwordField = $this->getProperty('passwordField', 'password');
        $password = $this->dictionary->get($passwordField);
        
        $success = false;
        
        if (empty($password) && !$this->validator->hasErrorsInField($passwordField)) {
            $this->_generatePassword();
            $success = true;
        }
        return $success;
    }

    /**
     * Automatically generate a password for the user.
     *
     * @access private
     * @return string $password
     */
    private function _generatePassword() {
        $classKey = $this->dictionary->get('class_key');
        if (empty($classKey)) { $classKey = 'modUser'; }
        
        $user = $this->modx->newObject($classKey);
        $password = $user->generatePassword();
        $this->dictionary->set('password', $password);
        return $password;
    }

    /**
     * Check if an email address already exists.
     * MODX allow_multiple_emails setting is ignored -> we never let subscribe an email address more then once!
     * 
     * @access public
     * @param string $emailField
     * @param string $email
     * @return mixed ID of MODX user || false
     */
    public function emailExists($emailField, $email) {
        $exists = false;
        
        $this->removeExpired($email);        
        
		$userProfile = $this->modx->getObject('modUserProfile', array('email' => $email));
		if (is_object($userProfile)) {
            // Check if we have more than 1 modUserProfiles based on this email
            // -> Normally this should't be necessary but it's possible that we have multiple users 
            //    with the same email address (if enabled in MODX system settings)
            if ($this->modx->getCount('modUserProfile', array('email' => $email)) > 1) {
                $this->validator->addError($emailField, $this->modx->lexicon('goodnews.validator_email_multiple', array('email' => $email)));
                $exists = false;
            } else {
        		$exists = $userProfile->get('internalKey');
            }
		}
		return $exists;
    }

    /**
     * Check if an email address belongs to a user object with an expired activation and if so -> remove!
     * 
     * @access public
     * @param string $search
     * @param string $searchMode (default=self::SEARCH_BY_EMAIL)
     * @return void
     */
    public function removeExpired($search, $searchMode = self::SEARCH_BY_EMAIL) {
        $activationttl = $this->getProperty('activationttl', 180);
        // convert UNIX timestamp value to ISO date (as "SubscriberMeta.createdon" is a date field)
        $expDate = date('Y-m-d H:i:s', time() - ($activationttl * 60));

        $c = $this->modx->newQuery('modUser');
        $c->leftJoin('modUserProfile', 'Profile');
        $c->leftJoin('GoodNewsSubscriberMeta', 'SubscriberMeta', 'modUser.id = SubscriberMeta.subscriber_id');
        
        switch ($searchMode) {
            default:
            case self::SEARCH_BY_EMAIL:
                $c->where(array('modUser.username' => $search));
                break;
            case self::SEARCH_BY_USERNAME:
                $c->where(array('Profile.email' => $search));
                break;
        }

        // in addition modUser must:
        // - be inactive
        // - have a cachepwd (this means it's an unactivated account)
        // - have SubscriberMeta.createdon date < expiration date (GoodNews setting)
        $c->where(array(
            'active' => false,
            'cachepwd:!=' => '', 
            'SubscriberMeta.createdon:<' => $expDate,
        ));
        
        $users = $this->modx->getIterator('modUser', $c);
        foreach ($users as $idx => $user) {
            $user->remove();
        }
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

    /**
     * Load any pre-subscription hooks.
     *
     * @return void
     */
    public function loadPreHooks() {
        $preHooks = $this->getProperty('preHooks','');
        $this->loadHooks('preHooks');
        
        if (!empty($preHooks)) {
            $fields = $this->dictionary->toArray();
            // pre-register hooks
            $this->preHooks->loadMultiple($preHooks, $fields, array(
                'submitVar' => $this->getProperty('submitVar'),
                'usernameField' => $this->getProperty('usernameField', 'username'),
            ));
            $values = $this->preHooks->getValues();
            if (!empty($values)) {
                $this->dictionary->fromArray($values);
            }
        }
    }
}
return 'GoodNewsSubscriptionSubscriptionController';
