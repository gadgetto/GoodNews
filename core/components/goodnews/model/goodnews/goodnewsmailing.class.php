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
require_once dirname(dirname(__FILE__)).'/smartdomdocument/smartdomdocument.class.php';

/**
 * GoodNewsMailing class handles mailing/newsletter sending
 *
 * @package goodnews
 */
class GoodNewsMailing {
    
    const GON_IPC_STATUS_STOPPED  = 0;
    const GON_IPC_STATUS_STARTED  = 1;
    
    const GON_STATUS_REPORT_MAILING_STOPPED  = 1;
    const GON_STATUS_REPORT_MAILING_FINISHED = 2;

    /** @var modX $modx A reference to the modX object */
    public $modx = null;

    /** @var GoodNewsResourceMailing $mailing A mailing resource object */
    public $mailing = null;

    /** @var GoodNewsProcessHandler $goodnewsprocesshandler A processhandler object */
    public $goodnewsprocesshandler = null;
        
    /** @var GoodNewsRecipientHandler $goodnewsrecipienthandler A recipientshandler object */
    public $goodnewsrecipienthandler = null;
        
    /** @var int $mailingid The id of the current mailing resource */
    public $mailingid = 0;
    
    /** @var int $bulksize The maximum value of mails to send by one task */
    public $bulksize = 30;
    
    /** @var boolean $testMailing Is this a test mailing? */
    public $testMailing = false;

    /** @var boolean $debug Debug mode on/off */
    public $debug = false;
    
    /** @var array $subscriberFields The object fields of modUser + modUserProfile + GoodNewsSubscriberMeta */
    public $subscriberFields = array();

    /**
     * Constructor for GoodNewsMailing object
     *
     * @param modX $modx
     */
    function __construct(modX &$modx) {
        $this->modx      = &$modx;
        $this->debug     = $this->modx->getOption('goodnews.debug', null, false) ? true : false;
        $this->bulksize  = $this->modx->getOption('goodnews.mailing_bulk_size', null, 30);
        
        $corePath = $this->modx->getOption('goodnews.core_path', null, $this->modx->getOption('core_path').'components/goodnews/');

        if (!$this->modx->loadClass('GoodNewsProcessHandler', $corePath.'model/goodnews/', true, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not load GoodNewsProcessHandler class. Processing aborted.');
            exit();
        }
        $this->goodnewsprocesshandler = new GoodNewsProcessHandler($this->modx);
        
        if (!$this->goodnewsprocesshandler->createLockFileDir()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Lockfile directory missing! Processing aborted.');
            exit();
        }

        if (!$this->modx->loadClass('GoodNewsRecipientHandler', $corePath.'model/goodnews/', true, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Could not load GoodNewsRecipientHandler class. Processing aborted.');
            exit();
        }
        $this->goodnewsrecipienthandler = new GoodNewsRecipientHandler($this->modx);

        $this->modx->lexicon->load('goodnews:default');
        
        // Get the default fields for a subscriber (for later use as placeholders)
        $this->subscriberFields = array_merge(
            $this->modx->getFields('GoodNewsSubscriberMeta'),
            $this->modx->getFields('modUserProfile')
        );
        $this->subscriberFields = $this->_cleanupKeys($this->subscriberFields);
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
        $properties['subject']       = $this->_getMailSubject();
        $properties['ishtml']        = $this->mailing->get('richtext') ? true : false;
        if ($properties['ishtml']) {
            $properties['body']      = $this->_getHTMLMailBody();
            $properties['altbody']   = $this->_getPlainMailBody();
        } else {
            $properties['body']      = $this->_getPlainMailBody();
            $properties['altbody']   = '';
        }
        $properties['mailFrom']      = $this->mailing->getProperty('mailFrom',     'goodnews', $this->modx->getOption('emailsender'));   
        $properties['mailFromName']  = $this->mailing->getProperty('mailFromName', 'goodnews', $this->modx->getOption('site_name'));
        $properties['mailReplyTo']   = $this->mailing->getProperty('mailReplyTo',  'goodnews', $this->modx->getOption('emailsender'));   
        $properties['mailCharset']   = $this->mailing->getProperty('mailCharset',  'goodnews', $this->modx->getOption('mail_charset',  null, 'UTF-8'));
        $properties['mailEncoding']  = $this->mailing->getProperty('mailEncoding', 'goodnews', $this->modx->getOption('mail_encoding', null, '8bit'));

        return $properties;
    }

    /**
     * Get the subscriber properties and collect in array.
     * 
     * @access private
     * @param integer $subscriberId The ID of the subscriber
     * @return mixed $properties The collection of properties || false
     */
    private function _getSubscriberProperties($subscriberId) {
        $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $subscriberId));
        if (!$meta) { return ''; }
        $profile = $this->modx->getObject('modUserProfile', array('internalKey' => $subscriberId));
        if (!$profile) { return ''; }
                
        // Flatten extended fields:
        // extended.field1
        // extended.container1.field2
        // ...
        $extended = $profile->get('extended') ? $profile->get('extended') : array();
        if (!empty($extended)) {
            $extended = $this->_flattenExtended($extended, 'extended.');
        }
        $properties = array_merge(
            $meta->toArray(),
            $profile->toArray(),
            $extended
        );
        $properties = $this->_cleanupKeys($properties);
        return $properties;
    }

