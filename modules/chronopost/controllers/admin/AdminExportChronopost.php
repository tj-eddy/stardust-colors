<?php

if (defined('__PS_VERSION_')) {
    exit('Restricted Access');
}

include(dirname(__FILE__) . '/../../chronopost.php');
include(dirname(__FILE__) . '/../../libraries/checkColis.php');

class AdminExportChronopostController extends ModuleAdminController
{

    /**
     * @var array
     */
    private $carrier = [];

    /**
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->table = 'order';
        $this->className = 'Order';
        $this->lang = false;
        $this->bootstrap = true;
        $this->deleted = false;
        $this->explicitSelect = true;
        $this->context = Context::getContext();

        $this->list_no_link = true; // so you can't click on rows. Ignore Prestashop docs.

        $this->_select = '
			a.id_order AS id_pdf,
			a.id_order AS account,
			a.id_order AS saturday,
			a.id_order AS weight,
			a.id_order AS width,
			a.id_order AS height,
			a.id_order AS length,
			a.id_order AS dlc,
			a.id_order AS chrono_product,
			CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
			osl.`name` AS `osname`,
			os.`color`,
			IF((SELECT COUNT(so.id_order) FROM `' . _DB_PREFIX_ . 'orders` so 
			    WHERE so.id_customer = a.id_customer) > 1, 0, 1) as new,
			country_lang.name as cname,
			IF(a.valid, 1, 0) badge_success';

        $this->_join = '
			LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
			INNER JOIN `' . _DB_PREFIX_ . 'carrier` ca ON (ca.`id_carrier` = a.`id_carrier`)
			INNER JOIN `' . _DB_PREFIX_ . 'address` address ON address.id_address = a.id_address_delivery
			INNER JOIN `' . _DB_PREFIX_ . 'country` country ON address.id_country = country.id_country
			INNER JOIN `' . _DB_PREFIX_ . 'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country` 
			    AND country_lang.`id_lang` = ' . (int)$this->context->language->id . ')
			LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)
			LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state`
			    AND osl.`id_lang` = ' . (int)$this->context->language->id . ')';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->_where = Chronopost::buildControllerWhereQuery($this);
        parent::__construct();

        // fields_lists *HAS* to be initiated in constructor, not later
        $this->fields_list = array(
            'id_order' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
            'customer' => array(
                'title'          => $this->l('Customer'),
                'widthColumn'    => 160,
                'width'          => 140,
                'filter_key'     => 'customer',
                'tmpTableFilter' => true
            ),
            'payment'  => array('title' => $this->l('Payment'), 'width' => 100),
            'osname'   => array(
                'title'       => $this->l('Status'),
                'type'        => 'select',
                'color'       => 'color',
                'list'        => $this->statuses_array,
                'filter_key'  => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key'   => 'osname'
            ),
            'date_add' => array(
                'title'      => $this->l('Date'),
                'width'      => 35,
                'align'      => 'right',
                'type'       => 'datetime',
                'filter_key' => 'a!date_add'
            ),
            'id_pdf'   => array(
                'title'    => $this->l('Waybills'),
                'align'    => 'text-center',
                'callback' => 'nbWaybillsInput',
                'orderby'  => false,
                'search'   => false
            ),
            'account'  => array(
                'title'    => $this->l('Account to use'),
                'align'    => 'text-center',
                'callback' => 'accountInput',
                'orderby'  => false,
                'search'   => false
            ),
            'saturday' => array(
                'title'    => $this->l('Saturday delivery'),
                'align'    => 'text-center',
                'callback' => 'saturdayInput',
                'orderby'  => false,
                'search'   => false
            ),
            'weight'   => array(
                'title'    => $this->l('Weight'),
                'align'    => 'text-center',
                'callback' => 'weightInput',
                'orderby'  => false,
                'search'   => false
            ),
            'length'   => array(
                'title'    => $this->l('Length'),
                'align'    => 'text-center',
                'callback' => 'lengthInput',
                'orderby'  => false,
                'search'   => false
            ),
            'height'   => array(
                'title'    => $this->l('Height'),
                'align'    => 'text-center',
                'callback' => 'heightInput',
                'orderby'  => false,
                'search'   => false
            ),
            'width'    => array(
                'title'    => $this->l('Width'),
                'align'    => 'text-center',
                'callback' => 'widthInput',
                'orderby'  => false,
                'search'   => false
            ),
        );

        $this->bulk_actions = array(
            'cssexport' => array(
                'text' => $this->l('CSS export '),
                'icon' => 'icon-save'
            ),
            'waybills'  => array(
                'text' => $this->l('Print all waybills'),
                'icon' => 'icon-print'
            ),
        );

        $this->displayInformation(
            $this->l(
                'For an export, select orders, then in the "Bulk Actions" menu, select the type of export wanted.'
            )
        );

        if (Tools::getIsset('dlfile')) {
            $url = urldecode(Tools::getValue('dlfile'));
            $message = '<a target="_blank" href="' . $url . '">' . $this->l("If the download doesn't start 
            automatically, click here to download your file") . '</a>';
            $message .= '<meta http-equiv="refresh" content="2;url=' . $url . '" />';
            $this->displayInformation($message);
        }
    }


    /**
     * Get carrier from order id
     *
     * @param $orderId
     *
     * @return Carrier|mixed
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getCarrier($orderId)
    {
        if (!isset($this->carrier[$orderId])) {
            $order = new Order($orderId);
            $this->carrier[$orderId] = new Carrier($order->id_carrier);
        }

        return $this->carrier[$orderId];
    }

    /**
     * Set media
     *
     * @param $isNewTheme
     *
     * @return void
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        if (version_compare(_PS_VERSION_, '1.7.7', '<=')) {
            $this->addJquery();
        }

        $this->addJS(_MODULE_DIR_ . "chronopost/views/js/exportMenu.js");
    }

    /**
     * Get weight input
     *
     * @param $orderId
     *
     * @return false|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function weightInput($orderId)
    {
        $order = new Order($orderId);
        $nbwb = Chronopost::minNumberOfPackages($orderId);
        $weight = round(($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF')) / $nbwb, 2);
        $this->context->smarty->assign(array(
            'weight'   => $weight,
            'id_order' => $orderId
        ));

        return $this->context->smarty->fetch(dirname(__FILE__) . '/../../views/templates/admin/weight_input.tpl');
    }

    /**
     * Get width input
     *
     * @param $orderId
     *
     * @return false|string
     * @throws SmartyException
     */
    public function widthInput($orderId)
    {
        $this->context->smarty->assign(array(
            'id_order' => $orderId,
        ));

        return $this->context->smarty->fetch(dirname(__FILE__) . '/../../views/templates/admin/width_input.tpl');
    }

