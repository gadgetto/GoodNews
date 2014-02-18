<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
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
 * GoodNewsResourceMailing classes
 *
 * @package goodnews
 */

require_once MODX_CORE_PATH.'model/modx/modprocessor.class.php';
require_once MODX_CORE_PATH.'model/modx/processors/resource/create.class.php';
require_once MODX_CORE_PATH.'model/modx/processors/resource/update.class.php';

/**
 * Overrides the modResource class
 *
 * @package goodnews
 */
class GoodNewsResourceMailing extends modResource {
    public $allowListingInClassKeyDropdown = false;
    public $showInContextMenu = false;

    /**
     * Override modResource::__construct to ensure specific fields are forced to be set.
     *
     * @param xPDO $xpdo
     */
    function __construct(xPDO &$xpdo) {
        parent::__construct($xpdo);
        $this->set('class_key', 'GoodNewsResourceMailing');
        $this->set('show_in_tree', false);
        $this->set('searchable', false);
    }
    
    /**
     * Get the controller path for our resource type.
     * 
     * {@inheritDoc}
     * @static
     * @param xPDO $modx
     * @return string
     */
    public static function getControllerPath(xPDO &$modx) {
        return $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'controllers/res/mailing/';
    }

    /**
     * Override modResource::process to set custom placeholders for the Resource when rendering it in front-end.
     *
     * {@inheritDoc}
     * @return string
     */
    public function process() {
        $this->xpdo->lexicon->load('goodnews:frontend');
        $settings = $this->getContainerSettings();
        foreach ($settings as $key => $value) {
            $this->xpdo->setPlaceholder($key, $value);
        }
        $this->_content = parent::process();
        return $this->_content;
    }

    /**
     * Get an array of settings from the container (read from modResource properties field -> MODx 2.2+).
     *
     * @return array $settings
     */
    public function getContainerSettings() {
        $container = $this->getOne('ResourceContainer');
        if ($container) {
            $settings = $container->getContainerSettings();
        }
        return is_array($settings) ? $settings : array();
    }

    /**
     * Prevent isLazy error
     *
     * @param string $key
     * @return bool
     */
    public function isLazy($key = '') {
        return false;
    }
}


/**
 * Overrides the modResourceCreateProcessor to provide custom processor functionality
 *
 * @package goodnews
 */
class GoodNewsResourceMailingCreateProcessor extends modResourceCreateProcessor {

    const GON_USER_NOT_YET_SENT = 0;
    const GON_USER_SENT         = 1;
    const GON_USER_SEND_ERROR   = 2;
    const GON_USER_RESERVED     = 4;

    const GON_IPC_STATUS_STOPPED  = 0;
    const GON_IPC_STATUS_STARTED  = 1;

    public $classKey = 'GoodNewsResourceMailing';
    public $languageTopics = array('resource','goodnews:resource');

    /** @var GoodNewsResourceMailing $object */
    public $object;
    
    /** @var boolean $isPublishing */
    public $isPublishing = false;


    public function beforeSet() {
        $this->setProperty('class_key', 'GoodNewsResourceMailing');
        $this->setProperty('searchable', false);
        $this->setProperty('isfolder', false);
        $this->setProperty('cacheable', true);
        $this->setProperty('clearCache', true);        
        return parent::beforeSet();
    }
    
    /**
     * Override modResourceCreateProcessor::beforeSave
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function beforeSave() {
        // Set related meta data for this resource
        $meta = $this->modx->newObject('GoodNewsMailingMeta');
        if (!$meta) {
            return $this->modx->lexicon('resource_err_save');
        }
        
        $nodelist = $this->getProperty('groupscategories');

        $this->prepareGroupsCategories();
        $meta->set('groups', $this->getProperty('groups'));
        $meta->set('categories', $this->getProperty('categories'));

        $this->collectRecipients();
        $meta->set('recipients_list', $this->getProperty('recipients_list'));
        $meta->set('recipients_total', $this->getProperty('recipients_total'));

        $this->object->addOne($meta);   

        if (!$this->parentResource) {
            $this->parentResource = $this->object->getOne('Parent');
        }

        // Copy container properties to mailing object properties
        $container = $this->modx->getObject('GoodNewsResourceContainer', $this->object->get('parent'));
        if ($container) {
            $settings = $container->getProperties('goodnews');
            $this->object->setProperties($settings, 'goodnews');
        }
        
        $this->isPublishing = $this->object->isDirty('published') && $this->object->get('published');
        
        return parent::beforeSave();
    }


    /**
     * Override modResourceCreateProcessor::afterSave
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function afterSave() {
        $this->clearContainerCache();
        return parent::afterSave();
    }

    /**
     * Clears the container cache to ensure that the container listing is updated
     *
     * @return void
     */
    public function clearContainerCache() {
        $this->modx->cacheManager->refresh(array(
            'db' => array(),
            'auto_publish' => array('contexts' => array($this->object->get('context_key'))),
            'context_settings' => array('contexts' => array($this->object->get('context_key'))),
            'resource' => array('contexts' => array($this->object->get('context_key'))),
        ));
    }

