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

namespace Bitego\GoodNews\Processors\Settings\Container;

use Bitego\GoodNews\BounceMailHandler\BounceMailHandler;
use MODX\Revolution\modX;
use MODX\Revolution\modResource;
use MODX\Revolution\Processors\Model\UpdateProcessor;

/**
 * Container settings update processor (for settings panel)
 *
 * @package goodnews
 * @subpackage processors
 */
class Update extends UpdateProcessor
{
    public $classKey = modResource::class;
    public $languageTopics = ['resource', 'goodnews:default'];
    public $permission = 'save_document';
    public $objectType = 'resource';
    public $beforeSaveEvent = 'OnBeforeDocFormSave';
    public $afterSaveEvent = 'OnDocFormSave';

    public function beforeSave()
    {
        // make sure editor_groups was specified (string)
        $editorGroups = $this->getProperty('editor_groups');
        if (empty($editorGroups)) {
            $this->addFieldError(
                'editor_groups',
                $this->modx->lexicon('goodnews.settings_container_err_ns_editor_groups')
            );
        }
        $this->object->setProperty('editorGroups', $editorGroups, 'goodnews');

        // make sure mail_from was specified (string)
        $mailFrom = $this->getProperty('mail_from');
        if (empty($mailFrom)) {
            $this->addFieldError(
                'mail_from',
                $this->modx->lexicon('goodnews.settings_container_err_ns_mail_from')
            );
        }
        $this->object->setProperty('mailFrom', $mailFrom, 'goodnews');

        // make sure mail_from_name was specified (string)
        $mailFromName = $this->getProperty('mail_from_name');
        if (empty($mailFromName)) {
            $this->addFieldError(
                'mail_from_name',
                $this->modx->lexicon('goodnews.settings_container_err_ns_mail_from_name')
            );
        }
        $this->object->setProperty('mailFromName', $mailFromName, 'goodnews');

        // make sure mail_reply_to was specified (string)
        $mailReplyTo = $this->getProperty('mail_reply_to');
        if (empty($mailReplyTo)) {
            $this->addFieldError(
                'mail_reply_to',
                $this->modx->lexicon('goodnews.settings_container_err_ns_mail_reply_to')
            );
        }
        $this->object->setProperty('mailReplyTo', $mailReplyTo, 'goodnews');

        // make sure mail_charset was specified (string)
        $mailCharset = $this->getProperty('mail_charset');
        if (empty($mailCharset)) {
            $this->addFieldError(
                'mail_charset',
                $this->modx->lexicon('goodnews.settings_container_err_ns_mail_charset')
            );
        }
        $this->object->setProperty('mailCharset', $mailCharset, 'goodnews');

        // make sure mail_encoding was specified (string)
        $mailEncoding = $this->getProperty('mail_encoding');
        if (empty($mailEncoding)) {
            $this->addFieldError(
                'mail_encoding',
                $this->modx->lexicon('goodnews.settings_container_err_ns_mail_encoding')
            );
        }
        $this->object->setProperty('mailEncoding', $mailEncoding, 'goodnews');

        // bouncehandling (0 = false, 1 = true)
        $mailBounceHandling = $this->getProperty('mail_bouncehandling');
        $this->object->setProperty('mailBounceHandling', intval($mailBounceHandling), 'goodnews');

        // use SMTP (0 = false, 1 = true, 2 = Mandrill)
        $mailUseSmtp = $this->getProperty('mail_use_smtp');
        $this->object->setProperty('mailUseSmtp', intval($mailUseSmtp), 'goodnews');

        // SMTP authentification (0 = false, 1 = true)
        $mailSmtpAuth = $this->getProperty('mail_smtp_auth');
        $this->object->setProperty('mailSmtpAuth', intval($mailSmtpAuth), 'goodnews');

        // SMTP user name (string)
        $mailSmtpUser = $this->getProperty('mail_smtp_user');
        $this->object->setProperty('mailSmtpUser', $mailSmtpUser, 'goodnews');

        // SMTP password (string)
        $mailSmtpPass = $this->getProperty('mail_smtp_pass');
        $this->object->setProperty('mailSmtpPass', $mailSmtpPass, 'goodnews');

        // SMTP hosts + ports (string)
        $mailSmtpHosts = $this->getProperty('mail_smtp_hosts');
        $this->object->setProperty('mailSmtpHosts', $mailSmtpHosts, 'goodnews');

        // SMTP prefix (string)
        $mailSmtpPrefix = $this->getProperty('mail_smtp_prefix');
        $this->object->setProperty('mailSmtpPrefix', $mailSmtpPrefix, 'goodnews');

        // SMTP keep alive (0 = false, 1 = true)
        $mailSmtpKeepalive = $this->getProperty('mail_smtp_keepalive');
        $this->object->setProperty('mailSmtpKeepalive', intval($mailSmtpKeepalive), 'goodnews');

        // SMTP timeout (integer)
        $mailSmtpTimeout = $this->getProperty('mail_smtp_timeout');
        $this->object->setProperty('mailSmtpTimeout', intval($mailSmtpTimeout), 'goodnews');

        // SMTP single TO (0 = false, 1 = true)
        $mailSmtpSingleTo = $this->getProperty('mail_smtp_single_to');
        $this->object->setProperty('mailSmtpSingleTo', intval($mailSmtpSingleTo), 'goodnews');

        // SMTP helo (string)
        $mailSmtpHelo = $this->getProperty('mail_smtp_helo');
        $this->object->setProperty('mailSmtpHelo', $mailSmtpHelo, 'goodnews');

        // service (string: pop3, imap)
        $mailService = $this->getProperty('mail_service');
        $this->object->setProperty('mailService', $mailService, 'goodnews');

        // mailhost (string)
        $mailMailHost = $this->getProperty('mail_mailhost');
        $this->object->setProperty('mailMailHost', $mailMailHost, 'goodnews');

        // mailbox_username (string)
        $mailMailboxUsername = $this->getProperty('mail_mailbox_username');
        $this->object->setProperty('mailMailboxUsername', $mailMailboxUsername, 'goodnews');

        // mailbox_password (string)
        $mailMailboxPassword = $this->getProperty('mail_mailbox_password');
        $this->object->setProperty('mailMailboxPassword', $mailMailboxPassword, 'goodnews');

        // boxname (string)
        $mailBoxname = $this->getProperty('mail_boxname');
        $this->object->setProperty('mailBoxname', $mailBoxname, 'goodnews');

        // port (string)
        $mailPort = $this->getProperty('mail_port');
        $this->object->setProperty('mailPort', $mailPort, 'goodnews');

        // service_option (string: none, tls, notls, ssl)
        $mailServiceOption = $this->getProperty('mail_service_option');
        $this->object->setProperty('mailServiceOption', $mailServiceOption, 'goodnews');

        // softbounced_message_action (string: move, delete)
        $mailSoftBouncedMessageAction = $this->getProperty('mail_softbounced_message_action');
        $this->object->setProperty('mailSoftBouncedMessageAction', $mailSoftBouncedMessageAction, 'goodnews');

        // soft_mailbox (string)
        $mailSoftMailbox = $this->getProperty('mail_soft_mailbox');
        $this->object->setProperty('mailSoftMailbox', $mailSoftMailbox, 'goodnews');

        // max_softbounces (integer)
        $mailMaxSoftBounces = $this->getProperty('mail_max_softbounces');
        $this->object->setProperty('mailMaxSoftBounces', intval($mailMaxSoftBounces), 'goodnews');

        // max_softbounces_action (string: disable, delete)
        $mailMaxSoftBouncesAction = $this->getProperty('mail_max_softbounces_action');
        $this->object->setProperty('mailMaxSoftBouncesAction', $mailMaxSoftBouncesAction, 'goodnews');

        // hardbounced_message_action (string: move, delete)
        $mailHardBouncedMessageAction = $this->getProperty('mail_hardbounced_message_action');
        $this->object->setProperty('mailHardBouncedMessageAction', $mailHardBouncedMessageAction, 'goodnews');

        // hard_mailbox (string)
        $mailHardMailbox = $this->getProperty('mail_hard_mailbox');
        $this->object->setProperty('mailHardMailbox', $mailHardMailbox, 'goodnews');

        // max_hardbounces (integer)
        $mailMaxHardBounces = $this->getProperty('mail_max_hardbounces');
        $this->object->setProperty('mailMaxHardBounces', intval($mailMaxHardBounces), 'goodnews');

        // max_hardbounces_action (string: disable, delete)
        $mailMaxHardBouncesAction = $this->getProperty('mail_max_hardbounces_action');
        $this->object->setProperty('mailMaxHardBouncesAction', $mailMaxHardBouncesAction, 'goodnews');

        // notclassified_message_action (string: move, delete)
        $mailNotClassifiedMessageAction = $this->getProperty('mail_notclassified_message_action');
        $this->object->setProperty('mailNotClassifiedMessageAction', $mailNotClassifiedMessageAction, 'goodnews');

        // notclassified_mailbox (string)
        $mailNotClassifiedMailbox = $this->getProperty('mail_notclassified_mailbox');
        $this->object->setProperty('mailNotClassifiedMailbox', $mailNotClassifiedMailbox, 'goodnews');

        // (string)
        $collection1Name    = $this->getProperty('collection1_name');
        $this->object->setProperty('collection1Name', $collection1Name, 'goodnews');

        // (string)
        $collection1Parents = $this->getProperty('collection1_parents');
        $this->object->setProperty('collection1Parents', $collection1Parents, 'goodnews');

        // (string)
        $collection2Name    = $this->getProperty('collection2_name');
        $this->object->setProperty('collection2Name', $collection2Name, 'goodnews');

        // (string)
        $collection2Parents = $this->getProperty('collection2_parents');
        $this->object->setProperty('collection2Parents', $collection2Parents, 'goodnews');

        // (string)
        $collection3Name    = $this->getProperty('collection3_name');
        $this->object->setProperty('collection3Name', $collection3Name, 'goodnews');

        // (string)
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

    public function afterSave()
    {
        $this->setProperty('clearCache', true);

        // update properties of all child resources (merge with existing properties)
        $parentProperties = $this->object->getProperties('goodnews');

        foreach ($this->object->getIterator('Children') as $child) {
            $child->setProperties($parentProperties, 'goodnews');
            if (!$child->save()) {
                $this->modx->log(
                    modX::LOG_LEVEL_ERROR,
                    "[GoodNews] Could not change properties of child resource {$child->get('id')}",
                    '',
                    __METHOD__,
                    __FILE__,
                    __LINE__
                );
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
    private function connectionTest()
    {
        $bmh = new BounceMailHandler($this->modx);
        if (!($bmh instanceof BounceMailHandler)) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] BounceMailHandler class could not be instantiated.'
            );
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
