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
 * Install/resolve custom MODX resources
 *
 * @package goodnews
 * @subpackage build
 */

$customresources = [];
$i = 0;
$epoch = time();

// GoodNews Mailing Container
$customresources[++$i] = [
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
    'content'               => 'custom.goodnews.container.tpl',
    'richtext'              => 1,
    'template'              => 'sample.GoodNewsContainerTemplate',
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
    'class_key'             => 'Bitego\\GoodNews\\Model\\GoodNewsResourceContainer',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 1,
    'show_in_tree'          => 1,
    'properties'            => null,
];


/**
 * Creates a batch of custom MODX resources.
 *
 * @param mixed &$modx A reference to the MODX object
 * @param array $resources An array of Resource properties
 * @return int $count Counter of installed MODx Resources
 */
if (!function_exists('createCustomResources')) {
    function createCustomResources(&$modx, $resources)
    {
        if (empty($resources) || !is_array($resources)) {
            return 0;
        }

        $modx->log(
            modX::LOG_LEVEL_INFO,
            'Custom resource resolver - installing custom resource documents...'
        );

        $corePath = $modx->getOption('core_path') . 'components/goodnews/';
        $resourceElementsPath = $modx->getOption('goodnews.core_path', null, $corePath) . 'elements/resources/';

        $count = 0;
        foreach ($resources as $key => $fieldvalues) {
            $upd = true;
            /** @var modResource $resource */
            $resource = $modx->getObject(modResource::class, [
                'pagetitle' => $fieldvalues['pagetitle']
            ]);
            if (!is_object($resource)) {
                $upd = false;
                $resource = $modx->newObject(modResource::class, [
                    'pagetitle' => $fieldvalues['pagetitle']
                ]);
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
                        '-> could not install custom resource: ' . $fieldvalues['pagetitle']
                    );
                    continue;
                }
            }

            // Replace Template name by Template ID in $fieldvalues
            if (!empty($fieldvalues['template'])) {
                if ($fieldvalues['template'] == 'default') {
                    $fieldvalues['template'] = $modx->getOption('default_template');
                } else {
                    $templateObj = $modx->getObject(modTemplate::class, [
                        'templatename' => $fieldvalues['template']
                    ]);
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
                $parentObj = $modx->getObject(modResource::class, [
                    'pagetitle' => $fieldvalues['parent']
                ]);
                if ($parentObj) {
                    $fieldvalues['parent'] = $parentObj->get('id');
                } else {
                    $modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '-> could not find parent: ' . $fieldvalues['parent']
                    );
                }
            }

            $resource->fromArray($fieldvalues);
            if ($resource->save()) {
                $modx->log(
                    modX::LOG_LEVEL_INFO,
                    '-> installed custom resource: ' . $fieldvalues['pagetitle']
                );
            } else {
                $modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '-> could not install custom resource: ' . $fieldvalues['pagetitle']
                );
            }
            ++$count;
        }
        return $count;
    }
}

if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $rescount = createCustomResources($modx, $customresources);
            break;

        case xPDOTransport::ACTION_UPGRADE:
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $modx->log(
                modX::LOG_LEVEL_WARN,
                'Custom resource resolver - custom resource documents will not be uninstalled to prevent data loss. ' .
                'Please remove manually.'
            );
            break;
    }
}

unset($customresources, $resource, $templateObj, $parentObj);
return true;
