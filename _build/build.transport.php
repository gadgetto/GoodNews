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


/* Define package name and namespace */
define('PKG_NAME', 'GoodNews');
define('PKG_NAMESPACE', strtolower(PKG_NAME));


/* Define build paths */
$root = dirname(dirname(__FILE__)).'/';
$sources = array(
    'root'           => $root,
    'build'          => $root.'_build/',
    'includes'       => $root.'_build/includes/',
    'data'           => $root.'_build/data/',
    'events'         => $root.'_build/data/events/',    
    'properties'     => $root.'_build/properties/',
    'validators'     => $root.'_build/validators/',
    'resolvers'      => $root.'_build/resolvers/',
    'packages'       => $root.'_packages/',
    'chunks'         => $root.'core/components/'.PKG_NAMESPACE.'/elements/chunks/',
    'plugins'        => $root.'core/components/'.PKG_NAMESPACE.'/elements/plugins/',
    'resources'      => $root.'core/components/'.PKG_NAMESPACE.'/elements/resources/',
    'snippets'       => $root.'core/components/'.PKG_NAMESPACE.'/elements/snippets/',
    'templates'      => $root.'core/components/'.PKG_NAMESPACE.'/elements/templates/',
    'lexicon'        => $root.'core/components/'.PKG_NAMESPACE.'/lexicon/',
    'docs'           => $root.'core/components/'.PKG_NAMESPACE.'/docs/',
    'source_core'    => $root.'core/components/'.PKG_NAMESPACE,
    'source_assets'  => $root.'assets/components/'.PKG_NAMESPACE,
);
unset($root);

require_once $sources['root'].'config.core.php';
require_once $sources['includes'].'functions.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';


/* Connect to MODx */
$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
echo '<pre>';
flush();


/* Load GoodNews class to get VERSION and RELEASE */
$modelPath = $modx->getOption('goodnews.core_path').'model/';
if (!$modx->loadClass('goodnews.GoodNews', $modelPath, false, true)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'GoodNews class could not be loaded.');
    exit();
}


/* Define package version and release */
define('PKG_VERSION', GoodNews::VERSION);
define('PKG_RELEASE', GoodNews::RELEASE);


/* Prepare Transport Package */
$modx->log(modX::LOG_LEVEL_INFO, 'Building transport package for '.PKG_NAMESPACE.'-'.PKG_VERSION.'-'.PKG_RELEASE);
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->directory = $sources['packages'];
$builder->createPackage(PKG_NAMESPACE, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAMESPACE, false, true, '{core_path}components/'.PKG_NAMESPACE.'/', '{assets_path}components/'.PKG_NAMESPACE.'/');
$modx->log(modX::LOG_LEVEL_INFO, 'Prepared Transport Package and registered Namespace.');
flush();


/* Add menu */
$menu = include $sources['data'].'transport.menu.php';
if (empty($menu)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in menu.');
} else {
    $vehicle = $builder->createVehicle($menu, array(
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'text',
    ));
    $builder->putVehicle($vehicle);
    $modx->log(modX::LOG_LEVEL_INFO,'Packaged in menu.');
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
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($settings).' system setting(s).');
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
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($snippets).' snippet(s).');
}
flush();
unset($snippets);


/* Add chunks (to category) */
$chunks = include $sources['data'].'transport.chunks.php';
if (!is_array($chunks)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding chunks failed.');
} else {
    $category->addMany($chunks);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($chunks).' chunk(s).');
}
flush();
unset($chunks);


/* Add templates (to category) */
$templates = include $sources['data'].'transport.templates.php';
if (!is_array($templates)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding templates failed.');
} else {
    $category->addMany($templates);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($templates).' template(s).');
}
flush();
unset($templates);


/* Add TVs (to category) */
$tvs = include $sources['data'].'transport.tvs.php';
if (!is_array($tvs)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding TVs failed.');
} else {
    $category->addMany($tvs);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($tvs).' TV(s).');
}
flush();
unset($tvs);


/* Add plugins (Vehicles) */
$plugins = include $sources['data'].'transport.plugins.php';
if (!is_array($plugins)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding plugins failed.');
} else {
	$category->addMany($plugins);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($plugins).' Plugin(s).');
}
flush();
unset($plugins);


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
    ),
    xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
);

// Exclude files with a specific pattern (eg. __ prefix)
$categoryAttributes = array_merge($attributes, array('copy_exclude_patterns' => array('/^__/')));
$vehicle = $builder->createVehicle($category, $categoryAttributes);
unset($category, $attributes, $categoryAttributes); // don't unset $vehicle as we still need it to add file and PHP resolvers!


/* Add file resolvers */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolvers...');
$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH.'components/';",
));
$vehicle->resolve('file',array(
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH.'components/';",
));


/* Add PHP resolvers (keep oder of PHP resolvers!) */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding PHP validators and resolvers...');
$vehicle->validate('php', array('source' => $sources['validators'].'validate.preinstall.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.settings.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.setupoptions.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.tables.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.dbchanges.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.tablescontent.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.newslettertemplates.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.resources.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.customresources.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.customresourceproperties.php'));
$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.plugins.php'));
//$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.tvs.php'));
//$vehicle->resolve('php', array('source' => $sources['resolvers'].'resolve.paths.php'));

$builder->putVehicle($vehicle);
flush();
unset($vehicle);


/* Add the license file, readme and setup options */
$builder->setPackageAttributes(array(
    'license'   => file_get_contents($sources['docs'].'license.txt'),
    'readme'    => file_get_contents($sources['docs'].'readme.txt'),
    'changelog' => file_get_contents($sources['docs'].'changelog.txt'),
    'copy_exclude_patterns' => array('/^__/'),
    'setup-options' => array(
        'source' => $sources['build'].'setup.options.php',
    ),
));
$modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');


/* Create zip package */
$modx->log(modX::LOG_LEVEL_INFO, 'Packing transport package zip...');
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
