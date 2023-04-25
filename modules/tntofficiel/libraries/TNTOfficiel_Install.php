<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

/**
 * Class TNTOfficiel_Install
 * Used in upgrade, do not rename or remove.
 */
class TNTOfficiel_Install
{
    /** @var array */
    public static $arrHookList = array(
        // Header
        'displayBackOfficeHeader',
        'actionAdminControllerSetMedia',
        'displayHeader',

        // Front-Office display carrier.
        'displayBeforeCarrier',
        'displayAfterCarrier',
        'displayCarrierExtraContent',
        // PS 1.7.1+.
        'actionValidateStepComplete',
        // Front-Office order detail.
        'displayOrderDetail',

        // Order created.
        'actionValidateOrder',

        // Order status before changed.
        'actionOrderStatusUpdate',
        // Order status after changed.
        'actionOrderStatusPostUpdate',
        // Order status added.
        'actionOrderHistoryAddAfter',

        // Back-Office order detail.
        'displayAdminOrder',
        // PS 1.7.7+.
        'ActionGetAdminOrderButtons',
        // PS 1.7.7+.
        'displayAdminOrderSide',
        // Carrier updated.
        'actionCarrierUpdate',

        // Add variables for email.
        'actionGetExtraMailTemplateVars'

        //actionAdminMetaControllerUpdate_optionsBefore
        //actionCarrierProcess
        //actionOrderDetail
        //actionValidateCustomerAddressForm
        //validateCustomerFormFields
    );

    /** @var array Configuration that is Updated on Install and Deleted on Uninstall. */
    // 'preserve' => true to prevent overwrite or delete during install/uninstall process. value is a default.
    // 'global' => true for global context only.
    public static $arrConfigUpdateDeleteList = array(
        // Latest release installed, then preserved until a newer version is installed.
        'TNTOFFICIEL_RELEASE' => array('value' => '', 'global' => true, 'preserve' => true),
    );

    /** @var array */
    public static $arrRemoveFileList = array(
        'libraries/TNTOfficiel_Cache.php',
        'libraries/TNTOfficiel_Parcel.php',
        'override/classes/order/OrderHistory.php',
        'override/classes/order/index.php',
        'override/classes/index.php',
        'views/fonts/tnt-middleware.oet',
        'views/fonts/tnt-middleware.svg',
        'views/fonts/tnt-middleware.ttf',
        'views/fonts/tnt-middleware.woff',
        'views/img/ajax-loader.gif',
    );

    /** @var array */
    public static $arrRemoveDirList = array(
        'override/classes/order/',
        'override/classes/',
        'views/fonts/',
    );

    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Clear Smarty cache.
     *
     * @return bool
     */
    public static function clearCache()
    {
        TNTOfficiel_Logstack::log();

        // Clear Smarty cache.
        Tools::clearSmartyCache();
        // Clear XML cache ('/config/xml/').
        Tools::clearXMLCache();
        // Clear current theme cache (/themes/<THEME>/cache/').
        Media::clearCache();

        // Clear class index cache ('/cache/class_index.php'). PS 1.6.0.5+.
        if (defined('_DB_PREFIX_') && Configuration::get('PS_DISABLE_OVERRIDES')) {
            PrestaShopAutoload::getInstance()->_include_override_path = false;
        }
        PrestaShopAutoload::getInstance()->generateIndex();

        return true;
    }

    /**
     * Remove unused files and unused dirs.
     *
     * @return bool
     */
    public static function uninstallDeprecatedFiles()
    {
        TNTOfficiel_Logstack::log();

        return TNTOfficiel_Tools::removeFiles(
            _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR,
            TNTOfficiel_Install::$arrRemoveFileList,
            TNTOfficiel_Install::$arrRemoveDirList
        );
    }

