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
 * GoodNews main class
 *
 * @package goodnews
 */

class GoodNews {

    const NAME     = 'GoodNews';
    const VERSION  = '1.5.0';
    const RELEASE  = 'beta3';
    
    const HELP_URL = 'http://www.bitego.com/extras/goodnews/';
    const DEV_NAME = 'bitego (Martin Gartner, Franz Gallei)';
    const DEV_URL  = 'http://www.bitego.com';
    
    const MIN_PHP_VERSION = '5.3.0';

    /** @var modX A reference to the modX object */
    public $modx = null;
    
    /** @var array $config GoodNews config array */
    public $config = array();
    
    /** @var boolean $multiProcessing Is multi processing available? (depends on server settings/features) */
    public $isMultiProcessing = false;
    
    /** @var boolean $imapExtension Is the php IMAP extension available? (required for automatic bounce handling) */
    public $imapExtension = false;
    
    /** @var boolean $pThumbAddOn Is the pThumb MODX Revo Add-On installed? (required for auto-fixing image sizes) */
    public $pThumbAddOn = false;
    
    /** @var string $actualPhpVersion The current PHP version */
    public $actualPhpVersion = null;
    
    /** @var string $requiredPhpVersion The required PHP version */
    public $requiredPhpVersion = null;
    
    /** @var boolean $phpVersionOK Is the php version sufficient? */
    public $phpVersionOK = false;
    
    /** @var boolean $isGoodNewsAdmin Is the current user a GoodNews admin? */
    public $isGoodNewsAdmin = false;
    
    /** @var string $userAvailableContainers Comma separated list of GoodNews containers the actual user has access to */
    public $userAvailableContainers = '';

    /** @var integer $userCurrentContainer The current GoodNews resource container for actual user */
    public $userCurrentContainer = 0;

    /** @var integer $siteStatus The site_status from system settings */
    public $siteStatus = false;

    /** @var integer $workerProcessActive The worker process status from system settings */
    public $workerProcessActive = false;

    /** @var array $setupErrors The setup error stack */
    public $setupErrors = array();
    
    /** @var boolean $debug Debug mode on/off */
    public $debug = false;

    /** @var boolean $legacyMode based on MODX Revo version (true if < 2.3) */
    public $legacyMode = false;
    
