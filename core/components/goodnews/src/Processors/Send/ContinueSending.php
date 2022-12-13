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

namespace Bitego\GoodNews\Processors\Send;

use MODX\Revolution\Processors\Processor;
use Bitego\GoodNews\Mailer;

/**
 * Continue sending newsletters processor.
 *
 * @var \MODX\Revolution\modX $modx
 * @package goodnews
 * @subpackage processors
 */
class ContinueSending extends Processor
{
    /** @var Mailer $mailer */
    public $mailer = null;
    
    /** @var int $mailingid The resource id of the newsletter */
    public $mailingid = 0;

    public function initialize()
    {
        $this->mailer = new Mailer($this->modx);
        $this->mailingid = $this->getProperty('mailingid');
        return parent::initialize();
    }
    
    public function process()
    {
        if (!$this->mailer) {
            return $this->failure('Mailer class could not be instantiated.');
        }
        $this->mailer->setIPCcontinue($this->mailingid);
        return $this->success();
    }
}
