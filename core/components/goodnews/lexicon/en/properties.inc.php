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
 * GoodNews properties
 *
 * @package goodnews
 * @subpackage lexicon
 * @language en
 */

// GoodNewsSubscription snippet
$_lang['prop_goodnewssubscription.activation_desc']                 = 'Whether to require activation for proper subscription. If true, subscribers will not be marked active until they have activated their account.';
$_lang['prop_goodnewssubscription.activationttl_desc']              = 'Number of minutes until the activation email expires.';
$_lang['prop_goodnewssubscription.activationemail_desc']            = 'If set, will send activation emails to this address instead of the newly subscribed user&apos;s address.';
$_lang['prop_goodnewssubscription.activationemailsubject_desc']     = 'The subject of the activation email.';
$_lang['prop_goodnewssubscription.activationemailtpl_desc']         = 'The activation email template.';
$_lang['prop_goodnewssubscription.activationemailtplalt_desc']      = 'Plain-text alternative for the activation email template.';
$_lang['prop_goodnewssubscription.activationemailtpltype_desc']     = 'The template-type for the activation email.';
$_lang['prop_goodnewssubscription.activationresourceid_desc']       = 'The ID of the Resource where the GoodNewsConfirmSubscription snippet for activation is located.';
$_lang['prop_goodnewssubscription.submittedresourceid_desc']        = 'Redirect to the Resource with the specified ID after the subscriber submits the form.';
$_lang['prop_goodnewssubscription.sendsubscriptionemail_desc']      = 'Wether to send the subscriber an email after successful activation.';
$_lang['prop_goodnewssubscription.unsubscriberesourceid_desc']      = 'The ID of the Resource to cancel subscriptions.';
$_lang['prop_goodnewssubscription.profileresourceid_desc']          = 'The ID of the Resource to edit subscription profiles.';
$_lang['prop_goodnewssubscription.subscriptionemailsubject_desc']   = 'The subject of the success email.';
$_lang['prop_goodnewssubscription.subscriptionemailtpl_desc']       = 'The success email template.';
$_lang['prop_goodnewssubscription.subscriptionemailtplalt_desc']    = 'Plain-text alternative for the success email template.';
$_lang['prop_goodnewssubscription.subscriptionemailtpltype_desc']   = 'The template-type for the success email.';
$_lang['prop_goodnewssubscription.resubscriptionemailsubject_desc'] = 'The subject of the renewal email.';
$_lang['prop_goodnewssubscription.resubscriptionemailtpl_desc']     = 'The renewal email template.';
$_lang['prop_goodnewssubscription.resubscriptionemailtplalt_desc']  = 'Plain-text alternative for the renewal email template.';
$_lang['prop_goodnewssubscription.resubscriptionemailtpltype_desc'] = 'The template-type for the renewal email.';
$_lang['prop_goodnewssubscription.errtpl_desc']                     = 'The template for error message output in fields.';
$_lang['prop_goodnewssubscription.useextended_desc']                = 'Set any non-profile fields in the form to extended fields of the MODX user profile. This can be useful for storing extra data.';
$_lang['prop_goodnewssubscription.excludeextended_desc']            = 'A comma-separated list of fields to exclude from setting as extended fields.';
$_lang['prop_goodnewssubscription.emailfield_desc']                 = 'Name of the field to use for the new Subscribers&apos;s email address.';
$_lang['prop_goodnewssubscription.persistparams_desc']              = 'A JSON object of parameters to persist across the subscription process. Useful when using redirect on GoodNewsConfirmSubscription snippet to redirect to another page (e.g. for shopping carts).';
$_lang['prop_goodnewssubscription.prehooks_desc']                   = 'A comma-separated list of scripts to fire, before the form passes validation. If one fails, the proceeding ones will not fire. A hook can also be a Snippet name.';
$_lang['prop_goodnewssubscription.posthooks_desc']                  = 'A comma-separated list of scripts to fire, after the user has been subscribed. If one fails, the proceeding ones will not fire. A hook can also be a Snippet name.';
$_lang['prop_goodnewssubscription.redirectback_desc']               = '';
$_lang['prop_goodnewssubscription.redirectbackparams_desc']         = '';
$_lang['prop_goodnewssubscription.submitvar_desc']                  = 'Variable for check: If empty or set to false, the snippet will process the form with all POST variables.';
$_lang['prop_goodnewssubscription.successmsg_desc']                 = 'If not redirecting using the submittedResourceId parameter, will display this message instead.';
$_lang['prop_goodnewssubscription.usergroups_desc']                 = 'A comma-separated list of MODX User Group names or IDs to automatically add the newly subscribed user to.';
$_lang['prop_goodnewssubscription.usergroupsfield_desc']            = 'Name of the field to specify the MODX User Group(s) to automatically add new subscribers to. Only used if this value is not blank.';
$_lang['prop_goodnewssubscription.validate_desc']                   = 'A comma-separated list of fields to validate, with each field name as name:validator (eg: fullname:required,email:required). Validators can also be chained, like email:email:required.';
$_lang['prop_goodnewssubscription.grpfieldsettpl_desc']             = 'The template to use for a GoodNews group form fieldset.';
$_lang['prop_goodnewssubscription.grpnametpl_desc']                 = 'The template to use for GoodNews group names (without form field).';
$_lang['prop_goodnewssubscription.grpfieldtpl_desc']                = 'The template to use for a GoodNews group form checkbox field.';
$_lang['prop_goodnewssubscription.grpfieldhiddentpl_desc']          = 'The template to use for a hidden GoodNews group form input field.';
$_lang['prop_goodnewssubscription.catfieldtpl_desc']                = 'The template to use for a GoodNews category form checkbox field.';
$_lang['prop_goodnewssubscription.catfieldhiddentpl_desc']          = 'The template to use for a hidden GoodNews category form input field.';
$_lang['prop_goodnewssubscription.groupsonly_desc']                 = 'If set to YES, only GoodNews groups will be used for subscription.';
$_lang['prop_goodnewssubscription.includegroups_desc']              = 'A comma-separated list of GoodNews group IDs, which will be used exclusively.';
$_lang['prop_goodnewssubscription.defaultgroups_desc']              = 'A comma-separated list of GoodNews group IDs, which will be used mandatory. Subscribers will be added to this groups automatically. Form fields will be hidden.';
$_lang['prop_goodnewssubscription.defaultcategories_desc']          = 'A comma-separated list of GoodNews category IDs, which will be used mandatory. Subscribers will be added to this categories automatically. Form fields will be hidden.';
$_lang['prop_goodnewssubscription.sort_desc']                       = 'Name of the field to sort GoodNews group and category fields by.';
$_lang['prop_goodnewssubscription.dir_desc']                        = 'Direction to sort GoodNews group and category fields by.';
$_lang['prop_goodnewssubscription.grpcatplaceholder_desc']          = 'Name of the placeholder which holds all the the GoodNews group and category form fields.';
$_lang['prop_goodnewssubscription.placeholderprefix_desc']          = 'The prefix to use for all placeholders set by this snippet.';
$_lang['prop_goodnewssubscription.errorpage_desc']                  = 'If set, will redirect user to a custom error page.';

