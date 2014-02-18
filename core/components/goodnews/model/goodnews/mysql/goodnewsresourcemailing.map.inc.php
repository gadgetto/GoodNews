<?php
/**
 * @package goodnews
 */
$xpdo_meta_map['GoodNewsResourceMailing']= array (
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
    'MailingMeta' => 
    array (
      'class' => 'GoodNewsMailingMeta',
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
      'class' => 'GoodNewsResourceContainer',
      'local' => 'parent',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
