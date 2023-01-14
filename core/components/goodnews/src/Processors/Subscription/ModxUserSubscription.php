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

namespace Bitego\GoodNews\Processors\Subscription;

use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Processors\Subscription\Base;

/**
 * Processor class which handles subscription forms when subscriber already
 * has a MODX user account but no GoodNews meta data:
 *  - no new MODX user is created
 *  - a Subscription profile is created (SubscriberMeta)
 *  - no Group and/or Category selections are created!
 *  - a subscription success mail is sent (including the secure links to edit/cancel subscription)
 *
 * @package goodnews
 * @subpackage processors
 */
class ModxUserSubscription extends Base
{
    /** @var modUser $user */
    public $user = null;

    /** @var modUserProfile $profile */
    public $profile = null;

    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta = null;

    /** @var array $persistParams */
    public $persistParams = [];

    /**
     * @access public
     * @return mixed
     */
    public function process()
    {
        $this->user  = $this->controller->user;
        $this->profile = $this->controller->profile;
        $this->subscribermeta = $this->modx->newObject(GoodNewsSubscriberMeta::class);

        $this->cleanseFields();

        // Save subscriber meta
        $this->setSubscriberMeta();
        if (!$this->subscribermeta->save()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save new subscriber meta data - ' .
                $this->user->get('id') .
                ' with username: ' .
                $this->user->get('username')
            );
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        $this->preparePersistentParameters();

        // Send a subscription success email including the secure links to edit subscription profile
        $sendSubscriptionEmail = $this->controller->getProperty('sendSubscriptionEmail', true, 'isset');
        if ($sendSubscriptionEmail) {
            $subscriberProperties = $this->getSubscriberProperties();
            $this->controller->sendReSubscriptionEmail($subscriberProperties);
        }

        $this->runPostHooks();
        $this->checkForRedirect();

        $successMsg = $this->controller->getProperty('successMsg', '');
        $placeholderPrefix = $this->controller->getProperty('placeholderPrefix', '');
        $this->modx->toPlaceholder($placeholderPrefix . 'success.message', $successMsg);

        return true;
    }

    /**
     * Remove any fields used for anti-spam, submission from the dictionary.
     *
     * @access public
     * @return void
     */
    public function cleanseFields()
    {
        $submitVar = $this->controller->getProperty('submitVar', 'goodnews-subscription-btn');
        $this->dictionary->remove('nospam');
        $this->dictionary->remove('blank');
        if (!empty($submitVar)) {
            $this->dictionary->remove($submitVar);
        }
    }

    /**
     * Set the subscriber meta data.
     *
     * @access public
     * @return void
     */
    public function setSubscriberMeta()
    {
        $userid = $this->user->get('id');
        $this->subscribermeta->set('subscriber_id', $userid);
        $this->subscribermeta->set('subscribedon', time());
        // create and set new sid
        $this->subscribermeta->set('sid', md5(time() . $userid));
        $this->subscribermeta->set('testdummy', 0);
        $this->subscribermeta->set('ip', $this->dictionary->get('ip'));
    }

    /**
     * Setup persistent parameters to go through the request cycle.
     *
     * @access public
     * @return array
     */
    public function preparePersistentParameters()
    {
        $this->persistParams = $this->controller->getProperty('persistParams', '');
        if (!empty($this->persistParams)) {
            $this->persistParams = $this->modx->fromJSON($this->persistParams);
        }
        if (empty($this->persistParams) || !is_array($this->persistParams)) {
            $this->persistParams = [];
        }
        return $this->persistParams;
    }

    /**
     * Get the subscriber properties and collect in array.
     *
     * @access private
     * @return mixed $properties The collection of properties || false
     */
    private function getSubscriberProperties()
    {
        $properties = array_merge(
            $this->user->toArray(),
            $this->profile->toArray(),
            $this->subscribermeta->toArray()
        );

        // Flatten extended fields:
        // extended.field1
        // extended.container1.field2
        // ...
        $extended = $this->profile->get('extended') ? $this->profile->get('extended') : [];
        if (!empty($extended)) {
            $extended = $this->flattenExtended($extended);
        }
        $properties = array_merge(
            $properties,
            $extended
        );

        $properties = $this->cleanupKeys($properties);
        return $properties;
    }

    /**
     * Run any post-subscription hooks.
     *
     * @access public
     * @return void
     */
    public function runPostHooks()
    {
        $postHooks = $this->controller->getProperty('postHooks', '');
        $this->postHooks = $this->subscription->loadHooks('postHooks');

        $fields = $this->dictionary->toArray();
        $fields['subscription.user'] = &$this->user;
        $fields['subscription.profile'] = &$this->profile;

        $this->postHooks->loadMultiple($postHooks, $fields);
        if ($this->postHooks->hasErrors()) {
            $errors = [];
            $hookErrors = $this->postHooks->getErrors();
            foreach ($hookErrors as $key => $error) {
                $errors[$key] = str_replace('[[+error]]', $error, $this->controller->getProperty('errTpl'));
            }
            $placeholderPrefix = $this->controller->getProperty('placeholderPrefix', '');
            $this->modx->toPlaceholders($errors, $placeholderPrefix . 'error');

            $errorMsg = $this->postHooks->getErrorMessage();
            $this->modx->toPlaceholder('message', $errorMsg, $placeholderPrefix . 'error');
        }
    }

    /**
     * Check for a redirect if the user subscription was successful. If one found, redirect.
     *
     * @access public
     * @return boolean
     */
    public function checkForRedirect()
    {
        // If provided a submittedResourceId, will redirect to that resource, with the GET param `email`
        $submittedResourceId = $this->controller->getProperty('submittedResourceId', '');
        if (!empty($submittedResourceId)) {
            $persistParams = array_merge($this->persistParams, [
                'email' => $this->profile->get('email'),
            ]);
            $url = $this->modx->makeUrl($submittedResourceId, '', $persistParams, 'full');
            $this->modx->sendRedirect($url);
            return true;
        }
        return false;
    }
}
