<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewsresourcemailing.class.php');
class GoodNewsResourceMailing_mysql extends GoodNewsResourceMailing {}
?>