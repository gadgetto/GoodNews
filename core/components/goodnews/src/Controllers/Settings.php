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
use Bitego\GoodNews\Controllers\Base;

/**
 * Settings manager controller class.
 *
 * @param \MODX\Revolution\modX &$modx A reference to the modX object
 * @param array $config An array of configuration options
 * @extends Bitego\GoodNews\Controllers\Base
 * @package goodnews
 * @subpackage controllers
 */
class Settings extends Base
{
    /**
     * {@inheritDoc}
     *
     * @access public
     * @return mixed
     */
    public function initialize()
    {
        if (!$this->goodnews->isGoodNewsAdmin) {
            $returl = $this->modx->getOption('manager_url') . '?a=' . $_GET['a'];
            $this->modx->sendRedirect($returl);
        }
        parent::initialize();
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return void
     */
    public function loadCustomCssJs()
    {
        if ($this->setupErrors) {
            $this->loadErrorPanelCssJs();
            return;
        }
        $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/settings_general.panel.js');
        $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/settings_container.panel.js');
        // $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/settings_bounceparsingrules.panel.js');
        $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/settings_system.panel.js');
        $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/settings_about.panel.js');
        $this->addLastJavascript($this->goodnews->config['jsUrl'] . 'mgr/sections/settings.panel.js');
        $this->addHtml(
            '<script>
            Ext.onReady(function(){
                GoodNews.config = ' . $this->modx->toJSON($this->goodnews->config) . ';
                GoodNews.request = ' . $this->modx->toJSON($_GET) . ';
                MODx.add("goodnews-panel-settings");
            });
            </script>'
        );
    }
}
