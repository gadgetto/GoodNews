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

use MODX\Revolution\Processors\Processor;
use Bitego\GoodNews\Model\GoodNewsMailingMeta;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Model\GoodNewsCategory;
use Bitego\GoodNews\Model\GoodNewsGroup;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Model\GoodNewsGroupMember;

/**
 * Get the GoodNews groups and categories in tree node format
 * and set checked status depending on current userID or current resourceID.
 * (this processor is called once for each GoodNews group node!)
 *
 * @package goodnews
 * @subpackage processors
 */

class GroupCategoryGetNodes extends Processor
{
    /** @var string $userID */
    public $userID;

    /** @var string $resourceID */
    public $resourceID;

    /** @var array $metaGroups */
    public $metaGroups = [];

    /** @var array $metaCategories */
    public $metaCategories = [];

    /** @var string $id The id of the current GoodNewsGroup node */
    public $id;

    /** @var GoodNewsGroup $gonGroup The current GoodNewsGroup object */
    public $gonGroup;

    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function initialize()
    {
        $this->setDefaultProperties([
            'id' => 0,
            'sort' => 'name',
            'dir' => 'ASC',
        ]);
        return true;
    }

    /**
     * Get GoodNews groups and categories in tree node format.
     *
     * @return mixed
     */
    public function process()
    {
        $this->userID = $this->getProperty('userID', 0);
        $this->resourceID = $this->getProperty('resourceID', 0);

        if (!empty($this->resourceID)) {
            $meta = $this->modx->getObject(GoodNewsMailingMeta::class, ['mailing_id' => $this->resourceID]);
            if ($meta) {
                $this->metaGroups = unserialize($meta->get('groups'));
                $this->metaCategories = unserialize($meta->get('categories'));
            }
        }

        // Parse the ID to get the parent group
        $this->id = str_replace('n_gongrp_', '', $this->getProperty('id'));
        $this->setGonGroup();

        $list = [];

        // We have a flat list of groups so this is needed only in first iteration
        if ($this->id == '0') {
            $groups = $this->getGonGroups();
            foreach ($groups['results'] as $group) {
                // if userID is set
                if (!empty($this->userID)) {
                    $groupArray = $this->prepareGonGroupUser($group);
                // if resourceID is set
                } elseif (!empty($this->resourceID)) {
                    $groupArray = $this->prepareGonGroupResource($group);
                // for plain tree
                } else {
                    $groupArray = $this->prepareGonGroup($group);
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
                // if resourceID is set
                } elseif (!empty($this->resourceID)) {
                    $categoryArray = $this->prepareGonCategoryResource($category);
                // for plain tree
                } else {
                    $categoryArray = $this->prepareGonCategory($category);
                }
                if (!empty($categoryArray)) {
                    $list[] = $categoryArray;
                }
            }
        }

        return $this->toJSON($list);
    }

    /**
     * Setter for selected GoodNews group node.
     * @return GoodNewsGroup||null
     */
    public function setGonGroup()
    {
        if (!empty($this->id)) {
            $this->gonGroup = $this->modx->getObject(GoodNewsGroup::class, $this->id);
        }
        return $this->gonGroup;
    }

    /**
     * Get all GoodNews groups.
     *
     * @return array $data (preformatted for ExtJS Json reader)
     */
    public function getGonGroups()
    {
        $data = [];
        $c = $this->modx->newQuery(GoodNewsGroup::class);
        // Filter out additional tree nodes -> groups with assigned MODx user groups
        if (!$this->getProperty('addModxGroups', false)) {
            $c->where([
                'modxusergroup' => 0,
            ]);
        }
        $data['total'] = $this->modx->getCount(GoodNewsGroup::class, $c);
        $c->sortby($this->getProperty('sort'), $this->getProperty('dir'));
        $data['results'] = $this->modx->getCollection(GoodNewsGroup::class, $c);
        return $data;
    }

    /**
     * Get all GoodNews categories by it's parent GoodNews group.
     *
     * @param integer $grpID The id of the GoodNews group
     * @return array $data (preformatted for ExtJS Json reader)
     */
    public function getGonCategories()
    {
        $data = [];
        $c = $this->modx->newQuery(GoodNewsCategory::class);
        $c->where([
            'goodnewsgroup_id' => $this->gonGroup->get('id'),
        ]);
        $data['total'] = $this->modx->getCount(GoodNewsCategory::class, $c);
        $c->sortby('name', 'ASC');
        $data['results'] = $this->modx->getCollection(GoodNewsCategory::class, $c);
        return $data;
    }

