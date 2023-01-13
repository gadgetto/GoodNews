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
 * GoodNewsSubscription
 *
 * Snippet which handles new subscriptions and processes subscription forms.
 *
 * @var modX $modx
 * @var Subscription $subscription
 * @var array $scriptProperties
 *
 * PROPERTIES
 *
 * @property boolean &activation Whether or not to require activation for subscription.
 *                   (default: 1)
 * @property string  &activationttl Number of minutes until the activation email expires.
 *                   (default: 180)
 * @property string  &activationEmail Send either to user's email address or this specified activation email address.
 *                   (default: '')
 * @property string  &activationEmailSubject The subject of the activation email.
 *                   (default: a lexicon string)
 * @property string  &activationEmailTpl The template for the activation email.
 *                   (default: sample.GoodNewsActivationEmailChunk)
 * @property string  &activationEmailTplAlt If set, will use this template instead of &activationEmailTpl
 *                   (e.g. for plaintext mails). (default: '')
 * @property string  &activationEmailTplType The type of tpl provided for the activation email.
 *                   (default: modChunk)
 * @property string  &activationResourceId The Resource ID where the GoodNewsConfirmSubscription snippet is located.
 *                   (default: '')
 * @property string  &submittedResourceId If set, will redirect to the specified resource after the user submits the
 *                   subscription form. (default: '')
 * @property string  &unsubscribeResourceId The ID of the resource for one-click unsubscription.
 *                   (default: '')
 * @property string  &profileResourceId The ID of the resource for editing the mailing profile.
 *                   (default: '')
 * @property boolean &sendSubscriptionEmail Wether or not to send user an email after successful subscription.
 *                   (default: 1)
 * @property string  &subscriptionEmailSubject The subject of the subscription email.
 *                   (default: a lexicon string)
 * @property string  &subscriptionEmailTpl The template for the subscription email.
 *                   (default: sample.GoodNewsSubscriptionEmailChunk)
 * @property string  &subscriptionEmailTplAlt If set, will use this template instead of &subscriptionEmailTpl
 *                   (e.g. for plaintext mails). (default: '')
 * @property string  &subscriptionEmailTplType The type of tpl provided for the subscription email.
 *                   (default: modChunk)
 * @property string  &reSubscriptionEmailSubject The subject of the renewal email.
 *                   (default: a lexicon string)
 * @property string  &reSubscriptionEmailTpl The template for the renewal email.
 *                   (default: sample.GoodNewsReSubscriptionEmailChunk)
 * @property string  &reSubscriptionEmailTplAlt If set, will use this template instead of &reSubscriptionEmailTpl
 *                   (e.g. for plaintext mails). (default: '')
 * @property string  &reSubscriptionEmailTplType The type of tpl provided for the renewal email.
 *                   (default: modChunk)
 * @property string  &errTpl Chunk for error output.
 *                   (default: <span class="error">[[+error]]</span>)
 * @property boolean &useExtended Whether or not to set any extra fields in the form to the users extended field.
 *                   (default: 0)
 * @property string  &excludeExtended A comma-separated list of fields to exclude from setting as extended fields.
 *                   (default: '')
 * @property string  &emailField The name of the field to use for the new User's email address.
 *                   (default: email)
 * @property string  &usernameField The name of the field to use for the new User's username.
 *                   (default: username)
 * @property string  &passwordField The name of the field to use for the new User's password.
                     (default: password)
 * @property string  &persistParams A JSON object of parameters to persist across the register process.
 *                   (default: '')
 * @property string  &preHooks A comma-separated list of 'hooks' (snippets), that will be executed before the user is
 *                   subscribed but after validation. (default: '')
 * @property string  &postHooks A comma-separated list of 'hooks' (snippets), that will be executed after the user is
 *                   subscribed. (default: '')
 * @property string  &redirectBack
 *                   (default: '')
 * @property string  &redirectBackParams
 *                   (default: '')
 * @property string  &submitVar The name of the form submit button that triggers the submission.
 *                   (default: goodnews-subscription-btn)
 * @property string  &successMsg If page redirects to itself, this message will be set to a placeholder.
 *                   (default: '')
 * @property string  &usergroups A comma-separated list of MODX user group names or IDs to add the new subscriber to.
 *                   (default: '')
 * @property string  &usergroupsField The name of the field to use for the new subscribers usergroups.
 *                   (default: 'usergroups')
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
 *                   (default: false)
 * @property string  &includeGroups Comma separated list of group ids to be used for output.
 *                   (default: '')
 * @property string  &defaultGroups Comma separated list of group ids which should be preselected as hidden fields.
 *                   (default: '')
 * @property string  &defaultCategories Comma separated list of category ids which should be preselected as
 *                   hidden fields. (default: '')
 * @property string  &sort Field to sort by for groups/categories.
 *                   (default: name)
 * @property string  &dir Sort direction for groups/categories.
 *                   (default: ASC)
 * @property string  &grpCatPlaceholder The placeholder to set the generated groups/categories tree to.
 *                   (default: grpcatfieldsets)
 * @property string  &placeholderPrefix The prefix to use for all placeholders set by this snippet.
 *                   (default: '')
 * @property string  &errorPage ID of the error page resource. If set, subscriber will be redirected to the
 *                   resource with this ID if confirmation failed. (default: 0)
 *
 * @package goodnews
 * @subpackage snippets
 */

$subscription = new Subscription($modx, $scriptProperties);
$controller = $subscription->loadController('Subscription');
$output = $controller->run($scriptProperties);
return $output;
