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
 * Additional element categories
 *
 * @var modX $modx
 * @var array $categories
 *
 * @package goodnews
 * @subpackage bootstrap
 */

$categories = array();
$i = 0;

// Use literal category names in 'parent' keys. Will be converted to IDs later.
// Use 'default' keyword in 'parent' keys if category should have default category as parent.

$categories[++$i] = $modx->newObject('modCategory');
$categories[$i]->fromArray(array(
    'parent'      => 'default',
    'category'    => 'Newsletter Templates',
), '', true, true);

/*
$categories[++$i] = $modx->newObject('modMenu');
$categories[$i]->fromArray(array(
    'parent'      => 'Parent Category Name',
    'category'    => 'Another Category',
), '', true, true);
*/

return $categories;
