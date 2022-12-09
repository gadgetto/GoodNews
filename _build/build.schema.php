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
    $modx->services->add('error', function($c) use ($modx) {
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
}
else {
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
