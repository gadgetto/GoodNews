<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewsrecipient.class.php');
class GoodNewsRecipient_mysql extends GoodNewsRecipient {}
?>