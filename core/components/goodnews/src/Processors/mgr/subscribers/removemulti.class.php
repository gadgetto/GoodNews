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
 * GoodNews processor to remove selected groups and categories from a batch of users
 *
 * @package goodnews
 * @subpackage processors
 */

class SubscribersRemoveMultiProcessor extends modProcessor {

    public $languageTopics = array('user', 'goodnews:default');

    /** @var mixed $userIds Array/Comma separated list of user ids */
    public $userIds = 0;
    
    /** @var string $groupscategories Comma separated list of group and category tags */
    public $groupscategories = '';
    
    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function initialize() {
        $this->userIds          = $this->getProperty('userIds', '');
        $this->groupscategories = $this->getProperty('groupscategories', '');
        return parent::initialize();
    }

	/**
	 * {@inheritDoc}
     * 
     * @return mixed
	 */    
    public function process() {

        if (empty($this->userIds)) {
            return $this->failure($this->modx->lexicon('goodnews.subscriber_err_ns_multi'));
        }
        $this->userIds = is_array($this->userIds) ? $this->userIds : explode(',', $this->userIds);
        $nodes = is_array($this->groupscategories) ? $this->groupscategories : explode(',', $this->groupscategories);

        // Extract group and category IDs
        // (e.g. n_gongrp_5,n_goncat_6_5,n_goncat_5_5,n_gongrp_6,n_gongrp_7 )
        // $nodeparts[0] = 'n'
        // $nodeparts[1] = 'gongrp' || 'goncat'
        // $nodeparts[2] = grpID || catID
        // $nodeparts[3] = parent grpID (or empty)

        $groups = array();
        $categories = array();

        foreach ($nodes as $node) {
            $nodeparts = explode('_', $node);
            if (!empty($nodeparts[1])) {
                if ($nodeparts[1] == 'gongrp') {
                    $groups[] = $nodeparts[2];
                } elseif ($nodeparts[1] == 'goncat') {
                    $categories[] = $nodeparts[2];
                }
            }
        }
        
        $groupslist     = implode(',', $groups);
        $categorieslist = implode(',', $categories);

        foreach ($this->userIds as $id) {
            if (empty($id)) { continue; }
                        
            // Remove selected categories from this user
            if (!empty($categorieslist)) {
                $result = $this->modx->removeCollection('GoodNewsCategoryMember', array('member_id' => $id, '`goodnewscategory_id` IN ('.$categorieslist.')'));
                if ($result == false && $result != 0) {
                    // @todo: return specific error message
                    return $this->failure($this->modx->lexicon('user_err_save'));
                }
            }

            // Remove selected groups from this user
            if (!empty($groupslist)) {
                $result = $this->modx->removeCollection('GoodNewsGroupMember', array('member_id' => $id, '`goodnewsgroup_id` IN ('.$groupslist.')'));
                if ($result == false && $result != 0) {
                    // @todo: return specific error message
                    return $this->failure($this->modx->lexicon('user_err_save'));
                }
            }
        }
        return $this->success();
    }
}
return 'SubscribersRemoveMultiProcessor';
