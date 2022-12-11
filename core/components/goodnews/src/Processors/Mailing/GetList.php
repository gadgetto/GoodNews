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

namespace GoodNews\Processors\Mailing;

use GoodNews\GoodNews;
use GoodNews\Model\GoodNewsResourceContainer;
use GoodNews\Model\GoodNewsResourceMailing;
use GoodNews\Model\GoodNewsMailingMeta;
use MODX\Revolution\modUser;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

/**
 * Newsletters get list processor.
 *
 * @var \MODX\Revolution\modX $modx
 * @package goodnews
 * @subpackage processors
 */

class GetList extends GetListProcessor
{
    public const GON_NEWSLETTER_STATUS_NOT_PUBLISHED     = 0;
    public const GON_NEWSLETTER_STATUS_NOT_READY_TO_SEND = 1;
    public const GON_NEWSLETTER_STATUS_NOT_YET_SENT      = 2;
    public const GON_NEWSLETTER_STATUS_STOPPED           = 3;
    public const GON_NEWSLETTER_STATUS_IN_PROGRESS       = 4;
    public const GON_NEWSLETTER_STATUS_SENT              = 5;
    public const GON_NEWSLETTER_STATUS_SCHEDULED         = 6;

    public const GON_IPC_STATUS_STOPPED         = 0;
    public const GON_IPC_STATUS_STARTED         = 1;

    public $classKey = GoodNewsResourceMailing::class;
    public $languageTopics = ['resource', 'goodnews:default'];
    public $checkListPermission = true;
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'DESC';
    public $objectType = 'goodnews';

    /** @var GoodNews $goodnews A reference to the GoodNews object */
    public $goodnews = null;

    /** @var GoodNewsResourceContainer $userCurrentContainer */
    public $userCurrentContainer = 0;
        
    public function initialize()
    {
        $this->goodnews = new GoodNews($this->modx);
        $this->userCurrentContainer = $this->goodnews->config['userCurrentContainer'];
        return parent::initialize();
    }

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        // GoodNewsResourceMailing object
        $resourceColumns = [
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
        ];
        $c->select(
            $this->modx->getSelectColumns(
                $this->classKey,
                'GoodNewsResourceMailing',
                '',
                $resourceColumns
            )
        );

        // modUser object CreatedBy
        $c->leftJoin('modUser', 'CreatedBy');
        $c->select($this->modx->getSelectColumns(modUser::class, 'CreatedBy', 'createdby_'));

        // modUser object PublishedBy
        $c->leftJoin('modUser', 'PublishedBy');
        $c->select($this->modx->getSelectColumns(modUser::class, 'PublishedBy', 'publishedby_'));

        // GoodNewsMailingMeta object
        $c->leftJoin(GoodNewsMailingMeta::class, 'MailingMeta', 'MailingMeta.mailing_id = GoodNewsResourceMailing.id');
        
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
        $c->select($this->modx->getSelectColumns(GoodNewsMailingMeta::class, 'MailingMeta', '', $metaColumns));

        $c->where(array('parent' => $this->userCurrentContainer));
        $c->where(array('class_key' => $this->classKey));
        
        // filter combo
        $filter = $this->getProperty('filter', '');
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
                'pagetitle:LIKE' => '%' . $query . '%',
            );
            $c->where($queryWhere);
        }
        
        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $resourceArray = parent::prepareRow($object);

        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $managerDateFormat = $this->modx->getOption('manager_date_format', null, 'Y-m-d');
        $managerTimeFormat = $this->modx->getOption('manager_time_format', null, 'H:i');
        $dateTimeFormat = $managerDateFormat . ' ' . $managerTimeFormat;
        
        $resourceArray['pagetitle'] = htmlentities($resourceArray['pagetitle'], ENT_COMPAT, $charset);

        $this->modx->getContext($resourceArray['context_key']);
        $resourceArray['preview_url'] = $this->modx->makeUrl($resourceArray['id'], $resourceArray['context_key']);
        $resourceArray['recipients_total_sent'] = (int)$resourceArray['recipients_total'] . ' / ' . (int)$resourceArray['recipients_sent'];
        $resourceArray['recipients_open'] = (int)$resourceArray['recipients_total'] - (int)$resourceArray['recipients_sent'];
        $resourceArray['test_recipients_total'] = $this->countTestRecipients();

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
                    'text'      => '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => '',
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
                $resourceArray['actions'][] = array(
                    'className' => 'log gon-ab-log',
                    'text'      => $this->modx->lexicon('goodnews.sendlog'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_NOT_READY_TO_SEND) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => '',
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
                $resourceArray['actions'][] = array(
                    'className' => 'log gon-ab-log',
                    'text'      => $this->modx->lexicon('goodnews.sendlog'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_NOT_YET_SENT) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => '',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => '',
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
                $resourceArray['actions'][] = array(
                    'className' => 'log gon-ab-log',
                    'text'      => $this->modx->lexicon('goodnews.sendlog'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_STOPPED) {
                $resourceArray['actions'][] = array(
                    'className' => 'continue gon-ab-continue',
                    'text'      => '',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => '',
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
                $resourceArray['actions'][] = array(
                    'className' => 'log gon-ab-log',
                    'text'      => $this->modx->lexicon('goodnews.sendlog'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_IN_PROGRESS) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => '',
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
                $resourceArray['actions'][] = array(
                    'className' => 'log gon-ab-log',
                    'text'      => $this->modx->lexicon('goodnews.sendlog'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_SENT) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => '',
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
                $resourceArray['actions'][] = array(
                    'className' => 'log gon-ab-log',
                    'text'      => $this->modx->lexicon('goodnews.sendlog'),
                );
            } elseif ($resourceArray['status'] == self::GON_NEWSLETTER_STATUS_SCHEDULED) {
                $resourceArray['actions'][] = array(
                    'className' => 'start gon-ab-start',
                    'text'      => '',
                    'disabled'  => ' disabled="disabled"',
                );
                $resourceArray['actions'][] = array(
                    'className' => 'stop gon-ab-stop',
                    'text'      => '',
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
                $resourceArray['actions'][] = array(
                    'className' => 'log gon-ab-log',
                    'text'      => $this->modx->lexicon('goodnews.sendlog'),
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
            $user = $this->modx->getObject(modUser::class, $resourceArray['sentby']);
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
    private function countTestRecipients()
    {
        $c = $this->modx->newQuery(modUser::class);
        $c->leftJoin(GoodNewsSubscriberMeta::class, 'SubscriberMeta', 'SubscriberMeta.subscriber_id = modUser.id');
        $c->where(array(
            'modUser.active' => true,
            'SubscriberMeta.testdummy' => 1,
        ));
        $count = $this->modx->getCount(modUser::class, $c);

        return $count;
    }
}
