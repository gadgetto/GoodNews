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
 * GoodNewsUpdateProfile
 *
 * Snippet which handles updating of subscribers profile.
 *
 * @var modX $modx
 * @var Subscription $subscription
 * @var array $scriptProperties
 *
 * PROPERTIES
 *
 * @property string  &errTpl Chunk name for group fieldset.
 *                   (default: <span class="error">[[+error]]</span>)
 * @property boolean &useExtended Whether or not to set any extra fields in the form to the users extended field.
 *                   (default: 0)
 * @property string  &excludeExtended A comma-separated list of fields to exclude from setting as extended fields.
 *                   (default: '')
 * @property string  &emailField The name of the field to use for the user's email address.
 *                   (default: email)
 * @property string  &preHooks A comma-separated list of 'hooks' (snippets), that will be executed before
 *                   the user profile is updated but after validation. (default: '')
 * @property string  &postHooks A comma-separated list of 'hooks' (snippets), that will be executed after
 *                   the user profile is updated. (default: '')
 * @property boolean &sendUnauthorizedPage Whether or not to redirect a subscriber to the unauthorized page if his
 *                   authentication is not verified (default: 0)
 * @property boolean &reloadOnSuccess If true, page will redirect to itself to prevent double-postbacks. Otherwise
 *                   it will set a success placeholder. (default: 1)
 * @property string  &submitVar The name of the form submit button that triggers the submission.
 *                   (default: goodnews-updateprofile-btn)
 * @property string  &successKey The name of the key submitted as url param in case of success.
 *                   (default: updsuccess)
 * @property string  &successMsg If page redirects to itself, this message will be set to a placeholder.
 *                   (default: a lexicon string)
 * @property string  &validate A comma-separated list of fields to validate.
 *                   (default: '')
 * @property string  &grpFieldsetTpl Chunk name for group fieldset.
 *                   (default: sample.GoodNewsGrpFieldsetChunk)
 * @property string  &grpFieldTpl Chunk name for group checkbox element.
 *                   (default: sample.GoodNewsGrpFieldChunk)
 * @property string  &grpNameTpl Chunk name for group name element.
 *                   (default: sample.GoodNewsGrpNameChunk)
 * @property string  &grpFieldHiddenTpl Chunk name for group input hidden element.
 *                   (default: sample.GoodNewsGrpFieldHiddenChunk)
 * @property string  &catFieldTpl Chunk name for category checkbox element.
 *                   (default: sample.GoodNewsCatFieldChunk)
 * @property string  &catFieldHiddenTpl Chunk name for category input hidden element.
 *                   (default: sample.GoodNewsCatFieldHiddenChunk)
 * @property boolean &groupsOnly Whether or not the output should only contain groups.
 *                   (default: 0)
 * @property string  &includeGroups Comma separated list of group ids to be used for output.
 *                   (default: 0 = use all groups)
 * @property string  &defaultGroups Comma separated list of group ids which should be preselected (checked).
 *                   (default: 0 = none checked)
 * @property string  &defaultCategories Comma separated list of category ids which should be preselected (checked).
 *                   (default: 0 = none checked)
 * @property string  &sort Field to sort by for groups/categories.
 *                   (default: name)
 * @property string  &dir Sort direction for groups/categories.
 *                   (default: ASC)
 * @property string  &grpCatPlaceholder The placeholder to set the generatede groups/categories tree to.
 *                   (default: grpcatfieldsets)
 * @property string  &placeholderPrefix The prefix to use for all placeholders set by this snippet.
 *                   (default: '')
 *
 * @package goodnews
 * @subpackage snippets
 */

$subscription = new Subscription($modx, $scriptProperties);
$controller = $subscription->loadController('UpdateProfile');
$output = $controller->run($scriptProperties);
return $output;
