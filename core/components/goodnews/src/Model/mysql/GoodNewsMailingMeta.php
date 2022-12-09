<?php
namespace GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsMailingMeta extends \GoodNews\Model\GoodNewsMailingMeta
{

    public static $metaMap = array (
        'package' => 'GoodNews\\Model\\',
        'version' => '3.0',
        'table' => 'goodnews_mailing_meta',
        'extends' => 'xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'mailing_id' => NULL,
            'groups' => '',
            'categories' => '',
            'collections' => '',
            'recipients_list' => '',
            'recipients_total' => 0,
            'recipients_sent' => 0,
            'recipients_error' => 0,
            'senton' => 0,
            'sentby' => 0,
            'finishedon' => 0,
            'ipc_status' => 0,
            'scheduled' => 0,
            'soft_bounces' => 0,
            'hard_bounces' => 0,
        ),
        'fieldMeta' => 
        array (
            'mailing_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'index' => 'unique',
            ),
            'groups' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'categories' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'collections' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'recipients_list' => 
            array (
                'dbtype' => 'mediumtext',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'recipients_total' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'recipients_sent' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'recipients_error' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'senton' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'timestamp',
                'null' => false,
                'default' => 0,
            ),
            'sentby' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'finishedon' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'timestamp',
                'null' => false,
                'default' => 0,
            ),
            'ipc_status' => 
            array (
                'dbtype' => 'int',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'scheduled' => 
            array (
                'dbtype' => 'int',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
            ),
            'soft_bounces' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'hard_bounces' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
        ),
        'indexes' => 
        array (
            'mailing_id' => 
            array (
                'alias' => 'mailing_id',
                'primary' => false,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'mailing_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'composites' => 
        array (
            'Recipient' => 
            array (
                'class' => 'GoodNewsRecipient',
                'local' => 'mailing_id',
                'foreign' => 'mailing_id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
        'aggregates' => 
        array (
            'SentBy' => 
            array (
                'class' => 'modUser',
                'local' => 'sentby',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'ResourceMailing' => 
            array (
                'class' => 'GoodNewsResourceMailing',
                'local' => 'mailing_id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
