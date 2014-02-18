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
 * Build script for GoodNews transport package
 *
 * @package goodnews 
 * @subpackage build
 */

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);


/* Define package name */
define('PKG_NAME', 'GoodNews');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));

/* Define build paths */
$root = dirname(dirname(__FILE__)).'/';
$sources = array(
    'root'           => $root,
    'build'          => $root.'_build/',
    'includes'       => $root.'_build/includes/',
    'data'           => $root.'_build/data/',
    'events'         => $root.'_build/data/events/',    
    'properties'     => $root.'_build/properties/',
    'resolvers'      => $root.'_build/resolvers/',
    'packages'       => $root.'_packages/',
    'chunks'         => $root.'core/components/'.PKG_NAME_LOWER.'/elements/chunks/',
    'plugins'        => $root.'core/components/'.PKG_NAME_LOWER.'/elements/plugins/',
    'resources'      => $root.'core/components/'.PKG_NAME_LOWER.'/elements/resources/',
    'snippets'       => $root.'core/components/'.PKG_NAME_LOWER.'/elements/snippets/',
    'templates'      => $root.'core/components/'.PKG_NAME_LOWER.'/elements/templates/',
    'lexicon'        => $root.'core/components/'.PKG_NAME_LOWER.'/lexicon/',
    'docs'           => $root.'core/components/'.PKG_NAME_LOWER.'/docs/',
    'source_core'    => $root.'core/components/'.PKG_NAME_LOWER,
    'source_assets'  => $root.'assets/components/'.PKG_NAME_LOWER,
);
unset($root);

require_once $sources['root'].'config.core.php';
require_once $sources['includes'].'functions.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';

/* Connect to MODx */
$modx = new modX();
$modx->initialize('mgr');
echo '<pre>';
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

/* Add GoodNews package to get VERSION and RELEASE */
$corePath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/');
$goodnews = $modx->getService('goodnews', 'GoodNews', $corePath.'model/goodnews/');
if (!($goodnews instanceof GoodNews)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'GoodNews class could not be loaded.');
    exit();
}

/* Define package version and release */
define('PKG_VERSION', GoodNews::VERSION);
define('PKG_RELEASE', GoodNews::RELEASE);

/* Prepare Transport Package */
$modx->log(modX::LOG_LEVEL_INFO, 'Building transport package for '.PKG_NAME_LOWER.'-'.PKG_VERSION.'-'.PKG_RELEASE);
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->directory = $sources['packages'];
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/'.PKG_NAME_LOWER.'/', '{assets_path}components/'.PKG_NAME_LOWER.'/');
$modx->log(modX::LOG_LEVEL_INFO, 'Prepared Transport Package and registered Namespace.');
flush();

/* Add menu and action */
$menu = include $sources['data'].'transport.menu.php';
if (empty($menu)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in menu and action.');
} else {
    $vehicle = $builder->createVehicle($menu, array(
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'text',
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
            'Action' => array(
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => array('namespace','controller'),
            ),
        ),
    ));
    $builder->putVehicle($vehicle);
    $modx->log(modX::LOG_LEVEL_INFO,'Packaged in menu and action.');
}
flush();
unset($vehicle, $menu);

/* Add system settings */
$settings = include $sources['data'].'transport.settings.php';
if (!is_array($settings)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in settings.');
} else {
    $attributes = array(
        xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    );
    foreach ($settings as $setting) {
        $vehicle = $builder->createVehicle($setting, $attributes);
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($settings).' system settings.');
}
flush();
unset($vehicle, $settings, $setting, $attributes);

/* Create elements category */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PKG_NAME);
$modx->log(modX::LOG_LEVEL_INFO, 'Created elements category.');
flush();

/* Add snippets (to category) */
$snippets = include $sources['data'].'transport.snippets.php';
if (!is_array($snippets)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding snippets failed.');
} else {
    $category->addMany($snippets);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($snippets).' snippets.');
}
flush();
unset($snippets);

/* Add chunks (to category) */
$chunks = include $sources['data'].'transport.chunks.php';
if (!is_array($chunks)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding chunks failed.');
} else {
    $category->addMany($chunks, 'Chunks');
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($chunks).' chunks.');
}
flush();
unset($chunks);

