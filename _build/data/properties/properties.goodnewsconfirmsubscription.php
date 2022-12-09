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
 * Properties for the GoodNewsConfirmSubscription snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = array(
    array(
        'name'    => 'sendSubscriptionEmail',
        'desc'    => 'prop_goodnewsconfirmsubscription.sendsubscriptionemail_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'unsubscribeResourceId',
        'desc'    => 'prop_goodnewsconfirmsubscription.unsubscriberesourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'profileResourceId',
        'desc'    => 'prop_goodnewsconfirmsubscription.profileresourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'subscriptionEmailSubject',
        'desc'    => 'prop_goodnewsconfirmsubscription.subscriptionemailsubject_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'subscriptionEmailTpl',
        'desc'    => 'prop_goodnewsconfirmsubscription.subscriptionemailtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsSubscriptionEmailChunk',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'subscriptionEmailTplAlt',
        'desc'    => 'prop_goodnewsconfirmsubscription.subscriptionemailtplalt_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'subscriptionEmailTplType',
        'desc'    => 'prop_goodnewsconfirmsubscription.subscriptionemailtpltype_desc',
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
        'name'    => 'errorPage',
        'desc'    => 'prop_goodnewsconfirmsubscription.errorpage_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
);

return $properties;
