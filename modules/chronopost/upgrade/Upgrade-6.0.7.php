<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_7($object)
{
    /** @var $object Chronopost */
    return $object->registerHook('displayPaymentTop');
}
