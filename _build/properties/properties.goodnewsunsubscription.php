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
 * Properties for the GoodNewsUnSubscription snippet.
 *
 * @package goodnews
 * @subpackage build
 */
            
$properties = array(
    array(
        'name'    => 'errTpl',
        'desc'    => 'prop_goodnewsunsubscription.errtpl_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '<span class="error">[[+error]]</span>',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'preHooks',
        'desc'    => 'prop_goodnewsunsubscription.prehooks_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'postHooks',
        'desc'    => 'prop_goodnewsunsubscription.posthooks_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'sendUnauthorizedPage',
        'desc'    => 'prop_goodnewsunsubscription.sendunauthorizedpage_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'submitVar',
        'desc'    => 'prop_goodnewsunsubscription.submitvar_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'goodnews-unsubscribe-btn',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'successKey',
        'desc'    => 'prop_goodnewsunsubscription.successkey_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => 'updsuccess',
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'removeUserData',
        'desc'    => 'prop_goodnewsunsubscription.removeuserdata_desc',
        'type'    => 'combo-boolean',
        'options' => '',
        'value'   => false,
        'lexicon' => 'goodnews:properties',
    ),
    array(
        'name'    => 'placeholderPrefix',
        'desc'    => 'prop_goodnewsunsubscription.placeholderprefix_desc',
        'type'    => 'textfield',
        'options' => '',
        'value'   => '',
        'lexicon' => 'goodnews:properties',
    ),
);

return $properties;
