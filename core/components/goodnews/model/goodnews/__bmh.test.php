<?php
$time_start = microtime_float();

require_once dirname(dirname(dirname(__FILE__))).'/model/goodnews/goodnewsbmh.class.php';

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/config.core.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->setLogTarget('ECHO');

echo '<pre>';

// testing examples
$bmh = new GoodNewsBounceMailHandler($modx);

//$bmh->testmode            = true; // false is default, no need to specify
$bmh->debug_rules          = true;
$bmh->max_mails_batchsize  = 200;
//$bmh->disable_delete      = false; // false is default, no need to specify
//$bmh->purge_unprocessed   = false; // false is default, no need to specify

$bmh->mailService          = 'pop3'; // the service to use (imap or pop3), default is 'imap'
$bmh->mailMailHost         = 'mxv1.is1130.com'; // your mail server
$bmh->mailMailboxUsername  = 'noreply@bitego.com'; // your mailbox username
$bmh->mailMailboxPassword  = 'n0r3ply'; // your mailbox password
//$bmh->mailBoxname         = 'INBOX'; // the mailbox to access, default is 'INBOX'
$bmh->mailPort             = 110; // the port to access your mailbox, default is 143
$bmh->mailServiceOption    = 'tls'; // the service options (none, tls, notls, ssl, etc.), default is 'notls'

//$bmh->mailSoftBouncedMessageAction = 'delete';
//$bmh->mailSoftMailbox              = 'INBOX.Softbounces';
//$bmh->mailMaxSoftBounces           = 3;
//$bmh->mailMaxSoftBouncesAction     = 'disable';
//$bmh->mailHardBouncedMessageAction = 'delete';
//$bmh->mailHardMailbox              = 'INBOX.Hardbounces';
//$bmh->mailMaxHardBounces           = 1;
//$bmh->mailMaxHardBouncesAction     = 'delete';

if ($bmh->openMailbox()) {
    echo 'Connected to mailbox.<br><br>';

    echo $bmh->error_msg;
    
    $bmh->processMailbox();
    
    $time = time();
    //$bmh->addSubscriberBounce('2', $time, 'hard');
    $bmh->addSubscriberBounce('2', $time, 'soft');

    echo '<br>Hard bounces for subscriber with ID 2: '.$bmh->getSubscriberBounceCounter('2', 'hard').' -> Hours between: '.$bmh->getSubscriberBounceLag('2', 'hard');
    echo '<br>Soft bounces for subscriber with ID 2: '.$bmh->getSubscriberBounceCounter('2', 'soft').' -> Hours between: '.$bmh->getSubscriberBounceLag('2', 'soft');
    
    //$bmh->resetSubscriberBounces('2');

    $bmh->increaseMailingBounceCounter('322', 'soft');
    $bmh->increaseMailingBounceCounter('322', 'hard');
    
    echo '<br>';
    echo '<br>Found mails: '.$bmh->get_cTotal();
    echo '<br>Fetched mails: '.$bmh->get_cFetch();
    echo '<br>Processed mails: '.$bmh->get_cProcessed();
    echo '<br>Unprocessed mails: '.$bmh->get_cUnprocessed();
    echo '<br>Deleted mails: '.$bmh->get_cDeleted();
    echo '<br>Moved mails: '.$bmh->get_cMoved();
    echo '<br><br>';


} else {
    echo 'Connection to mailbox failed.<br><br>';
}

$bmh->closeMailbox();





$time_end = microtime_float();
$time = $time_end - $time_start;
echo 'Processing time: '.$time;

echo '</pre>';

function microtime_float() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}

?>