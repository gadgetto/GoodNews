<?php
namespace GoodNews\Model;

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
 * @property \GoodNews\Model\GoodNewsCategoryMember[] $CategoryMember
 *
 * @package GoodNews\Model
 */
class GoodNewsCategory extends \xPDO\Om\xPDOSimpleObject
{
}
