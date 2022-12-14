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

namespace Bitego\GoodNews\Processors\Subscribers;

use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Model\GoodNewsGroupMember;

/**
 * GoodNews processor to remove selected groups and categories from a batch of users.
 *
 * @package goodnews
 * @subpackage processors
 */

class RemoveMulti extends Processor
{
    /** @var mixed $userIds Array/Comma separated list of user ids */
    public $userIds = 0;
    
    /** @var string $groupscategories Comma separated list of group and category tags */
    public $groupscategories = '';
    
    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function initialize()
    {
        $this->userIds = $this->getProperty('userIds', '');
        $this->groupscategories = $this->getProperty('groupscategories', '');
        return parent::initialize();
    }
    
    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function process()
    {
        if (empty($this->userIds)) {
            return $this->failure($this->modx->lexicon('goodnews.subscriber_err_ns_multi'));
        }
        $this->userIds = is_array($this->userIds)
            ? $this->userIds
            : explode(',', $this->userIds);
        $nodes = is_array($this->groupscategories)
            ? $this->groupscategories
            : explode(',', $this->groupscategories);
        
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
            if (empty($id)) {
                continue;
            }
            
            // Remove selected categories from this user
            if (!empty($categorieslist)) {
                $result = $this->modx->removeCollection(
                    GoodNewsCategoryMember::class,
                    ['member_id' => $id, '`goodnewscategory_id` IN (' . $categorieslist . ')']
                );
                if ($result == false && $result != 0) {
                    // @todo: return specific error message
                    return $this->failure($this->modx->lexicon('user_err_save'));
                }
            }

            // Remove selected groups from this user
            if (!empty($groupslist)) {
                $result = $this->modx->removeCollection(
                    GoodNewsGroupMember::class,
                    ['member_id' => $id, '`goodnewsgroup_id` IN (' . $groupslist . ')']
                );
                if ($result == false && $result != 0) {
                    // @todo: return specific error message
                    return $this->failure($this->modx->lexicon('user_err_save'));
                }
            }
        }
        return $this->success();
    }
    
    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['user', 'goodnews:default'];
    }
}
