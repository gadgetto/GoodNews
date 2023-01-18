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
use MODX\Revolution\modResource;
use MODX\Revolution\modChunk;
use MODX\Revolution\Mail\modMail;
use Bitego\GoodNews\ProcessHandler;
use Bitego\GoodNews\RecipientsHandler;
use Bitego\GoodNews\Model\GoodNewsResourceContainer;
use Bitego\GoodNews\Model\GoodNewsResourceMailing;
use Bitego\GoodNews\Model\GoodNewsMailingMeta;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Soundasleep\Html2Text;

/**
 * Mailer class handles mass-email sending.
 *
 * @package goodnews
 */
class Mailer
{
    public const GON_IPC_STATUS_STOPPED  = 0;
    public const GON_IPC_STATUS_STARTED  = 1;

    public const GON_STATUS_REPORT_MAILING_STOPPED  = 1;
    public const GON_STATUS_REPORT_MAILING_FINISHED = 2;

    /** @var modX $modx A reference to the modX object */
    public $modx = null;

    /** @var GoodNewsResourceMailing $mailing A mailing resource object */
    public $mailing = null;

    /** @var ProcessHandler $processhandler A processhandler object */
    public $processhandler = null;

    /** @var RecipientsHandler $recipientshandler A recipientshandler object */
    public $recipientshandler = null;

    /** @var int $mailingid The id of the current mailing resource */
    public $mailingid = 0;

    /** @var int $bulksize The maximum value of mails to send by one task */
    public $bulksize = 30;

    /** @var boolean $testMailing Is this a test mailing? */
    public $testMailing = false;

    /** @var boolean $debug Debug mode on/off */
    public $debug = false;

    /** @var array $subscriberFields The object fields of modUser + modUserProfile + GoodNewsSubscriberMeta */
    public $subscriberFields = [];

    /**
     * Constructor for Mailer object
     *
     * @param modX $modx
     */
    public function __construct(modX &$modx)
    {
        $this->modx = &$modx;
        $this->debug = $this->modx->getOption('goodnews.debug', null, false) ? true : false;
        $this->bulksize = $this->modx->getOption('goodnews.mailing_bulk_size', null, 30);
        $this->modx->lexicon->load('goodnews:default');
        $this->processhandler = new ProcessHandler($this->modx);
        if (!$this->processhandler->createLockFileDir()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Lockfile directory missing! Processing aborted.');
            exit();
        }
        $this->recipientshandler = new RecipientsHandler($this->modx);

        // Get the default fields for a subscriber (for later use as placeholders)
        $this->subscriberFields = array_merge(
            $this->modx->getFields(GoodNewsSubscriberMeta::class),
            $this->modx->getFields(modUserProfile::class)
        );
        $this->subscriberFields = $this->cleanupKeys($this->subscriberFields);
    }

    /**
     * Get the mail properties and collect in array.
     *
     * @access private
     * @return array $properties The collection of properties || false
     */
    private function getMailProperties()
    {
        $this->changeContext();

        $properties = [];
        $properties['subject'] = $this->getMailSubject();
        $properties['ishtml'] = $this->mailing->get('richtext') ? true : false;
        if ($properties['ishtml']) {
            $properties['body'] = $this->getHTMLMailBody();
            // This is filled later when subscriber placeholders are processed!
            $properties['altbody'] = '';
        } else {
            $properties['body'] = $this->getPlainMailBody();
            // This stays empty when plain text mail is sent!
            $properties['altbody'] = '';
        }
        $properties['mailFrom'] = $this->mailing->getProperty(
            'mailFrom',
            'goodnews',
            $this->modx->getOption('emailsender')
        );
        $properties['mailFromName'] = $this->mailing->getProperty(
            'mailFromName',
            'goodnews',
            $this->modx->getOption('site_name')
        );
        $properties['mailReplyTo'] = $this->mailing->getProperty(
            'mailReplyTo',
            'goodnews',
            $this->modx->getOption('emailsender')
        );
        $properties['mailCharset'] = $this->mailing->getProperty(
            'mailCharset',
            'goodnews',
            $this->modx->getOption('mail_charset', null, 'UTF-8')
        );
        $properties['mailEncoding'] = $this->mailing->getProperty(
            'mailEncoding',
            'goodnews',
            $this->modx->getOption('mail_encoding', null, '8bit')
        );

        return $properties;
    }

    /**
     * Get the subscriber properties and collect in array.
     *
     * @access private
     * @param integer $subscriberId The ID of the subscriber
     * @return mixed $properties The collection of properties || false
     */
    private function getSubscriberProperties($subscriberId)
    {
        $meta = $this->modx->getObject(GoodNewsSubscriberMeta::class, ['subscriber_id' => $subscriberId]);
        if (!$meta) {
            return '';
        }
        $profile = $this->modx->getObject(modUserProfile::class, ['internalKey' => $subscriberId]);
        if (!$profile) {
            return '';
        }
        // Flatten extended fields:
        // extended.field1
        // extended.container1.field2
        // ...
        $extended = $profile->get('extended') ? $profile->get('extended') : [];
        if (!empty($extended)) {
            $extended = $this->flattenExtended($extended, 'extended.');
        }
        $properties = array_merge(
            $meta->toArray(),
            $profile->toArray(),
            $extended
        );
        $properties = $this->cleanupKeys($properties);
        return $properties;
    }

