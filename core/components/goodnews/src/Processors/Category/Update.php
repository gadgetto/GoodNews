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
use MODX\Revolution\Processors\Model\UpdateProcessor;

/**
 * Category update processor
 *
 * @package goodnews
 * @subpackage processors
 */

class Update extends UpdateProcessor
{
    public $classKey = GoodNewsCategory::class;
    public $languageTopics = ['goodnews:default'];
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

        $this->object->set('editedon', date('Y-m-d H:i:s'));
        $this->object->set('editedby', $this->modx->user->get('id'));

        return parent::beforeSave();
    }

    public function alreadyExists($fieldname, $fieldvalue)
    {
        return $this->modx->getCount($this->classKey, [
            $fieldname => $fieldvalue,
            'id:!=' => $this->getProperty('id'),
        ]) > 0;
    }
}