    /**
     * Prepare a GoodNews group node for listing based on userID.
     *
     * @param GoodNewsGroup $group
     * @return array
     */
    public function prepareGonGroupUser(GoodNewsGroup $group)
    {
        $c = $this->modx->newQuery(GoodNewsGroupMember::class);
        $c->where([
            'member_id' => $this->userID,
            'goodnewsgroup_id' => $group->get('id'),
        ]);
        if ($this->modx->getCount(GoodNewsGroupMember::class, $c) > 0) {
            $checked = true;
        } else {
            $checked = false;
        }

        if ($group->get('modxusergroup')) {
            $cssClass = 'gonr-modx-group-assigned';
        } else {
            $cssClass = '';
        }
        $iconCls = 'icon-tags';
        return [
            'text'    => $group->get('name'),
            'id'      => 'n_gongrp_' . $group->get('id'),
            'leaf'    => false,
            'type'    => 'gongroup',
            'qtip'    => $group->get('description'),
            'checked' => $checked,
            'iconCls' => $iconCls,
            'cls'     => $cssClass,
        ];
    }

    /**
     * Prepare GoodNews category for listing based on userID.
     *
     * @param GoodNewsCategory $category
     * @return array
     */
    public function prepareGonCategoryUser(GoodNewsCategory $category)
    {
        $c = $this->modx->newQuery(GoodNewsCategoryMember::class);
        $c->where([
            'member_id' => $this->userID,
            'goodnewscategory_id' => $category->get('id'),
        ]);
        if ($this->modx->getCount(GoodNewsCategoryMember::class, $c) > 0) {
            $checked = true;
        } else {
            $checked = false;
        }
        $iconCls = 'icon-tag';
        return [
            'text'    => $category->get('name'),
            'id'      => 'n_goncat_' . $category->get('id') . '_' . $this->gonGroup->get('id'),
            'leaf'    => true,
            'type'    => 'goncategory',
            'qtip'    => $category->get('description'),
            'checked' => $checked,
            'iconCls' => $iconCls,
        ];
    }

    /**
     * Prepare a GoodNews group node for listing based on resourceID.
     *
     * @param GoodNewsGroup $group
     * @return array
     */
    public function prepareGonGroupResource(GoodNewsGroup $group)
    {
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
        $iconCls = 'icon-tags';
        return [
            'text'    => $group->get('name'),
            'id'      => 'n_gongrp_' . $group->get('id'),
            'leaf'    => false,
            'type'    => 'gongroup',
            'qtip'    => $group->get('description'),
            'checked' => $checked,
            'iconCls' => $iconCls,
            'cls'     => $cssClass,
        ];
    }

    /**
     * Prepare GoodNews category for listing based on resourceID.
     *
     * @param GoodNewsCategory $category
     * @return array
     */
    public function prepareGonCategoryResource(GoodNewsCategory $category)
    {
        if (in_array($category->get('id'), $this->metaCategories)) {
            $checked = true;
        } else {
            $checked = false;
        }
        $iconCls = 'icon-tag';
        return [
            'text'    => $category->get('name'),
            'id'      => 'n_goncat_' . $category->get('id') . '_' . $this->gonGroup->get('id'),
            'leaf'    => true,
            'type'    => 'goncategory',
            'qtip'    => $category->get('description'),
            'checked' => $checked,
            'iconCls' => $iconCls,
        ];
    }

    /**
     * Prepare a GoodNews group node for plain listing.
     *
     * @param GoodNewsGroup $group
     * @return array
     */
    public function prepareGonGroup(GoodNewsGroup $group)
    {
        if ($group->get('modxusergroup')) {
            $cssClass = 'gonr-modx-group-assigned';
        } else {
            $cssClass = '';
        }
        $iconCls = 'icon-tags';
        return [
            'text'    => $group->get('name'),
            'id'      => 'n_gongrp_' . $group->get('id'),
            'leaf'    => false,
            'type'    => 'gongroup',
            'qtip'    => $group->get('description'),
            'checked' => false,
            'iconCls' => $iconCls,
            'cls'     => $cssClass,
        ];
    }

    /**
     * Prepare GoodNews category for plain listing.
     *
     * @param GoodNewsCategory $category
     * @return array
     */
    public function prepareGonCategory(GoodNewsCategory $category)
    {
        $iconCls = 'icon-tag';
        return [
            'text'    => $category->get('name'),
            'id'      => 'n_goncat_' . $category->get('id') . '_' . $this->gonGroup->get('id'),
            'leaf'    => true,
            'type'    => 'goncategory',
            'qtip'    => $category->get('description'),
            'checked' => false,
            'iconCls' => $iconCls,
        ];
    }
}
