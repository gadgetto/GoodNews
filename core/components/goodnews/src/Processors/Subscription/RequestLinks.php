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
 * Processor class which handles request links forms
 *
 * @package goodnews
 * @subpackage processors
 */

class GoodNewsSubscriptionRequestLinksProcessor extends GoodNewsSubscriptionProcessor {
    /** @var modUser $user */
    public $user;
    
    /** @var modUserProfile $profile */
    public $profile;
    
    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta;
    
    /** @var GoodNewsSubscriberMeta.sid $sid */
    public $sid;

    /** @var string $email */
    public $email;

    /**
     * @return mixed
     */
    public function process() {
        $this->cleanseFields();
        
        // If we can't find an appropriate subscriber in database,
        // we return true here but no requestlinks email is sent!
        // (This is for security an privacy reasons!)
        if (!$this->authenticateSubscriberByEmail()) {
            sleep(2); // this is for simulating delay which is normally caused by sending an email!
            return true;
        }

        // Send request links email
        $subscriberProperties = $this->_getSubscriberProperties();
        if (!$this->sendRequestLinksEmail($subscriberProperties)) {
            return $this->modx->lexicon('goodnews.email_not_sent');
        }
        $this->checkForRedirect();
        return true;
    }

    /**
     * Remove any fields used for anti-spam, submission from the dictionary.
     *
     * @return void
     */
    public function cleanseFields() {
        $submitVar = $this->controller->getProperty('submitVar', 'goodnews-requestlinks-btn');
        
        $this->dictionary->remove('nospam');
        $this->dictionary->remove('blank');
        if (!empty($submitVar)) { $this->dictionary->remove($submitVar); }
    }

    /**
     * Authenticate the subscriber by email address submitted via form
     * and load modUser, modUserProfile and GoodNewsSusbcriberMeta objects.
     * (Verification means we have extracted a valid sid from SubscriberMeta - we dont need real MODX login!)
     *
     * @return boolean
     */
    public function authenticateSubscriberByEmail() {
        $emailField = $this->controller->getProperty('emailField', 'email');
        $this->email = $this->dictionary->get($emailField);
        
        $verified = false;
        
        // get profile
        $this->profile = $this->modx->getObject('modUserProfile', array('email' => $this->email));
        
        if (is_object($this->profile)) {
            
            // get user by profile
            $this->user = $this->profile->getOne('User');
            $active = $this->user->get('active');
            
            // subscriber must be active!
            if ($active) {
                
                // get subscriber meta by user
                $userid = $this->user->get('id');
                $this->subscribermeta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $userid));
                if (is_object($this->subscribermeta)) {
                    $this->sid = $this->subscribermeta->get('sid');
                    $verified = true;
                }
            }
        }
        
        If (!$verified) {
            if ($this->controller->getProperty('sendUnauthorizedPage', false, 'isset')) {
                $this->modx->sendUnauthorizedPage();
            }
        }
        return $verified;
    }

    /**
     * Send an email to the user containing secure links to update or cancel subscriptions.
     *
     * @return boolean
     */
    public function sendRequestLinksEmail($emailProperties) {
        
        // Additional required properties
        $emailTpl = $this->controller->getProperty('requestLinksEmailTpl', 'sample.GoodNewsRequestLinksEmailChunk');
        $emailTplAlt = $this->controller->getProperty('requestLinksEmailTplAlt', '');
        $emailTplType = $this->controller->getProperty('requestLinksEmailTplType', 'modChunk');
        
        $params = array(
            'sid' => $this->sid,
        );
        
        $profileResourceId = $this->controller->getProperty('profileResourceId', '');
        if (empty($profileResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_WARN, '[GoodNews] GoodNewsRequestLinks - snippet parameter profileResourceId not set.');
        } else {
            $emailProperties['updateProfileUrl'] = $this->modx->makeUrl($profileResourceId, '', $params, 'full');
        }

        $unsubscribeResourceId = $this->controller->getProperty('unsubscribeResourceId', '');
        if (empty($unsubscribeResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_WARN, '[GoodNews] GoodNewsRequestLinks - snippet parameter unsubscribeResourceId not set.');
        } else {
            $emailProperties['unsubscribeUrl'] = $this->modx->makeUrl($unsubscribeResourceId, '', $params, 'full');
        }
        
        $emailProperties['tpl']     = $emailTpl;
        $emailProperties['tplAlt']  = $emailTplAlt;
        $emailProperties['tplType'] = $emailTplType;

        $subject = $this->controller->getProperty('requestLinksEmailSubject', $this->modx->lexicon('goodnews.requestlinks_email_subject'));
        
        return $this->goodnewssubscription->sendEmail($this->email, $subject, $emailProperties);
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
     * Check for a redirect.
     *
     * @return boolean
     */
    public function checkForRedirect() {
        // If provided a redirect id, will redirect to that resource, with the GET param `email` for you to use
        $submittedResourceId = $this->controller->getProperty('submittedResourceId', '');
        if (!empty($submittedResourceId)) {
            $params = array(
                'email' => $this->email,
            );
            $url = $this->modx->makeUrl($submittedResourceId, '', $params, 'full');
            $this->modx->sendRedirect($url);
            return true;
        }
        return false;
    }
}
return 'GoodNewsSubscriptionRequestLinksProcessor';
