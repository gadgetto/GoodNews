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
 * GoodNewsResourceMailing create controller
 *
 * @package goodnews
 */

require_once $modx->getOption('manager_path', null, MODX_MANAGER_PATH).'controllers/default/resource/create.class.php';


/**
 * Loads the create GoodNewsResourceMailing page
 *
 * @package goodnews
 * @subpackage controllers
 */
class GoodNewsResourceMailingCreateManagerController extends ResourceCreateManagerController {

    /**
     * Language topics
     *
     * @return array
     */
    public function getLanguageTopics() {
        return array('resource', 'goodnews:resource');
    }

    /**
     * Register and load custom CSS/JS for the page
     *
     * {@inheritDoc}
     * @return void
     */
    public function loadCustomCssJs() {
        $managerUrl        = $this->modx->getOption('manager_url', null, MODX_MANAGER_URL);
        $goodNewsAssetsUrl = $this->modx->getOption('goodnews.assets_url', null, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL).'components/goodnews/');
        $connectorUrl      = $goodNewsAssetsUrl.'connector_res.php';
        $goodNewsJsUrl     = $goodNewsAssetsUrl.'js/';
        
        $this->addJavascript($managerUrl.'assets/modext/util/datetime.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/element/modx.panel.tv.renders.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/resource/modx.grid.resource.security.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/resource/modx.panel.resource.tv.js');
        $this->addJavascript($managerUrl.'assets/modext/widgets/resource/modx.panel.resource.js');
        $this->addJavascript($managerUrl.'assets/modext/sections/resource/create.js');
        
        $this->addJavascript($goodNewsJsUrl.'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl.'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl.'res/mailing/collect_resources.grid.js');
        $this->addJavascript($goodNewsJsUrl.'res/mailing/goodnewsresource.panel.mailing.js');
        $this->addLastJavascript($goodNewsJsUrl.'res/mailing/create.js');   

        $this->addHtml('
        <script type="text/javascript">
        // <![CDATA[
        GoodNewsResource.assets_url = "'.$goodNewsAssetsUrl.'";
        GoodNewsResource.connector_url = "'.$connectorUrl.'";
        MODx.config.publish_document = "'.$this->canPublish.'";
        MODx.onDocFormRender = "'.$this->onDocFormRender.'";
        MODx.ctx = "'.$this->ctx.'";
        Ext.onReady(function() {
            MODx.load({
                xtype: "goodnewsresource-page-mailing-create"
                ,record: '.$this->modx->toJSON($this->resourceArray).'
                ,publish_document: "'.$this->canPublish.'"
                ,canSave: "'.($this->modx->hasPermission('save_document') ? 1 : 0).'"
                ,show_tvs: '.(!empty($this->tvCounts) ? 1 : 0).'
                ,mode: "create"
            });
        });
        // ]]>
        </script>');
        
        /* load RTE */
        $this->loadRichTextEditor();
    }

    /**
     * Return the pagetitle
     *
     * @return string
     */
    public function getPageTitle() {
        return $this->modx->lexicon('goodnews.mailing_new');
    }

    /**
     * Process the mailing resource for output.
     *
     * {@inheritDoc}
     * @return string The processed cacheable content of the mailing.
     */
    public function process(array $scriptProperties = array()) {
        $placeholders = parent::process($scriptProperties);
        $this->resourceArray['published'] = 0;
        $this->getDefaultContainerSettings();
        
        return $placeholders;
    }

    /**
     * Get an array of properties from the container (read from modResource properties field -> MODx 2.2+)
     * and add them to the resourceArray
     *
     * @return void
     */
    public function getDefaultContainerSettings() {
        $container = $this->modx->getObject('GoodNewsResourceContainer', array('id' => $this->parent->get('id')));
        if ($container) {
            $properties = $container->getProperties('goodnews');
            $this->resourceArray['template']           = (int)$this->modx->getOption('mailingTemplate', $properties, 0);
            $this->resourceArray['templatesCategory']  = (int)$this->modx->getOption('templatesCategory', $properties, 0);
            $this->resourceArray['collection1Name']    = $this->modx->getOption('collection1Name', $properties, '');
            $this->resourceArray['collection2Name']    = $this->modx->getOption('collection2Name', $properties, '');
            $this->resourceArray['collection3Name']    = $this->modx->getOption('collection3Name', $properties, '');
            $this->resourceArray['collection1Parents'] = $this->modx->getOption('collection1Parents', $properties, '');
            $this->resourceArray['collection2Parents'] = $this->modx->getOption('collection2Parents', $properties, '');
            $this->resourceArray['collection3Parents'] = $this->modx->getOption('collection3Parents', $properties, '');
        }
    }
}
