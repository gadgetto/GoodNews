<?php
namespace GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsGroup extends \GoodNews\Model\GoodNewsGroup
{

    public static $metaMap = array (
        'package' => 'GoodNews\\Model\\',
        'version' => '3.0',
        'table' => 'goodnews_groups',
        'extends' => 'xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'name' => '',
            'description' => '',
            'public' => 1,
            'modxusergroup' => 0,
            'createdon' => NULL,
            'createdby' => 0,
            'editedon' => NULL,
            'editedby' => 0,
        ),
        'fieldMeta' => 
        array (
            'name' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'description' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'public' => 
            array (
                'dbtype' => 'int',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 1,
            ),
            'modxusergroup' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'createdon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => true,
            ),
            'createdby' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'editedon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => true,
            ),
            'editedby' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
        ),
        'composites' => 
        array (
            'GoodNewsCategory' => 
            array (
                'class' => 'GoodNewsCategory',
                'local' => 'id',
                'foreign' => 'goodnewsgroup_id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'GroupMember' => 
            array (
                'class' => 'GoodNewsGroupMember',
                'local' => 'id',
                'foreign' => 'goodnewsgroup_id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
        'aggregates' => 
        array (
            'CreatedBy' => 
            array (
                'class' => 'modUser',
                'local' => 'createdby',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'EditedBy' => 
            array (
                'class' => 'modUser',
                'local' => 'editedby',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'UserGroup' => 
            array (
                'class' => 'modUserGroup',
                'local' => 'modxusergroup',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