    /**
     * Manipulate/add/remove fields from array.
     *
     * @access private
     * @param array $properties
     * @return array $properties
     */
    private function _cleanupKeys(array $properties = array()) {
        unset(
            $properties['id'],          // multiple occurrence; not needed
            $properties['internalKey'], // not needed
            $properties['sessionid'],   // security!
            $properties['extended']     // not needed as its already flattened
        );    
        return $properties;
    }

    /**
     * Helper function to recursively flatten an array.
     * 
     * @access private
     * @param array $array The array to be flattened.
     * @param string $prefix The prefix for each new array key.
     * @return array $result The flattened and prefixed array.
     */
    private function _flattenExtended($array, $prefix = '') {
        $result = array();
        foreach($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + $this->_flattenExtended($value, $prefix.$key.'.');
            } else {
                $result[$prefix.$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Get the mail subject from resource document.
     *
     * @access private
     * @param integer $id The ID of the resource
     * @return string $subject The pagetitle of resource object
     */
    private function _getMailSubject() {
        $subject = $this->mailing->get('pagetitle');
        if ($this->testMailing) {
            $subject = $this->modx->getOption('goodnews.test_subject_prefix').$subject;
        }
        // Convert subject to charset of mailing
        $mail_charset           = $this->modx->getOption('mail_charset', null, 'UTF-8');
        $modx_charset           = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $mail_charset_goodnews  = $this->mailing->getProperty('mailCharset', 'goodnews', $mail_charset);
        $subject = iconv($modx_charset, $mail_charset_goodnews.'//TRANSLIT', $subject);
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
        
        // Prepare to process the Resource
        $this->modx->resource           = $this->mailing;
        $this->modx->resourceIdentifier = $this->mailing->get('id');
        $this->modx->elementCache       = array();
                
        // The Resource having access to itself via $this->modx->resource is critical 
        // for getting resource fields, as well as for proper execution of Snippets 
        // that may appear in the content.

        // Process and return the cacheable content of the Resource
        $html = $this->modx->resource->process();

        // Determine how many passes the parser should take at a maximum
        $maxIterations = intval($this->modx->getOption('parser_max_iterations', null, 10));
         
        if (!$this->modx->parser) { $this->modx->getParser(); }

        // Preserve GoodNews subscriber fields placeholders
        $phsArray = $this->subscriberFields;
        $search = array();
        $replace = array();
        
        foreach ($phsArray as $phs => $values) {
            $search[] = '[[+'.$phs;
            $replace[] = '&#91;&#91;+'.$phs;
        }
        $html = str_ireplace($search, $replace, $html);
        
        foreach ($phsArray as $phs => $values) {
            $search[] = '[[+extended';
            $replace[] = '&#91;&#91;+extended';
        }
        $html = str_ireplace($search, $replace, $html);

        // Process the non-cacheable content of the Resource, but leave any unprocessed tags alone
        $this->modx->parser->processElementTags('', $html, true, false, '[[', ']]', array(), $maxIterations);
         
        // Process the non-cacheable content of the Resource, this time removing the unprocessed tags
        $this->modx->parser->processElementTags('', $html, true, true, '[[', ']]', array(), $maxIterations);

        // Set back GoodNews subscriber fields placeholders
        $search = array();
        $replace = array();
        
        foreach ($phsArray as $phs => $value) {
            $search[] = '&#91;&#91;+'.$phs;
            $replace[] = '[[+'.$phs;
        }
        $html = str_ireplace($search, $replace, $html);
        
        foreach ($phsArray as $phs => $value) {
            $search[] = '&#91;&#91;+extended';
            $replace[] = '[[+extended';
        }
        $html = str_ireplace($search, $replace, $html);

        // Restore original values
        $this->modx->elementCache       = $currentElementCache;
        $this->modx->resourceIdentifier = $currentResourceIdentifier;
        $this->modx->resource           = $currentResource;

        // Process embeded CSS
        $html = $this->_inlineCSS($html);
        
        $base = $this->modx->getOption('site_url');

        // AutoFixImageSizes if activated in settings
        if ($this->modx->getOption('goodnews.auto_fix_imagesizes', null, true)) {
            $html = $this->_autoFixImageSizes($base, $html);
        }
        
        // Make full URLs if activated in settings
        if ($this->modx->getOption('goodnews.auto_full_urls', null, true)) {
            $html = $this->_fullUrls($base, $html);
        }

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
        if ($body === false) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Plain mail body for mailing [id: '.$this->mailingid.'] could not be created.');
            return false;
        }
        // Remove all HTML tags (MODX doesn't automatically remove htmls tags if WYSIWYG editor is disabled)
        return $this->_html2txt($body);
    }

    /**
     * Replace GoodNews Subscriber placeholders in preparsed newsletter template.
     *
     * @access private
     * @param string $html
     * @param array $subscriberProperties The placeholders.
     * @return string $output
     */
    private function _processSubscriberPlaceholders($html, array $subscriberProperties = array()) {
        if (empty($html)) { return false; }
        $chunk = $this->modx->newObject('modChunk');
        $chunk->setContent($html);
        $chunk->setCacheable(false);
        $chunk->_processed = false;
        $output = $chunk->process($subscriberProperties);
        $this->modx->parser->processElementTags('', $output, true, true);
        return $output;
    }

    /**
     * Get 1 recipient.
     * (gets next recipient which is not yet sent and reserve it for further processing)
     *
     * @access public
     * @return mixed integer $recipientId || false if empty
     */
    public function getNextRecipient() {
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(' ', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tstart = $mtime;
        }

        $this->goodnewsprocesshandler->lock($this->mailingid);

        // Find next unsent recipient
        $recipientId = $this->goodnewsrecipienthandler->getRecipientUnsent($this->mailingid);

        // No more recipients (or list is empty which shouldn't happen here)
        if (!$recipientId) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getNextRecipient - No unsent recipients found.'); }
            $this->goodnewsprocesshandler->unlock($this->mailingid);
            return false;
        }
        // Habemus recipient!
        if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getNextRecipient - Unsent recipient [id: '.$recipientId.'] found.'); }
        
        $this->goodnewsprocesshandler->unlock($this->mailingid);
        
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(" ", $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tend = $mtime;
            $totalTime = ($tend - $tstart);
            $totalTime = sprintf("%2.4f s", $totalTime);
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getNextRecipient - Lock time: '.$totalTime);
        }

        return $recipientId;
    }

    /**
     * Update the status of a recipient.
     *
     * @access public
     * @param integer $recipientId The id of the recipient
     * @param integer $status The status of the recipient
     * @return boolean
     */
    public function updateRecipientStatus($recipientId, $status) {
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(' ', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tstart = $mtime;
        }
        
        $this->goodnewsprocesshandler->lock($this->mailingid);

        if (!$this->goodnewsrecipienthandler->cleanupRecipient($recipientId, $this->mailingid, $status)) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::updateRecipientStatus - Status for recipient [id: '.$recipientId.'] could not be updated to: '.$status); }
            $this->goodnewsprocesshandler->unlock($this->mailingid);
            return false;
        }

        $meta = $this->modx->getObject('GoodNewsMailingMeta',  array('mailing_id'=>$this->mailingid));
        if (!is_object($meta)) { return false; }

        // Increase sent counter in mailing meta
        $recipientsSent = $meta->get('recipients_sent') + 1;
        $meta->set('recipients_sent', $recipientsSent);
        
        if ($status == GoodNewsRecipientHandler::GON_USER_SEND_ERROR) {
            // Increase error counter in mailing meta
            $recipientsError = $meta->get('recipients_error') + 1;
            $meta->set('recipients_error', $recipientsError);
        }
        $meta->save();
        unset($meta);
        
        $this->goodnewsprocesshandler->unlock($this->mailingid);
        
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(' ', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tend = $mtime;
            $totalTime = ($tend - $tstart);
            $totalTime = sprintf("%2.4f s", $totalTime);
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::updateRecipientStatus - Lock time: '.$totalTime);
        }
        return true;
    }

