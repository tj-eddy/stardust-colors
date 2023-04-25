<?php

if (defined('__PS_VERSION_')) {
    exit('Restricted Access');
}

require_once(__DIR__ . '/AdminExportChronopost.php');

class AdminExportChronofreshController extends AdminExportChronopostController
{
    /**
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();

        // Translations that will need to be picked up
        $this->l('Customer');
        $this->l('Payment');
        $this->l('Status');
        $this->l('Date');
        $this->l('Waybills');
        $this->l('Account to use');
        $this->l('Saturday delivery');
        $this->l('Weight');
        $this->l('Length');
        $this->l('Height');
        $this->l('Width');
        $this->l('CSS export');
        $this->l('Print all waybills');
        $this->l(
            'For an export, select orders, then in the "Bulk Actions" menu, '
            . 'select the type of export wanted.'
        );
        $this->l("If the download doesn't start automatically, click here to download your file");

        // fields_lists *HAS* to be initiated in constructor, not later
        $this->fields_list = array_merge($this->fields_list, array(
            'dlc'            => array(
                'title'    => $this->l('BBD'),
                'align'    => 'text-center',
                'callback' => 'dlcInput',
                'orderby'  => false,
                'search'   => false
            ),
            'chrono_product' => array(
                'title'    => $this->l('Product to use'),
                'align'    => 'text-center',
                'callback' => 'productInput',
                'orderby'  => false,
                'search'   => false,
                'class'    => 'fixed-width-lg'
            ),
        ));

        $this->fields_list['account']['callback'] = 'accountInputFresh';
        unset($this->fields_list['saturday']);
    }

    /**
     * @param false $isNewTheme
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->removeJS(_MODULE_DIR_ . "chronopost/views/js/exportMenu.js");
        $this->addJS(_MODULE_DIR_ . "chronopost/views/js/exportMenuChronofresh.js");
    }

    /**
     * Display account input
     *
     * @param string $orderId
     *
     * @return false|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function accountInputFresh($orderId)
    {
        $this->accountInputAssign($orderId);

        return $this->context->smarty->fetch(__DIR__ . '/../../views/templates/admin/account_input_fresh.tpl');
    }

    /**
     * Display DLC input
     *
     * @param string $orderId
     *
     * @return false|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function dlcInput($orderId)
    {
        $carrier = $this->getCarrier($orderId);
        $daysAfter = Configuration::get('CHRONOPOST_CHRONOFRESH_DLC');
        $now = new DateTime();

        $dlcDefault = null;
        if ($carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOFRESH_ID') ||
            $carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOFRESH_CLASSIC_ID')) {
            $dlcDefault = $now->add(new DateInterval("P{$daysAfter}D"))->format("Y-m-d");
        }

        $this->context->smarty->assign(array(
            'id_order'    => $orderId,
            'dlc_default' => $dlcDefault
        ));

        return $this->context->smarty->fetch(__DIR__ . '/../../views/templates/admin/dlc_input.tpl');
    }

    /**
     * Get product input
     *
     * @param $orderId
     *
     * @return false|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function productInput($orderId)
    {
        $carrier = $this->getCarrier($orderId);
        $wsHelper = Chronopost::getWsHelper();
        $availChronoFreshCodes = $wsHelper->getChronofreshCodes();

        $availChronoFreshProducts = [];
        if ($carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOFRESH_ID')) {
            foreach ($availChronoFreshCodes as $code) {
                $key = array_search(
                    $code,
                    array_column(Chronopost::$carriersDefinitions['CHRONOFRESH']['products'], 'code')
                );

                if (is_int($key)) {
                    $availChronoFreshProducts[] = Chronopost::$carriersDefinitions['CHRONOFRESH']['products'][$key];
                }
            }
        }

        $this->context->smarty->assign(array(
            'id_order'           => $orderId,
            'available_products' => $availChronoFreshProducts,
            'quickcost_product'  => Configuration::get('CHRONOPOST_QUICKCOST_PRODUCT')
        ));

        return $this->context->smarty->fetch(__DIR__ . '/../../views/templates/admin/product_input.tpl');
    }
}
