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
 * Properties for the GoodNewsConfirmSubscription snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = [
    [
        'name'    => 'sendSubscriptionEmail',
        'desc'    => 'prop_goodnewsconfirmsubscription.sendsubscriptionemail_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'unsubscribeResourceId',
        'desc'    => 'prop_goodnewsconfirmsubscription.unsubscriberesourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'profileResourceId',
        'desc'    => 'prop_goodnewsconfirmsubscription.profileresourceid_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'subscriptionEmailSubject',
        'desc'    => 'prop_goodnewsconfirmsubscription.subscriptionemailsubject_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'subscriptionEmailTpl',
        'desc'    => 'prop_goodnewsconfirmsubscription.subscriptionemailtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'sample.GoodNewsSubscriptionEmailChunk',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'subscriptionEmailTplAlt',
        'desc'    => 'prop_goodnewsconfirmsubscription.subscriptionemailtplalt_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'subscriptionEmailTplType',
        'desc'    => 'prop_goodnewsconfirmsubscription.subscriptionemailtpltype_desc',
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
        'name'    => 'errorPage',
        'desc'    => 'prop_goodnewsconfirmsubscription.errorpage_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
];

return $properties;
