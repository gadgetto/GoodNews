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
 
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/model/goodnews/goodnewsmailing.class.php';

/**
 * Send test newsletters processor
 *
 * @package goodnews
 * @subpackage processors
 */
class GoodNewsStartTestMailingProcessor extends modProcessor {

    /** @var GoodNewsMailing $goodnewsmailing */
    public $goodnewsmailing = null;
    
    /** @var int $mailingid The resource id of the newsletter */
    public $mailingid = 0;
    
    public function initialize() {
        $this->goodnewsmailing = new GoodNewsMailing($this->modx);
        $this->mailingid = $this->getProperty('mailingid');        
        return parent::initialize();
    }
    
    public function process() {

        if (!$this->goodnewsmailing) {
            return $this->failure('GoodNewsMailing class could not be instantiated.');
        }
        // Send the test mails
        $this->goodnewsmailing->processTestMailing($this->mailingid);
        
        return $this->success();
    }
    
}
return 'GoodNewsStartTestMailingProcessor';
