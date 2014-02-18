<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewsprocess.class.php');
class GoodNewsProcess_mysql extends GoodNewsProcess {}
?>