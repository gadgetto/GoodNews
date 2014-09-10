<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewssubscriberlog.class.php');
class GoodNewsSubscriberLog_mysql extends GoodNewsSubscriberLog {}
?>