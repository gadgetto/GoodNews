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
 * Resolve changes to db model on install and upgrade.
 *
 * @package goodnews
 * @subpackage build
 */

if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:

            // Add GoodNews package
            $modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/';
            $modx->addPackage('goodnews', $modelPath);
            $manager = $modx->getManager();

            // Set log-level to ERROR
            $oldLogLevel = $modx->getLogLevel();
            $modx->setLogLevel(xPDO::LOG_LEVEL_ERROR);

            // 1.1.0-pl+
            $manager->addField('GoodNewsSubscriberMeta', 'hard_bounces', array('after' => 'ip'));
            $manager->addField('GoodNewsSubscriberMeta', 'soft_bounces', array('after' => 'ip'));
            $manager->addField('GoodNewsMailingMeta', 'hard_bounces', array('after' => 'scheduled'));
            $manager->addField('GoodNewsMailingMeta', 'soft_bounces', array('after' => 'scheduled'));

            // 1.2.0-pl+
            $manager->addField('GoodNewsMailingMeta', 'recipients_error', array('after' => 'recipients_sent'));
            $manager->createObjectContainer('GoodNewsRecipient');
            $manager->createObjectContainer('GoodNewsSubscriberLog');
            
            //GoodNewsMailingMeta - recipients_list field deprecated since 1.2.0-pl+

            // 1.3.0-pl+
            $manager->addField('GoodNewsMailingMeta', 'collections', array('after' => 'categories'));
            
            // 1.3.9-pl+
            $manager->addField('GoodNewsGroup', 'public', array('after' => 'description'));
            
            // Set bakck log-level to previous level
            $modx->setLogLevel($oldLogLevel);
            break;
    }
}
unset($manager, $oldLogLevel);
return true;
