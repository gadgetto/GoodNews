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
class GoodNewsResourceMailingUpdateProcessor extends modResourceUpdateProcessor {

    public $classKey = 'GoodNewsResourceMailing';
    public $languageTopics = array('resource','goodnews:default');

    /** @var GoodNewsResourceMailing $object */
    public $object;

    /** @var GoodNewsMailingMeta $object */
    public $meta;

     /** @var GoodnewsRecipientHandler $object */
    public $goodnewsrecipienthandler;
    
    /** @var boolean $isPublishing */
    public $isPublishing = false;

    /**
     * Create the GoodNewsResourceMailing (modResource) object for manipulation
     *
     * {@inheritDoc}
     *
     * @return string|modResource
     */
    public function initialize() {
        $initialized = parent::initialize();

        $corePath = $this->modx->getOption('goodnews.core_path', null, $this->modx->getOption('core_path').'components/goodnews/');
        
        $this->meta = $this->modx->getObject('GoodNewsMailingMeta', array('mailing_id'=>$this->object->get('id')));
        if (!is_object($this->meta)) {
            $this->meta = $this->modx->newObject('GoodNewsMailingMeta');
            if (!is_object($this->meta)) {
                return $this->modx->lexicon('resource_err_update');
            }
        }

        if (!$this->modx->loadClass('GoodNewsRecipientHandler', $corePath.'model/goodnews/', true, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not load GoodNewsRecipientHandler class.');
            return $this->modx->lexicon('resource_err_update');
        }
        $this->goodnewsrecipienthandler = new GoodNewsRecipientHandler($this->modx);

        return $initialized;
    }

    /**
     * {@inheritDoc}
     *
     * @return boolean|string
     */
    public function beforeSet() {
        $this->setProperty('clearCache', true);
        return parent::beforeSet();
    }

    /**
     * Override modResourceUpdateProcessor::beforeSave
     * 
     * {@inheritDoc}
     *
     * @return boolean
     */
    public function beforeSave() {

        // If sending has already been startet, the resource can't be changed any longer
        // (normaly this shouldn't happen as we don't provide an 'Edit' menu in this case)
        if ($this->meta->get('recipients_sent') != 0) {
            return $this->modx->lexicon('goodnews.newsletter_err_save_already_sending');
        }

        $this->prepareGroupsCategories();
        $groups      = $this->getProperty('groups');
        $categories  = $this->getProperty('categories');
        $collection1 = array_filter(explode(',', $this->getProperty('collection1')));
        $collection2 = array_filter(explode(',', $this->getProperty('collection2')));
        $collection3 = array_filter(explode(',', $this->getProperty('collection3')));
        $collections = array();
        $collections['collection1'] = $collection1;
        $collections['collection2'] = $collection2;
        $collections['collection3'] = $collection3;
        
        $this->meta->set('groups', $groups);
        $this->meta->set('categories', $categories);
        $this->meta->set('collections', serialize($collections));

        $this->goodnewsrecipienthandler->collect(unserialize($groups), unserialize($categories));
        $this->meta->set('recipients_total', $this->goodnewsrecipienthandler->getRecipientsTotal());
        $this->object->addOne($this->meta);
        
        // Copy container properties to mailing object properties
        $container = $this->modx->getObject('GoodNewsResourceContainer', $this->object->get('parent'));
        if (is_object($container)) {
            $this->object->setProperties($container->getProperties('goodnews'), 'goodnews');
        }
        
        $this->isPublishing = $this->object->isDirty('published') && $this->object->get('published');

        return parent::beforeSave();
    }

    /**
     * Override modResourceUpdateProcessor::afterSave
     *
     * {@inheritDoc}
     *
     * @return boolean
     */
    public function afterSave() {
        $this->clearContainerCache();

        // update recipients list
        $this->goodnewsrecipienthandler->updateRecipientsCollection($this->object->get('id'));
        
        return parent::afterSave();
    }

    /**
     * Clears the container cache to ensure that the container listing is updated
     *
     * @return void
     */
    public function clearContainerCache() {
        $this->modx->cacheManager->refresh(array(
            'db' => array(),
            'auto_publish' => array('contexts' => array($this->object->get('context_key'))),
            'context_settings' => array('contexts' => array($this->object->get('context_key'))),
            'resource' => array('contexts' => array($this->object->get('context_key'))),
        ));
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
            if (strpos($k,'tv') === 0) {
                unset($returnArray[$k]);
            }
            if (strpos($k,'setting_') === 0) {
                unset($returnArray[$k]);
            }
        }
        $returnArray['class_key'] = $this->object->get('class_key');
        $this->workingContext->prepare(true);
        $returnArray['preview_url'] = $this->modx->makeUrl($this->object->get('id'), $this->object->get('context_key'), '', 'full');
        return $this->success('',$returnArray);
    }

    /**
     * Extract and prepare selected groups and categories
     * (e.g. n_gongrp_5,n_goncat_6_5,n_goncat_5_5,n_gongrp_6,n_gongrp_7 )
     * $nodeparts[0] = 'n'
     * $nodeparts[1] = 'gongrp' || 'goncat'
     * $nodeparts[2] = grpID || catID
     * $nodeparts[3] = parent grpID (or empty)
     *
     * @return void
     */
    public function prepareGroupsCategories() {
    
        $nodes = explode(',', $this->getProperty('groupscategories'));
        
        $groups = array();
        $categories = array();
      
        foreach ($nodes as $node) {
            $nodeparts = explode('_', $node);
            if ($nodeparts[1] == 'gongrp') {
                $groups[] = $nodeparts[2];
            } elseif ($nodeparts[1] == 'goncat') {
                $categories[] = $nodeparts[2];
            }
        }
        $this->setProperty('groups', serialize($groups));
        $this->setProperty('categories', serialize($categories));
    }
}