    /**
     * Update settings fields.
     *
     * @return bool
     */
    public static function updateSettings()
    {
        TNTOfficiel_Logstack::log();

        $boolUpdated = true;
        $strLogMessage = sprintf('%s::%s', __CLASS__, __FUNCTION__);

        foreach (TNTOfficiel_Install::$arrConfigUpdateDeleteList as $strCfgName => $arrConfig) {
            // Must be preserved ?
            $boolPreserve = array_key_exists('preserve', $arrConfig) && $arrConfig['preserve'] === true;
            $boolExist = Configuration::get($strCfgName) !== false;
            // if no need to preserve or not exist.
            if (!$boolPreserve || !$boolExist) {
                // Is global ?
                $boolGlobal = array_key_exists('global', $arrConfig) && $arrConfig['global'] === true;
                // Get value.
                $mxdValue = array_key_exists('value', $arrConfig) ? $arrConfig['value'] : '';

                if ($boolGlobal) {
                    $boolUpdated = $boolUpdated && Configuration::updateGlobalValue($strCfgName, $mxdValue);
                } else {
                    $boolUpdated = $boolUpdated && Configuration::updateValue($strCfgName, $mxdValue);
                }
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage, $boolUpdated);

        return $boolUpdated;
    }

    /**
     * Delete settings fields.
     *
     * @return bool
     */
    public static function deleteSettings()
    {
        TNTOfficiel_Logstack::log();

        $boolDeleted = true;
        $strLogMessage = sprintf('%s::%s', __CLASS__, __FUNCTION__);

        foreach (TNTOfficiel_Install::$arrConfigUpdateDeleteList as $strCfgName => $arrConfig) {
            // Must be preserved ?
            $boolPreserve = array_key_exists('preserve', $arrConfig) && $arrConfig['preserve'] === true;
            if (!$boolPreserve) {
                $boolDeleted = $boolDeleted && Configuration::deleteByName($strCfgName);
            }
        }

        TNTOfficiel_Logger::logUninstall($strLogMessage, $boolDeleted);

        return $boolDeleted;
    }

    /**
     * Creates the tables needed by the module.
     *
     * @return bool
     */
    public static function createTables()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = sprintf('%s::%s', __CLASS__, __FUNCTION__);

        if (!TNTOfficielCache::createTables()
            || !TNTOfficielAccount::createTables()
            || !TNTOfficielCarrier::createTables()
            || !TNTOfficielCart::createTables()
            || !TNTOfficielOrder::createTables()
            || !TNTOfficielReceiver::createTables()
            || !TNTOfficielParcel::createTables()
            || !TNTOfficielLabel::createTables()
            || !TNTOfficielPickup::createTables()
        ) {
            TNTOfficiel_Logger::logInstall($strLogMessage, false);

            return false;
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Check that tables and columns exist.
     *
     * @return bool
     */
    public static function checkTables()
    {
        TNTOfficiel_Logstack::log();

        static $boolStaticOnce = false;
        static $boolStaticResult = null;

        if ($boolStaticOnce) {
            return $boolStaticResult;
        }

        $boolStaticOnce = true;
        $boolStaticResult = true;

        if (!TNTOfficielCache::checkTables()
            || !TNTOfficielAccount::checkTables()
            || !TNTOfficielCarrier::checkTables()
            || !TNTOfficielCart::checkTables()
            || !TNTOfficielOrder::checkTables()
            || !TNTOfficielReceiver::checkTables()
            || !TNTOfficielParcel::checkTables()
            || !TNTOfficielLabel::checkTables()
            || !TNTOfficielPickup::checkTables()
        ) {
            $boolStaticResult = false;
        }

        return $boolStaticResult;
    }

    /**
     * Creates the Tab.
     *
     * @return bool
     */
    public static function createTab()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = sprintf('%s::%s', __CLASS__, __FUNCTION__);

        $arrLangList = Language::getLanguages(true);

        // Set displayed Tab name for each existing language.
        $arrTabNameLang = array();
        if (is_array($arrLangList)) {
            foreach ($arrLangList as $arrLang) {
                $arrTabNameLang[(int)$arrLang['id_lang']] = TNTOfficiel::CARRIER_NAME;
            }
        }

        // Creates the TNT Orders Tab.
        $objAdminTNTOrdersTab = new Tab();
        $objAdminTNTOrdersTab->active = 1;
        $objAdminTNTOrdersTab->class_name = 'AdminTNTOrders';
        $objAdminTNTOrdersTab->name = $arrTabNameLang;
        $objAdminTNTOrdersTab->module = TNTOfficiel::MODULE_NAME;
        $objAdminTNTOrdersTab->id_parent = Tab::getIdFromClassName('AdminParentOrders');
        $boolResultAdminTNTOrdersTab = (bool)$objAdminTNTOrdersTab->add();

        TNTOfficiel_Logger::logInstall($strLogMessage.' : AdminTNTOrders', $boolResultAdminTNTOrdersTab);

        // Creates the TNT setting Carrier Tab.
        $objAdminTNTSettingTab = new Tab();
        $objAdminTNTSettingTab->active = 1;
        $objAdminTNTSettingTab->class_name = 'AdminTNTSetting';
        $objAdminTNTSettingTab->name = $arrTabNameLang;
        $objAdminTNTSettingTab->module = TNTOfficiel::MODULE_NAME;
        $objAdminTNTSettingTab->id_parent = Tab::getIdFromClassName('AdminParentShipping');
        $boolResultAdminTNTSettingTab = (bool)$objAdminTNTSettingTab->add();

        TNTOfficiel_Logger::logInstall($strLogMessage.' : AdminTNTSetting', $boolResultAdminTNTSettingTab);

        // Create the Account setting child Tab (AdminAccountSettingController).
        $arrAccountSettingTabNameLang = array();
        if (is_array($arrLangList)) {
            foreach ($arrLangList as $arrLang) {
                $arrAccountSettingTabNameLang[(int)$arrLang['id_lang']] = 'Paramétrage du compte marchand';
            }
        }
        $objAdminTNTAccountSettingTab = new Tab();
        $objAdminTNTAccountSettingTab->active = 1;
        $objAdminTNTAccountSettingTab->class_name = 'AdminAccountSetting';
        $objAdminTNTAccountSettingTab->name = $arrAccountSettingTabNameLang;
        $objAdminTNTAccountSettingTab->module = TNTOfficiel::MODULE_NAME;
        $objAdminTNTAccountSettingTab->id_parent = Tab::getIdFromClassName('AdminTNTSetting');
        $boolResultAdminAccountSettingTab = (bool)$objAdminTNTAccountSettingTab->add();

        TNTOfficiel_Logger::logInstall($strLogMessage.' : AdminAccountSetting', $boolResultAdminAccountSettingTab);

        // Create the Carrier setting child Tab (AdminCarrierSettingController).
        $arrCarrierSettingTabNameLang = array();
        if (is_array($arrLangList)) {
            foreach ($arrLangList as $arrLang) {
                $arrCarrierSettingTabNameLang[(int)$arrLang['id_lang']] = 'Paramétrage des services de livraison TNT';
            }
        }
        $objAdminTNTCarrierSettingTab = new Tab();
        $objAdminTNTCarrierSettingTab->active = 1;
        $objAdminTNTCarrierSettingTab->class_name = 'AdminCarrierSetting';
        $objAdminTNTCarrierSettingTab->name = $arrCarrierSettingTabNameLang;
        $objAdminTNTCarrierSettingTab->module = TNTOfficiel::MODULE_NAME;
        $objAdminTNTCarrierSettingTab->id_parent = Tab::getIdFromClassName('AdminTNTSetting');
        $boolResultAdminCarrierSettingTab = (bool)$objAdminTNTCarrierSettingTab->add();

        TNTOfficiel_Logger::logInstall($strLogMessage.' : AdminCarrierSetting', $boolResultAdminCarrierSettingTab);

        return ($boolResultAdminTNTOrdersTab
            && $boolResultAdminTNTSettingTab
            && $boolResultAdminAccountSettingTab
            && $boolResultAdminCarrierSettingTab
        );
    }

    /**
     * Delete the Tab.
     *
     * @return bool
     */
    public static function deleteTab()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = sprintf('%s::%s', __CLASS__, __FUNCTION__);

        $objTabsPSCollection = Tab::getCollectionFromModule(TNTOfficiel::MODULE_NAME)->getAll();
        foreach ($objTabsPSCollection as $tab) {
            if (!$tab->delete()) {
                TNTOfficiel_Logger::logUninstall($strLogMessage, false);

                return false;
            }
        }

        TNTOfficiel_Logger::logUninstall($strLogMessage);

        return true;
    }

