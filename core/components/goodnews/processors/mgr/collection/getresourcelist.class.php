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
 * Resource collection get list processor
 *
 * @param string $parentIds The ids of the parent resource document as comma seperated list. Defaults to 0.
 * @param integer $start (optional) The record to start at. Defaults to 0.
 * @param integer $limit (optional) The number of records to limit to. Defaults to 10.
 * @param string $sort (optional) The column to sort by. Defaults to createdon.
 * @param string $dir (optional) The direction of the sort. Defaults to DESC.
 * @return array An array of modResources

 * @package goodnews
 * @subpackage processors
 */

class ResourcesGetListProcessor extends modObjectGetListProcessor {

    public $classKey = 'modResource';
    public $languageTopics = array('default','resource','goodnews:default');
    public $defaultSortField = 'publishedon';
    public $defaultSortDirection = 'DESC';
    public $objectType = 'goodnews';
    
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        
        $resourceColumns = array(
            'id',
            'parent',
            'pagetitle',
            'publishedon',
            'createdon',
            'context_key',
        );
        $c->select($this->modx->getSelectColumns('modResource', 'modResource', '', $resourceColumns));
        
        $parentIds = explode(',', $this->getProperty('parentIds', 0));

        $c->where(array(
            'parent:IN' => $parentIds,
            'published' => 1,
        ));

        // fixed sorting needed for grouping!
        $c->sortBy('parent', 'ASC');
        
        // seems that $defaultSortField and $defaultSortDirection doesn't work when using 
        // a grouped grid, so we create our own default sorting:
        $sort = $this->getProperty('sort', 'publishedon');
        $dir  = $this->getProperty('dir', 'DESC');
        $c->sortby($sort, $dir);

        return $c;
    }
    
    public function prepareRow(xPDOObject $object) {
        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $objectArray = $object->toArray();
        $objectArray['pagetitle'] = htmlentities($objectArray['pagetitle'], ENT_COMPAT, $charset);
        
        $parentObj = $this->modx->getObject('modResource', $objectArray['parent']);
        if (is_object($parentObj)) {
            $parent = $parentObj->get('pagetitle').' - '.$this->modx->lexicon('context').': '.$parentObj->get('context_key');
        } else {
            $parent = $this->modx->lexicon('context').': '.$objectArray['context_key'];  // as we have no parent -> use context key as parent name
        }
        $objectArray['parent'] = htmlentities($parent, ENT_COMPAT, $charset);

        $preview = $objectArray['introtext'];
        if (empty($preview)) {
            $preview = $objectArray['content'];
        }
        $objectArray['preview'] = $this->truncate($preview, 250);
                
        return $objectArray;
    }

    /**
     * Truncate plain text strings at word breaks and add ellipsis character
     * 
     * @access private
     * @param string $input The original string
     * @param int $limit The maximum length of the truncated output string (default = 200)
     * @return string $output The truncated string
     */
    private function truncate($input, $limit = 200) {
                
        // default values
        $break    = ' ';
        $ellipsis = '&nbsp;&#8230;';
        
        // read modx settings
        $usemb = function_exists('mb_strlen') && $this->modx->getOption('use_multibyte', null, false);
        $encoding = $this->modx->getOption('modx_charset', null, 'UTF-8');
        
        // strip tags
        $input = strip_tags($input);
        
        // convert html encoded chars back to single chars to ensure correct character counting
        $output = html_entity_decode($input, ENT_COMPAT, $encoding);
        
        // multi-byte based
        if ($usemb) {
            if (mb_strlen($output, $encoding) > $limit) {
                $output = mb_substr($output, 0, $limit, $encoding);  
                $length = mb_strrpos($output, $break, $encoding);
                if ($length !== false) {
                    $output = mb_substr($output, 0, $length, $encoding);
                }
                $output .= $ellipsis; 
            }
        } else {
            if (strlen($output) > $limit) {
                $output = substr($output, 0, $limit);
                $length = strrpos($output, $break);
                if ($length !== false) {
                    $output = substr($output, 0, $length);
                }
                $output .= $ellipsis;
            }
        }
        
        // re-encode special chars
        $output = htmlentities($output, ENT_COMPAT, $encoding, false);
        return $output;
    }
}
return 'ResourcesGetListProcessor';
