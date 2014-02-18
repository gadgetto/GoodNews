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
 * Asign events to GoodNews plugin
 *
 * @package goodnews
 * @subpackage build
 */

$events = array();

$events['OnManagerPageInit'] = $modx->newObject('modPluginEvent');
$events['OnManagerPageInit']->fromArray(array(
    'event'       => 'OnManagerPageInit',
    'priority'    => 0,
    'propertyset' => 0,
), '', true, true);

$events['OnUserRemove'] = $modx->newObject('modPluginEvent');
$events['OnUserRemove']->fromArray(array(
    'event'       => 'OnUserRemove',
    'priority'    => 0,
    'propertyset' => 0,
), '', true, true);

return $events;
