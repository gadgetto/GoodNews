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
 * Properties for the GoodNewsGetNewsletters snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = [
    [
        'name' => 'parent',
        'desc' => 'prop_goodnewsgetnewsletters.parent_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'tpl',
        'desc' => 'prop_goodnewsgetnewsletters.tpl_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'sortby',
        'desc' => 'prop_goodnewsgetnewsletters.sortby_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'publishedon',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'sortdir',
        'desc' => 'prop_goodnewsgetnewsletters.sortdir_desc',
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
        'desc' => 'prop_goodnewsgetnewsletters.includecontent_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'limit',
        'desc' => 'prop_goodnewsgetnewsletters.limit_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '0',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'offset',
        'desc' => 'prop_goodnewsgetnewsletters.offset_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '0',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'totalVar',
        'desc' => 'prop_goodnewsgetnewsletters.totalvar_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'total',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'outputSeparator',
        'desc' => 'prop_goodnewsgetnewsletters.outputseparator_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '\n',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'toPlaceholder',
        'desc' => 'prop_goodnewsgetnewsletters.toplaceholder_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name' => 'debug',
        'desc' => 'prop_goodnewsgetnewsletters.debug_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false,
        'lexicon' => 'goodnews:properties',
    ],
];

return $properties;
