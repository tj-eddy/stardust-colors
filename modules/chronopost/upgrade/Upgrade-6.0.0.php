<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Chronopost $object
 *
 * @return bool
 */
function upgrade_module_6_0_0($object)
{
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chrono_lt_history` 
        ADD `lt_dlc` varchar(20) NULL AFTER `lt_reference`;');

    $tabs = Tab::getCollectionFromModule('chronopost');
    if (!empty($tabs)) {
        foreach ($tabs as $tab) {
            $tab->delete();
        }
    }
    
    $object->adminInstall();

    return true;
}
