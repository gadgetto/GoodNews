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

use Bitego\GoodNews\Subscription\Subscription;

/**
 * GoodNewsConfirmSubscription
 *
 * Snippet to confirm and activate the subscription (double opt-in).
 * Place on an activation page where the user will be sent to via the activation email.
 *
 * @var modX $modx
 * @var Subscription $subscription
 * @var array $scriptProperties
 *
 * PROPERTIES
 *
 * @property boolean &sendSubscriptionEmail Wether or not to send user an email after successful subscription.
 *                   (default: true)
 * @property string  &unsubscribeResourceId The ID of the resource for one-click unsubscription.
 *                   (default: '')
 * @property string  &profileResourceId The ID of the resource for editing the mailing profile.
 *                   (default: '')
 * @property string  &subscriptionEmailSubject The subject of the subscription email.
 *                   (default: a lexicon string)
 * @property string  &subscriptionEmailTpl The template for the subscription email.
 *                   (default: sample.GoodNewsSubscriptionEmailChunk)
 * @property string  &subscriptionEmailTplAlt If set, will use this template instead of &subscriptionEmailTpl
 *                   (e.g. for plaintext mails). (default: '')
 * @property string  &subscriptionEmailTplType The type of tpl provided for the subscription email.
 *                   (default: modChunk)
 * @property string  &errorPage ID of the error page resource. If set, subscriber will be redirected to the
 *                   resource with this ID if confirmation failed. (default: false)
 *
 * @package goodnews
 * @subpackage snippets
 */

$subscription = new Subscription($modx, $scriptProperties);
$controller = $subscription->loadController('Confirm');
$output = $controller->run($scriptProperties);
return $output;
