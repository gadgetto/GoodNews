<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 * Based on code from Login add-on
 * Copyright 2012 by Jason Coward <jason@modx.com> and Shaun McCormick <shaun@modx.com>
 * Modified by bitego - 10/2013
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * Main controller class for subscription handling.
 *
 * @package goodnews
 */

abstract class GoodNewsSubscriptionController {
    /** @var modX $modx */
    public $modx;
    
    /** @var GoodNewsSubscription $goodnewssubscription */
    public $goodnewssubscription;
    
    /** @var array $config */
    public $config = array();
    
    /** @var array $scriptProperties */
    protected $scriptProperties = array();
    
    /** @var GoodNewsSubscriptionValidator $validator */
    public $validator;
    
    /** @var GoodNewsSubscriptionDictionary $dictionary */
    public $dictionary;
    
    /** @var GoodNewsSubscriptionHooks $preHooks */
    public $preHooks;
    
    /** @var GoodNewsSubscriptionHooks $postHooks */
    public $postHooks;
    
    /** @var array $placeholders */
    protected $placeholders = array();

    /** @var object $goodnewsGroups Collection of GoodNewsGroup entries */
    public $goodnewsGroups;
    
    /** @var object $goodnewsCategories Collection of GoodNewsCategory entries */
    public $goodnewsCategories;

    /** @var modUser $user */
    public $user;
    
    /** @var modUserProfile $profile */
    public $profile;
    
    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta;
    
    /** @var GoodNewsSubscriberMeta.sid $sid */
    public $sid;

    /** @var string $email */
    public $email;

