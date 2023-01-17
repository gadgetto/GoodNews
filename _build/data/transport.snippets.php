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

use MODX\Revolution\modSnippet;

/**
 * Add snippets to package.
 *
 * @var modX $modx
 * @var array $sources
 * @var array $properties
 * @var array $snippets
 *
 * @package goodnews
 * @subpackage build
 */

$snippets = [];
$i = 0;

$snippets[++$i] = $modx->newObject(modSnippet::class);
$snippets[$i]->fromArray([
    'id'          => $i,
    'name'        => 'GoodNewsConfirmSubscription',
    'description' => 'Handles activation of user subscriptions.',
    'snippet'     => getPHPFileContent($sources['snippets'] . 'goodnewsconfirmsubscription.snippet.php'),
], '', true, true);
$properties = include $sources['properties'] . 'properties.goodnewsconfirmsubscription.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject(modSnippet::class);
$snippets[$i]->fromArray([
    'id'          => $i,
    'name'        => 'GoodNewsSubscription',
    'description' => 'Handles GoodNews subscription forms in the front-end.',
    'snippet'     => getPHPFileContent($sources['snippets'] . 'goodnewssubscription.snippet.php'),
], '', true, true);
$properties = include $sources['properties'] . 'properties.goodnewssubscription.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject(modSnippet::class);
$snippets[$i]->fromArray([
    'id'          => $i,
    'name'        => 'GoodNewsUnSubscription',
    'description' => 'Handles one-click unsubscription in the front-end.',
    'snippet'     => getPHPFileContent($sources['snippets'] . 'goodnewsunsubscription.snippet.php'),
], '', true, true);
$properties = include $sources['properties'] . 'properties.goodnewsunsubscription.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject(modSnippet::class);
$snippets[$i]->fromArray([
    'id'          => $i,
    'name'        => 'GoodNewsUpdateProfile',
    'description' => 'Allows front-end updating of a users GoodNews profile.',
    'snippet'     => getPHPFileContent($sources['snippets'] . 'goodnewsupdateprofile.snippet.php'),
], '', true, true);
$properties = include $sources['properties'] . 'properties.goodnewsupdateprofile.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject(modSnippet::class);
$snippets[$i]->fromArray([
    'id'          => $i,
    'name'        => 'GoodNewsGetResourceID',
    'description' => 'Snippet to get the id of a resource by its name.',
    'snippet'     => getPHPFileContent($sources['snippets'] . 'goodnewsgetresourceid.snippet.php'),
], '', true, true);
//$properties = include $sources['properties'] . 'properties.goodnewsgetresourceid.php';
//$snippets[$i]->setProperties($properties);
//unset($properties);

$snippets[++$i] = $modx->newObject(modSnippet::class);
$snippets[$i]->fromArray([
    'id'          => $i,
    'name'        => 'GoodNewsGetNewsletters',
    'description' => 'Snippet to get a list of newsletters of the actual or specified container.',
    'snippet'     => getPHPFileContent($sources['snippets'] . 'goodnewsgetnewsletters.snippet.php'),
], '', true, true);
$properties = include $sources['properties'] . 'properties.goodnewsgetnewsletters.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject(modSnippet::class);
$snippets[$i]->fromArray([
    'id'          => $i,
    'name'        => 'GoodNewsContentCollection',
    'description' => 'Snippet to get a list of collected resource documents for inserting in newsletter body.',
    'snippet'     => getPHPFileContent($sources['snippets'] . 'goodnewscontentcollection.snippet.php'),
], '', true, true);
$properties = include $sources['properties'] . 'properties.goodnewscontentcollection.php';
$snippets[$i]->setProperties($properties);
unset($properties);

$snippets[++$i] = $modx->newObject(modSnippet::class);
$snippets[$i]->fromArray([
    'id'          => $i,
    'name'        => 'GoodNewsRequestLinks',
    'description' => 'Upon request - sends a subscriber an email with secure links to update or cancel his subscription.',
    'snippet'     => getPHPFileContent($sources['snippets'] . 'goodnewsrequestlinks.snippet.php'),
], '', true, true);
$properties = include $sources['properties'] . 'properties.goodnewsrequestlinks.php';
$snippets[$i]->setProperties($properties);
unset($properties);

unset($i);
return $snippets;
