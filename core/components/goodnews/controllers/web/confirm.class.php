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
    /** @var string $username */
    public $username;
    
    /** @var string $password */
    public $password;
    
    /** @var modUser $user */
    public $user;

    /**
     * initialize function.
     * 
     * @access public
     * @return void
     */
    public function initialize() {
        $this->setDefaultProperties(array(
            'errorPage' => false,
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
        $this->getUser();
        $this->validatePassword();
        $this->onBeforeUserActivate();

        $this->user->set('active', 1);        
        $this->user->set('cachepwd', '');
        
        if (!$this->user->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not save activated user: '.$this->user->get('username'));
            $this->redirectAfterFailure();
        }
        
        /* invoke OnUserActivate event */
        $this->modx->invokeEvent('OnUserActivate', array(
            'user' => &$this->user,
        ));

        return '';
    }

    /**
     * Verify that the username/password hashes were correctly sent (base64 encoded in URL) to prevent middle-man attacks.
     *
     * @return boolean
     */
    public function verifyManifest() {
        $verified = false;
        if (empty($_REQUEST['lp']) || empty($_REQUEST['lu'])) {
            $this->redirectAfterFailure();
        } else {
            /* get user from query params */
            $this->username = base64_decode(urldecode(rawurldecode($_REQUEST['lu'])));
            $this->password = base64_decode(urldecode(rawurldecode($_REQUEST['lp'])));
            $verified = true;
        }
        return $verified;
    }

    /**
     * Validate we have correct user.
     *
     * @return modUser
     */
    public function getUser() {
        $this->user = $this->modx->getObject('modUser', array('username' => $this->username));
        if ($this->user == null || $this->user->get('active')) {
            $this->redirectAfterFailure();
        }
        return $this->user;
    }

    /**
     * Validate password to prevent middleman attacks.
     *
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

    /**
     * Invoke OnBeforeUserActivateEvent, if result returns anything, do not proceed
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
            $this->redirectAfterFailure();
        }
        return $success;
    }

    /**
     * Handle the redirection after a failed verification.
     *
     * @return void
     */
    public function redirectAfterFailure() {
        $errorPage = $this->getProperty('errorPage', false, 'isset');
        if (!empty($errorPage)) {
            $url = $this->modx->makeUrl($errorPage, '', '', 'full');
            $this->modx->sendRedirect($url);
        } else {
            // send to the default MODX error page
            $this->modx->sendErrorPage();
        }
    }
}
return 'GoodNewsSubscriptionConfirmController';
