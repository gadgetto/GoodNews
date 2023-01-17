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

use MODX\Revolution\modMenu;

/**
 * Adds menus to package
 *
 * @var modX $modx
 * @var array $menus
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

$menus = [];
$i = 0;

$menus[++$i] = $modx->newObject(modMenu::class);
$menus[$i]->fromArray([
    'id'          => $i,
    'text'        => 'goodnews',
    'parent'      => 'components',
    'description' => 'goodnews.desc',
    'icon'        => '',
    'menuindex'   => 0,
    'params'      => '',
    'handler'     => '',
    'action'      => 'index',
    'namespace'   => 'goodnews',
], '', true, true);

unset($i);
return $menus;
