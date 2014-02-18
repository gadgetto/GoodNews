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
 * Container get list processor (for settings panel)
 *
 * @package goodnews
 * @subpackage processors
 */

class ContainerSettingsGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'GoodNewsResourceContainer';
    public $languageTopics = array('resource','goodnews:default');
    public $checkListPermission = true;
    public $defaultSortField = 'pagetitle';
    public $defaultSortDirection = 'ASC';
    
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->select(array(
            'id',
            'pagetitle',
            'context_key',
            'properties',
        ));
        $c->where(array('class_key' => 'GoodNewsResourceContainer'));
        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $resourceArray = parent::prepareRow($object);

        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $resourceArray['pagetitle'] = htmlentities($resourceArray['pagetitle'], ENT_COMPAT, $charset);
        
        // get properties field as array
        if (!empty($resourceArray['properties'])) {
            $properties = $resourceArray['properties'];
        } else {
            $properties = array();
        }

        // get editorGroups setting
        if (array_key_exists('goodnews', $properties) && array_key_exists('editorGroups', $properties['goodnews'])) {
            $resourceArray['editor_groups'] = $properties['goodnews']['editorGroups'];
        } else {
            $resourceArray['editor_groups'] = '';
        }
        
        return $resourceArray;
    }
}
return 'ContainerSettingsGetListProcessor';
