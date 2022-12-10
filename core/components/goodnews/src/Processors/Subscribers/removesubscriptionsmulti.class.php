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

/**
 * GoodNews processor to remove all subscriptions from a batch of users (groups, categories)
 *
 * @package goodnews
 * @subpackage processors
 */

class RemoveSubscriptionsMultiProcessor extends modProcessor {

    public function process() {
        
        $this->modx->lexicon->load('user');

        $userIds = $this->getProperty('userIds', null);
        if (empty($userIds)) {
            return $this->failure($this->modx->lexicon('goodnews.subscriber_err_ns_multi'));
        }
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);

        foreach ($userIds as $id) {
            if (empty($id)) { continue; }

            // remove all categories of this user
            $result = $this->modx->removeCollection('GoodNewsCategoryMember', array('member_id' => $id));
            if ($result == false && $result != 0) {
                // todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }
            
            // remove all groups of this user
            $result = $this->modx->removeCollection('GoodNewsGroupMember', array('member_id' => $id));
            if ($result == false && $result != 0) {
                // todo: return specific error message
                return $this->failure($this->modx->lexicon('user_err_save'));
            }
        }
        return $this->success();
    }
}
return 'RemoveSubscriptionsMultiProcessor';
