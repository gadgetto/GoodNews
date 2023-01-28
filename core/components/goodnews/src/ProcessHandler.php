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
use Bitego\GoodNews\Model\GoodNewsProcess;

/**
 * ProcessHandler class handles worker processes.
 *
 * @package goodnews
 * @compatibility Linux only
 */

class ProcessHandler
{
    public const PROCESS_TIMEOUT = 90;

    /** @var modX $modx A reference to the modX object */
    public $modx = null;

    /** @var string $pid GoodNews worker process id */
    private $pid = '';

    /** @var string $command The nohup command for the GoodNews worker process */
    private $command = '';

    /** @var string $currentTime Current epoch time string */
    private $currentTime = '';

    /** @var string $lockDir The path to the goodnews/locks/ directory in MODX cache folder */
    public $lockDir = '';

    /** @var boolean $debug Debug mode on/off */
    public $debug = false;

    /**
     * Constructor for ProcessHandler object.
     *
     * @access public
     * @param modX &$modx A reference to the modX object
     */
    public function __construct(modX &$modx)
    {
        $this->modx = &$modx;
        $this->currentTime = time();
        $this->debug = $this->modx->getOption('goodnews.debug', null, false) ? true : false;
        $this->lockDir = $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'cache/goodnews/locks/';
    }

    /**
     * Setter for $pid.
     *
     * @access public
     * @param string $pid The process id
     */
    public function setPid($processid)
    {
        $this->pid = $processid;
    }

    /**
     * Getter for $pid.
     *
     * @access public
     * @return string The process id
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Setter for $command.
     *
     * @access public
     * @param string The command for nohup
     */
    public function setCommand($cmd)
    {
        $this->command = $cmd;
    }

    /**
     * Getter for $command.
     *
     * @access public
     * @return string The command for nohup
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Getter for lockDir
     *
     * @access public
     * @return string The lockfile directory path
     */
    public function getLockDir()
    {
        return $this->lockDir;
    }

