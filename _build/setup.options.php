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
use xPDO\Transport\xPDOTransport;

/**
 * Setup options
 *
 * @package goodnews
 * @subpackage build
 */

$output = '';

// Default field values
$fieldvalues = [
    'install_resources' => true,
    //'setting1' => 'value',
    //'setting2' => 'value',
    //'setting3' => 'value',
];

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        /*
        // Read system setting
        $setting = $modx->getObject('modSystemSetting', ['key' => 'goodnews.setting1']);
        if ($setting != null) { $fieldvalues['setting1'] = $setting->get('value'); }
        unset($setting);

        $setting = $modx->getObject('modSystemSetting', ['key' => 'goodnews.setting2']);
        if ($setting != null) { $fieldvalues['setting2'] = $setting->get('value'); }
        unset($setting);
        */

        /*
        $output .= '<label for="setting1">Some Setting1:</label>
        <input type="text" name="some_setting1" id="setting1" value="'.$fieldvalues['setting1'].'" />
        <div class="field-desc">Some Setting1 description.</div>';

        $output .= '<label for="setting2">Some Setting2:</label>
        <input type="text" name="some_setting2" id="setting2" value="'.$fieldvalues['setting2'].'" />
        <div class="field-desc">Some Setting2 description.</div>';
        */

        $installResourcesChecked = ($fieldvalues['install_resources'] == true)
            ? ' checked="checked"'
            : '';

        $output .= '
        <style type="text/css">
            .field-desc {
                color: #A0A0A0;
                font-size: 12px;
                font-style: italic;
                line-height: 1;
                margin: 5px -15px 0;
                padding: 0 15px;
            }
            .field-desc.sep {
                border-bottom: 1px solid #E0E0E0;
                margin-bottom: 15px;
                padding-bottom: 15px;
            }
        </style>
        ';

        $output .= '
        <label for="install_resources">
            <input type="checkbox" name="install_resources" id="install_resources" value="1"' .
            $installResourcesChecked . '>Install Sample Resources
        </label>
        <div class="field-desc">
            If set, sample resources for Subscription, Unsubscription, Profile Update, ... will be installed.<br />
            Be carefull as this will overwrite/update existing resources with the same name!
        </div>
        ';
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

unset($installResourcesChecked, $fieldvalues);
return $output;
