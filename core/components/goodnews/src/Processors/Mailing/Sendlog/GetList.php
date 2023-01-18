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

namespace Bitego\GoodNews\Processors\Mailing\Sendlog;

use MODX\Revolution\modUserProfile;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;
use Bitego\GoodNews\Model\GoodNewsSubscriberLog;

/**
 * SendLog get list processor.
 *
 * @package goodnews
 * @subpackage processors
 */

class GetList extends GetListProcessor
{
    public $classKey = GoodNewsSubscriberLog::class;
    public $languageTopics = ['goodnews:default'];
    public $defaultSortField = 'statustime';
    public $defaultSortDirection = 'DESC';
    public $objectType = 'goodnews.sendlog';

    public const GON_USER_NOT_YET_SENT = 0;
    public const GON_USER_SENT         = 1;
    public const GON_USER_SEND_ERROR   = 2;
    public const GON_USER_RESERVED     = 4;

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->select([
            'GoodNewsSubscriberLog.id',
            'GoodNewsSubscriberLog.mailing_id',
            'GoodNewsSubscriberLog.subscriber_id',
            'GoodNewsSubscriberLog.statustime',
            'GoodNewsSubscriberLog.status',
            'GoodNewsSubscriberLog.log',
            'Profile.email AS subscriber_email',
            'Profile.fullname AS subscriber_fullname',
        ]);
        $c->leftJoin(modUserProfile::class, 'Profile', 'GoodNewsSubscriberLog.subscriber_id = Profile.internalKey');

        $mailingid = $this->getProperty('mailingid', 0);
        if (!empty($mailingid)) {
            $c->where(['GoodNewsSubscriberLog.mailing_id' => $mailingid]);
        }

        $statusfilter = $this->getProperty('statusfilter', '');
        if (!empty($statusfilter)) {
            $c->where(['GoodNewsSubscriberLog.status' => $statusfilter]);
        }

        $query = $this->getProperty('query', '');
        if (!empty($query)) {
            $c->where([
                'Profile.email:LIKE' => '%' . $query . '%',
                'OR:Profile.fullname:LIKE' => '%' . $query . '%',
                'OR:GoodNewsSubscriberLog.log:LIKE' => '%' . $query . '%',
            ]);
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $resourceArray = parent::prepareRow($object);

        $managerDateFormat = $this->modx->getOption('manager_date_format', null, 'Y-m-d');
        $managerTimeFormat = $this->modx->getOption('manager_time_format', null, 'H:i');
        $dateTimeFormat = $managerDateFormat . ' ' . $managerTimeFormat;

        $statustime = strtotime($resourceArray['statustime']);
        $resourceArray['statustime'] = date($dateTimeFormat, $statustime);

        switch ($resourceArray['status']) {
            case self::GON_USER_SENT:
                $resourceArray['status'] = $this->modx->lexicon('goodnews.sendlog_status_sent');
                break;
            case self::GON_USER_SEND_ERROR:
                $resourceArray['status'] = $this->modx->lexicon('goodnews.sendlog_status_send_error');
                break;
            default:
                $resourceArray['status'] = $this->modx->lexicon('goodnews.sendlog_status_unknown');
        }
        return $resourceArray;
    }
}
