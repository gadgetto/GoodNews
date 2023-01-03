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
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Model\GoodNewsGroupMember;

/**
 * GoodNews processor to remove all subscriptions from a batch of users (groups, categories).
 *
 * @package goodnews
 * @subpackage processors
 */

class RemoveSubscriptionsMulti extends Processor
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

            // Remove all categories of this user
            $result = $this->modx->removeCollection(GoodNewsCategoryMember::class, ['member_id' => $id]);
            if ($result == false && $result != 0) {
                // @todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }

            // Remove all groups of this user
            $result = $this->modx->removeCollection(GoodNewsGroupMember::class, ['member_id' => $id]);
            if ($result == false && $result != 0) {
                // @todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }
        }
        return $this->success();
    }
}
