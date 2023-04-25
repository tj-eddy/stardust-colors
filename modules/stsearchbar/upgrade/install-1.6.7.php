<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_6_7($object)
{
    return $object->registerHook('filterProductSearch');
}