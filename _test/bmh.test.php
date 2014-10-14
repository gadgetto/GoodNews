<?php
$time_start = microtime_float();

/* Define package name */
define('PKG_NAME', 'GoodNews');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));

/* Define paths */
$root = dirname(dirname(__FILE__)).'/';
$sources = array(
    'root'           => $root,
    'build'          => $root.'_build/',
    'includes'       => $root.'_build/includes/',
    'data'           => $root.'_build/data/',
    'events'         => $root.'_build/data/events/',    
    'properties'     => $root.'_build/properties/',
    'resolvers'      => $root.'_build/resolvers/',
    'packages'       => $root.'_packages/',
    'chunks'         => $root.'core/components/'.PKG_NAME_LOWER.'/elements/chunks/',
    'plugins'        => $root.'core/components/'.PKG_NAME_LOWER.'/elements/plugins/',
    'resources'      => $root.'core/components/'.PKG_NAME_LOWER.'/elements/resources/',
    'snippets'       => $root.'core/components/'.PKG_NAME_LOWER.'/elements/snippets/',
    'templates'      => $root.'core/components/'.PKG_NAME_LOWER.'/elements/templates/',
    'lexicon'        => $root.'core/components/'.PKG_NAME_LOWER.'/lexicon/',
    'docs'           => $root.'core/components/'.PKG_NAME_LOWER.'/docs/',
    'source_core'    => $root.'core/components/'.PKG_NAME_LOWER,
    'source_assets'  => $root.'assets/components/'.PKG_NAME_LOWER,
);
unset($root);

require_once $sources['root'].'config.core.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';

define('MODX_API_MODE', true);

/* Connect to MODx */
$modx = new modX();
$modx->initialize('mgr');
echo '<pre>';
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

/* Add GoodNews package */
$corePath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/');
$goodnews = $modx->getService('goodnews', 'GoodNews', $corePath.'model/goodnews/');
if (!($goodnews instanceof GoodNews)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'GoodNews class could not be loaded.');
    exit();
}


/***** Start test-code *****/

require_once $sources['source_core'].'/model/goodnews/goodnewsbmh.class.php';

// testing examples
$bmh = new GoodNewsBounceMailHandler($modx);

$bmh->testmode             = true; // false is default, no need to specify
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

$bmh->mailMailHost         = 'mxv1.is1130.com'; // your mail server
$bmh->mailMailboxUsername  = 'noreply@bitego.com'; // your mailbox username
$bmh->mailMailboxPassword  = 'n0r3ply'; // your mailbox password

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

if ($bmh->openImapStream()) {
    echo 'Connected to mailbox.<br><br>';

    echo $bmh->lastErrorMsg;
        
    //$bmh->processMailbox();
    
    //$time = time();
    $bmh->addSubscriberBounce('7846', $time, 'hard');
    $bmh->addSubscriberBounce('6816', $time, 'soft');

    //echo '<br>Hard bounces for subscriber with ID 2: '.$bmh->getSubscriberBounceCounter('2', 'hard').' -> Hours between: '.$bmh->getSubscriberBounceLag('2', 'hard');
    //echo '<br>Soft bounces for subscriber with ID 2: '.$bmh->getSubscriberBounceCounter('2', 'soft').' -> Hours between: '.$bmh->getSubscriberBounceLag('2', 'soft');
    
    //$bmh->resetSubscriberBounces('2');

    //$bmh->increaseMailingBounceCounter('322', 'soft');
    //$bmh->increaseMailingBounceCounter('322', 'hard');
    
    //$email = 'gadgetto66@gmail.com';
    //echo '<br>Subscriber ID for email address '.$email.': '.$bmh->getSubscriberID($email);

    //$bmh->disableSubscriber(7846);
    //$bmh->disableSubscriber(7847);
    
    //$bmh->deleteSubscriber(8879);
    
    $containerIDs = $bmh->getGoodNewsBmhContainers();
    echo 'Containers with activated bounce handling:<br>';
    echo print_r($containerIDs,true);
    
    
    echo '<br>';
    echo '<br>Found mails: '.$bmh->get_cTotal();
    echo '<br>Fetched mails: '.$bmh->get_cFetch();
    echo '<br>Classified mails: '.$bmh->get_cClassified();
    echo '<br>Unclassified mails: '.$bmh->get_cUnclassified();
    echo '<br>Deleted mails: '.$bmh->get_cDeleted();
    echo '<br>Moved mails: '.$bmh->get_cMoved();
    echo '<br><br>';


} else {
    echo 'Connection to mailbox failed. :-(<br><br>';
}

$bmh->closeImapStream();

/***** End test-code *****/




$time_end = microtime_float();
$time = $time_end - $time_start;
echo 'Processing time: '.$time;

echo '</pre>';

function microtime_float() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}

?>