    /**
     * Create Order Status.
     *
     * @return bool
     */
    public static function createOrderStates()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = sprintf('%s::%s', __CLASS__, __FUNCTION__);

        $arrLangList = Language::getLanguages(true);

        $arrPackageReadyNameLang = array();
        $arrPackageTakenNameLang = array();
        $arrPackageDeliveredPointNameLang = array();
        if (is_array($arrLangList)) {
            foreach ($arrLangList as $arrLang) {
                // Package ready to be remit to the carrier
                $arrPackageReadyNameLang[(int)$arrLang['id_lang']] =
                    sprintf('Colis prêt à être remis au transporteur [%s]', TNTOfficiel::CARRIER_NAME);
                // Parcel taken in charge by the carrier
                $arrPackageTakenNameLang[(int)$arrLang['id_lang']] =
                    sprintf('Colis pris en charge par le transporteur [%s]', TNTOfficiel::CARRIER_NAME);
                // Delivered to the partner merchant or TNT agency
                $arrPackageDeliveredPointNameLang[(int)$arrLang['id_lang']] =
                    sprintf('Livré chez le commerçant partenaire ou l\'agence TNT [%s]', TNTOfficiel::CARRIER_NAME);
            }
        }

        $arrOrderStateDefault = array(
            // Allow customers to download and read the PDF version of the invoice.
            'invoice' => true,
            // Send an e-mail to the customer when the order status changes.
            'send_email' => false,
            // Associated module name.
            'module_name' => TNTOfficiel::MODULE_NAME,
            // Default color.
            'color' => '#FF00FF',
            // Is unremovable.
            'unremovable' => false,
            // Hide this status in the order for customers.
            'hidden' => false,
            // Consider the associated command as validated.
            'logable' => true,
            // View delivery note PDF.
            'delivery' => true,
            // Mark the associated order as shipped.
            'shipped' => false,
            // Mark the associated order as paid.
            'paid' => true,
            // Attach the PDF invoice to the e-mail.
            'pdf_invoice' => false,
            // Attach the PDF delivery note to the e-mail.
            'pdf_delivery' => false,
            // Name.
            'name' => array(),
            // E-mail template name.
            'template' => '',
            // Logo.
            'logo' => 'preparation.gif',
        );

