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

namespace Bitego\GoodNews\Processors\Subscribers;

use MODX\Revolution\modX;
use MODX\Revolution\Processors\Model\GetListProcessor;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserProfile;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;
use Bitego\GoodNews\Model\GoodNewsSubscriberMeta;
use Bitego\GoodNews\Model\GoodNewsGroupMember;
use Bitego\GoodNews\Model\GoodNewsCategoryMember;

/**
 * Subscribers export processor.
 *
 * @package goodnews
 * @subpackage processors
 */

class Export extends GetListProcessor
{
    public $classKey = modUser::class;
    public $languageTopics = array('user', 'goodnews:default');
    public $defaultSortField = 'Profile.email';

    /** @var string $currentTime Current epoch time string */
    private $currentTime = '';

    /** @var string $exportDir The path to the goodnews/export/ directory in MODX cache folder */
    public $exportDir;

    /** @var resource $fileHandle A valid file pointer to a file successfully opened */
    public $fileHandle = false;

    /**
     * {@inheritDoc}
     *
     * @return mixed $initialized
     */
    public function initialize()
    {
        $initialized = parent::initialize();

        set_time_limit(0);

        $this->setDefaultProperties([
            'query'           => '',
            'groupfilter'     => '',
            'categoryfilter'  => '',
            'testdummyfilter' => '',
            'activefilter'    => '',
            'delimiter'       => ',',
            'enclosure'       => '"',
        ]);
        // Overwrite "limit" property -> needs to be 0!
        $this->setProperty('limit', 0);

        $this->currentTime = time();
        $this->exportDir = $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'cache/goodnews/tmp/';

        if (!$this->createExportDir()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Export::initialize - export directory missing!');
        }

        return $initialized;
    }

