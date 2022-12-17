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
use MODX\Revolution\modSystemSetting;
use MODX\Revolution\modX;

/**
 * GoodNews settings update processor.
 *
 * @package goodnews
 * @subpackage processors
 */

class Update extends Processor
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

        foreach ($settings as $key) {
            $value = $this->getProperty($key);
            if (isset($value)) {
                $setting = $this->modx->getObject(modSystemSetting::class, 'goodnews.' . $key);
                if ($setting != null) {
                    $setting->set('value', $value);
                    $setting->save();
                } else {
                    $this->modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '[GoodNews] Settings Update processor: ' . $key . ' setting could not be found.'
                    );
                }
            }
        }

        // refresh part of cache (MODx 2.1.x)
        $cacheRefreshOptions = array('system_settings' => array());
        $this->modx->cacheManager->refresh($cacheRefreshOptions);

        $response['data'] = $this->getProperties();
        $response['success'] = true;

        return $this->modx->toJSON($response);
    }
}
