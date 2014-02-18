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
 * Snippet which handles new subscriptions and processes subscription forms.
 *
 * @var modX $modx
 * @var GoodNewsSubscription $goodnewssubscription
 * @var array $scriptProperties
 *
 * @property boolean $activation Whether or not to require activation for subscription. (default: 1=true)
 * @property string $activationttl Number of minutes until the activation email expires. (default: 3 hours)
 * @property string $activationEmail Send either to user's email address or this specified activation email address. (default: '' = users email)
 * @property string $activationEmailSubject The subject of the activation email. (default: a lexicon string)
 * @property string $activationEmailTpl The template for the activation email. (default: sample.GoodNewsActivationEmailTpl)
 * @property string $activationEmailTplAlt If set, will use this template instead of $activationEmailTpl (e.g. for plaintext mails). (default: '')
 * @property string $activationEmailTplType The type of tpl provided for the activation email. (default: modChunk)
 * @property string $activationResourceId The Resource ID where the GoodNewsConfirmSubscription snippet is located. (default: '')
 * @property string $submittedResourceId If set, will redirect to the specified resource after the user submits the subscription form. (default: '')
 * @property string $errTpl Chunk for error output. (default: <span class="error">[[+error]]</span>)
 * @property boolean $useExtended Whether or not to set any extra fields in the form to the users extended field. (default: 0 = false)
 * @property string $excludeExtended A comma-separated list of fields to exclude from setting as extended fields. (default: '')
 * @property string $emailField The name of the field to use for the new User's email address. (default: email)
 * @property string $persistParams A JSON object of parameters to persist across the register process. (default: '')
 * @property string $preHooks A comma-separated list of 'hooks' (snippets), that will be executed before the user is subscribed but after validation. (default: '')
 * @property string $postHooks A comma-separated list of 'hooks' (snippets), that will be executed after the user is subscribed. (default: '')
 * @property string $redirectBack (default: '')
 * @property string $redirectBackParams (default: '')
 * @property string $submitVar The name of the form submit button that triggers the submission. (default: goodnews-subscription-btn)
 * @property string $successMsg If page redirects to itself, this message will be set to a placeholder.
 * @property string $usergroups A comma-separated list of MODX user group names or IDs to add the new subscriber to. (default: '')
 * @property string $usergroupsField The name of the field to use for the new subscribers usergroups. (default: 'usergroups')
 * @property string $validate A comma-separated list of fields to validate. (default: '')
 * @property string $grpFieldsetTpl Chunk name for group fieldset. (default: sample.GoodNewsGrpFieldsetTpl)
 * @property string $grpFieldTpl Chunk name for group checkbox element. (default: sample.GoodNewsGrpFieldTpl)
 * @property string $grpNameTpl Chunk name for group name element. (default: sample.GoodNewsGrpNameTpl)
 * @property string $grpFieldHiddenTpl Chunk name for group input hidden element. (default: sample.GoodNewsGrpFieldHiddenTpl)
 * @property string $catFieldTpl Chunk name for category checkbox element. (default: sample.GoodNewsCatFieldTpl)
 * @property string $catFieldHiddenTpl Chunk name for category input hidden element. (default: sample.GoodNewsCatFieldHiddenTpl)
 * @property boolean $groupsOnly Whether or not the output should only contain groups. (default: 0 = false)
 * @property string $includeGroups Comma separated list of group ids to be used for output. (default: 0 = use all groups)
 * @property string $defaultGroups Comma separated list of group ids which should be preselected as hidden fields. (default: 0 = none)
 * @property string $defaultCategories Comma separated list of category ids which should be preselected as hidden fields. (default: 0 = none)
 * @property string $sort Field to sort by for groups/categories. (default: name)
 * @property string $dir Sort direction for groups/categories. (default: ASC)
 * @property string $grpCatPlaceholder The placeholder to set the generated groups/categories tree to. (default: grpcatfieldsets)
 * @property string $placeholderPrefix The prefix to use for all placeholders set by this snippet. (default: '')
 * @package goodnews
 */

require_once $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/goodnews/goodnewssubscription.class.php';
$goodnewssubscription = new GoodNewsSubscription($modx, $scriptProperties);

$controller = $goodnewssubscription->loadController('Subscription');
$output = $controller->run($scriptProperties);
return $output;
