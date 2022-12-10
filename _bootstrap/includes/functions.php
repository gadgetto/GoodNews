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
use MODX\Revolution\modMenu;
use MODX\Revolution\modCategory;
use MODX\Revolution\modChunk;
use MODX\Revolution\modNamespace;
use MODX\Revolution\modPlugin;
use MODX\Revolution\modPluginEvent;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modSystemSetting;
use MODX\Revolution\modTemplate;
use MODX\Revolution\modTemplateVar;
use MODX\Revolution\modTemplateVarTemplate;
use MODX\Revolution\modResource;
use MODX\Revolution\Transport\modTransportPackage;
use MODX\Revolution\Error\modError;

/**
 * Helper functions for _bootstrap
 *
 * @package goodnews
 * @subpackage bootstrap
 */

/**
 * Create a batch of MODX resources (also custom resources).
 *
 * @param mixed &$modx
 * @param array $resources Resources data
 * @param array $sources Sources paths
 * @param boolean $custom Default resource or custom resource type
 * @return mixed/boolean
 */
function createModxResources(&$modx, $resources, $sources, $custom = false)
{
    $uCaseCustom = '';
    $lCaseCustom = '';
    if ($custom) {
        $uCaseCustom = 'Custom ';
        $lCaseCustom = 'custom ';
    }
    
    $modx->log(modX::LOG_LEVEL_INFO, 'Add ' . $lCaseCustom . 'MODX resource documents...');

    if (empty($resources) || !is_array($resources)) {
        $modx->log(modX::LOG_LEVEL_ERROR, $uCaseCustom . 'MODX resources could not be added. Data missing.');
        return false;
    }
        
    $count = 0;
    // $namespace will be namespace for properties array (if any)
    foreach ($resources as $namespace => $fieldvalues) {
        $upd = true;
        /** @var modResource $resource */
        $resource = $modx->getObject('modResource', ['pagetitle' => $fieldvalues['pagetitle']]);
        if (!is_object($resource)) {
            $upd = false;
            /* @var modResource $resource */
            $resource = $modx->newObject('modResource', ['pagetitle' => $fieldvalues['pagetitle']]);
        }
        
        // Replace content-template file-name with content-template content
        if (!empty($fieldvalues['content'])) {
            $filename = $sources['resources'] . $fieldvalues['content'];
            if (file_exists($filename)) {
                $fieldvalues['content'] = file_get_contents($filename);
            } else {
                $modx->log(modX::LOG_LEVEL_WARN, '-> content template file ' . $fieldvalues['content'] . ' for ' . $lCaseCustom . 'MODX resource ' . $fieldvalues['pagetitle'] . ' not found. No content assigned.');
            }
        }
        
        // Replace template name by template ID
        if (!empty($fieldvalues['template'])) {
            if ($fieldvalues['template'] == 'default') {
                $fieldvalues['template'] = $modx->getOption('default_template');
            } else {
                /* @var modTemplate $templateObj */
                $templateObj = $modx->getObject('modTemplate', ['templatename' => $fieldvalues['template']]);
                if ($templateObj) {
                    $fieldvalues['template'] = $templateObj->get('id');
                } else {
                    $fieldvalues['template'] = $modx->getOption('default_template');
                    $modx->log(modX::LOG_LEVEL_WARN, '-> template ' . $fieldvalues['template'] . ' for ' . $lCaseCustom . 'MODX resource ' . $fieldvalues['pagetitle'] . ' not found. Default template assigned.');
                }
            }
        }
        
        // Replace parent resource pagetitle with resource ID
        if (!empty($fieldvalues['parent'])) {
            /* @var modResource $parentObj */
            $parentObj = $modx->getObject('modResource', ['pagetitle' => $fieldvalues['parent']]);
            if ($parentObj) {
                $fieldvalues['parent'] = $parentObj->get('id');
            } else {
                $modx->log(modX::LOG_LEVEL_WARN, '-> parent resource ' . $fieldvalues['parent'] . ' for ' . $lCaseCustom . 'MODX resource ' . $fieldvalues['pagetitle'] . ' not found. No parent assigned.');
            }
        }
        
        // Get properties array from $fieldvalues and empty 'properties' key
        $properties = $fieldvalues['properties'];
        $fieldvalues['properties'] = null;
        
        // Set resource fieldvalues
        $resource->fromArray($fieldvalues);
        
        // Set resource properties
        if (!empty($properties) && is_array($properties)) {
            $resource->setProperties($properties, $namespace);
        }
        
        $upd_or_added = ($upd) ? 'updated' : 'added';
        if ($resource->save()) {
            ++$count;
            $modx->log(modX::LOG_LEVEL_INFO, '-> ' . $upd_or_added . ' ' . $lCaseCustom . 'MODX resource: ' . $fieldvalues['pagetitle']);
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> ' . $lCaseCustom . 'MODX resource ' . $fieldvalues['pagetitle'] . ' could not be ' . $upd_or_added . '. Saving failed!');
        }
    }
    
    return $count;
}

