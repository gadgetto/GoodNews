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
 * Install/resolve MODX resources
 *
 * @package goodnews
 * @subpackage build
 */

$resources = array();
$i = 0;
$epoch = time();

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Subscription Confirm',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-subscription-confirm',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.subscription-confirm.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Subscription Mail Sent',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-subscription-mail-sent',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.subscription-mail-sent.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Subscription Success',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-subscription-success',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.subscription-success.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Subscription Update',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-subscription-update',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.subscription-update.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Subscription',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-subscription',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.subscription.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Unsubscribe',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-unsubscribe',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.unsubscribe.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Request Links',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-request-links',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.request-links.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Registration Confirm',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-registration-confirm',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.registration-confirm.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Registration Mail Sent',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-registration-mail-sent',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.registration-mail-sent.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);

$resources[++$i] = array(
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Registration',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-registration',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.registration.resource.tpl',
    'richtext'              => 0,
    'template'              => 'sample.GoodNewsProfileTemplate',
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
    'class_key'             => 'modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => NULL,
);


/**
 * Creates a batch of MODX resources.
 * 
 * @param mixed &$modx A reference to the MODX object
 * @param array $resources An array of Resource properties
 * @return int $count Counter of installed MODx Resources
 */
if (!function_exists('createResources')) {
    function createResources(&$modx, $resources) {

        if (empty($resources) || !is_array($resources)) {
            return 0;
        }

        $modx->log(modX::LOG_LEVEL_INFO, 'Resource Resolver - installing sample Resource documents...');

        $corePath = $modx->getOption('core_path').'components/goodnews/';
        $resourceElementsPath = $modx->getOption('goodnews.core_path', null, $corePath).'elements/resources/';

        $count = 0;        
        foreach ($resources as $key => $fieldvalues) {

            $upd = true;
            /** @var modResource $resource */
            $resource = $modx->getObject('modResource', array('pagetitle' => $fieldvalues['pagetitle']));
            if (!is_object($resource)) {
                $upd = false;
                $resource = $modx->newObject('modResource', array('pagetitle' => $fieldvalues['pagetitle']));
            }
            
            // Replace Resource template name with Resource template content
            if (!empty($fieldvalues['content'])) {
                $filename = $resourceElementsPath.$fieldvalues['content'];
                if (file_exists($filename)) {
                    $fieldvalues['content'] = file_get_contents($filename);
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Resource Resolver - could not find content template: '.$fieldvalues['content']);
                    $modx->log(modX::LOG_LEVEL_ERROR, '-> could not install sample Resource document: '.$fieldvalues['pagetitle']);
                    continue;
                }
            }

            // Replace Template name by Template ID in $fieldvalues
            if (!empty($fieldvalues['template'])) {
                if ($fieldvalues['template'] == 'default') {
                    $fieldvalues['template'] = $modx->getOption('default_template');
                } else {
                    $templateObj = $modx->getObject('modTemplate', array('templatename' => $fieldvalues['template']));
                    if ($templateObj) {
                        $fieldvalues['template'] = $templateObj->get('id');
                    } else {
                        $modx->log(modX::LOG_LEVEL_ERROR, 'Resource Resolver - could not find template: '.$fieldvalues['template']);
                    }
                }
            }
            
            // Replace parent Resource name with Resource ID in $fieldvalues
            if (!empty($fieldvalues['parent'])) {
                $parentObj = $modx->getObject('modResource', array('pagetitle' => $fieldvalues['parent']));
                if ($parentObj) {
                    $fieldvalues['parent'] = $parentObj->get('id');
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Resource Resolver - could not find parent: '.$fieldvalues['parent']);
                }
            }
            
            $resource->fromArray($fieldvalues);
            
            if ($resource->save()) {
                $modx->log(modX::LOG_LEVEL_INFO, '-> installed sample Resource document: '.$fieldvalues['pagetitle']);
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, '-> could not install sample Resource document: '.$fieldvalues['pagetitle']);
            }
            
            ++$count;
        }
        return $count;
    }
}

/**
 * Deletes a batch of MODX resources.
 * 
 * @param mixed &$modx A reference to the MODX object
 * @param array $resources An array of Resource properties
 * @return int $count Counter of deleted MODx Resources
 */
if (!function_exists('deleteResources')) {
    function deleteResources(&$modx, $resources) {

        if (empty($resources) || !is_array($resources)) {
            return 0;
        }

        $modx->log(modX::LOG_LEVEL_INFO, 'Resource Resolver - removing sample Resource documents...');

        $count = 0;        
        foreach ($resources as $key => $fieldvalues) {

            /** @var modResource $resource */
            $resource = $modx->getObject('modResource', array('pagetitle' => $fieldvalues['pagetitle']));
            if (is_object($resource)) {
                $resource->remove();
                $modx->log(modX::LOG_LEVEL_INFO, '-> removed sample Resource document: '.$fieldvalues['pagetitle']);
                ++$count;
            } else {
                $modx->log(modX::LOG_LEVEL_WARN, '-> could not find sample Resource document: '.$fieldvalues['pagetitle'].'. Please remove manually.');
            }
        }
        return $count;
    }
}


if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
 
            // Handle checkbox value "install_resources" from setup.options.php form
            $installResources = (isset($options['install_resources'])) ? true : false;
            
            // Should sample Resource documents be installed?
            if (!$installResources) {
                $modx->log(modX::LOG_LEVEL_WARN, 'Resource Resolver - you decided to not install sample Resource documents.');
                break;
            }
            $rescount = createResources($modx, $resources);
            break;
 
        case xPDOTransport::ACTION_UNINSTALL:
            
            // Delete sample Resouce documents
            $rescount = deleteResources($modx, $resources);
            break;
    }
}
unset($resources, $resource);
return true;
