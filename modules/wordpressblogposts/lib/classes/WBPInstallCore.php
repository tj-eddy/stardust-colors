<?php

class WBPInstallCore
{
	protected static function dropTable($table_name)
	{
		$sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.pSQL($table_name).'`';
		DB::getInstance()->execute($sql);
	}

}