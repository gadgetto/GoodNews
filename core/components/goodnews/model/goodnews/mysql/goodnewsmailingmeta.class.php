<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewsmailingmeta.class.php');
class GoodNewsMailingMeta_mysql extends GoodNewsMailingMeta {}
?>