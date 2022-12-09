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
 * Category get list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class CategoryGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'GoodNewsCategory';
    public $languageTopics = array('goodnews:default');
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'goodnews.category';

    public function prepareQueryBeforeCount(xPDOQuery $c) {

        $c->select(array(
            'GoodNewsCategory.id',
            'GoodNewsCategory.name',
            'GoodNewsCategory.description',
            'GoodNewsCategory.public',
            'GoodNewsCategory.goodnewsgroup_id',
            'Group.name AS goodnewsgroup_name',
        ));

        $c->leftJoin('GoodNewsGroup', 'Group', 'GoodNewsCategory.goodnewsgroup_id = Group.id');

        $groupfilter = $this->getProperty('groupfilter');
        if (isset($groupfilter)) {
            //todo: can be 0 or associated group was deleted
            if ($groupfilter == '0') {
                $c->where(array('goodnewsgroup_id' => '0'));
            } else {
                $c->where(array('goodnewsgroup_id' => $groupfilter));
            }
        }
        
        $query = $this->getProperty('query');
        if (isset($query)) {
            $c->where(array(
                'GoodNewsCategory.name:LIKE' => '%'.$query.'%',
                'OR:GoodNewsCategory.description:LIKE' => '%'.$query.'%',
                'OR:Group.name:LIKE' => '%'.$query.'%',
            ));
        }
        
        // needed for grouping!
        $c->sortBy('Group.name', 'ASC');
        
        $sortby = $this->getProperty('sortby');
        if (isset($sortby)) {
            $c->sortBy($sortby, 'ASC');
        }
        
        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $resourceArray = parent::prepareRow($object);

        // get category subscribers count
        $c = $this->modx->newQuery('GoodNewsCategoryMember');
        $c->where(array(
            'goodnewscategory_id' => $resourceArray['id'],
        ));
        $membercount = $this->modx->getCount('GoodNewsCategoryMember', $c);
        $resourceArray['membercount'] = (int)$membercount;

        // generate a unique key
        $uniquekey = 'gongcat'.$resourceArray['id'];
        $resourceArray['uniquekey'] = $uniquekey;
        
        return $resourceArray;
    }

}
return 'CategoryGetListProcessor';
