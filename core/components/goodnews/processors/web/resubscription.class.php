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
 * Processor class which handles subscription forms when Subscriber already has GoodNews meta data available
 *  - no new MODX user is created
 *  - a re-subscription mail is sent (including the secure links to edit/cancel subscription)
 *
 * @package goodnews
 * @subpackage processors
 */

class GoodNewsSubscriptionReSubscriptionProcessor extends GoodNewsSubscriptionProcessor {
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
        $this->subscribermeta = $this->controller->subscribermeta;

        $this->cleanseFields();

        $this->preparePersistentParameters();

        // Send a subscription renewal email including the secure links to edit subscription profile
        $subscriberProperties = array_merge(
            $this->user->toArray(),
            $this->profile->toArray(),
            $this->subscribermeta->toArray()
        );
        $this->sendReSubscriptionEmail($subscriberProperties);

        $this->checkForRedirect();

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
     * Send an email to the user containing secure links to update or cancel subscriptions.
     *
     * @access public
     * @param array $emailProperties
     * @return boolean
     */
    public function sendReSubscriptionEmail($emailProperties) {
        // Additional required properties
        $emailProperties['tpl']     = $this->controller->getProperty('reSubscriptionEmailTpl', 'sample.GoodNewsReSubscriptionEmailTpl');
        $emailProperties['tplAlt']  = $this->controller->getProperty('reSubscriptionEmailTplAlt', '');
        $emailProperties['tplType'] = $this->controller->getProperty('reSubscriptionEmailTplType', 'modChunk');

        // Generate secure links urls
        $params = array(
            'sid' => $this->subscribermeta->get('sid'),
            'gg'  => $this->goodnewssubscription->encodeParams($this->dictionary->get('gongroups')),
            'gc'  => $this->goodnewssubscription->encodeParams($this->dictionary->get('goncategories')),
        );
        $updateProfileUrl = $this->modx->makeUrl($this->controller->getProperty('profileResourceId'), '', $params, 'full');
        $unsubscribeUrl   = $this->modx->makeUrl($this->controller->getProperty('unsubscribeResourceId'), '', $params, 'full');

        $emailProperties['updateProfileUrl'] = $updateProfileUrl;
        $emailProperties['unsubscribeUrl']   = $unsubscribeUrl;

        $email = $this->profile->get('email');
        $subject = $this->controller->getProperty('reSubscriptionEmailSubject', $this->modx->lexicon('goodnews.resubscription_email_subject'));
        return $this->goodnewssubscription->sendEmail($email, $subject, $emailProperties);
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
return 'GoodNewsSubscriptionReSubscriptionProcessor';
