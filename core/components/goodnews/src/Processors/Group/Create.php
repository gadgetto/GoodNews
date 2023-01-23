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
use MODX\Revolution\Processors\Model\CreateProcessor;

/**
 * Group create processor
 *
 * @package goodnews
 * @subpackage processors
 */

class Create extends CreateProcessor
{
    public $classKey = GoodNewsGroup::class;
    public $languageTopics = ['goodnews:default'];
    public $objectType = 'goodnews.group';

    public function beforeSave()
    {
        /* make sure a name was specified */
        $name = $this->getProperty('name');
        if (empty($name)) {
            $this->addFieldError('name', $this->modx->lexicon('goodnews.group_err_ns_name'));
        }

        /* check if name already exists */
        if ($this->doesAlreadyExist(['name' => $name])) {
            $this->addFieldError('name', $this->modx->lexicon('goodnews.group_err_ae'));
        }

        /* make sure the modx user group isn't already assigned to another GoodNews group */
        $modxusergroup = $this->getProperty('modxusergroup');
        if (
            $this->doesAlreadyExist(['modxusergroup' => $modxusergroup]) &&
            $modxusergroup != '0' && $modxusergroup != ''
        ) {
            $this->addFieldError('modxusergroup', $this->modx->lexicon('goodnews.group_modxgroup_err_ae'));
        }

        $this->object->set('createdon', date('Y-m-d H:i:s'));
        $this->object->set('createdby', $this->modx->user->get('id'));

        return parent::beforeSave();
    }
}
