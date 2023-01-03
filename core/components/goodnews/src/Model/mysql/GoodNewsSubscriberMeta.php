<?php
namespace Bitego\GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsSubscriberMeta extends \Bitego\GoodNews\Model\GoodNewsSubscriberMeta
{

    public static $metaMap = array (
        'package' => 'Bitego\\GoodNews\\Model\\',
        'version' => '3.0',
        'table' => 'goodnews_subscriber_meta',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'subscriber_id' => NULL,
            'sid' => '',
            'subscribedon' => 0,
            'activatedon' => 0,
            'ip' => '0',
            'ip_activated' => '0',
            'testdummy' => 0,
            'soft_bounces' => '',
            'hard_bounces' => '',
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
                'index' => 'unique',
            ),
            'sid' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'subscribedon' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'timestamp',
                'null' => false,
                'default' => 0,
            ),
            'activatedon' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'timestamp',
                'null' => false,
                'default' => 0,
            ),
            'ip' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '50',
                'phptype' => 'string',
                'null' => false,
                'default' => '0',
            ),
            'ip_activated' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '50',
                'phptype' => 'string',
                'null' => false,
                'default' => '0',
            ),
            'testdummy' => 
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
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'hard_bounces' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
        ),
        'indexes' => 
        array (
            'subscriber_id' => 
            array (
                'alias' => 'subscriber_id',
                'primary' => false,
                'unique' => true,
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
        ),
        'composites' => 
        array (
            'SubscriberLog' => 
            array (
                'class' => 'Bitego\\GoodNews\\Model\\GoodNewsSubscriberLog',
                'local' => 'subscriber_id',
                'foreign' => 'subscriber_id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
        'aggregates' => 
        array (
            'User' => 
            array (
                'class' => 'MODX\\Revolution\\modUser',
                'local' => 'subscriber_id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
