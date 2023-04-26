<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

function upgrade_module_2_5_2($module)
{
    Shop::isFeatureActive() && Shop::setContext(Shop::CONTEXT_ALL);

    Configuration::updateValue('elementor_viewport_lg', 1025);
    Configuration::updateValue('elementor_viewport_md', 768);
    Configuration::updateValue('elementor_global_image_lightbox', 1);

    $db = Db::getInstance();
    $ce_theme = _DB_PREFIX_ . 'ce_theme';
    $ce_revision = _DB_PREFIX_ . 'ce_revision';
    $engine = _MYSQL_ENGINE_;
    $result = $db->execute("
        CREATE TABLE IF NOT EXISTS `$ce_theme` (
            `id_ce_theme` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` int(10) UNSIGNED NOT NULL,
            `type` varchar(64) NOT NULL DEFAULT '',
            `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_ce_theme`)
        ) ENGINE=$engine DEFAULT CHARSET=utf8;
    ") && $db->execute("
        CREATE TABLE IF NOT EXISTS `{$ce_theme}_shop` (
            `id_ce_theme` int(10) UNSIGNED NOT NULL,
            `id_shop` int(10) UNSIGNED NOT NULL,
            `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_ce_theme`,`id_shop`),
            KEY `id_shop` (`id_shop`)
        ) ENGINE=$engine DEFAULT CHARSET=utf8;
    ") && $db->execute("
        CREATE TABLE IF NOT EXISTS `{$ce_theme}_lang` (
            `id_ce_theme` int(10) UNSIGNED NOT NULL,
            `id_lang` int(10) UNSIGNED NOT NULL,
            `id_shop` int(10) UNSIGNED NOT NULL DEFAULT 1,
            `title` varchar(128) NOT NULL DEFAULT '',
            `content` text,
            PRIMARY KEY (`id_ce_theme`,`id_shop`,`id_lang`)
        ) ENGINE=$engine DEFAULT CHARSET=utf8;
    ");
    try {
        // Add `type` to ce_revision table
        $db->execute("
            ALTER TABLE `$ce_revision`
            ADD `type` varchar(64) NOT NULL DEFAULT '' AFTER `title`
        ");
    } catch (Exception $ex) {
        // Do nothing when `type` already exists
    }

    foreach (Tab::getCollectionFromModule($module->name) as $tab) {
        $tab->delete();
    }

    $result &= $module->registerHook('actionObjectCEContentDeleteAfter');

    // Clean up
    foreach (glob(_CE_PATH_ . 'views/css/ce/global-*.css') as $css_file) {
        Tools::deleteFile($css_file);
    }

    return $result;
}
