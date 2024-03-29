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

namespace Bitego\GoodNews;

use MODX\Revolution\modX;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserProfile;
use MODX\Revolution\modUserGroupMember;
use Bitego\GoodNews\Model\GoodNewsRecipient;
use Bitego\GoodNews\Model\GoodNewsGroup;
use Bitego\GoodNews\Model\GoodNewsGroupMember;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;
use Bitego\GoodNews\Model\GoodNewsSubscriberLog;

/**
 * RecipientsHandler class.
 *
 * Handles selection of recipients based on groups and categories
 * + database management of the recipients lists.
 *
 * @package goodnews
 */

class RecipientsHandler
{
    public const GON_USER_NOT_YET_SENT = 0;
    public const GON_USER_SENT         = 1;
    public const GON_USER_SEND_ERROR   = 2;
    public const GON_USER_RESERVED     = 4;

    public const PROCESS_TIMEOUT = 90;

    /** @var modX $modx A reference to the modX object */
    public $modx = null;

    private $recipientsCollection;
    private $recipientsTotal;

    /**
     * Constructor for GoodNewsRecipients object.
     *
     * @access public
     * @param modX &$modx A reference to the modX object
     */
    public function __construct(modX &$modx)
    {
        $this->modx = &$modx;
    }

    /**
     * Getter method for list of collected subscribers.
     *
     * @access public
     * @return array recipientsCollection
     */
    public function getRecipientsCollection()
    {
        return $this->recipientsCollection;
    }

    /**
     * Getter method for total number of collected subscribers.
     *
     * @access public
     * @return integer recipientsTotal
     */
    public function getRecipientsTotal()
    {
        return $this->recipientsTotal;
    }

    /**
     * Collect recipients based on groups and categories and MODx user groups
     *
     * @access public
     * @param array $groups Array of group ids
     * @param array $categories Array of category ids
     * @return void
     */
    public function collect($groups = [], $categories = [])
    {
        if (empty($groups)) {
            $groups = ['0'];
        }
        if (empty($categories)) {
            $categories = ['0'];
        }

        $recipients = [];
        $modxgrouprecipients = [];

        // Select subscribers based on groups/categories + assigned MODx user groups
        $tblUsers                  = $this->modx->getTableName(modUser::class);
        $tblUserProfile            = $this->modx->getTableName(modUserProfile::class);
        $tblGoodNewsGroupMember    = $this->modx->getTableName(GoodNewsGroupMember::class);
        $tblGoodNewsCategoryMember = $this->modx->getTableName(GoodNewsCategoryMember::class);

        $groupslist = implode(',', $groups);
        $categorieslist = implode(',', $categories);

        $sql = "SELECT DISTINCT {$tblUsers}.id
                FROM {$tblUsers} 
                LEFT JOIN {$tblUserProfile} ON {$tblUserProfile}.internalKey = {$tblUsers}.id
                LEFT JOIN {$tblGoodNewsGroupMember} ON {$tblGoodNewsGroupMember}.member_id = {$tblUsers}.id
                LEFT JOIN {$tblGoodNewsCategoryMember} ON {$tblGoodNewsCategoryMember}.member_id = {$tblUsers}.id
                WHERE ({$tblGoodNewsGroupMember}.goodnewsgroup_id IN ({$groupslist})
                OR {$tblGoodNewsCategoryMember}.goodnewscategory_id IN ({$categorieslist}))
                AND {$tblUsers}.active = 1
                AND {$tblUserProfile}.blocked = 0";

        $query = $this->modx->query($sql);
        if ($query) {
            $users = $query->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            $users = [];
        }

        // Initialize each userid with status GON_USER_NOT_YET_SENT + timestamp placeholder 0
        foreach ($users as $id) {
            $recipients[$id] = [self::GON_USER_NOT_YET_SENT, 0];
        }

        $modxgrouprecipients = $this->collectModxGroupRecipients($groups);
        $recipients += $modxgrouprecipients; // also removes duplicated ids

        //$this->setList(serialize($recipients));
        $this->recipientsCollection = $recipients;
        $this->recipientsTotal = count($recipients);
    }

    /**
     * Collect recipients from MODX user groups
     * (if goodnews group is assigned to MODx user group)
     *
     * @access private
     * @param array $groups Array of group ids
     * @return array $modxgrouprecipients
     */
    private function collectModxGroupRecipients($groups = [])
    {
        if (empty($groups)) {
            $groups = ['0'];
        }

        $modxgrouprecipients = [];

        // Select subscribers based on groups/categories + assigned MODx user groups
        $tblUsers                  = $this->modx->getTableName(modUser::class);
        $tblUserGroupMember        = $this->modx->getTableName(modUserGroupMember::class);
        $tblGoodNewsGroup          = $this->modx->getTableName(GoodNewsGroup::class);

        $groupslist = implode(',', $groups);

        $sql = "SELECT DISTINCT {$tblUsers}.id
                FROM {$tblUsers} 
                INNER JOIN {$tblUserGroupMember} ON {$tblUserGroupMember}.member = {$tblUsers}.id
                INNER JOIN {$tblGoodNewsGroup} ON {$tblUserGroupMember}.user_group = {$tblGoodNewsGroup}.modxusergroup
                WHERE ({$tblGoodNewsGroup}.id IN ({$groupslist}))
                AND {$tblUsers}.active = 1";

        $query = $this->modx->query($sql);
        if ($query) {
            $users = $query->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            $users = [];
        }

        // Initialize each userid with status GON_USER_NOT_YET_SENT + timestamp placeholder 0
        foreach ($users as $id) {
            $modxgrouprecipients[$id] = [self::GON_USER_NOT_YET_SENT, 0];
        }

        return $modxgrouprecipients;
    }

