<?php

$sql = [
    "DROP TABLE " . _DB_PREFIX_ . lessismoreTable::$definition["table"]
];
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
