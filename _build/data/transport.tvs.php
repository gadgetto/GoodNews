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
    'properties'        => [],
], '', true, true);
*/

unset($i);
return $tvs;