    /**
     * Extract and prepare selected groups and categories
     * (e.g. n_gongrp_5,n_goncat_6_5,n_goncat_5_5,n_gongrp_6,n_gongrp_7 )
     * $nodeparts[0] = 'n'
     * $nodeparts[1] = 'gongrp' || 'goncat'
     * $nodeparts[2] = grpID || catID
     * $nodeparts[3] = parent grpID (or empty)
     *
     * @return void
     */
    public function prepareGroupsCategories() {

        $nodes = explode(',', $this->getProperty('groupscategories'));
        
        $groups = array();
        $categories = array();
      
        foreach ($nodes as $node) {
            $nodeparts = explode('_', $node);
            if ($nodeparts[1] == 'gongrp') {
                $groups[] = $nodeparts[2];
            } elseif ($nodeparts[1] == 'goncat') {
                $categories[] = $nodeparts[2];
            }
        }
        $this->setProperty('groups', serialize($groups));
        $this->setProperty('categories', serialize($categories));
    }

    /**
     * Collect recipients based on groups and categories and MODx user groups
     *
     * @return void
     */
    public function collectRecipients() {

        $recipients = array();
        $modxgrouprecipients = array();
        $groups = array();
        $categories = array();
        
        $groups = unserialize($this->getProperty('groups'));
        if (empty($groups)){
            $groups = array('0');
        }
        $categories = unserialize($this->getProperty('categories'));
        if (empty($categories)){
            $categories = array('0');
        }
        
        // Select subscribers based on groups/categories + assigned MODx user groups
        $tblUsers                  = $this->modx->getTableName('modUser');
        $tblUserAttributes         = $this->modx->getTableName('modUserProfile');
        $tblGoodNewsGroupMember    = $this->modx->getTableName('GoodNewsGroupMember');
        $tblGoodNewsCategoryMember = $this->modx->getTableName('GoodNewsCategoryMember');
        
        $groupslist = implode(',', $groups);
        $categorieslist = implode(',', $categories);
        
        $sql = "SELECT DISTINCT {$tblUsers}.id
                FROM {$tblUsers} 
                LEFT JOIN {$tblUserAttributes} ON {$tblUserAttributes}.internalKey = {$tblUsers}.id
                LEFT JOIN {$tblGoodNewsGroupMember} ON {$tblGoodNewsGroupMember}.member_id = {$tblUsers}.id
                LEFT JOIN {$tblGoodNewsCategoryMember} ON {$tblGoodNewsCategoryMember}.member_id = {$tblUsers}.id
                WHERE ({$tblGoodNewsGroupMember}.goodnewsgroup_id IN ({$groupslist}) OR {$tblGoodNewsCategoryMember}.goodnewscategory_id IN ({$categorieslist}))
                AND {$tblUsers}.active = 1 
                AND {$tblUserAttributes}.blocked = 0";

        $query = $this->modx->query($sql);
        if ($query) {
            $users = $query->fetchAll(PDO::FETCH_COLUMN);
        }

        // Initialize each userid with status not_yet_sent + timestamp placeholder 0
        foreach ($users as $id) {
            $recipients[$id] = array(self::GON_USER_NOT_YET_SENT,0);
        }

        $modxgrouprecipients = $this->_collectModxGroupRecipients();
        $recipients += $modxgrouprecipients;
        
        $this->setProperty('recipients_list', serialize($recipients));
        $this->setProperty('recipients_total', count($recipients));
    }

    /**
     * Collect recipients from MODx user groups
     * (if goodnews group is assigned to MODx user group)
     *
     * @return array $modxgrouprecipients
     */
    private function _collectModxGroupRecipients() {

        $modxgrouprecipients = array();
        $groups = array();
        
        $groups = unserialize($this->getProperty('groups'));
        if (empty($groups)){
            $groups = array('0');
        }

        // Select MODx group recipients
        $c = $this->modx->newQuery('modUser');
        $c->leftJoin('modUserProfile', 'Profile');
        $c->innerJoin('modUserGroupMember','UserGroupMembers');
        $c->innerJoin('GoodNewsGroup','Group','UserGroupMembers.user_group = Group.modxusergroup');

        $c->where(array(
            'modUser.active' => true,
            'Profile.blocked' => false,
            'Group.id:IN' => $groups,
        ));

        $c->select($this->modx->getSelectColumns('modUser', 'modUser', '', array('id')));
        //$c->select($this->modx->getSelectColumns('modUserProfile', 'Profile', '', array('blocked')));

        $users = $this->modx->getCollection('modUser', $c);

        // Initialize each userid with status not_yet_sent + timestamp placeholder 0
        foreach ($users as $user) {
            $userID = $user->get('id');            
            $modxgrouprecipients[$userID] = array(self::GON_USER_NOT_YET_SENT,0);
        }
        
        return $modxgrouprecipients;
    }
}

