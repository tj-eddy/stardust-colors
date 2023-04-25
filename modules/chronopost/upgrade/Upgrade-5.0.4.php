<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_0_4($object)
{
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chrono_lt_history` 
        ADD `type` int(11) NOT NULL AFTER `account_number`;');

    return true;
}
