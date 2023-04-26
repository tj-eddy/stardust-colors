<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

function upgrade_module_2_5_11($module)
{
    Shop::isFeatureActive() && Shop::setContext(Shop::CONTEXT_ALL);

    return
        $module->registerHook('actionFrontControllerAfterInit', null, 1) &&
        $module->registerHook('actionFrontControllerInitAfter', null, 1);
}
