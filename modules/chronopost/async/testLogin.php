<?php
include('../libraries/QuickcostServiceWSService.php');
include('../../../config/config.inc.php');

/* Check secret */
if (Tools::isEmpty('shared_secret') || Tools::getValue('shared_secret') != Configuration::get('CHRONOPOST_SECRET')) {
    die('Secret does not match.');
}

if (!Tools::getIsset('account')
    || !Tools::getIsset('password')) {
    die('Parameter Error');
}

$service = new QuickcostServiceWSService();
$quick = new quickCost();
$quick->accountNumber = Tools::getValue('account');
$quick->password = Tools::getValue('password');
$quick->depCode = '92500';
$quick->arrCode = '75001';
$quick->weight = '1';
$quick->productCode = '1';
$quick->type = 'D';

$res = $service->quickCost($quick);

if ($res->return->errorCode == 0) {
    die('OK');
} elseif ($res->return->errorCode == 3) {
    echo 'Le nom d\'utilisateur ou le mot de passe saisi est incorrect.';
} else {
    echo 'Une erreur système est survenue, contactez le support Chronopost si le problème persiste.';
}
