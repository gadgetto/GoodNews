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

use MODX\Revolution\modX;
use MODX\Revolution\modUser;
use Bitego\GoodNews\Subscription\Subscription;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Model\GoodNewsGroup;
use Bitego\GoodNews\Model\GoodNewsCategory;
use Bitego\GoodNews\Model\GoodNewsGroupMember;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;

/**
 * Base controller class for subscription handling.
 *
 * @package goodnews
 * @subpackage controllers
 */
abstract class Base
{
    /** @var modX $modx */
    public $modx = null;

    /** @var modUser $user */
    public $user = null;

    /** @var modUserProfile $profile */
    public $profile = null;

    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta = null;

    /** @var Subscription $subscription */
    public $subscription = null;

    /** @var Dictionary $dictionary */
    public $dictionary = null;

    /** @var Validator $validator */
    public $validator = null;

    /** @var array $config An array of configuration properties */
    public $config = [];

    /** @var array $scriptProperties An array of script properties */
    protected $scriptProperties = [];

    /** @var array $placeholders */
    protected $placeholders = [];

    /** @var object $goodnewsGroups Collection of GoodNewsGroup entries */
    public $goodnewsGroups = null;

    /** @var object $goodnewsCategories Collection of GoodNewsCategory entries */
    public $goodnewsCategories = null;

    /** @var GoodNewsSubscriberMeta.sid $sid */
    public $sid = '';

    /** @var string $email */
    public $email = '';

    /** @var string $username */
    public $username = '';

    /** @var string $password */
    public $password = '';

    /** @var Hooks $preHooks */
    public $preHooks = null;

    /** @var Hooks $postHooks */
    public $postHooks = null;

    /**
     * The constructor for the Base controller class.
     *
     * @param Subscription $subscription A reference to the Subscription instance
     * @param array $config
     */
    public function __construct(Subscription &$subscription, array $config = [])
    {
        $this->modx = &$subscription->modx;
        $this->subscription = &$subscription;
        $this->config = array_merge($this->config, $config);
        $this->modx->lexicon->load('goodnews:frontend');
    }

    /**
     * Run a process.
     *
     * @param array $scriptProperties An array of properties
     * @access public
     */
    public function run(array $scriptProperties)
    {
        $this->setProperties($scriptProperties);
        $this->initialize();
        return $this->process();
    }

    /**
     * Abstracts initialize method.
     */
    abstract public function initialize();

    /**
     * Abstracts process method.
     */
    abstract public function process();

    /**
     * Authenticate the subscriber by his user session or by sid param submitted via URL string
     * and load modUser, modUserProfile and GoodNewsSusbcriberMeta objects.
     *
     * @access public
     * @return boolean
     */
    public function authenticateSubscriber()
    {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $authenticated = false;

        if ($this->getSid()) {
            // Authenticate user by SID (submitted via URL param)
            if ($this->getUserBySid()) {
                if ($this->getProfile()) {
                    if ($this->getSubscriberMeta($this->user->get('id'))) {
                        $authenticated = true;
                    }
                }
            }
        } else {
            // Authenticate user by its session context
            $currentContext = $this->modx->context->key;
            if (!empty($currentContext) && $currentContext != 'mgr') {
                if ($this->modx->user->hasSessionContext($currentContext)) {
                    $this->user = $this->modx->user;
                    if ($this->getProfile()) {
                        $authenticated = true; // @todo needs to be with next if clause?
                        if ($this->getSubscriberMeta($this->user->get('id'))) {
                            // Set $sid to indicate that SubscriberMeta exists for this user
                            $this->sid = $this->subscribermeta->get('sid');
                            // Set placeholder for filtering content in templates
                            $this->modx->setPlaceholder($placeholderPrefix . 'is_subscriber', '1');
                        }
                    }
                }
            }
        }
        if (!$authenticated) {
            if ($this->getProperty('sendUnauthorizedPage', false, 'isset')) {
                $this->modx->sendUnauthorizedPage();
            }
        }
        return $authenticated;
    }

