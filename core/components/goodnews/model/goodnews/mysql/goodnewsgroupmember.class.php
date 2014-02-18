<?php
/**
 * @package goodnews
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/goodnewsgroupmember.class.php');
class GoodNewsGroupMember_mysql extends GoodNewsGroupMember {}
?>