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
 * Bootstrap script for setting up GoodNews development environment
 * (supports MODX version 2.3.0 up to 2.8.x)
 *
 * @package goodnews
 * @subpackage bootstrap
 */

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* Define package name and namespace */
define('PKG_NAME', 'GoodNews');
define('PKG_NAMESPACE', strtolower(PKG_NAME));

/* Define paths */
$root = dirname(__DIR__, 1) . '/';
$sources = array(
    'root'             => $root,
    'bootstrap'        => $root . '_bootstrap/',
    'includes'         => $root . '_bootstrap/includes/',
    'bootstrap_data'   => $root . '_bootstrap/data/',
    'build_data'       => $root . '_build/data/',
    'events'           => $root . '_build/data/events/',
    'properties'       => $root . '_build/data/properties/',
    'chunks'           => $root . 'core/components/' . PKG_NAMESPACE . '/elements/chunks/',
    'plugins'          => $root . 'core/components/' . PKG_NAMESPACE . '/elements/plugins/',
    'resources'        => $root . 'core/components/' . PKG_NAMESPACE . '/elements/resources/',
    'snippets'         => $root . 'core/components/' . PKG_NAMESPACE . '/elements/snippets/',
    'templates'        => $root . 'core/components/' . PKG_NAMESPACE . '/elements/templates/',
    'source_core'      => $root . 'core/components/' . PKG_NAMESPACE . '/',
    'source_assets'    => $root . 'assets/components/' . PKG_NAMESPACE . '/',
    'source_model'     => $root . 'core/components/' . PKG_NAMESPACE . '/model/' . PKG_NAMESPACE . '/',
);
unset($root);

require_once $sources['root'] . 'config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $sources['includes'] . 'functions.php';

/* Connect to MODX */
$modx = new modX();
$modx->initialize('mgr');
$modx->getService('error','error.modError', '', '');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
echo '<pre>';
flush();

$modx->log(modX::LOG_LEVEL_INFO, 'Building development environment for <b>' . PKG_NAME . '</b>...');

/* Get MODX version, eg. '2.8.4-pl' */
$modXversion = $modx->getVersionData();
$modXversion = $modXversion['full_version'];
$modx->log(modX::LOG_LEVEL_INFO, 'MODX version: ' . $modXversion);

/* Check if package is already installed */
if (isTransportPackageInstalled($modx, PKG_NAME)) {
    $modx->log(modX::LOG_LEVEL_WARN, PKG_NAME . ' is installed on this system.');
    $modx->log(modX::LOG_LEVEL_INFO, 'Building development environment stopped!');
    flush();
    exit();
}

/* Check if development environment for package is already available */
if (existsNamespace($modx, PKG_NAMESPACE)) {
    $modx->log(modX::LOG_LEVEL_WARN, 'It seems, that a development environment for ' . PKG_NAME . ' is already available on this system!');
    $modx->log(modX::LOG_LEVEL_INFO, 'Building development environment stopped!');
    flush();
    exit();
}

/** @var GoodNews $goodnews */
$goodnews = $modx->getService(PKG_NAMESPACE, PKG_NAME, $sources['source_model'], array(
    PKG_NAMESPACE . '.core_path' => $sources['source_core'],
));
$class = PKG_NAME;
if (!$goodnews instanceof $class) {
    $modx->log(modX::LOG_LEVEL_ERROR, PKG_NAME . ' service could not be loaded.');
    $modx->log(modX::LOG_LEVEL_INFO, 'Building development environment failed!');
    flush();
    exit();
}
unset($class);

/* Create namespace */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding namespace...');
if (createObject($modx, 'modNamespace', array(
    'name' => PKG_NAMESPACE,
    'path' => $sources['source_core'],
    'assets_path' => $sources['source_assets'],
), 'name', false)) {
    $modx->log(modX::LOG_LEVEL_INFO, '-> added namespace: ' . PKG_NAMESPACE);
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, '-> namespace ' . PKG_NAMESPACE . ' could not be added.');
    $modx->log(modX::LOG_LEVEL_INFO, 'Building development environment failed!');
    flush();
    exit();
}

/* Add menus (using sources from _build/data/ directory) */
$menus = include $sources['build_data'] . 'transport.menus.php';
$modx->log(modX::LOG_LEVEL_INFO, 'Adding menu entries...');
if (!empty($menus) && is_array($menus)) {
    foreach ($menus as $menu) {
        if ($menu->save()) {
            $modx->log(modX::LOG_LEVEL_INFO, '-> added menu entry');
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> menu entry could not be added. Saving failed!');
        }
    }
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Menu entries could not be added. Data missing.');
}
flush();
unset($menus, $menu);

