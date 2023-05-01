<?php
/**
 * 1961-2019 BNP Paribas
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1961-2019 BNP Paribas
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once(dirname(__FILE__).'/classes/MercanetAcquirerResponseCode.php');
require_once(dirname(__FILE__).'/classes/MercanetApi.php');
require_once(dirname(__FILE__).'/classes/MercanetComplementaryCode.php');
require_once(dirname(__FILE__).'/classes/MercanetCustomerPaymentRecurring.php');
require_once(dirname(__FILE__).'/classes/MercanetHistory.php');
require_once(dirname(__FILE__).'/classes/MercanetNotification.php');
require_once(dirname(__FILE__).'/classes/MercanetNxPayment.php');
require_once(dirname(__FILE__).'/classes/MercanetOrderQueue.php');
require_once(dirname(__FILE__).'/classes/MercanetOrderReference.php');
require_once(dirname(__FILE__).'/classes/MercanetPaymentRecurring.php');
require_once(dirname(__FILE__).'/classes/MercanetResponseCode.php');
require_once(dirname(__FILE__).'/classes/MercanetSchedule.php');
require_once(dirname(__FILE__).'/classes/MercanetTransaction.php');
require_once(dirname(__FILE__).'/classes/MercanetWallet.php');
require_once(dirname(__FILE__).'/classes/MercanetWebservice.php');
require_once(dirname(__FILE__).'/classes/MercanetLogger.php');
require_once(dirname(__FILE__).'/classes/MercanetReferencePayed.php');

class Mercanet extends PaymentModule
{

    protected $config_form = false;
    protected $config_single_values_keys = false;

    public function __construct()
    {
        $this->name = 'mercanet';
        $this->tab = 'payments_gateways';
        $this->version = '1.6.12';
        $this->author = 'Prestashop partners';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = 'c257d350b04b1e7559c9a909087e6ad1';

        parent::__construct();

        // Mercanet Helper Form Class
        require_once(dirname(__FILE__).'/classes/MercanetHelperForm.php');

        $this->displayName = $this->l('BNP Parisbas - Mercanet');
        $this->description = $this->l('Accept online and mobile payments on a secure page with BNP Paribas Mercanet official module.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall BNP Parisbas - Mercanet?');

        // Currencies available
        $this->limited_currencies = $this->getAvailableCurrencies();

        $this->ps_versions_compliancy = array(
            'min' => '1.6',
            'max' => _PS_VERSION_);

        // VALIDATION A FAIRE
        $this->config_single_values_keys = array(
            'MERCANET_ACTIVATION_KEY',
            'MERCANET_MERCHANT_ID',
            'MERCANET_SECRET_KEY',
            'MERCANET_KEY_VERSION',
            'MERCANET_DEFAULT_LANG',
            'MERCANET_3DS_ACTIVE',
            'MERCANET_3DS_MIN_AMOUNT',
            'MERCANET_PEC_ACTIVE',
            'MERCANET_PIP_ACTIVE',
            'MERCANET_SCP_ACTIVE',
            'MERCANET_A3D_ACTIVE',
            'MERCANET_CCO_ACTIVE',
            'MERCANET_CVI_ACTIVE',
            'MERCANET_LNC_ACTIVE',
            'MERCANET_AMT_ACTIVE',
            'MERCANET_ECC_ACTIVE',
            'MERCANET_ECI_ACTIVE',
            'MERCANET_LOG_ACTIVE',
            'MERCANET_LOG_ACCESS',
            'MERCANET_PRE_ACTIVE',
            'MERCANET_COUNTRIES_LIST[]',
            'MERCANET_CURRENCIES_LIST[]',
            'MERCANET_PAYMENT_VALIDATION_MODE',
            'MERCANET_BANK_DEPOSIT_NB_DAYS',
            'MERCANET_CARD_ALLOWED[]',
            'MERCANET_CSS_THEME_CONFIG',
            'MERCANET_CARD_DISPLAY_METHOD',
            'MERCANET_AUTOMATIC_REDIRECT_PAYMENT',
            'MERCANET_NOTIFY_CUSTOMER',
            'MERCANET_ONE_CLICK_ACTIVE',
            'MERCANET_TEST_MODE',
            'MERCANET_TEST_USER',
        );

        // Inputs by tab
        $this->single_values_keys_by_tabs = array(
            'credentials' => array(
                'MERCANET_ACTIVATION_KEY',
                'MERCANET_MERCHANT_ID',
                'MERCANET_SECRET_KEY',
                'MERCANET_KEY_VERSION',
                'MERCANET_TEST_MODE',
                'MERCANET_TEST_USER',
            ),
            'general' => array(
                'MERCANET_DEFAULT_LANG',
                'MERCANET_COUNTRIES_LIST',
                'MERCANET_CURRENCIES_LIST',
                'MERCANET_BANK_DEPOSIT_NB_DAYS',
                'MERCANET_CARD_ALLOWED',
                'MERCANET_CARD_DISPLAY_METHOD',
                'MERCANET_CSS_THEME_CONFIG',
                'MERCANET_3DS_ACTIVE',
                'MERCANET_3DS_MIN_AMOUNT',
                'MERCANET_PAYMENT_VALIDATION_MODE',
                'MERCANET_AUTOMATIC_REDIRECT_PAYMENT',
                'MERCANET_NOTIFY_CUSTOMER',
                'MERCANET_ONE_CLICK_ACTIVE',
                'MERCANET_LOG_ACTIVE',
                'MERCANET_LOG_ACCESS',
                'MERCANET_PEC_ACTIVE',
                'MERCANET_PIP_ACTIVE',
                'MERCANET_SCP_ACTIVE',
                'MERCANET_A3D_ACTIVE',
                'MERCANET_CCO_ACTIVE',
                'MERCANET_CVI_ACTIVE',
                'MERCANET_LNC_ACTIVE',
                'MERCANET_AMT_ACTIVE',
                'MERCANET_ECC_ACTIVE',
                'MERCANET_ECI_ACTIVE',
                'MERCANET_PRE_ACTIVE',
                'MERCANET_TEST_MODE',
                'MERCANET_TEST_USER',
            ),
            'payment_one_time' => array(
                'MERCANET_ONE_TIME_NAME',
                'MERCANET_ONE_TIME_ACTIVE',
                'MERCANET_ONE_TIME_MIN_AMOUNT',
                'MERCANET_ONE_TIME_MAX_AMOUNT'
            ),
            'payment_nx_time' => array(
                'MERCANET_NX_TIME_NAME',
                'MERCANET_NX_TIME_ACTIVE',
            ),
            'payment_recurring' => array(
                'MERCANET_RECURRING_NAME',
                'MERCANET_RECURRING_ACTIVE',
            ),
        );

        $this->empty_config_single_values_keys = array(
            'MERCANET_SECRET_KEY',
        );
    }

    /**
     * Install
     * @return boolean
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        if (extension_loaded('openssl') == false) {
            $this->_errors[] = $this->l('You have to enable the OpenSSL extension on your server to install this module');
            return false;
        }
        // TAB
        $this->installModuleTab(
            'AdminMercanetnxpayment',
            array(
                Language::getIdByIso('fr') => 'Mercanet liste des paiements',
                Language::getIdByIso('en') => 'Mercanet payment list'
            ),
            Tab::getIdFromClassName('AdminPayment'),
            false
        );
        $this->installModuleTab(
            'AdminMercanettransaction',
            array(
                Language::getIdByIso('fr') => 'Mercanet liste des transactions',
                Language::getIdByIso('en') => 'Mercanet transaction list'
            ),
            Tab::getIdFromClassName('AdminParentOrders'),
            true
        );

        $this->installModuleTab(
            'AdminMercanetRecurring',
            array(
                Language::getIdByIso('fr') => 'Mercanet liste des abonnements',
                Language::getIdByIso('en') => 'Mercanet Recurring list'
            ),
            Tab::getIdFromClassName('AdminParentOrders'),
            true
        );
        // Default Configuration
        $id_lang_fr = Language::getIdByIso('fr');

        // Global
        if (!Configuration::haskey('MERCANET_TEST_MODE')) {
            Configuration::updateGlobalValue('MERCANET_TEST_MODE', 1);
            Configuration::updateGlobalValue('MERCANET_TEST_USER', 1);
        }
        Configuration::updateGlobalValue('MERCANET_TEST_ACCOUNT', '211000021310001');
        Configuration::updateGlobalValue('MERCANET_TEST_KEY_SECRET', 'S9i8qClCnb2CZU3y3Vn0toIOgz3z_aBi79akR30vM9o');
        Configuration::updateGlobalValue('MERCANET_TEST_KEY_VERSION', '1');
        

        // default value
        if (!Configuration::get('MERCANET_MERCHANT_ID')) {
            Configuration::updateGlobalValue('MERCANET_MERCHANT_ID', '211000021310001');
        }
        if (!Configuration::get('MERCANET_SECRET_KEY')) {
            Configuration::updateGlobalValue('MERCANET_SECRET_KEY', 'S9i8qClCnb2CZU3y3Vn0toIOgz3z_aBi79akR30vM9o');
        }
        if (!Configuration::get('MERCANET_KEY_VERSION')) {
            Configuration::updateGlobalValue('MERCANET_KEY_VERSION', '1');
        }
        if (!Configuration::get('MERCANET_COUNTRIES_LIST')) {
            Configuration::updateGlobalValue('MERCANET_COUNTRIES_LIST', 'ALL');
        }
        
        Configuration::updateGlobalValue('MERCANET_PAYMENT_PAGE_URL', 'https://payment-webinit.mercanet.bnpparibas.net/paymentInit');
        Configuration::updateGlobalValue('MERCANET_TEST_PAYMENT_PAGE_URL', 'https://payment-webinit-mercanet.test.sips-services.com/paymentInit');
        Configuration::updateGlobalValue('MERCANET_DEFAULT_LANG', (!empty($id_lang_fr)) ? Language::getIdByIso('fr') : Configuration::get('PS_LANG_DEFAULT'));
        Configuration::updateGlobalValue('MERCANET_INTERFACE_VERSION', 'HP_2.19');
        Configuration::updateGlobalValue('MERCANET_EURO_ISO_CODE_NUM', '978');
        Configuration::updateGlobalValue('MERCANET_CANCEL_RC', '17');
        Configuration::updateGlobalValue('MERCANET_PEC_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_PIP_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_SCP_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_LOG_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_LOG_ACCESS', true);
        Configuration::updateGlobalValue('MERCANET_A3D_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_CCO_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_CVI_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_LNC_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_AMT_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_ECC_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_ECI_ACTIVE', true);
        Configuration::updateGlobalValue('MERCANET_BANK_DEPOSIT_NB_DAYS', 0);
        Configuration::updateGlobalValue('MERCANET_PAYMENT_VALIDATION_MODE', 'AUTHOR_CAPTURE');

        // Cetelem 3X
        Configuration::updateGlobalValue('MERCANET_F3CB_NAME', 'CETELEM_3X');
        Configuration::updateGlobalValue('MERCANET_F3CB_MIN_AMOUNT', 100);
        Configuration::updateGlobalValue('MERCANET_F3CB_MAX_AMOUNT', 3000);

        // Cetelem 4X
        Configuration::updateGlobalValue('MERCANET_F4CB_NAME', 'CETELEM_4X');
        Configuration::updateGlobalValue('MERCANET_F4CB_MIN_AMOUNT', 100);
        Configuration::updateGlobalValue('MERCANET_F4CB_MAX_AMOUNT', 3000);

        // One Time
        Configuration::updateGlobalValue(
            'MERCANET_ONE_TIME_NAME',
            array(
            Language::getIdByIso('fr') => 'Paiement sécurisé par carte via Mercanet',
            Language::getIdByIso('en') => 'Card payment secured by Mercanet'
            )
        );
        Configuration::updateGlobalValue('MERCANET_ONE_TIME_ACTIVE', true);
        // Nx Time
        Configuration::updateGlobalValue(
            'MERCANET_NX_TIME_NAME',
            array(
            Language::getIdByIso('fr') => 'Paiement sécurisé par carte en plusieurs fois via Mercanet',
            Language::getIdByIso('en') => 'Card payment in several times secured by Mercanet'
            )
        );
        $this->registerOrderStatus();
        Configuration::updateGlobalValue('MERCANET_NX_MAX_DAYS', 89);

        // Recurring
        Configuration::updateGlobalValue(
            'MERCANET_RECURRING_NAME',
            array(
            Language::getIdByIso('fr') => 'Paiement par abonnement sécurisé par carte via Mercanet',
            Language::getIdByIso('en') => 'Card payment recurring secured by Mercanet'
            )
        );

        // Webservice
        Configuration::updateGlobalValue('MERCANET_WS_URL', 'https://office-server.mercanet.bnpparibas.net/rs-services/v2/');
        Configuration::updateGlobalValue('MERCANET_WS_URL_TEST', 'https://office-server-mercanet.test.sips-services.com/rs-services/v2/');
        Configuration::updateGlobalValue('MERCANET_WS_INTERFACE_VERSION', 'CR_WS_2.6');

        // Wallet
        Configuration::updateGlobalValue('MERCANET_WT_URL', 'https://payment-webinit.mercanet.bnpparibas.net/walletManagementInit');
        Configuration::updateGlobalValue('MERCANET_WT_URL_TEST', 'https://payment-webinit-mercanet.test.sips-services.com/walletManagementInit');
        Configuration::updateGlobalValue('MERCANET_WT_INTERFACE_VERSION', 'HP_2.0');

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('displayCustomerAccount') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('paymentReturn');
    }

    /**
     * Uninstall
     * @return type
     */
    public function uninstall()
    {
        Configuration::deleteByName('MERCANET_ACTIVATION_KEY');

        // TAB
        $this->uninstallModuleTab('AdminMercanetnxpayment');
        $this->uninstallModuleTab('AdminMercanettransaction');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Create Be2bill order status pending
     * @return boolean
     */
    public function registerOrderStatus()
    {
        if ((int)Configuration::getGlobalValue('MERCANET_NX_OS_PAYMENT') == 0) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = array();

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'Mercanet -Paiement en plusieurs fois';
                } else {
                    $order_state->name[$language['id_lang']] = 'Mercanet -Payment in several times';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#0074A1';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            $order_state->send_email = false;
            $order_state->template = '';

            if ($order_state->save()) {
                // Logo
                copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'os/'.(int)$order_state->id.'.gif');

                Configuration::updateGlobalValue('MERCANET_NX_OS_PAYMENT', (int)$order_state->id);
            }
        }

        // Payment successful
        if ((int)Configuration::getGlobalValue('MERCANET_RECURRING_OS_PAYMENT') == 0) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = array();

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'Mercanet -Paiement par abonnement réussi';
                } else {
                    $order_state->name[$language['id_lang']] = 'Mercanet -Payment recurring successful';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#0074A1';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = true;
            $order_state->pdf_invoice = true;
            $order_state->send_email = true;
            $order_state->paid = true;
            $order_state->template = 'mercanet_payment_recurring_successful';

            if ($order_state->save()) {
                // Logo
                copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'os/'.(int)$order_state->id.'.gif');

                // Email
                copy(_PS_MODULE_DIR_.$this->name.'/mails/en/mercanet_payment_recurring_successful.html', _PS_MAIL_DIR_.'en/mercanet_payment_recurring_successful.html');
                copy(_PS_MODULE_DIR_.$this->name.'/mails/en/mercanet_payment_recurring_successful.txt', _PS_MAIL_DIR_.'en/mercanet_payment_recurring_successful.txt');
                copy(_PS_MODULE_DIR_.$this->name.'/mails/fr/mercanet_payment_recurring_successful.html', _PS_MAIL_DIR_.'fr/mercanet_payment_recurring_successful.html');
                copy(_PS_MODULE_DIR_.$this->name.'/mails/fr/mercanet_payment_recurring_successful.txt', _PS_MAIL_DIR_.'fr/mercanet_payment_recurring_successful.txt');
                Configuration::updateGlobalValue('MERCANET_RECURRING_OS_PAYMENT', (int)$order_state->id);
            }
        }

        // Payment recurring error
        if ((int)Configuration::getGlobalValue('MERCANET_RECURRING_OS_ERROR') == 0) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = array();

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'Mercanet -Paiement par abonnement en erreur';
                } else {
                    $order_state->name[$language['id_lang']] = 'Mercanet -Payment recurring error';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#8f0621';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            $order_state->send_email = true;
            $order_state->paid = false;
            $order_state->template = 'mercanet_payment_recurring_error';

            if ($order_state->save()) {
                copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'os/'.(int)$order_state->id.'.gif');
                // Logo
                Configuration::updateGlobalValue('MERCANET_RECURRING_OS_ERROR', (int)$order_state->id);

                // Email
                copy(_PS_MODULE_DIR_.$this->name.'/mails/en/mercanet_payment_recurring_error.html', _PS_MAIL_DIR_.'en/mercanet_payment_recurring_error.html');
                copy(_PS_MODULE_DIR_.$this->name.'/mails/en/mercanet_payment_recurring_error.txt', _PS_MAIL_DIR_.'en/mercanet_payment_recurring_error.txt');
                copy(_PS_MODULE_DIR_.$this->name.'/mails/fr/mercanet_payment_recurring_error.html', _PS_MAIL_DIR_.'fr/mercanet_payment_recurring_error.html');
                copy(_PS_MODULE_DIR_.$this->name.'/mails/fr/mercanet_payment_recurring_error.txt', _PS_MAIL_DIR_.'fr/mercanet_payment_recurring_error.txt');
            }
        }

        return true;
    }

    /**
     * Install TAB
     * @param string $tab_class
     * @param string $tab_name
     * @param integer $id_tab_parent
     * @return boolean
     */
    public function installModuleTab($tab_class, $tab_name, $id_tab_parent, $active = true)
    {
        $tab = new Tab();
        $languages = Language::getLanguages(true);
        foreach ($languages as $lang) {
            if (isset($tab_name[$lang['id_lang']])) {
                $tab->name[$lang['id_lang']] = $tab_name[$lang['id_lang']];
            } else {
                $tab->name[$lang['id_lang']] = $tab_name[Language::getIdByIso('en')];
            }
        }
        $tab->class_name = $tab_class;
        $tab->module = $this->name;
        $tab->id_parent = (int)$id_tab_parent;
        $tab->active = $active;
        if (!$tab->save()) {
            return false;
        }
        return true;
    }

    /**
     * Uninstall Tab
     * @param string $tab_class
     * @return boolean
     */
    private function uninstallModuleTab($tab_class)
    {
        $id_tab = Tab::getIdFromClassName($tab_class);
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            $tab->delete();
            return true;
        }
        return false;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {

        /**
         * If values have been submitted in the form, process.
         */
        // Check which one has been sent
        if (Tools::isSubmit('submitCredentials')) {
            $tab = 'credentials';
        }

        if (Tools::isSubmit('submitGeneral')) {
            $tab = 'general';
        }

        if (Tools::isSubmit('submitOneTime')) {
            $tab = 'payment_one_time';
        }

        if (Tools::isSubmit('submitNxTime')) {
            $tab = 'payment_nx_time';
        }

        if (Tools::isSubmit('submitRecurring')) {
            $tab = 'payment_recurring';
        }

        if (((bool)Tools::isSubmit('submitMercanetModule')) == true) {
            $this->postProcess($tab);
        }

        // Init if empty
        if (empty($tab)) {
            $tab = 'credentials';
        }

        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'tab' => $tab));

        $template = Configuration::get('MERCANET_ACTIVATION_KEY', null) ? 'configure.tpl' : 'configure-new-merchant.tpl';

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/'.$template);

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::getGlobalValue('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMercanetModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array(
                array(
                    'form' => $this->getConfigForm()
                )
        ));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        // Init Mercanet Helper Form
        $mercarnet_helper_form = new MercanetHelperForm();

        $form = array(
            'legend' => array(
                'title' => $this->l('Mercanet Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 3,
                    'label' => $this->l('Enter your activation key'),
                    'name' => 'MERCANET_ACTIVATION_KEY',
                    'desc' => $this->l('This key is provided by BNP Paribas'),
                ),
            ),
        );

        // if not activated, only show the activation file field
        if (!Configuration::get('MERCANET_ACTIVATION_KEY', null)) {
            // Submit Credentials Tab
            $form['input'][] = $mercarnet_helper_form->getSubmitForm('credentials', 'submitCredentials');
            return $form;
        }

        //======================================================================
        // CREDENTIALS TAB
        $form['tabs']['credentials'] = $this->l('Credentials');

        // if activated, file field is an update
        $form['input'][0]['tab'] = 'credentials';

        // Merchant ID
        $form['input'][] = array(
            'tab' => 'credentials',
            'col' => 2,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Enter your Merchant ID'),
            'name' => 'MERCANET_MERCHANT_ID',
            'desc' => $this->l('This ID is provided by BNP Paribas'),
        );

        // Merchant Secret Key
        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'password',
            'cols' => 3,
            'required' => true,
            'label' => $this->l('Enter your Secret Key'),
            'name' => 'MERCANET_SECRET_KEY',
            'desc' => $this->l('This secret key is provided by BNP Paribas'),
        );

        // Merchant Key version
        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Enter your key version'),
            'name' => 'MERCANET_KEY_VERSION',
            'desc' => $this->l('This version number is provided by BNP Paribas'),
        );

        // Test Mode
        //if ($this->isFeatureActivated('TEST')) {
        // Title Test mode
        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'html',
            'name' => 'sandbox_infos',
            'html_content' => '<h3>'.$this->l('Test').'</h3>'
        );

        // Enable / Disable test mode
        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'switch',
            'label' => $this->l('Test mode'),
            'name' => 'MERCANET_TEST_MODE',
            'is_bool' => true,
            'required' => true,
            'desc' => $this->l('Use this module in test mode'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            ),
        );
        // using test user config ?
        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'switch',
            'label' => $this->l('Test user'),
            'name' => 'MERCANET_TEST_USER',
            'is_bool' => true,
            'required' => true,
            'desc' => $this->l('Use test user for test mode or your own configuration'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            ),
        );

        // Submit Credentials Tab
        $form['input'][] = $mercarnet_helper_form->getSubmitForm('credentials', 'submitCredentials');

        //======================================================================
        // GENERAL TAB
        $form['tabs']['general'] = $this->l('General');

        // Title Payment page
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'html',
            'name' => 'payment_page',
            'html_content' => '<h3>'.$this->l('Payment page').'</h3>'
        );

        // Card list
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'select',
            'required' => true,
            'multiple' => true,
            'label' => $this->l('Card allowed'),
            'name' => 'MERCANET_CARD_ALLOWED[]',
            'desc' => $this->l('List of card authorized to make a payment'),
            'options' => array(
                'query' => $this->getAvailableCards(true),
                'id' => 'id',
                'name' => 'name'
        ));

        // Default PSP lang
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'select',
            'required' => true,
            'label' => $this->l('Default Payment page language'),
            'name' => 'MERCANET_DEFAULT_LANG',
            'desc' => $this->l('Used if the current Prestashop language is not available on Mercanet'),
            'options' => array(
                'query' => $this->getAvailableLanguages(),
                'id' => 'id',
                'name' => 'name'
        ));

        // Countries list
        if (!$this->isFeatureActivated('STA')) {
            $pec = 'enabled';
        } else {
            $pec = 'disabled';
        }
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'select',
            'required' => true,
            'multiple' => true,
            'label' => $this->l('Countries allowed'),
            'name' => 'MERCANET_COUNTRIES_LIST[]',
            'desc' => $this->l('List of countries authorized to make a payment'),
            $pec => true,
            'options' => array(
                'query' => $this->getAvailableCountries(),
                'id' => 'id',
                'name' => 'name'
        ));

        // Currencies list
        // Countries list
        if ($this->isFeatureActivated('MUL')) {
            $mul = 'enabled';
        } else {
            $mul = 'disabled';
        }

        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'select',
            'required' => true,
            'multiple' => true,
            'label' => $this->l('Currencies allowed'),
            'name' => 'MERCANET_CURRENCIES_LIST[]',
            $mul => true,
            'desc' => $this->l('List of currencies authorized to make a payment, others currencies will be send in EURO'),
            'options' => array(
                'query' => $this->getAvailableCurrencies(),
                'id' => 'id',
                'name' => 'name'
        ));

        // Deposite time limit
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'text',
            'label' => $this->l('Bank deposit time limit'),
            'name' => 'MERCANET_BANK_DEPOSIT_NB_DAYS',
            'required' => false,
            'desc' => $this->l('Add 0 for D-Day. The number of days before the bank deposit'),
        );

        // Payment validation
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'select',
            'required' => true,
            'class' => 'mercanet_select',
            'label' => $this->l('Bank remittance'),
            'desc' => $this->l('In manual mode, you must confirm payment within the back office of your shop'),
            'name' => 'MERCANET_PAYMENT_VALIDATION_MODE',
            'options' => array(
                'query' => $this->getAvailablePaymentModeValidation(),
                'id' => 'id',
                'name' => 'name'
        ));

        // Display payment method
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'select',
            'class' => 'mercanet_select',
            'label' => $this->l('Card data input method'),
            'name' => 'MERCANET_CARD_DISPLAY_METHOD',
            'options' => array(
                'query' => $mercarnet_helper_form->getDisplayChoices(),
                'id' => 'id',
                'name' => 'name'
            ),
        );

        // Automatic redirect after payment
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'switch',
            'label' => $this->l('Redirection to the shop after the payment'),
            'name' => 'MERCANET_AUTOMATIC_REDIRECT_PAYMENT',
            'is_bool' => true,
            'required' => false,
            'desc' => $this->l('This option will redirect the customer directly at your shop after the payment.'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            ),
        );

        // Enable / Disable Notification to the customer
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'switch',
            'label' => $this->l('Customer confirmation ticket'),
            'name' => 'MERCANET_NOTIFY_CUSTOMER',
            'is_bool' => true,
            'required' => false,
            'desc' => $this->l('The result of the transaction (confirmation / rejection) will be automatically emailed to the customer.'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            ),
        );

        // Title Payment page
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'html',
            'name' => 'customize_payment_page',
            'html_content' => '<h3>'.$this->l('Customizing the payment page').'</h3>'
        );

        // Theme configuration
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'text',
            'label' => $this->l('Theme configuration'),
            'name' => 'MERCANET_CSS_THEME_CONFIG',
            'required' => false,
            'desc' => $this->l('The theme configuration to customize the payment page (css)'),
        );

        // Title DEBUG
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'html',
            'name' => 'DEBUG',
            'html_content' => '<h3>'.$this->l('Advanced settings').'</h3>'
        );

        // Activate the log
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'switch',
            'label' => $this->l('Activate the log'),
            'name' => 'MERCANET_LOG_ACTIVE',
            'is_bool' => true,
            'required' => false,
            'desc' => $this->l('Log files are stored in the log folder of the module'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            ),
        );
        
        // Activate the access log
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'switch',
            'label' => $this->l('Activate the access log'),
            'name' => 'MERCANET_LOG_ACCESS',
            'is_bool' => true,
            'required' => false,
            'desc' => $this->l('Log files are stored in the log folder of the module'),
            'values' => array(
                array(
                    'id' => 'access_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'access_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            ),
        );
        
        // 3DSecure
        if ($this->isFeatureActivated('3DS')) {
            // Title 3DS
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'html',
                'name' => '3DS',
                'html_content' => '<h3>'.$this->l('3D-Secure').'</h3>'
            );

            // Enable / Disabled 3DSecure
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate 3D Secure'),
                'name' => 'MERCANET_3DS_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the 3DS Secure control based on the 3DS authentication'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );

            // Minimum Amount for 3DSecure is enable
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'text',
                'label' => $this->l('Minimum amount for which activate 3DS'),
                'name' => 'MERCANET_3DS_MIN_AMOUNT',
                'required' => false,
                'desc' => $this->l('Minimum amount to add the 3DS'),
            );
        }

        // Title Security
        if ($this->isFeatureActivated(array(
                'PEC',
                'PIP',
                'SCP',
                'A3D',
                'CCO',
                'CVI',
                'LNC',
                'AMT',
                'ECC',
                'ECI'))) {
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'html',
                'name' => 'SECURITY',
                'html_content' => '<h3>'.$this->l('Security tools').'</h3>'
            );
        }

        // Anti Fraud: Country Card
        if ($this->isFeatureActivated('PEC')) {
            // Enable / Disabled Anti Fraud: Country Adress IP
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the control on Host Country Card'),
                'name' => 'MERCANET_PEC_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the anti fraud control on Host Country Card'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // Anti Fraud: Country Adress IP
        if ($this->isFeatureActivated('PIP')) {
            // Enable / Disabled Anti Fraud: Country Adress IP
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the control on Country IP Address'),
                'name' => 'MERCANET_PIP_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the anti fraud control on Country IP Address'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // Anti Fraud: Similitude Country - Card
        if ($this->isFeatureActivated('SCP')) {
            // Enable / Disabled Anti Fraud: Similitude Country - Card
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the control on similitude of Country and Card'),
                'name' => 'MERCANET_SCP_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the anti fraud control on similitude of Country and Card'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // Anti Fraud: Authentification 3DS
        if ($this->isFeatureActivated('A3D')) {
            // Enable / Disabled Anti Fraud: Authentification 3DS
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the 3DSecude Authentification'),
                'name' => 'MERCANET_A3D_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the authentification 3DS'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // Anti Fraud: Card Commercial and Country
        if ($this->isFeatureActivated('CCO')) {
            // Enable / Disabled Anti Fraud: Card Commercial and Country
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the control on the eCard and the Country of the card'),
                'name' => 'MERCANET_CCO_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the anti fraud control on the eCard and the Country of the card'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // Anti Fraud: Ecard
        if ($this->isFeatureActivated('CVI')) {
            // Enable / Disabled Anti Fraud: Ecard
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the control on eCard'),
                'name' => 'MERCANET_CVI_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the anti fraud control on eCard'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // Anti Fraud: Black list of Card
        if ($this->isFeatureActivated('LNC')) {
            // Enable / Disabled Anti Fraud: Black list of Card
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the control on Black List of Card'),
                'name' => 'MERCANET_LNC_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the anti fraud control on Black List of Card Number'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // Anti Fraud: Transaction Amount
        if ($this->isFeatureActivated('AMT')) {
            // Enable / Disabled Anti Fraud: Transaction Amount
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the control on the Transaction Amount'),
                'name' => 'MERCANET_AMT_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the anti fraud control on the Transaction Amount'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }
        // Anti Fraud: Transaction Amount
        if ($this->isFeatureActivated('ECC')) {
            // Enable / Disabled Anti Fraud: Transaction Amount
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the control on the current Card'),
                'name' => 'MERCANET_ECC_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the anti fraud control on the current Card'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // Anti Fraud: Current IP
        if ($this->isFeatureActivated('ECI')) {
            // Enable / Disabled Anti Fraud: Current IP
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate the control on the Current IP Address'),
                'name' => 'MERCANET_ECI_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Add the anti fraud control on the Current IP Address'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // One Click
        if ($this->isFeatureActivated('ONE')) {
            // Title One Click
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'html',
                'name' => 'ONECLICK',
                'html_content' => '<h3>'.$this->l('One Click Payment').'</h3>'
            );

            // Enable / Disabled One Click
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Activate One Click'),
                'name' => 'MERCANET_ONE_CLICK_ACTIVE',
                'is_bool' => true,
                'required' => true,
                'desc' => $this->l('Activate the possibility to pay in one click.'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        // Active or not CCH product for PRESTO
        if ($this->isFeatureActivated('PRE')) {
            // Title Presto
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'html',
                'name' => 'PRESTO',
                'html_content' => '<h3>'.$this->l('Presto payment').'</h3>'
            );

            // Enable / Disabled Presto
            $form['input'][] = array(
                'tab' => 'general',
                'type' => 'switch',
                'label' => $this->l('Presto'),
                'name' => 'MERCANET_PRE_ACTIVE',
                'is_bool' => true,
                'required' => false,
                'desc' => $this->l('Activate CCH product'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
        }

        //}
        // Submit General Tab
        $form['input'][] = $mercarnet_helper_form->getSubmitForm('general', 'submitGeneral');

        //======================================================================
        // PAYMENT SIMPLE INTERNET
        // TAB header
        $form['tabs']['payment_one_time'] = $this->l('One-Time Payment');

        // Title Module Options
        $form['input'][] = $mercarnet_helper_form->getInputHtmlModuleOptions('payment_one_time');

        // Name of the payment
        $form['input'][] = $mercarnet_helper_form->getInputName(
            'payment_one_time',
            'ONE_TIME',
            $this->l('Label One-Time Payment'),
            $this->l('Wording payment imperative information for all languages of your shop')
        );

        // Activation
        $form['input'][] = $mercarnet_helper_form->getInputActivation(
            'payment_one_time',
            'ONE_TIME',
            $this->l('Active One Time Payment')
        );

        // Title Restrictions Amount
        $form['input'][] = $mercarnet_helper_form->getInputHtmlRestrictionsAmount('payment_one_time');

        // Minimum and Maximum to display the payment
        $form['input'][] = $mercarnet_helper_form->getInputMinAmount('payment_one_time', 'ONE_TIME');
        $form['input'][] = $mercarnet_helper_form->getInputMaxAmount('payment_one_time', 'ONE_TIME');

        // Submit ONE TIME PAYMENT Tab
        $form['input'][] = $mercarnet_helper_form->getSubmitForm('payment_one_time', 'submitOneTime');


        //======================================================================
        // PAYMENT IN SEVERAL TIMES
        if ($this->isFeatureActivated('NFO')) {
            // TAB header
            $form['tabs']['payment_nx_time'] = $this->l('Payment in several times');

            // Title Module Options
            $form['input'][] = $mercarnet_helper_form->getInputHtmlModuleOptions('payment_nx_time');

            // Name of the payment
            $form['input'][] = $mercarnet_helper_form->getInputName(
                'payment_nx_time',
                'NX_TIME',
                $this->l('Label payment in several times'),
                $this->l('Wording payment in several times imperative information for all languages of your shop')
            );

            // Activation
            $form['input'][] = $mercarnet_helper_form->getInputActivation(
                'payment_nx_time',
                'NX_TIME',
                $this->l('Active Several Time Payment')
            );

            // Title Payment Options
            $form['input'][] = $mercarnet_helper_form->getInputHtmlPaymentOptions('payment_nx_time');

            $form['input'][] = $mercarnet_helper_form->getInputArrayPayments(
                'payment_nx_time',
                'NX_TIME'
            );

            // Submit ONE TIME PAYMENT Tab
            $form['input'][] = $mercarnet_helper_form->getSubmitForm(
                'payment_nx_time',
                'submitNxTime'
            );
        }

        //======================================================================
        // PAYMENT RECURRING
        if ($this->isFeatureActivated('ABO')) {
            // TAB header
            $form['tabs']['payment_recurring'] = $this->l('Payment recurring');

            // Title Module Options
            $form['input'][] = $mercarnet_helper_form->getInputHtmlModuleOptions('payment_recurring');

            // Name of the payment
            $form['input'][] = $mercarnet_helper_form->getInputName(
                'payment_recurring',
                'RECURRING',
                $this->l('Label payment recurring'),
                $this->l('Wording payment recurring imperative information for all languages of your shop')
            );

            // Activation
            $form['input'][] = $mercarnet_helper_form->getInputActivation(
                'payment_recurring',
                'RECURRING',
                $this->l('Active Payment Recurring')
            );

            // Submit ONE TIME PAYMENT Tab
            $form['input'][] = $mercarnet_helper_form->getSubmitForm(
                'payment_recurring',
                'submitRecurring'
            );
        }

        return $form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $return = array();
        $languages = Language::getLanguages();

        // CREDENTIAL && GENERAL TAB
        foreach ($this->config_single_values_keys as $key) {
            $return[$key] = Configuration::get($key);
        }

        // Multi Select
        $return['MERCANET_CARD_ALLOWED[]'] = explode(',', Configuration::get('MERCANET_CARD_ALLOWED'));
        $return['MERCANET_COUNTRIES_LIST[]'] = explode(',', Configuration::get('MERCANET_COUNTRIES_LIST'));
        $return['MERCANET_CURRENCIES_LIST[]'] = explode(',', Configuration::get('MERCANET_CURRENCIES_LIST'));

        // PAYMENT ONE TIME
        // Name is multilang
        $one_time_names = array();
        foreach ($languages as $lang) {
            $one_time_names[(int)$lang['id_lang']] = Configuration::get('MERCANET_ONE_TIME_NAME', $lang['id_lang']);
        }
        $return['MERCANET_ONE_TIME_NAME'] = $one_time_names;
        $return['MERCANET_ONE_TIME_ACTIVE'] = Configuration::get('MERCANET_ONE_TIME_ACTIVE');
        $return['MERCANET_ONE_TIME_MIN_AMOUNT'] = Configuration::get('MERCANET_ONE_TIME_MIN_AMOUNT');
        $return['MERCANET_ONE_TIME_MAX_AMOUNT'] = Configuration::get('MERCANET_ONE_TIME_MAX_AMOUNT');

        // PAYMENT SEVERAL TIME
        // Name is multilang
        $nx_time_names = array();
        foreach ($languages as $lang) {
            $nx_time_names[(int)$lang['id_lang']] = Configuration::get('MERCANET_NX_TIME_NAME', $lang['id_lang']);
        }
        $return['MERCANET_NX_TIME_NAME'] = $nx_time_names;
        $return['MERCANET_NX_TIME_ACTIVE'] = Configuration::get('MERCANET_NX_TIME_ACTIVE');
        $return['MERCANET_NX_TIME_MIN_AMOUNT'] = Configuration::get('MERCANET_NX_TIME_MIN_AMOUNT');
        $return['MERCANET_NX_TIME_MAX_AMOUNT'] = Configuration::get('MERCANET_NX_TIME_MAX_AMOUNT');

        // PAYMENT RECURRING
        // Name is multilang
        $recurring_names = array();
        foreach ($languages as $lang) {
            $recurring_names[(int)$lang['id_lang']] = Configuration::get('MERCANET_RECURRING_NAME', $lang['id_lang']);
        }
        $return['MERCANET_RECURRING_NAME'] = $recurring_names;
        $return['MERCANET_RECURRING_ACTIVE'] = Configuration::get('MERCANET_RECURRING_ACTIVE');

        return $return;
    }

    /**
     * Save form data.
     */
    protected function postProcess($tab = null)
    {

        // Init Mercanet Helper Form
        $mercarnet_helper_form = new MercanetHelperForm();

        // Verify the activation key
        if ($activation_key_decrypted = Tools::getValue('MERCANET_ACTIVATION_KEY') && $this->decryptActivationKey(Tools::getValue('MERCANET_ACTIVATION_KEY')) == true) {
            Configuration::updateValue('MERCANET_ACTIVATION_KEY', Tools::getValue('MERCANET_ACTIVATION_KEY'));
            Configuration::updateValue('MERCANET_ACTIVATION_CONFIG', $activation_key_decrypted);
            if (!Tools::getIsset('MERCANET_MERCHANT_ID')) {
                return;
            }
        } else {
            $this->context->controller->errors[] = $this->l('The digital signature of your activation key could not be validated.');
            Configuration::updateValue('MERCANET_ACTIVATION_KEY', null);
            Configuration::updateValue('MERCANET_ACTIVATION_CONFIG', null);
            return;
        }

        // Validation
        $errors = $mercarnet_helper_form->validateForms($tab);

        if (!sizeof($errors)) {
            // Update if no errors
            foreach ($this->single_values_keys_by_tabs[$tab] as $key) {
                if (!in_array($key, $this->empty_config_single_values_keys)) {
                    // If is array = multiselect
                    if (is_array(Tools::getValue($key))) {
                        Configuration::updateValue($key, implode(',', Tools::getValue($key)));
                    } else {
                        Configuration::updateValue($key, Tools::getValue($key));
                    }

                    // For multilang
                    if ('MERCANET_ONE_TIME_NAME' == $key || 'MERCANET_NX_TIME_NAME' == $key || 'MERCANET_RECURRING_NAME' == $key) {
                        $values = array();
                        foreach (Language::getLanguages() as $lang) {
                            $values[(int)$lang['id_lang']] = Tools::getValue($key.'_'.$lang['id_lang']);
                        }
                        Configuration::updateValue($key, $values);
                    }
                } else {
                    $key_value = Tools::getValue($key);
                    if (!empty($key_value)) {
                        Configuration::updateValue($key, Tools::getValue($key));
                    }
                }
            }

             
            switch ($tab) {
                case 'payment_one_time':
                    $tab = 'One-Time Payment';
                    break;

                case 'payment_nx_time':
                    $tab = 'Payment in several times';
                    break;

                case 'payment_recurring':
                    $tab = 'Payment recurring';
                    break;
            }

            $this->context->smarty->assign('success', $this->l('Update successful on tab').' '.Translate::getModuleTranslation($this, Tools::ucfirst((string)$tab), 'mercanet'));
        } else {
            $this->context->controller->errors = $errors;
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addJqueryPlugin('fancybox');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPaymentOptions($params)
    {
        // Check if a payment can be display
        if (!$this->canDisplayPayment()) {
            return true;
        }
        die("rec");

        // Check if payment recurring is active
        $payment_recurring = false;
        if (!$this->canDisplayPaymentRecurring()) {
            $payment_recurring = true;
            $mercanet_recurring_name = Configuration::get('MERCANET_RECURRING_NAME', $this->context->cart->id_lang);

            $this->smarty->assign(
                array(
                    'recurring' => true,
                    'recurring_payment_name' => (!empty($mercanet_recurring_name)) ? Configuration::get(
                        'MERCANET_RECURRING_NAME',
                        $this->context->cart->id_lang
                    ) : Configuration::get('MERCANET_RECURRING_NAME', Configuration::get('PS_LANG_DEFAULT')),
                )
            );
        }

        // Check if simple payment can be display
        if ($this->canDisplayOneTimePayment() && $payment_recurring == false) {
            $mercanet_one_time_name = Configuration::get('MERCANET_ONE_TIME_NAME', $this->context->cart->id_lang);

            $this->smarty->assign(
                array(
                    'one_time' => true,
                    'one_time_payment_name' => (!empty($mercanet_one_time_name)) ? Configuration::get(
                        'MERCANET_ONE_TIME_NAME',
                        $this->context->cart->id_lang
                    ) : Configuration::get('MERCANET_ONE_TIME_NAME', Configuration::get('PS_LANG_DEFAULT')),
                )
            );
        }

        // NX PAYMENT
        if ($this->canDisplayNxTimePayment() && $payment_recurring == false) {
            $cards_availables = MercanetApi::getCards();
            $available = false;
            if (is_array($cards_availables)) {
                $authorized_card = MercanetApi::$CARDS_AUTHORIZED;

                foreach ($cards_availables as $card) {
                    if (in_array($card, $authorized_card)) {
                        $available = true;
                        continue;
                    }
                }

                $this->smarty->assign(
                    array(
                        'nx_time' => $available,
                        'nx_time_payment_name' => Configuration::get('MERCANET_NX_TIME_NAME', $this->context->cart->id_lang),
                        'nx_time_payments' => MercanetNxPayment::getAvailablePayments(),
                    )
                );
            }
        }

        $this->smarty->assign('module_dir', $this->_path);

        // Template to display payment


        switch (Configuration::get('MERCANET_CARD_DISPLAY_METHOD')) {
            default:
            case 'DIRECT_MERCANET':
                $template = 'direct-payment.tpl';
                break;

            case 'DISPLAY_CARDS':
                $template = 'display-cards-payment.tpl';
                $cart_euro_amount = MercanetApi::getConvertedAmount(
                    (float)$this->context->cart->getOrderTotal(),
                    new Currency((int)$this->context->cart->id_currency),
                    new Currency((int)Currency::getIdByNumericIsoCode((int)Configuration::get('MERCANET_EURO_ISO_CODE_NUM')))
                );

                if (Configuration::get('MERCANET_CARD_ALLOWED') == 'ALL') {
                    // Cards Default will now retrieve the payment without TRI
                    $cards = MercanetApi::getCardsWithTrigramme();
                } else {
                    $cards = MercanetApi::getCards();
                }

                $cards_nx = array();
                $cards_mif = array();
                if (Configuration::get('MERCANET_CARD_ALLOWED') && Configuration::get('MERCANET_CARD_ALLOWED') != 'ALL') {
                    $authorized_card = MercanetApi::$CARDS_AUTHORIZED;
                    $array_card = explode(',', Configuration::get('MERCANET_CARD_ALLOWED'));

                    foreach ($array_card as $card) {
                        if (in_array($card, $authorized_card)) {
                            $cards_nx[] = $card;
                            $cards_mif[] = $card;
                        }
                    }
                } else {
                    $cards_nx = MercanetApi::$CARDS_AUTHORIZED;
                    $cards_mif = MercanetApi::$CARDS_AUTHORIZED;
                }

                $this->context->smarty->assign(array(
                    'cards' => $cards,
                    'cards_nx' => $cards_nx,
                    'cards_mif' => $cards_mif,
                    'cards_mif_string' => implode(",", $cards_mif),
                    'cart_amount' => $cart_euro_amount,
                    'f3cb_name' => Configuration::get('MERCANET_F3CB_NAME'),
                    'f3cb_min_amount' => Configuration::get('MERCANET_F3CB_MIN_AMOUNT'),
                    'f3cb_max_amount' => Configuration::get('MERCANET_F3CB_MAX_AMOUNT'),
                    'f4cb_name' => Configuration::get('MERCANET_F4CB_NAME'),
                    'f4cb_min_amount' => Configuration::get('MERCANET_F4CB_MIN_AMOUNT'),
                    'f4cb_max_amount' => Configuration::get('MERCANET_F4CB_MAX_AMOUNT'),
                ));
                break;
            case 'IFRAME':
                $template = 'iframe.tpl';
                break;
        }

        return $this->display(__FILE__, $template);
    }

    /**
     * Hook in the product (admin)
     * @param  $params
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $product = new Product((int)Tools::getValue('id_product'));
        if (Validate::isLoadedObject($product) || $this->isFeatureActivated('ABO')) {
            $mercanet_payment_recurring = MercanetPaymentRecurring::getPaymentRecurringByProductId((int)$product->id);
            $this->context->smarty->assign(
                array(
                    'mercanet_payment_recurring' => $mercanet_payment_recurring,
                    'mercanet_types' => MercanetPaymentRecurring::getTypes(),
                    'mercanet_periodicities' => MercanetPaymentRecurring::getPeriodicities()
                )
            );
        }

        $this->context->smarty->assign(array(
            'feature_abo' => $this->isFeatureActivated('ABO')
        ));

        return $this->display(__FILE__, 'admin-product-extra.tpl');
    }

    public function hookActionProductUpdate($params)
    {
        $product = new Product((int)Tools::getValue('id_product'));
        if (Validate::isLoadedObject($product) && $this->isFeatureActivated('ABO') && Tools::getValue('key_tab') == 'ModuleMercanet') {
            // Type
            /* if ($mercanet_type = Tools::getValue('mercanet_type') && empty($mercanet_type)) {
              $this->context->controller->errors[] = $this->l('You have to choose a type');
              }

              // Periodicity
              if ($mercanet_periodicity = Tools::getValue('mercanet_periodicity') && empty($mercanet_periodicity)) {
              $this->context->controller->errors[] = $this->l('You have to choose a periodicity');
              } */

            // Occurrences
            if (!(int)Tools::getValue('mercanet_number_occurrences')) {
                $this->context->controller->errors[] = $this->l('You have to choose a number of occurences');
            }

            if (empty($this->context->controller->errors)) {
                $id_payment_recurring = MercanetPaymentRecurring::getIdPaymentRecurringByProductId((int)$product->id);

                // Add || Save
                $payment_recurring = new MercanetPaymentRecurring((int)$id_payment_recurring);

                $payment_recurring->id_product = (int)$product->id;
                $payment_recurring->type = (int)Tools::getValue('mercanet_type');
                $payment_recurring->periodicity = (int)Tools::getValue('mercanet_periodicity');
                $payment_recurring->number_occurences = (int)Tools::getValue('mercanet_number_occurrences');
                $payment_recurring->recurring_amount = (float)Tools::getValue('mercanet_recurring_amount');
                $payment_recurring->save();
            }
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
        $order = new Order((int)$params['id_order']);

        // Refund Submit
        if (Tools::isSubmit('submitMercanetRefund')) {
            if (!Tools::getValue('mercanet_refund_transaction')) {
                $this->_errors[] = $this->l('Select a transaction to refund.');
            }

            if (!Tools::getValue('mercanet_refund_slip')) {
                $this->_errors[] = $this->l('Select a slip to refund.');
            }

            if (!count($this->_errors)) {
                $transaction_reference = Tools::getValue('mercanet_refund_transaction');

                // Get transaction
                $transaction = MercanetTransaction::getTransactionByReference($transaction_reference);

                if (!empty($transaction)) {
                    // Get slip
                    $slip_id = Tools::getValue('mercanet_refund_slip');
                    $slip = new OrderSlip($slip_id);

                    if ($slip->id) {
                        // Refund
                        $result = MercanetWebservice::refund($order->id, $transaction['id_mercanet_transaction'], $slip->id);

                        if ($result->responseCode != '00') {
                            $this->_errors[] = (isset($result->message)) ? $result->message : $this->l('An error as occured:').' '.$result->responseCode.' '.MercanetResponseCode::getMessageByCode($result->responseCode);
                        }
                    } else {
                        $this->_errors[] = $this->l('Unknown slip ID:').' '.$slip_id;
                    }
                } else {
                    $this->_errors[] = $this->l('Unknown transaction ID:').' '.$transaction_reference;
                }
            }
        }

        if ($order->module == $this->name) {
            $transactions = MercanetHistory::getTransactionsByOrderId((int)$order->id);
            $schedules = MercanetSchedule::getScheduleByOrderId((int)$order->id);
            $recurring_schedules = MercanetCustomerPaymentRecurring::getScheduleFormattedByOrderId((int)$order->id);
            $recurring_orders = MercanetCustomerPaymentRecurring::getOrdersFormattedByOrderId((int)$order->id);

            $this->smarty->assign(
                array(
                    'transactions' => (!empty($transactions)) ? $transactions : null,
                    'schedules' => (!empty($schedules)) ? $schedules : null,
                    'recurring_schedules' => (!empty($recurring_schedules)) ? $recurring_schedules : null,
                    'recurring_orders' => (!empty($recurring_orders)) ? $recurring_orders : null,
                    'module_name' => $this->displayName,
                    'order' => $order,
                    'errors' => $this->_errors,
                    'slips' => MercanetTransaction::getOrderRefundableSlip((int)$order->id, (int)$order->id_customer),
                    'refundable_transaction' => MercanetTransaction::getOrderRefundableTransaction($order->reference),
                    'refund_tri' => $this->isFeatureActivated('REM'),
                )
            );
            return $this->display(__FILE__, 'admin-orders.tpl');
        }
    }

    public function hookDisplayCustomerAccount($params)
    {
        $has_recurring = false;
        $has_one_click = false;
        if ($this->isFeatureActivated('ONE') && (bool)Configuration::get('MERCANET_ONE_CLICK_ACTIVE') == true) {
            $has_one_click = true;
        }
        if ($this->isFeatureActivated('ABO') && MercanetCustomerPaymentRecurring::hasRecurringPayment($this->context->customer->id)) {
            $has_recurring = true;
        }
        $this->context->smarty->assign('has_one_click', $has_one_click);
        $this->context->smarty->assign('has_recurring', $has_recurring);
        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->display(__FILE__, 'my-account.tpl');
    }

    /**
     * Check if the payment can be displayed
     * @param type $params
     * @return boolean
     */
    public function canDisplayPayment()
    {
        // Check if the module is active
        if (!$this->active) {
            return false;
        }

        // Check credentials
        if (!Configuration::get('MERCANET_ACTIVATION_KEY') || !Configuration::get('MERCANET_MERCHANT_ID') || !Configuration::get('MERCANET_SECRET_KEY') || !Configuration::get('MERCANET_KEY_VERSION')) {
            return false;
        }

        // Check if EURO is configured
        if (!Currency::getIdByNumericIsoCode((int)Configuration::get('MERCANET_EURO_ISO_CODE_NUM'))) {
            return false;
        }

        return true;
    }

    /**
     * Check if the payment can be displayed
     * @return boolean
     */
    public function canDisplayPaymentRecurring()
    {
        // Check Key
        if (!$this->isFeatureActivated('ABO')) {
            return false;
        }

        // Check if Payment is enabled
        if ((bool)Configuration::get('MERCANET_RECURRING_ACTIVE') == false) {
            return false;
        }

        // Check if one product is in payment recurring
        $is_recurring = false;
        foreach ($this->context->cart->getProducts() as $product) {
            if ($is_recurring == false) {
                $is_recurring = MercanetPaymentRecurring::isThisProductPaymentRecurring((int)$product['id_product']);
            } else {
                continue;
            }
        }

        if ($is_recurring == false) {
            return false;
        }

        return true;
    }

    /**
     * Check of One Time Payment can be displayed
     * @param array $params
     * @return boolean
     */
    public function canDisplayOneTimePayment()
    {
        // Check if Payment is enabled
        if ((bool)Configuration::get('MERCANET_ONE_TIME_ACTIVE') == false) {
            return false;
        }

        // Check Payment Name
        $mercanet_one_time_name = Configuration::get('MERCANET_ONE_TIME_NAME', $this->context->customer->id_lang);
        if (empty($mercanet_one_time_name)) {
            $mercanet_one_time_name = Configuration::get('MERCANET_ONE_TIME_NAME', Configuration::get('PS_LANG_DEFAULT'));
            if (empty($mercanet_one_time_name)) {
                return false;
            }
        }

        // Amount in EURO
        $euro_amount = MercanetApi::getConvertedAmount(
            (float)$this->context->cart->getOrderTotal(),
            new Currency((int)$this->context->cart->id_currency),
            new Currency((int)Currency::getIdByIsoCodeNum((int)Configuration::get('MERCANET_EURO_ISO_CODE_NUM')))
        );

        // Check minimum amount
        if ($min_amount = (float)Configuration::get('MERCANET_ONE_TIME_MIN_AMOUNT')) {
            if ($min_amount > (float)$euro_amount) {
                return false;
            }
        }

        // Check maximum amount
        if ($max_amount = (float)Configuration::get('MERCANET_ONE_TIME_MAX_AMOUNT')) {
            if ($max_amount < (float)$euro_amount) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check of NX Time Payment can be displayed
     * @param array $params
     * @return boolean
     */
    public function canDisplayNxTimePayment()
    {

        // Check Key
        if (!$this->isFeatureActivated('NFO')) {
            return false;
        }

        // Check if Payment is enabled
        if ((bool)Configuration::get('MERCANET_NX_TIME_ACTIVE') == false) {
            return false;
        }

        // Check Payment Name
        $mercanet_nx_time_name = Configuration::get('MERCANET_NX_TIME_NAME', $this->context->customer->id_lang);
        if (empty($mercanet_nx_time_name)) {
            return false;
        }

        // Amount in EURO
        $euro_amount = MercanetApi::getConvertedAmount(
            (float)$this->context->cart->getOrderTotal(),
            new Currency((int)$this->context->cart->id_currency),
            new Currency((int)Currency::getIdByIsoCodeNum((int)Configuration::get('MERCANET_EURO_ISO_CODE_NUM')))
        );

        // Check if NX Payment can be display in amount range minimum amount
        if (!MercanetNxPayment::isCartAmountInRanges($euro_amount)) {
            return false;
        }

        return true;
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
            $transaction = MercanetTransaction::getTransactionByOrderId((int)$order->id);
        }

        $this->smarty->assign(array(
            'id_order' => (int)$order->id,
            'reference' => $order->reference,
            'params' => $params,
            'transaction' => (empty($transaction)) ? null : $transaction,
            'authorisation_id' => Tools::getValue('authorisation_id'),
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
            'schedules' => MercanetSchedule::getScheduleByOrderId((int)$order->id),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    /**
     * Decrypt Activation key
     */
    protected function decryptActivationKey($key)
    {
        $datas = (explode("\n", $key));
        $data = trim($datas[0]);

        $public_key_res = openssl_pkey_get_public(Tools::file_get_contents(_PS_MODULE_DIR_.'mercanet/tools/rsa.pub'));
        $signature = trim(Tools::substr($key, strpos($key, "\n") + 1));
        $signature_decode = base64_decode($signature);

        if (function_exists('openssl_verify')) {
            $result = openssl_verify($data, $signature_decode, $public_key_res);
            if ($result == 1) {
                return true;
            } elseif ($result == 0) {
                return false;
            }
        }

        return false;
    }

    /**
     * Decrypt Activation key
     */
    public function isFeatureActivated($feature_list)
    {
        if (!is_array($feature_list)) {
            $feature_list = array(
                $feature_list);
        }
        // Get only trigramme
        $keys = explode(';', Configuration::get('MERCANET_ACTIVATION_KEY'));
        array_pop($keys);

        $found = true;

        foreach ($feature_list as $feature) {
            if (!in_array($feature, $keys)) {
                $found = false;
            }
        }

        return $found;
    }

    /**
     * Return Mercanet availables langs.
     * @return array
     */
    public function getAvailableLanguages()
    {
        $results = array();
        $langs = Language::getLanguages();

        foreach ($langs as $key => $lang) {
            switch ($lang['iso_code']) {
                case 'de':
                case 'en':
                case 'es':
                case 'fr':
                case 'hi':
                case 'it':
                case 'ja':
                case 'nl':
                case 'pt':
                case 'ru':
                case 'sk':
                case 'zh':
                    $results[(int)$lang['id_lang']] = array(
                        'id' => (int)$lang['id_lang'],
                        'name' => (string)$lang['name'],
                        'iso_code' => (string)$lang['iso_code'],
                    );
                    break;
                default:
                    unset($langs[$key]);
            }
        }
        return $results;
    }

    /**
     * Get all card available
     * @param type $add_all_option
     * @return type
     */
    public function getAvailableCards($add_all_option = false)
    {
        $options = array();

        if ($add_all_option == true) {
            $options = array(
                0 => array(
                    'id' => 'ALL',
                    'name' => $this->l('ALL')
            ));
        }

        $payments = array(
            'CB' => array(
                'id' => 'CB',
                'name' => $this->l('Bank Card'),
                'type' => 'CARD',
            ),
            'VISA' => array(
                'id' => 'VISA',
                'name' => $this->l('VISA'),
                'type' => 'CARD',
            ),
            'MASTERCARD' => array(
                'id' => 'MASTERCARD',
                'name' => $this->l('MasterCard'),
                'type' => 'CARD',
            ),
            'BCMC' => array(
                'id' => 'BCMC',
                'name' => $this->l('Bancontact'),
                'type' => 'CARD',
            ),
            'iDeal' => array(
                'id' => 'iDeal',
                'name' => $this->l('iDeal'),
                'type' => 'CREDIT_TRANSFER',
            ),
            'ELV' => array(
                'id' => 'ELV',
                'name' => $this->l('Electronic Checks'),
                'type' => 'DIRECT_DEBIT',
            ),
        );

        // Paylib
        if ($this->isFeatureActivated('PLB')) {
            $payments['PAYLIB'] = array(
                'id' => 'PAYLIB',
                'name' => $this->l('PayLib'),
                'type' => 'WALLET',
            );
        }

        // Paypal
        if ($this->isFeatureActivated('PAL')) {
            $payments['PAL'] = array(
                'id' => 'PAYPAL',
                'name' => $this->l('Paypal'),
                'type' => 'WALLET',
            );
        }

        // Master Pass
        if ($this->isFeatureActivated('MPS')) {
            $payments['MASTERPASS'] = array(
                'id' => 'MASTERPASS',
                'name' => $this->l('MasterPass'),
                'type' => 'WALLET',
            );
        }

        // AMEX
        if ($this->isFeatureActivated('AMX')) {
            $payments['AMEX'] = array(
                'id' => 'AMEX',
                'name' => $this->l('Amex'),
                'type' => 'CARD',
            );
        }

        // JCB
        if ($this->isFeatureActivated('JCB')) {
            $payments['JCB'] = array(
                'id' => 'JCB',
                'name' => $this->l('JCB'),
                'type' => 'CARD',
            );
        }

        // Aurore
        if ($this->isFeatureActivated('AUR')) {
            $payments['AURORE'] = array(
                'id' => 'AURORE',
                'name' => $this->l('Card Aurore'),
                'type' => 'CARD',
            );
        }

        // Presto
        if ($this->isFeatureActivated('PRE')) {
            $payments['PRESTO'] = array(
                'id' => 'PRESTO',
                'name' => $this->l('Presto'),
                'type' => 'CARD',
            );
        }

        // FullCB
        if ($this->isFeatureActivated('FCB')) {
            $payments['CETELEM_3X'] = array(
                'id' => 'CETELEM_3X',
                'name' => $this->l('CETELEM 3xCB'),
                'type' => 'CARD',
            );
            $payments['CETELEM_4X'] = array(
                'id' => 'CETELEM_4X',
                'name' => $this->l('CETELEM 4xCB'),
                'type' => 'CARD',
            );
        }

        if (!empty($options)) {
            $result = array_merge($options, $payments);
        } else {
            $result = $payments;
        }

        return $result;
    }

    /**
     * Get all available payment mode validation
     * @param type $add_all_option
     * @return type
     */
    protected function getAvailablePaymentModeValidation()
    {
        return array(
            'AUTHOR_CAPTURE' => array(
                'id' => 'AUTHOR_CAPTURE',
                'name' => $this->l('Automatic re-authorization and payment (by default)'),
            ),
            'VALIDATION' => array(
                'id' => 'VALIDATION',
                'name' => $this->l('Hand over payment after validation by the Merchant'),
            ),
        );
    }

    /**
     * Return Available Currencies
     * @return type
     */
    protected function getAvailableCurrencies()
    {
        if (!$this->isFeatureActivated('MUL')) {
            return array(
                '978' => array(
                    'id' => '978',
                    'name' => 'Euro',
                )
            );
        }

        return array(
            '978' => array(
                'id' => '978',
                'name' => 'Euro',
            ),
            '756' => array(
                'id' => '756',
                'name' => 'Franc Suisse',
            ),
            '840' => array(
                'id' => '840',
                'name' => 'Dollar Américain',
            ),
            '826' => array(
                'id' => '826',
                'name' => 'Livre Sterling',
            ),
            '032' => array(
                'id' => '032',
                'name' => 'Peso Argentin'
            ),
            '036' => array(
                'id' => '036',
                'name' => 'Dollar Australien',
            ),
            '116' => array(
                'id' => '116',
                'name' => 'Riel',
            ),
            '124' => array(
                'id' => '124',
                'name' => 'Dollar Canadien',
            ),
            '208' => array(
                'id' => '208',
                'name' => 'Couronne Danoise',
            ),
            '356' => array(
                'id' => '356',
                'name' => 'Roupie indienne',
            ),
            '392' => array(
                'id' => '392',
                'name' => 'Yen',
            ),
            '410' => array(
                'id' => '410',
                'name' => 'Won',
            ),
            '484' => array(
                'id' => '484',
                'name' => 'Peso Mexicain',
            ),
            '554' => array(
                'id' => '554',
                'name' => 'Dollar Néo-Zélandais',
            ),
            '578' => array(
                'id' => '578',
                'name' => 'Couronne Norvégienne',
            ),
            '702' => array(
                'id' => '702',
                'name' => 'Dollar de Singapour',
            ),
            '752' => array(
                'id' => '752',
                'name' => 'Couronne Suédoise',
            ),
            '901' => array(
                'id' => '901',
                'name' => 'Dollar de Taiwan',
            ),
            '949' => array(
                'id' => '949',
                'name' => 'Nouvelle Livre Turque',
            ),
            '952' => array(
                'id' => '952',
                'name' => 'Franc CFA',
            ),
            '953' => array(
                'id' => '953',
                'name' => 'Franc Pacifique',
            ),
            '986' => array(
                'id' => '986',
                'name' => 'Real Brésilien',
            )
        );
    }

    /**
     * Get available Countries with key config
     * @return type
     */
    public function getAvailableCountries()
    {
        if ($this->isFeatureActivated('STA')) {
            return array(
                'FR' => array(
                    'id' => 'FRA',
                    'name' => 'France',
                ),
            );
        }
        return array(
            '00' => array(
                'id' => 'ALL',
                'name' => 'TOUS',
            ),
            'FR' => array(
                'id' => 'FRA',
                'name' => 'France',
            ),
            'AW' => array(
                'id' => 'ABW',
                'name' => 'Aruba',
            ),
            'AF' => array(
                'id' => 'AFG',
                'name' => 'Afghanistan',
            ),
            'AO' => array(
                'id' => 'AGO',
                'name' => 'Angola',
            ),
            'AI' => array(
                'id' => 'AIA',
                'name' => 'Anguilla',
            ),
            'AX' => array(
                'id' => 'ALA',
                'name' => 'Ãland îles,',
            ),
            'AL' => array(
                'id' => 'ALB',
                'name' => 'Albanie',
            ),
            'AD' => array(
                'id' => 'AND',
                'name' => 'Andorre',
            ),
            'AE' => array(
                'id' => 'ARE',
                'name' => 'Émirats Arabes Unis',
            ),
            'AR' => array(
                'id' => 'ARG',
                'name' => 'Argentine',
            ),
            'AM' => array(
                'id' => 'ARM',
                'name' => 'Arménie',
            ),
            'AS' => array(
                'id' => 'ASM',
                'name' => 'Samoa américaines',
            ),
            'AQ' => array(
                'id' => 'ATA',
                'name' => 'Antarctique',
            ),
            'TF' => array(
                'id' => 'ATF',
                'name' => 'Terres Autrales française',
            ),
            'AG' => array(
                'id' => 'ATG',
                'name' => 'Antigua-Et-Barbuda',
            ),
            'AU' => array(
                'id' => 'AUS',
                'name' => 'Australie',
            ),
            'AT' => array(
                'id' => 'AUT',
                'name' => 'Autriche',
            ),
            'AZ' => array(
                'id' => 'AZE',
                'name' => 'Azerbaïdjan',
            ),
            'BI' => array(
                'id' => 'BDI',
                'name' => 'Burundi',
            ),
            'BE' => array(
                'id' => 'BEL',
                'name' => 'Belgique',
            ),
            'BJ' => array(
                'id' => 'BEN',
                'name' => 'Bénin',
            ),
            'BES' => array(
                'id' => 'BES',
                'name' => 'Bonaire, Saint-Eustache et Saba',
            ),
            'BF' => array(
                'id' => 'BFA',
                'name' => 'Burkina Faso',
            ),
            'BD' => array(
                'id' => 'BGD',
                'name' => 'Bangladesh',
            ),
            'BG' => array(
                'id' => 'BGR',
                'name' => 'Bulgarie',
            ),
            'BH' => array(
                'id' => 'BHR',
                'name' => 'Bahreïn',
            ),
            'BS' => array(
                'id' => 'BHS',
                'name' => 'Bahamas',
            ),
            'BA' => array(
                'id' => 'BIH',
                'name' => 'Bosnie-Herzégovine',
            ),
            'KN' => array(
                'id' => 'BLM',
                'name' => 'Saint-Kitts-Et-Nevis',
            ),
            'BY' => array(
                'id' => 'BLR',
                'name' => 'Bélarus',
            ),
            'BZ' => array(
                'id' => 'BLZ',
                'name' => 'Belize',
            ),
            'BM' => array(
                'id' => 'BMU',
                'name' => 'Bermudes',
            ),
            'BO' => array(
                'id' => 'BOL',
                'name' => 'Bolivie',
            ),
            'BR' => array(
                'id' => 'BRA',
                'name' => 'Brésil',
            ),
            'BB' => array(
                'id' => 'BRB',
                'name' => 'Barbade',
            ),
            'BN' => array(
                'id' => 'BRN',
                'name' => 'Brunei Darussalam',
            ),
            'BT' => array(
                'id' => 'BTN',
                'name' => 'Bhoutan',
            ),
            'BV' => array(
                'id' => 'BVT',
                'name' => 'Bouvet, île',
            ),
            'BW' => array(
                'id' => 'BWA',
                'name' => 'Botswana',
            ),
            'CF' => array(
                'id' => 'CAF',
                'name' => 'Centrafricaine, république',
            ),
            'CA' => array(
                'id' => 'CAN',
                'name' => 'Canada',
            ),
            'CC' => array(
                'id' => 'CCK',
                'name' => 'Cocos (Keeling), îles',
            ),
            'CH' => array(
                'id' => 'CHE',
                'name' => 'Suisse',
            ),
            'CL' => array(
                'id' => 'CHL',
                'name' => 'Chili',
            ),
            'CN' => array(
                'id' => 'CHN',
                'name' => 'Chine',
            ),
            'CI' => array(
                'id' => 'CIV',
                'name' => 'Côte d\'ivoire',
            ),
            'CM' => array(
                'id' => 'CMR',
                'name' => 'Cameroun',
            ),
            'CD' => array(
                'id' => 'COD',
                'name' => 'Congo, la république démocratique',
            ),
            'CG' => array(
                'id' => 'COG',
                'name' => 'Congo',
            ),
            'CK' => array(
                'id' => 'COK',
                'name' => 'Cook, îles',
            ),
            'CO' => array(
                'id' => 'COL',
                'name' => 'Colombie',
            ),
            'KM' => array(
                'id' => 'COM',
                'name' => 'Comores',
            ),
            'CV' => array(
                'id' => 'CPV',
                'name' => 'Cap-vert',
            ),
            'CR' => array(
                'id' => 'CRI',
                'name' => 'Costa Rica',
            ),
            'CU' => array(
                'id' => 'CUB',
                'name' => 'Cuba',
            ),
            'CUW' => array(
                'id' => 'CUW',
                'name' => 'Curaçao ',
            ),
            'CX' => array(
                'id' => 'CXR',
                'name' => 'Christmas, îles',
            ),
            'KY' => array(
                'id' => 'CYM',
                'name' => 'Caïmans, îles',
            ),
            'CY' => array(
                'id' => 'CYP',
                'name' => 'Chypre',
            ),
            'CZ' => array(
                'id' => 'CZE',
                'name' => 'Tchèque, république',
            ),
            'DE' => array(
                'id' => 'DEU',
                'name' => 'Allemagne',
            ),
            'DJ' => array(
                'id' => 'DJI',
                'name' => 'Djibouti',
            ),
            'DM' => array(
                'id' => 'DMA',
                'name' => 'Dominique',
            ),
            'DK' => array(
                'id' => 'DNK',
                'name' => 'Danemark',
            ),
            'DO' => array(
                'id' => 'DOM',
                'name' => 'Dominicaine, république',
            ),
            'DZ' => array(
                'id' => 'DZA',
                'name' => 'Algérie',
            ),
            'EC' => array(
                'id' => 'ECU',
                'name' => 'Équateur',
            ),
            'EG' => array(
                'id' => 'EGY',
                'name' => 'Égypte',
            ),
            'ER' => array(
                'id' => 'ERI',
                'name' => 'Érythrée',
            ),
            'EH' => array(
                'id' => 'ESH',
                'name' => 'Sahara Occidental',
            ),
            'ES' => array(
                'id' => 'ESP',
                'name' => 'Espagne',
            ),
            'EE' => array(
                'id' => 'EST',
                'name' => 'Estonie',
            ),
            'ET' => array(
                'id' => 'ETH',
                'name' => 'Éthiopie',
            ),
            'FI' => array(
                'id' => 'FIN',
                'name' => 'Finlande',
            ),
            'FJ' => array(
                'id' => 'FJI',
                'name' => 'Fidji',
            ),
            'FK' => array(
                'id' => 'FLK',
                'name' => 'Falkland, îles (Malvinas)',
            ),
            'FO' => array(
                'id' => 'FRO',
                'name' => 'Féroé, îles',
            ),
            'FM' => array(
                'id' => 'FSM',
                'name' => 'Micronésie, état fédérés',
            ),
            'GA' => array(
                'id' => 'GAB',
                'name' => 'Gabon',
            ),
            'GB' => array(
                'id' => 'GBR',
                'name' => 'Royaume-Uni',
            ),
            'GE' => array(
                'id' => 'GEO',
                'name' => 'Géorgie',
            ),
            'GG' => array(
                'id' => 'GGY',
                'name' => 'Guernesey',
            ),
            'GH' => array(
                'id' => 'GHA',
                'name' => 'Ghana',
            ),
            'GI' => array(
                'id' => 'GIB',
                'name' => 'Gibraltar',
            ),
            'GN' => array(
                'id' => 'GIN',
                'name' => 'Guinée',
            ),
            'GP' => array(
                'id' => 'GLP',
                'name' => 'Guadeloupe',
            ),
            'GM' => array(
                'id' => 'GMB',
                'name' => 'Gambie',
            ),
            'GW' => array(
                'id' => 'GNB',
                'name' => 'Guinée-bissau',
            ),
            'GQ' => array(
                'id' => 'GNQ',
                'name' => 'Guinée équatoriale',
            ),
            'GR' => array(
                'id' => 'GRC',
                'name' => 'Grèce',
            ),
            'GD' => array(
                'id' => 'GRD',
                'name' => 'Grenade',
            ),
            'GL' => array(
                'id' => 'GRL',
                'name' => 'Groenland',
            ),
            'GT' => array(
                'id' => 'GTM',
                'name' => 'Guatemala',
            ),
            'GF' => array(
                'id' => 'GUF',
                'name' => 'Guyane française',
            ),
            'GU' => array(
                'id' => 'GUM',
                'name' => 'Guam',
            ),
            'GY' => array(
                'id' => 'GUY',
                'name' => 'Guyana',
            ),
            'HK' => array(
                'id' => 'HKG',
                'name' => 'Hong Kong',
            ),
            'HM' => array(
                'id' => 'HMD',
                'name' => 'Heard-et-Îles Macdonald',
            ),
            'HN' => array(
                'id' => 'HND',
                'name' => 'Honduras',
            ),
            'HR' => array(
                'id' => 'HRV',
                'name' => 'Croatie',
            ),
            'HT' => array(
                'id' => 'HTI',
                'name' => 'Haïti ',
            ),
            'HU' => array(
                'id' => 'HUN',
                'name' => 'Hongrie',
            ),
            'ID' => array(
                'id' => 'IDN',
                'name' => 'Indonésie ',
            ),
            'IM' => array(
                'id' => 'IMN',
                'name' => 'Île de Man',
            ),
            'IN' => array(
                'id' => 'IND',
                'name' => 'Inde',
            ),
            'IO' => array(
                'id' => 'IOT',
                'name' => 'Océan Indien, Territoire Britannique',
            ),
            'IE' => array(
                'id' => 'IRL',
                'name' => 'Irlande',
            ),
            'IR' => array(
                'id' => 'IRN',
                'name' => 'Iran, république Islamique',
            ),
            'IQ' => array(
                'id' => 'IRQ',
                'name' => 'Iraq',
            ),
            'IS' => array(
                'id' => 'ISL',
                'name' => 'Islande',
            ),
            'IL' => array(
                'id' => 'ISR',
                'name' => 'Israël',
            ),
            'IT' => array(
                'id' => 'ITA',
                'name' => 'Italie',
            ),
            'JM' => array(
                'id' => 'JAM',
                'name' => 'Jamaïque',
            ),
            'JE' => array(
                'id' => 'JEY',
                'name' => 'Jersey',
            ),
            'JO' => array(
                'id' => 'JOR',
                'name' => 'Jordanie',
            ),
            'JP' => array(
                'id' => 'JPN',
                'name' => 'Japon',
            ),
            'KZ' => array(
                'id' => 'KAZ',
                'name' => 'Kazakhstan',
            ),
            'KE' => array(
                'id' => 'KEN',
                'name' => 'Kenya',
            ),
            'KG' => array(
                'id' => 'KGZ',
                'name' => 'Kirghizistan',
            ),
            'KH' => array(
                'id' => 'KHM',
                'name' => 'Cambodge',
            ),
            'KI' => array(
                'id' => 'KIR',
                'name' => 'Kiribati',
            ),
            'BL' => array(
                'id' => 'KNA',
                'name' => 'Saint-barthélemy',
            ),
            'KR' => array(
                'id' => 'KOR',
                'name' => 'Corée',
            ),
            'KW' => array(
                'id' => 'KWT',
                'name' => 'Koweït',
            ),
            'LA' => array(
                'id' => 'LAO',
                'name' => 'Lao, république démocratique populaire',
            ),
            'LB' => array(
                'id' => 'LBN',
                'name' => 'Liban',
            ),
            'LR' => array(
                'id' => 'LBR',
                'name' => 'Libéria',
            ),
            'LY' => array(
                'id' => 'LBY',
                'name' => 'Libye',
            ),
            'LCA' => array(
                'id' => 'LCA',
                'name' => 'Sainte-hélène, Ascension et Tritan Da Cunha',
            ),
            'LI' => array(
                'id' => 'LIE',
                'name' => 'Liechtenstein',
            ),
            'LK' => array(
                'id' => 'LKA',
                'name' => 'Sri Lanka',
            ),
            'LS' => array(
                'id' => 'LSO',
                'name' => 'Lesotho',
            ),
            'LT' => array(
                'id' => 'LTU',
                'name' => 'Lituanie',
            ),
            'LU' => array(
                'id' => 'LUX',
                'name' => 'Luxembourg',
            ),
            'LV' => array(
                'id' => 'LVA',
                'name' => 'Lettonie',
            ),
            'MO' => array(
                'id' => 'MAC',
                'name' => 'Macao',
            ),
            'MF' => array(
                'id' => 'MAF',
                'name' => 'Saint-Martin(partie française)',
            ),
            'MA' => array(
                'id' => 'MAR',
                'name' => 'Maroc',
            ),
            'MC' => array(
                'id' => 'MCO',
                'name' => 'Monaco',
            ),
            'MD' => array(
                'id' => 'MDA',
                'name' => 'Moldova',
            ),
            'MG' => array(
                'id' => 'MDG',
                'name' => 'Madagascar',
            ),
            'MV' => array(
                'id' => 'MDV',
                'name' => 'Maldives',
            ),
            'MX' => array(
                'id' => 'MEX',
                'name' => 'Mexique',
            ),
            'MH' => array(
                'id' => 'MHL',
                'name' => 'Marshall, îles',
            ),
            'MK' => array(
                'id' => 'MKD',
                'name' => 'Macédoine',
            ),
            'ML' => array(
                'id' => 'MLI',
                'name' => 'Mali',
            ),
            'MT' => array(
                'id' => 'MLT',
                'name' => 'Malte',
            ),
            'MM' => array(
                'id' => 'MMR',
                'name' => 'Myanmar',
            ),
            'ME' => array(
                'id' => 'MNE',
                'name' => 'Monténégro',
            ),
            'MN' => array(
                'id' => 'MNG',
                'name' => 'Mongolie',
            ),
            'MP' => array(
                'id' => 'MNP',
                'name' => 'Mariannes du Nord',
            ),
            'MZ' => array(
                'id' => 'MOZ',
                'name' => 'Mozambique',
            ),
            'MR' => array(
                'id' => 'MRT',
                'name' => 'Mauritanie',
            ),
            'MS' => array(
                'id' => 'MSR',
                'name' => 'Montserrat',
            ),
            'MQ' => array(
                'id' => 'MTQ',
                'name' => 'Martinique',
            ),
            'MU' => array(
                'id' => 'MUS',
                'name' => 'Maurice',
            ),
            'MW' => array(
                'id' => 'MWI',
                'name' => 'Malawi',
            ),
            'MY' => array(
                'id' => 'MYS',
                'name' => 'Malaisie',
            ),
            'YT' => array(
                'id' => 'MYT',
                'name' => 'Mayotte',
            ),
            'NA' => array(
                'id' => 'NAM',
                'name' => 'Namibie',
            ),
            'NC' => array(
                'id' => 'NCL',
                'name' => 'Nouvelle-Calédonie ',
            ),
            'NE' => array(
                'id' => 'NER',
                'name' => 'Niger',
            ),
            'NF' => array(
                'id' => 'NFK',
                'name' => 'Norfolk',
            ),
            'NG' => array(
                'id' => 'NGA',
                'name' => 'Nigéria',
            ),
            'NI' => array(
                'id' => 'NIC',
                'name' => 'Nicaragua',
            ),
            'NU' => array(
                'id' => 'NIU',
                'name' => 'Niué',
            ),
            'NL' => array(
                'id' => 'NLD',
                'name' => 'Pays-bas',
            ),
            'NO' => array(
                'id' => 'NOR',
                'name' => 'Norvège',
            ),
            'NP' => array(
                'id' => 'NPL',
                'name' => 'Népal',
            ),
            'NR' => array(
                'id' => 'NRU',
                'name' => 'Nauru ',
            ),
            'NZ' => array(
                'id' => 'NZL',
                'name' => 'Nouvelle-Zélande ',
            ),
            'OM' => array(
                'id' => 'OMN',
                'name' => 'Oman',
            ),
            'PK' => array(
                'id' => 'PAK',
                'name' => 'Pakistan',
            ),
            'PA' => array(
                'id' => 'PAN',
                'name' => 'Panama',
            ),
            'PN' => array(
                'id' => 'PCN',
                'name' => 'Pitcairn',
            ),
            'PE' => array(
                'id' => 'PER',
                'name' => 'Pérou',
            ),
            'PH' => array(
                'id' => 'PHL',
                'name' => 'Philippines',
            ),
            'PW' => array(
                'id' => 'PLW',
                'name' => 'Palaos',
            ),
            'PG' => array(
                'id' => 'PNG',
                'name' => 'Papouasie-Nouvelle-Guinée',
            ),
            'PL' => array(
                'id' => 'POL',
                'name' => 'Pologne',
            ),
            'PR' => array(
                'id' => 'PRI',
                'name' => 'Porto Rico',
            ),
            'KP' => array(
                'id' => 'PRK',
                'name' => 'Corée, république populaire démocratique',
            ),
            'PT' => array(
                'id' => 'PRT',
                'name' => 'Portugal',
            ),
            'PY' => array(
                'id' => 'PRY',
                'name' => 'Paraguay',
            ),
            'PS' => array(
                'id' => 'PSE',
                'name' => 'Palestinien occupé',
            ),
            'PF' => array(
                'id' => 'PYF',
                'name' => 'Polynésie',
            ),
            'QA' => array(
                'id' => 'QAT',
                'name' => 'Qatar',
            ),
            'RE' => array(
                'id' => 'REU',
                'name' => 'Réunion ',
            ),
            'RO' => array(
                'id' => 'ROU',
                'name' => 'Roumanie',
            ),
            'RU' => array(
                'id' => 'RUS',
                'name' => 'Russie',
            ),
            'RW' => array(
                'id' => 'RWA',
                'name' => 'Rwanda',
            ),
            'SA' => array(
                'id' => 'SAU',
                'name' => 'Arabie Saoudite',
            ),
            'SD' => array(
                'id' => 'SDN',
                'name' => 'Soudan',
            ),
            'SN' => array(
                'id' => 'SEN',
                'name' => 'Sénégal',
            ),
            'SG' => array(
                'id' => 'SGP',
                'name' => 'Singapour',
            ),
            'GS' => array(
                'id' => 'SGS',
                'name' => 'Géorgie du Sud-Et-Les îles Sandwich du Sud',
            ),
            'MF' => array(
                'id' => 'SHN',
                'name' => 'Saint-Marin',
            ),
            'SJ' => array(
                'id' => 'SJM',
                'name' => 'Svalbard et île Jan Mayen',
            ),
            'SB' => array(
                'id' => 'SLB',
                'name' => 'Salomon',
            ),
            'SL' => array(
                'id' => 'SLE',
                'name' => 'Sierra Leone',
            ),
            'SV' => array(
                'id' => 'SLV',
                'name' => 'El Salvador',
            ),
            'SM' => array(
                'id' => 'SMR',
                'name' => 'Saint-Martin (partie néerlandaise)',
            ),
            'SO' => array(
                'id' => 'SOM',
                'name' => 'Somalie',
            ),
            'VA' => array(
                'id' => 'SPM',
                'name' => 'Saint-Siège',
            ),
            'RS' => array(
                'id' => 'SRB',
                'name' => 'Serbie',
            ),
            'SSD' => array(
                'id' => 'SSD',
                'name' => 'Soudan Du Sud',
            ),
            'ST' => array(
                'id' => 'STP',
                'name' => 'Sao Tomé-Et-Principe',
            ),
            'SR' => array(
                'id' => 'SUR',
                'name' => 'Suriname',
            ),
            'SK' => array(
                'id' => 'SVK',
                'name' => 'Slovaquie',
            ),
            'SI' => array(
                'id' => 'SVN',
                'name' => 'Slovénie',
            ),
            'SE' => array(
                'id' => 'SWE',
                'name' => 'Suède',
            ),
            'SZ' => array(
                'id' => 'SWZ',
                'name' => 'Swaziland',
            ),
            'PM' => array(
                'id' => 'SXM',
                'name' => 'Saint-Pierre-Et-Miquelon',
            ),
            'SC' => array(
                'id' => 'SYC',
                'name' => 'Seychelles',
            ),
            'SY' => array(
                'id' => 'SYR',
                'name' => 'Syrienne, république arabe',
            ),
            'TC' => array(
                'id' => 'TCA',
                'name' => 'Turks-Et-Caïcos',
            ),
            'TD' => array(
                'id' => 'TCD',
                'name' => 'Tchad',
            ),
            'TG' => array(
                'id' => 'TGO',
                'name' => 'Togo',
            ),
            'TH' => array(
                'id' => 'THA',
                'name' => 'Thaïlande ',
            ),
            'TJ' => array(
                'id' => 'TJK',
                'name' => 'Tadjikistan ',
            ),
            'TK' => array(
                'id' => 'TKL',
                'name' => 'Tokelau',
            ),
            'TM' => array(
                'id' => 'TKM',
                'name' => 'Turkménistan',
            ),
            'TL' => array(
                'id' => 'TLS',
                'name' => 'Timor-Leste',
            ),
            'TO' => array(
                'id' => 'TON',
                'name' => 'Tonga',
            ),
            'TT' => array(
                'id' => 'TTO',
                'name' => 'Trinité-Et-Tobago',
            ),
            'TN' => array(
                'id' => 'TUN',
                'name' => 'Tunisie',
            ),
            'TR' => array(
                'id' => 'TUR',
                'name' => 'Turquie',
            ),
            'TV' => array(
                'id' => 'TUV',
                'name' => 'Tuvalu',
            ),
            'TW' => array(
                'id' => 'TWN',
                'name' => 'Taïwan',
            ),
            'TZ' => array(
                'id' => 'TZA',
                'name' => 'Tanzanie',
            ),
            'UG' => array(
                'id' => 'UGA',
                'name' => 'Ouganda',
            ),
            'UA' => array(
                'id' => 'UKR',
                'name' => 'Ukraine',
            ),
            'UMI' => array(
                'id' => 'UMI',
                'name' => 'Îles mineures éloignées des États-Unis',
            ),
            'UY' => array(
                'id' => 'URY',
                'name' => 'Uruguay',
            ),
            'US' => array(
                'id' => 'USA',
                'name' => 'États-Unis ',
            ),
            'UZ' => array(
                'id' => 'UZB',
                'name' => 'Ouzbékistan ',
            ),
            'VC' => array(
                'id' => 'VAT',
                'name' => 'Saint-Vincent-Et-Les Grenadines',
            ),
            'LC' => array(
                'id' => 'VCT',
                'name' => 'Sainte-Lucie',
            ),
            'VE' => array(
                'id' => 'VEN',
                'name' => 'Venezuela',
            ),
            'VG' => array(
                'id' => 'VGB',
                'name' => 'Îles vierges britaniques',
            ),
            'VI' => array(
                'id' => 'VIR',
                'name' => 'Îles vierges des États-Unis',
            ),
            'VN' => array(
                'id' => 'VNM',
                'name' => 'Vietnam',
            ),
            'VU' => array(
                'id' => 'VUT',
                'name' => 'Vanuatu',
            ),
            'WF' => array(
                'id' => 'WLF',
                'name' => 'Wallis et Futuna',
            ),
            'WS' => array(
                'id' => 'WSM',
                'name' => 'Samoa',
            ),
            'YE' => array(
                'id' => 'YEM',
                'name' => 'Yémen ',
            ),
            'ZA' => array(
                'id' => 'ZAF',
                'name' => 'Afrique Du Sud',
            ),
            'ZM' => array(
                'id' => 'ZMB',
                'name' => 'Zambie',
            ),
            'ZW' => array(
                'id' => 'ZWE',
                'name' => 'Zimbabwe',
            ),
        );
    }
}
