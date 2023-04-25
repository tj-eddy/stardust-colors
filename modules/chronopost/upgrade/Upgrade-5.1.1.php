<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_1_1($object)
{
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chrono_lt_history` 
        ADD `lt_reference` varchar(20) NOT NULL AFTER `lt`;');

    return true;
}
