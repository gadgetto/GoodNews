<?php
namespace GoodNews\Model\mysql;

use xPDO\xPDO;

class GoodNewsResourceMailing extends \GoodNews\Model\GoodNewsResourceMailing
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
            'MailingMeta' => 
            array (
                'class' => 'GoodNews\\Model\\GoodNewsMailingMeta',
                'local' => 'id',
                'foreign' => 'mailing_id',
                'cardinality' => 'one',
                'owner' => 'local',
            ),
        ),
        'aggregates' => 
        array (
            'ResourceContainer' => 
            array (
                'class' => 'GoodNews\\Model\\GoodNewsResourceContainer',
                'local' => 'parent',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
