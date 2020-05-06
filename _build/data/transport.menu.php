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
 * Adds modMenus into package
 *
 * @package goodnews
 * @subpackage build
 */

// Create menu [MODX 2.3+ method]
/*
Note: will route to the first found of the following:
[namespace-path]controllers/[manager-theme]/index.class.php
[namespace-path]controllers/default/index.class.php
[namespace-path]controllers/index.class.php
*/
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text'        => 'goodnews',
    'parent'      => 'components',
    'description' => 'goodnews.desc',
    'icon'        => '',
    'menuindex'   => 0,
    'params'      => '',
    'handler'     => '',
    'action'      => 'index',
    'namespace'   => 'goodnews',
), '', true, true);
return $menu;
