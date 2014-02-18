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
 * Plugin to handle GoodNews events
 * Evens: OnManagerPageInit   Fired in the manager request handler, before the manager page response is loaded and after defining request action.
 *        OnUserRemove        Fires after a User is removed.
 *
 * @package goodnews
 */

switch ($modx->event->name) {

    case 'OnManagerPageInit':

        // Add css file to manager-page header based on Revo version (e.g. for custom resource type tree icon)
        $version = $modx->getVersionData();
        $fullVersion = $version['full_version'];
        if (version_compare($fullVersion, '2.3.0-dev', '>=')) {
            // We are on Revo >= 2.3.0
            $cssFile = $modx->getOption('goodnews.assets_url', null, $modx->getOption('assets_url').'components/goodnews/').'css/res23.css';
        } else {
            // We are on Revo < 2.3.0
            $cssFile = $modx->getOption('goodnews.assets_url', null, $modx->getOption('assets_url').'components/goodnews/').'css/res.css';
        }
        $modx->regClientCSS($cssFile);
        break;
        
    case 'OnUserRemove':
    
        // If a MODx user is deleted, also remove related GoodNews objects
        $corePath = $modx->getOption('goodnews.core_path', null, $modx->getOption('core_path').'components/goodnews/');
        $result = $modx->addPackage('goodnews', $corePath.'model/');
        if (!$result) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[GoodNews] GoodNews plugin event OnUserRemove: The GoodNews package could not be added.');
        }
        $id = $user->get('id');
        // Remove subscriber meta data
        $meta = $modx->getObject('GoodNewsSubscriberMeta', array('subscriber_id' => $id));
        if ($meta) { $meta->remove(); }
        // Remove groups and categories data
        $modx->removeCollection('GoodNewsCategoryMember', array('member_id' => $id));
        $modx->removeCollection('GoodNewsGroupMember', array('member_id' => $id));
        break;
}
return;