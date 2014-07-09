<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 * (Loosely) based on code from PHPMailer-BMH (Bounce Mail Handler)
 * Copyright 2002-2009 by Andy Prevost <andy.prevost@worxteam.com>
 * Modified by bitego - 04/2014
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
 * GoodNews GoodNewsBounceMailHandler class.
 * Connects to IMAP, POP3 mailboxes and processes bounced emails.
 *
 * @package goodnews
 */
class GoodNewsBounceMailHandler {

    /** @var modX $modx A reference to the modX object */
    public $modx = null;

    /** @var array $config The config array */
    public $config = array();

    /** @var boolean $testmode Test mode, if true will not delete messages */
    public $testmode = false;
    
    /** @var boolean $debug Enabele/disable debug output */
    public $debug = false;    

    /** @var int $maxMailsBatchsize Maximum count of messages processed in one batch */
    public $maxMailsBatchsize = 50;
    
    /** @var boolean $disableDelete Enable/disable the message delete function */
    public $disableDelete = false;

    /** @var string $errorMsg A string containing the last error msg */
    public $errorMsg = null;
    
    /** @var string $mailService The service ('imap' or 'pop3') */
    public $mailService = 'imap';
    
    /** @var string $mailMailHost The mail server name or IP */
    public $mailMailHost = 'localhost';
    
    /** @var string $mailMailboxUsername The username for accessing the mailbox */
    public $mailMailboxUsername;
    
    /** @var string $mailMailboxPassword The password associated with the mailMailboxUsername */
    public $mailMailboxPassword;
    
    /** @var string $mailBoxname The mailbox name ('INBOX', 'Tasks', 'Spam', 'Replies', etc.) */
    public $mailBoxname = 'INBOX';

    /** @var string $mailPort The port number */
    public $mailPort = 143;
    
    /** @var string $mailServiceOption The service option ('none', 'notls', 'tls', 'ssl', ...) */
    public $mailServiceOption = 'notls';
    
    /** @var boolean $mailSoftBouncedMessageAction What to do with soft bounced messages after processing (move | delete) */
    public $mailSoftBouncedMessageAction = 'delete';
    
    /** @var string $mailSoftMailbox Mailbox folder to move soft bounces to */
    public $mailSoftMailbox = 'INBOX.Softbounces';
    
    /** @var int $mailMaxSoftBounces Maximum count of soft bounces */
    public $mailMaxSoftBounces = 3;
    
    /** @var boolean $mailMaxSoftBouncesAction What to do with subscribers after max soft bounces are reached (disable | delete) */
    public $mailMaxSoftBouncesAction = 'disable';
    
    /** @var boolean $mailHardBouncedMessageAction What to do with hard bounced messages after processing (move | delete) */
    public $mailHardBouncedMessageAction = 'delete';
    
    /** @var string $mailHardMailbox Mailbox folder to move soft bounces to */
    public $mailHardMailbox = 'INBOX.Hardbounces';
    
    /** @var int $mailMaxHardBounces Maximum count of hard bounces */
    public $mailMaxHardBounces = 1;
    
    /** @var boolean $mailMaxHardBouncesAction What to do with subscribers after max hard bounces are reached (disable | delete) */
    public $mailMaxHardBouncesAction = 'delete';
    
    /** @var boolean $mailNotClassifiedMessageAction What to do with unclassified messages (move | delete) */
    public $mailNotClassifiedMessageAction = 'move';
    
    /** @var string $mailNotClassifiedMailbox Mailbox folder to move unclassified messages to */
    public $mailNotClassifiedMailbox = 'INBOX.NotClassified';
    
    /** @var int $mailMaxSoftBounceLag Maximum lag between first an last soft bounce in hours */
    public $mailMaxSoftBounceLag = 72;
       
    /** @var int $mailMaxHardBounceLag Maximum lag between first an last hard bounce in hours */
    public $mailMaxHardBounceLag = 72;
       
    /** @var object $_imapStream The resource object for the opened mailbox (POP3/IMAP/NNTP/etc.) */
    private $_imapStream = false;

