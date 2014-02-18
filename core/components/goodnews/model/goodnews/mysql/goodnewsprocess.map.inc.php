<?php
/**
 * @package goodnews
 */
$xpdo_meta_map['GoodNewsProcess']= array (
  'package' => 'goodnews',
  'version' => NULL,
  'table' => 'goodnews_processes',
  'extends' => 'xPDOSimpleObject',
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
