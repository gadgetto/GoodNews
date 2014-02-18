<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * Snippet to get a list of newsletters of the actual or specified container.
 *
 * @var modX $modx
 *
 * @package goodnews
 */

$corePath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/');
$goodnews = $modx->getService('goodnews','GoodNews', $corePath.'model/goodnews/', $scriptProperties);
if (!($goodnews instanceof GoodNews)) return '';

$output = array();

// Default properties
$parent          = !empty($parent) ? $parent : $modx->resource->get('id');
$tpl             = !empty($tpl) ? $tpl : 'sample.GoodNewsNewsletterRowTpl';
$sortby          = isset($sortby) ? $sortby : 'publishedon';
$sortdir         = isset($sortdir) ? $sortdir : 'DESC';
$includeContent  = !empty($includeContent) ? true : false;
$limit           = isset($limit) ? (integer)$limit : 10;
$offset          = isset($offset) ? (integer)$offset : 0;
$totalVar        = !empty($totalVar) ? $totalVar : 'total';
$outputSeparator = isset($outputSeparator) ? $outputSeparator : "\n";
$toPlaceholder   = !empty($toPlaceholder) ? $toPlaceholder : '';
$debug           = !empty($debug) ? true : false;

// Check for valid parent
$parent = (integer)$parent;
if (!$goodnews->isGoodNewsContainer($parent)) {
    $modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] GoodNewsGetNewsletters - The provided container [id: '.$parent.'] is not a valid GoodNews container.');
    return '';
}

// Query db
$query = $modx->newQuery('modResource');
$resourceColumns = array('id','pagetitle','introtext','createdon','publishedon','content');
if (!$includeContent) { $resourceColumns = array_diff($resourceColumns, array('content')); }
$query->select($modx->getSelectColumns('modResource', 'modResource', '', $resourceColumns));

$query->leftJoin('GoodNewsMailingMeta', 'MailingMeta', 'MailingMeta.mailing_id = modResource.id');
$metaColumns = array('id','senton','sentby','finishedon');
$query->select($modx->getSelectColumns('GoodNewsMailingMeta', 'MailingMeta', 'meta_', $metaColumns));

$query->leftJoin('modUser', 'SentBy', 'SentBy.id = MailingMeta.sentby');
$userColumns = array('id','username');
$query->select($modx->getSelectColumns('modUser', 'SentBy', 'sentby_', $userColumns));

$query->where(array(
    'modResource.class_key' => 'GoodNewsResourceMailing',
    'modResource.parent' => $parent,
    'modResource.published' => 1,
    'modResource.deleted' => 0,
    'MailingMeta.finishedon:>' => 0,
));

$total = $modx->getCount('modResource', $query);
$modx->setPlaceholder($totalVar, $total);

if (!empty($sortby)) {
    $sorts = array($sortby => $sortdir);
    if (is_array($sorts)) {
        while (list($sort, $dir) = each($sorts)) {
            $query->sortby($sort, $dir);
        }
    }
}
if (!empty($limit)) {
    $query->limit($limit, $offset);
}
if ($debug) {
    $query->prepare();
    $modx->log(modX::LOG_LEVEL_ERROR, $query->toSQL());
}

$collection = $modx->getCollection('modResource', $query);

foreach ($collection as $mailingId => $mailing) {

    $properties = array_merge(
        $scriptProperties,
        $mailing->get($resourceColumns),
        $mailing->get(array('meta_senton','meta_finishedon')),
        $mailing->get(array('sentby_username')),
        array('url' => $modx->makeUrl($mailing->get('id')))
    );
    
    $resourceTpl = '';
    if (!empty($tpl)) {
        $resourceTpl = $goodnews->parseTpl($tpl, $properties);
    }
    
    if (empty($resourceTpl)) {
        $chunk = $modx->newObject('modChunk');
        $chunk->setCacheable(false);
        $output[] = $chunk->process(array(), '<pre>'.print_r($properties, true).'</pre>');
    } else {
        $output[] = $resourceTpl;
    }

}

$output = implode($outputSeparator, $output);

$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}
return $output;
