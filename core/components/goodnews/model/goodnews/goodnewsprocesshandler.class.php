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
 * GoodNewsProcessHandler class handles worker processes
 *
 * @package goodnews
 * @compatibility Linux only
 */

class GoodNewsProcessHandler {

    const PROCESS_TIMEOUT = 90;

    /** @var modX $modx A reference to the modX object */
    public $modx;
 
    /** @var string $_pid GoodNews worker process id */
    private $_pid = '';
    
    /** @var string $_command The nohup command for the GoodNews worker process */
    private $_command = '';

    /** @var string $currentTime Current epoch time string */
    private $_currentTime = '';

    /** @var string $lockDir The path to the goodnews/locks/ directory in MODX cache folder */
    public $lockDir;
    
    /**
     * Constructor for GoodNewsProcessHandler object.
     *
     * @access public
     * @param modX &$modx A reference to the modX object
     */
    public function __construct(modX &$modx) {
        $this->modx         = &$modx;
        $this->_currentTime = time();
        $this->lockDir      = $this->modx->getOption('core_path', null, MODX_CORE_PATH).'cache/goodnews/locks/';
    }

    /**
     * Setter for $_pid.
     *
     * @access public
     * @param string $_pid The process id
     */    
    public function setPid($processid) {
        $this->_pid = $processid;
    }

    /**
     * Getter for $_pid.
     *
     * @access public
     * @return string The process id
     */    
    public function getPid() {
        return $this->_pid;
    }

    /**
     * Setter for $_command.
     *
     * @access public
     * @param string The command for nohup
     */    
    public function setCommand($cmd) {
        $this->_command = $cmd;
    }

    /**
     * Getter for $_command.
     *
     * @access public
     * @return string The command for nohup
     */    
    public function getCommand() {
        return $this->_command;
    }

    /**
     * Calls private method _runCommand to start a new process.
     *
     * @access public
     * @return boolean
     */    
    public function start() {
        if (empty($this->_command)) { return false; }
        if ($this->_runCommand()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Stop a GoodNews process and delete its temporary status entry from the process status table.
     *
     * @access public
     * @return boolean
     */    
    public function stop() {
        if (empty($this->_pid)) { return false; }
        $cm = 'kill '.$this->_pid;
        exec($cm);
        sleep(1); // workaround: process needs time to stop (otherwise status cant be read properly)
        // If the process is stopped (doesnt exist any longer) delete status entry and return true
        if ($this->status() == false) {
            $this->deleteProcessStatus();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if a GoodNews process is still running.
     *
     * @access public
     * @return boolean
     */    
    public function status() {
        if (empty($this->_pid)) { return false; }
        $cm = 'ps -p '.$this->_pid;
        exec($cm, $output);
        if (!isset($output[1])) { 
            return false;
        } else {
            return true;
        }
    }

    /**
     * Execute a command in background without having the script waiting for the result.
     * Also writes a temporary entry in the process status table.
     *
     * @access private
     * @return boolean
     */    
    private function _runCommand() {
        if (empty($this->_command)) { return false; }
        $cm = 'nohup '.$this->_command.' > /dev/null 2>&1 & echo $!';
        exec($cm, $output);
        $this->_pid = (int)$output[0];
        if (isset($this->_pid)) {
            $this->_newProcessStatus();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Cleanup running processes and sync with process status table.
     *
     * @access public
     * @return int Count of real running processes based on previous cleanup
     */    
    public function cleanupProcessStatuses() {
        $pr = $this->_collectProcessStatuses();
        foreach ($pr as $key => $value) {
            $this->setPid($value->get('pid'));
            
            // If process isn't running
            if (!$this->status()) {
                $this->deleteProcessStatus();
                
            // Else check if process runtime is too high (probably the process hangs!)
            } else {
                if ((($this->_currentTime) - self::PROCESS_TIMEOUT) > $value->get('starttime')) {
                    $this->modx->log(modX::LOG_LEVEL_INFO, '[GoodNews] GoodNewsProcessHandler::cleanupProcessStatuses - Worker process with pid: '.$this->_pid.' - timed out!');
                    if(!$this->stop()) {
                        $this->modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] GoodNewsProcessHandler::cleanupProcessStatuses - timed out worker process with pid: '.$this->_pid.' - could not be stopped!');
                    }
                }
            }
        }
        return $this->countProcessStatuses();
    }

    /**
     * Create a new temporary entry in the process status table.
     *
     * @access private
     * @return boolean
     */    
    private function _newProcessStatus() {
        if (empty($this->_pid)) { return false; }
        $process = $this->modx->newObject('GoodNewsProcess');
        if (!$process) { return false; }

        $process->set('pid', $this->_pid);
        $process->set('starttime', time());
        if ($process->save() == false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Delete a temporary entry from the process status table.
     *
     * @access public
     * @return boolean
     */    
    public function deleteProcessStatus() {
        if (empty($this->_pid)) { return false; }
        $process = $this->modx->getObject('GoodNewsProcess', array('pid' => $this->_pid));
        if (!$process) { return false; }
        
        if ($process->remove() == false) {
            return false;
        } else {
            $this->_removeLockFile();
            return true;
        }
    }

    /**
     * Removes a temporary lock file if process status is removed.
     * 
     * @todo: move all lockfile related methods to a separate lockfilehandler class!
     *
     * @access private
     * @return void
     */
    private function _removeLockFile() {
        $lockfilepattern = $this->lockDir.'*.'.$this->_pid;
        foreach (glob($lockfilepattern) as $filename) {
            @unlink($filename);
        }
        
    }
    
    /**
     * Counts the temporary entries from the process status table.
     *
     * @access public
     * @return int Count of processes
     */    
    public function countProcessStatuses() {
        $count = $this->modx->getCount('GoodNewsProcess');
        return $count;
    }

    /**
     * Lookup for a temporary entry in the process status table.
     *
     * @access public
     * @return boolean
     */    
    public function existsProcessStatus() {
        if (empty($this->_pid)) { return false; }
        $process = $this->modx->getObject('GoodNewsProcess', array('pid' => $this->_pid));
        if (!$process) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Read startTime from a temporary entry in the process status table.
     *
     * @access public
     * @return string startTime || false
     */    
    public function getProcessStartTime() {
        if (empty($this->_pid)) { return false; }
        $process = $this->modx->getObject('GoodNewsProcess', array('pid'=>$this->_pid));
        if (!$process) {
            return false;
        } else {
            return $process->get('starttime');
        }
    }
    
    /**
     * Read all temporary entries from the process status table.
     *
     * @access private
     * @return array Collection of all processes
     */    
    private function _collectProcessStatuses() {
        $processes = $this->modx->getCollection('GoodNewsProcess');
        //$processes = $this->modx->getIterator('GoodNewsProcess');
        return $processes;
    }

}
