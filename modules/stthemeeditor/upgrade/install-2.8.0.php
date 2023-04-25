<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_8_0($object)
{
    $result = true;

    $result &= Configuration::updateValue('STSN_RETINA', 1);
    
    return $result;
}
