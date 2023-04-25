<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_2_2_7($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_LAZYLOAD_MAIN_GALLERY', 0);
    
	return $result;
}
