<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

function upgrade_module_1_4_10($module)
{
    Shop::isFeatureActive() && Shop::setContext(Shop::CONTEXT_ALL);

    Configuration::updateValue('elementor_max_revisions', 10);
    Configuration::updateValue('elementor_space_between_widgets', 20);
    Configuration::updateValue('elementor_page_title_selector', 'header.page-header');
    Configuration::updateValue('elementor_page_wrapper_selector', '#content, #wrapper, #wrapper .container');

    $ce_revision = _DB_PREFIX_ . 'ce_revision';
    $engine = _MYSQL_ENGINE_;
    $result = Db::getInstance()->execute("
        CREATE TABLE IF NOT EXISTS `$ce_revision` (
            `id_ce_revision` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `parent` bigint(20) UNSIGNED NOT NULL,
            `id_employee` int(10) UNSIGNED NOT NULL,
            `title` varchar(255) NOT NULL,
            `content` longtext NOT NULL,
            `active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_ce_revision`),
            KEY `id` (`parent`),
            KEY `date_add` (`date_upd`)
        ) ENGINE=$engine DEFAULT CHARSET=utf8;
    ");
    $result &= $module->registerHook('actionObjectCERevisionDeleteAfter');
    $result &= $module->registerHook('actionProductAdd');
    $result &= $module->registerHook('CETemplate');

    CE\Plugin::instance()->files_manager->clearCache();
    Media::clearCache();

    return $result;
}