/**
 * Assign a batch of setting values.
 *
 * @param mixed &$modx
 * @param array $settingAttributes Setting attributes array
 * @return mixed/boolean
 */
function assignSettings(&$modx, $settingAttributes)
{
    $modx->log(modX::LOG_LEVEL_INFO, 'Assign setting values...');
    
    if (empty($settingAttributes) || !is_array($settingAttributes)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Setting values could not be assigned. Data missing.');
        return false;
    }
    
    $count = 0;
    foreach ($settingAttributes as $attributes) {
        // Check if setting exists
        $setting = $modx->getObject('modSystemSetting', ['key' => $attributes['key']]);
        if (!$setting) {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> could not find setting: ' . $attributes['key']);
            continue;
        }
        
        if ($attributes['xtype'] == 'modx-combo-template') {
            // Assign template id based on template name
            if (!empty($attributes['value'])) {
                $templateObj = $modx->getObject('modTemplate', ['templatename' => $attributes['value']]);
                if ($templateObj) {
                    $setting->set('value', $templateObj->get('id'));
                } else {
                    $setting->set('value', 0);
                    $modx->log(modX::LOG_LEVEL_ERROR, '-> could not find template: ' . $attributes['value']);
                }
            }
            if ($setting->save()) {
                ++$count;
                $modx->log(modX::LOG_LEVEL_INFO, '-> saved setting: ' . $attributes['key']);
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, '-> could not save setting: ' . $attributes['key']);
            }
        } elseif ($attributes['xtype'] == 'modx-combo-boolean') {
        } elseif ($attributes['xtype'] == 'numberfield') {
        } elseif ($attributes['xtype'] == 'textfield') {
        }
    }
    
    return $count;
}

/**
 * Create a batch of element categories.
 *
 * @param mixed &$modx
 * @param array $categories An array of category objects
 * @param integer $defaultCategoryId The ID of the package category
 * @return mixed/boolean
 */
function createElementCategories(&$modx, $categories, $defaultCategoryId = 0)
{
    $modx->log(modX::LOG_LEVEL_INFO, 'Adding additional elements categories...');
    if (empty($categories) || !is_array($categories)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Additional elements categories could not be added. Data missing.');
        return false;
    }
    
    $count = 0;
    foreach ($categories as $category) {
        $categoryName = $category->get('category');
        $parentName = $category->get('parent');
        $parentId = 0;
        if ($parentName == 'default') {
            $parentId = $defaultCategoryId;
        } else {
            $parentId = getCategoryID($modx, $parentName);
            if (!$parentId) {
                $modx->log(xPDO::LOG_LEVEL_INFO, '-> could not find parent category ' . $parentName . '. Parent for ' . $categoryName . ' will be root.');
            }
        }
        // Create category (if not already exists)
        if (!getCategoryID($modx, $categoryName)) {
            $category->set('parent', $parentId);
            if ($category->save()) {
                ++$count;
                $modx->log(xPDO::LOG_LEVEL_INFO, '-> added additional category: ' . $categoryName);
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, '-> additional category ' . $categoryName . ' could not be added. Saving failed!');
            }
        } else {
            $modx->log(xPDO::LOG_LEVEL_INFO, '-> additional category ' . $categoryName . ' already exists - skipped!');
        }
    }
    
    return $count;
}