    /**
     * Manipulate/add/remove fields from array.
     *
     * @access private
     * @param array $properties
     * @return array $properties
     */
    private function cleanupKeys(array $properties = [])
    {
        unset(
            $properties['id'],          // multiple occurrence; not needed
            $properties['internalKey'], // not needed
            $properties['sessionid'],   // security!
            $properties['extended']     // not needed as its already flattened
        );
        return $properties;
    }

    /**
     * Helper function to recursively flatten an extended fields array.
     *
     * @access private
     * @param array $array The array to be flattened.
     * @param string $prefix The prefix for each new array key.
     * @return array $result The flattened and prefixed array.
     */
    private function flattenExtended($array, $prefix = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + $this->flattenExtended($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
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
    private function getMailSubject()
    {
        $subject = $this->mailing->get('pagetitle');
        if ($this->testMailing) {
            $subject = $this->modx->getOption('goodnews.test_subject_prefix') . $subject;
        }
        // Convert subject to charset of mailing
        $mail_charset = $this->modx->getOption('mail_charset', null, 'UTF-8');
        $modx_charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $mail_charset_goodnews = $this->mailing->getProperty('mailCharset', 'goodnews', $mail_charset);
        $subject = iconv($modx_charset, $mail_charset_goodnews . '//TRANSLIT', $subject);
        return $subject;
    }

    /**
     * Get the full parsed HTML from a resource document.
     *
     * @access private
     * @return string $html The parsed html of the resource
     */
    private function getHTMLMailBody()
    {
        // Store some values for later restoration
        $currentResource = $this->modx->resource;
        $currentResourceIdentifier = $this->modx->resourceIdentifier;
        $currentElementCache = $this->modx->elementCache;

        // Prepare to process the Resource
        $this->modx->resource = $this->mailing;
        $this->modx->resourceIdentifier = $this->mailing->get('id');
        $this->modx->elementCache = [];

        // The Resource having access to itself via $this->modx->resource is critical
        // for getting resource fields, as well as for proper execution of Snippets
        // that may appear in the content.

        // Process and return the cacheable content of the Resource
        $html = $this->modx->resource->process();

        // Determine how many passes the parser should take at a maximum
        $maxIterations = intval($this->modx->getOption('parser_max_iterations', null, 10));

        if (!$this->modx->parser) {
            $this->modx->getParser();
        }

        // Preserve GoodNews subscriber fields placeholders
        $phsArray = $this->subscriberFields;
        $search = [];
        $replace = [];

        foreach ($phsArray as $phs => $values) {
            $search[] = '[[+' . $phs;
            $replace[] = '&#91;&#91;+' . $phs;
        }
        $html = str_ireplace($search, $replace, $html);

        foreach ($phsArray as $phs => $values) {
            $search[] = '[[+extended';
            $replace[] = '&#91;&#91;+extended';
        }
        $html = str_ireplace($search, $replace, $html);

        // Process the non-cacheable content of the Resource, but leave any unprocessed tags alone
        $this->modx->parser->processElementTags('', $html, true, false, '[[', ']]', [], $maxIterations);

        // Process the non-cacheable content of the Resource, this time removing the unprocessed tags
        $this->modx->parser->processElementTags('', $html, true, true, '[[', ']]', [], $maxIterations);

        // Set back GoodNews subscriber fields placeholders to it's original form
        $search = [];
        $replace = [];

        foreach ($phsArray as $phs => $value) {
            $search[] = '&#91;&#91;+' . $phs;
            $replace[] = '[[+' . $phs;
        }
        $html = str_ireplace($search, $replace, $html);

        foreach ($phsArray as $phs => $value) {
            $search[] = '&#91;&#91;+extended';
            $replace[] = '[[+extended';
        }
        $html = str_ireplace($search, $replace, $html);

        // Restore original values
        $this->modx->elementCache = $currentElementCache;
        $this->modx->resourceIdentifier = $currentResourceIdentifier;
        $this->modx->resource = $currentResource;

        // AutoInline CSS styles from template header (<styles>...</styles>) if activated in settings
        if ($this->modx->getOption('goodnews.auto_inline_css', null, true)) {
            $html = $this->inlineCSS($html);
        }

        // AutoFixImageSizes if activated in settings
        if ($this->modx->getOption('goodnews.auto_fix_imagesizes', null, true)) {
            $base = $this->modx->getOption('site_url');
            $html = $this->autoFixImageSizes($base, $html);
        }

        // Make full URLs if activated in settings
        if ($this->modx->getOption('goodnews.auto_full_urls', null, true)) {
            $base = $this->modx->getOption('site_url');
            $html = $this->fullURLs($base, $html);
        }

        return $html;
    }

    /**
     * Get the parsed plain-text mail body
     * (content field only as we don't use a template!)
     * (FullURLs generation is not supported here as we have no DOM to parse)
     *
     * @param $id
     * @return mixed string $body || false
     */
    private function getPlainMailBody()
    {
        $mailingObj = $this->mailing;
        $mailingObj->set('cacheable', false);
        $mailingObj->set('_processed', false);
        $mailingObj->set('_content', false);
        $mailingObj->set('template', 0); // No template is used!
        $body = $mailingObj->process();
        return $body;
    }

    /**
     * Replace GoodNews Subscriber placeholders in preparsed newsletter template.
     *
     * @access private
     * @param string $html
     * @param array $subscriberProperties The placeholders.
     * @return mixed string $output || boolean
     */
    private function processSubscriberPlaceholders($html, array $subscriberProperties = [])
    {
        if (empty($html)) {
            return false;
        }
        $chunk = $this->modx->newObject(modChunk::class);
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
    public function getNextRecipient()
    {
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(' ', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tstart = $mtime;
        }

        $this->processhandler->lock($this->mailingid);

        // Find next unsent recipient
        $recipientId = $this->recipientshandler->getRecipientUnsent($this->mailingid);

        // No more recipients (or list is empty which shouldn't happen here)
        if (!$recipientId) {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' .
                    getmypid() .
                    '] Mailer::getNextRecipient - No unsent recipients found.'
                );
            }
            $this->processhandler->unlock($this->mailingid);
            return false;
        }
        // Habemus recipient!
        if ($this->debug) {
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[GoodNews] [pid: ' .
                getmypid() .
                '] Mailer::getNextRecipient - Unsent recipient [id: ' .
                $recipientId .
                '] found.'
            );
        }

        $this->processhandler->unlock($this->mailingid);

        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(" ", $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tend = $mtime;
            $totalTime = ($tend - $tstart);
            $totalTime = sprintf("%2.4f s", $totalTime);
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[GoodNews] [pid: ' .
                getmypid() .
                '] Mailer::getNextRecipient - Lock time: ' .
                $totalTime
            );
        }
        return $recipientId;
    }

    /**
     * Update the status of a recipient.
     *
     * @access public
     * @param integer $recipientId The id of the recipient
     * @param integer $status The status of the recipient
     * @param string $log The status/error text
     * @return boolean
     */
    public function updateRecipientStatus($recipientId, $status, string $log = '')
    {
        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(' ', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tstart = $mtime;
        }

        $this->processhandler->lock($this->mailingid);

        if (!$this->recipientshandler->cleanupRecipient($recipientId, $this->mailingid, $status, $log)) {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' .
                    getmypid() .
                    '] Mailer::updateRecipientStatus - Status for recipient [id: ' .
                    $recipientId .
                    '] could not be updated to: ' .
                    $status
                );
            }
            $this->processhandler->unlock($this->mailingid);
            return false;
        }

