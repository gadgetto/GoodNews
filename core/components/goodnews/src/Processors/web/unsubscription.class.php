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
 * Processor class which handles one-click unsubscription.
 *
 * @package goodnews
 * @subpackage processors
 */

class GoodNewsSubscriptionUnSubscriptionProcessor extends GoodNewsSubscriptionProcessor {
    /** @var modUser $user */
    public $user;
    
    /** @var modUserProfile $profile */
    public $profile;
    
    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta;

    /** @var integer $userid */
    public $userid;

    /**
     * @return boolean|string
     */
    public function process() {
        $this->user           = $this->controller->user;
        $this->profile        = $this->controller->profile;
        $this->subscribermeta = $this->controller->subscribermeta;
        
        $this->userid = $this->user->get('id');

        $removeUserData = $this->controller->getProperty('removeUserData', false, 'isset');
        if ($removeUserData) {
            // Completely remove user and related GoodNews data    
            if (!$this->removeUser()) {
                return $this->modx->lexicon('goodnews.profile_err_unsubscribe');
            }
        } else {        
            $this->removeSubscriptions();
        }
        
        $this->runPostHooks();
        return true;
    }

    /**
     * Check if user is member of MODX user groups or sudo.
     * 
     * @access public
     * @return boolean
     */
    public function isModxGroupMember() {
        $ismember = false;
        $groups = $this->user->getUserGroups();
        if ($groups) { $ismember = true; }
        if ($this->user->get('sudo') == true) { $ismember = true; }
        return $ismember;
    }

    /**
     * Remove a user and all it's related GoodNews data.
     * 
     * @access public
     * @return boolean
     */
    public function removeUser() {
        $removed = true;
        
        $this->removeGoodNewsData();
        
        // Do not remove or deactivate MODx users with MODX groups assigned or sudo!
        // Those user will only have related GoodNews data removed.
        if (!$this->isModxGroupMember()) {
            
            // Delete user object
            if (!$this->user->remove()) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not delete user object of subscriber - '.$this->userid.' with username: '.$this->user->get('username'));
                $removed = false;
            }
        }
        return $removed;
    }

    /**
     * Remove all GoodNews data (meta + subscriptions).
     * 
     * @access public
     * @return void
     */
    public function removeGoodNewsData() {
        $this->removeSubscriptions();
        if (is_object($this->subscribermeta)) { $this->subscribermeta->remove(); }        
    }
    
    /**
     * Remove all GoodNews subscriptions.
     * 
     * @access public
     * @return void
     */
    public function removeSubscriptions() {
        $result = $this->modx->removeCollection('GoodNewsCategoryMember', array('member_id' => $this->userid));
        $result = $this->modx->removeCollection('GoodNewsGroupMember', array('member_id' => $this->userid));
        
        // Change sid to invalidate all secure links
        $this->subscribermeta->set('sid', md5(time().$this->userid));
        $this->subscribermeta->save();
    }

    /**
     * Run any post unsubscription hooks.
     *
     * @return void
     */
    public function runPostHooks() {
        $postHooks = $this->controller->getProperty('postHooks', '');
        $this->controller->loadHooks('postHooks');
        
        $fields = array();
        $fields['goodnewssubscription.user'] = &$this->user;
        $fields['goodnewssubscription.profile'] = &$this->profile;
        
        $this->controller->postHooks->loadMultiple($postHooks, $fields);

        /* process hooks */
        if ($this->controller->postHooks->hasErrors()) {
            $errors = array();
            $errTpl = $this->controller->getProperty('errTpl');
            $errs = $this->controller->postHooks->getErrors();
            foreach ($errs as $key => $error) {
                $errors[$key] = str_replace('[[+error]]', $error, $errTpl);
            }
            $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
            $this->modx->toPlaceholders($errors, $placeholderPrefix.'error');
            $errorMsg = $this->controller->postHooks->getErrorMessage();
            $this->modx->toPlaceholder('message', $errorMsg, $placeholderPrefix.'error');
        }
    }
}
return 'GoodNewsSubscriptionUnSubscriptionProcessor';
