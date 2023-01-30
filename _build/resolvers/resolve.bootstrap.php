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

use xPDO\Transport\xPDOTransport;

/**
 * Include bootstrap when installing the package
 *
 * @package goodnews
 * @subpackage build
 */

if ($object->xpdo) {
    $modx = &$object->xpdo;
    $success = true;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $corePath = $modx->getOption(
                'goodnews.core_path',
                null,
                $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/goodnews/'
            );
            $bootstrap = $corePath . 'bootstrap.php';
            if (file_exists($bootstrap)) {
                $namespace = [];
                $namespace['path'] = $corePath;
                require $bootstrap;
            } else {
                $success = false;
                $modx->log(modX::LOG_LEVEL_ERROR, 'Could not include bootstrap.php from path: ' . $corePath);
            }
    }
}
unset($corePath, $bootstrap);
return $success;
