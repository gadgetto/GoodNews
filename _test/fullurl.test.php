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


$base = 'https://www.domain.com/';

$html = '<a href="page1.html">page1.html</a>';
echo fullUrls($base, $html).PHP_EOL;

$html = '<a href="section1/page1.html">section1/page1.html</a>';
echo fullUrls($base, $html).PHP_EOL;

$html = '<a href="/section1/page1.html">/section1/page1.html</a>';
echo fullUrls($base, $html).PHP_EOL;

$html = '<a href="/section1/page1.html#jumpto">/section1/page1.html#jumpto</a>';
echo fullUrls($base, $html).PHP_EOL;

$html = '<a href="#jumpto">#jumpto</a>';
echo fullUrls($base, $html).PHP_EOL;

$html = '<a href="http://www.externaldomain.com/page1.html">http://www.externaldomain.com/page1.html</a>';
echo fullUrls($base, $html).PHP_EOL;

$html = '<a href="www.externaldomain.com/page1.html">www.externaldomain.com/page1.html</a>';
echo fullUrls($base, $html).PHP_EOL;

$html = '<a href="www.domain.com/page1.html">www.domain.com/page1.html</a>';
echo fullUrls($base, $html).PHP_EOL;

$html = '<a href="//www.externaldomain.com/page1.html">//www.externaldomain.com/page1.html</a>';
echo fullUrls($base, $html).PHP_EOL;

$html = '<img src="folder1/img1.jpg">';
echo fullUrls($base, $html).PHP_EOL;


//mixed preg_replace ( mixed $pattern , mixed $replacement , mixed $subject [, int $limit = -1 [, int &$count ]] )


function fullUrls($base, $html) {
    /* Extract domain name and protocol from $base */
    $splitBase = explode('//', $base);
    $protocol = $splitBase[0];
    $domain = $splitBase[1];
    $domain = rtrim($domain, '/ ');

    /* remove space around = sign */
    //$html = preg_replace('@(href|src)\s*=\s*@', '\1=', $html);
    $html = preg_replace('@(?<=href|src)\s*=\s*@', '=', $html);

    /* Fix google link weirdness */
    $html = str_ireplace('google.com/undefined', 'google.com', $html);

    /* add base protocol to naked domain links so they'll be ignored later */
    $html = str_ireplace('a href="'.$domain, 'a href="'.$protocol.'//'.$domain, $html);

    /* Standardize orthography of domain name */
    $html = str_ireplace($domain, $domain, $html);

    /* Correct base URL, if necessary */
    $server = preg_replace('@^([^\:]*)://([^/*]*)(/|$).*@', '\1://\2/', $base);

    /* Handle root-relative URLs */
    //$html = preg_replace('@\<([^>]*) (href|src)="/([^"]*)"@i', '<\1 \2="'.$server.'\3"', $html);
    $html = preg_replace('@\<([^>]*) (href|src)="/([^#"]*)"@i', '<\1 \2="' . $server . '\3"', $html);

    /* Handle base-relative URLs */
    //$html = preg_replace('@\<([^>]*) (href|src)="(?!http|mailto|sip|tel|callto|sms|ftp|sftp|gtalk|skype)(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="'.$base.'\3"', $html);
    $html = preg_replace('@\<([^>]*) (href|src)="(?!#|http|mailto|sip|tel|callto|sms|ftp|sftp|gtalk|skype)(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="' . $base . '\3"', $html);

    return $html;
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