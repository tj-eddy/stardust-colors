<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_ManifestPDFCreator
{
    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        // Required for TNTOfficiel_ManifestPDFCreator->createManifest() method.
        // class TNTOfficiel_ManifestPDF extends PDF
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/pdf/manifest/TNTOfficiel_ManifestPDF.php';
        // Require by TNTOfficiel_ManifestPDF.
        // Class loaded here to prevent any conflict with global K_TCPDF_CALLS_IN_HTML constant and others modules.
        // /vendor/tecnickcom/tcpdf/tcpdf_autoconfig.php
        // /vendor/tecnickcom/tcpdf/config/tcpdf_config.php
        // class TNTOfficiel_ManifestPDFGenerator extends PDFGenerator
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/pdf/manifest/TNTOfficiel_ManifestPDFGenerator.php';
        // Required by TNTOfficiel_ManifestPDFGenerator and his parent PDFGenerator::__construct().
        // which later load class HTMLTemplate<NAME> using name 'TNTOfficielManifest'.
        // class HTMLTemplateTNTOfficielManifest extends HTMLTemplate
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/pdf/manifest/HTMLTemplateTNTOfficielManifest.php';
    }

    /**
     * Create manifest from an order id list.
     *
     * @param array $arrArgOrderIDList
     */
    public function createManifest(array $arrArgOrderIDList)
    {
        TNTOfficiel_Logstack::log();

        $arrManifesDataList = array();

        foreach ($arrArgOrderIDList as $intOrderID) {
            $intOrderID = (int)$intOrderID;
            $objPSOrder = new Order($intOrderID);
            // Load TNT order info for it's ID.
            $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
            if ($objTNTOrderModel === null) {
                continue;
            }

            // Load an existing TNT carrier.
            $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($objPSOrder->id_carrier, false);
            // If fail or carrier is not from TNT module.
            if ($objTNTCarrierModel === null) {
                continue;
            }

            $objTNTCarrierAccountModel = $objTNTCarrierModel->getTNTAccountModel();
            // If no account available for this carrier.
            if ($objTNTCarrierAccountModel === null) {
                continue;
            }

            $intShopID = (int)$objTNTCarrierModel->id_shop;
            $objShop = new Shop($intShopID);
            // If fail.
            if (!Validate::isLoadedObject($objShop)
                || (int)$objShop->id !== $intShopID
            ) {
                continue;
            }

            // Doc per Account ID (shop)
            $strPageKey = '_'.$intShopID.'_'.$objTNTCarrierAccountModel->id;
            // Doc per Order
            //$strPageKey = '_'.$intOrderID;


            // Set shop context to get the right configuration.
            Shop::setContext(Shop::CONTEXT_SHOP, $intShopID);
/*
            $strShopCompanyName = $objShop->name;
            if ($strShopCompanyName && $strShopCompanyName !== Configuration::get('PS_SHOP_NAME')) {
                $strShopCompanyName .= (', '.Configuration::get('PS_SHOP_NAME'));
            }
*/
            if (!array_key_exists($strPageKey, $arrManifesDataList)) {
                $arrManifesDataList[$strPageKey] = array();
            }
            if (!array_key_exists('carrierAccount', $arrManifesDataList[$strPageKey])) {
                $arrManifesDataList[$strPageKey]['carrierAccount'] = $objTNTCarrierAccountModel->account_number;
            }
            if (!array_key_exists('address', $arrManifesDataList[$strPageKey])) {
                $intShopCountryID = Configuration::get('PS_SHOP_COUNTRY_ID')
                    ? Configuration::get('PS_SHOP_COUNTRY_ID')
                    : Configuration::get('PS_COUNTRY_DEFAULT')
                ;
                $arrManifesDataList[$strPageKey]['address'] = array(
                    'name' => $objTNTCarrierAccountModel->sender_company, //$strShopCompanyName,
                    'address1' => $objTNTCarrierAccountModel->sender_address1, //Configuration::get('PS_SHOP_ADDR1'),
                    'address2' => $objTNTCarrierAccountModel->sender_address2, //Configuration::get('PS_SHOP_ADDR2'),
                    'postcode' => trim($objTNTCarrierAccountModel->sender_zipcode), //Configuration::get('PS_SHOP_CODE'),
                    'city' => trim($objTNTCarrierAccountModel->sender_city), //Configuration::get('PS_SHOP_CITY'),
                    'country' => Country::getIsoById($intShopCountryID), //PS_SHOP_COUNTRY
                );
            }
            if (!array_key_exists('arrParcelInfoList', $arrManifesDataList[$strPageKey])) {
                $arrManifesDataList[$strPageKey]['arrParcelInfoList'] = array();
            }
            if (!array_key_exists('totalWeight', $arrManifesDataList[$strPageKey])) {
                $arrManifesDataList[$strPageKey]['totalWeight'] = (float)0;
            }
            if (!array_key_exists('parcelsNumber', $arrManifesDataList[$strPageKey])) {
                $arrManifesDataList[$strPageKey]['parcelsNumber'] = 0;
            }

            $objPSAddressDelivery = $objTNTOrderModel->getPSAddressDelivery();

            // Get the parcels.
            $arrObjTNTParcelModelList = $objTNTOrderModel->getTNTParcelModelList();
            foreach ($arrObjTNTParcelModelList as $objTNTParcelModel) {
                // Add weight for the parcels.
                $arrManifesDataList[$strPageKey]['totalWeight'] += $objTNTParcelModel->weight;
                $arrManifesDataList[$strPageKey]['parcelsNumber']++;
                $arrManifesDataList[$strPageKey]['arrParcelInfoList'][] = array(
                    'objTNTParcelModel' => $objTNTParcelModel,
                    'objPSAddressDelivery' => $objPSAddressDelivery,
                    'strCarrierLabel' => $objTNTCarrierModel->getCarrierInfos()->label
                );
            }
        }

        $objPDFMerger = new TNTOfficiel_PDFMerger();
        $intManifestCounter = 0;
        $strOutputFileName = 'manifest_list.pdf';

        foreach ($arrManifesDataList as $arrManifesData) {
            $objManifestPDF = new TNTOfficiel_ManifestPDF(
                array('manifestData' => $arrManifesData),
                'TNTOfficielManifest',
                Context::getContext()->smarty
            );

            ++$intManifestCounter;
            $objPDFMerger->addPDF('manifest_'.$intManifestCounter.'.pdf', 'all', $objManifestPDF->render(false));
        }

        // Concat.
        if ($intManifestCounter > 0) {
            // Download and exit.
            TNTOfficiel_Tools::download(
                $strOutputFileName,
                $objPDFMerger->merge('string', $strOutputFileName),
                'application/pdf'
            );
        }

    }

}
