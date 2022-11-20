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
 * Assign setting values after elements are created, so we have the necessary IDs
 *
 * @package goodnews
 * @subpackage bootstrap
 */

$settingAttributes = array();
$i = 0;

$settingAttributes[++$i] = array(
    'key'   => 'goodnews.default_container_template',
    'value' => 'sample.GoodNewsContainerTemplate', // value needs to be template name, not ID
    'xtype' => 'modx-combo-template',
);
/*
$settingAttributes[++$i] = array(
    'key'   => 'key_name',
    'value' => 'somevalue',
    'xtype' => 'modx-combo-boolean',
);
*/

unset($i);
return $settingAttributes;