    /**
     * Gets the sid param from GET request.
     *
     * @access public
     * @return mixed string $sid or false
     */
    public function getSid()
    {
        if (isset($_GET['sid'])) {
            $this->sid = $_GET['sid'];
            return $this->sid;
        }
        return false;
    }

    /**
     * Gets a user by the sid field from the GoodNewsSubscriberMeta table.
     *
     * @access public
     * @return modUser object or null
     */
    public function getUserBySid()
    {
        $subscribermeta = $this->modx->getObject(GoodNewsSubscriberMeta::class, ['sid' => $this->sid]);
        if (is_object($subscribermeta)) {
            $this->user = $this->modx->getObject(modUser::class, [
                'id' => $subscribermeta->get('subscriber_id'),
                'active' => true,
            ]);
            if (!is_object($this->user)) {
                $this->user = null;
            }
        }
        if (!is_object($this->user)) {
            $this->user = null;
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] Could not load user with sid: ' . $this->sid);
        }
        return $this->user;
    }

    /**
     * Gets a user object by the MODX user ID.
     *
     * @access public
     * @params integer $userID
     * @return modUser object or null
     */
    public function getUserById(int $userID)
    {
        $this->user = $this->modx->getObject(modUser::class, [
            'id' => $userID,
        ]);
        if (!is_object($this->user)) {
            $this->user = null;
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] Could not load user with id: ' . $userID);
        }
        return $this->user;
    }

    /**
     * Get the Profile of the active user object.
     *
     * @access public
     * @return modUserProfile object or null
     */
    public function getProfile()
    {
        if (!is_object($this->user)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] User object to load profile doesn\'t exist.');
            return false;
        }
        $this->profile = $this->user->getOne('Profile');
        if (!is_object($this->profile)) {
            $this->profile = null;
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[GoodNews] Could not load profile for user: ' . $this->user->get('username')
            );
        }
        return $this->profile;
    }

    /**
     * Gets a SubscriberMeta object by the MODX user ID.
     *
     * @access public
     * @params integer $userID
     * @return GoodNewsSubscriberMeta object or null
     */
    public function getSubscriberMeta(int $userID)
    {
        $this->subscribermeta = $this->modx->getObject(GoodNewsSubscriberMeta::class, [
            'subscriber_id' => $userID,
        ]);
        if (!is_object($this->subscribermeta)) {
            $this->subscribermeta = null;
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[GoodNews] Could not load GoodNewsSubscriberMeta for user with id: ' . $userID
            );
        }
        return $this->subscribermeta;
    }

    /**
     * Generates the GoodNews groups/categories tree/fields and writes output to defined placeholder.
     *
     * @access public
     * @param mixed $checkedGroups (default [])
     * @param mixed $checkedCategories (default [])
     * @return void
     */
    public function generateGrpCatFields($checkedGroups = [], $checkedCategories = [])
    {
        // Get default properties.
        $grpFieldsetTpl    = $this->getProperty('grpFieldsetTpl', 'sample.GoodNewsGrpFieldsetChunk');
        $grpFieldTpl       = $this->getProperty('grpFieldTpl', 'sample.GoodNewsGrpFieldChunk');
        $grpNameTpl        = $this->getProperty('grpNameTpl', 'sample.GoodNewsGrpNameChunk');
        $grpFieldHiddenTpl = $this->getProperty('grpFieldHiddenTpl', 'sample.GoodNewsGrpFieldHiddenChunk');
        $catFieldTpl       = $this->getProperty('catFieldTpl', 'sample.GoodNewsCatFieldChunk');
        $catFieldHiddenTpl = $this->getProperty('catFieldHiddenTpl', 'sample.GoodNewsCatFieldHiddenChunk');
        $groupsOnly        = $this->getProperty('groupsOnly', false);
        $defaultGroups     = $this->getProperty('defaultGroups', '');
        $defaultCategories = $this->getProperty('defaultCategories', '');
        $grpCatPlaceholder = $this->getProperty('grpCatPlaceholder', 'grpcatfieldsets');
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');

        $output = '';
        $fieldsOutput = '';

        $checkedGroups = empty($checkedGroups) ? [] : $checkedGroups;
        $checkedCategories = empty($checkedCategories) ? [] : $checkedCategories;

        $groups = null;
        $categories = null;

        // Read available groups and categories from database
        $groups = $this->collectGoodNewsGroups();
        if (!$groups) {
            $this->modx->setPlaceholder($placeholderPrefix . 'config_error', '1');
            return false;
        }
        if (!(bool)$groupsOnly) {
            $categories = $this->collectGoodNewsCategories();
        }

        // Groups/categories fields are hidden - subscriber can't select and will be automatically assigned
        // (most other properties are ignored in this case)
        if (!empty($defaultGroups) || !empty($defaultCategories)) {
            // Set a helper placeholder for filtering output
            $this->modx->setPlaceholder($placeholderPrefix . 'fields_hidden', '1');

            foreach ($groups as $group) {
                $grpPlaceholders = $group->toArray();
                // Add hidden group field to output
                $output .= $this->modx->getChunk($grpFieldHiddenTpl, $grpPlaceholders);

                if (!(bool)$groupsOnly) {
                    foreach ($categories as $category) {
                        $catPlaceholders = $category->toArray();
                        // Only categories of current group
                        if ($catPlaceholders['goodnewsgroup_id'] == $grpPlaceholders['id']) {
                            // Add hidden category field to output
                            $output .= $this->modx->getChunk($catFieldHiddenTpl, $catPlaceholders);
                        }
                    }
                }
            }

        // Groups/categories fields are built as visible list - subscriber can select
        } else {
            foreach ($groups as $group) {
                $grpPlaceholders = $group->toArray();

                if (in_array($grpPlaceholders['id'], $checkedGroups)) {
                    $grpPlaceholders['checked'] = ' checked="checked"';
                }

                if ((bool)$groupsOnly) {
                    // Add selectable group field to output
                    $fieldsOutput .= $this->modx->getChunk($grpFieldTpl, $grpPlaceholders);
                } else {
                    // Add group name to output (in this case the group will be
                    // selected automatically by its child category)
                    $fieldsOutput .= $this->modx->getChunk($grpNameTpl, $grpPlaceholders);

                    foreach ($categories as $category) {
                        $catPlaceholders = $category->toArray();

                        // Only categories which are assigned to current group
                        if ($catPlaceholders['goodnewsgroup_id'] == $grpPlaceholders['id']) {
                            if (in_array($catPlaceholders['id'], $checkedCategories)) {
                                $catPlaceholders['checked'] = ' checked="checked"';
                            }
                            // Add category field to output
                            $fieldsOutput .= $this->modx->getChunk($catFieldTpl, $catPlaceholders);
                        }
                    }

                    // Each single group + related categories is wrapped within a fieldset
                    $fieldsPlaceholder = ['grpcatfields' => $fieldsOutput];
                    unset($fieldsOutput);
                    $output .= $this->modx->getChunk($grpFieldsetTpl, $fieldsPlaceholder);
                }
            }

            // If only groups are used, the groups list as a whole is wrapped with a fieldset
            if ((bool)$groupsOnly) {
                $fieldsPlaceholder = ['grpcatfields' => $fieldsOutput];
                $output = $this->modx->getChunk($grpFieldsetTpl, $fieldsPlaceholder);
            }
        }

        // Write whole output to defined placeholder
        // -> this placeholder is used to output the whole group/category tree/list in frontend
        $this->modx->setPlaceholder($grpCatPlaceholder, $output);
    }

    /**
     * Read GoodNewsGroups from database.
     *
     * @access public
     * @return collection of GoodNewsGroup objects|null
     */
    public function collectGoodNewsGroups()
    {
        $includeGroups = $this->getProperty('includeGroups', '');
        $defaultGroups = $this->getProperty('defaultGroups', '');
        $sort          = $this->getProperty('sort', 'name');
        $dir           = $this->getProperty('dir', 'ASC');

        $query = $this->modx->newQuery(GoodNewsGroup::class);

        if (!empty($defaultGroups)) {
            $query->where(['id:IN' => explode(',', $defaultGroups)]);
        } elseif (!empty($includeGroups)) {
            $query->where(['id:IN' => explode(',', $includeGroups)]);
        }

        $query->where(['modxusergroup' => 0]);
        $query->where(['public' => 1]);
        $query->sortby($sort, $dir);
        $this->goodnewsGroups = $this->modx->getCollection(GoodNewsGroup::class, $query);
        if (empty($this->goodnewsGroups)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] No GoodNewsGroup data selected.');
        }
        return $this->goodnewsGroups;
    }

    /**
     * Read GoodNewsCategories from database.
     *
     * @access public
     * @return collection of GoodNewsCategory objects|null
     */
    public function collectGoodNewsCategories()
    {
        $defaultCategories = $this->getProperty('defaultCategories', '');
        $sort              = $this->getProperty('sort', 'name');
        $dir               = $this->getProperty('dir', 'ASC');

        $query = $this->modx->newQuery(GoodNewsCategory::class);
        if (!empty($defaultCategories)) {
            $query->where(['id:IN' => explode(',', $defaultCategories)]);
        }

        $query->where(['public' => 1]);
        $query->sortby($sort, $dir);
        $this->goodnewsCategories = $this->modx->getCollection(GoodNewsCategory::class, $query);
        if (empty($this->goodnewsCategories)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] No GoodNewsCategory data selected.');
        }
        return $this->goodnewsCategories;
    }

    /**
     * Get group member entries of user.
     *
     * @access public
     * @param int $userid
     * @return array $membergroupids
     */
    public function collectGoodNewsGroupMembers(int $userid)
    {
        $membergroups = $this->modx->getCollection(GoodNewsGroupMember::class, ['member_id' => $userid]);
        $membergroupids = [];
        foreach ($membergroups as $membergroup) {
            array_push($membergroupids, $membergroup->get('goodnewsgroup_id'));
        }
        return $membergroupids;
    }

    /**
     * Get category member entries of user.
     *
     * @access public
     * @param int $userid
     * @return array $membercategoryids
     */
    public function collectGoodNewsCategoryMembers(int $userid)
    {
        $membercategories = $this->modx->getCollection(GoodNewsCategoryMember::class, ['member_id' => $userid]);
        $membercategoryids = [];
        foreach ($membercategories as $membercategory) {
            array_push($membercategoryids, $membercategory->get('goodnewscategory_id'));
        }
        return $membercategoryids;
    }

    /**
     * Ensure that all parent groups of selected categories are also selected.
     * (This is only used if not groupsOnly mode!)
     *
     * @access public
     * @return void
     */
    public function selectParentGroupsByCategories()
    {
        $parentGroups = [];

        /* array $selectedCategories */
        $selectedCategories = $this->dictionary->get('goncategories');

        if (!empty($selectedCategories)) {
            $query = $this->modx->newQuery(GoodNewsCategory::class);
            $query->where(['id:IN' => $selectedCategories]);
            $query->sortby('goodnewsgroup_id', 'ASC');

            $categories = $this->modx->getCollection(GoodNewsCategory::class, $query);
            foreach ($categories as $category) {
                array_push($parentGroups, (string)$category->get('goodnewsgroup_id'));
            }
            // (the array_unique method messes up the array)
            //$this->dictionary->set('gongroups', array_unique($parentGroups));

            // Instead use this method to get unique values from a simple array
            $this->dictionary->set('gongroups', array_keys(array_flip($parentGroups)));
        }
    }

    /**
     * Set the default script properties.
     *
     * @access protected
     * @param array $defaults
     * @return void
     */
    protected function setDefaultProperties(array $defaults = [])
    {
        $this->scriptProperties = array_merge($defaults, $this->scriptProperties);
    }

    /**
     * Set a script property.
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setProperty(string $key, $value)
    {
        $this->scriptProperties[$key] = $value;
    }

    /**
     * Set an array of script properties.
     *
     * @access public
     * @param array $array
     * @return void
     */
    public function setProperties(array $array)
    {
        foreach ($array as $k => $v) {
            $this->setProperty($k, $v);
        }
    }

    /**
     * Get a script property.
     *
     * @access public
     * @param string $key
     * @param mixed $default
     * @param string $method
     * @return mixed
     */
    public function getProperty(string $key, $default = null, string $method = '!empty')
    {
        $v = $default;

        switch ($method) {
            case 'empty':
            case '!empty':
                if (!empty($this->scriptProperties[$key])) {
                    $v = $this->scriptProperties[$key];
                }
                break;

            case 'isset':
            default:
                if (isset($this->scriptProperties[$key])) {
                    $v = $this->scriptProperties[$key];
                }
                break;
        }
        return $v;
    }

    /**
     * Return an array of REQUEST script properties.
     *
     * @access public
     * @return array
     */
    public function getProperties()
    {
        return $this->scriptProperties;
    }

    /**
     * Set a placeholder.
     *
     * @access public
     * @param mixed $k
     * @param mixed $v
     * @return void
     */
    public function setPlaceholder($k, $v)
    {
        $this->placeholders[$k] = $v;
    }

    /**
     * Get a placeholder.
     *
     * @access public
     * @param mixed $k
     * @param mixed $default (default: null)
     * @return void
     */
    public function getPlaceholder($k, $default = null)
    {
        return isset($this->placeholders[$k]) ? $this->placeholders[$k] : $default;
    }

    /**
     * Set an array of placeholders.
     *
     * @access public
     * @param array $array
     * @return void
     */
    public function setPlaceholders(array $array)
    {
        foreach ($array as $k => $v) {
            $this->setPlaceholder($k, $v);
        }
    }

    /**
     * Get an array of placeholders.
     *
     * @access public
     * @return void
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Run a desired processor.
     *
     * @access public
     * @param string $processor Name of the processor to run
     * @return mixed|string
     */
    public function runProcessor(string $processor)
    {
        $output = '';
        $processor = $this->loadProcessor($processor);
        if (empty($processor)) {
            return $output;
        }
        // Return the output of the processor
        return $processor->process();
    }

    /**
     * Loads a processor.
     *
     * @access public
     * @param string $processor Name of the processor to load
     * @return bool|Subscription processor
     */
    public function loadProcessor(string $processor)
    {
        $processorFile = $this->config['processorsPath'] . $processor . '.php';
        if (!file_exists($processorFile)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not find processor file: ' . $processorFile);
            return false;
        }
        try {
            $className = 'Bitego\\GoodNews\\Processors\\Subscription\\' . $processor;
            if (!class_exists($className)) {
                $className = include_once $processorFile;
            }
            $processor = new $className($this->subscription, $this);
        } catch (\Exception $e) {
            $processor = false;
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] ' . $e->getMessage());
        }
        return $processor;
    }

    /**
     * Check if the form has been submitted + load and set dictionary.
     *
     * @access public
     * @return boolean
     */
    public function hasPost()
    {
        $hasPost = false;
        $submitVar = $this->getProperty('submitVar');
        if (!empty($_POST) && (empty($submitVar) || !empty($_POST[$submitVar]))) {
            $this->dictionary = $this->subscription->loadDictionary();
            if ($this->dictionary) {
                $hasPost = true;
            }
        }
        return $hasPost;
    }

    /**
     * Send a subscription success email to the user.
     *
     * @access public
     * @param array $properties Required mail properties
     * @return boolean
     */
    public function sendSubscriptionEmail(array $properties)
    {
        // Additional required properties
        $properties['tpl'] = $this->getProperty('subscriptionEmailTpl', 'sample.GoodNewsSubscriptionEmailChunk');
        $properties['tplAlt'] = $this->getProperty('subscriptionEmailTplAlt', '');
        $properties['tplType'] = $this->getProperty('subscriptionEmailTplType', 'modChunk');

        // Generate secure links urls
        $params = ['sid' => $properties['sid']];

        $profileResourceId = $this->getProperty('profileResourceId', '');
        if (empty($profileResourceId)) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[GoodNews] Subscription - snippet parameter profileResourceId not set.'
            );
        } else {
            $properties['updateProfileUrl'] = $this->modx->makeUrl($profileResourceId, '', $params, 'full');
        }

        $unsubscribeResourceId = $this->getProperty('unsubscribeResourceId', '');
        if (empty($unsubscribeResourceId)) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[GoodNews] Subscription - snippet parameter unsubscribeResourceId not set.'
            );
        } else {
            $properties['unsubscribeUrl'] = $this->modx->makeUrl($unsubscribeResourceId, '', $params, 'full');
        }

        $email = $properties['email'];
        $defaultSubject = $this->modx->lexicon('goodnews.subscription_email_subject');
        $subject = $this->getProperty('subscriptionEmailSubject', $defaultSubject);

        return $this->subscription->sendEmail($email, $subject, $properties);
    }

    /**
     * Send an email to the user containing secure links to update or cancel subscriptions.
     *
     * @access public
     * @param array $properties
     * @return boolean
     */
    public function sendReSubscriptionEmail(array $properties)
    {
        // Additional required properties
        $properties['tpl'] = $this->getProperty('reSubscriptionEmailTpl', 'sample.GoodNewsReSubscriptionEmailChunk');
        $properties['tplAlt'] = $this->getProperty('reSubscriptionEmailTplAlt', '');
        $properties['tplType'] = $this->getProperty('reSubscriptionEmailTplType', 'modChunk');

        // Generate secure links urls
        $params = [
            'sid' => $properties['sid'],
            'gg'  => $this->subscription->encodeParams($this->dictionary->get('gongroups')),
            'gc'  => $this->subscription->encodeParams($this->dictionary->get('goncategories')),
        ];

        $profileResourceId = $this->getProperty('profileResourceId', '');
        if (empty($profileResourceId)) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[GoodNews] Subscription - snippet parameter profileResourceId not set.'
            );
        } else {
            $properties['updateProfileUrl'] = $this->modx->makeUrl($profileResourceId, '', $params, 'full');
        }

        $unsubscribeResourceId = $this->getProperty('unsubscribeResourceId', '');
        if (empty($unsubscribeResourceId)) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[GoodNews] Subscription - snippet parameter unsubscribeResourceId not set.'
            );
        } else {
            $properties['unsubscribeUrl'] = $this->modx->makeUrl($unsubscribeResourceId, '', $params, 'full');
        }

        $email = $properties['email'];
        $defaultSubject = $this->modx->lexicon('goodnews.resubscription_email_subject');
        $subject = $this->getProperty('reSubscriptionEmailSubject', $defaultSubject);

        return $this->subscription->sendEmail($email, $subject, $properties);
    }

    /**
     * Handle the redirection after a failed verification.
     *
     * @access public
     * @return void
     */
    public function redirectAfterFailure()
    {
        $errorPage = $this->getProperty('errorPage', false, 'isset');
        if (!empty($errorPage)) {
            $url = $this->modx->makeUrl($errorPage, '', '', 'full');
            $this->modx->sendRedirect($url);
        } else {
            // Send to the default MODX error page
            $this->modx->sendErrorPage();
        }
    }

    /**
     * Helper function to get the "real" IP address of a subscriber.
     *
     * @access public
     * @return string $ip The IP address (or string 'unknown')
     */
    public function getSubscriberIP()
    {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // Trim for safety measures
                    $ip = trim($ip);
                    // Validate IP
                    if (
                        filter_var(
                            $ip,
                            FILTER_VALIDATE_IP,
                            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                        ) !== false
                    ) {
                        return $ip;
                    }
                }
            }
        }
        // If no IP could be determined
        $ip = 'unknown';
        return $ip;
    }
}
