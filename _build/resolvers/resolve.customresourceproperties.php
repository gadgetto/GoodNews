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
use MODX\Revolution\modCategory;
use MODX\Revolution\modTemplate;
use xPDO\Transport\xPDOTransport;

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
            $modx->log(
                modX::LOG_LEVEL_INFO,
                'Custom resource properties resolver - set properties for GoodNews container...'
            );

            // Check if resource exists
            $resource = $modx->getObject(modResource::class, [
                'pagetitle' => 'GoodNews',
                'class_key' => 'Bitego\\GoodNews\\Model\\GoodNewsResourceContainer'
            ]);
            if (!$resource) {
                break;
            }

            $properties = [];

            // Set default mailing templates category
            $templatesCategory = $modx->getObject(modCategory::class, [
                'category' => 'Newsletter Templates'
            ]);
            if (is_object($templatesCategory)) {
                $properties['templatesCategory'] = $templatesCategory->get('id');
            } else {
                $modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '-> could not set templatesCategory property for GoodNews container.'
                );
            }

            // Set default mailing template
            $mailingTemplate = $modx->getObject(modTemplate::class, [
                'templatename' => 'sample.GoodNewsNewsletterTemplate1'
            ]);
            if (is_object($mailingTemplate)) {
                $properties['mailingTemplate'] = $mailingTemplate->get('id');
            } else {
                $modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '-> could not set mailingTemplate property for GoodNews container.'
                );
            }

            // Set default resource for 1-click unsubscription
            $unsubscribeResource = $modx->getObject(modResource::class, [
                'pagetitle' => 'GoodNews Unsubscribe'
            ]);
            if (is_object($unsubscribeResource)) {
                $properties['unsubscribeResource'] = $unsubscribeResource->get('id');
            } else {
                $modx->log(
                    modX::LOG_LEVEL_WARN,
                    '-> unsubscribeResource property for GoodNews container not set as Resource does not exist. ' .
                    'Please set manually.'
                );
            }

            // Set default resource for updating subscription profile
            $profileResource = $modx->getObject(modResource::class, [
                'pagetitle' => 'GoodNews Subscription Update'
            ]);
            if (is_object($profileResource)) {
                $properties['profileResource'] = $profileResource->get('id');
            } else {
                $modx->log(
                    modX::LOG_LEVEL_WARN,
                    '-> profileResource property for GoodNews container not set as Resource does not exist. ' .
                    'Please set manually.'
                );
            }

            $properties['editorGroups'] = 'Administrator';
            // default sender email address (this is also the mailbox for bounce messages)
            $properties['mailFrom']                        = $modx->getOption('emailsender', null, 'postmaster@mydomain.com');
            $properties['mailFromName']                    = $modx->getOption('site_name', null, 'Sender Name');
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
