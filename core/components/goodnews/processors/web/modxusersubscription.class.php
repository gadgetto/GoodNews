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
 * Processor class which handles subscription forms when subscriber already has a MODX user account (w/o GoodNews meta data)
 *  - no new MODX user is created
 *  - a Subscription profile is created
 *  - a subscription success mail is sent (if enabled)
 *
 * @package goodnews
 * @subpackage processors
 */

class GoodNewsSubscriptionModxUserSubscriptionProcessor extends GoodNewsSubscriptionProcessor {
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
        $this->user           = $this->controller->user;
        $this->profile        = $this->controller->profile;
        $this->subscribermeta = $this->modx->newObject('GoodNewsSubscriberMeta');
        
        $this->cleanseFields();
        
        //$dic = $this->dictionary->toArray();
        //$this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] dictionary: '.$this->modx->toJson($dic));

        // Save user profile
        $this->setProfileFields();
        if (!$this->profile->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not update subscriber user profile - '.$this->user->get('id').' with username: '.$this->user->get('username'));
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

        // Activate Subscriber without double opt-in as the MODX user is alread registered
        $unsubscribeResourceId = $this->controller->getProperty('unsubscribeResourceId', '');
        $profileResourceId     = $this->controller->getProperty('profileResourceId', '');
        if (empty($unsubscribeResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] GoodNewsSubscription - snippet parameter unsubscribeResourceId not set.');
            return false;
        }
        if (empty($profileResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] GoodNewsSubscription - snippet parameter profileResourceId not set.');
            return false;
        }

        // Send a subscription success email including the secure links to edit subscription profile (if property set)
        $sendSubscriptionEmail = $this->controller->getProperty('sendSubscriptionEmail', false, 'isset');
        if ($sendSubscriptionEmail) {
            $subscriberProperties = array_merge(
                $this->user->toArray(),
                $this->profile->toArray(),
                $this->subscribermeta->toArray()
            );
            $this->controller->sendSubscriptionEmail($subscriberProperties);
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
     * Set the profile data using the existing profile object.
     *
     * @access public
     * @return void
     */
    public function setProfileFields() {
        $emailField      = $this->controller->getProperty('emailField', 'email');
        $useExtended     = $this->controller->getProperty('useExtended', false, 'isset');
        $usergroupsField = $this->controller->getProperty('usergroupsField', 'usergroups');
        
        $fields = $this->dictionary->toArray();

        // Set profile data
        $this->profile->fromArray($fields);
        $this->profile->set('email', $this->dictionary->get($emailField));
        if ($useExtended) { $this->setExtended(); }

        // Add modx user groups, if set
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
        $this->subscribermeta->set('createdon', strftime('%Y-%m-%d %H:%M:%S'));
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
        $selectedGroups = $this->dictionary->get('gongroups');
        
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
        $selectedCategories = $this->dictionary->get('goncategories');

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
     * If activated, set extra values in the form to profile extended field.
     *
     * @access public
     * @return void
     */
    public function setExtended() {
        $excludeExtended = $this->controller->getProperty('excludeExtended', '');
        $usergroupsField = $this->controller->getProperty('usergroupsField', 'usergroups');
        
        $excludeExtended = explode(',', $excludeExtended);
        
        $userFields = $this->user->toArray();
        $profileFields = $this->profile->toArray();
        
        $extended = array();
        $fields = $this->dictionary->toArray();
        
        foreach ($fields as $field => $value) {
            if (!isset($profileFields[$field]) && !isset($userFields[$field]) && $field != $usergroupsField && $field != 'gongroups' && $field != 'goncategories' && !in_array($field, $excludeExtended)) {
                $extended[$field] = $value;
            }
        }
        // set extended data
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
}
return 'GoodNewsSubscriptionModxUserSubscriptionProcessor';