// GoodNewsConfirmSubscription snippet
$_lang['prop_goodnewsconfirmsubscription.sendsubscriptionemail_desc']      = 'Wether to send the subscriber an email after successful activation.';
$_lang['prop_goodnewsconfirmsubscription.unsubscriberesourceid_desc']      = 'The ID of the Resource to cancel subscriptions.';
$_lang['prop_goodnewsconfirmsubscription.profileresourceid_desc']          = 'The ID of the Resource to edit subscription profiles.';
$_lang['prop_goodnewsconfirmsubscription.subscriptionemailsubject_desc']   = 'The subject of the success email.';
$_lang['prop_goodnewsconfirmsubscription.subscriptionemailtpl_desc']       = 'The success email template.';
$_lang['prop_goodnewsconfirmsubscription.subscriptionemailtplalt_desc']    = 'Plain-text alternative for the success email template.';
$_lang['prop_goodnewsconfirmsubscription.subscriptionemailtpltype_desc']   = 'The template-type for the success email.';
$_lang['prop_goodnewsconfirmsubscription.errorpage_desc']                  = 'If set, will redirect user to a custom error page if they try to access this page after activating their account.';

// GoodNewsUpdateProfile snippet
$_lang['prop_goodnewsupdateprofile.errtpl_desc']                    = 'The template for error message output in fields.';
$_lang['prop_goodnewsupdateprofile.useextended_desc']               = 'Whether or not to set any extra fields in the form to the Profiles extended field. This can be useful for storing extra user fields.';
$_lang['prop_goodnewsupdateprofile.excludeextended_desc']           = 'A comma-separated list of fields to exclude from setting as extended fields.';
$_lang['prop_goodnewsupdateprofile.emailfield_desc']                = 'The field name for the email field in the form.';
$_lang['prop_goodnewsupdateprofile.prehooks_desc']                  = 'A comma-separated list of scripts to fire, before the form passes validation. If one fails, the proceeding ones will not fire. A hook can also be a Snippet name.';
$_lang['prop_goodnewsupdateprofile.posthooks_desc']                 = 'A comma-separated list of scripts to fire, after the profile was updated. If one fails, the proceeding ones will not fire. A hook can also be a Snippet name.';
$_lang['prop_goodnewsupdateprofile.sendunauthorizedpage_desc']      = 'If a user is not identified by the given SID from the email, redirect him to the Unauthorized Page.';
$_lang['prop_goodnewsupdateprofile.reloadonsuccess_desc']           = 'If true, the page will redirect to itself with a GET parameter to prevent double-postbacks. If false, it will simply set a success placeholder.';
$_lang['prop_goodnewsupdateprofile.submitvar_desc']                 = 'Variable for check: If empty or set to false, the snippet will process the form with all POST variables.';
$_lang['prop_goodnewsupdateprofile.successkey_desc']                = 'Name of the key which will be sent as URL parameter with the value true if the update was successfull.';
$_lang['prop_goodnewsupdateprofile.successmsg_desc']                = 'The message which will be returned by the processor if the update was successfull.';
$_lang['prop_goodnewsupdateprofile.validate_desc']                  = 'A comma-separated list of fields to validate, with each field name as name:validator (eg: fullname:required,email:required). Validators can also be chained, like email:email:required.';
$_lang['prop_goodnewsupdateprofile.grpfieldsettpl_desc']            = 'The template to use for a GoodNews group form fieldset.';
$_lang['prop_goodnewsupdateprofile.grpnametpl_desc']                = 'The template to use for GoodNews group names (without form field).';
$_lang['prop_goodnewsupdateprofile.grpfieldtpl_desc']               = 'The template to use for a GoodNews group form checkbox field.';
$_lang['prop_goodnewsupdateprofile.grpfieldhiddentpl_desc']         = 'The template to use for a hidden GoodNews group form input field.';
$_lang['prop_goodnewsupdateprofile.catfieldtpl_desc']               = 'The template to use for a GoodNews category form checkbox field.';
$_lang['prop_goodnewsupdateprofile.catfieldhiddentpl_desc']         = 'The template to use for a hidden GoodNews category form input field.';
$_lang['prop_goodnewsupdateprofile.groupsonly_desc']                = 'If set to YES, only GoodNews groups will be used for subscription.';
$_lang['prop_goodnewsupdateprofile.includegroups_desc']             = 'A comma-separated list of GoodNews group IDs, which will be used exclusively.';
$_lang['prop_goodnewsupdateprofile.defaultgroups_desc']             = 'A comma-separated list of GoodNews group IDs, which will be used mandatory. Subscribers will be added to this groups automatically. Form fields will be hidden.';
$_lang['prop_goodnewsupdateprofile.defaultcategories_desc']         = 'A comma-separated list of GoodNews category IDs, which will be used mandatory. Subscribers will be added to this categories automatically. Form fields will be hidden.';
$_lang['prop_goodnewsupdateprofile.sort_desc']                      = 'Name of the field to sort GoodNews group and category fields by.';
$_lang['prop_goodnewsupdateprofile.dir_desc']                       = 'Direction to sort GoodNews group and category fields by.';
$_lang['prop_goodnewsupdateprofile.grpcatplaceholder_desc']         = 'Name of the placeholder which holds all the the GoodNews group and category form fields.';
$_lang['prop_goodnewsupdateprofile.placeholderprefix_desc']         = 'The prefix to use for all placeholders set by this snippet.';

