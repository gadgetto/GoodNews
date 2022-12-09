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
 * Grabs a list of templates filtered by a category.
 *
 * @package goodnews
 * @subpackage processors
 */

class MailingTemplateGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modTemplate';
    public $languageTopics = array('template','category');
    public $defaultSortField = 'templatename';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        // filter by category
        $catid = $this->getProperty('catid', 0);
        if (!empty($catid)) {
            $c->where(array('category' => $catid));   
        }
        return $c;
    }

    public function beforeIteration(array $list) {
        $empty = array(
            'id' => 0,
            'templatename' => $this->modx->lexicon('template_empty'),
            'description' => '',
            'editor_type' => 0,
            'icon' => '',
            'template_type' => 0,
            'content' => '',
            'locked' => false,
        );
        $list[] = $empty;
        return $list;
    }
}
return 'MailingTemplateGetListProcessor';
