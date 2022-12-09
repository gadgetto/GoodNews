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
 * Pre-installation package requirements (validator)
 *
 * @package goodnews
 * @subpackage build
 */

/** @var modX $modx */
$modx = &$object->xpdo;
$success = false;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $success = true;

        // Only check requirements if not already done
        $setting = $modx->getObject('modSystemSetting', ['key' => 'goodnews.system_requirements_ok']);
        if (is_object($setting) && !empty($setting->get('value'))) {
            break;
        }

        $modx->log(modX::LOG_LEVEL_WARN, 'Checking if system meets minimum requirements...');
        
        $level = modX::LOG_LEVEL_INFO;
        $modxVersion = $modx->getVersionData();
        if (version_compare($modxVersion['full_version'], '2.3.0') < 0) {
            $level = modX::LOG_LEVEL_ERROR;
            $success = false;
        }
        $modx->log($level, '-> required MODX Revo version: min. 2.3.0 -- found: <b>' . $modxVersion['full_version'] . '</b>');
        
        $level = modX::LOG_LEVEL_INFO;
        if (version_compare(PHP_VERSION, '7.0.0') < 0) {
            $level = modX::LOG_LEVEL_ERROR;
            $success = false;
        }
        $modx->log($level, '-> required PHP version: min. 7.0 -- found: <b>' . PHP_VERSION . '</b>');
        
        if ($success) {
            // If OK create system setting to store requirements state
            $setting = $modx->newObject('modSystemSetting');
            $setting->fromArray([
                'key'       => 'goodnews.system_requirements_ok',
                'value'     => 'MODX Revolution ' . $modxVersion['full_version'] . ', PHP ' . PHP_VERSION,
                'xtype'     => 'textfield',
                'namespace' => 'goodnews',
                'area'      => '',
            ], '', true, true);
            $setting->save();
            $modx->log(modX::LOG_LEVEL_INFO, '<b style="color: green;">Minimum requirements for PHP and MODX versions reached!</b>');
        } else {
            // Remove system setting with requirements state (rollback)
            $setting = $modx->getObject('modSystemSetting', ['key' => 'goodnews.system_requirements_ok']);
            if (is_object($setting)) {
                $setting->remove();
            }
            $modx->log(modX::LOG_LEVEL_ERROR, '<b style="color: red;">Your system does not meet the minimum requirements. Installation aborted.</b>');
        }
        
        break;
        
    case xPDOTransport::ACTION_UNINSTALL:
        $success = true;
        
        // Remove system setting which stores requirements state
        $setting = $modx->getObject('modSystemSetting', ['key' => 'goodnews.system_requirements_ok']);
        if (is_object($setting)) {
            $setting->remove();
        }

        break;
}

unset($level);
return $success;
