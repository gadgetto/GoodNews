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
 * Create default database entries in custom GoodNewsGroup table
 *
 * @package goodnews
 * @subpackage build
 */

$epoch = time();
$i = 0;

$grpAttributes[++$i] = [
    'id'            => $i,
    'name'          => 'Newsletters',
    'description'   => 'Default newsletters group',
    'modxusergroup' => 0,
    'createdon'     => $epoch,
    'createdby'     => 0,
    'editedon'      => $epoch,
    'editedby'      => 0,
];


if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        
            $modx->log(modX::LOG_LEVEL_INFO, 'Tables content resolver - creating database entries in GoodNewsGroup table...');
            
            // Add GoodNews package
            $modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/') . 'model/';
            $modx->addPackage('goodnews', $modelPath);
            $manager = $modx->getManager();
            
            if (is_array($grpAttributes)) {
                foreach ($grpAttributes as $attributes) {
                    
                    // Check if group already exists
                    $group = $modx->getObject('GoodNewsGroup', ['name' => $attributes['name']]);
                    if (!$group) {
                        // Create a GoodNews group
                        $group = $modx->newObject('GoodNewsGroup', $attributes);
                        if ($group->save()) {
                            $modx->log(modX::LOG_LEVEL_INFO, '-> created group: ' . $attributes['name']);
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, '-> could not create group: ' . $attributes['name']);
                        }
                    }
                }
            }
            
            break;
            
        case xPDOTransport::ACTION_UPGRADE:
            break;
            
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($grpAttributes, $attributes, $group, $epoch, $i);
return true;
