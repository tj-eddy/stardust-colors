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

function upgrade_module_1_6_12($module)
{   
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'mercanet_transaction` CHANGE `authorisation_id` `authorisation_id` VARCHAR(128)';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }
    
    Configuration::updateGlobalValue('MERCANET_TEST_PAYMENT_PAGE_URL', 'https://payment-webinit-mercanet.test.sips-services.com/paymentInit');
    Configuration::updateGlobalValue('MERCANET_WS_URL_TEST', 'https://office-server-mercanet.test.sips-services.com/rs-services/v2/');
    Configuration::updateGlobalValue('MERCANET_WT_URL_TEST', 'https://payment-webinit-mercanet.test.sips-services.com/walletManagementInit');
    
    return true;
}
