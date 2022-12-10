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
 * Processor class writes confirmation of subscription.
 *
 * @package goodnews
 * @subpackage processors
 */

class GoodNewsSubscriptionConfirmSubscriptionProcessor extends GoodNewsSubscriptionProcessor {
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
        
        $this->onBeforeUserActivate();
        
        $this->user->set('active', 1);
        $this->user->_fields['cachepwd'] = '';
        $this->user->setDirty('cachepwd');
        
        if (!$this->user->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not save activated user: '.$this->user->get('username'));
            $this->controller->redirectAfterFailure();
        }

        // Get the subscribers activation IP address
        $ip = $this->controller->getSubscriberIP();
        
        // Set some GDPR relevant fields (those fields will only be set when 2-opt-in is activated in snippet!)
        $this->subscribermeta->set('activatedon', time());
        $this->subscribermeta->set('ip_activated', $ip);

        if (!$this->subscribermeta->save()) {
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
            $subscriberProperties = $this->_getSubscriberProperties();
            $this->controller->sendSubscriptionEmail($subscriberProperties);
        }

        return true;
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
return 'GoodNewsSubscriptionConfirmSubscriptionProcessor';
