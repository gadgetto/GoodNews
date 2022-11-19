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
 * Resolve properties of custom resources (assign property values after they are installed by the transport package).
 * (currently hardcoded - @todo: rewrite for setting properties of multiple custom resources)
 *
 * @package goodnews
 * @subpackage build
 */

if ($object->xpdo) {
    $modx = &$object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:

            // Set all initial properties for the "GoodNews" container resource
            $modx->log(modX::LOG_LEVEL_INFO, 'Custom resource properties resolver - set properties for GoodNews container...');

            // Check if resource exists
            $resource = $modx->getObject('modResource', array('pagetitle' => 'GoodNews', 'class_key' => 'GoodNewsResourceContainer'));
            if (!$resource) {
                break;
            }

            $properties = array();
            
            // Set default mailing templates category
            $templatesCategory = $modx->getObject('modCategory', array('category' => 'Newsletter Templates'));
            if (is_object($templatesCategory)) {
                $properties['templatesCategory'] = $templatesCategory->get('id');
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, '-> could not set templatesCategory property for GoodNews container.');
            }
            
            // Set default mailing template
            $mailingTemplate = $modx->getObject('modTemplate', array('templatename' => 'sample.GoodNewsNewsletterTemplate1'));
            if (is_object($mailingTemplate)) {
                $properties['mailingTemplate'] = $mailingTemplate->get('id');
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, '-> could not set mailingTemplate property for GoodNews container.');
            }

            // Set default resource for 1-click unsubscription
            $unsubscribeResource = $modx->getObject('modResource', array('pagetitle' => 'GoodNews Unsubscribe'));
            if (is_object($unsubscribeResource)) {
                $properties['unsubscribeResource'] = $unsubscribeResource->get('id');
            } else {
                $modx->log(modX::LOG_LEVEL_WARN, '-> unsubscribeResource property for GoodNews container not set as Resource does not exist. Please set manually.');
            }
            
            // Set default resource for updating subscription profile
            $profileResource = $modx->getObject('modResource', array('pagetitle' => 'GoodNews Subscription Update'));
            if (is_object($profileResource)) {
                $properties['profileResource'] = $profileResource->get('id');
            } else {
                $modx->log(modX::LOG_LEVEL_WARN, '-> profileResource property for GoodNews container not set as Resource does not exist. Please set manually.');
            }
            
            $properties['editorGroups'] = 'Administrator';
            // default sender email address (this is also the mailbox for bounce messages)
            $properties['mailFrom']                        = $modx->getOption('emailsender', null, 'postmaster@mydomain.com');
            $properties['mailFromName']                    = $modx->getOption('site_name',   null, 'Sender Name');
            $properties['mailReplyTo']                     = $modx->getOption('emailsender', null, 'replyto@mydomain.com');
            $properties['mailCharset']                     = 'UTF-8';
            $properties['mailEncoding']                    = '8bit';
            $properties['mailBounceHandling']              = '0';
            $properties['mailUseSmtp']                     = '0';
            $properties['mailSmtpAuth']                    = '0';
            $properties['mailSmtpUser']                    = '';
            $properties['mailSmtpPass']                    = '';
            $properties['mailSmtpHosts']                   = 'localhost:25';
            $properties['mailSmtpPrefix']                  = '';
            $properties['mailSmtpKeepalive']               = '0';
            $properties['mailSmtpTimeout']                 = 10;
            $properties['mailSmtpSingleTo']                = '0';
            $properties['mailSmtpHelo']                    = '';
            $properties['mailService']                     = 'imap';
            $properties['mailMailHost']                    = '';
            $properties['mailMailboxUsername']             = '';
            $properties['mailMailboxPassword']             = '';
            $properties['mailBoxname']                     = 'INBOX';
            $properties['mailPort']                        = '143';
            $properties['mailServiceOption']               = 'notls';
            $properties['mailSoftBouncedMessageAction']    = 'delete';
            $properties['mailSoftMailbox']                 = 'INBOX.Softbounces';
            $properties['mailMaxSoftBounces']              = 3;
            $properties['mailMaxSoftBouncesAction']        = 'disable';
            $properties['mailHardBouncedMessageAction']    = 'delete';
            $properties['mailHardMailbox']                 = 'INBOX.Hardbounces';
            $properties['mailMaxHardBounces']              = 1;
            $properties['mailMaxHardBouncesAction']        = 'disable';
            $properties['mailNotClassifiedMessageAction']  = 'move';
            $properties['mailNotClassifiedMailbox']        = 'INBOX.NotClassified';
            $properties['collection1Name']                 = '';
            $properties['collection1Parents']              = '';
            $properties['collection2Name']                 = '';
            $properties['collection2Parents']              = '';
            $properties['collection3Name']                 = '';
            $properties['collection3Parents']              = '';

            $resource->setProperties($properties, 'goodnews');
            if ($resource->save()) {
                $modx->log(modX::LOG_LEVEL_INFO, '-> properties for GoodNews container set.');
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, '-> could not set properties for GoodNews container.');
            }

            break;
 
        case xPDOTransport::ACTION_UPGRADE:
        
            // @todo: add/change missing/new properties fields
            
            break;
            
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($resource, $properties, $templatesCategory, $mailingTemplate, $unsubscribeResource, $profileResource);
return true;
