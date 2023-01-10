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

use Bitego\GoodNews\Processors\Subscription\Base;

/**
 * Processor class handles confirmation of subscription.
 *
 * @package goodnews
 * @subpackage processors
 */
class ConfirmSubscription extends Base
{
    /** @var modUser $user */
    public $user = null;

    /** @var modUserProfile $profile */
    public $profile = null;

    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta =  null;

    /**
     * @return boolean|string
     */
    public function process()
    {
        $this->user = $this->controller->user;
        $this->profile = $this->controller->profile;
        $this->subscribermeta = $this->controller->subscribermeta;

        $this->onBeforeUserActivate();

        $this->user->set('active', 1);
        $this->user->_fields['cachepwd'] = '';
        $this->user->setDirty('cachepwd');

        if (!$this->user->save()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save activated user: ' . $this->user->get('username')
            );
            $this->controller->redirectAfterFailure();
        }

        // Get the subscribers activation IP address
        $ip = $this->controller->getSubscriberIP();

        // Set some GDPR relevant fields (those fields will only be set when 2-opt-in is activated in snippet!)
        $this->subscribermeta->set('activatedon', time());
        $this->subscribermeta->set('ip_activated', $ip);

        if (!$this->subscribermeta->save()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save activated user: ' . $this->user->get('username')
            );
            $this->controller->redirectAfterFailure();
        }

        // Invoke OnUserActivate event
        $this->modx->invokeEvent('OnUserActivate', [
            'user' => &$this->user,
        ]);

        // Send a subscription success email including the secure links to edit subscription profile
        $sendSubscriptionEmail = $this->controller->getProperty('sendSubscriptionEmail', true, 'isset');
        if ($sendSubscriptionEmail) {
            $subscriberProperties = $this->getSubscriberProperties();
            $this->controller->sendSubscriptionEmail($subscriberProperties);
        }

        return true;
    }

    /**
     * Get the subscriber properties and collect in array.
     *
     * @access private
     * @return mixed $properties The collection of properties|false
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
     * Invoke OnBeforeUserActivateEvent, if result returns anything, do not proceed
     *
     * @access public
     * @return boolean
     */
    public function onBeforeUserActivate()
    {
        $success = true;
        $result = $this->modx->invokeEvent('OnBeforeUserActivate', array(
            'user' => &$this->user,
        ));
        $preventActivation = $this->subscription->getEventResult($result);
        if (!empty($preventActivation)) {
            $success = false;
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] OnBeforeUserActivate event prevented activation for "' .
                $this->user->get('username') . '" by returning false: ' . $preventActivation
            );
            $this->controller->redirectAfterFailure();
        }
        return $success;
    }
}
