<?php

const CHRONO_SHIP_TYPE = 0;
const CHRONO_RETURN_TYPE = 1;

if (!isset($_SESSION)) {
    session_start();
}

require('../../config/config.inc.php');
$errors = [];

if (!Tools::getIsset('orderid') && !Tools::getIsset('orders')) {
    $errors[] = '<h1>Informations de commande non transmises</h1>';
}

require_once('chronopost.php');
include('libraries/ShippingServiceWSService.php');
include_once('libraries/PointRelaisServiceWSService.php');
include('libraries/QuickcostServiceWSService.php');
include_once('libraries/checkColis.php');
include_once('libraries/ChronopostLT.php');
include_once('libraries/webservicesHelper.php');

if (Shop::isFeatureActive()) {
    Shop::setContext(Shop::CONTEXT_ALL);
}

$chronopostLT = new ChronopostLT();
$module_instance = new Chronopost();

$accounts = [];
$freshOptions = [];
if (Tools::getIsset('orderid')) {
    $accounts[Tools::getValue('orderid')] = Tools::getValue(('account'));

    $dimensions[Tools::getValue('orderid')] = array(
        "weights" => Tools::getValue('weight'),
        "heights" => Tools::getValue('height'),
        "widths"  => Tools::getValue('width'),
        "lengths" => Tools::getValue('length'),
    );

    $freshOptions[Tools::getValue('orderid')] = array(
        "dlc"             => Tools::getValue('dlc'),
        "chrono_products" => Tools::getValue('chrono_product'),
    );
}

$multi = [];
$coef = Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF');

$massActions = false;
$shipSaturdays = false;
if (Tools::getIsset('weights', 'heights')) {
    $massActions = true;
    $dimensionsRaw = array(
        "weights" => json_decode(stripslashes(Tools::getValue('weights')), true),
        "heights" => json_decode(stripslashes(Tools::getValue('heights')), true),
        "widths"  => json_decode(stripslashes(Tools::getValue('widths')), true),
        "lengths" => json_decode(stripslashes(Tools::getValue('lengths')), true),
    );

    $dimensions = [];
    foreach ($dimensionsRaw as $dimension => $orderDimensions) {
        foreach ($orderDimensions as $orderId => $values) {
            if (!isset($dimensions[$orderId])) {
                $dimensions[$orderId] = [];
            }
            $dimensions[$orderId][$dimension] = $values;
        }
    }

    $freshOptionsRaw = array(
        "dlc"             => json_decode(stripslashes(Tools::getValue('dlc')), true),
        "chrono_products" => json_decode(stripslashes(Tools::getValue('chrono_products')), true),
    );

    $freshOptions = [];
    foreach ($freshOptionsRaw as $option => $orderOptions) {
        foreach ($orderOptions as $orderId => $values) {
            if (!isset($freshOptions[$orderId])) {
                $freshOptions[$orderId] = [];
            }
            $freshOptions[$orderId][$option] = $values;
        }
    }
}

if (Tools::isEmpty('shared_secret') || Tools::getValue('shared_secret') != Configuration::get('CHRONOPOST_SECRET')) {
    $errors[] = 'Secret does not match.';
}

// Check dimensions (In case JS verification passed due to user modifications)
// MassExport is handled in AdminExportChronopostController
if (Tools::getIsset('orderid')) {
    $checkColis = json_decode(checkColis::check(
        Tools::getValue('orderid'),
        $dimensions[Tools::getValue('orderid')]['weights'],
        $dimensions[Tools::getValue('orderid')]['widths'],
        $dimensions[Tools::getValue('orderid')]['heights'],
        $dimensions[Tools::getValue('orderid')]['lengths'],
        $coef), true);

    if ($checkColis['error'] != 0) {
        $errors[] = 'Problème rencontré avec la dimensions d\'un ou plusieurs colis';
    }
}

$return = false;
$method = [];

$multi = [];
if (Tools::getIsset('multi')) {
    $multi = Tools::getValue('multi');
    $multi = json_decode(stripslashes($multi), true);
}

