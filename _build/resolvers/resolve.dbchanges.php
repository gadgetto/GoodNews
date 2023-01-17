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
use Bitego\GoodNews\Model\GoodNewsMailingMeta;
use Bitego\GoodNews\Model\GoodNewsRecipient;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Model\GoodNewsSubscriberLog;
use Bitego\GoodNews\Model\GoodNewsGroup;
use Bitego\GoodNews\Model\GoodNewsGroupMember;
use Bitego\GoodNews\Model\GoodNewsCategory;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Model\GoodNewsProcess;

/**
 * Resolve changes to db model on install and upgrade.
 *
 * @package goodnews
 * @subpackage build
 */

/**
 * Checks if a field in a specified database table exist
 *
 * @param mixed &$modx A reference to the MODX object
 * @param string $xpdoTableClass xPDO schema class name for the database table
 * @param string $field Name of the field to check
 * @return boolean
 */
if (!function_exists('existsField')) {
    function existsField(&$modx, $xpdoTableClass, $field)
    {
        $existsField = true;

        $table = $modx->getTableName($xpdoTableClass);
        $sql = "SHOW COLUMNS FROM {$table} LIKE '" . $field . "'";
        $stmt = $modx->prepare($sql);
        $stmt->execute();
        $count = $stmt->rowCount();
        $stmt->closeCursor();

        if ($count < 1) {
            $existsField = false;
        }
        return $existsField;
    }
}

/**
 * Checks if a field in a specified database table exist and creates it if not.
 * (this prevents the annoying erro messages in MODX install log)
 *
 * @param mixed &$modx A reference to the MODX object
 * @param mixed &$manager A reference to the Manager object
 * @param string $xpdoTableClass xPDO schema class name for the database table
 * @param string $field Name of the field to create
 * @param string $after Name of the field after which the new field should be placed (Optional)
 * @return void
 */
if (!function_exists('checkAddField')) {
    function checkAddField(&$modx, &$manager, $xpdoTableClass, $field, $after = '')
    {
        if (existsField($modx, $xpdoTableClass, $field)) {
            return;
        }
        $options = [];
        if (!empty($after)) {
            $options['after'] = $after;
        }
        $manager->addField($xpdoTableClass, $field, $options);
    }
}

if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            break;

        case xPDOTransport::ACTION_UPGRADE:
            $modx->log(
                modX::LOG_LEVEL_INFO,
                'Database changes resolver - updating required database tables/fields...'
            );

            $manager = $modx->getManager();

            // Set log-level to ERROR
            $oldLogLevel = $modx->getLogLevel();
            $modx->setLogLevel(modX::LOG_LEVEL_ERROR);

            /*
            Samples

            // 2.0.1+
            $manager->createObjectContainer(NewClass::class);

            // Change the field name of MyClass.oldfield to MyClass.newfield
            // and change the field type from date to int (phptype="timestamp")

            // First add the new field
            checkAddField($modx, $manager, MyClass::class, 'newfield', 'sid');

            // Now convert all ISO Dates from oldfield field to unix timestamp and move to newfield field
            if (existsField($modx, MyClass::class, 'oldfield')) {
                $tblName = $modx->getTableName(MyClass::class);
                $sql = "UPDATE {$tblName} SET newfield = UNIX_TIMESTAMP(oldfield)";

                // returns count of affected rows
                $updResult = $modx->exec($sql);
                if ($updResult) {
                    // If conversion was successfull, remove oldfield field
                    $manager->removeField(MyClass::class, 'oldfield');
                }
            }
            */

            // Set back log-level to previous level
            $modx->setLogLevel($oldLogLevel);
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($manager, $oldLogLevel);
return true;
