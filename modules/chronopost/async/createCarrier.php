<?php
header('Content-type: application/json');
require('../../../config/config.inc.php');
require('../chronopost.php');

include_once dirname(__FILE__) . '/../libraries/webservicesHelper.php';
$wsHelper = new webservicesHelper();
$module_instance = new Chronopost();

$return = [];

/* Check secret */
if (Tools::isEmpty('shared_secret') || Tools::getValue('shared_secret') != Configuration::get('CHRONOPOST_SECRET')) {
    $return['error'] = 'Secret does not match.';
}

if (!Tools::getIsset('code') || !Tools::getIsset('contract')) {
    $return['error'] = 'Parameter Error';
}

$contract = Tools::getValue('contract');

if (!is_numeric($contract) || $contract <= 0) {
    $return['error'] = $module_instance->l('Please choose a contract', 'createcarrier');
}

$carrier_code = Tools::getValue('code');

// Check if we can create this carrier
$productAvailable = false;
$available_products = $wsHelper->getMethodsForContract(Tools::getValue('contract'));

if (!isset($return['error'])) {
    if (!isset(Chronopost::$carriersDefinitions[$carrier_code]['products'])
        && in_array(Chronopost::$carriersDefinitions[$carrier_code]['product_code'], $available_products)) {
        $productAvailable = true;
    } else {
        foreach (Chronopost::$carriersDefinitions[$carrier_code]['products'] as $product) {
            if (in_array($product['code'], $available_products)) {
                $productAvailable = true;
            }
        }
    }
}

if ($productAvailable === false) {
    $return['error'] = $module_instance->l(
        'Product not available : you can\'t create this carrier with this contract',
        'createcarrier'
    );
}

if (!isset($return['error'])) {
    $carrier = Chronopost::createCarrier($carrier_code);
    if ($carrier) {
        Configuration::updateValue('CHRONOPOST_' . Tools::strtoupper($carrier_code) . '_ACCOUNT', $contract);
        $return['success'] = true;
    } else {
        $return['error'] = $module_instance->l(
            'An error occurred while creating the carrier. Please check your settings (contract and addresses).',
            'createcarrier'
        );
    }
}

echo json_encode($return);
exit;