if (Tools::getIsset('orders')) {
    $orders = Tools::getValue('orders');
    $orders = explode(';', $orders);
    foreach ($orders as $order) {
        $coef = Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF');
        $checkColis = json_decode(
            checkColis::check(
                $order,
                $dimensions[$order]['weights'],
                $dimensions[$order]['widths'],
                $dimensions[$order]['heights'],
                $dimensions[$order]['lengths'],
                $coef
            ), true);

        if ($checkColis['error'] != 0) {
            $errors[] = 'Problème rencontré avec la dimension d\'un ou plusieurs colis';
        }
    }

    $shipSaturdays = json_decode(stripslashes(Tools::getValue('shipSaturdays')), true);
    foreach ($orders as $order) {
        if (!isset($shipSaturdays[$order][0])) {
            $shipSaturdays[$order][0] = 0;
        }
    }
} else {
    $orders = array(Tools::getValue('orderid'));
    if (Tools::getIsset('return')) {
        foreach ($orders as $order) {
            $coef = Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF');
            $checkColis = json_decode(
                checkColis::check(
                    $order,
                    $dimensions[$order]['weights'],
                    $dimensions[$order]['widths'],
                    $dimensions[$order]['heights'],
                    $dimensions[$order]['lengths'],
                    $coef
                ), true);

            if ($checkColis['error'] != 0) {
                $errors[] = 'Problème rencontré avec la dimension d\'un ou plusieurs colis';
            }

            $numberOfTracking = count(Chronopost::getAllTrackingNumbers($order));
            if ($numberOfTracking <= 0) {
                $errors[] = 'Impossible de créer une étiquette de retour avant d\'éditer celle de l\'aller';
            }

            if(Tools::getValue('return_method')) {
                $returnMethodContract = Tools::getValue('return_method_contract');
                if ($returnMethodContract) {
                    $accounts[$order] = $returnMethodContract;
                }
                $wsHelper = new webservicesHelper();
                $methodCode = Tools::getValue('return_method');
                $method = [
                    'product_code' => $methodCode,
                    'product_service' => 6
                ];
            }else {
                $method = false;
            }
        }

        $return = true;
    }

    if (Tools::getIsset('multiOne')) {
        $multi = array($orders[0] => Tools::getValue('multiOne'));
    }
}

if (Tools::getIsset('accounts')) {
    $accounts = stripslashes(Tools::getValue('accounts'));
    $accounts = json_decode($accounts, true);
}

// Test accounts
if ($accounts && is_array($accounts)) {
    foreach ($orders as $orderid) {
        if (!isset($accounts[$orderid])) {
            $errors[] = 'Erreur : veuillez configurer le module avant de procéder à l\'édition des étiquettes.';
        }

        // Check if we need multi accounts
        if (is_array($accounts[$orderid])) {
            $uniqueContracts = array_unique($accounts[$orderid]);
            if (count($uniqueContracts)) {
                $accounts[$orderid] = $uniqueContracts[0];
            }
        }

        // Multi contracts (ChronoFresh)
        if (is_array($accounts[$orderid])) {
            foreach ($accounts[$orderid] as $account) {
                $errors = $chronopostLT->checkAccount($account, $errors);
            }
        } else {
            $errors = $chronopostLT->checkAccount($accounts[$orderid], $errors);
        }
    }
}

// Check if product is available for the chosen contract
if (Tools::getIsset('orderid') && Tools::getIsset('orders')) {
    $wsHelper = new webservicesHelper();
    foreach ($orders as $order) {
        $order = new Order($order);
        $details = Chronopost::getSkybillDetails($order, Tools::getIsset('return'));
        $available_products = $wsHelper->getMethodsForContract($accounts[$order->id]);
        if (!in_array($details['productCode'], $available_products)) {
            $errors[] = 'Erreur : Le contrat sélectionné pour une ou plusieurs commandes ne disposent pas du transporteur.' .
                'Veuillez choisir un autre contrat pour imprimer l\'étiquette.';
            break;
        }
    }
}

// Verify expiration dates (can't be shorter than 3 days)
if ($freshOptions) {
    $today = new DateTime(date('Y-m-d 00:00:00'));
    $dateLimit = $today->modify('+3 days');
    foreach ($freshOptions as $orderId => $freshOption) {
        $order = new Order($orderId);
        $carrier = new Carrier($order->id_carrier);
        if (Chronofresh::isChronoFreshCarrier($carrier) || Chronofresh::isChronoFreshClassicCarrier($carrier)) {
            $dlc = new DateTime($freshOption['dlc']);
            if ($dlc < $dateLimit) {
                $errors[] = $module_instance->l('BBD error : the date must be in 3 days or more', 'postskybill');
            }
        }
    }
}

