<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates the module from previous versions to this version.
 * Triggered if module is installed and source is directly updated.
 * http://doc.prestashop.com/display/PS17/Enabling+the+Auto-Update
 */
function upgrade_module_1_0_3($objArgTNTOfficiel_1_0_3)
{
    // New Hook.
    if (!$objArgTNTOfficiel_1_0_3->registerHook('actionGetExtraMailTemplateVars')) {
        return false;
    }

    // Module::uninstall().
    if (!$objArgTNTOfficiel_1_0_3->uninstall()) {
        return false;
    }

    // If MultiShop and more than 1 Shop.
    if (Shop::isFeatureActive()) {
        // Define Shop context to all Shops.
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    // Module::install().
    if (!$objArgTNTOfficiel_1_0_3->install()) {
        return false;
    }

    // Success.
    return true;
}
