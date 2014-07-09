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
 * GoodNews Settings manager controller
 *
 * @package goodnews
 */

class GoodNewsSettingsManagerController extends GoodNewsManagerController {

    public function process(array $scriptProperties = array()) {
        if (!$this->goodnews->isGoodNewsAdmin) {
            $returl = $this->modx->getOption('manager_url').'?a='.$_GET['a'];
            $this->modx->sendRedirect($returl);
        }
    }
    
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
        $this->addJavascript($this->goodnews->config['jsUrl'].'mgr/widgets/settings_general.panel.js');
        $this->addJavascript($this->goodnews->config['jsUrl'].'mgr/widgets/settings_container.panel.js');
        // $this->addJavascript($this->goodnews->config['jsUrl'].'mgr/widgets/settings_bounceparsingrules.panel.js');
        $this->addJavascript($this->goodnews->config['jsUrl'].'mgr/widgets/settings_system.panel.js');
        $this->addJavascript($this->goodnews->config['jsUrl'].'mgr/widgets/settings_about.panel.js');
        
        // load settings panel widgets container
        $this->addLastJavascript($this->goodnews->config['jsUrl'].'mgr/sections/settings.panel.js');

        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function(){
            GoodNews.config = '.$this->modx->toJSON($this->goodnews->config).';
            GoodNews.request = '.$this->modx->toJSON($_GET).';
            Ext.onReady(function(){MODx.add("goodnews-panel-settings");});
        });
        </script>');
    }
}
