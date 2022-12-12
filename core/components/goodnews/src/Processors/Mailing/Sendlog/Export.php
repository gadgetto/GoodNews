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

namespace GoodNews\Processors\Mailing\Sendlog;

use MODX\Revolution\modUserProfile;
use MODX\Revolution\Processors\Processor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;
use GoodNews\Model\GoodNewsSubscriberLog;

/**
 * SendLog export processor
 *
 * @package goodnews
 * @subpackage processors
 */

class Export extends Processor
{
    public $classKey = GoodNewsSubscriberLog::class;

    public const GON_USER_NOT_YET_SENT = 0;
    public const GON_USER_SENT         = 1;
    public const GON_USER_SEND_ERROR   = 2;
    public const GON_USER_RESERVED     = 4;

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process()
    {
        $c = $this->modx->newQuery($this->classKey);
        $c->select([
            'GoodNewsSubscriberLog.id',
            'GoodNewsSubscriberLog.mailing_id',
            'GoodNewsSubscriberLog.subscriber_id',
            'GoodNewsSubscriberLog.statustime',
            'GoodNewsSubscriberLog.status',
            'Profile.email AS subscriber_email',
            'Profile.fullname AS subscriber_fullname',
        ]);
        $c->leftJoin(modUserProfile::class, 'Profile', 'GoodNewsSubscriberLog.subscriber_id = Profile.internalKey');

        $mailingid = $this->getProperty('mailingid', 0);
        if (!empty($mailingid)) {
            $c->where([
                'GoodNewsSubscriberLog.mailing_id' => $mailingid,
            ]);
        } else {
            exit();
        }
        
        $c->sortby('GoodNewsSubscriberLog.statustime', 'DESC');
        
        // Get the send-log collection
        $sendlog = $this->modx->getCollection($this->classKey, $c);
        
        $exportfile = 'sendlog_' . $mailingid . '.csv';

        // CSV header array (field names)
        $header = [
            'idx',
            'subscriberid',
            'email',
            'fullname',
            'statustime',
            'status',
        ];
        
        // Generate the rows array
        $rows = [];
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
            $rows[] = [
                $idx,
                $subscriber_id,
                $subscriber_email,
                $subscriber_fullname,
                $statustime,
                $status,
            ];
        }

        header('Pragma: public');
        header('Expires: -1');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
        header('Content-Disposition: attachment; filename="' . $exportfile . '"');
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
    
    public function getLanguageTopics()
    {
        return ['goodnews:default'];
    }
}
