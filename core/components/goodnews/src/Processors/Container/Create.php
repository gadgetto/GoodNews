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
 * Overrides the modResourceCreateProcessor to provide custom processor functionality
 *
 * @package goodnews
 */
class GoodNewsResourceContainerCreateProcessor extends modResourceCreateProcessor {
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
        $this->object->set('class_key', 'GoodNewsResourceContainer');
        $this->object->set('cacheable', true);
        $this->object->set('isfolder', true);
        
        return parent::beforeSave();
    }

    /**
     * Override modResourceCreateProcessor::afterSave to provide custom functionality
     *
     * {@inheritDoc}
     * @return boolean
     */
    public function afterSave() {
        $this->setProperty('clearCache', true);
        
        return parent::afterSave();
    }

    private function _extractID($str) {
        $str = str_replace('[[~', '', $str);
        $str = str_replace(']]', '', $str);
        $str = trim($str);
        return $str;
    }
}
