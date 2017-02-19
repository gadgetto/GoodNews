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
 * GoodNews settings get processor
 *
 * @package goodnews
 * @subpackage processors
 */

class GetSettingsProcessor extends modProcessor {

    public function process() {

        // get cached versions of system settings
        $settings = array(
            'test_subject_prefix'            => $this->modx->getOption('goodnews.test_subject_prefix'),
            'statusemail_enabled'            => (int)$this->modx->getOption('goodnews.statusemail_enabled'),
            'statusemail_fromname'           => $this->modx->getOption('goodnews.statusemail_fromname'),
            'statusemail_chunk'              => $this->modx->getOption('goodnews.statusemail_chunk'),
            'admin_groups'                   => $this->modx->getOption('goodnews.admin_groups'),
            // integer typecasting required to work with MODExt.xcheckbox field
            'auto_fix_imagesizes'            => (int)$this->modx->getOption('goodnews.auto_fix_imagesizes'),
            // integer typecasting required to work with MODExt.xcheckbox field
            'auto_full_urls'                 => (int)$this->modx->getOption('goodnews.auto_full_urls'),
            // integer typecasting required to work with MODExt.xcheckbox field
            'auto_cleanup_subscriptions'     => (int)$this->modx->getOption('goodnews.auto_cleanup_subscriptions'),
            'auto_cleanup_subscriptions_ttl' => (int)$this->modx->getOption('goodnews.auto_cleanup_subscriptions_ttl'),
            // integer typecasting required to work with ExtJS.SliderField
            'mailing_bulk_size'              => (int)$this->modx->getOption('goodnews.mailing_bulk_size'),
            // integer typecasting required to work with ExtJS.SliderField
            'worker_process_limit'           => (int)$this->modx->getOption('goodnews.worker_process_limit'),
            // integer typecasting required to work with MODExt.xcheckbox field
            'worker_process_active'          => (int)$this->modx->getOption('goodnews.worker_process_active'),
            'cron_security_key'              => $this->modx->getOption('goodnews.cron_security_key'),
        );        
        $response['success'] = true;
        $response['data'] = $settings;
        
        return $this->modx->toJSON($response);
    }

}
return 'GetSettingsProcessor';
