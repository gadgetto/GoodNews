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

require_once dirname(dirname(__FILE__)).'/csstoinlinestyles/Exception.php';
require_once dirname(dirname(__FILE__)).'/csstoinlinestyles/CssToInlineStyles.php';

/**
 * GoodNewsMailing class handles mailing/newsletter sending
 *
 * @package goodnews
 */
class GoodNewsMailing {

    const GON_USER_NOT_YET_SENT = 0;
    const GON_USER_SENT         = 1;
    const GON_USER_SEND_ERROR   = 2;
    const GON_USER_RESERVED     = 4;
    
    const GON_IPC_STATUS_STOPPED  = 0;
    const GON_IPC_STATUS_STARTED  = 1;
    
    const PROCESS_TIMEOUT = 90;

    /** @var modX $modx A reference to the modX object */
    public $modx = null;

    /** @var GoodNewsResourceMailing $mailing A mailing resource object */
    public $mailing = null;

    /** @var GoodNewsProcessHandler $goodnewsprocesshandler A processhandler object */
    public $goodnewsprocesshandler = null;
        
    /** @var int $mailingid The id of the current mailing resource */
    public $mailingid = 0;
    
    /** @var int $bulksize The maximum value of mails to send by one task */
    public $bulksize = 30;
    
    /** @var string $lockDir The path to the goodnews/locks/ directory in MODX cache folder */
    public $lockDir;

    /** @var boolean $testMailing Is this a test mailing? */
    public $testMailing = false;

    /** @var boolean $debug Debug mode on/off */
    public $debug = false;

    /**
     * Constructor for GoodNewsMailing object
     *
     * @param modX $modx
     */
    function __construct(modX &$modx) {
        $this->modx      = &$modx;
        $this->debug     = $this->modx->getOption('goodnews.debug', null, false) ? true : false;
        $this->bulksize  = $this->modx->getOption('goodnews.mailing_bulk_size', null, 30);
        $this->_createLockFileDir();
        $corePath = $this->modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/');
        if (!$this->modx->loadClass('GoodNewsProcessHandler', $corePath.'model/goodnews/', true, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not load GoodNewsProcessHandler class.');
            exit();
        }
        $this->goodnewsprocesshandler = new GoodNewsProcessHandler($this->modx);
        $this->modx->lexicon->load('goodnews:default');
    }

    /**
     * Get the mail properties and collect in array.
     * 
     * @access private
     * @return array $properties The collection of properties || false
     */
    private function _getMailProperties() {
        $this->_changeContext();

        $properties = array();
        $properties['subject']      = $this->_getMailSubject();
        $properties['ishtml']       = $this->mailing->get('richtext') ? true : false;
        if ($properties['ishtml']) {
            $properties['body']     = $this->_getHTMLMailBody();
            $properties['altbody']  = $this->_getPlainMailBody();
        } else {
            $properties['body']     = $this->_getPlainMailBody();
            $properties['altbody']  = '';
        }
        $properties['mailFrom']     = $this->mailing->getProperty('mailFrom', 'goodnews', $this->modx->getOption('emailsender'));   
        $properties['mailFromName'] = $this->mailing->getProperty('mailFromName', 'goodnews', $this->modx->getOption('site_name'));
        $properties['mailReplyTo']  = $this->mailing->getProperty('mailReplyTo', 'goodnews', $this->modx->getOption('emailsender'));   

        return $properties;
    }

    /**
     * Get the subscriber properties and collect in array.
     * 
     * @access private
     * @return array $properties The collection of properties || false
     */
    private function _getSubscriberProperties($userid) {
        $subscribermeta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id'=>$userid));
        if (!is_object($subscribermeta)) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_getSubscriberProperties - Recipient [id: '.$userid.'] not found.'); }
            return false;
        }
        $subscriberprofile = $this->modx->getObject('modUserProfile', array('internalKey'=>$userid));
        $properties['id']        = $userid;
        $properties['email']     = $subscriberprofile->get('email');
        $properties['fullname']  = $subscriberprofile->get('fullname');
        $properties['sid']       = $subscribermeta->get('sid');
        if (empty($properties['fullname'])) { $properties['fullname'] = $properties['email']; }

        return $properties;
    }

