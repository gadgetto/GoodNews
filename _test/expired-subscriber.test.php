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

$email = 'martin.gartner@me.com';
$activationttl = 180;

$expDate = date('Y-m-d H:i:s', time() - ($activationttl * 60));
$modx->log(modX::LOG_LEVEL_INFO, '$expDate: '.$expDate);

$c = $modx->newQuery('modUser');
$c->leftJoin('modUserProfile', 'Profile');
$c->leftJoin('GoodNewsSubscriberMeta', 'SubscriberMeta', 'modUser.id = SubscriberMeta.subscriber_id');

// modUser must:
// - be inactive
// - have a cachepwd (this means it's an unactivated account)
// - have SubscriberMeta.createdon date < expiration date (GoodNews setting)
$c->where(array(
    'Profile.email' => $email,
    'active' => false,
    'cachepwd:!=' => '', 
    'SubscriberMeta.createdon:<' => $expDate,
));

$users = $modx->getIterator('modUser', $c);
foreach ($users as $idx => $user) {
    $modx->log(modX::LOG_LEVEL_INFO, 'Found expired user: '.$user->get('id'));
    //$user->remove();
}


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