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

$modelPath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/').'model/';
if (!$modx->loadClass('smartdomdocument.SmartDOMDocument', $modelPath, true, true)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not get service SmartDOMDocument.');
    exit();
}

$html = '<br><br>'.PHP_EOL;

$html .= '<a href="https://www.domain.com">https://www.domain.com</a>'.PHP_EOL;

$html .= '<a href="http://www.domain.com">http://www.domain.com</a>'.PHP_EOL;

$html .= '<a href="http://www.domain.com/page1.html?paramx=1&paramy=2">http://www.domain.com/page1.html?paramx=1&paramy=2</a>'.PHP_EOL;

$html .= '* <a href="http://page1.html">http://page1.html</a>'.PHP_EOL;

$html .= '* <a href="www.domain.com/page1.html">www.domain.com/page1.html</a>'.PHP_EOL;

$html .= '<a href="page1.html">page1.html</a>'.PHP_EOL;

$html .= '<a href="section1/page1.html">section1/page1.html</a>'.PHP_EOL;

$html .= '<a href="/section1/page1.html">/section1/page1.html</a>'.PHP_EOL;

$html .= '<a href="#jumpto">#jumpto</a>'.PHP_EOL;

$html .= '<a href="/section1/page1.html#jumpto">/section1/page1.html#jumpto</a>'.PHP_EOL;

$html .= '<a href="http://www.externaldomain.com">http://www.externaldomain.com</a>'.PHP_EOL;

$html .= '<a href="https://www.externaldomain.com">https://www.externaldomain.com</a>'.PHP_EOL;

$html .= '<a href="http://www.externaldomain.com/page1.html">http://www.externaldomain.com/page1.html</a>'.PHP_EOL;

$html .= '<a href="http://www.externaldomain.com/page1.html">https://www.externaldomain.com/page1.html</a>'.PHP_EOL;

$html .= '* <a href="www.externaldomain.com/page1.html">www.externaldomain.com/page1.html</a>'.PHP_EOL;

$html .= '<a href="//www.externaldomain.com/page1.html">//www.externaldomain.com/page1.html</a>'.PHP_EOL;

$html .= '<a href="http://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker">http://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker</a>'.PHP_EOL;

$html .= '<a href="ftp://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker">http://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker</a>'.PHP_EOL;

$html .= '<a href="http://user@:80">http://user@:80</a>'.PHP_EOL;

$html .= '<img src="folder1/img1.jpg">'.PHP_EOL;

$html .= '<img src="http://www.domain.com/folder1/img1.jpg">'.PHP_EOL;

$html .= '<a href="mailto:info@example.com">mailto:info@example.com</a>'.PHP_EOL;

$base = 'https://www.domain.com/';



//fullUrls($base, $html);


echo fullURLs($base, $html);



function fullURLs($base = null, $html = null) {
    if (empty($html) || empty($base)) { return false; }
    
    // Use the SmartDOMDocument extension
    if (!class_exists('SmartDOMDocument')) { return false; }

    $output = new SmartDOMDocument();
    $output->loadHTML($html);
    
    // Process all link tags
    $elements = $output->getElementsByTagName('a');

    foreach ($elements as $element){
        // Get the value of the href attribute
        $href = $element->getAttribute('href');
        echo  $href.'<br>';
        
        // Check if we have a protocol-relative URL - if so, don't touch and continue!
        // Sample:  //www.domain.com/page.html 
        if (mb_substr($href, 0, 2) == '//') { continue; }
        
        // Remove / from relative URLs
        $href = ltrim($href, '/');
        
        // De-construct the UR(L|I)
        $url_parts = parse_url($href);
        echo print_r($url_parts, true);
        echo '<br>';

        // ['scheme']   - (string) https | http | ftp | ...
        // ['host']     - (string) www.domain.com
        // ['port']     - (int)    9090
        // ['user']     - (string) username
        // ['pass']     - (string) password
        // ['path']     - (string) section1/page1.html | /section1/page1.html
        // ['query']    - (string) all after ?
        // ['fragment'] - (string) all after text anchor #
        
        // Check if URL/URI is completely invalid - if so, don't touch and continue!
        if ($url_parts == false) { continue; }
        
        // Check if text anchor only - if so, don't touch and continue!
        // Sample: #textanchor
        if (!empty($url_parts['fragment']) && empty($url_parts['scheme']) && empty($url_parts['host']) && empty($url_parts['path'])) { continue; }

        // Check if mailto: link - if so, don't touch and continue!
        if ($url_parts['scheme'] == "mailto") { continue; }
        
        // Finally add base URL to href value
        if (empty($url_parts['host'])) {
            $element->setAttribute('href', $base.$href);
        }
    }

    // Process all img tags
    $elements = $output->getElementsByTagName('img');
    
    foreach ($elements as $element){
        // Get the value of the href attribute
        $href = $element->getAttribute('src');
        echo  $href.'<br>';
        
        // Check if we have a protocol-relative URL - if so, don't touch and continue!
        // Sample:  //www.domain.com/page.html 
        if (mb_substr($href, 0, 2) == '//') { continue; }
        
        // Remove / from relative URLs
        $href = ltrim($href, '/');
        
        // De-construct the UR(L|I)
        $url_parts = parse_url($href);
        echo print_r($url_parts, true);
        echo '<br>';

        // Check if URL/URI is completely invalid - if so, don't touch and continue!
        if ($url_parts == false) { continue; }
        
        // Finally add base URL to href value
        if (empty($url_parts['host'])) {
            $element->setAttribute('src', $base.$href);
        }
    }
        
    // Return the processed (X)HTML
    return $output->saveHTMLExact();
}


function xxx_fullUrls($base, $html) {
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
    $html = preg_replace('@\<([^>]*) (href|src)="/([^#"]*)"@i', '<\1 \2="'.$server.'\3"', $html);

    /* Handle base-relative URLs */
    //$html = preg_replace('@\<([^>]*) (href|src)="(?!http|mailto|sip|tel|callto|sms|ftp|sftp|gtalk|skype)(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="'.$base.'\3"', $html);
    $html = preg_replace('@\<([^>]*) (href|src)="(?!#|https|http|mailto|sip|tel|callto|sms|ftp|sftp|gtalk|skype)(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="'.$base.'\3"', $html);

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