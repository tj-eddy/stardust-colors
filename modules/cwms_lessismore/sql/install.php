<?php

$sql = array();

$sql[] = "CREATE TABLE `" . _DB_PREFIX_ . lessismoreTable::$definition["table"] . "` (
        `" . lessismoreTable::$definition["primary"] . "` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `page` varchar(255) DEFAULT NULL,
        `selector` varchar(255) DEFAULT NULL,
        `speed` int(11) unsigned NOT NULL,
        `collapsedHeight` varchar(255) DEFAULT NULL,
        `moreLink` varchar(255) DEFAULT NULL,
        `lessLink` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`" . lessismoreTable::$definition["primary"] . "`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
