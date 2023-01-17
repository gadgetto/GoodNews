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
use xPDO\Transport\xPDOTransport;
use Bitego\GoodNews\Model\GoodNewsGroup;

/**
 * Resolve/create default database entries in custom tables
 *
 * @package goodnews
 * @subpackage build
 */

$epoch = time();
$i = 0;

$grpAttributes[++$i] = [
    'id'            => $i,
    'name'          => 'Newsletters',
    'description'   => 'Default newsletters group',
    'modxusergroup' => 0,
    'createdon'     => $epoch,
    'createdby'     => 0,
    'editedon'      => $epoch,
    'editedby'      => 0,
];

if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $modx->log(
                modX::LOG_LEVEL_INFO,
                'Tables content resolver - creating database entries in GoodNewsGroup table...'
            );

            $manager = $modx->getManager();

            if (is_array($grpAttributes)) {
                foreach ($grpAttributes as $attributes) {
                    // Check if group already exists
                    $group = $modx->getObject(GoodNewsGroup::class, [
                        'name' => $attributes['name']
                    ]);
                    if (!$group) {
                        // Create a GoodNews group
                        $group = $modx->newObject(GoodNewsGroup::class, $attributes);
                        if ($group->save()) {
                            $modx->log(modX::LOG_LEVEL_INFO, '-> created group: ' . $attributes['name']);
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, '-> could not create group: ' . $attributes['name']);
                        }
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UPGRADE:
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($grpAttributes, $attributes, $group, $epoch, $i);
return true;
