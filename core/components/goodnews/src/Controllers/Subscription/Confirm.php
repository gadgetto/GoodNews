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

use MODX\Revolution\modUser;
use MODX\Revolution\modUserProfile;
use MODX\Revolution\Registry\modRegistry;
use MODX\Revolution\Registry\modFileRegister;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Controllers\Subscription\Base;

/**
 * Controller class which confirms a user's subscription after activation.
 *
 * @package goodnews
 * @subpackage controllers
 */
class Confirm extends Base
{
    /**
     * initialize function.
     *
     * @access public
     * @return void
     */
    public function initialize()
    {
        $this->setDefaultProperties([
            'sendSubscriptionEmail'    => true,
            'unsubscribeResourceId'    => '',
            'profileResourceId'        => '',
            'subscriptionEmailSubject' => $this->modx->lexicon('goodnews.subscription_email_subject'),
            'subscriptionEmailTpl'     => 'sample.GoodNewsSubscriptionEmailChunk',
            'subscriptionEmailTplAlt'  => '',
            'subscriptionEmailTplType' => 'modChunk',
            'errorPage'                => false,
        ]);
    }

    /**
     * process function.
     *
     * @access public
     * @return void
     */
    public function process()
    {
        $this->verifyManifest();
        $this->getSubscriber();
        $this->validatePassword();
        $result = $this->runProcessor('ConfirmSubscription');
        return '';
    }

    /**
     * Verify that the username/password hashes were correctly sent
     * (base64 encoded in URL) to prevent middle-man attacks.
     *
     * @access public
     * @return boolean
     */
    protected function verifyManifest()
    {
        $verified = false;
        if (empty($_REQUEST['lp']) || empty($_REQUEST['lu'])) {
            $this->redirectAfterFailure();
        } else {
            // Get username and password from query params
            $this->username = $this->subscription->base64UrlDecode($_REQUEST['lu']);
            $this->password = $this->subscription->base64UrlDecode($_REQUEST['lp']);
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
    protected function getSubscriber()
    {
        $this->user = $this->modx->getObject(modUser::class, ['username' => $this->username]);
        if (!is_object($this->user) || $this->user->get('active')) {
            $this->redirectAfterFailure();
        }
        $userID = $this->user->get('id');

        $this->profile = $this->modx->getObject(modUserProfile::class, ['internalKey' => $userID]);
        if (!is_object($this->profile)) {
            $this->redirectAfterFailure();
        }

        $this->subscribermeta = $this->modx->getObject(GoodNewsSubscriberMeta::class, ['subscriber_id' => $userID]);
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
    protected function validatePassword()
    {
        // Read new password from modRegistry to prevent middleman attacks.
        if (!$this->modx->services->has('registry')) {
            $modx = &$this->modx;
            $this->modx->services->add('registry', function ($c) use ($modx) {
                return new modRegistry($modx);
            });
        }
        $registry = $this->modx->services->get('registry');
        $registry->addRegister('goodnewssubscription', modFileRegister::class);
        $registry->goodnewssubscription->connect();
        $registry->goodnewssubscription->subscribe('/useractivation/' . $this->user->get('username'));
        $msgs = $registry->goodnewssubscription->read();
        if (empty($msgs)) {
            $this->modx->sendErrorPage();
        }
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
