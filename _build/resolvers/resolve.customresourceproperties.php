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
 * Resolve properties of custom resources (assign property values after they are installed by the transport package).
 * (currently hardcoded - todo: rewrite for setting properties of multiple custom resources)
 *
 * @package goodnews
 * @subpackage build
 */

if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:

            // Set all properties for the "GoodNews" container resource
            
            // Check if resource exists
            $resource = $modx->getObject('modResource', array('pagetitle' => 'GoodNews', 'class_key' => 'GoodNewsResourceContainer'));
            if (!$resource) {
                break;
            }

            $properties = array();
            
            // Set default mailing templates category
            $templatesCategory = $modx->getObject('modCategory', array('category' => 'Newsletter Templates'));
            if ($templatesCategory) {
                $properties['templatesCategory'] = $templatesCategory->get('id');
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Custom Resource Properties Resolver - could not set templatesCategory property for GoodNews container.');
            }
            
            // Set default mailing template
            $mailingTemplate = $modx->getObject('modTemplate', array('templatename' => 'sample.GoodNewsNewsletterTemplate1'));
            if ($mailingTemplate) {
                $properties['mailingTemplate'] = $mailingTemplate->get('id');
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Custom Resource Properties Resolver - could not set mailingTemplate property for GoodNews container.');
            }

            // Set default resource for 1-click unsubscription
            $unsubscribeResource = $modx->getObject('modResource', array('pagetitle' => 'Unsubscribe'));
            if ($unsubscribeResource) {
                $properties['unsubscribeResource'] = $unsubscribeResource->get('id');
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Custom Resource Properties Resolver - could not set unsubscribeResource property for GoodNews container.');
            }
            
            // Set default resource for updating subscription profile
            $profileResource = $modx->getObject('modResource', array('pagetitle' => 'Subscription Update'));
            if ($profileResource) {
                $properties['profileResource'] = $profileResource->get('id');
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Custom Resource Properties Resolver - could not set profileResource property for GoodNews container.');
            }
            
            // Set default sender email address
            $properties['mailFrom'] = $modx->getOption('emailsender', null, 'noreply@mydomain.com');
            
            // Set default sender name
            $properties['mailFromName'] = $modx->getOption('site_name', null, 'Sender Name');

            // Set default editor groups
            $properties['editorGroups'] = 'Administrator';

            $resource->setProperties($properties, 'goodnews');
            if (!$resource->save()) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Custom Resource Properties Resolver - could not set properties for GoodNews container.');
            }

            break;
 
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
unset($resource, $properties, $templatesCategory, $mailingTemplate, $unsubscribeResource, $profileResource);
return true;
