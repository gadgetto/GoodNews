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

namespace Bitego\GoodNews\Controllers;

use MODX\Revolution\modX;
use MODX\Revolution\modExtraManagerController;

/**
 * Base manager controller class.
 *
 * @param \MODX\Revolution\modX &$modx A reference to the modX object
 * @param array $config An array of configuration options
 * @extends MODX\Revolution\modExtraManagerController
 * @package goodnews
 * @subpackage controllers
 */
class Base extends modExtraManagerController
{
    /** @var GoodNews $goodnews */
    public $goodnews = null;

    /** @var array $setupErrors The setup error stack */
    public $setupErrors = [];

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return void
     */
    public function __construct(modX $modx, $config = [])
    {
        parent::__construct($modx, $config);
        $this->goodnews = $this->modx->services->get('goodnews');
        $this->setupErrors = $this->goodnews->getSetupErrors();
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return mixed
     */
    public function initialize()
    {
        $this->addCss($this->goodnews->config['cssUrl'] . 'mgr.css');
        $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/goodnews.js');
        $this->addJavascript($this->goodnews->config['jsUrl'] . 'utils/utilities.js');
        parent::initialize();
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['goodnews:default'];
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @returns array
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('goodnews');
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @returns string
     */
    public function getTemplateFile()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @returns boolean
     */
    public function checkPermissions()
    {
        return true;
    }

    /**
     * Load the error panel
     *
     * @access public
     * @return void
     */
    public function loadErrorPanelCssJs()
    {
        $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/error_message.panel.js');
        $this->addLastJavascript($this->goodnews->config['jsUrl'] . 'mgr/sections/error.panel.js');
        $this->addHtml(
            '<script>
            Ext.onReady(function(){
                GoodNews.config = ' . $this->modx->toJSON($this->goodnews->config) . ';
                GoodNews.request = ' . $this->modx->toJSON($_GET) . ';
                MODx.add("goodnews-panel-error");
            });
            </script>'
        );
    }
}
