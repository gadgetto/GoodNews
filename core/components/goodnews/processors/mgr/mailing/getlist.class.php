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

/**
 * Newsletters list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class NewsletterGetListProcessor extends modObjectGetListProcessor {

    const GON_NEWSLETTER_STATUS_NOT_PUBLISHED     = 0;
    const GON_NEWSLETTER_STATUS_NOT_READY_TO_SEND = 1;    
    const GON_NEWSLETTER_STATUS_NOT_YET_SENT      = 2;
    const GON_NEWSLETTER_STATUS_STOPPED           = 3;
    const GON_NEWSLETTER_STATUS_IN_PROGRESS       = 4;
    const GON_NEWSLETTER_STATUS_SENT              = 5;
    const GON_NEWSLETTER_STATUS_SCHEDULED         = 6;

    const GON_IPC_STATUS_STOPPED         = 0;
    const GON_IPC_STATUS_STARTED         = 1;

    public $classKey = 'GoodNewsResourceMailing';
    public $languageTopics = array('resource','goodnews:default');
    public $checkListPermission = true;
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'DESC';
    public $objectType = 'goodnews';

    /** @var GoodNewsResourceContainer $currentContainer */
    public $currentContainer = 0;
    
    /** @var boolean $legacyMode */
    public $legacyMode = false;
    
    public function initialize() {
        // Determine MODX Revo version and set legacy mode
        $version = $this->modx->getVersionData();
        $fullVersion = $version['full_version'];
        $this->legacyMode = version_compare($fullVersion, '2.3.0-dev', '>=') ? false : true;
        
        $this->currentContainer = $this->modx->goodnews->config['currentContainer'];
        return parent::initialize();
    }

    public function prepareQueryBeforeCount(xPDOQuery $c) {
                    
        // GoodNewsResourceMailing object
        $resourceColumns = array(
            'id',
            'parent',
            'pagetitle',
            'createdon',
            'published',
            'publishedon',
            'pub_date',
            'uri',
            'uri_override',
            'richtext',
            'deleted',
            'content',
        );
        $c->select($this->modx->getSelectColumns('GoodNewsResourceMailing', 'GoodNewsResourceMailing', '', $resourceColumns));

        // modUser object CreatedBy
        $c->leftJoin('modUser', 'CreatedBy');
        $c->select($this->modx->getSelectColumns('modUser', 'CreatedBy', 'createdby_'));

        // modUser object PublishedBy
        $c->leftJoin('modUser', 'PublishedBy');
        $c->select($this->modx->getSelectColumns('modUser', 'PublishedBy', 'publishedby_'));

        // GoodNewsMailingMeta object
        $c->leftJoin('GoodNewsMailingMeta', 'MailingMeta', 'MailingMeta.mailing_id = GoodNewsResourceMailing.id');
        $metaColumns = array(
            'recipients_total',
            'recipients_sent',
            'recipients_error',
            'senton',
            'sentby',
            'finishedon',
            'ipc_status',
            'scheduled',
            'soft_bounces',
            'hard_bounces',
        );
        $c->select($this->modx->getSelectColumns('GoodNewsMailingMeta', 'MailingMeta', '', $metaColumns));

        $c->where(array('parent' => $this->currentContainer));
        $c->where(array('class_key' => 'GoodNewsResourceMailing'));
        
        // filter combo
        $filter = $this->getProperty('filter','');
        switch ($filter) {
            case 'scheduled':
                $c->where(array(
                    'pub_date:>' => 0,
                    'deleted' => 0,
                ));
                break;
            case 'published':
                $c->where(array(
                    'published' => 1,
                    'deleted' => 0,
                ));
                break;
            case 'unpublished':
                $c->where(array(
                    'published' => 0,
                    'deleted' => 0,
                ));
                break;
            case 'deleted':
                $c->where(array(
                    'deleted' => 1,
                ));
                break;
            default:
                $c->where(array(
                    'deleted' => 0,
                ));
                break;
        }

        // Search query
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $queryWhere = array(
                'pagetitle:LIKE' => '%'.$query.'%',
            );
            $c->where($queryWhere);
        }
        
        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $resourceArray = parent::prepareRow($object);

        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $managerDateFormat = $this->modx->getOption('manager_date_format', null, 'Y-m-d');
        $managerTimeFormat = $this->modx->getOption('manager_time_format', null, 'H:i');
        $dateTimeFormat = $managerDateFormat.' '.$managerTimeFormat;
        
        $resourceArray['pagetitle'] = htmlentities($resourceArray['pagetitle'], ENT_COMPAT, $charset);

        $this->modx->getContext($resourceArray['context_key']);
        $resourceArray['preview_url'] = $this->modx->makeUrl($resourceArray['id'], $resourceArray['context_key']);
        $resourceArray['recipients_total_sent'] = (int)$resourceArray['recipients_total'].' / '.(int)$resourceArray['recipients_sent'];
        $resourceArray['recipients_open'] = (int)$resourceArray['recipients_total'] - (int)$resourceArray['recipients_sent'];
        $resourceArray['test_recipients_total'] = $this->_countTestRecipients();

        // Prepare status of each newsletter (for grid status display)
        if (!$resourceArray['published'] && empty($resourceArray['pub_date'])) {
        
            $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_NOT_PUBLISHED;
            $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_not_published');

        } elseif (!empty($resourceArray['pub_date'])) { 
                   
            // No recipients selected
            if ((int)$resourceArray['recipients_total'] == 0) {
                $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_NOT_READY_TO_SEND;
                $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_not_ready_to_send');
            } else {
                $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_SCHEDULED;
                $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_scheduled');
            }

        } else {
            
            // No recipients selected
            if ((int)$resourceArray['recipients_total'] == 0) {
                $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_NOT_READY_TO_SEND;
                $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_not_ready_to_send');
            
            // Sending finished
            } elseif ((int)$resourceArray['recipients_total'] == (int)$resourceArray['recipients_sent']) {
    
                if ((int)$resourceArray['ipc_status'] == self::GON_IPC_STATUS_STOPPED) {
                    $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_SENT;
                    $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_finished');
                } else {
                    $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_IN_PROGRESS;
                    $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_in_progress');
                }
            
            // Sending in progress or not yet started
            } elseif ((int)$resourceArray['recipients_sent'] == 0) {
    
                if ((int)$resourceArray['ipc_status'] == self::GON_IPC_STATUS_STARTED) {
                    $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_IN_PROGRESS;
                    $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_in_progress');
                } else {
                    $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_NOT_YET_SENT;
                    $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_not_yet_sent');
                }
            
            // Sending in progress or stopped
            } elseif ((int)$resourceArray['recipients_total'] != (int)$resourceArray['recipients_sent'] && (int)$resourceArray['recipients_sent'] != 0) {
    
                if ((int)$resourceArray['ipc_status'] == self::GON_IPC_STATUS_STOPPED) {
                    $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_STOPPED;
                    $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_stopped');
                } else {
                    $resourceArray['status'] = self::GON_NEWSLETTER_STATUS_IN_PROGRESS;
                    $resourceArray['statusmessage'] = $this->modx->lexicon('goodnews.newsletter_status_in_progress');
                }
    
            }
        
        }

        // Prepare action buttons
        $resourceArray['actions'] = array();
        
        if (!empty($resourceArray['deleted'])) {

            $resourceArray['actions'][] = array(
                'className' => 'undelete',
                'text'      => $this->modx->lexicon('undelete'),
            );

        } else {
            
            if ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_NOT_PUBLISHED) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.start') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.stop') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'preview gon-ab-preview',
                    'text'      => $this->modx->lexicon('view'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'test gon-ab-test',
                    'text'      => $this->modx->lexicon('goodnews.test'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'edit gon-ab-edit',
                    'text'      => $this->modx->lexicon('edit'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'delete gon-ab-delete',
                    'text'      => $this->modx->lexicon('delete'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'publish orange gon-ab-publish',
                    'text'      => $this->modx->lexicon('publish'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_NOT_READY_TO_SEND) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.start') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.stop') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'preview gon-ab-preview',
                    'text'      => $this->modx->lexicon('view'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'test gon-ab-test',
                    'text'      => $this->modx->lexicon('goodnews.test'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'edit gon-ab-edit',
                    'text'      => $this->modx->lexicon('edit'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'delete gon-ab-delete',
                    'text'      => $this->modx->lexicon('delete'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'unpublish gon-ab-unpublish',
                    'text'      => $this->modx->lexicon('unpublish'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_NOT_YET_SENT) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.start') : '',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.stop') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'preview gon-ab-preview',
                    'text'      => $this->modx->lexicon('view'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'test gon-ab-test',
                    'text'      => $this->modx->lexicon('goodnews.test'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'edit gon-ab-edit',
                    'text'      => $this->modx->lexicon('edit'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'delete gon-ab-delete',
                    'text'      => $this->modx->lexicon('delete'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'unpublish gon-ab-unpublish',
                    'text'      => $this->modx->lexicon('unpublish'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_STOPPED) {
                $resourceArray['actions'][] = array(
                    'className' => 'continue gon-ab-continue',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.continue') : '',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.stop') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'preview gon-ab-preview',
                    'text'      => $this->modx->lexicon('view'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'test gon-ab-test',
                    'text'      => $this->modx->lexicon('goodnews.test'),
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'edit gon-ab-edit',
                    'text'      => $this->modx->lexicon('edit'),
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'delete gon-ab-delete',
                    'text'      => $this->modx->lexicon('delete'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'unpublish gon-ab-unpublish',
                    'text'      => $this->modx->lexicon('unpublish'),
                    'disabled'  => ' disabled="disabled"',
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_IN_PROGRESS) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.start') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.stop') : '',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'preview gon-ab-preview',
                    'text'      => $this->modx->lexicon('view'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'test gon-ab-test',
                    'text'      => $this->modx->lexicon('goodnews.test'),
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'edit gon-ab-edit',
                    'text'      => $this->modx->lexicon('edit'),
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'delete gon-ab-delete',
                    'text'      => $this->modx->lexicon('delete'),
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'unpublish gon-ab-unpublish',
                    'text'      => $this->modx->lexicon('unpublish'),
                    'disabled'  => ' disabled="disabled"',
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_SENT) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.start') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.stop') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'preview gon-ab-preview',
                    'text'      => $this->modx->lexicon('view'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'test gon-ab-test',
                    'text'      => $this->modx->lexicon('goodnews.test'),
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'edit gon-ab-edit',
                    'text'      => $this->modx->lexicon('edit'),
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'delete gon-ab-delete',
                    'text'      => $this->modx->lexicon('delete'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'unpublish gon-ab-unpublish',
                    'text'      => $this->modx->lexicon('unpublish'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_SCHEDULED) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.start') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => $this->legacyMode ? $this->modx->lexicon('goodnews.stop') : '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'preview gon-ab-preview',
                    'text'      => $this->modx->lexicon('view'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'test gon-ab-test',
                    'text'      => $this->modx->lexicon('goodnews.test'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'edit gon-ab-edit',
                    'text'      => $this->modx->lexicon('edit'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'delete gon-ab-delete',
                    'text'      => $this->modx->lexicon('delete'),
                );
                $resourceArray['actions'][] = array(
                    'className' => 'publish orange gon-ab-publish',
                    'text'      => $this->modx->lexicon('publish'),
                );
            }            
            
        }

        $createdon = strtotime($resourceArray['createdon']);
        $resourceArray['createdon_formatted'] = date($dateTimeFormat, $createdon);
        if (empty($resourceArray['createdby_username'])) {
            $resourceArray['createdby_username'] = $resourceArray['createdby'];
        }

        if ($resourceArray['published']) {
        	$publishedon = strtotime($resourceArray['publishedon']);
            $resourceArray['publishedon_formatted'] = date($dateTimeFormat, $publishedon);
            if (empty($resourceArray['publishedby_username'])) {
                $resourceArray['publishedby_username'] = $resourceArray['publishedby'];
            }
        } else {
            $resourceArray['publishedon_formatted'] = '-';
            $resourceArray['publishedby_username'] = '-';            
        }

        if ($resourceArray['pub_date']) {
        	$pub_date = strtotime($resourceArray['pub_date']);
            $resourceArray['pub_date_formatted'] = date($dateTimeFormat, $pub_date);
        } else {
            if ($resourceArray['scheduled']) {
                $resourceArray['pub_date_formatted'] = $this->modx->lexicon('goodnews.newsletter_sent_scheduled');
            } else {
                $resourceArray['pub_date_formatted'] = '-';
            }
        }

        if ($resourceArray['senton']) {
            $resourceArray['senton_formatted'] = date($dateTimeFormat, $resourceArray['senton']);
        } else {
            $resourceArray['senton_formatted'] = '-';
        }

        if ($resourceArray['sentby']) {
            $user = $this->modx->getObject('modUser', $resourceArray['sentby']);
            if (is_object($user)) {
                $resourceArray['sentby_username'] = $user->get('username');
            } else {
                $resourceArray['sentby_username'] = $resourceArray['sentby'];
            }
            unset($user);
        } else {
            $resourceArray['sentby_username'] = '-';
        }

        if ($resourceArray['finishedon']) {
            $resourceArray['finishedon_formatted'] = date($dateTimeFormat, $resourceArray['finishedon']);
        } else {
            $resourceArray['finishedon_formatted'] = '-';
        }

        return $resourceArray;
    }

    /**
     * Count test-recipients
     *
     * @return integer $count
     */
    private function _countTestRecipients() {

        $c = $this->modx->newQuery('modUser');
        $c->leftJoin('GoodNewsSubscriberMeta', 'SubscriberMeta', 'SubscriberMeta.subscriber_id = modUser.id');  
        $c->where(array(
            'modUser.active' => true,
            'SubscriberMeta.testdummy' => 1,
        ));
        $count = $this->modx->getCount('modUser', $c);

        return $count;
    }
}
return 'NewsletterGetListProcessor';
