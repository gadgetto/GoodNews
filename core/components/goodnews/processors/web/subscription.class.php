<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 * Based on code from Login add-on
 * Copyright 2012 by Jason Coward <jason@modx.com> and Shaun McCormick <shaun@modx.com>
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
 * Processor class which creates a subscriber:
 *  - a new MODX user is created
 *  - a Subscription profile is created (SubscriberMeta)
 *  - Group and/or Category selections are created!
 *  - an activation mail is sent (if double opt-in is enabled)
 *  - a subscription success mail is sent (if enabled)
 *
 * @package goodnews
 * @subpackage processors
 */

class GoodNewsSubscriptionSubscriptionProcessor extends GoodNewsSubscriptionProcessor {
    /** @var modUser $user */
    public $user;
    
    /** @var modUserProfile $profile */
    public $profile;
    
    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta;
    
    /** @var array $userGroups */
    public $userGroups = array();

    /** @var array $persistParams */
    public $persistParams = array();
    
    /**
     * @access public
     * @return mixed
     */
    public function process() {
        $this->user           = $this->modx->newObject('modUser');
        $this->profile        = $this->modx->newObject('modUserProfile');
        $this->subscribermeta = $this->modx->newObject('GoodNewsSubscriberMeta');
            
        $this->cleanseFields();
        
        //$dic = $this->dictionary->toArray();
        //$this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] dictionary: '.$this->modx->toJson($dic));

        // Save user
        $this->setUserFields();
        if (!$this->user->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not save new subscriber user data - '.$this->user->get('id').' with username: '.$this->user->get('username'));
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        // Save subscriber meta
        $this->setSubscriberMeta();
        if (!$this->subscribermeta->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not save new subscriber meta data - '.$this->user->get('id').' with username: '.$this->user->get('username'));
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        // Save goodnews group member
        if (!$this->saveGroupMember()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not save new subscriber group member data - '.$this->user->get('id').' with username: '.$this->user->get('username'));
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        // Save goodnews category member
        if (!$this->saveCategoryMember()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not save new subscriber category member data - '.$this->user->get('id').' with username: '.$this->user->get('username'));
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        $this->preparePersistentParameters();

        // Send activation email (if property set)
        $email = $this->profile->get('email');
        $activation = $this->controller->getProperty('activation', true, 'isset');
        $activationResourceId = $this->controller->getProperty('activationResourceId', '', 'isset');
        
        if ($activation && !empty($email) && !empty($activationResourceId)) {
            $this->sendActivationEmail();
        
        // Activate Subscriber without double opt-in
        } else {
            $this->onBeforeUserActivate();
            
            $this->user->set('active', 1);
            $this->user->_fields['cachepwd'] = '';
            $this->user->setDirty('cachepwd');
            
            if (!$this->user->save()) {
                $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not save activated user: '.$this->user->get('username'));
                $this->controller->redirectAfterFailure();
            }

            // Invoke OnUserActivate event
            $this->modx->invokeEvent('OnUserActivate', array(
                'user' => &$this->user,
            ));

            // Send a subscription success email including the secure links to edit subscription profile
            $sendSubscriptionEmail = $this->controller->getProperty('sendSubscriptionEmail', true, 'isset');
            if ($sendSubscriptionEmail) {
                $subscriberProperties = array_merge(
                    $this->user->toArray(),
                    $this->profile->toArray(),
                    $this->subscribermeta->toArray()
                );
                $this->controller->sendSubscriptionEmail($subscriberProperties);
            }
        }
        
        $this->runPostHooks();
        $this->checkForRedirect();

        $successMsg = $this->controller->getProperty('successMsg', '');
        $placeholderPrefix = $this->controller->getProperty('placeholderPrefix', '');
        $this->modx->toPlaceholder($placeholderPrefix.'success.message', $successMsg);
        
        return true;
    }

    /**
     * Remove any fields used for anti-spam, submission from the dictionary.
     *
     * @access public
     * @return void
     */
    public function cleanseFields() {
        $submitVar = $this->controller->getProperty('submitVar', 'goodnews-subscription-btn');
        $this->dictionary->remove('nospam');
        $this->dictionary->remove('blank');
        if (!empty($submitVar)) { $this->dictionary->remove($submitVar); }
    }

    /**
     * Set the user data and create the user/profile objects.
     *
     * @access public
     * @return void
     */
    public function setUserFields() {
        $emailField      = $this->controller->getProperty('emailField', 'email');
        $useExtended     = $this->controller->getProperty('useExtended', false, 'isset');
        $usergroupsField = $this->controller->getProperty('usergroupsField', 'usergroups');
        
        $fields = $this->dictionary->toArray();
        
        // Allow overriding of class key
        if (empty($fields['class_key'])) $fields['class_key'] = 'modUser';

        // Set user data
        $this->user->fromArray($fields);
        $this->user->set('username', $fields['username']);
        $this->user->set('active', 0);
        
        $version = $this->modx->getVersionData();
        // MODX 2.1.x +
        if (version_compare($version['full_version'], '2.1.0-rc1') >= 0) {
            $this->user->set('password', $fields['password']);
        // MODX 2.0.x
        } else {
            $this->user->set('password', md5($fields['password']));
        }

        // Set profile data
        $this->profile->fromArray($fields);
        $this->profile->set('email', $this->dictionary->get($emailField));
        if ($useExtended) { $this->setExtended(); }
        $this->user->addOne($this->profile, 'Profile');

        // Add MODX user groups, if set
        $userGroups = !empty($usergroupsField) && array_key_exists($usergroupsField, $fields) ? $fields[$usergroupsField] : array();
        $this->setUserGroups($userGroups);
    }

    /**
     * Set the subscriber meta data.
     *
     * @access public
     * @return void
     */
    public function setSubscriberMeta() {
        $userid = $this->user->get('id');
        $this->subscribermeta->set('subscriber_id', $userid);
        $this->subscribermeta->set('subscribedon', time());
        // create and set new sid
        $this->subscribermeta->set('sid', md5(time().$userid));
        $this->subscribermeta->set('testdummy', 0);
        $this->subscribermeta->set('ip', $this->dictionary->get('ip'));
    }

    /**
     * Set and save the group member data.
     *
     * @access public
     * @return void
     */
    public function saveGroupMember() {
        $userid = $this->user->get('id');
        $gongroups = $this->dictionary->get('gongroups');
        $selectedGroups = !empty($gongroups) ? $gongroups : array();
        
        $success = true;
        foreach ($selectedGroups as $grpid) {
            $groupmember = $this->modx->newObject('GoodNewsGroupMember');
            $groupmember->set('goodnewsgroup_id', $grpid);
            $groupmember->set('member_id', $userid);
            if (!$groupmember->save()) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Set and save the category member data.
     *
     * @access public
     * @return void
     */
    public function saveCategoryMember() {
        $userid = $this->user->get('id');
        $goncategories = $this->dictionary->get('goncategories');
        $selectedCategories = !empty($goncategories) ? $goncategories : array();

        $success = true;
        foreach ($selectedCategories as $catid) {
            $categorymember = $this->modx->newObject('GoodNewsCategoryMember');
            $categorymember->set('goodnewscategory_id', $catid);
            $categorymember->set('member_id', $userid);
            if (!$categorymember->save()) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * If activated, use extra field in form to write extra values to profile extended field.
     *
     * @access public
     * @return void
     */
    public function setExtended() {
        $excludeExtended = $this->controller->getProperty('excludeExtended', '');
        $usergroupsField = $this->controller->getProperty('usergroupsField', 'usergroups');
        
        $excludeExtended = explode(',', $excludeExtended);
        
        $alwaysExclude = array('gongroups','goncategories','password_confirm','passwordconfirm');
        
        // gets a list of fields for modUser and modUserProfile by class name
        $userFields    = $this->modx->getFields('modUser');
        $profileFields = $this->modx->getFields('modUserProfile');
        
        $extended = array();
        $fields = $this->dictionary->toArray();
        
        foreach ($fields as $field => $value) {
            if (
                !isset($profileFields[$field]) 
                && !isset($userFields[$field]) 
                && $field != $usergroupsField 
                && !in_array($field, $alwaysExclude) 
                && !in_array($field, $excludeExtended)
            ) {
                $extended[$field] = $value;
            }
        }
        // set extended field
        $this->profile->set('extended', $extended);
    }

    /**
     * If user groups were passed, set them here.
     *
     * @access public
     * @param string $userGroups Comma separated string of MODX user groups.
     * @return array
     */
    public function setUserGroups($userGroups) {
        $added = array();
        // If $userGroups set in form, override here; otherwise use snippet property
        $this->userGroups = !empty($userGroups) ? $userGroups : $this->controller->getProperty('usergroups', '');
        if (!empty($this->userGroups)) {
            $this->userGroups = is_array($this->userGroups) ? $this->userGroups : explode(',', $this->userGroups);

            foreach ($this->userGroups as $userGroupMeta) {
                $userGroupMeta = explode(':', $userGroupMeta);
                if (empty($userGroupMeta[0])) continue;

                // Get usergroup
                $pk = array();
                $pk[intval($userGroupMeta[0]) > 0 ? 'id' : 'name'] = trim($userGroupMeta[0]);
                $userGroup = $this->modx->getObject('modUserGroup', $pk);
                if (!$userGroup) continue;

                // Get role
                $rolePk = !empty($userGroupMeta[1]) ? $userGroupMeta[1] : 'Member';
                $role = $this->modx->getObject('modUserGroupRole', array('name' => $rolePk));

                // Create membership
                $member = $this->modx->newObject('modUserGroupMember');
                $member->set('member', 0);
                $member->set('user_group',$userGroup->get('id'));
                if (!empty($role)) {
                    $member->set('role', $role->get('id'));
                } else {
                    $member->set('role', 1);
                }
                $this->user->addMany($member, 'UserGroupMembers');
                $added[] = $userGroup->get('name');
            }
        }
        return $added;
    }

    /**
     * Setup persistent parameters to go through the request cycle.
     *
     * @access public
     * @return array
     */
    public function preparePersistentParameters() {
        $this->persistParams = $this->controller->getProperty('persistParams', '');
        if (!empty($this->persistParams)) $this->persistParams = $this->modx->fromJSON($this->persistParams);
        if (empty($this->persistParams) || !is_array($this->persistParams)) $this->persistParams = array();
        return $this->persistParams;
    }

    /**
     * Send an activation email to the user with an encrypted username and password hash, to allow for secure
     * activation processes that are not vulnerable to middle-man attacks.
     *
     * @access public
     * @return boolean
     */
    public function sendActivationEmail() {
        $emailProperties = $this->gatherActivationEmailProperties();

        // Send either to user's email or a specified activation email address
        $activationEmail = $this->controller->getProperty('activationEmail', $this->profile->get('email'));
        $subject = $this->controller->getProperty('activationEmailSubject', $this->modx->lexicon('goodnews.activation_email_subject'));
        
        return $this->goodnewssubscription->sendEmail($activationEmail, $subject, $emailProperties);
    }

    /**
     * Get the properties for the activation email
     *
     * @access public
     * @return array
     */
    public function gatherActivationEmailProperties() {
        // Generate a password and encode it and the username into the url
        $pword = $this->user->generatePassword();
        $confirmParams['lp'] = $this->goodnewssubscription->base64url_encode($pword);
        $confirmParams['lu'] = $this->goodnewssubscription->base64url_encode($this->user->get('username'));
        $confirmParams = array_merge($this->persistParams, $confirmParams);

        // If using redirectBack param, set here to allow dynamic redirection handling from other forms
        $redirectBack = $this->modx->getOption('redirectBack', $_REQUEST, $this->controller->getProperty('redirectBack', ''));
        if (!empty($redirectBack)) {
            $confirmParams['redirectBack'] = $redirectBack;
        }
        $redirectBackParams = $this->modx->getOption('redirectBackParams', $_REQUEST, $this->controller->getProperty('redirectBackParams', ''));
        if (!empty($redirectBackParams)) {
            $confirmParams['redirectBackParams'] = $redirectBackParams;
        }

        // Generate confirmation url
        $confirmUrl = $this->modx->makeUrl($this->controller->getProperty('activationResourceId', 1), '', $confirmParams, 'full');

        // Set confirmation email properties
        $emailTpl = $this->controller->getProperty('activationEmailTpl', 'sample.GoodNewsActivationEmailTpl');
        $emailTplAlt = $this->controller->getProperty('activationEmailTplAlt', '');
        $emailTplType = $this->controller->getProperty('activationEmailTplType', 'modChunk');
        
        $emailProperties = $this->user->toArray();
        $emailProperties['confirmUrl'] = $confirmUrl;
        $emailProperties['tpl'] = $emailTpl;
        $emailProperties['tplAlt'] = $emailTplAlt;
        $emailProperties['tplType'] = $emailTplType;

        $this->setCachePassword($pword);
        return $emailProperties;
    }

    /**
     * setCachePassword function.
     * 
     * @access public
     * @param mixed $password
     * @return bool $success
     */
    public function setCachePassword($password) {
        // Now set new password to registry to prevent middleman attacks.
        // Will read from the registry on the confirmation page.
        $this->modx->getService('registry', 'registry.modRegistry');
        $this->modx->registry->addRegister('goodnewssubscription', 'registry.modFileRegister');
        $this->modx->registry->goodnewssubscription->connect();
        $this->modx->registry->goodnewssubscription->subscribe('/useractivation/');
        $this->modx->registry->goodnewssubscription->send('/useractivation/', array($this->user->get('username') => $password), array(
            'ttl' => ($this->controller->getProperty('activationttl', 180) * 60),
        ));
        // Set cachepwd here to prevent re-registration of inactive users
        $this->user->set('cachepwd', md5($password));
        $success = $this->user->save();
        if (!$success) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not update cachepwd for activation for user: '.$this->user->get('username'));
        }
        return $success;
    }

    /**
     * Run any post-subscription hooks.
     *
     * @access public
     * @return void
     */
    public function runPostHooks() {
        $postHooks = $this->controller->getProperty('postHooks', '');
        $this->controller->loadHooks('postHooks');
        
        $fields = $this->dictionary->toArray();
        $fields['goodnewssubscription.user'] =& $this->user;
        $fields['goodnewssubscription.profile'] =& $this->profile;
        $fields['goodnewssubscription.usergroups'] = $this->userGroups;
        
        $this->controller->postHooks->loadMultiple($postHooks, $fields);
        if ($this->controller->postHooks->hasErrors()) {
            $errors = array();
            $hookErrors = $this->controller->postHooks->getErrors();
            foreach ($hookErrors as $key => $error) {
                $errors[$key] = str_replace('[[+error]]', $error, $this->controller->getProperty('errTpl'));
            }
            $placeholderPrefix = $this->controller->getProperty('placeholderPrefix', '');
            $this->modx->toPlaceholders($errors, $placeholderPrefix.'error');

            $errorMsg = $this->controller->postHooks->getErrorMessage();
            $this->modx->toPlaceholder('message', $errorMsg, $placeholderPrefix.'error');
        }
    }

    /**
     * Check for a redirect if the user subscription was successful. If one found, redirect.
     *
     * @access public
     * @return boolean
     */
    public function checkForRedirect() {
        // If provided a submittedResourceId, will redirect to that resource, with the GET param `email`
        $submittedResourceId = $this->controller->getProperty('submittedResourceId', '');
        if (!empty($submittedResourceId)) {
            $persistParams = array_merge($this->persistParams, array(
                'email' => $this->profile->get('email'),
            ));
            $url = $this->modx->makeUrl($submittedResourceId, '', $persistParams, 'full');
            $this->modx->sendRedirect($url);
            return true;
        }
        return false;
    }

    /**
     * Invoke OnBeforeUserActivateEvent, if result returns anything, do not proceed
     *
     * @access public
     * @return boolean
     */
    public function onBeforeUserActivate() {
        $success = true;
        $result = $this->modx->invokeEvent('OnBeforeUserActivate',array(
            'user' => &$this->user,
        ));
        $preventActivation = $this->goodnewssubscription->getEventResult($result);
        if (!empty($preventActivation)) {
            $success = false;
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] OnBeforeUserActivate event prevented activation for "'.$this->user->get('username').'" by returning false: '.$preventActivation);
            $this->controller->redirectAfterFailure();
        }
        return $success;
    }
}
return 'GoodNewsSubscriptionSubscriptionProcessor';
