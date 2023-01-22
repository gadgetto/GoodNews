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

use MODX\Revolution\modUser;
use MODX\Revolution\modUserProfile;
use MODX\Revolution\modUserGroup;
use MODX\Revolution\modUserGroupMember;
use MODX\Revolution\modUserGroupRole;
use MODX\Revolution\Registry\modRegistry;
use MODX\Revolution\Registry\modFileRegister;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Model\GoodNewsGroupMember;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Processors\Subscription\Base;

/**
 * Processor class which creates a subscriber:
 *  - a new MODX user is created
 *  - a Subscription profile is created (SubscriberMeta)
 *  - Group and/or Category selections are created!
 *  - an activation mail is sent (if double opt-in is enabled)
 *  - a subscription success mail is sent (if enabled)
 *
 * @package goodnews
 * @subpackage processors
 */
class Subscription extends Base
{
    /** @var modUser $user */
    public $user = null;

    /** @var modUserProfile $profile */
    public $profile = null;

    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta = null;

    /** @var array $userGroups */
    public $userGroups = [];

    /** @var array $persistParams */
    public $persistParams = [];

    /**
     * Process
     *
     * @access public
     * @return mixed
     */
    public function process()
    {
        $this->user = $this->modx->newObject(modUser::class);
        $this->profile = $this->modx->newObject(modUserProfile::class);
        $this->subscribermeta = $this->modx->newObject(GoodNewsSubscriberMeta::class);

        $this->cleanseFields();

        //$dic = $this->dictionary->toArray();
        //$this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] dictionary: '.$this->modx->toJson($dic));

        // Save user
        $this->setUserFields();
        if (!$this->user->save()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save new subscriber user data - ' .
                $this->user->get('id') .
                ' with username: ' .
                $this->user->get('username')
            );
            return $this->modx->lexicon('goodnews.user_err_save');
        }

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

