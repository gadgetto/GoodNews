<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewsresourcecontainer.class.php');
class GoodNewsResourceContainer_mysql extends GoodNewsResourceContainer {}
?>