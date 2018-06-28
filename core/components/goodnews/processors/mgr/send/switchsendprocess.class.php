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

    /** @var string $emergencystop The state to switch the send process to (can be "true" "false") */
    public $emergencystop = null;
    
    public function initialize() {
        $this->emergencystop = $this->getProperty('emergencystop');
        return parent::initialize();
    }
    
    public function process() {
 
        $worker_process_active = $this->modx->getObject('modSystemSetting', 'goodnews.worker_process_active');

        // Toggle button is pressed (means emergency stopp for send process!)
        if ($this->emergencystop == "true") {
            $worker_process_active->set('value', '0');
        // Toggle button is not pressed
        } else {
            $worker_process_active->set('value', '1');
        }

        if ($worker_process_active->save()) {
            // refresh part of cache (MODx 2.1.x) so that settings change immediately takes effect
            $cacheRefreshOptions = array('system_settings' => array());
            $this->modx->cacheManager->refresh($cacheRefreshOptions);
            if ($this->emergencystop == "true") {
                return $this->success($this->modx->lexicon('goodnews.newsletter_send_process_stopped_msg_success'));
            } else {
                return $this->success($this->modx->lexicon('goodnews.newsletter_send_process_started_msg_success'));
            }
        } else {
            if ($this->emergencystop == "true") {
                return $this->modx->error->failure($this->modx->lexicon('goodnews.newsletter_send_process_stopped_msg_failed'));
            } else {
                return $this->success($this->modx->lexicon('goodnews.newsletter_send_process_started_msg_failed'));
            }
        }
    }
}
return 'GoodNewsSwitchSendProcessProcessor';