/* Add system settings (using sources from _build/data/ directory) */
$settings = include $sources['build_data'] . 'transport.settings.php';
$modx->log(modX::LOG_LEVEL_INFO, 'Adding system settings...');
if (!empty($settings) && is_array($settings)) {
    foreach ($settings as $setting => $obj) {
        if ($obj->save()) {
            $modx->log(modX::LOG_LEVEL_INFO, '-> added system setting: ' . $setting);
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> system setting ' . $setting . ' could not be added. Saving failed!');
        }
    }
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, 'System settings could not be added. Data missing.');
}
flush();
unset($settings, $setting, $obj);

/* Create default elements category */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding default elements category...');
if (createObject($modx, 'modCategory', array(
    'category' => PKG_NAME,
    'parent' => 0,
), 'category', false)) {
    $modx->log(modX::LOG_LEVEL_INFO, '-> added default elements category: ' . PKG_NAME);
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, '-> default elements category ' . PKG_NAME . ' could not be added.');
}

/* Get ID of default elements category for later use */
$defaultCategoryId = 0;
/** @var modCategory $obj */
$obj = $modx->getObject('modCategory', array('category' => PKG_NAME));
if ($obj) {
    $defaultCategoryId = $obj->get('id');
}
flush();
unset($obj);

/* Add plugins (as static elements) (using sources from _build/data/ directory) */
$plugins = include $sources['build_data'] . 'transport.plugins.php';
$modx->log(modX::LOG_LEVEL_INFO, 'Adding plugins...');
if (!empty($plugins) && is_array($plugins)) {
    foreach ($plugins as $plugin) {
        $pluginName = $plugin->get('name');
        $plugin->set('category', $defaultCategoryId);
        $plugin->set('source', 0);
        $plugin->set('static', true);
        $plugin->set('plugincode', '');
        $pluginPath = $sources['plugins'] . strtolower($pluginName) . '.plugin.php';
        $plugin->set('static_file', $pluginPath);
        if ($plugin->save()) {
            $modx->log(modX::LOG_LEVEL_INFO, '-> added plugin: ' . $pluginName);
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> plugin ' . $pluginName . ' could not be added. Saving failed!');
        }
    }
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Plugins could not be added. Data missing.');
}
flush();
unset($plugins, $plugin, $pluginPath, $pluginName);


/* Add snippets (as static elements) (using sources from _build/data/ directory) */
$snippets = include $sources['build_data'] . 'transport.snippets.php';
$modx->log(modX::LOG_LEVEL_INFO, 'Adding snippets...');
if (!empty($snippets) && is_array($snippets)) {
    foreach ($snippets as $snippet) {
        $snippetName = $snippet->get('name');
        $snippet->set('category', $defaultCategoryId);
        $snippet->set('source', 0);
        $snippet->set('static', true);
        $snippet->set('snippet', '');
        $snippetPath = $sources['snippets'] . strtolower($snippetName) . '.snippet.php';
        $snippet->set('static_file', $snippetPath);
        if ($snippet->save()) {
            $modx->log(modX::LOG_LEVEL_INFO, '-> added snippet: ' . $snippetName);
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> snippet ' . $snippetName . ' could not be added. Saving failed!');
        }
    }
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Snippets could not be added. Data missing.');
}
flush();
unset($snippets, $snippet, $snippetPath, $snippetName);

/* Add chunks (as static elements) (using sources from _build/data/ directory) */
$chunks = include $sources['build_data'] . 'transport.chunks.php';
$modx->log(modX::LOG_LEVEL_INFO, 'Adding chunks...');
if (!empty($chunks) && is_array($chunks)) {
    foreach ($chunks as $chunk) {
        $chunkName = $chunk->get('name');
        $chunk->set('category', $defaultCategoryId);
        $chunk->set('source', 0);
        $chunk->set('static', true);
        $chunk->set('snippet', '');
        $chunkPath = $sources['chunks'] . strtolower($chunkName) . '.chunk.php';
        $chunk->set('static_file', $chunkPath);
        if ($chunk->save()) {
            $modx->log(modX::LOG_LEVEL_INFO, '-> added chunk: ' . $chunkName);
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> chunk ' . $chunkName . ' could not be added. Saving failed!');
        }
    }
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Chunks could not be added. Data missing.');
}
flush();
unset($chunks, $chunk, $chunkPath, $chunkName);