        $meta = $this->modx->getObject(GoodNewsMailingMeta::class, ['mailing_id' => $this->mailingid]);
        if (!is_object($meta)) {
            return false;
        }

        // Increase sent counter in mailing meta
        $recipientsSent = $meta->get('recipients_sent') + 1;
        $meta->set('recipients_sent', $recipientsSent);

        if ($status == RecipientsHandler::GON_USER_SEND_ERROR) {
            // Increase error counter in mailing meta
            $recipientsError = $meta->get('recipients_error') + 1;
            $meta->set('recipients_error', $recipientsError);
        }
        $meta->save();
        unset($meta);

        $this->processhandler->unlock($this->mailingid);

        if ($this->debug) {
            $mtime = microtime();
            $mtime = explode(' ', $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $tend = $mtime;
            $totalTime = ($tend - $tstart);
            $totalTime = sprintf("%2.4f s", $totalTime);
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[GoodNews] [pid: ' .
                getmypid() .
                '] Mailer::updateRecipientStatus - Lock time: ' .
                $totalTime
            );
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
    public function getTestRecipients()
    {
        $c = $this->modx->newQuery(modUser::class);
        $c->leftJoin(GoodNewsSubscriberMeta::class, 'SubscriberMeta', 'SubscriberMeta.subscriber_id = modUser.id');
        $c->where([
            'modUser.active' => true,
            'SubscriberMeta.testdummy' => 1,
        ]);
        $recipients = $this->modx->getIterator(modUser::class, $c);

        $testrecipients = [];
        foreach ($recipients as $recipient) {
            $testrecipients[] = $recipient->get('id');
        }
        if (count($testrecipients) == 0) {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' .
                    getmypid() .
                    '] Mailer::getTestRecipients - Test-recipients list is empty.'
                );
            }
            $testrecipients = false;
        }
        return $testrecipients;
    }

