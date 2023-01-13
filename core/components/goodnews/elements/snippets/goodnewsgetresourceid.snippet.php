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

use MODX\Revolution\modResource;

/**
 * GoodNewsGetResourceID
 *
 * Helper Snippet to get the id of a resource by it's name.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var $resourceObj modResource
 *
 * PROPERTIES
 *
 * @property string &pagetitle The pagetitle of the resource we'd like to get the id from.
 *                  (default: '')
 *
 * @package goodnews
 * subpackage snippets
 */

$resourceObj = $modx->getObject(modResource::class, ['pagetitle' => $pagetitle]);
return is_object($resourceObj) ? $resourceObj->get('id') : '';
