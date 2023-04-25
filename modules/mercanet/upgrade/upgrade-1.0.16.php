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
function upgrade_module_1_0_16($module)
{
    // cetelem 3X
    Configuration::updateGlobalValue('MERCANET_F3CB_NAME', 'CETELEM_3X');
    Configuration::updateGlobalValue('MERCANET_F3CB_MIN_AMOUNT', 100);
    Configuration::updateGlobalValue('MERCANET_F3CB_MAX_AMOUNT', 3000);
    // cetelem 4X
    Configuration::updateGlobalValue('MERCANET_F4CB_NAME', 'CETELEM_4X');
    Configuration::updateGlobalValue('MERCANET_F4CB_MIN_AMOUNT', 100);
    Configuration::updateGlobalValue('MERCANET_F4CB_MAX_AMOUNT', 3000);

    Configuration::updateGlobalValue('MERCANET_INTERFACE_VERSION', 'HP_2.16');

    $sql = array();

    // MERCANET ORDER QUEUE
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_order_queue` (
        `id_mercanet_order_queue` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_cart` INT(10) NOT NULL,
        `id_order` INT(10) NOT NULL,
        `source` VARCHAR(128) NOT NULL,
        `date_add` DATETIME,
        `date_done` DATETIME,
        PRIMARY KEY (`id_mercanet_order_queue`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return true;
}
