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

namespace Bitego\GoodNews\Processors\Settings;

use MODX\Revolution\Processors\Processor;

/**
 * GoodNews settings get processor.
 *
 * @package goodnews
 * @subpackage processors
 */

class Get extends Processor
{
    public function process()
    {
        $settings = [
            'test_subject_prefix',
            'statusemail_enabled',
            'statusemail_fromname',
            'statusemail_chunk',
            'admin_groups',
            'auto_fix_imagesizes',
            'auto_full_urls',
            'auto_inline_css',
            'auto_cleanup_subscriptions',
            'auto_cleanup_subscriptions_ttl',
            'mailing_bulk_size',
            'worker_process_limit',
            'worker_process_active',
            'cron_security_key',
        ];

        // Get cached versions of system settings (getOption not getObject::modSystemSetting)
        // (MODExt.xcheckbox field needs integer typecasting)
        $data = [];
        foreach ($settings as $key) {
            $option = $this->modx->getOption('goodnews.' . $key);
            $option = is_numeric($option) ? (int)$option : $option;
            $data[$key] = $option;
        }

        $response['data'] = $data;
        $response['success'] = true;

        return $this->modx->toJSON($response);
    }
}
