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
 * Properties for the GoodNewsContentCollection snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = array(
    array(
        'name' => 'collectionId',
        'desc' => 'prop_goodnewscontentcollection.collectionid_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'collection1',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'tpl',
        'desc' => 'prop_goodnewscontentcollection.tpl_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'sample.GoodNewsContentCollectionRowTpl',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'tplWrapper',
        'desc' => 'prop_goodnewscontentcollection.tplwrapper_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'sortby',
        'desc' => 'prop_goodnewscontentcollection.sortby_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'publishedon',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'sortdir',
        'desc' => 'prop_goodnewscontentcollection.sortdir_desc',
        'type' => 'list',
        'options' => array(
            array('name' => 'opt_goodnews.asc','value'  => 'ASC'),
            array('name' => 'opt_goodnews.desc','value' => 'DESC'),
        ),
        'value' => 'DESC',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'includeContent',
        'desc' => 'prop_goodnewscontentcollection.includecontent_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'toPlaceholder',
        'desc' => 'prop_goodnewscontentcollection.toplaceholder_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'debug',
        'desc' => 'prop_goodnewscontentcollection.debug_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false,
        'lexicon' => 'goodnews:properties',
    ),
);

return $properties;
