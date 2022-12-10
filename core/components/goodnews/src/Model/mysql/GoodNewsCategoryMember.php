<?php
namespace GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsCategoryMember extends \GoodNews\Model\GoodNewsCategoryMember
{

    public static $metaMap = array (
        'package' => 'GoodNews\\Model\\',
        'version' => '3.0',
        'table' => 'goodnews_category_members',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'goodnewscategory_id' => 0,
            'member_id' => 0,
        ),
        'fieldMeta' => 
        array (
            'goodnewscategory_id' => 
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
            'goodnewscategory_id' => 
            array (
                'alias' => 'goodnewscategory_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'goodnewscategory_id' => 
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
            'GoodNewsCategory' => 
            array (
                'class' => 'GoodNews\\Model\\GoodNewsCategory',
                'local' => 'goodnewscategory_id',
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
