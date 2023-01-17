<?php

/**
 * This file is part of the GoodNews package.
 *
 * @copyright bitego (Martin Gartner)
 * @license GNU General Public License v2.0 (and later)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use MODX\Revolution\modX;
use MODX\Revolution\modTemplate;
use MODX\Revolution\modSystemSetting;
use xPDO\Transport\xPDOTransport;

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
            if (empty($settingsAttributes) || !is_array($settingsAttributes)) {
                break;
            }
            foreach ($settingsAttributes as $attributes) {
                $modx->log(
                    modX::LOG_LEVEL_INFO,
                    'System settings rersolver - assign settings...'
                );
                // Check if setting exists
                $setting = $modx->getObject(modSystemSetting::class, ['key' => $attributes['key']]);
                if (!$setting) {
                    $modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '-> could not find setting: ' . $attributes['key']
                    );
                } else {
                    if ($attributes['xtype'] == 'modx-combo-template') {
                        // Assign template id based on template name
                        if (!empty($attributes['value'])) {
                            $templateObj = $modx->getObject(modTemplate::class, [
                                'templatename' => $attributes['value']
                            ]);
                            if ($templateObj) {
                                $setting->set('value', $templateObj->get('id'));
                            } else {
                                $setting->set('value', 0);
                                $modx->log(
                                    modX::LOG_LEVEL_ERROR,
                                    '-> could not find template: ' . $attributes['value']
                                );
                            }
                        }
                        if ($setting->save()) {
                            $modx->log(
                                modX::LOG_LEVEL_INFO,
                                '-> saved setting: ' . $attributes['key']
                            );
                        } else {
                            $modx->log(
                                modX::LOG_LEVEL_ERROR,
                                '-> could not save setting: ' . $attributes['key']
                            );
                        }
                    } elseif ($attributes['xtype'] == 'modx-combo-boolean') {
                    } elseif ($attributes['xtype'] == 'numberfield') {
                    } elseif ($attributes['xtype'] == 'textfield') {
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
