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
 * Add plugins to package
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
    'plugincode'  => getFileContent($sources['plugins'].'goodnews.plugin.php'),
), '', true, true);

//$properties = include $sources['data'].'properties/properties.myplugin1.php';
//$plugins[$i]->setProperties($properties);
//unset($properties);

/* add plugin events */
$events = include $sources['events'].'events.goodnews.php';
if (!is_array($events) && empty($events)) {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find events for GoodNews plugin!');
} else {
    $plugins[$i]->addMany($events);
    $modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in '.count($events).' events for GoodNews plugin.');
}
flush();
unset($events);

return $plugins;
