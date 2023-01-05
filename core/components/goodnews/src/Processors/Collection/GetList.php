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

namespace Bitego\GoodNews\Processors\Collection;

use Bitego\GoodNews\Model\GoodNewsGroupMember;
use MODX\Revolution\Processors\Model\GetListProcessor;
use MODX\Revolution\modResource;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

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

class GetList extends GetListProcessor
{
    public $classKey = modResource::class;
    public $languageTopics = ['default', 'resource', 'goodnews:default'];
    public $defaultSortField = 'publishedon';
    public $defaultSortDirection = 'DESC';
    public $objectType = 'goodnews';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $resourceColumns = [
            'id',
            'parent',
            'pagetitle',
            'publishedon',
            'createdon',
            'context_key',
        ];
        $c->select($this->modx->getSelectColumns(modResource::class, 'modResource', '', $resourceColumns));
        $parentIds = explode(',', $this->getProperty('parentIds', 0));
        $c->where([
            'parent:IN' => $parentIds,
            'published' => 1,
        ]);

        $parentfilter = $this->getProperty('parentfilter', '');
        if (!empty($parentfilter)) {
            if ($parentfilter != 'all') {
                $c->where([
                    'parent' => $parentfilter
                ]);
            }
        }
        if (!empty($groupfilter)) {
            $c->leftJoin(GoodNewsGroupMember::class, 'GroupMember', 'modUser.id = GroupMember.member_id');
            if ($groupfilter == 'nogroup') {
                $c->where(['GroupMember.goodnewsgroup_id' => null]);
            } else {
                $c->where(['GroupMember.goodnewsgroup_id' => $groupfilter]);
            }
        }
        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $charset = $this->modx->getOption('modx_charset', null, 'UTF-8');
        $objectArray = $object->toArray();
        $objectArray['pagetitle'] = htmlentities($objectArray['pagetitle'], ENT_COMPAT, $charset);
        $parentObj = $this->modx->getObject(modResource::class, $objectArray['parent']);
        if (is_object($parentObj)) {
            $parent = $parentObj->get('pagetitle') .
                ' - ' .
                $this->modx->lexicon('context') .
                ': ' .
                $parentObj->get('context_key');
        } else {
            // as we have no parent -> use context key as parent name
            $parent = $this->modx->lexicon('context') . ': ' . $objectArray['context_key'];
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
    private function truncate($input, $limit = 200)
    {
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
