<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_5_0($object)
{
    if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
        return ($this->registerHook('displayAfterCarrier') && // For point relais GMap
            $this->registerHook('actionCarrierUpdate') && // For update of carrier IDs
            $this->registerHook('displayHeader'));
    } else {
        return true;
    }
}
