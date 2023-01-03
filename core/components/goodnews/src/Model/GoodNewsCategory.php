<?php
namespace Bitego\GoodNews\Model;

use xPDO\xPDO;

/**
 * Class GoodNewsCategory
 *
 * @property integer $goodnewsgroup_id
 * @property string $name
 * @property string $description
 * @property boolean $public
 * @property string $createdon
 * @property integer $createdby
 * @property string $editedon
 * @property integer $editedby
 *
 * @property \Bitego\GoodNews\Model\GoodNewsCategoryMember[] $CategoryMember
 *
 * @package Bitego\GoodNews\Model
 */
class GoodNewsCategory extends \xPDO\Om\xPDOSimpleObject
{
}