        // Save goodnews group member
        if (!$this->saveGroupMember()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save new subscriber group member data - ' .
                $this->user->get('id') .
                ' with username: ' .
                $this->user->get('username')
            );
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        // Save goodnews category member
        if (!$this->saveCategoryMember()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save new subscriber category member data - ' .
                $this->user->get('id') .
                ' with username: ' .
                $this->user->get('username')
            );
            return $this->modx->lexicon('goodnews.user_err_save');
        }

        $this->preparePersistentParameters();

        // Send activation email (if property set)
        $email = $this->profile->get('email');
        $activation = $this->controller->getProperty('activation', true, 'isset');
        $activationResourceId = $this->controller->getProperty('activationResourceId', '', 'isset');

        if ($activation && !empty($email) && !empty($activationResourceId)) {
            $this->sendActivationEmail();

        // Activate Subscriber without double opt-in
        } else {
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

            // Invoke OnUserActivate event
            $this->modx->invokeEvent('OnUserActivate', array(
                'user' => &$this->user,
            ));

            // Send a subscription success email including the secure links to edit subscription profile
            $sendSubscriptionEmail = $this->controller->getProperty('sendSubscriptionEmail', true, 'isset');
            if ($sendSubscriptionEmail) {
                $subscriberProperties = $this->getSubscriberProperties();
                $this->controller->sendSubscriptionEmail($subscriberProperties);
            }
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
     * Set the user data and create the user/profile objects.
     *
     * @access public
     * @return void
     */
    public function setUserFields()
    {
        $emailField = $this->controller->getProperty('emailField', 'email');
        $usernameField = $this->controller->getProperty('usernameField', 'username');
        $passwordField = $this->controller->getProperty('passwordField', 'password');
        $usergroupsField = $this->controller->getProperty('usergroupsField', 'usergroups');
        $useExtended = $this->controller->getProperty('useExtended', false, 'isset');

        $fields = $this->dictionary->toArray();

        // Allow overriding of class key
        if (empty($fields['class_key'])) {
            $fields['class_key'] = 'MODX\\Revolution\\modUser';
        }

        // Set user data
        $this->user->fromArray($fields);
        $this->user->set('username', $fields[$usernameField]);
        $this->user->set('password', $fields[$passwordField]);
        $this->user->set('active', 0);

        // Set profile data
        $this->profile->fromArray($fields);
        $this->profile->set('email', $this->dictionary->get($emailField));
        if ($useExtended) {
            $this->setExtended();
        }
        $this->user->addOne($this->profile, 'Profile');

        // Add MODX user groups, if set
        $userGroups = !empty($usergroupsField) && array_key_exists($usergroupsField, $fields)
            ? $fields[$usergroupsField]
            : [];

        $this->setUserGroups($userGroups);
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
        $this->subscribermeta->set('sid', md5(uniqid(rand() . $userid, true)));
        $this->subscribermeta->set('testdummy', 0);
        $this->subscribermeta->set('ip', $this->dictionary->get('ip'));
    }

    /**
     * Set and save the group member data.
     *
     * @access public
     * @return void
     */
    public function saveGroupMember()
    {
        $userid = $this->user->get('id');
        $gongroups = $this->dictionary->get('gongroups');
        $selectedGroups = !empty($gongroups) ? $gongroups : [];

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
     * Set and save the category member data.
     *
     * @access public
     * @return void
     */
    public function saveCategoryMember()
    {
        $userid = $this->user->get('id');
        $goncategories = $this->dictionary->get('goncategories');
        $selectedCategories = !empty($goncategories) ? $goncategories : [];

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
     * If activated, use extra field in form to write extra values to profile extended field.
     *
     * @access public
     * @return void
     */
    public function setExtended()
    {
        $excludeExtended = $this->controller->getProperty('excludeExtended', '');
        $usergroupsField = $this->controller->getProperty('usergroupsField', 'usergroups');

        $excludeExtended = explode(',', $excludeExtended);

        $alwaysExclude = [
            'gongroups',
            'goncategories',
            'password_confirm',
            'passwordconfirm'
        ];

        // Gets a list of fields for modUser and modUserProfile by class name
        $userFields    = $this->modx->getFields(modUser::class);
        $profileFields = $this->modx->getFields(modUserProfile::class);

        $extended = [];
        $fields = $this->dictionary->toArray();

        foreach ($fields as $field => $value) {
            if (
                !isset($profileFields[$field])
                && !isset($userFields[$field])
                && $field != $usergroupsField
                && !in_array($field, $alwaysExclude)
                && !in_array($field, $excludeExtended)
            ) {
                $extended[$field] = $value;
            }
        }
        // set extended field
        $this->profile->set('extended', $extended);
    }

    /**
     * If MODX user groups were passed, set them here.
     *
     * @access public
     * @param mixed $userGroups Array or comma separated string of MODX user groups
     * @return array
     */
    public function setUserGroups($userGroups)
    {
        $added = [];
        // If $userGroups set in form, override here; otherwise use snippet property
        $this->userGroups = !empty($userGroups)
            ? $userGroups
            : $this->controller->getProperty('usergroups', '');

        if (!empty($this->userGroups)) {
            $this->userGroups = is_array($this->userGroups)
                ? $this->userGroups
                : explode(',', $this->userGroups);

            foreach ($this->userGroups as $userGroupMeta) {
                $userGroupMeta = explode(':', $userGroupMeta);
                if (empty($userGroupMeta[0])) {
                    continue;
                }

                // Get usergroup
                $pk = [];
                $pk[intval($userGroupMeta[0]) > 0 ? 'id' : 'name'] = trim($userGroupMeta[0]);
                $userGroup = $this->modx->getObject(modUserGroup::class, $pk);
                if (!$userGroup) {
                    continue;
                }

                // Get role
                $rolePk = !empty($userGroupMeta[1]) ? $userGroupMeta[1] : 'Member';
                $role = $this->modx->getObject(modUserGroupRole::class, ['name' => $rolePk]);

                // Create membership
                $member = $this->modx->newObject(modUserGroupMember::class);
                $member->set('member', 0);
                $member->set('user_group', $userGroup->get('id'));
                if (!empty($role)) {
                    $member->set('role', $role->get('id'));
                } else {
                    $member->set('role', 1);
                }
                $this->user->addMany($member, 'UserGroupMembers');
                $added[] = $userGroup->get('name');
            }
        }
        return $added;
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
     * Send an activation email to the user with an encrypted username and password hash, to allow for secure
     * activation processes that are not vulnerable to middle-man attacks.
     *
     * @access public
     * @return boolean
     */
    public function sendActivationEmail()
    {
        $emailProperties = $this->gatherActivationEmailProperties();

        // Send either to user's email or a specified activation email address
        $activationEmail = $this->controller->getProperty('activationEmail', $this->profile->get('email'));
        $defaultSubject = $this->modx->lexicon('goodnews.activation_email_subject');
        $subject = $this->controller->getProperty('activationEmailSubject', $defaultSubject);

        return $this->subscription->sendEmail($activationEmail, $subject, $emailProperties);
    }

    /**
     * Get the properties for the activation email
     *
     * @access public
     * @return array
     */
    public function gatherActivationEmailProperties()
    {
        // Generate a cache password and encode it and the username into the url
        $pword = $this->setCachePassword();
        $confirmParams['lp'] = $this->subscription->base64UrlEncode($pword);
        $confirmParams['lu'] = $this->subscription->base64UrlEncode($this->user->get('username'));
        $confirmParams = array_merge($this->persistParams, $confirmParams);

        // If using redirectBack param, set here to allow dynamic redirection handling from other forms
        $redirectBack = $this->modx->getOption(
            'redirectBack',
            $_REQUEST,
            $this->controller->getProperty('redirectBack', '')
        );
        if (!empty($redirectBack)) {
            $confirmParams['redirectBack'] = $redirectBack;
        }
        $redirectBackParams = $this->modx->getOption(
            'redirectBackParams',
            $_REQUEST,
            $this->controller->getProperty('redirectBackParams', '')
        );
        if (!empty($redirectBackParams)) {
            $confirmParams['redirectBackParams'] = $redirectBackParams;
        }

        // Generate confirmation url
        $confirmUrl = $this->modx->makeUrl(
            $this->controller->getProperty('activationResourceId', 1),
            '',
            $confirmParams,
            'full'
        );

        // Set confirmation email properties
        $emailTpl = $this->controller->getProperty('activationEmailTpl', 'sample.GoodNewsActivationEmailChunk');
        $emailTplAlt = $this->controller->getProperty('activationEmailTplAlt', '');
        $emailTplType = $this->controller->getProperty('activationEmailTplType', 'modChunk');

        $emailProperties = $this->getSubscriberProperties();
        $emailProperties['confirmUrl'] = $confirmUrl;
        $emailProperties['tpl'] = $emailTpl;
        $emailProperties['tplAlt'] = $emailTplAlt;
        $emailProperties['tplType'] = $emailTplType;

        return $emailProperties;
    }

    /**
     * Generates a cache password for subscriber activation and writes it to modRegistry.
     *
     * @access public
     * @param mixed $password
     * @return mixed $cachepwd || false
     */
    public function setCachePassword()
    {
        // Generate a new password
        $cachepwd = $this->user->generatePassword();

        // Set new password to modRegistry to prevent middleman attacks.
        // (Will be read from the registry on the confirmation page)
        if (!$this->modx->services->has('registry')) {
            $modx = &$this->modx;
            $this->modx->services->add('registry', function ($c) use ($modx) {
                return new modRegistry($modx);
            });
        }
        $registry = $this->modx->services->get('registry');
        $registry->addRegister('goodnewssubscription', modFileRegister::class);
        $registry->goodnewssubscription->connect();
        $registry->goodnewssubscription->subscribe('/useractivation/');
        $registry->goodnewssubscription->send('/useractivation/', [md5($this->user->get('username')) => $cachepwd], [
            'ttl' => ($this->controller->getProperty('activationttl', 180) * 60),
        ]);
        // Set cachepwd here to prevent re-registration of inactive users
        $this->user->set('cachepwd', $cachepwd);
        $success = $this->user->save();
        if (!$success) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not set cachepwd for activation for user: ' . $this->user->get('username')
            );
            $cachepwd = false;
        }
        return $cachepwd;
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
        $fields['subscription.usergroups'] = $this->userGroups;

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

    /**
     * Invoke OnBeforeUserActivateEvent, if result returns anything, do not proceed
     *
     * @access public
     * @return boolean
     */
    public function onBeforeUserActivate()
    {
        $success = true;
        $result = $this->modx->invokeEvent('OnBeforeUserActivate', [
            'user' => &$this->user,
        ]);
        $preventActivation = $this->subscription->getEventResult($result);
        if (!empty($preventActivation)) {
            $success = false;
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] OnBeforeUserActivate event prevented activation for "' .
                $this->user->get('username') .
                '" by returning false: ' .
                $preventActivation
            );
            $this->controller->redirectAfterFailure();
        }
        return $success;
    }
}
