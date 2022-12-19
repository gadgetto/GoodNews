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
) . 'controllers/default/resource/update.class.php';

/**
 * GoodNewsResourceContainer update manager controller.
 *
 * @param \MODX\Revolution\modX &$modx A reference to the modX object
 * @param array $config An array of configuration options
 * @extends ResourceCreateManagerController
 * @package goodnews
 * @subpackage controllers
 */
class Update extends ResourceUpdateManagerController
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
        $this->addJavascript($managerUrl . 'assets/modext/sections/resource/update.js');
        $this->addJavascript($goodNewsJsUrl . 'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl . 'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl . 'res/container/goodnewsresource.panel.container.js');
        $this->addLastJavascript($goodNewsJsUrl . 'res/container/update.js');

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
                    xtype: "goodnewsresource-page-container-update"
                    ,resource: "' . $this->resource->get('id') . '"
                    ,record: ' . $this->modx->toJSON($this->resourceArray) . '
                    ,publish_document: "' . $this->canPublish . '"
                    ,preview_url: "' . $this->previewUrl . '"
                    ,locked: ' . ($this->locked ? 1 : 0) . '
                    ,lockedText: "' . $this->lockedText . '"
                    ,canSave: ' . ($this->canSave ? 1 : 0) . '
                    ,canEdit: ' . ($this->canEdit ? 1 : 0) . '
                    ,canCreate: ' . ($this->canCreate ? 1 : 0) . '
                    ,canDuplicate: ' . ($this->canDuplicate ? 1 : 0) . '
                    ,canDelete: ' . ($this->canDelete ? 1 : 0) . '
                    ,show_tvs: ' . (!empty($this->tvCounts) ? 1 : 0) . '
                    ,mode: "update"
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
     * Used to set values on the resource record sent to the template for derivative classes
     *
     * @return void
     */
    public function prepareResource()
    {
        $settings = $this->resource->getProperties('goodnews');
        if (is_array($settings) && !empty($settings)) {
            foreach ($settings as $k => $v) {
                $this->resourceArray['setting_' . $k] = $v;
            }
        }
    }
}
