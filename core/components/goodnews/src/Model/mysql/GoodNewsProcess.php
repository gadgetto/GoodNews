<?php
namespace Bitego\GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsProcess extends \Bitego\GoodNews\Model\GoodNewsProcess
{

    public static $metaMap = array (
        'package' => 'Bitego\\GoodNews\\Model\\',
        'version' => '3.0',
        'table' => 'goodnews_processes',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'pid' => '',
            'starttime' => '',
        ),
        'fieldMeta' => 
        array (
            'pid' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'starttime' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
        ),
    );

}
