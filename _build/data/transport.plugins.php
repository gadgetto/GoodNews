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
 * Add plugins to package
 *
 * @var modX $modx
 * @var array $sources
 * @var array $plugins
 *
 * @package goodnews
 * @subpackage build
 */

$plugins = array();
$i = 0;

$plugins[++$i] = $modx->newObject('modPlugin');
$plugins[$i]->fromArray(array(
    'id'          => $i,
    'name'        => 'GoodNews',
    'description' => 'Main GoodNews plugin. Do not change!',
    'plugincode'  => getPHPFileContent($sources['plugins'] . 'goodnews.plugin.php'),
    'category'    => 0,
), '', true, true);

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
