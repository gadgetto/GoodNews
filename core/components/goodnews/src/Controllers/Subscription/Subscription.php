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
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Controllers\Subscription\Base;

/**
 * Controller class which handles subscription process of users.
 *
 * @package goodnews
 * @subpackage controllers
 */
class Subscription extends Base
{
    public const SEARCH_BY_USERNAME = 'username';
    public const SEARCH_BY_EMAIL    = 'email';

    /** @var boolean $success */
    public $success = false;

    /**
     * Load default properties for this controller.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setDefaultProperties([
            'activation'                 => true,
            'activationttl'              => 180,
            'activationEmail'            => '',
            'activationEmailSubject'     => $this->modx->lexicon('goodnews.activation_email_subject'),
            'activationEmailTpl'         => 'sample.GoodNewsActivationEmailChunk',
            'activationEmailTplAlt'      => '',
            'activationEmailTplType'     => 'modChunk',
            'activationResourceId'       => '',
            'submittedResourceId'        => '',
            'sendSubscriptionEmail'      => true,
            'unsubscribeResourceId'      => '',
            'profileResourceId'          => '',
            'subscriptionEmailSubject'   => $this->modx->lexicon('goodnews.subscription_email_subject'),
            'subscriptionEmailTpl'       => 'sample.GoodNewsSubscriptionEmailChunk',
            'subscriptionEmailTplAlt'    => '',
            'subscriptionEmailTplType'   => 'modChunk',
            'reSubscriptionEmailSubject' => $this->modx->lexicon('goodnews.resubscription_email_subject'),
            'reSubscriptionEmailTpl'     => 'sample.GoodNewsReSubscriptionEmailChunk',
            'reSubscriptionEmailTplAlt'  => '',
            'reSubscriptionEmailTplType' => 'modChunk',
            'errTpl'                     => '<span class="error">[[+error]]</span>',
            'useExtended'                => false,
            'excludeExtended'            => '',
            'emailField'                 => 'email',
            'usernameField'              => 'username',
            'passwordField'              => 'password',
            'persistParams'              => '',
            'preHooks'                   => '',
            'postHooks'                  => '',
            'redirectBack'               => '',
            'redirectBackParams'         => '',
            'submitVar'                  => 'goodnews-subscription-btn',
            'successMsg'                 => '',
            'usergroups'                 => '',
            'usergroupsField'            => 'usergroups',
            'validate'                   => '',
            'grpFieldsetTpl'             => 'sample.GoodNewsGrpFieldsetChunk',
            'grpFieldTpl'                => 'sample.GoodNewsGrpFieldChunk',
            'grpNameTpl'                 => 'sample.GoodNewsGrpNameChunk',
            'grpFieldHiddenTpl'          => 'sample.GoodNewsGrpFieldHiddenChunk',
            'catFieldTpl'                => 'sample.GoodNewsCatFieldChunk',
            'catFieldHiddenTpl'          => 'sample.GoodNewsCatFieldHiddenChunk',
            'groupsOnly'                 => false,
            'includeGroups'              => '',
            'defaultGroups'              => '',
            'defaultCategories'          => '',
            'sort'                       => 'name',
            'dir'                        => 'ASC',
            'grpCatPlaceholder'          => 'grpcatfieldsets',
            'placeholderPrefix'          => '',
            'errorPage'                  => false,
        ]);
    }

    /**
     * Handle the GoodNewsSubscription snippet business logic.
     *
     * @return string
     */
    public function process()
    {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $groupsOnly = $this->getProperty('groupsOnly', false);
        $userID = false;

        // Set Dictionary instance and load POST array
        /** @var Dictionary $dictionary */
        if (!$this->hasPost()) {
            $this->generateGrpCatFields();
            return '';
        }

        $fields = $this->validate();
        $this->dictionary->reset();
        $this->dictionary->fromArray($fields);

        // Synchronize categories with groups
        // (a category cant be selected without its parent group!)
        if (!$groupsOnly) {
            $this->selectParentGroupsByCategories();
        }

        // Get the subscribers IP address
        $ip = $this->getSubscriberIP();
        $this->dictionary->set('ip', $ip);

        // Check/create username here to prevent processor save error later
        $this->setUsername();

        // Email address is entered by subscriber
        $emailField = $this->getProperty('emailField', 'email');
        $email = $this->dictionary->get($emailField);

        if ($this->validateEmail($emailField, $email)) {
            // Is email address already in use? (existing MODX user!)
            // $userID is either false or holds the MODX user ID and if an email address
            // is existing more than once, a validator error is added.
            $userID = $this->emailExists($emailField, $email);
        }

        if ($this->validator->hasErrors()) {
            $this->modx->toPlaceholders($this->validator->getErrors(), $placeholderPrefix . 'error');
            $this->modx->setPlaceholder($placeholderPrefix . 'validation_error', true);
        } else {
            // Process hooks
            $this->loadPreHooks();
            if ($this->preHooks->hasErrors()) {
                $this->modx->toPlaceholders($this->preHooks->getErrors(), $placeholderPrefix . 'error');
                $errorMsg = $this->preHooks->getErrorMessage();
                $this->modx->setPlaceholder($placeholderPrefix . 'error.message', $errorMsg);
            } else {
                // There are 2 cases where an email address already exists:
                //   1) an existing (active) GoodNews Subscriber
                //      Here we let the subscriber update his subscription profile
                //   2) a MODX user (active) without GoodNews subscriptions:
                //      Here we let the user add subscriptions to his existing MODX account
                //      so he gets a GoodNews susbcriber
                if ($userID) {
                    $userLoaded = false;
                    if ($this->getUserById($userID)) {
                        if ($this->getProfile()) {
                            $userLoaded = true;
                        }
                    }
                    if (!$userLoaded) {
                        $this->redirectAfterFailure();
                    }

                    // An existing GoodNews Subscriber
                    if ($this->getSubscriberMeta($userID)) {
                        // Execute the ReSubscription processor
                        // An existing Subscriber gets the same front-end reaction as a new Subscriber (Privacy!) but:
                        //  - no new MODX user is created
                        //  - a re-subscription mail is sent (including the secure links to edit/cancel subscription)
                        $result = $this->runProcessor('ReSubscription');

                    // A MODX user without GoodNews subscriptions
                    } else {
                        // Execute the ModxUserSubscription processor
                        // An existing MODX user gets the same front-end reaction as a new Subscriber (Privacy!) but:
                        //  - no new MODX user is created
                        //  - a Subscription profile is created (SubscriberMeta)
                        //  - no Group and/or Category selections are created!
                        //  - a subscription success mail is sent
                        //    (including the secure links to edit/cancel subscription)
                        $result = $this->runProcessor('ModxUserSubscription');
                    }

                // A new Subscriber
                } else {
                    $this->setPassword();

                    // Execute the Subscription processor:
                    //  - a new MODX user is created
                    //  - a Subscription profile is created (SubscriberMeta)
                    //  - Group and/or Category selections are created!
                    //  - an activation mail is sent (if double opt-in is enabled)
                    //  - a subscription success mail is sent (if enabled)
                    $result = $this->runProcessor('Subscription');
                }

                if ($result !== true) {
                    $this->modx->setPlaceholder($placeholderPrefix . 'error.message', $result);
                } else {
                    $this->success = true;
                }
            }
        }

        $selectedGroups = $this->dictionary->get('gongroups');
        $selectedCategories = $this->dictionary->get('goncategories');

        $this->generateGrpCatFields($selectedGroups, $selectedCategories);

        // Preserve field values if form loads again (no redirect in subscription processor!)
        $placeholders = $this->dictionary->toArray();
        $this->modx->setPlaceholders($placeholders, $placeholderPrefix);
        foreach ($placeholders as $k => $v) {
            if (is_array($v)) {
                $this->modx->setPlaceholder($placeholderPrefix . $k, json_encode($v));
            }
        }
        return '';
    }

