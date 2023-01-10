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

namespace Bitego\GoodNews\Processors\Subscription;

use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Processors\Subscription\Base;

/**
 * Processor class which creates subscriber meta data
 * for registered MODX users.
 *
 * @package goodnews
 * @subpackage processors
 */

class CreateSubscriberMeta extends Base
{
    /** @var modUser $user */
    public $user = null;

    /** @var GoodNewsSubscriberMeta $subscribermeta */
    public $subscribermeta = null;

    /**
     * @access public
     * @return mixed
     */
    public function process()
    {
        $this->user = $this->controller->user;

        // Save subscriber meta
        $this->subscribermeta = $this->modx->newObject(GoodNewsSubscriberMeta::class);
        $this->setSubscriberMeta();
        if (!$this->subscribermeta->save()) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Could not save new subscriber meta data - ' .
                $this->user->get('id') .
                ' with username: ' .
                $this->user->get('username')
            );
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
    public function setSubscriberMeta()
    {
        $userid = $this->user->get('id');
        $this->subscribermeta->set('subscriber_id', $userid);
        $this->subscribermeta->set('subscribedon', time());
        // Create and set new sid
        $this->subscribermeta->set('sid', md5(time() . $userid));
        $this->subscribermeta->set('testdummy', 0);
        $this->subscribermeta->set('ip', $this->controller->getSubscriberIP());
    }
}
