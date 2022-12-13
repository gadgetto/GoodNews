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
use MODX\Revolution\Error\modError;

/**
 * Create database tables script
 * (supports MODX version 3.0.0 up to *)
 *
 * @package goodnews
 * @subpackage build
 */

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* Define package name and namespace */
define('VENDOR_NAME', 'Bitego');
define('PKG_NAME', 'GoodNews');
define('PKG_NAMESPACE', strtolower(PKG_NAME));

$className = VENDOR_NAME . '\\' . PKG_NAME . '\\' . PKG_NAME;

$root = dirname(__DIR__, 1) . '/';
$sources = array(
    'root'       => $root,
    'source_src' => $root . 'core/components/' . PKG_NAMESPACE . '/src/',
);
unset($root);

require_once $sources['root'] . 'config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';

/* Load MODX */
$modx = new modX();
$modx->initialize('mgr');
if (!$modx->services->has('error')) {
    $modx->services->add('error', function ($c) use ($modx) {
        return new modError($modx);
    });
}
$modx->error = $modx->services->get('error');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
echo '<pre>';
flush();

$modx->log(modX::LOG_LEVEL_INFO, 'Creating database tables...');

/* Add package */
if ($modx->addPackage(VENDOR_NAME . '\\' . PKG_NAME . '\\Model', $sources['source_src'], null, VENDOR_NAME . '\\' . PKG_NAME . '\\')) {
    $modx->log(modX::LOG_LEVEL_ERROR, PKG_NAME . ' package added.');
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, PKG_NAME . ' package could not be added.');
    $modx->log(modX::LOG_LEVEL_INFO, 'Building development environment failed!');
    flush();
    exit();
}
flush();

/* Add package service */
if (!$modx->services->has(PKG_NAMESPACE)) {
    $modx->services->add(PKG_NAMESPACE, function ($c) use ($modx, $className) {
        return new $className($modx);
    });
}
$service = $modx->services->get(PKG_NAMESPACE);
if ($service instanceof $className) {
    $modx->log(modX::LOG_LEVEL_ERROR, PKG_NAME . ' service loaded.');
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, PKG_NAME . ' service could not be loaded.');
    $modx->log(modX::LOG_LEVEL_INFO, 'Building development environment failed!');
    flush();
    exit();
}
flush();

$tables = [
    /*
    GoodNews\Model\GoodNewsMailingMeta::class,
    GoodNews\Model\GoodNewsRecipient::class,
    GoodNews\Model\GoodNewsSubscriberMeta::class,
    GoodNews\Model\GoodNewsSubscriberLog::class,
    GoodNews\Model\GoodNewsGroup::class,
    GoodNews\Model\GoodNewsGroupMember::class,
    GoodNews\Model\GoodNewsCategory::class,
    GoodNews\Model\GoodNewsCategoryMember::class,
    GoodNews\Model\GoodNewsProcess::class,
    */
];

$manager = $modx->getManager();
$count = 0;
foreach ($tables as $table) {
    $manager->createObjectContainer($table);
    $count++;
}

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "{$count} Database tables created.");
$modx->log(modX::LOG_LEVEL_INFO, "Execution time: {$totalTime}");
echo '</pre>';
flush();
