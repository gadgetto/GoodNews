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
 * GoodNewsImportSubscribers class handles batch import of users into MODX users database
 * and make them GoodNews subscribers.
 *
 * @package goodnews
 */

class GoodNewsImportSubscribers {

    const EMAIL    = 0;
    const FULLNAME = 1;

    /** @var modX $modx A reference to the modX object */
    public $modx = null;

    /** @var array $config An array of config values */
    public $config = array();
    
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
    public $header = array();
    
    /** @var int $batchSize Number of users to be imported in one batch */
    public $batchSize;


    /**
     * Constructor for GoodNewsImportSubscribers object.
     *
     * @access public
     * @param modX &$modx A reference to the modX object
     */
    public function __construct(modX &$modx, array $config = array()) {
        $this->modx = &$modx;
        $this->modx->lexicon->load('goodnews:default');
        ini_set('auto_detect_line_endings', true);
        $this->config = array_merge(array(
            'use_multibyte' => (boolean)$this->modx->getOption('use_multibyte', null, false),
            'encoding'      => $this->modx->getOption('modx_charset', null, 'UTF-8'),
        ), $config);
    }

    /**
     * Destructor for GoodNewsImportSubscribers object.
     * 
     * @access public
     * @return void
     */
    public function __destruct() {
        $this->_closeFile();
        ini_set('auto_detect_line_endings', false);
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
    public function init($update, $filePath, $delimiter = ',', $enclosure = '"', $escape = '\\', $lineLength = 1024, $hasHeader = false) {

        $this->update      = $update;
        if ($this->_openFile($filePath) == false) {
            return false;
        }
        $this->delimiter   = $delimiter;
        $this->enclosure   = $enclosure; 
        $this->escape      = $escape; 
        $this->lineLength  = $lineLength; 
        $this->hasHeader   = $hasHeader; 
        if ($this->hasHeader) {
            $this->_getHeader();
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
    private function _openFile($filePath) { 
        $this->fileHandle = @fopen($filePath, 'r');
        return $this->fileHandle;
    } 

    /**
     * Close a file.
     * 
     * @access private
     * @return void
     */
    private function _closeFile() { 
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
    private function _getHeader() {
        $this->header = fgetcsv($this->fileHandle, $this->lineLength, $this->delimiter, $this->enclosure, $this->escape); 
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
    private function _getImportUsers() {
            
        $importUsers = array(); 
        
        if ($this->batchSize > 0) {
            $lineCount = 0; 
        } else {
            $lineCount = -1; // loop limit is ignored 
        }
        while ($lineCount < $this->batchSize && ($row = fgetcsv($this->fileHandle, $this->lineLength, $this->delimiter, $this->enclosure, $this->escape)) !== false) { 

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
    public function importUsers($batchSize = 0, $gonGroups = array(), $gonCategories = array()){
        $this->batchSize = $batchSize;
        
        // At least 1 group is required (both needs to be arrays)
        if (empty($gonGroups) || !is_array($gonGroups) || !is_array($gonCategories)) {
            return false;
        }
        
        $importUsers = $this->_getImportUsers();
        $importCount = 0;
        foreach ($importUsers as $importUser) {
            
            if ($this->emailExists($importUser[self::EMAIL]) || $this->usernameExists($importUser[self::EMAIL])) {
                // If update mode is enabled
                if ($this->update) {
                    if ($this->_updateSubscriber($importUser, $gonGroups, $gonCategories)) {
                        $importCount++;
                    }
                } else {
            		$this->modx->log(modX::LOG_LEVEL_WARN, '-> '.$this->modx->lexicon('goodnews.import_subscribers_log_err_subscr_ae').$importUser[self::EMAIL]);
                }         
            } else {
                if ($this->_saveSubscriber($importUser, $gonGroups, $gonCategories)) {
                    $importCount++;
                }
            }
        }
        return $importCount;
    }

    /**
     * Update a user + profile + subscriber meta + group member entry.
     * 
     * @access private
     * @param array $fields The field values for the new MODX user ($fields[0] = email, $fields[1] = fullname)
     * @param array $groups The GoodNews group IDs for the new MODX user
     * @param array $categories The GoodNews category IDs for the new MODX user
     * @return boolean $subscriberUpdated
     */
    private function _updateSubscriber($fields, $groups = array(), $categories = array()) {
        
        $subscriberUpdated = false;
        
        // Select a modUserProfile based on email
        $subscriberProfile = $this->modx->getObject('modUserProfile', array('email' => $fields[self::EMAIL]));

        // Update the subscribers Full name if provided
        if (!empty($fields[self::FULLNAME])) {
            $subscriberProfile->set('fullname', $fields[self::FULLNAME]);
        }
        
		if ($subscriberProfile->save()) {
		    $subscriberUpdated = true;
    		$id = $subscriberProfile->get('internalKey'); // preserve id of updated user for later use

            // Update GoodNewsGroupMember entries (preserve existing!)            
		    foreach ($groups as $groupid) {
    		    if ($this->modx->getObject('GoodNewsGroupMember', array('goodnewsgroup_id' => $groupid,'member_id' => $id))) {
        		    continue;
    		    }
                $groupmember = $this->modx->newObject('GoodNewsGroupMember');
                $groupmember->set('goodnewsgroup_id', $groupid);
                $groupmember->set('member_id', $id);
        		
        		if (!$groupmember->save()) {
            		$subscriberUpdated = false;
            		break;
        		}
		    }

            // Update GoodNewsCategoryMember entries (preserve existing!)            
            if ($subscriberUpdated) {
    		    foreach ($categories as $categoryid) {
        		    if ($this->modx->getObject('GoodNewsCategoryMember', array('goodnewscategory_id' => $categoryid,'member_id' => $id))) {
            		    continue;
        		    }
        		    
                    $categorymember = $this->modx->newObject('GoodNewsCategoryMember');
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
    		$this->modx->log(modX::LOG_LEVEL_WARN, '-> '.$this->modx->lexicon('goodnews.import_subscribers_log_err_subscr_update').$fields[self::EMAIL]);
		} else {
    		$this->modx->log(modX::LOG_LEVEL_INFO, '-> '.$this->modx->lexicon('goodnews.import_subscribers_log_subscr_updated').$fields[self::EMAIL]);
		}
		return $subscriberUpdated;
    }

    /**
     * Save a new user + profile + subscriber meta + group member entry.
     * 
     * @access private
     * @param array $fields The field values for the new MODX user ($fields[0] = email, $fields[1] = fullname)
     * @param array $groups The GoodNews group IDs for the new MODX user
     * @param array $categories The GoodNews category IDs for the new MODX user
     * @return boolean $subscriberSaved
     */
    private function _saveSubscriber($fields, $groups = array(), $categories = array()) {
        if (!$this->validEmail($fields[self::EMAIL])) {
    		$this->modx->log(modX::LOG_LEVEL_WARN, '-> '.$this->modx->lexicon('goodnews.import_subscribers_log_err_email_invalid').$fields[self::EMAIL]);
            return false;
        }
        
        $subscriberSaved = false;
        
        // New modUser
        $subscriber = $this->modx->newObject('modUser');
        $password = $subscriber->generatePassword(8);
        $subscriber->set('username', $fields[self::EMAIL]); // username = email
		$subscriber->set('password', $password);
		$subscriber->set('active', 1);
		$subscriber->set('blocked', 0);
		
		// Add modUserProfile
        $subscriberProfile = $this->modx->newObject('modUserProfile');
        $subscriberProfile->set('email', $fields[self::EMAIL]);
        $subscriberProfile->set('fullname', $fields[self::FULLNAME]);
        $subscriber->addOne($subscriberProfile);
        
		if ($subscriber->save()) {
    		$id = $subscriber->get('id'); // preserve id of new user for later use
    		// New GoodNewsSubscriberMeta
            $subscriberMeta = $this->modx->newObject('GoodNewsSubscriberMeta');
            $subscriberMeta->set('subscriber_id', $id);
            $sid = md5(time().$id);
            $subscriberMeta->set('sid', $sid);
            $createdon = strftime('%Y-%m-%d %H:%M:%S');
            $subscriberMeta->set('createdon', $createdon);
            $subscriberMeta->set('ip', 'imported'); // Set IP field to string 'imported' for later reference
            
    		if ($subscriberMeta->save()) {
    		    
    		    $subscriberSaved = true;
    		    
    		    foreach ($groups as $groupid) {
            		// New GoodNewsGroupMember entry
                    $groupmember = $this->modx->newObject('GoodNewsGroupMember');
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
                        $categorymember = $this->modx->newObject('GoodNewsCategoryMember');
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
    		$this->modx->log(modX::LOG_LEVEL_WARN, '-> '.$this->modx->lexicon('goodnews.import_subscribers_log_err_subscr_save').$fields[self::EMAIL]);
    		// Rollback if one of the savings failed!
            $meta = $this->modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $id));
            if ($meta) { $meta->remove(); }
            $this->modx->removeCollection('GoodNewsGroupMember', array('member_id' => $id));
            $this->modx->removeCollection('GoodNewsCategoryMember', array('member_id' => $id));
		} else {
    		$this->modx->log(modX::LOG_LEVEL_INFO, '-> '.$this->modx->lexicon('goodnews.import_subscribers_log_subscr_imported').$fields[self::EMAIL]);
		}
		return $subscriberSaved;
    }
    
    /**
     * Check if a username already exists.
     * 
     * @access public
     * @param string $username
     * @return mixed ID of MODX user or false
     */
    public function usernameExists($username) {
		$user = $this->modx->getObject('modUser', array('username' => $username));
		if (is_object($user)) {
    		return $user->get('id');
		} else {
    		return false;
		}
    }
    
    /**
     * Check if an email address already exists.
     * 
     * @access public
     * @param string $email
     * @return mixed ID of MODX user or false
     */
    public function emailExists($email) {
		$userProfile = $this->modx->getObject('modUserProfile', array('email' => $email));
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
    public function csvMimeType($mimetype) {
        $csv_mimetypes = array(
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
        );
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
    public function validEmail($email) {

        // Validate length and @
        $pattern = "^[^@]{1,64}\@[^\@]{1,255}$";
        $condition = $this->config['use_multibyte'] ? @mb_ereg($pattern, $email) : @ereg($pattern, $email);
        if (!$condition) {
            return false;
        }

        $email_array = explode("@", $email);
        $local_array = explode(".", $email_array[0]);
        for ($i = 0; $i < sizeof($local_array); $i++) {
            $pattern = "^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$";
            $condition = $this->config['use_multibyte'] ? @mb_ereg($pattern, $local_array[$i]) : @ereg($pattern, $local_array[$i]);
            if (!$condition) {
                return false;
            }
        }
        // Validate domain name
        $pattern = "^\[?[0-9\.]+\]?$";
        $condition = $this->config['use_multibyte'] ? @mb_ereg($pattern, $email_array[1]) : @ereg($pattern, $email_array[1]);
        if (!$condition) {
            $domain_array = explode(".", $email_array[1]);
            if (sizeof($domain_array) < 2) {
                return false;
            }
            for ($i = 0; $i < sizeof($domain_array); $i++) {
                $pattern = "^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$";
                $condition = $this->config['use_multibyte'] ? @mb_ereg($pattern, $domain_array[$i]) : @ereg($pattern, $domain_array[$i]);
                if (!$condition) {
                    return false;
                }
            }
        }
        return true;
    }
}
