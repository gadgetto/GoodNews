<?php
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

/**
 * Resolver for creating a special newsletter-templates category
 * and adding all preinstalled newsletter-templates to this category.
 *
 * @package goodnews
 * @subpackage build
 */

/**
 * Helper function to get ID of a category.
 *
 * @param mixed &$modx
 * @param mixed $name
 * @return int category ID
 */
if (!function_exists('getCategoryID')) {
    function getCategoryID(&$modx, $name) {
        $categoryObj = $modx->getObject('modCategory', array('category' => $name));
        if (!empty($categoryObj)) {
            return $categoryObj->get('id');
        } else {
            return 0;
        }
    }
}


if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            
            $modx->log(modX::LOG_LEVEL_INFO, 'Newsletter templates resolver - create categories and assign templates to categories...');
            
            $packageName  = 'GoodNews';
            $categoryName = 'Newsletter Templates';
            
            $newsletterTemplates = array(
                'sample.GoodNewsNewsletterTemplate1',
                'sample.GoodNewsNewsletterTemplate2',
            );
            
            
            // Check if category already exists
            $category = $modx->getObject('modCategory', array('category' => $categoryName));
            if ($category) {
                $modx->log(modX::LOG_LEVEL_INFO, '-> category ' . $categoryName . ' already exists.');
            } else {
                // Create newsletter templates category
                $category = $modx->newObject('modCategory');
                $category->set('category', $categoryName);
                $category->set('parent', getCategoryID($modx, $packageName));
                if ($category->save()) {
                    $modx->log(modX::LOG_LEVEL_INFO, '-> created category: ' . $categoryName);
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, '-> could not create category: ' . $categoryName);
                    break;
                }
            }
                
            // Add newsletter templates to this category
            if (!empty($newsletterTemplates)) {
                foreach ($newsletterTemplates as $templateName) {
                    
                    // Check if template exists
                    $template = $modx->getObject('modTemplate', array('templatename' => $templateName));
                    if ($template) {
                        // Assign category
                        $template->set('category', getCategoryID($modx, $categoryName));
                        if ($template->save()) {
                            $modx->log(modX::LOG_LEVEL_INFO, '-> assigned template ' . $templateName . ' to category ' . $categoryName);
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, '-> could not assign template ' . $templateName . ' to category ' . $categoryName);
                        }
                    }
                }
            }
            break;
            
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($packageName, $categoryName, $newsletterTemplates, $category, $templateName, $template);
return true;
