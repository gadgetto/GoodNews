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
use MODX\Revolution\Processors\Model\UpdateProcessor;

/**
 * Group update processor
 *
 * @package goodnews
 * @subpackage processors
 */

class Update extends UpdateProcessor
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

        /* if changing name, but new one already exists */
        if ($this->alreadyExists('name', $name)) {
            $this->addFieldError('name', $this->modx->lexicon('goodnews.group_err_ae'));
        }

        /* make sure the modx user group isn't already assigned to another GoodNews group */
        $modxusergroup = $this->getProperty('modxusergroup');
        if ($this->alreadyExists('modxusergroup', $modxusergroup) && $modxusergroup != '0' && $modxusergroup != '') {
            $this->addFieldError('modxusergroup', $this->modx->lexicon('goodnews.group_modxgroup_err_ae'));
        }

        $this->object->set('editedon', strftime('%Y-%m-%d %H:%M:%S'));
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
