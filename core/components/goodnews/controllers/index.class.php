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
 * GoodNews Index manager controller
 *
 * @package goodnews
 */

require_once dirname(dirname(__FILE__)) . '/model/goodnews/goodnews.class.php';

class GoodNewsIndexManagerController extends modExtraManagerController {
    
    /** @var GoodNews $goodnews */
    public $goodnews;
    
    public function initialize() {
        $this->goodnews = new GoodNews($this->modx);
        $container = $this->modx->getObject('modResource', $this->goodnews->userCurrentContainer);
        
        // Normally should not happen here (but we stay secure)
        if (!is_object($container)) {
            $this->goodnews->addSetupError('503 Service Unavailable', $this->modx->lexicon('goodnews.error_message_no_container_available'), false);
        }
        
        // Security check: is user entitled to manage the requested GoodNews container?
        if (!$this->goodnews->isEditor($container)) {
            $this->goodnews->addSetupError('401 Unauthorized', $this->modx->lexicon('goodnews.error_message_unauthorized'), false);
        }
        
        // Add custom css file to manager-page header
        $cssFile = $this->goodnews->config['cssUrl'] . 'mgr23.css';
        $this->addCss($cssFile);
        
        // Initialize GoodNews Js
        $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/goodnews.js');
        
        return parent::initialize();
    }
    
    public function process(array $scriptProperties = array()) {}
    
    public function getLanguageTopics() {
        return array('goodnews:default');
    }
        
    public function getPageTitle() {
        return $this->modx->lexicon('goodnews');
    }
    
    public function getTemplateFile() {
        return '';
    }

    public function checkPermissions() {
        return true;
    }
    
    public function loadCustomCssJs() {
        
        // Load utilities and reusable functions
        $this->addJavascript($this->goodnews->config['jsUrl'].'utils/utilities.js');
        
        $setupErrors = $this->goodnews->getSetupErrors();
        
        if (empty($setupErrors)) {
            
            // Load widgets
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/newsletters.panel.js');
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/categories.panel.js');
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/groups.panel.js');
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/subscribers.panel.js');
            
            // Load home panel widgets container
            $this->addLastJavascript($this->goodnews->config['jsUrl'] . 'mgr/sections/home.panel.js');
            
            $this->addHtml('<script type="text/javascript">
            Ext.onReady(function(){
                GoodNews.config = ' . $this->modx->toJSON($this->goodnews->config) . ';
                GoodNews.request = ' . $this->modx->toJSON($_GET) . ';
                MODx.add("goodnews-panel-home");
            });
            </script>');
            
        } else {
            
            // Load widgets
            $this->addJavascript($this->goodnews->config['jsUrl'] . 'mgr/widgets/error_message.panel.js');
            
            // Load error panel widgets container
            $this->addLastJavascript($this->goodnews->config['jsUrl'] . 'mgr/sections/error.panel.js');
            
            $this->addHtml('<script type="text/javascript">
            Ext.onReady(function(){
                GoodNews.config = ' . $this->modx->toJSON($this->goodnews->config) . ';
                GoodNews.request = ' . $this->modx->toJSON($_GET) . ';
                MODx.add("goodnews-panel-error");
            });
            </script>');
        }
    }
}
