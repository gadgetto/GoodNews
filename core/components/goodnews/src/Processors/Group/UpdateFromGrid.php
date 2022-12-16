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

namespace Bitego\GoodNews\Processors\Group;

use Bitego\GoodNews\Processors\Group\Update;

/**
 * Group update from grid processor
 *
 * @package goodnews
 * @subpackage processors
 */

class UpdateFromGrid extends Update
{
    public function initialize()
    {
        $data = $this->getProperty('data');
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }
        $data = $this->modx->fromJSON($data);
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }
        $this->setProperties($data);
        $this->unsetProperty('data');
        return parent::initialize();
    }

    public function beforeSave()
    {
        $this->object->set('editedon', strftime('%Y-%m-%d %H:%M:%S'));
        $this->object->set('editedby', $this->modx->user->get('id'));
        return parent::beforeSave();
    }
}
