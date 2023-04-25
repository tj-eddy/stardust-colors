<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_4_6($object)
{
	$field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_news_letter` `showonclick`');
    if(!is_array($field) || !count($field)) {
        return Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_news_letter` ADD `showonclick` tinyint(1) unsigned NOT NULL DEFAULT 0');    
    }
    return true;
}
