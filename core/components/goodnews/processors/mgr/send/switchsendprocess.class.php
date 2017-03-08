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
 * Switch send process ON/OFF processor
 *
 * @package goodnews
 * @subpackage processors
 */
class GoodNewsSwitchSendProcessProcessor extends modObjectProcessor {

    public $languageTopics = array('setting');

    /** @var string $value The state to switch the send process to (can be on off off) */
    public $state = null;
    
    public function initialize() {
        $this->state = $this->getProperty('state');
        return parent::initialize();
    }
    
    public function process() {
 
        $worker_process_active = $this->modx->getObject('modSystemSetting', 'goodnews.worker_process_active');
        if ($this->state == 'on') {
            $worker_process_active->set('value', '1');
        } else {
            $worker_process_active->set('value', '0');
        }

        if ($worker_process_active->save()) {
            // refresh part of cache (MODx 2.1.x) so that settings change immediately takes effect
            $cacheRefreshOptions = array('system_settings' => array());
            $this->modx->cacheManager->refresh($cacheRefreshOptions);
            return $this->success($this->modx->lexicon('goodnews.msg_saving_successfull'));
        } else {
            return $this->modx->error->failure($this->modx->lexicon('setting_err_save'));
        }
    }
}
return 'GoodNewsSwitchSendProcessProcessor';
