<?php
namespace Bitego\GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsGroupMember extends \Bitego\GoodNews\Model\GoodNewsGroupMember
{

    public static $metaMap = array (
        'package' => 'Bitego\\GoodNews\\Model\\',
        'version' => '3.0',
        'table' => 'goodnews_group_members',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'goodnewsgroup_id' => 0,
            'member_id' => 0,
        ),
        'fieldMeta' => 
        array (
            'goodnewsgroup_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
            'member_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
        ),
        'indexes' => 
        array (
            'goodnewsgroup_id' => 
            array (
                'alias' => 'goodnewsgroup_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'goodnewsgroup_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'member_id' => 
            array (
                'alias' => 'member_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'member_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'aggregates' => 
        array (
            'GoodNewsGroup' => 
            array (
                'class' => 'Bitego\\GoodNews\\Model\\GoodNewsGroup',
                'local' => 'goodnewsgroup_id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'User' => 
            array (
                'class' => 'MODX\\Revolution\\modUser',
                'local' => 'member_id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
