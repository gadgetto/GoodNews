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
 * Create database tables
 *
 * @package goodnews
 * @subpackage build
 */

if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:

            // Add GoodNews package
            $modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/';
            $modx->addPackage('goodnews', $modelPath);
            $manager = $modx->getManager();

            $objects = array(
                'GoodNewsMailingMeta',
                'GoodNewsSubscriberMeta',
                'GoodNewsGroup',
                'GoodNewsGroupMember',
                'GoodNewsCategory',
                'GoodNewsCategoryMember',
                'GoodNewsProcess',
            );
            
            $count = 0;
            foreach ($objects as $obj) {
                $manager->createObjectContainer($obj);
                $count++;
            }
            break;

        case xPDOTransport::ACTION_UPGRADE:
            break;
 
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
unset($objects, $obj);
return true;
