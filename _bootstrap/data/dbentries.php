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
 * Entries for custom database tables
 *
 * @package goodnews
 * @subpackage bootstrap
 *
 *  @todo: this file is currently only used by bootstrap script; needs to be also implemented with build script (see resolve.tablescontent.php)
 */

$epoch = time();

$entries['GoodNewsGroup'] = array(
    'name'          => 'Newsletters',
    'description'   => 'Default newsletters group',
    'modxusergroup' => 0,
    'createdon'     => $epoch,
    'createdby'     => 0,
    'editedon'      => $epoch,
    'editedby'      => 0,
);

unset($epoch);
return $entries;