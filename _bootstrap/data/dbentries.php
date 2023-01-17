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
 * Entries for custom database tables
 *
 * @package goodnews
 * @subpackage bootstrap
 */

$epoch = time();

$entries['Bitego\\GoodNews\\Model\\GoodNewsGroup'] = [
    'name'          => 'Newsletters',
    'description'   => 'Default newsletters group',
    'modxusergroup' => 0,
    'createdon'     => $epoch,
    'createdby'     => 0,
    'editedon'      => $epoch,
    'editedby'      => 0,
];

unset($epoch);
return $entries;
