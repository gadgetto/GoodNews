<?php
/**
 * GoodNews
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
 * Get the GoodNews groups and categories in tree node format
 * and set checked status depending on current userID or current resourceID
 * (This processor is called once for each GoodNews group node!)
 *
 * @package goodnews
 * @subpackage processors
 */

class GroupCategoryGetNodesProcessor extends modProcessor {
 
    /** @var string $userID */
    public $userID;
    
    /** @var string $resourceID */
    public $resourceID;

    /** @var array $metaGroups */
    public $metaGroups = array();

    /** @var array $metaCategories */
    public $metaCategories = array();
    
    /** @var string $id The id of the current GoodNewsGroup node */
    public $id;
    
    /** @var GoodNewsGroup $gonGroup The current GoodNewsGroup object */
    public $gonGroup;
    

    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function initialize() {
        // Determine MODX Revo version and set legacy mode (for usage in ExtJS - deprecated connectors since 2.3)
        $version = $this->modx->getVersionData();
        $fullVersion = $version['full_version'];

        $this->setDefaultProperties(array(
            'id'         => 0,
            'sort'       => 'name',
            'dir'        => 'ASC',
            'legacyMode' => version_compare($fullVersion, '2.3.0-dev', '>=') ? false : true,
        ));

        return true;
    }

	/**
	 * Get GoodNews groups and categories in tree node format
     * 
     * @return mixed
	 */    
	public function process() {
 
        $this->userID = $this->getProperty('userID', 0);
        $this->resourceID = $this->getProperty('resourceID', 0);
                
        if (!empty($this->resourceID)) {
            $meta = $this->modx->getObject('GoodNewsMailingMeta', array('mailing_id'=>$this->resourceID));
            if ($meta) {
                $this->metaGroups = unserialize($meta->get('groups'));
                $this->metaCategories = unserialize($meta->get('categories'));
            }
        }
        
        // Parse the ID to get the parent group
        $this->id = str_replace('n_gongrp_', '', $this->getProperty('id'));
        $this->setGonGroup();

        $list = array();
        
        // we have a flat list of groups so this is needed only in first iteration
        if ($this->id == '0') {
            $groups = $this->getGonGroups();
            foreach ($groups['results'] as $group) {
                // if userID is set
                if ($this->userID) {
                    $groupArray = $this->prepareGonGroupUser($group);
                } else {
                    $groupArray = $this->prepareGonGroupResource($group);
                }
                if (!empty($groupArray)) {
                    $list[] = $groupArray;
                }
            }
        }

        if ($this->gonGroup) {
            $categories = $this->getGonCategories();
            foreach ($categories['results'] as $category) {
                // if userID is set
                if ($this->userID) {
                    $categoryArray = $this->prepareGonCategoryUser($category);
                } else {
                    $categoryArray = $this->prepareGonCategoryResource($category);
                }
                if (!empty($categoryArray)) {
                    $list[] = $categoryArray;
                }
            }
        }
        
        return $this->toJSON($list);
	}
	
    /**
     * Setter for selected GoodNews group node
     * @return GoodNewsGroup||null
     */
    public function setGonGroup() {
        if (!empty($this->id)) {
            $this->gonGroup = $this->modx->getObject('GoodNewsGroup', $this->id);
        }
        return $this->gonGroup;
    }

    /**
     * Get all GoodNews groups
     *
     * @return array $data (preformatted for ExtJS Json reader)
     */
    public function getGonGroups() {
        $data = array();
        $c = $this->modx->newQuery('GoodNewsGroup');
        // filter out additional tree nodes -> groups with assigned MODx user groups
        if (!$this->getProperty('addModxGroups', false)) {
            $c->where(array(
                'modxusergroup' => 0,
            ));
        }
        $data['total'] = $this->modx->getCount('GoodNewsGroup', $c);
        $c->sortby($this->getProperty('sort'), $this->getProperty('dir'));
        $data['results'] = $this->modx->getCollection('GoodNewsGroup', $c);
        return $data;
    }

    /**
     * Get all GoodNews categories by it's parent GoodNews group
     *
	 * @param integer $grpID The id of the GoodNews group
     * @return array $data (preformatted for ExtJS Json reader)
     */
    public function getGonCategories() {
        $data = array();
        $c = $this->modx->newQuery('GoodNewsCategory');
        $c->where(array(
			'public' => 1,
			'goodnewsgroup_id' => $this->gonGroup->get('id'),
        ));
        $data['total'] = $this->modx->getCount('GoodNewsCategory', $c);
        $c->sortby('name', 'ASC');
        $data['results'] = $this->modx->getCollection('GoodNewsCategory', $c);
        return $data;
    }

