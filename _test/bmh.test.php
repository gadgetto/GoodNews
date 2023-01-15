<?php

use MODX\Revolution\modX;
use MODX\Revolution\Error\modError;
use Bitego\GoodNews\Mailer;
use Bitego\GoodNews\BounceMailHandler\BounceMailHandler;

$time_start = microtime_float();

/* Define paths */
$root = dirname(__DIR__, 1) . '/';

require_once $root . 'config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';

/* Load MODX */
$modx = new modX();
$modx->initialize('mgr');
if (!$modx->services->has('error')) {
    $modx->services->add('error', function ($c) use ($modx) {
        return new modError($modx);
    });
}
$modx->error = $modx->services->get('error');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
echo '<pre>';
flush();

$goodnews = $modx->services->get('goodnews');


/********** Start test-code **********/


// testing examples
$bmh = new BounceMailHandler($modx);

$bmh->testmode             = true;
$bmh->debug                = true;
$bmh->maxMailsBatchsize    = 200;
//$bmh->disableDelete        = false; // false is default, no need to specify

//$bmh->mailService          = 'pop3'; // the service to use (imap or pop3), default is 'imap'
//$bmh->mailPort             = 110; // the port to access your mailbox, default is 143
//$bmh->mailServiceOption    = 'tls'; // the service options (none, tls, notls, ssl, etc.), default is 'notls'

$bmh->mailService          = 'imap'; // the service to use (imap or pop3), default is 'imap'
$bmh->mailPort             = 993; // the port to access your mailbox, default is 143
$bmh->mailServiceOption    = 'ssl'; // the service options (none, tls, notls, ssl, etc.), default is 'notls'

$bmh->mailBoxname          = 'INBOX'; // the mailbox to access, default is 'INBOX'

$bmh->mailMailHost         = ''; // your mail server
$bmh->mailMailboxUsername  = ''; // your mailbox username
$bmh->mailMailboxPassword  = ''; // your mailbox password

//$bmh->mailSoftBouncedMessageAction = 'delete';
//$bmh->mailSoftMailbox              = 'INBOX.Softbounces';
//$bmh->mailMaxSoftBounces           = 3;
//$bmh->mailMaxSoftBouncesAction     = 'disable';
//$bmh->mailHardBouncedMessageAction = 'delete';
//$bmh->mailHardMailbox              = 'INBOX.Hardbounces';
//$bmh->mailMaxHardBounces           = 1;
//$bmh->mailMaxHardBouncesAction     = 'delete';


//$bmh->mailboxFolderExists('INBOX.Test');
echo $bmh->lastErrorMsg;

if (!$bmh->openImapStream()) {
    echo 'Connection to mailbox failed. :-(<br><br>';
} else {
    echo 'Connected to mailbox.<br><br>';

    echo $bmh->lastErrorMsg;

    //$bmh->processMailbox();

    //$time = time();
    //$bmh->addSubscriberBounce('7846', $time, 'hard');
    //$bmh->addSubscriberBounce('6816', $time, 'soft');

    //echo '<br>Hard bounces for subscriber with ID 2: ' .
    //    $bmh->getSubscriberBounceCounter('2', 'hard') .
    //    ' -> Hours between: ' .
    //    $bmh->getSubscriberBounceLag('2', 'hard');
    //echo '<br>Soft bounces for subscriber with ID 2: ' .
    //    $bmh->getSubscriberBounceCounter('2', 'soft') .
    //    ' -> Hours between: ' .
    //    $bmh->getSubscriberBounceLag('2', 'soft');

    //$bmh->resetSubscriberBounces('2');

    //$bmh->increaseMailingBounceCounter('322', 'soft');
    //$bmh->increaseMailingBounceCounter('322', 'hard');

    //$email = 'gadgetto66@gmail.com';
    //echo '<br>Subscriber ID for email address ' . $email . ': ' . $bmh->getSubscriberID($email);

    //$bmh->disableSubscriber(7846);
    //$bmh->disableSubscriber(7847);

    //$bmh->deleteSubscriber(8879);

    //$containerIDs = $bmh->getGoodNewsBmhContainers();
    //echo 'Containers with activated bounce handling:<br>';
    //echo print_r($containerIDs, true);


    echo '<br>';
    echo '<br>Found mails: ' . $bmh->getcTotal();
    echo '<br>Fetched mails: ' . $bmh->getcFetch();
    echo '<br>Classified mails: ' . $bmh->getcClassified();
    echo '<br>Unclassified mails: ' . $bmh->getcUnclassified();
    echo '<br>Deleted mails: ' . $bmh->getcDeleted();
    echo '<br>Moved mails: ' . $bmh->getcMoved();
    echo '<br><br>';

    $bmh->closeImapStream();
}


/********** End test-code **********/

$time_end = microtime_float();
$time = $time_end - $time_start;
echo '<br><br>';
echo 'Processing time: ' . $time;
echo '</pre>';

function microtime_float()
{
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}
