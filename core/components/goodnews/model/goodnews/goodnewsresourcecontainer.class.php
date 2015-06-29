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
 * GoodNewsResourceContainer classes
 *
 * @package goodnews
 */

require_once MODX_CORE_PATH.'model/modx/modprocessor.class.php';
require_once MODX_CORE_PATH.'model/modx/processors/resource/create.class.php';
require_once MODX_CORE_PATH.'model/modx/processors/resource/update.class.php';

/**
 * Overrides the modResource class
 *
 * @package goodnews
 */
class GoodNewsResourceContainer extends modResource {
    /** @var modX $xpdo */
    public $xpdo;
    public $allowListingInClassKeyDropdown = false;
    public $showInContextMenu = true;
    public $allowChildrenResources = false;
    public $oldAlias = null;

    /**
     * Override modResource::__construct to ensure specific fields are forced to be set.
     * @param xPDO $xpdo
     */
    function __construct(xPDO & $xpdo) {
        parent :: __construct($xpdo);
        $this->set('class_key', 'GoodNewsResourceContainer');
        $this->set('hide_children_in_tree', true);
    }

    /**
     * Get the controller path for our resource type.
     * 
     * {@inheritDoc}
     * @static
     * @param xPDO $modx
     * @return string
     */
    public static function getControllerPath(xPDO &$modx) {
        return $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'controllers/res/container/';
    }

    /**
     * Provide the custom context menu for GoodNews container creation.
     *
     * {@inheritDoc}
     * @return array
     */
    public function getContextMenuText() {
        $this->xpdo->lexicon->load('goodnews:resource');
        return array(
            'text_create' => $this->xpdo->lexicon('goodnews.container'),
            'text_create_here' => $this->xpdo->lexicon('goodnews.container_create_here'),
        );
    }

    /**
     * Provide the translated name of this CRT
     * {@inheritDoc}
     * @return string
     */
    public function getResourceTypeName() {
        $this->xpdo->lexicon->load('goodnews:resource');
        return $this->xpdo->lexicon('goodnews.container');
    }
    
    /**
     * 
     * @return object
     */
    public function set($k, $v= null, $vType= '') {
        $oldAlias = false;
        if ($k == 'alias') {
            $oldAlias = $this->get('alias');
        }
        $set = parent::set($k, $v, $vType);
        if ($this->isDirty('alias') && !empty($oldAlias)) {
            $this->oldAlias = $oldAlias;
        }
        return $set;
    }

    /**
     * Save new GoodNewsResourceContainer instances to the database.
     *
     * @param boolean $cacheFlag
     * @return boolean
     */
    public function save($cacheFlag = null) {
        $isNew = $this->isNew();
        $saved = parent::save($cacheFlag);
        if ($saved && !$isNew && !empty($this->oldAlias)) {
            $newAlias = $this->get('alias');
            $saved = $this->updateChildrenURIs($newAlias, $this->oldAlias);
        }
        return $saved;
    }
    
    /**
     * Update all child resource URIs to reflect the new container alias
     *
     * @param string $newAlias
     * @param string $oldAlias
     * @return bool
     */
    public function updateChildrenURIs($newAlias, $oldAlias) {
        $useMultiByte = $this->getOption('use_multibyte', null, false) && function_exists('mb_strlen');
        $encoding = $this->getOption('modx_charset', null, 'UTF-8');
        
        $oldAliasLength = ($useMultiByte ? mb_strlen($oldAlias, $encoding) : strlen($oldAlias)) + 1;
        $uriField = $this->xpdo->escape('uri');

        $sql = 'UPDATE '.$this->xpdo->getTableName('GoodNewsResourceMailing').'
            SET '.$uriField.' = CONCAT("'.$newAlias.'",SUBSTRING('.$uriField.','.$oldAliasLength.'))
            WHERE
                '.$this->xpdo->escape('parent').' = '.$this->get('id').'
            AND SUBSTRING('.$uriField.',1,'.$oldAliasLength.') = "'.$oldAlias.'/"';
        $this->xpdo->log(xPDO::LOG_LEVEL_DEBUG, $sql);
        $this->xpdo->exec($sql);
        
        return true;
    }
    
