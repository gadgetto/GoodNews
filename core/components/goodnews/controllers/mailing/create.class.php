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
use Bitego\GoodNews\Model\GoodNewsResourceContainer;

/**
 * GoodNewsResourceMailing create controller
 *
 * @package goodnews
 * @subpackage controllers
 */
class GoodNewsResourceMailingCreateManagerController extends ResourceCreateManagerController
{
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
        $this->addJavascript($managerUrl . 'assets/modext/sections/resource/create.js');
        $this->addJavascript($goodNewsJsUrl . 'utils/utilities.js');
        $this->addJavascript($goodNewsJsUrl . 'res/goodnewsresource.js');
        $this->addJavascript($goodNewsJsUrl . 'res/mailing/collect_resources.grid.js');
        $this->addJavascript($goodNewsJsUrl . 'res/mailing/goodnewsresource.panel.mailing.js');
        $this->addLastJavascript($goodNewsJsUrl . 'res/mailing/create.js');

        $data = [
            'xtype' => 'goodnewsresource-page-mailing-create',
            'record' => $this->resourceArray,
            'publish_document' => $this->canPublish,
            'canSave' => (int)$this->modx->hasPermission('save_document'),
            'show_tvs' => (int)!empty($this->tvCounts),
            'mode' => 'create',
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
        $this->resourceArray['published'] = 0;
        $this->resourceArray['uri_override'] = 0;
        $this->getContainerSettings();
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
     * Return the pagetitle
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('goodnews.mailing_new');
    }

    /**
     * Get an array of properties from parent container (read from modResource properties field -> MODx 2.2+)
     * and add them to the resourceArray
     *
     * @return void
     */
    public function getContainerSettings()
    {
        $container = $this->modx->getObject(GoodNewsResourceContainer::class, ['id' => $this->parent->get('id')]);
        if ($container) {
            $properties = $container->getProperties('goodnews');
            $mailingTemplate = (int)$this->modx->getOption('mailingTemplate', $properties, 0);
            $templatesCategory = (int)$this->modx->getOption('templatesCategory', $properties, 0);
            $collection1Name = $this->modx->getOption('collection1Name', $properties, '');
            $collection2Name = $this->modx->getOption('collection2Name', $properties, '');
            $collection3Name = $this->modx->getOption('collection3Name', $properties, '');
            $collection1Parents = $this->modx->getOption('collection1Parents', $properties, '');
            $collection2Parents = $this->modx->getOption('collection2Parents', $properties, '');
            $collection3Parents = $this->modx->getOption('collection3Parents', $properties, '');
            $this->resourceArray['template']           = $mailingTemplate;
            $this->resourceArray['templatesCategory']  = $templatesCategory;
            $this->resourceArray['collection1Name']    = $collection1Name;
            $this->resourceArray['collection2Name']    = $collection2Name;
            $this->resourceArray['collection3Name']    = $collection3Name;
            $this->resourceArray['collection1Parents'] = $collection1Parents;
            $this->resourceArray['collection2Parents'] = $collection2Parents;
            $this->resourceArray['collection3Parents'] = $collection3Parents;
        }
    }
}
