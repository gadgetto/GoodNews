<?php
/**
 * GoodNews
 *
 * Copyright 2022 by bitego <office@bitego.com>
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * Overrides the modResourceUpdateProcessor to provide custom processor functionality
 *
 * @package goodnews
 */
class GoodNewsResourceContainerUpdateProcessor extends modResourceUpdateProcessor {
    /** @var GoodNewsResourceContainer $object */
    public $object;

    /**
     * Override modResourceCreateProcessor::beforeSave to provide custom functionality
     * (save the container settings to the modResource "properties" field -> MODx 2.2+)
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function beforeSave() {
        $properties = $this->getProperties();
        $settings = $this->object->getProperties('goodnews');

        foreach ($properties as $k => $v) {
            if (substr($k, 0, 8) == 'setting_') {
            
                // Remove 'stetting_' prefix
                $key = substr($k, 8);
                // Set all boolean values to 0 || 1
                if ($v === 'false') $v = 0;
                if ($v === 'true') $v = 1;

                $settings[$key] = $v;

                // Remove MODX tag delimiters
                $settings['unsubscribeResource'] = $this->_extractID($settings['unsubscribeResource']);
                $settings['profileResource'] = $this->_extractID($settings['profileResource']); 
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
    public function afterSave() {
        $this->setProperty('clearCache', true);
        $this->object->set('isfolder', true);

        // update properties of all child resources (merge with existing properties)
        $parentProperties = $this->object->getProperties('goodnews');

        foreach ($this->object->getIterator('Children') as $child) {
            $child->setProperties($parentProperties, 'goodnews');
            if (!$child->save()) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, "Could not change properties of child resource {$child->get('id')}", '', __METHOD__, __FILE__, __LINE__);
            }
        }

        return parent::afterSave();
    }

    /**
     * Override cleanup to send back only needed params
     *
     * @return array|string
     */
    public function cleanup() {
        $this->object->removeLock();
        $this->clearCache();

        $returnArray = $this->object->get(array_diff(array_keys($this->object->_fields), array('content','ta','introtext','description','link_attributes','pagetitle','longtitle','menutitle','goodnews_container_settings','properties')));
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
        $returnArray['preview_url'] = $this->modx->makeUrl($this->object->get('id'), $this->object->get('context_key'), '', 'full');

        return $this->success('', $returnArray);
    }

    private function _extractID($str) {
        $str = str_replace('[[~', '', $str);
        $str = str_replace(']]', '', $str);
        $str = trim($str);
        return $str;
    }
}

