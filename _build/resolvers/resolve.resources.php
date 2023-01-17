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
use MODX\Revolution\modResource;
use MODX\Revolution\modTemplate;
use xPDO\Transport\xPDOTransport;

/**
 * Resolve/install MODX resources
 *
 * @package goodnews
 * @subpackage build
 */

$resources = [];
$i = 0;
$epoch = time();

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Profile Update',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-profile-update',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.profile-update.resource.tpl',
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Terms and Conditions',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-terms-and-conditions',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.terms-and-conditions.resource.tpl',
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];

$resources[++$i] = [
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews Privacy Policy',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews-privacy-policy',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 0,
    'introtext'             => '',
    'content'               => 'sample.privacy-policy.resource.tpl',
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
    'class_key'             => 'MODX\\Revolution\\modDocument',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 0,
    'show_in_tree'          => 1,
    'properties'            => null,
];


/**
 * Creates a batch of MODX resources.
 *
 * @param mixed &$modx A reference to the MODX object
 * @param array $resources An array of Resource properties
 * @return int $count Counter of installed MODx Resources
 */
if (!function_exists('createResources')) {
    function createResources(&$modx, $resources)
    {
        if (empty($resources) || !is_array($resources)) {
            return 0;
        }

        $modx->log(
            modX::LOG_LEVEL_INFO,
            'Resource resolver - installing sample resource documents...'
        );

        $corePath = $modx->getOption('core_path') . 'components/goodnews/';
        $resourceElementsPath = $modx->getOption('goodnews.core_path', null, $corePath) . 'elements/resources/';

        $count = 0;
        foreach ($resources as $key => $fieldvalues) {
            $upd = true;
            /** @var modResource $resource */
            $resource = $modx->getObject(modResource::class, ['pagetitle' => $fieldvalues['pagetitle']]);
            if (!is_object($resource)) {
                $upd = false;
                $resource = $modx->newObject(modResource::class, ['pagetitle' => $fieldvalues['pagetitle']]);
            }

            // Replace Resource template name with Resource template content
            if (!empty($fieldvalues['content'])) {
                $filename = $resourceElementsPath . $fieldvalues['content'];
                if (file_exists($filename)) {
                    $fieldvalues['content'] = file_get_contents($filename);
                } else {
                    $modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '-> could not find content template: ' . $fieldvalues['content']
                    );
                    $modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '-> could not install sample resource document: ' . $fieldvalues['pagetitle']
                    );
                    continue;
                }
            }

            // Replace Template name by Template ID in $fieldvalues
            if (!empty($fieldvalues['template'])) {
                if ($fieldvalues['template'] == 'default') {
                    $fieldvalues['template'] = $modx->getOption('default_template');
                } else {
                    $templateObj = $modx->getObject(modTemplate::class, ['templatename' => $fieldvalues['template']]);
                    if ($templateObj) {
                        $fieldvalues['template'] = $templateObj->get('id');
                    } else {
                        $modx->log(
                            modX::LOG_LEVEL_ERROR,
                            '-> could not find template: ' . $fieldvalues['template']
                        );
                    }
                }
            }

            // Replace parent Resource name with Resource ID in $fieldvalues
            if (!empty($fieldvalues['parent'])) {
                $parentObj = $modx->getObject(modResource::class, ['pagetitle' => $fieldvalues['parent']]);
                if ($parentObj) {
                    $fieldvalues['parent'] = $parentObj->get('id');
                } else {
                    $modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '-> could not find parent resource: ' . $fieldvalues['parent']
                    );
                }
            }

            $resource->fromArray($fieldvalues);
            if ($resource->save()) {
                $modx->log(
                    modX::LOG_LEVEL_INFO,
                    '-> installed sample resource document: ' . $fieldvalues['pagetitle']
                );
            } else {
                $modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '-> could not install sample resource document: ' . $fieldvalues['pagetitle']
                );
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
    function deleteResources(&$modx, $resources)
    {
        if (empty($resources) || !is_array($resources)) {
            return 0;
        }

        $modx->log(
            modX::LOG_LEVEL_INFO,
            'Resource resolver - removing sample resource documents...'
        );

        $count = 0;
        foreach ($resources as $key => $fieldvalues) {
            /** @var modResource $resource */
            $resource = $modx->getObject(modResource::class, ['pagetitle' => $fieldvalues['pagetitle']]);
            if (is_object($resource)) {
                $resource->remove();
                $modx->log(
                    modX::LOG_LEVEL_INFO,
                    '-> removed sample resource document: ' . $fieldvalues['pagetitle']
                );
                ++$count;
            } else {
                $modx->log(
                    modX::LOG_LEVEL_WARN,
                    '-> could not find sample resource document: ' . $fieldvalues['pagetitle'] .
                    '. Please remove manually.'
                );
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
                $modx->log(
                    modX::LOG_LEVEL_INFO,
                    'Resource resolver - you decided to not install sample Resource documents.'
                );
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