if (count($orders) === 0) {
    $errors[] = '<h1>Aucune commande sélectionnée</h1>';
}

if (count($errors) > 0) {
    $_SESSION['chronopost_errors'] = $errors;
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Refresh: 0; url=' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Refresh: 0; url=http://' . $_SERVER['HTTP_HOST']);
    }

    echo 'Une erreur est survenue, veuillez patienter pendant la redirection ...';
    exit;
}

require_once('libraries/PDFMerger.php');
@$pdf = new PDFMerger;

foreach ($orders as $orderid) {
    if (!is_array($accounts[$orderid])) {
        $accounts[$orderid] = array($accounts[$orderid]);
    }

    foreach ($accounts[$orderid] as $skybillAccount) {
        if (is_array($multi) && array_key_exists($orderid, $multi)) {
            $nb = $multi[$orderid];
        } else {
            $nb = 1;
        }

        $totalnb = $nb;
        if (!isset($shipSaturdays[$orderid])) {
            $shipSaturdays[$orderid][0] = 0; // Unticked
        }

        if ($totalnb > 1) {
            $ltInfo = $chronopostLT->createLTMultiColis(
                $orderid,
                $totalnb,
                Chronopost::getAccountInformationByAccountNumber($skybillAccount),
                $return,
                $dimensions[$orderid],
                $freshOptions,
                $shipSaturdays[$orderid]
            );
        } else {
            $ltInfo = $chronopostLT->createLT(
                $orderid,
                Chronopost::getAccountInformationByAccountNumber($skybillAccount),
                $return,
                $dimensions[$orderid],
                $freshOptions,
                $shipSaturdays[$orderid],
                $method
            );
        }

        $service = new ShippingServiceWSService();
        $params = new getReservedSkybillWithTypeAndMode();
        $params->reservationNumber = $ltInfo->reservationNumber;

        $reservedSkybillResult = webservicesHelper::getReservedSkybill($service, $ltInfo, $params);

        // Shipping service was failed. This usually means that the printing method is not valid.
        // Retry with PDF mode.
        if ($reservedSkybillResult->return->errorCode === 29) {
            $reservedSkybillResult = webservicesHelper::getReservedSkybill($service, $ltInfo, $params, 'PDF');
        }
        
        $lt = new stdClass();
        if ($reservedSkybillResult->return->errorCode === 0 && isset($reservedSkybillResult->return->skybill) && $reservedSkybillResult->return->skybill) {
            $lt->pdfEtiquette = base64_decode($reservedSkybillResult->return->skybill);
            if (is_array($ltInfo->resultParcelValue)) {
                $lt->skybillNumber = $ltInfo->resultParcelValue[0]->skybillNumber;
            } else {
                $lt->skybillNumber = $ltInfo->resultParcelValue->skybillNumber;
            }
        }
        
        if (!isset($lt->skybillNumber)) {
            $errors[] = 'Erreur : Impossible d\'imprimer l\'étiquette';
            $_SESSION['chronopost_errors'] = $errors;
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Refresh: 0; url=' . $_SERVER['HTTP_REFERER']);
            } else {
                header('Refresh: 0; url=http://' . $_SERVER['HTTP_HOST']);
            }
            echo 'Redirection ...';

            exit;
        }

        $file = 'skybills/' . $lt->skybillNumber . '.pdf';
        $fp = fopen($file, 'w');
        fwrite($fp, $lt->pdfEtiquette);
        fclose($fp);
        @$pdf->addPDF($file, 'all');
    }
}

try {
    if (isset($_SERVER['HTTP_REFERER']) && preg_match('#AdminOrders#', $_SERVER['HTTP_REFERER'])) {
        header('Refresh: 0; url=' . $_SERVER['HTTP_REFERER']);
    }

    $pdf->merge('download', 'Chronopost-LT-' . date('Ymd-Hi') . '.pdf');
} catch (Exception $e) {
    echo '<p>Le fichier généré est invalide.</p>';
    echo '<p>Vérifiez la configuration du module et que les commandes visées disposent d\'adresses de livraison 
valides.</p>';
}
