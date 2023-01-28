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

use MODX\Revolution\modX;
use MODX\Revolution\modUser;
use MODX\Revolution\Error\modError;
use MODX\Revolution\Registry\modRegistry;
use MODX\Revolution\Registry\modFileRegister;
use Bitego\GoodNews\GoodNews;
use Bitego\GoodNews\Mailer;
use Bitego\GoodNews\ProcessHandler;
use Bitego\GoodNews\BounceMailHandler\BounceMailHandler;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;

/**
 * cron.php is the Cron connector and GoodNews process manager
 *
 * @package goodnews
 */

set_time_limit(0);

// Fetch params of CLI calls and merge with URL params (for universal usage)
if (isset($_SERVER['argc'])) {
    $argc = $_SERVER['argc'];
    if ($argc > 0) {
        for ($i = 1; $i < $argc; $i++) {
            parse_str($argv[$i], $tmp);
            $_GET = array_merge($_GET, $tmp);
        }
    }
}

// Load MODX
define('MODX_API_MODE', true);
$root = dirname(__DIR__, 3) . '/';
require_once $root . 'config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';
/** @var modX $modx */
$modx = new modX();
$modx->initialize('mgr');
if (!$modx->services->has('error')) {
    $modx->services->add('error', function ($c) use ($modx) {
        return new modError($modx);
    });
}

// Security check:
// (if set - connector script may only be continued if the correct
// security key is provided by @param sid via CLI calls or URL params)
$sid = (string)isset($_GET['sid']) ? $_GET['sid'] : '';
$securityKey = (string)$modx->getOption('goodnews.cron_security_key', null, '');
if ($sid !== $securityKey) {
    $modx->log(modX::LOG_LEVEL_WARN, '[GoodNews] cron.php - Missing or wrong security key!');
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Set cron ping-time to modRegistry.
// (Will be read by ping processor called in home.panel.js)
if (!$modx->services->has('registry')) {
    $modx->services->add('registry', function ($c) use ($modx) {
        return new modRegistry($modx);
    });
}
$registry = $modx->services->get('registry');
$registry->addRegister('goodnewscron', modFileRegister::class);
$registry->goodnewscron->connect();
$registry->goodnewscron->subscribe('/ping/');
$registry->goodnewscron->send('/ping/', ['time' => time()]);

// Is the worker process active or blocked (via GoodNews CMP interface)?
$workerProcessActive = $modx->getOption('goodnews.worker_process_active', null, 1);
if (!$workerProcessActive) {
    exit();
}

// Debug mode?
$debug = $modx->getOption('goodnews.debug', null, false) ? true : false;

// Load GoodNews
/** @var GoodNews $goodnews */
$goodnews = new GoodNews($modx);
if (!($goodnews instanceof GoodNews)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] cron.php - Could not load GoodNews class.');
    exit();
}
$assetsPath = $goodnews->config['assetsPath'];

// Load BounceMailHandler
/** @var BounceMailHandler $goodnews */
$bmh = new BounceMailHandler($modx);
if (!($bmh instanceof BounceMailHandler)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] cron.php - Could not load BounceMailHandler class.');
    exit();
}

$workerProcessLimit = $modx->getOption('goodnews.worker_process_limit', null, 1);

