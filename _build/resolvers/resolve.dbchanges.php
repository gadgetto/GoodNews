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
    function existsField(&$modx, $xpdoTableClass, $field) {

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
    function checkAddField(&$modx, &$manager, $xpdoTableClass, $field, $after = '') {

        if (existsField($modx, $xpdoTableClass, $field)) { return; }

        $options = array();
        if (!empty($after)) $options['after'] = $after;
        $manager->addField($xpdoTableClass, $field, $options);
    }
}


if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            break;
            
        case xPDOTransport::ACTION_UPGRADE:

            $modx->log(modX::LOG_LEVEL_INFO, 'Database changes resolver - updating required database tables/fields...');

            // Add GoodNews package
            $modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path') . 'components/goodnews/') . 'model/';
            $modx->addPackage('goodnews', $modelPath);
            $manager = $modx->getManager();

            // Set log-level to ERROR
            $oldLogLevel = $modx->getLogLevel();
            $modx->setLogLevel(xPDO::LOG_LEVEL_ERROR);

            // 1.1.0-pl+
            checkAddField($modx, $manager, 'GoodNewsSubscriberMeta', 'hard_bounces', 'ip');
            checkAddField($modx, $manager, 'GoodNewsSubscriberMeta', 'soft_bounces', 'ip');
            checkAddField($modx, $manager, 'GoodNewsMailingMeta', 'hard_bounces', 'scheduled');
            checkAddField($modx, $manager, 'GoodNewsMailingMeta', 'soft_bounces', 'scheduled');

            // 1.2.0-pl+
            checkAddField($modx, $manager, 'GoodNewsMailingMeta', 'recipients_error', 'recipients_sent');
            $manager->createObjectContainer('GoodNewsRecipient');
            $manager->createObjectContainer('GoodNewsSubscriberLog');
            
            //GoodNewsMailingMeta - recipients_list field deprecated since 1.2.0-pl+

            // 1.3.0-pl+
            checkAddField($modx, $manager, 'GoodNewsMailingMeta', 'collections', 'categories');
            
            // 1.3.9-pl+
            checkAddField($modx, $manager, 'GoodNewsGroup', 'public', 'description');
            
            // 1.4.2-pl+
            // We need to change the field name of GoodNewsSubscriberMeta.createdon to GoodNewsSubscriberMeta.subscribedon
            // and change the field type from date to int (phptype="timestamp")
            
            // First add the new field
            checkAddField($modx, $manager, 'GoodNewsSubscriberMeta', 'subscribedon', 'sid');
            
            // Now convert all ISO Dates from old createdon field to unix timestamp and move to new subscribedon field
            if (existsField($modx, 'GoodNewsSubscriberMeta', 'createdon')) {
                
                $tblSubscriberMeta = $modx->getTableName('GoodNewsSubscriberMeta');
                $sql = "UPDATE {$tblSubscriberMeta} SET subscribedon = UNIX_TIMESTAMP(createdon)";
        
                $updResult = $modx->exec($sql); // returns count of affected rows
                if ($updResult) {
                    // If conversion was successfull, remove old createdon field
                    $manager->removeField('GoodNewsSubscriberMeta', 'createdon');
                }
            }
            
            // 1.4.9-pl+
            checkAddField($modx, $manager, 'GoodNewsSubscriberMeta', 'activatedon', 'subscribedon');
            checkAddField($modx, $manager, 'GoodNewsSubscriberMeta', 'ip_activated', 'ip');

            // Set bakck log-level to previous level
            $modx->setLogLevel($oldLogLevel);
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($manager, $oldLogLevel);
return true;
