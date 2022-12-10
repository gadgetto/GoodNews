<?php
/**
 * GoodNews
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
 * GroupFilter get list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class GroupFilterGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'GoodNewsGroup';
    public $languageTopics = array('goodnews:default');
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'goodnews.group';

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->select(array(
            'GoodNewsGroup.id',
            'GoodNewsGroup.name',
        ));
        // only list groups without(!) assigned MODx user group
        $c->where(array('GoodNewsGroup.modxusergroup' => 0));
        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $resourceArray =  $object->toArray('', false, true, true);
        return $resourceArray;
    }

    public function beforeIteration(array $list) {
        // additional option value "no group assigned"
        if ($this->getProperty('addNoGroupOption', false)) {
            $list[] = array(
                'id' => 'nogroup',
                'name' => $this->modx->lexicon('goodnews.subscribers_no_group'),
            );
        }
        return $list;
    }

}
return 'GroupFilterGetListProcessor';
