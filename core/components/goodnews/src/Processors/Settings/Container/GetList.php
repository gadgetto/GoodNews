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

use Bitego\GoodNews\Model\GoodNewsResourceContainer;
use MODX\Revolution\modX;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * Container get list processor (for settings panel)
 *
 * @package goodnews
 * @subpackage processors
 */

class GetList extends GetListProcessor
{
    public $classKey = GoodNewsResourceContainer::class;
    public $languageTopics = ['resource', 'goodnews:default'];
    public $checkListPermission = true;
    public $defaultSortField = 'pagetitle';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->select([
            'id',
            'pagetitle',
            'context_key',
            'properties',
        ]);
        $c->where([
            'class_key' => $this->classKey,
            'deleted' => 0,
        ]);
        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $resourceArray = parent::prepareRow($object);

        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $resourceArray['pagetitle'] = htmlentities($resourceArray['pagetitle'], ENT_COMPAT, $charset);

        // Get container setting (from Resource properties field)
        $properties = [];
        if (!empty($resourceArray['properties'])) {
            $properties = $resourceArray['properties'];
        }

        if (!array_key_exists('goodnews', $properties)) {
            return $resourceArray;
        }

        $properties = $properties['goodnews'];

        // General settings
        if (array_key_exists('editorGroups', $properties)) {
            $resourceArray['editor_groups'] = $properties['editorGroups'];
        } else {
            $resourceArray['editor_groups'] = 'Administrator';
        }
        if (array_key_exists('mailFrom', $properties)) {
            $resourceArray['mail_from'] = $properties['mailFrom'];
        } else {
            $resourceArray['mail_from'] = '';
        }
        if (array_key_exists('mailFromName', $properties)) {
            $resourceArray['mail_from_name'] = $properties['mailFromName'];
        } else {
            $resourceArray['mail_from_name'] = '';
        }
        if (array_key_exists('mailReplyTo', $properties)) {
            $resourceArray['mail_reply_to'] = $properties['mailReplyTo'];
        } else {
            $resourceArray['mail_reply_to'] = '';
        }
        if (array_key_exists('mailCharset', $properties)) {
            $resourceArray['mail_charset'] = $properties['mailCharset'];
        } else {
            $resourceArray['mail_charset'] = 'UTF-8';
        }
        if (array_key_exists('mailEncoding', $properties)) {
            $resourceArray['mail_encoding'] = $properties['mailEncoding'];
        } else {
            $resourceArray['mail_encoding'] = '8bit';
        }
        if (array_key_exists('mailBounceHandling', $properties)) {
            $resourceArray['mail_bouncehandling'] = intval($properties['mailBounceHandling']);
        } else {
            $resourceArray['mail_bouncehandling'] = 0;
        }
        // SMTP settings
        if (array_key_exists('mailUseSmtp', $properties)) {
            // 0 = false, 1 = true, 2 = Mandrill
            $resourceArray['mail_use_smtp'] = intval($properties['mailUseSmtp']);
        } else {
            $resourceArray['mail_use_smtp'] = 0;
        }
        if (array_key_exists('mailSmtpAuth', $properties)) {
            $resourceArray['mail_smtp_auth'] = intval($properties['mailSmtpAuth']);
        } else {
            $resourceArray['mail_smtp_auth'] = 0;
        }
        if (array_key_exists('mailSmtpUser', $properties)) {
            $resourceArray['mail_smtp_user'] = $properties['mailSmtpUser'];
        } else {
            $resourceArray['mail_smtp_user'] = '';
        }
        if (array_key_exists('mailSmtpPass', $properties)) {
            $resourceArray['mail_smtp_pass'] = $properties['mailSmtpPass'];
        } else {
            $resourceArray['mail_smtp_pass'] = '';
        }
        if (array_key_exists('mailSmtpHosts', $properties)) {
            $resourceArray['mail_smtp_hosts'] = $properties['mailSmtpHosts'];
        } else {
            $resourceArray['mail_smtp_hosts'] = 'localhost:25';
        }
        if (array_key_exists('mailSmtpPrefix', $properties)) {
            $resourceArray['mail_smtp_prefix'] = $properties['mailSmtpPrefix'];
        } else {
            $resourceArray['mail_smtp_prefix'] = '';
        }
        if (array_key_exists('mailSmtpKeepalive', $properties)) {
            $resourceArray['mail_smtp_keepalive'] = intval($properties['mailSmtpKeepalive']);
        } else {
            $resourceArray['mail_smtp_keepalive'] = 0;
        }
        if (array_key_exists('mailSmtpTimeout', $properties)) {
            $resourceArray['mail_smtp_timeout'] = $properties['mailSmtpTimeout'];
        } else {
            $resourceArray['mail_smtp_timeout'] = 10;
        }
        if (array_key_exists('mailSmtpSingleTo', $properties)) {
            $resourceArray['mail_smtp_single_to'] = intval($properties['mailSmtpSingleTo']);
        } else {
            $resourceArray['mail_smtp_single_to'] = 0;
        }
        if (array_key_exists('mailSmtpHelo', $properties)) {
            $resourceArray['mail_smtp_helo'] = $properties['mailSmtpHelo'];
        } else {
            $resourceArray['mail_smtp_helo'] = '';
        }
        // Bounce Mailbox
        if (array_key_exists('mailService', $properties)) {
            $resourceArray['mail_service'] = $properties['mailService'];
        } else {
            $resourceArray['mail_service'] = 'pop3';
        }
        if (array_key_exists('mailMailHost', $properties)) {
            $resourceArray['mail_mailhost'] = $properties['mailMailHost'];
        } else {
            $resourceArray['mail_mailhost'] = '';
        }
        if (array_key_exists('mailMailboxUsername', $properties)) {
            $resourceArray['mail_mailbox_username'] = $properties['mailMailboxUsername'];
        } else {
            $resourceArray['mail_mailbox_username'] = '';
        }
        if (array_key_exists('mailMailboxPassword', $properties)) {
            $resourceArray['mail_mailbox_password'] = $properties['mailMailboxPassword'];
        } else {
            $resourceArray['mail_mailbox_password'] = '';
        }
        if (array_key_exists('mailBoxname', $properties)) {
            $resourceArray['mail_boxname'] = $properties['mailBoxname'];
        } else {
            $resourceArray['mail_boxname'] = 'INBOX';
        }
        if (array_key_exists('mailPort', $properties)) {
            $resourceArray['mail_port'] = $properties['mailPort'];
        } else {
            $resourceArray['mail_port'] = '143';
        }
        if (array_key_exists('mailServiceOption', $properties)) {
            $resourceArray['mail_service_option'] = $properties['mailServiceOption'];
        } else {
            $resourceArray['mail_service_option'] = 'notls';
        }
        // Soft Bounces handling
        if (array_key_exists('mailSoftBouncedMessageAction', $properties)) {
            $resourceArray['mail_softbounced_message_action'] = $properties['mailSoftBouncedMessageAction'];
        } else {
            $resourceArray['mail_softbounced_message_action'] = 'delete';
        }
        if (array_key_exists('mailSoftMailbox', $properties)) {
            $resourceArray['mail_soft_mailbox'] = $properties['mailSoftMailbox'];
        } else {
            $resourceArray['mail_soft_mailbox'] = 'INBOX.Softbounces';
        }
        if (array_key_exists('mailMaxSoftBounces', $properties)) {
            $resourceArray['mail_max_softbounces'] = $properties['mailMaxSoftBounces'];
        } else {
            $resourceArray['mail_max_softbounces'] = 3;
        }
        if (array_key_exists('mailMaxSoftBouncesAction', $properties)) {
            $resourceArray['mail_max_softbounces_action'] = $properties['mailMaxSoftBouncesAction'];
        } else {
            $resourceArray['mail_max_softbounces_action'] = 'disable';
        }
        // Hard bounces handling
        if (array_key_exists('mailHardBouncedMessageAction', $properties)) {
            $resourceArray['mail_hardbounced_message_action'] = $properties['mailHardBouncedMessageAction'];
        } else {
            $resourceArray['mail_hardbounced_message_action'] = 'delete';
        }
        if (array_key_exists('mailHardMailbox', $properties)) {
            $resourceArray['mail_hard_mailbox'] = $properties['mailHardMailbox'];
        } else {
            $resourceArray['mail_hard_mailbox'] = 'INBOX.Hardbounces';
        }
        if (array_key_exists('mailMaxHardBounces', $properties)) {
            $resourceArray['mail_max_hardbounces'] = $properties['mailMaxHardBounces'];
        } else {
            $resourceArray['mail_max_hardbounces'] = 1;
        }
        if (array_key_exists('mailMaxHardBouncesAction', $properties)) {
            $resourceArray['mail_max_hardbounces_action'] = $properties['mailMaxHardBouncesAction'];
        } else {
            $resourceArray['mail_max_hardbounces_action'] = 'delete';
        }
        // Unclassified handling
        if (array_key_exists('mailNotClassifiedMessageAction', $properties)) {
            $resourceArray['mail_notclassified_message_action'] = $properties['mailNotClassifiedMessageAction'];
        } else {
            $resourceArray['mail_notclassified_message_action'] = 'move';
        }
        if (array_key_exists('mailNotClassifiedMailbox', $properties)) {
            $resourceArray['mail_notclassified_mailbox'] = $properties['mailNotClassifiedMailbox'];
        } else {
            $resourceArray['mail_notclassified_mailbox'] = 'INBOX.NotClassified';
        }
        // Content Collection handling
        if (array_key_exists('collection1Name', $properties)) {
            $resourceArray['collection1_name'] = $properties['collection1Name'];
        } else {
            $resourceArray['collection1_name'] = '';
        }
        if (array_key_exists('collection1Parents', $properties)) {
            $resourceArray['collection1_parents'] = $properties['collection1Parents'];
        } else {
            $resourceArray['collection1_parents'] = '';
        }
        if (array_key_exists('collection2Name', $properties)) {
            $resourceArray['collection2_name'] = $properties['collection2Name'];
        } else {
            $resourceArray['collection2_name'] = '';
        }
        if (array_key_exists('collection2Parents', $properties)) {
            $resourceArray['collection2_parents'] = $properties['collection2Parents'];
        } else {
            $resourceArray['collection2_parents'] = '';
        }
        if (array_key_exists('collection3Name', $properties)) {
            $resourceArray['collection3_name'] = $properties['collection3Name'];
        } else {
            $resourceArray['collection3_name'] = '';
        }
        if (array_key_exists('collection3Parents', $properties)) {
            $resourceArray['collection3_parents'] = $properties['collection3Parents'];
        } else {
            $resourceArray['collection3_parents'] = '';
        }

        return $resourceArray;
    }
}
