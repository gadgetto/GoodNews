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

use MODX\Revolution\modTemplateVar;
use MODX\Revolution\modTemplateVarTemplate;

/**
 * Add TVs to package
 *
 * @var modX $modx
 *
 * @package goodnews
 * @subpackage build
 */

$tvs = [];
$i = 0;

/*
$tvs[++$i]= $modx->newObject(modTemplateVar::class);
$tvs[$i]->fromArray([
    'id'                => $i,
    'type'              => 'checkbox',
    'name'              => 'tvName',
    'caption'           => 'Name of TV',
    'description'       => 'A description...',
    'display'           => 'delim',
    'elements'          => '',
    'locked'            => 0,
    'rank'              => 1,
    'default_text'      => '',
    'input_properties'  => '',
    'output_properties' => '',
    'properties'        => array(),
], '', true, true);
*/

unset($i);
return $tvs;