    /**
     * Get test-recipients.
     * (get test-recipients from users table - not pre-generated!)
     *
     * @access public
     * @return mixed array $testrecipients || false if empty
     */
    public function getTestRecipients() {
        $c = $this->modx->newQuery('modUser');
        $c->leftJoin('GoodNewsSubscriberMeta', 'SubscriberMeta', 'SubscriberMeta.subscriber_id = modUser.id');  
        $c->where(array(
            'modUser.active' => true,
            'SubscriberMeta.testdummy' => 1,
        ));
        $recipients = $this->modx->getIterator('modUser', $c);

        $testrecipients = array();
        foreach ($recipients as $recipient) {
            $testrecipients[] = $recipient->get('id');            
        }
        if (count($testrecipients) == 0) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getTestRecipients - Test-recipients list is empty.'); }
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
            'MailingMeta.ipc_status' => self::GON_IPC_STATUS_STARTED,
        ));
        $mailings = $this->modx->getIterator('modResource', $c);
        
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
     * Get all mailing resources where sending has finished.
     *
     * @access public
     * @return array $mailingIDs or false
     */
    public function getMailingsFinished() {
        $c = $this->modx->newQuery('GoodNewsMailingMeta');
        $c->where(array(
            'recipients_total > 0',
            'recipients_total = recipients_sent',
            'finishedon > 0',
            'ipc_status' => self::GON_IPC_STATUS_STOPPED,
        ));
        $mailings = $this->modx->getIterator('GoodNewsMailingMeta', $c);
        
        $mailingIDs = array();
        foreach ($mailings as $mailing) {
            $mailingIDs[] = $mailing->get('id');
        }
        if (count($mailingIDs) == 0) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::getMailingsFinished - No mailing resources found for processing.'); }
            $mailingIDs = false;
        }
        return $mailingIDs;
    }

    /**
     * Processes a mailing.
     *
     * @access public
     * @param integer $id The ID of the mailing resource
     * @return boolean
     */
    public function processMailing($id) {
    
        $this->mailingid = $id;
        $this->mailing   = $this->_getMailingObject();
        if (!$this->mailing) { return false; }

        if (!$this->goodnewsprocesshandler->createLockFile($this->mailingid)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] Lockfile missing! Processing aborted.');
            exit();
        }
        
        $mail = $this->_getMailProperties();
        
        // Send a defined bulk of emails
        for ($n = 0; $n < $this->bulksize; $n++) {
            
            $recipientId = $this->getNextRecipient();
            
            // There are no more recipients -> mailing has finished!
            if (!$recipientId) {
                
                if ($this->goodnewsrecipienthandler->getRecipientReserved($this->mailingid)) {
                    // Before we stop, cleanup all timed out recipients!
                    while ($timeoutRecipientId = $this->goodnewsrecipienthandler->getRecipientTimeout($this->mailingid)) {
                        $this->updateRecipientStatus($timeoutRecipientId, GoodNewsRecipientHandler::GON_USER_SEND_ERROR);
                        if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::processMailing - Sending for recipient [id: '.$timeoutRecipientId.'] timed out.'); }
                    }
                } else {
                    // Stop this process and remove temp lockfile!
                    $this->goodnewsprocesshandler->setPid(getmypid());
                    $this->goodnewsprocesshandler->deleteProcessStatus();
                    $this->goodnewsprocesshandler->removeTempLockFile($this->mailingid);
                    
                    // Also we set the mailing to finished (= IPCstatus "stopped")
                    $this->setIPCstop($this->mailingid, true);
                    if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::processMailing - Mailing [id: '.$this->mailingid.'] finished.'); }
                }
                break;
            }            

            $subscriber = $this->_getSubscriberProperties($recipientId);
            if ($subscriber) {
                $temp_mail = $mail;
                $temp_mail['body'] = $this->_processSubscriberPlaceholders($temp_mail['body'], $subscriber);            
 
                if ($this->sendEmail($temp_mail, $subscriber)) {
                    $status = GoodNewsRecipientHandler::GON_USER_SENT;
                } else {
                    $status = GoodNewsRecipientHandler::GON_USER_SEND_ERROR;
                }
            
            // this could happen, if a subscriber was deleted while mailing is processed
            } else {
                $status = GoodNewsRecipientHandler::GON_USER_SEND_ERROR; // @todo: other status required eg. GON_USER_NOT_FOUND
            }
            
            $this->updateRecipientStatus($recipientId, $status);            
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
        if (empty($recipients)) { return false; }

        foreach ($recipients as $recipientId) {
            $subscriber = $this->_getSubscriberProperties($recipientId);
            $temp_mail = $mail;
            $temp_mail['body'] = $this->_processSubscriberPlaceholders($temp_mail['body'], $subscriber);
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

        // Set SMTP params for modMail based on container settings
        // (this enables each container to have it's own set of SMTP settings - overriding the MODX system settings)
        if ($this->mailing->getProperty('mailUseSmtp', 'goodnews', $this->modx->getOption('mail_use_smtp', null, false))) {
            $this->modx->mail->set(modMail::MAIL_ENGINE, 'smtp');
            $this->modx->mail->set(modMail::MAIL_SMTP_AUTH,   $this->mailing->getProperty('mailSmtpAuth',   'goodnews', $this->modx->getOption('mail_smtp_auth',   null, false)));
            $this->modx->mail->set(modMail::MAIL_SMTP_USER,   $this->mailing->getProperty('mailSmtpUser',   'goodnews', $this->modx->getOption('mail_smtp_user',   null, '')));
            $this->modx->mail->set(modMail::MAIL_SMTP_PASS,   $this->mailing->getProperty('mailSmtpPass',   'goodnews', $this->modx->getOption('mail_smtp_pass',   null, '')));
            $this->modx->mail->set(modMail::MAIL_SMTP_HOSTS,  $this->mailing->getProperty('mailSmtpHosts',  'goodnews', $this->modx->getOption('mail_smtp_hosts',  null, 'localhost:25')));
            $this->modx->mail->set(modMail::MAIL_SMTP_PREFIX, $this->mailing->getProperty('mailSmtpPrefix', 'goodnews', $this->modx->getOption('mail_smtp_prefix', null, '')));
            $helo = $this->mailing->getProperty('mailSmtpHelo', 'goodnews', $this->modx->getOption('mail_smtp_helo', null, ''));
            if (!empty($helo)) {
                $this->modx->mail->set(modMail::MAIL_SMTP_HELO, $helo);
            }
            $this->modx->mail->set(modMail::MAIL_SMTP_KEEPALIVE, $this->mailing->getProperty('mailSmtpKeepalive', 'goodnews', $this->modx->getOption('mail_smtp_keepalive', null, false)));
            $this->modx->mail->set(modMail::MAIL_SMTP_SINGLE_TO, $this->mailing->getProperty('mailSmtpSingleTo',  'goodnews', $this->modx->getOption('mail_smtp_single_to', null, false)));
            $this->modx->mail->set(modMail::MAIL_SMTP_TIMEOUT,   $this->mailing->getProperty('mailSmtpTimeout',   'goodnews', $this->modx->getOption('mail_smtp_timeout',   null, 10)));
        }
        $this->modx->mail->header('X-goodnews-user-id: '.$subscriber['subscriber_id']);
        $this->modx->mail->header('X-goodnews-mailing-id: '.$this->mailingid);
        $this->modx->mail->set(modMail::MAIL_BODY,      $mail['body']);
        $this->modx->mail->set(modMail::MAIL_BODY_TEXT, $mail['altbody']);
        $this->modx->mail->set(modMail::MAIL_FROM,      $mail['mailFrom']);
        $this->modx->mail->set(modMail::MAIL_FROM_NAME, $mail['mailFromName']);
        $this->modx->mail->set(modMail::MAIL_SENDER,    $mail['mailFrom']);
        $this->modx->mail->set(modMail::MAIL_SUBJECT,   $mail['subject']);
        $this->modx->mail->set(modMail::MAIL_CHARSET,   $mail['mailCharset']);
        $this->modx->mail->set(modMail::MAIL_ENCODING,  $mail['mailEncoding']);

        $this->modx->mail->address('reply-to',          $mail['mailReplyTo']);
        if (empty($subscriber['fullname'])) { $subscriber['fullname'] = $subscriber['email']; }
        $this->modx->mail->address('to', $subscriber['email'], $subscriber['fullname']);
        $this->modx->mail->setHTML($mail['ishtml']);
                
        $sent = $this->modx->mail->send();
        $this->modx->mail->reset();

        if (!$sent) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] Email could not be sent to '.$subscriber['email'].' ('.$subscriber['subscriber_id'].').');
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Mailer error: '.$this->modx->mail->mailer->ErrorInfo); }
        } else {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::sendEmail - Email sent to '.$subscriber['email'].' ('.$subscriber['subscriber_id'].').'); }
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
        $containers = $this->modx->getIterator('modResource', $c);

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
     * Automatically fix image sizes based on scr or style attributes.
     * (Uses pThumb extra)
     *
     * based on AutoFixImagesize Plugin by Gerrit van Aaken <gerrit@praegnanz.de>

     * @param string $base
     * @param string $html
     * @return mixed $html The parsed string or false
     */
    private function _autoFixImageSizes($base, $html) {
        
        if (empty($html)) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] [pid: '.getmypid().'] GoodNewsMailing::_autoFixImageSizes - No HTML content provided for parsing.'); }
            return false;
        }

        $images = array();
        $phpthumb_nohotlink_enabled = $this->modx->getOption('phpthumb_nohotlink_enabled', null, true);
        $phpthumb_nohotlink_valid_domains = $this->modx->getOption('phpthumb_nohotlink_valid_domains');
                
        // find all img elements with a src attribute
        preg_match_all('|\<img.*?src=[",\'](.*?)[",\'].*?[^>]+\>|i', $html, $filenames);
        
        // loop through all found img elements
        foreach($filenames[1] as $i => $filename) {
        
            $img_old = $filenames[0][$i];
            $allowcaching = false;
            
            // is file already cached?
            if (strpos($filename, '?') == false || strpos($filename, '/phpthumb') == false) {
                
                // check if external caching is allowed
                if (substr($filename,0,7) == 'http://' || substr($filename, 0, 8) == 'https://') {
                    $pre = '';
                    if ($phpthumb_nohotlink_enabled) {
                        foreach (explode(',', $phpthumb_nohotlink_valid_domains) as $alldomain) {
                            if (strpos(strtolower($filename), strtolower(trim($alldomain))) != false) {
                                $allowcaching = true;
                            }
                        } 
                    } else {
                        $allowcaching = true;
                    }
                } else {
                    $pre = $base;
                    $allowcaching = true;
                }
            }
            
            // do we have physical access to the file?
            $mypath = $pre.str_replace('%20', ' ', $filename);
            if ($allowcaching && $dimensions = @getimagesize($mypath, $info)) {
                
                // find width and height attribut and save value
                preg_match_all('|width=[",\']([0-9]+?)[",\']|i', $filenames[0][$i], $widths);
                if (isset($widths[1][0])) {
                    $width = $widths[1][0];
                } else {
                    preg_match_all('|width:\s*([0-9]+?)px|i', $filenames[0][$i], $widths);
                    if (isset($widths[1][0])) {
                        $width = $widths[1][0];
                    } else {
                        $width = false;
                    }
                }
                preg_match_all('|height=[",\']([0-9]+?)[",\']|i', $filenames[0][$i], $heights);
                if (isset($heights[1][0])) {
                    $height = $heights[1][0];
                } else {
                    preg_match_all('|height:\s*([0-9]+?)px|i', $filenames[0][$i], $heights);
                    if (isset($heights[1][0])) {
                        $height = $heights[1][0];
                    } else {
                        $height = false;
                    }
                }
                
                // if resizing needed...
                if (($width && $width != $dimensions[0]) || ($height && $height != $dimensions[1])) {
                
                    // prepare resizing metadata
                    $filetype = strtolower(substr($filename, strrpos($filename,".")+1));
                    $image = array();
                    $image['input'] = $filename;
                    $image['options'] = 'f='.$filetype.'&h='.$height.'&w='.$width.'&iar=1'; 
                    
                    // perform physical resizing and caching via phpthumbof
                    $cacheurl = $this->modx->runSnippet('phpthumbof', $image);
                    
                    // set freshly cached image file location into old src attribute
                    $img_new = str_replace($filename, $cacheurl, $img_old);  
                    
                    // replace old image element with new one on whole page content
                    $html = str_replace($img_old, $img_new, $html);  
                }
            }
        } // end: foreach
        
        return $html;
    }

    /**
     * Replace URLs in HTML with full URLs
     * (works for "<a href" and "<img src" tags)
     *
     * @access private
     * @param string $base The base URL (needs trailing /)
     * @param string $html The unparsed HTML
     * @return mixed The parsed HTML as string or false
     */
    private function _fullURLs($base = null, $html = null) {
        if (empty($html) || empty($base)) { return false; }
        
        // Use the SmartDOMDocument extension
        if (!class_exists('SmartDOMDocument')) { return false; }
    
        $smartDOMDocument = new SmartDOMDocument();
        if (!($smartDOMDocument instanceof SmartDOMDocument)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] SmartDOMDocument class could not be instantiated.');
            return false;
        }
    
        $smartDOMDocument->loadHTML($html);
        
        // Process all link tags
        $elements = $smartDOMDocument->getElementsByTagName('a');
    
        foreach ($elements as $element){
            // Get the value of the href attribute
            $href = $element->getAttribute('href');
            
            // Check if we have a protocol-relative URL - if so, don't touch and continue!
            // Sample:  //www.domain.com/page.html 
            if (mb_substr($href, 0, 2) == '//') { continue; }
            
            // Remove leading / from relative URLs
            $href = ltrim($href, '/');
            
            // De-construct the UR(L|I)
            $url_parts = parse_url($href);
            
            // ['scheme']   - (string) https | http | ftp | ...
            // ['host']     - (string) www.domain.com
            // ['port']     - (int)    9090
            // ['user']     - (string) username
            // ['pass']     - (string) password
            // ['path']     - (string) section1/page1.html | /section1/page1.html
            // ['query']    - (string) all after ?
            // ['fragment'] - (string) all after text anchor #
            
            // Check if UR(L|I) is completely invalid - if so, don't touch and continue!
            if ($url_parts == false) { continue; }
            
            // Check if text anchor only - if so, don't touch and continue!
            // Sample: #textanchor
            if (!empty($url_parts['fragment']) && empty($url_parts['scheme']) && empty($url_parts['host']) && empty($url_parts['path'])) { continue; }
    
            // Finally add base URL to href value
            if (empty($url_parts['host'])) {
                $element->setAttribute('href', $base.$href);
            }
        }
    
        // Process all img tags
        $elements = $smartDOMDocument->getElementsByTagName('img');
        
        foreach ($elements as $element){
            // Get the value of the img attribute
            $href = $element->getAttribute('src');
            
            // Check if we have a protocol-relative URL - if so, don't touch and continue!
            // Sample:  //www.domain.com/page.html 
            if (mb_substr($href, 0, 2) == '//') { continue; }
            
            // Remove / from relative URLs
            $href = ltrim($href, '/');
            
            // De-construct the UR(L|I)
            $url_parts = parse_url($href);
    
            // Check if UR(L|I) is completely invalid - if so, don't touch and continue!
            if ($url_parts == false) { continue; }
            
            // Finally add base URL to href value
            if (empty($url_parts['host'])) {
                $element->setAttribute('src', $base.$href);
            }
        }
            
        // Return the processed (X)HTML
        return $smartDOMDocument->saveHTMLExact();
    }

    /**
     * Convert HTML into HTML with inline styles.
     * (https://github.com/tijsverkoyen/CssToInlineStyles)
     * Requires PHP 5.3 (or later)!
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
            $status = self::GON_STATUS_REPORT_MAILING_FINISHED;
        } else {
            $status = self::GON_STATUS_REPORT_MAILING_STOPPED;
        }
        $meta->set('ipc_status', self::GON_IPC_STATUS_STOPPED);
        if ($meta->save()) {
            // Send status report to MODX user who initiated (sent) the mailing if enabled
            $statusemail = $this->modx->getOption('goodnews.statusemail_enabled', null, 1);
            if ($statusemail) {
                $this->_statusReport($id, $status);
            }
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
     * Generates a status report to be sent via mail.
     *
     * @access private
     * @param integer $id The id of the mailing Resource document
     * @param integer $status The status of the mailing to report (default: self::GON_STATUS_REPORT_MAILING_FINISHED)
     * @return array
     */
    private function _statusReport($id, $status = self::GON_STATUS_REPORT_MAILING_FINISHED) {
        $mailing = $this->modx->getObject('GoodNewsResourceMailing', $id);
        if (!is_object($mailing)) { return false; }

        $meta = $mailing->getOne('MailingMeta');
        if (!is_object($meta)) { return false; }
        
        $profile = $this->modx->getObject('modUserProfile',  array('internalKey'=>$meta->get('sentby')));
        if (!is_object($profile)) { return false; }

        $user = $profile->getOne('User');
        
        $properties = array();
        
        // Properties for sending email
        $properties['email']    = $profile->get('email');
        $properties['name']     = $profile->get('fullname');
        $properties['subject']  = $this->modx->lexicon('goodnews.newsletter_statusemail_subject_prefix').$mailing->get('pagetitle');
        $properties['from']     = $this->modx->getOption('emailsender');
        $properties['fromname'] = $this->modx->getOption('goodnews.statusemail_fromname');

        $tpl     = $this->modx->getOption('goodnews.statusemail_chunk');    
        $tplAlt  = '';         // @todo: alternative plaintext template
        $tplType = 'modChunk'; // @todo: the type of tpl/chunk can be file

        // Email body placeholders
        $properties['mailing_title']    = $mailing->get('pagetitle');
        $properties['recipients_total'] = $meta->get('recipients_total');
        $properties['recipients_sent']  = $meta->get('recipients_sent');
        $properties['recipients_error'] = $meta->get('recipients_error');
        $properties['senton']           = $meta->get('senton');
        $properties['finishedon']       = $meta->get('finishedon');
        $properties['sentby']           = $user->get('username');
        
        switch ($status) {
            
            case self::GON_STATUS_REPORT_MAILING_STOPPED;
                $properties['mailingstatus'] = $this->modx->lexicon('goodnews.newsletter_status_stopped');
                break;
                
            case self::GON_STATUS_REPORT_MAILING_FINISHED;
                $properties['mailingstatus'] = $this->modx->lexicon('goodnews.newsletter_status_finished');
                break;
        }

        // Parsed email body
        $properties['msg']      = $this->_getChunk($tpl, $properties, $tplType);
        $properties['msgAlt']   = (!empty($tplAlt)) ? $this->_getChunk($tplAlt, $properties, $tplType) : '';

        $this->_sendStatusEmail($properties);
    }

    /**
     * Sends a newsletter status email based on the specified information and templates.
     *
     * @access private
     * @param array $properties A collection of mail properties.
     * @return boolean
     */
    private function _sendStatusEmail($properties = array()) {
        if (empty($properties['email']) || empty($properties['subject']) || empty($properties['msg']) || empty($properties['from'])) {
            return false;
        }
        $email    = $properties['email'];
        $name     = (!empty($properties['name'])) ? $properties['name'] : $properties['email'];
        $subject  = $properties['subject'];
        $from     = $properties['from'];
        $fromName = $properties['fromname'];
        $sender   = $properties['from'];
        $replyTo  = $properties['from'];
        $msg      = $properties['msg'];
        $msgAlt   = $properties['msgAlt'];

        $this->modx->getService('statusmail', 'mail.modPHPMailer');
        $this->modx->statusmail->set(modMail::MAIL_BODY, $msg);
        if (!empty($msgAlt)) {
            $this->modx->statusmail->set(modMail::MAIL_BODY_TEXT, $msgAlt);
        }
        $this->modx->statusmail->set(modMail::MAIL_FROM, $from);
        $this->modx->statusmail->set(modMail::MAIL_FROM_NAME, $fromName);
        $this->modx->statusmail->set(modMail::MAIL_SENDER, $sender);
        $this->modx->statusmail->set(modMail::MAIL_SUBJECT, $subject);
        $this->modx->statusmail->address('reply-to', $replyTo);
        $this->modx->statusmail->address('to', $email, $name);
        $this->modx->statusmail->setHTML(true);
        
        $sent = $this->modx->statusmail->send();
        $this->modx->statusmail->reset();
        
        if (!$sent) {
            if ($this->debug) { $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] GoodNewsMailing::_sendStatusEmail - Mailer error: '.$this->modx->statusmail->mailer->ErrorInfo); }
        }
        return $sent;
    }
    
    /**
     * Helper function to get a chunk or tpl by different methods.
     *
     * @access private
     * @param string $name The name of the tpl/chunk.
     * @param array $properties The properties to use for the tpl/chunk.
     * @param string $type The type of tpl/chunk. Can be modChunk or file. Defaults to modChunk.
     * @return string The processed tpl/chunk.
     */
    private function _getChunk($name, $properties, $type = 'modChunk') {
        $output = '';
        switch ($type) {
            case 'modChunk':
                $output .= $this->modx->getChunk($name, $properties);
                break;
                
            case 'file':
                $name = str_replace(array(
                    '{base_path}',
                    '{assets_path}',
                    '{core_path}',
                ),array(
                    $this->modx->getOption('base_path'),
                    $this->modx->getOption('assets_path'),
                    $this->modx->getOption('core_path'),
                ), $name);
                $output .= file_get_contents($name);
                $this->modx->setPlaceholders($properties);
                break;
        }
        return $output;
    }
}
