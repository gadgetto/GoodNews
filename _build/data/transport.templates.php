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

use MODX\Revolution\modTemplate;

/**
 * Add templates to package
 *
 * @var modX $modx
 * @var array $sources
 * @var array $templates
 *
 * @package goodnews
 * @subpackage build
 */

$templates = [];
$i = 0;

$templates[++$i] = $modx->newObject(modTemplate::class);
$templates[$i]->fromArray([
    'id'           => $i,
    'templatename' => 'sample.GoodNewsContainerTemplate',
    'description'  => 'A sample Template for GoodNews containers. Duplicate this to override it.',
    'content'      => file_get_contents($sources['templates'] . 'sample.goodnewscontainer.template.tpl'),
    'icon'         => 'icon-envelope',
]);

$templates[++$i] = $modx->newObject(modTemplate::class);
$templates[$i]->fromArray([
    'id'           => $i,
    'templatename' => 'sample.GoodNewsProfileTemplate',
    'description'  => 'A sample Template for GoodNews subscription pages (Subscription, Unsubscription, Update Subscription, Confirmation, ...). Duplicate this to override it.',
    'content'      => file_get_contents($sources['templates'] . 'sample.goodnewsprofile.template.tpl'),
]);

$templates[++$i] = $modx->newObject(modTemplate::class);
$templates[$i]->fromArray([
    'id'           => $i,
    'templatename' => 'sample.GoodNewsNewsletterTemplate1',
    'description'  => 'Single column. A sample Template for GoodNews newsletters. Duplicate this to override it.',
    'content'      => file_get_contents($sources['templates'] . 'sample.goodnewsnewsletter1.template.tpl'),
]);

$templates[++$i] = $modx->newObject(modTemplate::class);
$templates[$i]->fromArray([
    'id'           => $i,
    'templatename' => 'sample.GoodNewsNewsletterTemplate2',
    'description'  => 'Single column with GoodNews content collection. A sample Template for GoodNews newsletters. Duplicate this to override it.',
    'content'      => file_get_contents($sources['templates'] . 'sample.goodnewsnewsletter2.template.tpl'),
]);

unset($i);
return $templates;
