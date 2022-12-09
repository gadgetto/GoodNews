<?php
namespace GoodNews\Model;

/**
 * GoodNews
 *
 * Copyright 2022 by bitego <office@bitego.com>
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

use xPDO\xPDO;

/**
 * Class GoodNewsResourceMailing
 *
 * @property \GoodNewsMailingMeta $MailingMeta
 * @package GoodNews\Model
 */
class GoodNewsResourceMailing extends \modResource
{
    public $allowListingInClassKeyDropdown = false;
    public $showInContextMenu = false;
    
    /**
     * Override modResource::__construct to ensure specific fields are forced to be set.
     *
     * @param xPDO $xpdo
     */
    function __construct(xPDO &$xpdo) {
        parent::__construct($xpdo);
        $this->set('class_key', 'GoodNewsResourceMailing');
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
    public static function getControllerPath(xPDO &$modx) {
        return $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path') . 'components/goodnews/') . 'controllers/res/mailing/';
    }
    
    /**
     * Override modResource::process to set custom placeholders for the Resource when rendering it in front-end.
     *
     * {@inheritDoc}
     *
     * @return string
     */
    public function process() {
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
    public function getContainerSettings() {
        $container = $this->getOne('ResourceContainer');
        if ($container) {
            $settings = $container->getContainerSettings();
        }
        return is_array($settings) ? $settings : array();
    }
}
