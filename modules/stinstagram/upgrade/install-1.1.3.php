<?php
//20220318
if (!defined('_PS_VERSION_'))
	exit;

    function upgrade_module_1_1_3($object)
    {
        $result=true;
        $field=Db::getInstance()->executeS('describe `'._DB_PREFIX_.'st_instagram` pause_on_hover');
        if(!is_array($field) || !count($field)){
         $result = Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram`     ADD COLUMN `pause_on_enter` TINYINT(1) DEFAULT \'0\' NULL AFTER `pause_on_hover`,     ADD COLUMN `reverse_direction` TINYINT(1) DEFAULT \'0\' NULL AFTER `pause_on_enter`');
        }
        return (bool)$result;
    }