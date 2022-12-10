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

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/model/goodnews/goodnewsbmh.class.php';

/**
 * Container definition update processor
 *
 * @package goodnews
 * @subpackage processors
 */
class ContainerSettingsUpdateProcessor extends modObjectUpdateProcessor {
    public $classKey = 'modResource';
    public $languageTopics = array('resource','goodnews:default');
    public $permission = 'save_document';
    public $objectType = 'resource';
    public $beforeSaveEvent = 'OnBeforeDocFormSave';
    public $afterSaveEvent = 'OnDocFormSave';

    public function beforeSave() {

        // make sure editor_groups was specified
        $editorGroups = $this->getProperty('editor_groups');
        if (empty($editorGroups)) {
            $this->addFieldError('editor_groups', $this->modx->lexicon('goodnews.settings_container_err_ns_editor_groups'));
        }
        $this->object->setProperty('editorGroups', $editorGroups, 'goodnews');
        
        // make sure mail_from was specified
        $mailFrom = $this->getProperty('mail_from');
        if (empty($mailFrom)) {
            $this->addFieldError('mail_from', $this->modx->lexicon('goodnews.settings_container_err_ns_mail_from'));
        }
        $this->object->setProperty('mailFrom', $mailFrom, 'goodnews');
               
        // make sure mail_from_name was specified
        $mailFromName = $this->getProperty('mail_from_name');
        if (empty($mailFromName)) {
            $this->addFieldError('mail_from_name', $this->modx->lexicon('goodnews.settings_container_err_ns_mail_from_name'));
        }
        $this->object->setProperty('mailFromName', $mailFromName, 'goodnews');
        
        // make sure mail_reply_to was specified
        $mailReplyTo = $this->getProperty('mail_reply_to');
        if (empty($mailReplyTo)) {
            $this->addFieldError('mail_reply_to', $this->modx->lexicon('goodnews.settings_container_err_ns_mail_reply_to'));
        }
        $this->object->setProperty('mailReplyTo', $mailReplyTo, 'goodnews');

        // make sure mail_charset was specified
        $mailCharset = $this->getProperty('mail_charset');
        if (empty($mailCharset)) {
            $this->addFieldError('mail_charset', $this->modx->lexicon('goodnews.settings_container_err_ns_mail_charset'));
        }
        $this->object->setProperty('mailCharset', $mailCharset, 'goodnews');

        // make sure mail_encoding was specified
        $mailEncoding = $this->getProperty('mail_encoding');
        if (empty($mailEncoding)) {
            $this->addFieldError('mail_encoding', $this->modx->lexicon('goodnews.settings_container_err_ns_mail_encoding'));
        }
        $this->object->setProperty('mailEncoding', $mailEncoding, 'goodnews');

        // bouncehandling (boolean)
        $mailBounceHandling = $this->getProperty('mail_bouncehandling');
        $this->object->setProperty('mailBounceHandling', $mailBounceHandling, 'goodnews');

        // use SMTP
        $mailUseSmtp = $this->getProperty('mail_use_smtp');
        $this->object->setProperty('mailUseSmtp', $mailUseSmtp, 'goodnews');

        // SMTP authentification  (boolean)
        $mailSmtpAuth = $this->getProperty('mail_smtp_auth');
        $this->object->setProperty('mailSmtpAuth', $mailSmtpAuth, 'goodnews');

        // SMTP user name
        $mailSmtpUser = $this->getProperty('mail_smtp_user');
        $this->object->setProperty('mailSmtpUser', $mailSmtpUser, 'goodnews');

        // SMTP password
        $mailSmtpPass = $this->getProperty('mail_smtp_pass');
        $this->object->setProperty('mailSmtpPass', $mailSmtpPass, 'goodnews');
        
        // SMTP hosts + ports
        $mailSmtpHosts = $this->getProperty('mail_smtp_hosts');
        $this->object->setProperty('mailSmtpHosts', $mailSmtpHosts, 'goodnews');
        
        // SMTP prefix
        $mailSmtpPrefix = $this->getProperty('mail_smtp_prefix');
        $this->object->setProperty('mailSmtpPrefix', $mailSmtpPrefix, 'goodnews');
        
        // SMTP keep alive (boolean)
        $mailSmtpKeepalive = $this->getProperty('mail_smtp_keepalive');
        $this->object->setProperty('mailSmtpKeepalive', $mailSmtpKeepalive, 'goodnews');
        
        // SMTP timeout
        $mailSmtpTimeout = $this->getProperty('mail_smtp_timeout');
        $this->object->setProperty('mailSmtpTimeout', $mailSmtpTimeout, 'goodnews');
        
        // SMTP single TO (boolean)
        $mailSmtpSingleTo = $this->getProperty('mail_smtp_single_to');
        $this->object->setProperty('mailSmtpSingleTo', $mailSmtpSingleTo, 'goodnews');
        
        // SMTP helo
        $mailSmtpHelo = $this->getProperty('mail_smtp_helo');
        $this->object->setProperty('mailSmtpHelo', $mailSmtpHelo, 'goodnews');

        // service
        $mailService = $this->getProperty('mail_service');
        $this->object->setProperty('mailService', $mailService, 'goodnews');
        
        // mailhost
        $mailMailHost = $this->getProperty('mail_mailhost');
        $this->object->setProperty('mailMailHost', $mailMailHost, 'goodnews');

        // mailbox_username
        $mailMailboxUsername = $this->getProperty('mail_mailbox_username');
        $this->object->setProperty('mailMailboxUsername', $mailMailboxUsername, 'goodnews');
        
        // mailbox_password
        $mailMailboxPassword = $this->getProperty('mail_mailbox_password');
        $this->object->setProperty('mailMailboxPassword', $mailMailboxPassword, 'goodnews');
        
        // boxname
        $mailBoxname = $this->getProperty('mail_boxname');
        $this->object->setProperty('mailBoxname', $mailBoxname, 'goodnews');
        
        // port
        $mailPort = $this->getProperty('mail_port');
        $this->object->setProperty('mailPort', $mailPort, 'goodnews');
        
        // service_option
        $mailServiceOption = $this->getProperty('mail_service_option');
        $this->object->setProperty('mailServiceOption', $mailServiceOption, 'goodnews');

        // softbounced_message_action (move | delete)
        $mailSoftBouncedMessageAction = $this->getProperty('mail_softbounced_message_action');
        $this->object->setProperty('mailSoftBouncedMessageAction', $mailSoftBouncedMessageAction, 'goodnews');

        // soft_mailbox
        $mailSoftMailbox = $this->getProperty('mail_soft_mailbox');
        $this->object->setProperty('mailSoftMailbox', $mailSoftMailbox, 'goodnews');

        // max_softbounces (if this count of soft bounces is reached - process subscriber)
        $mailMaxSoftBounces = $this->getProperty('mail_max_softbounces');
        $this->object->setProperty('mailMaxSoftBounces', $mailMaxSoftBounces, 'goodnews');

        // max_softbounces_action (disable | delete)
        $mailMaxSoftBouncesAction = $this->getProperty('mail_max_softbounces_action');
        $this->object->setProperty('mailMaxSoftBouncesAction', $mailMaxSoftBouncesAction, 'goodnews');

        // hardbounced_message_action (move | delete)
        $mailHardBouncedMessageAction = $this->getProperty('mail_hardbounced_message_action');
        $this->object->setProperty('mailHardBouncedMessageAction', $mailHardBouncedMessageAction, 'goodnews');

        // hard_mailbox
        $mailHardMailbox = $this->getProperty('mail_hard_mailbox');
        $this->object->setProperty('mailHardMailbox', $mailHardMailbox, 'goodnews');

        // max_hardbounces (if this count of hard bounces is reached - process subscriber)
        $mailMaxHardBounces = $this->getProperty('mail_max_hardbounces');
        $this->object->setProperty('mailMaxHardBounces', $mailMaxHardBounces, 'goodnews');

        // max_hardbounces_action (disable | delete)
        $mailMaxHardBouncesAction = $this->getProperty('mail_max_hardbounces_action');
        $this->object->setProperty('mailMaxHardBouncesAction', $mailMaxHardBouncesAction, 'goodnews');

        // notclassified_message_action (move | delete)
        $mailNotClassifiedMessageAction = $this->getProperty('mail_notclassified_message_action');
        $this->object->setProperty('mailNotClassifiedMessageAction', $mailNotClassifiedMessageAction, 'goodnews');

        // notclassified_mailbox
        $mailNotClassifiedMailbox = $this->getProperty('mail_notclassified_mailbox');
        $this->object->setProperty('mailNotClassifiedMailbox', $mailNotClassifiedMailbox, 'goodnews');

        // resource collection settings
        $collection1Name    = $this->getProperty('collection1_name');
        $this->object->setProperty('collection1Name', $collection1Name, 'goodnews');
        
        $collection1Parents = $this->getProperty('collection1_parents');
        $this->object->setProperty('collection1Parents', $collection1Parents, 'goodnews');
        
        $collection2Name    = $this->getProperty('collection2_name');
        $this->object->setProperty('collection2Name', $collection2Name, 'goodnews');
        
        $collection2Parents = $this->getProperty('collection2_parents');
        $this->object->setProperty('collection2Parents', $collection2Parents, 'goodnews');
        
        $collection3Name    = $this->getProperty('collection3_name');
        $this->object->setProperty('collection3Name', $collection3Name, 'goodnews');
        
        $collection3Parents = $this->getProperty('collection3_parents');
        $this->object->setProperty('collection3Parents', $collection3Parents, 'goodnews');

        $this->object->set('editedby', $this->modx->user->get('id'));
        $this->object->set('editedon', time(), 'integer');

        // If no field error occures -> run connection test to mailserver/mailbox!
        if ($mailBounceHandling && !$this->hasErrors()) {
            if ($this->connectionTest() == false) {
                $this->failure($this->modx->lexicon('goodnews.settings_container_err_mailbox_connection_failed'));
            }
        }
        return parent::beforeSave();
    }

