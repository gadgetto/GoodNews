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
use MODX\Revolution\modUser;
use MODX\Revolution\modChunk;
use Bitego\GoodNews\Model\GoodNewsMailingMeta;

/**
 * GoodNewsGetNewsletters
 *
 * Snippet to get a list of newsletters from the actual or specified container.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var GoodNews $goodnews
 * @var modChunk $chunk
 *
 * PROPERTIES
 *
 * @property string  &parent The container to get resources from.
 *                   (default: ID of current Resource)
 * @property string  &tpl Name of a Chunk serving as template for a Resource row. NOTE: if not provided, properties are
 *                   dumped to output for each resource. (default: sample.GoodNewsNewsletterRowChunk)
 * @property string  &sortby A field name to sort by or JSON object of field names and sortdir for each field,
 *                   e.g. {"publishedon":"ASC","createdon":"DESC"}. (default: publishedon)
 * @property string  &sortdir Order which to sort by.
 *                   (default: DESC)
 * @property string  &includeContent Indicates if the content of each resource should be returned in the results.
 *                   (default: 0)
 * @propetry string  &limit The limit for pagination purposes.
 *                   (default: 10)
 * @property string  &offset The offset for pagination purposes.
 *                   (default: 0)
 * @property string  &totalVar The name of the placeholder which holds total Resource count.
 *                   (default: total)
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
$parent          = !empty($parent) ? (int)$parent : (int)$modx->resource->get('id');
$tpl             = !empty($tpl) ? $tpl : 'sample.GoodNewsNewsletterRowChunk';
$sortby          = isset($sortby) ? $sortby : 'publishedon';
$sortdir         = isset($sortdir) ? $sortdir : 'DESC';
$includeContent  = !empty($includeContent) ? true : false;
$limit           = isset($limit) ? (int)$limit : 10;
$offset          = isset($offset) ? (int)$offset : 0;
$totalVar        = !empty($totalVar) ? $totalVar : 'total';
$outputSeparator = isset($outputSeparator) ? $outputSeparator : "\n";
$toPlaceholder   = !empty($toPlaceholder) ? $toPlaceholder : '';
$debug           = !empty($debug) ? true : false;

// Check for valid parent
if (!$goodnews->isGoodNewsContainer($parent)) {
    $modx->log(
        modX::LOG_LEVEL_INFO,
        '[GoodNews] GoodNewsGetNewsletters - The provided container [id: ' . $parent .
        '] is not a valid GoodNews container.'
    );
    return 'The provided container [id: ' . $parent . '] is not a valid GoodNews container.';
}

// Query db
$query = $modx->newQuery(modResource::class);
$resourceColumns = [
    'id',
    'pagetitle',
    'introtext',
    'createdon',
    'publishedon',
    'content'
];
if (!$includeContent) {
    $resourceColumns = array_diff($resourceColumns, ['content']);
}
$query->select($modx->getSelectColumns(modResource::class, 'modResource', '', $resourceColumns));
$query->leftJoin(GoodNewsMailingMeta::class, 'MailingMeta', 'MailingMeta.mailing_id = modResource.id');
$metaColumns = [
    'id',
    'senton',
    'sentby',
    'finishedon'
];
$query->select($modx->getSelectColumns(GoodNewsMailingMeta::class, 'MailingMeta', 'meta_', $metaColumns));
$query->leftJoin(modUser::class, 'SentBy', 'SentBy.id = MailingMeta.sentby');
$userColumns = [
    'id',
    'username'
];
$query->select($modx->getSelectColumns(modUser::class, 'SentBy', 'sentby_', $userColumns));
$query->where([
    'modResource.class_key' => 'Bitego\\GoodNews\\Model\\GoodNewsResourceMailing',
    'modResource.parent' => $parent,
    'modResource.published' => 1,
    'modResource.deleted' => 0,
    'MailingMeta.finishedon:>' => 0,
]);
$total = $modx->getCount(modResource::class, $query);
$modx->setPlaceholder($totalVar, $total);
if (!empty($sortby)) {
    $sorts = [$sortby => $sortdir];
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

$collection = $modx->getCollection(modResource::class, $query);
foreach ($collection as $mailingId => $mailing) {
    $properties = array_merge(
        $scriptProperties,
        $mailing->get($resourceColumns),
        $mailing->get(['meta_senton', 'meta_finishedon']),
        $mailing->get(['sentby_username']),
        ['url' => $modx->makeUrl($mailing->get('id'), '', '', $modx->getOption('link_tag_scheme'))]
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

$output = implode($outputSeparator, $output);

$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}
return $output;