    /**
     * {@inheritDoc}
     *
     * @return query $c
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query           = $this->getProperty('query', '');
        $groupfilter     = $this->getProperty('groupfilter', '');
        $categoryfilter  = $this->getProperty('categoryfilter', '');
        $testdummyfilter = $this->getProperty('testdummyfilter', '');
        $activefilter    = $this->getProperty('activefilter', '');

        $c->leftJoin(modUserProfile::class, 'Profile');
        $c->leftJoin(GoodNewsSubscriberMeta::class, 'SubscriberMeta', 'modUser.id = SubscriberMeta.subscriber_id');
        $c->leftJoin(GoodNewsGroupMember::class, 'GroupMember', 'modUser.id = GroupMember.member_id');
        $c->leftJoin(GoodNewsCategoryMember::class, 'CategoryMember', 'modUser.id = CategoryMember.member_id');

        $query = $this->getProperty('query', '');
        if (!empty($query)) {
            $c->where(['modUser.username:LIKE' => '%' . $query . '%']);
            $c->orCondition(['Profile.fullname:LIKE' => '%' . $query . '%']);
            $c->orCondition(['Profile.email:LIKE' => '%' . $query . '%']);
            $c->orCondition(['SubscriberMeta.ip:LIKE' => '%' . $query . '%']);
        }

        $groupfilter = $this->getProperty('groupfilter', '');
        if (!empty($groupfilter)) {
            if ($groupfilter == 'nogroup') {
                $c->where(['GroupMember.goodnewsgroup_id' => null]);
            } else {
                $c->where(['GroupMember.goodnewsgroup_id' => $groupfilter]);
            }
        }

        $categoryfilter = $this->getProperty('categoryfilter', '');
        if (!empty($categoryfilter)) {
            if ($categoryfilter == 'nocategory') {
                $c->where(['CategoryMember.goodnewscategory_id' => null]);
            } else {
                $c->where(['CategoryMember.goodnewscategory_id' => $categoryfilter]);
            }
        }

        $testdummyfilter = $this->getProperty('testdummyfilter', '');
        if (!empty($testdummyfilter)) {
            if ($testdummyfilter == 'isdummy') {
                $c->where(['SubscriberMeta.testdummy' => '1']);
            } else {
                $c->where(['SubscriberMeta.testdummy' => '0']);
            }
        }

        $activefilter = $this->getProperty('activefilter', '');
        if (!empty($activefilter)) {
            if ($activefilter == 'active') {
                $c->where(['modUser.active' => '1']);
            } else {
                $c->where(['modUser.active' => '0']);
            }
        }

        return $c;
    }

    /**
     * {@inheritDoc}
     *
     * @return query $c
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->select($this->modx->getSelectColumns(modUser::class, 'modUser'));
        $c->select($this->modx->getSelectColumns(modUserProfile::class, 'Profile', '', [
            'id',
            'internalKey'
        ], true));
        $c->select($this->modx->getSelectColumns(GoodNewsSubscriberMeta::class, 'SubscriberMeta', '', [
            'testdummy',
            'subscribedon',
            'activatedon',
            'ip',
            'ip_activated',
            'soft_bounces',
            'hard_bounces'
        ]));
        return $c;
    }

    /**
     * {@inheritDoc}
     *
     * @return array $userArray
     */
    public function prepareRow(xPDOObject $object)
    {
        $userArray = $object->toArray();

        $managerDateFormat = $this->modx->getOption('manager_date_format', null, 'Y-m-d');
        $managerTimeFormat = $this->modx->getOption('manager_time_format', null, 'H:i');
        $dateTimeFormat = $managerDateFormat . ' ' . $managerTimeFormat;

        // @todo: remove this quickhack and get the counts in prepareQueryBeforeCount
        if (!empty($userArray['id'])) {
            // Check if user has GoodNews meta data
            $c = $this->modx->newQuery('GoodNewsSubscriberMeta');
            $c->where([
                'subscriber_id' => $userArray['id'],
            ]);
        }

        if (empty($userArray['blockeduntil'])) {
            $userArray['blockeduntil'] = '';
        } else {
            // Format timestamp into manager date/time format
            $userArray['blockeduntil'] = date($dateTimeFormat, $userArray['blockeduntil']);
        }

        if (empty($userArray['blockedafter'])) {
            $userArray['blockedafter'] = '';
        } else {
            // Format timestamp into manager date/time format
            $userArray['blockedafter'] = date($dateTimeFormat, $userArray['blockedafter']);
        }

        if (empty($userArray['lastlogin'])) {
            $userArray['lastlogin'] = '';
        } else {
            // Format timestamp into manager date/time format
            $userArray['lastlogin'] = date($dateTimeFormat, $userArray['lastlogin']);
        }

        if (empty($userArray['thislogin'])) {
            $userArray['thislogin'] = '';
        } else {
            // Format timestamp into manager date/time format
            $userArray['thislogin'] = date($dateTimeFormat, $userArray['thislogin']);
        }

        if (empty($userArray['dob']) && $userArray['dob'] != 0) {
            $userArray['dob'] = '';
        } else {
            // Format timestamp into manager date/time format
            // @todo: fix 1970-01-01 = timestamp 0 problem!
            $userArray['dob'] = date($managerDateFormat, $userArray['dob']);
        }

        if (empty($userArray['extended']) || $userArray['extended'] == '[]') {
            $userArray['extended'] = '';
        }

        if (empty($userArray['subscribedon'])) {
            $userArray['subscribedon'] = '';
        } else {
            // Format timestamp into manager date/time format
            $userArray['subscribedon'] = date($dateTimeFormat, $userArray['subscribedon']);
        }

        if (empty($userArray['activatedon'])) {
            $userArray['activatedon'] = '';
        } else {
            // Format timestamp into manager date/time format
            $userArray['activatedon'] = date($dateTimeFormat, $userArray['activatedon']);
        }

        if ($userArray['ip'] == null || $userArray['ip'] == '0') {
            $userArray['ip'] = '';
        } elseif ($userArray['ip'] == 'unknown') {
            $userArray['ip'] = $this->modx->lexicon('goodnews.subscriber_ip_unknown');
        } elseif ($userArray['ip'] == 'imported') {
            $userArray['ip'] = $this->modx->lexicon('goodnews.subscriber_ip_imported');
        } elseif ($userArray['ip'] == 'manually') {
            $userArray['ip'] = $this->modx->lexicon('goodnews.subscriber_ip_manually');
        }

        if ($userArray['ip_activated'] == null || $userArray['ip_activated'] == '0') {
            $userArray['ip_activated'] = '';
        } elseif ($userArray['ip_activated'] == 'unknown') {
            $userArray['ip_activated'] = $this->modx->lexicon('goodnews.subscriber_ip_unknown');
        } elseif ($userArray['ip_activated'] == 'imported') {
            $userArray['ip_activated'] = $this->modx->lexicon('goodnews.subscriber_ip_imported');
        } elseif ($userArray['ip_activated'] == 'manually') {
            $userArray['ip_activated'] = $this->modx->lexicon('goodnews.subscriber_ip_manually');
        }

        // We don't need these fields in our array
        unset(
            $userArray['password'],
            $userArray['cachepwd'],
            $userArray['salt'],
            $userArray['hash_class'],
            $userArray['session_stale'],
            $userArray['sessionid']
        );

        return $userArray;
    }

