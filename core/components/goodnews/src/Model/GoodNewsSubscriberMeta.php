<?php
namespace Bitego\GoodNews\Model;

use xPDO\xPDO;

/**
 * Class GoodNewsSubscriberMeta
 *
 * @property integer $subscriber_id
 * @property string $sid
 * @property string $subscribedon
 * @property string $activatedon
 * @property string $ip
 * @property string $ip_activated
 * @property boolean $testdummy
 * @property string $soft_bounces
 * @property string $hard_bounces
 *
 * @property \Bitego\GoodNews\Model\GoodNewsSubscriberLog[] $SubscriberLog
 *
 * @package Bitego\GoodNews\Model
 */
class GoodNewsSubscriberMeta extends \xPDO\Om\xPDOSimpleObject
{
}
