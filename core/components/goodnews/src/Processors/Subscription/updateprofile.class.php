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
 * Processor class which handles subscription update forms
 *
 * @package goodnews
 * @subpackage processors
 */

class GoodNewsSubscriptionUpdateProfileProcessor extends GoodNewsSubscriptionProcessor {
    /** @var modUser $user */
    public $user;
    
    /** @var modUserProfile $profile */
    public $profile;
    
    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta;
    
    /**
     * @return boolean|string
     */
    public function process() {
        $this->user           = $this->controller->user;
        $this->profile        = $this->controller->profile;
        $this->subscribermeta = $this->controller->subscribermeta;
        
        $this->cleanseFields();

        //$dic = $this->controller->dictionary->toArray();
        //$this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] dictionary: '.$this->modx->toJson($dic));

        $this->setExtended();
        
        // Save user changes
        $this->setUserFields();
        if (!$this->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not save changed subscriber user data - '.$this->user->get('id').' with username: '.$this->user->get('username'));
            return $this->modx->lexicon('goodnews.profile_err_save');
        }

        // Update goodnews group member
        if (!$this->updateGroupMember()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not save changed subscriber group member data - '.$this->user->get('id').' with username: '.$this->user->get('username'));
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        // Update goodnews category member
        if (!$this->updateCategoryMember()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not save changed subscriber category member data - '.$this->user->get('id').' with username: '.$this->user->get('username'));
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        $this->runPostHooks();
        $this->handleSuccess();
        return true;
    }

    /**
     * Remove any fields used for anti-spam, submission from the dictionary.
     *
     * @return void
     */
    public function cleanseFields() {
        $submitVar = $this->controller->getProperty('submitVar', 'goodnews-updateprofile-btn');
        
        $this->dictionary->remove('nospam');
        $this->dictionary->remove('blank');
        if (!empty($submitVar)) { $this->dictionary->remove($submitVar); }
    }

    /**
     * Set the form fields to the user.
     *
     * @return void
     */
    public function setUserFields() {
        $fields = $this->dictionary->toArray();
        foreach ($fields as $key => $value) {
            $this->profile->set($key, $value);
        }
    }

    /**
     * Set and update the group member data.
     *
     * @return boolean
     */
    public function updateGroupMember() {
        $userid = $this->user->get('id');
        
        // First remove all previously stored group member entries
        $this->modx->removeCollection('GoodNewsGroupMember', array('member_id' => $userid));        
        
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
     * Set and update the category member data.
     *
     * @return boolean
     */
    public function updateCategoryMember() {
        $userid = $this->user->get('id');

        // First remove all previously stored category member entries
        $this->modx->removeCollection('GoodNewsCategoryMember', array('member_id' => $userid));

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
     * If desired, set any extended fields.
     *
     * @return void
     */
    public function setExtended() {
        $useExtended = $this->controller->getProperty('useExtended', false, 'isset');
        
        if ($useExtended) {
            // first cut out regular fields
            $excludeExtended = $this->controller->getProperty('excludeExtended', '');
            $excludeExtended = explode(',', $excludeExtended);

            $profileFields = $this->profile->toArray();
            $userFields = $this->user->toArray();
            
            $newExtended = array();
            $fields = $this->dictionary->toArray();
            
            foreach ($fields as $field => $value) {
                $isValidExtended = true;
                if (isset($profileFields[$field]) || isset($userFields[$field]) || in_array($field, $excludeExtended) || $field == 'nospam' || $field == 'nospam:blank') {
                    $isValidExtended = false;
                }
                if ($isValidExtended) {
                    $newExtended[$field] = $value;
                }
            }
            // now merge with existing extended data
            $extended = $this->profile->get('extended');
            $extended = is_array($extended) ? array_merge($extended, $newExtended) : $newExtended;
            $this->profile->set('extended', $extended);
        }
    }

    /**
     * Save the user data.
     *
     * @return boolean
     */
    public function save() {
        $this->controller->user->addOne($this->profile, 'Profile');
        $saved = $this->controller->user->save();
        return $saved;
    }

    /**
     * Run any post-update hooks.
     *
     * @return void
     */
    public function runPostHooks() {
        $postHooks = $this->controller->getProperty('postHooks', '');
        $this->controller->loadHooks('postHooks');
        
        $fields = $this->controller->dictionary->toArray();
        //$fields = $this->dictionary->toArray(); ??? bug in original file?
        $fields['goodnewssubscription.user'] = &$this->controller->user;
        $fields['goodnewssubscription.profile'] =&$this->profile;
        
        $this->controller->postHooks->loadMultiple($postHooks, $fields);

        /* process hooks */
        if ($this->controller->postHooks->hasErrors()) {
            $errors = array();
            $errTpl = $this->controller->getProperty('errTpl');
            $errs = $this->controller->postHooks->getErrors();
            foreach ($errs as $key => $error) {
                $errors[$key] = str_replace('[[+error]]', $error, $errTpl);
            }
            $placeholderPrefix = $this->controller->getProperty('placeholderPrefix', '');
            $this->modx->toPlaceholders($errors, $placeholderPrefix.'error');
            $errorMsg = $this->controller->postHooks->getErrorMessage();
            $this->modx->toPlaceholder('message', $errorMsg, $placeholderPrefix.'error');
        }
    }

    /**
     * Set the success placeholder.
     *
     * @return void
     */
    public function handleSuccess() {
        $placeholderPrefix = $this->controller->getProperty('placeholderPrefix', '');
        $successMsg        = $this->controller->getProperty('successMsg', $this->modx->lexicon('goodnews.profile_updated'));
        $this->modx->toPlaceholder('message', $successMsg, $placeholderPrefix.'success');
    }
}
return 'GoodNewsSubscriptionUpdateProfileProcessor';
