<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 * Based on code from Login add-on
 * Copyright 2010 by Shaun McCormick <shaun@modx.com>
 * Modified by bitego - 10/2013
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
 * Snippet to confirm and activate the subscription (double opt-in).
 * Place on an activation page where the user will be sent to via the activation email.
 *
 * @var modX $modx
 * @var GoodNewsSubscription $goodnewssubscription
 * @var array $scriptProperties
 *
 * @property boolean $sendSubscriptionEmail Wether or not to send user an email after successful subscription.
 * @property string $unsubscribeResourceId The ID of the resource for one-click unsubscription. (default: '')
 * @property string $profileResourceId The ID of the resource for editing the mailing profile. (default: '')
 * @property string $subscriptionEmailSubject The subject of the subscription email. (default: a lexicon string)
 * @property string $subscriptionEmailTpl The template for the subscription email. (default: sample.GoodNewsSubscriptionEmailChunk)
 * @property string $subscriptionEmailTplAlt If set, will use this template instead of $subscriptionEmailTpl (e.g. for plaintext mails). (default: '')
 * @property string $subscriptionEmailTplType The type of tpl provided for the subscription email. (default: modChunk)
 * @property string $errorPage ID of the error page resource. If set, subscriber will be redirected to the resource with this ID if confirmation failed.
 *
 * @package goodnews
 */

require_once $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/goodnews/goodnewssubscription.class.php';
$goodnewssubscription = new GoodNewsSubscription($modx, $scriptProperties);

$controller = $goodnewssubscription->loadController('Confirm');
$output = $controller->run($scriptProperties);
return $output;