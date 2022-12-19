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
 * GoodNewsResourceContainer update controller
 *
 * @package goodnews
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/model/goodnews/goodnews.class.php';
require_once $modx->getOption('manager_path', null, MODX_MANAGER_PATH).'controllers/default/resource/update.class.php';

/**
 * Loads the update GoodNewsResourceContainer page
 *
 * @package goodnews
 * @subpackage controllers
 */
class GoodNewsResourceContainerUpdateManagerController extends ResourceUpdateManagerController {
    /** @var GoodNewsResourceContainer $resource */
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

        $this->resourceArray['goodnews_container_settings'] = $this->resource->getContainerSettings();
        
        $this->addJavascript($managerUrl.'assets/modext/util/datetime.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/element/modx.panel.tv.renders.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/resource/modx.grid.resource.security.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/resource/modx.panel.resource.tv.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/resource/modx.panel.resource.js');
        $this->addJavascript($managerUrl.'assets/modext/sections/resource/update.js');

        $this->addJavascript($goodNewsJsUrl.'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl.'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl.'res/container/goodnewsresource.panel.container.js');
        $this->addLastJavascript($goodNewsJsUrl.'res/container/update.js');
        
        $this->addHtml('
        <script type="text/javascript">
        // <![CDATA[
        GoodNewsResource.assets_url = "'.$goodNewsAssetsUrl.'";
        GoodNewsResource.connector_url = "'.$connectorUrl.'";
        GoodNewsResource.helpUrl = "'.GoodNews::HELP_URL.'";
        MODx.config.publish_document = "'.$this->canPublish.'";
        MODx.onDocFormRender = "'.$this->onDocFormRender.'";
        MODx.ctx = "'.$this->resource->get('context_key').'";
        Ext.onReady(function() {
            MODx.load({
                xtype: "goodnewsresource-page-container-update"
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

        // load RTE
        $this->loadRichTextEditor();
    }

    /**
     * Used to set values on the resource record sent to the template for derivative classes
     *
     * @return void
     */
    public function prepareResource() {
        $settings = $this->resource->getProperties('goodnews');
        if (is_array($settings) && !empty($settings)) {
            foreach ($settings as $k => $v) {
                $this->resourceArray['setting_'.$k] = $v;
            }
        }
    }
}
