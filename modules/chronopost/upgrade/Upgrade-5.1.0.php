<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_1_0($object)
{
    // Version specific hooks
    if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
        if (!$object->registerHook('displayCarrierList')) {
            return false;
        }
    } else {
        if (!$object->registerHook('displayAfterCarrier')) {
            return false;
        }
    }
    
    /** @var $object Chronopost */
    return (
        $object->registerHook('displayAdminOrder') 
        && $object->registerHook('displayAdminOrderMainBottom') 
        && $object->registerHook('actionValidateOrder')
    );
}
