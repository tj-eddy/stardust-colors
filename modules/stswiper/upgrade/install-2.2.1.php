<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_2_2_1($object)
{
    $result = true;
    
    $result &= $object->registerHook('actionOutputHTMLBefore');

    return $result;
}
