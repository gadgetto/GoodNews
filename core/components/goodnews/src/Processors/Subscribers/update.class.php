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
 * Subscribers update processor (GoodNews categories, groups, testdummy)
 *
 * @package goodnews
 * @subpackage processors
 */

class SubscribersUpdateProcessor extends modProcessor {    

    public $languageTopics = array('user', 'goodnews:default');

    /** @var int $userid The resource id of the user */
    public $userid = 0;
    
    /** @var string $groupscategories Comma separated list of group and category tags */
    public $groupscategories = '';
    
    /** @var boolean $testdummy Is user a testdummy? */
    public $testdummy = false;
    
    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function initialize() {
        $this->userid           = $this->getProperty('id');
        $this->groupscategories = $this->getProperty('groupscategories');
        $this->testdummy        = (bool)$this->getProperty('testdummy');
        return parent::initialize();
    }

	/**
	 * {@inheritDoc}
     * 
     * @return mixed
	 */    
	public function process() {

        // Extract group and category IDs
        // (e.g. n_gongrp_5,n_goncat_6_5,n_goncat_5_5,n_gongrp_6,n_gongrp_7 )
        // $nodeparts[0] = 'n'
        // $nodeparts[1] = 'gongrp' || 'goncat'
        // $nodeparts[2] = grpID || catID
        // $nodeparts[3] = parent grpID (or empty)
        
        $nodes = explode(',', $this->groupscategories);
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
        
        // Remove all prior categories of this user
        $result = $this->modx->removeCollection('GoodNewsCategoryMember', array('member_id' => $this->userid));
        if ($result == false && $result != 0) {
            // todo: return specific error message
            return $this->failure($this->modx->lexicon('user_err_save'));
        }
        
        // Remove all prior groups of this user
        $result = $this->modx->removeCollection('GoodNewsGroupMember', array('member_id' => $this->userid));
        if ($result == false && $result != 0) {
            // todo: return specific error message
            return $this->failure($this->modx->lexicon('user_err_save'));
        }

        // Add new groups for this user
        $grpupdate = true;
        foreach ($groups as $group) {
            $gongroupmember = $this->modx->newObject('GoodNewsGroupMember');
            $gongroupmember->set('goodnewsgroup_id', $group);
            $gongroupmember->set('member_id', $this->userid);
            if (!$gongroupmember->save()) {
                $grpupdate = false;
            }
        }
        if (!$grpupdate) {
            // todo: return specific error message
            return $this->failure($this->modx->lexicon('user_err_save'));
        }
        
        // Add new categories for this user
        $catupdate = true;
        foreach ($categories as $category) {
            $goncategorymember = $this->modx->newObject('GoodNewsCategoryMember');
            $goncategorymember->set('goodnewscategory_id', $category);
            $goncategorymember->set('member_id', $this->userid);
            if (!$goncategorymember->save()) {
                $catupdate = false;
            }
        }
        if (!$catupdate) {
            // todo: return specific error message
            return $this->failure($this->modx->lexicon('user_err_save'));
        }
        
        // Write subscriber meta data
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $this->userid));
        if (!is_object($meta)) {
            $meta = $this->modx->newObject('GoodNewsSubscriberMeta');
            $meta->set('subscriber_id', $this->userid);
            // Set member subscription date (this is not the creation date of the MODx user!)
            $meta->set('subscribedon', time());
            $meta->set('sid', md5(time().$this->userid));
            $meta->set('ip', 'manually');  // Set IP field to string 'manually' for later reference
        }
        $meta->set('testdummy', $this->testdummy);
        
        if (!$meta->save()) {
            // todo: return specific error message
            return $this->failure($this->modx->lexicon('user_err_save'));
        }
        return $this->success();
	}
}
return 'SubscribersUpdateProcessor';
