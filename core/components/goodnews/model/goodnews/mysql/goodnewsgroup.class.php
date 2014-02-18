<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewsgroup.class.php');
class GoodNewsGroup_mysql extends GoodNewsGroup {}
?>