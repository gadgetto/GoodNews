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

use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Model\GoodNewsGroupMember;

/**
 * Plugin to handle GoodNews events:
 *
 * OnManagerPageInit   Fired in the manager request handler, before the
 *                     manager page response is loaded and after defining request action.
 * OnUserRemove        Fires after a User is removed.
 *
 * @package goodnews
 */

switch ($modx->event->name) {
    case 'OnManagerPageInit':
        // Add css file to global manager-page header (not only GoodNews manager page)
        $cssFile = $modx->getOption(
            'goodnews.assets_url',
            null,
            $modx->getOption('assets_url') . 'components/goodnews/'
        ) . 'css/res.css';
        $modx->regClientCSS($cssFile);
        break;

    case 'OnUserRemove':
        // If a MODx user is deleted, also remove related GoodNews objects
        $id = $user->get('id');
        $meta = $modx->getObject(GoodNewsSubscriberMeta::class, ['subscriber_id' => $id]);
        if ($meta) {
            $meta->remove();
        }
        $modx->removeCollection(GoodNewsCategoryMember::class, ['member_id' => $id]);
        $modx->removeCollection(GoodNewsGroupMember::class, ['member_id' => $id]);
        break;
}
return;