if (!$goodnews->isMultiProcessing || $workerProcessLimit <= 1) {
    // If multi processing isn't available, directly send mails without a worker process
    /** @var Mailer $mailer */
    $mailer = new Mailer($modx);
    if (!($mailer instanceof Mailer)) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] cron.php - Could not load Mailer class.');
        exit();
    }

    $mailingsToSend = $mailer->getMailingsToSend();
    if (is_array($mailingsToSend)) {
        foreach ($mailingsToSend as $mailingid) {
            $mailer->processMailing($mailingid);
        }
    }
} else {
    // Otherwise start multiple worker processes
    /** @var ProcessHandler $processhandler */
    $processhandler = new ProcessHandler($modx);
    if (!($processhandler instanceof ProcessHandler)) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] cron.php - Could not load ProcessHandler class.');
        exit();
    }

    // Cleanup old processes and get count of actual running processes
    $actualProcessCount = $processhandler->cleanupProcessStatuses();
    if ($debug) {
        $modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] cron.php - Actual process count: ' . $actualProcessCount);
    }

    while ($actualProcessCount < $workerProcessLimit) {
        $actualProcessCount++;
        $processhandler->setCommand('php ' . $assetsPath . 'cron.worker.php sid=' . $sid);
        if (!$processhandler->start()) {
            if ($debug) {
                $modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] cron.php - No worker started.');
            }
            break;
        } else {
            if ($debug) {
                $modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] cron.php - New worker started with pid: ' .
                    $modx->goodnewsprocesshandler->getPid() .
                    ' | Start-time: ' .
                    $modx->goodnewsprocesshandler->getProcessStartTime()
                );
            }
        }
        // Wait a little before letting start another process
        sleep(2);
        if (!$processhandler->status()) {
            // If after this time no process is running, there are no more mailings to send
            break;
        }
    }
}

bounceHandling($modx, $bmh, $debug);
cleanUpSubscriptions($modx, $debug);

/**
 * Handle bounced messages.
 *
 * @access public
 * @param mixed &$modx The modX object
 * @param mixed &$bhm The BounceMailHandler object
 * @param bool $debug_bmh (default: false)
 * @return void
 */
function bounceHandling(&$modx, &$bmh, $debug_bmh = false)
{
    $containerIDs = $bmh->getGoodNewsBmhContainers();
    if (!is_array($containerIDs)) {
        return false;
    }

    foreach ($containerIDs as $containerID) {
        $bmh->getBmhContainerProperties($containerID);

        if ($debug_bmh) {
            $bmh->debug             = true;
            $bmh->maxMailsBatchsize = 20;
        }

        if ($bmh->openImapStream()) {
            $bmh->processMailbox();
        } else {
            $modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] cron.php - Connection to mailhost failed: ' . $bmh->mailMailHost
            );
            if (!empty($bmh->lastErrorMsg)) {
                $modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[GoodNews] cron.php - phpIMAP error message: ' . $bmh->lastErrorMsg
                );
            }
        }
    }
    $bmh->closeImapStream();
}

/**
 * Cleanup subscriptions ( = MODX users):
 * - delete subscriptions which were not activated within a specific time
 *
 * @access public
 * @param mixed &$modx The modX object
 * @param bool $debug_cs (default: false)
 * @return void || false
 */
function cleanUpSubscriptions(&$modx, $debug_cs = false)
{
    $autoCleanUpSubscriptions = $modx->getOption('goodnews.auto_cleanup_subscriptions', null, false)
        ? true
        : false;
    if (!$autoCleanUpSubscriptions) {
        return false;
    }

    $autoCleanUpSubscriptionsTtl = $modx->getOption('goodnews.auto_cleanup_subscriptions_ttl', null, 360);
    $expDate = time() - ($autoCleanUpSubscriptionsTtl * 60);

    $c = $modx->newQuery(modUser::class);
    $c->leftJoin(GoodNewsSubscriberMeta::class, 'SubscriberMeta', 'modUser.id = SubscriberMeta.subscriber_id');

    // modUser must:
    // - be inactive
    // - have a cachepwd (this means it's an unactivated account)
    // - not be in a MODX group
    // - must not be sudo
    // - have SubscriberMeta.subscribedon date < expiration date (GoodNews setting)
    $c->where([
        'active' => false,
        'cachepwd:!=' => '',
        'primary_group' => 0,
        'sudo' => 0,
        'SubscriberMeta.subscribedon:<' => $expDate,
    ]);

    $users = $modx->getIterator(modUser::class, $c);
    foreach ($users as $idx => $user) {
        if ($debug_cs) {
            $modx->log(
                modX::LOG_LEVEL_INFO,
                '[GoodNews] cron.php::cleanUpSubscriptions - User with ID: ' . $user->get('id') . ' would be deleted.'
            );
        } else {
            $user->remove();
        }
    }
}
