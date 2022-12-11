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

namespace GoodNews\Processors\Container;
 
use MODX\Revolution\Processors\Processor;
use GoodNews\GoodNews;

/**
 * Switch current GoodNews container in CMP
 *
 * @var \MODX\Revolution\modX $modx
 * @var \GoodNews\GoodNews $goodnews
 * @package goodnews
 * @subpackage processors
 */

class SwitchContainer extends Processor
{
    /** @var GoodNews $goodnews A reference to the GoodNews object */
    public $goodnews = null;

    /** @var int $containerid The resource id of the container */
    public $containerid = 0;
    
    public function initialize()
    {
        $this->goodnews = new GoodNews($this->modx);
        $this->containerid = $this->getProperty('containerid');
        return parent::initialize();
    }
    
    public function process()
    {
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
