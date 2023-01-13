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
 * GoodNewsRequestLinks
 *
 * Snippet which upon request - sends a subscriber an email with secure links to
 * update or cancel his subscription.
 *
 * @var modX $modx
 * @var Subscription $subscription
 * @var array $scriptProperties
 *
 * PROPERTIES
 *
 * @property string  &unsubscribeResourceId The ID of the resource for one-click unsubscription.
 *                   (default: '')
 * @property string  &profileResourceId The ID of the resource for editing the mailing profile.
 *                   (default: '')
 * @property string  &submittedResourceId If set, will redirect to the specified resource after the
 *                   user submits the request links form. (default: '')
 * @property string  &requestLinksEmailSubject The subject of the request links email.
 *                   (default: a lexicon string)
 * @property string  &requestLinksEmailTpl The template for the request links email.
 *                   (default: sample.GoodNewsRequestLinksEmailChunk)
 * @property string  &requestLinksEmailTplAlt If set, will use this template instead of &requestLinksEmailTpl
 *                   (e.g. for plaintext mails). (default: '')
 * @property string  &requestLinksEmailTplType The type of tpl provided for the request links email.
 *                   (default: modChunk)
 * @property string  &errTpl Chunk for error output.
 *                   (default: <span class="error">[[+error]]</span>)
 * @property string  &emailField The name of the field to use for the User's email address.
 *                   (default: email)
 * @property boolean &sendUnauthorizedPage Whether or not to redirect a subscriber to the unauthorized
 *                   page if not verified (default: false)
 * @property string  &submitVar The name of the form submit button that triggers the submission.
 *                   (default: goodnews-requestlinks-btn)
 * @property string  &successMsg If page redirects to itself, this message will be set to a placeholder.
 *                   (default: a lexicon string)
 * @property string  &validate A comma-separated list of fields to validate.
 *                   (default: '')
 * @property string  &placeholderPrefix The prefix to use for all placeholders set by this snippet.
 *                   (default: '')
 *
 * @package goodnews
 * @subpackage snippets
 */

$subscription = new Subscription($modx, $scriptProperties);
$controller = $subscription->loadController('RequestLinks');
$output = $controller->run($scriptProperties);
return $output;