    /**
     * This runs each time the tree is drawn.
     *
     * @param array $node
     * @return array
     */
    public function prepareTreeNode(array $node = array()) {
        $this->xpdo->lexicon->load('goodnews:resource');

        $idNote = $this->xpdo->hasPermission('tree_show_resource_ids') ? ' <span dir="ltr">('.$this->id.')</span>' : '';
        
		// get default mailing template from container properties
		$container = $this->xpdo->getObject('modResource', $this->id);
        $template_id = 0;
		if ($container) {
			$props = $container->get('properties');
			if ($props) {
				if (isset($props['goodnews']['mailingTemplate']) && !empty($props['goodnews']['mailingTemplate'])) {
					$template_id = $props['goodnews']['mailingTemplate'];
				}
			}
		}
		
		// customized tree node menu
        $menu = array();
        $menu[] = array(
            'text' => '<b>'.$this->get('pagetitle').'</b>'.$idNote,
            'handler' => 'Ext.emptyFn',
        );
        $menu[] = '-';
        $menu[] = array(
            'text' => $this->xpdo->lexicon('goodnews.container_manage'),
            'handler' => 'this.editResource',
        );
        $menu[] = array(
            'text' => $this->xpdo->lexicon('goodnews.mailing_create_new'),
            'handler' => "function(itm,e) { 
				var at = this.cm.activeNode.attributes;
		        var p = itm.usePk ? itm.usePk : at.pk;
	
	            Ext.getCmp('modx-resource-tree').loadAction(
	                'a='+MODx.action['resource/create']
	                + '&class_key='+((itm.classKey) ? itm.classKey : 'GoodNewsResourceMailing')
	                + '&parent='+p
	                + '&template=".$template_id."'
	                + (at.ctx ? '&context_key='+at.ctx : '')
                );
        	}",
        );
        $menu[] = '-';
        if ($this->get('published')) {
            $menu[] = array(
                'text' => $this->xpdo->lexicon('goodnews.container_unpublish'),
                'handler' => 'this.unpublishDocument',
            );
        } else {
            $menu[] = array(
                'text' => $this->xpdo->lexicon('goodnews.container_publish'),
                'handler' => 'this.publishDocument',
            );
        }
        if ($this->get('deleted')) {
            $menu[] = array(
                'text' => $this->xpdo->lexicon('goodnews.container_undelete'),
                'handler' => 'this.undeleteDocument',
            );
        } else {
            $menu[] = array(
                'text' => $this->xpdo->lexicon('goodnews.container_delete'),
                'handler' => 'this.deleteDocument',
            );
        }
        $menu[] = '-';
        $menu[] = array(
            'text' => $this->xpdo->lexicon('goodnews.container_view'),
            'handler' => 'this.preview',
        );
        $menu[] = '-';
        $menu[] = array(
            'text' => $this->xpdo->lexicon('goodnews.manage_mailings'),
            'handler' => "function(itm,e) { 
	            Ext.getCmp('modx-resource-tree').loadAction(
	                'a='+MODx.action['goodnews:index']
                );
        	}",
        );

        $node['menu'] = array('items' => $menu);
        $node['hasChildren'] = true;
        
        return $node;
    }


    /**
     * Prevent isLazy error - needed ???
     *
     * @param string $key
     * @return bool
     */
    public function isLazy($key = '') {
        return false;
    }

    /**
     * Override modResource::process to set custom placeholders for the Resource when rendering it in front-end.
     *
     * {@inheritDoc}
     * @return string
     */
    public function process() {
        $this->xpdo->lexicon->load('goodnews:frontend');
        $settings = $this->getContainerSettings();
        foreach ($settings as $key => $value) {
            $this->xpdo->setPlaceholder($key, $value);
        }
        $this->_content = parent::process();
        return $this->_content;
    }
    
    /**
     * Get an array of settings for the container (read from modResource properties field -> MODx 2.2+).
     *
     * @return array
     */
    public function getContainerSettings() {
        $settings = $this->getProperties('goodnews');
        if (!empty($settings)) {
            $settings = is_array($settings) ? $settings : $this->xpdo->fromJSON($settings);
        }
        return !empty($settings) ? $settings : array();
    }
}