    /**
     * {@inheritDoc}
     *
     * @return string The extended JSON-encoded string.
     */
    public function outputArray(array $users, $count = false)
    {
        if ($count === false) {
            $count = count($users);
        }

        if ($count > 0) {
            $delimiter = $this->getProperty('delimiter', ',');
            $enclosure = $this->getProperty('enclosure', '"');

            // Security by obscurity!
            $fileName = md5($this->currentTime) . '.php';
            $filePath = $this->exportDir . $fileName;

            $this->openFile($filePath);

            // CSV header line
            $headers = array_keys(reset($users));
            $this->fwriteCSV($headers, $delimiter, $enclosure);

            // CSV data lines
            foreach ($users as $key => $user) {
                $this->fwriteCSV($user, $delimiter, $enclosure);
            }
            $this->closeFile();

            $message = $count . $this->modx->lexicon('goodnews.export_subscribers_msg_successfull');
            return '{"success":true,"message":"' . $message . '","object":{"file":"' . $fileName . '","total":"' . $count . '"},"data":[]}';
        } else {
            $message = $this->modx->lexicon('goodnews.export_subscribers_msg_ns_subscribers');
            return '{"success":true,"message":"' . $message . '","object":{"total":"0"},"data":[]}';
        }
    }

    /**
     * Writes a line to CSV file.
     *
     * @access private
     * @param array $user One line of data fields
     * @param string $delimiter The field delimiter
     * @param string $enclosure The field enclosure
     * @return mixed $lineLen || false
     */
    private function fwriteCSV($user, $delimiter = ',', $enclosure = '"')
    {
        if (!is_array($user)) {
            return false;
        }
        $data = array_values($user);
        $lineLen = fputcsv($this->fileHandle, $data, $delimiter, $enclosure);
        return $lineLen;
    }

    /**
     * Open a file for writing.
     *
     * @access private
     * @param string $filePath
     * @return mixed file handle || false
     */
    private function openFile($filePath)
    {
        $this->fileHandle = @fopen($filePath, 'w');
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
     * Creates the directory for the temporary export files.
     *
     * @access public
     * @return boolean (true -> if directory already exists or is created successfully)
     */
    public function createExportDir()
    {
        $dir = false;
        if (is_dir($this->exportDir)) {
            $dir = true;
        } else {
            $dir = mkdir($this->exportDir, 0777, true);
            if ($dir) {
                @chmod($this->exportDir, 0777);
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] Export::createExportDir - could not create export directory.');
            }
        }
        return $dir;
    }
}
