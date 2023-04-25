<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';


/**
 * Class TNTOfficiel.
 */
class TNTOfficiel extends CarrierModule
{
    // Name identifier.
    const MODULE_NAME = 'tntofficiel';
    // Release stamp : (((+new Date('YYYY-MM-DD HH:MM'))/1000)|0).toString(36)
    const MODULE_RELEASE = 'qtzrs0';

    // Carrier name.
    const CARRIER_NAME = 'TNT';

    // Google Map API Version (google.maps.version).
    const GMAP_API_VER = '3.exp';

    /**
     * Request timeout.
     */

    // Timeout for connection to the server.
    const REQUEST_CONNECTTIMEOUT = 8;
    // Timeout global (expiration).
    const REQUEST_TIMEOUT = 32;

    /**
     * Reserved by Cart Model.
     * @var int|null Carrier ID set when retrieving shipping cost from module.
     * see getOrderShippingCost()
     */
    public $id_carrier = null;

    /** @var array[int] order ID list where shipment label was requested */
    public $arrRequestedSaveShipment = array();


    /**
     * TNTOfficiel constructor.
     */
    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        // Module is compliant with bootstrap. PS1.6+
        $this->bootstrap = true;

        // Version.
        $this->version = '1.0.12';
        // Prestashop supported version. PS1.7.0.5+
        $this->ps_versions_compliancy = array('min' => '1.7.0.5', 'max' => '1.7.99.99');
        // Prestashop modules dependencies.
        $this->dependencies = array();

        // Name.
        $this->name = 'tntofficiel'; // TNTOfficiel::MODULE_NAME;
        // Displayed Name.
        $this->displayName = $this->l('TNT'); // TNTOfficiel::CARRIER_NAME;
        // Description.
        $this->description = $this->l('Offer your customers, different delivery methods with TNT');

        // Type.
        $this->tab = 'shipping_logistics';

        // Confirmation message before uninstall.
        $this->confirmUninstall = $this->l('Are you sure you want to delete this module?');

        // Author.
        $this->author = 'Inetum';

        // Module key provided by addons.prestashop.com.
        $this->module_key = '1cf0bbdc13a4d4f319266cfe0bfac777';

        // Is this instance required on module when it is displayed in the module list.
        // This can be useful if the module has to perform checks on the PrestaShop configuration.
        $this->need_instance = 0;

        // Module Constructor.
        parent::__construct();

        /*
         * Display error or warning message in the module list.
         */

        if (!extension_loaded('curl')) {
            $this->displayAdminError(
                sprintf($this->l('You have to enable the PHP %s extension on your server.'), 'cURL'),
                null,
                array('adminaccountsettingcontroller')
            );
        }
        if (!extension_loaded('soap')) {
            $this->displayAdminError(
                sprintf($this->l('You have to enable the PHP %s extension on your server.'), 'SOAP'),
                null,
                array('adminaccountsettingcontroller')
            );
        }
        if (!extension_loaded('zip')) {
            $this->displayAdminWarning(
                sprintf($this->l('You have to enable the PHP %s extension on your server.'), 'Zip'),
                null,
                array('adminaccountsettingcontroller')
            );
        }

        // Check tntofficiel release version.
        if (TNTOfficiel::isDownGraded()) {
            $this->displayAdminError(
                $this->l('Update Required : Previously installed version is greater than the current one.'),
                null,
                array('adminaccountsettingcontroller')
            );
        }

        // If module not ready.
        if (!TNTOfficiel::isContextReady()) {
            // Do nothing.
            return;
        }

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();
        // If no account available for this context.
        if ($objTNTContextAccountModel === null) {
            // Do nothing.
            return;
        }

        // If credential validated.
        if ($objTNTContextAccountModel->getAuthValidatedDateTime() !== null) {
            // Check each days state for auto invalidation (e.g: password changed).
            // If invalidated, module is disabled and carrier are not displayed on front-office.
            $objTNTContextAccountModel->updateAuthValidation(60 * 60 * 24);
        }