    /** @var int $_cTotal Count of messages found in mailbox */
    private $_cTotal = 0;

    /** @var int $_cFetch Count of messages to fetch from mailbox (limited by $maxMailsBatchsize) */
    private $_cFetch = 0;
    
    /** @var int $_cClassified Count of messages which could be classified */
    private $_cClassified = 0;

    /** @var int $_cUnclassified Count of messages which could not be classified */
    private $_cUnclassified = 0;
    
    /** @var int $_cDeleted Count of messages which were deleted */
    private $_cDeleted = 0;

    /** @var int $_cMoved Count of messages which were moved */
    private $_cMoved = 0;    


    /**
     * The constructor for the GoodNewsBounceMailHandler class.
     *
     * @param modX &$modx A reference to the modX instance.
     * @param array $config An array of configuration parameters.
     */
    function __construct(modX &$modx, array $config = array()) {
        $this->modx = &$modx;
        $corePath = $modx->getOption('goodnews.core_path', $config, $modx->getOption('core_path', null, MODX_CORE_PATH).'components/goodnews/');
        $this->config = array_merge(array(
            'corePath'   => $corePath,
            'modelPath'  => $corePath.'model/',
        ), $config);
        $this->modx->lexicon->load('goodnews:default');
        $this->_loadRulesSet();
    }

    /**
     * Load the rules set.
     *
	 * @access private
     * @return boolean
     */
    private function _loadRulesSet() {
        require_once $this->config['modelPath'].'goodnews/goodnewsbmh.dsnrules.php';
        require_once $this->config['modelPath'].'goodnews/goodnewsbmh.bodyrules.php';
    }

    /**
     * Getter for _cTotal (Count of messages found in mailbox)
     * 
     * @access public
     * @return int $this->_cTotal
     */
    public function get_cTotal() {
        return $this->_cTotal;
    }

    /**
     * Getter for _cFetch (Count of messages to fetch from mailbox)
     * 
     * @access public
     * @return int $this->_cFetch
     */
    public function get_cFetch() {
        return $this->_cFetch;
    }

    /**
     * Getter for _cClassified (Count of messages which could be classified)
     * 
     * @access public
     * @return int $this->_cClassified
     */
    public function get_cClassified() {
        return $this->_cClassified;
    }

    /**
     * Getter for _cUnclassified (Count of messages which could not be classified)
     * 
     * @access public
     * @return int $this->_cUnclassified
     */
    public function get_cUnclassified() {
        return $this->_cUnclassified;
    }

    /**
     * Getter for _cDeleted (Count of messages which were deleted)
     * 
     * @access public
     * @return int $this->_cDeleted
     */
    public function get_cDeleted() {
        return $this->_cDeleted;
    }

    /**
     * Getter for _cMoved (Count of messages which were moved)
     * 
     * @access public
     * @return int $this->_cMoved
     */
    public function get_cMoved() {
        return $this->_cMoved;
    }

