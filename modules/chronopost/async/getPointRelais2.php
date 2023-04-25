<?php
header('Content-type: text/plain');
require('../../../config/config.inc.php');
require('../chronopost.php');
include_once '../libraries/PointRelaisServiceWSService.php';

$ws = new PointRelaisServiceWSService();
$params = new recherchePointChronopost();
$params->zipCode = Tools::getValue('codePostal');
$accounts = json_decode(Configuration::get('CHRONOPOST_GENERAL_ACCOUNTS'), 1);
$account = $accounts[0];
$params->accountNumber = $account['account'];
$params->password = $account['password'];
$params->address = Tools::getValue('address');

if (Tools::getIsset('city') && Tools::getValue('city') != 'unknown') {
    $params->city = Tools::getValue('city');
}

if (Tools::getIsset('country') && Tools::getValue('country') != 'unknown') {
    $params->countryCode = Tools::getValue('country');
} else {
    $params->countryCode = 'FR';
}

$params->type = Configuration::get('CHRONOPOST_MAP_DROPMODE') ? Configuration::get('CHRONOPOST_MAP_DROPMODE') : 'P';

if (in_array(trim(Tools::getValue('carrier'), ","), Chronopost::getToShopIDs()) ) {
    // ToShop Direct has only dropoff in stores
    $params->type = 'C';
}

$params->service = 'L';
$params->weight = 0;
$params->shippingDate = date('d/m/Y');
$params->maxPointChronopost = 10;
$params->maxDistanceSearch = 40;
$params->holidayTolerant = 1;

$lastSelectedPr = '';
if ((int)Tools::getValue('cartID')) {
    $lastSelectedPr = Db::getInstance()->getValue('SELECT id_pr FROM `' . _DB_PREFIX_ . 'chrono_cart_relais` WHERE id_cart = ' . (int)Tools::getValue('cartID') . ' ORDER BY id_cart DESC');    
}

if ($params->countryCode == 'FR' || $params->countryCode == 'FX' || $params->countryCode == 'MC') {
    $params->countryCode = Chronopost::maybeCountryMapping($params->countryCode);
    $result = $ws->recherchePointChronopost($params)->return;
    $result->lastPR = $lastSelectedPr;
    echo Tools::jsonEncode($result);
} else {
    $result = $ws->recherchePointChronopostInter($params)->return;
    $result->lastPR = $lastSelectedPr;
    echo Tools::jsonEncode($result);
}
