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
 * Resolve/create custom resources (GoodNewsResourceContainer, GoodNewsResourceMailing).
 *
 * @package goodnews
 * @subpackage build
 */

$epoch = time();

/* GoodNews container */
$i = 0;
$resourcesAttributes[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 1,
    'introtext'             => '',
    'content'               => '[[GoodNewsGetNewsletters]]',
    'richtext'              => 1,
    'template'              => 0,
    'searchable'            => 0,
    'cacheable'             => 1,
    'createdby'             => 0,
    'createdon'             => $epoch,
    'editedby'              => 0,
    'editedon'              => $epoch,
    'deleted'               => 0,
    'deletedon'             => 0,
    'deletedby'             => 0,
    'publishedon'           => $epoch,
    'publishedby'           => 0,
    'menutitle'             => '',
    'donthit'               => 0,
    'privateweb'            => 0,
    'privatemgr'            => 0,
    'content_dispo'         => 0,
    'hidemenu'              => 1,
    'class_key'             => 'GoodNewsResourceContainer',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 1,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);


if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:

            // Add GoodNews package
            $modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/';
            $modx->addPackage('goodnews', $modelPath);
            $manager = $modx->getManager();

            // Create custom resources
            if (!empty($resourcesAttributes)) {
                foreach ($resourcesAttributes as $attributes) {

                    // Check if resource already exists
                    $resource = $modx->getObject('modResource', array('pagetitle' => $attributes['pagetitle']));
                    if ($resource) {
                        $modx->log(modX::LOG_LEVEL_INFO, 'Custom Resource Resolver - resource '.$attributes['pagetitle'].' already exists.');
                        continue;
                    }
                    
                    $resource = $modx->newObject('modResource');
                    $resource->fromArray($attributes, '', true, true);
                    if (!$resource->save()) {
                        $modx->log(modX::LOG_LEVEL_ERROR, 'Custom Resource Resolver - could not save resource: '.$attributes['pagetitle']);
                    }
                }
            }
            break;
            
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }

}
unset($resourcesAttributes, $attributes, $resource);
return true;
