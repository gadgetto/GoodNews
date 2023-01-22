<?php

/**
 * This file is part of the GoodNews package.
 *
 * @copyright bitego (Martin Gartner)
 * @license GNU General Public License v2.0 (and later)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use MODX\Revolution\modX;
use MODX\Revolution\modResource;
use MODX\Revolution\modChunk;
use Bitego\GoodNews\Model\GoodNewsMailingMeta;

/**
 * GoodNewsContentCollection
 *
 * Snippet to get a list of collected resource documents for inserting in newsletter content.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var GoodNews $goodnews
 * @var GoodNewsMailingMeta $meta
 * @var modChunk $chunk
 *
 * PROPERTIES
 *
 * @property string  &collectionId Internal name of the content collection (collection1, collection2 or collection3).
 *                   (default: 'collection1')
 * @property string  &tpl Name of a Chunk serving as template for a Resource row. NOTE: if not provided, properties are
 *                   dumped to output for each resource. (default: 'sample.GoodNewsContentCollectionRowChunk')
 * @property string  &tplWrapper Name of a Chunk serving as wrapper template for the Snippet output.
 *                   (default: '')
 * @property string  &sortby A field name to sort by or JSON object of field names and sortdir for each field,
 *                   e.g. {"publishedon":"DESC","pagetitle":"ASC"}. (default: 'publishedon')
 * @property string  &sortdir Order which to sort by.
 *                   (default: 'DESC')
 * @property string  &includeContent Indicates if the content of each resource should be returned in the results.
 *                   (default: 'false')
 * @property string  &outputSeparator Separator for the output of row chunks.
 *                   (default: '')
 * @property string  &toPlaceholder If set, will assign the result to this placeholder instead of outputting it.
 *                   (default: '')
 * @property boolean &debug If true, will send the SQL query to the MODX log.
 *                   (default: 0)
 *
 * @package goodnews
 * @subpackage snippets
 */

$goodnews = $modx->services->get('goodnews');

$output = [];

// Default properties
$collectionId    = !empty($collectionId) ? $collectionId : 'collection1';
$tpl             = !empty($tpl) ? $tpl : 'sample.GoodNewsContentCollectionRowChunk';
$tplWrapper      = !empty($tplWrapper) ? $tplWrapper : '';
$sortby          = isset($sortby) ? $sortby : 'publishedon';
$sortdir         = isset($sortdir) ? $sortdir : 'DESC';
$includeContent  = !empty($includeContent) ? true : false;
$outputSeparator = isset($outputSeparator) ? $outputSeparator : "\n";
$toPlaceholder   = !empty($toPlaceholder) ? $toPlaceholder : '';
$debug           = !empty($debug) ? true : false;


$meta = $modx->getObject(GoodNewsMailingMeta::class, ['mailing_id' => $modx->resource->get('id')]);
if (!is_object($meta)) {
    $modx->log(
        modX::LOG_LEVEL_ERROR,
        '[GoodNews] ContentCollection snippet - could not read meta data for mailing resource.'
    );
    return 'Could not read meta data for this mailing resource.';
}
$collections = unserialize($meta->get('collections'));
if (!is_array($collections)) {
    $modx->log(
        modX::LOG_LEVEL_ERROR,
        '[GoodNews] ContentCollection snippet - could no read collections array for mailing resource.'
    );
    return 'Could not read collections array for this mailing resource.';
}
$collection = $collections[$collectionId];
if (empty($collection)) {
    $modx->log(
        modX::LOG_LEVEL_INFO,
        '[GoodNews] ContentCollection snippet - ' . $collectionId . ' is empty.'
    );
    return '';
}

// Query db
$query = $modx->newQuery(modResource::class);
$fields = array_keys($modx->getFields(modResource::class));
if (!$includeContent) {
    $fields = array_diff($fields, ['content']);
}
$query->select($modx->getSelectColumns(modResource::class, 'modResource', '', $fields));
$query->where(['id:IN' => $collection]);

if (!empty($sortby) && is_string($sortby)) {
    if (is_array(json_decode($sortby, true)) && (json_last_error() == JSON_ERROR_NONE)) {
        $sorts = json_decode($sortby, true);
    } else {
        $sorts = [$sortby => $sortdir];
    }
    foreach ($sorts as $sort => $dir) {
        $query->sortby($sort, $dir);
    }
}

if ($debug) {
    $query->prepare();
    $modx->log(modX::LOG_LEVEL_ERROR, $query->toSQL());
}

$resources = $modx->getCollection(modResource::class, $query);
foreach ($resources as $resource) {
    $properties = array_merge(
        $scriptProperties,
        $resource->get($fields),
        ['url' => $modx->makeUrl($resource->get('id'), '', '', 'full')]
    );
    $resourceTpl = '';
    if (!empty($tpl)) {
        $resourceTpl = $goodnews->parseTpl($tpl, $properties);
    }
    if (empty($resourceTpl)) {
        $chunk = $modx->newObject(modChunk::class);
        $chunk->setCacheable(false);
        $output[] = $chunk->process([], '<pre>' . print_r($properties, true) . '</pre>');
    } else {
        $output[] = $resourceTpl;
    }
}
// Convert to HTML string
$output = implode($outputSeparator, $output);

if (!empty($tplWrapper) && !empty($output)) {
    $output = $goodnews->parseTpl($tplWrapper, ['output' => $output]);
}

$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}
return $output;
