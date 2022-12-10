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
 * Group get list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class GroupGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'GoodNewsGroup';
    public $languageTopics = array('goodnews:default');
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'goodnews.group';

    public function prepareQueryBeforeCount(xPDOQuery $c) {

        $c->select(array(
            'GoodNewsGroup.id',
            'GoodNewsGroup.name',
            'GoodNewsGroup.description',
            'GoodNewsGroup.modxusergroup',
            'UserGroup.name AS modxusergroup_name',
        ));

        $c->leftJoin('modUserGroup','UserGroup','GoodNewsGroup.modxusergroup = UserGroup.id');

        // optionally filter out groups with assigned MODx user-group
        if ($this->getProperty('noModxGroups', false)) {
            $c->where(array('GoodNewsGroup.modxusergroup' => '0'));
        }
        
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where(array(
                'GoodNewsGroup.name:LIKE' => '%'.$query.'%',
                'OR:GoodNewsGroup.description:LIKE' => '%'.$query.'%',
                'OR:UserGroup.name:LIKE' => '%'.$query.'%',
            ));
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $resourceArray = parent::prepareRow($object);
        
        // get group subscribers count
        $c = $this->modx->newQuery('GoodNewsGroupMember');
        $c->where(array(
            'goodnewsgroup_id' => $resourceArray['id'],
        ));
        $membercount = $this->modx->getCount('GoodNewsGroupMember', $c);
        $resourceArray['membercount'] = (int)$membercount;
        
        if ($resourceArray['modxusergroup_name'] != '') {
            $resourceArray['membercount'] = '-';
        }

        // generate a unique key
        $uniquekey = 'gongroup'.$resourceArray['id'];
        $resourceArray['uniquekey'] = $uniquekey;
        
        return $resourceArray;
    }
}
return 'GroupGetListProcessor';
