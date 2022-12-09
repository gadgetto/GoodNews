<?php
/**
 * Bootstrap file for MODX 3.x
 * 
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

$modx->addPackage('GoodNews\Model', $namespace['path'] . 'src/', null, 'GoodNews\\');
$modx->services->add('goodnews', function($c) use ($modx) {
    return new GoodNews\GoodNews($modx);
});
