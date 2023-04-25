<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficielCarrierModuleFrontController extends ModuleFrontController
{
    /**
     * TNTOfficielCarrierModuleFrontController constructor.
     * Controller always used for AJAX response.
     */
    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        parent::__construct();

        // SSL
        $this->ssl = Tools::usingSecureMode();
        // No header/footer.
        $this->ajax = true;
    }

    /**
     * Get the delivery points popup via Ajax.
     * DROPOFFPOINT (Commerçants Partenaires) : XETT
     * DEPOT (Agences TNT) : PEX
     */
    public function displayAjaxBoxDeliveryPoints()
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objPSCart = $objContext->cart;

        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID((int)$objPSCart->id_carrier, false);
        if ($objTNTCarrierModel === null) {
            return '';
        }

        $strArgZipCode = trim(pSQL(Tools::getValue('tnt_postcode')));
        $strArgCity = trim(pSQL(Tools::getValue('tnt_city')));

        $objPSAddressDelivery = TNTOfficielReceiver::getPSAddress($objPSCart->id_address_delivery);
        // Default from delivery address.
        if (!$strArgZipCode && !$strArgCity && $objPSAddressDelivery !== null) {
            $strArgZipCode = trim($objPSAddressDelivery->postcode);
            $strArgCity = trim($objPSAddressDelivery->city);
        }

        $arrResultDeliveryPoints = $objTNTCarrierModel->getDeliveryPoints($strArgZipCode, $strArgCity);

        if ($arrResultDeliveryPoints === null) {
            return '';
        }

        // Get the relay points
        $this->context->smarty->assign(array(
            'carrier_type' => $objTNTCarrierModel->carrier_type,
            'current_postcode' => $arrResultDeliveryPoints['strZipCode'],
            'current_city' => $arrResultDeliveryPoints['strCity'],
            'arrRespositoryList' => $arrResultDeliveryPoints['arrPointsList'],
            'cities' => $arrResultDeliveryPoints['arrCitiesNameList'],
        ));

        echo $this->context->smarty->fetch(sprintf(
            'module:%s/views/templates/front/displayAjaxBoxDeliveryPoints.tpl',
            TNTOfficiel::MODULE_NAME
        ));

        return true;
    }

    /**
     * Save delivery point XETT or PEX info.
     */
    public function displayAjaxSaveProductInfo()
    {
        TNTOfficiel_Logstack::log();

        $strDeliveryPoint = (string)Tools::getValue('product');
        $strDeliveryPointJSON = TNTOfficiel_Tools::inflate($strDeliveryPoint);
        $arrDeliveryPoint = Tools::jsonDecode($strDeliveryPointJSON, true);

        // Check code exist.
        if (!array_key_exists('xett', $arrDeliveryPoint)
        &&  !array_key_exists('pex', $arrDeliveryPoint)
        ) {
            return false;
        }

        $objContext = $this->context;
        $objCart = $objContext->cart;
        $intCartID = (int)$objCart->id;

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID, true);
        if ($objTNTCartModel !== null) {
            $objTNTCartModel->setDeliveryPoint($arrDeliveryPoint);
        }

        $this->context->smarty->assign(array(
            'item' => $arrDeliveryPoint,
            'carrier_type' => isset($arrDeliveryPoint['xett']) ? 'DROPOFFPOINT' : 'DEPOT',
        ));

        echo $this->context->smarty->fetch(sprintf(
            'module:%s/views/templates/front/displayAjaxSaveProductInfo.tpl',
            TNTOfficiel::MODULE_NAME
        ));

        return true;
    }

    /**
     * Check TNT data before payment process.
     *
     * @return array
     */
    public function displayAjaxCheckPaymentReady()
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        $objCart = $objContext->cart;

        $intCartID = (int)$objCart->id;

        $arrResult = array(
            'error' => 'errorTechnical',
            'carrier' => null
        );

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID, true);
        if ($objTNTCartModel !== null) {
            $arrResult = $objTNTCartModel->isPaymentReady();
        }

        echo Tools::jsonEncode($arrResult);

        return true;
    }
}
