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
use MODX\Revolution\Processors\Resource\Update;
use Bitego\GoodNews\Model\GoodNewsResourceContainer;
use Bitego\GoodNews\Model\GoodNewsResourceMailing;
use Bitego\GoodNews\Model\GoodNewsMailingMeta;
use Bitego\GoodNews\RecipientsHandler;

/**
 * Overrides the MODX\Revolution\Processors\Resource\Update processor
 * to provide custom processor functionality.
 *
 * @package goodnews
 */
class GoodNewsResourceMailingUpdateProcessor extends Update
{
    public $classKey = GoodNewsResourceMailing::class;
    public $languageTopics = ['resource','goodnews:default'];

    /** @var GoodNewsResourceMailing $object */
    public $object;

    /** @var GoodNewsMailingMeta $meta */
    public $meta;

    /** @var RecipientsHandler $recipientshandler */
    public $recipientshandler;

    /** @var boolean $isPublishing */
    public $isPublishing = false;

    /**
     * Create the GoodNewsResourceMailing (modResource) object for manipulation.
     *
     * @return string|modResource
     */
    public function initialize()
    {
        $initialized = parent::initialize();
        $this->meta = $this->modx->getObject(
            GoodNewsMailingMeta::class,
            ['mailing_id' => $this->object->get('id')]
        );
        if (!is_object($this->meta)) {
            $this->meta = $this->modx->newObject(GoodNewsMailingMeta::class);
            if (!is_object($this->meta)) {
                return $this->modx->lexicon('resource_err_update');
            }
        }
        $this->recipientshandler = new RecipientsHandler($this->modx);
        return $initialized;
    }

    /**
     * Override Update::beforeSet
     *
     * @return boolean|string
     */
    public function beforeSet()
    {
        $this->setProperty('clearCache', true);
        return parent::beforeSet();
    }

    /**
     * Override Update::beforeSave
     *
     * @return boolean
     */
    public function beforeSave()
    {
        // If sending has already been startet, the resource can't be changed any longer
        // (normaly this shouldn't happen as we don't provide an 'Edit' menu in this case)
        if ($this->meta->get('recipients_sent') != 0) {
            return $this->modx->lexicon('goodnews.newsletter_err_save_already_sending');
        }

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

        // Copy container properties to mailing object properties
        $container = $this->modx->getObject(GoodNewsResourceContainer::class, $this->object->get('parent'));
        if (is_object($container)) {
            $this->object->setProperties($container->getProperties('goodnews'), 'goodnews');
        }

        $this->isPublishing = $this->object->isDirty('published') && $this->object->get('published');

        return parent::beforeSave();
    }

    /**
     * Override Update::afterSave
     *
     * @return boolean
     */
    public function afterSave()
    {
        $this->clearContainerCache();
        // update recipients list
        $this->recipientshandler->updateRecipientsCollection($this->object->get('id'));
        return parent::afterSave();
    }

    /**
     * Clears the container cache to ensure that the container listing is updated
     *
     * @return void
     */
    public function clearContainerCache()
    {
        $this->modx->cacheManager->refresh([
            'db' => [],
            'auto_publish' => ['contexts' => [$this->object->get('context_key')]],
            'context_settings' => ['contexts' => [$this->object->get('context_key')]],
            'resource' => ['contexts' => [$this->object->get('context_key')]],
        ]);
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
