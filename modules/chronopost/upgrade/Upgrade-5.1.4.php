<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_1_4($object)
{
    Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'carrier` SET is_module = 1 
        WHERE `external_module_name` = \'chronopost\';');

    return true;
}