// GoodNewsUnSubscription snippet
$_lang['prop_goodnewsunsubscription.errtpl_desc']                   = 'The template for error message output in fields.';
$_lang['prop_goodnewsunsubscription.prehooks_desc']                 = 'What scripts to fire, before the form passes validation. This can be a comma-separated list of hooks, and if the first fails, the proceeding ones will not fire. A hook can also be a Snippet name.';
$_lang['prop_goodnewsunsubscription.posthooks_desc']                = 'What scripts to fire, after the user has been unsubscribed. This can be a comma-separated list of hooks, and if the first fails, the proceeding ones will not fire. A hook can also be a Snippet name.';
$_lang['prop_goodnewsunsubscription.sendunauthorizedpage_desc']     = 'If a user is not identified by the sid from the email, redirect him to the Unauthorized Page.';
$_lang['prop_goodnewsunsubscription.submitvar_desc']                = 'Variable for check: If empty or set to false, the snippet will process the form with all POST variables.';
$_lang['prop_goodnewsunsubscription.successkey_desc']               = 'Name of the key which will be sent as URL parameter with the value true if the unsubscription was successfull.';
$_lang['prop_goodnewsunsubscription.removeuserdata_desc']           = 'If set, all user-data will be removed from the MODX database if a users cancels his subscription. Otherwise only GoodNews related data will be removed and the user will be deactivated.';
$_lang['prop_goodnewsunsubscription.placeholderprefix_desc']        = 'The prefix to use for all placeholders set by this snippet.';

