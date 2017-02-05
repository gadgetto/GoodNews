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
 * Pre-installation scripts (validator).
 *
 * @package goodnews
 * @subpackage build
 */

if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
        
            // Add GoodNewsResource to Extension Packages system setting
            $modelPath = $modx->getOption('goodnews.core_path');
            if (empty($modelPath)) {
                $modelPath = '[[++core_path]]components/goodnews/model/';
            }
            if ($modx instanceof modX) {
                $modx->addExtensionPackage('goodnews', $modelPath);
                $modx->log(modX::LOG_LEVEL_INFO, 'Added "GoodNews" to extension_packages system setting.');
            }
            
            // do some other pre-install things here ...
            
            break;
            
        case xPDOTransport::ACTION_UNINSTALL:
            
             // Remove GoodNewsResource from Extension Packages system setting
            $modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/';
            if ($modx instanceof modX) {
                $modx->removeExtensionPackage('goodnews');
                $modx->log(modX::LOG_LEVEL_INFO, 'Removed "GoodNews" from extension_packages system setting.');
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
}
return true;
