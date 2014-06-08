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
    
    /** @var boolean $debug_rules Enabele/disable rules debug output */
    public $debug_rules = false;    

    /** @var int $max_mails_batchsize Maximum count of messages processed in one batch */
    public $max_mails_batchsize = 50;
    
    /** @var boolean $disable_delete Enable/disable the message delete function */
    public $disable_delete = false;

    /** @var boolean $purge_unprocessed Purge the unknown messages (or not) */
    public $purge_unprocessed = false;

    /** @var string $error_msg A string containing the last error msg */
    public $error_msg = null;
    
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
    
    /** @var int $mailMaxSoftBounceLag Maximum lag between first an last soft bounce in hours */
    public $mailMaxSoftBounceLag = 72;
       
    /** @var int $mailMaxHardBounceLag Maximum lag between first an last hard bounce in hours */
    public $mailMaxHardBounceLag = 72;
       
    /** @var object $_mailbox_object The resource object for the opened mailbox (POP3/IMAP/NNTP/etc.) */
    private $_mailbox_object = false;

    /** @var int $_cTotal Count of messages found in mailbox */
    private $_cTotal = 0;

    /** @var int $_cFetch Count of messages to fetch from mailbox (limited by $max_mails_batchsize) */
    private $_cFetch = 0;
    
    /** @var int $_cProcessed Count of messages which were processed */
    private $_cProcessed = 0;

    /** @var int $_cUnprocessed Count of messages which were not processed */
    private $_cUnprocessed = 0;
    
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
     * Getter for _cProcessed (Count of messages which were processed)
     * 
     * @access public
     * @return int $this->_cProcessed
     */
    public function get_cProcessed() {
        return $this->_cProcessed;
    }

    /**
     * Getter for _cUnprocessed (Count of messages which were not processed)
     * 
     * @access public
     * @return int $this->_cUnprocessed
     */
    public function get_cUnprocessed() {
        return $this->_cUnprocessed;
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
     * Open a mail box.
     * (This is also the init method of the class)
     *
	 * @access public
     * @return null|resource
     */
    public function openMailbox() {
        if (stristr($this->mailMailHost, 'gmail')) {
            $this->moveSoft = false;
            $this->moveHard = false;
        }
        if ($this->testmode) {
            $this->disable_delete = true;
        }

        $portstring = $this->mailPort.'/'.$this->mailService.'/'.$this->mailServiceOption;

        // First have a look if a mailbox is already open (and if so close it)
		if ($this->_mailbox_object && (!is_resource($this->_mailbox_object) || !imap_ping($this->_mailbox_object))) {
			$this->closeMailbox();
		}

        if (!$this->testmode) {
            $this->_mailbox_object = @imap_open('{'.$this->mailMailHost.':'.$portstring.'}'.$this->mailBoxname, $this->mailMailboxUsername, $this->mailMailboxPassword, CL_EXPUNGE);
        } else {
            $this->_mailbox_object = @imap_open('{'.$this->mailMailHost.':'.$portstring.'}'.$this->mailBoxname, $this->mailMailboxUsername, $this->mailMailboxPassword);
        }
		if (!$this->_mailbox_object) {
			$this->error_msg = imap_last_error();
		}
        return $this->_mailbox_object;
    }

	/**
	 * Close the current mailbox.
	 * 
	 * @access public
	 * @return void
	 */
	public function closeMailbox() {
		if ($this->_mailbox_object && is_resource($this->_mailbox_object)) {
            if (!$this->testmode) {
			    imap_close($this->_mailbox_object, CL_EXPUNGE);
			 } else {
			    imap_close($this->_mailbox_object);
			 }
		}
        $this->_mailbox_object = null;
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
		if (!$this->_mailbox_object || !is_resource($this->_mailbox_object)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'GoodNewsBounceMailHandler::processMailbox() - init error: No mailbox open.');
		    return false;
		}

        // Initialize counters
        $this->_cTotal       = imap_num_msg($this->_mailbox_object);
        $this->_cFetch       = $this->_cTotal;
        $this->_cProcessed   = 0;
        $this->_cUnprocessed = 0;
        $this->_cDeleted     = 0;
        $this->_cMoved       = 0;
        
        // Maximum number of messages to process
        if ($this->_cFetch > $this->max_mails_batchsize) { $this->_cFetch = $this->max_mails_batchsize; }
        
        // Fetch one mail per iteration
        for ($msgnr = 1; $msgnr <= $this->_cFetch; $msgnr++) {

            // Fetches all the structured information for a given message and returns the structure in an object
            $structure = imap_fetchstructure($this->_mailbox_object, $msgnr);
            
            // Is it a standard DSN msg?
            if ($structure->type == 1 && 
                $structure->ifsubtype && 
                strtolower($structure->subtype) == 'report' && 
                $structure->ifparameters && 
                $this->_isParameter($structure->parameters, 'REPORT-TYPE', 'delivery-status'))
            {
                $classified = $this->_classifyBounceMessage($msgnr, 'DSN');
            
            // None standard DSN msg
            } else {
                $classified = $this->_classifyBounceMessage($msgnr, 'BODY');
            }

            // If the message is classified -> process it!
            if ($classified) {
            
                /*
                if ((!$this->testmode) && (!$this->disable_delete)) {
                
                    @imap_delete($this->_mailbox_object, $x);
                    $this->_cDeleted++;
                    
                } elseif ($this->moveHard) {
                
                    // Check if the move directory exists, if not create it
                    $this->mailboxExists($this->mailHardMailbox);
                    @imap_mail_move($this->_mailbox_object, $x, $this->mailHardMailbox);
                    $this->_cMoved++;
                    
                } elseif ($this->moveSoft) {
                
                    // Check if the move directory exists, if not create it
                    $this->mailboxExists($this->mailSoftMailbox);
                    @imap_mail_move($this->_mailbox_object, $x, $this->mailSoftMailbox);
                    $this->_cMoved++;
                }
                
                
                $this->_cProcessed++;
                */
                
            } else {
            
                /*
                if (!$this->testmode && !$this->disable_delete && $this->purge_unprocessed) {
                    @imap_delete($this->_mailbox_object, $x);
                    $this->_cDeleted++;
                }
                
                $this->_cUnprocessed++;
                */
            }
        
        } // end: for
        
        $this->closeMailbox();
        return true;
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
        $body   = '';
        $result = array();
        
        if ($type == 'DSN') {

            // First part of DSN (Delivery Status Notification), human-readable explanation
            $dsnMsg           = imap_fetchbody($this->_mailbox_object, $msgnr, '1');
            $dsnMsgStructure = imap_bodystruct($this->_mailbox_object, $msgnr, '1');
            
            switch ($dsnMsgStructure->encoding) {
                case 3: // Encoding = BASE64
                    $dsnMsg = base64_decode($dsnMsg);
                    break;
                case 4: // Encoding = QUOTED-PRINTABLE
                    $dsnMsg = quoted_printable_decode($dsnMsg);
                    break;
                }
            
            // Second part of DSN (Delivery Status Notification), delivery-status
            $dsnReport = imap_fetchbody($this->_mailbox_object, $msgnr, '2');
            
            // Determine bounce type
            $result = bmhDSNRules($dsnMsg, $dsnReport);
            
        } else {

            $structure = imap_fetchstructure($this->_mailbox_object, $msgnr);
            
            switch ($structure->type) {
                case 0: // Content-type = text
                    $body = imap_fetchbody($this->_mailbox_object, $msgnr, '1');
                    break;
                    
                case 1: // Content-type = multipart
                    $body = imap_fetchbody($this->_mailbox_object, $msgnr, '1');

                    // Detect encoding and decode - only base64
                    if ($structure->parts[0]->encoding == 4) {
                        $body = quoted_printable_decode($body);
                    } elseif ($structure->parts[0]->encoding == 3) {
                        $body = base64_decode($body);
                    }
                    break;
                    
                case 2: // Content-type = message
                    $body = imap_body($this->_mailbox_object, $msgnr);

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

        // Get custom xheaders:
        // Scan message source for X-goodnews-user-id and X-goodnews-mailing-id
        $source = imap_body($this->_mailbox_object, $msgnr);
        if (preg_match('/X-goodnews-user-id[: \t\n]+([0-9]+)/i', $source, $match)) {
            $result['user_id'] = $match[1];
        }
        if (preg_match('/X-goodnews-mailing-id[: \t\n]+([0-9]+)/i', $source, $match)) {
            $result['mailing_id'] = $match[1];
        }
        
        // Debug output
        if ($this->debug_rules) {
            $this->modx->log(modX::LOG_LEVEL_INFO, print_r($result, true));
            if ($result['bounce_type'] == false) {
                $mailSource = imap_fetchheader($this->_mailbox_object, $msgnr).PHP_EOL.imap_body($this->_mailbox_object, $msgnr);
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
     * Function to check if a mailbox exists
     * - if not found, it will create it
     *
	 * @access public
     * @param string  $mailbox The mailbox name (must be in 'INBOX.checkmailbox' format)
     * @param boolean $create Whether or not to create the checkmailbox if not found (defaults to true)
     * @return boolean
     */
    public function mailboxExists($mailbox, $create = true) {
        if (trim($mailbox) == '' || !strstr($mailbox, 'INBOX.')) {
            // this is a critical error with either the mailbox name blank or an invalid mailbox name
            // need to stop processing and exit at this point
            exit();
        }
        $portstring = $this->mailPort.'/'.$this->mailService.'/'.$this->mailServiceOption;

        $mbox = imap_open('{'.$this->mailMailHost.':'.$portstring.'}', $this->mailMailboxUsername, $this->mailMailboxPassword, OP_HALFOPEN);
        $list = imap_getmailboxes($mbox,'{'.$this->mailMailHost.':'.$portstring.'}', "*");

        $mailboxFound = false;

        if (is_array($list)) {
            foreach ($list as $key => $val) {
                // Get the mailbox name only
                $nameArr = explode('}', imap_utf7_decode($val->name));
                $nameRaw = $nameArr[count($nameArr) - 1];
                if ($mailbox == $nameRaw) {
                    $mailboxFound = true;
                }
            }
            if ((!$mailboxFound) && $create) {
                @imap_createmailbox($mbox, imap_utf7_encode('{'.$this->mailMailHost.':'.$portstring.'}'.$mailbox));
                imap_close($mbox);
                return true;
            } else {
                imap_close($mbox);
                return false;
            }
        } else {
            imap_close($mbox);
            return false;
        }
    }
    
    /**
     * Method to determine the id of a subscriber by given email address.
     * 
     * @access public
     * @param string $email The email address
     * @return void
     */
    public function getSubscriberID($email) {
        //$meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberID));


    }

    /**
     * Method to get the bounce counters from subscribers meta table (hard/soft).
     *
     * @access public
     * @param integer $subscriberId The ID of the subscriber ( = MODX userID)
     * @param string $bounceType The bounce type (hard || soft)
     * @return bounce counter or false
     */
    public function getSubscriberBounceCounter($subscriberID, $bounceType) {
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberID));
        if (!is_object($meta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not read bounce counter. Subscriber with ID: '.$subscriberID.' not found.');
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
    public function getSubscriberBounceLag($subscriberID, $bounceType) {
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberID));
        if (!is_object($meta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not read bounce delay. Subscriber with ID: '.$subscriberID.' not found.');
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
    public function addSubscriberBounce($subscriberID, $timeStamp, $bounceType) {
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberID));
        if (!is_object($meta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not add bounce. Subscriber with ID: '.$subscriberID.' not found.');
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
    public function resetSubscriberBounces($subscriberID, $bounceType = false) {
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberID));
        if (!is_object($meta)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not reset bounces. Subscriber with ID: '.$subscriberID.' not found.');
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
        $containers = $this->modx->getCollection('modResource', $c);

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
                  
        $this->mailService                  = $goodnewscontainer->getProperty('mailService',                  'goodnews', 'imap');
        $this->mailMailHost                 = $goodnewscontainer->getProperty('mailMailHost',                 'goodnews', 'localhost');
        $this->mailMailboxUsername          = $goodnewscontainer->getProperty('mailMailboxUsername',          'goodnews');
        $this->mailMailboxPassword          = $goodnewscontainer->getProperty('mailMailboxPassword',          'goodnews');
        $this->mailBoxname                  = $goodnewscontainer->getProperty('mailBoxname',                  'goodnews', 'INBOX');
        $this->mailPort                     = $goodnewscontainer->getProperty('mailPort',                     'goodnews', 143);
        $this->mailServiceOption            = $goodnewscontainer->getProperty('mailServiceOption',            'goodnews', 'notls');
        $this->mailSoftBouncedMessageAction = $goodnewscontainer->getProperty('mailSoftBouncedMessageAction', 'goodnews', 'delete');
        $this->mailSoftMailbox              = $goodnewscontainer->getProperty('mailSoftMailbox',              'goodnews', 'INBOX.Softbounces');
        $this->mailMaxSoftBounces           = $goodnewscontainer->getProperty('mailMaxSoftBounces',           'goodnews', 3);
        $this->mailMaxSoftBouncesAction     = $goodnewscontainer->getProperty('mailMaxSoftBouncesAction',     'goodnews', 'disable');
        $this->mailHardBouncedMessageAction = $goodnewscontainer->getProperty('mailHardBouncedMessageAction', 'goodnews', 'delete');
        $this->mailHardMailbox              = $goodnewscontainer->getProperty('mailHardMailbox',              'goodnews', 'INBOX.Hardbounces');
        $this->mailMaxHardBounces           = $goodnewscontainer->getProperty('mailMaxHardBounces',           'goodnews', 1);
        $this->mailMaxHardBouncesAction     = $goodnewscontainer->getProperty('mailMaxHardBouncesAction',     'goodnews', 'delete');
        
    }

}
