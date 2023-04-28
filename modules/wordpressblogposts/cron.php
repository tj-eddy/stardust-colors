<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include_once(_PS_MODULE_DIR_.'/wordpressblogposts/lib/bootstrap.php');

$controller = new WBPCronController(null);
$controller->run(__FILE__);
