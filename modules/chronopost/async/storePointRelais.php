<?php
header('Content-type: text/plain');
include_once '../../../config/config.inc.php';

if (!Tools::getIsset('relaisID')
    || !Tools::getIsset('cartID')) {
    die('Parameter Error');
}


$cart = new Cart((int)Tools::getValue('cartID'));

if ($cart->id_customer != (int)Context::getContext()->customer->id) {
    die('KO');
}

Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'chrono_cart_relais` VALUES (
	' . (int)Tools::getValue('cartID') . ', "' . pSQL(Tools::getValue('relaisID')) . '") ON DUPLICATE 
	KEY UPDATE id_pr="' . pSQL(Tools::getValue('relaisID')) . '"');

echo json_encode(array('status' => 'OK'));
