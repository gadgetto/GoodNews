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

// Add GoodNews package
$modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/';
$modx->addPackage('goodnews', $modelPath);
$manager = $modx->getManager();


/***** Start test-code *****/


$properties = array();
$properties['email']    = 'apple@bitego.com';
$properties['name']     = 'Franz';
$properties['subject']  = 'Status report for mailing: Test1';
$properties['from']     = 'office@bitego.com';
$properties['fromname'] = 'Status Reporter';
$properties['tpl']      = 'xxx';





if (empty($properties['email']) || empty($properties['subject']) || empty($properties['tpl'])) {
    return false;
}
$email    = $properties['email'];
$name     = (!empty($properties['name'])) ? $properties['name'] : $properties['email'];
$subject  = $properties['subject'];
$from     = $properties['from'];
$fromName = $properties['fromname'];
$sender   = $properties['from'];
$replyTo  = $properties['from'];
$tpl      = $properties['tpl'];
$tplType  = (!empty($properties['tplType'])) ? $properties['tplType'] : 'modChunk';
$msg      = '<p>Testnachricht</p>';
$msgAlt   = '';

$modx->getService('mail', 'mail.modPHPMailer');
$modx->mail->set(modMail::MAIL_BODY, $msg);
if (!empty($msgAlt)) {
    $modx->mail->set(modMail::MAIL_BODY_TEXT, $msgAlt);
}
$modx->mail->set(modMail::MAIL_FROM, $from);
$modx->mail->set(modMail::MAIL_FROM_NAME, $fromName);
$modx->mail->set(modMail::MAIL_SENDER, $sender);
$modx->mail->set(modMail::MAIL_SUBJECT, $subject);
$modx->mail->address('reply-to', $replyTo);
$modx->mail->address('to', $email, $name);
$modx->mail->setHTML(true);

$modx->log(modX::LOG_LEVEL_INFO,'$msg: '.$msg);
$modx->log(modX::LOG_LEVEL_INFO,'$from: '.$from);
$modx->log(modX::LOG_LEVEL_INFO,'$fromName: '.$fromName);
$modx->log(modX::LOG_LEVEL_INFO,'$sender: '.$sender);
$modx->log(modX::LOG_LEVEL_INFO,'$subject: '.$subject);
$modx->log(modX::LOG_LEVEL_INFO,'$msgAlt: '.$msgAlt);
$modx->log(modX::LOG_LEVEL_INFO,'$email: '.$email);
$modx->log(modX::LOG_LEVEL_INFO,'$name: '.$name);
$modx->log(modX::LOG_LEVEL_INFO,'$replyTo: '.$replyTo);

$sent = $modx->mail->send();
$modx->mail->reset();

if (!$sent) {
    if ($this->debug) { $modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] GoodNewsMailing::_sendStatusEmail - Mailer error: '.$modx->mail->mailer->ErrorInfo); }
}
return $sent;





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