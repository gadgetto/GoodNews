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
 * GoodNewsResourceMailing update controller
 *
 * @package goodnews
 */
 
require_once $modx->getOption('manager_path', null, MODX_MANAGER_PATH).'controllers/default/resource/update.class.php';


/**
 * Loads the update GoodNewsResourceMailing page
 *
 * @package goodnews
 * @subpackage controllers
 */
class GoodNewsResourceMailingUpdateManagerController extends ResourceUpdateManagerController {
    /** @var GoodNewsResourceMailing $resource */
    public $resource;
    
    /**
     * Language topics
     *
     * @return array
     */
    public function getLanguageTopics() {
        return array('resource','goodnews:resource');
    }

    /**
     * Register and load custom CSS/JS for the page
     *
     * {@inheritDoc}
     * @return void
     */
    public function loadCustomCssJs() {
        $managerUrl        = $this->context->getOption('manager_url', MODX_MANAGER_URL, $this->modx->_userConfig);
        $goodNewsAssetsUrl = $this->modx->getOption('goodnews.assets_url', null, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL).'components/goodnews/');
        $connectorUrl      = $goodNewsAssetsUrl.'connector_res.php';
        $goodNewsJsUrl     = $goodNewsAssetsUrl.'js/';
        
        $this->addJavascript($managerUrl.'assets/modext/util/datetime.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/element/modx.panel.tv.renders.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/resource/modx.grid.resource.security.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/resource/modx.panel.resource.tv.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/resource/modx.panel.resource.js');
        $this->addJavascript($managerUrl.'assets/modext/sections/resource/update.js');

        $this->addJavascript($goodNewsJsUrl.'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl.'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl.'res/mailing/collect_resources.grid.js');
        $this->addJavascript($goodNewsJsUrl.'res/mailing/goodnewsresource.panel.mailing.js');
        $this->addLastJavascript($goodNewsJsUrl.'res/mailing/update.js');   

        $this->addHtml('
        <script type="text/javascript">
        // <![CDATA[
        GoodNewsResource.assets_url = "'.$goodNewsAssetsUrl.'";
        GoodNewsResource.connector_url = "'.$connectorUrl.'";
        MODx.config.publish_document = "'.$this->canPublish.'";
        MODx.onDocFormRender = "'.$this->onDocFormRender.'";
        MODx.ctx = "'.$this->resource->get('context_key').'";
        Ext.onReady(function() {
            MODx.load({
                xtype: "goodnewsresource-page-mailing-update"
                ,resource: "'.$this->resource->get('id').'"
                ,record: '.$this->modx->toJSON($this->resourceArray).'
                ,publish_document: "'.$this->canPublish.'"
                ,preview_url: "'.$this->previewUrl.'"
                ,locked: '.($this->locked ? 1 : 0).'
                ,lockedText: "'.$this->lockedText.'"
                ,canSave: '.($this->canSave ? 1 : 0).'
                ,canEdit: '.($this->canEdit ? 1 : 0).'
                ,canCreate: '.($this->canCreate ? 1 : 0).'
                ,canDuplicate: '.($this->canDuplicate ? 1 : 0).'
                ,canDelete: '.($this->canDelete ? 1 : 0).'
                ,show_tvs: '.(!empty($this->tvCounts) ? 1 : 0).'
                ,mode: "update"
            });
        });
        // ]]>
        </script>');
        
        /* load RTE */
        $this->loadRichTextEditor();
    }

    /**
     * Process the mailing resource for output.
     *
     * {@inheritDoc}
     * @return string The processed cacheable content of the mailing.
     */
    public function process(array $scriptProperties = array()) {
        $placeholders = parent::process($scriptProperties);
        $settings = $this->resource->getContainerSettings();
        $this->resourceArray['templatesCategory']  = (int)$this->modx->getOption('templatesCategory', $settings, 0);
        $this->resourceArray['collection1Name']    = $this->modx->getOption('collection1Name', $settings, '');
        $this->resourceArray['collection2Name']    = $this->modx->getOption('collection2Name', $settings, '');
        $this->resourceArray['collection3Name']    = $this->modx->getOption('collection3Name', $settings, '');
        $this->resourceArray['collection1Parents'] = $this->modx->getOption('collection1Parents', $settings, '');
        $this->resourceArray['collection2Parents'] = $this->modx->getOption('collection2Parents', $settings, '');
        $this->resourceArray['collection3Parents'] = $this->modx->getOption('collection3Parents', $settings, '');
        $this->getMailingMeta();

        return $placeholders;
    }

    /**
     * Get values from the mailing meta and add to the resourceArray
     *
     * @return void
     */
    public function getMailingMeta() {
        $meta = $this->modx->getObject('GoodNewsMailingMeta', array('mailing_id' => $this->resource->get('id')));
        if (is_object($meta)) {
            $collections = unserialize($meta->get('collections'));
            if (is_array($collections)) {
                $collection1 = implode(',', $collections['collection1']);
                $collection2 = implode(',', $collections['collection2']);
                $collection3 = implode(',', $collections['collection3']);
                $this->resourceArray['collection1'] = $collection1;
                $this->resourceArray['collection2'] = $collection2;
                $this->resourceArray['collection3'] = $collection3;
            }
        }
    }
}
