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
 * Snippet which upon request - sends a subscriber an email with secure links to update or cancel his subscription.
 *
 * @var modX $modx
 * @var GoodNewsSubscription $goodnewssubscription
 * @var array $scriptProperties
 *
 * @property string $unsubscribeResourceId The ID of the resource for one-click unsubscription. (default: '')
 * @property string $profileResourceId The ID of the resource for editing the mailing profile. (default: '')
 * @property string $submittedResourceId If set, will redirect to the specified resource after the user submits the request links form. (default: '')
 * @property string $requestLinksEmailSubject The subject of the request links email. (default: a lexicon string)
 * @property string $requestLinksEmailTpl The template for the request links email. (default: sample.GoodNewsRequestLinksEmailTpl)
 * @property string $requestLinksEmailTplAlt If set, will use this template instead of $requestLinksEmailTpl (e.g. for plaintext mails). (default: '')
 * @property string $requestLinksEmailTplType The type of tpl provided for the request links email. (default: modChunk)
 * @property string $errTpl Chunk for error output. (default: <span class="error">[[+error]]</span>)
 * @property string $emailField The name of the field to use for the User's email address. (default: email)
 * @property boolean $sendUnauthorizedPage Whether or not to redirect a subscriber to the unauthorized page if not verified (default: 0 = false)
 * @property string $submitVar The name of the form submit button that triggers the submission. (default: goodnews-requestlinks-btn)
 * @property string $successMsg If page redirects to itself, this message will be set to a placeholder.
 * @property string $validate A comma-separated list of fields to validate. (default: '')
 * @property string $placeholderPrefix The prefix to use for all placeholders set by this snippet. (default: '')
 * @package goodnews
 */

require_once $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/goodnews/goodnewssubscription.class.php';
$goodnewssubscription = new GoodNewsSubscription($modx, $scriptProperties);

$controller = $goodnewssubscription->loadController('RequestLinks');
$output = $controller->run($scriptProperties);
return $output;
