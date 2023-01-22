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

use Bitego\GoodNews\Model\GoodNewsCategory;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * Category filter get list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class FilterGetList extends GetListProcessor
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
        ]);
        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $resourceArray =  $object->toArray('', false, true, true);
        return $resourceArray;
    }

    public function beforeIteration(array $list)
    {
        // additional option value "no category assigned"
        if ($this->getProperty('addNoCategoryOption', false)) {
            $list[] = [
                'id' => 'nocategory',
                'name' => $this->modx->lexicon('goodnews.subscribers_no_category'),
            ];
        }
        return $list;
    }
}