/* Add templates (as static elements) (using sources from _build/data/ directory) */
$templates = include $sources['build_data'] . 'transport.templates.php';
$modx->log(modX::LOG_LEVEL_INFO, 'Adding templates...');
if (!empty($templates) && is_array($templates)) {
    foreach ($templates as $template) {
        $templateName = $template->get('templatename');
        $templateCategory = $template->get('category');
        if (!empty($templateCategory)) {
            $categoryId = getCategoryID($modx, $templateCategory);
        } else {
            $categoryId = $defaultCategoryId;
        }
        $template->set('category', $categoryId);
        $template->set('source', 0);
        $template->set('static', true);
        $template->set('content', '');
        $templatePath = $sources['templates'] . strtolower($templateName) . '.template.php';
        $template->set('static_file', $templatePath);
        if ($template->save()) {
            $modx->log(modX::LOG_LEVEL_INFO, '-> added template: ' . $templateName);
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> template ' . $templateName . ' could not be added. Saving failed!');
        }
    }
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Templates could not be added. Data missing.');
}
flush();
unset($templates, $template, $templatePath, $templateName);

/* Add template variables */
// @todo add template variables

/**
 * The following parts are equivalent to the resolvers/validators of build script
 */

/* Add package to extension_packages (system setting) */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding package ' . PKG_NAME . ' to extension_packages system setting...');
$modx->removeExtensionPackage(PKG_NAMESPACE);
$modx->addExtensionPackage(PKG_NAMESPACE, $sources['source_core'] . 'model/');
flush();
 
/* Add development path settings */
$modx->log(modX::LOG_LEVEL_INFO, 'Adding development path settings...');
if (createSystemSetting($modx, 'core_path', $sources['source_core'], PKG_NAMESPACE)) {
    $modx->log(modX::LOG_LEVEL_INFO, '-> added path setting: ' . PKG_NAMESPACE . '.core_path');
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, '-> path setting ' . PKG_NAMESPACE . '.core_path could not be added.');
}

if (createSystemSetting($modx, 'assets_path', $sources['source_assets'], PKG_NAMESPACE)) {
    $modx->log(modX::LOG_LEVEL_INFO, '-> added path setting: ' . PKG_NAMESPACE . '.assets_path');
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, '-> path setting ' . PKG_NAMESPACE . '.assets_path could not be added.');
}

if (createSystemSetting($modx, 'assets_url', fetchAssetsUrl(PKG_NAMESPACE), PKG_NAMESPACE)) {
    $modx->log(modX::LOG_LEVEL_INFO, '-> added path setting: ' . PKG_NAMESPACE . '.assets_url');
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, '-> path setting ' . PKG_NAMESPACE . '.assets_url could not be added.');
}
flush();

/* Create database tables (using sources from _bootstrap/data/ directory) */
$tables = include $sources['bootstrap_data'] . 'dbtables.php';
createDatabaseTables($modx, $tables);
flush();
unset($tables, $table, $tableName, $created, $manager, $prevLogLevel);

/* Add entries for custom database tables (using sources from _bootstrap/data/ directory) */
$entries = include $sources['bootstrap_data'] . 'dbentries.php';
createDatabaseEntries($modx, $entries);
flush();
unset($entries);

/* Add additional elements categories (using sources from _bootstrap/data/ directory) */
$categories = include $sources['bootstrap_data'] . 'categories.php';
createElementCategories($modx, $categories, $defaultCategoryId);
flush();
unset($categories);

/* Add MODX resource documents (using sources from _bootstrap/data/ directory) */
$resources = include $sources['bootstrap_data'] . 'resources.php';
createModxResources($modx, $resources, $sources);
flush();
unset($resources);

/* Add custom MODX resource documents (using sources from _bootstrap/data/ directory) */
$customresources = include $sources['bootstrap_data'] . 'customresources.php';
createModxResources($modx, $customresources, $sources, true);
flush();
unset($customresources);

/* Assign setting values after all elements are created (using sources from _bootstrap/data/ directory) */
$settingAttributes = include $sources['bootstrap_data'] . 'settingattributes.php';
assignSettings($modx, $settingAttributes);
flush();
unset($settingAttributes);

/* Assign templates to categories (using sources from _bootstrap/data/ directory) */
$templateCategories = include $sources['bootstrap_data'] . 'tpl-categories.php';
assignTemplateCategories($modx, $templateCategories);
flush();
unset($templateCategories);

/* Clear the MODX cache */
$modx->cacheManager->refresh();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, 'Building development environment finished!');
$modx->log(modX::LOG_LEVEL_INFO, 'Execution time: ' . $totalTime);
echo '</pre>';
flush();
exit();
