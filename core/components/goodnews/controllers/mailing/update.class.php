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
use Bitego\GoodNews\Model\GoodNewsMailingMeta;

/**
 * GoodNewsResourceMailing update controller
 *
 * @package goodnews
 * @subpackage controllers
 */
class GoodNewsResourceMailingUpdateManagerController extends ResourceUpdateManagerController
{
    /** @var boolean $readOnly Whether or not this Resource is in read-only mode */
    public $readOnly = false;

    /**
     * Register and load custom CSS/JS for the page
     *
     * @return void
     */
    public function loadCustomCssJs()
    {
        $managerUrl = $this->context->getOption('manager_url', null, MODX_MANAGER_URL);
        $modxAssetsUrl = $this->modx->getOption('assets_url', null, MODX_ASSETS_URL);
        $goodNewsAssetsUrl = $this->modx->getOption(
            'goodnews.assets_url',
            null,
            $modxAssetsUrl . 'components/goodnews/'
        );
        $goodNewsJsUrl = $goodNewsAssetsUrl . 'js/';
        $connectorUrl = $goodNewsAssetsUrl . 'connector.php';

        $this->addJavascript($managerUrl . 'assets/modext/widgets/element/modx.panel.tv.renders.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/resource/modx.grid.resource.security.local.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/resource/modx.panel.resource.tv.js');
        $this->addJavascript($managerUrl . 'assets/modext/widgets/resource/modx.panel.resource.js');
        $this->addJavascript($managerUrl . 'assets/modext/sections/resource/update.js');
        $this->addJavascript($goodNewsJsUrl . 'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl . 'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl . 'res/mailing/collect_resources.grid.js');
        $this->addJavascript($goodNewsJsUrl . 'res/mailing/goodnewsresource.panel.mailing.js');
        $this->addLastJavascript($goodNewsJsUrl . 'res/mailing/update.js');

        $data = [
            'xtype' => 'goodnewsresource-page-mailing-update',
            'resource' => $this->resource->get('id'),
            'record' => $this->resourceArray,
            'publish_document' => $this->canPublish,
            'preview_url' => $this->previewUrl,
            'locked' => (int)$this->locked,
            'lockedText' => $this->lockedText,
            'canSave' => (int)$this->canSave,
            'canEdit' => (int)$this->canEdit,
            'canCreate' => (int)$this->canCreate,
            'canCreateRoot' => (int)$this->canCreateRoot,
            'canDuplicate' => (int)$this->canDuplicate,
            'canDelete' => (int)$this->canDelete,
            'show_tvs' => (int)!empty($this->tvCounts),
            'mode' => 'update',
        ];

        $this->addHtml(
            '<script>
            GoodNewsResource.assets_url = "' . $goodNewsAssetsUrl . '";
            GoodNewsResource.connector_url = "' . $connectorUrl . '";
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
     * Custom logic code here for setting placeholders, etc
     *
     * @param array $scriptProperties
     * @return mixed
     */
    public function process(array $scriptProperties = [])
    {
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
        $this->checkForReadOnly();

        return $placeholders;
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
     * Get values from the mailing meta and add to the resourceArray
     *
     * @return void
     */
    public function getMailingMeta()
    {
        $collection1 = '';
        $collection2 = '';
        $collection3 = '';
        $recipientsSent = 0;

        $meta = $this->modx->getObject(GoodNewsMailingMeta::class, ['mailing_id' => $this->resource->get('id')]);
        if (is_object($meta)) {
            $collections = unserialize($meta->get('collections'));
            if (is_array($collections)) {
                $collection1 = implode(',', $collections['collection1']);
                $collection2 = implode(',', $collections['collection2']);
                $collection3 = implode(',', $collections['collection3']);
            }
            $recipientsSent = $meta->get('recipients_sent');
        }
        $this->resourceArray['collection1'] = $collection1;
        $this->resourceArray['collection2'] = $collection2;
        $this->resourceArray['collection3'] = $collection3;
        $this->resourceArray['recipients_sent'] = $recipientsSent;
    }

    /**
     * Check for read-only mode on the Resource
     * (sending has already been started?)
     *
     * @return bool
     */
    public function checkForReadOnly()
    {
        $this->readOnly = false;

        // Sending in progress?
        if ((int)$this->resourceArray['recipients_sent'] > 0) {
            $this->readOnly = true;
        }
        return $this->readOnly;
    }
}