    /**
     * Get the mail subject from resource document.
     *
     * @param integer $id The ID of the resource
     * @return string $subject The pagetitle of resource object
     */
    private function _getMailSubject() {
        $subject = $this->mailing->get('pagetitle');
        if ($this->testMailing) {
            $subject = $this->modx->getOption('goodnews.test_subject_prefix').$subject;
        }
        if ($this->debug) {
            $subject = '[pid: '.getmypid().'] '.$subject;
        }
        return $subject;
    }
    
    /**
     * Get the full parsed HTML from resource
     * 
     * @access private
     * @return string $html The parsed html of the resource
     */
    private function _getHtmlMailBody() {
        // Store some values for later restoration
        $currentResource           = $this->modx->resource;
        $currentResourceIdentifier = $this->modx->resourceIdentifier;
        $currentElementCache       = $this->modx->elementCache;
        
        // Changes are made to prepare to process the Resource
        $this->modx->resource           = $this->mailing;
        $this->modx->resourceIdentifier = $this->mailing->get('id');
        $this->modx->elementCache       = array();
                
        // The Resource having access to itself via $this->modx->resource is critical 
        // for getting resource fields, as well as for proper execution of Snippets 
        // that may appear in the content.

        // Process and return the cacheable content of the Resource
        $html = $this->modx->resource->process();

        // Restore the original values
        $this->modx->elementCache       = $currentElementCache;
        $this->modx->resourceIdentifier = $currentResourceIdentifier;
        $this->modx->resource           = $currentResource;
         
        // Determine how many passes the parser should take at a maximum
        $maxIterations = intval($this->modx->getOption('parser_max_iterations', null, 10));
         
        if (!$this->modx->parser) { $this->modx->getParser(); }

        // Preserve GoodNews placeholders
        $phsArray = array('EMAIL','FULLNAME','SID');
        $search = array();
        $replace = array();
        foreach ($phsArray as $phs) {
            $search[] = '[[+'.$phs;
            $replace[] = '&#91;&#91;+'.$phs;
        }
        $html = str_replace($search, $replace, $html);

        // Process the non-cacheable content of the Resource, but leave any unprocessed tags alone
        $this->modx->parser->processElementTags('', $html, true, false, '[[', ']]', array(), $maxIterations);
         
        // Process the non-cacheable content of the Resource, this time removing the unprocessed tags
        $this->modx->parser->processElementTags('', $html, true, true, '[[', ']]', array(), $maxIterations);
         
        // Set back GoodNews placeholders
        $search = array();
        $replace = array();
        foreach ($phsArray as $phs) {
            $search[] = '&#91;&#91;+'.$phs;
            $replace[] = '[[+'.$phs;
        }
        $html = str_replace($search, $replace, $html);

        // Process embeded CSS
        $html = $this->_inlineCSS($html);

        // Process full URLs
        $base = $this->modx->getOption('site_url');
        $html = $this->_fullUrls($base, $html);

        return $html;
    }

