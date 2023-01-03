<?php
namespace Bitego\GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsSubscriberLog extends \Bitego\GoodNews\Model\GoodNewsSubscriberLog
{

    public static $metaMap = array (
        'package' => 'Bitego\\GoodNews\\Model\\',
        'version' => '3.0',
        'table' => 'goodnews_subscriber_log',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'subscriber_id' => 0,
            'mailing_id' => 0,
            'statustime' => 0,
            'status' => 0,
        ),
        'fieldMeta' => 
        array (
            'subscriber_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
            'mailing_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
            'statustime' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'timestamp',
                'null' => false,
                'default' => 0,
            ),
            'status' => 
            array (
                'dbtype' => 'int',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
        ),
        'indexes' => 
        array (
            'subscriber_id' => 
            array (
                'alias' => 'subscriber_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'subscriber_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'mailing_id' => 
            array (
                'alias' => 'mailing_id',
                'primary' => false,
                'unique' => false,
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
        'aggregates' => 
        array (
            'SubscriberMeta' => 
            array (
                'class' => 'Bitego\\GoodNews\\Model\\GoodNewsSubscriberMeta',
                'local' => 'subscriber_id',
                'foreign' => 'subscriber_id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