/**
 * Create database tables.
 *
 * @param mixed &$modx
 * @param array $tables An array database table-names
 * @return mixed/boolean
 */
function createDatabaseTables(&$modx, $tables)
{
    $modx->log(modX::LOG_LEVEL_INFO, 'Creating database tables...');
    if (empty($tables) || !is_array($tables)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Database tables could not be added. Data missing.');
        return false;
    }

    $manager = $modx->getManager();
    $count = 0;
    foreach ($tables as $table) {
        $tableName = $modx->getTableName($table);
        $prevLogLevel = $modx->setLogLevel(modX::LOG_LEVEL_ERROR); // Do not report table creation detailes
        $created = $manager->createObjectContainer($table);
        $modx->setLogLevel($prevLogLevel);
        if ($created) {
            ++$count;
            $modx->log(modX::LOG_LEVEL_INFO, '-> added database table: ' . $tableName);
        } else {
            $modx->log(modX::LOG_LEVEL_INFO, '-> database table ' . $tableName . ' already exists - skipped!');
        }
    }
    
    return $count;
}

/**
 * Create entries in database tables.
 *
 * @param mixed &$modx
 * @param array $categories An array of data
 * @return mixed/boolean
 */
function createDatabaseEntries(&$modx, $entries)
{
    $modx->log(modX::LOG_LEVEL_INFO, 'Creating entries in custom database tables...');
    if (empty($entries) || !is_array($entries)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Database entries could not be added. Data missing.');
        return false;
    }
    
    $count = 0;
    foreach ($entries as $class => $attributes) {
        // Check if entry already exists
        $obj = $modx->getObject($class, $attributes);
        if ($obj) {
            continue;
        }
        $obj = $modx->newObject($class, $attributes);
        if ($obj->save()) {
            ++$count;
            $modx->log(modX::LOG_LEVEL_INFO, '-> added entry in: ' . $class);
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> entry in ' . $class . ' could not be added. Saving failed!');
        }
    }

    return $count;
}

/**
 * Assign a batch of templates to categories.
 *
 * @param mixed &$modx
 * @param array $templateCategories An array of template => category associations
 * @return mixed/boolean
 */
function assignTemplateCategories(&$modx, $templateCategories)
{
    $modx->log(modX::LOG_LEVEL_INFO, 'Assign templates to categories...');
    
    if (empty($templateCategories) || !is_array($templateCategories)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Templates could not be assigned to categories. Data missing.');
        return false;
    }
    
    $count = 0;
    foreach ($templateCategories as $templateName => $categoryName) {
        // Check if template exists
        $template = $modx->getObject('modTemplate', ['templatename' => $templateName]);
        if (!$template) {
            $modx->log(modX::LOG_LEVEL_WARN, '-> template ' . $templateName . ' does not exist. No category assigned.');
            continue;
        }
        
        $template->set('category', getCategoryID($modx, $categoryName));
        if ($template->save()) {
            ++$count;
            $modx->log(modX::LOG_LEVEL_INFO, '-> assigned template ' . $templateName . ' to category ' . $categoryName);
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, '-> could not assign template ' . $templateName . ' to category ' . $categoryName);
        }
    }
    
    return $count;
}

/**
 * Creates an object.
 *
 * @param mixed &$modx
 * @param string $className
 * @param array $data
 * @param string $primaryField
 * @param bool $update
 * @return bool
 */
