<?php
header('Content-type: text/plain');
include('../../../config/config.inc.php');
include_once '../chronopost.php';

define(
    '_TRACKING_URL',
    'https://www.chronopost.fr/tracking-cxf/TrackingServiceWS/trackSkybill?language=fr_FR&skybillNumber=%s'
);

/* No more than one update per hour */
$time = time() - (int)Configuration::get('CHRONO_TRACKING_LAST_UPDATE');
if ($time < 3600) {
    die('No more than one update per hour');
}

/* Orders in state "shipping", for our carriers, with a tracking number */
$cookie = new Cookie('psAdmin');
if ($cookie->id_employee) {
    $today = new DateTime();
    $today->modify('-1 month');
    Configuration::updateValue('CHRONO_TRACKING_LAST_UPDATE', time());

    /* Orders in state "shipping", for our carriers, with a tracking number */
    $orders = Db::getInstance()->ExecuteS('SELECT oc.id_order, oc.tracking_number FROM ' . _DB_PREFIX_ . 'order_carrier oc 
    LEFT JOIN '
        . _DB_PREFIX_ . 'orders o ON o.id_order=oc.id_order LEFT JOIN ' . _DB_PREFIX_ . 'carrier c ON o.id_carrier=c.id_carrier
    WHERE c.id_reference IN ('.implode(', ', Chronopost::getChronoIDs()).')
        AND oc.tracking_number!=""  
        AND o.date_add > "' . $today->format('Y-m-d') . ' 00:00:00"
        AND o.current_state=' . _PS_OS_SHIPPING_);

    foreach ($orders as $order) {
        // Fix compatibility issues with hookActionOrderStatusUpdate
        if (Context::getContext()->currency === null) {
            $o = new Order((int)$order['id_order']);
            Context::getContext()->currency = new Currency($o->id_currency);
        }
        
        $fp = fopen(sprintf(_TRACKING_URL, $order['tracking_number']), 'r');
        $xml = stream_get_contents($fp);
        fclose($fp);

        if ($xml === false) {
            continue;
        }

        try {
            $xml = new SimpleXMLElement($xml);

            /* Registering needed namespaces. See http://stackoverflow.com/questions/10322464/ */
            $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xml->registerXPathNamespace('ns1', 'http://cxf.tracking.soap.chronopost.fr/');

            /* XPathing on namespaced XMLs can't be relative */
            foreach ($xml->xpath('//soap:Body/ns1:trackSkybillResponse/return/listEvents/events/code') as $event) {
                if (trim((string)$event) === 'D' ||
                    trim((string)$event) === 'D1' ||
                    trim((string)$event) === 'D2' ||
                    trim((string)$event) === 'D3' ||
                    trim((string)$event) === 'D4' ||
                    trim((string)$event) === 'D5' ||
                    trim((string)$event) === 'R') {
                    $history = new OrderHistory();
                    $history->id_order = (int)$order['id_order'];
                    $history->changeIdOrderState(_PS_OS_DELIVERED_, (int)$order['id_order'], true);
                    $history->save();
                }
            }
        } catch (\Exception $e) {
            continue;
        }
    }

    Configuration::updateValue('CHRONO_TRACKING_LAST_UPDATE', time());
}
