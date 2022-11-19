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
 * Properties for the GoodNewsGetNewsletters snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = array(
    array(
        'name' => 'parent',
        'desc' => 'prop_goodnewsgetnewsletters.parent_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'tpl',
        'desc' => 'prop_goodnewsgetnewsletters.tpl_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'sortby',
        'desc' => 'prop_goodnewsgetnewsletters.sortby_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'publishedon',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'sortdir',
        'desc' => 'prop_goodnewsgetnewsletters.sortdir_desc',
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
        'desc' => 'prop_goodnewsgetnewsletters.includecontent_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'limit',
        'desc' => 'prop_goodnewsgetnewsletters.limit_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '0',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'offset',
        'desc' => 'prop_goodnewsgetnewsletters.offset_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '0',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'totalVar',
        'desc' => 'prop_goodnewsgetnewsletters.totalvar_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'total',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'outputSeparator',
        'desc' => 'prop_goodnewsgetnewsletters.outputseparator_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '\n',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'toPlaceholder',
        'desc' => 'prop_goodnewsgetnewsletters.toplaceholder_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name' => 'debug',
        'desc' => 'prop_goodnewsgetnewsletters.debug_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false,
        'lexicon' => 'goodnews:properties',
    ),
);

return $properties;
