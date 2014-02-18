<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewscategory.class.php');
class GoodNewsCategory_mysql extends GoodNewsCategory {}
?>