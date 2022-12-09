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
 * Resolve system settings (assign setting values after they are installed by the transport package).
 *
 * @package goodnews
 * @subpackage build
 */

$i = 0;
$settingsAttributes[++$i] = [
    'key'   => 'goodnews.default_container_template',
    'value' => 'sample.GoodNewsContainerTemplate', // Literal name will be converted to ID later
    'xtype' => 'modx-combo-template',
];
 /*
 $settingsAttributes[++$i] = [
     'key'   => 'key_name',
     'value' => 'somevalue',
     'xtype' => 'modx-combo-boolean',
 ];
 */


if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            
            if (is_array($settingsAttributes)) {
                foreach ($settingsAttributes as $attributes) {
                    
                    $modx->log(modX::LOG_LEVEL_INFO, 'System settings rersolver - assign settings...');
                    
                    // Check if setting exists
                    $setting = $modx->getObject('modSystemSetting', ['key' => $attributes['key']]);
                    if (!$setting) {
                        $modx->log(modX::LOG_LEVEL_ERROR, '-> could not find setting: ' . $attributes['key']);
                    } else {
                        
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
                                $modx->log(modX::LOG_LEVEL_INFO, '-> saved setting: ' . $attributes['key']);
                            } else {
                                $modx->log(modX::LOG_LEVEL_ERROR, '-> could not save setting: ' . $attributes['key']);
                            }
                        
                        } elseif ($attributes['xtype'] == 'modx-combo-boolean') {
                            
                        } elseif ($attributes['xtype'] == 'numberfield') {
                            
                        } elseif ($attributes['xtype'] == 'textfield') {
                            
                        }
                    }
                    
                }
            }
            
            break;
            
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($settingsAttributes, $attributes, $setting, $templateObj, $i);
return true;
