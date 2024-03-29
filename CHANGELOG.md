# Changelog for GoodNews
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [2.0.0-alpha2] - 2023-03-03
### Changed
- Import subscribers feature is now maintained by additional User Import add-on
- Import button only available when User Import add-on is installed

## [2.0.0-alpha1] - 2023-01-31
### Added
- [#50] Implemented detailed error text in send-log

### Changed
- Completely refactored for MODX 3 (as standalone version; currently no upgrade path!)
- Moved translations to Crowdin

### Fixed
- Fixed deprecated PHP each() usage for PHP8+ in snippets (GoodNewsGetNewsletters and GoodNewsContentCollection)
- Removed deprecated use of strftime (PHP8+)
- Security: changed registry key to md5 hash in subscription process

## [1.6.1-pl] - 2022-11-20
### Added
- Added bootstrap script for setting up GoodNews development environment

## [1.6.0-pl2] - 2022-11-19
### Changed
- Reworked package builder and installation process

### Fixed
- Fixed a warning in modx log (undefined variable in GoodNews class)

## [1.6.0-pl] - 2020-05-18
### Added
- Added link_tag_scheme to GoodNewsGetNewsletters Snippet
- Added help buttons in GoodNews resources (container, mailing)

### Changed
- [#66] Updated controllers to remove deprecated use of modAction
- Updated installer and package builder to remove deprecated use of modAction
- Changed GoodNews help URL (new docs site)
- [#79] Allow password placeholder to be used in GoodNewsSubscription snippet

### Removed
- Removed fileuploadfield.js utility (already included with MODX > 2.3)
- Removed mgr.css (no longer needed with MODX > 2.3)
- Removed legacyMode
- Removed deprecated use of modAction in js files

### Fixed
- Fixed page header in mailing editor
- Fixed undefined variable notice in cron.php
- Fixed some old ExtJS legacy code for resource panels (GoodNewsResourceContainer, GoodNewsResourceMailing)
- [#65] Fixed group names of subscribers groups surrounded by spans

## [1.5.1-pl2] - 2018-07-26
### Fixed
- Fixed icon in task scheduler status display

## [1.5.1-pl] - 2018-07-26
### Added
- Added task scheduler status display in action toolbar (seconds since last touch by task scheduler job)

### Removed
- Removed add-on version number from action toolbar

## [1.5.0-pl] - 2018-07-23
### Added
- General GDPR compliance release (1st-step)!
- Added new GDPR fields (activatedon, ip_activated) to subscribers export
- Implemented a flexible setup/initialization error handling
- Added a CMP for error handling
- [#60] Proper handling for "no GoodNews container available" situations
- Proper handling for "access denied" situations
- Make automatic inlining of CSS a setting (Gmail now supports embedded CSS, which removes the need for inline CSS in Gmail)
- Added sample Privacy Policy and terms and Conditions resources to installer
- [#61] Extended subscription process to also log activation IP + timestamp (GDPR compliance)

### Changed
- Newsletters grid initially loads with autorefresh enabled
- Enhanced automatic multipart mail generation (plain-text part of mail is now perfectly formatted and respects full mail body)
- Refactored preinstalled sample newsletter templates for simplicity and clean design
- Refactored all email-template chunks (for subscription, profile and reports) for simplicity and clean design
- Changed autorefresh and workerprocess-emergency-stop buttons from splitbutton to modern chebox toggle switches
- First changes for better MODX 3 compatibility
- Better UI for auto-refresh button
- Better UI/UX for emergency stop of worker processes
- Remastered the initialization process
- [#63] Refactored all sample resource documents for GDPR compliance + fresh and clean HTML5

### Fixed
- Fixed a problem with opening preview window for plain text mailings
- Fixing a small bug in email template processing (sometimes closing </body></html> tags were cropped)
- Fixed flickering in groups and categories grid on tabchange (caused by forced refresh)
- Finally fixing all collapse problems on grid refresh when rowExpander is enabled (thanks @Alroniks)
- Fixed a problem where grid refresh doesn’t properly collapse expanded rows
- Fixed a PHP warning in full URL conversion class
- Fixed user related placeholders for subscription/activation email templates (now all placeholders from user object are provided)

## [1.4.8-pl] - 2018-02-16
### Added
- [#39] Added subscribers export feature
- [#6] Added bulk actions to susbcribers grid

### Changed
- Refined subscribers grid display (more details, better differentiability)

### Fixed
- Fixed a critical bug in subscriber authentication (as a result from a merge conflict)

## [1.4.7-pl2] - 2017-12-13
### Fixed
- Fixed edit icon in grid row in Settings -> Container grid

## [1.4.7-pl] - 2017-12-12
### Added
- Added edit icon to grid row in Settings -> Container grid
- Added feature to use GoodNewsUpdateProfile Snippet for logged in MODX users (not only via sid URL param)

### Changed
- Send mail method now also respects mail port setting from MODX system settings

### Removed
- Removed path and TV resolvers from setup process

### Fixed
- [#56] Fixed broken chunksPath in goodnewssubscription.class.php
- Fixed some typos in lexicons
- [#57] Exlude mailto links from link manipulation (_fullURLs method)
- [#54] Fixed typos in sample.GoodNewsSubscriptionBoxTpl Chunk
- [#53] Fixed typo in requestlinks.class.php
- [#52] Fixed typo in sample.GoodNewsContentCollectionRowTpl
- [#51] Fixed typos in LOG_LEVEL_WARN constant
- Fixed a problem in Registration/Subscription process where username wasn't checked before trying to create user

## [1.4.6-pl] - 2017-03-10
### Removed
- Removed permission check for „Toggle send process“
- Removed grid flickering (grid mask) when auto-refresh is activated

### Fixed
- Fixed a problem where Subscriber placeholders in newsletter templates were URL encoded before processing
- Fixed a PHP warning: Undefined variable: base
- Fixed a problem where GoodNews groups which were assigned to MODX user groups couldn't be unassigned

## [1.4.5-pl] - 2017-02-19
### Added
- Added setup options to package installer to enable/disable installation of sample Resource documents
- Added system setting to enable/disable automatic URL conversion in email body
- [#17] Added newsletter send-log viewer and log-file export to CSV

### Changed
- [#43] complete rewrite of automatic full URL conversion (now respects #anchor links external links and protocol-relative URLs)
- Refined Package installer and uninstaller to output more usefull informations
- "Save" button in mailing editor has now MODX primary button color
- Changed row expander icons for newsletter and subscribers grid to make it clear that rows can be expanded
- Mail subjects are now converted to charset of mailing (contributed by @mindeffects)

### Removed
- Removed superfluous default 'isset' parameter from getProperty calls (fix by @Jako taken from Register snippet/Login package)

### Fixed
- Exclude blocked MODX users from recipients collection
- Fixed a problem in Subscription controller when &usernameField property is set
- Fixed errors in package uninstall process
- Fixed "Created by" dropdown username display in mailing editor
- Fixed a problem where selecting Collection rows didn't fire a resource form change ("Save" button wasn't activated)
- Fixed issues with array fields in GoodNewsUpdateProfile snippet (fix by @Jako taken from Register snippet/Login package)
- Fixed a very stupid and old bug in lock-file handling
- Fixed a missing index log entry in cron.php
- Fixed a problem (since MODX 2.5.2) in goodnews.class.php constructor when executed in CLI mode
- Fixed issues with array fields in GoodNewsSubscription snippet (fix by @Jako taken from Register snippet/Login package)

## [1.4.2-pl] - 2016-10-29
### Fixed
- Fixed subscriber "Created on" problem (conflicting field names since MODX 1.5.x users have an explicit createdon field)
- Fixed emptying cachepwd field when subscription is confirmed
- Fixed sending activation email if activationEmail property is not set
- Fixed a problem with detecting extended fields in form
- Fixed Custom Resource Properties Resolver in GoodNews container installation (unsubscribeResource and profileResource properties)
- Fixed template assignement for sample resources on package upgrade
- Corrected poor thinking in registration-confirm resource

## [1.4.1-pl] - 2016-03-02
### Added
- Added all missing custom validators from Login extra
- Added snippets, resources and chunks to allow full MODX user registration including newsletter subscription on a single page
- [#3] Send a status email to newsletter sender if mailing status changes

### Changed
- Even more refined subscription process
- [#40] Newsletter templates now have full access to all modUserProfile and GoodNewsSubscriberMeta fields as placeholders
- Renamed preinstalled Resource domcuments to prevent overwriting of existing

### Fixed
- Missing snippet params dont break Subscription, RequestLinks and ConfirmSubscription snippets any longer
- Fixed a bug with tvs_below_content setting in Mailing editor
- Preinstalled Resource documents aren't moved to tree root on package update (Installer)
- [#38] Fixed a php warning in processhandler class

## [1.4.0-pl] - 2015-11-05
### Added
- New professional system mail templates (activation, success, request links, etc.)
- [#22] Enable subscription of users which already have an account in the MODX instance
- [#21] Enable re-subscription of users with cancelled or forgotten subscriptions

### Changed
- Completely revised the Subscriber registration system (front-end)

### Fixed
- Fixed a few nasty bugs in Susbcriber registration
- fixed Security/privacy problems (now front-end forms will never tell if email addresses already exists)

## [1.3.9-pl] - 2015-10-23
### Added
- Added "public" field to Groups (same behaviour as in Categories)

### Changed
- Changed behaviour of "public" field for Categories and makes it possible to send newsletters to non-public Categories

### Fixed
- Improved robustness of mail processing
- Fixed a nasty bug with lockfile handling + recipient status update
- Fixed error messages in GoodNews installer while updating database schema (...field allready exists)

## [1.3.8-pl] - 2015-09-13
### Added
- Added Activated filter to Subscribers grid
- Added plugin version and icons to action-toolbar
- [#34] Added Category filter to Subscribers grid
- Added action buttons to newsletters grid

### Changed
- Reordered newsletter grid context menu (now it should be more logical)
- The Mail Summary field is now able to grow in it's height when text is entered
- Refined some lexicon strings

### Removed
- Removed/disabled Delete action (menu and button) from mailing when sending is in progress

### Fixed
- Fixed a bug in combining filters on Subscribers grid
- [#35] Fixed a problem with send counter when timed out mails are detected
- Small graphical fixes

## [1.3.7-pl] - 2015-06-29
### Added
- Added preset for Mandrill SMTP service settings
- Added feature to let each container have it's own SMTP settings (overrides MODX system settings)
- Added feature to let each container have it's own mail charset and mail encoding settings (overrides MODX system settings)

### Fixed
- Fixed a PHP warning in MODX error log

## [1.3.6-pl] - 2015-03-29
### Added
- Added French translation (thank's Julien Studer!)
- Added indicator in mailing editor if mailing is in read-only mode (sending already started)
- [#33] Integrate functionality to auto-detect images and convert physical image dimensions based on src or style attributes

### Removed
- Removed ContentCollectionSnippet output if collection is empty

### Fixed
- Fixed missing english strings + small typo in lexicon
- Fixed a bug when sorting the Subscribers grid by subscription date
- Fixed waring message when mailing resource is in read-only mode
- Fixed some PHP warnings if collections field of mailing_meta is empty (happens for resources prior to version 1.3.0-pl)
- Fixed a problem in ContentCollection snippet when using tplWrapper

## [1.3.5-pl] - 2014-12-10
### Added
- Added ability to click on newsletter title to edit the newsletter
- Added a switch above Newsletters grid to quickly activate/deactivate send-processes
- [#30] Added indicator in GoodNews - System Checks for requirement of PHP versions > 5.3.0

### Changed
- [#32] Add back an indicator for displaying if sending processes are activated/deactivated in settings

### Fixed
- Fixed a problem in System settings when Admin or Editor group names have leading or trailing spaces
- Fixed a bug with slider value displays in GoodNews - System settings
- Fixed a bug in resource rendering (this was a tricky one!)

## [1.3.4-pl] - 2014-12-03
### Added
- Added container filter dropdown in Resource Collections grid (better user experience)
- Added PHP 5.3 (or later) requirement to readme.txt

### Removed
- Removed grouping by container in Resource Collections grid

### Fixed
- Fixed a problem where Resource Collections aren't restored correctly from database
- Again fixed another small bug in how cron security keys are checked :)
- Fixed a problem where Resource Collections are empty on first load (no Collections array available in mailing_meta)

## [1.3.3-pl] - 2014-11-16
### Added
- [#5] Render warning message to GoodNews forms if Snippets are mis-configured

### Changed
- Improved username creation in Subscriber Importer (we now use the userid part of the email address)

### Fixed
- Fixed an issue with checkboxes causing text-parsing problems when using the "required" validator
- Fixed a few PHP warnings in goodnewssubscriptioncontroller.class.php
- When updating the subscription profile: update form fields with new values when reloadOnSuccess = false
- [#29] Frontend: groups checkboxes output is falsely rendered inside html form fieldset for each single group
- [#28] Frontend: Subscription snippet - crashes during validation if email field is empty

## [1.3.2-pl] - 2014-11-01
### Changed
- Small lexicon adjustments to better match MODX 2.3

### Fixed
- [#27] Subscriber meta data not created for existing MODX users when using the "Import Update Feature"
- [#26] Editing a GoodNews Container - mailing templates category dropdown doesn't list all Categories

## [1.3.1-pl] - 2014-10-28
### Added
- [#25] Extend Subscriber Importer to enable "Update" of existing subscribers

### Changed
- GoodNews management interface now takes account of MODX manager date and time format settings

### Fixed
- Fixed a bug in Import console window (console window is now destroyed after closing)
- Fixed a bug in German lexicon file (missing semicolon could lead to white page -> sorry for this!)
- Deleted mailing editor users are now recognized and it's id is now shown instead empty string

## [1.3.0-pl] - 2014-10-13
### Added
- [#15] New feature: Content collector

## [1.2.2-pl] - 2014-09-15
### Changed
- Some small cosmetical changes in forms

## [1.2.1-pl] - 2014-09-14
### Added
- Added debug-mode status to System Checks table

### Changed
- Adapted searchfilter fields + reset buttons above grids to match MODX 2.3 style

### Fixed
- [#24] Fixed rendering of grids in tab panels (resizing problem)
- Fixed some PHP warnings in getgroupcatnodes.class.php
- Fixed some IMAP warnings in bounce handler

## [1.2.0-pl] - 2014-09-09
### Added
- Added sending error counter to mailing grid

### Changed
- Complete rewrite on how the sending engine handles recipients!
- Huge performance increase and much better memory management for sending engine
- Many other small memory and performance related enhancements

### Fixed
- [#23] Fixed PHP memory limit problem on server with huge list of subscribers

## [1.1.7-pl] - 2014-09-02
### Added
- [#20] New feature to manually disable multiprocessing
- Added error handler loading to cron.php and cron.worker.php (needed since MODX 2.3+)

### Fixed
- Fixed another small bug in how cron security keys are checked :)

## [1.1.6-pl] - 2014-08-24
### Added
- [#19] New feature to request secure links via email
- Added feature to manually reset bounce counters of a subscriber
- Added row-expander to subscribers grid for detailed informations and save grid space

### Changed
- Fileuploadfield extension only needs to be loaded in MODX < 2.3 (natively supported in MODX >= 2.3)
- Cosmetical changes to Groups/Categories tree
- Moved  subscriber id to row epander
- Moved  subscriber ip address to row epander

### Removed
- Removed custom form field description styles - using default MODx style

### Fixed
- Fixed wrong width of "Created By" combo field in mailing editor
- Small corrections/changes in lexicon strings

### Security
- Security fix for subscribers getlist processor

## [1.1.5-pl] - 2014-07-25
### Changed
- Small corrections/changes in lexicon strings
- Changed Auto-Refresh toggle to ExtJs Cycle element for better UX
- Other small cosmetical changes to match new Revo 2.3 manager skin
- Removed Cron trigger status field (and make worker_process_active == true by default)

### Fixed
- Additional CSS fixes (because of last minute changes in Revo 2.3)

## [1.1.4-pl] - 2014-07-20
### Added
- Added contentblocks_replacement class to make GoodNews compatible with ContentBlocks 1.1

### Fixed
- [#12] You have changes pending; are you sure you want to cancel?

## [1.1.3-pl] - 2014-07-17
### Added
- Implemented a legacyMode flag for detecting older MODX versions (< 2.3)

### Removed
- Removed version_compare JS plugin and all it's references

## [1.1.2-pl] - 2014-07-14
### Changed
- Changed default bounce handling settings to more secure values

### Fixed
- Fixed some installation issues (missing or wrong default settings, default container template)

## [1.1.1-pl] - 2014-07-09
### Added
- Added SB + HB column to Subscribers grid

### Fixed
- Fixed deleteSubscribers + updateSubscribers methods (also added groupmember and sudo check!)

## [1.1.0-pl] - 2014-07-09
### Added
- Added automatic bounce handling (parsing bounced emails and automatically handle subscriber status)
- Added system check for PHP Imap extension
- Added auto-cleanup of susbcriptions (remove never activated accounts)
- Added ID column to Subscribers grid

### Changed
- Moved all protected container settings into GoodNews system settings

## [1.0.4-pl] - 2014-06-16
### Fixed
- [#11] Compatibility problems with Revolution >= 2.3

## [1.0.3-pl] - 2014-06-10
### Fixed
- [#10] cron.php cannot be called without the sid parameter when security key setting is disabled
- [#9] cron.worker.php throws a warning to the MODX error log (Invalid argument supplied for foreach())

## [1.0.2-pl] - 2014-06-08
### Fixed
- [#7] Mailing: selection of subscribers in a category will erroneously also select the full group

## [1.0.1-pl]
### Added
- Added feature/properties to optionally send user an email after successful subscription
- Added preinstalled Resource for successful subscriptions when activation isn't required

## [1.0.0-pl]
### Changed
- First stable release

## [1.0.0-beta3]
### Added
- Added subscribers ip tracking
- Added subscription box chunk to place subscription form somewhere on site

### Fixed
- Fixed missing security check if user is entitled to manage GoodNews container

## [1.0.0-beta2]
### Added
- Added necessary code to provide support for Revo version >= 2.3
- Added Revo version detection for backwards compatibillity

### Fixed
- Fixed a bug in multiprocessed sending (catch some rare race conditions)

## [1.0.0-beta1]
### Added
- First public beta release
