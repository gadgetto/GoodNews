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
use MODX\Revolution\Transport\modPackageBuilder;
use MODX\Revolution\modCategory;
use xPDO\Transport\xPDOTransport;
use Bitego\GoodNews\GoodNews;

/**
 * Build script for GoodNews transport package
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

/* Define build paths */
$root = dirname(__DIR__, 1) . '/';
$sources = [
    'root'           => $root,
    'build'          => $root . '_build/',
    'includes'       => $root . '_build/includes/',
    'data'           => $root . '_build/data/',
    'events'         => $root . '_build/data/events/',
    'properties'     => $root . '_build/data/properties/',
    'validators'     => $root . '_build/validators/',
    'resolvers'      => $root . '_build/resolvers/',
    'packages'       => $root . '_packages/',
    'chunks'         => $root . 'core/components/'   . PKG_NAMESPACE . '/elements/chunks/',
    'plugins'        => $root . 'core/components/'   . PKG_NAMESPACE . '/elements/plugins/',
    'resources'      => $root . 'core/components/'   . PKG_NAMESPACE . '/elements/resources/',
    'snippets'       => $root . 'core/components/'   . PKG_NAMESPACE . '/elements/snippets/',
    'templates'      => $root . 'core/components/'   . PKG_NAMESPACE . '/elements/templates/',
    'lexicon'        => $root . 'core/components/'   . PKG_NAMESPACE . '/lexicon/',
    'source_core'    => $root . 'core/components/'   . PKG_NAMESPACE . '/',
    'source_src'     => $root . 'core/components/'   . PKG_NAMESPACE . '/src/',
    'source_assets'  => $root . 'assets/components/' . PKG_NAMESPACE . '/',
    'source_model'   => $root . 'core/components/'   . PKG_NAMESPACE . '/src/Model/',
];
unset($root);

require_once $sources['root'] . 'config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';
require_once $sources['includes'] . 'functions.php';

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

/* Define package version and release */
define('PKG_VERSION', GoodNews::VERSION);
define('PKG_RELEASE', GoodNews::RELEASE);

$modx->log(
    modX::LOG_LEVEL_INFO,
    'Building transport package for <b>' . PKG_NAMESPACE . '-' . PKG_VERSION . '-' . PKG_RELEASE . '</b>...'
);

