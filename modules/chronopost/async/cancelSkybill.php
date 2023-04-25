<?php
header('Content-type: text/plain');
require('../../../config/config.inc.php');
include_once '../libraries/TrackingServiceWSService.php';
include_once '../chronopost.php';

/** @var Chronopost $chronopostInstance */
$chronopostInstance = Module::getInstanceByName('chronopost');

/* Check secret */
if (Tools::isEmpty('shared_secret') || Tools::getValue('shared_secret') != Configuration::get('CHRONOPOST_SECRET')) {
    die('Secret does not match.');
}

if (!Tools::getIsset('skybill') || !Tools::getIsset('id_order')) {
    die('Parameter Error');
}

$LTRequest = DB::getInstance()->executeS(
    'SELECT lt, account_number FROM '
    . _DB_PREFIX_ . 'chrono_lt_history WHERE id_order = ' . (int)Tools::getValue('id_order') . ' AND `cancelled` IS NULL AND lt = "' . pSQL(Tools::getValue('skybill')) . '"'
);

foreach ($LTRequest as $LT) {
    $ws = new TrackingServiceWSService();
    $params = new cancelSkybill();
    $params->language = 'fr_FR';
    $params->skybillNumber = $LT['lt'];

    $order = new Order((int)Tools::getValue('id_order'));
    $account = Chronopost::getAccountInformationByAccountNumber($LT['account_number']);
    $params->accountNumber = $account['account'];
    $params->password = $account['password'];

    $return = $ws->cancelSkybill($params)->return;

    if ($return) {
        DB::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'chrono_lt_history
          SET cancelled = 1
          WHERE lt = \'' . pSQL($LT['lt']) . '\''
        );
    }

    $count = 0;
    $LTcount = DB::getInstance()->executeS(
        'SELECT count(lt) as count FROM '
        . _DB_PREFIX_ . 'chrono_lt_history WHERE id_order = ' . (int)Tools::getValue('id_order') . ' AND `cancelled` IS NULL'
    );
    if (isset($LTcount[0]['count'])) {
        $count = $LTcount[0]['count'];

        if ($count === "0") {
            $chronopostInstance::setWsShippingNumber($order, '');
        }
    }
}


echo Tools::jsonEncode($return);
