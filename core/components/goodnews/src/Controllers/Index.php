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
use MODX\Revolution\modResource;
use Bitego\GoodNews\Controllers\Base;

/**
 * Index manager controller class.
 *
 * @param \MODX\Revolution\modX &$modx A reference to the modX object
 * @param array $config An array of configuration options
 * @extends Bitego\GoodNews\Controllers\Base
 * @package goodnews
 * @subpackage controllers
 */
class Index extends Base
{
    /** @var GoodNewsResourceContainer $container The current mailing ontainer */
    public $container = null;

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return mixed
     */
    public function initialize()
    {
        $container = $this->modx->getObject(modResource::class, $this->goodnews->userCurrentContainer);

        // Normally should not happen here (but just to be sure)
        if (!is_object($container)) {
            $this->goodnews->addSetupError(
                '503 Service Unavailable',
                $this->modx->lexicon('goodnews.error_message_no_container_available'),
                false
            );
        }

        // Security check: is user entitled to manage the requested GoodNews container?
        if (!$this->goodnews->isEditor($container)) {
            $this->goodnews->addSetupError(
                '401 Unauthorized',
                $this->modx->lexicon('goodnews.error_message_unauthorized'),
                false
            );
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
        if (empty($setupErrors)) {
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/newsletters.panel.js');
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/categories.panel.js');
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/groups.panel.js');
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/subscribers.panel.js');
            $this->addLastJavascript($this->goodnews->config['jsUrl'] . 'mgr/sections/home.panel.js');
            $panel = 'goodnews-panel-home';
        } else {
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/error_message.panel.js');
            $this->addLastJavascript($this->goodnews->config['jsUrl'] . 'mgr/sections/error.panel.js');
            $panel = 'goodnews-panel-error';
        }

        $this->addHtml(
            '<script type="text/javascript">
            Ext.onReady(function(){
                GoodNews.config = ' . $this->modx->toJSON($this->goodnews->config) . ';
                GoodNews.request = ' . $this->modx->toJSON($_GET) . ';
                MODx.add("' . $panel . '");
            });
            </script>'
        );
    }
}
