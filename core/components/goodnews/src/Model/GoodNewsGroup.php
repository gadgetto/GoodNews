<?php
namespace GoodNews\Model;

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
 * @property \GoodNews\Model\GoodNewsCategory[] $GoodNewsCategory
 * @property \GoodNews\Model\GoodNewsGroupMember[] $GroupMember
 *
 * @package GoodNews\Model
 */
class GoodNewsGroup extends \xPDO\Om\xPDOSimpleObject
{
}