/**
 * Overrides the modResourceUpdateProcessor to provide custom processor functionality
 *
 * @package goodnews
 */
class GoodNewsResourceMailingUpdateProcessor extends modResourceUpdateProcessor {

    const GON_USER_NOT_YET_SENT = 0;
    const GON_USER_SENT         = 1;
    const GON_USER_SEND_ERROR   = 2;
    const GON_USER_RESERVED     = 4;

    const GON_IPC_STATUS_STOPPED  = 0;
    const GON_IPC_STATUS_STARTED  = 1;

    public $classKey = 'GoodNewsResourceMailing';
    public $languageTopics = array('resource','goodnews:resource');

    /** @var GoodNewsResourceMailing $object */
    public $object;

    /** @var boolean $isPublishing */
    public $isPublishing = false;

    /**
     * {@inheritDoc}
     * @return boolean|string
     */
    public function beforeSet() {
        $this->setProperty('clearCache', true);
        return parent::beforeSet();
    }

    /**
     * Override modResourceUpdateProcessor::beforeSave
     * 
     * {@inheritDoc}
     * @return boolean
     */
    public function beforeSave() {
        // Set related meta data for this resource
        $meta = $this->modx->getObject('GoodNewsMailingMeta', array('mailing_id'=>$this->object->get('id')));
        if (!is_object($meta)) {
            $meta = $this->modx->newObject('GoodNewsMailingMeta');
            if (!is_object($meta)) {
                return $this->modx->lexicon('resource_err_save');
            }
        }

        // If sending has already been startet, the resource can't be changed any longer
        // (normaly this shouldn't happen as we don't provide an 'Edit' menu in this case)
        if ($meta->get('recipients_sent') != 0) {
            return $this->modx->lexicon('goodnews.newsletter_err_save_already_sending');
        }

        $nodelist = $this->getProperty('groupscategories');

        $this->prepareGroupsCategories();
        $meta->set('groups', $this->getProperty('groups'));
        $meta->set('categories', $this->getProperty('categories'));

        $this->collectRecipients();
        $meta->set('recipients_list', $this->getProperty('recipients_list'));
        $meta->set('recipients_total', $this->getProperty('recipients_total'));

        $this->object->addOne($meta);   
        
        // Copy container properties to mailing object properties
        $container = $this->modx->getObject('GoodNewsResourceContainer', $this->object->get('parent'));
        if (is_object($container)) {
            $this->object->setProperties($container->getProperties('goodnews'), 'goodnews');
        }
        
        $this->isPublishing = $this->object->isDirty('published') && $this->object->get('published');

        return parent::beforeSave();
    }

    /**
     * Override modResourceUpdateProcessor::afterSave
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function afterSave() {
        $this->clearContainerCache();
        return parent::afterSave();
    }

    /**
     * Clears the container cache to ensure that the container listing is updated
     * @return void
     */
    public function clearContainerCache() {
        $this->modx->cacheManager->refresh(array(
            'db' => array(),
            'auto_publish' => array('contexts' => array($this->object->get('context_key'))),
            'context_settings' => array('contexts' => array($this->object->get('context_key'))),
            'resource' => array('contexts' => array($this->object->get('context_key'))),
        ));
    }

    /**
     * Override cleanup to send back only needed params
     *
     * @return array|string
     */
    public function cleanup() {
        $this->object->removeLock();
        $this->clearCache();

        $returnArray = $this->object->get(array_diff(array_keys($this->object->_fields), array('content','ta','introtext','description','link_attributes','pagetitle','longtitle','menutitle','goodnews_container_settings','properties')));
        foreach ($returnArray as $k => $v) {
            if (strpos($k,'tv') === 0) {
                unset($returnArray[$k]);
            }
            if (strpos($k,'setting_') === 0) {
                unset($returnArray[$k]);
            }
        }
        $returnArray['class_key'] = $this->object->get('class_key');
        $this->workingContext->prepare(true);
        $returnArray['preview_url'] = $this->modx->makeUrl($this->object->get('id'), $this->object->get('context_key'), '', 'full');
        return $this->success('',$returnArray);
    }

