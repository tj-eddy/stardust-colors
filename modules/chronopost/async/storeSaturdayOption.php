<?php
header('Content-type: text/plain');
include_once '../../../config/config.inc.php';

if (!Tools::getIsset('saturday_supplement')
    || !Tools::getIsset('cartID')) {
    die('Parameter Error');
}

$cart = new Cart((int)Tools::getValue('cartID'));
$value = Tools::getValue('saturday_supplement') === 'true' ? 1 : 0;

if ($cart->id_customer != (int)Context::getContext()->customer->id) {
    die('KO');
}

Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'chrono_cart_saturday_supplement` VALUES (
	' . (int)Tools::getValue('cartID') . ',' . pSQL($value) . ') ON DUPLICATE 
	KEY UPDATE saturday_supplement=' . pSQL($value));

echo $value;
