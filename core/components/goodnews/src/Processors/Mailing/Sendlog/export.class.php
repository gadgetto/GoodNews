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
 * SendLog export processor
 *
 * @package goodnews
 * @subpackage processors
 */

class SendLogExportProcessor extends modObjectProcessor {    
    public $classKey = 'GoodNewsSubscriberLog';
    public $languageTopics = array('goodnews:default');
    public $objectType = 'goodnews.sendlog';

    const GON_USER_NOT_YET_SENT = 0;
    const GON_USER_SENT         = 1;
    const GON_USER_SEND_ERROR   = 2;
    const GON_USER_RESERVED     = 4;

    /**
     * {@inheritDoc}
     * @return mixed
     */
	public function process() {

        $c = $this->modx->newQuery($this->classKey);
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
        } else {
            exit();
        }
        
        $c->sortby('GoodNewsSubscriberLog.statustime','DESC');
        
        // Get the send-log collection        
        $sendlog = $this->modx->getCollection($this->classKey, $c);
        
        $exportfile = 'sendlog_'.$mailingid.'.csv';

        // CSV header array (field names)
        $header = array(
            'idx',
            'subscriberid',
            'email',
            'fullname',
            'statustime',
            'status',
        );
        
        // Generate the rows array
        $rows = array();
        $idx = 0;
        
        foreach ($sendlog as $line) {
            $idx += 1;
            $subscriber_id       = $line->get('subscriber_id');
            $subscriber_email    = $line->get('subscriber_email');
            $subscriber_fullname = $line->get('subscriber_fullname');
            $statustime          = $line->get('statustime');
            switch ($line->get('status')) {
                case self::GON_USER_SENT:
                    $status = $this->modx->lexicon('goodnews.sendlog_status_sent');
                    break;
                case self::GON_USER_SEND_ERROR:
                    $status = $this->modx->lexicon('goodnews.sendlog_status_send_error');
                    break;
                default:
                    $status = $this->modx->lexicon('goodnews.sendlog_status_unknown');
            }
            $rows[] = array(
                $idx,
                $subscriber_id,
                $subscriber_email,
                $subscriber_fullname,
                $statustime,
                $status,
            );
        }

		header('Pragma: public');
		header('Expires: -1');
		header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
		header('Content-Disposition: attachment; filename="'.$exportfile.'"');
        header('Content-Type: application/octet-stream');

        // Generate CSV file in memory (no physical file operation)
        $out = fopen('php://output', 'w');
        
        // Add header line
        fputcsv($out, $header);
        
        // Add export rows
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        
        fclose($out);
        die();
	}
}
return 'SendLogExportProcessor';
