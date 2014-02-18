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
 * Resolve resources (assign resource values after they are installed by the transport package).
 *
 *  Search for resources by its pagetitle and:
 *
 *  - assign templates by template name
 *  - assign parent resources by its page title
 *  - assign TV values to specific resources
 *
 * @package goodnews
 * @subpackage build
 */

if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
 
            $i = 0;
            $resourcesAttributes[++$i] = array(
                'pagetitle' => 'Subscription Confirm',
                'parent'    => '',
                'template'  => 'sample.GoodNewsProfileTemplate',
            );
            $resourcesAttributes[++$i] = array(
                'pagetitle' => 'Subscription Mail Sent',
                'parent'    => '',
                'template'  => 'sample.GoodNewsProfileTemplate',
            );
            $resourcesAttributes[++$i] = array(
                'pagetitle' => 'Subscription Update',
                'parent'    => '',
                'template'  => 'sample.GoodNewsProfileTemplate',
            );
            $resourcesAttributes[++$i] = array(
                'pagetitle' => 'Subscription',
                'parent'    => '',
                'template'  => 'sample.GoodNewsProfileTemplate',
            );
            $resourcesAttributes[++$i] = array(
                'pagetitle' => 'Unsubscribe',
                'parent'    => '',
                'template'  => 'sample.GoodNewsProfileTemplate',
            );
            $resourcesAttributes[++$i] = array(
                'pagetitle' => 'GoodNews',
                'parent'    => '',
                'template'  => 'sample.GoodNewsContainerTemplate',
            );
            /*
            $resourcesAttributes[++$i] = array(
                'pagetitle' => 'Resource1',
                'parent'    => 'default',  // uses the default template from system settings
                'template'  => 'Template1',
            );
            $resourcesAttributes[++$i] = array(
                'pagetitle' => 'Resource2',
                'parent'    => 'Resource1',
                'template'  => 'Template2',
                'tvValues'  =>  array(
                    'Tv1' => 'SomeValue',
                    'Tv2' => 'SomeOtherValue',
                ),
            );
            */
 
            if (is_array($resourcesAttributes)) {
                foreach ($resourcesAttributes as $attributes) {
                    
                    // Check if resource exists
                    $resource = $modx->getObject('modResource', array('pagetitle' => $attributes['pagetitle']));
                    if (!$resource) {
                        continue;
                    }
                    
                    // Assign template
                    if (!empty($attributes['template'])) {
                        if ($attributes['template'] == 'default') {
                            $resource->set('template', $modx->getOption('default_template'));
                        } else {
                            $templateObj = $modx->getObject('modTemplate', array('templatename' => $attributes['template']));
                            if ($templateObj) {
                                $resource->set('template', $templateObj->get('id'));
                            } else {
                                $modx->log(modX::LOG_LEVEL_ERROR, 'Resource Resolver - could not find template: '.$attributes['template']);
                            }
                        }
                    }
                    
                    // Assign parent resource
                    if (!empty($attributes['parent'])) {
                        $parentObj = $modx->getObject('modResource', array('pagetitle' => $attributes['parent']));
                        if ($parentObj) {
                            $resource->set('parent', $parentObj->get('id'));
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, 'Resource Resolver - could not find parent: '.$attributes['parent']);
                        }
                    }

                    // Assign TV values
                    if (isset($attributes['tvValues'])) {
                        foreach($attributes['tvValues'] as $tvName => $value) {
                            $resource->setTVValue($tvName, $value);
                        }
                    }
                    
                    if (!$resource->save()) {
                        $modx->log(modX::LOG_LEVEL_ERROR, 'Resource Resolver - could not save resource: '.$attributes['pagetitle']);
                    }
                }
            }
            break;
 
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
unset($resourcesAttributes, $attributes, $resource, $templateObj, $tvName, $value);
return true;
