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
use Bitego\GoodNews\Model\GoodNewsResourceMailing;
use Bitego\GoodNews\Model\GoodNewsMailingMeta;
use Bitego\GoodNews\RecipientsHandler;

/**
 * Overrides the MODX\Revolution\Processors\Resource\Create processor
 * to provide custom processor functionality.
 *
 * @package goodnews
 */
class GoodNewsResourceMailingCreateProcessor extends Create
{
    public $classKey = GoodNewsResourceMailing::class;
    public $languageTopics = ['resource', 'goodnews:resource'];

    /** @var GoodNewsResourceMailing $object */
    public $object;

    /** @var GoodNewsMailingMeta $meta */
    public $meta;

    /** @var RecipientsHandler $recipientshandler */
    public $recipientshandler;

    /** @var boolean $isPublishing */
    public $isPublishing = false;

    /**
     * Create the GoodNewsResourceMailing (modResource) object for manipulation
     *
     * @return string|modResource
     */
    public function initialize()
    {
        $initialized = parent::initialize();
        $this->meta = $this->modx->newObject(GoodNewsMailingMeta::class);
        if (!is_object($this->meta)) {
            return $this->modx->lexicon('resource_err_create');
        }
        $this->recipientshandler = new RecipientsHandler($this->modx);
        return $initialized;
    }

    public function beforeSet()
    {
        $this->setProperty('class_key', $classKey);
        $this->setProperty('searchable', false);
        $this->setProperty('isfolder', false);
        $this->setProperty('cacheable', true);
        $this->setProperty('clearCache', true);
        return parent::beforeSet();
    }

    /**
     * Override Create::beforeSave
     *
     * @return boolean
     */
    public function beforeSave()
    {
        $this->prepareGroupsCategories();
        $groups = $this->getProperty('groups');
        $categories  = $this->getProperty('categories');
        $collection1 = array_filter(explode(',', $this->getProperty('collection1')));
        $collection2 = array_filter(explode(',', $this->getProperty('collection2')));
        $collection3 = array_filter(explode(',', $this->getProperty('collection3')));
        $collections = [];
        $collections['collection1'] = $collection1;
        $collections['collection2'] = $collection2;
        $collections['collection3'] = $collection3;

        $this->meta->set('groups', $groups);
        $this->meta->set('categories', $categories);
        $this->meta->set('collections', serialize($collections));

        $this->recipientshandler->collect(unserialize($groups), unserialize($categories));
        $this->meta->set('recipients_total', $this->recipientshandler->getRecipientsTotal());
        $this->object->addOne($this->meta);

        if (!$this->parentResource) {
            $this->parentResource = $this->object->getOne('Parent');
        }

        // Copy container properties to mailing object properties
        $container = $this->modx->getObject(GoodNewsResourceContainer::class, $this->object->get('parent'));
        if ($container) {
            $settings = $container->getProperties('goodnews');
            $this->object->setProperties($settings, 'goodnews');
        }

        $this->isPublishing = $this->object->isDirty('published') && $this->object->get('published');

        return parent::beforeSave();
    }


    /**
     * Override Create::afterSave
     *
     * @return boolean
     */
    public function afterSave()
    {
        $this->clearContainerCache();
        // save recipients list
        $this->recipientshandler->saveRecipientsCollection($this->object->get('id'));
        return parent::afterSave();
    }

    /**
     * Clears the container cache to ensure that the container listing is updated
     *
     * @return void
     */
    public function clearContainerCache()
    {
        $this->modx->cacheManager->refresh(array(
            'db' => [],
            'auto_publish' => ['contexts' => [$this->object->get('context_key')]],
            'context_settings' => ['contexts' => [$this->object->get('context_key')]],
            'resource' => ['contexts' => [$this->object->get('context_key')]],
        ));
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
    public function prepareGroupsCategories()
    {
        $nodes = explode(',', $this->getProperty('groupscategories'));

        $groups = [];
        $categories = [];

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
