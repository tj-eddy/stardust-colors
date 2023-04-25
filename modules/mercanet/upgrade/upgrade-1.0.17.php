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
function upgrade_module_1_0_17($module)
{
    // Recurring
    Configuration::updateGlobalValue(
        'MERCANET_RECURRING_NAME',
        array(
        Language::getIdByIso('fr') => 'Paiement par abonnement sécurisé par carte via Mercanet',
        Language::getIdByIso('en') => 'Card payment recurring secured by Mercanet'
        )
    );
    
    // test config
    Configuration::updateGlobalValue('MERCANET_TEST_ACCOUNT', '211000021310001');
    Configuration::updateGlobalValue('MERCANET_TEST_KEY_SECRET', 'S9i8qClCnb2CZU3y3Vn0toIOgz3z_aBi79akR30vM9o');
    Configuration::updateGlobalValue('MERCANET_TEST_KEY_VERSION', '1');
    
    $sql = array();
    // MERCANET PAYMENT RECURRING
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_payment_recurring` (
        `id_mercanet_payment_recurring` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_product` INT(10) NOT NULL,
        `type` INT(10) NULL,
        `periodicity` VARCHAR(10) NOT NULL,
        `number_occurences` VARCHAR(10) NOT NULL,
        `recurring_amount` FLOAT(6) NULL,
        PRIMARY KEY (`id_mercanet_payment_recurring`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_customer_payment_recurring` (
        `id_mercanet_customer_payment_recurring` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_product` INT(10) NOT NULL,
        `id_tax_rules_group` INT(10) NOT NULL,
        `id_order` INT(10) NOT NULL,
        `id_customer` INT(10) NOT NULL,
        `id_mercanet_transaction` INT(10) NOT NULL,
        `status` INT(10) NOT NULL,
        `amount_tax_exclude` FLOAT(6) NOT NULL,
        `periodicity` VARCHAR(10) NOT NULL,
        `number_occurences` VARCHAR(10) NOT NULL,
        `current_occurence` INT(10) NOT NULL DEFAULT 0,
        `date_add` DATETIME,
        `last_schedule` DATETIME,
        `next_schedule` DATETIME,
        `current_specific_price` INT(10) NOT NULL DEFAULT 0,
        `id_cart_paused_currency` INT(10) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id_mercanet_customer_payment_recurring`),
        KEY (`id_product`),
        KEY (`id_tax_rules_group`),
        KEY (`id_order`),
        KEY (`id_mercanet_transaction`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'mercanet_transaction` ADD `id_order_recurring` INT(10) NULL AFTER `id_order`';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }
    $module->registerHook('displayAdminProductsExtra');
    $module->registerHook('actionProductUpdate');
    $module->registerOrderStatus();


    return true;
}
