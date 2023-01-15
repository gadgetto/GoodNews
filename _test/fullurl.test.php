<?php

use MODX\Revolution\modX;
use MODX\Revolution\Error\modError;
use Bitego\GoodNews\Mailer;

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


$mailer = new Mailer($modx);

$html = '<br><br>' . PHP_EOL;
$html .= '<a href="https://www.domain.com">https://www.domain.com</a><br>' . PHP_EOL;
$html .= '<a href="http://www.domain.com">http://www.domain.com</a><br>' . PHP_EOL;
$html .= '<a href="http://www.domain.com/page1.html?paramx=1&paramy=2">http://www.domain.com/page1.html?paramx=1&paramy=2</a><br>' . PHP_EOL;
$html .= '<a href="http://page1.html">http://page1.html</a><br>' . PHP_EOL;
$html .= '<a href="www.domain.com/page1.html">www.domain.com/page1.html</a><br>' . PHP_EOL;
$html .= '<a href="page1.html">page1.html</a><br>' . PHP_EOL;
$html .= '<a href="section1/page1.html">section1/page1.html</a><br>' . PHP_EOL;
$html .= '<a href="/section1/page1.html">/section1/page1.html</a><br>' . PHP_EOL;
$html .= '<a href="#jumpto">#jumpto</a><br>' . PHP_EOL;
$html .= '<a href="/section1/page1.html#jumpto">/section1/page1.html#jumpto</a><br>' . PHP_EOL;
$html .= '<a href="http://www.externaldomain.com">http://www.externaldomain.com</a><br>' . PHP_EOL;
$html .= '<a href="https://www.externaldomain.com">https://www.externaldomain.com</a><br>' . PHP_EOL;
$html .= '<a href="http://www.externaldomain.com/page1.html">http://www.externaldomain.com/page1.html</a><br>' . PHP_EOL;
$html .= '<a href="http://www.externaldomain.com/page1.html">https://www.externaldomain.com/page1.html</a><br>' . PHP_EOL;
$html .= '<a href="www.externaldomain.com/page1.html">www.externaldomain.com/page1.html</a><br>' . PHP_EOL;
$html .= '<a href="//www.externaldomain.com/page1.html">//www.externaldomain.com/page1.html</a><br>' . PHP_EOL;
$html .= '<a href="http://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker">http://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker</a><br>' . PHP_EOL;
$html .= '<a href="ftp://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker">http://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker</a><br>' . PHP_EOL;
$html .= '<a href="http://user@:80">http://user@:80</a><br>' . PHP_EOL;
$html .= '<img src="folder1/img1.jpg"><br>' . PHP_EOL;
$html .= '<img src="http://www.domain.com/folder1/img1.jpg"><br>' . PHP_EOL;
$html .= '<a href="mailto:info@example.com">mailto:info@example.com</a><br>' . PHP_EOL;

$base = 'https://www.domain.com/';

echo $mailer->fullURLs($base, $html);


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
