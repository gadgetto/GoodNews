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
 * Resolve settings (assign setting values after they are installed by the transport package).
 *
 * @package goodnews
 * @subpackage build
 */

if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:

            $i = 0;
            $settingsAttributes[++$i] = array(
                'key'   => 'goodnews.default_container_template',
                'value' => 'sample.GoodNewsContainerTemplate',
                'xtype' => 'modx-combo-template',
            );
            /*
            $settingsAttributes[++$i] = array(
                'key'   => 'key_name',
                'value' => 'somevalue',
                'xtype' => 'modx-combo-boolean',
            );
            */
            
            if (is_array($settingsAttributes)) {
                foreach ($settingsAttributes as $attributes) {
                    
                    // Check if setting exists
                    $setting = $modx->getObject('modSystemSetting', array('key' => $attributes['key']));
                    if (!$setting) {
                        continue;
                    }
                    
                    switch ($attributes['xtype']) {
                        // Assign template id based on template name
                        case 'modx-combo-template':
                        
                            if (!empty($attributes['value'])) {
                                $templateObj = $modx->getObject('modTemplate', array('templatename' => $attributes['value']));
                                if ($templateObj) {
                                    $setting->set('value', $templateObj->get('id'));
                                } else {
                                    $modx->log(modX::LOG_LEVEL_ERROR, 'Settings Resolver - could not find template: '.$attributes['value']);
                                    continue;
                                }
                            }
                            if (!$setting->save()) {
                                $modx->log(modX::LOG_LEVEL_ERROR, 'Settings Resolver - could not save setting: '.$attributes['key']);
                            }
                            break;
                            
                        case 'modx-combo-boolean':
                            break;
                        
                        case 'numberfield':
                            break;
                        
                        case 'textfield':
                            break;
                    }
                }
            }

            break;

        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
unset($settingsAttributes, $attributes, $setting, $templateObj);
return true;
