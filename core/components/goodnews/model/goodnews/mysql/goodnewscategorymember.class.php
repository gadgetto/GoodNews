<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewscategorymember.class.php');
class GoodNewsCategoryMember_mysql extends GoodNewsCategoryMember {}
?>