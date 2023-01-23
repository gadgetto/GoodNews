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
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Model\GoodNewsGroupMember;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;

/**
 * ImportSubscribers class handles batch import of users into MODX users database
 * and make them GoodNews subscribers.
 *
 * @package goodnews
 */

class ImportSubscribers
{
    public const EMAIL    = 0;
    public const FULLNAME = 1;

    /** @var modX $modx A reference to the modX object */
    public $modx = null;

    /** @var array $config An array of config values */
    public $config = [];

    /** @var boolean $update If import mode = update */
    public $update = false;

    /** @var resource $fileHandle A valid file pointer to a file successfully opened */
    public $fileHandle = false;

    /** @var int $lineLength Must be greater than the longest line (in characters) to be found in the CSV file */
    public $lineLength;

    /** @var string $delimiter The field delimiter (one character only) */
    public $delimiter;

    /** @var string $enclosure The field enclosure character (one character only) */
    public $enclosure;

    /** @var string $escape The escape character (one character only). Defaults to backslash. */
    public $escape;

    /** @var boolean $hasHeader If the first row includes field names */
    public $hasHeader;

    /** @var array $header The first row (field names) */
    public $header = [];

    /** @var int $batchSize Number of users to be imported in one batch */
    public $batchSize;


    /**
     * Constructor for GoodNewsImportSubscribers object.
     *
     * @access public
     * @param modX &$modx A reference to the modX object
     */
    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx = &$modx;
        $this->modx->lexicon->load('goodnews:default');
        $this->config = array_merge([
            'use_multibyte' => (bool)$this->modx->getOption('use_multibyte', null, false),
            'encoding' => $this->modx->getOption('modx_charset', null, 'UTF-8'),
        ], $config);
    }

    /**
     * Destructor for GoodNewsImportSubscribers object.
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        $this->closeFile();
    }

    /**
     * Initialize csv file import.
     *
     * @access public
     * @param bool $update (default: false)
     * @param string $filePath
     * @param string $delimiter (default: ,)
     * @param string $enclosure (default: ")
     * @param string $escape (default: \)
     * @param int $lineLength (default: 1024)
     * @param bool $hasHeader (default: false)
     * @return boolean
     */
    public function init(
        $update,
        $filePath,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\',
        $lineLength = 1024,
        $hasHeader = false
    ) {
        $this->update      = $update;
        if ($this->openFile($filePath) == false) {
            return false;
        }
        $this->delimiter   = $delimiter;
        $this->enclosure   = $enclosure;
        $this->escape      = $escape;
        $this->lineLength  = $lineLength;
        $this->hasHeader   = $hasHeader;
        if ($this->hasHeader) {
            $this->getHeader();
        }
        return true;
    }

    /**
     * Open a file.
     *
     * @access private
     * @param string $filePath
     * @return mixed file handle || false
     */
    private function openFile($filePath)
    {
        $this->fileHandle = @fopen($filePath, 'r');
        return $this->fileHandle;
    }

    /**
     * Close a file.
     *
     * @access private
     * @return void
     */
    private function closeFile()
    {
        if ($this->fileHandle) {
            @fclose($this->fileHandle);
        }
    }

    /**
     * Get first line of CSV file as field names.
     *
     * @access private
     * @return void
     */
    private function getHeader()
    {
        $this->header = fgetcsv(
            $this->fileHandle,
            $this->lineLength,
            $this->delimiter,
            $this->enclosure,
            $this->escape
        );
    }

    /**
     * Get users data from CSV file.
     *
     * @todo Currently we only support CSV files with predifined columns/fields!
     *       email | fullname
     *
     * @access private
     * @return void
     */
    private function getImportUsers()
    {
        $importUsers = [];
        if ($this->batchSize > 0) {
            $lineCount = 0;
        } else {
            $lineCount = -1; // loop limit is ignored
        }
        while (
            $lineCount < $this->batchSize &&
            ($row = fgetcsv(
                $this->fileHandle,
                $this->lineLength,
                $this->delimiter,
                $this->enclosure,
                $this->escape
            )) !== false
        ) {
            $importUsers[] = $row;
            if ($this->batchSize > 0) {
                $lineCount++;
            }
        }
        return $importUsers;
    }

    /**
     * Import a batch of users into MODX database.
     *
     * @access public
     * @param int $batchSize (default: 0) If set to 0, get all the data at once
     * @param array $gonGroups Array of GoodNews group ids
     * @param array $gonCategories Array of GoodNews category ids
     * @return mixed int $importCount || bool
     */
    public function importUsers($batchSize = 0, $gonGroups = [], $gonCategories = [])
    {
        $this->batchSize = $batchSize;

        // At least 1 group is required (both needs to be arrays)
        if (empty($gonGroups) || !is_array($gonGroups) || !is_array($gonCategories)) {
            return false;
        }

        $importUsers = $this->getImportUsers();
        $importCount = 0;
        foreach ($importUsers as $importUser) {
            if ($this->emailExists($importUser[self::EMAIL])) {
                // If update mode is enabled
                if ($this->update) {
                    if ($this->updateSubscriber($importUser, $gonGroups, $gonCategories)) {
                        $importCount++;
                    }
                } else {
                    $this->modx->log(
                        modX::LOG_LEVEL_WARN,
                        '-> ' . $this->modx->lexicon(
                            'goodnews.import_subscribers_log_err_subscr_ae'
                        ) . $importUser[self::EMAIL]
                    );
                }
            } else {
                if ($this->saveSubscriber($importUser, $gonGroups, $gonCategories)) {
                    $importCount++;
                }
            }
        }
        return $importCount;
    }

    /**
     * Update a userprofile + subscriber meta + group/category member entry.
     *
     * @access private
     * @param array $fields The field values for the new MODX user ($fields[0] = email, $fields[1] = fullname)
     * @param array $groups The GoodNews group IDs for the new MODX user
     * @param array $categories The GoodNews category IDs for the new MODX user
     * @return boolean $subscriberUpdated
     */
    private function updateSubscriber($fields, $groups = [], $categories = [])
    {
        $subscriberUpdated = false;

        // Check if we have more than 1 modUserProfiles based on this email
        // -> Normally this should't be necessary but it's possible that we have multiple
        //    users with the same email address (if enabled in MODX system settings)
        // If we finde multiple users -> the update can't be performed!
        if ($this->modx->getCount(modUserProfile::class, ['email' => $fields[self::EMAIL]]) > 1) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '-> ' . $this->modx->lexicon(
                    'goodnews.import_subscribers_log_err_duplicate_email'
                ) . $fields[self::EMAIL]
            );
            return false;
        }

        $subscriberProfile = $this->modx->getObject(modUserProfile::class, ['email' => $fields[self::EMAIL]]);

        // Update the subscribers Full name if provided
        if (!empty($fields[self::FULLNAME])) {
            $subscriberProfile->set('fullname', $fields[self::FULLNAME]);
        }

        if ($subscriberProfile->save()) {
            $subscriberUpdated = true;
            $id = $subscriberProfile->get('internalKey'); // preserve id of updated user for later use

            // New GoodNewsSubscriberMeta if not exists
            if (!$this->modx->getObject(GoodNewsSubscriberMeta::class, ['subscriber_id' => $id])) {
                $subscriberMeta = $this->modx->newObject(GoodNewsSubscriberMeta::class);
                $subscriberMeta->set('subscriber_id', $id);
                $sid = md5(time() . $id);
                $subscriberMeta->set('sid', $sid);
                $subscriberMeta->set('subscribedon', time());
                $subscriberMeta->set('ip', 'imported'); // Set IP field to string 'imported' for later reference

                if (!$subscriberMeta->save()) {
                    $subscriberUpdated = false;
                }
            }

            // Update GoodNewsGroupMember entries (preserve existing!)
            if ($subscriberUpdated) {
                foreach ($groups as $groupid) {
                    if (
                        $this->modx->getObject(
                            GoodNewsGroupMember::class,
                            ['goodnewsgroup_id' => $groupid, 'member_id' => $id]
                        )
                    ) {
                        continue;
                    }
                    $groupmember = $this->modx->newObject(GoodNewsGroupMember::class);
                    $groupmember->set('goodnewsgroup_id', $groupid);
                    $groupmember->set('member_id', $id);

                    if (!$groupmember->save()) {
                        $subscriberUpdated = false;
                        break;
                    }
                }
            }

            // Update GoodNewsCategoryMember entries (preserve existing!)
            if ($subscriberUpdated) {
                foreach ($categories as $categoryid) {
                    if (
                        $this->modx->getObject(
                            GoodNewsCategoryMember::class,
                            ['goodnewscategory_id' => $categoryid,'member_id' => $id]
                        )
                    ) {
                        continue;
                    }
                    $categorymember = $this->modx->newObject(GoodNewsCategoryMember::class);
                    $categorymember->set('goodnewscategory_id', $categoryid);
                    $categorymember->set('member_id', $id);

                    if (!$categorymember->save()) {
                        $subscriberUpdated = false;
                        break;
                    }
                }
            }
        }
        if (!$subscriberUpdated) {
            // @todo: rollback if upd failed
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '-> ' .
                $this->modx->lexicon('goodnews.import_subscribers_log_err_subscr_update') .
                $fields[self::EMAIL]
            );
        } else {
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '-> ' .
                $this->modx->lexicon('goodnews.import_subscribers_log_subscr_updated') .
                $fields[self::EMAIL]
            );
        }
        return $subscriberUpdated;
    }

    /**
     * Save a new user + profile + subscriber meta + group/category member entry.
     *
     * @access private
     * @param array $fields The field values for the new MODX user ($fields[0] = email, $fields[1] = fullname)
     * @param array $groups The GoodNews group IDs for the new MODX user
     * @param array $categories The GoodNews category IDs for the new MODX user
     * @return boolean $subscriberSaved
     */
    private function saveSubscriber($fields, $groups = [], $categories = [])
    {
        if (!$this->validEmail($fields[self::EMAIL])) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '-> ' .
                $this->modx->lexicon('goodnews.import_subscribers_log_err_email_invalid') .
                $fields[self::EMAIL]
            );
            return false;
        }
        $subscriberSaved = false;

        // New modUser
        $subscriber = $this->modx->newObject(modUser::class);
        $password = $subscriber->generatePassword(8);
        $username = $this->generateUsername($fields[self::EMAIL]);
        $subscriber->set('username', $username);
        $subscriber->set('password', $password);
        $subscriber->set('active', 1);
        $subscriber->set('blocked', 0);

        // Add modUserProfile
        $subscriberProfile = $this->modx->newObject(modUserProfile::class);
        $subscriberProfile->set('email', $fields[self::EMAIL]);
        $subscriberProfile->set('fullname', $fields[self::FULLNAME]);
        $subscriber->addOne($subscriberProfile);

        if ($subscriber->save()) {
            $id = $subscriber->get('id'); // preserve id of new user for later use
            // New GoodNewsSubscriberMeta
            $subscriberMeta = $this->modx->newObject(GoodNewsSubscriberMeta::class);
            $subscriberMeta->set('subscriber_id', $id);
            $sid = md5(time() . $id);
            $subscriberMeta->set('sid', $sid);
            $subscriberMeta->set('subscribedon', time());
            $subscriberMeta->set('ip', 'imported'); // Set IP field to string 'imported' for later reference

            if ($subscriberMeta->save()) {
                $subscriberSaved = true;

                foreach ($groups as $groupid) {
                    // New GoodNewsGroupMember entry
                    $groupmember = $this->modx->newObject(GoodNewsGroupMember::class);
                    $groupmember->set('goodnewsgroup_id', $groupid);
                    $groupmember->set('member_id', $id);

                    if (!$groupmember->save()) {
                        $subscriberSaved = false;
                        break;
                    }
                }

                if ($subscriberSaved) {
                    foreach ($categories as $categoryid) {
                        // New GoodNewsCategoryMember entry
                        $categorymember = $this->modx->newObject(GoodNewsCategoryMember::class);
                        $categorymember->set('goodnewscategory_id', $categoryid);
                        $categorymember->set('member_id', $id);

                        if (!$categorymember->save()) {
                            $subscriberSaved = false;
                            break;
                        }
                    }
                }
            }
        }
        if (!$subscriberSaved) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '-> ' .
                $this->modx->lexicon('goodnews.import_subscribers_log_err_subscr_save') .
                $fields[self::EMAIL]
            );
            // Rollback if one of the savings failed!
            $meta = $this->modx->getObject(GoodNewsSubscriberMeta::class, ['subscriber_id' => $id]);
            if ($meta) {
                $meta->remove();
            }
            $this->modx->removeCollection(GoodNewsGroupMember::class, ['member_id' => $id]);
            $this->modx->removeCollection(GoodNewsCategoryMember::class, ['member_id' => $id]);
        } else {
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '-> ' .
                $this->modx->lexicon('goodnews.import_subscribers_log_subscr_imported') .
                $fields[self::EMAIL]
            );
        }
        return $subscriberSaved;
    }

    /**
     * Generate a new unique username based on email address.
     *
     * @access public
     * @return string $newusername
     */
    public function generateUsername($email)
    {
        // Username is generated from userid part of email address
        $parts = explode('@', $email);
        $usernamepart = $parts[0];

        // Add counter (john.doe_1, martin_2, ...) if username already exists
        $counter = 0;
        $newusername = $usernamepart;
        while ($this->usernameExists($newusername)) {
            $newusername = $usernamepart . '_' . $counter;
            $counter++;
        }
        return $newusername;
    }

    /**
     * Check if a user(name) already exists.
     *
     * @access public
     * @param string $username
     * @return boolean
     */
    public function usernameExists($username)
    {
        $usernameExists = false;
        $user = $this->modx->getObject(modUser::class, ['username' => $username]);
        if (is_object($user)) {
            $usernameExists = true;
        }
        return $usernameExists;
    }

    /**
     * Check if an email address already exists.
     *
     * @access public
     * @param string $email
     * @return mixed ID of MODX user or false
     */
    public function emailExists($email)
    {
        $userProfile = $this->modx->getObject(modUserProfile::class, ['email' => $email]);
        if (is_object($userProfile)) {
            return $userProfile->get('internalKey');
        } else {
            return false;
        }
    }

    /**
     * Checks if we have a CSV mime-type.
     *
     * @access public
     * @param string $mimetype The mime-type to check
     * @return boolean $iscsv
     */
    public function csvMimeType($mimetype)
    {
        $csv_mimetypes = [
            'text/csv',
            'text/plain',
            'application/csv',
            'text/comma-separated-values',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel',
            'text/anytext',
            'application/octet-stream',
            'application/txt',
            'application/download',
        ];
        if (in_array($mimetype, $csv_mimetypes)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if we have a valid email address.
     *
     * @access public
     * @param string $email The email address to check
     * @return boolean
     */
    public function validEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
    }
}
