<?php

$sql = array(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'urbit_inventoryfeed` (
        `id_urbit_inventoryfeed` int(11) NOT NULL AUTO_INCREMENT,
        PRIMARY KEY  (`id_urbit_inventoryfeed`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;',
);

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
