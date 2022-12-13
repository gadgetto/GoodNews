<?php

/**
 * Bootstrap file for MODX 3.x
 *
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 * @see \MODX\Revolution\modX::_initNamespaces()
 */

require_once __DIR__ . '/vendor/autoload.php';

$modx->addPackage(
    'Bitego\\GoodNews\\Model',
    $namespace['path'] . 'src/',
    null,
    'Bitego\\GoodNews\\'
);

$modx->services->add('goodnews', function ($c) use ($modx) {
    return new \Bitego\GoodNews\GoodNews($modx);
});

$modx->services->add('mail', function ($c) use ($modx) {
    return new \MODX\Revolution\Mail\modPHPMailer($modx);
});
