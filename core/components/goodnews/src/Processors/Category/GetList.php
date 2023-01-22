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

namespace Bitego\GoodNews\Processors\Category;

use Bitego\GoodNews\Model\GoodNewsGroup;
use Bitego\GoodNews\Model\GoodNewsCategory;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * Categories get list processor.
 *
 * @package goodnews
 * @subpackage processors
 */

class GetList extends GetListProcessor
{
    public $classKey = GoodNewsCategory::class;
    public $languageTopics = ['goodnews:default'];
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'goodnews.category';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->select([
            'GoodNewsCategory.id',
            'GoodNewsCategory.name',
            'GoodNewsCategory.description',
            'GoodNewsCategory.public',
            'GoodNewsCategory.goodnewsgroup_id',
            'Group.name AS goodnewsgroup_name',
        ]);

        $c->leftJoin(GoodNewsGroup::class, 'Group', 'GoodNewsCategory.goodnewsgroup_id = Group.id');

        $groupfilter = $this->getProperty('groupfilter');
        if (isset($groupfilter)) {
            // @todo: can be 0 or associated group was deleted
            if ($groupfilter == '0') {
                $c->where(['goodnewsgroup_id' => '0']);
            } else {
                $c->where(['goodnewsgroup_id' => $groupfilter]);
            }
        }

        $query = $this->getProperty('query');
        if (isset($query)) {
            $c->where([
                'GoodNewsCategory.name:LIKE' => '%' . $query . '%',
                'OR:GoodNewsCategory.description:LIKE' => '%' . $query . '%',
                'OR:Group.name:LIKE' => '%' . $query . '%',
            ]);
        }

        // needed for grouping!
        $c->sortBy('Group.name', 'ASC');

        $sortby = $this->getProperty('sortby');
        if (isset($sortby)) {
            $c->sortBy($sortby, 'ASC');
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $resourceArray = parent::prepareRow($object);

        // get category subscribers count
        $c = $this->modx->newQuery(GoodNewsCategoryMember::class);
        $c->where([
            'goodnewscategory_id' => $resourceArray['id'],
        ]);
        $membercount = $this->modx->getCount(GoodNewsCategoryMember::class, $c);
        $resourceArray['membercount'] = (int)$membercount;

        // generate a unique key
        $uniquekey = 'gongcat' . $resourceArray['id'];
        $resourceArray['uniquekey'] = $uniquekey;

        return $resourceArray;
    }
}
