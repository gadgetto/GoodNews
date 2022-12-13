<?php
namespace Bitego\GoodNews\Model;

use xPDO\xPDO;

/**
 * Class GoodNewsGroup
 *
 * @property string $name
 * @property string $description
 * @property boolean $public
 * @property integer $modxusergroup
 * @property string $createdon
 * @property integer $createdby
 * @property string $editedon
 * @property integer $editedby
 *
 * @property \Bitego\GoodNews\Model\GoodNewsCategory[] $GoodNewsCategory
 * @property \Bitego\GoodNews\Model\GoodNewsGroupMember[] $GroupMember
 *
 * @package Bitego\GoodNews\Model
 */
class GoodNewsGroup extends \xPDO\Om\xPDOSimpleObject
{
}
