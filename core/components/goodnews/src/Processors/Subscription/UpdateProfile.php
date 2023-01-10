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
 * Processor class which handles subscription update.
 *
 * @package goodnews
 * @subpackage processors
 */

class UpdateProfile extends Base
{
    /** @var modUser $user */
    public $user = null;

    /** @var modUserProfile $profile */
    public $profile = null;

    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta = null;

    /**
     * @return boolean|string
     */
    public function process()
    {
        $this->user = $this->controller->user;
        $this->profile = $this->controller->profile;
        $this->subscribermeta = $this->controller->subscribermeta;

        $this->cleanseFields();

        //$dic = $this->controller->dictionary->to[];
        //$this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] dictionary: '.$this->modx->toJson($dic));

        $this->setExtended();

        // Save user changes
        $this->setUserFields();
        if (!$this->save()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save changed subscriber user data - ' .
                $this->user->get('id') .
                ' with username: ' .
                $this->user->get('username')
            );
            return $this->modx->lexicon('goodnews.profile_err_save');
        }

        // Update goodnews group member
        if (!$this->updateGroupMember()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save changed subscriber group member data - ' .
                $this->user->get('id') .
                ' with username: ' .
                $this->user->get('username')
            );
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        // Update goodnews category member
        if (!$this->updateCategoryMember()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save changed subscriber category member data - ' .
                $this->user->get('id') .
                ' with username: ' .
                $this->user->get('username')
            );
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        $this->runPostHooks();
        $this->handleSuccess();
        return true;
    }

    /**
     * Remove any fields used for anti-spam, submission from the dictionary.
     *
     * @return void
     */
    public function cleanseFields()
    {
        $submitVar = $this->controller->getProperty('submitVar', 'goodnews-updateprofile-btn');
        $this->dictionary->remove('nospam');
        $this->dictionary->remove('blank');
        if (!empty($submitVar)) {
            $this->dictionary->remove($submitVar);
        }
    }

    /**
     * Set the form fields to the user.
     *
     * @return void
     */
    public function setUserFields()
    {
        $fields = $this->dictionary->toArray();
        foreach ($fields as $key => $value) {
            $this->profile->set($key, $value);
        }
    }

    /**
     * Set and update the group member data.
     *
     * @return boolean
     */
    public function updateGroupMember()
    {
        $userid = $this->user->get('id');

        // First remove all previously stored group member entries
        $this->modx->removeCollection(GoodNewsGroupMember::class, ['member_id' => $userid]);

        $selectedGroups = $this->dictionary->get('gongroups');

        $success = true;
        foreach ($selectedGroups as $grpid) {
            $groupmember = $this->modx->newObject(GoodNewsGroupMember::class);
            $groupmember->set('goodnewsgroup_id', $grpid);
            $groupmember->set('member_id', $userid);
            if (!$groupmember->save()) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Set and update the category member data.
     *
     * @return boolean
     */
    public function updateCategoryMember()
    {
        $userid = $this->user->get('id');

        // First remove all previously stored category member entries
        $this->modx->removeCollection(GoodNewsCategoryMember::class, ['member_id' => $userid]);

        $selectedCategories = $this->dictionary->get('goncategories');

        $success = true;
        foreach ($selectedCategories as $catid) {
            $categorymember = $this->modx->newObject(GoodNewsCategoryMember::class);
            $categorymember->set('goodnewscategory_id', $catid);
            $categorymember->set('member_id', $userid);
            if (!$categorymember->save()) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * If desired, set any extended fields.
     *
     * @return void
     */
    public function setExtended()
    {
        $useExtended = $this->controller->getProperty('useExtended', false, 'isset');

        if ($useExtended) {
            // first cut out regular fields
            $excludeExtended = $this->controller->getProperty('excludeExtended', '');
            $excludeExtended = explode(',', $excludeExtended);

            $profileFields = $this->profile->toArray();
            $userFields = $this->user->toArray();

            $newExtended = [];
            $fields = $this->dictionary->toArray();

            foreach ($fields as $field => $value) {
                $isValidExtended = true;
                if (
                    isset($profileFields[$field]) ||
                    isset($userFields[$field]) ||
                    in_array($field, $excludeExtended) ||
                    $field == 'nospam' ||
                    $field == 'nospam:blank'
                ) {
                    $isValidExtended = false;
                }
                if ($isValidExtended) {
                    $newExtended[$field] = $value;
                }
            }
            // Now merge with existing extended data
            $extended = $this->profile->get('extended');
            $extended = is_array($extended) ? array_merge($extended, $newExtended) : $newExtended;
            $this->profile->set('extended', $extended);
        }
    }

    /**
     * Save the user data.
     *
     * @return boolean
     */
    public function save()
    {
        $this->user->addOne($this->profile, 'Profile');
        $saved = $this->user->save();
        return $saved;
    }

    /**
     * Run any post-update hooks.
     *
     * @return void
     */
    public function runPostHooks()
    {
        $postHooks = $this->controller->getProperty('postHooks', '');
        $this->controller->loadHooks('postHooks');

        $fields = $this->dictionary->toArray();
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
            $placeholderPrefix = $this->controller->getProperty('placeholderPrefix', '');
            $this->modx->toPlaceholders($errors, $placeholderPrefix . 'error');
            $errorMsg = $this->controller->postHooks->getErrorMessage();
            $this->modx->toPlaceholder('message', $errorMsg, $placeholderPrefix . 'error');
        }
    }

    /**
     * Set the success placeholder.
     *
     * @return void
     */
    public function handleSuccess()
    {
        $placeholderPrefix = $this->controller->getProperty('placeholderPrefix', '');
        $successMsg = $this->controller->getProperty('successMsg', $this->modx->lexicon('goodnews.profile_updated'));
        $this->modx->toPlaceholder('message', $successMsg, $placeholderPrefix . 'success');
    }
}
