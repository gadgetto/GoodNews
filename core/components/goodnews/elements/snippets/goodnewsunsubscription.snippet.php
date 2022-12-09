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
 * Snippet which handles one-click unsubscription through email link.
 *
 * @var modX $modx
 * @var GoodNewsSubscription $goodnewssubscription
 * @var array $scriptProperties
 *
 * @property string $errTpl Chunk for error output (default: <span class="error">[[+error]]</span>)
 * @property string $preHooks A comma-separated list of 'hooks' (snippets), that will be executed before the user is unsubscribed but after validation.
 * @property string $postHooks A comma-separated list of 'hooks' (snippets), that will be executed after the user is unsubscribed.
 * @property boolean $sendUnauthorizedPage Whether or not to redirect a subscriber to the unauthorized page if his authentication is not verified (default: 0 = false)
 * @property string $submitVar The name of the form submit button that triggers the submission. (default: goodnews-unsubscribe-btn)
 * @property string $successKey The name of the key submitted as url param in case of success (default: unsubsuccess)
 * @property boolean $removeUserData Whether or not to completely remove all user data from database after unsubscription (default: 0 = false)
 * @property string $placeholderPrefix The prefix to use for all placeholders set by this snippet. (default: '')
 *
 * @package goodnews
 */

require_once $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/goodnews/goodnewssubscription.class.php';
$goodnewssubscription = new GoodNewsSubscription($modx, $scriptProperties);

$controller = $goodnewssubscription->loadController('UnSubscription');
$output = $controller->run($scriptProperties);
return $output;