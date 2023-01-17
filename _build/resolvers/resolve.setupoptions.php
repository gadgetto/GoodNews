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
 * Resolve setup options (only for system settings)
 *
 * @package goodnews
 * @subpackage build
 */

$settings = [
    //'setting1',
    //'setting2',
    //'setting3',
];


if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            // Process system setting based install options
            // System settings must already exist (will be created by installer before)!
            if (!empty($settings) && is_array($settings)) {
                foreach ($settings as $key) {
                    if (isset($options[$key])) {
                        $setting = $modx->getObject(modSystemSetting::class, ['key' => 'goodnews.' . $key]);
                        if ($setting != null) {
                            $setting->set('value', $options[$key]);
                            $setting->save();
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, $key . ' setting could not be found.');
                        }
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($settings, $key);
return true;
