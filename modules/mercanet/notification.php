<?php
/**
 * 1961-2019 BNP Paribas
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1961-2019 BNP Paribas
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/mercanet.php');

$params = $_POST;
$link = new Link();

MercanetLogger::log('(notification) We are now in Notification. params : '.MercanetLogger::transformArrayToString($params), MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);

// If data is missing, we stop the process
if (!isset($params['Data']) || !isset($params['Seal'])) {
    MercanetLogger::log('(notification) Data is missing : we stop the process.', MercanetLogger::LOG_ERROR, MercanetLogger::FILE_DEBUG);
    
    Tools::redirect($link->getModuleLink('mercanet', 'validation', array(
            'is_sealed' => false)));
} else {
    MercanetLogger::log('(notification) Data are here.', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
}

if (!isset($params['Encode'])) {
    $params['Encode'] = null;
}
$is_sealed = MercanetApi::verifySeal($params['Data'], $params['Seal']);
$source = 'notification.php';

// Decode Data
switch ($params['Encode']) {
    case 'base64':
        $raw_data = base64_decode($params['Data']);
        $base64 = true;
        break;

    default:
        $raw_data = $params['Data'];
        $base64 = false;
        break;
}

// Get data from rawData
$data = MercanetApi::getDataFromRawData($raw_data);

// If the sealed is valid, we create the order / payment and redirect the customer
if ((bool)$is_sealed == true) {
    MercanetLogger::log('(notification) Seal is good', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);

    if (MercanetReferencePayed::insertReference($data['transactionReference'], $source, $params['Data']) == true) {
        // Create Order / Payment
        $notification = new MercanetNotification();
        $notification->notify($params['Data'], $params['Seal'], $source, $base64);
    }
} else {
    $message = 'Seal invalid =>';
    $message .= ' Params: ';
    $message .= implode(
        ', ',
        array_map(
            function ($v, $k) {
                return $k.'='.$v;
            },
            $params,
            array_keys($params)
        )
    );

    MercanetLogger::log("(notification)".$message, MercanetLogger::LOG_ERROR, MercanetLogger::FILE_DEBUG);
}
