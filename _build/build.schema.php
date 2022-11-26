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
 * Build Schema script
 * (supports MODX version 2.3.0 up to 2.8.x)
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
    'model'  => $root . 'core/components/' . PKG_NAMESPACE . '/model/',
    'schema' => $root . 'core/components/' . PKG_NAMESPACE . '/model/schema/',
    'assets' => $root . 'assets/components/' . PKG_NAMESPACE . '/',
);

/* Load modx and configs */
require_once $sources['root'] . 'config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

/* Connect to MODX */
$modx= new modX();
$modx->initialize('mgr');
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$modx->getService('error', 'error.modError', '', '');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
echo '<pre>';

$manager = $modx->getManager();
$generator = $manager->getGenerator();

$generator->classTemplate = <<<EOD
<?php
/**
 * [+phpdoc-package+]
 */
class [+class+] extends [+extends+] {}
?>
EOD;
$generator->platformTemplate = <<<EOD
<?php
/**
 * [+phpdoc-package+]
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\\\', '/') . '/[+class-lowercase+].class.php');
class [+class+]_[+platform+] extends [+class+] {}
?>
EOD;
$generator->mapHeader = <<<EOD
<?php
/**
 * [+phpdoc-package+]
 */
EOD;

$generator->parseSchema(
    $sources['schema'] . PKG_NAMESPACE . '.mysql.schema.xml',
    $sources['model']
);

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

echo "\nExecution time: {$totalTime}\n";
echo '</pre>';
exit ();