    /**
     * The constructor for the GoodNewsSubscriptionController class.
     *
     * @param GoodNewsSubscription $goodnewssubscription A reference to the GoodNewsSubscription instance
     * @param array $config
     */
    function __construct(GoodNewsSubscription &$goodnewssubscription, array $config = array()) {
        $this->goodnewssubscription =& $goodnewssubscription;
        $this->modx =& $goodnewssubscription->modx;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 
     * @access public
     */
    public function run($scriptProperties) {
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
    public function authenticateSubscriber() {
        $placeholderPrefix = $this->getProperty('placeholderPrefix', '');
        $authenticated = false;
        
        // Authenticate user by SID (submitted via URL param)
        if ($this->getSid()) {
            if ($this->getUserBySid()) {
                if ($this->getProfile()) {
                    if ($this->getSubscriberMeta($this->user->get('id'))) {
                        $authenticated = true;   
                    }
                }        
            }
        // Authenticate user by its session context
        } else {
            $currentContext = $this->modx->context->key;
            if (!empty($currentContext) && $currentContext != 'mgr') {
                if ($this->modx->user->hasSessionContext($currentContext)) {
                    $this->user = $this->modx->user;
                    if ($this->getProfile()) {
                        $authenticated = true; 
                        if ($this->getSubscriberMeta($this->user->get('id'))) {
                            // Set $sid to indicate that SubscriberMeta exists for this user
                            $this->sid = $this->subscribermeta->get('sid');
                            // Set placeholder for filtering content in templates
                            $this->modx->setPlaceholder($placeholderPrefix.'is_subscriber', '1');
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
    public function getSid() {
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
    public function getUserBySid() {
        $subscribermeta = $this->modx->getObject('GoodNewsSubscriberMeta', array('sid' => $this->sid));
        if ($subscribermeta) {
            $this->user = $this->modx->getObject('modUser', array(
                'id' => $subscribermeta->get('subscriber_id'),
                'active' => true,
            ));
        }
        if (!is_object($this->user)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] Could not load user with sid: '.$this->sid);
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
    public function getUserById($userID) {
        $this->user = $this->modx->getObject('modUser', array(
            'id' => $userID,
        ));
        if (!is_object($this->user)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] Could not load user with id: '.$userID);
        }
        return $this->user;
    }

    /**
     * Get the Profile of the active user object.
     *
     * @access public
     * @return modUserProfile object or null
     */
    public function getProfile() {
        if (!is_object($this->user)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] User object to load profile doesn\'t exist.');
            return false;
        }
        $this->profile = $this->user->getOne('Profile');
        if (!is_object($this->profile)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] Could not load profile for user: '.$this->user->get('username'));
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
    public function getSubscriberMeta($userID) {
        $this->subscribermeta = $this->modx->getObject('GoodNewsSubscriberMeta', array(
            'subscriber_id' => $userID,
        ));
        if (!is_object($this->subscribermeta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] Could not load GoodNewsSubscriberMeta for user with id: '.$userID);
        }
        return $this->subscribermeta;
    }

    /**
     * Generates the GoodNews groups/categories tree/fields and writes output to defined placeholder.
     * 
     * @access public
     * @param array $checkedGroups (default array())
     * @param array $checkedCategories (default array())
     * @return void
     */
    public function generateGrpCatFields($checkedGroups = array(), $checkedCategories = array()) {
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
        
        // Read available groups and categories from database
        $groups = $this->collectGoodNewsGroups();
        if (!$groups) {
            $this->modx->setPlaceholder($placeholderPrefix.'config_error', '1');
            return false;
        }
        if (!(bool)$groupsOnly) {
            $categories = $this->collectGoodNewsCategories();
        }

        // Groups/categories fields are hidden - subscriber can't select and will be automatically assigned
        // (most other properties are ignored in this case)
        if (!empty($defaultGroups) || !empty($defaultCategories)) {

            // Set a helper placeholder for filtering output
            $this->modx->setPlaceholder($placeholderPrefix.'fields_hidden', '1');
            
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
                
                if (in_array($grpPlaceholders['id'], $checkedGroups)) { $grpPlaceholders['checked'] = ' checked="checked"'; }
                
                if ((bool)$groupsOnly) {
                    // Add selectable group field to output
                    $fieldsOutput .= $this->modx->getChunk($grpFieldTpl, $grpPlaceholders);
                } else {
                    // Add group name to output (in this case the group will be selected automatically by its child category)
                    $fieldsOutput .= $this->modx->getChunk($grpNameTpl, $grpPlaceholders);
            
                    foreach ($categories as $category) {
                    
                        $catPlaceholders = $category->toArray();
                        
                        // Only categories which are assigned to current group
                        if ($catPlaceholders['goodnewsgroup_id'] == $grpPlaceholders['id']) {

                            if (in_array($catPlaceholders['id'], $checkedCategories)) { $catPlaceholders['checked'] = ' checked="checked"'; }

                            // Add category field to output
                            $fieldsOutput .= $this->modx->getChunk($catFieldTpl, $catPlaceholders);
                        }
                    }
                    
                    // Each single group + related categories is wrapped within a fieldset
                    $fieldsPlaceholder = array('grpcatfields' => $fieldsOutput);
                    unset($fieldsOutput);
                    $output .= $this->modx->getChunk($grpFieldsetTpl, $fieldsPlaceholder);
                }

            }
            
            // If only groups are used, the groups list as a whole is wrapped with a fieldset
            if ((bool)$groupsOnly) {
                $fieldsPlaceholder = array('grpcatfields' => $fieldsOutput);
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
     * @return collection of goodnewsGroup objects || null
     */
    public function collectGoodNewsGroups() {
        $includeGroups = $this->getProperty('includeGroups', '');
        $defaultGroups = $this->getProperty('defaultGroups', '');
        $sort          = $this->getProperty('sort', 'name');
        $dir           = $this->getProperty('dir', 'ASC');
        
        $query = $this->modx->newQuery('GoodNewsGroup');
        
        if (!empty($defaultGroups)) {
            $query->where(array('id:IN' => explode(',', $defaultGroups)));
        } elseif (!empty($includeGroups)) {
            $query->where(array('id:IN' => explode(',', $includeGroups)));
        }
        
        $query->where(array('modxusergroup' => 0));
        $query->where(array('public' => 1));
        $query->sortby($sort, $dir);
        $this->goodnewsGroups = $this->modx->getCollection('GoodNewsGroup', $query);
        if (empty($this->goodnewsGroups)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] No GoodNewsGroup data selected.');
        }
        return $this->goodnewsGroups;
    }

    /**
     * Read GoodNewsCategories from database.
     * 
     * @access public
     * @return collection of goodnewsCategory objects || null
     */
    public function collectGoodNewsCategories() {
        $defaultCategories = $this->getProperty('defaultCategories', '');
        $sort              = $this->getProperty('sort', 'name');
        $dir               = $this->getProperty('dir', 'ASC');

        $query = $this->modx->newQuery('GoodNewsCategory');
        if (!empty($defaultCategories)) {
            $query->where(array('id:IN' => explode(',', $defaultCategories)));
        }
        
        $query->where(array('public' => 1));
        $query->sortby($sort, $dir);
        $this->goodnewsCategories = $this->modx->getCollection('GoodNewsCategory', $query);
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
    public function collectGoodNewsGroupMembers($userid) {
        $membergroups = $this->modx->getCollection('GoodNewsGroupMember', array('member_id' => $userid));
        $membergroupids = array();
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
    public function collectGoodNewsCategoryMembers($userid) {
        $membercategories = $this->modx->getCollection('GoodNewsCategoryMember', array('member_id' => $userid));
        $membercategoryids = array();
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
    public function selectParentGroupsByCategories() {
        $parentGroups = array();
        
        /* array $selectedCategories */
        $selectedCategories = $this->dictionary->get('goncategories');

        if (!empty($selectedCategories)) {
            $query = $this->modx->newQuery('GoodNewsCategory');
            $query->where(array('id:IN' => $selectedCategories));
            $query->sortby("goodnewsgroup_id", 'ASC');

            $categories = $this->modx->getCollection('GoodNewsCategory', $query);
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
     * Set the default options for this module.
     *
     * @access protected
     * @param array $defaults
     * @return void
     */
    protected function setDefaultProperties(array $defaults = array()) {
        $this->scriptProperties = array_merge($defaults, $this->scriptProperties);
    }

    /**
     * Set an option for this module.
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setProperty($key, $value) {
        $this->scriptProperties[$key] = $value;
    }
    
    /**
     * Set an array of options.
     *
     * @access public
     * @param array $array
     * @return void
     */
    public function setProperties($array) {
        foreach ($array as $k => $v) {
            $this->setProperty($k, $v);
        }
    }

    /**
     * Get an option.
     *
     * @access public
     * @param $key
     * @param null $default
     * @param string $method
     * @return mixed
     */
    public function getProperty($key, $default = null, $method = '!empty') {
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
     * Return an array of REQUEST options.
     *
     * @access public
     * @return array
     */
    public function getProperties() {
        return $this->scriptProperties;
    }

    /**
     * setPlaceholder function.
     * 
     * @access public
     * @param mixed $k
     * @param mixed $v
     * @return void
     */
    public function setPlaceholder($k, $v) {
        $this->placeholders[$k] = $v;
    }
    
    /**
     * getPlaceholder function.
     * 
     * @access public
     * @param mixed $k
     * @param mixed $default (default: null)
     * @return void
     */
    public function getPlaceholder($k, $default = null) {
        return isset($this->placeholders[$k]) ? $this->placeholders[$k] : $default;
    }
    
    /**
     * setPlaceholders function.
     * 
     * @access public
     * @param mixed $array
     * @return void
     */
    public function setPlaceholders($array) {
        foreach ($array as $k => $v) {
            $this->setPlaceholder($k, $v);
        }
    }
    
    /**
     * getPlaceholders function.
     * 
     * @access public
     * @return void
     */
    public function getPlaceholders() {
        return $this->placeholders;
    }

    /**
     * Load the Dictionary class and gather $_POST params.
     *
     * @access public
     * @return GoodNewsSubscriptionDictionary
     */
    public function loadDictionary() {
        $classPath = $this->getProperty('dictionaryClassPath', $this->goodnewssubscription->config['modelPath'].'goodnews/');
        $className = $this->getProperty('dictionaryClassName', 'GoodNewsSubscriptionDictionary');
        if ($this->modx->loadClass($className, $classPath, true, true)) {
            $this->dictionary = new GoodNewsSubscriptionDictionary($this->goodnewssubscription);
            // load POST parameters
            $this->dictionary->gather();
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not load GoodNewsSubscriptionDictionary class from: '.$classPath);
        }
        return $this->dictionary;
    }

    /**
     * Loads the GoodNewsSubscriptionValidator class.
     *
     * @access public
     * @param array $config An array of configuration parameters for the GoodNewsSubscriptionValidator class
     * @return GoodNewsSubscriptionValidator An instance of the GoodNewsSubscriptionValidator class.
     */
    public function loadValidator($config = array()) {
        if (!$this->modx->loadClass('GoodNewsSubscriptionValidator', $this->config['modelPath'].'goodnews/', true, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not load Validator class.');
            return false;
        }
        $this->validator = new GoodNewsSubscriptionValidator($this->goodnewssubscription, $config);
        return $this->validator;
    }

    /**
     * Loads the Hooks class.
     *
     * @access public
     * @param string $type The name of the Hooks service to load
     * @param array $config array An array of configuration parameters for the hooks class
     * @return GoodNewsSubscriptionHooks An instance of the GoodNewsSubscriptionHooks class.
     */
    public function loadHooks($type, $config = array()) {
        if (!$this->modx->loadClass('GoodNewsSubscriptionHooks', $this->config['modelPath'].'goodnews/', true, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not load Hooks class.');
            return false;
        }
        $this->$type = new GoodNewsSubscriptionHooks($this->goodnewssubscription, $this, $config);
        return $this->$type;
    }

    /**
     * Run a desired processor.
     *
     * @access public
     * @param string $processor
     * @return mixed|string
     */
    public function runProcessor($processor) {
        $output = '';
        $processor = $this->loadProcessor($processor);
        if (empty($processor)) return $output;

        // Return the output of the processor
        return $processor->process();
    }

    /**
     * Loads a processor.
     *
     * @access public
     * @param string $processor
     * @return bool || GoodNewsSubscriptionProcessor
     */
    public function loadProcessor($processor) {
        $processorFile = $this->config['processorsPath'].strtolower($processor).'.class.php';
        if (!file_exists($processorFile)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not load processor file: '.$processorFile);
            return false;
        }
        try {
            $className = 'GoodNewsSubscription'.$processor.'Processor';
            if (!class_exists($className)) {
                $className = include_once $processorFile;
            }
            $processor = new $className($this->goodnewssubscription, $this);
        } catch (Exception $e) {
            $processor = false;
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] '.$e->getMessage());
        }
        return $processor;
    }

    /**
     * Check if the form has been submitted.
     *
     * @access public
     * @return boolean
     */
    public function hasPost() {
        $submitVar = $this->getProperty('submitVar');
        return (!empty($_POST) && (empty($submitVar) || !empty($_POST[$submitVar])));
    }

    /**
     * Send a subscription success email to the user.
     *
     * @access public
     * @param array $emailProperties
     * @return boolean
     */
    public function sendSubscriptionEmail($emailProperties) {
        
        // Additional required properties
        $emailProperties['tpl']     = $this->getProperty('subscriptionEmailTpl', 'sample.GoodNewsSubscriptionEmailChunk');
        $emailProperties['tplAlt']  = $this->getProperty('subscriptionEmailTplAlt', '');
        $emailProperties['tplType'] = $this->getProperty('subscriptionEmailTplType', 'modChunk');

        // Generate secure links urls
        $params = array(
            'sid' => $emailProperties['sid'],
        );

        $profileResourceId = $this->getProperty('profileResourceId', '');
        if (empty($profileResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_WARN, '[GoodNews] GoodNewsSubscription - snippet parameter profileResourceId not set.');
        } else {
            $emailProperties['updateProfileUrl'] = $this->modx->makeUrl($profileResourceId, '', $params, 'full');
        }

        $unsubscribeResourceId = $this->getProperty('unsubscribeResourceId', '');
        if (empty($unsubscribeResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_WARN, '[GoodNews] GoodNewsSubscription - snippet parameter unsubscribeResourceId not set.');
        } else {
            $emailProperties['unsubscribeUrl'] = $this->modx->makeUrl($unsubscribeResourceId, '', $params, 'full');
        }

        $email = $emailProperties['email'];
        $subject = $this->getProperty('subscriptionEmailSubject', $this->modx->lexicon('goodnews.subscription_email_subject'));

        return $this->goodnewssubscription->sendEmail($email, $subject, $emailProperties);
    }

    /**
     * Send an email to the user containing secure links to update or cancel subscriptions.
     *
     * @access public
     * @param array $emailProperties
     * @return boolean
     */
    public function sendReSubscriptionEmail($emailProperties) {
        // Additional required properties
        $emailProperties['tpl']     = $this->getProperty('reSubscriptionEmailTpl', 'sample.GoodNewsReSubscriptionEmailChunk');
        $emailProperties['tplAlt']  = $this->getProperty('reSubscriptionEmailTplAlt', '');
        $emailProperties['tplType'] = $this->getProperty('reSubscriptionEmailTplType', 'modChunk');

        // Generate secure links urls
        $params = array(
            'sid' => $emailProperties['sid'],
            'gg'  => $this->goodnewssubscription->encodeParams($this->dictionary->get('gongroups')),
            'gc'  => $this->goodnewssubscription->encodeParams($this->dictionary->get('goncategories')),
        );
        
        $profileResourceId = $this->getProperty('profileResourceId', '');
        if (empty($profileResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_WARN, '[GoodNews] GoodNewsSubscription - snippet parameter profileResourceId not set.');
        } else {
            $emailProperties['updateProfileUrl'] = $this->modx->makeUrl($profileResourceId, '', $params, 'full');
        }

        $unsubscribeResourceId = $this->getProperty('unsubscribeResourceId', '');
        if (empty($unsubscribeResourceId)) {
            $this->modx->log(modX::LOG_LEVEL_WARN, '[GoodNews] GoodNewsSubscription - snippet parameter unsubscribeResourceId not set.');
        } else {
            $emailProperties['unsubscribeUrl'] = $this->modx->makeUrl($unsubscribeResourceId, '', $params, 'full');
        }

        $email = $emailProperties['email'];
        $subject = $this->getProperty('reSubscriptionEmailSubject', $this->modx->lexicon('goodnews.resubscription_email_subject'));
        return $this->goodnewssubscription->sendEmail($email, $subject, $emailProperties);
    }

    /**
     * Handle the redirection after a failed verification.
     *
     * @access public
     * @return void
     */
    public function redirectAfterFailure() {
        $errorPage = $this->getProperty('errorPage', false, 'isset');
        if (!empty($errorPage)) {
            $url = $this->modx->makeUrl($errorPage, '', '', 'full');
            $this->modx->sendRedirect($url);
        } else {
            // send to the default MODX error page
            $this->modx->sendErrorPage();
        }
    }

    /**
     * Helper function to get the "real" IP address of a subscriber.
     *
     * @access public
     * @return string $ip The IP address (or string 'unknown')
     */
    public function getSubscriberIP() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // validate IP
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
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


/**
 * Abstracts processors into a class
 *
 * @package goodnews
 */
abstract class GoodNewsSubscriptionProcessor {
    /** @var GoodNewsSubscription $goodnewssubscription */
    public $goodnewssubscription;
    
    /** @var GoodNewsSubscriptionController $controller */
    public $controller;
    
    /** @var GoodNewsSubscriptionDictionary $dictionary */
    public $dictionary;
    
    /** @var array $config */
    public $config = array();
    
    /**
     * @param GoodNewsSubscription &$goodnewssubscription A reference to the GoodNewsSubscription instance
     * @param GoodNewsSubscriptionController &$controller
     * @param array $config
     */
    function __construct(GoodNewsSubscription &$goodnewssubscription, GoodNewsSubscriptionController &$controller, array $config = array()) {
        $this->goodnewssubscription = &$goodnewssubscription;
        $this->modx = &$goodnewssubscription->modx;
        $this->controller = &$controller;
        $this->dictionary = &$controller->dictionary;
        $this->config = array_merge($this->config, $config);
    }

    abstract function process();
}
