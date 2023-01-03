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
 * Resolve Template Variables (sample currently not in use)
 *
 * @package goodnews
 * @subpackage build
 */

$templates = [
    'templateName',
];


if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
        
            $tv = $modx->getObject('modTemplateVar', [
                'name' => 'tvName',
            ]);
            
            if ($tv) {
            
                foreach ($templates as $templateName) {
                
                    $template = $modx->getObject('modTemplate', ['templatename' => $templateName]);
                    if ($template) {
                        $templateVarTemplate = $modx->getObject('modTemplateVarTemplate', [
                            'templateid' => $template->get('id'),
                            'tmplvarid' => $tv->get('id'),
                        ]);
                        if (!$templateVarTemplate) {
                            $templateVarTemplate = $modx->newObject('modTemplateVarTemplate');
                            $templateVarTemplate->set('templateid', $template->get('id'));
                            $templateVarTemplate->set('tmplvarid', $tv->get('id'));
                            $templateVarTemplate->save();
                        }
                    }

                }
            }
            break;
 
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($tv, $templates, $templateName, $template, $templateVarTemplate);
return true;
