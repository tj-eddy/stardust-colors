<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_0_6($object)
{
    Configuration::updateValue('CHRONOPOST_SATURDAY_CUSTOMER', 'no');
    Configuration::updateValue('CHRONOPOST_SATURDAY_SUPPLEMENT', null);

    Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chrono_cart_saturday_supplement` (
                `id_cart` int(10) NOT null,
                `saturday_supplement` int null,
                PRIMARY KEY (`id_cart`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8;');


    return true;
}
