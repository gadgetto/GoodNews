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
        if (!$this->user->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not save activated user: '.$this->user->get('username'));
            $this->controller->redirectAfterFailure();
        }

        // Clear the cachepwd field
        $this->goodnewssubscription->emptyCachePwd($this->user->get('id'));

        // Invoke OnUserActivate event
        $this->modx->invokeEvent('OnUserActivate', array(
            'user' => &$this->user,
        ));        
        
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

        return true;
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
