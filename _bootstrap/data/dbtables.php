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
    GoodNews\Model\GoodNewsMailingMeta::class,
    GoodNews\Model\GoodNewsRecipient::class,
    GoodNews\Model\GoodNewsSubscriberMeta::class,
    GoodNews\Model\GoodNewsSubscriberLog::class,
    GoodNews\Model\GoodNewsGroup::class,
    GoodNews\Model\GoodNewsGroupMember::class,
    GoodNews\Model\GoodNewsCategory::class,
    GoodNews\Model\GoodNewsCategoryMember::class,
    GoodNews\Model\GoodNewsProcess::class,
];

return $tables;
