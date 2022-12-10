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
 * Category create processor
 *
 * @package goodnews
 * @subpackage processors
 */

class CategoryCreateProcessor extends modObjectCreateProcessor {
    public $classKey = 'GoodNewsCategory';
    public $languageTopics = array('goodnews:default');
    public $objectType = 'goodnews.category';
 
    public function beforeSave() {

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
return 'CategoryCreateProcessor';