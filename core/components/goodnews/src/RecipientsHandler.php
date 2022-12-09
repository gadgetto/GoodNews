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
 * GoodNewsRecipientHandler class
 *
 * Handles selection of recipients based on groups and categories
 * + database management of the recipients lists.
 *
 * @package goodnews
 */

class GoodNewsRecipientHandler {
    
    const GON_USER_NOT_YET_SENT = 0;
    const GON_USER_SENT         = 1;
    const GON_USER_SEND_ERROR   = 2;
    const GON_USER_RESERVED     = 4;

    const PROCESS_TIMEOUT = 90;

    /** @var modX $modx A reference to the modX object */
    public $modx;

    private $recipientsCollection;
    private $recipientsTotal;
    
    /**
     * Constructor for GoodNewsRecipients object.
     *
     * @access public
     * @param modX &$modx A reference to the modX object
     */
    public function __construct(modX &$modx) {
        $this->modx = &$modx;

    }

    /**
     * Getter method for list of collected subscribers.
     * 
     * @access public
     * @return array recipientsCollection
     */
    public function getRecipientsCollection() {
        return $this->recipientsCollection;
    }
    
    /**
     * Getter method for total number of collected subscribers.
     * 
     * @access public
     * @return integer recipientsTotal
     */
    public function getRecipientsTotal() {
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
    public function collect($groups = array(), $categories = array()) {

        if (empty($groups)) {
            $groups = array('0');
        }
        if (empty($categories)) {
            $categories = array('0');
        }
        
        $recipients = array();
        $modxgrouprecipients = array();
            
        // Select subscribers based on groups/categories + assigned MODx user groups
        $tblUsers                  = $this->modx->getTableName('modUser');
        $tblUserProfile            = $this->modx->getTableName('modUserProfile');
        $tblGoodNewsGroupMember    = $this->modx->getTableName('GoodNewsGroupMember');
        $tblGoodNewsCategoryMember = $this->modx->getTableName('GoodNewsCategoryMember');
        
        $groupslist = implode(',', $groups);
        $categorieslist = implode(',', $categories);
        
        $sql = "SELECT DISTINCT {$tblUsers}.id
                FROM {$tblUsers} 
                LEFT JOIN {$tblUserProfile} ON {$tblUserProfile}.internalKey = {$tblUsers}.id
                LEFT JOIN {$tblGoodNewsGroupMember} ON {$tblGoodNewsGroupMember}.member_id = {$tblUsers}.id
                LEFT JOIN {$tblGoodNewsCategoryMember} ON {$tblGoodNewsCategoryMember}.member_id = {$tblUsers}.id
                WHERE ({$tblGoodNewsGroupMember}.goodnewsgroup_id IN ({$groupslist}) OR {$tblGoodNewsCategoryMember}.goodnewscategory_id IN ({$categorieslist}))
                AND {$tblUsers}.active = 1
                AND {$tblUserProfile}.blocked = 0";

        $query = $this->modx->query($sql);
        if ($query) {
            $users = $query->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $users = array();
        }

        // Initialize each userid with status GON_USER_NOT_YET_SENT + timestamp placeholder 0
        foreach ($users as $id) {
            $recipients[$id] = array(self::GON_USER_NOT_YET_SENT,0);
        }

        $modxgrouprecipients = $this->_collectModxGroupRecipients($groups);
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
    private function _collectModxGroupRecipients($groups = array()) {

        if (empty($groups)) {
            $groups = array('0');
        }

        $modxgrouprecipients = array();

        // Select subscribers based on groups/categories + assigned MODx user groups
        $tblUsers                  = $this->modx->getTableName('modUser');
        $tblUserGroupMember        = $this->modx->getTableName('modUserGroupMember');
        $tblGoodNewsGroup          = $this->modx->getTableName('GoodNewsGroup');

        $groupslist = implode(',', $groups);

        $sql = "SELECT DISTINCT {$tblUsers}.id
                FROM {$tblUsers} 
                INNER JOIN {$tblUserGroupMember} ON {$tblUserGroupMember}.member = {$tblUsers}.id
                INNER JOIN {$tblGoodNewsGroup} ON {$tblUserGroupMember}.user_group = {$tblGoodNewsGroup}.modxusergroup
                WHERE ({$tblGoodNewsGroup}.id IN ({$groupslist}))
                AND {$tblUsers}.active = 1";

        $query = $this->modx->query($sql);
        if ($query) {
            $users = $query->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $users = array();
        }

        // Initialize each userid with status GON_USER_NOT_YET_SENT + timestamp placeholder 0
        foreach ($users as $id) {
            $modxgrouprecipients[$id] = array(self::GON_USER_NOT_YET_SENT,0);
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
    public function saveRecipientsCollection($mailingId) {
                    
        $tblGoodNewsRecipients = $this->modx->getTableName('GoodNewsRecipient');

        $listChunks = array_chunk($this->recipientsCollection, 1000, true);

        foreach ($listChunks as $chunk) {
            
            $recipientsData = array(); // reset array
            foreach ($chunk as $userId => $values) {
                $recipientsData[] = "($mailingId,$userId,$values[0],$values[1])";
            }
            $sql = "INSERT INTO {$tblGoodNewsRecipients} (mailing_id,recipient_id,statustime,status) VALUES ".implode(',', $recipientsData);
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
    public function updateRecipientsCollection($mailingId) {
                    
        // first delete all existing entries for the give mailingId
        $tblGoodNewsRecipients = $this->modx->getTableName('GoodNewsRecipient');
        
        $sql = "DELETE FROM {$tblGoodNewsRecipients} WHERE mailing_id = $mailingId";
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
    public function getRecipientUnsent($mailingId) {

        $recipient = $this->modx->getObject('GoodNewsRecipient', array(
            'mailing_id' => $mailingId,
            'status'     => self::GON_USER_NOT_YET_SENT,
        ));
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
    public function getRecipientTimeout($mailingId) {

        $recipient = $this->modx->getObject('GoodNewsRecipient', array(
            'mailing_id' => $mailingId,
            'status'     => self::GON_USER_RESERVED,
        ));
        if (!is_object($recipient)) {
            return false;
        }
        $currentTime = time();
        $statusTime = strtotime($recipient->get('statustime')); // xPDO converts unixtimestamp into readable date so we have to convert it back to timestamp!
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
    public function getRecipientReserved($mailingId) {

        $recipient = $this->modx->getObject('GoodNewsRecipient', array(
            'mailing_id' => $mailingId,
            'status'     => self::GON_USER_RESERVED,
        ));
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
     * @return boolean
     */
    public function cleanupRecipient($recipientId, $mailingId, $status) {

        $recipient = $this->modx->getObject('GoodNewsRecipient', array(
            'mailing_id'   => $mailingId,
            'recipient_id' => $recipientId,
        ));
        if (!is_object($recipient)) {
            return false;
        }
        $currentTime = time();
        
        if ($recipient->remove()) {
            unset ($recipient);
            if ($this->_updateSubscriberLog($recipientId, $mailingId, $currentTime, $status)) {
                return true;
            }
        }        
        return false;
    }

    /**
     * Update the subscriber log.
     * 
     * @access private
     * @param integer $recipientId The id of the recipient
     * @param integer $mailingId The id of the mailing
     * @param integer $statusTime The statustime -> a unix timestamp
     * @param integer $status The status of the recipient
     * @return boolean
     */
    private function _updateSubscriberLog($recipientId, $mailingId, $statusTime, $status) {

        $log = $this->modx->newObject('GoodNewsSubscriberLog');
        $log->set('subscriber_id', $recipientId);
        $log->set('mailing_id',    $mailingId);
        $log->set('statustime',    $statusTime);
        $log->set('status',        $status);
        if (!$log->save()) {
            return false;
        }
        return true;
    }

}