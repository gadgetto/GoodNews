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
 * GoodNews processor to remove all subscriptions of a user (groups, categories).
 *
 * @package goodnews
 * @subpackage processors
 */

class RemoveSubscriptions extends Processor
{
    public function process()
    {
        $this->modx->lexicon->load('user');
        $id = $this->getProperty('id');
        
        // remove all categories of this user
        $result = $this->modx->removeCollection(GoodNewsCategoryMember::class, array('member_id' => $id));
        if ($result == false && $result != 0) {
            // @todo: return specific error message
            return $this->modx->error->failure($this->modx->lexicon('user_err_save'));
        }
        
        // remove all groups of this user
        $result = $this->modx->removeCollection(GoodNewsGroupMember::class, array('member_id' => $id));
        if ($result == false && $result != 0) {
            // @todo: return specific error message
            return $this->modx->error->failure($this->modx->lexicon('user_err_save'));
        }
        
        return $this->modx->error->success('');
    }
}
