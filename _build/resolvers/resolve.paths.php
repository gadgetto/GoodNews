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
 * Resolve paths. These are useful to change if you want to debug and/or develop
 * in a directory outside of the MODx webroot. They are not required to set
 * for basic usage.
 *
 * @package goodnews
 * @subpackage build
 */

/**
 * createSetting function.
 * 
 * @param mixed &$modx
 * @param mixed $key
 * @param mixed $value
 * @return void
 */
if (!function_exists('createSetting')) {
    function createSetting(&$modx, $key, $value) {
        $ct = $modx->getCount('modSystemSetting',array(
            'key' => 'goodnews.'.$key,
        ));
        if (empty($ct)) {
            $setting = $modx->newObject('modSystemSetting');
            $setting->set('key', 'goodnews.'.$key);
            $setting->set('value', $value);
            $setting->set('namespace', 'goodnews');
            $setting->set('area', 'Paths');
            $setting->save();
        }
    }
}


if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:

            /* setup paths */
            createSetting($modx, 'core_path', $modx->getOption('core_path').'components/goodnews/');
            createSetting($modx, 'assets_path', $modx->getOption('assets_path').'components/goodnews/');

            /* setup urls */
            createSetting($modx, 'assets_url', $modx->getOption('assets_url').'components/goodnews/');
            break;
            
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }

}
return true;