// GoodNewsGetNewsletters snippet
$_lang['prop_goodnewsgetnewsletters.parent_desc']                   = 'The id of the mailing container to get newsletter documents from. If empty, the id of the current container is used.';
$_lang['prop_goodnewsgetnewsletters.tpl_desc']                      = 'Name of the template for a newsletter resource row. NOTE: if not provided, properties are dumped to output for each resource.';
$_lang['prop_goodnewsgetnewsletters.sortby_desc']                   = 'A field name to sort by or JSON object of field names and sortdir for each field, e.g. {"publishedon":"ASC","createdon":"DESC"}. Defaults to publishedon.';
$_lang['prop_goodnewsgetnewsletters.sortdir_desc']                  = 'Order which to sort by. Defaults to DESC.';
$_lang['prop_goodnewsgetnewsletters.includecontent_desc']           = 'Indicates if the content of each newsletter resource should be returned in the results. Defaults to false.';
$_lang['prop_goodnewsgetnewsletters.limit_desc']                    = 'Limits the number of newsletter resources returned. Defaults to 0 = unlimited.';
$_lang['prop_goodnewsgetnewsletters.offset_desc']                   = 'An offset of resources returned by the criteria to skip.';
$_lang['prop_goodnewsgetnewsletters.totalvar_desc']                 = 'Name of the placeholder which holds the count of the received newsletter resources.';
$_lang['prop_goodnewsgetnewsletters.outputseparator_desc']          = 'Separator for the output of newsletter row chunks.';
$_lang['prop_goodnewsgetnewsletters.toplaceholder_desc']            = 'If set, will assign the result to this placeholder instead of outputting it directly.';
$_lang['prop_goodnewsgetnewsletters.debug_desc']                    = 'If true, will send the SQL query to the MODX log. Defaults to false.';

