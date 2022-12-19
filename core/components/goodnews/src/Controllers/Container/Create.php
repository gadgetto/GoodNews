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

namespace Bitego\GoodNews\Controllers\Container;

use MODX\Revolution\modX;
use Bitego\GoodNews\GoodNews;
use Bitego\GoodNews\Model\GoodNewsResourceContainer;

require_once $modx->getOption(
    'manager_path',
    null,
    MODX_MANAGER_PATH
) . 'controllers/default/resource/create.class.php';

/**
 * GoodNewsResourceContainer create manager controller.
 *
 * @param \MODX\Revolution\modX &$modx A reference to the modX object
 * @param array $config An array of configuration options
 * @extends ResourceCreateManagerController
 * @package goodnews
 * @subpackage controllers
 */
class Create extends ResourceCreateManagerController
{
    /** @var GoodNewsResourceContainer $resource */
    public $resource;

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->prepareResource();

        $managerUrl = $this->context->getOption(
            'manager_url',
            MODX_MANAGER_URL,
            $this->modx->_userConfig
        );
        $goodNewsAssetsUrl = $this->modx->getOption(
            'goodnews.assets_url',
            null,
            $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/goodnews/'
        );
        $connectorUrl = $goodNewsAssetsUrl . 'connector_res.php';
        $goodNewsJsUrl = $goodNewsAssetsUrl . 'js/';

        $this->resourceArray['goodnews_container_settings'] = $this->resource->getContainerSettings();

        $this->addJavascript($managerUrl . 'assets/modext/util/datetime.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/element/modx.panel.tv.renders.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/resource/modx.grid.resource.security.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/resource/modx.panel.resource.tv.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/resource/modx.panel.resource.js');
        $this->addJavascript($managerUrl . 'assets/modext/sections/resource/create.js');
        $this->addJavascript($goodNewsJsUrl . 'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl . 'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl . 'res/container/goodnewsresource.panel.container.js');
        $this->addLastJavascript($goodNewsJsUrl . 'res/container/create.js');

        $this->addHtml(
            '<script type="text/javascript">
            // <![CDATA[
            GoodNewsResource.assets_url = "' . $goodNewsAssetsUrl . '";
            GoodNewsResource.connector_url = "' . $connectorUrl . '";
            GoodNewsResource.helpUrl = "' . GoodNews::HELP_URL . '";
            MODx.config.publish_document = "' . $this->canPublish . '";
            MODx.onDocFormRender = "' . $this->onDocFormRender . '";
            MODx.ctx = "' . $this->resource->get('context_key') . '";
            Ext.onReady(function() {
                MODx.load({
                    xtype: "goodnewsresource-page-container-create"
                    ,resource: "' . $this->resource->get('id') . '"
                    ,record: ' . $this->modx->toJSON($this->resourceArray) . '
                    ,publish_document: "' . $this->canPublish . '"
                    ,canSave: ' . ($this->canSave ? 1 : 0) . '
                    ,canEdit: ' . ($this->canEdit ? 1 : 0) . '
                    ,canCreate: ' . ($this->canCreate ? 1 : 0) . '
                    ,canDuplicate: ' . ($this->canDuplicate ? 1 : 0) . '
                    ,canDelete: ' . ($this->canDelete ? 1 : 0) . '
                    ,show_tvs: ' . (!empty($this->tvCounts) ? 1 : 0) . '
                    ,mode: "create"
                });
            });
            // ]]>
            </script>'
        );

        $this->loadRichTextEditor();
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['resource', 'goodnews:resource'];
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('goodnews.container_new');
    }


    /**
     * Used to set values on the resource record sent to the template for derivative classes
     *
     * @return void
     */
    public function prepareResource()
    {
        $settings = $this->resource->getProperties('goodnews');
        if (empty($settings)) {
            $settings = array();
        }

        $defaultContainerTemplate = $this->modx->getOption(
            'goodnews.default_container_template',
            $settings,
            false
        );
        if (empty($defaultContainerTemplate)) {
            $template = $this->modx->getObject(
                'modTemplate',
                ['templatename' => 'sample.GoodNewsContainerTemplate']
            );
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

        $this->resourceArray['setting_mailCharset']                     = 'UTF-8';
        $this->resourceArray['setting_mailEncoding']                    = '8bit';
        $this->resourceArray['setting_mailBounceHandling']              = '0';
        $this->resourceArray['setting_mailUseSmtp']                     = '0';
        $this->resourceArray['setting_mailSmtpAuth']                    = '0';
        $this->resourceArray['setting_mailSmtpUser']                    = '';
        $this->resourceArray['setting_mailSmtpPass']                    = '';
        $this->resourceArray['setting_mailSmtpHosts']                   = 'localhost:25';
        $this->resourceArray['setting_mailSmtpPrefix']                  = '';
        $this->resourceArray['setting_mailSmtpKeepalive']               = '0';
        $this->resourceArray['setting_mailSmtpTimeout']                 = 10;
        $this->resourceArray['setting_mailSmtpSingleTo']                = '0';
        $this->resourceArray['setting_mailSmtpHelo']                    = '';
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
        $this->resourceArray['setting_mailMaxHardBouncesAction']        = 'disable';
        $this->resourceArray['setting_mailNotClassifiedMessageAction']  = 'move';
        $this->resourceArray['setting_mailNotClassifiedMailbox']        = 'INBOX.NotClassified';
        $this->resourceArray['setting_collection1Name']                 = '';
        $this->resourceArray['setting_collection1Parents']              = '';
        $this->resourceArray['setting_collection2Name']                 = '';
        $this->resourceArray['setting_collection2Parents']              = '';
        $this->resourceArray['setting_collection3Name']                 = '';
        $this->resourceArray['setting_collection3Parents']              = '';

        foreach ($settings as $k => $v) {
            $this->resourceArray['setting_' . $k] = $v;
        }
    }
}
