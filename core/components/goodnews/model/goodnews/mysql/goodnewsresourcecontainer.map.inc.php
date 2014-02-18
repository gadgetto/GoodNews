<?php
/**
 * @package goodnews
 */
$xpdo_meta_map['GoodNewsResourceContainer']= array (
  'package' => 'goodnews',
  'version' => NULL,
  'table' => 'site_content',
  'extends' => 'modResource',
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
      'class' => 'GoodNewsResourceMailing',
      'local' => 'id',
      'foreign' => 'parent',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