    /**
     * Get the plain-text mail body
     *
     * @param $id
     * @return mixed string $body || false
     */
    private function _getPlainMailBody() {
        // Get content of mail directly from resource content field
        $body = $this->mailing->get('content');
        if ($body === FALSE) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Plain mail body for mailing [id: '.$this->mailingid.'] could not be created.');
            return false;
        }
        // Remove all HTML tags (MODX doesn't automatically remove htmls tags if WYSIWYG editor is disabled)
        return $this->_html2txt($body);
    }

    /**
     * Replace GoodNews placeholders
     * (currently "hardcoded" - todo: rewrite for universal usage)
     *
     * @param string $html
     * @param $sid
     * @param $fullname
     * @param $email
     * @return string $html || false
     */
    private function _gonPlaceholders($html, $sid, $fullname, $email) {
        if (empty($html)) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_gonPlaceholders - No HTML content provided for parsing.'); }
            return false;
        }        
        $placeholders = array(
            '[[+EMAIL]]'    => $email,
            '[[+FULLNAME]]' => $fullname,
            '[[+SID]]'      => $sid,
        );
        $html = $this->_strReplaceAssoc($placeholders, $html);
        return $html;
    }

    /**
     * Get 1 recipient.
     * (gets next recipient which is not yet sent and reserve it for further processing)
     *
     * @access public
     * @return mixed array $recipient or false if empty
     */
    public function getNextRecipient() {
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(' ', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tstart = $mtime;
        }

        $this->_lock();

        // Database access has to be exclusively for each recipient - not class wide!
        // Otherwise multiprocessing will have caching related problems.
        $meta = $this->modx->getObject('GoodNewsMailingMeta',  array('mailing_id'=>$this->mailingid));
        if (!is_object($meta)) { return false; }

        $recipients = unserialize($meta->get('recipients_list'));
        if (!is_array($recipients) || count($recipients) == 0) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getRecipient - Recipients list is empty.'); }
            $this->_unlock();
            return false;
        }

        $currentTime = time();
        
        // Search for next recipient with GON_USER_NOT_YET_SENT
        foreach ($recipients as $key => &$val) {
            // Check if reservation timestamp is too old, which means the mail could not be sent within 90 seconds
            if ($val[0] == self::GON_USER_RESERVED) {
                if ((($currentTime) - self::PROCESS_TIMEOUT) > $val[1]) {
                    $val[0] = self::GON_USER_SEND_ERROR;
                }
                
            }
            if ($val[0] == self::GON_USER_NOT_YET_SENT) {
                $recipient = $key;
                break;
            }
        }
        unset($key, $val);
        
        if ($recipient) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getRecipient - Unsent recipient [id: '.$recipient.'] found.'); }
            // Set recipient status to GON_USER_RESERVED + current time stamp
            $recipients[$recipient] = array(self::GON_USER_RESERVED,$currentTime);
            $meta->set('recipients_list', serialize($recipients));

            if (!$meta->save()) {
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getRecipient - Recipient [id: '.$recipient.'] could not be set to status: '.self::GON_USER_RESERVED); }
                $recipient = false;
            }
        } else {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getRecipient - No more unsent recipients found.'); }
        }
        
        $this->_unlock();
        
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(" ", $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tend = $mtime;
            $totalTime = ($tend - $tstart);
            $totalTime = sprintf("%2.4f s", $totalTime);
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getRecipient - Lock time: '.$totalTime);
        }

        return $recipient;
    }

    /**
     * Update recipient status.
     *
     * @access public
     * @param $recipient
     * @param $status
     * @return mixed array $recipient or false if empty
     */
    public function updateRecipientStatus($recipient, $status) {
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(' ', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tstart = $mtime;
        }
        
        $this->_lock();

        // Database access has to be exclusively for each recipient - not class wide!
        // Otherwise multiprocessing will have caching related problems.
        $meta = $this->modx->getObject('GoodNewsMailingMeta',  array('mailing_id'=>$this->mailingid));
        if (!is_object($meta)) { return false; }

        $recipients = unserialize($meta->get('recipients_list'));
        if (!is_array($recipients) || count($recipients) == 0) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::updateRecipientStatus - Recipients list is empty.'); }
            $this->_unlock();
            return false;
        }
        
        // Increase sent counter
        $recipientsSent = $meta->get('recipients_sent') + 1;
        $currentTime = time();

        // Set recipient status to GON_USER_SENT or GON_USER_SEND_ERROR + current time stamp
        $recipients[$recipient] = array($status,$currentTime);
        $meta->set('recipients_list', serialize($recipients));
        $meta->set('recipients_sent', $recipientsSent);

        if (!$meta->save()) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::updateRecipientStatus - Status for recipient [id: '.$recipient.'] could not be updated to: '.$status); }
            $update = false;
        } else {
            $update = true;
        }
        
        $this->_unlock();
        
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(' ', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tend = $mtime;
            $totalTime = ($tend - $tstart);
            $totalTime = sprintf("%2.4f s", $totalTime);
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::updateRecipientStatus - Lock time: '.$totalTime);
        }
        return $update;
    }

    /**
     * Get test-recipients.
     * (get test-recipients from users table - not pre-generated!)
     *
     * @access public
     * @return mixed array $testrecipients or false if empty
     */
    public function getTestRecipients() {
        $c = $this->modx->newQuery('modUser');
        $c->leftJoin('modUserProfile', 'Profile');
        $c->leftJoin('GoodNewsSubscriberMeta', 'SubscriberMeta', 'SubscriberMeta.subscriber_id = modUser.id');  
        $c->where(array(
            'modUser.active' => true,
            'SubscriberMeta.testdummy' => 1,
        ));
        $users = $this->modx->getCollection('modUser', $c);

        $testrecipients = array();
        foreach ($users as $user) {
            $testrecipients[] = $user->get('id');            
        }
        if (count($testrecipients) == 0) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::collectTestRecipients - Test-recipients list is empty.'); }
            $testrecipients = false;
        }
        return $testrecipients;
    }

    /**
     * Get all mailing resources to be sent.
     *
     * @access public
     * @return array $mailingIDs or false
     */
    public function getMailingsToSend() {
        $containerIDs = $this->getGoodNewsContainers();
        if (empty($containerIDs)) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] No mailing containers found.');
            return false;
        }
        // Check for scheduled mailings
        $this->_startScheduledMailings();
        
        $c = $this->modx->newQuery('modResource');
        $c->leftJoin('GoodNewsMailingMeta', 'MailingMeta', 'MailingMeta.mailing_id = modResource.id');
        $c->where(array(
            'modResource.published'  => true,
            'modResource.deleted'    => false,
            'modResource.parent:IN'  => $containerIDs,
            'MailingMeta.ipc_status' => self::GON_IPC_STATUS_STARTED
        ));
        $mailings = $this->modx->getCollection('modResource', $c);
        
        $mailingIDs = array();
        foreach ($mailings as $mailing) {
            $mailingIDs[] = $mailing->get('id');
        }
        if (count($mailingIDs) == 0) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getMailingsToSend - No mailing resources found for processing.'); }
            $mailingIDs = false;
        }
        return $mailingIDs;
    }

    /**
     * Processes a mailing.
     * (This is also the initialization method of the class)
     *
     * @access public
     * @param integer $id The ID of the mailing resource
     * @return boolean
     */
    public function processMailing($id) {
    
        $this->mailingid = $id;
        $this->mailing   = $this->_getMailingObject();
        if (!$this->mailing) { return false; }
       
        $this->_createLockFile();
        
        $mail = $this->_getMailProperties();
        
        for ($n = 0; $n < $this->bulksize; $n++) {
            
            $recipient = $this->getNextRecipient();
            if (!$recipient) {
                // If there are no more recipients to send, my process will stop immediately!
                // So I need to remove my process status!
                $this->goodnewsprocesshandler->setPid(getmypid());
                $this->goodnewsprocesshandler->deleteProcessStatus();
                
                // Also we set the mailing to finished (= IPCstatus "stopped")
                $this->setIPCstop($this->mailingid, true);
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::processMailing - Mailing [id: '.$this->mailingid.'] finished.'); }
                break;
            }            
            
            $subscriber = $this->_getSubscriberProperties($recipient);
            if (!$subscriber) {
                $userStatus = self::GON_USER_SEND_ERROR; // todo: other status required eg. GON_USER_NOT_FOUND
            } else {
                $temp_mail = $mail;
                $temp_mail['body'] = $this->_gonPlaceholders($temp_mail['body'], $subscriber['sid'], $subscriber['fullname'], $subscriber['email']);
                $sent = $this->sendEmail($temp_mail, $subscriber);            
                if ($sent) {
                    $userStatus = self::GON_USER_SENT;
                } else {
                    $userStatus = self::GON_USER_SEND_ERROR;
                }
                
            }
            $this->updateRecipientStatus($recipient, $userStatus);
        }
        return true;
    }

    /**
     * Processes a test-mailing.
     *
     * @access public
     * @param integer $id The ID of the mailing resource
     * @return boolean
     */
    public function processTestMailing($id) {    
        $this->mailingid   = $id;
        $this->testMailing = true;
        $this->mailing     = $this->_getMailingObject();
        if (!$this->mailing) { return false; }
        
        $mail       = $this->_getMailProperties();
        $recipients = $this->getTestRecipients();
        if (empty($recipients)) {
            return false;
        }

        foreach ($recipients as $recipient) {
            $subscriber = $this->_getSubscriberProperties($recipient);
            $temp_mail = $mail;
            $temp_mail['body'] = $this->_gonPlaceholders($temp_mail['body'], $subscriber['sid'], $subscriber['fullname'], $subscriber['email']);
            $sent = $this->sendEmail($temp_mail, $subscriber);            
        }
        return true;
    }

    /**
     * Sends an email based on the specified parameters using phpMailer.
     *
     * @access public
     * @param array $mail
     * @param array $subscriber
     * @return array
     */
    public function sendEmail(array $mail, array $subscriber) {

        $this->modx->getService('mail', 'mail.modPHPMailer');
        $this->modx->mail->header('X-goodnews-user-id: '.$subscriber['id']);
        $this->modx->mail->header('X-goodnews-mailing-id: '.$this->mailingid);
        $this->modx->mail->set(modMail::MAIL_BODY,      $mail['body']);
        $this->modx->mail->set(modMail::MAIL_BODY_TEXT, $mail['altbody']);
        $this->modx->mail->set(modMail::MAIL_FROM,      $mail['mailFrom']);
        $this->modx->mail->set(modMail::MAIL_FROM_NAME, $mail['mailFromName']);
        $this->modx->mail->set(modMail::MAIL_SENDER,    $mail['mailFrom']);
        $this->modx->mail->set(modMail::MAIL_SUBJECT,   $mail['subject']);
        $this->modx->mail->address('reply-to',          $mail['mailReplyTo']);
        $this->modx->mail->address('to', $subscriber['email'], $subscriber['fullname']);
        $this->modx->mail->setHTML($mail['ishtml']);
                
        $sent = $this->modx->mail->send();
        $this->modx->mail->reset();

        if (!$sent) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::sendEmail - Could not send mail to recipient [id: '.$subscriber['id'].'] ('.$subscriber['email'].').'); }
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] An error occurred while trying to send email. Mailer error: '.$this->modx->mail->mailer->ErrorInfo);
        } else {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::sendEmail - Mail sent to recipient [id: '.$subscriber['id'].'] ('.$subscriber['email'].').'); }
        }
        return $sent;
    }

    /**
     * Get all mailing resource container ids.
     *
     * @access public
     * @return array $containerIDs
     */
    public function getGoodNewsContainers() {
        $c = $this->modx->newQuery('modResource');
        $c->where(array(
            'published' => true,
            'deleted'   => false,
            'class_key' => 'GoodNewsResourceContainer'
        ));
        $containers = $this->modx->getCollection('modResource', $c);

        $containerIDs = array();
        foreach ($containers as $container){
            $containerIDs[] = $container->get('id');
        }
        return $containerIDs;
    }
    
    /**
     * Get the mailing object and set member variable.
     * 
     * @access private
     * @return boolean
     */
    private function _getMailingObject() {
        $this->mailing = $this->modx->getObject('GoodNewsResourceMailing', $this->mailingid);
        if (!is_object($this->mailing)) { return false; }
        return $this->mailing;
    }

    /**
     * Set the context based on the current resource.
     *
     * @access private
     * @return boolean
     */
    private function _changeContext() {
        $key = $this->mailing->get('context_key');
        $this->modx->switchContext($key);
        return true;
    }

    /**
     * Auto start scheduled mailings.
     * (Check for mailing resources with pub_date reached and set them to published
     * + GON_IPC_STATUS_STARTED in mailing meta table so they can be sent automatically)
     *
     * @access private
     * @return int $publishingResults The number of mailings affected by the sql statement.
     */
    private function _startScheduledMailings() {
        $tblResource = $this->modx->getTableName('modResource');
        $tblMailingMeta = $this->modx->getTableName('GoodNewsMailingMeta');
        $timeNow = time();
        $ipcStatus = self::GON_IPC_STATUS_STARTED;
        
        $sql = "UPDATE {$tblResource}, {$tblMailingMeta} 
                SET {$tblMailingMeta}.senton = {$timeNow},
                    {$tblMailingMeta}.sentby = {$tblResource}.createdby,
                    {$tblMailingMeta}.ipc_status = {$ipcStatus},
                    {$tblMailingMeta}.scheduled = 1,
                    {$tblResource}.published = 1,
                    {$tblResource}.publishedon = {$tblResource}.pub_date,
                    {$tblResource}.publishedby = {$tblResource}.createdby,
                    {$tblResource}.pub_date = 0 
                WHERE {$tblMailingMeta}.mailing_id = {$tblResource}.id 
                AND {$tblResource}.class_key = 'GoodNewsResourceMailing' 
                AND {$tblResource}.pub_date IS NOT NULL 
                AND {$tblResource}.pub_date < {$timeNow} 
                AND {$tblResource}.pub_date > 0
                AND {$tblMailingMeta}.recipients_total > 0";

        $publishingResults = $this->modx->exec($sql);
        if ($this->debug) {
            if ($publishingResults) {
                $mailings = $publishingResults / 2; // we always have two rows affected!
                $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::autoPublish - autopublished mailings: '.$mailings);
            }
        }
        return $publishingResults;
    }

    /**
     * Sets the IPC status of a mailing to "start".
     *
     * @access public
     * @param integer $id The ID of the resource
     * @return boolean
     */
    public function setIPCstart($id) {
        // Get resource mailing meta object
        $meta = $this->modx->getObject('GoodNewsMailingMeta', array('mailing_id' => $id));
        if (!is_object($meta)) { return false; }

        $currentUser = $this->modx->user->get('id');
        
        // set mailing sender and send date
        $meta->set('senton', time());
        $meta->set('sentby', $currentUser);
        $meta->set('ipc_status', self::GON_IPC_STATUS_STARTED);
        if ($meta->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets the IPC status of a mailing to "stopped".
     *
     * @access public
     * @param integer $id The ID of the resource
     * @param bool $finished
     * @return boolean
     */
    public function setIPCstop($id, $finished = false) {
        // Get resource mailing meta object
        $meta = $this->modx->getObject('GoodNewsMailingMeta', array('mailing_id' => $id));
        if (!is_object($meta)) { return false; }

        if ($finished) {
            $meta->set('finishedon', time());
        }
        $meta->set('ipc_status', self::GON_IPC_STATUS_STOPPED);
        if ($meta->save()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Sets the IPC status of a mailing to "started".
     *
     * @access public
     * @param integer $id The ID of the resource
     * @return boolean
     */
    public function setIPCcontinue($id) {
        // Get resource mailing meta object
        $meta = $this->modx->getObject('GoodNewsMailingMeta', array('mailing_id' => $id));
        if (!is_object($meta)) { return false; }

        $meta->set('ipc_status', self::GON_IPC_STATUS_STARTED);
        if ($meta->save()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Creates the directory for the temporary lock files.
     * 
     * @todo: move all lockfile related methods to a separate lockfilehandler class!
     *
     * @access private
     * @return boolean
     */
    private function _createLockFileDir() {
        $this->lockDir = $this->modx->getOption('core_path', null, MODX_CORE_PATH).'cache/goodnews/locks/';
        $dir = false;
        
        if (!is_dir($this->lockDir)) {
            $dir = @mkdir($this->lockDir, 0777, true);
            if ($dir) {
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_createLockFileDir - lockfile directory created.'); }
            } else {
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_createLockFileDir - could not create lockfile directory (file operation failed).'); }
            }
        } else {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_createLockFileDir - lockfile directory already exists.'); }
        }
        return $dir;
    }

    /**
     * Creates a temporary lock file for a specific mailing.
     * 
     * @todo: move all lockfile related methods to a separate lockfilehandler class!
     *
     * @access private
     * @return boolean
     */
    private function _createLockFile() {
        $tempfile = $this->lockDir.$this->mailingid.'.temp';
        $lockfilepattern = $this->lockDir.$this->mailingid.'.*';
        $file = false;
        
        $ary = glob($lockfilepattern);
        if (empty($ary)) {
            $file = file_put_contents($tempfile, $this->mailingid, LOCK_EX);
            @chmod($tempfile, 0777);
            if ($file) {
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_createLockFile - Mailing meta [id: '.$this->mailingid.'] - lockfile created.'); }
            } else {
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_createLockFile - Mailing meta [id: '.$this->mailingid.'] - could not create lockfile (file operation failed).'); }
            }
        } else {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_createLockFile - Mailing meta [id: '.$this->mailingid.'] - lockfile already exists.'); }
        }
        return $file;
    }

    /**
     * Removes a temporary lock file.
     * 
     * @todo: move all lockfile related methods to a separate lockfilehandler class!
     *
     * @access private
     * @return void
     */
    private function _removeLockFile() {
        $tempfile = $this->lockDir.$this->mailingid.'.temp';
        @unlink($tempfile);
    }

    /**
     * Set lock on db entry.
     *
     * @todo: move all lockfile related methods to a separate lockfilehandler class!
     *
     * @access private
     * @return boolean
     */
    private function _lock() {
        $tempfile = $this->lockDir.$this->mailingid.'.temp';
        $lockfile = $this->lockDir.$this->mailingid.'.'.getmypid();
        
        while (true) {
            while (!file_exists($tempfile)) {
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::lock - waiting (mailing currently locked).'); }
                usleep(rand(20000, 100000)); // 20 to 100 millisec
            }
            // Atomic method to use the file for locking purposes
            $lock = @rename($tempfile, $lockfile); 
            if ($lock) {
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::lock - Mailing meta [id: '.$this->mailingid.'] - locked.'); }
            } else {
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::lock - Mailing meta [id: '.$this->mailingid.'] - could not be locked (file operation failed).'); }
            }
            // Catch race conditions! 
            if (file_exists($lockfile)) {
                return $lock;
            } else {
                if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::lock - Mailing meta [id: '.$this->mailingid.'] - race condition!'); }
            }
        }
    }

    /**
     * Remove lock from db entry.
     *
     * @todo: move all lockfile related methods to a separate lockfilehandler class!
     *
     * @access private
     * @return boolean
     */
    private function _unlock() {
        $tempfile = $this->lockDir.$this->mailingid.'.temp';
        $lockfile = $this->lockDir.$this->mailingid.'.'.getmypid();
        // Atomic method to use the file for locking purposes
        $unlock = @rename($lockfile, $tempfile); 
        if ($unlock) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::unlock - Mailing meta [id: '.$this->mailingid.'] - unlocked.'); }
        } else {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::unlock - Mailing meta [id: '.$this->mailingid.'] - could not be unlocked (file operation failed).');
        }
        return $unlock;
    }
        
    /**
     * Replace URLs in resource with full URLs
     * (Method from Bob Ray's emailresource plugin with kind permission)
     *
     * @param string $base
     * @param string $html
     * @return mixed string $html The parsed string or false
     */
    private function _fullUrls($base, $html) {
        if (empty($html)) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_fullUrls - No HTML content provided for parsing.'); }
            return false;
        }

        // Extract domain name from $base
        $splitBase = explode('//', $base);
        $domain = $splitBase[1];
        $domain = rtrim($domain,'/ ');

        // remove space around = sign
        //$html = preg_replace('@(href|src)\s*=\s*@', '\1=', $html);
        $html = preg_replace('@(?<=href|src)\s*=\s*@', '=', $html);

        // Fix google link weirdness
        $html = str_ireplace('google.com/undefined', 'google.com', $html);

        // add http to naked domain links so they'll be ignored later
        $html = str_ireplace('a href="'.$domain, 'a href="http://'.$domain, $html);

        // Standardize orthography of domain name
        $html = str_ireplace($domain, $domain, $html);

        // Correct base URL, if necessary
        $server = preg_replace('@^([^\:]*)://([^/*]*)(/|$).*@', '\1://\2/', $base);

        // Handle root-relative URLs
        $html = preg_replace('@\<([^>]*) (href|src)="/([^"]*)"@i', '<\1 \2="'.$server.'\3"', $html);

        // Handle base-relative URLs
        $html = preg_replace('@\<([^>]*) (href|src)="(?!http|mailto|sip|tel|callto|sms|ftp|sftp|gtalk|skype)(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="'.$base.'\3"', $html);

        return $html;
    }

    /**
     * Convert HTML into HTML with inline styles.
     * (https://github.com/tijsverkoyen/CssToInlineStyles)
     *
     * @param string $html HTML content
     * @return string $html HTML content with inlined CSS
     * @author Original method by Josh Gulledge <jgulledge19@hotmail.com>
     */
    private function _inlineCSS($html) {
        if (empty($html)) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_inlineCSS - No HTML content provided for parsing.'); }
            return false;
        }

        // GoodNews templates are built with embedded CSS
        // (this can handle multiple <style></style> blocks)
        preg_match_all('|<style(.*)>(.*)</style>|isU', $html, $css);
        $css_rules = '';
        
        if (!empty($css[2])) {
            foreach ($css[2] as $cssblock) {
                $css_rules .= $cssblock;
            }
        }

        $cssToInlineStyles = new TijsVerkoyen\CSSToInlineStyles\CSSToInlineStyles($html, $css_rules);
        if (!($cssToInlineStyles instanceof TijsVerkoyen\CSSToInlineStyles\CSSToInlineStyles)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] CSSToInlineStyles class could not be instantiated.');
            return false;
        }
        
        // Problem with converted chars in URL strings!!
        $cssToInlineStyles->setEncoding($this->modx->getOption('mail_charset', null, 'UTF-8'));
        $html = $cssToInlineStyles->convert();

        // Workaround to preserve placeholder delimiters - as CSSToInlineStyles converts special chars within urls
        $html = str_replace('%5B%5B', '[[', $html);
        $html = str_replace('%5D%5D', ']]', $html);

        return $html;
    }
    
    /**
     * Helper method to turn HTML into text.
     * 
     * @access private
     * @param string $html
     * @return string
     */
    private function _html2txt($html) { 
        $search = array(
            '@<script[^>]*?>.*?</script>@si',   // Strip out javascript 
            '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags 
            '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly 
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA 
        ); 
        $text = preg_replace($search, '', $html); 
        return $text; 
    }
    
    /**
     * Helper method to replace values in a give array.
     *
     * @access private
     * @param array $replace
     * @param string $subject
     * @return array or string
     */
    private function _strReplaceAssoc(array $replace, $subject) {
        return str_replace(array_keys($replace), array_values($replace), $subject);
    }
}
