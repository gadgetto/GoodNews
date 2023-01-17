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
 * Properties for the GoodNewsUnSubscription snippet.
 *
 * @package goodnews
 * @subpackage build
 */

$properties = [
    [
        'name'    => 'errTpl',
        'desc'    => 'prop_goodnewsunsubscription.errtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '<span class="error">[[+error]]</span>',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'preHooks',
        'desc'    => 'prop_goodnewsunsubscription.prehooks_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'postHooks',
        'desc'    => 'prop_goodnewsunsubscription.posthooks_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'sendUnauthorizedPage',
        'desc'    => 'prop_goodnewsunsubscription.sendunauthorizedpage_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'submitVar',
        'desc'    => 'prop_goodnewsunsubscription.submitvar_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'goodnews-unsubscribe-btn',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'successKey',
        'desc'    => 'prop_goodnewsunsubscription.successkey_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'updsuccess',
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'removeUserData',
        'desc'    => 'prop_goodnewsunsubscription.removeuserdata_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ],
    [
        'name'    => 'placeholderPrefix',
        'desc'    => 'prop_goodnewsunsubscription.placeholderprefix_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ],
];

return $properties;
