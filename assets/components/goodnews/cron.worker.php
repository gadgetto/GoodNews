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
 * cron.worker.php is the mail sender (handled and started by cron.php via php exec)
 *
 * @package goodnews
 */

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;

set_time_limit(0);

// Fetch params of CLI calls and merge with URL params (for universal usage)
if(isset($_SERVER['argc'])) {
    if ($argc > 0) {
        for ($i=1; $i < $argc; $i++) {
            parse_str($argv[$i], $tmp);
            $_GET = array_merge($_GET, $tmp);
        }
    }
}

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');

// If set - worker script may only be continued if the correct security key is provided by cron (@param sid)
$securityKey = $modx->getOption('goodnews.cron_security_key', null, '');
if (!empty($securityKey) && $_GET['sid'] !== $securityKey) {
    exit('[GoodNews] cron.worker.php - Missing or wrong authentification! Sorry Dude!');
}

$debug = $modx->getOption('goodnews.debug', null, false) ? true : false;

$corePath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/');
require_once $corePath.'model/goodnews/goodnewsmailing.class.php';
$modx->goodnewsmailing = new GoodNewsMailing($modx);
if (!($modx->goodnewsmailing instanceof GoodNewsMailing)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] cron.worker.php - Could not load GoodNewsMailing class.');
    exit();
}

$mailingsToSend = $modx->goodnewsmailing->getMailingsToSend();
if (is_array($mailingsToSend)) {
    foreach ($mailingsToSend as $mailingid) {
        $modx->goodnewsmailing->processMailing($mailingid);
    }
}

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);
if ($debug) {
    $modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] cron.worker.php - Finished with execution time: '.$totalTime);
}

exit();
