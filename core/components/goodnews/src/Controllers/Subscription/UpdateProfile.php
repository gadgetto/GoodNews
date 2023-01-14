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

use MODX\Revolution\modUserProfile;
use Bitego\GoodNews\Controllers\Subscription\Base;

/**
 * Controller class which handles updating the subscription profile of a user.
 *
 * @package goodnews
 * @subpackage controllers
 */
class UpdateProfile extends Base
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
            'useExtended'           => false,
            'excludeExtended'       => '',
            'emailField'            => 'email',
            'preHooks'              => '',
            'postHooks'             => '',
            'sendUnauthorizedPage'  => false,
            'reloadOnSuccess'       => true,
            'submitVar'             => 'goodnews-updateprofile-btn',
            'successKey'            => 'updsuccess',
            'successMsg'            => $this->modx->lexicon('goodnews.profile_updated'),
            'validate'              => '',
            'grpFieldsetTpl'        => 'sample.GoodNewsGrpFieldsetChunk',
            'grpFieldTpl'           => 'sample.GoodNewsGrpFieldChunk',
            'grpNameTpl'            => 'sample.GoodNewsGrpNameChunk',
            'grpFieldHiddenTpl'     => 'sample.GoodNewsGrpFieldHiddenChunk',
            'catFieldTpl'           => 'sample.GoodNewsCatFieldChunk',
            'catFieldHiddenTpl'     => 'sample.GoodNewsCatFieldHiddenChunk',
            'groupsOnly'            => false,
            'includeGroups'         => '',
            'defaultGroups'         => '',
            'defaultCategories'     => '',
            'sort'                  => 'name',
            'dir'                   => 'ASC',
            'grpCatPlaceholder'     => 'grpcatfieldsets',
            'placeholderPrefix'     => '',
        ]);
    }

    /**
     * Handle the GoodNewsUpdateProfile snippet business logic.
     *
     * @access public
     * @return string
     */
    public function process()
    {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $reloadOnSuccess   = $this->getProperty('reloadOnSuccess', true);
        $successKey        = $this->getProperty('successKey', 'updsuccess');
        $groupsOnly        = $this->getProperty('groupsOnly', false);

        // Verifies a subscriber and loads modUser + modUserProfile objects
        if (!$this->authenticateSubscriber()) {
            // this is only executed if sendUnauthorizedPage property is set to false
            $this->modx->setPlaceholder($placeholderPrefix . 'authorization_failed', true);
            return '';
        } else {
            if (!empty($this->sid)) {
                $this->modx->setPlaceholder($placeholderPrefix . 'sid', $this->sid);
            }
            $this->modx->setPlaceholder($placeholderPrefix . 'authorization_success', true);
        }

        // Groups and/or categories are submitted via URL string (used for re-subscription part!)
        if (isset($_GET['gg']) && isset($_GET['gc'])) {
            $memberGroups = $this->subscription->decodeParams($_GET['gg']);
            $memberCategories = $this->subscription->decodeParams($_GET['gc']);

        // or use saved groups and/or categories from subscribers profile
        } else {
            $memberGroups = $this->collectGoodNewsGroupMembers($this->user->get('id'));
            $memberCategories = $this->collectGoodNewsCategoryMembers($this->user->get('id'));
        }
        $this->generateGrpCatFields($memberGroups, $memberCategories);

        $this->checkForSuccessMessage();

        // Set Dictionary instance and load POST array
        /** @var Dictionary $dictionary */
        if (!$this->hasPost()) {
            $this->setFieldPlaceholders();
            return '';
        }

        // Synchronize categories with groups (a category can't be selected without its parent group!)
        if (!$groupsOnly) {
            $this->selectParentGroupsByCategories();
        }

        if ($this->validate()) {
            if ($this->runPreHooks()) {
                // If user has no GoodNews profile (= GoodNewsSubscriberMeta) create one
                if (empty($this->sid)) {
                    $result = $this->runProcessor('CreateSubscriberMeta');
                    if ($result !== true) {
                        $this->modx->setPlaceholder($placeholderPrefix . 'error.message', $result);
                        $this->setFieldPlaceholders();
                        return '';
                    }
                }

                // Update the profile
                $result = $this->runProcessor('UpdateProfile');
                if ($result !== true) {
                    $this->modx->setPlaceholder($placeholderPrefix . 'error.message', $result);
                } elseif ($reloadOnSuccess) {
                    $urlParams = [];
                    $urlParams[$successKey] = 1;
                    if (!empty($this->sid)) {
                        $urlParams['sid'] = $this->sid;
                    }
                    $url = $this->modx->makeUrl($this->modx->resource->get('id'), '', $urlParams, 'full');
                    $this->modx->sendRedirect($url);
                } else {
                    $this->modx->setPlaceholder($placeholderPrefix . 'update_success', true);
                }
            }
        }

        $this->setFieldPlaceholders();
        return '';
    }

    /**
     * Read the subscribers data from db an set as placeholders.
     *
     * @access public
     * @return void
     */
    public function setFieldPlaceholders()
    {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $useExtended       = $this->getProperty('useExtended', false);

        $placeholders = $this->profile->toArray();
        // Add extended fields to placeholders
        if ($useExtended) {
            $extended = $this->profile->get('extended');
            if (!empty($extended) && is_array($extended)) {
                $placeholders = array_merge($extended, $placeholders);
            }
        }
        $this->modx->toPlaceholders($placeholders, $placeholderPrefix);
        foreach ($placeholders as $k => $v) {
            if (is_array($v)) {
                $this->modx->toPlaceholder($k, json_encode($v), $placeholderPrefix);
            }
        }
    }

    /**
     * Look for a success message by the previous reload.
     *
     * @access public
     * @return void
     */
    public function checkForSuccessMessage()
    {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $successKey        = $this->getProperty('successKey', 'updsuccess');

        if (!empty($_REQUEST[$successKey])) {
            $this->modx->setPlaceholder($placeholderPrefix . 'update_success', true);
        }
    }

    /**
     * Validate the form submission.
     *
     * @return boolean
     */
    public function validate()
    {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $validate          = $this->getProperty('validate', '');
        $validated = false;

        $this->validator = $this->subscription->loadValidator();
        $fields = $this->validator->validateFields($this->dictionary, $validate);

        foreach ($fields as $k => $v) {
            $fields[$k] = str_replace(['[',']'], ['&#91;','&#93;'], $v);
        }
        $this->dictionary->reset();
        $this->dictionary->fromArray($fields);

        $this->removeSubmitVar();
        $this->preventDuplicateEmails();

        if ($this->validator->hasErrors()) {
            $this->modx->toPlaceholders($this->validator->getErrors(), $placeholderPrefix . 'error');
            $this->modx->toPlaceholders($this->dictionary->toArray(), $placeholderPrefix);
        } else {
            $validated = true;
        }
        return $validated;
    }

    /**
     * Remove the submitVar from the field list.
     *
     * @access public
     * @return void
     */
    public function removeSubmitVar()
    {
        $submitVar = $this->getProperty('submitVar');
        if (!empty($submitVar)) {
            $this->dictionary->remove($submitVar);
        }
    }

    /**
     * Prevent duplicate emails.
     * MODX allow_multiple_emails setting is ignored -> we never let subscribe an email address more then once!
     *
     * @access public
     * @return void
     */
    public function preventDuplicateEmails()
    {
        $emailField = $this->getProperty('emailField', 'email');

        $email = $this->dictionary->get($emailField);
        if (!empty($email)) {
            $emailTaken = $this->modx->getObject(modUserProfile::class, [
                'email' => $email,
                'internalKey:!=' => $this->user->get('id'),
            ]);
            if ($emailTaken) {
                $this->validator->addError(
                    $emailField,
                    $this->modx->lexicon('goodnews.validator_email_taken', ['email' => $email])
                );
            }
        }
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
        $submitVar            = $this->getProperty('submitVar', 'goodnews-updateprofile-btn');
        $preHooks             = $this->getProperty('preHooks', '');
        $sendUnauthorizedPage = $this->getProperty('sendUnauthorizedPage', true);
        $reloadOnSuccess      = $this->getProperty('reloadOnSuccess', true);

        $validated = true;
        if (!empty($preHooks)) {
            $this->subscription->loadHooks('preHooks');
            $this->preHooks->loadMultiple($preHooks, $this->dictionary->toArray(), [
                'submitVar' => $submitVar,
                'sendUnauthorizedPage' => $sendUnauthorizedPage,
                'reloadOnSuccess' => $reloadOnSuccess,
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
}
