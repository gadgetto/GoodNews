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
 * Resolve setup options
 *
 * @package goodnews 
 * @subpackage build
 */
 
if ($object->xpdo) {
    $modx =& $object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
        
            $settings = array(
                //'setting1',
                //'setting2',
                //'setting3',
            );
            
            foreach ($settings as $key) {
                if (isset($options[$key])) {
                    $setting = $modx->getObject('modSystemSetting', array('key' => 'goodnews.'.$key));
                    if ($setting != null) {
                        $setting->set('value', $options[$key]);
                        $setting->save();
                    } else {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, $key.' setting could not be found');
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

