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

use MODX\Revolution\modX;
use Bitego\GoodNews\Mailer;

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
if (isset($_SERVER['argc'])) {
    $argc = $_SERVER['argc'];
    if ($argc > 0) {
        for ($i = 1; $i < $argc; $i++) {
            parse_str($argv[$i], $tmp);
            $_GET = array_merge($_GET, $tmp);
        }
    }
}

// Load MODX
define('MODX_API_MODE', true);
$root = dirname(__DIR__, 3) . '/';
require_once $root . 'config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';
/** @var modX $modx */
$modx = new modX();
$modx->initialize('mgr');
if (!$modx->services->has('error')) {
    $modx->services->add('error', function ($c) use ($modx) {
        return new modError($modx);
    });
}

// Security check:
// (if set - connector script may only be continued if the correct
// security key is provided by @param sid via CLI calls or URL params)
$sid = (string)isset($_GET['sid']) ? $_GET['sid'] : '';
$securityKey = (string)$modx->getOption('goodnews.cron_security_key', null, '');
if ($sid !== $securityKey) {
    $modx->log(modX::LOG_LEVEL_WARN, '[GoodNews] cron.worker.php - missing or wrong security key!');
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Debug mode?
$debug = $modx->getOption('goodnews.debug', null, false) ? true : false;

/** @var Mailer $mailer */
$mailer = new Mailer($modx);
if (!($mailer instanceof Mailer)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] cron.worker.php - could not load Mailer class.');
    exit();
}

$mailingsToSend = $mailer->getMailingsToSend();
if (is_array($mailingsToSend)) {
    foreach ($mailingsToSend as $mailingid) {
        $mailer->processMailing($mailingid);
    }
}

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);
if ($debug) {
    $modx->log(
        modX::LOG_LEVEL_INFO,
        '[GoodNews] [pid: ' . getmypid() . '] cron.worker.php - process finished with execution time: ' . $totalTime
    );
}
