<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_0_2($object)
{
    Configuration::updateValue('CHRONOPOST_MAP_DROPMODE', 'P');
    Configuration::updateValue('CHRONOPOST_SAMEDAY_SAMEDAY_HOUR_END','15');
    Configuration::updateValue('CHRONOPOST_SAMEDAY_SAMEDAY_MINUTE_END','0');

    return true;
}
