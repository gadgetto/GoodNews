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
 * Processor class which creates subscriber meta data
 * for registered MODX users
 *
 * @package goodnews
 * @subpackage processors
 */

class GoodNewsSubscriptionCreateSubscriberMetaProcessor extends GoodNewsSubscriptionProcessor {
    /** @var modUser $user */
    public $user;
    
    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta;
    
    /**
     * @access public
     * @return mixed
     */
    public function process() {
        $this->user = $this->controller->user;
                    
        // Save subscriber meta
        $this->subscribermeta = $this->modx->newObject('GoodNewsSubscriberMeta');
        $this->setSubscriberMeta();
        if (!$this->subscribermeta->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Could not save new subscriber meta data - '.$this->user->get('id').' with username: '.$this->user->get('username'));
            return $this->modx->lexicon('goodnews.user_err_save');
        }
        return true;
    }

    /**
     * Set the subscriber meta data.
     *
     * @access public
     * @return void
     */
    public function setSubscriberMeta() {
        $userid = $this->user->get('id');
        $this->subscribermeta->set('subscriber_id', $userid);
        $this->subscribermeta->set('subscribedon', time());
        // create and set new sid
        $this->subscribermeta->set('sid', md5(time().$userid));
        $this->subscribermeta->set('testdummy', 0);
        $this->subscribermeta->set('ip', $this->controller->getSubscriberIP());
    }
}
return 'GoodNewsSubscriptionCreateSubscriberMetaProcessor';
