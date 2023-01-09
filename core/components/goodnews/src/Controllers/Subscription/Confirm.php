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
 * Class which confirms a user's subscription after activation
 *
 * @package goodnews
 * @subpackage controllers
 */

class GoodNewsSubscriptionConfirmController extends GoodNewsSubscriptionController {
    /** @var modUser $user */
    public $user;
    
    /** @var modUserProfile $profile */
    public $profile;
    
    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta;

    /** @var string $username */
    public $username;
    
    /** @var string $password */
    public $password;
    
    /**
     * initialize function.
     * 
     * @access public
     * @return void
     */
    public function initialize() {
        $this->setDefaultProperties(array(
            'sendSubscriptionEmail'    => true,
            'unsubscribeResourceId'    => '',
            'profileResourceId'        => '',
            'subscriptionEmailSubject' => $this->modx->lexicon('goodnews.subscription_email_subject'),
            'subscriptionEmailTpl'     => 'sample.GoodNewsSubscriptionEmailChunk',
            'subscriptionEmailTplAlt'  => '',
            'subscriptionEmailTplType' => 'modChunk',
            'errorPage'                => false,
        ));
    }

    /**
     * process function.
     * 
     * @access public
     * @return void
     */
    public function process() {
        $this->verifyManifest();
        $this->getSubscriber();
        $this->validatePassword();
        
        $result = $this->runProcessor('ConfirmSubscription');
        
        return '';
    }

    /**
     * Verify that the username/password hashes were correctly sent (base64 encoded in URL) to prevent middle-man attacks.
     *
     * @access public
     * @return boolean
     */
    public function verifyManifest() {
        $verified = false;
        if (empty($_REQUEST['lp']) || empty($_REQUEST['lu'])) {
            $this->redirectAfterFailure();
        } else {
            // get username and password from query params
            $this->username = $this->goodnewssubscription->base64UrlDecode($_REQUEST['lu']);
            $this->password = $this->goodnewssubscription->base64UrlDecode($_REQUEST['lp']);
            $verified = true;
        }
        return $verified;
    }

    /**
     * Load complete subscriber object (modUser + modUserProfile + GoodNewsSubscriberMeta).
     *
     * @access public
     * @return void
     */
    public function getSubscriber() {
        $this->user = $this->modx->getObject('modUser', array('username' => $this->username));
        if (!is_object($this->user) || $this->user->get('active')) {
            $this->redirectAfterFailure();
        }
        $userID = $this->user->get('id');
        
        $this->profile = $this->modx->getObject('modUserProfile', array('internalKey' => $userID));
        if (!is_object($this->profile)) {
            $this->redirectAfterFailure();
        }
        
        $this->subscribermeta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $userID));
        if (!is_object($this->subscribermeta)) {
            $this->redirectAfterFailure();
        }
    }

    /**
     * Validate password to prevent middleman attacks.
     *
     * @access public
     * @return boolean
     */
    public function validatePassword() {
        $this->modx->getService('registry', 'registry.modRegistry');
        $this->modx->registry->addRegister('goodnewssubscription','registry.modFileRegister');
        $this->modx->registry->goodnewssubscription->connect();
        $this->modx->registry->goodnewssubscription->subscribe('/useractivation/'.$this->user->get('username'));
        $msgs = $this->modx->registry->goodnewssubscription->read();
        if (empty($msgs)) $this->modx->sendErrorPage();
        $found = false;
        foreach ($msgs as $msg) {
            if ($msg == $this->password) {
                $found = true;
            }
        }
        if (!$found) {
            $this->redirectAfterFailure();
        }
        return $found;
    }
}
return 'GoodNewsSubscriptionConfirmController';
