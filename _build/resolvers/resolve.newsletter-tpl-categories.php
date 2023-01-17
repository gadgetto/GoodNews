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

use MODX\Revolution\modX;
use MODX\Revolution\modCategory;
use MODX\Revolution\modTemplate;
use xPDO\Transport\xPDOTransport;

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
    function getCategoryID(&$modx, $name)
    {
        $categoryObj = $modx->getObject(modCategory::class, ['category' => $name]);
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
            $modx->log(
                modX::LOG_LEVEL_INFO,
                'Newsletter templates resolver - create categories and assign templates to categories...'
            );

            $packageName  = 'GoodNews';
            $categoryName = 'Newsletter Templates';
            $newsletterTemplates = [
                'sample.GoodNewsNewsletterTemplate1',
                'sample.GoodNewsNewsletterTemplate2',
            ];

            // Check if category already exists
            $category = $modx->getObject(modCategory::class, ['category' => $categoryName]);
            if ($category) {
                $modx->log(modX::LOG_LEVEL_INFO, '-> category ' . $categoryName . ' already exists.');
            } else {
                // Create newsletter templates category
                $category = $modx->newObject(modCategory::class);
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
                    $template = $modx->getObject(modTemplate::class, ['templatename' => $templateName]);
                    if ($template) {
                        // Assign category
                        $template->set('category', getCategoryID($modx, $categoryName));
                        if ($template->save()) {
                            $modx->log(
                                modX::LOG_LEVEL_INFO,
                                '-> assigned template ' . $templateName . ' to category ' . $categoryName
                            );
                        } else {
                            $modx->log(
                                modX::LOG_LEVEL_ERROR,
                                '-> could not assign template ' . $templateName . ' to category ' . $categoryName
                            );
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
