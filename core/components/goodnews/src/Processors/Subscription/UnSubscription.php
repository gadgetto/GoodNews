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

use Bitego\GoodNews\Model\GoodNewsGroupMember;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Processors\Subscription\Base;

/**
 * Processor class which handles one-click un-subscription.
 * - completely remove user/profile/meta or
 * - remove only meta
 *
 * @package goodnews
 * @subpackage processors
 */
class UnSubscription extends Base
{
    /** @var modUser $user */
    public $user = null;

    /** @var modUserProfile $profile */
    public $profile = null;

    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta = null;

    /** @var integer $userid */
    public $userid = 0;

    /**
     * @return boolean|string
     */
    public function process()
    {
        $this->user = $this->controller->user;
        $this->profile = $this->controller->profile;
        $this->subscribermeta = $this->controller->subscribermeta;

        $this->userid = $this->user->get('id');

        $removeUserData = $this->controller->getProperty('removeUserData', false, 'isset');
        if ($removeUserData) {
            // Completely remove user and related GoodNews data
            if (!$this->removeUser()) {
                return $this->modx->lexicon('goodnews.profile_err_unsubscribe');
            }
        } else {
            $this->removeSubscriptions();
        }

        $this->runPostHooks();
        return true;
    }

    /**
     * Check if user is member of MODX user groups or sudo.
     *
     * @access public
     * @return boolean
     */
    public function isModxGroupMember()
    {
        $ismember = false;
        $groups = $this->user->getUserGroups();
        if ($groups) {
            $ismember = true;
        }
        if ($this->user->get('sudo') == true) {
            $ismember = true;
        }
        return $ismember;
    }

    /**
     * Remove a user and all it's related GoodNews data.
     *
     * @access public
     * @return boolean
     */
    public function removeUser()
    {
        $removed = true;

        $this->removeGoodNewsData();

        // Do not remove or deactivate MODx users with MODX groups assigned or sudo!
        // Those user will only have related GoodNews data removed.
        if (!$this->isModxGroupMember()) {
            // Delete user object
            if (!$this->user->remove()) {
                $this->modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[GoodNews] Could not delete user object of subscriber - ' .
                    $this->userid . ' with username: ' . $this->user->get('username')
                );
                $removed = false;
            }
        }
        return $removed;
    }

    /**
     * Remove all GoodNews data (meta + subscriptions).
     *
     * @access public
     * @return void
     */
    public function removeGoodNewsData()
    {
        $this->removeSubscriptions();
        if (is_object($this->subscribermeta)) {
            $this->subscribermeta->remove();
        }
    }

    /**
     * Remove all GoodNews subscriptions.
     *
     * @access public
     * @return void
     */
    public function removeSubscriptions()
    {
        $result = $this->modx->removeCollection(GoodNewsCategoryMember::class, ['member_id' => $this->userid]);
        $result = $this->modx->removeCollection(GoodNewsGroupMember::class, ['member_id' => $this->userid]);

        // Change sid to invalidate all secure links
        $this->subscribermeta->set('sid', md5(time() . $this->userid));
        $this->subscribermeta->save();
    }

    /**
     * Run any post unsubscription hooks.
     *
     * @return void
     */
    public function runPostHooks()
    {
        $postHooks = $this->controller->getProperty('postHooks', '');
        $this->controller->loadHooks('postHooks');

        $fields = [];
        $fields['subscription.user'] = &$this->user;
        $fields['subscription.profile'] = &$this->profile;

        $this->controller->postHooks->loadMultiple($postHooks, $fields);

        // Process hooks
        if ($this->controller->postHooks->hasErrors()) {
            $errors = [];
            $errTpl = $this->controller->getProperty('errTpl');
            $errs = $this->controller->postHooks->getErrors();
            foreach ($errs as $key => $error) {
                $errors[$key] = str_replace('[[+error]]', $error, $errTpl);
            }
            $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
            $this->modx->toPlaceholders($errors, $placeholderPrefix . 'error');
            $errorMsg = $this->controller->postHooks->getErrorMessage();
            $this->modx->toPlaceholder('message', $errorMsg, $placeholderPrefix . 'error');
        }
    }
}
