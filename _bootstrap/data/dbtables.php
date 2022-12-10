<?php
/**
 * GoodNews
 *
 * Copyright 2022 by bitego <office@bitego.com>
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
