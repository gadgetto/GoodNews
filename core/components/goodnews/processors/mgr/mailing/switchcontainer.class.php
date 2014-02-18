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

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/model/goodnews/goodnews.class.php';

/**
 * Switch current GoodNews container in CMP
 *
 * @package goodnews
 * @subpackage processors
 */
class GoodNewsSwitchContainerProcessor extends modProcessor {

    /** @var GoodNews $goodnews A reference to the GoodNews object */
    public $goodnews = null;

    /** @var int $nlid The resource id of the container */
    public $containerid = 0;
    
    public function initialize() {
        $this->goodnews = new GoodNews($this->modx);
        $this->containerid = $this->getProperty('containerid');
        return parent::initialize();
    }
    
    public function process() {
        if (!$this->goodnews) {
            return $this->failure('GoodNews class could not be instantiated.');
        }
        if ($this->goodnews->setUserCurrentContainer($this->containerid)) {
            return $this->success('');
        } else {
            return $this->failure('User settings could not be updated.');
        }
    }
}
return 'GoodNewsSwitchContainerProcessor';
