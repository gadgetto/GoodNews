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
use MODX\Revolution\modChunk;

/**
 * GoodNewsResource main class
 *
 * @package goodnews
 */

class GoodNewsResource
{
    /** @var modX $modx A reference to the modX instance */
    public $modx = null;

    /** @var array $config An array of configuration properties */
    public $config = [];

    /** @var array $chunks An array of cached chunks used for faster processing */
    public $chunks = [];

    /**
     * Create an instance of GoodNewsResource.
     *
     * @param modX $modx A reference to the modX object
     * @param array $config A configuration array
     */
    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx = &$modx;

        $corePath = $this->modx->getOption(
            'goodnews.core_path',
            $config,
            $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/goodnews/'
        );
        $assetsPath = $this->modx->getOption(
            'goodnews.assets_path',
            $config,
            $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/goodnews/'
        );
        $assetsUrl = $this->modx->getOption(
            'goodnews.assets_url',
            $config,
            $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/goodnews/'
        );

        $this->modx->lexicon->load('goodnews:resource');

        $this->config = array_merge([
            'corePath'       => $corePath,
            'srcPath'        => $corePath . 'src/',
            'modelPath'      => $corePath . 'src/Model/',
            'processorsPath' => $corePath . 'src/Processors/',
            'chunksPath'     => $corePath . 'elements/chunks/',
            'includesPath'   => $corePath . 'includes/',
            'docsPath'       => $corePath . 'docs/',
            'assetsPath'     => $assetsPath,
            'assetsUrl'      => $assetsUrl,
            'jsUrl'          => $assetsUrl . 'js/',
            'cssUrl'         => $assetsUrl . 'css/',
            'imgUrl'         => $assetsUrl . 'img/',
            'connectorUrl'   => $assetsUrl . 'connector_res.php',
        ], $config);
    }

    /**
     * Gets a Chunk and caches it; also falls back to file-based templates
     * for easier debugging.
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function getChunk($name, array $properties = [])
    {
        $chunk = null;
        if (!isset($this->chunks[$name])) {
            $chunk = $this->modx->getObject(modChunk::class, ['name' => $name], true);
            if (empty($chunk)) {
                $chunk = $this->getTplChunk($name, $this->config['chunkSuffix']);
                if ($chunk == false) {
                    return false;
                }
            }
            $this->chunks[$name] = $chunk->getContent();
        } else {
            $o = $this->chunks[$name];
            $chunk = $this->modx->newObject(modChunk::class);
            $chunk->setContent($o);
        }
        $chunk->setCacheable(false);
        return $chunk->process($properties);
    }

    /**
     * Returns a modChunk object from a template file.
     *
     * @access private
     * @param string $name The name of the Chunk. Will parse to name.chunk.tpl by default.
     * @param string $suffix The suffix to add to the chunk filename.
     * @return modChunk/boolean Returns the modChunk object if found, otherwise
     * false.
     */
    private function getTplChunk($name, $suffix = '.chunk.tpl')
    {
        $chunk = false;
        $f = $this->config['chunksPath'] . strtolower($name) . $suffix;
        if (file_exists($f)) {
            $o = file_get_contents($f);
            /** @var modChunk $chunk */
            $chunk = $this->modx->newObject(modChunk::class);
            $chunk->set('name', $name);
            $chunk->setContent($o);
        }
        return $chunk;
    }
}
