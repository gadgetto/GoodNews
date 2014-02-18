<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewssubscribermeta.class.php');
class GoodNewsSubscriberMeta_mysql extends GoodNewsSubscriberMeta {}
?>