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
 * Class which handles process of users one-click unsubscription.
 *
 * @package goodnews
 * @subpackage controllers
 */

class GoodNewsSubscriptionUnSubscriptionController extends GoodNewsSubscriptionController {

    /**
     * Load default properties for this controller.
     *
     * @access public
     * @return void
     */
    public function initialize() {
        $this->modx->lexicon->load('goodnews:frontend');
        $this->setDefaultProperties(array(
            'errTpl'                => '<span class="error">[[+error]]</span>',
            'preHooks'              => '',
            'postHooks'             => '',
            'sendUnauthorizedPage'  => false,
            'submitVar'             => 'goodnews-unsubscribe-btn',
            'successKey'            => 'unsubsuccess',
            'removeUserData'        => false,
            'placeholderPrefix'     => '',
        ));
    }

    /**
     * Handle the GoodNewsSubscriptionUnSubscription snippet business logic.
     *
     * @access public
     * @return string
     */
    public function process() {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $successKey        = $this->getProperty('successKey', 'unsubsuccess');
        
        // If unsubscription was successfull authentication check isnt necessary any longer
        // so set placeholder and return
        if ($this->checkForSuccessKey()) {
            return '';
        }
        
        // Verifies a subscriber by its sid and loads user + profile object
        if (!$this->authenticateSubscriberBySid()) {
            // this is only executed if sendUnauthorizedPage property is set to false
            $this->modx->setPlaceholder($placeholderPrefix.'authorization_failed', true);
            return '';
        } else {
            $this->modx->setPlaceholder($placeholderPrefix.'email', $this->profile->get('email'));
            $this->modx->setPlaceholder($placeholderPrefix.'sid', $this->sid);
            $this->modx->setPlaceholder($placeholderPrefix.'authorization_success', true);
        }
        
        if ($this->hasPost()) {

            if ($this->runPreHooks()) {
            
                // Unsubscribe (delete user or remove GoodNews specific data and de-activate)
                $result = $this->runProcessor('UnSubscription');
                if ($result !== true) {
                    $this->modx->setPlaceholder($placeholderPrefix.'error.message', $result);
                } else {
                    $url = $this->modx->makeUrl($this->modx->resource->get('id'), '', array(
                        $successKey => 1,
                        'email' => $this->profile->get('email'),
                        'sid' => $this->sid,
                    ), 'full');
                    $this->modx->sendRedirect($url);
                }
            }

        }
        return '';
    }

    /**
     * Run any preHooks for this snippet, that allow it to stop the form as submitted.
     *
     * @access public
     * @return boolean
     */
    public function runPreHooks() {
        $placeholderPrefix    = $this->getProperty('placeholderPrefix', '');
        $preHooks             = $this->getProperty('preHooks', '');
        $sendUnauthorizedPage = $this->getProperty('sendUnauthorizedPage', true, 'isset');
        
        $validated = true;
        if (!empty($preHooks)) {
            $this->loadHooks('preHooks');
            $this->preHooks->loadMultiple($preHooks, $this->dictionary->toArray(), array(
                'sendUnauthorizedPage' => $sendUnauthorizedPage,
            ));
            $values = $this->preHooks->getValues();
            if (!empty($values)) {
                $this->dictionary->fromArray($values);
            }

            if ($this->preHooks->hasErrors()) {
                $errors = array();
                $es = $this->preHooks->getErrors();
                $errTpl = $this->getProperty('errTpl');
                foreach ($es as $key => $error) {
                    $errors[$key] = str_replace('[[+error]]', $error, $errTpl);
                }
                $this->modx->toPlaceholders($errors, $placeholderPrefix.'error');

                $errorMsg = $this->preHooks->getErrorMessage();
                $this->modx->toPlaceholder('message', $errorMsg, $placeholderPrefix.'error');
                $validated = false;
            }
        }
        return $validated;
    }
    
    /**
     * Look for a success key by the previous reload.
     *
     * @access public
     * @return boolean
     */
    public function checkForSuccessKey() {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $successKey        = $this->getProperty('successKey', 'unsubsuccess');
        
        $success = false;
        if (!empty($_REQUEST[$successKey])) {
            $this->modx->setPlaceholder($placeholderPrefix.'unsubscribe_success', true);
            $this->modx->setPlaceholder($placeholderPrefix.'email', $_REQUEST['email']);
            $success = true;
        }
        return $success;
    }
}
return 'GoodNewsSubscriptionUnSubscriptionController';
