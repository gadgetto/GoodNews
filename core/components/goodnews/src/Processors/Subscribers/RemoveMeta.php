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
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Model\GoodNewsGroupMember;

/**
 * GoodNews processor to remove all GoodNews meta-data of a user (meta, groups, categories).
 * (this reverts the subscriber back to a normal MODx user)
 *
 * @package goodnews
 * @subpackage processors
 */

class RemoveMeta extends Processor
{
    public function process()
    {
        $this->modx->lexicon->load('user');
        $id = $this->getProperty('id');

        // Remove subscriber meta entry
        $meta = $this->modx->getObject(GoodNewsSubscriberMeta::class, ['subscriber_id' => $id]);
        if ($meta) {
            $meta->remove();
        }

        // Remove all categories of this user
        $result = $this->modx->removeCollection(GoodNewsCategoryMember::class, ['member_id' => $id]);
        if ($result == false && $result != 0) {
            // @todo: return specific error message
            return $this->modx->error->failure($this->modx->lexicon('user_err_save'));
        }

        // Remove all groups of this user
        $result = $this->modx->removeCollection(GoodNewsGroupMember::class, ['member_id' => $id]);
        if ($result == false && $result != 0) {
            // @todo: return specific error message
            return $this->modx->error->failure($this->modx->lexicon('user_err_save'));
        }

        return $this->modx->error->success('');
    }
}
