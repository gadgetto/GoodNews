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

/**
 * Helper functions for _build
 *
 * @package goodnews
 * @subpackage build
 */

/**
 * Get content of php file.
 *
 * @param string $filename
 * @return mixed|string
 */
function getPHPFileContent($filename)
{
    $o = file_get_contents($filename);
    $o = str_replace('<?php', '', $o);
    $o = str_replace('?>', '', $o);
    $o = trim($o);
    return $o;
}
