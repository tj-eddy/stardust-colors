<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_4_0($object)
{
    /** @var $object Chronopost */
    
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_calculateproducts_cache2` 
        ADD `toshop` INT NOT NULL AFTER `dimanchebal`;');
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_calculateproducts_cache2` 
        ADD `toshopeurope` INT NOT NULL AFTER `toshop`;');
}
