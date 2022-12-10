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
 * Build Schema script
 * (supports MODX version 3.0.0 up to *)
 *
 * @package goodnews
 * @subpackage build
 */

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* Define package name and namespace */
define('PKG_NAME', 'GoodNews');
define('PKG_NAMESPACE', strtolower(PKG_NAME));

/* define sources */
$root = dirname(__DIR__, 1) . '/';
$sources = array(
    'root'   => $root,
    'core'   => $root . 'core/components/' . PKG_NAMESPACE . '/',
    'src'    => $root . 'core/components/' . PKG_NAMESPACE . '/src/',
    'schema' => $root . 'core/components/' . PKG_NAMESPACE . '/schema/',
);

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

$manager = $modx->getManager();
$generator = $manager->getGenerator();
$schemaFile = $sources['schema'] . PKG_NAMESPACE . '.mysql.schema.xml';

if (is_file($schemaFile)) {
    echo "Parsing schema: {$schemaFile}" . PHP_EOL;
    // Parse schema and generate class files in src/Model/
    // (Model directory will be created automatically)
    $generator->parseSchema(
        $schemaFile,
        $sources['src'],
        [
            'compile' => 0,
            'update' => 0,
            'regenerate' => 1,
            'namespacePrefix' => 'GoodNews\\'
        ]
    );
} else {
    echo "Schema file path invalid: {$schemaFile}" . PHP_EOL;
    echo 'Parsing schema failed!' . PHP_EOL;
}

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

echo "Execution time: {$totalTime}" . PHP_EOL;
echo '</pre>';
exit();
