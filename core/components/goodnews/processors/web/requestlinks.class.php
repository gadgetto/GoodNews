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
        $unsubscribeResourceId = $this->controller->getProperty('unsubscribeResourceId', '');
        $profileResourceId     = $this->controller->getProperty('profileResourceId', '');
        if (empty($unsubscribeResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] GoodNewsRequestLinks - snippet parameter unsubscribeResourceId not set.');
            return false;
        }
        if (empty($profileResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] GoodNewsRequestLinks - snippet parameter profileResourceId not set.');
            return false;
        }

        $this->cleanseFields();
        
        // If we can't find an appropriate subscriber in database,
        // we return true here but no requestlinks email is sent!
        // (This is for security an privacy reasons!)
        if (!$this->authenticateSubscriberByEmail()) {
            sleep(2); // this is for simulating delay which is normally caused by sending an email!
            return true;
        }
        
        // Send request links email
        $subscriberProperties = array_merge(
            $this->user->toArray(),
            $this->profile->toArray(),
            $this->subscribermeta->toArray()
        );
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
        $emailTpl = $this->controller->getProperty('requestLinksEmailTpl', 'sample.GoodNewsRequestLinksEmailTpl');
        $emailTplAlt = $this->controller->getProperty('requestLinksEmailTplAlt', '');
        $emailTplType = $this->controller->getProperty('requestLinksEmailTplType', 'modChunk');
        
        $params = array(
            'sid' => $this->sid,
        );
        // Generate secure links urls
        $updateProfileUrl = $this->modx->makeUrl($this->controller->getProperty('profileResourceId'), '', $params, 'full');
        $unsubscribeUrl   = $this->modx->makeUrl($this->controller->getProperty('unsubscribeResourceId'), '', $params, 'full');
        
        $emailProperties['updateProfileUrl'] = $updateProfileUrl;
        $emailProperties['unsubscribeUrl']   = $unsubscribeUrl;
        $emailProperties['tpl']              = $emailTpl;
        $emailProperties['tplAlt']           = $emailTplAlt;
        $emailProperties['tplType']          = $emailTplType;

        $subject = $this->controller->getProperty('requestLinksEmailSubject', $this->modx->lexicon('goodnews.requestlinks_email_subject'));
        
        return $this->goodnewssubscription->sendEmail($this->email, $subject, $emailProperties);
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
