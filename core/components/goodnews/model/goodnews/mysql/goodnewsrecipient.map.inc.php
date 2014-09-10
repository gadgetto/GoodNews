<?php
/**
 * @package goodnews
 */
$xpdo_meta_map['GoodNewsRecipient']= array (
  'package' => 'goodnews',
  'version' => NULL,
  'table' => 'goodnews_recipients',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'mailing_id' => 0,
    'recipient_id' => 0,
    'statustime' => 0,
    'status' => 0,
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
      'default' => 0,
      'index' => 'index',
    ),
    'recipient_id' => 
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
    'recipient_id' => 
    array (
      'alias' => 'recipient_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'recipient_id' => 
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
    'MailingMeta' => 
    array (
      'class' => 'GoodNewsMailingMeta',
      'local' => 'mailing_id',
      'foreign' => 'mailing_id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
