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

namespace Bitego\GoodNews\Processors\Group;

use Bitego\GoodNews\Model\GoodNewsGroup;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * GroupFilter get list processor.
 *
 * @package goodnews
 * @subpackage processors
 */

class FilterGetList extends GetListProcessor
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
        ]);
        // only list groups without(!) assigned MODx user group
        $c->where(['GoodNewsGroup.modxusergroup' => 0]);
        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $resourceArray = $object->toArray('', false, true, true);
        return $resourceArray;
    }

    public function beforeIteration(array $list)
    {
        // additional option value "no group assigned"
        if ($this->getProperty('addNoGroupOption', false)) {
            $list[] = [
                'id' => 'nogroup',
                'name' => $this->modx->lexicon('goodnews.subscribers_no_group'),
            ];
        }
        return $list;
    }
}
