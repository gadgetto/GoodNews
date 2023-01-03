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

use MODX\Revolution\modChunk;

/**
 * Add chunks to package
 *
 * @var modX $modx
 * @var array $sources
 * @var array $chunks
 *
 * @package goodnews
 * @subpackage build
 */

$chunks = [];
$i = 0;

// For GoodNews subscriptions

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsActivationEmailChunk',
    'description' => 'The HTML content of the email body for activating GoodNews subscriptions. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsactivationemail.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsCatFieldChunk',
    'description' => 'The template code for a GoodNews category checkbox form field. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewscatfield.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsCatFieldHiddenChunk',
    'description' => 'The template code for a GoodNews category input hidden form field. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewscatfieldhidden.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsGrpFieldChunk',
    'description' => 'The template code for a GoodNews group checkbox form field. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsgrpfield.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsGrpFieldHiddenChunk',
    'description' => 'The template code for a GoodNews group input hidden form field. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsgrpfieldhidden.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsGrpFieldsetChunk',
    'description' => 'The template code for a GoodNews group form fieldset. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsgrpfieldset.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsGrpNameChunk',
    'description' => 'The template code for a GoodNews group name only output. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsgrpname.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsNewsletterRowChunk',
    'description' => 'The template code for a GoodNews newsletter row in containers output. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsnewsletterrow.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsContentCollectionWrapperChunk',
    'description' => 'The template code for a GoodNews content collection wrapper for rows in mailing content output. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewscontentcollectionwrapper.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsContentCollectionRowChunk',
    'description' => 'The template code for a GoodNews content collection row in mailing content output. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewscontentcollectionrow.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsSubscriptionBoxChunk',
    'description' => 'The template code for a GoodNews subscription box to be placed somewhere on you site. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewssubscriptionbox.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsSubscriptionEmailChunk',
    'description' => 'The HTML content of the GoodNews subscription success email body. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewssubscriptionemail.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsReSubscriptionEmailChunk',
    'description' => 'The HTML content of the GoodNews renewal email body. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsresubscriptionemail.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsRequestLinksEmailChunk',
    'description' => 'The HTML content of the GoodNews request links email body. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsrequestlinksemail.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsStatusEmailChunk',
    'description' => 'The HTML content of the GoodNews status email body. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsstatusemail.chunk.tpl'),
], '', true, true);


// For GoodNews registrations

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsActivationRegEmailChunk',
    'description' => 'The HTML content of the email body for activating GoodNews registrations including newsletter subscriptions. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsactivationregemail.chunk.tpl'),
], '', true, true);

$chunks[++$i] = $modx->newObject(modChunk::class);
$chunks[$i]->fromArray([
    'id'          => $i,
    'name'        => 'sample.GoodNewsReRegistrationEmailChunk',
    'description' => 'The HTML content of the GoodNews registration/subscription renewal email body. Duplicate this to override it.',
    'snippet'     => file_get_contents($sources['chunks'] . 'sample.goodnewsreregistrationemail.chunk.tpl'),
], '', true, true);

unset($i);
return $chunks;
