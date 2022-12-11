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
 * Assign setting values after elements are created, so we have the necessary IDs
 *
 * @package goodnews
 * @subpackage bootstrap
 */

$settingAttributes = [];
$i = 0;

$settingAttributes[++$i] = [
    'key'   => 'goodnews.default_container_template',
    'value' => 'sample.GoodNewsContainerTemplate', // value needs to be template name, not ID
    'xtype' => 'modx-combo-template',
];
/*
$settingAttributes[++$i] = [
    'key'   => 'key_name',
    'value' => 'somevalue',
    'xtype' => 'modx-combo-boolean',
];
*/

unset($i);
return $settingAttributes;