    /**
     * Get height input
     *
     * @param $orderId
     *
     * @return false|string
     * @throws SmartyException
     */
    public function heightInput($orderId)
    {
        $this->context->smarty->assign(array(
            'id_order' => $orderId,
        ));

        return $this->context->smarty->fetch(dirname(__FILE__) . '/../../views/templates/admin/height_input.tpl');
    }

    /**
     * Get length input
     *
     * @param $orderId
     *
     * @return false|string
     * @throws SmartyException
     */
    public function lengthInput($orderId)
    {
        $this->context->smarty->assign(array(
            'id_order' => $orderId,
        ));

        return $this->context->smarty->fetch(dirname(__FILE__) . '/../../views/templates/admin/length_input.tpl');
    }

    /**
     * Get saturday input
     *
     * @param $orderId
     *
     * @return false|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function saturdayInput($orderId)
    {
        $order = new Order($orderId);
        if ((bool)Chronopost::getSaturdaySupplement($order->id_cart)) {
            $saturdayOk = true;
        } elseif (Configuration::get('CHRONOPOST_SATURDAY_CUSTOMER') === 'yes') {
            $saturdayOk = false;
        } else {
            $saturdayOk = Chronopost::isSaturdayOptionApplicable();
        }

        $carrier = new Carrier($order->id_carrier);
        $this->context->smarty->assign(array(
            'id_order'                    => $orderId,
            'saturday'                    => Chronopost::gettingReadyForSaturday($carrier) || (
                (bool)Chronopost::getSaturdaySupplement($order->id_cart) &&
                Chronopost::gettingReadyForSaturday($carrier, true)
            ) ? 1 : 0,
            'saturday_ok'                 => $saturdayOk ? 1 : 0,
            'saturday_supplement_enabled' => Configuration::get('CHRONOPOST_SATURDAY_CUSTOMER') === 'yes',
        ));

        return $this->context->smarty->fetch(dirname(__FILE__) . '/../../views/templates/admin/saturday_input.tpl');
    }

    /**
     * Get account input assign
     *
     * @param $orderId
     *
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function accountInputAssign($orderId)
    {
        $carrier = $this->getCarrier($orderId);

        $ltHistory = DB::getInstance()->executeS(
            'SELECT lt, account_number, product, zipcode, country, insurance, city  FROM '
            . _DB_PREFIX_ . 'chrono_lt_history WHERE id_order = (' . (int) $orderId . ') AND cancelled IS NULL'
        );

        $isFreshCarrier = true;
        $disable = false;
        $isChronoFreshCarrier = Chronofresh::isChronoFreshCarrier($carrier);
        $isChronoFreshClassicCarrier = Chronofresh::isChronoFreshClassicCarrier($carrier);
        if (!$isChronoFreshCarrier && !$isChronoFreshClassicCarrier) {
            $isFreshCarrier = false;
            $disable = (!empty($ltHistory));
        }

        $accountUsed = false;
        if (is_array($ltHistory) && isset($ltHistory[0]['account_number'])) {
            $accountUsed = $ltHistory[0]['account_number'];
        }

        $productCode = Chronopost::getCodeFromCarrier($carrier->id_reference);
        $defaultAccount = Chronopost::getAccountInformationByAccountNumber(Configuration::get('CHRONOPOST_' . $productCode . '_ACCOUNT'));
        $wsHelper = Chronopost::getWsHelper();
        $availContracts = $wsHelper->getContractsForProduct(Chronopost::$carriersDefinitions[$productCode]);

        $this->context->smarty->assign(array(
            'id_order'           => $orderId,
            'disable'            => $disable,
            'default_account'    => $defaultAccount,
            'available_accounts' => $availContracts,
            'account_used'       => $accountUsed,
            'is_chronofresh'     => false //$isFreshCarrier
        ));
    }

    /**
     * Get account input
     *
     * @param $orderId
     *
     * @return false|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function accountInput($orderId)
    {
        $this->accountInputAssign($orderId);

        return $this->context->smarty->fetch(dirname(__FILE__) . '/../../views/templates/admin/account_input.tpl');
    }

    /**
     * Get waybill input
     *
     * @param $orderId
     *
     * @return false|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function nbWaybillsInput($orderId)
    {
        $carrier = $this->getCarrier($orderId);
        $multiParcelsAvailable = true;

        if ($carrier->id_reference === Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID') || $carrier->id_reference === Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID')) {
            $multiParcelsAvailable = false;
        }

        $isFreshCarrier = Chronofresh::isChronoFreshCarrier($carrier) || Chronofresh::isChronoFreshClassicCarrier($carrier);

        $this->context->smarty->assign(array(
            'id_order'              => $orderId,
            'nbwb'                  => Chronopost::minNumberOfPackages($orderId),
            'multiParcelsAvailable' => $multiParcelsAvailable,
            'is_fresh_carrier'      => false //$isFreshCarrier
        ));

        return $this->context->smarty->fetch(dirname(__FILE__) . '/../../views/templates/admin/nb_waybill_input.tpl');
    }

    /**
     * Process bulk export
     *
     * @return void
     * @throws PrestaShopException
     */
    public function processBulkcsoexport()
    {
        $orderBox = Tools::getValue('orderBox');

        if (empty($orderBox)) {
            $this->displayWarning($this->l('You must selected orders for the export'));

            return;
        }

        $url = '../modules/chronopost/importExport.php?shared_secret='
            . Configuration::get('CHRONOPOST_SECRET')
            . '&cible=CSO&orders=' . implode(';', Tools::getValue('orderBox'))
            . '&multi=' . urlencode(Tools::jsonEncode($this->cleanArray(Tools::getValue('multi'),
                Tools::getValue('orderBox'))));

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminExportChronopost')
            . '&dlfile=' . urlencode($url));
    }

    /**
     * Process bulk export
     *
     * @return void
     * @throws PrestaShopException
     */
    public function processBulkcssexport()
    {
        $orderBox = Tools::getValue('orderBox');

        if (empty($orderBox)) {
            $this->displayWarning($this->l('You must selected orders for the export'));

            return;
        }

        $url = '../modules/chronopost/importExport.php?shared_secret='
            . Configuration::get('CHRONOPOST_SECRET')
            . '&cible=CSS&orders=' . implode(';', Tools::getValue('orderBox'))
            . '&multi=' . urlencode(Tools::jsonEncode($this->cleanArray(Tools::getValue('multi'),
                Tools::getValue('orderBox'))));

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminExportChronopost')
            . '&dlfile=' . urlencode($url));
    }

    /**
     * Process bulkway bills
     *
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function processBulkwaybills()
    {
        $orderBox = Tools::getValue('orderBox');

        if (empty($orderBox)) {
            $this->errors[] = sprintf(Tools::displayError($this->l('You must selected orders for the export')));

            return;
        }

        $coef = Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF');
        foreach ($orderBox as $order) {
            $ltHistory = DB::getInstance()->executeS(
                'SELECT lt, account_number, product, zipcode, country, insurance, city  FROM '
                . _DB_PREFIX_ . 'chrono_lt_history WHERE id_order = (' . (int) $order . ') AND cancelled IS NULL'
            );

            $result = json_decode(checkColis::check(
                new Order($order),
                Tools::getValue('weight')[$order],
                Tools::getValue('width')[$order],
                Tools::getValue('height')[$order],
                Tools::getValue('length')[$order],
                $coef
            ), 1);

            if ($result['error'] !== 0) {
                $this->errors[] = sprintf(Tools::displayError($this->l($result['message'])));

                return;
            }

            $ltFound = !empty($ltHistory);
            if ($ltFound) {
                $this->errors[] = sprintf(Tools::displayError($this->l('Order was already shipped')));
            }
        }

        if (!empty($orderBox)) {
            Tools::redirectAdmin(
                '../modules/chronopost/postSkybill.php?shared_secret='
                . Configuration::get('CHRONOPOST_SECRET')
                . '&orders=' . implode(';', Tools::getValue('orderBox'))
                . '&multi=' . addslashes(Tools::jsonEncode($this->cleanArray(Tools::getValue('multi'),
                    Tools::getValue('orderBox'))))
                . '&accounts=' . addslashes(Tools::jsonEncode($this->cleanArray(Tools::getValue('account'),
                    Tools::getValue('orderBox'))))
                . '&shipSaturdays=' . addslashes(Tools::jsonEncode($this->cleanArray(Tools::getValue('shipSaturday'),
                    Tools::getValue('orderBox'))))
                . '&weights=' . addslashes(Tools::jsonEncode($this->cleanArray(Tools::getValue('weight'),
                    Tools::getValue('orderBox'))))
                . '&widths=' . addslashes(Tools::jsonEncode($this->cleanArray(Tools::getValue('width'),
                    Tools::getValue('orderBox'))))
                . '&lengths=' . addslashes(Tools::jsonEncode($this->cleanArray(Tools::getValue('length'),
                    Tools::getValue('orderBox'))))
                . '&heights=' . addslashes(Tools::jsonEncode($this->cleanArray(Tools::getValue('height'),
                    Tools::getValue('orderBox'))))
                . '&dlc=' . addslashes(Tools::jsonEncode($this->cleanArray(Tools::getValue('dlc'),
                    Tools::getValue('orderBox'))))
                . '&chrono_products=' . addslashes(Tools::jsonEncode($this->cleanArray(Tools::getValue('chrono_product'),
                    Tools::getValue('orderBox'))))
            );
        }
    }

    /**
     * Clean array
     *
     * @param $array
     * @param $orders
     *
     * @return array
     */
    public function cleanArray($array, $orders)
    {
        $newArray = [];

        if (!is_array($array)) {
            return $newArray;
        }

        foreach ($orders as $order) {
            if (array_key_exists($order, $array)) {
                $newArray[$order] = $array[$order];
            }
        }

        return $newArray;
    }

    /**
     * Check if array is empty
     *
     * @param array $array
     *
     * @return bool
     */
    private function isEmpty(array $array)
    {
        $empty = true;
        array_walk_recursive($array, function ($leaf) use (&$empty) {
            if ($leaf === [] || $leaf === '') {
                return;
            }

            $empty = false;
        });

        return $empty;
    }

    /**
     * Init toolbar
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        // Remove "Add" button from toolbar
        unset($this->toolbar_btn['new']);
        unset($this->toolbar_btn['export']);
    }

    /**
     * Init content
     *
     * @return void
     */
    public function initContent()
    {
        if (isset($_SESSION['chronopost_errors'])) {
            foreach ($_SESSION['chronopost_errors'] as $message) {
                $this->errors[] = $message;
            }
            unset($_SESSION['chronopost_errors']);
        }

        return parent::initContent();
    }

    /**
     * Translate
     *
     * @param $string
     * @param $class
     * @param $addslashes
     * @param $htmlentities
     *
     * @return mixed|string
     * @throws Exception
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Translate::getModuleTranslation(
            'chronopost',
            $string,
            Tools::substr(get_class($this), 0, -10)
        );
    }
}