    /**
     * Prepare a GoodNews group node for listing based on userID
     * 
     * @param GoodNewsGroup $group
     * @return array
     */
    public function prepareGonGroupUser(GoodNewsGroup $group) {
        $c = $this->modx->newQuery('GoodNewsGroupMember');
        $c->where(array(
			'member_id' => $this->userID,
			'goodnewsgroup_id' => $group->get('id'),
        ));
        if ($this->modx->getCount('GoodNewsGroupMember', $c) > 0) {
            $checked = true;
        } else {
            $checked = false;
        }
        if ($group->get('modxusergroup')) {
            $cssClass = 'gonr-modx-group-assigned';
        } else {
            $cssClass = '';
        }
        
        if (!$this->getProperty('legacyMode')) {
            // We are on Revo >= 2.3.0
            $iconCls = 'icon-tags';
        } else {
            // We are on Revo < 2.3.0
            $iconCls = 'gonr-icn-group';
        }

        return array(
            'text'    => '<span class="'.$cssClass.'">'.$group->get('name').'</span>',
            'id'      => 'n_gongrp_'.$group->get('id'),
            'leaf'    => false,
            'type'    => 'gongroup',
            'qtip'    => $group->get('description'),
            'checked' => $checked,
            'iconCls' => $iconCls,
        );
    }

    /**
     * Prepare GoodNews category for listing based on userID
     * 
     * @param GoodNewsCategory $category
     * @return array
     */
    public function prepareGonCategoryUser(GoodNewsCategory $category) {
        $c = $this->modx->newQuery('GoodNewsCategoryMember');
        $c->where(array(
			'member_id' => $this->userID,
			'goodnewscategory_id' => $category->get('id'),
        ));
        if ($this->modx->getCount('GoodNewsCategoryMember', $c) > 0) {
            $checked = true;
        } else {
            $checked = false;
        }

        if (!$this->getProperty('legacyMode')) {
            // We are on Revo >= 2.3.0
            $iconCls = 'icon-tag';
        } else {
            // We are on Revo < 2.3.0
            $iconCls = 'gonr-icn-category';
        }

        return array(
            'text'    => $category->get('name'),
            'id'      => 'n_goncat_'.$category->get('id').'_'.$this->gonGroup->get('id'),
            'leaf'    => true,
            'type'    => 'goncategory',
            'qtip'    => $category->get('description'),
            'checked' => $checked,
            'iconCls' => $iconCls,
        );
    }

    /**
     * Prepare a GoodNews group node for listing based on resourceID
     * 
     * @param GoodNewsGroup $group
     * @return array
     */
    public function prepareGonGroupResource(GoodNewsGroup $group) {

        if (in_array($group->get('id'), $this->metaGroups)) {
            $checked = true;
        } else {
            $checked = false;
        }
        
        if ($group->get('modxusergroup')) {
            $cssClass = 'gonr-modx-group-assigned';
        } else {
            $cssClass = '';
        }

        if (!$this->getProperty('legacyMode')) {
            // We are on Revo >= 2.3.0
            $iconCls = 'icon-tags';
        } else {
            // We are on Revo < 2.3.0
            $iconCls = 'gonr-icn-group';
        }
        
        return array(
            'text'    => '<span class="'.$cssClass.'">'.$group->get('name').'</span>',
            'id'      => 'n_gongrp_'.$group->get('id'),
            'leaf'    => false,
            'type'    => 'gongroup',
            'qtip'    => $group->get('description'),
            'checked' => $checked,
            'iconCls' => $iconCls,
        );
    }

    /**
     * Prepare GoodNews category for listing based on resourceID
     * 
     * @param GoodNewsCategory $category
     * @return array
     */
    public function prepareGonCategoryResource(GoodNewsCategory $category) {
        
        if (in_array($category->get('id'), $this->metaCategories)) {
            $checked = true;
        } else {
            $checked = false;
        }
        
        if (!$this->getProperty('legacyMode')) {
            // We are on Revo >= 2.3.0
            $iconCls = 'icon-tag';
        } else {
            // We are on Revo < 2.3.0
            $iconCls = 'gonr-icn-category';
        }
        
        return array(
            'text'    => $category->get('name'),
            'id'      => 'n_goncat_'.$category->get('id').'_'.$this->gonGroup->get('id'),
            'leaf'    => true,
            'type'    => 'goncategory',
            'qtip'    => $category->get('description'),
            'checked' => $checked,
            'iconCls' => $iconCls,
        );
    }

}
return 'GroupCategoryGetNodesProcessor';