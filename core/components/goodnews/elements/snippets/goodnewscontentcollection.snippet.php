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
 * Snippet to get a list of collected resource documents for inserting in newsletter content.
 *
 * @var modX $modx
 *
 * @property string $collectionId Internal name of the content collection (collection1, collection2 or collection3). (default: 'collection1')
 * @property string $tpl Name of a Chunk serving as template for a Resource row. NOTE: if not provided, properties are dumped to output for each resource. (default: 'sample.GoodNewsContentCollectionRowTpl')
 * @property string $tplWrapper Name of a Chunk serving as wrapper template for the Snippet output. (default: '')
 * @property string $sortby A field name to sort by or JSON object of field names and sortdir for each field, e.g. {"publishedon":"ASC","createdon":"DESC"}. (default: 'publishedon')
 * @property string $sortdir Order which to sort by. (default: 'DESC')
 * @property string $includeContent Indicates if the content of each resource should be returned in the results. (default: 'false')
 * @property string $outputSeparator Separator for the output of row chunks. (default: '')
 * @property string $toPlaceholder If set, will assign the result to this placeholder instead of outputting it directly. (default: '')
 * @property string $debug If true, will send the SQL query to the MODX log. (default: 'false')
 *
 * @package goodnews
 */

$corePath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/');
$goodnews = $modx->getService('goodnews','GoodNews', $corePath.'model/goodnews/', $scriptProperties);
if (!($goodnews instanceof GoodNews)) return '';

$output = array();

// Default properties
$collectionId    = !empty($collectionId) ? $collectionId : 'collection1';
$tpl             = !empty($tpl) ? $tpl : 'sample.GoodNewsContentCollectionRowTpl';
$tplWrapper      = !empty($tplWrapper) ? $tpl : '';
$sortby          = isset($sortby) ? $sortby : 'publishedon';
$sortdir         = isset($sortdir) ? $sortdir : 'DESC';
$includeContent  = !empty($includeContent) ? true : false;
$outputSeparator = isset($outputSeparator) ? $outputSeparator : "\n";
$toPlaceholder   = !empty($toPlaceholder) ? $toPlaceholder : '';
$debug           = !empty($debug) ? true : false;

$meta = $modx->getObject('GoodNewsMailingMeta', array('mailing_id' => $modx->resource->get('id')));
if (!is_object($meta)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] ContentCollection snippet - could not read meta data for mailing resource.');
    return 'Could not read meta data for this mailing resource.';
}

$collections = unserialize($meta->get('collections'));
if (!is_array($collections)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] ContentCollection snippet - could no read collections array for mailing resource.');
    return 'Could not read collections array for this mailing resource.';
}

$collection = $collections[$collectionId];
if (empty($collection)) {
    $modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] ContentCollection snippet - '.$collectionId.' is empty.');
    return $collectionId.' is empty.';
}

// Query db
$query = $modx->newQuery('modResource');
$fields = array_keys($modx->getFields('modResource'));
if (!$includeContent) { $fields = array_diff($fields, array('content')); } 
$query->select($modx->getSelectColumns('modResource', 'modResource', '', $fields));

$query->where(array('id:IN' => $collection));

if (!empty($sortby)) {
    $sorts = array($sortby => $sortdir);
    if (is_array($sorts)) {
        while (list($sort, $dir) = each($sorts)) {
            $query->sortby($sort, $dir);
        }
    }
}
if ($debug) {
    $query->prepare();
    $modx->log(modX::LOG_LEVEL_ERROR, $query->toSQL());
}

$resources = $modx->getCollection('modResource', $query);
foreach ($resources as $resource) {

    $properties = array_merge(
        $scriptProperties,
        $resource->get($fields),
        array('url' => $modx->makeUrl($resource->get('id'), '', '', 'full'))
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

if (!empty($tplWrapper) && !empty($output)) {
    $output = parseTpl($tplWrapper, array('output' => $output));
}

$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}
return $output;