    /**
     * Validate the form fields.
     *
     * @access public
     * @return array $fields
     */
    public function validate()
    {
        $this->validator = $this->subscription->loadValidator();
        $fields = $this->validator->validateFields($this->dictionary, $this->getProperty('validate', ''));
        foreach ($fields as $k => $v) {
            $fields[$k] = str_replace(['[', ']'], ['&#91;', '&#93;'], $v);
        }
        return $fields;
    }

    /**
     * Validate the email address, and ensure it is not empty.
     *
     * @access public
     * @param string $emailField
     * @param string $email
     * @return boolean
     */
    public function validateEmail(string $emailField, string $email)
    {
        $success = true;
        if (empty($email) && !$this->validator->hasErrorsInField($emailField)) {
            $this->validator->addError($emailField, $this->modx->lexicon('goodnews.validator_field_required'));
            $success = false;
        }
        return $success;
    }

    /**
     * Check if username is submitted via form or needs to be auto-generated.
     *  - if submitted via form - check if already exists
     *
     * @access private
     * @return boolean
     */
    private function setUsername()
    {
        $usernameField = $this->getProperty('usernameField', 'username');
        $success = false;

        // Generate username
        $username = $this->dictionary->get($usernameField);
        if (
            empty($username) &&
            !$this->validator->hasErrorsInField($usernameField)
        ) {
            $username = $this->generateUsername();
            $success = true;
        // Take username from form field
        } else {
            if ($this->usernameExists($username)) {
                $this->validator->addError($usernameField, $this->modx->lexicon('goodnews.validator_username_taken'));
                $success = false;
            } else {
                $success = true;
            }
        }
        $this->dictionary->set($usernameField, $username);
        return $success;
    }

