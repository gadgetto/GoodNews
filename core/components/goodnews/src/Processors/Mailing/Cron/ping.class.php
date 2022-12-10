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
 * Processor to read cron ping status
 *
 * @package goodnews
 * @subpackage processors
 */
class GoodNewsPingProcessor extends modProcessor {

    const GON_MAX_TASK_SCEDULER_INTERVAL = 300; // = 5 minutes

    public function initialize() {
        return parent::initialize();
    }
    
    public function process() {
        // Read cron ping time from modRegistry
        $this->modx->getService('registry', 'registry.modRegistry');
        $this->modx->registry->addRegister('goodnewscron','registry.modFileRegister');
        $this->modx->registry->goodnewscron->connect();
        $this->modx->registry->goodnewscron->subscribe('/ping/time');
        $msg = $this->modx->registry->goodnewscron->read(array(
            'poll_limit' => 1,
            'msg_limit' => 1,
            'remove_read' => false
        ));

        $currentTime = time();
        $touchTime = !empty($msg[0]) ? $msg[0] : 0;
        
        // No touch since GON_MAX_TASK_SCEDULER_INTERVAL?
        if ($touchTime < ($currentTime - self::GON_MAX_TASK_SCEDULER_INTERVAL)) {
            return $this->success($this->modx->lexicon('goodnews.task_scheduler_touch_waiting'));
        } else {
            return $this->success($this->modx->lexicon('goodnews.task_scheduler_touch_seconds_ago', array('seconds' => ($currentTime - $touchTime))));
        }
    }
}
return 'GoodNewsPingProcessor';
