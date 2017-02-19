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
 * Assign events to Plugins.
 *
 * @package goodnews
 * @subpackage build
 */

$events = array(
    'OnManagerPageInit' => 'GoodNews',
    'OnUserRemove' => 'GoodNews',
);


if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
        
            $modx->log(modX::LOG_LEVEL_INFO, 'Plugin Event Resolver - assigning Plugin events...');

            foreach ($events as $event => $plugin) {
                /** @var modPlugin $pluginObj */
    			$pluginObj = $modx->getObject('modPlugin', array('name' => $plugin));
    			if (!is_object($pluginObj)) {
        			$modx->log(xPDO::LOG_LEVEL_INFO, 'Plugin Event Resolver - could not find Plugin: '.$plugin);
                } else {
                    $pluginId = $pluginObj->get('id');
                    $alreadyAssigned = (bool)$modx->getCount('modPluginEvent', array('pluginid' => $pluginId, 'event' => $event));
                    if (!$alreadyAssigned) {
                        /** @var modPluginEvent $pluginEventObj */
                        $pluginEventObj = $modx->newObject('modPluginEvent');
                        $pluginEventObj->set('event', $event);
                        $pluginEventObj->set('pluginid', $pluginId);
                        if ($pluginEventObj->save()) {
                            $modx->log(xPDO::LOG_LEVEL_INFO, '-> assigned Event '.$event.' to Plugin '.$plugin);
                        } else {
                            $modx->log(xPDO::LOG_LEVEL_ERROR, '-> could not assign Event '.$event.' to Plugin '.$plugin);
                        }
                    }
                    
                }
    		}

            break;
            
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
return true;