    /**
     * Generate a new unique username based on email address.
     *
     * @todo: add property to use full email address as username
     * @access public
     * @return string The username
     */
    public function generateUsername()
    {
        $usernameField = $this->getProperty('usernameField', 'username');
        $emailField = $this->getProperty('emailField', 'email');

        // Don't auto-generate username if usernameField is emailField!
        if ($usernameField == $emailField) {
            return $this->dictionary->get($emailField);
        }

        // Username is generated from local-part of email address
        $email = $this->dictionary->get($emailField);
        $parts = explode('@', $email);
        $usernamepart = $parts[0];

        // Add counter (john.doe_1, martin_2, ...) if username already exists
        $counter = 0;
        $newusername = $usernamepart;
        while ($this->usernameExists($newusername)) {
            $counter++;
            $newusername = $usernamepart . '_' . $counter;
        }
        return $newusername;
    }

    /**
     * Check if a user(name) already exists.
     *
     * @access public
     * @param string $username
     * @return boolean
     */
    public function usernameExists(string $username)
    {
        $exists = false;
        $this->removeExpired($username, self::SEARCH_BY_USERNAME);
        $user = $this->modx->getObject(modUser::class, ['username' => $username]);
        if (is_object($user)) {
            $exists = true;
        }
        return $exists;
    }

    /**
     * Check if password is submitted via form or needs to be auto-generated.
     *
     * @access private
     * @return boolean
     */
    private function setPassword()
    {
        $passwordField = $this->getProperty('passwordField', 'password');
        $password = $this->dictionary->get($passwordField);

        $success = false;
        if (empty($password) && !$this->validator->hasErrorsInField($passwordField)) {
            $this->generatePassword();
            $success = true;
        }
        return $success;
    }

    /**
     * Automatically generate a password for the user.
     *
     * @access private
     * @return string $password
     */
    private function generatePassword()
    {
        $classKey = $this->dictionary->get('class_key');
        if (empty($classKey)) {
            $classKey = 'MODX\\Revolution\\modUser';
        }
        $user = $this->modx->newObject($classKey);
        $password = $user->generatePassword();
        $this->dictionary->set('password', $password);
        return $password;
    }

    /**
     * Check if an email address already exists.
     * MODX allow_multiple_emails setting is ignored -> we never let subscribe an email address more then once!
     *
     * @access public
     * @param string $emailField
     * @param string $email
     * @return mixed ID of MODX user || false
     */
    public function emailExists(string $emailField, string $email)
    {
        $exists = false;

        $this->removeExpired($email);

        $userProfile = $this->modx->getObject(modUserProfile::class, ['email' => $email]);
        if (is_object($userProfile)) {
            // Check if we have more than 1 modUserProfiles based on this email
            // -> Normally this should't be necessary but it's possible that we have multiple users
            //    with the same email address (if enabled in MODX system settings)
            if ($this->modx->getCount(modUserProfile::class, ['email' => $email]) > 1) {
                $this->validator->addError(
                    $emailField,
                    $this->modx->lexicon('goodnews.validator_email_multiple', ['email' => $email])
                );
                $exists = false;
            } else {
                $exists = $userProfile->get('internalKey');
            }
        }
        return $exists;
    }

    /**
     * Check if an email address belongs to a user object with an expired activation and if so -> remove!
     *
     * @access public
     * @param string $search
     * @param string $searchMode (default=self::SEARCH_BY_EMAIL)
     * @return void
     */
    public function removeExpired(string $search, string $searchMode = self::SEARCH_BY_EMAIL)
    {
        $activationttl = $this->getProperty('activationttl', 180);
        // Calculate the expiration date in seconds
        $expDate = time() - ($activationttl * 60);

        $c = $this->modx->newQuery(modUser::class);
        $c->leftJoin(modUserProfile::class, 'Profile');
        $c->leftJoin(GoodNewsSubscriberMeta::class, 'SubscriberMeta', 'modUser.id = SubscriberMeta.subscriber_id');

        switch ($searchMode) {
            default:
            case self::SEARCH_BY_EMAIL:
                $c->where(['Profile.email' => $search]);
                break;
            case self::SEARCH_BY_USERNAME:
                $c->where(['modUser.username' => $search]);
                break;
        }

        // in addition modUser must:
        // - be inactive
        // - have a cachepwd (this means it's an unactivated account)
        // - have SubscriberMeta.subscribedon date < expiration date (GoodNews setting)
        $c->where([
            'active' => false,
            'cachepwd:!=' => '',
            'SubscriberMeta.subscribedon:<' => $expDate,
        ]);

        $users = $this->modx->getIterator(modUser::class, $c);
        foreach ($users as $idx => $user) {
            $user->remove();
        }
    }

    /**
     * Load any pre-subscription hooks.
     *
     * @return void
     */
    public function loadPreHooks()
    {
        $preHooks = $this->getProperty('preHooks', '');
        $this->preHooks = $this->subscription->loadHooks('preHooks');

        if (!empty($preHooks)) {
            $fields = $this->dictionary->toArray();
            // pre-register hooks
            $this->preHooks->loadMultiple($preHooks, $fields, [
                'submitVar' => $this->getProperty('submitVar'),
                'usernameField' => $this->getProperty('usernameField', 'username'),
            ]);
            $values = $this->preHooks->getValues();
            if (!empty($values)) {
                $this->dictionary->fromArray($values);
            }
        }
    }
}
