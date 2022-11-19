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
 * Pre-installation scripts (validator).
 *
 * @package goodnews
 * @subpackage build
 */

/**
 * Checks if an array key exists in a multi dimensional array
 *
 * @param array $arr
 * @param string $key
 * @return boolean
 */
if (!function_exists('arrayKeyExists')) {
    function arrayKeyExists(array $arr, $key) {
        if (!is_array($arr)) { return false; }
        // Is in root array?
        if (array_key_exists($key, $arr)) { return true; }
        // Check arrays contained in this array
        foreach ($arr as $element) {
            if (is_array($element)) {
                if (arrayKeyExists($element, $key)) {
                    return true;
                }
            }
        }
        return false;
    }
}


/** @var modX $modx */
$modx = &$object->xpdo;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        
        // @todo: move from deprecated extension_packages system setting to modExtensionPackage (required for 3.x!)
            
        // Check if GoodNews is already available in extension_packages
        // (using getObject method as we need uncached results)
        $setting = $modx->getObject('modSystemSetting', array('key' => 'extension_packages'));
        if (is_object($setting)) {
            $extPackages = $setting->get('value');
            $extPackages = $modx->fromJSON($extPackages);
            if (!empty($extPackages) && arrayKeyExists($extPackages, 'goodnews')) {
                break;
            }
        }
        
        // Add GoodNewsResource to Extension Packages system setting
        // (using getObject method as we need uncached results)
        $setting = $modx->getObject('modSystemSetting', array('key' => 'goodnews.core_path'));
        $modelPath = '';
        if (is_object($setting)) {
            $modelPath = $setting->get('value');
        }
        if (empty($modelPath)) {
            $modelPath = '[[++core_path]]components/goodnews/model/';
        }
        $modx->addExtensionPackage('goodnews', $modelPath);
        $modx->log(modX::LOG_LEVEL_INFO, 'Added "GoodNews" to extension_packages system setting.');
        
        // do some other pre-install things here ...
        
        break;
        
    case xPDOTransport::ACTION_UNINSTALL:
        
        // Check if GoodNews is available in extension_packages and remove it
        // (using getObject method as we need uncached results)
        $setting = $modx->getObject('modSystemSetting', array('key' => 'extension_packages'));
        if (is_object($setting)) {
            $extPackages = $setting->get('value');
            $extPackages = $modx->fromJSON($extPackages);
            if (!empty($extPackages) && arrayKeyExists($extPackages, 'goodnews')) {
                $modx->removeExtensionPackage('goodnews');
                $modx->log(modX::LOG_LEVEL_INFO, 'Removed "GoodNews" from extension_packages system setting.');
            }
        }
        
        // Set back all custom resources to modDocument resources
        $resources = $modx->getIterator('modResource', array('class_key' => 'GoodNewsResourceContainer'));
        foreach ($resources as $resource) {
            $resource->set('class_key', 'modDocument');
            $resource->set('hide_children_in_tree', false);
            $resource->save();
        }
        unset($resources, $resource);

        $resources = $modx->getIterator('modResource', array('class_key' => 'GoodNewsResourceMailing'));
        foreach ($resources as $resource) {
            $resource->set('class_key', 'modDocument');
            $resource->set('show_in_tree', true);
            $resource->save();
        }
        unset($resources, $resource);
        
        break;
}
    
return true;
