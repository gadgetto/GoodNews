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

namespace Bitego\GoodNews\Subscription;

/**
 * Class which abstracts storage of values of POST fields + fields set by hooks
 *
 * @package goodnews
 * @subpackage subscription
 */

class Dictionary
{
    /** @var modX $modx A reference to the modX instance */
    public $modx = null;

    /** @var Subscription $subscription A reference to the main Subscription instance */
    public $subscription = null;

    /** @var array $config A configuration array */
    public $config = [];

    /** @var array $fields An array of key->name pairs storing the fields passed */
    public $fields = [];

    /**
     * The constructor for the Dictionary class.
     *
     * @param Subscription $subscription A reference to the main Subscription instance
     * @param array $config A configuration array
     */
    public function __construct(Subscription &$subscription, array $config = [])
    {
        $this->modx = &$subscription->modx;
        $this->subscription = &$subscription;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get the fields from POST.
     *
     * @param array $fields A default set of fields to load
     * @return void
     */
    public function gather(array $fields = [])
    {
        if (empty($fields)) {
            $fields = [];
        }
        $this->fields = array_merge($fields, $_POST);
    }

    /**
     * Set a dictionary field value.
     *
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function set($field, $value)
    {
        $this->fields[$field] = $value;
    }

    /**
     * Get a dictionary field value.
     *
     * @param string $field
     * @param mixed $default
     * @return mixed
     */
    public function get($field, $default = null)
    {
        return isset($this->fields[$field]) ? $this->fields[$field] : $default;
    }

    /**
     * Return all field values in an array of key->name pairs.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->fields;
    }

    /**
     * Set a variable number of fields by passing in a key->name pair array.
     *
     * @param array $array
     * @return void
     */
    public function fromArray(array $array)
    {
        foreach ($array as $k => $v) {
            $this->fields[$k] = $v;
        }
    }

    /**
     * Remove a field from the stack.
     *
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        unset($this->fields[$key]);
    }

    /**
     * Reset all fields.
     *
     * @return void
     */
    public function reset()
    {
        $this->fields = [];
    }
}
