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
 * Container definition update processor
 *
 * @package goodnews
 * @subpackage processors
 */

class ContainerSettingsUpdateProcessor extends modObjectUpdateProcessor {
    public $classKey = 'modResource';
    public $languageTopics = array('resource','goodnews:default');
    public $permission = 'save_document';
    public $objectType = 'resource';
    public $beforeSaveEvent = 'OnBeforeDocFormSave';
    public $afterSaveEvent = 'OnDocFormSave';

    public function beforeSave() {

        // make sure editor_groups was specified
        $editorGroups = $this->getProperty('editor_groups');
        if (empty($editorGroups)) {
            $this->addFieldError('editor_groups', $this->modx->lexicon('goodnews.settings_container_err_ns_editor_groups'));
        }
        
        $this->object->setProperty('editorGroups', $editorGroups, 'goodnews');
        $this->object->set('editedby', $this->modx->user->get('id'));
        $this->object->set('editedon', time(), 'integer');
        
        return parent::beforeSave();
    }

    public function afterSave() {
        $this->setProperty('clearCache', true);

        // update properties of all child resources (merge with existing properties)
        $parentProperties = $this->object->getProperties('goodnews');

        foreach ($this->object->getIterator('Children') as $child) {
            $child->setProperties($parentProperties, 'goodnews');
            if (!$child->save()) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, "Could not change properties of child resource {$child->get('id')}", '', __METHOD__, __FILE__, __LINE__);
            }
        }

        return parent::afterSave();
    }

}
return 'ContainerSettingsUpdateProcessor';