<?php

/**
 * This file is part of the GoodNews package.
 *
 * @copyright bitego (Martin Gartner)
 * @license GNU General Public License v2.0 (and later)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * GoodNewsResource Connector
 *
 * @var \MODX\Revolution\modX $modx
 * @var Bitego\GoodNews\GoodNewsResource $goodnewsresource
 * @package goodnews
 */

require_once dirname(__DIR__, 3) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$goodnewsresource = $modx->services->get('goodnewsresource');
$modx->lexicon->load('goodnews:resource');

$modx->request->handleRequest([
    'processors_path' => $goodnewsresource->config['processorsPath'],
    'location' => '',
]);
