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

namespace Bitego\GoodNews\Processors\Groups;

use Bitego\GoodNews\Model\GoodNewsGroup;
use Bitego\GoodNews\Model\GoodNewsGroupMember;
use MODX\Revolution\modUserGroup;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * Groups get list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class GetList extends GetListProcessor
{
    public $classKey = GoodNewsGroup::class;
    public $languageTopics = ['goodnews:default'];
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'goodnews.group';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->select([
            'GoodNewsGroup.id',
            'GoodNewsGroup.name',
            'GoodNewsGroup.description',
            'GoodNewsGroup.modxusergroup',
            'UserGroup.name AS modxusergroup_name',
        ]);

        $c->leftJoin(modUserGroup::class, 'UserGroup', 'GoodNewsGroup.modxusergroup = UserGroup.id');

        // optionally filter out groups with assigned MODx user-group
        if ($this->getProperty('noModxGroups', false)) {
            $c->where(array('GoodNewsGroup.modxusergroup' => '0'));
        }

        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where([
                'GoodNewsGroup.name:LIKE' => '%' . $query . '%',
                'OR:GoodNewsGroup.description:LIKE' => '%' . $query . '%',
                'OR:UserGroup.name:LIKE' => '%' . $query . '%',
            ]);
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $resourceArray = parent::prepareRow($object);

        // get group subscribers count
        $c = $this->modx->newQuery(GoodNewsGroupMember::class);
        $c->where([
            'goodnewsgroup_id' => $resourceArray['id'],
        ]);
        $membercount = $this->modx->getCount(GoodNewsGroupMember::class, $c);
        $resourceArray['membercount'] = (int)$membercount;

        if ($resourceArray['modxusergroup_name'] != '') {
            $resourceArray['membercount'] = '-';
        }

        // generate a unique key
        $uniquekey = 'gongroup' . $resourceArray['id'];
        $resourceArray['uniquekey'] = $uniquekey;

        return $resourceArray;
    }
}
