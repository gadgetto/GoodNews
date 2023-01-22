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

use MODX\Revolution\Processors\Processor;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Model\GoodNewsGroupMember;

/**
 * GoodNews processor to assign groups, categories and testdummy flag to a batch of users.
 *
 * @package goodnews
 * @subpackage processors
 */

class AssignMulti extends Processor
{
    /** @var mixed $userIds Array/Comma separated list of user ids */
    public $userIds = 0;

    /** @var string $groupscategories Comma separated list of group and category tags */
    public $groupscategories = '';

    /** @var boolean $testdummy Should user receive test emails? */
    public $testdummy = false;

    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function initialize()
    {
        $this->userIds = $this->getProperty('userIds', '');
        $this->groupscategories = $this->getProperty('groupscategories', '');
        $this->testdummy = (bool)$this->getProperty('testdummy', false);
        $this->replaceGrpsCats = (bool)$this->getProperty('replaceGrpsCats', false);
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

        $groups = [];
        $categories = [];

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

        foreach ($this->userIds as $id) {
            if (empty($id)) {
                continue;
            }

            $groupsToAdd = [];
            $categoriesToAdd = [];
            $prevGroupsMember = [];
            $prevCategoriesMember = [];

            // Ignore previous groups and categories (REPLACE)
            if ($this->replaceGrpsCats) {
                $groupsToAdd = $groups;
                $categoriesToAdd = $categories;
            // Merge previous groups and categories (ADD)
            } else {
                $grpObj = $this->modx->getIterator(GoodNewsGroupMember::class, ['member_id' => $id]);
                foreach ($grpObj as $idx => $grp) {
                    $prevGroupsMember[] .= $grp->get('goodnewsgroup_id');
                }

                $catObj = $this->modx->getIterator(GoodNewsCategoryMember::class, ['member_id' => $id]);
                foreach ($catObj as $idx => $cat) {
                    $prevCategoriesMember[] .= $cat->get('goodnewscategory_id');
                }

                $groupsToAdd = array_merge($prevGroupsMember, $groups);
                // remove duplicates
                $groupsToAdd = array_keys(array_flip($groupsToAdd));

                $categoriesToAdd = array_merge($prevCategoriesMember, $categories);
                // remove duplicates
                $categoriesToAdd = array_keys(array_flip($categoriesToAdd));
            }

            // Remove all prior categories of this user
            $result = $this->modx->removeCollection(GoodNewsCategoryMember::class, ['member_id' => $id]);
            if ($result == false && $result != 0) {
                // @todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }

            // Remove all prior groups of this user
            $result = $this->modx->removeCollection(GoodNewsGroupMember::class, ['member_id' => $id]);
            if ($result == false && $result != 0) {
                // @todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }

            // Add new groups for this user
            $grpupdate = true;
            foreach ($groupsToAdd as $group) {
                $gongroupmember = $this->modx->newObject(GoodNewsGroupMember::class);
                $gongroupmember->set('goodnewsgroup_id', $group);
                $gongroupmember->set('member_id', $id);
                if (!$gongroupmember->save()) {
                    $grpupdate = false;
                }
            }
            if (!$grpupdate) {
                // @todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }

            // Add new categories for this user
            $catupdate = true;
            foreach ($categoriesToAdd as $category) {
                $goncategorymember = $this->modx->newObject(GoodNewsCategoryMember::class);
                $goncategorymember->set('goodnewscategory_id', $category);
                $goncategorymember->set('member_id', $id);
                if (!$goncategorymember->save()) {
                    $catupdate = false;
                }
            }
            if (!$catupdate) {
                // @todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }

            // Write subscriber meta data
            $meta = $this->modx->getObject(GoodNewsSubscriberMeta::class, ['subscriber_id' => $id]);
            if (!is_object($meta)) {
                $meta = $this->modx->newObject(GoodNewsSubscriberMeta::class);
                $meta->set('subscriber_id', $id);
                // Set member subscription date (this is not the creation date of the MODX user!)
                $meta->set('subscribedon', time());
                $meta->set('sid', md5(time() . $id));
                // Set IP field to string 'manually' for later reference
                $meta->set('ip', 'manually');
            }
            $meta->set('testdummy', $this->testdummy);

            if (!$meta->save()) {
                // @todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
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
