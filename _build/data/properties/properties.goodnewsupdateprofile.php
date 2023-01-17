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
 * Properties for the GoodNewsUpdateProfile snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = [
    [
        'name'    => 'errTpl',
        'desc'    => 'prop_goodnewsupdateprofile.errtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '<span class="error">[[+error]]</span>',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'useExtended',
        'desc'    => 'prop_goodnewsupdateprofile.useextended_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'excludeExtended',
        'desc'    => 'prop_goodnewsupdateprofile.excludeextended_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'emailField',
        'desc'    => 'prop_goodnewsupdateprofile.emailfield_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'email',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'preHooks',
        'desc'    => 'prop_goodnewsupdateprofile.prehooks_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'postHooks',
        'desc'    => 'prop_goodnewsupdateprofile.posthooks_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'sendUnauthorizedPage',
        'desc'    => 'prop_goodnewsupdateprofile.sendunauthorizedpage_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'reloadOnSuccess',
        'desc'    => 'prop_goodnewsupdateprofile.reloadonsuccess_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => true,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'submitVar',
        'desc'    => 'prop_goodnewsupdateprofile.submitvar_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'goodnews-updateprofile-btn',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'successKey',
        'desc'    => 'prop_goodnewsupdateprofile.successkey_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'updsuccess',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'successMsg',
        'desc'    => 'prop_goodnewsupdateprofile.successmsg_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'validate',
        'desc'    => 'prop_goodnewsupdateprofile.validate_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'grpFieldsetTpl',
        'desc'    => 'prop_goodnewsupdateprofile.grpfieldsettpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsGrpFieldsetChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'grpNameTpl',
        'desc'    => 'prop_goodnewsupdateprofile.grpnametpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsGrpNameChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'grpFieldTpl',
        'desc'    => 'prop_goodnewsupdateprofile.grpfieldtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsGrpFieldChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'grpFieldHiddenTpl',
        'desc'    => 'prop_goodnewsupdateprofile.grpfieldhiddentpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsGrpFieldHiddenChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'catFieldTpl',
        'desc'    => 'prop_goodnewsupdateprofile.catfieldtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsCatFieldChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'catFieldHiddenTpl',
        'desc'    => 'prop_goodnewsupdateprofile.catfieldhiddentpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsCatFieldHiddenChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'groupsOnly',
        'desc'    => 'prop_goodnewsupdateprofile.groupsonly_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'includeGroups',
        'desc'    => 'prop_goodnewsupdateprofile.includegroups_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'defaultGroups',
        'desc'    => 'prop_goodnewsupdateprofile.defaultgroups_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'defaultCategories',
        'desc'    => 'prop_goodnewsupdateprofile.defaultcategories_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'sort',
        'desc'    => 'prop_goodnewsupdateprofile.sort_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'name',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'dir',
        'desc'    => 'prop_goodnewsupdateprofile.dir_desc',
        'type'    => 'list',
        'options' => [
            ['name' => 'opt_goodnews.asc','value'  => 'ASC'],
            ['name' => 'opt_goodnews.desc','value' => 'DESC'],
        ],
        'value'   => 'ASC',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'grpCatPlaceholder',
        'desc'    => 'prop_goodnewsupdateprofile.grpcatplaceholder_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'grpcatfieldsets',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'placeholderPrefix',
        'desc'    => 'prop_goodnewsupdateprofile.placeholderprefix_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
];

return $properties;
