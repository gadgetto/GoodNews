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
use MODX\Revolution\modTemplate;
use MODX\Revolution\modTemplateVar;
use MODX\Revolution\modTemplateVarTemplate;
use xPDO\Transport\xPDOTransport;

/**
 * Resolve Template Variables (sample currently not in use)
 *
 * @package goodnews
 * @subpackage build
 */

$templates = [
    'templateName',
];

if ($object->xpdo) {
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $tv = $modx->getObject(modTemplateVar::class, [
                'name' => 'tvName',
            ]);

            if ($tv) {
                foreach ($templates as $templateName) {
                    $template = $modx->getObject(modTemplate::class, ['templatename' => $templateName]);
                    if ($template) {
                        $templateVarTemplate = $modx->getObject(modTemplateVarTemplate::class, [
                            'templateid' => $template->get('id'),
                            'tmplvarid' => $tv->get('id'),
                        ]);
                        if (!$templateVarTemplate) {
                            $templateVarTemplate = $modx->newObject(modTemplateVarTemplate::class);
                            $templateVarTemplate->set('templateid', $template->get('id'));
                            $templateVarTemplate->set('tmplvarid', $tv->get('id'));
                            $templateVarTemplate->save();
                        }
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

unset($tv, $templates, $templateName, $template, $templateVarTemplate);
return true;
