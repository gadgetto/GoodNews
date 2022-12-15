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

namespace Bitego\GoodNews\Processors\Subscribers;

use MODX\Revolution\Processors\Processor;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;

/**
 * GoodNews processor to reset the bounce counters (hardb., softb.) of a batch of users.
 *
 * @var \MODX\Revolution\modX $modx
 * @package goodnews
 * @subpackage processors
 */

class ResetBounceCountersMulti extends Processor
{
    public function process()
    {
        $this->modx->lexicon->load('user');

        $userIds = $this->getProperty('userIds', null);
        if (empty($userIds)) {
            return $this->failure($this->modx->lexicon('goodnews.subscriber_err_ns_multi'));
        }
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);

        foreach ($userIds as $id) {
            if (empty($id)) {
                continue;
            }

            $meta = $this->modx->getObject(GoodNewsSubscriberMeta::class, array('subscriber_id' => $id));
            if (!is_object($meta)) {
                // @todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }

            $meta->set('soft_bounces', '');
            $meta->set('hard_bounces', '');

            if (!$meta->save()) {
                // @todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }
        }
        return $this->success();
    }
}