// GoodNewsContentCollection snippet
$_lang['prop_goodnewscontentcollection.collectionid_desc']          = 'Internal name of the content collection (collection1, collection2 or collection3).';
$_lang['prop_goodnewscontentcollection.tpl_desc']                   = 'Name of a Chunk serving as template for a Resource row. NOTE: if not provided, properties are dumped to output for each resource.';
$_lang['prop_goodnewscontentcollection.tplwrapper_desc']            = 'Name of a Chunk serving as wrapper template for the Snippet output.';
$_lang['prop_goodnewscontentcollection.sortby_desc']                = 'A field name to sort by or JSON object of field names and sortdir for each field, e.g. {"publishedon":"ASC","createdon":"DESC"}. Defaults to publishedon.';
$_lang['prop_goodnewscontentcollection.sortdir_desc']               = 'Order which to sort by. Defaults to DESC.';
$_lang['prop_goodnewscontentcollection.includecontent_desc']        = 'Indicates if the content of each resource should be returned in the results. Defaults to false.';
$_lang['prop_goodnewscontentcollection.outputseparator_desc']       = 'Separator for the output of row chunks.';
$_lang['prop_goodnewscontentcollection.toplaceholder_desc']         = 'If set, will assign the result to this placeholder instead of outputting it directly.';
$_lang['prop_goodnewscontentcollection.debug_desc']                 = 'If true, will send the SQL query to the MODX log. Defaults to false.';

// GoodNewsRequestLinks snippet
$_lang['prop_goodnewsrequestlinks.unsubscriberesourceid_desc']      = 'The ID of the Resource to cancel subscriptions.';
$_lang['prop_goodnewsrequestlinks.profileresourceid_desc']          = 'The ID of the Resource to edit subscription profiles.';
$_lang['prop_goodnewsrequestlinks.submittedresourceid_desc']        = 'Redirect to the Resource with the specified ID after the subscriber submits the form.';
$_lang['prop_goodnewsrequestlinks.requestlinksemailsubject_desc']   = 'The subject of the request links email.';
$_lang['prop_goodnewsrequestlinks.requestlinksemailtpl_desc']       = 'The request links email template.';
$_lang['prop_goodnewsrequestlinks.requestlinksemailtplalt_desc']    = 'Plain-text alternative for the request links email template.';
$_lang['prop_goodnewsrequestlinks.requestlinksemailtpltype_desc']   = 'The template-type for the request links email.';
$_lang['prop_goodnewsrequestlinks.errtpl_desc']                     = 'The template for error message output in fields.';
$_lang['prop_goodnewsrequestlinks.emailfield_desc']                 = 'Name of the field to use for the email address.';
$_lang['prop_goodnewsrequestlinks.sendunauthorizedpage_desc']       = 'If a user is not identified by the submitted email, redirect him to the Unauthorized Page.';
$_lang['prop_goodnewsrequestlinks.submitvar_desc']                  = 'Variable for check: If empty or set to false, the snippet will process the form with all POST variables.';
$_lang['prop_goodnewsrequestlinks.successmsg_desc']                 = 'If not redirecting using the submittedResourceId parameter, will display this message instead.';
$_lang['prop_goodnewsrequestlinks.validate_desc']                   = 'A comma-separated list of fields to validate, with each field name as name:validator (eg: email:required). Validators can also be chained, like email:email:required.';
$_lang['prop_goodnewsrequestlinks.placeholderprefix_desc']          = 'The prefix to use for all placeholders set by this snippet.';

// List options
$_lang['opt_goodnews.chunk']    = 'Chunk';
$_lang['opt_goodnews.file']     = 'File';
$_lang['opt_goodnews.inline']   = 'Inline';
$_lang['opt_goodnews.embedded'] = 'Embedded';
$_lang['opt_goodnews.asc']      = 'Ascending';
$_lang['opt_goodnews.desc']     = 'Descending';
