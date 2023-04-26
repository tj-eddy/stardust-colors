<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

function upgrade_module_2_5_7($module)
{
    // Create table for Custom Fonts
    $ce_font = _DB_PREFIX_ . 'ce_font';
    $engine = _MYSQL_ENGINE_;

    $result = Db::getInstance()->execute("
        CREATE TABLE IF NOT EXISTS `$ce_font` (
            `id_ce_font` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `family` varchar(128) NOT NULL DEFAULT '',
            `files` text,
            PRIMARY KEY (`id_ce_font`)
        ) ENGINE=$engine DEFAULT CHARSET=utf8;
    ");

    // Register delete action for ThemeVolty Blog post
    $result &= $module->registerHook('actionObjectTvcmsBlogPostsClassDeleteAfter');

    return $result;
}
