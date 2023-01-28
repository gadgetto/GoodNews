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
use xPDO\Transport\xPDOTransport;

/**
 * Pre-installation scripts (validator).
 *
 * @package goodnews
 * @subpackage build
 */

/** @var modX $modx */
$modx = &$object->xpdo;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        // Deprecated extension_packages system setting and modExtensionPackage is no longer necessary
        // as we use the new MODX3 services injection container!

        // Do other pre-install things here ...
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        // Set back all custom resources to modDocument resources
        $resources = $modx->getIterator(modResource::class, [
            'class_key' => 'Bitego\\GoodNews\\Model\\GoodNewsResourceContainer'
        ]);
        foreach ($resources as $resource) {
            $resource->set('class_key', 'MODX\\Revolution\\modDocument');
            $resource->set('hide_children_in_tree', false);
            $resource->save();
        }
        unset($resources, $resource);

        $resources = $modx->getIterator('modResource', [
            'class_key' => 'Bitego\\GoodNews\\Model\\GoodNewsResourceMailing'
        ]);
        foreach ($resources as $resource) {
            $resource->set('class_key', 'MODX\\Revolution\\modDocument');
            $resource->set('show_in_tree', true);
            $resource->save();
        }
        unset($resources, $resource);
        break;
}

return true;
