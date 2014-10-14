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
 * Add snippets to package.
 * 
 * @package goodnews
 * @subpackage build
 */

$snippets = array();
$i = 0;

$snippets[++$i] = $modx->newObject('modSnippet');
$snippets[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'GoodNewsConfirmSubscription',
    'description' => 'Handles activation of user subscriptions.',
    'snippet'     => getFileContent($sources['snippets'].'goodnewsconfirmsubscription.snippet.php'),
), '' ,true, true);
$properties = include $sources['properties'].'properties.goodnewsconfirmsubscription.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject('modSnippet');
$snippets[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'GoodNewsSubscription',
    'description' => 'Handles GoodNews subscription forms in the front-end.',
    'snippet'     => getFileContent($sources['snippets'].'goodnewssubscription.snippet.php'),
), '', true, true);
$properties = include $sources['properties'].'properties.goodnewssubscription.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject('modSnippet');
$snippets[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'GoodNewsUnSubscription',
    'description' => 'Handles one-click unsubscription in the front-end.',
    'snippet'     => getFileContent($sources['snippets'].'goodnewsunsubscription.snippet.php'),
), '', true, true);
$properties = include $sources['properties'].'properties.goodnewsunsubscription.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject('modSnippet');
$snippets[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'GoodNewsUpdateProfile',
    'description' => 'Allows front-end updating of a users GoodNews profile.',
    'snippet'     => getFileContent($sources['snippets'].'goodnewsupdateprofile.snippet.php'),
), '', true, true);
$properties = include $sources['properties'].'properties.goodnewsupdateprofile.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject('modSnippet');
$snippets[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'GoodNewsGetResourceID',
    'description' => 'Snippet to get the id of a resource by its name.',
    'snippet'     => getFileContent($sources['snippets'].'goodnewsgetresourceid.snippet.php'),
), '', true, true);
//$properties = include $sources['properties'].'properties.goodnewsgetresourceid.php';
//$snippets[$i]->setProperties($properties);
//unset($properties);

$snippets[++$i] = $modx->newObject('modSnippet');
$snippets[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'GoodNewsGetNewsletters',
    'description' => 'Snippet to get a list of newsletters of the actual or specified container.',
    'snippet'     => getFileContent($sources['snippets'].'goodnewsgetnewsletters.snippet.php'),
), '', true, true);
$properties = include $sources['properties'].'properties.goodnewsgetnewsletters.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject('modSnippet');
$snippets[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'GoodNewsContentCollection',
    'description' => 'Snippet to get a list of collected resource documents for inserting in newsletter body.',
    'snippet'     => getFileContent($sources['snippets'].'goodnewscontentcollection.snippet.php'),
), '', true, true);
$properties = include $sources['properties'].'properties.goodnewscontentcollection.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject('modSnippet');
$snippets[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'GoodNewsRequestLinks',
    'description' => 'Upon request - sends a subscriber an email with secure links to update or cancel his subscription.',
    'snippet'     => getFileContent($sources['snippets'].'goodnewsrequestlinks.snippet.php'),
), '', true, true);
$properties = include $sources['properties'].'properties.goodnewsrequestlinks.php';
$snippets[$i]->setProperties($properties);
unset($properties);

return $snippets;
