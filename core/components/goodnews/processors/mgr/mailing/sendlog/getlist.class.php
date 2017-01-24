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
 * SendLog get list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class SendLogGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'GoodNewsSubscriberLog';
    public $languageTopics = array('goodnews:default');
    public $defaultSortField = 'statustime';
    public $defaultSortDirection = 'DESC';
    public $objectType = 'goodnews.sendlog';

    const GON_USER_NOT_YET_SENT = 0;
    const GON_USER_SENT         = 1;
    const GON_USER_SEND_ERROR   = 2;
    const GON_USER_RESERVED     = 4;

    public function prepareQueryBeforeCount(xPDOQuery $c) {

        $c->select(array(
            'GoodNewsSubscriberLog.id',
            'GoodNewsSubscriberLog.mailing_id',
            'GoodNewsSubscriberLog.subscriber_id',
            'GoodNewsSubscriberLog.statustime',
            'GoodNewsSubscriberLog.status',
            'Profile.email AS subscriber_email',
            'Profile.fullname AS subscriber_fullname',
        ));
        $c->leftJoin('modUserProfile', 'Profile', 'GoodNewsSubscriberLog.subscriber_id = Profile.internalKey');

        $mailingid = $this->getProperty('mailingid', 0);
        if (!empty($mailingid)) {
            $c->where(array(
                'GoodNewsSubscriberLog.mailing_id' => $mailingid,
            ));
        }

        $statusfilter = $this->getProperty('statusfilter', '');
        if (!empty($statusfilter)) {
            $c->where(array(
                'GoodNewsSubscriberLog.status' => $statusfilter,
            ));
        }

        $query = $this->getProperty('query', '');
        if (!empty($query)) {
            $c->where(array(
                'Profile.email:LIKE' => '%'.$query.'%',
                'OR:Profile.fullname:LIKE' => '%'.$query.'%',
            ));
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $resourceArray = parent::prepareRow($object);

        $managerDateFormat = $this->modx->getOption('manager_date_format', null, 'Y-m-d');
        $managerTimeFormat = $this->modx->getOption('manager_time_format', null, 'H:i');
        $dateTimeFormat = $managerDateFormat.' '.$managerTimeFormat;

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
return 'SendLogGetListProcessor';
