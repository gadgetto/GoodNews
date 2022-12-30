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

/**
 * GoodNewsResourceContainer update controller
 *
 * @package goodnews
 * @subpackage controllers
 */

class GoodNewsResourceContainerUpdateManagerController extends ResourceUpdateManagerController
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
        $this->addJavascript($managerUrl . 'assets/modext/sections/resource/update.js');
        $this->addJavascript($goodNewsJsUrl . 'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl . 'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl . 'res/container/goodnewsresource.panel.container.js');
        $this->addLastJavascript($goodNewsJsUrl . 'res/container/update.js');

        $data = [
            'xtype' => 'goodnewsresource-page-container-update',
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
     * Used to set values on the resource record sent to the template for derivative classes
     *
     * @return void
     */
    public function prepareResource()
    {
        $this->resourceArray['goodnews_container_settings'] = $this->resource->getContainerSettings();
        $settings = $this->resource->getProperties('goodnews');
        if (is_array($settings) && !empty($settings)) {
            foreach ($settings as $k => $v) {
                $this->resourceArray['setting_' . $k] = $v;
            }
        }
    }
}
