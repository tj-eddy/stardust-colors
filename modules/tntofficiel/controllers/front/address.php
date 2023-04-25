<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficielAddressModuleFrontController extends ModuleFrontController
{
    /**
     * TNTOfficielAddressModuleFrontController constructor.
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
     * Store Extra Information of Receiver Delivery Address (FO).
     *
     * @return string
     */
    public function displayAjaxStoreReceiverInfo()
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objCart = $objContext->cart;

        // Load TNT receiver info or create a new one for it's ID.
        $objTNTReceiverModel = TNTOfficielReceiver::loadAddressID($objCart->id_address_delivery);
        // Validate and store receiver info, using form values.
        $arrFormReceiverInfoValidate = $objTNTReceiverModel->storeReceiverInfo(
            (string)Tools::getValue('receiver_email'),
            (string)Tools::getValue('receiver_mobile'),
            (string)Tools::getValue('receiver_building'),
            (string)Tools::getValue('receiver_accesscode'),
            (string)Tools::getValue('receiver_floor'),
            (string)Tools::getValue('receiver_instructions')
        );

        echo Tools::jsonEncode($arrFormReceiverInfoValidate);

        return true;
    }

    /**
     * Check if the city match the postcode.
     *
     * @return string
     */
    public function displayAjaxCheckPostcodeCity()
    {
        TNTOfficiel_Logstack::log();

        $arrResult = array(
            'required' => false,
            'postcode' => false,
            'cities' => false,
        );

        // Check the country
        $intCountryID = (int)Tools::getValue('countryId');
        $strCountryISO = Country::getIsoById($intCountryID);
        $strZipCode = pSQL(Tools::getValue('postcode'));
        $strCity = pSQL(Tools::getValue('city'));

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();
        // If no account available for this context.
        if ($objTNTContextAccountModel === null) {
            return true;
        }

        if ($strCountryISO === 'FR') {
            // Check is required for France.
            $arrResult['required'] = true;
            // Check the city/postcode.
            $arrResultCitiesGuide = $objTNTContextAccountModel->citiesGuide($strCountryISO, $strZipCode, $strCity);
            // PostCode is well formated NNNNN
            if ($arrResultCitiesGuide['strZipCode'] !== null) {
                // If city/postcode correct.
                // If communication error, TNT carrier are not available,
                // but postcode/city is considered wrong and then show error "unknow postcode" on Front-Office checkout.
                // Also, return true to prevent always invalid address form.
                if ($arrResultCitiesGuide['boolIsRequestComError'] || $arrResultCitiesGuide['boolIsCityNameValid']) {
                    $arrResult['postcode'] = true;
                    $arrResult['cities'] = true;
                } else {
                    // Get cities from the webservice from the given postal code.
                    if (count($arrResultCitiesGuide['arrCitiesNameList']) > 0) {
                        $arrResult['postcode'] = true;
                    }

                    $arrResult['cities'] = $arrResultCitiesGuide['arrCitiesNameList'];
                }
            }
        }

        echo Tools::jsonEncode($arrResult);

        return true;
    }

    /**
     * Get cities for a postcode.
     *
     * @return string
     */
    public function displayAjaxGetCities()
    {
        TNTOfficiel_Logstack::log();

        $objCart = $this->context->cart;

        $arrResult = array(
            'valid' => true,
            'cities' => array(),
            'postcode' => false,
        );

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();
        // If account available for this context.
        if ($objTNTContextAccountModel !== null) {
            $objPSAddressDelivery = TNTOfficielReceiver::getPSAddress($objCart->id_address_delivery);
            // If delivery address object is available.
            if ($objPSAddressDelivery !== null) {
                // Check the city/postcode.
                $arrResultCitiesGuide = $objTNTContextAccountModel->citiesGuide(
                    Country::getIsoById($objPSAddressDelivery->id_country),
                    $objPSAddressDelivery->postcode,
                    $objPSAddressDelivery->city
                );

                $arrResult = array(
                    // Is current ZipCode/CityName Valid for FR (else valid) ?
                    // Unsupported country or communication error is considered true to prevent always
                    // invalid address form and show error "unknow postcode" on Front-Office checkout.
                    'valid' => (!$arrResultCitiesGuide['boolIsCountrySupported']
                        || $arrResultCitiesGuide['boolIsRequestComError']
                        || $arrResultCitiesGuide['boolIsCityNameValid']
                    ),
                    // Cities name list available for current ZipCode.
                    'cities' => $arrResultCitiesGuide['arrCitiesNameList'],
                    // Current ZipCode.
                    'postcode' => $arrResultCitiesGuide['strZipCode']
                );
            }
        }

        echo Tools::jsonEncode($arrResult);

        return true;
    }

    /**
     * Update the city for the current delivery address.
     *
     * @return array
     */
    public function displayAjaxUpdateDeliveryAddress()
    {
        TNTOfficiel_Logstack::log();

        $objCart = $this->context->cart;

        $strCity = trim(pSQL(Tools::getValue('city')));

        $objPSAddressDelivery = TNTOfficielReceiver::getPSAddress($objCart->id_address_delivery);

        $boolResult = false;

        if ($strCity && $objPSAddressDelivery !== null) {
            $objPSAddressDelivery->city = $strCity;
            $boolResult = $objPSAddressDelivery->save();
        }

        echo Tools::jsonEncode(array(
            'result' => $boolResult
        ));

        return true;
    }
}