    /**
     * Constructor for GoodNews object
     *
     * @param modX &$modx A reference to the modX object
     * @param array $config An array of configuration options
     */
    function __construct(modX &$modx, array $config = array()) {
        $this->modx = &$modx;
 
        $corePath = $this->modx->getOption('goodnews.core_path', $config, $this->modx->getOption('core_path').'components/goodnews/');
        $assetsUrl = $this->modx->getOption('goodnews.assets_url', $config, $this->modx->getOption('assets_url').'components/goodnews/');

        $this->modx->lexicon->load('goodnews:default');

        $this->config = array_merge(array(            
            'corePath'       => $corePath,
            'modelPath'      => $corePath.'model/',
            'processorsPath' => $corePath.'processors/',
            'chunksPath'     => $corePath.'elements/chunks/',
            'docsPath'       => $corePath.'docs/',
            'assetsUrl'      => $assetsUrl,
            'jsUrl'          => $assetsUrl.'js/',
            'cssUrl'         => $assetsUrl.'css/',
            'imgUrl'         => $assetsUrl.'img/',
            'connectorUrl'   => $assetsUrl.'connector.php',
        ), $config);

        // This part is only used in 'mgr' context
        if ($this->modx->context->key == 'mgr') {

            // Determine MODX Revo version and set legacy mode (for usage in ExtJS - deprecated connectors since 2.3)
            $version = $this->modx->getVersionData();

            $fullVersion = $version['full_version'];
            $this->legacyMode         = version_compare($fullVersion, '2.3.0-dev', '>=') ? false : true;
            $this->debug              = $this->modx->getOption('goodnews.debug', null, false) ? true : false;
            $this->isMultiProcessing  = $this->isMultiProcessing();
            $this->imapExtension      = $this->imapExtension();
            $this->pThumbAddOn        = $this->isTransportPackageInstalled('pThumb');
            $this->actualPhpVersion   = phpversion();
            $this->requiredPhpVersion = self::MIN_PHP_VERSION;
            $this->phpVersionOK       = version_compare($this->actualPhpVersion, $this->requiredPhpVersion, '>=') ? true : false;
            
            $contextKey = false;
            $mailingTemplate = false;

            // Only executed if we have a logged in user within MODX manager!
            if ($this->loggedInMgrUser()) {
                
                $this->isGoodNewsAdmin = $this->isGoodNewsAdmin();
                
                // Get all GoodNews mailing containers the user has access to
                $this->userAvailableContainers = $this->getUserAvailableContainers();
                
                if ($this->userAvailableContainers) {
                    $this->_initializeMailingContainer();
                } else {
                    $this->addSetupError('503 Service Unavailable', $this->modx->lexicon('goodnews.error_message_no_container_available'), false);
                }
            }
            
            $this->siteStatus          = $this->modx->getOption('site_status', null, false) ? true : false;
            $this->workerProcessActive = $this->modx->getOption('goodnews.worker_process_active', null, 1) ? true : false;

            $this->config = array_merge(array(
                'setupErrors'             => $this->setupErrors,
                'userCurrentContainer'    => $this->userCurrentContainer,
                'userAvailableContainers' => $this->userAvailableContainers,
                'contextKey'              => $contextKey,
                'mailingTemplate'         => $mailingTemplate,
                'isMultiProcessing'       => $this->isMultiProcessing,
                'imapExtension'           => $this->imapExtension,
                'pThumbAddOn'             => $this->pThumbAddOn,
                'actualPhpVersion'        => $this->actualPhpVersion,   
                'requiredPhpVersion'      => $this->requiredPhpVersion,   
                'phpVersionOK'            => $this->phpVersionOK,   
                'isGoodNewsAdmin'         => $this->isGoodNewsAdmin,
                'siteStatus'              => $this->siteStatus,
                'workerProcessActive'     => $this->workerProcessActive,
                'helpUrl'                 => self::HELP_URL,
                'componentName'           => self::NAME,
                'componentVersion'        => self::VERSION,
                'componentRelease'        => self::RELEASE,
                'developerName'           => self::DEV_NAME,
                'developerUrl'            => self::DEV_URL,
                'debug'                   => $this->debug,
                'legacyMode'              => $this->legacyMode,
            ), $this->config);

        }        
        $this->modx->addPackage('goodnews', $this->config['modelPath']);
    }

    /**
     * Initialize a mailing container for current user.
     *
     * @access private
     * @return void
     */
    private function _initializeMailingContainer() {
        // If request has a container ID - switch to this container
        if (isset($_GET['id'])) {
            $reqid = $_GET['id'];
            if (!empty($reqid) && is_numeric($reqid)) {
                $this->setUserCurrentContainer($reqid);
            }
        }
        $this->userCurrentContainer = $this->getUserCurrentContainer();
        
        // Ensure the current container is set
        if (empty($this->userCurrentContainer) || !$this->isGoodNewsContainer($this->userCurrentContainer)) {
            // If no container is preselected, set default container (= first container based on ID)
            $containers = explode(',', $this->userAvailableContainers);
            $this->userCurrentContainer = reset($containers);
            $this->setUserCurrentContainer($this->userCurrentContainer);
        }
        
        $contextKey = false;
        $mailingTemplate = false;

        $resource = $this->modx->getObject('modResource', $this->userCurrentContainer);
        if ($resource) {
            // Get context key of actual GoodNews container
            $contextKey = $resource->get('context_key');
    
            // Read template setting for child resources (mailings) of actual GoodNews container
            $mailingTemplate = $resource->getProperty('mailingTemplate', 'goodnews');
        }
    }