/**
 * Overrides the modResourceCreateProcessor to provide custom processor functionality
 *
 * @package goodnews
 */
class GoodNewsResourceContainerCreateProcessor extends modResourceCreateProcessor {
    /** @var GoodNewsResourceContainer $object */
    public $object;
    
    /**
     * Override modResourceCreateProcessor::beforeSave to provide custom functionality
     * (save the container settings to the modResource "properties" field -> MODx 2.2+)
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function beforeSave() {
        $properties = $this->getProperties();
        $settings = $this->object->getProperties('goodnews');
        
        foreach ($properties as $k => $v) {
            if (substr($k, 0, 8) == 'setting_') {
            
                // Remove 'stetting_' prefix
                $key = substr($k, 8);
                // Set all boolean values to 0 || 1
                if ($v === 'false') $v = 0;
                if ($v === 'true') $v = 1;

                $settings[$key] = $v;

                // Remove MODX tag delimiters
                $settings['unsubscribeResource'] = $this->_extractID($settings['unsubscribeResource']);
                $settings['profileResource'] = $this->_extractID($settings['profileResource']); 
            }
        }
        
        $this->object->setProperties($settings, 'goodnews');
        $this->object->set('class_key', 'GoodNewsResourceContainer');
        $this->object->set('cacheable', true);
        $this->object->set('isfolder', true);
        
        return parent::beforeSave();
    }

    /**
     * Override modResourceCreateProcessor::afterSave to provide custom functionality
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function afterSave() {
        $this->setProperty('clearCache', true);
        
        return parent::afterSave();
    }

    private function _extractID($str) {
        $str = str_replace('[[~', '', $str);
        $str = str_replace(']]', '', $str);
        $str = trim($str);
        return $str;
    }
}


/**
 * Overrides the modResourceUpdateProcessor to provide custom processor functionality
 *
 * @package goodnews
 */
class GoodNewsResourceContainerUpdateProcessor extends modResourceUpdateProcessor {
    /** @var GoodNewsResourceContainer $object */
    public $object;

    /**
     * Override modResourceCreateProcessor::beforeSave to provide custom functionality
     * (save the container settings to the modResource "properties" field -> MODx 2.2+)
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function beforeSave() {
        $properties = $this->getProperties();
        $settings = $this->object->getProperties('goodnews');

        foreach ($properties as $k => $v) {
            if (substr($k, 0, 8) == 'setting_') {
            
                // Remove 'stetting_' prefix
                $key = substr($k, 8);
                // Set all boolean values to 0 || 1
                if ($v === 'false') $v = 0;
                if ($v === 'true') $v = 1;

                $settings[$key] = $v;

                // Remove MODX tag delimiters
                $settings['unsubscribeResource'] = $this->_extractID($settings['unsubscribeResource']);
                $settings['profileResource'] = $this->_extractID($settings['profileResource']); 
            }
        }
        
        $this->object->setProperties($settings, 'goodnews');
        
        return parent::beforeSave();
    }

    /**
     * Override modResourceUpdateProcessor::afterSave to provide custom functionality
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function afterSave() {
        $this->setProperty('clearCache', true);
        $this->object->set('isfolder', true);

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

    /**
     * Override cleanup to send back only needed params
     *
     * @return array|string
     */
    public function cleanup() {
        $this->object->removeLock();
        $this->clearCache();

        $returnArray = $this->object->get(array_diff(array_keys($this->object->_fields), array('content','ta','introtext','description','link_attributes','pagetitle','longtitle','menutitle','goodnews_container_settings','properties')));
        foreach ($returnArray as $k => $v) {
            if (strpos($k, 'tv') === 0) {
                unset($returnArray[$k]);
            }
            if (strpos($k, 'setting_') === 0) {
                unset($returnArray[$k]);
            }
        }
        $returnArray['class_key'] = $this->object->get('class_key');
        $this->workingContext->prepare(true);
        $returnArray['preview_url'] = $this->modx->makeUrl($this->object->get('id'), $this->object->get('context_key'), '', 'full');

        return $this->success('', $returnArray);
    }

    private function _extractID($str) {
        $str = str_replace('[[~', '', $str);
        $str = str_replace(']]', '', $str);
        $str = trim($str);
        return $str;
    }
}
