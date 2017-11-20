<?php
/**
 * 2015-2017 Urb-it
 *
 * NOTICE OF LICENSE
 *
 *
 *
 * Do not edit or add to this file if you wish to upgrade Urb-it to newer
 * versions in the future. If you wish to customize Urb-it for your
 * needs please refer to https://urb-it.com for more information.
 *
 * @author    Urb-it SA <parissupport@urb-it.com>
 * @copyright 2015-2017 Urb-it SA
 * @license  http://www.gnu.org/licenses/
 */

$sql = array(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'urbitinventoryfeed` (
        `id_urbitinventoryfeed` int(11) NOT NULL AUTO_INCREMENT,
        PRIMARY KEY  (`id_urbitinventoryfeed`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;',
);

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
