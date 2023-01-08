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

namespace Bitego\GoodNews\Processors\Subscription;

/**
 * Abstracts processors into a class.
 *
 * @package goodnews
 * @subpackage processors
 */
abstract class Base
{
    /** @var modX $modx A reference to the modX instance */
    public $modx = null;

    /** @var Subscription $subscription */
    public $subscription = null;

    /** @var object $controller */
    public $controller = null;

    /** @var Dictionary $dictionary */
    public $dictionary = null;

    /** @var array $config */
    public $config = [];

    /**
     * Constructor for the Base subscription processor.
     *
     * @param Subscription &$subscription A reference to the Subscription instance
     * @param object &$controller
     * @param array $config
     */
    public function __construct(Subscription &$subscription, &$controller, array $config = [])
    {
        $this->modx = &$subscription->modx;
        $this->subscription = &$subscription;
        $this->controller = &$controller;
        $this->dictionary = &$controller->dictionary;
        $this->config = array_merge($this->config, $config);
    }

    abstract public function process();
}
