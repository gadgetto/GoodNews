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

use Bitego\GoodNews\Subscription\Subscription;

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

    /**
     * Helper function to recursively flatten an array of extended fields.
     *
     * @access protected
     * @param array $array The array to be flattened.
     * @param string $prefix The prefix for each new array key.
     * @return array $result The flattened and prefixed array.
     */
    protected function flattenExtended(array $array, string $prefix = 'extended.')
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + $this->flattenExtended($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }

    /**
     * Manipulate/add/remove fields from array.
     *
     * @access protected
     * @param array $properties
     * @return array $properties The cleaned array
     */
    protected function cleanupKeys(array $properties = [])
    {
        unset(
            // users table
            $properties['id'],          // multiple occurrence; not needed
            $properties['password'],    // security!
            $properties['cachepwd'],    // security!
            $properties['hash_class'],  // security!
            $properties['salt'],        // security!
            // user_attributes table
            $properties['internalKey'], // not needed
            $properties['sessionid'],   // security!
            $properties['extended']     // not needed as its already flattened
        );
        return $properties;
    }
}
