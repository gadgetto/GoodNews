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

use MODX\Revolution\modPlugin;
use MODX\Revolution\modPluginEvent;

/**
 * Add plugins to package
 *
 * @var modX $modx
 * @var array $sources
 * @var array $plugins
 *
 * @package goodnews
 * @subpackage build
 */

$plugins = [];
$i = 0;

$plugins[++$i] = $modx->newObject(modPlugin::class);
$plugins[$i]->fromArray([
    'id'          => $i,
    'name'        => 'GoodNews',
    'description' => 'Main GoodNews plugin. Do not change!',
    'plugincode'  => getPHPFileContent($sources['plugins'] . 'goodnews.plugin.php'),
    'category'    => 0,
], '', true, true);

$events = include $sources['events'] . 'events.goodnews.php';
if (!empty($events) && is_array($events)) {
    $plugins[$i]->addMany($events);
    $modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in <b>' . count($events) . '</b> plugin event(s) for GoodNews plugin.');
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not package in plugin event(s) for GoodNews plugin. Data missing.');
}
flush();

unset($events, $i);
return $plugins;
