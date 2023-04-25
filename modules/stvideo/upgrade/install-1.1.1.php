<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_1_1($object)
{
    $result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_video` `bo_title`');  
   
    if(is_array($field) && count($field))
        return $result;

    if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_video` ADD COLUMN `bo_title` VARCHAR(255) NULL AFTER `offset_y`'
        ))
        $result &= false;
    
    return $result;
}
