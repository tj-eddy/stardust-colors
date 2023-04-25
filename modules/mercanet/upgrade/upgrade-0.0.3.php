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
function upgrade_module_0_0_3($module)
{
    // Install Order Override
    installOrderOverride($module);

    $sql = array();

    // MERCANET ORDER REFERENCE
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_order_reference` (
    `id_cart` INT(10) NOT NULL,
    `reference` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`id_cart`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    // Installer Override Order

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }
    
    return true;
}

/**
 * Install Order Override
 * @param Module $module
 * @return boolean
 */
function installOrderOverride($module)
{
    if (!is_dir($module->getLocalPath().'override')) {
        return true;
    }

    $result = true;

    foreach (Tools::scandir($module->getLocalPath().'override', 'php', '', true) as $file) {
        if ($file != 'classes/order/Order.php') {
            continue;
        }
        $class = basename($file, '.php');
        if (PrestaShopAutoload::getInstance()->getClassPath($class.'Core') || Module::getModuleIdByName($class)) {
            $result &= $module->addOverride($class);
        }
        return $result;
    }
}