function createObject(&$modx, $className = '', array $data = [], $primaryField = '', $update = true)
{
    /* @var xPDOObject $object */
    $object = null;

    /* Attempt to get the existing object */
    if (!empty($primaryField)) {
        if (is_array($primaryField)) {
            $condition = [];
            foreach ($primaryField as $key) {
                $condition[$key] = $data[$key];
            }
        } else {
            $condition = [$primaryField => $data[$primaryField]];
        }

        $object = $modx->getObject($className, $condition);
        if ($object instanceof $className) {
            if ($update) {
                $object->fromArray($data);
                return $object->save();
            } else {
                $condition = $modx->toJSON($condition);
                $modx->log(modX::LOG_LEVEL_INFO, "-> skipping object {$className} {$condition}. Already exists!");
                return true;
            }
        }
    }

    /* Create new object if it doesn't exist */
    if (!$object) {
        $object = $modx->newObject($className);
        $object->fromArray($data, '', true);
        return $object->save();
    }

    return false;
}

/**
 * Create a system setting.
 *
 * @param mixed &$modx
 * @param mixed $key
 * @param mixed $value
 * @param string $xtype
 * @param string $namespace
 * @return boolean
 */
function createSystemSetting(&$modx, $key, $value, $namespace, $xtype = 'textfield', $area = 'Development')
{
    $exists = $modx->getCount('modSystemSetting', ['key' => "{$namespace}.{$key}"]);
    $saved = false;
    if (!$exists) {
        $setting = $modx->newObject('modSystemSetting');
        $setting->set('key', "{$namespace}.{$key}");
        $setting->set('value', $value);
        $setting->set('xtype', $xtype);
        $setting->set('namespace', $namespace);
        $setting->set('area', $area);
        $setting->set('editedon', time());
        if ($setting->save()) {
            $saved = true;
        }
    }
    return $saved;
}

/**
 * Cecks if a MODX transport package is installed.
 *
 * @param mixed &$modx
 * @param string $name Name of transport package
 * @return boolean
 */
function isTransportPackageInstalled(&$modx, $tpname)
{
    $installed = false;
    /** @var transport.modTransportPackage $package */
    $package = $modx->getObject('transport.modTransportPackage', [
        'package_name' => $tpname,
    ]);
    if (is_object($package)) {
        $installed = true;
    }
    return $installed;
}

/**
 * Cecks if a MODX namespace exists.
 *
 * @param mixed &$modx
 * @param string $name Name of namespace
 * @return boolean
 */
function existsNamespace(&$modx, $nspace)
{
    $exists = false;
    /** @var modNamespace $namespace */
    $namespace = $modx->getObject('modNamespace', ['name' => $nspace,]);
    if (is_object($namespace)) {
        $exists = true;
    }
    return $exists;
}

/**
 * Get ID of a MODX category.
 *
 * @param mixed &$modx
 * @param mixed $name
 * @return int category ID | 0 if not found
 */
function getCategoryID(&$modx, $name)
{
    $id = 0;
    $categoryObj = $modx->getObject('modCategory', ['category' => $name]);
    if (is_object($categoryObj)) {
        $id = $categoryObj->get('id');
    }
    return $id;
}

/**
 * Fetch the assets url.
 *
 * @param string $namespace
 * @return string
 */
function fetchAssetsUrl($namespace)
{
    $url = 'http';
    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) {
        $url .= 's';
    }
    $url .= '://' . $_SERVER["SERVER_NAME"];
    if ($_SERVER['SERVER_PORT'] != '80') {
        $url .= ':' . $_SERVER['SERVER_PORT'];
    }
    $requestUri = $_SERVER['REQUEST_URI'];
    $bootstrapPos = strpos($requestUri, '_bootstrap/');
    $requestUri = rtrim(substr($requestUri, 0, $bootstrapPos), '/') . '/';

    return "{$url}{$requestUri}assets/components/" . $namespace . '/';
}

/**
 * Get content of php file.
 *
 * @param string $filename
 * @return mixed|string
 */
function getPHPFileContent($filename)
{
    $o = file_get_contents($filename);
    $o = str_replace('<?php', '', $o);
    $o = str_replace('?>', '', $o);
    $o = trim($o);
    return $o;
}