        $arrOrderStateList = array(
            'TNTOFFICIEL_OS_READYFORPICKUP' => array(
                'logo' => 'preparation.gif',
                'color' => '#00E4F5',
                'shipped' => false,
                'name' => $arrPackageReadyNameLang,
            ),
            'TNTOFFICIEL_OS_TAKENINCHARGE' => array(
                'logo' => 'shipping.gif',
                'color' => '#E099FF',
                'shipped' => true,
                'name' => $arrPackageTakenNameLang,
            ),
            'TNTOFFICIEL_OS_DELIVEREDTOPOINT' => array(
                'logo' => 'delivered.gif',
                'color' => '#10DA97',
                'shipped' => true,
                'name' => $arrPackageDeliveredPointNameLang,
            ),
        );

        foreach ($arrOrderStateList as $strOSConfigName => $arrOrderStateItem) {
            $intOrderStateID = (int)Configuration::get($strOSConfigName);
            $boolOrderStateCreate = !($intOrderStateID > 0);

            // Check OrderState exist.
            if (!$boolOrderStateCreate) {
                $objOrderStateAllDelivered = new OrderState(
                    $intOrderStateID,
                    (int)Configuration::get('PS_LANG_DEFAULT')
                );
                if (!Validate::isLoadedObject($objOrderStateAllDelivered)
                    || (int)$objOrderStateAllDelivered->id !== $intOrderStateID
                ) {
                    $boolOrderStateCreate = true;
                }
            }

            // If not already created.
            if ($boolOrderStateCreate) {
                $objOrderStateItem = (object)array_merge($arrOrderStateDefault, $arrOrderStateItem);

                $objOrderStateNew = new OrderState();
                $objOrderStateNew->invoice = $objOrderStateItem->invoice;
                $objOrderStateNew->send_email = $objOrderStateItem->send_email;
                $objOrderStateNew->module_name = $objOrderStateItem->module_name;
                $objOrderStateNew->color = $objOrderStateItem->color;
                $objOrderStateNew->unremovable = $objOrderStateItem->unremovable;
                $objOrderStateNew->hidden = $objOrderStateItem->hidden;
                $objOrderStateNew->logable = $objOrderStateItem->logable;
                $objOrderStateNew->delivery = $objOrderStateItem->delivery;
                $objOrderStateNew->shipped = $objOrderStateItem->shipped;
                $objOrderStateNew->paid = $objOrderStateItem->paid;
                $objOrderStateNew->name = $objOrderStateItem->name;
                $objOrderStateNew->template = $objOrderStateItem->template;

                // If unable to create new Prestashop OrderState.
                if (!$objOrderStateNew->save()) {
                    TNTOfficiel_Logger::logInstall($strLogMessage.' : '.$strOSConfigName, false);

                    return false;
                }

                // Get new ID.
                $intOrderStateIDNew = (int)$objOrderStateNew->id;

                // Save new OrderState ID in configuration.
                Configuration::updateGlobalValue($strOSConfigName, $intOrderStateIDNew);

                // Add carrier logo.
                $boolResult = copy(
                    _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/views/img/os/'.$objOrderStateItem->logo,
                    _PS_ROOT_DIR_.'/img/os/'.$intOrderStateIDNew.'.gif'
                );
            }

            TNTOfficiel_Logger::logInstall($strLogMessage.' : '.$strOSConfigName);
        }

        return true;
    }
}
