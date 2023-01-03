<?php
namespace Bitego\GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsResourceContainer extends \Bitego\GoodNews\Model\GoodNewsResourceContainer
{

    public static $metaMap = array (
        'package' => 'Bitego\\GoodNews\\Model\\',
        'version' => '3.0',
        'table' => 'site_content',
        'extends' => 'MODX\\Revolution\\modResource',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
        ),
        'fieldMeta' => 
        array (
        ),
        'composites' => 
        array (
            'ResourceMailing' => 
            array (
                'class' => 'Bitego\\GoodNews\\Model\\GoodNewsResourceMailing',
                'local' => 'id',
                'foreign' => 'parent',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
    );

}
