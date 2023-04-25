<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_2_1_0($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_HIDE_DISCOUNT', 0);
    $result &= Configuration::updateGlobalValue('ST_B_RELATED_IMAGE_TYPE', 'medium');//it was home_default
    

    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_swiper` `content_width`');  
   
    if(!is_array($field) || !count($field))
    {
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_swiper` 
        ADD `content_width` tinyint(2) unsigned NOT NULL DEFAULT 0'))
            $result &= false;
    }
    

    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_owl_carousel` `content_width`');  
   
    if(!is_array($field) || !count($field))
    {
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_owl_carousel` 
        ADD `content_width` tinyint(2) unsigned NOT NULL DEFAULT 0'))
            $result &= false;
    }
    
	return $result;
}
