<?php
/**
 * @package goodnews
 */
$xpdo_meta_map['GoodNewsCategoryMember']= array (
  'package' => 'goodnews',
  'version' => NULL,
  'table' => 'goodnews_category_members',
  'extends' => 'xPDOSimpleObject',
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
      'class' => 'GoodNewsCategory',
      'local' => 'goodnewscategory_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'User' => 
    array (
      'class' => 'modUser',
      'local' => 'member_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
