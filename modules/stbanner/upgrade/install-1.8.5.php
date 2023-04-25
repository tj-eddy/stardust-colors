<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_8_5($object)
{
    $result = true;
    
    $result &= $object->registerHook('actionOutputHTMLBefore');
    
    return $result;
}
