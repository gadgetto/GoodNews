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
 * Container get list processor (for settings panel)
 *
 * @package goodnews
 * @subpackage processors
 */

class ContainerSettingsGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'GoodNewsResourceContainer';
    public $languageTopics = array('resource','goodnews:default');
    public $checkListPermission = true;
    public $defaultSortField = 'pagetitle';
    public $defaultSortDirection = 'ASC';
    
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->select(array(
            'id',
            'pagetitle',
            'context_key',
            'properties',
        ));
        $c->where(array(
            'class_key' => 'GoodNewsResourceContainer'
            ,'deleted' => 0
        ));
        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $resourceArray = parent::prepareRow($object);

        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $resourceArray['pagetitle'] = htmlentities($resourceArray['pagetitle'], ENT_COMPAT, $charset);
        
        // get properties field as array
        if (!empty($resourceArray['properties'])) {
            $properties = $resourceArray['properties'];
        } else {
            $properties = array();
        }

        // action buttons in grid row
        $resourceArray['actions'] = array();
        $resourceArray['actions'][] = array(
            'className' => 'settings fa fa-lg fa-cog',
            'text' => $this->modx->lexicon('goodnews.settings_container_update'),
        );


        // get container setting (from Resource properties field)
        if (array_key_exists('goodnews', $properties)) {
            
            // General settings
            if (array_key_exists('editorGroups', $properties['goodnews'])) {
                $resourceArray['editor_groups'] = $properties['goodnews']['editorGroups'];
            } else {
                $resourceArray['editor_groups'] = '';
            }
            if (array_key_exists('mailFrom', $properties['goodnews'])) {
                $resourceArray['mail_from'] = $properties['goodnews']['mailFrom'];
            } else {
                $resourceArray['mail_from'] = '';
            }
            if (array_key_exists('mailFromName', $properties['goodnews'])) {
                $resourceArray['mail_from_name'] = $properties['goodnews']['mailFromName'];
            } else {
                $resourceArray['mail_from_name'] = '';
            }
            if (array_key_exists('mailReplyTo', $properties['goodnews'])) {
                $resourceArray['mail_reply_to'] = $properties['goodnews']['mailReplyTo'];
            } else {
                $resourceArray['mail_reply_to'] = '';
            }
            if (array_key_exists('mailCharset', $properties['goodnews'])) {
                $resourceArray['mail_charset'] = $properties['goodnews']['mailCharset'];
            } else {
                $resourceArray['mail_charset'] = 'UTF-8';
            }
            if (array_key_exists('mailEncoding', $properties['goodnews'])) {
                $resourceArray['mail_encoding'] = $properties['goodnews']['mailEncoding'];
            } else {
                $resourceArray['mail_encoding'] = '8bit';
            }
            if (array_key_exists('mailBounceHandling', $properties['goodnews'])) {
                $resourceArray['mail_bouncehandling'] = $properties['goodnews']['mailBounceHandling'];
            } else {
                $resourceArray['mail_bouncehandling'] = '0';
            }
            
            // SMTP settings
            if (array_key_exists('mailUseSmtp', $properties['goodnews'])) {
                $resourceArray['mail_use_smtp'] = $properties['goodnews']['mailUseSmtp'];
            } else {
                $resourceArray['mail_use_smtp'] = '0';
            }
            if (array_key_exists('mailSmtpAuth', $properties['goodnews'])) {
                $resourceArray['mail_smtp_auth'] = $properties['goodnews']['mailSmtpAuth'];
            } else {
                $resourceArray['mail_smtp_auth'] = '0';
            }
            if (array_key_exists('mailSmtpUser', $properties['goodnews'])) {
                $resourceArray['mail_smtp_user'] = $properties['goodnews']['mailSmtpUser'];
            } else {
                $resourceArray['mail_smtp_user'] = '';
            }
            if (array_key_exists('mailSmtpPass', $properties['goodnews'])) {
                $resourceArray['mail_smtp_pass'] = $properties['goodnews']['mailSmtpPass'];
            } else {
                $resourceArray['mail_smtp_pass'] = '';
            }
            if (array_key_exists('mailSmtpHosts', $properties['goodnews'])) {
                $resourceArray['mail_smtp_hosts'] = $properties['goodnews']['mailSmtpHosts'];
            } else {
                $resourceArray['mail_smtp_hosts'] = 'localhost:25';
            }
            if (array_key_exists('mailSmtpPrefix', $properties['goodnews'])) {
                $resourceArray['mail_smtp_prefix'] = $properties['goodnews']['mailSmtpPrefix'];
            } else {
                $resourceArray['mail_smtp_prefix'] = '';
            }
            if (array_key_exists('mailSmtpKeepalive', $properties['goodnews'])) {
                $resourceArray['mail_smtp_keepalive'] = $properties['goodnews']['mailSmtpKeepalive'];
            } else {
                $resourceArray['mail_smtp_keepalive'] = '0';
            }
            if (array_key_exists('mailSmtpTimeout', $properties['goodnews'])) {
                $resourceArray['mail_smtp_timeout'] = $properties['goodnews']['mailSmtpTimeout'];
            } else {
                $resourceArray['mail_smtp_timeout'] = 10;
            }
            if (array_key_exists('mailSmtpSingleTo', $properties['goodnews'])) {
                $resourceArray['mail_smtp_single_to'] = $properties['goodnews']['mailSmtpSingleTo'];
            } else {
                $resourceArray['mail_smtp_single_to'] = '0';
            }
            if (array_key_exists('mailSmtpHelo', $properties['goodnews'])) {
                $resourceArray['mail_smtp_helo'] = $properties['goodnews']['mailSmtpHelo'];
            } else {
                $resourceArray['mail_smtp_helo'] = '';
            }
            
            // Bounce Mailbox
            if (array_key_exists('mailService', $properties['goodnews'])) {
                $resourceArray['mail_service'] = $properties['goodnews']['mailService'];
            } else {
                $resourceArray['mail_service'] = 'pop3';
            }
            if (array_key_exists('mailMailHost', $properties['goodnews'])) {
                $resourceArray['mail_mailhost'] = $properties['goodnews']['mailMailHost'];
            } else {
                $resourceArray['mail_mailhost'] = '';
            }
            if (array_key_exists('mailMailboxUsername', $properties['goodnews'])) {
                $resourceArray['mail_mailbox_username'] = $properties['goodnews']['mailMailboxUsername'];
            } else {
                $resourceArray['mail_mailbox_username'] = '';
            }
            if (array_key_exists('mailMailboxPassword', $properties['goodnews'])) {
                $resourceArray['mail_mailbox_password'] = $properties['goodnews']['mailMailboxPassword'];
            } else {
                $resourceArray['mail_mailbox_password'] = '';
            }
            if (array_key_exists('mailBoxname', $properties['goodnews'])) {
                $resourceArray['mail_boxname'] = $properties['goodnews']['mailBoxname'];
            } else {
                $resourceArray['mail_boxname'] = 'INBOX';
            }
            if (array_key_exists('mailPort', $properties['goodnews'])) {
                $resourceArray['mail_port'] = $properties['goodnews']['mailPort'];
            } else {
                $resourceArray['mail_port'] = '143';
            }
            if (array_key_exists('mailServiceOption', $properties['goodnews'])) {
                $resourceArray['mail_service_option'] = $properties['goodnews']['mailServiceOption'];
            } else {
                $resourceArray['mail_service_option'] = 'notls';
            }
            
            // Soft Bounces handling
            if (array_key_exists('mailSoftBouncedMessageAction', $properties['goodnews'])) {
                $resourceArray['mail_softbounced_message_action'] = $properties['goodnews']['mailSoftBouncedMessageAction'];
            } else {
                $resourceArray['mail_softbounced_message_action'] = 'delete';
            }
            if (array_key_exists('mailSoftMailbox', $properties['goodnews'])) {
                $resourceArray['mail_soft_mailbox'] = $properties['goodnews']['mailSoftMailbox'];
            } else {
                $resourceArray['mail_soft_mailbox'] = 'INBOX.Softbounces';
            }
            if (array_key_exists('mailMaxSoftBounces', $properties['goodnews'])) {
                $resourceArray['mail_max_softbounces'] = $properties['goodnews']['mailMaxSoftBounces'];
            } else {
                $resourceArray['mail_max_softbounces'] = '3';
            }
            if (array_key_exists('mailMaxSoftBouncesAction', $properties['goodnews'])) {
                $resourceArray['mail_max_softbounces_action'] = $properties['goodnews']['mailMaxSoftBouncesAction'];
            } else {
                $resourceArray['mail_max_softbounces_action'] = 'disable';
            }

            // Hard bounces handling            
            if (array_key_exists('mailHardBouncedMessageAction', $properties['goodnews'])) {
                $resourceArray['mail_hardbounced_message_action'] = $properties['goodnews']['mailHardBouncedMessageAction'];
            } else {
                $resourceArray['mail_hardbounced_message_action'] = 'delete';
            }
            if (array_key_exists('mailHardMailbox', $properties['goodnews'])) {
                $resourceArray['mail_hard_mailbox'] = $properties['goodnews']['mailHardMailbox'];
            } else {
                $resourceArray['mail_hard_mailbox'] = 'INBOX.Hardbounces';
            }
            if (array_key_exists('mailMaxHardBounces', $properties['goodnews'])) {
                $resourceArray['mail_max_hardbounces'] = $properties['goodnews']['mailMaxHardBounces'];
            } else {
                $resourceArray['mail_max_hardbounces'] = '1';
            }
            if (array_key_exists('mailMaxHardBouncesAction', $properties['goodnews'])) {
                $resourceArray['mail_max_hardbounces_action'] = $properties['goodnews']['mailMaxHardBouncesAction'];
            } else {
                $resourceArray['mail_max_hardbounces_action'] = 'delete';
            }

            // Unclassified handling
            if (array_key_exists('mailNotClassifiedMessageAction', $properties['goodnews'])) {
                $resourceArray['mail_notclassified_message_action'] = $properties['goodnews']['mailNotClassifiedMessageAction'];
            } else {
                $resourceArray['mail_notclassified_message_action'] = 'move';
            }
            if (array_key_exists('mailNotClassifiedMailbox', $properties['goodnews'])) {
                $resourceArray['mail_notclassified_mailbox'] = $properties['goodnews']['mailNotClassifiedMailbox'];
            } else {
                $resourceArray['mail_notclassified_mailbox'] = 'INBOX.NotClassified';
            }
            
            // Content Collection handling
            if (array_key_exists('collection1Name', $properties['goodnews'])) {
                $resourceArray['collection1_name'] = $properties['goodnews']['collection1Name'];
            } else {
                $resourceArray['collection1_name'] = '';
            }
            if (array_key_exists('collection1Parents', $properties['goodnews'])) {
                $resourceArray['collection1_parents'] = $properties['goodnews']['collection1Parents'];
            } else {
                $resourceArray['collection1_parents'] = '';
            }
            if (array_key_exists('collection2Name', $properties['goodnews'])) {
                $resourceArray['collection2_name'] = $properties['goodnews']['collection2Name'];
            } else {
                $resourceArray['collection2_name'] = '';
            }
            if (array_key_exists('collection2Parents', $properties['goodnews'])) {
                $resourceArray['collection2_parents'] = $properties['goodnews']['collection2Parents'];
            } else {
                $resourceArray['collection2_parents'] = '';
            }
            if (array_key_exists('collection3Name', $properties['goodnews'])) {
                $resourceArray['collection3_name'] = $properties['goodnews']['collection3Name'];
            } else {
                $resourceArray['collection3_name'] = '';
            }
            if (array_key_exists('collection3Parents', $properties['goodnews'])) {
                $resourceArray['collection3_parents'] = $properties['goodnews']['collection3Parents'];
            } else {
                $resourceArray['collection3_parents'] = '';
            }

        }
        return $resourceArray;
    }
}
return 'ContainerSettingsGetListProcessor';
