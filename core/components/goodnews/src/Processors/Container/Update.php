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

namespace Bitego\GoodNews\Processors\Container;

use MODX\Revolution\modX;
use MODX\Revolution\Processors\Resource\Update;

/**
 * Overrides the MODX\Revolution\Processors\Resource\Update processor
 * to provide custom processor functionality
 *
 * @package goodnews
 */
class ResourceContainerUpdate extends Update
{
    /** @var GoodNewsResourceContainer $object */
    public $object;

    /**
     * Override Update::beforeSave to provide custom functionality
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

                // Remove MODX tag delimiters
                $settings['unsubscribeResource'] = $this->extractID($settings['unsubscribeResource']);
                $settings['profileResource'] = $this->extractID($settings['profileResource']);
            }
        }

        $this->object->setProperties($settings, 'goodnews');

        return parent::beforeSave();
    }

    /**
     * Override modResourceUpdateProcessor::afterSave to provide custom functionality
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function afterSave()
    {
        $this->setProperty('clearCache', true);
        $this->object->set('isfolder', true);

        // update properties of all child resources (merge with existing properties)
        $parentProperties = $this->object->getProperties('goodnews');

        foreach ($this->object->getIterator('Children') as $child) {
            $child->setProperties($parentProperties, 'goodnews');
            if (!$child->save()) {
                $this->modx->log(
                    modX::LOG_LEVEL_ERROR,
                    "Could not change properties of child resource {$child->get('id')}",
                    '',
                    __METHOD__,
                    __FILE__,
                    __LINE__
                );
            }
        }

        return parent::afterSave();
    }

    /**
     * Override cleanup to send back only needed params
     *
     * @return array|string
     */
    public function cleanup()
    {
        $this->object->removeLock();
        $this->clearCache();

        $returnArray = $this->object->get(
            array_diff(
                array_keys($this->object->_fields),
                [
                    'content',
                    'ta',
                    'introtext',
                    'description',
                    'link_attributes',
                    'pagetitle',
                    'longtitle',
                    'menutitle',
                    'goodnews_container_settings',
                    'properties'
                ]
            )
        );
        foreach ($returnArray as $k => $v) {
            if (strpos($k, 'tv') === 0) {
                unset($returnArray[$k]);
            }
            if (strpos($k, 'setting_') === 0) {
                unset($returnArray[$k]);
            }
        }
        $returnArray['class_key'] = $this->object->get('class_key');
        $this->workingContext->prepare(true);
        $returnArray['preview_url'] = $this->modx->makeUrl(
            $this->object->get('id'),
            $this->object->get('context_key'),
            '',
            'full'
        );

        return $this->success('', $returnArray);
    }

    private function extractID($str)
    {
        $str = str_replace('[[~', '', $str);
        $str = str_replace(']]', '', $str);
        $str = trim($str);
        return $str;
    }
}
