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
 * Create default database entries in custom tables
 *
 * @package goodnews
 * @subpackage build
 */

if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:

            // Add GoodNews package
            $modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/';
            $modx->addPackage('goodnews', $modelPath);
            $manager = $modx->getManager();

            $epoch = time();
            
            $i = 0;
            $grpAttributes[++$i] = array(
                'id'            => $i,
                'name'          => 'Newsletters',
                'description'   => 'Default newsletters group',
                'modxusergroup' => 0,
                'createdon'     => $epoch,
                'createdby'     => 0,
                'editedon'      => $epoch,
                'editedby'      => 0,
            );            

            if (is_array($grpAttributes)) {
                foreach ($grpAttributes as $attributes) {
                    
                    // Check if group already exists
                    $group = $modx->getObject('GoodNewsGroup', array('name' => $attributes['name']));
                    if ($group) {
                        $modx->log(modX::LOG_LEVEL_INFO, 'Tables Content Resolver - group '.$attributes['name'].' already exists.');
                        continue;
                    }

                    // Create a GoodNews group
                    $group = $modx->newObject('GoodNewsGroup', $attributes);
                    if (!$group->save()) {
                        $modx->log(modX::LOG_LEVEL_ERROR, 'Tables Content Resolver - could not save group: '.$attributes['name']);
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
unset($grpAttributes, $attributes, $group);
return true;
