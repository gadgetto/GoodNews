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
 * Properties for the GoodNewsRequestLinks snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = array(
    array(
        'name'    => 'unsubscribeResourceId',
        'desc'    => 'prop_goodnewsrequestlinks.unsubscriberesourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'profileResourceId',
        'desc'    => 'prop_goodnewsrequestlinks.profileresourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'submittedResourceId',
        'desc'    => 'prop_goodnewsrequestlinks.submittedresourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'requestLinksEmailSubject',
        'desc'    => 'prop_goodnewsrequestlinks.requestlinksemailsubject_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'requestLinksEmailTpl',
        'desc'    => 'prop_goodnewsrequestlinks.requestlinksemailtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsRequestLinksEmailChunk',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'requestLinksEmailTplAlt',
        'desc'    => 'prop_goodnewsrequestlinks.requestlinksemailtplalt_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'requestLinksEmailTplType',
        'desc'    => 'prop_goodnewsrequestlinks.requestlinksemailtpltype_desc',
        'type'    => 'list',
        'options' => array(
            array('name' => 'opt_goodnews.chunk','value'    => 'modChunk'),
            array('name' => 'opt_goodnews.file','value'     => 'file'),
            array('name' => 'opt_goodnews.inline','value'   => 'inline'),
            array('name' => 'opt_goodnews.embedded','value' => 'embedded'),
        ),
        'value'   => 'modChunk',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'errTpl',
        'desc'    => 'prop_goodnewsrequestlinks.errtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '<span class="error">[[+error]]</span>',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'emailField',
        'desc'    => 'prop_goodnewsrequestlinks.emailfield_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'email',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'sendUnauthorizedPage',
        'desc'    => 'prop_goodnewsrequestlinks.sendunauthorizedpage_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'submitVar',
        'desc'    => 'prop_goodnewsrequestlinks.submitvar_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'goodnews-requestlinks-btn',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'successMsg',
        'desc'    => 'prop_goodnewsrequestlinks.successmsg_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'validate',
        'desc'    => 'prop_goodnewsrequestlinks.validate_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'placeholderPrefix',
        'desc'    => 'prop_goodnewsrequestlinks.placeholderprefix_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
);

return $properties;
