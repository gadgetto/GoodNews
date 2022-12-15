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

namespace Bitego\GoodNews\Processors\Container;

use Bitego\GoodNews\Model\GoodNewsResourceContainer;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * GoodNews resource container GetListProcessor for dropdowns.
 *
 * @var \MODX\Revolution\modX $modx
 * @package goodnews
 * @subpackage processors
 */

class SimpleGetList extends GetListProcessor
{
    public $classKey = GoodNewsResourceContainer::class;
    public $languageTopics = ['goodnews:default'];
    public $checkListPermission = true;
    public $defaultSortField = 'pagetitle';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $columns = [
            'id',
            'pagetitle',
            'class_key',
            'context_key',
        ];
        $c->select(
            $this->modx->getSelectColumns(
                $this->classKey,
                'GoodNewsResourceContainer',
                '',
                $columns
            )
        );
        $c->where(['class_key' => $this->classKey]);
        $c->where(['published' => 1]);
        $c->where(['deleted' => 0]);

        // only return containers the user is assigned to
        $containerIDs = explode(',', $this->getProperty('containerIDs', ''));
        $c->where(['id:IN' => $containerIDs]);

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $resourceArray = parent::prepareRow($object);
        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $resourceArray['pagetitle'] = htmlentities($resourceArray['pagetitle'], ENT_COMPAT, $charset);
        $resourceArray['name'] = $resourceArray['pagetitle'] . ' (' . $resourceArray['context_key'] . ')';
        return $resourceArray;
    }
}
