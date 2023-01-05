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

namespace Bitego\GoodNews\Processors\Collection;

use MODX\Revolution\Processors\Model\GetListProcessor;
use MODX\Revolution\modResource;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * ParentFilter get list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class ParentFilterGetList extends GetListProcessor
{
    public $classKey = modResource::class;
    public $languageTopics = ['resource', 'goodnews:default'];
    public $defaultSortField = 'pagetitle';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $resourceColumns = [
            'id',
            'pagetitle',
            'context_key',
        ];
        $c->select($this->modx->getSelectColumns(modResource::class, 'modResource', '', $resourceColumns));
        $parentIds = explode(',', $this->getProperty('parentIds', 0));
        $c->where([
            'id:IN' => $parentIds,
        ]);
        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $resourceArray = $object->toArray('', false, true, true);
        $resourceArray['container'] = $resourceArray['pagetitle'] .
            ' - ' .
            $this->modx->lexicon('context') .
            ': ' .
            $resourceArray['context_key'];
        $resourceArray['container'] = htmlentities($resourceArray['container'], ENT_COMPAT, $charset);

        return $resourceArray;
    }

    public function beforeIteration(array $list)
    {
        // additional option value for displaying all resources
        if ($this->getProperty('addAllOption', false)) {
            $list[] = [
                'id' => 'all',
                'container' => $this->modx->lexicon('goodnews.mailing_rc_resources_all'),
            ];
        }
        return $list;
    }
}
