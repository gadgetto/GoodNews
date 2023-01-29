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
use MODX\Revolution\modSystemSetting;
use xPDO\Transport\xPDOTransport;

/**
 * Pre-installation package requirements (validator)
 *
 * @package goodnews
 * @subpackage build
 */

// Don't use constants as this leads to an error in installer (already defined)
$minPHPVersion  = '7.2.5';
$minMODXVersion = '3.0.0';
$maxMODXVersion = '';

/** @var modX $modx */
$modx = &$object->xpdo;
$success = false;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $success = true;

        // Only check requirements if not already done
        $setting = $modx->getObject(modSystemSetting::class, ['key' => 'goodnews.system_requirements_ok']);
        if (is_object($setting) && !empty($setting->get('value'))) {
            break;
        }

        $modx->log(
            modX::LOG_LEVEL_WARN,
            'Checking if system meets minimum requirements...'
        );

        $level = modX::LOG_LEVEL_INFO;
        $modxVersion = $modx->getVersionData();
        $modxVersion = $modxVersion['full_version'];

        /* Check min/max MODX version */
        if (!empty($minMODXVersion)) {
            $level = modX::LOG_LEVEL_INFO;
            if (version_compare($modxVersion, $minMODXVersion, '<=')) {
                $level = modX::LOG_LEVEL_ERROR;
                $success = false;
            }
            $modx->log(
                $level,
                '-> min. required MODX Revo version: ' . $minMODXVersion .
                ' -- found: <b>' . $modxVersion . '</b>'
            );
        }
        if (!empty($maxMODXVersion)) {
            $level = modX::LOG_LEVEL_INFO;
            if (version_compare($modxVersion, $maxMODXVersion, '>=')) {
                $level = modX::LOG_LEVEL_ERROR;
                $success = false;
            }
            $modx->log(
                $level,
                '-> max. required MODX Revo version: ' . $maxMODXVersion .
                ' -- found: <b>' . $modxVersion . '</b>'
            );
        }

        /* Check PHP version */
        if (!empty($minPHPVersion)) {
            $level = modX::LOG_LEVEL_INFO;
            if (version_compare(PHP_VERSION, $minPHPVersion, '<=')) {
                $level = modX::LOG_LEVEL_ERROR;
                $success = false;
            }
            $modx->log(
                $level,
                '-> min. required PHP version: ' . $minPHPVersion .
                ' -- found: <b>' . PHP_VERSION . '</b>'
            );
        }

        if ($success) {
            // If OK create system setting to store requirements state
            $setting = $modx->newObject(modSystemSetting::class);
            $setting->fromArray([
                'key'       => 'goodnews.system_requirements_ok',
                'value'     => 'MODX Revolution ' . $modxVersion['full_version'] . ', PHP ' . PHP_VERSION,
                'xtype'     => 'textfield',
                'namespace' => 'goodnews',
                'area'      => '',
            ], '', true, true);
            $setting->save();
            $modx->log(
                modX::LOG_LEVEL_INFO,
                '<b style="color: green;">Minimum requirements for PHP and MODX versions reached!</b>'
            );
        } else {
            // Remove system setting with requirements state (rollback)
            $setting = $modx->getObject(modSystemSetting::class, ['key' => 'goodnews.system_requirements_ok']);
            if (is_object($setting)) {
                $setting->remove();
            }
            $modx->log(
                modX::LOG_LEVEL_ERROR,
                '<b style="color: red;">Your system does not meet the minimum requirements. Installation aborted.</b>'
            );
        }
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        $success = true;

        // Remove system setting which stores requirements state
        $setting = $modx->getObject(modSystemSetting::class, ['key' => 'goodnews.system_requirements_ok']);
        if (is_object($setting)) {
            $setting->remove();
        }
        break;
}

unset($level, $modxVersion);
return $success;
