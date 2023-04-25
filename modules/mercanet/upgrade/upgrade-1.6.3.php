<?php
/**
 * 1961-2019 BNP Paribas
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1961-2019 BNP Paribas
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_6_3($module)
{

    $sql = array();
    // MERCANET REFERENCE PAYED
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_reference_payed` (
        `order_reference` varchar(128) NOT NULL,
        `source` varchar(128) NOT NULL,
        `date_add` datetime DEFAULT NULL,
        PRIMARY KEY (`order_reference`),
        UNIQUE KEY `order_reference` (`order_reference`)
      ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';


    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return true;
}
