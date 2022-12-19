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

namespace Bitego\GoodNews\Model;

use xPDO\xPDO;
use MODX\Revolution\modX;
use MODX\Revolution\modResource;
use Bitego\GoodNews\Model\GoodNewsResourceContainer;
use Bitego\GoodNews\Model\GoodNewsResourceMailing;

/**
 * Class GoodNewsResourceContainer
 *
 * @property \GoodNewsResourceMailing[] $ResourceMailing
 * @package Bitego\GoodNews\Model
 */
class GoodNewsResourceContainer extends modResource
{
    /** @var xPDO $xpdo */
    public $xpdo;
    public $allowListingInClassKeyDropdown = false;
    public $showInContextMenu = true;
    public $allowChildrenResources = false;
    public $oldAlias = null;

    /**
     * Override modResource::__construct to ensure specific fields are forced to be set.
     * @param xPDO $xpdo
     */
    public function __construct(xPDO &$xpdo)
    {
        parent::__construct($xpdo);
        $this->set('class_key', GoodNewsResourceContainer::class);
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
    public static function getControllerPath(xPDO &$modx)
    {
        return $modx->getOption(
            'goodnews.core_path',
            null,
            $modx->getOption('core_path') . 'components/goodnews/'
        ) . 'controllers/container/';
    }

    /**
     * Provide the custom context menu for GoodNews container creation.
     *
     * {@inheritDoc}
     * @return array
     */
    public function getContextMenuText()
    {
        $this->xpdo->lexicon->load('goodnews:resource');
        return [
            'text_create' => $this->xpdo->lexicon('goodnews.container'),
            'text_create_here' => $this->xpdo->lexicon('goodnews.container_create_here'),
        ];
    }

    /**
     * Provide the translated name of this CRT
     * {@inheritDoc}
     * @return string
     */
    public function getResourceTypeName()
    {
        $this->xpdo->lexicon->load('goodnews:resource');
        return $this->xpdo->lexicon('goodnews.container');
    }

    /**
     *
     * @return object
     */
    public function set($k, $v = null, $vType = '')
    {
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
    public function save($cacheFlag = null)
    {
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
    public function updateChildrenURIs($newAlias, $oldAlias)
    {
        $useMultiByte = $this->getOption('use_multibyte', null, false) && function_exists('mb_strlen');
        $encoding = $this->getOption('modx_charset', null, 'UTF-8');

        $oldAliasLength = ($useMultiByte ? mb_strlen($oldAlias, $encoding) : strlen($oldAlias)) + 1;
        $uriField = $this->xpdo->escape('uri');

        $sql = 'UPDATE ' . $this->xpdo->getTableName(GoodNewsResourceMailing::class) . '
            SET ' . $uriField . ' = CONCAT("' . $newAlias . '",SUBSTRING(' . $uriField . ',' . $oldAliasLength . '))
            WHERE
                ' . $this->xpdo->escape('parent') . ' = ' . $this->get('id') . '
            AND SUBSTRING(' . $uriField . ',1,' . $oldAliasLength . ') = "' . $oldAlias . '/"';
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
    public function prepareTreeNode(array $node = array())
    {
        $this->xpdo->lexicon->load('goodnews:resource');

        $idNote = $this->xpdo->hasPermission('tree_show_resource_ids')
            ? ' <span dir="ltr">(' . $this->id . ')</span>'
            : '';

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
        $menu = [];
        $menu[] = [
            'text' => '<b>' . $this->get('pagetitle') . '</b>' . $idNote,
            'handler' => 'Ext.emptyFn',
        ];
        $menu[] = '-';
        $menu[] = [
            'text' => $this->xpdo->lexicon('goodnews.container_manage'),
            'handler' => 'this.editResource',
        ];
        $menu[] = [
            'text' => $this->xpdo->lexicon('goodnews.mailing_create_new'),
            'handler' => "function(itm,e) { 
                var at = this.cm.activeNode.attributes;
                var p = itm.usePk ? itm.usePk : at.pk;
    
                Ext.getCmp('modx-resource-tree').loadAction(
                    'a=resource/create'
                    + '&class_key='+((itm.classKey) ? itm.classKey : 'Bitego\\GoodNews\\Model\\GoodNewsResourceMailing')
                    + '&parent='+p
                    + '&template=" . $template_id . "'
                    + (at.ctx ? '&context_key='+at.ctx : '')
                );
            }",
        ];
        $menu[] = '-';
        if ($this->get('published')) {
            $menu[] = [
                'text' => $this->xpdo->lexicon('goodnews.container_unpublish'),
                'handler' => 'this.unpublishDocument',
            ];
        } else {
            $menu[] = [
                'text' => $this->xpdo->lexicon('goodnews.container_publish'),
                'handler' => 'this.publishDocument',
            ];
        }
        if ($this->get('deleted')) {
            $menu[] = [
                'text' => $this->xpdo->lexicon('goodnews.container_undelete'),
                'handler' => 'this.undeleteDocument',
            ];
        } else {
            $menu[] = [
                'text' => $this->xpdo->lexicon('goodnews.container_delete'),
                'handler' => 'this.deleteDocument',
            ];
        }
        $menu[] = '-';
        $menu[] = [
            'text' => $this->xpdo->lexicon('goodnews.container_view'),
            'handler' => 'this.preview',
        ];
        $menu[] = '-';
        $menu[] = [
            'text' => $this->xpdo->lexicon('goodnews.manage_mailings'),
            'handler' => "function(itm,e) { 
                Ext.getCmp('modx-resource-tree').loadAction(
                    'a=index&namespace=goodnews'
                );
            }",
        ];

        $node['menu'] = ['items' => $menu];
        $node['hasChildren'] = true;

        return $node;
    }


    /**
     * Prevent isLazy error - needed ???
     *
     * @param string $key
     * @return bool
     */
    public function isLazy($key = '')
    {
        return false;
    }

    /**
     * Override modResource::process to set custom placeholders for the Resource when rendering it in front-end.
     *
     * {@inheritDoc}
     * @return string
     */
    public function process()
    {
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
    public function getContainerSettings()
    {
        $settings = $this->getProperties('goodnews');
        if (!empty($settings)) {
            $settings = is_array($settings)
                ? $settings
                : $this->xpdo->fromJSON($settings);
        }
        return !empty($settings) ? $settings : array();
    }
}
