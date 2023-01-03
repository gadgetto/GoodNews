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

namespace Bitego\GoodNews\Model;

use xPDO\xPDO;
use MODX\Revolution\modResource;
use Bitego\GoodNews\Model\GoodNewsResourceMailing;

/**
 * Class GoodNewsResourceMailing
 *
 * @property \GoodNewsMailingMeta $MailingMeta
 * @package Bitego\GoodNews\Model
 */
class GoodNewsResourceMailing extends modResource
{
    public $allowListingInClassKeyDropdown = false;
    public $showInContextMenu = false;

    /**
     * Override modResource::__construct to ensure specific fields are forced to be set.
     *
     * @param xPDO $xpdo
     */
    public function __construct(xPDO &$xpdo)
    {
        parent::__construct($xpdo);
        $this->set('class_key', GoodNewsResourceMailing::class);
        $this->set('show_in_tree', false);
        $this->set('searchable', false);
    }

    /**
     * Get the controller path for our resource type.
     *
     * {@inheritDoc}
     *
     * @static
     * @param xPDO $modx
     * @return string
     */
    public static function getControllerPath(xPDO &$modx)
    {
        return $modx->getOption(
            'goodnews.core_path',
            null,
            $modx->getOption('core_path') . 'components/goodnews/'
        ) . 'controllers/mailing/';
    }

    /**
     * Override modResource::process to set custom placeholders for the Resource when rendering it in front-end.
     *
     * {@inheritDoc}
     *
     * @return string
     */
    public function process()
    {
        $this->xpdo->lexicon->load('goodnews:frontend');
        $settings = $this->getContainerSettings();
        foreach ($settings as $key => $value) {
            $this->xpdo->setPlaceholder($key, $value);
        }
        $this->_content = parent::process();
        return $this->_content;
    }

    /**
     * Get an array of settings from the container (read from modResource properties field -> MODx 2.2+).
     *
     * @return array $settings
     */
    public function getContainerSettings()
    {
        $container = $this->getOne('ResourceContainer');
        if ($container) {
            $settings = $container->getContainerSettings();
        }
        return is_array($settings) ? $settings : array();
    }
}
