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

use MODX\Revolution\modSystemSetting;
use MODX\Revolution\Processors\Processor;

/**
 * Switch send process ON/OFF processor
 *
 * @var \MODX\Revolution\modX $modx
 * @package goodnews
 * @subpackage processors
 */
class SwitchSendProcess extends Processor
{
    /** @var string $emergencystop The state to switch the send process to (can be "true" "false") */
    public $emergencystop = null;

    public function initialize()
    {
        $this->emergencystop = $this->getProperty('emergencystop');
        return parent::initialize();
    }

    public function process()
    {
        $worker_process_active = $this->modx->getObject(modSystemSetting::class, 'goodnews.worker_process_active');

        // Toggle button is pressed (means emergency stopp for send process!)
        if ($this->emergencystop == "true") {
            $worker_process_active->set('value', '0');
        // Toggle button is not pressed
        } else {
            $worker_process_active->set('value', '1');
        }

        if ($worker_process_active->save()) {
            // refresh part of cache (MODx 2.1.x) so that settings change immediately takes effect
            $cacheRefreshOptions = ['system_settings' => []];
            $this->modx->cacheManager->refresh($cacheRefreshOptions);
            if ($this->emergencystop == "true") {
                return $this->success(
                    $this->modx->lexicon('goodnews.newsletter_send_process_stopped_msg_success')
                );
            } else {
                return $this->success(
                    $this->modx->lexicon('goodnews.newsletter_send_process_started_msg_success')
                );
            }
        } else {
            if ($this->emergencystop == "true") {
                return $this->modx->error->failure(
                    $this->modx->lexicon('goodnews.newsletter_send_process_stopped_msg_failed')
                );
            } else {
                return $this->success(
                    $this->modx->lexicon('goodnews.newsletter_send_process_started_msg_failed')
                );
            }
        }
    }

    public function getLanguageTopics()
    {
        return ['setting'];
    }
}
