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
 * GoodNewsUnSubscription
 *
 * Snippet which handles one-click unsubscription through email link.
 *
 * @var modX $modx
 * @var Subscription $subscription
 * @var array $scriptProperties
 *
 * PROPERTIES
 *
 * @property string  &errTpl Chunk for error output
 *                   (default: <span class="error">[[+error]]</span>)
 * @property string  &preHooks A comma-separated list of 'hooks' (snippets), that will be executed before the user is
 *                   unsubscribed but after validation. (default: '')
 * @property string  &postHooks A comma-separated list of 'hooks' (snippets), that will be executed after the user is
 *                   unsubscribed. (default: '')
 * @property boolean &sendUnauthorizedPage Whether or not to redirect a subscriber to the unauthorized page if his
 *                   authentication is not verified (default: 0)
 * @property string  &submitVar The name of the form submit button that triggers the submission.
 *                   (default: goodnews-unsubscribe-btn)
 * @property string  &successKey The name of the key submitted as url param in case of success
 *                   (default: unsubsuccess)
 * @property boolean &removeUserData Whether or not to completely remove all user data from database after
 *                   unsubscription (default: 0)
 * @property string  &placeholderPrefix The prefix to use for all placeholders set by this snippet.
 *                   (default: '')
 *
 * @package goodnews
 * @subpackage snippets
 */

$subscription = new Subscription($modx, $scriptProperties);
$controller = $subscription->loadController('UnSubscription');
$output = $controller->run($scriptProperties);
return $output;
