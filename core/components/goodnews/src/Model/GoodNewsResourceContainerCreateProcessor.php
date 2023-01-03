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

namespace Bitego\GoodNews\Model;

use MODX\Revolution\modX;
use MODX\Revolution\Processors\Resource\Create;
use Bitego\GoodNews\Model\GoodNewsResourceContainer;

/**
 * Overrides the MODX\Revolution\Processors\Resource\Create processor
 * to provide custom processor functionality.
 *
 * @package goodnews
 */
class GoodNewsResourceContainerCreateProcessor extends Create
{
    /** @var GoodNewsResourceContainer $object */
    public $object;

    /**
     * Override Create::beforeSave to provide custom functionality
     * (save the container settings to the modResource "properties" field -> MODx 2.2+)
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function beforeSave()
    {
        $properties = $this->getProperties();
        $settings = $this->object->getProperties('goodnews');

        foreach ($properties as $k => $v) {
            if (substr($k, 0, 8) == 'setting_') {
                // Remove 'stetting_' prefix
                $key = substr($k, 8);
                // Set all boolean values to 0 || 1
                if ($v === 'false') {
                    $v = 0;
                }
                if ($v === 'true') {
                    $v = 1;
                }

                $settings[$key] = $v;

                $settings['unsubscribeResource'] = !empty($settings['unsubscribeResource'])
                    ? $this->extractID($settings['unsubscribeResource'])
                    : '';
                $settings['profileResource'] = !empty($settings['profileResource'])
                    ? $this->extractID($settings['profileResource'])
                    : '';
            }
        }

        $this->object->setProperties($settings, 'goodnews');
        $this->object->set('class_key', GoodNewsResourceContainer::class);
        $this->object->set('cacheable', true);
        $this->object->set('isfolder', true);

        return parent::beforeSave();
    }

    /**
     * Override Create::afterSave to provide custom functionality
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function afterSave()
    {
        $this->setProperty('clearCache', true);
        return parent::afterSave();
    }

    /**
     * Remove MODX tag delimiters from given string
     *
     * @param string $str The string to parse
     * @return string The parsed string
     */
    private function extractID($str)
    {
        $str = str_replace('[[~', '', $str);
        $str = str_replace(']]', '', $str);
        $str = trim($str);
        return $str;
    }
}
