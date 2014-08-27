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
 * cron.php is the Cron connector and GoodNews process manager
 *
 * @package goodnews
 */

set_time_limit(0);

// Fetch params of CLI calls and merge with URL params (for universal usage)
if(isset($_SERVER['argc'])) {
    if ($argc > 0) {
        for ($i=1; $i < $argc; $i++) {
            parse_str($argv[$i], $tmp);
            $_GET = array_merge($_GET, $tmp);
        }
    }
}

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');

// If set - connector script may only be continued if the correct security key is provided by cron (@param sid)
$securityKey = $modx->getOption('goodnews.cron_security_key', null, '');
if ($_GET['sid'] !== $securityKey) {
    exit('[GoodNews] cron.php: Missing or wrong authentification! Sorry Dude!');
}

$debug = $modx->getOption('goodnews.debug', null, false) ? true : false;

$workerProcessActive = $modx->getOption('goodnews.worker_process_active', null, 1);
if (!$workerProcessActive) { exit(); }

$corePath  = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/');
$assetsUrl = $modx->getOption('goodnews.assets_url', null, $modx->getOption('assets_url').'components/goodnews/');

require_once $corePath.'model/goodnews/goodnews.class.php';
$modx->goodnews = new GoodNews($modx);
if (!($modx->goodnews instanceof GoodNews)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] cron.php - Could not load GoodNews class.');
    exit();
}

require_once $corePath.'model/goodnews/goodnewsbmh.class.php';
$modx->bmh = new GoodNewsBounceMailHandler($modx);
if (!($modx->bmh instanceof GoodNewsBounceMailHandler)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] cron.php - Could not load GoodNewsBounceMailHandler class.');
    exit();
}

// If multi processing isn't available we directly send mails without a worker process
$workerProcessLimit = $modx->getOption('goodnews.worker_process_limit', null, 1);
if (!$modx->goodnews->isMultiProcessing || $workerProcessLimit <= 1) {

    require_once $corePath.'model/goodnews/goodnewsmailing.class.php';
    $modx->goodnewsmailing = new GoodNewsMailing($modx);
    if (!($modx->goodnewsmailing instanceof GoodNewsMailing)) {
        $modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] cron.php - Could not load GoodNewsMailing class.');
        exit();
    }
    
    $mailingsToSend = $modx->goodnewsmailing->getMailingsToSend();
    if (is_array($mailingsToSend)) {
        foreach ($mailingsToSend as $mailingid) {
            $modx->goodnewsmailing->processMailing($mailingid);
        }
    }

// Otherwise start multiple worker processes
} else {

    require_once $corePath.'model/goodnews/goodnewsprocesshandler.class.php';
    $modx->goodnewsprocesshandler = new GoodNewsProcessHandler($modx);
    if (!($modx->goodnewsprocesshandler instanceof GoodNewsProcessHandler)) {
        $modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] cron.php - Could not load GoodNewsProcessHandler class.');
        exit();
    }

    // Cleanup old processes and get count of actual running processes
    $actualProcessCount = $modx->goodnewsprocesshandler->cleanupProcessStatuses();
    if ($debug) { $modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] cron.php - Actual process count: '.$actualProcessCount); }
    
    while ($actualProcessCount < $workerProcessLimit) {
            
        $actualProcessCount++;
        $modx->goodnewsprocesshandler->setCommand('php '.rtrim(MODX_BASE_PATH, '/').$assetsUrl.'cron.worker.php sid='.$_GET['sid']);
        if (!$modx->goodnewsprocesshandler->start()) {
            if ($debug) { $modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] cron.php - No worker started.'); }
            break;
        } else {
            if ($debug) { $modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] cron.php - New worker started with pid: '.$modx->goodnewsprocesshandler->getPid().' | Start-time: '.$modx->goodnewsprocesshandler->getProcessStartTime()); }
        }
        // Wait a little before letting start another process
        sleep(2);
        if (!$modx->goodnewsprocesshandler->status()) {
            // If after this time no process is running, there are no more mailings to send 
            break;
        }
    }
}


bounceHandling($modx, $debug);
cleanUpSubscriptions($modx, $debug);


/**
 * Handle bounced messages.
 * 
 * @access public
 * @param mixed &$modx The modx object
 * @param bool $debug_bmh (default: false)
 * @return void
 */
function bounceHandling(&$modx, $debug_bmh = false) {
 
    $containerIDs = $modx->bmh->getGoodNewsBmhContainers();
    //$modx->log(modX::LOG_LEVEL_INFO,'[GoodNews] cron.php - mailing containers: '.print_r($containerIDs, true));
    if (!is_array($containerIDs)) {
        return false;
    }
    
    foreach ($containerIDs as $containerID) {
        $modx->bmh->getBmhContainerProperties($containerID);
        
        if ($debug_bmh) {
            $modx->bmh->debug             = true;
            $modx->bmh->maxMailsBatchsize = 20;
        }

        if ($modx->bmh->openImapStream()) {
            $modx->bmh->processMailbox();
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] cron.php - Connection to mailhost failed: '.$modx->bmh->mailMailHost);
            if (!empty($modx->bmh->errorMsg)) {
                $modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] cron.php - phpIMAP error message: '.$modx->bmh->errorMsg);
            }
        }
    }
    $modx->bmh->closeImapStream();
}

/**
 * Cleanup subscriptions ( = MODX users):
 * - delete subscriptions which were not activated within a specific time
 * 
 * @access public
 * @param mixed &$modx The modx object
 * @param bool $debug_bmh (default: false)
 * @return void || false
 */
function cleanUpSubscriptions(&$modx, $debug_bmh = false) {
    $autoCleanUpSubscriptions = $modx->getOption('goodnews.auto_cleanup_subscriptions', null, false) ? true : false;
    if (!$autoCleanUpSubscriptions) { return false; }
    
    $autoCleanUpSubscriptionsTtl = $modx->getOption('goodnews.auto_cleanup_subscriptions_ttl', null, 360);
    // convert UNIX timestamp value to ISO date (as "SubscriberMeta.createdon" is a date field)
    $expDate = date('Y-m-d H:i:s', time() - ($autoCleanUpSubscriptionsTtl * 60));

    $c = $modx->newQuery('modUser');
    $c->leftJoin('GoodNewsSubscriberMeta', 'SubscriberMeta', 'modUser.id = SubscriberMeta.subscriber_id');

    // modUser must:
    // - be inactive
    // - have a cachepwd (this means it's an unactivated account)
    // - not be in a MODX group
    // - must not be sudo
    // - have SubscriberMeta.createdon date < expiration date (GoodNews setting)
    $c->where(array(
        'active' => false,
        'cachepwd:!=' => '', 
        'primary_group' => 0,
        'sudo' => 0,
        'SubscriberMeta.createdon:<' => $expDate,
    ));
    
    $users = $modx->getIterator('modUser', $c);
    foreach ($users as $idx => $user) {
        if ($debug_bmh) {
            $modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] cron.php::cleanUpSubscriptions - user with ID: '.$user->get('id').' would be deleted.');
        } else {
            $user->remove();
        }
    }
}
