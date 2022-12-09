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
 * Properties for the GoodNewsUpdateProfile snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = array(
    array(
        'name'    => 'errTpl',
        'desc'    => 'prop_goodnewsupdateprofile.errtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '<span class="error">[[+error]]</span>',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'useExtended',
        'desc'    => 'prop_goodnewsupdateprofile.useextended_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'excludeExtended',
        'desc'    => 'prop_goodnewsupdateprofile.excludeextended_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'emailField',
        'desc'    => 'prop_goodnewsupdateprofile.emailfield_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'email',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'preHooks',
        'desc'    => 'prop_goodnewsupdateprofile.prehooks_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'postHooks',
        'desc'    => 'prop_goodnewsupdateprofile.posthooks_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'sendUnauthorizedPage',
        'desc'    => 'prop_goodnewsupdateprofile.sendunauthorizedpage_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'reloadOnSuccess',
        'desc'    => 'prop_goodnewsupdateprofile.reloadonsuccess_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => true,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'submitVar',
        'desc'    => 'prop_goodnewsupdateprofile.submitvar_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'goodnews-updateprofile-btn',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'successKey',
        'desc'    => 'prop_goodnewsupdateprofile.successkey_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'updsuccess',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'successMsg',
        'desc'    => 'prop_goodnewsupdateprofile.successmsg_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'validate',
        'desc'    => 'prop_goodnewsupdateprofile.validate_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'grpFieldsetTpl',
        'desc'    => 'prop_goodnewsupdateprofile.grpfieldsettpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsGrpFieldsetChunk',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'grpNameTpl',
        'desc'    => 'prop_goodnewsupdateprofile.grpnametpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsGrpNameChunk',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'grpFieldTpl',
        'desc'    => 'prop_goodnewsupdateprofile.grpfieldtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsGrpFieldChunk',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'grpFieldHiddenTpl',
        'desc'    => 'prop_goodnewsupdateprofile.grpfieldhiddentpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsGrpFieldHiddenChunk',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'catFieldTpl',
        'desc'    => 'prop_goodnewsupdateprofile.catfieldtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsCatFieldChunk',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'catFieldHiddenTpl',
        'desc'    => 'prop_goodnewsupdateprofile.catfieldhiddentpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsCatFieldHiddenChunk',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'groupsOnly',
        'desc'    => 'prop_goodnewsupdateprofile.groupsonly_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'includeGroups',
        'desc'    => 'prop_goodnewsupdateprofile.includegroups_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'defaultGroups',
        'desc'    => 'prop_goodnewsupdateprofile.defaultgroups_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'defaultCategories',
        'desc'    => 'prop_goodnewsupdateprofile.defaultcategories_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'sort',
        'desc'    => 'prop_goodnewsupdateprofile.sort_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'name',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'dir',
        'desc'    => 'prop_goodnewsupdateprofile.dir_desc',
        'type'    => 'list',
        'options' => array(
            array('name' => 'opt_goodnews.asc','value'  => 'ASC'),
            array('name' => 'opt_goodnews.desc','value' => 'DESC'),
        ),
        'value'   => 'ASC',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'grpCatPlaceholder',
        'desc'    => 'prop_goodnewsupdateprofile.grpcatplaceholder_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'grpcatfieldsets',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'placeholderPrefix',
        'desc'    => 'prop_goodnewsupdateprofile.placeholderprefix_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
);

return $properties;
