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
 * Class AdminTNTOrdersController
 */
class AdminTNTOrdersController extends ModuleAdminController
{
    public $toolbar_title;
    protected $statuses_array = array();

    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        $this->bootstrap = true;
        $this->table = 'order';
        $this->className = 'Order';
        $this->lang = false;
        $this->addRowAction('view');
        $this->explicitSelect = true;
        $this->allow_export = false;
        $this->deleted = false;

        parent::__construct();

        $this->_select = '
        a.id_currency,
        a.id_order AS id_pdf,
        CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
        osl.`name` AS `osname`,
        os.`color`,
        -- override start.
        `tl`.`label_name` as `BT`,
        `a`.`id_order` as `tntofficiel_id_order`,
        `to`.`pickup_number` as `tntofficiel_pickup_number`,
        c1.`name` AS `carrier`,
        c1.`id_carrier` AS id_carrier,
        -- override end.
        IF(a.valid, 1, 0) badge_success';

        $this->_join = '
        LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
        LEFT JOIN `'._DB_PREFIX_.'address` address ON address.id_address = a.id_address_delivery
        LEFT JOIN `'._DB_PREFIX_.'country` country ON address.id_country = country.id_country
        LEFT JOIN `'._DB_PREFIX_.'country_lang` country_lang
            ON (country.`id_country` = country_lang.`id_country`
                AND country_lang.`id_lang` = '.(int)$this->context->language->id.')
        LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = a.`current_state`)
        LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl
            ON (os.`id_order_state` = osl.`id_order_state`
                AND osl.`id_lang` = '.(int)$this->context->language->id.')
        -- override start.
        JOIN `'._DB_PREFIX_.'carrier` c1 ON (a.`id_carrier` = c1.`id_carrier` AND  c1.`external_module_name` = "'
            .pSQL(TNTOfficiel::MODULE_NAME).'")
        LEFT JOIN `'._DB_PREFIX_.'tntofficiel_order` `to` ON (a.`id_order` = `to`.`id_order`)
        LEFT JOIN `'._DB_PREFIX_.'tntofficiel_label` `tl` ON (a.`id_order` = `tl`.`id_order`)
        -- override end.
        ';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $carriers = Carrier::getCarriers(
            (int)$this->context->language->id,
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );
        $carriers_array = array();
        foreach ($carriers as $carrier) {
            if (TNTOfficielCarrier::isTNTOfficielCarrierID($carrier['id_carrier'])) {
                $carriers_array[$carrier['id_carrier']] = $carrier['name'];
            }
        }

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            ),
        );
        if (Configuration::get('PS_B2B_ENABLE')) {
            $this->fields_list = array_merge(
                $this->fields_list,
                array(
                    'company' => array(
                        'title' => $this->l('Company'),
                        'filter_key' => 'c!company',
                    ),
                )
            );
        }

        $this->fields_list = array_merge(
            $this->fields_list,
            array(
                // Added : Carrier select.
                'carrier' => array(
                    'title' => $this->l('Carrier'),
                    'filter_key' => 'c1!id_carrier',
                    'filter_type' => 'int',
                    'order_key' => 'carrier',
                    'havingFilter' => true,
                    'type' => 'select',
                    'list' => $carriers_array,
                ),
                'total_paid_tax_incl' => array(
                    'title' => $this->l('Total'),
                    'align' => 'text-right',
                    'type' => 'price',
                    'currency' => true,
                    'callback' => 'setOrderCurrency',
                    'badge_success' => true,
                ),
                'payment' => array(
                    'title' => $this->l('Payment'),
                ),
                'osname' => array(
                    'title' => $this->l('Status'),
                    'type' => 'select',
                    'color' => 'color',
                    'list' => $this->statuses_array,
                    'filter_key' => 'os!id_order_state',
                    'filter_type' => 'int',
                    'order_key' => 'osname',
                ),
                'date_add' => array(
                    'title' => $this->l('Date'),
                    'align' => 'text-right',
                    'type' => 'datetime',
                    'filter_key' => 'a!date_add',
                ),
                'id_pdf' => array(
                    'title' => $this->l('PDF'),
                    'align' => 'text-center',
                    'callback' => 'printPDFIcons',
                    'orderby' => false,
                    'search' => false,
                    'remove_onclick' => true,
                ),
                // Added after 'id_pdf': TNT BT.
                'tntofficiel_id_order' => array(
                    'title' => $this->l('TNT'),
                    'align' => 'text-center',
                    'orderby' => false,
                    'search' => false,
                    'callback' => 'printBtIcon',
                    'remove_onclick' => true,
                ),
            )
        );


        $this->fields_list = array_merge(
            $this->fields_list,
            array(
                // Optionally Added : TNT Pickup Number.
                'tntofficiel_pickup_number' => array(
                    'title' => $this->l('Pickup Number'),
                    'align' => 'text-right',
                    'callback' => 'printPickUpNumber',
                    'orderby' => false,
                    'search' => false,
                ),
            )
        );


        $this->shopLinkType = 'shop';
        $this->shopShareDatas = Shop::SHARE_ORDER;

        if (Tools::isSubmit('id_order')) {
            // Save context (in order to apply cart rule)
            $order = new Order((int)Tools::getValue('id_order'));
            $this->context->cart = new Cart($order->id_cart);
            $this->context->customer = new Customer($order->id_customer);
        }

        $this->bulk_actions = array();


        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();
        // If account available for this context.
        if ($objTNTContextAccountModel !== null) {
            if (Shop::getContext() === Shop::CONTEXT_SHOP) {
                $intLangID = (int)$this->context->language->id;
                $objOrderStateShipmentSave = $objTNTContextAccountModel->getOSShipmentSave($intLangID);
                if ($objOrderStateShipmentSave !== null) {
                    $this->bulk_actions += array(
                        // Apply.
                        'updateOrderStatus' => array(
                            'text' => sprintf($this->l('Apply "%s" status'), $objOrderStateShipmentSave->name),
                            'icon' => 'icon-time',
                        ),
                    );
                }
            }
        }

        $this->bulk_actions += array(
            // TNT BT.
            'getBT' => array(
                'text' => $this->l('TNT shipping label'),
                'icon' => 'icon-tnt',
            ),
            // TNT Manifest.
            'getManifest' => array(
                'text' => $this->l('TNT manifest'),
                'icon' => 'icon-file-text',
            ),
            // Update order status for all parcels delivered.
            'updateDelivered' => array(
                // $objOrderStateAllDelivered->name
                'text' => sprintf($this->l('Refresh TNT delivery status')),
                'icon' => 'icon-refresh',
            ),
        );
    }

    /**
     * Load script.
     */
    public function setMedia($isNewTheme = false)
    {
        TNTOfficiel_Logstack::log();

        parent::setMedia(false);

        // Get Order.
        $intOrderIDView = Tools::getValue('vieworder');
        // No order to view : Order list.
        if ($intOrderIDView === false) {
            $this->module->addJS('AdminTNTOrders.js');
        }
    }

    /**
     * Get the current objects' list form the database
     *
     * @param int         $id_lang   Language used for display
     * @param string|null $order_by  ORDER BY clause
     * @param string|null $order_way Order way (ASC, DESC)
     * @param int         $start     Offset in LIMIT clause
     * @param int|null    $limit     Row count in LIMIT clause
     * @param int|bool    $id_lang_shop
     */
    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        TNTOfficiel_Logstack::log();

        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

        $boolDisplayPickupNumberColumn = false;

        foreach ($this->_list as $arrRow) {
            $intCarrierID = (int)$arrRow['id_carrier'];

            $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($intCarrierID, false);
            if ($objTNTCarrierModel === null) {
                continue;
            }

            $objTNTCarrierAccountModel = $objTNTCarrierModel->getTNTAccountModel();
            // If no account available for this carrier.
            if ($objTNTCarrierAccountModel === null) {
                continue;
            }

            $boolDisplayPickupNumberColumn = $boolDisplayPickupNumberColumn
                || ($objTNTCarrierAccountModel->pickup_display_number ? true : false);
        }

        // If no need to display.
        if (!$boolDisplayPickupNumberColumn) {
            // Remove column.
            unset($this->fields_list['tntofficiel_pickup_number']);
        }
    }

    public function initPageHeaderToolbar()
    {
        TNTOfficiel_Logstack::log();

        $this->toolbar_title = array($this->breadcrumbs);
        if (is_array($this->breadcrumbs)) {
            $this->toolbar_title = array_unique($this->breadcrumbs);
        }

        if ($filter = $this->addFiltersToBreadcrumbs()) {
            $this->toolbar_title[] = $filter;
        }

        $this->toolbar_btn = array(
            //'back' => array()
        );

        //this->meta_title
        //$this->addMetaTitle($this->toolbar_title[count($this->toolbar_title) - 1]);
        //$this->page_header_toolbar_title = $this->toolbar_title[count($this->toolbar_title) - 1];
        $this->page_header_toolbar_btn = array();

        $this->show_page_header_toolbar = true;

        parent::initPageHeaderToolbar();

        $this->context->smarty->assign(
            array(
                'help_link' => null,
            )
        );
    }

    public function renderForm()
    {
        TNTOfficiel_Logstack::log();

        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP
            && Shop::isFeatureActive()
        ) {
            $this->errors[] = $this->l('You have to select a shop before creating new orders.');
        }

        parent::renderForm();
    }

    public function renderList()
    {
        TNTOfficiel_Logstack::log();

        if (Tools::isSubmit('submitBulkupdateOrderStatus'.$this->table)) {
            if (Tools::getIsset('cancel')) {
                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
            }

            $this->tpl_list_vars['updateOrderStatus_mode'] = true;
            $this->tpl_list_vars['order_statuses'] = $this->statuses_array;
            $this->tpl_list_vars['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
            // TODO : use Tools::getValue().
            $this->tpl_list_vars['POST'] = $_POST;
        }

        return parent::renderList();
    }

    /**
     * View redirect on AdminOrders order or AdminTNTOrders list (PS1.7.7-).
     *
     * @return string
     */
    public function renderView()
    {
        TNTOfficiel_Logstack::log();

        // Get Order.
        $intOrderIDView = Tools::getValue('vieworder');
        // No order to view : Order list.
        if ($intOrderIDView === false) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminTNTOrders'));
        } else {
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminOrders', false)
                .'&id_order='.Tools::getValue('id_order').'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders')
            );
        }

        return '';
    }

    /**
     * @param $echo
     * @param $tr
     *
     * @return mixed
     */
    public static function setOrderCurrency($echo, $tr)
    {
        TNTOfficiel_Logstack::log();

        $order = new Order($tr['id_order']);

        return Tools::displayPrice($echo, (int)$order->id_currency);
    }

    /**
     * @param $id_order
     * @param $tr
     *
     * @return string|null
     */
    public function printPDFIcons($id_order, $tr)
    {
        TNTOfficiel_Logstack::log();

        static $valid_order_state = array();

        $intOrderID = (int)$id_order;

        $objPSOrder = new Order($intOrderID);
        if (!Validate::isLoadedObject($objPSOrder)) {
            return '';
        }

        if (!isset($valid_order_state[$objPSOrder->current_state])) {
            $valid_order_state[$objPSOrder->current_state] =
                Validate::isLoadedObject($objPSOrder->getCurrentOrderState());
        }

        if (!$valid_order_state[$objPSOrder->current_state]) {
            return '';
        }

        $this->context->smarty->assign(
            array(
                'order' => $objPSOrder,
                'tr' => $tr,
            )
        );

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'tntofficiel/views/templates/admin/_print_pdf_icon.tpl'
        );
    }

    /***
     * @param $id_order
     * @param $tr
     *
     * @return string|null
     */
    public function printBtIcon($id_order, $tr)
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)$id_order;

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            return null;
        }

        $strBTLabelName = '';
        if ($objTNTOrderModel->isExpeditionCreated()) {
            // Load an existing TNT label info.
            $objTNTLabelModel = TNTOfficielLabel::loadOrderID($intOrderID, false);
            // If fail.
            if ($objTNTLabelModel !== null) {
                $strBTLabelName = $objTNTLabelModel->label_name;
            }
        }

        $this->context->smarty->assign(
            array(
                'strBTLabelName' => $strBTLabelName,
                'intOrderID' => $intOrderID,
                'tr' => $tr,
            )
        );

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'tntofficiel/views/templates/admin/_print_bt_icon.tpl'
        );
    }

    /**
     * @param $pickup_number
     * @param $tr
     *
     * @return string|null
     */
    public function printPickUpNumber($pickup_number, $tr)
    {
        TNTOfficiel_Logstack::log();

        $intCarrierID = (int)$tr['id_carrier'];

        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($intCarrierID, false);
        if ($objTNTCarrierModel === null) {
            return null;
        }

        $objTNTCarrierAccountModel = $objTNTCarrierModel->getTNTAccountModel();
        // If no account available for this carrier.
        if ($objTNTCarrierAccountModel === null) {
            return null;
        }

        return ($objTNTCarrierAccountModel->pickup_display_number ? $pickup_number : null);
    }

    /**
     * Downloads an archive containing all the logs files.
     * /<ADMIN>/index.php?controller=AdminTNTOrders&action=downloadLogs
     * /modules/tntofficiel/log/logs.zip
     */
    public function processDownloadLogs()
    {
        TNTOfficiel_Logstack::log();

        // Create Zip.
        $strZipContent = TNTOfficiel_Tools::getZip(
            TNTOfficiel_Logstack::getRootPath(),
            array('log', 'json')
        );

        // Download and exit.
        TNTOfficiel_Tools::download('log.zip', $strZipContent);

        // We want to be sure that downloading is the last thing this controller will do.
        exit;
    }

    /**
     * Apply OrderState for shipment creation.
     */
    public function processBulkUpdateOrderStatus()
    {
        TNTOfficiel_Logstack::log();

        $objCookie = $this->context->cookie;

        $arrOrderID = array();
        if (Tools::getIsset('orderBox')) {
            $arrOrderID = (array)Tools::getValue('orderBox');
        }

        if (count($arrOrderID) === 0) {
            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);

            return;
        }

        $arrErrorMessageList = array();

        foreach ($arrOrderID as $strOrderID) {
            $intOrderID = (int)$strOrderID;
            // Load TNT order info for it's ID.
            $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
            // If fail.
            if ($objTNTOrderModel === null) {
                $arrErrorMessageList[] = sprintf($this->l('Unable to load TNTOfficielOrder #%s'), $intOrderID);
                // Next.
                continue;
            }

            // If already created.
            if ($objTNTOrderModel->isExpeditionCreated()) {
                // Next.
                continue;
            }

            $objOrderStateShipmentSave = $objTNTOrderModel->getOSShipmentSave();
            // If no OrderStatus available for this order carrier account.
            if ($objOrderStateShipmentSave === null) {
                // Next.
                continue;
            }

            $boolUpdatedOS = $objTNTOrderModel->addOrderStateHistory(
                (int)$objOrderStateShipmentSave->id,
                (int)$this->context->employee->id
            );

            if ($boolUpdatedOS === false) {
                $arrErrorMessageList[] = sprintf(
                    $this->l('Unable to apply "%s" OrderState for TNTOfficielOrder #%s'),
                    $objOrderStateShipmentSave->name,
                    $intOrderID
                );
            }
        }

        if (count($arrErrorMessageList) > 0) {
            $objCookie->TNTOfficielError = implode("\n", $arrErrorMessageList);
        } else {
            $objCookie->TNTOfficielSuccess = $this->l('Update successful');
        }

        Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
    }

    /**
     * Concatenate PDF for all the BT for the selected orders.
     *
     * @throws Exception
     */
    public function processBulkGetBT()
    {
        TNTOfficiel_Logstack::log();

        $arrOrderID = (array)Tools::getValue('orderBox');
        $objPDFMerger = new TNTOfficiel_PDFMerger();
        $intBTCounter = 0;

        foreach ($arrOrderID as $strOrderID) {
            $intOrderID = (int)$strOrderID;
            $objPSOrder = new Order($intOrderID);

            // Load an existing TNT carrier.
            $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($objPSOrder->id_carrier, false);
            // If success and carrier is from TNT module.
            if ($objTNTCarrierModel !== null) {
                // Load TNT order info for it's ID.
                $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
                // If fail or expedition is not created.
                if ($objTNTOrderModel === null || !$objTNTOrderModel->isExpeditionCreated()) {
                    continue;
                }
                // Load an existing TNT label info.
                $objTNTLabelModel = TNTOfficielLabel::loadOrderID($intOrderID, false);
                // If fail.
                if ($objTNTLabelModel === null) {
                    continue;
                }

                $strLabelPDFContent = $objTNTLabelModel->getLabelPDFContent();

                if ($objTNTLabelModel->label_name
                    && Tools::strlen($strLabelPDFContent) > 0
                ) {
                    ++$intBTCounter;
                    // Merge pdf BT content.
                    $objPDFMerger->addPDF($objTNTLabelModel->label_name, 'all', $strLabelPDFContent);
                }
            }
        }

        // Concat.
        if ($intBTCounter > 0) {
            $strOutputFileName = 'bt_list.pdf';
            // Download and exit.
            TNTOfficiel_Tools::download(
                $strOutputFileName,
                $objPDFMerger->merge('string', $strOutputFileName),
                'application/pdf'
            );
        }
    }

    /**
     * Return all the Manifest for the selected orders.
     */
    public function processBulkGetManifest()
    {
        TNTOfficiel_Logstack::log();

        if (!Tools::getIsset('orderBox')) {
            return;
        }

        $arrOrderID = (array)Tools::getValue('orderBox');
        $arrOrderIDList = array();
        foreach ($arrOrderID as $strOrderID) {
            $intOrderID = (int)$strOrderID;
            $arrOrderIDList[] = $intOrderID;
        }
        $objManifestPDF = new TNTOfficiel_ManifestPDFCreator();
        $objManifestPDF->createManifest($arrOrderIDList);
    }

    /**
     * Update order parcels tracking state and delivered orderstate accordingly.
     */
    public function processBulkUpdateDelivered()
    {
        TNTOfficiel_Logstack::log();

        $objCookie = $this->context->cookie;

        $arrOrderID = array();
        if (Tools::getIsset('orderBox')) {
            $arrOrderID = (array)Tools::getValue('orderBox');
        }

        if (count($arrOrderID) === 0) {
            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);

            return;
        }

        foreach ($arrOrderID as $intOrderID) {
            $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
            if ($objTNTOrderModel !== null) {
                // Update parcel tracking state and order state accordingly.
                $objTNTOrderModel->updateOrderStateDeliveredParcels();
            }
        }

        $objCookie->TNTOfficielSuccess = $this->l('Update successful');
        Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
    }

    /**
     *
     */
    public function processDownloadBT()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('id_order');

        // Load an existing TNT label info.
        $objTNTLabelModel = TNTOfficielLabel::loadOrderID($intOrderID, false);
        // If fail.
        if ($objTNTLabelModel === null) {
            return;
        }

        $strLabelPDFContent = $objTNTLabelModel->getLabelPDFContent();

        // Download and exit.
        if ($objTNTLabelModel->label_name
            && Tools::strlen($strLabelPDFContent) > 0
        ) {
            TNTOfficiel_Tools::download(
                $objTNTLabelModel->label_name,
                $strLabelPDFContent,
                'application/pdf'
            );
        }

        // We want to be sure that downloading is the last thing this controller will do.
        exit;
    }

    /**
     * Generate the manifest for an order (download).
     */
    public function processGetManifest()
    {
        TNTOfficiel_Logstack::log();

        $objManifestPDF = new TNTOfficiel_ManifestPDFCreator();
        $intOrderID = (int)Tools::getValue('id_order');
        $arrOrderIDList = array($intOrderID);
        $objManifestPDF->createManifest($arrOrderIDList);

        // We want to be sure that downloading is the last thing this controller will do.
        exit;
    }

    /**
     *
     */
    public function displayAjaxSelectPostcodeCities()
    {
        TNTOfficiel_Logstack::log();

        // Check the country
        $strZipCode = pSQL(Tools::getValue('zipcode'));
        $strCity = pSQL(Tools::getValue('city'));

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();
        // If no account available for this context.
        if ($objTNTContextAccountModel === null) {
            return false;
        }

        // Check the city/postcode.
        $arrResultCitiesGuide = $objTNTContextAccountModel->citiesGuide('FR', $strZipCode, $strCity);

        echo Tools::jsonEncode($arrResultCitiesGuide);

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

        $intOrderID = (int)Tools::getValue('id_order');

        $arrResult = array(
            'valid' => true,
            'cities' => array(),
            'postcode' => false,
        );

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            echo Tools::jsonEncode($arrResult);

            return false;
        }

        $objPSAddressDelivery = $objTNTOrderModel->getPSAddressDelivery();
        // If delivery address object is not available.
        if ($objPSAddressDelivery === null) {
            echo Tools::jsonEncode($arrResult);

            return false;
        }

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();
        // If account not available for this context.
        if ($objTNTContextAccountModel === null) {
            echo Tools::jsonEncode($arrResult);

            return false;
        }

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
            'postcode' => $arrResultCitiesGuide['strZipCode'],
        );


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

        $intOrderID = (int)Tools::getValue('id_order');
        $strCity = trim(pSQL(Tools::getValue('city')));

        $arrResult = array(
            'result' => false,
        );

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            echo Tools::jsonEncode($arrResult);

            return false;
        }

        $objPSAddressDelivery = $objTNTOrderModel->getPSAddressDelivery();
        // If delivery address object is not available.
        if ($objPSAddressDelivery === null) {
            echo Tools::jsonEncode($arrResult);

            return false;
        }

        $boolResult = false;
        if ($strCity) {
            $objPSAddressDelivery->city = $strCity;
            $boolResult = $objPSAddressDelivery->save();
        }

        echo Tools::jsonEncode(
            array(
                'result' => $boolResult,
            )
        );

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
     * Get the delivery points popup via Ajax.
     * DROPOFFPOINT (Commerçants Partenaires) : XETT
     * DEPOT (Agences TNT) : PEX
     */
    public function displayAjaxBoxDeliveryPoints()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('id_order');

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            return '';
        }

        $objTNTCarrierModel = $objTNTOrderModel->getTNTCarrierModel();
        if ($objTNTCarrierModel === null) {
            return '';
        }

        $strArgZipCode = trim(pSQL(Tools::getValue('tnt_postcode')));
        $strArgCity = trim(pSQL(Tools::getValue('tnt_city')));

        $objPSAddressDelivery = $objTNTOrderModel->getPSAddressDelivery();
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
        $this->context->smarty->assign(
            array(
                'carrier_type' => $objTNTCarrierModel->carrier_type,
                'current_postcode' => $arrResultDeliveryPoints['strZipCode'],
                'current_city' => $arrResultDeliveryPoints['strCity'],
                'arrRespositoryList' => $arrResultDeliveryPoints['arrPointsList'],
                'cities' => $arrResultDeliveryPoints['arrCitiesNameList'],
            )
        );

        echo $this->context->smarty->fetch(
            _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.
            '/views/templates/front/displayAjaxBoxDeliveryPoints.tpl'
        );

        return true;
    }

    /**
     * Save delivery point info for order.
     *
     * @return bool
     */
    public function displayAjaxSaveProductInfo()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('id_order');
        $strDeliveryPoint = (string)Tools::getValue('product');

        $strDeliveryPointJSON = TNTOfficiel_Tools::inflate($strDeliveryPoint);
        $arrDeliveryPoint = Tools::jsonDecode($strDeliveryPointJSON, true);

        // Load TNT order info or create a new one for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            return false;
        }

        if (!$objTNTOrderModel->setDeliveryPoint($arrDeliveryPoint)) {
            return false;
        }

        // Save TNT order.
        return $objTNTOrderModel->save();
    }

    /**
     * Store Extra Information of Receiver Delivery Address (BO).
     *
     * @return string
     */
    public function displayAjaxStoreReceiverInfo()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('id_order');
        $objOrder = new Order($intOrderID);

        // Load TNT receiver info or create a new one for it's ID.
        $objTNTReceiverModel = TNTOfficielReceiver::loadAddressID($objOrder->id_address_delivery);
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
     * Display the tracking popup.
     *
     * @return bool
     */
    public function displayAjaxTracking()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('orderId');

        // Load TNT order.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel !== null) {
            // Update tracking state.
            $objTNTOrderModel->updateParcelsTrackingState();
            // Get parcels.
            $arrObjTNTParcelModelList = $objTNTOrderModel->getTNTParcelModelList();
            if ((count($arrObjTNTParcelModelList) > 0)) {
                $this->context->smarty->assign(
                    array(
                        'arrObjTNTParcelModelList' => $arrObjTNTParcelModelList,
                    )
                );
                echo $this->context->smarty->fetch(
                    _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/views/templates/front/displayAjaxTracking.tpl'
                );

                return true;
            }
        }

        // 404 fallback.
        Controller::getController('AdminNotFoundController')->run();

        return false;
    }

    /**
     * Add a parcel.
     *
     * @return array
     */
    public function displayAjaxAddParcel()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('orderId');
        $fltWeight = (float)Tools::getValue('weight');

        // Create a parcel.
        $objTNTParcelModel = TNTOfficielParcel::loadParcelID();
        if ($objTNTParcelModel === null) {
            echo Tools::jsonEncode(
                array(
                    'error' => $this->l('Unable to create TNTOfficielParcel'),
                )
            );

            return true;
        }

        // Set order ID and weight.
        $boolResult = $objTNTParcelModel->updateParcel($intOrderID, $fltWeight);
        if (is_string($boolResult)) {
            echo Tools::jsonEncode(
                array(
                    'error' => $boolResult,
                )
            );

            return true;
        }

        echo Tools::jsonEncode(
            array(
                'parcel' => array(
                    'id' => $objTNTParcelModel->id,
                    'weight' => $objTNTParcelModel->weight,
                    'insurance_amount' => $objTNTParcelModel->insurance_amount,
                ),
            )
        );

        return true;
    }

    /**
     * Remove a parcel.
     *
     * @return array
     */
    public function displayAjaxRemoveParcel()
    {
        TNTOfficiel_Logstack::log();

        $intParcelID = (int)Tools::getValue('parcelId');

        $objTNTParcelModel = TNTOfficielParcel::loadParcelID($intParcelID);
        if ($objTNTParcelModel === null) {
            echo Tools::jsonEncode(
                array(
                    'error' => sprintf($this->l('Unable to load TNTOfficielParcel #%s'), $intParcelID),
                )
            );

            return true;
        }

        $boolSuccess = $objTNTParcelModel->delete();
        $arrResult = array(
            'result' => $boolSuccess,
        );

        echo Tools::jsonEncode($arrResult);

        return true;
    }

    /**
     * Update a parcel.
     *
     * @return array
     */
    public function displayAjaxUpdateParcel()
    {
        TNTOfficiel_Logstack::log();

        $intParcelID = (int)Tools::getValue('parcelId');
        $intOrderID = (int)Tools::getValue('orderId');
        $fltWeight = (float)Tools::getValue('weight');
        $fltInsuranceAmount = null;
        if (Tools::getIsset('parcelInsuranceAmount')) {
            $fltInsuranceAmount = (float)Tools::getValue('parcelInsuranceAmount');
        }

        $objTNTParcelModel = TNTOfficielParcel::loadParcelID($intParcelID);
        if ($objTNTParcelModel === null) {
            echo Tools::jsonEncode(
                array(
                    'error' => sprintf($this->l('Unable to load TNTOfficielParcel #%s'), $intParcelID),
                )
            );

            return true;
        }

        $boolResult = $objTNTParcelModel->updateParcel($intOrderID, $fltWeight, $fltInsuranceAmount);
        if (is_string($boolResult)) {
            echo Tools::jsonEncode(
                array(
                    'error' => $boolResult,
                )
            );

            return true;
        }

        echo Tools::jsonEncode(
            array(
                'weight' => $objTNTParcelModel->weight,
                'insurance_amount' => $objTNTParcelModel->insurance_amount,
            )
        );

        return true;
    }

    /**
     * Update all parcels state each 10 minutes.
     *
     * @return array
     */
    public function displayAjaxUpdateOrderStateDeliveredParcels()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('orderId');

        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            echo Tools::jsonEncode(
                array(
                    'error' => sprintf($this->l('Unable to load TNTOfficielOrder #%s'), $intOrderID),
                )
            );

            return true;
        }

        $objTNTCarrierAccountModel = $objTNTOrderModel->getTNTAccountModel();
        if ($objTNTCarrierAccountModel === null) {
            echo Tools::jsonEncode(
                array(
                    'error' => sprintf($this->l('Unable to load TNT Account for TNTOfficielOrder #%s'), $intOrderID),
                )
            );

            return true;
        }

        $strPickUpNumber = $objTNTCarrierAccountModel->pickup_display_number ? $objTNTOrderModel->pickup_number : null;

        $boolDelivered = $objTNTOrderModel->updateOrderStateDeliveredParcels(10 * 60);

        $this->context->smarty->assign(
            array(
                'strPickUpNumber' => $strPickUpNumber,
                'arrObjTNTParcelModelList' => $objTNTOrderModel->getTNTParcelModelList(),
                'isExpeditionCreated' => (bool)$objTNTOrderModel->isExpeditionCreated(),
                'isUpdateParcelsStateAllowed' => (bool)$objTNTOrderModel->isUpdateParcelsStateAllowed(),
                'isAccountInsuranceEnabled' => (bool)$objTNTOrderModel->isAccountInsuranceEnabled(),
            )
        );
        $strTemplate = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME
            .'/views/templates/admin/displayAjaxUpdateOrderStateDeliveredParcels.tpl'
        );

        echo Tools::jsonEncode(
            array(
                // If all parcels delivered and order state delivered is applied.
                'delivered' => ($boolDelivered === true),
                'template' => $strTemplate,
            )
        );

        return true;
    }

    /**
     *  Checks the shipping.
     *
     * @return bool
     */
    public function displayAjaxCheckShippingDateValid()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('orderId');
        $strShippingDate = trim(pSQL(Tools::getValue('shippingDate')));

        $arrPostDate = explode('/', $strShippingDate);
        $strFormatedShippingDate = $arrPostDate[2].'-'.$arrPostDate[1].'-'.$arrPostDate[0];

        $arrResultPickupDate = array(
            'boolIsRequestComError' => false,
            'strResponseMsgError' => null,
            'strResponseMsgWarning' => null,
            'dueDate' => null,
        );

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            $arrResultPickupDate['strResponseMsgError'] = sprintf(
                $this->l('Unable to load TNTOfficielOrder #%s'),
                $intOrderID
            );
        } else {
            // Try to update the requested shipping date.
            $arrResultPickupDate = array_merge(
                $arrResultPickupDate,
                $objTNTOrderModel->updatePickupDate($strFormatedShippingDate)
            );
            // Format due date.
            if (is_string($arrResultPickupDate['dueDate'])) {
                $tempDate = explode('-', $arrResultPickupDate['dueDate']);
                $arrResultPickupDate['dueDate'] = $tempDate[2].'/'.$tempDate[1].'/'.$tempDate[0];
            }
        }

        echo Tools::jsonEncode($arrResultPickupDate);

        return true;
    }

    /**
     * Update HRA.
     *
     * @return array
     */
    public function displayAjaxUpdateHRA()
    {
        TNTOfficiel_Logstack::log();

        $boolSuccess = TNTOfficielCarrier::updateHRAZipCodeList();

        $arrResult = array(
            'result' => $boolSuccess,
        );

        echo Tools::jsonEncode($arrResult);

        return true;
    }
}
