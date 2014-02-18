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
 * Setup options
 *
 * @package goodnews 
 * @subpackage build
 */

$output = '';

/* default values */
/*
$values = array(
    'some_setting1' => '1',
    'some_setting2' => 'value',
);

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $setting = $modx->getObject('modSystemSetting', array('key' => 'goodnews.some_setting1'));
        if ($setting != null) { $values['some_setting1'] = $setting->get('value'); }
        unset($setting);
        
        $setting = $modx->getObject('modSystemSetting', array('key' => 'goodnews.some_setting2'));
        if ($setting != null) { $values['some_setting2'] = $setting->get('value'); }
        unset($setting);
        break;
        
    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

$output .= '
<style type="text/css">
    .field-desc{
        color: #A0A0A0;
        font-size: 11px;
        font-style: italic;
        line-height: 1;
        margin: 5px -15px 0;
        padding: 0 15px;
    }
    .field-desc.sep{
        border-bottom: 1px solid #E0E0E0;
        margin-bottom: 15px;
        padding-bottom: 15px;
    }
</style>';

$output .= '<label for="some_setting1">Some Setting1:</label>
<input type="text" name="some_setting1" id="some_setting1" width="300" value="'.$values['some_setting1'].'" />
<div class="field-desc sep">Some Setting1 description.</div>';

$output .= '<label for="some_setting2">Some Setting2:</label>
<input type="text" name="some_setting2" id="some_setting2" width="300" value="'.$values['some_setting2'].'" />
<div class="field-desc">Some Setting2 description.</div>';
*/
return $output;
