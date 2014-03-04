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
 * Add chunks to package
 * 
 * @package goodnews
 * @subpackage build
 */

$chunks = array();
$i = 0;

$chunks[++$i] = $modx->newObject('modChunk');
$chunks[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'sample.GoodNewsActivationEmailTpl',
    'description' => 'The HTML content of the GoodNews activation email body. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'].'sample.goodnewsactivationemail.chunk.tpl'),
), '', true, true);

$chunks[++$i] = $modx->newObject('modChunk');
$chunks[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'sample.GoodNewsCatFieldTpl',
    'description' => 'The template code for a GoodNews category checkbox form field. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'].'sample.goodnewscatfield.chunk.tpl'),
), '', true, true);

$chunks[++$i] = $modx->newObject('modChunk');
$chunks[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'sample.GoodNewsCatFieldHiddenTpl',
    'description' => 'The template code for a GoodNews category input hidden form field. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'].'sample.goodnewscatfieldhidden.chunk.tpl'),
), '', true, true);

$chunks[++$i] = $modx->newObject('modChunk');
$chunks[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'sample.GoodNewsGrpFieldTpl',
    'description' => 'The template code for a GoodNews group checkbox form field. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'].'sample.goodnewsgrpfield.chunk.tpl'),
), '', true, true);

$chunks[++$i] = $modx->newObject('modChunk');
$chunks[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'sample.GoodNewsGrpFieldHiddenTpl',
    'description' => 'The template code for a GoodNews group input hidden form field. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'].'sample.goodnewsgrpfieldhidden.chunk.tpl'),
), '', true, true);

$chunks[++$i] = $modx->newObject('modChunk');
$chunks[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'sample.GoodNewsGrpFieldsetTpl',
    'description' => 'The template code for a GoodNews group form fieldset. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'].'sample.goodnewsgrpfieldset.chunk.tpl'),
), '', true, true);

$chunks[++$i] = $modx->newObject('modChunk');
$chunks[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'sample.GoodNewsGrpNameTpl',
    'description' => 'The template code for a GoodNews group name only output. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'].'sample.goodnewsgrpname.chunk.tpl'),
), '', true, true);

$chunks[++$i] = $modx->newObject('modChunk');
$chunks[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'sample.GoodNewsNewsletterRowTpl',
    'description' => 'The template code for a GoodNews newsletter row in containers output. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'].'sample.goodnewsnewsletterrow.chunk.tpl'),
), '', true, true);

$chunks[++$i] = $modx->newObject('modChunk');
$chunks[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'sample.GoodNewsSubscriptionBoxTpl',
    'description' => 'The template code for a GoodNews subscription box to be placed somewhere on you site. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'].'sample.goodnewssubscriptionbox.chunk.tpl'),
), '', true, true);

return $chunks;
