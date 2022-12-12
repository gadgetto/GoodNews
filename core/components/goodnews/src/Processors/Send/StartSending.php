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

namespace GoodNews\Processors\Send;

use MODX\Revolution\Processors\Processor;
use GoodNews\GoodNewsMailing;

/**
 * Start sending newsletters processor.
 *
 * @var \MODX\Revolution\modX $modx
 * @package goodnews
 * @subpackage processors
 */
class StartSending extends Processor
{
    /** @var GoodNewsMailing $goodnewsmailing */
    public $goodnewsmailing = null;
    
    /** @var int $mailingid The resource id of the newsletter */
    public $mailingid = 0;
    
    public function initialize()
    {
        $this->goodnewsmailing = new GoodNewsMailing($this->modx);
        $this->mailingid = $this->getProperty('mailingid');
        return parent::initialize();
    }
    
    public function process()
    {
        if (!$this->goodnewsmailing) {
            return $this->failure('GoodNewsMailing class could not be instantiated.');
        }
        $this->goodnewsmailing->setIPCstart($this->mailingid);
        return $this->success();
    }
}
