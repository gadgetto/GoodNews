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
 * Create database tables script
 * (supports MODX version 2.3.0 up to 2.8.x)
 *
 * @package goodnews
 * @subpackage build
 */

require_once dirname(__DIR__, 1) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

$modx = new modX();
$modx->initialize('mgr');
$modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path') . 'components/goodnews/') . 'model/';

$modx->addPackage('goodnews', $modelPath);

$manager = $modx->getManager();
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('HTML');

$objects = array(
    //'GoodNewsMailingMeta',
    //'GoodNewsRecipient',
    //'GoodNewsSubscriberMeta',
    //'GoodNewsSubscriberLog',
    //'GoodNewsGroup',
    //'GoodNewsGroupMember',
    //'GoodNewsCategory',
    //'GoodNewsCategoryMember',
    //'GoodNewsProcess',
);

$count = 0;
foreach ($objects as $obj) {
    $manager->createObjectContainer($obj);
    $count++;
}

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "\n<br>{$count} Database tables created.<br>\nExecution time: {$totalTime}\n");
