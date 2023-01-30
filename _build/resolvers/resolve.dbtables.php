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

/**
 * Resolve/create database tables
 *
 * @package goodnews
 * @subpackage build
 */

$tables = [
    Bitego\GoodNews\Model\GoodNewsMailingMeta::class,
    Bitego\GoodNews\Model\GoodNewsRecipient::class,
    Bitego\GoodNews\Model\GoodNewsSubscriberMeta::class,
    Bitego\GoodNews\Model\GoodNewsSubscriberLog::class,
    Bitego\GoodNews\Model\GoodNewsGroup::class,
    Bitego\GoodNews\Model\GoodNewsGroupMember::class,
    Bitego\GoodNews\Model\GoodNewsCategory::class,
    Bitego\GoodNews\Model\GoodNewsCategoryMember::class,
    Bitego\GoodNews\Model\GoodNewsProcess::class,
];

if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx->log(modX::LOG_LEVEL_INFO, 'Database tables resolver - creating database tables...');
            $modx->log(modX::LOG_LEVEL_WARN, 'Existing tables will be skipped!');
            $manager = $modx->getManager();
            $count = 0;
            foreach ($tables as $table) {
                $tableName = $modx->getTableName($table);
                // Do not report table creation detailes
                $prevLogLevel = $modx->setLogLevel(modX::LOG_LEVEL_ERROR);
                $created = $manager->createObjectContainer($table);
                $modx->setLogLevel($prevLogLevel);
                if ($created) {
                    ++$count;
                    $modx->log(modX::LOG_LEVEL_INFO, '-> added database table: ' . $tableName);
                } else {
                    $modx->log(modX::LOG_LEVEL_INFO, '-> database table ' . $tableName . ' - skipped!');
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $modx->log(
                modX::LOG_LEVEL_WARN,
                'Database tables resolver - database tables will not be uninstalled to prevent data loss. ' .
                'Please remove manually.'
            );
            break;
    }
}
unset($modelPath, $tables, $table, $tableName, $prevLogLevel, $created, $count);
return true;
