<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_4_3($object)
{
	return $object->registerHook('displayAdminCustomersForm');
}
