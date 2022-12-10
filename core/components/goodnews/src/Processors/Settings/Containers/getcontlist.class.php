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
 * GoodNews resource container get list processor for dropdown
 *
 * @package goodnews
 * @subpackage processors
 */

class ResourceContainerGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'GoodNewsResourceContainer';
    public $languageTopics = array('goodnews:default');
    public $checkListPermission = true;
    public $defaultSortField = 'pagetitle';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c) {

        $columns = array(
            'id',
            'pagetitle',
            'class_key',
            'context_key',
        );
        $c->select($this->modx->getSelectColumns('GoodNewsResourceContainer', 'GoodNewsResourceContainer', '', $columns));
        
        $c->where(array('class_key' => 'GoodNewsResourceContainer'));
        $c->where(array('published' => 1));
        $c->where(array('deleted' => 0));
        
        // only return containers the user is assigned to
        $containerIDs = explode(',', $this->getProperty('containerIDs', ''));
        $c->where(array('id:IN' => $containerIDs));

        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $resourceArray = parent::prepareRow($object);

        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $resourceArray['pagetitle'] = htmlentities($resourceArray['pagetitle'], ENT_COMPAT, $charset);

        $resourceArray['name'] = $resourceArray['pagetitle'].' ('.$resourceArray['context_key'].')';
        return $resourceArray;
    }

}
return 'ResourceContainerGetListProcessor';
