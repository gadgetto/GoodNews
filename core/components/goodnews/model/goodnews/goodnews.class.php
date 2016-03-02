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

    const VERSION = '1.4.1';
    const RELEASE = 'pl';
    
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
    
    /** @var string $assignedContainers Comma separated list of GoodNews containers the actual user has access to */
    public $assignedContainers = '';

    /** @var integer $currentContainer The current GoodNews resource container for actual user */
    public $currentContainer = 0;

    /** @var integer $siteStatus The site_status from system settings */
    public $siteStatus = false;

    /** @var integer $cronTriggerStatus The worker process status from system settings */
    public $cronTriggerStatus = false;

    /** @var boolean $setupError */
    public $setupError = false;
    
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
            $this->isMultiProcessing  = $this->_isMultiProcessing();
            $this->imapExtension      = $this->_imapExtension();
            $this->pThumbAddOn        = $this->_isTransportPackageInstalled('pThumb');
            $this->actualPhpVersion   = phpversion();
            $this->requiredPhpVersion = self::MIN_PHP_VERSION;
            $this->phpVersionOK       = version_compare($this->actualPhpVersion, $this->requiredPhpVersion, '>=') ? true : false;
            $this->isGoodNewsAdmin    = $this->_isGoodNewsAdmin();
            $this->assignedContainers = $this->_assignedContainers();
            
            if (!$this->assignedContainers) {
                $this->setupError = true;
            }
            // If request has a container id - switch to this container
            if (isset($_GET['id'])) {
                $reqid = $_GET['id'];
                if (!empty($reqid) && is_numeric($reqid)) {
                    $this->setUserCurrentContainer($reqid);
                }
            }
            
            $this->currentContainer = $this->_getUserCurrentContainer();
            // Check if current container is set
            if (empty($this->currentContainer) || !$this->isGoodNewsContainer($this->currentContainer)) {
                // If no container is preselected, set default container (=first container based on id)
                $containers = explode(',', $this->assignedContainers);
                $this->currentContainer = reset($containers);
                $this->setUserCurrentContainer($this->currentContainer);
            }
            
            $this->siteStatus        = $this->modx->getOption('site_status', null, false) ? true : false;
            $this->cronTriggerStatus = $this->modx->getOption('goodnews.worker_process_active', null, 1) ? true : false;
            $contextKey = false;
            $mailingTemplate = false;
            
            // Get context key of actual GoodNews container
            $resource = $modx->getObject('modResource', $this->currentContainer);
            $contextKey = $resource->get('context_key');
    
            // Read template setting for child resources (mailings) of actual GoodNews container
            $mailingTemplate = $resource->getProperty('mailingTemplate', 'goodnews');

            $this->config = array_merge(array(
                'setupError'         => $this->setupError,
                'currentContainer'   => $this->currentContainer,
                'assignedContainers' => $this->assignedContainers,
                'contextKey'         => $contextKey,
                'mailingTemplate'    => $mailingTemplate,
                'isMultiProcessing'  => $this->isMultiProcessing,
                'imapExtension'      => $this->imapExtension,
                'pThumbAddOn'        => $this->pThumbAddOn,
                'actualPhpVersion'   => $this->actualPhpVersion,   
                'requiredPhpVersion' => $this->requiredPhpVersion,   
                'phpVersionOK'       => $this->phpVersionOK,   
                'isGoodNewsAdmin'    => $this->isGoodNewsAdmin,
                'siteStatus'         => $this->siteStatus,
                'cronTriggerStatus'  => $this->cronTriggerStatus,
                'helpUrl'            => 'http://www.bitego.com/extras/goodnews/',
                'componentName'      => 'GoodNews',
                'componentVersion'   => self::VERSION,
                'componentRelease'   => self::RELEASE,
                'developerName'      => 'bitego (Martin Gartner, Franz Gallei)',
                'developerUrl'       => 'http://www.bitego.com',
                'debug'              => $this->debug,
                'legacyMode'         => $this->legacyMode,
            ), $this->config);

        }
        
        $this->modx->addPackage('goodnews', $this->config['modelPath']);
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
     * @access private
     * @return boolean
     */    
    private function _isMultiProcessing() {
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
     * @access private
     * @return boolean
     */    
    private function _imapExtension() {
        return function_exists('imap_open');
    }
    
    /**
     * Cecks if a MODX transport package is installed.
     *
     * @access private
     * @param string $name Name of transport package
     * @return boolean
     */    
    private function _isTransportPackageInstalled($tpname) {
        $installed = false;
        $package = $this->modx->getObject('transport.modTransportPackage', array(
            'package_name' => $tpname,
        ));
        if (is_object($package)) { $installed = true; }
        return $installed;

    }
    
    /**
     * Checks if the current logged in user has permissions to administrate GoodNews system
     *
     * @access private
     * @return boolean
     */
    private function _isGoodNewsAdmin() {
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
     * Collect a list of GoodNews containers the actual user has access to.
     *
     * @access private
     * @return string/boolean Returns comma seperated list if containers found, otherwise false.
     */
    private function _assignedContainers() {
                
        if (!$this->modx->user || ($this->modx->user->get('id') < 1)) {
            return false;
        }

        $c = $this->modx->newQuery('modResource');
        $c->where(array(
            //'id' => $id,
            'published' => true,
            'deleted'   => false,
            'class_key' => 'GoodNewsResourceContainer'
        ));
        $c->sortby('id', 'ASC');
        $containers = $this->modx->getCollection('modResource', $c);
        
        $containerIDs = '';
        foreach ($containers as $container) {
            if ($this->isEditor($container)) {
                if ($containerIDs != '') {
                    $containerIDs .= ',';
                }
                $containerIDs .= $container->get('id');
            }
        }
        
        if (!empty($containerIDs)) {
            return $containerIDs;
        } else {
            return false;
        }
    }

    /**
     * Check if current user is entitled to access a specific mailing container
     *
     * @access public
     * @param $gonrc A GoodNews container object
     * @return boolean false || true
     */
    public function isEditor($gonrc) {
        $goneditor = false;
        
        // read GoodNews editor groups from container properties
        $groups = explode(',', $gonrc->getProperty('editorGroups', 'goodnews', 'Administrator'));
        $groups = array_map('trim', $groups);
             
        // Check if group member
        if ($this->modx->user->isMember($groups)) {
            $goneditor = true;

        // Additionally check for sudo user
        } else {
            $version = $this->modx->getVersionData();
            if (version_compare($version['full_version'], '2.2.1-pl') == 1) {
                $goneditor = (bool)$this->modx->user->get('sudo');
            }
        }
        return $goneditor;
    }

    /**
     * hasAccess function.
     * 
     * @access public
     * @return void
     */
    public function hasAccess() {
        
    }
    
    /**
     * Read current container id from user settings (uncached!).
     *
     * @access private
     * @return integer Current container id or false if not found.
     */
    private function _getUserCurrentContainer() {
        $usersetting = $this->modx->getObject('modUserSetting', array(
            'key' => 'goodnews.current_container',
            'user' => $this->modx->user->get('id')
        ));
        if (!is_object($usersetting)) { return false; }
        
        $current_container = $usersetting->get('value');
        if (!empty($current_container)) {
            return $current_container;
        } else {
            return false;
        }
    }

    /**
     * Write actual container id to current user settings (create new setting if not exists).
     *
     * @access public
     * @param $id
     * @return boolean
     */
    public function setUserCurrentContainer($id) {
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
        $usersetting->set('value', $id);
        if($usersetting->save()) {
            // clear user settings cache (MODx 2.1.x)
            $this->modx->cacheManager->refresh(array('user_settings' => array()));
            return true;
        } else {
            return false;
        }
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
