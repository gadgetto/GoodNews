<?php

/**
 * This file is part of the GoodNews package.
 *
 * @copyright bitego (Martin Gartner)
 * @license GNU General Public License v2.0 (and later)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitego\GoodNews\Controllers\Subscription;

use Bitego\GoodNews\Controllers\Subscription\Base;

/**
 * Controller class which handles process of users one-click unsubscription.
 *
 * @package goodnews
 * @subpackage controllers
 */
class UnSubscription extends Base
{
    /**
     * Load default properties for this controller.
     *
     * @access public
     * @return void
     */
    public function initialize()
    {
        $this->setDefaultProperties([
            'errTpl'                => '<span class="error">[[+error]]</span>',
            'preHooks'              => '',
            'postHooks'             => '',
            'sendUnauthorizedPage'  => false,
            'submitVar'             => 'goodnews-unsubscribe-btn',
            'successKey'            => 'unsubsuccess',
            'removeUserData'        => false,
            'placeholderPrefix'     => '',
        ]);
    }

    /**
     * Handle the UnSubscription snippet business logic.
     *
     * @access public
     * @return string
     */
    public function process()
    {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $successKey        = $this->getProperty('successKey', 'unsubsuccess');

        // If unsubscription was successfull authentication check isn't necessary any longer
        // so set placeholder and return
        if ($this->checkForSuccessKey()) {
            return '';
        }

        // Verifies a subscriber by its sid and loads modUser + modUserProfile objects
        if (!$this->authenticateSubscriber()) {
            // this is only executed if sendUnauthorizedPage property is set to false
            $this->modx->setPlaceholder($placeholderPrefix . 'authorization_failed', true);
            return '';
        } else {
            $this->modx->setPlaceholder($placeholderPrefix . 'email', $this->profile->get('email'));
            $this->modx->setPlaceholder($placeholderPrefix . 'sid', $this->sid);
            $this->modx->setPlaceholder($placeholderPrefix . 'authorization_success', true);
        }

        // Set Dictionary instance and load POST array
        /** @var Dictionary $dictionary */
        if (!$this->hasPost()) {
            return '';
        }

        if ($this->runPreHooks()) {
            // Unsubscribe (delete user or remove GoodNews specific data and de-activate)
            $result = $this->runProcessor('UnSubscription');
            if ($result !== true) {
                $this->modx->setPlaceholder($placeholderPrefix . 'error.message', $result);
            } else {
                $url = $this->modx->makeUrl($this->modx->resource->get('id'), '', [
                    $successKey => 1,
                    'email' => $this->profile->get('email'),
                    'sid' => $this->sid,
                ], 'full');
                $this->modx->sendRedirect($url);
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
    public function runPreHooks()
    {
        $placeholderPrefix    = $this->getProperty('placeholderPrefix', '');
        $preHooks             = $this->getProperty('preHooks', '');
        $sendUnauthorizedPage = $this->getProperty('sendUnauthorizedPage', true);

        $validated = true;
        if (!empty($preHooks)) {
            $this->subscription->loadHooks('preHooks');
            $this->preHooks->loadMultiple($preHooks, $this->dictionary->toArray(), [
                'sendUnauthorizedPage' => $sendUnauthorizedPage,
            ]);
            $values = $this->preHooks->getValues();
            if (!empty($values)) {
                $this->dictionary->fromArray($values);
            }

            if ($this->preHooks->hasErrors()) {
                $errors = [];
                $es = $this->preHooks->getErrors();
                $errTpl = $this->getProperty('errTpl');
                foreach ($es as $key => $error) {
                    $errors[$key] = str_replace('[[+error]]', $error, $errTpl);
                }
                $this->modx->toPlaceholders($errors, $placeholderPrefix . 'error');
                $errorMsg = $this->preHooks->getErrorMessage();
                $this->modx->toPlaceholder('message', $errorMsg, $placeholderPrefix . 'error');
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
    public function checkForSuccessKey()
    {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $successKey        = $this->getProperty('successKey', 'unsubsuccess');
        $success = false;
        if (!empty($_REQUEST[$successKey])) {
            $this->modx->setPlaceholder($placeholderPrefix . 'unsubscribe_success', true);
            $this->modx->setPlaceholder($placeholderPrefix . 'email', $_REQUEST['email']);
            $success = true;
        }
        return $success;
    }
}
