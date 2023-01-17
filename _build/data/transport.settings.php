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

use MODX\Revolution\modSystemSetting;

/**
 * Add system settings to package.
 *
 * @var modX $modx
 * @var array $settings
 *
 * @package goodnews
 * @subpackage build
 */

$settings = [];

$settings['goodnews.test_subject_prefix'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.test_subject_prefix']->fromArray([
    'key'       => 'goodnews.test_subject_prefix',
    'value'     => '[TESTMAILING] ',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.statusemail_enabled'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.statusemail_enabled']->fromArray([
    'key'       => 'goodnews.statusemail_enabled',
    'value'     => '1',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.statusemail_fromname'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.statusemail_fromname']->fromArray([
    'key'       => 'goodnews.statusemail_fromname',
    'value'     => 'GoodNews Reporter',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.statusemail_chunk'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.statusemail_chunk']->fromArray([
    'key'       => 'goodnews.statusemail_chunk',
    'value'     => 'sample.GoodNewsStatusEmailChunk',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_fix_imagesizes'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.auto_fix_imagesizes']->fromArray([
    'key'       => 'goodnews.auto_fix_imagesizes',
    'value'     => '0',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_full_urls'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.auto_full_urls']->fromArray([
    'key'       => 'goodnews.auto_full_urls',
    'value'     => '1',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_inline_css'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.auto_inline_css']->fromArray([
    'key'       => 'goodnews.auto_inline_css',
    'value'     => '1',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_cleanup_subscriptions'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.auto_cleanup_subscriptions']->fromArray([
    'key'       => 'goodnews.auto_cleanup_subscriptions',
    'value'     => '0',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_cleanup_subscriptions_ttl'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.auto_cleanup_subscriptions_ttl']->fromArray([
    'key'       => 'goodnews.auto_cleanup_subscriptions_ttl',
    'value'     => '360',
    'xtype'     => 'numberfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.mailing_bulk_size'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.mailing_bulk_size']->fromArray([
    'key'       => 'goodnews.mailing_bulk_size',
    'value'     => '30',
    'xtype'     => 'numberfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.worker_process_active'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.worker_process_active']->fromArray([
    'key'       => 'goodnews.worker_process_active',
    'value'     => '1',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.worker_process_limit'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.worker_process_limit']->fromArray([
    'key'       => 'goodnews.worker_process_limit',
    'value'     => '4',
    'xtype'     => 'numberfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.admin_groups'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.admin_groups']->fromArray([
    'key'       => 'goodnews.admin_groups',
    'value'     => 'Administrator',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.cron_security_key'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.cron_security_key']->fromArray([
    'key'       => 'goodnews.cron_security_key',
    'value'     => '',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.default_container_template'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.default_container_template']->fromArray([
    'key'       => 'goodnews.default_container_template',
    'value'     => '0',
    'xtype'     => 'modx-combo-template',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.debug'] = $modx->newObject(modSystemSetting::class);
$settings['goodnews.debug']->fromArray([
    'key'       => 'goodnews.debug',
    'value'     => '0',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

return $settings;