    /**
     * Saves collection of recipients to database to be later used for sending mails.
     * (The list can be huge so we need to bulk insert to gain performance)
     *
     * @access public
     * @param integer $mailingId The ID of the mailing resource
     * @return void
     */
    public function saveRecipientsCollection($mailingId)
    {
        $tblGoodNewsRecipients = $this->modx->getTableName(GoodNewsRecipient::class);

        $listChunks = array_chunk($this->recipientsCollection, 1000, true);

        foreach ($listChunks as $chunk) {
            $recipientsData = []; // reset array
            foreach ($chunk as $userId => $values) {
                $recipientsData[] = "($mailingId,$userId,$values[0],$values[1])";
            }
            $sql = "INSERT INTO {$tblGoodNewsRecipients} (mailing_id,recipient_id,statustime,status) VALUES " .
                implode(',', $recipientsData);
            $query = $this->modx->query($sql);
        }
    }

    /**
     * Updates collection of recipients in database to be later used for sending mails.
     *
     * @access public
     * @param integer $mailingId The ID of the mailing resource
     * @return void
     */
    public function updateRecipientsCollection($mailingId)
    {

        // first delete all existing entries for the give mailingId
        $tblGoodNewsRecipients = $this->modx->getTableName(GoodNewsRecipient::class);

        $sql = "DELETE FROM {$tblGoodNewsRecipients} WHERE mailing_id = {$mailingId}";
        $query = $this->modx->query($sql);

        // now save updated list
        $this->saveRecipientsCollection($mailingId);
    }

    /**
     * Get the next recipient with status GON_USER_NOT_YET_SENT from database and reserve.
     *
     * @access public
     * @param integer $mailingId
     * @return mixed integer recipient_id || false
     */
    public function getRecipientUnsent($mailingId)
    {
        $recipient = $this->modx->getObject(GoodNewsRecipient::class, [
            'mailing_id' => $mailingId,
            'status'     => self::GON_USER_NOT_YET_SENT,
        ]);
        if (!is_object($recipient)) {
            return false;
        }
        $currentTime = time();
        $recipient->set('statustime', $currentTime);
        $recipient->set('status', self::GON_USER_RESERVED);
        $recipient->save();

        $recipientId = $recipient->get('recipient_id');
        unset($recipient);

        return $recipientId;
    }

    /**
     * Get the next recipient with status "reserved" from database and
     * check if reservation timestamp is too old, which means a task could not
     * send within 90 seconds.
     *
     * @access public
     * @param integer $mailingId
     * @return mixed integer recipient_id || false
     */
    public function getRecipientTimeout($mailingId)
    {
        $recipient = $this->modx->getObject(GoodNewsRecipient::class, [
            'mailing_id' => $mailingId,
            'status'     => self::GON_USER_RESERVED,
        ]);
        if (!is_object($recipient)) {
            return false;
        }
        $currentTime = time();
        // xPDO converts unixtimestamp into readable date so we have to convert it back to timestamp!
        $statusTime = strtotime($recipient->get('statustime'));
        $recipientId = $recipient->get('recipient_id');

        // Check if reservation timestamp is too old
        if ($statusTime < ($currentTime - self::PROCESS_TIMEOUT)) {
            return $recipientId;
        }
        return false;
    }

    /**
     * Get the next recipient with status "reserved" from database.
     *
     * @access public
     * @param integer $mailingId
     * @return mixed integer recipient_id || false
     */
    public function getRecipientReserved($mailingId)
    {
        $recipient = $this->modx->getObject(GoodNewsRecipient::class, [
            'mailing_id' => $mailingId,
            'status'     => self::GON_USER_RESERVED,
        ]);
        if (!is_object($recipient)) {
            return false;
        }
        $recipientId = $recipient->get('recipient_id');
        unset($recipient);

        return $recipientId;
    }

    /**
     * Cleanup a recipient.
     *
     * - remove recipient entry and
     * - write back status to subscriber_log
     *
     * @access public
     * @param integer $recipientId The id of the recipient
     * @param integer $mailingId The id of the mailing
     * @param integer $status The status of the recipient
     * @param string $log The status/error text
     * @return boolean
     */
    public function cleanupRecipient($recipientId, $mailingId, $status, string $log = '')
    {
        $recipient = $this->modx->getObject(GoodNewsRecipient::class, [
            'mailing_id'   => $mailingId,
            'recipient_id' => $recipientId,
        ]);
        if (!is_object($recipient)) {
            return false;
        }
        $currentTime = time();

        if ($recipient->remove()) {
            unset($recipient);
            if ($this->writeLog($recipientId, $mailingId, $currentTime, $status, $log)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create subscriber log entry.
     *
     * @access private
     * @param integer $recipientId The id of the recipient
     * @param integer $mailingId The id of the mailing
     * @param integer $statusTime The statustime -> a unix timestamp
     * @param integer $status The status of the recipient
     * @param string $log The status/error text
     * @return boolean
     */
    private function writeLog($recipientId, $mailingId, $statusTime, $status, string $log = '')
    {
        $subscriberlog = $this->modx->newObject(GoodNewsSubscriberLog::class);
        $subscriberlog->set('subscriber_id', $recipientId);
        $subscriberlog->set('mailing_id', $mailingId);
        $subscriberlog->set('statustime', $statusTime);
        $subscriberlog->set('status', $status);
        $subscriberlog->set('log', $log);
        if (!$subscriberlog->save()) {
            return false;
        }
        return true;
    }
}