    /**
     * Calls private method runCommand to start a new process.
     *
     * @access public
     * @return boolean
     */
    public function start()
    {
        if (empty($this->command)) {
            return false;
        }
        if ($this->runCommand()) {
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
    public function stop()
    {
        if (empty($this->pid)) {
            return false;
        }
        $cm = 'kill ' . $this->pid;
        exec($cm);
        // workaround: process needs time to stop (otherwise status cant be read properly)
        sleep(1);
        // If the process is stopped (doesn't exist any longer) delete status entry and return true
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
    public function status()
    {
        if (empty($this->pid)) {
            return false;
        }
        $cm = 'ps -p ' . $this->pid;
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
    private function runCommand()
    {
        if (empty($this->command)) {
            return false;
        }
        $cm = 'nohup ' . $this->command . ' > /dev/null 2>&1 & echo $!';
        exec($cm, $output);
        $this->pid = (int)$output[0];
        if (!empty($this->pid)) {
            $this->newProcessStatus();
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
    public function cleanupProcessStatuses()
    {
        $pr = $this->collectProcessStatuses();
        foreach ($pr as $key => $value) {
            $this->setPid($value->get('pid'));

            // If process isn't running
            if (!$this->status()) {
                $this->deleteProcessStatus();
            // Else check if process runtime is too high (probably the process hangs!)
            } else {
                if ((($this->currentTime) - self::PROCESS_TIMEOUT) > $value->get('starttime')) {
                    $this->modx->log(
                        modX::LOG_LEVEL_INFO,
                        '[GoodNews] ProcessHandler::cleanupProcessStatuses - Worker process: ' .
                        $this->pid .
                        ' - timed out!'
                    );
                    if (!$this->stop()) {
                        $this->modx->log(
                            modX::LOG_LEVEL_ERROR,
                            '[GoodNews] ProcessHandler::cleanupProcessStatuses - timed out worker process: ' .
                            $this->pid .
                            ' - could not be stopped!'
                        );
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
    private function newProcessStatus()
    {
        if (empty($this->pid)) {
            return false;
        }
        $process = $this->modx->newObject(GoodNewsProcess::class);
        if (!$process) {
            return false;
        }
        $process->set('pid', $this->pid);
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
    public function deleteProcessStatus()
    {
        if (empty($this->pid)) {
            return false;
        }
        $process = $this->modx->getObject(GoodNewsProcess::class, ['pid' => $this->pid]);
        if (!$process) {
            return false;
        }

        if ($process->remove() == false) {
            return false;
        } else {
            $this->removeLockFile();
            return true;
        }
    }

    /**
     * Counts the temporary entries from the process status table.
     *
     * @access public
     * @return int Count of processes
     */
    public function countProcessStatuses()
    {
        $count = $this->modx->getCount(GoodNewsProcess::class);
        return $count;
    }

    /**
     * Lookup for a temporary entry in the process status table.
     *
     * @access public
     * @return boolean
     */
    public function existsProcessStatus()
    {
        if (empty($this->pid)) {
            return false;
        }
        $process = $this->modx->getObject(GoodNewsProcess::class, ['pid' => $this->pid]);
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
    public function getProcessStartTime()
    {
        if (empty($this->pid)) {
            return false;
        }
        $process = $this->modx->getObject(GoodNewsProcess::class, ['pid' => $this->pid]);
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
    private function collectProcessStatuses()
    {
        $processes = $this->modx->getCollection(GoodNewsProcess::class);
        //$processes = $this->modx->getIterator('GoodNewsProcess');
        return $processes;
    }

    /**
     * Creates the directory for the temporary lock files.
     *
     * @access public
     * @return boolean (true -> if directory already exists or is created successfully)
     */
    public function createLockFileDir()
    {
        $dir = false;

        if (is_dir($this->lockDir)) {
            $dir = true;
        } else {
            $dir = mkdir($this->lockDir, 0777, true);
            if ($dir) {
                @chmod($this->lockDir, 0777);
                if ($this->debug) {
                    $this->modx->log(
                        modX::LOG_LEVEL_INFO,
                        '[GoodNews] [pid: ' .
                        getmypid() .
                        '] ProcessHandler::createLockFileDir - lockfile directory created.'
                    );
                }
            } else {
                if ($this->debug) {
                    $this->modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '[GoodNews] [pid: ' .
                        getmypid() .
                        '] ProcessHandler::createLockFileDir - could not create lockfile directory.'
                    );
                }
            }
        }
        return $dir;
    }

    /**
     * Creates a temporary lock file for a specific mailing.
     *
     * @access public
     * @param integer $mailingId
     * @return boolean (true -> if file already exists or is created successfully)
     */
    public function createLockFile($mailingId)
    {
        $tempfile = $this->lockDir . $mailingId . '.temp';
        $lockfilepattern = $this->lockDir . $mailingId . '.*';
        $file = false;

        $ary = glob($lockfilepattern);
        if (!empty($ary)) {
            $file = true;
        } else {
            $file = file_put_contents($tempfile, $mailingId, LOCK_EX);
            if ($file) {
                @chmod($tempfile, 0777);
                if ($this->debug) {
                    $this->modx->log(
                        modX::LOG_LEVEL_INFO,
                        '[GoodNews] [pid: ' .
                        getmypid() .
                        '] ProcessHandler::createLockFile - Mailing meta [id: ' .
                        $mailingId .
                        '] - lockfile created.'
                    );
                }
            } else {
                if ($this->debug) {
                    $this->modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '[GoodNews] [pid: ' .
                        getmypid() .
                        '] ProcessHandler::createLockFile - Mailing meta [id: ' .
                        $mailingId .
                        '] - could not create lockfile.'
                    );
                }
            }
        }
        return $file;
    }

    /**
     * Set lock on db entry.
     *
     * @access public
     * @param integer $mailingId
     * @return boolean
     */
    public function lock($mailingId)
    {
        $tempfile = $this->lockDir . $mailingId . '.temp';
        $lockfile = $this->lockDir . $mailingId . '.' . getmypid();

        while (!file_exists($lockfile)) {
            while (!file_exists($tempfile)) {
                if ($this->debug) {
                    $this->modx->log(
                        modX::LOG_LEVEL_INFO,
                        '[GoodNews] [pid: ' .
                        getmypid() .
                        '] ProcessHandler::lock - waiting (mailing currently locked).'
                    );
                }
                // 20 to 100 millisec
                usleep(rand(20000, 100000));
            }
            // Atomic method to use the file for locking purposes (@ is required here!)
            $lock = @rename($tempfile, $lockfile);
            if ($lock) {
                if ($this->debug) {
                    $this->modx->log(
                        modX::LOG_LEVEL_INFO,
                        '[GoodNews] [pid: ' .
                        getmypid() .
                        '] ProcessHandler::lock - Mailing [id: ' .
                        $mailingId .
                        '] - locked.'
                    );
                }
            } else {
                if ($this->debug) {
                    $this->modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '[GoodNews] [pid: ' .
                        getmypid() .
                        '] ProcessHandler::lock - Mailing meta [id: ' .
                        $mailingId .
                        '] - could not be locked (file operation failed).'
                    );
                }
            }
            // Catch race conditions!
            if (file_exists($lockfile)) {
                return $lock;
            } else {
                if ($this->debug) {
                    $this->modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '[GoodNews] [pid: ' .
                        getmypid() .
                        '] ProcessHandler::lock - Mailing meta [id: ' .
                        $mailingId .
                        '] - race condition!'
                    );
                }
            }
        }
    }

    /**
     * Remove lock from db entry.
     *
     * @access public
     * @param integer $mailingId
     * @return boolean
     */
    public function unlock($mailingId)
    {
        $tempfile = $this->lockDir . $mailingId . '.temp';
        $lockfile = $this->lockDir . $mailingId . '.' . getmypid();
        // Atomic method to use the file for locking purposes
        $unlock = @rename($lockfile, $tempfile);
        if ($unlock) {
            if ($this->debug) {
                $this->modx->log(
                    modX::LOG_LEVEL_INFO,
                    '[GoodNews] [pid: ' .
                    getmypid() .
                    '] ProcessHandler::unlock - Mailing meta [id: ' .
                    $mailingId .
                    '] - unlocked.'
                );
            }
        } else {
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[GoodNews] [pid: ' .
                getmypid() .
                '] ProcessHandler::unlock - Mailing meta [id: ' .
                $mailingId .
                '] - could not be unlocked (file operation failed).'
            );
        }
        return $unlock;
    }

    /**
     * Removes a lock file for a specific process.
     *
     * @access private
     * @return void
     */
    private function removeLockFile()
    {
        $lockfilepattern = $this->lockDir . '*.' . $this->pid;
        $files = glob($lockfilepattern);
        if (is_array($files) && count($files) > 0) {
            foreach ($files as $filename) {
                @unlink($filename);
            }
        }
    }

    /**
     * Removes a temporary lock file for a specific mailing.
     *
     * @access public
     * @param integer $mailingId
     * @return void
     */
    public function removeTempLockFile($mailingId)
    {
        $tempfile = $this->lockDir . $mailingId . '.temp';
        @unlink($tempfile);
    }
}
