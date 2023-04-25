<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_7_0($object)
{
    // Convert conract data
    $current_contract = array(
        'account'    => Configuration::get('CHRONOPOST_GENERAL_ACCOUNT'),
        'password'   => Configuration::get('CHRONOPOST_GENERAL_PASSWORD'),
        'subaccount' => Configuration::get('CHRONOPOST_GENERAL_SUBACCOUNT'),
        'accountname' => 'Contrat par défaut'
    );
    Configuration::updateValue('CHRONOPOST_GENERAL_ACCOUNTS', json_encode(array($current_contract)));

    // Assign all products to this contract
    foreach (Chronopost::$carriersDefinitions as $product_code => $product) {
        Configuration::updateValue('CHRONOPOST_' . $product_code . '_ACCOUNT',
            Configuration::get('CHRONOPOST_GENERAL_ACCOUNT'));
    }

    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chrono_lt_history` 
        ADD `account_number` VARCHAR(8) NOT NULL AFTER `city`;');

    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chrono_quickcost_cache` 
        ADD `account_number` VARCHAR(8) NOT NULL AFTER `price`;');

    return true;
}
