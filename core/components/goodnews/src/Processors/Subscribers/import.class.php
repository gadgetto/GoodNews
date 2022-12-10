<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
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

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/model/goodnews/goodnewsimportsubscribers.class.php';

/**
 * Subscribers import processor
 *
 * @package goodnews
 * @subpackage processors
 */

class SubscribersImportProcessor extends modProcessor {    

    public $languageTopics = array('goodnews:default');

    /** @var GoodNewsImportSubscribers $goodnewsimportsubscribers A reference to the GoodNewsImportSubscribers object */
    public $goodnewsimportsubscribers = null;

    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    public function initialize() {
        set_time_limit(0);
        $this->goodnewsimportsubscribers = new GoodNewsImportSubscribers($this->modx);
        return parent::initialize();
    }

	/**
	 * 
     * 
     * @return mixed
	 */    
	public function process() {

        $error = false;
        
        $this->modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('goodnews.import_subscribers_log_prep_csv_import'));
        sleep(1);

        if (!($this->goodnewsimportsubscribers instanceof GoodNewsImportSubscribers)) {
            $this->modx->log(modX::LOG_LEVEL_FATAL, $this->modx->lexicon('goodnews.import_subscribers_log_no_class'));
            $error = true;
        }

        // Make sure a csv file was specified ($csvfile is an array!)
        $csvfile = $this->getProperty('csvfile');
        if (empty($csvfile['name'])) {
            $this->addFieldError('csvfile', $this->modx->lexicon('goodnews.import_subscribers_log_ns_csvfile'));
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_ns_csvfile'));
            sleep(1);
        } elseif (!$this->goodnewsimportsubscribers->csvMimeType($csvfile['type'])) {
            $this->addFieldError('csvfile', $this->modx->lexicon('goodnews.import_subscribers_log_wrong_filetype'));
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_wrong_filetype'));
            sleep(1);
        }

        // Make sure a batchsize was specified
        $batchsize = $this->getProperty('batchsize');
        if (empty($batchsize) && !is_numeric($batchsize)) {
            $this->addFieldError('batchsize', $this->modx->lexicon('goodnews.import_subscribers_log_ns_batchsize'));
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_ns_batchsize'));
            sleep(1);
        }
        
        // Make sure a delimiter was specified
        $delimiter = $this->getProperty('delimiter');
        if (empty($delimiter)) {
            $this->addFieldError('delimiter', $this->modx->lexicon('goodnews.import_subscribers_log_ns_delimiter'));
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_ns_delimiter'));
            sleep(1);
        }
        
        // Make sure an enclosure was specified */
        $enclosure = $this->getProperty('enclosure');
        if (empty($enclosure)) {
            $this->addFieldError('enclosure', $this->modx->lexicon('goodnews.import_subscribers_log_ns_enclosure'));
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
        }
        
        // Update mode?
        $update = $this->getProperty('update') ? true : false;
        
        // Only continue with processing if no errors occured
        if ($error || $this->hasErrors()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_failed'));
            sleep(2);
            unset($this->goodnewsimportsubscribers);
            return $this->failure();
        }

        $this->modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('goodnews.import_subscribers_log_importing_csv').' '.$csvfile['name']);
        sleep(1);
        $this->modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('goodnews.import_subscribers_log_batchsize').' '.$batchsize);
        sleep(1);

        // Initialize the GoodNewsImportSubscribers object
        if ($this->goodnewsimportsubscribers->init($update, $csvfile['tmp_name'], $delimiter, $enclosure) == false) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_err_open_csvfile'));
            sleep(1);
            $error = true;
        }
        
        // Only continue with processing if no errors occured
        if ($error) {
            unset($this->goodnewsimportsubscribers);
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('goodnews.import_subscribers_log_failed'));
            sleep(2);
            return $this->failure();
        }
        
        $count = $this->goodnewsimportsubscribers->importUsers($batchsize, $groups, $categories);
        unset($this->goodnewsimportsubscribers);
        $this->modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('goodnews.import_subscribers_log_finished').$count);
        sleep(2);
        return $this->success();
	}
}
return 'SubscribersImportProcessor';
