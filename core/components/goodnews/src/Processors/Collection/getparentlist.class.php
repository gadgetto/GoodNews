<?php
/**
 * GoodNews
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
 * ParentFilter get list processor
 *
 * @package goodnews
 * @subpackage processors
 */

class ParentFilterGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modResource';
    public $languageTopics = array('resource','goodnews:default');
    public $defaultSortField = 'pagetitle';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        
        $resourceColumns = array(
            'id',
            'pagetitle',
            'context_key',
        );
        $c->select($this->modx->getSelectColumns('modResource', 'modResource', '', $resourceColumns));
        
        $parentIds = explode(',', $this->getProperty('parentIds', 0));

        $c->where(array(
            'id:IN' => $parentIds,
        ));
        
        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        
        $resourceArray = $object->toArray('', false, true, true);
        $resourceArray['container'] = $resourceArray['pagetitle'].' - '.$this->modx->lexicon('context').': '.$resourceArray['context_key'];
        $resourceArray['container'] = htmlentities($resourceArray['container'], ENT_COMPAT, $charset);

        return $resourceArray;
    }

    public function beforeIteration(array $list) {
        // additional option value for displaying all resources
        if ($this->getProperty('addAllOption', false)) {
            $list[] = array(
                'id' => 'all',
                'container' => $this->modx->lexicon('goodnews.mailing_rc_resources_all'),
            );
        }
        return $list;
    }
}
return 'ParentFilterGetListProcessor';