    /**
     * Get all mailing resources to be sent.
     *
     * @access public
     * @return array $mailingIDs || false
     */
    public function getMailingsToSend()
    {
        $containerIDs = $this->getGoodNewsContainers();
        if (empty($containerIDs)) {
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[GoodNews]  Mailer::getMailingsToSend - No mailing containers found.'
            );
            return false;
        }
        // Check for scheduled mailings
        $this->startScheduledMailings();

        $c = $this->modx->newQuery(modResource::class);
        $c->leftJoin(GoodNewsMailingMeta::class, 'MailingMeta', 'MailingMeta.mailing_id = modResource.id');
        $c->where([
            'modResource.published'  => true,
            'modResource.deleted'    => false,
            'modResource.parent:IN'  => $containerIDs,
            'MailingMeta.ipc_status' => self::GON_IPC_STATUS_STARTED,
        ]);
        $mailings = $this->modx->getIterator(modResource::class, $c);

        $mailingIDs = [];
        foreach ($mailings as $mailing) {
            $mailingIDs[] = $mailing->get('id');
        }
        if (count($mailingIDs) == 0) {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' .
                    getmypid() .
                    '] Mailer::getMailingsToSend - No mailing resources found for processing.'
                );
            }
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
    public function getMailingsFinished()
    {
        $c = $this->modx->newQuery(GoodNewsMailingMeta::class);
        $c->where([
            'recipients_total > 0',
            'recipients_total = recipients_sent',
            'finishedon > 0',
            'ipc_status' => self::GON_IPC_STATUS_STOPPED,
        ]);
        $mailings = $this->modx->getIterator(GoodNewsMailingMeta::class, $c);

        $mailingIDs = [];
        foreach ($mailings as $mailing) {
            $mailingIDs[] = $mailing->get('id');
        }
        if (count($mailingIDs) == 0) {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' .
                    getmypid() .
                    '] Mailer::getMailingsFinished - No mailing resources found for processing.'
                );
            }
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
    public function processMailing($id)
    {
        $this->mailingid = $id;
        $this->mailing = $this->getMailingObject();
        if (!$this->mailing) {
            return false;
        }

        if (!$this->processhandler->createLockFile($this->mailingid)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Lockfile missing! Processing aborted.');
            exit();
        }

        $mail = $this->getMailProperties();

        // Send a defined bulk of emails
        for ($n = 0; $n < $this->bulksize; $n++) {
            $recipientId = $this->getNextRecipient();

            // There are no more recipients -> mailing has finished!
            if (!$recipientId) {
                if ($this->recipientshandler->getRecipientReserved($this->mailingid)) {
                    // Before we stop, cleanup all timed out recipients!
                    while ($timeoutRecipientId = $this->recipientshandler->getRecipientTimeout($this->mailingid)) {
                        $this->updateRecipientStatus(
                            $timeoutRecipientId,
                            RecipientsHandler::GON_USER_SEND_ERROR,
                            'Sending timed out'
                        );
                        if ($this->debug) {
                            $this->modx->log(
                                modX::LOG_LEVEL_INFO,
                                '[GoodNews] [pid: ' .
                                getmypid() .
                                '] Mailer::processMailing - Sending for recipient [id: ' .
                                $timeoutRecipientId .
                                '] timed out.'
                            );
                        }
                    }
                } else {
                    // Stop this process and remove temp lockfile!
                    $this->processhandler->setPid(getmypid());
                    $this->processhandler->deleteProcessStatus();
                    $this->processhandler->removeTempLockFile($this->mailingid);

                    // Also we set the mailing to finished (= IPCstatus "stopped")
                    $this->setIPCstop($this->mailingid, true);
                    if ($this->debug) {
                        $this->modx->log(
                            modX::LOG_LEVEL_INFO,
                            '[GoodNews] [pid: ' .
                            getmypid() .
                            '] Mailer::processMailing - Mailing [id: ' .
                            $this->mailingid .
                            '] finished.'
                        );
                    }
                }
                break;
            }

            $subscriber = $this->getSubscriberProperties($recipientId);
            if ($subscriber) {
                $temp_mail = $mail;
                $temp_mail['body'] = $this->processSubscriberPlaceholders($temp_mail['body'], $subscriber);
                if ($temp_mail['ishtml']) {
                    // Convert HTML to plain text using "html2text" library
                    // (https://github.com/soundasleep/html2text)
                    $temp_mail['altbody'] = Html2Text::convert($temp_mail['body'], [
                        'ignore_errors' => true,
                    ]);
                }
                $result = $this->sendEmail($temp_mail, $subscriber);
                if ($result === true && !is_string($result)) {
                    $status = RecipientsHandler::GON_USER_SENT;
                    $log = '';
                } else {
                    $status = RecipientsHandler::GON_USER_SEND_ERROR;
                    $log = $result;
                }
            // This could happen, if a subscriber is deleted while mailing is processed
            } else {
                // @todo: other status required eg. GON_USER_NOT_FOUND
                $status = RecipientsHandler::GON_USER_SEND_ERROR;
                $log = 'Subscriber not found';
            }

            $this->updateRecipientStatus($recipientId, $status, $log);
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
    public function processTestMailing($id)
    {
        $this->mailingid = $id;
        $this->testMailing = true;
        $this->mailing = $this->getMailingObject();
        if (!$this->mailing) {
            return false;
        }

        $mail = $this->getMailProperties();
        $recipients = $this->getTestRecipients();
        if (empty($recipients)) {
            return false;
        }

        foreach ($recipients as $recipientId) {
            $subscriber = $this->getSubscriberProperties($recipientId);
            $temp_mail = $mail;
            $temp_mail['body'] = $this->processSubscriberPlaceholders($temp_mail['body'], $subscriber);
            if ($temp_mail['ishtml']) {
                // Create altbody -> convert HTML to plain text using "html2text" library
                // (https://github.com/soundasleep/html2text)
                $temp_mail['altbody'] = Html2Text::convert($temp_mail['body'], [
                    'ignore_errors' => true,
                ]);
            }
            $sent = $this->sendEmail($temp_mail, $subscriber);
        }
        return true;
    }

    /**
     * Sends an email based on the specified parameters using phpMailer.
     *
     * @access public
     * @param array $email
     * @param array $subscriber
     * @return mixed true If mail was sent successfully | Error text
     */
    public function sendEmail(array $email, array $subscriber)
    {
        $mail = $this->modx->services->get('mail');
        $mailUseSmtp = $this->mailing->getProperty(
            'mailUseSmtp',
            'goodnews',
            $this->modx->getOption('mail_use_smtp', null, false)
        );

        // SMTP params for modMail based on container settings
        // (this enables each container to have it's own set of
        // SMTP settings - overriding the MODX system settings)

        if ($mailUseSmtp) {
            $mailSmtpAuth = $this->mailing->getProperty(
                'mailSmtpAuth',
                'goodnews',
                $this->modx->getOption('mail_smtp_auth', null, false)
            );
            $mailSmtpUser = $this->mailing->getProperty(
                'mailSmtpUser',
                'goodnews',
                $this->modx->getOption('mail_smtp_user', null, '')
            );
            $mailSmtpPass = $this->mailing->getProperty(
                'mailSmtpPass',
                'goodnews',
                $this->modx->getOption('mail_smtp_pass', null, '')
            );
            $mailSmtpHosts = $this->mailing->getProperty(
                'mailSmtpHosts',
                'goodnews',
                $this->modx->getOption('mail_smtp_hosts', null, 'localhost:25')
            );
            $mailSmtpPrefix = $this->mailing->getProperty(
                'mailSmtpPrefix',
                'goodnews',
                $this->modx->getOption('mail_smtp_prefix', null, '')
            );
            $mailSmtpHelo = $this->mailing->getProperty(
                'mailSmtpHelo',
                'goodnews',
                $this->modx->getOption('mail_smtp_helo', null, '')
            );
            $mailSmtpKeepalive = $this->mailing->getProperty(
                'mailSmtpKeepalive',
                'goodnews',
                $this->modx->getOption('mail_smtp_keepalive', null, false)
            );
            $mailSmtpSingleTo = $this->mailing->getProperty(
                'mailSmtpSingleTo',
                'goodnews',
                $this->modx->getOption('mail_smtp_single_to', null, false)
            );
            $mailSmtpTimeout = $this->mailing->getProperty(
                'mailSmtpTimeout',
                'goodnews',
                $this->modx->getOption('mail_smtp_timeout', null, 10)
            );
            // This is from MODX system settings only
            // (GoodNews containers settings has [hostname:port] format)
            $mailSmtpPort = $this->modx->getOption('mail_smtp_port', null, 25);

            $mail->set(modMail::MAIL_ENGINE, 'smtp');
            $mail->set(modMail::MAIL_SMTP_AUTH, $mailSmtpAuth);
            $mail->set(modMail::MAIL_SMTP_USER, $mailSmtpUser);
            $mail->set(modMail::MAIL_SMTP_PASS, $mailSmtpPass);
            $mail->set(modMail::MAIL_SMTP_HOSTS, $mailSmtpHosts);
            $mail->set(modMail::MAIL_SMTP_PORT, $mailSmtpPort);
            $mail->set(modMail::MAIL_SMTP_PREFIX, $mailSmtpPrefix);
            if (!empty($mailSmtpHelo)) {
                $mail->set(modMail::MAIL_SMTP_HELO, $mailSmtpHelo);
            }
            $mail->set(modMail::MAIL_SMTP_KEEPALIVE, $mailSmtpKeepalive);
            $mail->set(modMail::MAIL_SMTP_SINGLE_TO, $mailSmtpSingleTo);
            $mail->set(modMail::MAIL_SMTP_TIMEOUT, $mailSmtpTimeout);
        }

        $mail->header('X-goodnews-user-id: ' . $subscriber['subscriber_id']);
        $mail->header('X-goodnews-mailing-id: ' . $this->mailingid);

        $mail->set(modMail::MAIL_BODY, $email['body']);
        $mail->set(modMail::MAIL_BODY_TEXT, $email['altbody']);
        $mail->set(modMail::MAIL_FROM, $email['mailFrom']);
        $mail->set(modMail::MAIL_FROM_NAME, $email['mailFromName']);
        $mail->set(modMail::MAIL_SENDER, $email['mailFrom']);
        $mail->set(modMail::MAIL_SUBJECT, $email['subject']);
        $mail->set(modMail::MAIL_CHARSET, $email['mailCharset']);
        $mail->set(modMail::MAIL_ENCODING, $email['mailEncoding']);

        $mail->address('reply-to', $email['mailReplyTo']);
        if (empty($subscriber['fullname'])) {
            $subscriber['fullname'] = $subscriber['email'];
        }
        $mail->address('to', $subscriber['email'], $subscriber['fullname']);
        $mail->setHTML($email['ishtml']);

        $sent = $mail->send();
        if (!$sent) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[GoodNews] Mailer::sendEmail - Email could not be sent to ' .
                $subscriber['email'] . ' (' . $subscriber['subscriber_id'] . ') -- Error: ' .
                $mail->mailer->ErrorInfo
            );
            $sent = $mail->mailer->ErrorInfo;
        } else {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' . getmypid() .
                    '] Mailer::sendEmail - Email sent to ' . $subscriber['email'] .
                    ' (' . $subscriber['subscriber_id'] . ')'
                );
            }
        }
        $mail->reset();
        return $sent;
    }

    /**
     * Get all mailing resource container ids.
     *
     * @access public
     * @return array $containerIDs
     */
    public function getGoodNewsContainers()
    {
        $c = $this->modx->newQuery(modResource::class);
        $c->where([
            'published' => true,
            'deleted'   => false,
            'class_key' => GoodNewsResourceContainer::class
        ]);
        $containers = $this->modx->getIterator(modResource::class, $c);

        $containerIDs = [];
        foreach ($containers as $container) {
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
    private function getMailingObject()
    {
        $this->mailing = $this->modx->getObject(GoodNewsResourceMailing::class, $this->mailingid);
        if (!is_object($this->mailing)) {
            return false;
        }
        return $this->mailing;
    }

    /**
     * Set the context based on the current resource.
     *
     * @access private
     * @return boolean
     */
    private function changeContext()
    {
        $key = $this->mailing->get('context_key');
        $this->modx->switchContext($key);
        return true;
    }

    /**
     * Automatically fix image sizes based on src or style attributes.
     * (Uses pThumb extra)
     *
     * based on AutoFixImagesize Plugin by Gerrit van Aaken <gerrit@praegnanz.de>

     * @param string $base
     * @param string $html
     * @return mixed $html The parsed string or false
     */
    private function autoFixImageSizes($base, $html)
    {
        if (empty($html)) {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' .
                    getmypid() .
                    '] Mailer::autoFixImageSizes - No HTML content provided for parsing.'
                );
            }
            return false;
        }

        $images = [];
        $phpthumb_nohotlink_enabled = $this->modx->getOption('phpthumb_nohotlink_enabled', null, true);
        $phpthumb_nohotlink_valid_domains = $this->modx->getOption('phpthumb_nohotlink_valid_domains');

        // Find all img elements with a src attribute
        preg_match_all('|\<img.*?src=[",\'](.*?)[",\'].*?[^>]+\>|i', $html, $filenames);

        // Loop through all found img elements
        foreach ($filenames[1] as $i => $filename) {
            $img_old = $filenames[0][$i];
            $allowcaching = false;

            // Is file already cached?
            if (strpos($filename, '?') == false || strpos($filename, '/phpthumb') == false) {
                //Check if external caching is allowed
                if (substr($filename, 0, 7) == 'http://' || substr($filename, 0, 8) == 'https://') {
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

            // Do we have physical access to the file?
            $mypath = $pre . str_replace('%20', ' ', $filename);
            if ($allowcaching && $dimensions = @getimagesize($mypath, $info)) {
                // Find width and height attribut and save value
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

                // If resizing needed...
                if (($width && $width != $dimensions[0]) || ($height && $height != $dimensions[1])) {
                    // Prepare resizing metadata
                    $filetype = strtolower(substr($filename, strrpos($filename, ".") + 1));
                    $image = [];
                    $image['input'] = $filename;
                    $image['options'] = 'f=' . $filetype . '&h=' . $height . '&w=' . $width . '&iar=1';

                    // Perform physical resizing and caching via phpthumbof
                    $cacheurl = $this->modx->runSnippet('phpthumbof', $image);

                    // Set freshly cached image file location into old src attribute
                    $img_new = str_replace($filename, $cacheurl, $img_old);

                    // Replace old image element with new one on whole page content
                    $html = str_replace($img_old, $img_new, $html);
                }
            }
        }
        return $html;
    }

    /**
     * Replace URLs in HTML with full URLs
     * (works for "<a href" and "<img src" tags)
     *
     * @access public
     * @param string $base The base URL (needs trailing /)
     * @param string $html The unparsed HTML
     * @return mixed $output The parsed HTML as string or false
     */
    public function fullURLs($base, $html)
    {
        if (empty($html) || empty($base)) {
            return false;
        }

        $document = new \DOMDocument();
        if (!$document instanceof \DOMDocument) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Mailer::fullURLs - DOMDocument class could not be instantiated. ' .
                'Can\'t convert full urls!'
            );
            return false;
        }

        // Preserve GoodNews subscriber fields placeholders (which aren't processed yet)
        $html = str_replace('[[', '%5B%5B', $html);
        $html = str_replace(']]', '%5D%5D', $html);

        // Set error level (suppress parser warnings)
        $internalErrors = libxml_use_internal_errors(true);

        // Ensure UTF-8 is respected by using 'mb_convert_encoding'
        $document->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        // Restore error level
        libxml_use_internal_errors($internalErrors);

        // Process all link tags
        $elements = $document->getElementsByTagName('a');

        foreach ($elements as $element) {
            // Get the value of the href attribute
            $href = $element->getAttribute('href');

            // Check if we have a protocol-relative URL - if so, don't touch and continue!
            // Sample: //www.domain.com/page.html
            if (mb_substr($href, 0, 2) == '//') {
                continue;
            }

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
            if ($url_parts == false) {
                continue;
            }

            // Check if text anchor only - if so, don't touch and continue!
            // Sample: #textanchor
            if (
                !empty($url_parts['fragment']) &&
                empty($url_parts['scheme']) &&
                empty($url_parts['host']) &&
                empty($url_parts['path'])
            ) {
                continue;
            }

            // Check if mailto: link - if so, don't touch and continue!
            if (!empty($url_parts['scheme']) && $url_parts['scheme'] == "mailto") {
                continue;
            }

            // Finally add base URL to href value
            if (empty($url_parts['host'])) {
                $element->setAttribute('href', $base . $href);
            }
        }

        // Process all img tags
        $elements = $document->getElementsByTagName('img');

        foreach ($elements as $element) {
            // Get the value of the img attribute
            $href = $element->getAttribute('src');

            // Check if we have a protocol-relative URL - if so, don't touch and continue!
            // Sample:  //www.domain.com/page.html
            if (mb_substr($href, 0, 2) == '//') {
                continue;
            }

            // Remove / from relative URLs
            $href = ltrim($href, '/');

            // De-construct the UR(L|I)
            $url_parts = parse_url($href);

            // Check if UR(L|I) is completely invalid - if so, don't touch and continue!
            if ($url_parts == false) {
                continue;
            }

            // Finally add base URL to href value
            if (empty($url_parts['host'])) {
                $element->setAttribute('src', $base . $href);
            }
        }

        // Return the processed (X)HTML
        $html = $document->saveHTML();

        // Set back GoodNews subscriber fields placeholders to it's original form
        $html = str_replace('%5B%5B', '[[', $html);
        $html = str_replace('%5D%5D', ']]', $html);

        return $html;
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
    private function inlineCSS($html)
    {
        if (empty($html)) {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' .
                    getmypid() .
                    '] Mailer::inlineCSS - No HTML content provided for parsing.'
                );
            }
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

        $cssToInlineStyles = new CssToInlineStyles();
        $className = CssToInlineStyles::class;
        if (!$cssToInlineStyles instanceof $className) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[GoodNews] Mailer::inlineCSS - CssToInlineStyles class could not be instantiated. ' .
                'Could not apply inline styles!'
            );
            return false;
        }

        $html = $cssToInlineStyles->convert(
            $html,
            $css_rules
        );

        // Workaround to preserve placeholder delimiters - as CssToInlineStyles converts special chars within urls
        $html = str_replace('%5B%5B', '[[', $html);
        $html = str_replace('%5D%5D', ']]', $html);

        return $html;
    }

    /**
     * Auto start scheduled mailings.
     * (Check for mailing resources with pub_date reached and set them to published
     * + GON_IPC_STATUS_STARTED in mailing meta table so they can be sent automatically)
     *
     * @access private
     * @return int $publishingResults The number of mailings affected by the sql statement.
     */
    private function startScheduledMailings()
    {
        $tblResource = $this->modx->getTableName(modResource::class);
        $tblMailingMeta = $this->modx->getTableName(GoodNewsMailingMeta::class);
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
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' .
                    getmypid() .
                    '] Mailer::autoPublish - autopublished mailings: ' .
                    $mailings
                );
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
    public function setIPCstart($id)
    {
        // Get resource mailing meta object
        $meta = $this->modx->getObject(GoodNewsMailingMeta::class, ['mailing_id' => $id]);
        if (!is_object($meta)) {
            return false;
        }

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
    public function setIPCstop($id, $finished = false)
    {
        // Get resource mailing meta object
        $meta = $this->modx->getObject(GoodNewsMailingMeta::class, ['mailing_id' => $id]);
        if (!is_object($meta)) {
            return false;
        }

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
                $this->statusReport($id, $status);
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
    public function setIPCcontinue($id)
    {
        // Get resource mailing meta object
        $meta = $this->modx->getObject(GoodNewsMailingMeta::class, ['mailing_id' => $id]);
        if (!is_object($meta)) {
            return false;
        }

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
    private function statusReport($id, $status = self::GON_STATUS_REPORT_MAILING_FINISHED)
    {
        $mailing = $this->modx->getObject(GoodNewsResourceMailing::class, $id);
        if (!is_object($mailing)) {
            return false;
        }
        $meta = $mailing->getOne('MailingMeta');
        if (!is_object($meta)) {
            return false;
        }
        $profile = $this->modx->getObject(modUserProfile::class, ['internalKey' => $meta->get('sentby')]);
        if (!is_object($profile)) {
            return false;
        }
        $user = $profile->getOne('User');
        $properties = [];

        // Properties for sending email
        $properties['email'] = $profile->get('email');
        $properties['name'] = $profile->get('fullname');
        $properties['subject'] = $this->modx->lexicon('goodnews.newsletter_statusemail_subject_prefix') .
            $mailing->get('pagetitle');
        $properties['from'] = $this->modx->getOption('emailsender');
        $properties['fromname'] = $this->modx->getOption('goodnews.statusemail_fromname');

        $tpl = $this->modx->getOption('goodnews.statusemail_chunk');
        $tplAlt = ''; // @todo: alternative plaintext template
        $tplType = 'modChunk'; // @todo: the type of tpl/chunk can be file

        // Email body placeholders
        $properties['mailing_title'] = $mailing->get('pagetitle');
        $properties['recipients_total'] = $meta->get('recipients_total');
        $properties['recipients_sent'] = $meta->get('recipients_sent');
        $properties['recipients_error'] = $meta->get('recipients_error');
        $properties['senton'] = $meta->get('senton');
        $properties['finishedon'] = $meta->get('finishedon');
        $properties['sentby'] = $user->get('username');

        switch ($status) {
            case self::GON_STATUS_REPORT_MAILING_STOPPED:
                $properties['mailingstatus'] = $this->modx->lexicon('goodnews.newsletter_status_stopped');
                break;

            case self::GON_STATUS_REPORT_MAILING_FINISHED:
                $properties['mailingstatus'] = $this->modx->lexicon('goodnews.newsletter_status_finished');
                break;
        }

        // Parsed email body
        $properties['msg'] = $this->getChunk($tpl, $properties, $tplType);
        $properties['msgAlt'] = (!empty($tplAlt)) ? $this->getChunk($tplAlt, $properties, $tplType) : '';

        $this->sendStatusEmail($properties);
    }

    /**
     * Sends a newsletter status email based on the specified information and templates.
     *
     * @access private
     * @param array $properties A collection of mail properties.
     * @return boolean
     */
    private function sendStatusEmail($properties = [])
    {
        if (
            empty($properties['email']) ||
            empty($properties['subject']) ||
            empty($properties['msg']) ||
            empty($properties['from'])
        ) {
            return false;
        }
        $email = $properties['email'];
        $name = (!empty($properties['name'])) ? $properties['name'] : $properties['email'];
        $subject = $properties['subject'];
        $from = $properties['from'];
        $fromName = $properties['fromname'];
        $sender = $properties['from'];
        $replyTo = $properties['from'];
        $msg = $properties['msg'];
        $msgAlt = $properties['msgAlt'];

        $statusmail = $this->modx->services->get('mail');
        $statusmail->set(modMail::MAIL_BODY, $msg);
        if (!empty($msgAlt)) {
            $statusmail->set(modMail::MAIL_BODY_TEXT, $msgAlt);
        }
        $statusmail->set(modMail::MAIL_FROM, $from);
        $statusmail->set(modMail::MAIL_FROM_NAME, $fromName);
        $statusmail->set(modMail::MAIL_SENDER, $sender);
        $statusmail->set(modMail::MAIL_SUBJECT, $subject);
        $statusmail->address('reply-to', $replyTo);
        $statusmail->address('to', $email, $name);
        $statusmail->setHTML(true);
        $sent = $statusmail->send();
        if (!$sent) {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[GoodNews] Mailer::sendStatusEmail - Mailer error: ' .
                    $statusmail->mailer->ErrorInfo
                );
            }
        }
        $statusmail->reset();
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
    private function getChunk($name, $properties, $type = 'modChunk')
    {
        $output = '';
        switch ($type) {
            case 'modChunk':
                $output .= $this->modx->getChunk($name, $properties);
                break;

            case 'file':
                $name = str_replace([
                    '{base_path}',
                    '{assets_path}',
                    '{core_path}',
                ], [
                    $this->modx->getOption('base_path'),
                    $this->modx->getOption('assets_path'),
                    $this->modx->getOption('core_path'),
                ], $name);
                $output .= file_get_contents($name);
                $this->modx->setPlaceholders($properties);
                break;
        }
        return $output;
    }
}
