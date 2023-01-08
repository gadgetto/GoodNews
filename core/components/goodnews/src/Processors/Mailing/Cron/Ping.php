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

namespace Bitego\GoodNews\Processors\Mailing\Cron;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\Registry\modRegistry;
use MODX\Revolution\Registry\modFileRegister;

/**
 * Processor to read cron ping status.
 *
 * @var \MODX\Revolution\modX $modx
 * @package goodnews
 * @subpackage processors
 */

class Ping extends Processor
{
    public const GON_MAX_TASK_SCEDULER_INTERVAL = 300; // = 5 minutes

    public function initialize()
    {
        return parent::initialize();
    }

    public function process()
    {
        // Read cron ping time from modRegistry
        if (!$this->modx->services->has('registry')) {
            $modx = &$this->modx;
            $this->modx->services->add('registry', function ($c) use ($modx) {
                return new modRegistry($modx);
            });
        }
        $registry = $this->modx->services->get('registry');
        $registry->addRegister('goodnewscron', modFileRegister::class);
        $registry->goodnewscron->connect();
        $registry->goodnewscron->subscribe('/ping/time');
        $msg = $registry->goodnewscron->read([
            'poll_limit' => 1,
            'msg_limit' => 1,
            'remove_read' => false
        ]);

        $currentTime = time();
        $touchTime = !empty($msg[0]) ? $msg[0] : 0;

        // No touch since GON_MAX_TASK_SCEDULER_INTERVAL?
        if ($touchTime < ($currentTime - self::GON_MAX_TASK_SCEDULER_INTERVAL)) {
            return $this->success(
                $this->modx->lexicon('goodnews.task_scheduler_touch_waiting')
            );
        } else {
            return $this->success(
                $this->modx->lexicon(
                    'goodnews.task_scheduler_touch_seconds_ago',
                    ['seconds' => ($currentTime - $touchTime)]
                )
            );
        }
    }
}
