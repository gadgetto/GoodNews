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
 * Processor class which handles subscription forms when subscriber already has a MODX user account but no GoodNews meta data:
 *  - no new MODX user is created
 *  - a Subscription profile is created (SubscriberMeta)
 *  - no Group and/or Category selections are created!
 *  - a subscription success mail is sent (including the secure links to edit/cancel subscription)
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
        
        // Save subscriber meta
        $this->setSubscriberMeta();
        if (!$this->subscribermeta->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not save new subscriber meta data - '.$this->user->get('id').' with username: '.$this->user->get('username'));
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        $this->preparePersistentParameters();

        // Send a subscription success email including the secure links to edit subscription profile
        $sendSubscriptionEmail = $this->controller->getProperty('sendSubscriptionEmail', true, 'isset');
        if ($sendSubscriptionEmail) {
            $subscriberProperties = $this->_getSubscriberProperties();
            $this->controller->sendReSubscriptionEmail($subscriberProperties);
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
     * Get the subscriber properties and collect in array.
     * 
     * @access private
     * @return mixed $properties The collection of properties || false
     */
    private function _getSubscriberProperties() {

        $properties = array_merge(
            $this->user->toArray(),
            $this->profile->toArray(),
            $this->subscribermeta->toArray()
        );
                
        // Flatten extended fields:
        // extended.field1
        // extended.container1.field2
        // ...
        $extended = $this->profile->get('extended') ? $this->profile->get('extended') : array();
        if (!empty($extended)) {
            $extended = $this->_flattenExtended($extended, 'extended.');
        }
        $properties = array_merge(
            $properties,
            $extended
        );
        
        $properties = $this->_cleanupKeys($properties);
        return $properties;
    }

    /**
     * Manipulate/add/remove fields from array.
     *
     * @access private
     * @param array $properties
     * @return array $properties
     */
    private function _cleanupKeys(array $properties = array()) {
        unset(
            // users table
            $properties['id'],          // multiple occurrence; not needed
            $properties['password'],    // security!
            $properties['cachepwd'],    // security!
            $properties['hash_class'],  // security!
            $properties['salt'],        // security!
            // user_attributes table
            $properties['internalKey'], // not needed
            $properties['sessionid'],   // security!
            $properties['extended']     // not needed as its already flattened
        );    
        return $properties;
    }

    /**
     * Helper function to recursively flatten an array.
     * 
     * @access private
     * @param array $array The array to be flattened.
     * @param string $prefix The prefix for each new array key.
     * @return array $result The flattened and prefixed array.
     */
    private function _flattenExtended($array, $prefix = '') {
        $result = array();
        foreach($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + $this->_flattenExtended($value, $prefix.$key.'.');
            } else {
                $result[$prefix.$key] = $value;
            }
        }
        return $result;
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
        $fields['goodnewssubscription.user'] = &$this->user;
        $fields['goodnewssubscription.profile'] = &$this->profile;
        
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
