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
 * Subscribers update processor (GoodNews categories, groups, testdummy).
 *
 * @package goodnews
 * @subpackage processors
 */

class Update extends Processor
{
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
    public function initialize()
    {
        $this->userid = $this->getProperty('id');
        $this->groupscategories = $this->getProperty('groupscategories');
        $this->testdummy = (bool)$this->getProperty('testdummy');
        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function process()
    {
        // Extract group and category IDs
        // (e.g. n_gongrp_5,n_goncat_6_5,n_goncat_5_5,n_gongrp_6,n_gongrp_7 )
        // $nodeparts[0] = 'n'
        // $nodeparts[1] = 'gongrp' || 'goncat'
        // $nodeparts[2] = grpID || catID
        // $nodeparts[3] = parent grpID (or empty)

        $nodes = explode(',', $this->groupscategories);
        $groups = [];
        $categories = [];

        foreach ($nodes as $node) {
            $nodeparts = explode('_', $node);
            if ($nodeparts[1] == 'gongrp') {
                $groups[] = $nodeparts[2];
            } elseif ($nodeparts[1] == 'goncat') {
                $categories[] = $nodeparts[2];
            }
        }

        // Remove all prior categories of this user
        $result = $this->modx->removeCollection(GoodNewsCategoryMember::class, ['member_id' => $this->userid]);
        if ($result == false && $result != 0) {
            // @todo: return specific error message
            return $this->failure($this->modx->lexicon('user_err_save'));
        }

        // Remove all prior groups of this user
        $result = $this->modx->removeCollection(GoodNewsGroupMember::class, ['member_id' => $this->userid]);
        if ($result == false && $result != 0) {
            // @todo: return specific error message
            return $this->failure($this->modx->lexicon('user_err_save'));
        }

        // Add new groups for this user
        $grpupdate = true;
        foreach ($groups as $group) {
            $gongroupmember = $this->modx->newObject(GoodNewsGroupMember::class);
            $gongroupmember->set('goodnewsgroup_id', $group);
            $gongroupmember->set('member_id', $this->userid);
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
        foreach ($categories as $category) {
            $goncategorymember = $this->modx->newObject(GoodNewsCategoryMember::class);
            $goncategorymember->set('goodnewscategory_id', $category);
            $goncategorymember->set('member_id', $this->userid);
            if (!$goncategorymember->save()) {
                $catupdate = false;
            }
        }
        if (!$catupdate) {
            // @todo: return specific error message
            return $this->failure($this->modx->lexicon('user_err_save'));
        }

        // Write subscriber meta data
        $meta = $this->modx->getObject(GoodNewsSubscriberMeta::class, ['subscriber_id' => $this->userid]);
        if (!is_object($meta)) {
            $meta = $this->modx->newObject(GoodNewsSubscriberMeta::class);
            $meta->set('subscriber_id', $this->userid);
            // Set member subscription date (this is not the creation date of the MODx user!)
            $meta->set('subscribedon', time());
            $meta->set('sid', md5(uniqid(rand() . $this->userid, true)));
            // Set IP field to string 'manually' for later reference
            $meta->set('ip', 'manually');
        }
        $meta->set('testdummy', $this->testdummy);

        if (!$meta->save()) {
            // @todo: return specific error message
            return $this->failure($this->modx->lexicon('user_err_save'));
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
