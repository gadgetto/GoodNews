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

use MODX\Revolution\modPluginEvent;

/**
 * Add plugin events for GoodNews plugin
 *
 * @var modX $modx
 * @package goodnews
 * @subpackage build
 */

$events['OnManagerPageInit'] = $modx->newObject(modPluginEvent::class);
$events['OnManagerPageInit']->fromArray([
    'event' => 'OnManagerPageInit',
    'priority' => 0,
    'propertyset' => 0,
], '', true, true);

$events['OnUserRemove'] = $modx->newObject(modPluginEvent::class);
$events['OnUserRemove']->fromArray([
    'event' => 'OnUserRemove',
    'priority' => 0,
    'propertyset' => 0,
], '', true, true);

return $events;
