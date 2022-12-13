<?php
namespace Bitego\GoodNews\Model;

use xPDO\xPDO;

/**
 * Class GoodNewsMailingMeta
 *
 * @property integer $mailing_id
 * @property string $groups
 * @property string $categories
 * @property string $collections
 * @property string $recipients_list
 * @property integer $recipients_total
 * @property integer $recipients_sent
 * @property integer $recipients_error
 * @property string $senton
 * @property integer $sentby
 * @property string $finishedon
 * @property integer $ipc_status
 * @property boolean $scheduled
 * @property integer $soft_bounces
 * @property integer $hard_bounces
 *
 * @property \Bitego\GoodNews\Model\GoodNewsRecipient[] $Recipient
 *
 * @package Bitego\GoodNews\Model
 */
class GoodNewsMailingMeta extends \xPDO\Om\xPDOSimpleObject
{
}
