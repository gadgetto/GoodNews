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
 * GoodNews Home manager controller
 *
 * @package goodnews
 */

class GoodNewsHomeManagerController extends GoodNewsManagerController {

    public function process(array $scriptProperties = array()) {}
    
    public function getPageTitle() {
        return $this->modx->lexicon('goodnews');
    }
    
    public function getTemplateFile() {
        return '';
    }
    
    public function loadCustomCssJs() {
        
        // load utilities and reusable functions
        $this->addJavascript($this->goodnews->config['jsUrl'].'utils/utilities.js');
        
        // load widgets
        $this->addJavascript($this->goodnews->config['jsUrl'].'mgr/widgets/newsletters.panel.js');
        $this->addJavascript($this->goodnews->config['jsUrl'].'mgr/widgets/categories.panel.js');
        $this->addJavascript($this->goodnews->config['jsUrl'].'mgr/widgets/groups.panel.js');
        $this->addJavascript($this->goodnews->config['jsUrl'].'mgr/widgets/subscribers.panel.js');

        // load home panel widgets container
        $this->addLastJavascript($this->goodnews->config['jsUrl'].'mgr/sections/home.panel.js');

        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function(){
            GoodNews.config = '.$this->modx->toJSON($this->goodnews->config).';
            GoodNews.request = '.$this->modx->toJSON($_GET).';
            Ext.onReady(function(){MODx.add("goodnews-panel-home");});
        });
        </script>');
    }
}
