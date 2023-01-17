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

/**
 * Properties for the GoodNewsContentCollection snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = [
    [
        'name' => 'collectionId',
        'desc' => 'prop_goodnewscontentcollection.collectionid_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'collection1',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'tpl',
        'desc' => 'prop_goodnewscontentcollection.tpl_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'sample.GoodNewsContentCollectionRowChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'tplWrapper',
        'desc' => 'prop_goodnewscontentcollection.tplwrapper_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'sortby',
        'desc' => 'prop_goodnewscontentcollection.sortby_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'publishedon',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'sortdir',
        'desc' => 'prop_goodnewscontentcollection.sortdir_desc',
        'type' => 'list',
        'options' => [
            ['name' => 'opt_goodnews.asc','value'  => 'ASC'],
            ['name' => 'opt_goodnews.desc','value' => 'DESC'],
        ],
        'value' => 'DESC',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'includeContent',
        'desc' => 'prop_goodnewscontentcollection.includecontent_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'toPlaceholder',
        'desc' => 'prop_goodnewscontentcollection.toplaceholder_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'debug',
        'desc' => 'prop_goodnewscontentcollection.debug_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false,
        'lexicon' => 'goodnews:properties',
    ],
];

return $properties;
