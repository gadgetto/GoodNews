<?php
/**
 * GoodNews
 *
 * Copyright 2022 by bitego <office@bitego.com>
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

$settings['goodnews.test_subject_prefix'] = $modx->newObject('modSystemSetting');
$settings['goodnews.test_subject_prefix']->fromArray([
    'key'       => 'goodnews.test_subject_prefix',
    'value'     => '[TESTMAILING] ',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.statusemail_enabled'] = $modx->newObject('modSystemSetting');
$settings['goodnews.statusemail_enabled']->fromArray([
    'key'       => 'goodnews.statusemail_enabled',
    'value'     => '1',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.statusemail_fromname'] = $modx->newObject('modSystemSetting');
$settings['goodnews.statusemail_fromname']->fromArray([
    'key'       => 'goodnews.statusemail_fromname',
    'value'     => 'GoodNews Reporter',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.statusemail_chunk'] = $modx->newObject('modSystemSetting');
$settings['goodnews.statusemail_chunk']->fromArray([
    'key'       => 'goodnews.statusemail_chunk',
    'value'     => 'sample.GoodNewsStatusEmailChunk',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_fix_imagesizes'] = $modx->newObject('modSystemSetting');
$settings['goodnews.auto_fix_imagesizes']->fromArray([
    'key'       => 'goodnews.auto_fix_imagesizes',
    'value'     => '0',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_full_urls'] = $modx->newObject('modSystemSetting');
$settings['goodnews.auto_full_urls']->fromArray([
    'key'       => 'goodnews.auto_full_urls',
    'value'     => '1',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_inline_css'] = $modx->newObject('modSystemSetting');
$settings['goodnews.auto_inline_css']->fromArray([
    'key'       => 'goodnews.auto_inline_css',
    'value'     => '1',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_cleanup_subscriptions'] = $modx->newObject('modSystemSetting');
$settings['goodnews.auto_cleanup_subscriptions']->fromArray([
    'key'       => 'goodnews.auto_cleanup_subscriptions',
    'value'     => '0',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.auto_cleanup_subscriptions_ttl'] = $modx->newObject('modSystemSetting');
$settings['goodnews.auto_cleanup_subscriptions_ttl']->fromArray([
    'key'       => 'goodnews.auto_cleanup_subscriptions_ttl',
    'value'     => '360',
    'xtype'     => 'numberfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.mailing_bulk_size'] = $modx->newObject('modSystemSetting');
$settings['goodnews.mailing_bulk_size']->fromArray([
    'key'       => 'goodnews.mailing_bulk_size',
    'value'     => '30',
    'xtype'     => 'numberfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.worker_process_active'] = $modx->newObject('modSystemSetting');
$settings['goodnews.worker_process_active']->fromArray([
    'key'       => 'goodnews.worker_process_active',
    'value'     => '1',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.worker_process_limit'] = $modx->newObject('modSystemSetting');
$settings['goodnews.worker_process_limit']->fromArray([
    'key'       => 'goodnews.worker_process_limit',
    'value'     => '4',
    'xtype'     => 'numberfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.admin_groups'] = $modx->newObject('modSystemSetting');
$settings['goodnews.admin_groups']->fromArray([
    'key'       => 'goodnews.admin_groups',
    'value'     => 'Administrator',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.cron_security_key'] = $modx->newObject('modSystemSetting');
$settings['goodnews.cron_security_key']->fromArray([
    'key'       => 'goodnews.cron_security_key',
    'value'     => '',
    'xtype'     => 'textfield',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.default_container_template'] = $modx->newObject('modSystemSetting');
$settings['goodnews.default_container_template']->fromArray([
    'key'       => 'goodnews.default_container_template',
    'value'     => '0',
    'xtype'     => 'modx-combo-template',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

$settings['goodnews.debug'] = $modx->newObject('modSystemSetting');
$settings['goodnews.debug']->fromArray([
    'key'       => 'goodnews.debug',
    'value'     => '0',
    'xtype'     => 'combo-boolean',
    'namespace' => 'goodnews',
    'area'      => '',
], '', true, true);

return $settings;