        // Apply default carriers values if required.
        TNTOfficielCarrier::forceAllCarrierDefaultValues();
    }

    /**
     * Get HTML text with optional link.
     *
     * @param string $strArgMessage
     * @param array  $arrArgAttr
     * @param string $strArgName
     *
     * @return mixed
     */
    public function getTextLink($strArgMessage, $arrArgAttr = array(), $strArgName = null)
    {
        $this->smarty->assign(array(
            'strName' => $strArgName,
            'strMessage' => $strArgMessage,
            'arrAttr' => $arrArgAttr,
        ));

        return $this->display(__FILE__, 'views/templates/front/fragment/textLink.tpl');
    }

    /**
     * Get a message for admin controller.
     *
     * @param string $strArgMessage
     * @param string $strArgURL
     * @param array $arrArgControllers
     *
     * todo : add shop/group context filter
     *
     * @return bool
     */
    public function getAdminMessage($strArgMessage, $strArgURL = null, $arrArgControllers = array())
    {
        $objContext = $this->context;

        if (!property_exists($objContext, 'controller')) {
            return false;
        }

        // Controller.
        $objAdminController = $objContext->controller;

        // If not an AdminController or is an AJAX request.
        if (!($objAdminController instanceof AdminController) || $objAdminController->ajax) {
            return false;
        }

        // Get Controller Name.
        $strCurrentControllerName = TNTOfficiel_Tools::getControllerName($objAdminController);

        // If controller filter list exist but not in list.
        if (!is_array($arrArgControllers)
            || (count($arrArgControllers) > 0 && !in_array($strCurrentControllerName, $arrArgControllers))
        ) {
            return false;
        }

        if (!is_string($strArgMessage) || Tools::strlen($strArgMessage) === 0) {
            return false;
        }

        if (!is_string($strArgURL)) {
            $strArgURL = null;
        }

        return $this->getTextLink($strArgMessage, array('href' => $strArgURL), TNTOfficiel::CARRIER_NAME);
    }

    /**
     * Display a warning for admin controller.
     *
     * @param string $strArgMessage
     * @param string $strArgURL
     * @param array $arrArgControllers
     *
     * @return bool
     */
    public function displayAdminWarning($strArgMessage, $strArgURL = null, $arrArgControllers = array())
    {
        $strArgMessage = $this->getAdminMessage($strArgMessage, $strArgURL, $arrArgControllers);

        if (is_string($strArgMessage)) {
            $this->context->controller->warnings[] = $strArgMessage;
        }

        return $strArgMessage;
    }

    /**
     * Display a error for admin controller.
     *
     * @param string $strArgMessage
     * @param string $strArgURL
     * @param array $arrArgControllers
     *
     * @return bool
     */
    public function displayAdminError($strArgMessage, $strArgURL = null, $arrArgControllers = array())
    {
        $strArgMessage = $this->getAdminMessage($strArgMessage, $strArgURL, $arrArgControllers);

        if (is_string($strArgMessage)) {
            $this->context->controller->errors[] = $strArgMessage;
        }

        return $strArgMessage;
    }

    /**
     * Module install.
     *
     * @return bool
     */
    public function install()
    {
        TNTOfficiel_Logstack::log();

        // If MultiShop and more than 1 Shop.
        if (Shop::isFeatureActive()) {
            // Define Shop context to all Shops.
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        TNTOfficiel_Logger::logInstall(sprintf(
            $this->l('__ %s [%s] v%s : Install begins __'),
            TNTOfficiel::CARRIER_NAME,
            TNTOfficiel::MODULE_NAME,
            $this->version
        ));

        // Check tntofficiel release version.
        if (TNTOfficiel::isDownGraded()) {
            $strMessage =
                $this->l('Downgrade not allowed : Previously installed version is greater than the current one.');
            TNTOfficiel_Logger::logInstall($strMessage, false);
            $this->_errors[] = $strMessage;

            // Do not install.
            return false;
        }

        // Check compatibility.
        if (version_compare(_PS_VERSION_, $this->ps_versions_compliancy['min'], '<')) {
            $strMessage = sprintf(
                $this->l('Prestashop %s or higher is required.'),
                $this->ps_versions_compliancy['min']
            );
            TNTOfficiel_Logger::logInstall($strMessage, false);
            $this->_errors[] = $strMessage;

            return false;
        }
        if (!extension_loaded('curl')) {
            $strMessage = sprintf($this->l('You have to enable the PHP %s extension on your server.'), 'cURL');
            TNTOfficiel_Logger::logInstall($strMessage, false);
            $this->_errors[] = $strMessage;

            return false;
        }
        if (!extension_loaded('soap')) {
            $strMessage = sprintf($this->l('You have to enable the PHP %s extension on your server.'), 'SOAP');
            TNTOfficiel_Logger::logInstall($strMessage, false);
            $this->_errors[] = $strMessage;

            return false;
        }
        if (!extension_loaded('zip')) {
            $strMessage = sprintf($this->l('You have to enable the PHP %s extension on your server.'), 'Zip');
            TNTOfficiel_Logger::logInstall($strMessage, false);
            $this->_errors[] = $strMessage;

            return false;
        }

        // Store release.
        Configuration::updateGlobalValue('TNTOFFICIEL_RELEASE', TNTOfficiel::MODULE_RELEASE);

        // Remove deprecated files.
        TNTOfficiel_Install::uninstallDeprecatedFiles();

        // Prestashop install.
        if (!parent::install()) {
            TNTOfficiel_Logger::logInstall('Module::install', false);
            $this->_errors[] = $this->l('Unable to install Module::install().');

            return false;
        }
        TNTOfficiel_Logger::logInstall('Module::install');

        // Update settings.
        if (!TNTOfficiel_Install::updateSettings()) {
            $this->_errors[] = $this->l('Unable to define configuration.');

            return false;
        }

        // Register hooks.
        foreach (TNTOfficiel_Install::$arrHookList as $strHookName) {
            if (!$this->registerHook($strHookName)) {
                TNTOfficiel_Logger::logInstall('Module::registerHook ('.$strHookName.')', false);
                $this->_errors[] = sprintf($this->l('Unable to register hook "%s".'), $strHookName);

                return false;
            }
        }
        TNTOfficiel_Logger::logInstall('Module::registerHook');

        // Create the TNT OrderStates.
        if (!TNTOfficiel_Install::createOrderStates()) {
            $this->_errors[] = $this->l('Unable to add order states.');

            return false;
        }

        // Create the TNT tab.
        if (!TNTOfficiel_Install::createTab()) {
            $this->_errors[] = $this->l('Unable to add menu tab.');

            return false;
        }

        // Create the tables.
        if (!TNTOfficiel_Install::createTables()) {
            $this->_errors[] = $this->l('Unable to create tables in database.');

            return false;
        }

        // Clear cache.
        TNTOfficiel_Install::clearCache();

        TNTOfficiel_Logger::logInstall(sprintf(
            $this->l('__ %s [%s] v%s : Install complete __'),
            TNTOfficiel::CARRIER_NAME,
            TNTOfficiel::MODULE_NAME,
            $this->version
        ));

        return true;
    }

    /**
     * Module uninstall.
     *
     * @return bool
     */
    public function uninstall()
    {
        TNTOfficiel_Logstack::log();

        TNTOfficiel_Logger::logUninstall(sprintf(
            '__ %s [%s] v%s : Uninstall init __',
            TNTOfficiel::CARRIER_NAME,
            TNTOfficiel::MODULE_NAME,
            $this->version
        ));

        // Delete Tab.
        if (!TNTOfficiel_Install::deleteTab()) {
            $this->_errors[] = $this->l('Unable to delete menu tab.');

            return false;
        }

        // Delete Settings.
        if (!TNTOfficiel_Install::deleteSettings()) {
            $this->_errors[] = $this->l('Unable to delete configuration.');

            return false;
        }

        // Prestashop Uninstall : Uninstall class or controllers override, Unregister Hooks, etc.
        if (!parent::uninstall()) {
            TNTOfficiel_Logger::logUninstall('Module::uninstall', false);
            $this->_errors[] = $this->l('Unable to uninstall Parent::uninstall().');

            return false;
        }
        TNTOfficiel_Logger::logUninstall('Module::uninstall');

        TNTOfficiel_Logger::logUninstall(sprintf(
            '__ %s [%s] v%s : Uninstall complete __',
            TNTOfficiel::CARRIER_NAME,
            TNTOfficiel::MODULE_NAME,
            $this->version
        ));

        // TODO: check default carrier is not TNT.
        // Configuration::get('PS_CARRIER_DEFAULT')

        return true;
    }

    /**
     * Module configuration page content.
     * Large form is displayed in a custom admin controller.
     *
     * @return string HTML content.
     */
    public function getContent()
    {
        TNTOfficiel_Logstack::log();

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminAccountSetting'));

        return '';
    }

    /**
     * Is current release older than the previously installed.
     */
    public static function isDownGraded()
    {
        // Check tntofficiel release version.
        $strRLPrevious = (string)Configuration::get('TNTOFFICIEL_RELEASE');
        $intTSPrevious = (int)base_convert($strRLPrevious, 36, 10);
        $intTSCurrent = base_convert(TNTOfficiel::MODULE_RELEASE, 36, 10);

        return ($intTSCurrent < $intTSPrevious);
    }

    /**
     * Is module ready for current context.
     */
    public static function isContextReady()
    {
        TNTOfficiel_Logstack::log();

        $objContext = Context::getContext();
        if (property_exists($objContext, 'controller')) {
            // Controller.
            $objController = $objContext->controller;
            if ($objController !== null) {
                // Get Controller Name.
                $strCurrentControllerName = TNTOfficiel_Tools::getControllerName($objController);

                switch ($strCurrentControllerName) {
                    // Prevent extra processing (ex: .map file not found).
                    case 'pagenotfoundcontroller':
                    case 'adminnotfoundcontroller':
                        return false;
                    // Prevent extra processing.
                    case 'adminajaxfaviconbocontroller':
                        return false;
                    default:
                        break;
                }
            }
        }

        // If module not installed (ps_module:id_module) $this->id > 0
        // or module not activated (ps_module:active) $this->active ps_module_shop
        // or module is downgraded.
        if (!Module::isInstalled(TNTOfficiel::MODULE_NAME)
            || !Module::isEnabled(TNTOfficiel::MODULE_NAME)
            || TNTOfficiel::isDownGraded()
        ) {
            return false;
        }

        // Check that tables and columns exist.
        if (!TNTOfficiel_Install::checkTables()) {
            return false;
        }

        return true;
    }

    /**
     * Add JS.
     *
     * @param string $strArgFile
     *
     * @return mixed
     */
    public function addJS($strArgFile)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        // Controller.
        $objController = $objContext->controller;

        $strViews = implode(DIRECTORY_SEPARATOR, array('views','js',TNTOfficiel::MODULE_RELEASE)).DIRECTORY_SEPARATOR;

        $strAssetJSAbsPath = $this->getLocalPath().$strViews;
        $strAssetJSPath = $this->getPathUri().$strViews;

        // If an AdminController.
        if ($objController instanceof AdminController) {
            $strFile = $strArgFile;
            if (file_exists($strAssetJSAbsPath.$strArgFile)) {
                $strFile = $strAssetJSPath.$strArgFile;
            }

            return $objController->addJS($strFile);
        }

        $strFile = $strArgFile;
        if (file_exists($strAssetJSAbsPath.$strArgFile)) {
            $strFile = $strAssetJSPath.$strArgFile;
        }

        return $objController->registerJavascript(
            sha1($strFile),
            $strFile,
            array('position' => 'bottom', 'priority' => 80)
        );
    }

    /**
     * Add CSS.
     *
     * @param string $strArgFile
     * @param string $strArgCSSMediaType
     *
     * @return mixed
     */
    public function addCSS($strArgFile, $strArgCSSMediaType = 'all')
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        // Controller.
        $objController = $objContext->controller;

        $strViews = implode(DIRECTORY_SEPARATOR, array('views','css',TNTOfficiel::MODULE_RELEASE)).DIRECTORY_SEPARATOR;

        $strAssetCSSAbsPath = $this->getLocalPath().$strViews;
        $strAssetCSSPath = $this->getPathUri().$strViews;

        // If an AdminController.
        if ($objController instanceof AdminController) {
            $strFile = $strArgFile;
            if (file_exists($strAssetCSSAbsPath.$strArgFile)) {
                $strFile = $strAssetCSSPath.$strArgFile;
            }

            return $objController->addCSS($strFile);
        }

        $strFile = $strArgFile;
        if (file_exists($strAssetCSSAbsPath.$strArgFile)) {
            $strFile = $strAssetCSSPath.$strArgFile;
        }

        return $objController->registerStylesheet(
            sha1($strFile),
            $strFile,
            array('media' => $strArgCSSMediaType, 'priority' => 80)
        );
    }

    /**
     * @return array
     */
    public function getCommonVariable()
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objLink = $objContext->link;
        $objShop = $objContext->shop;

        // Controller.
        $objController = $objContext->controller;
        // Get Controller Name.
        $strCurrentControllerName = TNTOfficiel_Tools::getControllerName($objController);

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        $boolContextAuth = false;
        $strAPIGoogleMapKey = '';
        if ($objTNTContextAccountModel !== null) {
            $boolContextAuth = $objTNTContextAccountModel->getAuthValidatedDateTime() !== null;
            $strAPIGoogleMapKey = $objTNTContextAccountModel->api_google_map_key;
        }

        $arrCarrierList = array();
        $arrObjTNTCarrierModelList = TNTOfficielCarrier::getContextCarrierModelList();
        foreach ($arrObjTNTCarrierModelList as $intTNTCarrierID => $objTNTCarrierModel) {
            $arrCarrierList[$intTNTCarrierID] = array(
                'account_type' => $objTNTCarrierModel->account_type,
                'carrier_type' => $objTNTCarrierModel->carrier_type
            );
        }

        if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
            $arrCountryList = Carrier::getDeliveredCountries($this->context->language->id, true, true);
        } else {
            $arrCountryList = Country::getCountries($this->context->language->id, true);
        }


        // Javascript config.
        $arrTNTOfficiel = array(
            'timestamp' => microtime(true) * 1000,
            'module' => array(
                'name' => TNTOfficiel::MODULE_NAME,
                'title' => TNTOfficiel::CARRIER_NAME,
                'version' => $this->version,
                'context' => $boolContextAuth,
                'ready' => TNTOfficiel::isContextReady()
            ),
            'config' => array(
                'google' => array(
                    'map' => array(
                        'url' => 'https://maps.googleapis.com/maps/api/js',
                        'data' => array(
                            'v' => TNTOfficiel::GMAP_API_VER,
                            'key' => $strAPIGoogleMapKey
                        ),
                        'default' => array(
                            "lat"  => 46.827742,
                            "lng"  => 2.835644,
                            "zoom" => 6
                        )
                    )
                )
            ),
            'translate' => array(
                'validateDeliveryAddress' => htmlentities($this->l('Validate your delivery address')),
                'unknownPostalCode' => htmlentities($this->l('Unknown postal code')),
                'validatePostalCodeDeliveryAddress' => htmlentities(
                    $this->l('Please edit and validate the postal code of your delivery address.')
                ),
                'unrecognizedCity' => htmlentities($this->l('Unrecognized city')),
                'selectCityDeliveryAddress' => htmlentities(
                    $this->l('Please select the city from your delivery address.')
                ),
                'postalCode' => htmlentities($this->l('Postal code')),
                'city' => htmlentities($this->l('City')),
                'validate' => htmlentities($this->l('Validate')),
                'validateAdditionalCarrierInfo' => htmlentities(
                    $this->l('Please confirm the form with additional information for the carrier.')
                ),
                'errorDownloadingHRA' => htmlentities(
                    $this->l('Error while downloading the HRA list. Please contact the support.')
                ),
                'errorInvalidPhoneNumber' => htmlentities($this->l('The phone number must be 10 digits')),
                'errorInvalidEMail' => htmlentities($this->l('The email is invalid')),
                'errorNoDeliveryOptionSelected' => htmlentities($this->l('No delivery options selected.')),
                'errorNoDeliveryAddressSelected' => htmlentities($this->l('No delivery address selected.')),
                'errorNoDeliveryPointSelected' => htmlentities($this->l('No delivery point selected.')),
                'errorUnknow' => htmlentities($this->l('An error has occurred.')),
                'errorTechnical' => htmlentities($this->l('A technical error occurred.')),
                'errorConnection' => htmlentities($this->l('A connection error occurred.'))
            ),
            'link' => array(
                'controller' => $strCurrentControllerName,
                'front' => array(
                    'shop' => $objShop->getBaseURL(true),
                    'module' => array(
                        'boxDeliveryPoints' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'carrier',
                            array('action' => 'boxDeliveryPoints'),
                            true
                        ),
                        'saveProductInfo' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'carrier',
                            array('action' => 'saveProductInfo'),
                            true
                        ),
                        'checkPaymentReady' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'carrier',
                            array('action' => 'checkPaymentReady'),
                            true
                        ),
                        'storeReceiverInfo' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'address',
                            array('action' => 'storeReceiverInfo'),
                            true
                        ),
                        'getAddressCities' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'address',
                            array('action' => 'getCities'),
                            true
                        ),
                        'updateAddressDelivery' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'address',
                            array('action' => 'updateDeliveryAddress'),
                            true
                        ),
                        'checkAddressPostcodeCity' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'address',
                            array('action' => 'checkPostcodeCity'),
                            true
                        )
                    ),
                    'page' => array(
                        'order' => $objLink->getPageLink('order', true)
                    )
                ),
                'back' => null,
                'image' => _MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/views/img/'
            ),
            'country' => array(
                'list' => $arrCountryList
            ),
            'carrier' => array(
                'list' => $arrCarrierList
            ),
            'cart' => array(
                'isCarrierListDisplay' => false
            ),
            'order' => array(
                'isTNT' => false
            )
        );

        return $arrTNTOfficiel;
    }

    /**
     * HOOK (AKA backOfficeHeader) called inside the head tag.
     * Ideal location for adding JavaScript and CSS files.
     * Hook called even if module is disabled !
     *
     * @param array $arrArgHookParams
     *
     * @return string HTML content in head tag.
     */
    public function hookDisplayBackOfficeHeader($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        $objHookCookie = $arrArgHookParams['cookie'];

        // Controller.
        $objAdminController = $objContext->controller;
        // Get Controller Name.
        $strCurrentControllerName = TNTOfficiel_Tools::getControllerName($objAdminController);

        // Update All Parcels and OrderState accordingly.
        TNTOfficielOrder::updateAllOrderStateDeliveredParcels();

        // Global Admin CSS.
        $this->addCSS('Admin.css', 'all');

        TNTOfficiel_Logstack::dump(array(
            'method' => sprintf('%s::%s', __CLASS__, __FUNCTION__),
            'ajax' => $objAdminController->ajax,
            'controller_type' => $objAdminController->controller_type,
            'controllername' => $strCurrentControllerName,
            'controllerfilename' => Dispatcher::getInstance()->getController()
        ));

        // Display nothing.
        return '';
    }

    /**
     * HOOK called to include CSS or JS files in the Back-Office header.
     *
     * @param array $arrArgHookParams
     */
    public function hookActionAdminControllerSetMedia($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        $objHookCookie = $arrArgHookParams['cookie'];

        // Controller.
        $objAdminController = $objContext->controller;
        // Get Controller Name.
        $strCurrentControllerName = TNTOfficiel_Tools::getControllerName($objAdminController);

        $strAssetJSPath = $this->getPathUri().'views/js/'.TNTOfficiel::MODULE_RELEASE.'/';

        $this->addCSS('global.css', 'all');
        $this->addJS('global.js');

        // Global Admin CSS.
        $this->addCSS('Admin.css', 'all');

        switch ($strCurrentControllerName) {
            case 'adminorderscontroller':
                if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
                    // Form.css required for address-city-check, ExtraData
                    $this->addCSS('form.css', 'all');
                    //
                    $this->addCSS('carrier.css', 'all');

                    // DatePicker.
                    $objAdminController->addJqueryUI('ui.datepicker');

                    // FancyBox required to display form (cp/ville check).
                    $objAdminController->addJqueryPlugin('fancybox');
                    $this->addJS('address.js');

                    // TNTOfficiel_inflate() TNTOfficiel_deflate(), required by carrierDeliveryPoint.js
                    $this->addJS('lib/string.js');
                    // jQuery.fn.nanoScroller, required by carrierDeliveryPoint.js
                    $this->addJS('lib/nanoscroller/jquery.nanoscroller.min.js');
                    $this->addCSS($strAssetJSPath.'lib/nanoscroller/nanoscroller.css', 'all');

                    $this->addJS('carrierDeliveryPoint.js');
                    $this->addJS('carrierAdditionalInfo.js');
                    $this->addJS('AdminOrder.js');
                }
                break;
            // Back-Office Carrier Wizard.
            case 'admincarrierwizardcontroller':
                $this->addJS('AdminCarrierWizard.js');
                break;
            case 'adminaddressescontroller':
                // Form.css required for address-city-check, ExtraData
                $this->addCSS('form.css', 'all');

                // FancyBox required to display form (cp/ville check).
                $objAdminController->addJqueryPlugin('fancybox');
                $this->addJS('address.js');
                break;
            default:
                // Update All Parcels and OrderState accordingly.
                TNTOfficielOrder::updateAllOrderStateDeliveredParcels();
                break;
        }

        $arrJSONTNTOfficiel = $this->getCommonVariable();
        $arrJSONTNTOfficiel['link']['back'] = array(
            'module' => array(
                /* Account settings. */
                'selectPostcodeCities' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=selectPostcodeCities&ajax=true',
                'updateHRA' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=updateHRA&ajax=true',
                /* Order detail. */
                'addParcelUrl' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=addParcel&ajax=true',
                'removeParcelUrl' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=removeParcel&ajax=true',
                'updateParcelUrl' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=updateParcel&ajax=true',
                'checkShippingDateValidUrl' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=checkShippingDateValid&ajax=true',
                'updateOrderStateDeliveredParcels' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=updateOrderStateDeliveredParcels&ajax=true',
                // common displayAjaxStoreReceiverInfo
                'storeReceiverInfo' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=storeReceiverInfo&ajax=true',
                // common displayAjaxBoxDeliveryPoints
                'boxDeliveryPoints' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=boxDeliveryPoints&ajax=true',
                // common displayAjaxSaveProductInfo
                'saveProductInfo' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=saveProductInfo&ajax=true',
                // common displayAjaxGetCities
                'getAddressCities' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=getCities&ajax=true',
                // common displayAjaxUpdateDeliveryAddress
                'updateAddressDelivery' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=updateDeliveryAddress&ajax=true',
                /* Customer Address detail. */
                // common displayAjaxCheckPostcodeCity
                'checkAddressPostcodeCity' =>
                    $this->context->link->getAdminLink('AdminTNTOrders').'&action=checkPostcodeCity&ajax=true',
            )
        );

        $arrJSONTNTOfficiel['translate']['back'] = array(
            'updateSuccessfulStr' => htmlentities($this->l('Update successful')),
            'updateFailRetryStr' => htmlentities($this->l('Update not completed, please try again')),
            'deleteStr' => htmlentities($this->l('Delete')),
            'updateStr' => htmlentities($this->l('Update')),
            'atLeastOneParcelStr' => htmlentities($this->l('An order requires at least one parcel')),
            'confirmApplyContext' => htmlentities(
                $this->l('The changes made will be applied to all selected stores.')."\n\n"
                .$this->l('Do you want to apply these changes and overwrite previously saved data?')
            ),
            'accountNotRegisteredStr' => htmlentities(
                $this->l('Access to TNT web services not recognized.')."\n"
                .$this->l('The account number used is not authorized to access TNT\'s web services.')."\n"
                .$this->l('Please refer to the "prerequisites" in the TNT module installation manual').' '
                .$this->l('to request by e-mail an authorization to connect your TNT account to the web services.')
            ),
        );

        if (!array_key_exists('alert', $arrJSONTNTOfficiel)
            || !is_array($arrJSONTNTOfficiel['alert'])
        ) {
            $arrJSONTNTOfficiel['alert'] = array(
                'error' => array(),
                'warning' => array(),
                'success' => array()
            );
        }

        // Cookie TNTOfficielError is used to display error message once after redirect.
        if (!empty($objHookCookie->TNTOfficielError)) {
            // Add error message to the admin page if exists.
            $arrJSONTNTOfficiel['alert']['error'][] = $objHookCookie->TNTOfficielError;
            // Delete cookie.
            $objHookCookie->TNTOfficielError = null;
        }
        if (!empty($objHookCookie->TNTOfficielWarning)) {
            // Add error message to the admin page if exists.
            $arrJSONTNTOfficiel['alert']['warning'][] = $objHookCookie->TNTOfficielWarning;
            // Delete cookie.
            $objHookCookie->TNTOfficielWarning = null;
        }
        if (!empty($objHookCookie->TNTOfficielSuccess)) {
            // Add error message to the admin page if exists.
            $arrJSONTNTOfficiel['alert']['success'][] = $objHookCookie->TNTOfficielSuccess;
            // Delete cookie.
            $objHookCookie->TNTOfficielSuccess = null;
        }

        // Add TNTOfficiel global variable with others in main inline script.
        Media::addJsDef(array('TNTOfficiel' => $arrJSONTNTOfficiel));

        TNTOfficiel_Logstack::dump(array(
            'method' => sprintf('%s::%s', __CLASS__, __FUNCTION__),
            'ajax' => $objAdminController->ajax,
            'controller_type' => $objAdminController->controller_type,
            'controllername' => $strCurrentControllerName,
            'controllerfilename' => Dispatcher::getInstance()->getController()
        ));
    }

    /**
     * HOOK (AKA Header) displayed in head tag on Front-Office.
     *
     * @param array $arrArgHookParams
     *
     * @return string
     */
    public function hookDisplayHeader($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        //$objHookCart = $arrArgHookParams['cart'];

        $objContext = $this->context;

        // Controller.
        $objFrontController = $objContext->controller;
        // Get Controller Name.
        $strCurrentControllerName = TNTOfficiel_Tools::getControllerName($objFrontController);

        // If module not ready.
        if (!TNTOfficiel::isContextReady()) {
            // Display nothing.
            return '';
        }

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        // If no account available for this context, or is not authenticated.
        if ($objTNTContextAccountModel === null
            || $objTNTContextAccountModel->getAuthValidatedDateTime() === null
        ) {
            // Display nothing.
            return '';
        }

        $arrJSONTNTOfficiel = $this->getCommonVariable();
        // Add TNTOfficiel global variable with others in main inline script.
        Media::addJsDef(array('TNTOfficiel' => $arrJSONTNTOfficiel));

        // Google Font: Open Sans.
        $this->addCSS('https://fonts.googleapis.com/css?family=Open+Sans:400,700', 'all');

        $strAssetJSPath = $this->getPathUri().'views/js/'.TNTOfficiel::MODULE_RELEASE.'/';

        $this->addCSS('global.css', 'all');
        $this->addJS('global.js');

        // Switch Controller Name.
        switch ($strCurrentControllerName) {
            // Front-Office Order History +guest.
            case 'orderdetailcontroller':
            case 'guesttrackingcontroller':
                // Form.css required for displayOrderDetail.tpl
                $this->addCSS('form.css', 'all');
                break;
            // Front-Office Address.
            case 'addresscontroller':
                // Front-Office Guest Checkout Address.
            case 'authcontroller':
                // Form.css required for address-city-check, ExtraData
                $this->addCSS('form.css', 'all');

                // FancyBox required to display form (cp/ville check).
                $objFrontController->addJqueryPlugin('fancybox');
                $this->addJS('address.js');
                break;

            // Front-Office Cart Process.
            case 'ordercontroller':
                // form.css required for address-city-check.
                $this->addCSS('form.css', 'all');
                // receiver.css for extradata.
                $this->addCSS('receiver.css', 'all');
                //
                $this->addCSS('carrier.css', 'all');

                // Prestashop Validation system.
                $this->addJS(_PS_JS_DIR_.'validate.js');

                // FancyBox required to display form (cp/ville check).
                $objFrontController->addJqueryPlugin('fancybox');
                $this->addJS('address.js');

                // TNTOfficiel_inflate() TNTOfficiel_deflate(), required by carrierDeliveryPoint.js
                $this->addJS('lib/string.js');
                // jQuery.fn.nanoScroller, required by carrierDeliveryPoint.js
                $this->addJS('lib/nanoscroller/jquery.nanoscroller.min.js');
                $this->addCSS($strAssetJSPath.'lib/nanoscroller/nanoscroller.css', 'all');

                $this->addJS('carrierDeliveryPoint.js');
                $this->addJS('carrierAdditionalInfo.js');
                // TNTOfficiel_deliveryPointsBox, used in displayAjaxBoxDeliveryPoints.tpl
                $this->addJS('carrier.js');
                break;

            default:
                break;
        }


        TNTOfficiel_Logstack::dump(array(
            'method' => sprintf('%s::%s', __CLASS__, __FUNCTION__),
            'ajax' => $objFrontController->ajax,
            'controller_type' => $objFrontController->controller_type,
            'controllername' => $strCurrentControllerName,
            'controllerfilename' => Dispatcher::getInstance()->getController(),
            'js' => $arrJSONTNTOfficiel
        ));

        // Display nothing.
        return '';
    }

    /**
     * HOOK (AKA beforeCarrier) displayed before the carrier list on Front-Office.
     *
     * @param array $arrArgHookParams
     *
     * @return string
     */
    public function hookDisplayBeforeCarrier($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objPSCart = $objContext->cart;

        // If module not ready.
        if (!TNTOfficiel::isContextReady()) {
            // Display nothing.
            return '';
        }

        // Force $objPSCart->id_carrier Update using autoselect if not set (without using cache).
        // $objPSCart->id_carrier maybe incorrectly set when autoselection determine current selected carrier.
        // e.g: only one core carrier available, input radio is always already preselected,
        // but not $objPSCart->id_carrier since setDeliveryOption() was not used (and no change is possible).
        $objPSCart->setDeliveryOption($objPSCart->getDeliveryOption(null, false, false));
        $objPSCart->save();

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        // If no account available for this context, or is not authenticated.
        if ($objTNTContextAccountModel === null
            || $objTNTContextAccountModel->getAuthValidatedDateTime() === null
        ) {
            // Display nothing.
            return '';
        }

        $boolCityPostCodeIsValid = true;

        $objPSAddressDelivery = TNTOfficielReceiver::getPSAddress($objPSCart->id_address_delivery);
        // If delivery address object is available.
        if ($objPSAddressDelivery !== null) {
            // Check the city/postcode.
            $arrResultCitiesGuide = $objTNTContextAccountModel->citiesGuide(
                Country::getIsoById($objPSAddressDelivery->id_country),
                $objPSAddressDelivery->postcode,
                $objPSAddressDelivery->city
            );
            // Unsupported country or communication error is considered true to prevent
            // always invalid address form and show error "unknow postcode" on Front-Office checkout.
            $boolCityPostCodeIsValid = (!$arrResultCitiesGuide['boolIsCountrySupported']
                || $arrResultCitiesGuide['boolIsRequestComError']
                || $arrResultCitiesGuide['boolIsCityNameValid']
            );
        }


        $objHookCookie = $arrArgHookParams['cookie'];
        $strTNTPaymentReadyError = null;
        if (!empty($objHookCookie->TNTPaymentReadyError)) {
            $strTNTPaymentReadyError = $objHookCookie->TNTPaymentReadyError;
        }
        $objHookCookie->TNTPaymentReadyError = null;


        $this->smarty->assign(array(
            'boolCityPostCodeIsValid' => $boolCityPostCodeIsValid,
            'linkAddress' => $objContext->link->getPageLink('address', true),
            'id_address_delivery' => (int)$objPSCart->id_address_delivery,
            'strTNTPaymentReadyError' => $strTNTPaymentReadyError,
        ));

        // Display template.
        return $this->fetch(sprintf(
            'module:%s/views/templates/hook/displayBeforeCarrier.tpl',
            TNTOfficiel::MODULE_NAME
        ));
    }

    /**
     * HOOK called after the list of available carriers, during the order process.
     * Ideal location to add a carrier, as added by a module.
     * Display TNT products during the order process.
     * (displayCarrierList AKA extraCarrier is deprecated).
     *
     * @param array $arrArgHookParams array
     *
     * @return string
     */
    public function hookDisplayAfterCarrier($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookCart = $arrArgHookParams['cart'];

        $intCartID = (int)$objHookCart->id;
        $intCarrierIDSelected = (int)$objHookCart->id_carrier;
        //$intAddressIDDelivery = (int)$objHookCart->id_address_delivery;
        $intCustomerID = (int)$objHookCart->id_customer;

        // If module not ready.
        if (!TNTOfficiel::isContextReady()) {
            // Display nothing.
            return '';
        }

        // Prevent AJAX bug with carrier id inconsistency.
        $objHookCart->save();

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        // If no account available for this context, or is not authenticated.
        if ($objTNTContextAccountModel === null
            || $objTNTContextAccountModel->getAuthValidatedDateTime() === null
        ) {
            // Display nothing.
            return '';
        }

        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID, true);
        if ($objTNTCartModel === null) {
            // Display nothing.
            return '';
        }

        // Load an existing TNT carrier.
        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($intCarrierIDSelected, false);

        $arrFormReceiverInfoValidate = null;
        $strExtraAddressDataValid = 'false';
        // A delivery address is optional.
        $objPSAddressDelivery = $objTNTCartModel->getPSAddressDelivery();
        $objAddressDelivery = null;
        // If delivery address object is available.
        if ($objPSAddressDelivery !== null) {
            $objAddressDelivery = (object)array(
                'company' => $objPSAddressDelivery->company,
                'id_country' => $objPSAddressDelivery->id_country,
                'postcode' => trim($objPSAddressDelivery->postcode),
                'city' => trim($objPSAddressDelivery->city),
            );
            // Get postcode from delivery point.
            if ($objTNTCarrierModel !== null
                && in_array($objTNTCarrierModel->carrier_type, array('DROPOFFPOINT', 'DEPOT'))
            ) {
                $arrDeliveryPoint = $objTNTCartModel->getDeliveryPoint();
                if (array_key_exists('postcode', $arrDeliveryPoint)) {
                    $objAddressDelivery->postcode = trim($arrDeliveryPoint['postcode']);
                }
                if (array_key_exists('city', $arrDeliveryPoint)) {
                    $objAddressDelivery->city = trim($arrDeliveryPoint['city']);
                }
            }

            // Load TNT receiver info or create a new one for it's ID.
            $objTNTReceiverModel = TNTOfficielReceiver::loadAddressID($objPSAddressDelivery->id);
            // If success.
            if ($objTNTReceiverModel !== null) {
                $strCustomerEMail = null;
                // A shipping address is optional.
                $objCustomer = new Customer($intCustomerID);
                // If shipping address object is available.
                if (Validate::isLoadedObject($objCustomer)
                    && (int)$objCustomer->id === $intCustomerID
                ) {
                    $strCustomerEMail = $objCustomer->email;
                }

                $strAddressPhone = $objTNTReceiverModel::searchPhoneMobile($objPSAddressDelivery);

                // Validate & store receiver info, using the customer email and address mobile phone as default values.
                $arrFormReceiverInfoValidate = $objTNTReceiverModel->storeReceiverInfo(
                    $objTNTReceiverModel->receiver_email ? $objTNTReceiverModel->receiver_email : $strCustomerEMail,
                    $objTNTReceiverModel->receiver_mobile ? $objTNTReceiverModel->receiver_mobile : $strAddressPhone,
                    $objTNTReceiverModel->receiver_building,
                    $objTNTReceiverModel->receiver_accesscode,
                    $objTNTReceiverModel->receiver_floor,
                    $objTNTReceiverModel->receiver_instructions
                );

                $strExtraAddressDataValid = $arrFormReceiverInfoValidate['stored'] ? 'true' : 'false';
            }
        }

        // Get the carriers model list.
        $arrObjTNTCarrierModelList = TNTOfficielCarrier::getLiveFeasibilityContextCarrierModelList(
            // Get the heaviest product weight from cart.
            $objTNTCartModel->getCartHeaviestProduct(),
            $objAddressDelivery
        );

        $this->smarty->assign(array(
            'arrObjTNTCarrierModelList' => $arrObjTNTCarrierModelList,
            'arrDeliveryOption' => $objTNTCartModel->getDeliveryOption(),
            'strCarrierTypeSelected' =>
                $objTNTCarrierModel === null ? null : ($objTNTCarrierModel->carrier_type),
            'arrFormReceiverInfoValidate' => $arrFormReceiverInfoValidate,
            'strExtraAddressDataValid' => $strExtraAddressDataValid,
        ));

        // Display template.
        return $this->fetch(sprintf(
            'module:%s/views/templates/hook/displayAfterCarrier.tpl',
            TNTOfficiel::MODULE_NAME
        ))/*
        .'<pre style="font-size: 11px;line-height: 1.2em;">'
        .'<b>intCarrierIDSelected</b> : '.$intCarrierIDSelected."\n"
        .'<b>objHookCart</b> : '.TNTOfficiel_Tools::encJSON($objHookCart)."\n"
        .'<b>getDeliveryOptionList</b> : '.TNTOfficiel_Tools::encJSON(TNTOfficiel_Tools::dumpSafe($objHookCart->getDeliveryOptionList(null), 1048576*10, 7))."\n"
        .'<b>getPackageList</b> : '.TNTOfficiel_Tools::encJSON(TNTOfficiel_Tools::dumpSafe($objHookCart->getPackageList(true), 1048576, 6))."\n"
        .'<b>objTNTCartModel</b> : '.TNTOfficiel_Tools::encJSON($objTNTCartModel)."\n"
        .'<b>getDeliveryOption</b> : '.TNTOfficiel_Tools::encJSON($objTNTCartModel->getDeliveryOption())."\n"
        .'<b>isMultiShippingSupport</b> : '.TNTOfficiel_Tools::encJSON($objTNTCartModel->isMultiShippingSupport())."\n"
        .'<b>CartTotalWeight</b> : '.TNTOfficiel_Tools::encJSON($objTNTCartModel->getCartTotalWeight())." Kg\n"
        .'<b>CartTotalPrice</b> : '.TNTOfficiel_Tools::encJSON($objTNTCartModel->getCartTotalPrice())." € TTC\n"
        .'</pre>'
        */
        ;
    }

    /**
     * HOOK called to display extra content of an available carriers when selected.
     *
     * @param array $arrArgHookParams
     *
     * @return string
     */
    public function hookDisplayCarrierExtraContent($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        $arrHookCarrier = $arrArgHookParams['carrier'];
        $intCarrierID = (int)$arrHookCarrier['id'];

        $objHookCart = $objContext->cart;
        $intCartID = (int)$objHookCart->id;

        // If module not ready.
        if (!TNTOfficiel::isContextReady()) {
            // Display nothing.
            return '';
        }

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID, true);
        // If fail.
        if ($objTNTCartModel === null) {
            // Display nothing.
            return '';
        }

        // Load an existing TNT carrier.
        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($intCarrierID, false);
        // If fail.
        if ($objTNTCarrierModel === null) {
            // Display nothing.
            return '';
        }

        $objTNTCarrierAccountModel = $objTNTCarrierModel->getTNTAccountModel();

        // If no account available for this carrier, or is not authenticated.
        if ($objTNTCarrierAccountModel === null
            || $objTNTCarrierAccountModel->getAuthValidatedDateTime() === null
        ) {
            // Display nothing.
            return '';
        }

        /*
         * Estimated delivery date.
         */

        $arrLiveFeasibility = null;
        $strDueDate = null;

        // A delivery address is optional.
        $objPSAddressDelivery = $objTNTCartModel->getPSAddressDelivery();
        $objAddressDelivery = null;
        $strReceiverPostCode = null;
        $strReceiverCity = null;
        // If delivery address object is available.
        if ($objPSAddressDelivery !== null) {
            $objAddressDelivery = (object)array(
                'company' => $objPSAddressDelivery->company,
                'id_country' => $objPSAddressDelivery->id_country,
                'postcode' => trim($objPSAddressDelivery->postcode),
                'city' => trim($objPSAddressDelivery->city),
            );
            // Get postcode from delivery point.
            if (in_array($objTNTCarrierModel->carrier_type, array('DROPOFFPOINT', 'DEPOT'))) {
                $arrDeliveryPoint = $objTNTCartModel->getDeliveryPoint();
                if (array_key_exists('postcode', $arrDeliveryPoint)) {
                    $objAddressDelivery->postcode = trim($arrDeliveryPoint['postcode']);
                }
                if (array_key_exists('city', $arrDeliveryPoint)) {
                    $objAddressDelivery->city = trim($arrDeliveryPoint['city']);
                }
            }
            $strReceiverPostCode = $objAddressDelivery->postcode;
            $strReceiverCity = $objAddressDelivery->city;
        }

        // If delivery address object is available.
        if ($objPSAddressDelivery !== null) {
            $arrLiveFeasibility = $objTNTCarrierModel->liveFeasibility(
                $strReceiverPostCode,
                $strReceiverCity,
                $objTNTCarrierModel->getReceiverType($objPSAddressDelivery)
            );
            if (is_array($arrLiveFeasibility)) {
                $strDueDate = $arrLiveFeasibility['dueDate'];
            }
        }

        $this->smarty->assign(array(
            'objTNTCarrierModel' => $objTNTCarrierModel,
            'strDueDate' => $objTNTCarrierAccountModel->delivery_display_edd ? $strDueDate : null,
            'deliveryPoint' => $objTNTCartModel->getDeliveryPoint(),
        ));

        // Display template.
        return $this->fetch(sprintf(
            'module:%s/views/templates/hook/displayCarrierExtraContent.tpl',
            TNTOfficiel::MODULE_NAME
        ))/*
        .'<pre style="font-size: 11px;line-height: 1.2em;">'
        .'<b>intCarrierIDSelected</b> : '.$intCarrierID."\n"
        .'<b>Account</b> : '.TNTOfficiel_Tools::encJSON($objTNTCarrierAccountModel)."\n"
        .'<b>getMaxPackageWeight</b> : '.$objTNTCarrierModel->getMaxPackageWeight()." Kg \n"
        .'<b>LiveFeasibility</b> : '.TNTOfficiel_Tools::encJSON($arrLiveFeasibility)."\n"
        .'<b>ZonesConf</b> : '.TNTOfficiel_Tools::encJSON($objTNTCarrierModel->getZonesConf())."\n"
        .'<b>CartShippingFree</b> : '
        .TNTOfficiel_Tools::encJSON($objTNTCartModel->isCartShippingFree($intCarrierID))."\n"
        .'<b>ExtraShippingCost</b> : '.$objTNTCartModel->getCartExtraShippingCost($intCarrierID)." € HT\n"
        .'<b>getPrice</b> : '.TNTOfficiel_Tools::encJSON($objTNTCarrierModel->getPrice(
            $objTNTCartModel->getCartTotalWeight(), $objTNTCartModel->getCartTotalPrice(), $strReceiverPostCode
         ))." € HT\n"
        .'</pre>'*/
        ;
    }

    /**
     * HOOK 1.7.1+ called when button continue is submitted (confirmDeliveryOption) on delivery step.
     * Check if state for a selected carrier of this module is completed.
     * https://github.com/PrestaShop/PrestaShop/commit/895255fd61b9cdf77e4e6096ef076b5149d884a4
     *
     * @param array $arrArgHookParams
     *
     * @return bool
     */
    public function hookActionValidateStepComplete($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookCart = $arrArgHookParams['cart'];

        $objHookCookie = $arrArgHookParams['cookie'];

        $intCartID = (int)$objHookCart->id;

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID, true);
        if ($objTNTCartModel !== null) {
            $arrResult = $objTNTCartModel->isPaymentReady();
            // Set to true if completed.
            $arrArgHookParams['completed'] = !array_key_exists('error', $arrResult) || !is_string($arrResult['error']);
            // Store error message to display later after redirect in BeforeCarrier Hook.
            if (array_key_exists('error', $arrResult) && is_string($arrResult['error'])) {
                $arrJSONTNTOfficiel = $this->getCommonVariable();
                if (array_key_exists($arrResult['error'], $arrJSONTNTOfficiel['translate'])) {
                    $objHookCookie->TNTPaymentReadyError = html_entity_decode(
                        $arrJSONTNTOfficiel['translate'][$arrResult['error']]
                    );
                } else {
                    $objHookCookie->TNTPaymentReadyError = $arrResult['error'];
                }
            }
        }

        return true;
    }

    /**
     * HOOK (AKA newOrder) called during the new order creation process, right after it has been created.
     * Called from /classes/PaymentModule.php
     *
     * Create XETT/PEX address if required and create parcels.
     *
     * @param $arrArgHookParams array
     *
     * @return bool
     */
    public function hookActionValidateOrder($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookOrder = $arrArgHookParams['order'];
        $intOrderID = (int)$objHookOrder->id;

        //$objHookCustomer = $arrArgHookParams['customer'];
        //$objHookCurrency = $arrArgHookParams['currency'];
        //$objHookOrderStatus = $arrArgHookParams['orderStatus'];

        // Load TNT order info or create a new one for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, true);
        // If fail.
        if ($objTNTOrderModel === null) {
            return false;
        }

        // Load an existing TNT carrier.
        $objTNTCarrierModel = $objTNTOrderModel->getTNTCarrierModel();
        // If fail or carrier is not from TNT module.
        if ($objTNTCarrierModel === null) {
            // Do not have to save this cart.
            return false;
        }

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = $objTNTOrderModel->getTNTCartModel();
        // If fail.
        if ($objTNTCartModel === null) {
            return false;
        }

        // Creates parcels for order.
        $objTNTOrderModel->createParcels();

        if (in_array($objTNTCarrierModel->carrier_type, array('DROPOFFPOINT', 'DEPOT'))) {
            $arrDeliveryPoint = $objTNTCartModel->getDeliveryPoint();

            // Copy Delivery Point from cart to order and create a new address.
            $mxdNewIDAddressDelivery = $objTNTOrderModel->setDeliveryPoint($arrDeliveryPoint);
            if (is_int($mxdNewIDAddressDelivery) && $mxdNewIDAddressDelivery > 0) {
                // Bind again for hook.
                $objHookOrder->id_address_delivery = $mxdNewIDAddressDelivery;
            } else {
                $objException = new Exception(sprintf(
                    'Error while binding new Address #%s from %s delivery point for Order #%s',
                    $mxdNewIDAddressDelivery,
                    $objTNTCarrierModel->carrier_type,
                    $intOrderID
                ));
                TNTOfficiel_Logger::logException($objException);
                return false;
            }

            // Save TNT order.
            $objTNTOrderModel->save();
        }

        // Update shipping date if available.
        $objTNTOrderModel->updatePickupDate();

        return true;
    }

    /**
     * HOOK (AKA adminOrder) called when the order's details are displayed, below the Client Information block.
     * Parcel management for orders with a tnt carrier.
     *
     * @param array $arrArgHookParams
     *
     * @return string
     */
    public function hookDisplayAdminOrder($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        // Controller.
        $objAdminController = $objContext->controller;

        //$objHookCookie = $arrArgHookParams['cookie'];
        //$objHookCart = $arrArgHookParams['cart'];

        $intHookOrderID = (int)$arrArgHookParams['id_order'];


        $objPSOrder = TNTOfficielOrder::getPSOrder($intHookOrderID);
        if ($objPSOrder === null) {
            // Display nothing.
            return '';
        }

        // If order carrier is not created by tntofficiel module.
        if (!TNTOfficielCarrier::isTNTOfficielCarrierID($objPSOrder->id_carrier)) {
            // Display nothing.
            return '';
        }

        // Prevent Prestahop bugs without override.
        // http://forge.prestashop.com/browse/BOOM-4050
        // http://forge.prestashop.com/browse/BOOM-5821
        if (version_compare(_PS_VERSION_, '1.7.7', '<')
            && Shop::getContext() !== Shop::CONTEXT_SHOP
        ) {
            // Change context to order shop.
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminOrders', false)
                .'&id_order='.$objPSOrder->id.'&vieworder'
                .'&token='.Tools::getAdminTokenLite('AdminOrders')
                .'&setShopContext=s-'.$objPSOrder->id_shop
            );
        }

        // Load TNT order info or create a new one for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intHookOrderID, true);
        if ($objTNTOrderModel === null) {
            $this->displayAdminError(sprintf(
                $this->l('Unable to load or create TNT Order for Order #%s'),
                $intHookOrderID
            ));

            // Display nothing.
            return '';
        }

        $objTNTCarrierModel = $objTNTOrderModel->getTNTCarrierModel();
        if ($objTNTCarrierModel === null) {
            $this->displayAdminError(sprintf(
                $this->l('Unable to load TNT Carrier #%s'),
                $objPSOrder->id_carrier
            ));

            // Display nothing.
            return '';
        }

        $objTNTCarrierAccountModel = $objTNTOrderModel->getTNTAccountModel();
        // If no account available for this order's carrier.
        if ($objTNTCarrierAccountModel === null) {
            $this->displayAdminError(sprintf(
                $this->l('Unable to load TNT Account for Carrier #%s'),
                $objPSOrder->id_carrier
            ));

            // Display nothing.
            return '';
        }

        $objPSAddressDelivery = $objTNTOrderModel->getPSAddressDelivery();
        // If no address available for this order.
        if ($objPSAddressDelivery === null) {
            $this->displayAdminError(sprintf(
                $this->l('Unable to load Address #%s'),
                $objPSOrder->id_address_delivery
            ));

            // Display nothing.
            return '';
        }

        // If account is not authenticated.
        if ($objTNTCarrierAccountModel->getAuthValidatedDateTime() === null) {
            $this->displayAdminError(sprintf(
                $this->l('TNT Account is not authenticated for Account #%s'),
                $objTNTCarrierAccountModel->id
            ));

            // Display nothing.
            return '';
        }

        $boolDirectAddressCheck = false;

        if (!$objTNTOrderModel->isExpeditionCreated()) {
            // Is carrier already available for account ?
            $arrCarrierAvailabilities = $objTNTCarrierAccountModel->availabilities();
            $strCarrierID = implode(':', array(
                $objTNTCarrierModel->account_type,
                $objTNTCarrierModel->carrier_type,
                $objTNTCarrierModel->carrier_code1,
                $objTNTCarrierModel->carrier_code2
            ));
            if (!array_key_exists($strCarrierID, $arrCarrierAvailabilities)) {
                $this->displayAdminError(TNTOfficiel::CARRIER_NAME.' : '.sprintf(
                    $this->l('Current TNT Carrier is no more available on TNT Account %s.'),
                    $objTNTCarrierAccountModel->account_number
                ).' '.$this->l('Please replace it to allow expedition creation.').' '
                .$this->l('In the ORDER section, DELIVERY tab, MODIFY the Carrier.'));

                // Display nothing.
                return '';
            }

            // Is carrier available for address ?
            $boolIsReceiverB2B = !!trim($objPSAddressDelivery->company);
            $boolIsAvailable = $objTNTCarrierModel->isAvailableForReceiverType($boolIsReceiverB2B);
            if (!$boolIsAvailable) {
                $this->displayAdminError(TNTOfficiel::CARRIER_NAME.' : '.sprintf(
                    $this->l('Delivery address is %s, but not the carrier.'),
                    $boolIsReceiverB2B ? $this->l('B2B') : $this->l('B2C')
                ).' '.$this->l('Please verify "Company" field in delivery address.').' '
                .$this->l('Otherwise, you can also replace current carrier.').' '
                .$this->l('In the ORDER section, DELIVERY tab, MODIFY the Carrier.'));
            }

            // Is address zipcode or city valid ?
            $arrResultCitiesGuideReceiver = $objTNTCarrierAccountModel->citiesGuide(
                'FR',
                $objPSAddressDelivery->postcode,
                $objPSAddressDelivery->city
            );
            // If the country is not supported
            // or the city does not match the postcode for the delivery address (without communication error).
            if (!$arrResultCitiesGuideReceiver['boolIsCountrySupported']
                || (!$arrResultCitiesGuideReceiver['boolIsRequestComError']
                    && !$arrResultCitiesGuideReceiver['boolIsCityNameValid']
                )
            ) {
                $boolDirectAddressCheck = true;
                $this->displayAdminError(TNTOfficiel::CARRIER_NAME.' : '.sprintf(
                    $this->l('Unrecognized zipcode or city in delivery Address #%s'),
                    $objPSOrder->id_address_delivery
                ));
            }
        }

        // Load TNT Receiver info or create a new one for it's ID.
        $objTNTReceiverModel = TNTOfficielReceiver::loadAddressID($objPSOrder->id_address_delivery);
        // If fail.
        if ($objTNTReceiverModel === null) {
            $this->displayAdminError(sprintf(
                $this->l('Unable to load or create TNT Receiver for Address #%s'),
                $objPSOrder->id_address_delivery
            ));
            // Display nothing.
            return '';
        }

        if (version_compare(_PS_VERSION_, '1.7.7', '<')) {
            $strAssetJSPath = $this->getPathUri().'views/js/'.TNTOfficiel::MODULE_RELEASE.'/';

            // Form.css required for address-city-check, ExtraData
            $this->addCSS('form.css', 'all');
            //
            $this->addCSS('carrier.css', 'all');

            // FancyBox required to display form (cp/ville check).
            $objAdminController->addJqueryPlugin('fancybox');
            $this->addJS('address.js');

            // TNTOfficiel_inflate() TNTOfficiel_deflate(), required by carrierDeliveryPoint.js
            $this->addJS('lib/string.js');
            // jQuery.fn.nanoScroller, required by carrierDeliveryPoint.js
            $this->addJS('lib/nanoscroller/jquery.nanoscroller.min.js');
            $this->addCSS($strAssetJSPath.'lib/nanoscroller/nanoscroller.css', 'all');

            $this->addJS('carrierDeliveryPoint.js');
            $this->addJS('carrierAdditionalInfo.js');
            $this->addJS('AdminOrder.js');

            // Remove script load of API Google Map to prevent conflicts.
            // Removed in this hook triggered after the setMedia to catch parent class script addition.
            foreach ($objAdminController->js_files as $key => $jsFile) {
                if (preg_match('/^((https?:)?\/\/)?maps\.google(apis)?\.com\/maps\/api\/js/ui', $jsFile)) {
                    unset($objAdminController->js_files[$key]);
                }
            }
            // Load once using TNTOfficel module API key.
            $this->addJS(
                'https://maps.googleapis.com/maps/api/js?v='.TNTOfficiel::GMAP_API_VER.'&key='
                .$objTNTCarrierAccountModel->api_google_map_key
            );
        }

        $strPickUpNumber = $objTNTCarrierAccountModel->pickup_display_number ? $objTNTOrderModel->pickup_number : null;

        // Creates parcels for order if not already done.
        $objTNTOrderModel->createParcels();

        // If all parcels delivered and order state delivered is applied.
        if ($objTNTOrderModel->updateOrderStateDeliveredParcels() === true) {
            // Redirect to show new order state.
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminOrders', false)
                .'&id_order='.$objPSOrder->id.'&vieworder'
                .'&token='.Tools::getAdminTokenLite('AdminOrders')
                //.'&setShopContext=s-'.$objPSOrder->id_shop
            );
        }

        // Get the parcels.
        $arrObjTNTParcelModelList = $objTNTOrderModel->getTNTParcelModelList();

        // Check and display error about shipping date.
        if (!Tools::isSubmit('submitState')) {
            // Check or update the shipping date.
            $arrResultPickupDate = $objTNTOrderModel->updatePickupDate();
            // If true error.
            if (is_string($arrResultPickupDate['strResponseMsgError'])) {
                $this->displayAdminError(TNTOfficiel::CARRIER_NAME.' : '
                    .$arrResultPickupDate['strResponseMsgError']);
            }
/*
            // If normal error.
            if (is_string($arrResultPickupDate['strResponseMsgWarning'])) {
                $objController->warnings[] = TNTOfficiel::CARRIER_NAME.' : '
                    .$arrResultPickupDate['strResponseMsgWarning'];
            }
*/
        }

        $intTSShippingDate = TNTOfficiel_Tools::getDateTimeFormat($objTNTOrderModel->shipping_date);

        $dueDate = '';
        $objDateTimeDue = TNTOfficiel_Tools::getDateTime($objTNTOrderModel->due_date);
        if ($objDateTimeDue !== null) {
            $dueDate = $objDateTimeDue->format('d/m/Y');
        }

        $objDateTimeToday = new DateTime('midnight');
        $intTSFirstAvailableDate = (int)$objDateTimeToday->format('U');

        $arrDeliveryPoint = $objTNTOrderModel->getDeliveryPoint();
        $strDeliveryPointType = $objTNTOrderModel->getDeliveryPointType();
        $strDeliveryPointCode = $objTNTOrderModel->getDeliveryPointCode();

        $objCustomer = new Customer((int)$objPSOrder->id_customer);
        $objPSAddressDelivery = $objTNTOrderModel->getPSAddressDelivery();
        $strAddressPhone = $objTNTReceiverModel::searchPhoneMobile($objPSAddressDelivery);

        // Validate and store receiver info, using the customer email and address mobile phone as default values.
        $arrFormReceiverInfoValidate = $objTNTReceiverModel->storeReceiverInfo(
            $objTNTReceiverModel->receiver_email ? $objTNTReceiverModel->receiver_email : $objCustomer->email,
            $objTNTReceiverModel->receiver_mobile ? $objTNTReceiverModel->receiver_mobile : $strAddressPhone,
            $objTNTReceiverModel->receiver_building,
            $objTNTReceiverModel->receiver_accesscode,
            $objTNTReceiverModel->receiver_floor,
            $objTNTReceiverModel->receiver_instructions
        );


        $strBTLabelName = '';
        if ($objTNTOrderModel->isExpeditionCreated()) {
            // Load an existing TNT label info.
            $objTNTLabelModel = TNTOfficielLabel::loadOrderID($intHookOrderID, false);
            // If success.
            if ($objTNTLabelModel !== null) {
                $strBTLabelName = $objTNTLabelModel->label_name;
            }
        }

        $this->smarty->assign(array(
            'objPSOrder' => $objPSOrder,
            'objPSAddressDelivery' => $objPSAddressDelivery,
            'strPickUpNumber' => $strPickUpNumber,
            'arrObjTNTParcelModelList' => $arrObjTNTParcelModelList,
            'intTSFirstAvailableDate' => $intTSFirstAvailableDate,
            'intTSShippingDate' => $intTSShippingDate,
            'dueDate' => $dueDate,
            'boolDirectAddressCheck' => $boolDirectAddressCheck,
            'isExpeditionCreated' => (bool)$objTNTOrderModel->isExpeditionCreated(),
            'isUpdateParcelsStateAllowed' => (bool)$objTNTOrderModel->isUpdateParcelsStateAllowed(),
            'isAccountInsuranceEnabled' => (bool)$objTNTOrderModel->isAccountInsuranceEnabled(),
            'strBTLabelName' => $strBTLabelName,
            'strDeliveryPointType' => $strDeliveryPointType,
            'strDeliveryPointCode' => $strDeliveryPointCode,
            'arrFormReceiverInfoValidate' => $arrFormReceiverInfoValidate,
            'arrDeliveryPoint' => $arrDeliveryPoint,
            'boolDisplayNew' => (bool)version_compare(_PS_VERSION_, '1.7.7', '>=')
        ));

        if (!$objTNTOrderModel->isExpeditionCreated()) {
            if ($strDeliveryPointType !== null && $strDeliveryPointCode === null) {
                $this->displayAdminError(TNTOfficiel::CARRIER_NAME.' : '
                .$this->l('This order must be finalized for expedition creation.').' '
                .$this->l('In the CLIENT section, DELIVERY ADDRESS tab, SELECT a delivery point.'));
            }
            if ($arrFormReceiverInfoValidate['length'] !== 0) {
                $this->displayAdminError(TNTOfficiel::CARRIER_NAME.' : '
                .$this->l('This order must be finalized for expedition creation.').' '
                .$this->l('In the CUSTOMER section, DELIVERY ADDRESS tab, CONFIRM the ADDITIONAL INFORMATION form.'));
            }
        }

        // Display template.
        return $this->display(__FILE__, 'views/templates/hook/displayAdminOrder.tpl');
        /*
        return $this->fetch(sprintf(
            'module:%s/views/templates/hook/displayAdminOrder.tpl',
            TNTOfficiel::MODULE_NAME
        ));
        */
    }

    /**
     * HOOK 1.7.7+ called when the order's details are displayed, below customer adress.
     *
     * @param $arrArgHookParams
     *
     * @return string
     */
    public function hookDisplayAdminOrderSide($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        //$objContext = $this->context;

        // Controller.
        //$objAdminController = $objContext->controller;

        $intHookOrderID = (int)$arrArgHookParams['id_order'];

        $intHookOrderID === $intHookOrderID;

        // Display template.
        return $this->display(__FILE__, 'views/templates/hook/displayAdminOrderSide.tpl');
    }

    /**
     * HOOK 1.7.7+ called when the order's details are displayed, next to order status.
     *
     * @param array $arrArgHookParams
     *
     * @return bool
     */
    public function hookActionGetAdminOrderButtons($arrArgHookParams)
    {
        // Controller.
        //$objController = $arrArgHookParams['controller'];

        $intHookOrderID = (int)$arrArgHookParams['id_order'];
        /** @var \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection $backOfficeOrderButtons */
        $backOfficeOrderButtons = $arrArgHookParams['actions_bar_buttons_collection'];

        // Load TNT order info or create a new one for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intHookOrderID, true);


        if ($objTNTOrderModel->isExpeditionCreated()) {
            $viewOrderUrlDownloadBT = $this->context->link->getAdminLink('AdminTNTOrders')
                .'&action=downloadBT&id_order='.$intHookOrderID;

            $strBTLabelName = '';
            // Load an existing TNT label info.
            $objTNTLabelModel = TNTOfficielLabel::loadOrderID($intHookOrderID, false);
            // If success.
            if ($objTNTLabelModel !== null) {
                $strBTLabelName = $objTNTLabelModel->label_name;
            }

            $backOfficeOrderButtons->add(
                new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                    'btn-secondary'.($objTNTOrderModel->isExpeditionCreated() ? '' : ' disabled'),
                    array('href' => $viewOrderUrlDownloadBT, 'title' => $strBTLabelName, 'target' => '_blank'),
                    $this->l('TNT Transport Ticket')
                )
            );
        } else {
            $backOfficeOrderButtons->add(
                new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                    'btn-secondary disabled',
                    array('href' => 'javascript:void(0);'),
                    $this->l('TNT Transport Ticket')
                )
            );
        }


        $viewOrderUrlGetManifest = $this->context->link->getAdminLink('AdminTNTOrders')
            .'&action=getManifest&id_order='.$intHookOrderID;

        $backOfficeOrderButtons->add(
            new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                'btn-link',
                array('href' => $viewOrderUrlGetManifest, 'title' => $this->l('Manifest')),
                $this->l('TNT Manifest')
            )
        );


        if ($objTNTOrderModel->isExpeditionCreated()) {
            $viewOrderUrlTracking = $this->context->link->getAdminLink('AdminTNTOrders')
                .'&action=tracking&ajax=true&orderId='.$intHookOrderID;

            $backOfficeOrderButtons->add(
                new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                    'btn-link',
                    array(
                        'href' => 'javascript:void(0);',
                        'onclick' => 'window.open('
                            .'\''.$viewOrderUrlTracking.'\', '
                            .'\'Tracking\', '
                            .'\'menubar=no, scrollbars=yes, top=100, left=100, width=900, height=600\');'
                    ),
                    $this->l('TNT Tracking')
                )
            );
        } else {
            $backOfficeOrderButtons->add(
                new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                    'btn-link disabled',
                    array('href' => 'javascript:void(0);'),
                    $this->l('TNT Tracking')
                )
            );
        }

        return true;
    }


    /**
     * HOOK (AKA updateCarrier) called when a carrier is updated.
     * Updating a Carrier means preserve its previous state and adding a new one which include change using a new ID.
     *
     * @param array $arrArgHookParams
     *
     * @return bool
     */
    public function hookActionCarrierUpdate($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $intHookCarrierIDModified = $arrArgHookParams['id_carrier'];
        $objHookCarrierNew = $arrArgHookParams['carrier'];

        // Update it.
        return TNTOfficielCarrier::updateCarrierID(
            $intHookCarrierIDModified,
            $objHookCarrierNew->id
        );
    }

    /**
     * Carrier module : Method triggered form Cart Model if $carrier->need_range == false.
     * Get the cart shipping price without using the ranges.
     * (best price).
     *
     * @param Cart $objArgCart
     *
     * @return float|false
     */
    public function getOrderShippingCostExternal($objArgCart)
    {
        TNTOfficiel_Logstack::log();

        $fltPrice = $this->getOrderShippingCost($objArgCart, 0.0);

        return $fltPrice;
    }

    /**
     * Carrier module : Method triggered form Cart Model if $carrier->need_range == true.
     * Get the shipping price depending on the ranges that were set in the back office.
     * Get the shipping cost for a cart (best price), if carrier need range (default).
     *
     * @param Cart $objArgCart
     *
     * @return float|false false if no shipping cost (not available).
     */
    public function getOrderShippingCost($objArgCart, $fltArgShippingCost)
    {
        TNTOfficiel_Logstack::log();

        $intCartID = (int)$objArgCart->id;
        // See comment about current class $id_carrier property.
        $intCarrierID = (int)$this->id_carrier;

        // If cart carrier is not created by tntofficiel module.
        if (!TNTOfficielCarrier::isTNTOfficielCarrierID($intCarrierID)) {
            // No shipping cost, not available.
            return false;
        }

        // If module not ready.
        if (!TNTOfficiel::isContextReady()) {
            // No shipping cost, not available.
            return false;
        }

        // Load an existing TNT carrier.
        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($intCarrierID, false);
        // If fail or carrier is not from TNT module.
        if ($objTNTCarrierModel === null) {
            // No shipping cost, not available.
            return false;
        }

        $objTNTCarrierAccountModel = $objTNTCarrierModel->getTNTAccountModel();

        // If no account available for this carrier, or is not authenticated.
        if ($objTNTCarrierAccountModel === null
            || $objTNTCarrierAccountModel->getAuthValidatedDateTime() === null
        ) {
            // No shipping cost, not available.
            return false;
        }

        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID, true);
        if ($objTNTCartModel === null) {
            // No shipping cost, not available.
            return false;
        }

        // Multi-Shipping with multiple address or different carrier not supported.
        $boolMultiShippingSupport = $objTNTCartModel->isMultiShippingSupport();
        if (!$boolMultiShippingSupport) {
            return false;
        }

        // A delivery address is optional.
        $objPSAddressDelivery = $objTNTCartModel->getPSAddressDelivery();
        $objAddressDelivery = null;
        $strReceiverPostCode = null;
        // If delivery address object is available.
        if ($objPSAddressDelivery !== null) {
            $objAddressDelivery = (object)array(
                'company' => $objPSAddressDelivery->company,
                'id_country' => $objPSAddressDelivery->id_country,
                'postcode' => trim($objPSAddressDelivery->postcode),
                'city' => trim($objPSAddressDelivery->city),
            );
            // Get postcode from delivery point.
            if (in_array($objTNTCarrierModel->carrier_type, array('DROPOFFPOINT', 'DEPOT'))) {
                $arrDeliveryPoint = $objTNTCartModel->getDeliveryPoint();
                if (array_key_exists('postcode', $arrDeliveryPoint)) {
                    $objAddressDelivery->postcode = trim($arrDeliveryPoint['postcode']);
                }
                if (array_key_exists('city', $arrDeliveryPoint)) {
                    $objAddressDelivery->city = trim($arrDeliveryPoint['city']);
                }
            }
            $strReceiverPostCode = $objAddressDelivery->postcode;
        }

        $arrObjTNTCarrierModelList = TNTOfficielCarrier::getLiveFeasibilityContextCarrierModelList(
            // Get the heaviest product weight from cart.
            $objTNTCartModel->getCartHeaviestProduct(),
            $objAddressDelivery
        );

        // If carrier is feasible.
        if (array_key_exists($intCarrierID, $arrObjTNTCarrierModelList)) {
            //$objTNTCarrierModel = $arrObjTNTCarrierModelList[$intCarrierID];

            $fltPrice = $objTNTCarrierModel->getPrice(
                $objTNTCartModel->getCartTotalWeight(),
                $objTNTCartModel->getCartTotalPrice(),
                $strReceiverPostCode
            );

            // Use native Prestashop price.
            if ($fltPrice === null) {
                return $fltArgShippingCost;
            }
            // Carrier is disabled.
            if ($fltPrice === false) {
                return false;
            }
            // Shipping is free.
            if ($objTNTCartModel->isCartShippingFree($intCarrierID)) {
                return 0.0;
            }

            // Get additional shipping cost for cart.
            $fltCartExtraShippingCost = $objTNTCartModel->getCartExtraShippingCost($intCarrierID);

            return $fltPrice + $fltCartExtraShippingCost;
        }

        // No shipping cost, not available.
        return false;
    }

    /**
     * HOOK (AKA updateOrderStatus) called when an order's status is changed, right before it is actually changed.
     * Creates an expedition if status match the one set in account config.
     *
     * @param array $arrArgHookParams
     */
    public function hookActionOrderStatusUpdate($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookCookie = $arrArgHookParams['cookie'];

        $objHookOrderStateNew = $arrArgHookParams['newOrderStatus'];
        $intHookOrderID = (int)$arrArgHookParams['id_order'];

        $intOrderStateIDNewID = (int)$objHookOrderStateNew->id;

        $objPSOrder = TNTOfficielOrder::getPSOrder($intHookOrderID);
        if ($objPSOrder === null) {
            return;
        }

        $intCarrierID = (int)$objPSOrder->id_carrier;

        // Load an existing TNT carrier.
        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($intCarrierID, false);
        // If fail or carrier is not from TNT module.
        if ($objTNTCarrierModel === null) {
            // Do nothing.
            return;
        }

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intHookOrderID, false);
        // If fail.
        if ($objTNTOrderModel === null) {
            $objHookCookie->TNTOfficielError = sprintf(
                $this->l('Unable to load Order #%s'),
                $intHookOrderID
            );
            // Do nothing.
            return;
        }

        $objOrderStateShipmentSave = $objTNTOrderModel->getOSShipmentSave();
        // If no orderstate available for this order carrier account.
        if ($objOrderStateShipmentSave === null) {
            // Do nothing.
            return;
        }
        $intOrderStateShipmentSaveID = (int)$objOrderStateShipmentSave->id;

        // If new order status must trigger expedition creation.
        if ($intOrderStateIDNewID === $intOrderStateShipmentSaveID) {
            // Check or update the shipping date.
            $arrResultPickupDate = $objTNTOrderModel->updatePickupDate();

            // If true error.
            if (is_string($arrResultPickupDate['strResponseMsgError'])) {
                $objHookCookie->TNTOfficielError = $arrResultPickupDate['strResponseMsgError'];
            } elseif (!$objTNTOrderModel->isExpeditionCreated()) {
                // Flag shipment label was requested for this order.
                // Prevent to chain to an After OrderState if was not a shipment creation request.
                $this->arrRequestedSaveShipment += array($intHookOrderID => true);
                // Send a shipment request.
                $arrResponse = $objTNTOrderModel->saveShipment();
                // If the response is a string, there is an error.
                if (is_string($arrResponse['strResponseMsgError'])) {
                    $objHookCookie->TNTOfficielError = $arrResponse['strResponseMsgError'];
                }
            }

            // If normal error.
            if (is_string($arrResultPickupDate['strResponseMsgWarning'])) {
                $objHookCookie->TNTOfficielWarning = $arrResultPickupDate['strResponseMsgWarning'];
            }

            // If order has no shipment created.
            if (!$objTNTOrderModel->isExpeditionCreated()) {
                // Default error message.
                if (!$objHookCookie->TNTOfficielError) {
                    $objHookCookie->TNTOfficielError = sprintf(
                        $this->l('Error while create shipping for Order #%s'),
                        $intHookOrderID
                    );
                }
                // Log.
                TNTOfficiel_Logger::logException(new Exception($objHookCookie->TNTOfficielError));
                // Redirect to prevent new order state (cleaner than reverting).
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders', false)
                    .'&id_order='.$objPSOrder->id.'&vieworder'
                    .'&token='.Tools::getAdminTokenLite('AdminOrders')
                    //.'&setShopContext=s-'.$objPSOrder->id_shop
                );
            }
        }
    }

    /**
     * HOOK (AKA postUpdateOrderStatus) called when an order's status is changed, right after it is actually changed.
     * Alert if the shipment was not saved (for an unknown reason).
     *
     * @param array $arrArgHookParams
     */
    public function hookActionOrderStatusPostUpdate($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookCookie = $arrArgHookParams['cookie'];

        $objHookOrderStateNew = $arrArgHookParams['newOrderStatus'];
        $intHookOrderID = (int)$arrArgHookParams['id_order'];
        $objPSOrder = new Order($intHookOrderID);
        $intOrderStateIDNewID = (int)$objHookOrderStateNew->id;

        $intCarrierID = (int)$objPSOrder->id_carrier;

        // Load an existing TNT carrier.
        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($intCarrierID, false);
        // If fail or carrier is not from TNT module.
        if ($objTNTCarrierModel === null) {
            // Do nothing.
            return;
        }

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intHookOrderID, false);
        // If fail.
        if ($objTNTOrderModel === null) {
            // Do nothing.
            return;
        }

        $objOrderStateShipmentSave = $objTNTOrderModel->getOSShipmentSave();
        // If no orderstate available for this order carrier account.
        if ($objOrderStateShipmentSave === null) {
            // Do nothing.
            return;
        }
        $intOrderStateShipmentSaveID = (int)$objOrderStateShipmentSave->id;

        // Check if the new order status is the one that must trigger shipment creation.
        if ($intOrderStateIDNewID === $intOrderStateShipmentSaveID) {
            // If order has no shipment created.
            if (!$objTNTOrderModel->isExpeditionCreated()) {
                $strMsgError = sprintf(
                    $this->l('Error while create shipping for Order #%s'),
                    $intHookOrderID
                );
                TNTOfficiel_Logger::logException(new Exception($strMsgError));
                if (!$objHookCookie->TNTOfficielError) {
                    $objHookCookie->TNTOfficielError = $strMsgError;
                }
            }
        }
    }

    /**
     * HOOK called after an order's status is changed.
     * Used to chain with another status.
     *
     * @param $arrArgHookParams
     */
    public function hookActionOrderHistoryAddAfter($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookCookie = $arrArgHookParams['cookie'];

        $objHookOrderHistory = $arrArgHookParams['order_history'];
        $intOrderID = (int)$objHookOrderHistory->id_order;
        $intOrderStateIDNewID = (int)$objHookOrderHistory->id_order_state;

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        // If fail.
        if ($objTNTOrderModel === null) {
            // Do nothing.
            return;
        }

        $objOrderStateShipmentSave = $objTNTOrderModel->getOSShipmentSave();
        // If no orderstate available for this order carrier account.
        if ($objOrderStateShipmentSave === null) {
            // Do nothing.
            return;
        }
        $intOrderStateShipmentSaveID = (int)$objOrderStateShipmentSave->id;

        // Check if the new order status is the one that must trigger shipment creation.
        // And if shipment is created
        // And if shipment was requested for this order.
        if ($intOrderStateIDNewID === $intOrderStateShipmentSaveID
            && $objTNTOrderModel->isExpeditionCreated()
            && array_key_exists($intOrderID, $this->arrRequestedSaveShipment)
        ) {
            $objOrderStateShipmentAfter = $objTNTOrderModel->getOSShipmentAfter();
            // If no orderstate available for this order carrier account.
            if ($objOrderStateShipmentAfter === null) {
                // Do nothing.
                return;
            }
            $intOrderStateShipmentAfterID = (int)$objOrderStateShipmentAfter->id;
            // Apply next OrderState.
            $mxdUpdatedOS = $objTNTOrderModel->addOrderStateHistory($intOrderStateShipmentAfterID);
            if ($mxdUpdatedOS === false) {
                $strMsgError = sprintf(
                    $this->l('Error while adding status "%s" to Order #%s'),
                    $objOrderStateShipmentAfter->name,
                    $intOrderID
                );
                TNTOfficiel_Logger::logException(new Exception($strMsgError));
                if (!$objHookCookie->TNTOfficielError) {
                    $objHookCookie->TNTOfficielError = $strMsgError;
                }
            }
        }
    }

    /**
     * HOOK (AKA orderDetailDisplayed) displayed on order detail on Front-Office.
     * Insert parcel tracking block on order detail.
     *
     * @param array $arrArgHookParams
     *
     * @return string
     */
    public function hookDisplayOrderDetail($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookOrder = $arrArgHookParams['order'];
        $intHookOrderID = (int)$objHookOrder->id;

        // Load an existing TNT carrier.
        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($objHookOrder->id_carrier, false);
        // If fail or carrier is not from TNT module.
        if ($objTNTCarrierModel === null) {
            // Display nothing.
            return '';
        }

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intHookOrderID, false);

        // If order has no shipment created.
        if ($objTNTOrderModel === null || !$objTNTOrderModel->isExpeditionCreated()) {
            // Display nothing.
            return '';
        }

        $this->smarty->assign(array(
            'trackingUrl' => $this->context->link->getModuleLink(
                TNTOfficiel::MODULE_NAME,
                'tracking',
                array('action' => 'tracking', 'orderId' => $intHookOrderID),
                true
            )
        ));

        // Display template.
        return $this->fetch(sprintf(
            'module:%s/views/templates/hook/displayOrderDetail.tpl',
            TNTOfficiel::MODULE_NAME
        ));
    }

    /**
     * Add mail template variable.
     *
     * @param $arrArgHookParams
     *
     * @return bool
     */
    public function hookActionGetExtraMailTemplateVars($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        if (!array_key_exists('extra_template_vars', $arrArgHookParams)) {
            return false;
        }

        // Variables default is immediately available (empty).
        $arrArgHookParams['extra_template_vars']['{tntofficiel_tracking_url_text}'] = '';
        $arrArgHookParams['extra_template_vars']['{tntofficiel_tracking_url_html}'] = '';

        $intLangID = (int)$arrArgHookParams['id_lang'];
        $strLangISO = Language::getIsoById($intLangID);

        // If id_order not provided.
        if (!array_key_exists('{id_order}', $arrArgHookParams['template_vars'])) {
            return false;
        }

        $intOrderID = (int)$arrArgHookParams['template_vars']['{id_order}'];

        // If not an order associated with a TNT Carrier.
        if (!TNTOfficielOrder::isTNTOfficielOrderID($intOrderID)) {
            return false;
        }

        // Load TNT order.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        // If fail.
        if ($objTNTOrderModel === null) {
            return false;
        }

        // Translation.
        $strLinkTrack = 'Track my TNT packages';
        if ($strLangISO === 'fr') {
            $strLinkTrack = 'Suivre mes colis TNT';
        }

        // mails/fr/shipped.txt; mails/fr/shipped.html
        // if ($arrArgHookParams['template'] === 'shipped') {}

        // Get tracking URL if available.
        $strTrackingURL = $objTNTOrderModel->getTrackingURL();
        if (!is_string($strTrackingURL)) {
            return false;
        }

        $arrArgHookParams['extra_template_vars']['{tntofficiel_tracking_url_text}'] =
            $strLinkTrack.' : ['.$strTrackingURL.']';
        $arrArgHookParams['extra_template_vars']['{tntofficiel_tracking_url_html}'] =
            $this->getTextLink($strLinkTrack, array('href' => $strTrackingURL, 'style' => 'color:#337FF1'));

        return true;
    }
}
