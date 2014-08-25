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
 * Subscribers list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class SubscribersGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modUser';
    public $languageTopics = array('user', 'goodnews:default');
    public $defaultSortField = 'Profile.email';
    
    public function initialize() {
        $initialized = parent::initialize();
        $this->setDefaultProperties(array(
            'query' => '',
            'groupfilter' => '',
            'testdummyfilter' => '',
        ));
        return $initialized;
    }
    
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->leftJoin('modUserProfile', 'Profile');
        $c->leftJoin('GoodNewsSubscriberMeta', 'SubscriberMeta', 'modUser.id = SubscriberMeta.subscriber_id');
        
        $query = $this->getProperty('query', '');
        if (!empty($query)) {
            $c->where(array('modUser.username:LIKE' => '%'.$query.'%'));
            $c->orCondition(array('Profile.fullname:LIKE' => '%'.$query.'%'));
            $c->orCondition(array('Profile.email:LIKE' => '%'.$query.'%'));
            $c->orCondition(array('SubscriberMeta.ip:LIKE' => '%'.$query.'%'));
        }

        $groupfilter = $this->getProperty('groupfilter', '');
        if (!empty($groupfilter)) {
            $c->leftJoin('GoodNewsGroupMember', 'GroupMember', 'modUser.id = GroupMember.member_id');
            if ($groupfilter == 'nogroup') {
                $c->where(array('GroupMember.goodnewsgroup_id' => NULL));
            } else {
                $c->where(array('GroupMember.goodnewsgroup_id' => $groupfilter));
            }
        }

        $testdummyfilter = $this->getProperty('testdummyfilter', '');
        if (!empty($testdummyfilter)) {
            if ($testdummyfilter == 'isdummy') {
                $c->where(array('SubscriberMeta.testdummy' => '1'));
            } else {
                $c->where(array('SubscriberMeta.testdummy' => '0'));
                $c->orCondition(array('SubscriberMeta.testdummy' => NULL));
            }
        }

        return $c;
    }

    public function prepareQueryAfterCount(xPDOQuery $c) {
        $c->select($this->modx->getSelectColumns('modUser', 'modUser'));
        $c->select($this->modx->getSelectColumns('modUserProfile', 'Profile', '', array('fullname', 'email')));
        $c->select($this->modx->getSelectColumns('GoodNewsSubscriberMeta', 'SubscriberMeta', '', array('testdummy', 'createdon', 'ip', 'soft_bounces', 'hard_bounces')));
        return $c;
    }

    /**
     * Prepare the row for iteration
     *
     * @access public
     * @param xPDOObject $object
     * @return array $userArray
     */
    public function prepareRow(xPDOObject $object) {
        $userArray = $object->toArray();

        //todo: remove this quickhack and get the count in prepareQueryBeforeCount
        if (!empty($userArray['id'])) {
            // count group subscriptions
            $c = $this->modx->newQuery('GoodNewsGroupMember');
            $c->where(array(
                'member_id' => $userArray['id'],
            ));
            $grpcount = $this->modx->getCount('GoodNewsGroupMember', $c);
            $userArray['grpcount'] = (int)$grpcount;
        }
        
        if ($userArray['testdummy'] == null || $userArray['testdummy'] == '') {
            $userArray['testdummy'] = '-';
        }
        
        if ($userArray['createdon'] == null || $userArray['createdon'] == '') {
            $userArray['createdon'] = '-';
        }
        
        if ($userArray['ip'] == null || $userArray['ip'] == '0') {
            $userArray['ip'] = '-';
        } elseif ($userArray['ip'] == 'unknown') {
            $userArray['ip'] = $this->modx->lexicon('goodnews.subscriber_ip_unknown');
        } elseif ($userArray['ip'] == 'imported') {
            $userArray['ip'] = $this->modx->lexicon('goodnews.subscriber_ip_imported');
        } elseif ($userArray['ip'] == 'manually') {
            $userArray['ip'] = $this->modx->lexicon('goodnews.subscriber_ip_manually');
        }
        
        $softBounces = unserialize($userArray['soft_bounces']);
        if (!is_array($softBounces)) {
            $userArray['soft_bounces'] = 0;
        } else {
            $userArray['soft_bounces'] = count($softBounces);
        }
        
        $hardBounces = unserialize($userArray['hard_bounces']);
        if (!is_array($hardBounces)) {
            $userArray['hard_bounces'] = 0;
        } else {
            $userArray['hard_bounces'] = count($hardBounces);
        }
        
        // security! (we dont want these values in our array)
        unset($userArray['password'], $userArray['cachepwd'], $userArray['salt']);

        return $userArray;
    }

}
return 'SubscribersGetListProcessor';