    /**
     * Open a mail box (POP3 or IMAP).
     * Using POP3 results in limited features:
     *  - no move
     *  - no mailbox folders
     *
	 * @access public
     * @return null|resource
     */
    public function openImapStream() {
        if (stristr($this->mailMailHost, 'gmail') || $this->mailService == 'pop3') {
            $this->moveSoft = false;
            $this->moveHard = false;
        }
        if ($this->testmode) {
            $this->disableDelete = true;
        }

        if ($this->debug) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'mailMailHost: '.$this->mailMailHost.' mailService: '.$this->mailService.' mailServiceOption: '.$this->mailServiceOption.' mailPort: '.$this->mailPort.' mailBoxname: '.$this->mailBoxname);

        }
        $portstring = $this->mailPort.'/'.$this->mailService.'/'.$this->mailServiceOption;

        // First have a look if a mailbox is already open (and if so, close it)
		if ($this->_imapStream && (!is_resource($this->_imapStream) || !imap_ping($this->_imapStream))) {
			$this->closeImapStream();
		}

        if (!$this->testmode) {
            $this->_imapStream = @imap_open('{'.$this->mailMailHost.':'.$portstring.'}'.$this->mailBoxname, $this->mailMailboxUsername, $this->mailMailboxPassword, CL_EXPUNGE);
        } else {
            $this->_imapStream = @imap_open('{'.$this->mailMailHost.':'.$portstring.'}'.$this->mailBoxname, $this->mailMailboxUsername, $this->mailMailboxPassword);
        }
		if (!$this->_imapStream) {
			$this->errorMsg = imap_last_error();
		}
        return $this->_imapStream;
    }

	/**
	 * Close the current mailbox.
	 * 
	 * @access public
	 * @return void
	 */
	public function closeImapStream() {
		if ($this->_imapStream && is_resource($this->_imapStream)) {
            if (!$this->testmode) {
			    imap_close($this->_imapStream, CL_EXPUNGE);
			 } else {
			    imap_close($this->_imapStream);
			 }
		}
        $this->_imapStream = null;
	}

    /**
     * Check if a mailbox folder exists (only IMAP!).
     * - if not found, it will create it
     *
	 * @access public
     * @param string  $mailboxFolder The mailbox folder name (must be in 'INBOX.checkmailbox' format)
     * @param boolean $autocreate Whether or not to create the checkmailbox if not found (defaults to true)
     * @return boolean
     */
    public function mailboxFolderExists($mailboxFolder, $autocreate = true) {
        if (trim($mailboxFolder) == '' || !strstr($mailboxFolder, 'INBOX.') || $this->mailService == 'pop3') {
            return false;
        }
        $portstring = $this->mailPort.'/'.$this->mailService.'/'.$this->mailServiceOption;
        
        $imapStream = @imap_open('{'.$this->mailMailHost.':'.$portstring.'}'.$this->mailBoxname, $this->mailMailboxUsername, $this->mailMailboxPassword, OP_HALFOPEN);
        if (!$imapStream) {
			$this->errorMsg = imap_last_error();
			return false;
		}

        $folderList = imap_getmailboxes($imapStream,'{'.$this->mailMailHost.':'.$portstring.'}', "*");
        
        $mailboxFolderFound = false;

        if (is_array($folderList)) {
            foreach ($folderList as $key => $val) {
                // Get the mailbox name only
                $nameArr = explode('}', imap_utf7_decode($val->name));
                //echo print_r($nameArr, true).'<br>';
                $nameRaw = $nameArr[count($nameArr) - 1];
                if ($mailboxFolder == $nameRaw) {
                    $mailboxFolderFound = true;
                }
            }
        }

        if ((!$mailboxFolderFound) && $autocreate) {
            if (@imap_createmailbox($imapStream, imap_utf7_encode('{'.$this->mailMailHost.':'.$portstring.'}'.$mailboxFolder))) {
                imap_close($imapStream);
                return true;
            }
        }
        
        imap_close($imapStream);
        return false;
    }

    /**
     * Process mails in a mailbox.
     * (determine message type)
     *
	 * @access public
     * @return boolean
     */
    public function processMailbox() {
        // Error: No mailbox object available (not initialized!)
		if (!$this->_imapStream || !is_resource($this->_imapStream)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'GoodNewsBounceMailHandler::processMailbox() - init error: No imapStream open.');
		    return false;
		}

        // Initialize counters
        $this->_cTotal        = imap_num_msg($this->_imapStream);
        $this->_cFetch        = $this->_cTotal;
        $this->_cClassified   = 0;
        $this->_cUnclassified = 0;
        $this->_cDeleted      = 0;
        $this->_cMoved        = 0;
        
        // Maximum number of messages to process
        if ($this->_cFetch > $this->maxMailsBatchsize) { $this->_cFetch = $this->maxMailsBatchsize; }
        
        // Fetch one message per iteration
        for ($msgnr = 1; $msgnr <= $this->_cFetch; $msgnr++) {

            // Fetches all the structured information for a given message and returns the structure in an object
            $structure = imap_fetchstructure($this->_imapStream, $msgnr);
            
            // Is it a standard DSN msg?
            if ($structure->type == 1 && 
                $structure->ifsubtype && 
                strtolower($structure->subtype) == 'report' && 
                $structure->ifparameters && 
                $this->_isParameter($structure->parameters, 'REPORT-TYPE', 'delivery-status'))
            {
                $result = $this->_classifyBounceMessage($msgnr, 'DSN');
            
            // None standard DSN msg
            } else {
                $result = $this->_classifyBounceMessage($msgnr, 'BODY');
            }

            // If the message is classified -> process it!
            // We should now have the following array (a sample):
            /*
            array(
                 'rule_type'   => 'DSN'
                ,'email'       => 'name@domain.com'
                ,'user_id'     => '3425'
                ,'mailing_id'  => '21'
                ,'status_code' => '5.0.0'
                ,'diag_code'   => 'smtp; 554 mailbox not found'
                ,'rule_no'     => '0000'
                ,'time'        => '1392325284'
                ,'bounce_type' => 'hard'
            )
            */
            if (is_array($result) && $result['bounce_type']) {
            
                if ($result['bounce_type'] == 'soft') {
                    if ($this->mailSoftBouncedMessageAction == 'move') {
                        $this->_moveMessage($msgnr, $this->mailSoftMailbox);
                    } else {
                        $this->_deleteMessage($msgnr);
                    }
                } else {
                    if ($this->mailHardBouncedMessageAction == 'move') {
                        $this->_moveMessage($msgnr, $this->mailHardMailbox);
                    } else {
                        $this->_deleteMessage($msgnr);
                    }
                }

                // Now process the subscriber (if we have a userID)
                if ($result['user_id']) {
                    $this->addSubscriberBounce($result['user_id'], $result['time'], $result['bounce_type']);

                    if ($result['bounce_type'] == 'soft') {
                        $subscriberSoftBounceCounter = $this->getSubscriberBounceCounter($result['user_id'], 'soft');
                        $subscriberSoftBounceLag     = $this->getSubscriberBounceLag($result['user_id'], 'soft');
                        if ($subscriberSoftBounceCounter > $this->mailMaxSoftBounces && $subscriberSoftBounceLag > $this->mailMaxSoftBounceLag) {
                            if ($this->mailMaxSoftBouncesAction == 'disable') {
                                $this->disableSubscriber($result['user_id']);
                            } else {
                                $this->deleteSubscriber($result['user_id']);
                            }
                        }
                    } else {
                        $subscriberHardBounceCounter = $this->getSubscriberBounceCounter($result['user_id'], 'hard');
                        $subscriberHardBounceLag     = $this->getSubscriberBounceLag($result['user_id'], 'hard');
                        if ($subscriberHardBounceCounter > $this->mailMaxHardBounces && $subscriberHardBounceLag > $this->mailMaxHardBounceLag) {
                            if ($this->mailMaxHardBouncesAction == 'disable') {
                                $this->disableSubscriber($result['user_id']);
                            } else {
                                $this->deleteSubscriber($result['user_id']);
                            }
                        }
                    }
                }
                
                // At last process the mailing (if we have a mailingID)
                if ($result['mailing_id']) {
                    $this->increaseMailingBounceCounter($result['mailing_id'], $result['bounce_type']);
                }            

                $this->_cClassified++;
                        
            // Delete or move messages which couldn't be classified/processed:
            //  - messages which have no usable information about the bounce reason
            //  - completely empty messages
            //  - messages which are not bounce messages in general (e.g. someone manually sent a mail to the bounce mailbox)
            } else {
                if ($this->mailNotClassifiedMessageAction == 'move') {
                    $this->_moveMessage($msgnr, $this->mailNotClassifiedMailbox);
                } else {
                    $this->_deleteMessage($msgnr);
                }
                $this->_cUnclassified++;
            }
        
        } // end: for
        
        $this->closeImapStream();
        return true;
    }
    
    /**
     * Move a message.
     * 
     * @access private
     * @param mixed $msgnr The number of the message.
     * @param mixed $mailboxFolder The name of the mailbox folder.
     * @return void
     */
    private function _moveMessage($msgnr, $mailboxFolder) {
        // Check if the mail folder exists, if not create it
        $this->mailboxFolderExists($mailboxFolder);
        @imap_mail_move($this->_imapStream, $msgnr, $mailboxFolder);
        $this->_cMoved++;
    }
    
    /**
     * Delete a message.
     * 
     * @access private
     * @param mixed $msgnr The number of the message.
     * @return void
     */
    private function _deleteMessage($msgnr) {
        if (!$this->testmode && !$this->disableDelete) {
            @imap_delete($this->_imapStream, $msgnr);
            $this->_cDeleted++;
        }
    }

    /**
     * Method to classify each individual message and determine:
     * - the bounce type using rules
     * - the modx resourceid of the mailing
     * - the modx userid of the original recipient 
     * - the email address of the original recipient
     *
     * (here we process the body of the message)
     *
	 * @access private
     * @param int $msgnr The message number
     * @param string $type DNS or BODY type
     * @return mixed array $result | false
     */
    private function _classifyBounceMessage($msgnr, $type) {
        if ($type == 'DSN') {

            // First part of DSN (Delivery Status Notification), human-readable explanation
            $dsnMsg          = imap_fetchbody($this->_imapStream, $msgnr, '1');
            $dsnMsgStructure = imap_bodystruct($this->_imapStream, $msgnr, '1');
            
            switch ($dsnMsgStructure->encoding) {
                case 3: // Encoding = BASE64
                    $dsnMsg = base64_decode($dsnMsg);
                    break;
                case 4: // Encoding = QUOTED-PRINTABLE
                    $dsnMsg = quoted_printable_decode($dsnMsg);
                    break;
                }
            
            // Second part of DSN (Delivery Status Notification), delivery-status
            $dsnReport = imap_fetchbody($this->_imapStream, $msgnr, '2');
            
            // Determine bounce type
            $result = bmhDSNRules($dsnMsg, $dsnReport);
            
        } else {

            $structure = imap_fetchstructure($this->_imapStream, $msgnr);
            
            switch ($structure->type) {
                case 0: // Content-type = text
                    $body = imap_fetchbody($this->_imapStream, $msgnr, '1');
                    break;
                    
                case 1: // Content-type = multipart
                    $body = imap_fetchbody($this->_imapStream, $msgnr, '1');

                    // Detect encoding and decode - only base64
                    if ($structure->parts[0]->encoding == 4) {
                        $body = quoted_printable_decode($body);
                    } elseif ($structure->parts[0]->encoding == 3) {
                        $body = base64_decode($body);
                    }
                    break;
                    
                case 2: // Content-type = message
                    $body = imap_body($this->_imapStream, $msgnr);

                    if ($structure->encoding == 4) {
                        $body = quoted_printable_decode($body);
                    } elseif ($structure->encoding == 3) {
                        $body = base64_decode($body);
                    }
                    break;
                    
                default: // Content-type unsupported
                    return false;
            }
            
            // Determine bounce type
            $result = bmhBodyRules($body);
        }

        // Get custom X-headers:
        // Scan message source for X-goodnews-user-id and X-goodnews-mailing-id
        $source = imap_body($this->_imapStream, $msgnr);
        if (preg_match('/X-goodnews-mailing-id[: \t\n]+([0-9]+)/i', $source, $match)) {
            $result['mailing_id'] = $match[1];
        }
        if (preg_match('/X-goodnews-user-id[: \t\n]+([0-9]+)/i', $source, $match)) {
            $result['user_id'] = $match[1];
        }
        
        // If we couldn't find the X-header with the user_id, try to find it by the fetched email address
        if (empty($result['user_id']) && $result['email']) {
            $result['user_id'] = $this->getSubscriberID($result['email']);
        }
        
        // Get the sending date of the bounce message
        $msgheader = imap_headerinfo($this->_imapStream, $msgnr);
        $result['time'] = $msgheader->udate;
        
        // Debug output
        if ($this->debug) {
            $this->modx->log(modX::LOG_LEVEL_INFO, print_r($result, true));
            if ($result['bounce_type'] == false) {
                $mailSource = imap_fetchheader($this->_imapStream, $msgnr).PHP_EOL.imap_body($this->_imapStream, $msgnr);
                $this->modx->log(modX::LOG_LEVEL_INFO, quoted_printable_decode($mailSource));
            }
        }
        
        if ($result['bounce_type'] == false) {
            return false;
        }
        return $result; // result array
    }

    /**
     * Function to determine if a particular value is found in an imap_fetchstructure key
     *
     * @access private
     * @param array $currParameters imap_fetstructure parameters
     * @param string $varKey imap_fetstructure key
     * @param string $varValue value to check for
     * @return boolean
     */
    private function _isParameter($currParameters, $varKey, $varValue) {
        $varKey = strtolower($varKey);
        $varValue = strtolower($varValue);
        foreach ($currParameters as $param) {
            if (strtolower($param->attribute) == $varKey) {
                if (strtolower($param->value) == $varValue) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Determine the id of a subscriber (= MODX userID) by given email address.
     * 
     * @access public
     * @param string $email The email address
     * @return mixed subscriberID | false
     */
    public function getSubscriberID($email) {
        $profile = $this->modx->getObject('modUserProfile', array('email' => $email));
        if (!is_object($profile)) {
            if ($this->debug) {
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not determine subscriber ID. Subscriber with email address: '.$email.' not found.');
            }
		    return false;
        }
        return $profile->get('internalKey');
    }

    /**
     * Method to get the bounce counters from subscribers meta table (hard/soft).
     *
     * @access public
     * @param integer $subscriberId The ID of the subscriber ( = MODX userID)
     * @param string $bounceType The bounce type (hard || soft)
     * @return bounce counter or false
     */
    public function getSubscriberBounceCounter($subscriberId, $bounceType) {
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberId));
        if (!is_object($meta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not read bounce counter. Subscriber with ID: '.$subscriberId.' not found.');
		    return false;
        }

        switch ($bounceType) {
            case 'soft';
                $softBounces = unserialize($meta->get('soft_bounces'));
                if (!is_array($softBounces)) {
                    return 0;
                }
                return count($softBounces);
                break;
                
            case 'hard';
                $hardBounces = unserialize($meta->get('hard_bounces'));
                if (!is_array($hardBounces)) {
                    return 0;
                }
                return count($hardBounces);
                break;
            }
        
        return false;
    }

    /**
     * Method to get the lag between first and last bounce from subscribers meta table (hard/soft).
     *
     * @access public
     * @param integer $subscriberId The ID of the subscriber ( = MODX userID)
     * @param string $bounceType The bounce type (hard || soft)
     * @return bounce delay in hours (rounded) or false
     */
    public function getSubscriberBounceLag($subscriberId, $bounceType) {
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberId));
        if (!is_object($meta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not read bounce delay. Subscriber with ID: '.$subscriberId.' not found.');
		    return false;
        }

        switch ($bounceType) {
            case 'soft';
                $softBounces = unserialize($meta->get('soft_bounces'));
                if (!is_array($softBounces)) {
                    break;
                }
                $min = min($softBounces);
                $max = max($softBounces);
                return round(abs($max - $min)/3600);
                break;
                
            case 'hard';
                $hardBounces = unserialize($meta->get('hard_bounces'));
                if (!is_array($hardBounces)) {
                    break;
                }
                $min = min($hardBounces);
                $max = max($hardBounces);
                return round(abs($max - $min)/3600);
                break;
            }
        
        return false;
    }

    /**
     * Method to add a bounce time-stamp to subscribers meta table (hard/soft).
     * 
     * The soft_bounces and hard_bounces fields each holds an serialized array of timestamps.
     * Each of those timestamps marks an occurence of a bounce.
     *
     * @access public
     * @param integer $subscriberId The ID of the subscriber ( = MODX userID)
     * @param string $timeStamp The sending-date of the bounce mail as unix time stamp (default: time())
     * @param string $bounceType The bounce type (hard || soft)
     * @return boolean
     */
    public function addSubscriberBounce($subscriberId, $timeStamp, $bounceType) {
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberId));
        if (!is_object($meta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not add bounce. Subscriber with ID: '.$subscriberId.' not found.');
		    return false;
        }

        switch ($bounceType) {
            case 'soft';
                $softBounces = unserialize($meta->get('soft_bounces'));
                $softBounces[] = $timeStamp;
                $meta->set('soft_bounces', serialize($softBounces));
                break;
                
            case 'hard';
                $hardBounces = unserialize($meta->get('hard_bounces'));
                $hardBounces[] = $timeStamp;
                $meta->set('hard_bounces', serialize($hardBounces));
                break;
        }

        if (!$meta->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'GoodNewsBounceMailHandler::addSubscriberBounce() - save error: Could not add bounce.');
		    return false;
        }
        return true;
    }

    /**
     * Method to reset the bounces in subscribers meta table (hard/soft).
     * 
     * @access public
     * @param integer $subscriberId The ID of the subscriber ( = MODX userID)
     * @param string $bounceType The bounce type (hard || soft || default = both)
     * @return boolean
     */
    public function resetSubscriberBounces($subscriberId, $bounceType = false) {
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberId));
        if (!is_object($meta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not reset bounces. Subscriber with ID: '.$subscriberId.' not found.');
		    return false;
        }

        switch ($bounceType) {
            case 'soft';
                $meta->set('soft_bounces', '');
                break;
                
            case 'hard';
                $meta->set('hard_bounces', '');
                break;
            
            default:
                $meta->set('soft_bounces', '');
                $meta->set('hard_bounces', '');
                break;
        }
        
        if (!$meta->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'GoodNewsBounceMailHandler::resetSubscriberBounces() - save error: Could not reset bounces.');
		    return false;
        }
        return true;
    }

    /**
     * Disable a subscriber.
     * 
     * @access public
     * @param integer $subscriberId The ID of the subscriber ( = MODX userID)
     * @return boolean
     */
    public function disableSubscriber($subscriberId) {
        // modUser must not:
        // - be in a MODX group
        // - be sudo
        $subscriber = $this->modx->getObject('modUser', array('id' => $subscriberId, 'primary_group' => 0, 'sudo' => 0));
        if (!is_object($subscriber)) { return false; }

        $subscriber->set('active', 0);
        if (!$subscriber->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'GoodNewsBounceMailHandler::disableSubscriber() - save error: Could not deactivate user.');
            return false;
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'GoodNewsBounceMailHandler::disableSubscriber() - disabled user ID: '.$subscriberId. ' (too many bounces)');
        return true;
    }

    /**
     * Delete a subscriber.
     * 
     * @access public
     * @param integer $subscriberId The ID of the subscriber ( = MODX userID)
     * @return boolean
     */
    public function deleteSubscriber($subscriberId) {
        // modUser must not:
        // - be in a MODX group
        // - be sudo
        $subscriber = $this->modx->getObject('modUser', array('id' => $subscriberId, 'primary_group' => 0, 'sudo' => 0));
        if (!is_object($subscriber)) { return false; }

        if (!$subscriber->remove()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'GoodNewsBounceMailHandler::disableSubscriber() - remove error: Could not delete user.');
            return false;
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'GoodNewsBounceMailHandler::deleteSubscriber() - deleted user ID: '.$subscriberId. ' (too many bounces)');
        return true;
    }

    /**
     * Method to increase the bounce counters in mailing meta table (hard/soft).
     * 
     * @access public
     * @param integer $mailingId The ID of the mailing
     * @param string $bounceType The bounce type (hard || soft)
     * @return boolean
     */
    public function increaseMailingBounceCounter($mailingId, $bounceType) {
        $meta = $this->modx->getObject('GoodNewsMailingMeta', array('mailing_id' => $mailingId));
        if (!is_object($meta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not increase bounce counter. Mailing with ID: '.$mailingId.' not found.');
		    return false;
        }

        switch ($bounceType) {
            case 'soft';
                $softBounces = $meta->get('soft_bounces');
                $softBounces++;
                $meta->set('soft_bounces', $softBounces);
                break;
                
            case 'hard';
                $hardBounces = $meta->get('hard_bounces');
                $hardBounces++;
                $meta->set('hard_bounces', $hardBounces);
                break;
        }

        if (!$meta->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'GoodNewsBounceMailHandler::increaseMailingBounceCounter() - save error: Could not increase bounce counter.');
		    return false;
        }
        return true;
    }

    /**
     * Get all mailing resource container ids where bounce handling is enabled.
     *
     * @access public
     * @return mixed array $containerIDs || false
     */
    public function getGoodNewsBmhContainers() {
        $c = $this->modx->newQuery('modResource');
        $c->where(array(
            'published' => true,
            'deleted'   => false,
            'class_key' => 'GoodNewsResourceContainer'
        ));
        $containers = $this->modx->getCollection('modResource', $c); //todo: use getIterator!

        $containerIDs = array();
        foreach ($containers as $container) {
            $id = $container->get('id');
            $goodnewscontainer = $this->modx->getObject('GoodNewsResourceContainer', $id);
            if (!is_object($goodnewscontainer)) { return false; }
            
            $mailBounceHandling = $goodnewscontainer->getProperty('mailBounceHandling', 'goodnews', 0);   
            if ($mailBounceHandling) {
                $containerIDs[] = $id;
            }
        }
        return $containerIDs;
    }
    
    /**
     * Get the bounce mail handling properties from container and set them as class properties.
     * 
     * @access public
     * @param mixed $id
     * @return void
     */
    public function getBmhContainerProperties($id = 0) {

        $goodnewscontainer = $this->modx->getObject('GoodNewsResourceContainer', $id);
        if (!is_object($goodnewscontainer)) { return false; }
                  
        $this->mailService                    = $goodnewscontainer->getProperty('mailService',                   'goodnews', 'imap');
        $this->mailMailHost                   = $goodnewscontainer->getProperty('mailMailHost',                  'goodnews', 'localhost');
        $this->mailMailboxUsername            = $goodnewscontainer->getProperty('mailMailboxUsername',           'goodnews');
        $this->mailMailboxPassword            = $goodnewscontainer->getProperty('mailMailboxPassword',           'goodnews');
        $this->mailBoxname                    = $goodnewscontainer->getProperty('mailBoxname',                   'goodnews', 'INBOX');
        $this->mailPort                       = $goodnewscontainer->getProperty('mailPort',                      'goodnews', 143);
        $this->mailServiceOption              = $goodnewscontainer->getProperty('mailServiceOption',             'goodnews', 'notls');
        $this->mailSoftBouncedMessageAction   = $goodnewscontainer->getProperty('mailSoftBouncedMessageAction',  'goodnews', 'delete');
        $this->mailSoftMailbox                = $goodnewscontainer->getProperty('mailSoftMailbox',               'goodnews', 'INBOX.Softbounces');
        $this->mailMaxSoftBounces             = $goodnewscontainer->getProperty('mailMaxSoftBounces',            'goodnews', 3);
        $this->mailMaxSoftBouncesAction       = $goodnewscontainer->getProperty('mailMaxSoftBouncesAction',      'goodnews', 'disable');
        $this->mailHardBouncedMessageAction   = $goodnewscontainer->getProperty('mailHardBouncedMessageAction',  'goodnews', 'delete');
        $this->mailHardMailbox                = $goodnewscontainer->getProperty('mailHardMailbox',               'goodnews', 'INBOX.Hardbounces');
        $this->mailMaxHardBounces             = $goodnewscontainer->getProperty('mailMaxHardBounces',            'goodnews', 1);
        $this->mailMaxHardBouncesAction       = $goodnewscontainer->getProperty('mailMaxHardBouncesAction',      'goodnews', 'delete');
        $this->mailNotClassifiedMessageAction = $goodnewscontainer->getProperty('mailNotClassifiedMessageAction','goodnews', 'move');
        $this->mailNotClassifiedMailbox       = $goodnewscontainer->getProperty('mailNotClassifiedMailbox',      'goodnews', 'INBOX.NotClassified');   
    }

}
