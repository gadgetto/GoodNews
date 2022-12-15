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

namespace Bitego\GoodNews\Processors\Mailing;

use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;
use MODX\Revolution\Processors\Model\GetListProcessor;
use MODX\Revolution\modTemplate;

/**
 * Grabs a list of templates filtered by a category.
 *
 * @package goodnews
 * @subpackage processors
 */

class MailingTemplatesGetList extends GetListProcessor
{
    public $classKey = modTemplate::class;
    public $languageTopics = ['template', 'category'];
    public $defaultSortField = 'templatename';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        // filter by category
        $catid = $this->getProperty('catid', 0);
        if (!empty($catid)) {
            $c->where(['category' => $catid]);
        }
        return $c;
    }

    public function beforeIteration(array $list)
    {
        $empty = [
            'id' => 0,
            'templatename' => $this->modx->lexicon('template_empty'),
            'description' => '',
            'editor_type' => 0,
            'icon' => '',
            'template_type' => 0,
            'content' => '',
            'locked' => false,
        ];
        $list[] = $empty;
        return $list;
    }
}
