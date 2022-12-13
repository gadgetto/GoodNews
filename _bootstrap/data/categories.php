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

use MODX\Revolution\modCategory;

/**
 * Additional element categories
 *
 * @var modX $modx
 * @var array $categories
 *
 * @package goodnews
 * @subpackage bootstrap
 */

$categories = [];
$i = 0;

// Use literal category names in 'parent' keys. Will be converted to IDs later.
// Use 'default' keyword in 'parent' keys if category should have default category as parent.

$categories[++$i] = $modx->newObject(modCategory::class);
$categories[$i]->fromArray([
    'parent'      => 'default',
    'category'    => 'Newsletter Templates',
], '', true, true);

/*
$categories[++$i] = $modx->newObject(modCategory::class);
$categories[$i]->fromArray([
    'parent'      => 'Parent Category Name',
    'category'    => 'Another Category',
], '', true, true);
*/

return $categories;