    /**
     * Extract and prepare selected groups and categories
     * (e.g. n_gongrp_5,n_goncat_6_5,n_goncat_5_5,n_gongrp_6,n_gongrp_7 )
     * $nodeparts[0] = 'n'
     * $nodeparts[1] = 'gongrp' || 'goncat'
     * $nodeparts[2] = grpID || catID
     * $nodeparts[3] = parent grpID (or empty)
     *
     * @return void
     */
    public function prepareGroupsCategories() {
    
        $nodes = explode(',', $this->getProperty('groupscategories'));
        
        $groups = array();
        $categories = array();
      
        foreach ($nodes as $node) {
            $nodeparts = explode('_', $node);
            if ($nodeparts[1] == 'gongrp') {
                $groups[] = $nodeparts[2];
            } elseif ($nodeparts[1] == 'goncat') {
                $categories[] = $nodeparts[2];
            }
        }
        $this->setProperty('groups', serialize($groups));
        $this->setProperty('categories', serialize($categories));
    }

    /**
     * Collect recipients based on groups and categories and MODx user groups
     *
     * @return void
     */
    public function collectRecipients() {

        $recipients = array();
        $modxgrouprecipients = array();
        $groups = array();
        $categories = array();
        
        $groups = unserialize($this->getProperty('groups'));
        if (empty($groups)){
            $groups = array('0');
        }
        $categories = unserialize($this->getProperty('categories'));
        if (empty($categories)){
            $categories = array('0');
        }
        
        // Select subscribers based on groups/categories + assigned MODx user groups
        $tblUsers                  = $this->modx->getTableName('modUser');
        $tblUserAttributes         = $this->modx->getTableName('modUserProfile');
        $tblGoodNewsGroupMember    = $this->modx->getTableName('GoodNewsGroupMember');
        $tblGoodNewsCategoryMember = $this->modx->getTableName('GoodNewsCategoryMember');
        
        $groupslist = implode(',', $groups);
        $categorieslist = implode(',', $categories);
        
        $sql = "SELECT DISTINCT {$tblUsers}.id
                FROM {$tblUsers} 
                LEFT JOIN {$tblUserAttributes} ON {$tblUserAttributes}.internalKey = {$tblUsers}.id
                LEFT JOIN {$tblGoodNewsGroupMember} ON {$tblGoodNewsGroupMember}.member_id = {$tblUsers}.id
                LEFT JOIN {$tblGoodNewsCategoryMember} ON {$tblGoodNewsCategoryMember}.member_id = {$tblUsers}.id
                WHERE ({$tblGoodNewsGroupMember}.goodnewsgroup_id IN ({$groupslist}) OR {$tblGoodNewsCategoryMember}.goodnewscategory_id IN ({$categorieslist}))
                AND {$tblUsers}.active = 1 
                AND {$tblUserAttributes}.blocked = 0";

        $query = $this->modx->query($sql);
        if ($query) {
            $users = $query->fetchAll(PDO::FETCH_COLUMN);
        }

        // Initialize each userid with status not_yet_sent + timestamp placeholder 0
        foreach ($users as $id) {
            $recipients[$id] = array(self::GON_USER_NOT_YET_SENT,0);
        }

        $modxgrouprecipients = $this->_collectModxGroupRecipients();
        $recipients += $modxgrouprecipients;
        
        $this->setProperty('recipients_list', serialize($recipients));
        $this->setProperty('recipients_total', count($recipients));
    }

    /**
     * Collect recipients from MODx user groups
     * (if goodnews group is assigned to MODx user group)
     *
     * @return array $modxgrouprecipients
     */
    private function _collectModxGroupRecipients() {

        $modxgrouprecipients = array();
        $groups = array();
        
        $groups = unserialize($this->getProperty('groups'));
        if (empty($groups)){
            $groups = array('0');
        }

        // Select MODx group recipients
        $c = $this->modx->newQuery('modUser');
        $c->leftJoin('modUserProfile', 'Profile');
        $c->innerJoin('modUserGroupMember','UserGroupMembers');
        $c->innerJoin('GoodNewsGroup','Group','UserGroupMembers.user_group = Group.modxusergroup');

        $c->where(array(
            'modUser.active' => true,
            'Profile.blocked' => false,
            'Group.id:IN' => $groups,
        ));

        $c->select($this->modx->getSelectColumns('modUser', 'modUser', '', array('id')));
        //$c->select($this->modx->getSelectColumns('modUserProfile', 'Profile', '', array('blocked')));

        $users = $this->modx->getCollection('modUser', $c);

        // Initialize each userid with status not_yet_sent + timestamp placeholder 0
        foreach ($users as $user) {
            $userID = $user->get('id');            
            $modxgrouprecipients[$userID] = array(self::GON_USER_NOT_YET_SENT,0);
        }
        
        return $modxgrouprecipients;
    }
}
