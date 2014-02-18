<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * Group create processor
 *
 * @package goodnews
 * @subpackage processors
 */

class GroupCreateProcessor extends modObjectCreateProcessor {
    public $classKey = 'GoodNewsGroup';
    public $languageTopics = array('goodnews:default');
    public $objectType = 'goodnews.group';
 
    public function beforeSave() {

        /* make sure a name was specified */
        $name = $this->getProperty('name');
        if (empty($name)) {
            $this->addFieldError('name', $this->modx->lexicon('goodnews.group_err_ns_name'));
        }
        
        /* check if name already exists */
        if ($this->doesAlreadyExist(array('name' => $name))) {
            $this->addFieldError('name', $this->modx->lexicon('goodnews.group_err_ae'));
        }
        
        /* make sure the modx user group isn't already assigned to another GoodNews group */
        $modxusergroup = $this->getProperty('modxusergroup');
        if ($this->doesAlreadyExist(array('modxusergroup' => $modxusergroup)) && $modxusergroup != '0' && $modxusergroup != '') {
            $this->addFieldError('modxusergroup', $this->modx->lexicon('goodnews.group_modxgroup_err_ae'));
        }
        
        $this->object->set('createdon',strftime('%Y-%m-%d %H:%M:%S'));
        $this->object->set('createdby',$this->modx->user->get('id'));

        return parent::beforeSave();
    }
}
return 'GroupCreateProcessor';