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
use MODX\Revolution\Processors\Model\CreateProcessor;

/**
 * Category create processor
 *
 * @package goodnews
 * @subpackage processors
 */

class Create extends CreateProcessor
{
    public $classKey = GoodNewsCategory::class;
    public $languageTopics = array('goodnews:default');
    public $objectType = 'goodnews.category';

    public function beforeSave()
    {
        /* make sure a name was specified */
        $name = $this->getProperty('name');
        if (empty($name)) {
            $this->addFieldError('name', $this->modx->lexicon('goodnews.category_err_ns_name'));
        }

        /* make sure a group was specified */
        $usergroup = $this->getProperty('goodnewsgroup_id');
        if (empty($usergroup)) {
            $this->addFieldError('goodnewsgroup_id', $this->modx->lexicon('goodnews.category_err_ns_group'));
        }

        $this->object->set('createdon', strftime('%Y-%m-%d %H:%M:%S'));
        $this->object->set('createdby', $this->modx->user->get('id'));

        return parent::beforeSave();
    }
}
