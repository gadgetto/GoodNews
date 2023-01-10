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

use MODX\Revolution\modUserProfile;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Processors\Subscription\Base;

/**
 * Processor class which handles request secure links forms.
 *
 * @package goodnews
 * @subpackage processors
 */
class RequestLinks extends Base
{
    /** @var modUser $user */
    public $user = null;

    /** @var modUserProfile $profile */
    public $profile = null;

    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta = null;

    /** @var GoodNewsSubscriberMeta.sid $sid */
    public $sid = '';

    /** @var string $email */
    public $email = '';

    /**
     * @return mixed
     */
    public function process()
    {
        $this->cleanseFields();

        // If we can't find an appropriate subscriber in database,
        // we return true here but no requestlinks email is sent!
        // (This is for security an privacy reasons!)
        if (!$this->authenticateSubscriberByEmail()) {
            sleep(2); // this is for simulating delay which is normally caused by sending an email!
            return true;
        }

        // Send request links email
        $subscriberProperties = $this->getSubscriberProperties();
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
    public function cleanseFields()
    {
        $submitVar = $this->controller->getProperty('submitVar', 'goodnews-requestlinks-btn');
        $this->dictionary->remove('nospam');
        $this->dictionary->remove('blank');
        if (!empty($submitVar)) {
            $this->dictionary->remove($submitVar);
        }
    }

    /**
     * Authenticate the subscriber by email address submitted via form
     * and load modUser, modUserProfile and GoodNewsSusbcriberMeta objects.
     * (Verification means we have extracted a valid sid from SubscriberMeta - we dont need real MODX login!)
     *
     * @return boolean
     */
    public function authenticateSubscriberByEmail()
    {
        $emailField = $this->controller->getProperty('emailField', 'email');
        $this->email = $this->dictionary->get($emailField);

        $verified = false;

        // get profile
        $this->profile = $this->modx->getObject(modUserProfile::class, ['email' => $this->email]);

        if (is_object($this->profile)) {
            // get user by profile
            $this->user = $this->profile->getOne('User');
            $active = $this->user->get('active');

            // Subscriber must be active!
            if ($active) {
                // Get subscriber meta by user
                $userid = $this->user->get('id');
                $this->subscribermeta = $this->modx->getObject(
                    GoodNewsSubscriberMeta::class,
                    [
                        'subscriber_id' => $userid
                    ]
                );
                if (is_object($this->subscribermeta)) {
                    $this->sid = $this->subscribermeta->get('sid');
                    $verified = true;
                }
            }
        }

        if (!$verified) {
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
    public function sendRequestLinksEmail($emailProperties)
    {
        // Additional required properties
        $emailTpl = $this->controller->getProperty('requestLinksEmailTpl', 'sample.GoodNewsRequestLinksEmailChunk');
        $emailTplAlt = $this->controller->getProperty('requestLinksEmailTplAlt', '');
        $emailTplType = $this->controller->getProperty('requestLinksEmailTplType', 'modChunk');

        $params = [
            'sid' => $this->sid,
        ];

        $profileResourceId = $this->controller->getProperty('profileResourceId', '');
        if (empty($profileResourceId)) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[GoodNews] GoodNewsRequestLinks - snippet parameter profileResourceId not set.'
            );
        } else {
            $emailProperties['updateProfileUrl'] = $this->modx->makeUrl($profileResourceId, '', $params, 'full');
        }

        $unsubscribeResourceId = $this->controller->getProperty('unsubscribeResourceId', '');
        if (empty($unsubscribeResourceId)) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[GoodNews] GoodNewsRequestLinks - snippet parameter unsubscribeResourceId not set.'
            );
        } else {
            $emailProperties['unsubscribeUrl'] = $this->modx->makeUrl($unsubscribeResourceId, '', $params, 'full');
        }

        $emailProperties['tpl']     = $emailTpl;
        $emailProperties['tplAlt']  = $emailTplAlt;
        $emailProperties['tplType'] = $emailTplType;

        $defaultSubject = $this->modx->lexicon('goodnews.requestlinks_email_subject');
        $subject = $this->controller->getProperty('requestLinksEmailSubject', $defaultSubject);

        return $this->subscription->sendEmail($this->email, $subject, $emailProperties);
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
     * Check for a redirect.
     *
     * @return boolean
     */
    public function checkForRedirect()
    {
        // If provided a redirect id, will redirect to that resource, with the GET param `email` for you to use
        $submittedResourceId = $this->controller->getProperty('submittedResourceId', '');
        if (!empty($submittedResourceId)) {
            $params = [
                'email' => $this->email,
            ];
            $url = $this->modx->makeUrl($submittedResourceId, '', $params, 'full');
            $this->modx->sendRedirect($url);
            return true;
        }
        return false;
    }
}