    public function afterSave() {
        $this->setProperty('clearCache', true);

        // update properties of all child resources (merge with existing properties)
        $parentProperties = $this->object->getProperties('goodnews');

        foreach ($this->object->getIterator('Children') as $child) {
            $child->setProperties($parentProperties, 'goodnews');
            if (!$child->save()) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, "Could not change properties of child resource {$child->get('id')}", '', __METHOD__, __FILE__, __LINE__);
            }
        }
        return parent::afterSave();
    }
    
    /**
     * Test mailbox connection.
     * 
     * @access private
     * @return boolean
     */
    private function connectionTest() {
        
        $bmh = new GoodNewsBounceMailHandler($this->modx);
        if (!($bmh instanceof GoodNewsBounceMailHandler)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] GoodNewsBounceMailHandler class could not be instantiated.');
            return false;
        }

        $bmh->testmode              = true;
        
        $bmh->mailService           = $this->object->getProperty('mailService', 'goodnews');
        $bmh->mailMailHost          = $this->object->getProperty('mailMailHost', 'goodnews');
        $bmh->mailMailboxUsername   = $this->object->getProperty('mailMailboxUsername', 'goodnews');
        $bmh->mailMailboxPassword   = $this->object->getProperty('mailMailboxPassword', 'goodnews');
        $bmh->mailBoxname           = $this->object->getProperty('mailBoxname', 'goodnews');
        $bmh->mailPort              = $this->object->getProperty('mailPort', 'goodnews');
        $bmh->mailServiceOption     = $this->object->getProperty('mailServiceOption', 'goodnews');
        
        if ($bmh->openImapStream()) {
            $bmh->closeImapStream();
            return true;
        } else {
            return false;
        }    
    }
}
return 'ContainerSettingsUpdateProcessor';