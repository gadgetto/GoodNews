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

use Bitego\GoodNews\GoodNews;
use MODX\Revolution\modTemplate;

/**
 * Legacy GoodNewsResourceContainer create controller.
 *
 * @package goodnews
 */

class GoodNewsResourceContainerCreateManagerController extends ResourceCreateManagerController
{
    /**
     * Register custom CSS/JS for the page
     *
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->prepareResource();

        $managerUrl = $this->context->getOption('manager_url', null, MODX_MANAGER_URL);
        $modxAssetsUrl = $this->modx->getOption('assets_url', null, MODX_ASSETS_URL);
        $goodNewsAssetsUrl = $this->modx->getOption(
            'goodnews.assets_url',
            null,
            $modxAssetsUrl . 'components/goodnews/'
        );
        $goodNewsJsUrl = $goodNewsAssetsUrl . 'js/';

        $this->addJavascript($managerUrl . 'assets/modext/widgets/element/modx.panel.tv.renders.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/resource/modx.grid.resource.security.local.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/resource/modx.panel.resource.tv.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/resource/modx.panel.resource.js');
        $this->addJavascript($managerUrl . 'assets/modext/sections/resource/create.js');
        $this->addJavascript($goodNewsJsUrl . 'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl . 'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl . 'res/container/goodnewsresource.panel.container.js');
        $this->addLastJavascript($goodNewsJsUrl . 'res/container/create.js');

        $data = [
            'xtype' => 'goodnewsresource-page-container-create',
            'record' => $this->resourceArray,
            'publish_document' => $this->canPublish,
            'canSave' => (int)$this->modx->hasPermission('save_document'),
            'show_tvs' => (int)!empty($this->tvCounts),
            'mode' => 'create',
        ];

        $this->addHtml(
            '<script>
            GoodNewsResource.assets_url = "' . $goodNewsAssetsUrl . '";
            GoodNewsResource.helpUrl = "' . GoodNews::HELP_URL . '";
            MODx.config.publish_document = "' . $this->canPublish . '";
            MODx.onDocFormRender = "' . $this->onDocFormRender . '";
            MODx.ctx = "' . $this->ctx . '";
            Ext.onReady(function() {
                MODx.load(' . json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE) . ')
            });
            </script>'
        );

        $this->loadRichTextEditor();
    }

    /**
     * Specify the language topics to load
     *
     * @return array
     */
    public function getLanguageTopics()
    {
        $languageTopics = parent::getLanguageTopics();
        return array_merge($languageTopics, ['goodnews:resource']);
    }

    /**
     * Return the pagetitle
     *
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
            $settings = [];
        }

        $defaultContainerTemplate = $this->modx->getOption(
            'goodnews.default_container_template',
            $settings,
            false
        );
        if (empty($defaultContainerTemplate)) {
            /** @var modTemplate $template */
            $template = $this->modx->getObject(modTemplate::class, [
                'templatename' => 'sample.GoodNewsContainerTemplate'
            ]);
            if ($template) {
                $defaultContainerTemplate = $template->get('id');
            }
        }

        $this->resourceArray['goodnews_container_settings'] = $this->resource->getContainerSettings();
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
