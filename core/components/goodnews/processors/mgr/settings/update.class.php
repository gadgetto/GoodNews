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
 * GoodNews settings update processor
 *
 * @package goodnews
 * @subpackage processors
 */

class SettingsUpdateProcessor extends modProcessor {

    public function process() {

        $settings = array(
            'test_subject_prefix',
            'admin_groups',
            'auto_fix_imagesizes',
            'auto_cleanup_subscriptions',
            'auto_cleanup_subscriptions_ttl',
            'mailing_bulk_size',
            'worker_process_limit',
            'worker_process_active',
            'cron_security_key',
        );

        foreach ($settings as $key) {
            $value = $this->getProperty($key);
            if (isset($value)) {
                $setting = $this->modx->getObject('modSystemSetting', 'goodnews.'.$key);
                if ($setting != null) {
                    $setting->set('value', $value);
                    $setting->save();
                } else {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] SettingsUpdateProcessor: '.$key.' setting could not be found');
                }
            }            
        }
        
        // refresh part of cache (MODx 2.1.x)
        $cacheRefreshOptions = array('system_settings' => array());
        $this->modx->cacheManager->refresh($cacheRefreshOptions);

        $response['success'] = true;
        $response['data'] = $this->getProperties();
        
        return $this->modx->toJSON($response);
    }

}
return 'SettingsUpdateProcessor';