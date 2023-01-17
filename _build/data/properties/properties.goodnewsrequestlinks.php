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
 * Properties for the GoodNewsRequestLinks snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = [
    [
        'name'    => 'unsubscribeResourceId',
        'desc'    => 'prop_goodnewsrequestlinks.unsubscriberesourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'profileResourceId',
        'desc'    => 'prop_goodnewsrequestlinks.profileresourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'submittedResourceId',
        'desc'    => 'prop_goodnewsrequestlinks.submittedresourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'requestLinksEmailSubject',
        'desc'    => 'prop_goodnewsrequestlinks.requestlinksemailsubject_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'requestLinksEmailTpl',
        'desc'    => 'prop_goodnewsrequestlinks.requestlinksemailtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsRequestLinksEmailChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'requestLinksEmailTplAlt',
        'desc'    => 'prop_goodnewsrequestlinks.requestlinksemailtplalt_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'requestLinksEmailTplType',
        'desc'    => 'prop_goodnewsrequestlinks.requestlinksemailtpltype_desc',
        'type'    => 'list',
        'options' => [
            ['name' => 'opt_goodnews.chunk','value'    => 'modChunk'],
            ['name' => 'opt_goodnews.file','value'     => 'file'],
            ['name' => 'opt_goodnews.inline','value'   => 'inline'],
            ['name' => 'opt_goodnews.embedded','value' => 'embedded'],
        ],
        'value'   => 'modChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'errTpl',
        'desc'    => 'prop_goodnewsrequestlinks.errtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '<span class="error">[[+error]]</span>',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'emailField',
        'desc'    => 'prop_goodnewsrequestlinks.emailfield_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'email',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'sendUnauthorizedPage',
        'desc'    => 'prop_goodnewsrequestlinks.sendunauthorizedpage_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'submitVar',
        'desc'    => 'prop_goodnewsrequestlinks.submitvar_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'goodnews-requestlinks-btn',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'successMsg',
        'desc'    => 'prop_goodnewsrequestlinks.successmsg_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'validate',
        'desc'    => 'prop_goodnewsrequestlinks.validate_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'placeholderPrefix',
        'desc'    => 'prop_goodnewsrequestlinks.placeholderprefix_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
];

return $properties;
