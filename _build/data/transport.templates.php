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
 * Add templates to package
 * 
 * @package goodnews
 * @subpackage build
 */

$templates = array();
$i = 0;

$templates[++$i]= $modx->newObject('modTemplate');
$templates[$i]->fromArray(array(
    'id'           => $i,
    'templatename' => 'sample.GoodNewsContainerTemplate',
    'description'  => 'A sample Template for GoodNews containers. Duplicate this to override it.',
    'content'      => file_get_contents($sources['templates'].'sample.goodnewscontainer.template.tpl'),
    'icon'         => 'icon-envelope',
));

$templates[++$i]= $modx->newObject('modTemplate');
$templates[$i]->fromArray(array(
    'id'           => $i,
    'templatename' => 'sample.GoodNewsProfileTemplate',
    'description'  => 'A sample Template for GoodNews subscription pages (Subscription, Unsubscription, Update Subscription, Confirmation, ...). Duplicate this to override it.',
    'content'      => file_get_contents($sources['templates'].'sample.goodnewsprofile.template.tpl'),
));

$templates[++$i]= $modx->newObject('modTemplate');
$templates[$i]->fromArray(array(
    'id'           => $i,
    'templatename' => 'sample.GoodNewsNewsletterTemplate1',
    'description'  => 'A sample Template for GoodNews newsletters: Single column. Duplicate this to override it.',
    'content'      => file_get_contents($sources['templates'].'sample.goodnewsnewsletter1.template.tpl'),
));

$templates[++$i]= $modx->newObject('modTemplate');
$templates[$i]->fromArray(array(
    'id'           => $i,
    'templatename' => 'sample.GoodNewsNewsletterTemplate2',
    'description'  => 'A sample Template for GoodNews newsletters: Single column with Content Collection. Duplicate this to override it.',
    'content'      => file_get_contents($sources['templates'].'sample.goodnewsnewsletter2.template.tpl'),
));

return $templates;