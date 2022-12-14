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

namespace Bitego\GoodNews\Processors\Subscribers;

use MODX\Revolution\Processors\Processor;
use Bitego\GoodNews\ImportSubscribers;

/**
 * Subscribers import processor
 *
 * @package goodnews
 * @subpackage processors
 */

class Import extends Processor
{
    /** @var ImportSubscribers $importsubscribers A reference to the ImportSubscribers object */
    public $importsubscribers = null;

    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function initialize()
    {
        set_time_limit(0);
        $this->importsubscribers = new ImportSubscribers($this->modx);
        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function process()
    {
        $error = false;
        
        $this->modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('goodnews.import_subscribers_log_prep_csv_import'));
        sleep(1);

        if (!$this->importsubscribers instanceof ImportSubscribers) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_no_class'));
            return $this->failure();
        }

        // Make sure a csv file was specified ($csvfile is an array!)
        $csvfile = $this->getProperty('csvfile');
        if (empty($csvfile['name'])) {
            $this->addFieldError(
                'csvfile',
                $this->modx->lexicon('goodnews.import_subscribers_log_ns_csvfile')
            );
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_ns_csvfile'));
            sleep(1);
        } elseif (!$this->importsubscribers->csvMimeType($csvfile['type'])) {
            $this->addFieldError(
                'csvfile',
                $this->modx->lexicon('goodnews.import_subscribers_log_wrong_filetype')
            );
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_wrong_filetype'));
            sleep(1);
        }

        // Make sure a batchsize was specified
        $batchsize = $this->getProperty('batchsize');
        if (empty($batchsize) && !is_numeric($batchsize)) {
            $this->addFieldError(
                'batchsize',
                $this->modx->lexicon('goodnews.import_subscribers_log_ns_batchsize')
            );
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_ns_batchsize'));
            sleep(1);
        }
        
        // Make sure a delimiter was specified
        $delimiter = $this->getProperty('delimiter');
        if (empty($delimiter)) {
            $this->addFieldError(
                'delimiter',
                $this->modx->lexicon('goodnews.import_subscribers_log_ns_delimiter')
            );
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_ns_delimiter'));
            sleep(1);
        }
        
        // Make sure an enclosure was specified */
        $enclosure = $this->getProperty('enclosure');
        if (empty($enclosure)) {
            $this->addFieldError(
                'enclosure',
                $this->modx->lexicon('goodnews.import_subscribers_log_ns_enclosure')
            );
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_ns_enclosure'));
            sleep(1);
        }

        // Make sure at least one GoodNews group was selected
        $groupscategories = $this->getProperty('groupscategories');
        if (empty($groupscategories)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_ns_grpcat'));
            $error = true;
            sleep(1);
        } else {
            // extract group and category IDs
            // (e.g. n_gongrp_5,n_goncat_6_5,n_goncat_5_5,n_gongrp_6,n_gongrp_7 )
            // $nodeparts[0] = 'n'
            // $nodeparts[1] = 'gongrp' || 'goncat'
            // $nodeparts[2] = grpID || catID
            // $nodeparts[3] = parent grpID (or empty)
            
            $nodes = explode(',', $groupscategories);
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
        }
        
        // Update mode?
        $update = $this->getProperty('update') ? true : false;
        
        // Only continue with processing if no errors occured
        if ($error || $this->hasErrors()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_failed'));
            sleep(2);
            unset($this->importsubscribers);
            return $this->failure();
        }

        $this->modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('goodnews.import_subscribers_log_importing_csv') . ' ' . $csvfile['name']);
        sleep(1);
        $this->modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('goodnews.import_subscribers_log_batchsize') . ' ' . $batchsize);
        sleep(1);

        // Initialize the ImportSubscribers object
        if ($this->importsubscribers->init($update, $csvfile['tmp_name'], $delimiter, $enclosure) == false) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_err_open_csvfile'));
            sleep(1);
            $error = true;
        }
        
        // Only continue with processing if no errors occured
        if ($error) {
            unset($this->importsubscribers);
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_failed'));
            sleep(2);
            return $this->failure();
        }
        
        $count = $this->importsubscribers->importUsers($batchsize, $groups, $categories);
        unset($this->importsubscribers);
        $this->modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('goodnews.import_subscribers_log_finished') . $count);
        sleep(2);
        
        return $this->success();
    }
    
    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['goodnews:default'];
    }
}
