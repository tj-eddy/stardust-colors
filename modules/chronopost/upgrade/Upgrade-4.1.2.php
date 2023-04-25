<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_1_2()
{
    return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'configuration` 
        WHERE name LIKE "CHRONOPOST%" AND (id_shop_group != NULL OR id_shop!=NULL)');
}