/* Add templates (to category) */
$templates = include $sources['data'].'transport.templates.php';
if (!is_array($templates)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding templates failed.');
} else {
    $category->addMany($templates, 'Templates');
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($templates).' templates.');
}
flush();
unset($templates);

/* Add TVs (to category) */
$tvs = include $sources['data'].'transport.tvs.php';
if (!is_array($tvs)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding TVs failed.');
} else {
    $category->addMany($tvs, 'TemplateVars');
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($tvs).' TVs.');
}
flush();
unset($tvs);

/* Add plugins (Vehicles) */
$plugins = include $sources['data'].'transport.plugins.php';
if (!is_array($plugins)) {
    $modx->log(modX::LOG_LEVEL_FATAL,'Adding plugins failed.');
} else {
    $attributes= array(
        xPDOTransport::UNIQUE_KEY => 'name',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
            'PluginEvents' => array(
                xPDOTransport::PRESERVE_KEYS => true,
                xPDOTransport::UPDATE_OBJECT => false,
                xPDOTransport::UNIQUE_KEY => array('pluginid','event'),
            ),
        ),
    );
    foreach ($plugins as $plugin) {
        $vehicle = $builder->createVehicle($plugin, $attributes);
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($plugins).' plugins.');
}
flush();
unset($plugins, $plugin, $attributes, $vehicle);

/* Add Resources (Vehicles) */
$resources = include $sources['data'].'transport.resources.php';
if (!is_array($resources)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding Resources failed.');
} else {
    $attributes = array(
        xPDOTransport::UNIQUE_KEY => 'pagetitle',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
            'ContentType' => array(
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'name',
            ),
        ),
    );
    foreach ($resources as $resource) {
        $vehicle = $builder->createVehicle($resource, $attributes);
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($resources).' Resources.');
}
flush();
unset($resources, $resource, $attributes, $vehicle);

/* Create category vehicle for all elements */
$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Snippets' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
        'Chunks' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
        'Plugins' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
        'Templates' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'templatename',
        ),
        'TemplateVars' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
    )
);
// Exclude files with a specific pattern (eg. __ prefix)
$categoryAttributes = array_merge($attributes, array('copy_exclude_patterns' => array('/^__/')));

$vehicle = $builder->createVehicle($category, $categoryAttributes);
$builder->putVehicle($vehicle);
unset($category, $attributes, $categoryAttributes);

/* Add file and PHP resolvers (keep of oder of resolvers!) */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding file and PHP resolvers to category vehicle...');
$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH.'components/';",
));
$vehicle->resolve('file',array(
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH.'components/';",
));
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'].'resolve.extpack.php',
));
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'].'resolve.tables.php',
));
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'].'resolve.tablescontent.php',
));
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'].'resolve.settings.php',
));
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'].'resolve.newslettertemplates.php',
));
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'].'resolve.resources.php',
));
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'].'resolve.customresources.php',
));
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'].'resolve.customresourceproperties.php',
));
/*
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'].'resolve.tvs.php',
));
$vehicle->resolve('php',array(
    'source' => $sources['resolvers'].'resolve.setupoptions.php',
));
$vehicle->resolve('php',array(
    'source' => $sources['resolvers'].'resolve.paths.php',
));
*/
$builder->putVehicle($vehicle);
flush();
unset($vehicle);

/* Add the license file, readme and setup options */
$builder->setPackageAttributes(array(
    'license'   => file_get_contents($sources['docs'].'license.txt'),
    'readme'    => file_get_contents($sources['docs'].'readme.txt'),
    'changelog' => file_get_contents($sources['docs'].'changelog.txt'),
    'copy_exclude_patterns' => array('/^__/'),
    //'setup-options' => array(
    //    'source' => $sources['build'].'setup.options.php',
    //),
));
$modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');

/* Create zip package */
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, 'Transport Package Built.');
$modx->log(modX::LOG_LEVEL_INFO, 'Execution time: '.$totalTime);
flush();

exit();
