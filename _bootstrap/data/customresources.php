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

use MODX\Revolution\modCategory;
use MODX\Revolution\modTemplate;
use MODX\Revolution\modResource;

/**
 * Add custom MODX resources to package
 *
 * @package goodnews
 * @subpackage bootstrap
 */

$customresources = [];
$epoch = time();

// GoodNews Mailing Container
$customresources['goodnews'] = [
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'GoodNews',
    'longtitle'             => '',
    'description'           => '',
    'alias'                 => 'goodnews',
    'link_attributes'       => '',
    'published'             => 1,
    'pub_date'              => 0,
    'unpub_date'            => 0,
    'isfolder'              => 1,
    'introtext'             => '',
    'content'               => 'custom.goodnews.container.tpl',
    'richtext'              => 1,
    'template'              => 'sample.GoodNewsContainerTemplate',
    'searchable'            => 0,
    'cacheable'             => 1,
    'createdby'             => 0,
    'createdon'             => $epoch,
    'editedby'              => 0,
    'editedon'              => $epoch,
    'deleted'               => 0,
    'deletedon'             => 0,
    'deletedby'             => 0,
    'publishedon'           => $epoch,
    'publishedby'           => 0,
    'menutitle'             => '',
    'donthit'               => 0,
    'privateweb'            => 0,
    'privatemgr'            => 0,
    'content_dispo'         => 0,
    'hidemenu'              => 1,
    'class_key'             => 'GoodNews\Model\GoodNewsResourceContainer',
    'context_key'           => 'web',
    'content_type'          => 1,
    'uri_override'          => 0,
    'hide_children_in_tree' => 1,
    'show_in_tree'          => 1,
    'properties'            => null,
];

// array key = later properties namespace
$properties['goodnews'] = [];

// Set default mailing templates category
$templatesCategory = $modx->getObject('modCategory', ['category' => 'Newsletter Templates']);
if (is_object($templatesCategory)) {
    $properties['goodnews']['templatesCategory'] = $templatesCategory->get('id');
} else {
    $properties['goodnews']['templatesCategory'] = 0;
    $modx->log(modX::LOG_LEVEL_ERROR, '-> could not set templatesCategory property for GoodNews container.');
}

// Set default mailing template
$mailingTemplate = $modx->getObject('modTemplate', ['templatename' => 'sample.GoodNewsNewsletterTemplate1']);
if (is_object($mailingTemplate)) {
    $properties['goodnews']['mailingTemplate'] = $mailingTemplate->get('id');
} else {
    $properties['goodnews']['mailingTemplate'] = 0;
    $modx->log(modX::LOG_LEVEL_ERROR, '-> could not set mailingTemplate property for GoodNews container.');
}

// Set default resource for 1-click unsubscription
$unsubscribeResource = $modx->getObject('modResource', ['pagetitle' => 'GoodNews Unsubscribe']);
if (is_object($unsubscribeResource)) {
    $properties['goodnews']['unsubscribeResource'] = $unsubscribeResource->get('id');
} else {
    $properties['goodnews']['unsubscribeResource'] = 0;
    $modx->log(modX::LOG_LEVEL_WARN, '-> unsubscribeResource property for GoodNews container not set as resource does not exist. Please set manually.');
}

// Set default resource for updating subscription profile
$profileResource = $modx->getObject('modResource', ['pagetitle' => 'GoodNews Subscription Update']);
if (is_object($profileResource)) {
    $properties['goodnews']['profileResource'] = $profileResource->get('id');
} else {
    $properties['goodnews']['profileResource'] = 0;
    $modx->log(modX::LOG_LEVEL_WARN, '-> profileResource property for GoodNews container not set as resource does not exist. Please set manually.');
}

$properties['goodnews']['editorGroups'] = 'Administrator';
// default sender email address (this is also the mailbox for bounce messages)
$properties['goodnews']['mailFrom']                        = $modx->getOption('emailsender', null, 'postmaster@mydomain.com');
$properties['goodnews']['mailFromName']                    = $modx->getOption('site_name', null, 'Sender Name');
$properties['goodnews']['mailReplyTo']                     = $modx->getOption('emailsender', null, 'replyto@mydomain.com');
$properties['goodnews']['mailCharset']                     = 'UTF-8';
$properties['goodnews']['mailEncoding']                    = '8bit';
$properties['goodnews']['mailBounceHandling']              = '0';
$properties['goodnews']['mailUseSmtp']                     = '0';
$properties['goodnews']['mailSmtpAuth']                    = '0';
$properties['goodnews']['mailSmtpUser']                    = '';
$properties['goodnews']['mailSmtpPass']                    = '';
$properties['goodnews']['mailSmtpHosts']                   = 'localhost:25';
$properties['goodnews']['mailSmtpPrefix']                  = '';
$properties['goodnews']['mailSmtpKeepalive']               = '0';
$properties['goodnews']['mailSmtpTimeout']                 = 10;
$properties['goodnews']['mailSmtpSingleTo']                = '0';
$properties['goodnews']['mailSmtpHelo']                    = '';
$properties['goodnews']['mailService']                     = 'imap';
$properties['goodnews']['mailMailHost']                    = '';
$properties['goodnews']['mailMailboxUsername']             = '';
$properties['goodnews']['mailMailboxPassword']             = '';
$properties['goodnews']['mailBoxname']                     = 'INBOX';
$properties['goodnews']['mailPort']                        = '143';
$properties['goodnews']['mailServiceOption']               = 'notls';
$properties['goodnews']['mailSoftBouncedMessageAction']    = 'delete';
$properties['goodnews']['mailSoftMailbox']                 = 'INBOX.Softbounces';
$properties['goodnews']['mailMaxSoftBounces']              = 3;
$properties['goodnews']['mailMaxSoftBouncesAction']        = 'disable';
$properties['goodnews']['mailHardBouncedMessageAction']    = 'delete';
$properties['goodnews']['mailHardMailbox']                 = 'INBOX.Hardbounces';
$properties['goodnews']['mailMaxHardBounces']              = 1;
$properties['goodnews']['mailMaxHardBouncesAction']        = 'disable';
$properties['goodnews']['mailNotClassifiedMessageAction']  = 'move';
$properties['goodnews']['mailNotClassifiedMailbox']        = 'INBOX.NotClassified';
$properties['goodnews']['collection1Name']                 = '';
$properties['goodnews']['collection1Parents']              = '';
$properties['goodnews']['collection2Name']                 = '';
$properties['goodnews']['collection2Parents']              = '';
$properties['goodnews']['collection3Name']                 = '';
$properties['goodnews']['collection3Parents']              = '';

// Add resource properties to properties key as array (needs to be later converted by setProperties!)
$customresources['goodnews']['properties'] = $properties['goodnews'];

/*
// Another custom resource
$customresources['namespace'] = [
    'type'                  => 'document',
    'contentType'           => 'text/html',
    'pagetitle'             => 'Pagetitle',
    ...
];
$properties['namespace'] = array();
$properties['namespace']['myKey'] = 'myValue';
...
$customresources['namespace']['properties'] = $properties['namespace'];
*/

unset($epoch, $properties, $templatesCategory, $mailingTemplate, $unsubscribeResource, $profileResource);
return $customresources;
