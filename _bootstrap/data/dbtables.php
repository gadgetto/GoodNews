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

/**
 * Add database tables
 *
 * @package goodnews
 * @subpackage bootstrap
 */

$tables = [
    Bitego\GoodNews\Model\GoodNewsMailingMeta::class,
    Bitego\GoodNews\Model\GoodNewsRecipient::class,
    Bitego\GoodNews\Model\GoodNewsSubscriberMeta::class,
    Bitego\GoodNews\Model\GoodNewsSubscriberLog::class,
    Bitego\GoodNews\Model\GoodNewsGroup::class,
    Bitego\GoodNews\Model\GoodNewsGroupMember::class,
    Bitego\GoodNews\Model\GoodNewsCategory::class,
    Bitego\GoodNews\Model\GoodNewsCategoryMember::class,
    Bitego\GoodNews\Model\GoodNewsProcess::class,
];

return $tables;