/* Prepare Transport Package and register namespace */
$builder = new modPackageBuilder($modx);
$builder->directory = $sources['packages'];
$builder->createPackage(PKG_NAMESPACE, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(
    PKG_NAMESPACE,
    false,
    true,
    '{core_path}components/' . PKG_NAMESPACE . '/',
    '{assets_path}components/' . PKG_NAMESPACE . '/'
);
$modx->log(modX::LOG_LEVEL_INFO, 'Prepared transport package and registered namespace.');
flush();

/* Add menus */
$menus = include $sources['data'] . 'transport.menus.php';
if (!empty($menus) && is_array($menus)) {
    foreach ($menus as $menu) {
        $vehicle = $builder->createVehicle($menu, [
            xPDOTransport::UNIQUE_KEY => 'text',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => true,
        ]);
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in <b>' . count($menus) . '</b> menu(s).');
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in menu(s). Data missing.');
}
flush();
unset($vehicle, $menus, $menu);

/* Add system settings */
$settings = include $sources['data'] . 'transport.settings.php';
if (!empty($settings) && is_array($settings)) {
    foreach ($settings as $setting) {
        $vehicle = $builder->createVehicle($setting, [
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false, // existing settings should not be overwritten
        ]);
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in <b>' . count($settings) . '</b> system setting(s).');
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in system setting(s). Data missing.');
}
flush();
unset($vehicle, $settings, $setting);

/* Create default elements category (but not saved yet) */
$category = $modx->newObject(modCategory::class);
$category->set('id', 1);
$category->set('category', PKG_NAME);
$category->set('parent', 0);
$modx->log(modX::LOG_LEVEL_INFO, 'Created default elements category.');
flush();

/* Add snippets (to category) */
$snippets = include $sources['data'] . 'transport.snippets.php';
if (!empty($snippets) && is_array($snippets)) {
    $category->addMany($snippets);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in <b>' . count($snippets) . '</b> snippet(s).');
} else {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Could not package in snippet(s). Data missing.');
}
flush();
unset($snippets);

/* Add chunks (to category) */
$chunks = include $sources['data'] . 'transport.chunks.php';
if (!empty($chunks) && is_array($chunks)) {
    $category->addMany($chunks);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in <b>' . count($chunks) . '</b> chunk(s).');
} else {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Could not package in chunks. Data missing.');
}
flush();
unset($chunks);

/* Add plugins (to category) */
$plugins = include $sources['data'] . 'transport.plugins.php';
if (!empty($plugins) && is_array($plugins)) {
    $category->addMany($plugins);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in <b>' . count($plugins) . '</b> plugin(s).');
} else {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Could not package in plugins. Data missing.');
}
flush();
unset($plugins);

/* Add templates (to category) */
$templates = include $sources['data'] . 'transport.templates.php';
if (!empty($templates) && is_array($templates)) {
    $category->addMany($templates);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in <b>' . count($templates) . '</b> template(s).');
} else {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Could not package in templates. Data missing.');
}
flush();
unset($templates);

/* Add TVs (to category) */
/*
$tvs = include $sources['data'] . 'transport.tvs.php';
if (!empty($tvs) && is_array($tvs)) {
    $category->addMany($tvs);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in <b>' . count($tvs) . '</b> TV(s).');
} else {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Could not package in TV(s). Data missing.');
}

flush();
unset($tvs);
*/

/* Create category vehicle for all elements */
$attributes = [
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
        'Snippets' => [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
        ],
        'Chunks' => [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
        ],
        'Plugins' => [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
                'PluginEvents' => [
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => false,
                    xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'],
                ],
            ],
        ],
        'Templates' => [
            xPDOTransport::UNIQUE_KEY => 'templatename',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
        ],
        'TemplateVars' => [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
        ],
    ],
    xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
];

$modx->log(modX::LOG_LEVEL_INFO, 'Adding category vehicle for all elements...');

// Exclude files with a specific pattern (eg. __ prefix)
$categoryAttributes = array_merge($attributes, ['copy_exclude_patterns' => ['/^__/']]);
$vehicle = $builder->createVehicle($category, $categoryAttributes);
flush();
// Don't unset $vehicle as we still need it to add resolvers and validators!
unset($category, $attributes, $categoryAttributes);

/* Add file resolvers */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolvers...');
$vehicle->resolve('file', [
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
]);
$vehicle->resolve('file', [
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH . 'components/';",
]);

/* Add PHP validators and resolvers (keep oder!) */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding PHP validators and resolvers...');
$vehicle->validate('php', ['source' => $sources['validators'] . 'validate.requirements.php']);
$vehicle->validate('php', ['source' => $sources['validators'] . 'validate.preinstall.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.bootstrap.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.system-settings.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.setupoptions.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.dbtables.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.dbchanges.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.gngroup-tablescontent.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.newsletter-tpl-categories.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.resources.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.customresources.php']);
$vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.customresourceproperties.php']);
//$vehicle->resolve('php', ['source' => $sources['resolvers'].'resolve.tvs.php']);

$builder->putVehicle($vehicle);
flush();
unset($vehicle);

/* Add the license file, readme and setup options */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding package attributes and setup options...');
$builder->setPackageAttributes([
    'license'   => file_get_contents($sources['root'] . 'LICENSE.md'),
    'readme'    => file_get_contents($sources['root'] . 'README.md'),
    'changelog' => file_get_contents($sources['root'] . 'CHANGELOG.md'),
    'copy_exclude_patterns' => ['/^__/'],
    'setup-options' => [
        'source' => $sources['build'] . 'setup.options.php',
    ],
]);

/* Create zip package */
$modx->log(modX::LOG_LEVEL_INFO, 'Packing transport package zip...');
$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, 'Transport package built.');
$modx->log(modX::LOG_LEVEL_INFO, 'Execution time: ' . $totalTime);
flush();

exit();
