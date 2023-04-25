<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_6_2($object)
{
    try {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_lt_history` 
        ADD `cancelled` INT NULL AFTER `city`;');
    } catch (Exception $e) {
        // Silence is golden
    }
    return true;
}
