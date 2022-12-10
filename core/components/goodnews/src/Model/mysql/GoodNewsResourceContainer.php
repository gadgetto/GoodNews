<?php
namespace GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsResourceContainer extends \GoodNews\Model\GoodNewsResourceContainer
{

    public static $metaMap = array (
        'package' => 'GoodNews\\Model\\',
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
                'class' => 'GoodNews\\Model\\GoodNewsResourceMailing',
                'local' => 'id',
                'foreign' => 'parent',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
    );

}