    /**
     * Collect a list of GoodNews containers the actual user has access to.
     *
     * @access public
     * @return mixed Comma seperated list of container IDs || false.
     */
    public function getUserAvailableContainers() {
                
        if (!$this->modx->user || ($this->modx->user->get('id') < 1)) {
            return false;
        }

        $c = $this->modx->newQuery('modResource');
        $c->where(array(
            'published' => true,
            'deleted'   => false,
            'class_key' => 'GoodNewsResourceContainer'
        ));
        $c->sortby('id', 'ASC');
        $containers = $this->modx->getCollection('modResource', $c);
        
        $containerIDs = false;
        
        foreach ($containers as $container) {
            if ($this->isEditor($container)) {
                if ($containerIDs != '') {
                    $containerIDs .= ',';
                }
                $containerIDs .= $container->get('id');
            }
        }

        return $containerIDs;
    }

    /**
     * Read current container id from user settings (uncached!).
     *
     * @access public
     * @return mixed Current container ID || false.
     */
    public function getUserCurrentContainer() {
        $usersetting = $this->modx->getObject('modUserSetting', array(
            'key' => 'goodnews.current_container',
            'user' => $this->modx->user->get('id')
        ));
        if (!is_object($usersetting)) { return false; }
        
        return $usersetting->get('value') ? $usersetting->get('value') : false;
    }

