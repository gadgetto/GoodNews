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

namespace Bitego\GoodNews\Processors\Group;

use Bitego\GoodNews\Model\GoodNewsGroup;
use MODX\Revolution\Processors\Model\RemoveProcessor;

/**
 * Group remove processor
 *
 * @package goodnews
 * @subpackage processors
 */

class Remove extends RemoveProcessor
{
    public $classKey = GoodNewsGroup::class;
    public $languageTopics = ['goodnews:default'];
    public $objectType = 'goodnews.group';
}
