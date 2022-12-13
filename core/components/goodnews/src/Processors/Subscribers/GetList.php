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

use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Model\GoodNewsGroupMember;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserProfile;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * Subscribers list processor.
 *
 * @package goodnews
 * @subpackage processors
 */

class GetList extends GetListProcessor
{
    public $classKey = modUser::class;
    public $languageTopics = ['user', 'goodnews:default'];
    public $defaultSortField = 'Profile.email';
    
    public function initialize()
    {
        $initialized = parent::initialize();
        $this->setDefaultProperties([
            'query' => '',
            'groupfilter' => '',
            'categoryfilter' => '',
            'testdummyfilter' => '',
            'activefilter' => '',
        ]);
        
        if ($this->getProperty('sort') == 'subscribedon_formatted') {
            $this->setProperty('sort', 'SubscriberMeta.subscribedon');
        }
        
        return $initialized;
    }
    
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->leftJoin(modUserProfile::class, 'Profile');
        $c->leftJoin(GoodNewsSubscriberMeta::class, 'SubscriberMeta', 'modUser.id = SubscriberMeta.subscriber_id');
        
        $query = $this->getProperty('query', '');
        if (!empty($query)) {
            $c->where(['modUser.username:LIKE' => '%' . $query . '%']);
            $c->orCondition(['Profile.fullname:LIKE' => '%' . $query . '%']);
            $c->orCondition(['Profile.email:LIKE' => '%' . $query . '%']);
            $c->orCondition(['SubscriberMeta.ip:LIKE' => '%' . $query . '%']);
            $c->orCondition(['SubscriberMeta.ip_activated:LIKE' => '%' . $query . '%']);
        }
        
        $groupfilter = $this->getProperty('groupfilter', '');
        if (!empty($groupfilter)) {
            $c->leftJoin(GoodNewsGroupMember::class, 'GroupMember', 'modUser.id = GroupMember.member_id');
            if ($groupfilter == 'nogroup') {
                $c->where(['GroupMember.goodnewsgroup_id' => null]);
            } else {
                $c->where(['GroupMember.goodnewsgroup_id' => $groupfilter]);
            }
        }
        
        $categoryfilter = $this->getProperty('categoryfilter', '');
        if (!empty($categoryfilter)) {
            $c->leftJoin(GoodNewsCategoryMember::class, 'CategoryMember', 'modUser.id = CategoryMember.member_id');
            if ($categoryfilter == 'nocategory') {
                $c->where(['CategoryMember.goodnewscategory_id' => null]);
            } else {
                $c->where(['CategoryMember.goodnewscategory_id' => $categoryfilter]);
            }
        }
        
        $testdummyfilter = $this->getProperty('testdummyfilter', '');
        if (!empty($testdummyfilter)) {
            if ($testdummyfilter == 'isdummy') {
                $c->where(['SubscriberMeta.testdummy' => '1']);
            } else {
                $c->where(['SubscriberMeta.testdummy' => '0']);
            }
        }
        
        $activefilter = $this->getProperty('activefilter', '');
        if (!empty($activefilter)) {
            if ($activefilter == 'active') {
                $c->where(['modUser.active' => '1']);
            } else {
                $c->where(['modUser.active' => '0']);
            }
        }
        
        return $c;
    }

    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->select($this->modx->getSelectColumns(modUser::class, 'modUser'));
        $c->select($this->modx->getSelectColumns(modUserProfile::class, 'Profile', '', ['fullname', 'email']));
        $c->select($this->modx->getSelectColumns(GoodNewsSubscriberMeta::class, 'SubscriberMeta', '', ['testdummy', 'subscribedon', 'activatedon', 'ip', 'ip_activated', 'soft_bounces', 'hard_bounces']));
        return $c;
    }

    /**
     * Prepare the row for iteration
     *
     * @access public
     * @param xPDOObject $object
     * @return array $userArray
     */
    public function prepareRow(xPDOObject $object)
    {
        $userArray = $object->toArray();
        
        $managerDateFormat = $this->modx->getOption('manager_date_format', null, 'Y-m-d');
        $managerTimeFormat = $this->modx->getOption('manager_time_format', null, 'H:i');
        $dateTimeFormat = $managerDateFormat . ' ' . $managerTimeFormat;
        
        // @todo: remove this quickhack and get the counts in prepareQueryBeforeCount
        if (!empty($userArray['id'])) {
            // check if user has GoodNews meta data
            $c = $this->modx->newQuery(GoodNewsSubscriberMeta::class);
            $c->where(['subscriber_id' => $userArray['id']]);
            $hasmeta = $this->modx->getCount(GoodNewsSubscriberMeta::class, $c);
            if ($hasmeta) {
                $userArray['hasmeta'] = true;
            } else {
                $userArray['hasmeta'] = false;
            }
            
            // count groups where user is member
            $c = $this->modx->newQuery(GoodNewsGroupMember::class);
            $c->where([
                'member_id' => $userArray['id'],
            ]);
            $grpcount = $this->modx->getCount(GoodNewsGroupMember::class, $c);
            $userArray['grpcount'] = (int)$grpcount;
            
            // count categories where user is member
            $c = $this->modx->newQuery(GoodNewsCategoryMember::class);
            $c->where(['member_id' => $userArray['id']]);
            $catcount = $this->modx->getCount(GoodNewsCategoryMember::class, $c);
            $userArray['catcount'] = (int)$catcount;
        }
        
        if ($userArray['testdummy'] == null || $userArray['testdummy'] == '') {
            $userArray['testdummy'] = '-';
        }
        
        if (empty($userArray['subscribedon'])) {
            $userArray['subscribedon_formatted'] = '-';
        } else {
            // Format timestamp into manager date/time format
            $userArray['subscribedon_formatted'] = date($dateTimeFormat, $userArray['subscribedon']);
        }
        
        if (empty($userArray['activatedon'])) {
            $userArray['activatedon_formatted'] = '-';
        } else {
            // Format timestamp into manager date/time format
            $userArray['activatedon_formatted'] = date($dateTimeFormat, $userArray['activatedon']);
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
        
        if ($userArray['ip_activated'] == null || $userArray['ip_activated'] == '0') {
            $userArray['ip_activated'] = '-';
        } elseif ($userArray['ip_activated'] == 'unknown') {
            $userArray['ip_activated'] = $this->modx->lexicon('goodnews.subscriber_ip_unknown');
        } elseif ($userArray['ip_activated'] == 'imported') {
            $userArray['ip_activated'] = $this->modx->lexicon('goodnews.subscriber_ip_imported');
        } elseif ($userArray['ip_activated'] == 'manually') {
            $userArray['ip_activated'] = $this->modx->lexicon('goodnews.subscriber_ip_manually');
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