    /**
     * Write actual container ID to current user settings (create new setting if not exists).
     *
     * @access public
     * @param integer $containerId The container ID (= MODX resource ID)
     * @return boolean
     */
    public function setUserCurrentContainer($containerId) {
        $usersetting = $this->modx->getObject('modUserSetting', array(
            'key' => 'goodnews.current_container',
            'user' => $this->modx->user->get('id')
        ));
        if (!is_object($usersetting)) {
            $usersetting = $this->modx->newObject('modUserSetting');
            $usersetting->set('user', $this->modx->user->get('id'));
            $usersetting->set('key', 'goodnews.current_container');
            $usersetting->set('xtype', 'textfield');
            $usersetting->set('namespace', 'goodnews');
        }
        $usersetting->set('value', $containerId);
        if ($usersetting->save()) {
            // clear user settings cache (MODx 2.1.x)
            $this->modx->cacheManager->refresh(array('user_settings' => array()));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if a resource container is a published GoodNews container.
     * (only this containers will be checked for mailings/newsletters to be sent)
     *
     * @access public
     * @param integer $id The id of the resource container
     * @return boolean
     */    
    public function isGoodNewsContainer($id) {
        $goncontainer = false;
        
        $c = $this->modx->newQuery('modResource');
        $c->where(array(
            'id' => $id,
            'published' => true,
            'deleted'   => false,
            'class_key' => 'GoodNewsResourceContainer'
        ));
        $containers = $this->modx->getCollection('modResource', $c);
        
        if (count($containers)) {
            $goncontainer = true;
        }
        return $goncontainer;
    }

    /**
     * Cecks if server configuration is capable of doing multiple processes.
     * (php exec required)
     *
     * @access public
     * @return boolean
     */    
    public function isMultiProcessing() {
        $enabled = true;
        
        $d = ini_get('disable_functions');
        $s = ini_get('suhosin.executor.func.blacklist');
        if ("$d$s") {
            $array = preg_split('/,\s*/', "$d,$s");
            if (in_array('exec', $array)) {
                $enabled = false;
            }
        }
        return $enabled;
    }

    /**
     * Cecks if php IMAP extension is enabled in server configuration.
     *
     * @access public
     * @return boolean
     */    
    public function imapExtension() {
        return function_exists('imap_open');
    }
    
    /**
     * Cecks if a MODX transport package is installed.
     *
     * @access private
     * @param string $name Name of transport package
     * @return boolean
     */    
    private function isTransportPackageInstalled($tpname) {
        $installed = false;
        $package = $this->modx->getObject('transport.modTransportPackage', array(
            'package_name' => $tpname,
        ));
        if (is_object($package)) { $installed = true; }
        return $installed;

    }
    
    /**
     * Checks if the current logged in user has permissions to administrate GoodNews system.
     *
     * @access public
     * @return boolean
     */
    public function isGoodNewsAdmin() {
        $gonadmin = false;
        
        if (!$this->modx->user || ($this->modx->user->get('id') < 1)) {
            $gonadmin = false;
        }
        $groups = explode(',', $this->modx->getOption('goodnews.admin_groups', null, 'Administrator'));
        $groups = array_map('trim', $groups);

        // Check if group member
        if ($this->modx->user->isMember($groups)) {
            $gonadmin = true;

        // Additionally check for sudo user
        } else {
            $version = $this->modx->getVersionData();
            if (version_compare($version['full_version'], '2.2.1-pl') == 1) {
                $gonadmin = (bool)$this->modx->user->get('sudo');
            }
        }
        return $gonadmin;
    }

    /**
     * Check if current user is entitled to access a specific mailing container.
     *
     * @access public
     * @param $container A GoodNews resource container object
     * @return boolean false || true
     */
    public function isEditor($container) {
        
        if (!$container) { return false; }
        
        $iseditor = false;
        
        // Read GoodNews editor groups from container properties
        $groups = explode(',', $container->getProperty('editorGroups', 'goodnews', 'Administrator'));
        $groups = array_map('trim', $groups);
             
        // Check if user is a specific MODX group member
        if ($this->modx->user->isMember($groups)) {
            $iseditor = true;

        // Additionally check for sudo user
        } else {
            $version = $this->modx->getVersionData();
            if (version_compare($version['full_version'], '2.2.1-pl') == 1) {
                $iseditor = (bool)$this->modx->user->get('sudo');
            }
        }
        return $iseditor;
    }

    /**
     * Check if we have a logged in manager user.
     *
     * @access public
     * @return mixed user ID || false
     */
    public function loggedInMgrUser() {
        $loggedInMgrUser = false;
        
        $user = &$this->modx->user;
        if ($user->hasSessionContext('mgr')) {
            $loggedInMgrUser = $user->get('id');
        }
        return $loggedInMgrUser;
    }

    /**
     * Adds a setup error to error stack + redirects to the error page if execution should be stopped.
     *
     * @access public
     * @param string $statuscode HTTP/1.1 Status Code definitions
     * @param string $description Verbose status/error description
     * @return void
     */
    public function addSetupError($statuscode, $description, $stopexecution = false) {
        if (empty($statuscode) || empty($description)) { return; }
        $this->setupErrors[] = array(
            'statuscode'    => $statuscode,
            'description'   => $description,
            'stopexecution' => $stopexecution
        );        
        if ($stopexecution) {
            ob_get_level() && @ob_end_flush();
            @include($this->config['modelPath'].'goodnews/error/stopexecution.include.php');
            exit();
        }
    }

    /**
     * Get the full setup error stack.
     *
     * @access public
     * @return array $setupErrors The setup error stack.
     */
    public function getSetupErrors() {
        return $this->setupErrors;
    }

    /**
     * Gets a Chunk and caches it + fall back to file-based templates for easier debugging.
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function getChunk($name, $properties = array()) {
    
        $chunk = null;
        
        if (!isset($this->chunks[$name])) {
            $chunk = $this->_getTplChunk($name);
            if (empty($chunk)) {
                $chunk = $this->modx->getObject('modChunk', array('name' => $name));
                if ($chunk == false) return false;
            }
            $this->chunks[$name] = $chunk->getContent();
        } else {
            $o = $this->chunks[$name];
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setContent($o);
        }
        $chunk->setCacheable(false);
        
        return $chunk->process($properties);
    }

    /**
     * Returns a modChunk object from a template file.
     *
     * @access private
     * @param string $name The name of the Chunk. Will parse to name.$postfix
     * @param string $postfix The default postfix to search for chunks at.
     * @return modChunk/boolean Returns the modChunk object if found, otherwise false.
     */
    private function _getTplChunk($name, $postfix = '.chunk.tpl') {
    
        $chunk = false;
        
        $f = $this->config['chunksPath'].strtolower($name).$postfix;
        if (file_exists($f)) {
            $o = file_get_contents($f);
            $chunk = $this->modx->newObject('modChunk');
            $chunk->set('name',$name);
            $chunk->setContent($o);
        }
        return $chunk;
    }

    /**
     * parseTpl function.
     * (from getResources snippet)
     *
     * @author Jason Coward
     * @copyright Copyright 2010-2012, Jason Coward
     *
     * @access public
     * @param mixed $tpl
     * @param mixed $properties (default: null)
     * @return string|void $output
     */
    public function parseTpl($tpl, $properties = null) {
        static $_tplCache;
        $_validTypes = array(
            '@CHUNK'
            ,'@FILE'
            ,'@INLINE'
        );
        $output = '';
        $prefix = $this->modx->getOption('tplPrefix', $properties, '');
        if (!empty($tpl)) {
            $bound = array(
                'type' => '@CHUNK'
                ,'value' => $tpl
            );
            if (strpos($tpl, '@') === 0) {
                $endPos = strpos($tpl, ' ');
                if ($endPos > 2 && $endPos < 10) {
                    $tt = substr($tpl, 0, $endPos);
                    if (in_array($tt, $_validTypes)) {
                        $bound['type'] = $tt;
                        $bound['value'] = substr($tpl, $endPos + 1);
                    }
                }
            }
            if (is_array($bound) && isset($bound['type']) && isset($bound['value'])) {
                $output = $this->parseTplElement($_tplCache, $_validTypes, $bound['type'], $bound['value'], $properties);
            }
        }
        if (empty($output) && $output !== '0') { // print_r the object fields that were returned if no tpl is provided
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setCacheable(false);
            $output = $chunk->process(array("{$prefix}output" => print_r($properties, true)), "<pre>[[+{$prefix}output]]</pre>");
        }
        return $output;
    }

    /**
     * parseTplElement function.
     * (from getResources snippet)
     *
     * @author Jason Coward
     * @copyright Copyright 2010-2012, Jason Coward
     *
     * @access public
     * @param mixed &$_cache
     * @param mixed $_validTypes
     * @param mixed $type
     * @param mixed $source
     * @param mixed $properties (default: null)
     * @return void
     */
    function parseTplElement(&$_cache, $_validTypes, $type, $source, $properties = null) {
        $output = null;
        if (!is_string($type) || !in_array($type, $_validTypes)) $type = $this->modx->getOption('tplType', $properties, '@CHUNK');
        $content = false;
        switch ($type) {
            case '@FILE':
                $path = $this->modx->getOption('tplPath', $properties, $this->modx->getOption('assets_path', $properties, MODX_ASSETS_PATH) . 'elements/chunks/');
                $key = $path . $source;
                if (!isset($_cache['@FILE'])) $_cache['@FILE'] = array();
                if (!array_key_exists($key, $_cache['@FILE'])) {
                    if (file_exists($key)) {
                        $content = file_get_contents($key);
                    }
                    $_cache['@FILE'][$key] = $content;
                } else {
                    $content = $_cache['@FILE'][$key];
                }
                if (!empty($content) && $content !== '0') {
                    $chunk = $this->modx->newObject('modChunk', array('name' => $key));
                    $chunk->setCacheable(false);
                    $output = $chunk->process($properties, $content);
                }
                break;
            case '@INLINE':
                $uniqid = uniqid();
                $chunk = $this->modx->newObject('modChunk', array('name' => "{$type}-{$uniqid}"));
                $chunk->setCacheable(false);
                $output = $chunk->process($properties, $source);
                break;
            case '@CHUNK':
            default:
                $chunk = null;
                if (!isset($_cache['@CHUNK'])) $_cache['@CHUNK'] = array();
                if (!array_key_exists($source, $_cache['@CHUNK'])) {
                    if ($chunk = $this->modx->getObject('modChunk', array('name' => $source))) {
                        $_cache['@CHUNK'][$source] = $chunk->toArray('', true);
                    } else {
                        $_cache['@CHUNK'][$source] = false;
                    }
                } elseif (is_array($_cache['@CHUNK'][$source])) {
                    $chunk = $this->modx->newObject('modChunk');
                    $chunk->fromArray($_cache['@CHUNK'][$source], '', true, true, true);
                }
                if (is_object($chunk)) {
                    $chunk->setCacheable(false);
                    $output = $chunk->process($properties);
                }
                break;
        }
        return $output;
    }
}
