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
 * GoodNewsResourceContainer create controller
 *
 * @package goodnews
 */

require_once $modx->getOption('manager_path', null, MODX_MANAGER_PATH).'controllers/default/resource/create.class.php';


/**
 * Loads the create GoodNewsResourceContainer page
 *
 * @package goodnews
 * @subpackage controllers
 */
class GoodNewsResourceContainerCreateManagerController extends ResourceCreateManagerController {
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
        $this->prepareResource();
        
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
        $this->addJavascript($managerUrl.'assets/modext/sections/resource/create.js');
        
        $this->addJavascript($goodNewsJsUrl.'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl.'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl.'res/container/goodnewsresource.panel.container.js');
        $this->addLastJavascript($goodNewsJsUrl.'res/container/create.js');   

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
                xtype: "goodnewsresource-page-container-create"
                ,resource: "'.$this->resource->get('id').'"
                ,record: '.$this->modx->toJSON($this->resourceArray).'
                ,publish_document: "'.$this->canPublish.'"
                ,canSave: '.($this->canSave ? 1 : 0).'
                ,canEdit: '.($this->canEdit ? 1 : 0).'
                ,canCreate: '.($this->canCreate ? 1 : 0).'
                ,canDuplicate: '.($this->canDuplicate ? 1 : 0).'
                ,canDelete: '.($this->canDelete ? 1 : 0).'
                ,show_tvs: '.(!empty($this->tvCounts) ? 1 : 0).'
                ,mode: "create"
            });
        });
        // ]]>
        </script>');
        
        // load RTE
        $this->loadRichTextEditor();
    }

    /**
     * Return the pagetitle
     *
     * @return string
     */
    public function getPageTitle() {
        return $this->modx->lexicon('goodnews.container_new');
    }

    
    /**
     * Used to set values on the resource record sent to the template for derivative classes
     *
     * @return void
     */
    public function prepareResource() {
        $settings = $this->resource->getProperties('goodnews');
        if (empty($settings)) $settings = array();
        
        $defaultContainerTemplate = $this->modx->getOption('goodnews.default_container_template', $settings, false);
        if (empty($defaultContainerTemplate)) {
            $template = $this->modx->getObject('modTemplate', array('templatename' => 'sample.GoodNewsContainerTemplate'));
            if ($template) {
                $defaultContainerTemplate = $template->get('id');
            }
        }
        $this->resourceArray['template'] = $defaultContainerTemplate;

        // The following setting can only be edited trough GoodNews system settings!
        // But they need to be initialized here!
        $this->resourceArray['setting_editorGroups'] = 'Administrator';
        
        $mailFrom = $this->modx->getOption('emailsender', $settings, false);
        if (empty($mailFrom)) {
            $mailFrom = 'postmaster@mydomain.com';
        }
        $this->resourceArray['setting_mailFrom'] = $mailFrom;
        
        $mailFromName = $this->modx->getOption('site_name', $settings, false);
        if (empty($mailFromName)) {
            $mailFromName = 'Sender Name';
        }
        $this->resourceArray['setting_mailFromName'] = $mailFromName;

        $mailReplyTo = $this->modx->getOption('emailsender', $settings, false);
        if (empty($mailReplyTo)) {
            $mailReplyTo = 'replyto@mydomain.com';
        }
        $this->resourceArray['setting_mailReplyTo'] = $mailReplyTo;
        
        $this->resourceArray['setting_mailBounceHandling']              = '0';
        $this->resourceArray['setting_mailService']                     = 'imap';
        $this->resourceArray['setting_mailMailHost']                    = '';
        $this->resourceArray['setting_mailMailboxUsername']             = '';
        $this->resourceArray['setting_mailMailboxPassword']             = '';
        $this->resourceArray['setting_mailBoxname']                     = 'INBOX';
        $this->resourceArray['setting_mailPort']                        = '143';
        $this->resourceArray['setting_mailServiceOption']               = 'notls';
        $this->resourceArray['setting_mailSoftBouncedMessageAction']    = 'delete';
        $this->resourceArray['setting_mailSoftMailbox']                 = 'INBOX.Softbounces';
        $this->resourceArray['setting_mailMaxSoftBounces']              = 3;
        $this->resourceArray['setting_mailMaxSoftBouncesAction']        = 'disable';
        $this->resourceArray['setting_mailHardBouncedMessageAction']    = 'delete';
        $this->resourceArray['setting_mailHardMailbox']                 = 'INBOX.Hardbounces';
        $this->resourceArray['setting_mailMaxHardBounces']              = 1;
        $this->resourceArray['setting_mailMaxHardBouncesAction']        = 'delete';
        $this->resourceArray['setting_mailNotClassifiedMessageAction']  = 'move';
        $this->resourceArray['setting_mailNotClassifiedMailbox']        = 'INBOX.NotClassified';

        foreach ($settings as $k => $v) {
            $this->resourceArray['setting_'.$k] = $v;
        }
    }
}
