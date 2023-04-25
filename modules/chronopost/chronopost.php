<?php
define('MIN_VERSION', '1.5');
define('MAX_VERSION', '1.8');

require_once 'libraries/Chronofresh.php';

/**
 * Class Chronopost
 */
class Chronopost extends CarrierModule
{
    /**
     * @var string
     */
    public $id_carrier;

    const CHRONO_SHIP_TYPE = 0;
    const CHRONO_RETURN_TYPE = 1;
    const CHRONOPOST_TYPE_ID = '1';
    const CHRONOFRESH_TYPE_ID = '2';

    /**
     * @var int
     */
    public static $idTaxRulesGroup = 1;

    /**
     * @var string
     */
    public static $trackingUrl = 'http://www.chronopost.fr/tracking-no-cms/suivi-page?listeNumerosLT=@&langue=fr';

    /**
     * @var array[]
     */
    public static $carriersDefinitions = array(
        'CHRONO10'             => array(
            'product_code'     => '02',
            'name'             => 'Chronopost - Livraison express à domicile avant 10h',
            'product_code_bal' => '02',
            'delay'            => array(
                'fr' => 'Colis livré le lendemain matin avant 10h à votre domicile. La veille de la livraison, vous êtes averti par e-mail et SMS.',
                'en' => 'Parcels delivered the next day before 10am at your home. The day before delivery, You\'ll be notified by e-mail and SMS.'
            ),
        ),
        'CHRONO13'             => array(
            'product_code'     => '01',
            'name'             => 'Chronopost - Livraison express à domicile avant 13h',
            'product_code_bal' => '01',
            'delay'            => array(
                'fr' => 'Colis livré le lendemain matin avant 13h à votre domicile. La veille de la livraison, vous êtes averti par e-mail et SMS.',
                'en' => 'Parcels delivered the next day before 13pm at your home. The day before delivery, You\'ll be notified by e-mail and SMS.'
            ),
        ),
        'CHRONO13_INSTANCE'    => array(
            'product_code'     => '1S',
            'name'             => 'Chronopost - Livraison express à domicile avant 13h',
            'product_code_bal' => '1S',
            'delay'            => array(
                'fr' => 'Colis livré le lendemain matin avant 13h à votre domicile. La veille de la livraison, vous êtes averti par e-mail et SMS. Si vous n\'êtes pas présent, votre colis est déposé dans le relais le plus proche.',
                'en' => 'Parcels delivered the next day before 13pm at your home. The day before delivery, You\'ll be notified by e-mail and SMS. If you are not present, your parcel is deposited in the nearest relay.'
            ),
        ),
        'CHRONO18'             => array(
            'product_code'     => '16',
            'name'             => 'Chronopost - Livraison express à domicile avant 18h',
            'product_code_bal' => '16',
            'delay'            => array(
                'fr' => 'Colis livré le lendemain matin avant 18h à votre domicile. La veille de la livraison, vous êtes averti par e-mail et SMS.',
                'en' => 'Parcels delivered the next day before 18pm at your home. The day before delivery, You\'ll be notified by e-mail and SMS.'
            ),
        ),
        'CHRONORELAIS'         => array(
            'product_code'     => '86',
            'name'             => 'Chronopost - Livraison express en relais Pickup',
            'product_code_bal' => '86',
            'delay'            => array(
                'fr' => 'Colis livré le lendemain avant 13 h dans le relais Pickup de votre choix. Vous serez averti par e-mail et SMS.',
                'en' => 'Parcels delivered the next day before 1pm in the Pickup relay of your choice. You\'ll be notified by e-mail and SMS.'
            ),
        ),
        'CHRONORELAIS_AMBIENT' => array(
            'fresh'            => true,
            'product_code'     => '5Q',
            'name'             => 'Chronofresh - Livraison express en relais Pickup',
            'product_code_bal' => '5Q',
            'delay'            => array(
                'fr' => 'Colis livré le lendemain avant 13 h dans le relais Pickup de votre choix. Vous serez averti par e-mail et SMS.',
                'en' => 'Parcels delivered the next day before 1pm in the Pickup relay of your choice. You\'ll be notified by e-mail and SMS.'
            )
        ),
        'TOSHOPDIRECT'         => array(
            'product_code'     => '5X',
            'name'             => 'Chronopost - Livraison en relais Pickup',
            'product_code_bal' => '5X',
            'delay'            => array(
                'fr' => 'Colis livré en 2 à 3 jours dans le relais Pickup de votre choix. Vous serez averti par e-mail',
                'en' => 'Parcels delivered in 2 to 3 days in the Pickup point of your choice. You\'ll be notified by e-mail'
            ),
        ),
        'TOSHOPDIRECT_EUROPE'  => array(
            'product_code'     => '6B',
            'name'             => 'Chronopost - Livraison Europe en relais Pickup',
            'product_code_bal' => '6B',
            'delay'            => array(
                'fr' => 'Colis livré en 3 à 7 jours vers l’Europe dans le relais Pickup de votre choix.',
                'en' => 'Parcels delivered in 3 to 7 days to Europe in the Pickup point of your choice.'
            ),
        ),
        'CHRONOCLASSIC'        => array(
            'shared_carrier'   => true,
            'product_code'     => '44',
            'name'             => 'Chronopost - Livraison à domicile',
            'product_code_bal' => '44',
            'delay'            => array(
                'fr' => 'Colis livré en 1 à 3 jours vers l\'Europe.',
                'Parcels delivered to Europe in 1 to 3 days'
            ),
        ),
        'CHRONOEXPRESS'        => array(
            'shared_carrier'   => true,
            'product_code'     => '17',
            'name'             => 'Chronopost - Livraison express à domicile',
            'product_code_bal' => '17',
            'delay'            => array(
                'fr' => 'Colis livré en 1 à 3 jours vers l\'Europe, en 48h vers les DOM et en 2 à 5 jours vers le reste du monde.',
                'en' => 'Parcels delivered to Europe in 1 to 3 days, 48 hours to the DOM and 2 to 5 days to the rest of the world.'
            ),
        ),
        'RELAISEUROPE'         => array(
            'product_code'     => '49',
            'name'             => 'Chronopost - Livraison Europe en relais Pickup',
            'product_code_bal' => '49',
            'delay'            => array(
                'fr' => 'Colis livré en 2 à 6 jours vers l’Europe dans le relais Pickup de votre choix.',
                'en' => 'Parcels delivered in 2 to 6 days to Europe in the Pickup point of your choice.'
            ),
        ),
        'RELAISDOM'            => array(
            'product_code'     => '4P',
            'name'             => 'Chronopost – Livraison DOM en relais Pickup',
            'product_code_bal' => '4P',
            'delay'            => array(
                'fr' => 'Colis livré en 3 à 4 jours vers les DOM dans le relais Pickup de votre choix.',
                'en' => 'Parcels delivered in 3 to 4 days to the DOM in the Pickup point of your choice.'
            ),
        ),
        'SAMEDAY'              => array(
            'product_code'     => '4I',
            'name'             => 'Chronopost - Livraison Sameday',
            'product_code_bal' => '4I',
            'delay'            => array('fr' => 'Livraison le jour même.')
        ),
        'CHRONORDV'            => array(
            'product_code'     => '2O',
            'name'             => 'Chronopost - Livraison express sur rendez-vous',
            'product_code_bal' => '2O',
            'delay'            => array('fr' => 'Livraison sur rendez-vous.')
        ),
        'CHRONOFRESH_CLASSIC'  => array(
            'fresh'            => true,
            'product_code'     => '4X',
            'name'             => 'ChronoFresh - Livraison à domicile',
            'product_code_bal' => '4X',
            'delay'            => array(
                'fr' => 'Colis livré en 1 à 3 jours vers l\'Europe.',
                'en' => 'Parcels delivered to Europe in 1 to 3 days'
            ),
        ),
        'CHRONOFRESH'          => array(
            'fresh'        => true,
            'product_code' => '5T',
            'name'         => 'ChronoFresh - Livraison express à domicile avant 13H',
            'delay'        => array(
                'fr' => 'Colis livré le lendemain matin avant 13h à votre domicile. La veille de la livraison, vous êtes averti par e-mail et SMS.',
                'en' => 'Parcels delivered the next day before 13pm at your home. The day before delivery, You\'ll be notified by e-mail and SMS.'
            ),
            'products'     => [
                [
                    'code'  => '1T',
                    'label' => 'Chrono13 Sec (1T)'
                ],
                [
                    'code'  => '5T',
                    'label' => 'Chrono13 Sec (5T)'
                ],
                [
                    'code'  => '2R',
                    'label' => 'Chrono13 Fresh (2R)'
                ],
                [
                    'code'  => '2S',
                    'label' => 'Chrono13 Freeze (2S)'
                ],
            ]
        ),
    );

    /**
     * @var webservicesHelper
     */
    protected static $_wsHelper;

    /**
     * @var int
     */
    public static $RETURN_ADDRESS_RETURN = 0;

    /**
     * @var chronopostPaymentHelper
     */
    protected static $_paymentHelper;

    /**
     * @var int
     */
    public static $RETURN_ADDRESS_INVOICE = 1;

    /**
     * @var int
     */
    public static $RETURN_ADDRESS_SHIPPING = 2;

    /**
     * Chronopost constructor.
     */
    public function __construct()
    {
        $this->name = 'chronopost';
        $this->tab = 'shipping_logistics';

        $this->version = '6.4.0';
        $this->bootstrap = true;
        $this->author = $this->l('Chronopost Official');
        $this->module_key = 'ed72dc5234f171ec266a664a8088d8ef';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.8');
        parent::__construct();

        $this->displayName = $this->l('Chronopost');
        $this->description = $this->l('Manage Chronopost and Chronopost Pickup relay');
        $this->confirmUninstall = $this->l('Remember, once this module is uninstalled , you won\'t be able to
        edit Chronopost waybills or propose Pickup delivery point to your customers. Are you sure you wish to proceed?');

        // Check is SOAP is available
        if (!extension_loaded('soap')) {
            $this->warning .= $this->l('The SOAP extension is not available or configured on the server ; The
            module will not work without this extension ! Please contact your host to activate it in your PHP
            installation.');
        }

        if (!self::checkPSVersion()) {
            $this->warning .= $this->l('This module is incompatible with your Prestashop installation. You can visit
the <a href =
"http://www.chronopost.fr/transport-express/livraison-colis/accueil/produits-tarifs/expertise-sectorielle/e-commerce/plateformes">Chronopost.fr
</a>website to download a comptible version.');
        }

        // Check is module is properly configured
        $decode = json_decode(Configuration::get('CHRONOPOST_GENERAL_ACCOUNTS'), 1);
        if (isset($decode[0]) && Tools::strlen($decode[0]['account'] < 8)) {
            $this->warning .= $this->l('You have to configure the module with your Chronopost contract number. If you
 don\'t have one, please sign in to the following address <a href =
 "http://www.chronopost.fr/transport-express/livraison-colis/accueil/produits-tarifs/expertise-sectorielle/pid/8400"
 target = "_blank">www.mychrono.chronopost.fr</a>');
        }
    }

    public function preInstall()
    {
        if (!self::checkPSVersion()) {
            $this->context->controller->errors[] = 'This module is incompatible with your Prestashop installation. You
can visit the <a href =
"http://www.chronopost.fr/transport-express/livraison-colis/accueil/produits-tarifs/expertise-sectorielle/e-commerce/plateformes">Chronopost.fr </a>
website to download a comptible version.';

            return false;
        }

        // Check for SOAP
        if (!extension_loaded('soap')) {
            $this->context->controller->errors[] = $this->l('The SOAP extension is not available or configured on
             the server ; The module will not work without this extension ! Please contact your host to activate it
              in your PHP installation.');

            return false;
        }

        if (!parent::install()) {
            return false;
        }

        // Admin tab
        if (!$this->adminInstall()) {
            return false;
        }

        // Register hooks compatible 1.5 - 1.7.7
        if (!$this->registerHook('actionCarrierUpdate') ||
            !$this->registerHook('actionValidateOrder') ||
            !$this->registerHook('displayHeader') ||
            !$this->registerHook('displayBackOfficeHeader') ||
            !$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('displayAdminOrderMainBottom')
        ) {
            return false;
        }

        // Version specific hooks
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            if (!$this->registerHook('extraCarrier')) {
                return false;
            }
        } else {
            if (!$this->registerHook('displayAfterCarrier')) {
                return false;
            }
        }

        return true;
    }

    /** INSTALLATION-RELATED FUNCTIONS **/
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // new in 3.8.0
        Configuration::updateValue('CHRONOPOST_SATURDAY_DAY_START', -1);

        // new in 4.7.0

        Configuration::updateValue(
            'CHRONOPOST_GENERAL_ACCOUNTS',
            '[{"account":"19869502","subaccount":"","password":"255562","accountname":"Chronopost test"}]'
        );
        Configuration::updateValue('CHRONOPOST_GENERAL_PRINTMODE', 'PDF');
        Configuration::updateValue('CHRONOPOST_GENERAL_WEIGHTCOEF', '1');
        Configuration::updateValue('CHRONOPOST_SHIPPER_CIVILITY', 'M');
        Configuration::updateValue('CHRONOPOST_CUSTOMER_CIVILITY', 'M');
        Configuration::updateValue('CHRONOPOST_RETURN_CIVILITY', 'M');
        Configuration::updateValue('CHRONOPOST_RETURN_DEFAULT', '0');
        Configuration::updateValue('CHRONOPOST_MAP_ENABLED', '0');
        Configuration::updateValue('CHRONOPOST_ADVALOREM_ENABLED', '0');
        Configuration::updateValue('CHRONOPOST_BAL_ENABLED', '0');
        Configuration::updateValue('CHRONOPOST_SATURDAY_ACTIVE', 'no');
        Configuration::updateValue('CHRONOPOST_SATURDAY_CHECKED', 'no');
        Configuration::updateValue('CHRONOPOST_SATURDAY_HOUR_START', '18');
        Configuration::updateValue('CHRONOPOST_SATURDAY_MINUTE_START', '0');
        Configuration::updateValue('CHRONOPOST_SATURDAY_DAY_END', '5');
        Configuration::updateValue('CHRONOPOST_SATURDAY_HOUR_END', '16');
        Configuration::updateValue('CHRONOPOST_SATURDAY_MINUTE_END', '0');
        Configuration::updateValue('CHRONOPOST_QUICKCOST_ENABLED', '0');

        // Chrono Precise
        Configuration::updateValue('CHRONOPOST_RDV_DELAY', '1');
        Configuration::updateValue('CHRONOPOST_RDV_DAY_ON', '-1');
        Configuration::updateValue('CHRONOPOST_RDV_HOUR_ON', '0');
        Configuration::updateValue('CHRONOPOST_RDV_MINUTE_ON', '0');
        Configuration::updateValue('CHRONOPOST_RDV_DAY_CLOSE_ST', '0');
        Configuration::updateValue('CHRONOPOST_RDV_HR_CLOSE_ST', '0');
        Configuration::updateValue('CHRONOPOST_RDV_MIN_CLOSE_ST', '0');
        Configuration::updateValue('CHRONOPOST_RDV_DAY_CLOSE_END', '0');
        Configuration::updateValue('CHRONOPOST_RDV_HR_CLOSE_END', '0');
        Configuration::updateValue('CHRONOPOST_RDV_MIN_CLOSE_END', '0');
        Configuration::updateValue('CHRONOPOST_RDV_STATE1', '1');
        Configuration::updateValue('CHRONOPOST_RDV_STATE2', '1');
        Configuration::updateValue('CHRONOPOST_RDV_STATE3', '1');
        Configuration::updateValue('CHRONOPOST_RDV_STATE4', '1');
        Configuration::updateValue('CHRONOPOST_RDV_PRICE1', null);
        Configuration::updateValue('CHRONOPOST_RDV_PRICE2', null);
        Configuration::updateValue('CHRONOPOST_RDV_PRICE3', null);
        Configuration::updateValue('CHRONOPOST_RDV_PRICE4', null);

        // new in 5.0.0
        Configuration::updateValue('CHRONOPOST_SAMEDAY_SAMEDAY_HOUR_END', '15');
        Configuration::updateValue('CHRONOPOST_SAMEDAY_SAMEDAY_MINUTE_END', '0');
        Configuration::updateValue('CHRONOPOST_MAP_DROPMODE', 'P');
        Configuration::updateValue('CHRONOPOST_SATURDAY_CUSTOMER', 'no');
        Configuration::updateValue('CHRONOPOST_SATURDAY_SUPPLEMENT', null);
        Configuration::updateValue('CHRONOPOST_SATURDAY_CARRIERS', '[]');

        foreach (self::$carriersDefinitions as $productCode => $product) {
            Configuration::updateValue('CHRONOPOST_' . $productCode . '_ACCOUNT', '19869502');
            Configuration::updateValue('CHRONOPOST_' . $productCode . '_ID', '-1');
        }

        $addrCodes = array(
            "SHIPPER",
            "CUSTOMER",
            "RETURN"
        );

        foreach ($addrCodes as $code) {
            Configuration::updateValue('CHRONOPOST_' . $code . '_NAME', 'Chronopost SAS');
            Configuration::updateValue('CHRONOPOST_' . $code . '_ADDRESS', '3 avenue Gallieni');
            Configuration::updateValue('CHRONOPOST_' . $code . '_ZIPCODE', '94250');
            Configuration::updateValue('CHRONOPOST_' . $code . '_CITY', 'Gentilly');
            Configuration::updateValue('CHRONOPOST_' . $code . '_COUNTRY', 'FR');
            Configuration::updateValue('CHRONOPOST_' . $code . '_CONTACTNAME', 'Centre de service Chronopost');
            Configuration::updateValue('CHRONOPOST_' . $code . '_EMAIL', 'demandez.a.chronopost@chronopost.fr');
            Configuration::updateValue('CHRONOPOST_' . $code . '_PHONE', '0 825 885 866*');
        }

        DB::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'chrono_calculateproducts_cache2` (
             `id` int(11) NOT null AUTO_INCREMENT,
             `postcode` varchar(10) NOT null,
             `country` varchar(2) NOT null,
             `chrono10` tinyint(1) NOT null,
             `chrono18` tinyint(1) NOT null,
             `chronoclassic` tinyint(1) NOT null,
             `relaiseurope` tinyint(1) NOT null,
             `relaisdom` tinyint(1) NOT null,
             `rdv` tinyint(1) NOT null,
             `sameday` tinyint(1) NOT null,
             `dimanchebal` INT NOT NULL,
             `toshop` INT NOT NULL,
             `toshopeurope` INT NOT NULL,
             `last_updated` int(11) NOT null,
             PRIMARY KEY (`id`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1 ;');

        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'chrono_cart_relais` (
                `id_cart` int(10) NOT null,
                `id_pr` varchar(10) NOT null,
                PRIMARY KEY (`id_cart`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8;');

        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'chrono_cart_saturday_supplement` (
                `id_cart` int(10) NOT null,
                `saturday_supplement` int null,
                PRIMARY KEY (`id_cart`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8;');

        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'chrono_cart_creneau` (
                `id_cart` int(10) NOT NULL,
                `rank` int(10) NOT NULL,
                `delivery_date` varchar(29) NOT NULL,
                `delivery_date_end` VARCHAR(29) NULL,
                `slot_code` varchar(10) NOT NULL,
                `tariff_level` int(10) NOT NULL,
                `transaction_id` varchar(60) NOT NULL,
                `fee` decimal(20,6) NOT NULL,
                `product_code` VARCHAR(2) NULL DEFAULT NULL,
                `service_code` VARCHAR(6) NULL DEFAULT NULL,
                `as_code` VARCHAR(6) NULL DEFAULT NULL,
                PRIMARY KEY (`id_cart`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'chrono_lt_history` (
                `id_order` int(10) NOT null,
                `lt` varchar(20) NOT null,
                `lt_reference` varchar(20) NOT null,
                `lt_dlc` varchar(20) null,
                `product` varchar(2) NOT null,
                `zipcode` varchar(10) NOT null,
                `country` varchar(2) NOT null,
                `insurance` int(10) NOT null,
                `city` varchar(32) NOT null,
                `account_number` varchar(8) NOT null,
                `type` int(11) NOT null,
                `cancelled` int null,
                PRIMARY KEY (`id_order`, `lt`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8;');

        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'chrono_quickcost_cache` (
                `id` int(11) NOT null AUTO_INCREMENT,
                `product_code` varchar(2) NOT null,
                `arrcode` varchar(10) NOT null,
                `weight` decimal(10,2) NOT null,
                `price` decimal(10,2) NOT null,
                `account_number` varchar(8) NOT null,
                `last_updated` int(11) NOT null,
                PRIMARY KEY (`id`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1 ;');

        // pre install
        if (!$this->preInstall()) {
            return false;
        }

        // init config
        if (!Configuration::updateValue('CHRONOPOST_SECRET', sha1(microtime(true) . mt_rand(10000, 90000)))
            || !Configuration::updateValue('CHRONOPOST_CORSICA_SUPPLEMENT', '19.60')
            || !Configuration::updateValue('CHRONOPOST_RDV_DELAY', '1')) {
            return false;
        }

        return true;
    }

    public function adminInstall()
    {
        $tabExport = new Tab();
        $tabExport->class_name = 'AdminExportChronopost';
        $tabExport->id_parent = Tab::getIdFromClassName('AdminParentShipping');
        $tabExport->module = 'chronopost';
        foreach (Language::getLanguages(false) as $language) {
            $tabExport->name[$language['id_lang']] = $this->l('Chronopost Export');
        }

        $tabExportFresh = new Tab();
        $tabExportFresh->class_name = 'AdminExportChronofresh';
        $tabExportFresh->id_parent = Tab::getIdFromClassName('AdminParentShipping');
        $tabExportFresh->module = 'chronopost';
        foreach (Language::getLanguages(false) as $language) {
            $tabExportFresh->name[$language['id_lang']] = $this->l('Chronofresh Export');
        }

        $tabImport = new Tab();
        $tabImport->class_name = 'AdminImportChronopost';
        $tabImport->id_parent = Tab::getIdFromClassName('AdminParentShipping');
        $tabImport->module = 'chronopost';
        foreach (Language::getLanguages(false) as $language) {
            $tabImport->name[$language['id_lang']] = $this->l('Chronopost Import');
        }

        $tabBordereau = new Tab();
        $tabBordereau->class_name = 'AdminBordereauChronopost';
        $tabBordereau->id_parent = Tab::getIdFromClassName('AdminParentShipping');
        $tabBordereau->module = 'chronopost';
        foreach (Language::getLanguages(false) as $language) {
            $tabBordereau->name[$language['id_lang']] = $this->l('Daily docket');
        }

        return $tabImport->add() && $tabExportFresh->add() && $tabExport->add() && $tabBordereau->add();
    }

    public function loadExternalTranslations()
    {
        $this->l("Product not available : you can't create this carrier with this contract");
        $this->l("An error occurred while creating the carrier. Please check your settings (contract and addresses).");
    }

    public static function checkPSVersion()
    {
        return ((version_compare(_PS_VERSION_, MIN_VERSION) >= 0) && (version_compare(_PS_VERSION_, MAX_VERSION) < 0));
    }

    /**
     * @return webservicesHelper
     * @todo replace all instanciations by this method
     */
    public static function getWsHelper()
    {
        if (!self::$_wsHelper) {
            include_once __DIR__ . '/libraries/webservicesHelper.php';
            self::$_wsHelper = new webservicesHelper();
        }

        return self::$_wsHelper;
    }

    /**
     * Get payment helper
     *
     * @return ChronopostPaymentHelper
     * @throws PrestaShopDatabaseException
     */
    public static function getPaymentHelper()
    {
        if (!self::$_paymentHelper) {
            include_once __DIR__ . '/libraries/ChronopostPaymentHelper.php';
            self::$_paymentHelper = new ChronopostPaymentHelper();
        }

        return self::$_paymentHelper;
    }

    public static function createCarrier($code)
    {
        Shop::setContext(Shop::CONTEXT_ALL);

        require_once(__DIR__ . '/libraries/range/RangePrice.php');
        require_once(__DIR__ . '/libraries/range/RangeWeight.php');

        if (!array_key_exists($code, self::$carriersDefinitions)) {
            echo "Code incorrect.";

            return false;
        }

        $carrier = new Carrier();
        $carrier->name = self::$carriersDefinitions[$code]['name'];
        $carrier->id_tax_rules_group = self::$idTaxRulesGroup;
        $carrier->url = self::$trackingUrl;
        $carrier->active = true;
        $carrier->deleted = 0;
        $delays = self::$carriersDefinitions[$code]['delay'];
        foreach ($delays as $key => $delay) {
            $delays[$key] = substr($delay, 0, 128);
        }

        $carrier->delay = $delays;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'chronopost';
        $carrier->need_range = true;

        foreach (Language::getLanguages(true) as $language) {
            if (array_key_exists($language['iso_code'], self::$carriersDefinitions[$code]['delay'])) {
                $carrier->delay[$language['id_lang']] = substr(
                    self::$carriersDefinitions[$code]['delay'][$language['iso_code']],
                    0,
                    128
                );
            } else {
                $carrier->delay[$language['id_lang']] = substr(
                    self::$carriersDefinitions[$code]['delay']['fr'],
                    0,
                    128
                );
            }
        }

        if ($carrier->add()) { // ASSIGN GROUPS
            $groups = Group::getgroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'carrier_group
                    VALUE (\'' . (int)($carrier->id) . '\',\'' . (int)($group['id_group']) . '\')');
            }

            // ASSIGN ZONES
            $zones = Zone::getZones();
            foreach ($zones as $zone) {
                $carrier->addZone($zone['id_zone']);
            }

            // RANGE PRICE
            $rp = new RangePrice();
            $rp->id_carrier = $carrier->id;
            $rp->delimiter1 = 0;
            $rp->delimiter2 = 100000;
            $rp->add();

            $fp = null;
            if (file_exists(__DIR__ . '/csv/' . Tools::strtolower($code) . '.csv')) {
                $fp = fopen(__DIR__ . '/csv/' . Tools::strtolower($code) . '.csv', 'r');
            }

            // fails silently if no CSV
            if ($fp) {
                // insert prices per weight range
                while ($line = fgetcsv($fp)) {
                    $rangeWeight = new RangeWeight();
                    $rangeWeight->id_carrier = $carrier->id;
                    $rangeWeight->delimiter1 = $line[0];
                    $rangeWeight->delimiter2 = $line[1];
                    $rangeWeight->price_to_affect = $line[2];
                    $rangeWeight->add();
                }
            }

            // Set authorized payment methods for the new carrier
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                try {
                    $paymentHelper = self::getPaymentHelper();
                    if ($carrier->id) {
                        $paymentHelper->useAllActivePaymentsForCarrier($carrier->id);
                    }
                } catch (Exception $e) {
                }
            }

            //copy logo
            if (!@copy(__DIR__ . '/views/img/carriers/' . Tools::strtolower($code) . '.jpg',
                _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg')) {
                if (!@copy(__DIR__ . '/views/img/carriers/chronopost2.jpg',
                    _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg')) {
                    return false;
                }
            }
        } else {
            echo "Le transporteur n'a pas été créé.";

            return false;
        }

        return Configuration::updateValue('CHRONOPOST_' . Tools::strtoupper($code) . '_ID', (int)$carrier->id);
    }

    public function uninstall()
    {
        // Remove admin tabs
        $tab = new Tab(Tab::getIdFromClassName('AdminExportChronopost'));
        if (!$tab->delete()) {
            return false;
        }

        $tab = new Tab(Tab::getIdFromClassName('AdminImportChronopost'));
        if (!$tab->delete()) {
            return false;
        }

        $tab = new Tab(Tab::getIdFromClassName('AdminBordereauChronopost'));
        if (!$tab->delete()) {
            return false;
        }

        // Cleanup
        Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'chrono_calculateproducts_cache2`');

        return parent::uninstall();
    }

    public static function gettingReadyForSaturday($carrier = null, $force = null)
    {
        if (Configuration::get('CHRONOPOST_SATURDAY_ACTIVE') !== 'yes') {
            return false;
        }

        $blacklist = [
            Configuration::get('CHRONOPOST_CHRONOCLASSIC_ID'),
            Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID'),
            Configuration::get('CHRONOPOST_RELAISEUROPE_ID'),
            Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID'),
            Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID'),
            Configuration::get('CHRONOPOST_RELAISDOM_ID'),
            Configuration::get('CHRONOPOST_CHRONORDV_ID'),
            Configuration::get('CHRONOPOST_DIMANCHEBAL_ID'),
            Configuration::get('CHRONOPOST_CHRONOFRESH_ID'),
            Configuration::get('CHRONOPOST_CHRONOFRESH_CLASSIC_ID'),
            Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ID')
        ];

        if ($carrier != null && in_array($carrier->id_reference, $blacklist)) {
            return false;
        }

        $start = new DateTime('last sun');
        // COMPAT < 5.6 : no chaining (returns null)
        $start->modify('+' . Configuration::get('CHRONOPOST_SATURDAY_DAY_START') . ' days');
        $start->modify('+' . Configuration::get('CHRONOPOST_SATURDAY_HOUR_START') . ' hours');
        $start->modify('+' . Configuration::get('CHRONOPOST_SATURDAY_MINUTE_START') . ' minutes');
        $end = new DateTime('last sun');
        $end->modify('+' . Configuration::get('CHRONOPOST_SATURDAY_DAY_END') . ' days');
        $end->modify('+' . Configuration::get('CHRONOPOST_SATURDAY_HOUR_END') . ' hours');
        $end->modify('+' . Configuration::get('CHRONOPOST_SATURDAY_MINUTE_END') . ' minutes');

        if ($end < $start) {
            $end->modify('+1 week');
        }

        $now = new DateTime();
        if (($start <= $now && $now <= $end) || $force) {
            return true;
        }

        return false;
    }

    public static function maybeCountryMapping(string $country) {
        $mapping = [
            'YT' => 'YO', // (Mayotte)
            'MC' => 'FR', // (Monaco)
            'MF' => 'PM', // (Saint Marin)
        ];
        return $mapping[$country] ?? $country;
    }

    public static function isSaturdayOptionApplicable()
    {
        if (Configuration::get('CHRONOPOST_SATURDAY_CHECKED') !== 'yes') {
            return false;
        }

        return self::gettingReadyForSaturday();
    }

    public static function trackingStatus($orderId, $shippingNumber)
    {
        $moduleInstance = new Chronopost();

        // MAIL::SEND is bugged in 1.5 !
        // http://forge.prestashop.com/browse/PNM-754 (Unresolved as of 2013-04-15)
        // Context fix (it's that easy)
        Context::getContext()->link = new Link();

        // Fix context by adding employee
        $cookie = new Cookie('psAdmin');
        if (version_compare(_PS_VERSION_, '1.7.6.6', '<')) {
            Context::getContext()->employee = new Employee($cookie->id_employee);
        } else {
            $context = (new PrestaShop\PrestaShop\Adapter\LegacyContext())->getContext();
            $context->employee = new Employee($cookie->id_employee);
            $context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }

        $o = new Order($orderId);

        // Fix compatibility issues with hookActionOrderStatusUpdate
        if (Context::getContext()->currency === null) {
            Context::getContext()->currency = new Currency($o->id_currency);
        }

        $o->shipping_number = $shippingNumber;
        $o->save();

        $idOrderCarrier = self::getIdOrderCarrier($o);
        $orderCarrier = new OrderCarrier($idOrderCarrier);
        if ($orderCarrier->tracking_number == "" || $orderCarrier->tracking_number == null) {
            $orderCarrier->tracking_number = $shippingNumber;
            $orderCarrier->id_order = $orderId;
            $orderCarrier->id_carrier = $o->id_carrier;
            $orderCarrier->update();
        }

        if ($o->getCurrentState() != _PS_OS_SHIPPING_) {
            try {
                $history = new OrderHistory();
                $history->id_order = (int)($o->id);
                $history->id_order_state = _PS_OS_SHIPPING_;
                $history->changeIdOrderState(_PS_OS_SHIPPING_, $o->id, true);
                $history->save();
            } catch (\Exception $e) {
            }
        }

        $customer = new Customer($o->id_customer);
        $carrier = new Carrier($o->id_carrier);
        $trackingUrl = str_replace('@', $o->shipping_number, $carrier->url);

        $trackingDomain = 'www.chronopost.fr';
        if (in_array($carrier->id_reference, self::getToShopIDs())) {
            $trackingDomain = 'www.chronoshop2shop.fr';
        }

        $templateVars = array(
            '{tracking_link}'   => '<a href = "' . $trackingUrl . '">' . $o->shipping_number . '</a>',
            '{tracking_code}'   => $o->shipping_number,
            '{tracking_domain}' => $trackingDomain,
            '{firstname}'       => $customer->firstname,
            '{lastname}'        => $customer->lastname,
            '{id_order}'        => (int)($o->id)
        );

        $subject = $moduleInstance->l('Tracking number for your order');

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            Mail::Send(
                $o->id_lang,
                'tracking',
                $subject,
                $templateVars,
                $customer->email,
                $customer->firstname . ' ' . $customer->lastname,
                null,
                null,
                null,
                null,
                __DIR__ . '/mails/',
                true
            );
        } else {
            Mail::Send(
                $o->id_lang,
                'tracking',
                $subject,
                $templateVars,
                $customer->email,
                $customer->firstname . ' ' . $customer->lastname,
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_ . 'chronopost/mails/',
                true
            );
        }
    }

    public static function getSelectedPickupPoint($id_cart)
    {
        $row = Db::getInstance()->getRow(
            'SELECT id_pr FROM ' . _DB_PREFIX_ . 'chrono_cart_relais WHERE id_cart=' . $id_cart
        );

        return $row['id_pr'];
    }

    public static function getSaturdaySupplement($id_cart)
    {
        $row = Db::getInstance()->getRow(
            'SELECT saturday_supplement FROM ' . _DB_PREFIX_ . 'chrono_cart_saturday_supplement WHERE id_cart=' . $id_cart
        );

        if (!$row) {
            return false;
        }

        return $row['saturday_supplement'];
    }

    public static function getSelectedSlot($id_cart)
    {
        $row = Db::getInstance()->getRow('SELECT slot_code FROM ' . _DB_PREFIX_ . 'chrono_cart_creneau WHERE id_cart=' . $id_cart);

        return $row['slot_code'];
    }

    /**
     * @param Order $order
     * @param bool  $is_return
     * @param bool  $shipSaturday
     *
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getSkybillDetails($order, $is_return = false, $shipSaturday = false)
    {
        include_once __DIR__ . '/libraries/webservicesHelper.php';
        $wsHelper = new webservicesHelper();
        $carrier = new Carrier($order->id_carrier);

        $result = [];
        // Ships with Chrono 13 by default
        $result['productCode'] = Chronopost::$carriersDefinitions['CHRONO13']['product_code'];
        // Service code 0 by default
        $result['service'] = '0';

        switch ($carrier->id_reference) {
            case Configuration::get('CHRONOPOST_CHRONORELAIS_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13
                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONORELAIS']['product_code'];
                $result['recipientRef'] = Chronopost::getSelectedPickupPoint($order->id_cart);
                break;

            case Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONORELAIS_AMBIENT']['product_code'];
                $result['recipientRef'] = Chronopost::getSelectedPickupPoint($order->id_cart);
                break;

            case Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $result['productCode'] = Chronopost::$carriersDefinitions['TOSHOPDIRECT']['product_code'];
                $result['recipientRef'] = Chronopost::getSelectedPickupPoint($order->id_cart);
                $result['service'] = '6';
                $shipSaturday = true;
                break;

            case Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $result['productCode'] = Chronopost::$carriersDefinitions['TOSHOPDIRECT_EUROPE']['product_code'];
                $result['recipientRef'] = Chronopost::getSelectedPickupPoint($order->id_cart);

                // service is dependant on weight
                $result['service'] = 337;
                if ($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 3) {
                    $result['service'] = 338;
                }
                
                $shipSaturday = true;
                break;
            
            case Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID'):
                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONOEXPRESS']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_CHRONO13_ID'):
                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONO13']['product_code'];

                if (Configuration::get('CHRONOPOST_BAL_ENABLED') == 1 && !$is_return) {
                    $result['productCode'] = '58';
                } // CHRONO 13 + BAL
                break;

            case Configuration::get('CHRONOPOST_CHRONO13_INSTANCE_ID'):
                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONO13_INSTANCE']['product_code'];

                if (Configuration::get('CHRONOPOST_BAL_ENABLED') == 1 && !$is_return) {
                    $result['productCode'] = '58';
                } // CHRONO 13 + BAL
                break;

            case Configuration::get('CHRONOPOST_CHRONO18_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONO18']['product_code'];
                if (Configuration::get('CHRONOPOST_BAL_ENABLED') == 1) {
                    $result['productCode'] = '2M';
                } // CHRONO 18 + BAL
                break;

            case Configuration::get('CHRONOPOST_CHRONO10_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONO10']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_CHRONOCLASSIC_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONOCLASSIC']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_SAMEDAY_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $result['productCode'] = Chronopost::$carriersDefinitions['SAMEDAY']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_DIMANCHEBAL_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $result['service'] = '514';
                $result['as'] = 'B34';
                $result['productCode'] = Chronopost::$carriersDefinitions['DIMANCHEBAL']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_CHRONORDV_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $res = DB::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'chrono_cart_creneau WHERE id_cart=' . (int)$order->id_cart);

                $timeSlot = new DateTime($res[0]['delivery_date']);
                $timeSlot->modify('+2 hours');

                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONORDV']['product_code'];
                $result['service'] = $res[0]['service_code'];
                $result['as'] = $res[0]['as_code'];
                $result['timeSlot'] = true;
                $result['timeSlotStartDate'] = $res[0]['delivery_date'];
                $result['timeSlotEndDate'] = $res[0]['delivery_date_end'];
                $result['timeSlotTariffLevel'] = $res[0]['tariff_level'];
                break;

            case Configuration::get('CHRONOPOST_RELAISEUROPE_ID'):
                if ($is_return) {
                    // returns are a specific product !
                    $result['productCode'] = '3T';
                    break;
                }

                $result['productCode'] = Chronopost::$carriersDefinitions['RELAISEUROPE']['product_code'];
                $result['recipientRef'] = Chronopost::getSelectedPickupPoint($order->id_cart);

                // service is dependant on weight
                $result['service'] = 337;
                if ($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 3) {
                    $result['service'] = 338;
                }
                break;

            case Configuration::get('CHRONOPOST_RELAISDOM_ID'):
                if ($is_return) {
                    // returns are Chrono Express
                    $result['productCode'] = Chronopost::$carriersDefinitions['CHRONOEXPRESS']['product_code'];
                    break;
                }

                $result['productCode'] = Chronopost::$carriersDefinitions['RELAISDOM']['product_code'];
                $result['recipientRef'] = Chronopost::getSelectedPickupPoint($order->id_cart);
                $result['service'] = 368;

                break;

            case Configuration::get('CHRONOPOST_CHRONOFRESH_CLASSIC_ID'):
                if ($is_return) {
                    break;
                }

                $result['productCode'] = Chronopost::$carriersDefinitions['CHRONOFRESH_CLASSIC']['product_code'];
                break;
        }

        // Service code for Saturday deliveries
        $chronoCarriers = array(
            Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ID'),
            Configuration::get('CHRONOPOST_CHRONORELAIS_ID'),
            Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID'),
            Configuration::get('CHRONOPOST_CHRONO13_ID'),
            Configuration::get('CHRONOPOST_CHRONO13_INSTANCE_ID'),
            Configuration::get('CHRONOPOST_CHRONO10_ID'),
            Configuration::get('CHRONOPOST_CHRONO18_ID'),
            Configuration::get('CHRONOPOST_RELAISDOM_ID')
        );

        // International carriers never do deliveries on saturday
        if (in_array($carrier->id_reference, $chronoCarriers)) {
            if (Tools::getIsset('shipSaturday')) {
                $result['service'] = '6';
                if ($carrier->id_reference == Configuration::get('CHRONOPOST_RELAISDOM_ID')) {
                    $result['service'] = 369;
                }
            }

            if (Tools::getIsset('orders') && $shipSaturday) {
                $result['service'] = '6';
                if ($carrier->id_reference == Configuration::get('CHRONOPOST_RELAISDOM_ID')) {
                    $result['service'] = 369;
                }
            }

            if (Tools::getIsset('orderid') && Tools::getValue('shipSaturday') == 'yes'
                && Chronopost::isSaturdayOptionApplicable()) {
                $result['service'] = '6';
                if ($carrier->id_reference == Configuration::get('CHRONOPOST_RELAISDOM_ID')) {
                    $result['service'] = 369;
                }
            }

            if (Chronopost::gettingReadyForSaturday($carrier) && $result['service'] != '6' && $result['service'] != 369
                && $result['service'] != 368) {
                $result['service'] = '1';
            }
        }

        if ($is_return) {
            $returnProductCode = Tools::getValue('return_method');
            $shippingAddress = new Address($order->id_address_delivery);
            /* @todo réactiver ce code quand les WS auront été mis à jour
             * $result['service'] = $wsHelper->getReturnServiceCode($result['productCode']);
             */
            $result['productCode'] = $wsHelper->getReturnProductCode($shippingAddress);
            if ($result['service'] == 6 || $result['service'] == 368 || $result['service'] == 369) {
                $result['service'] = 1;
            } else {
                $result['service'] = 0;
            }
            if ($returnProductCode) {
                $result['productCode'] = $returnProductCode;
                $codeServiceForMethod = $wsHelper->getReturnServiceCode($result['productCode']);
                if ($codeServiceForMethod) {
                    $result['service'] = $codeServiceForMethod;
                }
            }
        }

        return $result;
    }

    public static function buildControllerWhereQuery($controller = null)
    {
        $query = '';

        $codes = array_keys(self::$carriersDefinitions);
        if ($controller) {
            if ($controller instanceof AdminExportChronofreshController) {
                $codes = ['CHRONOFRESH', 'CHRONOFRESH_CLASSIC', 'CHRONORELAIS_AMBIENT'];
                if (Chronofresh::isFreshAccount()) {
                    $codes = array_merge($codes, ['CHRONOCLASSIC', 'CHRONOEXPRESS']);
                }
            } else {
                unset($codes[array_search('CHRONOFRESH', $codes, true)]);
                unset($codes[array_search('CHRONOFRESH_CLASSIC', $codes, true)]);
                unset($codes[array_search('CHRONORELAIS_AMBIENT', $codes, true)]);

                if (Chronofresh::isFreshAccount()) {
                    unset($codes[array_search('CHRONOCLASSIC', $codes, true)]);
                    unset($codes[array_search('CHRONOEXPRESS', $codes, true)]);
                }
            }
        }

        foreach ($codes as $code) {
            if ($query === '') {
                $query = 'AND (ca.id_reference=' . ((int)Configuration::get('CHRONOPOST_' . $code . '_ID'));
            } else {
                $query .= ' OR ca.id_reference=' . ((int)Configuration::get('CHRONOPOST_' . $code . '_ID'));
            }
        }

        return $query . ') ';
    }

    /**
     * @param $orderId
     *
     * @return float|int
     */
    public static function amountToInsure($orderId)
    {
        if (Configuration::get('CHRONOPOST_ADVALOREM_ENABLED') == 0) {
            return -1;
        }

        $order = new Order($orderId);
        $carrier = new Carrier($order->id_carrier);

        $disabledOffers = [
            Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID'),
            Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID')
        ];
        if (in_array($carrier->id_reference, $disabledOffers)) {
            return -1;
        }

        $minAmountToTrigger = (float)Configuration::get('CHRONOPOST_ADVALOREM_MINVALUE');
        $amountToInsure = 0;
        $cartValue = (float)$order->total_products_wt;
        if ($cartValue >= $minAmountToTrigger) {
            $amountToInsure = $cartValue;
        }

        if ($recapLT = DB::getInstance()->getRow('SELECT insurance FROM ' . _DB_PREFIX_ . 'chrono_lt_history WHERE
        id_order=' . $orderId)) {
            if ($recapLT["insurance"] >= (float)Configuration::get('CHRONOPOST_ADVALOREM_MINVALUE')) {
                $amountToInsure = (float)Configuration::get('CHRONOPOST_ADVALOREM_MINVALUE');
            } else {
                $amountToInsure = $recapLT["insurance"];
            }
        }

        return $amountToInsure;
    }

    public function hookActionCarrierUpdate($params)
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        // Ensures Chrono18 && Chrono13 not selected at the same time
        $c18 = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONO18_ID'));
        $c13 = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONO13_ID'));
        $c13Instance = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONO13_INSTANCE_ID'));

        if (($params['carrier']->id_reference == Configuration::get('CHRONOPOST_CHRONO13_ID')
                && (int)$params['carrier']->active == 1 && $c18->active == 1)
            || ($params['carrier']->id == Configuration::get('CHRONOPOST_CHRONO18_ID')
                && (int)$params['carrier']->active == 1 && ($c13->active == 1 || $c13Instance->active == 1))
            || ($params['carrier']->id_reference == Configuration::get('CHRONOPOST_CHRONO13_INSTANCE_ID')
                && (int)$params['carrier']->active == 1 && $c18->active == 1)) {
            $params['carrier']->active = 0;
            $params['carrier']->save();

            echo '<script>alert("' . $this->l('You can\'t activate simultaneously Chronopost before 13h and before 18h.'
                ) . '");
                history.back();
            </script>';
            exit();
        }
    }

    public function getAddressPointRelais($cart, $id_carrier)
    {
        $cartId = $cart->id;
        $relaisId = self::getSelectedPickupPoint($cartId);

        $carrier = new Carrier($id_carrier);
        $code = self::getCodeFromCarrier($carrier->id_reference);
        $account = self::getAccountInformationByAccountNumber(Configuration::get('CHRONOPOST_' . $code . '_ACCOUNT'));
        $current_address = new Address($cart->id_address_delivery);

        include_once _PS_MODULE_DIR_ . '/chronopost/libraries/PointRelaisServiceWSService.php';

        // Getting relais details
        // We have to use PointRelaisService so we are in Chronopost's most up-to-date environnement
        $ws = new PointRelaisServiceWSService();
        $paramsw = new rechercheDetailPointChronopost();
        $paramsw->accountNumber = $account['account'];
        $paramsw->password = $account['password'];
        $paramsw->identifiant = $relaisId;

        $bt = $ws->rechercheDetailPointChronopost($paramsw)->return->listePointRelais;

        // Populate Address object
        $a = new Address();
        $a->alias = 'Point ChronoRelais ' . $bt->identifiant;
        $a->id_customer = $cart->id_customer;
        $a->id_country = Country::getByIso($bt->codePays);
        $a->company = Tools::substr($bt->nom, 0, 32);
        $a->lastname = $current_address->lastname;
        $a->firstname = $current_address->firstname;
        $a->address1 = $bt->adresse1;
        $a->address2 = isset($bt->adresse2) ? $bt->adresse2 : '';
        $a->postcode = $bt->codePostal;
        $a->city = $bt->localite;
        $a->phone = $current_address->phone;
        $a->phone_mobile = $current_address->phone_mobile;
        $a->other = $bt->identifiant; // ID Point Relais
        $a->active = 0;
        $a->deleted = 1;
        $a->id_customer = null;
        $a->id_manufacturer = null;

        return $a;
    }

    public function hookActionValidateOrder($params)
    {
        $carrier = new Carrier($params['order']->id_carrier);
        $code = self::getCodeFromCarrier($carrier->id_reference);
        $account = self::getAccountInformationByAccountNumber(Configuration::get('CHRONOPOST_' . $code . '_ACCOUNT'));
        if ($account === false) {
            return false;
        }

        if (Chronopost::isRelais($params['order']->id_carrier)) {
            $relais = Db::getInstance()->getValue('SELECT id_pr FROM `' . _DB_PREFIX_ . 'chrono_cart_relais` WHERE id_cart = ' . (int)$params['cart']->id);
            if (!$relais) {
                return false;
            }

            include_once _PS_MODULE_DIR_ . '/chronopost/libraries/PointRelaisServiceWSService.php';

            // Data
            $cart = $params['cart'];
            if (!Validate::isLoadedObject($cart)) {
                return false;
            }

            $current_address = new Address($cart->id_address_delivery);

            // Getting relais details
            // We have to use PointRelaisService so we are in Chronopost's most up-to-date environnement
            $ws = new PointRelaisServiceWSService();
            $paramsw = new rechercheDetailPointChronopost();
            $paramsw->accountNumber = $account['account'];
            $paramsw->password = $account['password'];
            $paramsw->identifiant = $relais;
            $bt = $ws->rechercheDetailPointChronopost($paramsw)->return->listePointRelais;

            // Populate Address object
            $a = new Address();
            $a->alias = 'Point ChronoRelais ' . $bt->identifiant;
            $a->id_customer = $cart->id_customer;
            $a->id_country = Country::getByIso($bt->codePays);
            $a->company = Tools::substr($bt->nom, 0, 32);
            $a->lastname = $current_address->lastname;
            $a->firstname = $current_address->firstname;
            $a->address1 = $bt->adresse1;
            $a->address2 = isset($bt->adresse2) ? $bt->adresse2 : '';
            $a->postcode = $bt->codePostal;
            $a->city = $bt->localite;
            $a->phone = $current_address->phone;
            $a->phone_mobile = $current_address->phone_mobile;
            $a->other = $bt->identifiant; // ID Point Relais
            $a->active = 0;
            $a->deleted = 1;
            $a->id_customer = null;
            $a->id_manufacturer = null;

            // Save && assign to cart
            $a->save();
            $params['order']->id_address_delivery = $a->id;
            $params['order']->save();

            return false;
        }

        if (Chronopost::isRDV($params['order']->id_carrier)) {
            include_once(__DIR__ . '/libraries/CreneauWS.php');

            // Data
            $cart = $params['cart'];
            if (!Validate::isLoadedObject($cart)) {
                return false;
            }

            $current_address = new Address($cart->id_address_delivery);

            $header = [];
            $header[] = new SoapHeader(
                'http://cxf.soap.ws.creneau.chronopost.fr/',
                'accountNumber',
                $account['account'],
                false
            );

            $header[] = new SoapHeader(
                'http://cxf.soap.ws.creneau.chronopost.fr/',
                'password',
                $account['password'],
                false
            );

            $ws = new CreneauWS();
            $ws->__setSoapHeaders($header);

            $query = new confirmDeliverySlotV2();
            $query->callerTool = 'RDVPRE';
            $query->productType = 'RDV'; // normal product
            $query->meshCode = $current_address->postcode;
            $res = DB::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'chrono_cart_creneau WHERE id_cart=' . (int)$params['cart']->id);

            $query->rank = $res[0]['rank'];
            $query->dateSelected = $res[0]['delivery_date'];
            $query->transactionID = $res[0]['transaction_id'];
            $query->codeSlot = $res[0]['slot_code'];

            $res = $ws->confirmDeliverySlotV2($query);

            if ($res->return->code != 0) {
                return false;
            }

            DB::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'chrono_cart_creneau SET
                product_code="' . pSQL((string)$res->return->productServiceV2->productCode) . '",
                service_code="' . pSQL((string)$res->return->productServiceV2->serviceCode) . '",
                as_code="' . pSQL((string)$res->return->productServiceV2->asCode) . '"
                WHERE id_cart=' . (int)$params['cart']->id);
        }
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $file = Tools::getValue('controller');
        if (!in_array($file, array('AdminOrders'))) {
            return false;
        }

        if (version_compare(_PS_VERSION_, 1.6) < 0) {
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/backoffice15.css', 'all');
        }

        $this->context->controller->addJquery();
        $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/chronoadmin.js', 'all');

        return '<script>
            $(document).ready(function() {
                $.get("' . _MODULE_DIR_ . 'chronopost/async/updateTracking.php");
            });
        </script>';
    }

    public function hookDisplayHeader($params)
    {
        $file = Tools::getValue('controller');
        $moduleUri = _MODULE_DIR_ . $this->name;

        if ($file === 'orderdetail') {
            $this->context->controller->addJS($moduleUri . '/views/js/orderHistory.js');

            return false;
        } elseif (!in_array($file, array('order-opc', 'order', 'orderopc'))) {
            return false;
        }

        $this->context->controller->addCSS($moduleUri . '/views/css/chronorelais.css');
        $this->context->controller->addCSS($moduleUri . '/views/css/chronordv.css');
        $this->context->controller->addCSS($moduleUri . '/views/css/leaflet/leaflet.css');
        $this->context->controller->addJS($moduleUri . '/views/js/leaflet.js');

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $this->context->controller->addJS($moduleUri . '/views/js/chronorelais.js');
            $this->context->controller->addJS($moduleUri . '/views/js/chronordv.js');
            if (self::gettingReadyForSaturday()) {
                $this->context->controller->addJS($moduleUri . '/views/js/chronosaturday-16.js');
            }
        } else {
            $this->context->controller->addJS($moduleUri . '/views/js/chronorelais-17.js');
            $this->context->controller->addJS($moduleUri . '/views/js/chronordv-17.js');
            if (self::gettingReadyForSaturday()) {
                $this->context->controller->addJS($moduleUri . '/views/js/chronosaturday.js');
            }
        }
    }

    protected function shouldDisplayChrono()
    {
        $chronorelaisCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORELAIS_ID'));
        $chronorelaisAmbientCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ID'));
        $relaiseuropeCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_RELAISEUROPE_ID'));
        $relaisdomCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_RELAISDOM_ID'));
        $toShopCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID'));
        $toShopEuropeCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID'));
        $rdv_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORDV_ID'));

        $chronorelaisId = $chronorelaisCarrier ? $chronorelaisCarrier->id : -1;
        $chronorelaisAmbientId = $chronorelaisAmbientCarrier ? $chronorelaisAmbientCarrier->id : -1;
        $relaiseuropeId = $relaiseuropeCarrier ? $relaiseuropeCarrier->id : -1;
        $relaisdomId = $relaisdomCarrier ? $relaisdomCarrier->id : -1;
        $toShopCarrier_id = $toShopCarrier ? $toShopCarrier->id : -1;
        $rdv_id = $rdv_carrier ? $rdv_carrier->id : -1;

        if ($chronorelaisAmbientId === -1 && $rdv_id === -1 && $chronorelaisId === -1 && $relaiseuropeId === -1 &&
            $relaisdomId === -1 && $toShopCarrier_id === -1) {
            return false;
        }

        return true;
    }

    /**
     * Assign saturday carrier ids to smarty
     *
     * @param $params
     */
    protected function assignChronoSmartyVars($params)
    {
        $address = new Address($params['cart']->id_address_delivery);

        $chronorelaisCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORELAIS_ID'));
        $chronorelaisId = $chronorelaisCarrier ? $chronorelaisCarrier->id : -1;
        $chronorelaisAmbientCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ID'));
        $chronorelaisAmbientId = $chronorelaisAmbientCarrier ? $chronorelaisAmbientCarrier->id : -1;
        $relaiseuropeCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_RELAISEUROPE_ID'));
        $relaiseuropeId = $relaiseuropeCarrier ? $relaiseuropeCarrier->id : -1;
        $relaisdomCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_RELAISDOM_ID'));
        $relaisdomId = $relaisdomCarrier ? $relaisdomCarrier->id : -1;
        $toShopCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID'));
        $toShopId = $toShopCarrier ? $toShopCarrier->id : -1;
        $toShopEuropeCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID'));
        $toShopEuropeId = $toShopEuropeCarrier ? $toShopEuropeCarrier->id : -1;
        $rdvCarrier = Carrier::getCarrierByReference(Configuration::get('CHRONORDV_ID'));
        $rdvId = $rdvCarrier ? $rdvCarrier->id : -1;

        $customer_address = sprintf(
            '%s %s %s %s',
            $address->address1,
            $address->address2,
            $address->postcode,
            $address->city
        );

        $this->context->smarty->assign(array(
            'module_uri'                  => __PS_BASE_URI__ . 'modules/' . $this->name,
            'cartID'                      => $params['cart']->id,
            'cust_codePostal'             => $address->postcode,
            'cust_firstname'              => $address->firstname,
            'cust_lastname'               => $address->lastname,
            'cust_address'                => $customer_address,
            'cust_address_clean'          => sprintf('%s %s ', $address->address1, $address->address2),
            'cust_city'                   => $address->city,
            'cust_country'                => Country::getIsoById($address->id_country),
            'CHRONORELAIS_ID'             => $chronorelaisId,
            'CHRONORELAIS_ID_INT'         => (string)Cart::intifier($chronorelaisId),
            'CHRONORELAIS_AMBIENT_ID'     => $chronorelaisAmbientId,
            'CHRONORELAIS_AMBIENT_ID_INT' => (string)Cart::intifier($chronorelaisAmbientId),
            'RELAISEUROPE_ID'             => $relaiseuropeId,
            'RELAISEUROPE_ID_INT'         => (string)Cart::intifier($relaiseuropeId),
            'RELAISDOM_ID'                => $relaisdomId,
            'RELAISDOM_ID_INT'            => (string)Cart::intifier($relaisdomId),
            'TOSHOPDIRECT_ID'             => $toShopId,
            'TOSHOPDIRECT_ID_INT'         => (string)Cart::intifier($toShopId),
            'TOSHOPDIRECT_EUROPE_ID'      => $toShopEuropeId,
            'TOSHOPDIRECT_EUROPE_ID_INT'  => (string)Cart::intifier($toShopEuropeId),
            'RDV_ID'                      => $rdvId,
            'RDV_ID_INT'                  => (string)Cart::intifier($rdvId),
        ));
    }

    /**
     * Assign saturday carrier ids to smarty
     *
     * @param $params
     */
    protected function assignChronoSaturdaySmartyVars($params)
    {
        $saturdayIds = $this->getSaturdayCarrierIds();
        $cartId = Context::getContext()->cart->id;
        $cartSupplementEnabled = (bool)$this->getSaturdaySupplement($cartId);

        $isSaturdayCarrier = false;
        if (isset($params['cart'], $params['cart']->id_carrier) &&
            in_array($params['cart']->id_carrier, $saturdayIds)) {
            $isSaturdayCarrier = true;
        }

        $this->context->smarty->assign(array(
            'is_saturday_carrier'         => $isSaturdayCarrier,
            'saturday_ids'                => $saturdayIds,
            'saturday_supplement'         => Configuration::get('CHRONOPOST_SATURDAY_SUPPLEMENT'),
            'saturday_supplement_enabled' => Configuration::get('CHRONOPOST_SATURDAY_CUSTOMER') === 'yes',
            'cart_supplement_enabled'     => $cartSupplementEnabled
        ));
    }

    protected function assignChronorelaisSmartyVars()
    {
        $isFreshAccount = Chronofresh::isFreshAccount();

        $this->context->smarty->assign(
            [
                'map_enabled'      => Configuration::get('CHRONOPOST_MAP_ENABLED'),
                'is_fresh_account' => $isFreshAccount
            ]
        );
    }

    protected function assignChronoRdvSmartyVars($params)
    {
        $address = new Address($params['cart']->id_address_delivery);
        $country = new Country($address->id_country);
        $rdv_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORDV_ID'));
        $rdv_id = $rdv_carrier ? $rdv_carrier->id : -1;

        if ($rdv_id == -1 || ($country->iso_code !== 'FR' && $country->iso_code !== 'FX')) {
            throw new Exception("Chrono Precise not available");
        }

        include_once(__DIR__ . '/libraries/CreneauWS.php');
        $query = new searchDeliverySlot();
        $query->callerTool = 'RDVPRE';
        $query->productType = 'RDV'; // normal product
        $query->recipientZipCode = $address->postcode;
        $query->shipperAdress1 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS');
        $query->shipperAdress2 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS2');
        $query->shipperCity = Configuration::get('CHRONOPOST_SHIPPER_CITY');
        $query->shipperCountry = Configuration::get('CHRONOPOST_SHIPPER_COUNTRY');
        $query->shipperName = Configuration::get('CHRONOPOST_SHIPPER_NAME');
        $query->shipperName2 = Configuration::get('CHRONOPOST_SHIPPER_NAME2');
        $query->shipperZipCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');
        $query->recipientAdress1 = $address->address1;
        $query->recipientAdress2 = $address->address2;
        $query->recipientCity = $address->city;
        $query->recipientCountry = 'FR';
        $query->recipientZipCode = $address->postcode;
        $query->weight = 1;

        // Calculate earliest possible shipping date
        $date = $this->getNextDay(
            Configuration::get('CHRONOPOST_RDV_DAY_ON')
        );

        if ($date == null) {
            $date = new DateTime();
            $date->modify('+ ' . (int)Configuration::get('CHRONOPOST_RDV_DELAY') . ' days');
        }

        $query->dateBegin = $date->format('Y-m-d\TH:i:s');
        $date->modify('+ 7 days');
        $query->dateEnd = $date->format('Y-m-d\TH:i:s');

        // Calculate next closing period
        $closeStart = $this->getNextDay(
            Configuration::get('CHRONOPOST_RDV_DAY_CLOSE_ST')
        );

        $closeEnd = $this->getNextDay(
            Configuration::get('CHRONOPOST_RDV_DAY_CLOSE_END')
        );

        if ($closeStart != null && $closeEnd != null && $closeStart != $closeEnd) {
            $query->customerDeliverySlotClosed = $closeStart->format('Y-m-d\TH:i:s\Z')
                . '/' . $closeEnd->format('Y-m-d\TH:i:s\Z');
        }

        $account = self::getAccountInformationByAccountNumber(Configuration::get('CHRONOPOST_CHRONORDV_ACCOUNT'));
        $ws = new CreneauWS();

        $header = [];
        $header[] = new SoapHeader(
            'http://cxf.soap.ws.creneau.chronopost.fr/',
            'accountNumber',
            $account['account'],
            false
        );

        $header[] = new SoapHeader(
            'http://cxf.soap.ws.creneau.chronopost.fr/',
            'password',
            $account['password'],
            false
        );

        $ws->__setSoapHeaders($header);

        $res = $ws->searchDeliverySlot($query);
        if (!$res->return->slotList) {
            throw new Exception("Can't find any available slot");
        }

        // group by hour then days
        $orderedSlots = [];
        $days = [];
        foreach ($res->return->slotList as $slot) {
            if ($slot->startHour < 10) {
                $slot->startHour = '0' . $slot->startHour;
            }

            $hour_idx = $slot->startHour . 'H - ' . $slot->endHour . 'H';
            $when = new DateTime($slot->deliveryDate);
            $day_idx = $when->format("d/m/Y");

            if (!array_key_exists($hour_idx, $orderedSlots)) {
                $orderedSlots[$hour_idx] = [];
            }

            if (!in_array($day_idx, $days)) {
                $days[] = $day_idx;
            }

            $deliveryDateTime = new DateTime($slot->deliveryDate);
            $deliveryDateTime->setTime($slot->startHour, $slot->startMinutes);

            $deliveryDateTimeEnd = clone $deliveryDateTime;
            $deliveryDateTimeEnd->setTime($slot->endHour, $slot->endMinutes);

            $tariffLevel = Tools::substr($slot->tariffLevel, 1);
            $price = Chronopost::getRDVCost($params['cart']->id, $tariffLevel);
            $slot->tariffLevel = $tariffLevel;
            $slot->price = Tools::displayPrice($price);
            $slot->fee = $price;
            $slot->deliveryDateTime = date_format($deliveryDateTime, 'Y-m-d\TH:i:s');
            $slot->deliveryDateTimeEnd = date_format($deliveryDateTimeEnd, 'Y-m-d\TH:i:s');
            $slot->enable = Configuration::get('CHRONOPOST_RDV_STATE' . $tariffLevel);

            // For sundays, we let the WS drive the enabled status
            if ($slot->dayOfWeek == 7) {
                $slot->enable = (bool)($slot->status == 'O');
            }

            $orderedSlots[$hour_idx][$day_idx] = $slot;
        }

        ksort($orderedSlots);

        $this->context->smarty->assign(
            array(
                'rdv_ordered_slots' => $orderedSlots,
                'rdv_days'          => $days,
                'rdv_carrierID'     => $rdv_id,
                'rdv_carrierIntID'  => (string)Cart::intifier($rdv_id),
                'rdv_transactionID' => (string)$res->return->transactionID
            )
        );
    }

    /**
     * @param $params
     *
     * @return string
     * @deprecated since 1.7.0.0.
     */
    public function hookExtraCarrier($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return '';
        }

        return $this->hookDisplayAfterCarrier($params);
    }

    public function hookDisplayAfterCarrier($params)
    {
        $r = '';
        $this->assignChronoSmartyVars($params);
        $r .= $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/chronocommon.tpl');

        if (self::gettingReadyForSaturday()) {
            $this->assignChronoSaturdaySmartyVars($params);
            $r .= $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/chronosaturday.tpl');
        }

        if (!$this->shouldDisplayChrono()) {
            return $r;
        }

        $this->assignChronorelaisSmartyVars();
        $r .= $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/chronorelais.tpl');

        try {
            $this->assignChronoRdvSmartyVars($params);
        } catch (Exception $e) {
            return $r;
        }

        return $r . $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/chronordv.tpl');
    }

    private function getNextDay($day)
    {
        $date = new DateTime();
        if ($day == -1) {
            return null;
        }

        switch ($day) {
            case 0:
                $date->modify('next Sunday');
                break;
            case 1:
                $date->modify('next Monday');
                break;
            case 2:
                $date->modify('next Tuesday');
                break;
            case 3:
                $date->modify('next Wednesday');
                break;
            case 4:
                $date->modify('next Thursday');
                break;
            case 5:
                $date->modify('next Friday');
                break;
            case 6:
                $date->modify('next Saturday');
                break;
        }

        $date->modify('+ ' . Configuration::get('CHRONOPOST_RDV_HOUR_ON') . ' hours ' .
            Configuration::get('CHRONOPOST_RDV_MINUTE_ON') . ' minutes');

        return $date;
    }

    public static function getPointRelaisAddress($orderId)
    {
        $order = new Order($orderId);
        include_once __DIR__ . '/libraries/PointRelaisServiceWSService.php';

        if ($order->id_carrier != Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ID') ||
            $order->id_carrier != Configuration::get('CHRONOPOST_CHRONORELAIS_ID') ||
            $order->id_carrier != Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID') ||
            $order->id_carrier != Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID')
        ) {
            return null;
        }

        $btid = Db::getInstance()->getRow('SELECT id_pr FROM `' . _DB_PREFIX_ . 'chrono_cart_relais` WHERE id_cart = ' . (int)$order->id_cart);
        $btid = $btid['id_pr'];

        // Fetch BT object
        $p = new rechercheBtAvecPFParIdChronopostA2Pas();
        $p->id = $btid;
        $ws = new PointRelaisServiceWSService();

        return $ws->rechercheBtAvecPFParIdChronopostA2Pas($p)->return;
    }

    public static function minNumberOfPackages($orderId)
    {
        $nblt = 1;

        $order = new Order($orderId);
        $carrier = new Carrier($order->id_carrier);
        if ($carrier->id_reference == Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID') 
            || $carrier->id_reference == Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID')) {
            return $nblt;
        }

        if ($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 20
            && Chronopost::isRelais($order->id_carrier)) {
            $nblt = ceil($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') / 20);
        }

        if ($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 30) {
            $nblt = ceil($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') / 30);
        }

        return $nblt;
    }

    public static function isChrono($idCarrier)
    {
        $carrier = new Carrier($idCarrier);
        return in_array((int) $carrier->id_reference, self::getChronoIDs(), true);
    }
    
    public static function getChronoIDs() {
        return array_merge([
            (int) Configuration::get('CHRONOPOST_CHRONO13_ID'),
            (int) Configuration::get('CHRONOPOST_CHRONO13_INSTANCE_ID'),
            (int) Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID'),
            (int) Configuration::get('CHRONOPOST_CHRONO10_ID'),
            (int) Configuration::get('CHRONOPOST_CHRONO18_ID'),
            (int) Configuration::get('CHRONOPOST_CHRONOCLASSIC_ID'),
            (int) Configuration::get('CHRONOPOST_CHRONORDV_ID'),
            (int) Configuration::get('CHRONOPOST_SAMEDAY_ID'),
            (int) Configuration::get('CHRONOPOST_DIMANCHEBAL_ID'),
            (int) Configuration::get('CHRONOPOST_CHRONOFRESH_ID'),
            (int) Configuration::get('CHRONOPOST_CHRONOFRESH_CLASSIC_ID'),
        ], self::getChronoRelaisIDs());
    }
    
    public static function getChronoRelaisIDs()
    {
        return array_merge([
            (int) Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ID'),
            (int) Configuration::get('CHRONOPOST_CHRONORELAIS_ID'),
            (int) Configuration::get('CHRONOPOST_RELAISEUROPE_ID'),
            (int) Configuration::get('CHRONOPOST_RELAISDOM_ID'),
        ], self::getToShopIDs());
    }
    
    public static function getToShopIDs()
    {
        return [
            (int) Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID'),
            (int) Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID'),
        ];
    }

    public static function isRelais($idCarrier)
    {
        if (!self::isChrono($idCarrier)) {
            return false;
        }

        $carrier = new Carrier($idCarrier);

        return in_array($carrier->id_reference, self::getChronoRelaisIDs());
    }

    public static function isRDV($idCarrier)
    {
        if (!self::isChrono($idCarrier)) {
            return false;
        }

        $carrier = new Carrier($idCarrier);

        return $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONORDV_ID');
    }

    public static function getRDVCost($id_cart, $tariffLevel = null, $shippingCost = 0)
    {
        $cart = new Cart($id_cart);
        $subTariff = 1;

        if (self::isRDV($cart->id_carrier)) {
            // then carrier is already selected
            // return cost for selected slot
            $res = DB::getInstance()->executeS('SELECT fee, tariff_level FROM ' . _DB_PREFIX_ . 'chrono_cart_creneau WHERE id_cart=' . (int)$id_cart);
            if ($tariffLevel === null && $res && $res[0]['fee'] > 0) {
                return $res[0]['fee'];
            }

            if ($res) {
                $subTariff = (int)$res[0]['tariff_level'];
            }
        }

        // other price display
        if ($tariffLevel === null) {
            $tariffLevel = 1;
        }

        if ($shippingCost === 0) {
            $rdv_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORDV_ID'));
            $rdv_id = $rdv_carrier ? $rdv_carrier->id : -1;
            $shippingCost = $cart->getOrderShippingCost($rdv_id) - Configuration::get('CHRONOPOST_RDV_PRICE' . $subTariff);
        }

        if (!is_numeric($shippingCost)) {
            $shippingCost = 0;
        }

        return ((float)Configuration::get('CHRONOPOST_RDV_PRICE' . $tariffLevel)) + $shippingCost;
    }

    public static function isReturnAvailable($order)
    {
        $address = new Address($order->id_address_delivery);
        $country = new Country($address->id_country);

        // Load whitelist
        $whitelistFile = fopen(_PS_MODULE_DIR_ . 'chronopost/csv/chronoretour.csv', 'r');
        $whitelist = fgetcsv($whitelistFile);

        return in_array($country->iso_code, $whitelist);
    }

    public static function checkReturnEurope($code, $address)
    {
        if ($code !== 'RELAISEUROPE') {
            return true;
        }

        $wsHelper = self::getWsHelper();
        $availReturn = $wsHelper->getReturnProductCode($address);

        return $availReturn === $wsHelper::CHRONOPOST_REVERSE_RELAIS_EUROPE;
    }

    public function hookDisplayAdminOrderMainBottom($params)
    {
        return $this->adminOrder($params);
    }

    public function hookDisplayAdminOrder($params)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            return false;
        }

        return $this->adminOrder($params);
    }

    public function hookDisplayPaymentTop()
    {
        if (!isset(Context::getContext()->cart) || Context::getContext()->controller->ajax === true) {
            return false;
        }

        $cart = Context::getContext()->cart;
        $idCarrier = $cart->id_carrier;
        if (!self::isRelais($idCarrier)) {
            return false;
        }
        
        if ($cart->isVirtualCart()) {
            return false;
        }

        $address = $this->getAddressPointRelais($cart, $idCarrier);
        $addressFormatted = AddressFormat::generateAddress($address, [], '<br>');

        $html = sprintf(
            '<h4 class="h5 black addresshead">%s</h4> %s',
            $this->l('Your shipping address'), $addressFormatted
        );

        echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
              let orderSummary = document.getElementById('order-summary-content');
              if (!orderSummary) {
                return;
              }
              let block = orderSummary.querySelector('.col-md-6').querySelector('.card-block');
              if (block) {
                  block.innerHTML = '" . $html . "'
              }
        });
        </script>";
    }

    public function adminOrder($params)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $order = new Order((int)$params['id_order']);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        if (!self::isChrono($order->id_carrier)) {
            return '';
        }

        $returnAvailable = self::isReturnAvailable($order);
        $carrier = new Carrier($order->id_carrier);
        $idOrderCarrier = self::getIdOrderCarrier($order);
        $orderCarrier = new OrderCarrier($idOrderCarrier);
        $ltHistory = [];
        $accountUsed = false;

        $LTHistory = self::getAllTrackingNumbers($params['id_order']);
        if (count($LTHistory) > 0) {
            foreach ($LTHistory as $lt) {
                $ltHistory[$lt['lt']]['link'] = str_replace('@', $lt['lt'], self::$trackingUrl);
                $ltHistory[$lt['lt']]['lt'] = $lt['lt'];
                $ltHistory[$lt['lt']]['lt_reference'] = $lt['lt_reference'];
                $ltHistory[$lt['lt']]['type'] = $lt['type'] == self::CHRONO_RETURN_TYPE ? '[RETOUR] ' : '[ENVOI] ';
            }

            $accountUsedResults = self::getAccountTrackingNumber($params['id_order'], $LTHistory[0]['lt']);
            $accountUsed = $accountUsedResults[0];
        }

        $cart = new Cart($order->id_cart);

        // Cleaning up after us
        $chronopostErrors = [];
        if (isset($_SESSION['chronopost_errors'])) {
            $chronopostErrors = $_SESSION['chronopost_errors'];
            unset($_SESSION['chronopost_errors']);
        }

        $wsHelper = self::getWsHelper();
        $availContracts = $wsHelper->getContractsForProduct(self::$carriersDefinitions[self::getCodeFromCarrier($carrier->id_reference)]);

        $saturdayIDs = $this->getSaturdayCarrierIds();
        if ((bool)$this->getSaturdaySupplement($order->id_cart) && in_array($carrier->id_reference, $saturdayIDs)) {
            $saturdayOk = true;
        } elseif (Configuration::get('CHRONOPOST_SATURDAY_CUSTOMER') === 'yes') {
            $saturdayOk = false;
        } else {
            $saturdayOk = self::isSaturdayOptionApplicable();
        }

        $nbwb = self::minNumberOfPackages($params['id_order']);

        $bal = Configuration::get('CHRONOPOST_BAL_ENABLED') == 1 && (
            $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONO13_ID') ||
            $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONO13_INSTANCE_ID') ||
            $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONO18_ID')
        ) ? 1 : 0;

        $saturday = self::gettingReadyForSaturday($carrier) ||
        ((bool)self::getSaturdaySupplement($order->id_cart) && self::gettingReadyForSaturday($carrier, true)) ? 1 : 0;

        $availChronoFreshProducts = [];
        $dlcDefault = '';
        if (Configuration::get('CHRONOPOST_GENERAL_ACCOUNTTYPE') === self::CHRONOFRESH_TYPE_ID) {
            $ws = new webservicesHelper();
            $availChronoFreshCodes = $ws->getChronofreshCodes();
            foreach ($availChronoFreshCodes as $code) {
                $key = array_search(
                    $code,
                    array_column(self::$carriersDefinitions['CHRONOFRESH']['products'], 'code')
                );

                if (is_int($key)) {
                    $availChronoFreshProducts[] = self::$carriersDefinitions['CHRONOFRESH']['products'][$key];
                }
            }

            $daysAfter = Configuration::get('CHRONOPOST_CHRONOFRESH_DLC') ?: 3;
            $now = new DateTime();
            $dlcDefault = $now->add(new DateInterval("P{$daysAfter}D"))->format("Y-m-d");
        }

        // can do multi parcels
        $multiParcelsAvailable = true;
        if ($carrier->id_reference === Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID') 
            || $carrier->id_reference === Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID')) {
            $multiParcelsAvailable = false;
            //$returnAvailable = false;
        }

        $isFreshAccount = Chronofresh::isFreshAccount();
        $isChronoFreshCarrier = Chronofresh::isChronoFreshCarrier($carrier);
        $isChronoFreshClassicCarrier = Chronofresh::isChronoFreshClassicCarrier($carrier);

        if ($isFreshAccount || $isChronoFreshCarrier || $isChronoFreshClassicCarrier) {
            $returnAvailable = false;
        }
        $wsHelper = new webservicesHelper();
        $contracts = json_decode(Configuration::get('CHRONOPOST_GENERAL_ACCOUNTS'), 1);
        
        $availableReturnMethodsForContracts = $availableReturnMethods = [];
        foreach ($contracts as $contract) {
            $availableReturnMethodsForContracts[$contract['account']] = $wsHelper->getAvailableReturnMethods($contract['account']);
        }

        $shownMethods = [];
        $orderAccount = Configuration::get('CHRONOPOST_' . self::getCodeFromCarrier($carrier->id_reference) . '_ACCOUNT');
        $defaultOrderMethod = '';
        foreach ($availableReturnMethodsForContracts as $contract => $methods) {
            foreach ($methods as $method) {
                if (!$defaultOrderMethod && ((string) $contract === (string) $orderAccount)) {
                    $defaultOrderMethod = $method;
                }
                if (!in_array($method, $shownMethods)) {
                    $availableReturnMethods[$method] = [
                        'contract' => $contract,
                        'code' => $method,
                        'label' => $wsHelper->getReturnServiceLabel($method),
                    ];
                }
                $shownMethods[] = $method;
            }
        }
        
        if ($defaultOrderMethod) {
            $availableReturnMethods[$defaultOrderMethod]['selected'] = 'selected="selected"';
        }
        
        $this->context->smarty->assign(
            array(
                'default_weight'              => round(($cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF')) / $nbwb,
                    2),
                'general_accounts'            => json_decode(Configuration::get('CHRONOPOST_GENERAL_ACCOUNTS'), 1),
                'available_accounts'          => $availContracts,
                'default_account'             => $orderAccount,
                'account_used'                => $accountUsed,
                'module_uri'                  => __PS_BASE_URI__ . 'modules/' . $this->name,
                'id_order'                    => $params['id_order'],
                'chronopost_secret'           => Configuration::get('CHRONOPOST_SECRET'),
                'bal'                         => $bal,
                'saturday'                    => $saturday,
                'saturday_ok'                 => $saturdayOk ? 1 : 0,
                'saturday_supplement_enabled' => Configuration::get('CHRONOPOST_SATURDAY_CUSTOMER') === 'yes',
                'to_insure'                   => self::amountToInsure($params['id_order']),
                'nbwb'                        => $nbwb,
                'return'                      => $returnAvailable ? 1 : 0,
                'return_default'              => Configuration::get('CHRONOPOST_RETURN_DEFAULT'),
                'lt'                          => $orderCarrier->tracking_number,
                'has_lt'                      => (count($LTHistory) > 0),
                'lt_history'                  => json_encode($LTHistory),
                'lt_history_link'             => json_encode($ltHistory),
                'chronopost_errors'           => json_encode($chronopostErrors),
                'is_chronofresh'              => $isChronoFreshCarrier || $isChronoFreshClassicCarrier,
                'use_chronofresh_products'    => $isChronoFreshCarrier,
                'chronofresh_products'        => $availChronoFreshProducts,
                'dlc_default'                 => $dlcDefault,
                'multiParcelsAvailable'       => $multiParcelsAvailable,
                'quickcost_product'           => Configuration::get('CHRONOPOST_QUICKCOST_PRODUCT'),
                'availableMethods'            => $availableReturnMethods,
            )
        );

        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            return $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/adminOrder-17.tpl');
        }

        if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            return $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/adminOrder-16.tpl');
        }

        return $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/adminOrder-15.tpl');
    }

    public static function calculateProducts($cart)
    {
        $a = new Address($cart->id_address_delivery);
        $c = new Country($a->id_country);

        $res = array(
            'chrono10'      => false,
            'chronoclassic' => false,
            'chrono18'      => false,
            'relaiseurope'  => false,
            'relaisdom'     => false,
            'rdv'           => false,
            'sameday'       => false,
            'dimanchebal'   => false,
            'toshop'        => false,
            'toshopeurope'  => false,
        );

        $cache = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'chrono_calculateproducts_cache2`
            WHERE postcode = "' . pSQL($a->postcode) . '" AND country = "' . pSQL(Chronopost::maybeCountryMapping($c->iso_code)) . '"');

        if (empty($cache) || $cache[0]['last_updated'] + 24 * 3600 < time()) {
            include_once(__DIR__ . '/libraries/QuickcostServiceWSService.php');

            $ws = new QuickcostServiceWSService();
            $cp = new calculateProducts();

            $accounts = json_decode(Configuration::get('CHRONOPOST_GENERAL_ACCOUNTS'), 1);

            // Check if the product is available with the chosen default account
            foreach ($accounts as $account) {
                $cp->accountNumber = $account['account'];
                $cp->password = $account['password'];
                $cp->depZipCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');
                $cp->depCountryCode = Chronopost::maybeCountryMapping(Configuration::get('CHRONOPOST_SHIPPER_COUNTRY'));
                $cp->weight = $cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') + 0.1;
                $cp->arrCountryCode = Chronopost::maybeCountryMapping($c->iso_code);
                $cp->arrZipCode = $a->postcode;
                $cp->type = 'M';

                try {
                    $cpres = $ws->calculateProducts($cp);
                    if (!isset($cpres->return->productList)) {
                        continue;
                    }
                } catch (Exception $e) {
                    return $res;
                }

                if (!is_array($cpres->return->productList)) {
                    $cpres->return->productList = array($cpres->return->productList);
                }

                foreach ($cpres->return->productList as $product) {
                    if ($product->productCode === '2' && Configuration::get('CHRONOPOST_CHRONO10_ACCOUNT') === $account['account']) {
                        $res['chrono10'] = true;
                    }

                    if ($product->productCode === '16' && Configuration::get('CHRONOPOST_CHRONO18_ACCOUNT') === $account['account']) {
                        $res['chrono18'] = true;
                    }

                    if ($product->productCode === '44' && Configuration::get('CHRONOPOST_CHRONOCLASSIC_ACCOUNT') === $account['account']) {
                        $res['chronoclassic'] = true;
                    }

                    if ($product->productCode === '4P' && Configuration::get('CHRONOPOST_RELAISDOM_ACCOUNT') === $account['account']) {
                        $res['relaisdom'] = true;
                    }

                    if ($product->productCode === '49' && Configuration::get('CHRONOPOST_RELAISEUROPE_ACCOUNT') === $account['account']) {
                        $res['relaiseurope'] = true;
                    }

                    if ($product->productCode === '2O' && Configuration::get('CHRONOPOST_CHRONORDV_ACCOUNT') === $account['account']) {
                        $res['rdv'] = true;
                    }

                    if ($product->productCode === '4I' && Configuration::get('CHRONOPOST_SAMEDAY_ACCOUNT') === $account['account']) {
                        $res['sameday'] = true;
                    }

                    if ($product->productCode === '5A') {
                        $res['dimanchebal'] = true;
                    }

                    if ($product->productCode === '5X' && Configuration::get('CHRONOPOST_TOSHOPDIRECT_ACCOUNT') === $account['account']) {
                        $res['toshop'] = true;
                    }

                    if ($product->productCode === '6B' && Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ACCOUNT') === $account['account']) {
                        $res['toshopeurope'] = true;
                    }
                }
            }

            // INSERT cache
            if (empty($cache)) {
                $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'chrono_calculateproducts_cache2`
                    (`postcode`,`country`, `chrono10`,`chrono18`, `chronoclassic`, `relaiseurope`, `relaisdom`, `rdv`,
                    `sameday`, `dimanchebal`, `toshop`, `toshopeurope`, `last_updated`) VALUES
                    ("' . pSQL($a->postcode) . '",
                    "' . pSQL(Chronopost::maybeCountryMapping($c->iso_code)) . '",
                    ' . ($res['chrono10'] == true ? 1 : 0) . ',
                    ' . ($res['chrono18'] == true ? 1 : 0) . ',
                    ' . ($res['chronoclassic'] == true ? 1 : 0) . ',
                    ' . ($res['relaiseurope'] == true ? 1 : 0) . ',
                    ' . ($res['relaisdom'] == true ? 1 : 0) . ',
                    ' . ($res['rdv'] == true ? 1 : 0) . ',
                    ' . ($res['sameday'] == true ? 1 : 0) . ',
                    ' . ($res['dimanchebal'] == true ? 1 : 0) . ',
                    ' . ($res['toshop'] == true ? 1 : 0) . ',
                    ' . ($res['toshopeurope'] == true ? 1 : 0) . ',
                    ' . time() . ')';
                Db::getInstance()->Execute($sql);
            } else { // UPDATE cache
                Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'chrono_calculateproducts_cache2`
                    SET `chrono10` = ' . ($res['chrono10'] == true ? 1 : 0) . ',
                    `chrono18` = ' . ($res['chrono18'] == true ? 1 : 0) . ',
                    `chronoclassic` = ' . ($res['chronoclassic'] == true ? 1 : 0) . ',
                    `relaiseurope` = ' . ($res['relaiseurope'] == true ? 1 : 0) . ',
                    `relaisdom` = ' . ($res['relaisdom'] == true ? 1 : 0) . ',
                    `rdv` = ' . ($res['rdv'] == true ? 1 : 0) . ',
                    `sameday` = ' . ($res['sameday'] == true ? 1 : 0) . ',
                    `dimanchebal` = ' . ($res['dimanchebal'] == true ? 1 : 0) . ',
                    `toshop` = ' . ($res['toshop'] == true ? 1 : 0) . ',
                    `toshopeurope` = ' . ($res['toshopeurope'] == true ? 1 : 0) . ',
                    `last_updated` = ' . time() . '
                    WHERE postcode = "' . pSQL($a->postcode) . '" && country = "' . pSQL(Chronopost::maybeCountryMapping($c->iso_code)) . '"');
            }

            return $res;
        }

        return $cache[0];
    }

    /** CARRIER-RELATED FUNCTIONS **/
    public function getOrderShippingCost($cart, $shippingCost)
    {
        // Check if Chronopost method
        if (!self::isChrono($this->id_carrier)) {
            return $shippingCost;
        }

        // Case not logged in
        if ($cart->id_address_delivery == 0) {
            return $shippingCost;
        }

        $a = new Address($cart->id_address_delivery);
        $c = new Country($a->id_country);
        $carrier = new Carrier($this->id_carrier);

        // ChronoFresh/Chronopost mode switcher
        $isSharedCarrier = Chronofresh::isSharedCarrier($carrier);
        if (!$isSharedCarrier) {
            $isFreshAccount = Chronofresh::isFreshAccount();
            $isFreshCarrier = Chronofresh::isFreshCarrier($carrier);
            if ($isFreshAccount && !$isFreshCarrier) {
                return false;
            }

            if (!$isFreshAccount && $isFreshCarrier) {
                return false;
            }
        }

        // Check weight limit
        $relaisAvailable = true;
        $classicAvailable = true;
        foreach ($cart->getProducts() as $p) {
            if ($p['weight'] * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 20) {
                $relaisAvailable = false;
            }

            if ($p['weight'] * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 30) {
                $classicAvailable = false;
                break;
            }
        }

        if (!$classicAvailable) {
            return false;
        }

        $freeFeesPrice = Configuration::get('PS_SHIPPING_FREE_PRICE');
        $freeFeesWeight = Configuration::get('PS_SHIPPING_FREE_WEIGHT');
        $orderTotalwithDiscounts = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, null, null, false);

        if ($orderTotalwithDiscounts >= (float)($freeFeesPrice) && (float)($freeFeesPrice) > 0) {
            return 0;
        }

        if ((float)$freeFeesWeight > 0 && $cart->getTotalWeight() >= (float)$freeFeesWeight) {
            return 0;
        }

        // CALCULATE PRODUCTS
        $calculatedProducts = self::calculateProducts($cart);
        $wsHelper = self::getWsHelper();

        $productCode = 1;
        $defaultAccount = '';
        switch ($carrier->id_reference) {
            case Configuration::get('CHRONOPOST_CHRONORELAIS_ID'):
                $productCode = self::$carriersDefinitions['CHRONORELAIS']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONORELAIS_ACCOUNT');
                if ($c->iso_code !== 'FR' && $c->iso_code !== 'FX' && $c->iso_code !== 'MC') {
                    return false;
                }

                if (!$relaisAvailable) {
                    return false;
                }
                break;
            case Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ID'):
                $productCode = self::$carriersDefinitions['CHRONORELAIS_AMBIENT']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ACCOUNT');
                if ($c->iso_code !== 'FR' && $c->iso_code !== 'FX' && $c->iso_code !== 'MC') {
                    return false;
                }

                if (!$relaisAvailable) {
                    return false;
                }
                break;
            case Configuration::get('CHRONOPOST_RELAISEUROPE_ID'):
                $productCode = self::$carriersDefinitions['RELAISEUROPE']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_RELAISEUROPE_ACCOUNT');
                if (!$relaisAvailable) {
                    return false;
                }

                if ($calculatedProducts['relaiseurope'] === '0') {
                    return false;
                }

                break;
            case Configuration::get('CHRONOPOST_RELAISDOM_ID'):
                $productCode = self::$carriersDefinitions['RELAISDOM']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_RELAISDOM_ACCOUNT');
                if (!$relaisAvailable) {
                    return false;
                }

                if ($calculatedProducts['relaisdom'] === '0') {
                    return false;
                }
                break;
            case Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID'):
                $productCode = self::$carriersDefinitions['TOSHOPDIRECT']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_TOSHOPDIRECT_ACCOUNT');
                if ($c->iso_code !== 'FR' && $c->iso_code !== 'FX' && $c->iso_code !== 'MC') {
                    return false;
                }

                if (!$relaisAvailable) {
                    return false;
                }

                if ($calculatedProducts['toshop'] === '0') {
                    return false;
                }
                break;
            case Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID'):
                $productCode = self::$carriersDefinitions['TOSHOPDIRECT_EUROPE']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ACCOUNT');
                if (!$relaisAvailable) {
                    return false;
                }

                if ($calculatedProducts['toshopeurope'] === '0') {
                    return false;
                }
                break;
            case Configuration::get('CHRONOPOST_CHRONO13_ID'):
                $productCode = self::$carriersDefinitions['CHRONO13']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONO13_ACCOUNT');
                if ($c->iso_code !== 'FR' && $c->iso_code !== 'FX') {
                    return false;
                }

                break;
            case Configuration::get('CHRONOPOST_CHRONO13_INSTANCE_ID'):
                $productCode = self::$carriersDefinitions['CHRONO13_INSTANCE']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONO13_INSTANCE_ACCOUNT');
                if ($c->iso_code !== 'FR' && $c->iso_code !== 'FX') {
                    return false;
                }

                break;
            case Configuration::get('CHRONOPOST_CHRONO10_ID'):
                if ($c->iso_code !== 'FR' && $c->iso_code !== 'FX' && $c->iso_code !== 'MC') {
                    return false;
                }

                if ($calculatedProducts['chrono10'] === '0') {
                    return false;
                }

                $productCode = self::$carriersDefinitions['CHRONO10']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONO10_ACCOUNT');
                break;
            case Configuration::get('CHRONOPOST_CHRONO18_ID'):
                if ($c->iso_code !== 'FR' && $c->iso_code !== 'FX' && $c->iso_code !== 'MC') {
                    return false;
                }

                if ($calculatedProducts['chrono18'] === '0') {
                    return false;
                }

                $productCode = self::$carriersDefinitions['CHRONO18']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONO18_ACCOUNT');
                break;
            case Configuration::get('CHRONOPOST_CHRONORDV_ID'):
                if ($c->iso_code !== 'FR' && $c->iso_code !== 'FX') {
                    return false;
                }

                if ($calculatedProducts['rdv'] === '0') {
                    return false;
                }

                return Chronopost::getRDVCost($cart->id, null, $shippingCost);
            case Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID'):
                if ($c->iso_code === 'FR' || $c->iso_code === 'FX' || $c->iso_code === 'MC') {
                    return false;
                }

                $productCode = self::$carriersDefinitions['CHRONOEXPRESS']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONOEXPRESS_ACCOUNT');
                break;
            case Configuration::get('CHRONOPOST_CHRONOCLASSIC_ID'):
                if ($calculatedProducts['chronoclassic'] === '0') {
                    return false;
                }

                $productCode = self::$carriersDefinitions['CHRONOCLASSIC']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONOCLASSIC_ACCOUNT');
                break;
            case Configuration::get('CHRONOPOST_SAMEDAY_ID'):
                if ($calculatedProducts['sameday'] === '0') {
                    return false;
                }

                $productCode = self::$carriersDefinitions['SAMEDAY']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_SAMEDAY_ACCOUNT');

                // Additionnal verification about time limit defined in configuration
                $hourEnd = Configuration::get('CHRONOPOST_SAMEDAY_SAMEDAY_HOUR_END');
                $minuteEnd = Configuration::get('CHRONOPOST_SAMEDAY_SAMEDAY_MINUTE_END');
                if (!is_numeric($hourEnd) || !is_numeric($minuteEnd)) {
                    $hourEnd = '15';
                    $minuteEnd = '00';
                }

                $deliveryTimeLimit = new \DateTime(date('Y-m-d') . ' ' . "$hourEnd:$minuteEnd:00");
                $currentTime = new \DateTime('NOW');
                if ($currentTime >= $deliveryTimeLimit) {
                    return false;
                }

                break;
            case Configuration::get('CHRONOPOST_DIMANCHEBAL_ID'):
                if ($calculatedProducts['dimanchebal'] === '0') {
                    return false;
                }

                $productCode = self::$carriersDefinitions['DIMANCHEBAL']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_DIMANCHEBAL_ACCOUNT');
                break;
            case Configuration::get('CHRONOPOST_CHRONOFRESH_ID'):
                // ChronoFresh is not shipping to Corsica
                if ($c->iso_code === 'FR' && $a->postcode >= 20000 && $a->postcode < 21000) {
                    return false;
                }

                // 1T product can be removed from contract, so we need to fallback on available product if needed
                $productCode = Configuration::get('CHRONOPOST_QUICKCOST_PRODUCT');
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONOFRESH_ACCOUNT');
                $availFreshProducts = $wsHelper->getChronofreshCodes();
                if (!in_array($productCode, $availFreshProducts)) {
                    // Replace with next in line
                    $productCode = array_shift($availFreshProducts);

                    Configuration::updateValue('CHRONOPOST_QUICKCOST_PRODUCT', $productCode);
                }

                if ($c->iso_code !== 'FR' && $c->iso_code !== 'FX') {
                    return false;
                }
                break;
            case Configuration::get('CHRONOPOST_CHRONOFRESH_CLASSIC_ID'):
                if ($c->iso_code !== 'BE' && $c->iso_code !== 'ES') {
                    return false;
                }

                $productCode = self::$carriersDefinitions['CHRONOFRESH_CLASSIC']['product_code'];
                $defaultAccount = Configuration::get('CHRONOPOST_CHRONOFRESH_CLASSIC_ACCOUNT');
                break;
        }

        $useSaturday = (bool)$this->getSaturdaySupplement($cart->id) && Configuration::get('CHRONOPOST_SATURDAY_CUSTOMER') === 'yes';
        if ($useSaturday && self::gettingReadyForSaturday($carrier, true) &&
            in_array($carrier->id, $this->getSaturdayCarrierIds())) {
            $shippingCost += Configuration::get('CHRONOPOST_SATURDAY_SUPPLEMENT');
        }

        // Check if quickcost is enabled
        if (Configuration::get('CHRONOPOST_QUICKCOST_ENABLED') === '0') {
            if ($c->iso_code == 'FR' && $a->postcode >= 20000 && $a->postcode < 21000) {
                return $shippingCost + (float)Configuration::get('CHRONOPOST_CORSICA_SUPPLEMENT');
            }

            return $shippingCost;
        }

        $arrcode = ((in_array($c->iso_code, ['FR', 'FX', 'BL', 'MF'])) ? $a->postcode : $c->iso_code);
        $cache = Db::getInstance()->executeS(
            'SELECT price, last_updated FROM `' . _DB_PREFIX_ . 'chrono_quickcost_cache` '
            . 'WHERE arrcode = "' . pSQL($arrcode) . '" && product_code="' . pSQL($productCode) . '"'
            . ' && weight="' . $cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') . '"'
            . ' && account_number="' . $defaultAccount . '"'
        );

        if (!empty($cache) && $cache[0]['last_updated'] + 24 * 3600 > time()) {
            // return from cache
            $cachedPrice = $shippingCost;
            if ($cache[0]['price'] > 0) {
                $cachedPrice = $cache[0]['price'] * (1 + (float)Configuration::get('CHRONOPOST_QUICKCOST_SUPPLEMENT') / 100);
            }

            return $cachedPrice;
        }

        include_once(__DIR__ . '/libraries/QuickcostServiceWSService.php');

        // Get account informations for the product
        $ws = new QuickcostServiceWSService();
        $qc = new quickCost();
        $qc->accountNumber = $defaultAccount;
        $qc->password = self::getAccountInformationByAccountNumber($defaultAccount)['password'];
        $qc->depCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');
        $qc->arrCode = $arrcode;
        $qc->productCode = $productCode;
        $qc->type = 'M';
        $qc->weight = $cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF');

        if (!$qc->weight) {
            $qc->weight = 0.1;
        } // 0 yields an error

        try {
            $quickCostAmount = 0;
            $res = $ws->quickCost($qc);

            if ($res->return->amountTTC > 0) {
                $quickCostAmount = $res->return->amountTTC;
            }

            if ($res->return->amount > 0) {
                $quickCostAmount = $res->return->amount;
            }
        } catch (Exception $e) {
            return $shippingCost;
        }

        if (empty($cache)) {
            DB::getInstance()->query('INSERT INTO ' . _DB_PREFIX_ . 'chrono_quickcost_cache (product_code, arrcode,
                 weight, price, account_number, last_updated) VALUES (
                        "' . pSQL($productCode) . '",
                        "' . pSQL($arrcode) . '",
                        "' . (float)$cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') . '",
                        "' . (float)$quickCostAmount . '",
                        "' . $defaultAccount . '",
                        "' . time() . '")
                ');
        } else {
            DB::getInstance()->query('UPDATE ' . _DB_PREFIX_ . 'chrono_quickcost_cache
                    SET price="' . (float)$quickCostAmount . '", last_updated=' . time() . '
                    WHERE arrcode="' . pSQL($arrcode) . '"
                    and product_code="' . pSQL($productCode) . '"
                    and account_number="' . pSQL($defaultAccount) . '"
                    AND weight="' . (float)$cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') . '"
                ');
        }

        if ($quickCostAmount != 0) {
            return $res->return->amountTTC * (1 + (float)Configuration::get('CHRONOPOST_QUICKCOST_SUPPLEMENT') / 100);
        }

        return $shippingCost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return $this->getOrderShippingCost($params, 0);
    }

    /** ADMINISTRATION **/
    private function generateChronoForm($prefix)
    {
        $prefix = Tools::strtolower($prefix);
        $var_name = Tools::strtoupper($prefix);
        $vars = array(
            'civility',
            'name',
            'name2',
            'address',
            'address2',
            'zipcode',
            'city',
            'contactname',
            'email',
            'phone',
            'mobile',
            'country'
        );

        $smarty = [];
        $smarty['prefix'] = $prefix;
        foreach ($vars as $var) {
            $smarty[$var] = Configuration::get('CHRONOPOST_' . $var_name . '_' . Tools::strtoupper($var));
        }

        $this->context->smarty->assign($smarty);

        return $this->context->smarty->fetch(__DIR__ . '/views/templates/admin/contact.tpl');
    }

    private function dayField($fieldName, $default = 0, $group_name = 'saturday')
    {
        $selected = Configuration::get('CHRONOPOST_' . Tools::strtoupper($group_name) . '_' . Tools::strtoupper($fieldName));
        if ($selected === false) {
            $selected = $default;
        }

        $this->context->smarty->assign(
            array(
                'selected'   => $selected,
                'field_name' => $fieldName,
                'group_name' => $group_name
            )
        );

        return $this->context->smarty->fetch(__DIR__ . '/views/templates/admin/days.tpl');
    }

    private function hourField($fieldName, $default = 0, $group_name = 'saturday')
    {
        $selected = Configuration::get('CHRONOPOST_' . Tools::strtoupper($group_name) . '_' . Tools::strtoupper($fieldName));
        if ($selected === false) {
            $selected = $default;
        }

        // Smarty is so painful
        $this->context->smarty->assign(
            array(
                'selected'   => $selected,
                'field_name' => $fieldName,
                'group_name' => $group_name
            )
        );

        return $this->context->smarty->fetch(__DIR__ . '/views/templates/admin/hours.tpl');
    }

    private function minuteField($fieldName, $default = 0, $group_name = 'saturday')
    {
        $selected = Configuration::get('CHRONOPOST_' . Tools::strtoupper($group_name) . '_' . Tools::strtoupper($fieldName));
        if ($selected === false) {
            $selected = $default;
        }

        // Can't stop the pain
        $this->context->smarty->assign(
            array(
                'selected'   => $selected,
                'field_name' => $fieldName,
                'group_name' => $group_name
            )
        );

        return $this->context->smarty->fetch(__DIR__ . '/views/templates/admin/minutes.tpl');
    }

    private function carrierForm($code)
    {
        $wsHelper = self::getWsHelper();

        $availContracts = $wsHelper->getContractsForProduct(self::$carriersDefinitions[$code]);

        $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, CARRIERS_MODULE);
        $selected = Configuration::get('CHRONOPOST_' . Tools::strtoupper($code) . '_ID');
        $defaultAccount = Configuration::get('CHRONOPOST_' . Tools::strtoupper($code) . '_ACCOUNT');

        $labelsMap = array(
            'DIMANCHEBAL'          => 'CHRONO DIMANCHE',
            'CHRONORDV'            => 'CHRONO PRECISE',
            'CHRONOFRESH'          => 'CHRONO FRESH',
            'CHRONOFRESH_CLASSIC'  => 'CHRONO FRESH CLASSIC',
            'CHRONORELAIS'         => 'CHRONO RELAIS',
            'CHRONORELAIS_AMBIENT' => 'CHRONO AMBIENT RELAIS',
            'CHRONOCLASSIC'        => 'CHRONO CLASSIC',
            'CHRONOEXPRESS'        => 'CHRONO EXPRESS',
            'RELAISDOM'            => 'CHRONO RELAIS DOM',
            'RELAISEUROPE'         => 'CHRONO RELAIS EUROPE',
            'TOSHOPDIRECT'         => 'CHRONO TOSHOP DIRECT',
            'TOSHOPDIRECT_EUROPE'  => 'CHRONO TOSHOP EUROPE',
            'CHRONO13_INSTANCE'    => 'CHRONO13 INSTANCE AGENCE'
        );

        $isFresh = isset(self::$carriersDefinitions[$code]['fresh']) ?
            self::$carriersDefinitions[$code]['fresh'] : false;
        $isSharedCarrier = isset(self::$carriersDefinitions[$code]['shared_carrier']) ?
            self::$carriersDefinitions[$code]['shared_carrier'] : false;

        $this->context->smarty->assign(
            array(
                'is_fresh'           => $isFresh,
                'shared_carrier'     => $isSharedCarrier,
                'available_accounts' => $availContracts,
                'carriers'           => $carriers,
                'selected'           => $selected,
                'code'               => $code,
                'default_account'    => $defaultAccount,
                'code_label'         => isset($labelsMap[$code]) ? $labelsMap[$code] : $code,
            )
        );

        return $this->context->smarty->fetch(__DIR__ . '/views/templates/admin/carrier.tpl');
    }

    private function postValidation()
    {
        return true;
    }

    private function postProcess()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        include_once __DIR__ . '/libraries/webservicesHelper.php';
        $wsHelper = new webservicesHelper();

        DB::getInstance()->Execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'chrono_calculateproducts_cache2');
        if (Tools::getValue('createnewcarrier') == "") {
            $params = Tools::getValue('chronoparams');
            $accountsInfos = [];
            $accounts = [];

            $defaultAddress = new Address();
            $country = Country::getByIso($params['shipper']['country']);
            $defaultAddress->city = $params['shipper']['city'];
            $defaultAddress->postcode = $params['shipper']['zipcode'];
            $defaultAddress->id_country = $country;

            if (!isset($params['saturday']['carriers'])) {
                $params['saturday']['carriers'] = [];
            }

            foreach ($params as $prefix => $var) {
                foreach ($var as $varname => $value) {
                    if (($varname == "account" && $prefix == "general") || $varname == "subaccount" ||
                        $varname == "password" || $varname == "accountname") {
                        $i = 0;
                        foreach ($value as $val) {
                            $accounts[$i][$varname] = $value[$i];
                            $i++;
                        }
                    } else {
                        if ($varname == 'account') {
                            if (!isset($accountsInfos[$value]['methods']) && $value != -1) {
                                $contractInfos = Chronopost::getAccountInformationByAccountNumber($value);
                                $accountsInfos[$value]['methods'] = $wsHelper->getMethodsForContract($contractInfos['account']);
                            }

                            // Check contract before saving
                            if ($var['id'] !== '-1' && $value !== '-1' && $accountsInfos[$value]['methods'] &&
                                !in_array(self::$carriersDefinitions[$prefix]['product_code'],
                                    $accountsInfos[$value]['methods'])) {
                                $inContract = false;

                                // (Fresh) check alternative products
                                if (isset(self::$carriersDefinitions[$prefix]['products']) && is_array(self::$carriersDefinitions[$prefix]['products'])) {
                                    foreach (self::$carriersDefinitions[$prefix]['products'] as $product) {
                                        if (in_array($product['code'], $accountsInfos[$value]['methods'])) {
                                            $inContract = true;
                                        }
                                    }
                                }

                                if (!$inContract) {
                                    $this->_errors[] = Module::displayError(
                                        sprintf($this->l('Carrier %s is not available for the selected contract'),
                                            $prefix)
                                    );

                                    continue;
                                }
                            }
                        }

                        if (is_array($value)) {
                            $value = json_encode($value);
                        }

                        Configuration::updateValue(
                            'CHRONOPOST_' . Tools::strtoupper($prefix) . '_' . Tools::strtoupper($varname),
                            $value
                        );
                    }
                }

                if (!empty($accounts) && $prefix == 'general') {
                    // Cleanup
                    $accountDeleted = false;
                    foreach ($accounts as $i => $account) {
                        if (!$account['account'] || !$account['password'] || !$account['accountname']) {
                            unset($accounts[$i]);
                            $accountDeleted = true;
                        }
                    }

                    if ($accountDeleted) {
                        $this->_errors[] = Module::displayError($this->l('Invalid contracts found and deleted.'));
                    }

                    Configuration::updateValue(
                        'CHRONOPOST_' . Tools::strtoupper($prefix) . '_ACCOUNTS',
                        json_encode($accounts)
                    );
                }
            }
        }

        // Force disabling saturday shipping for ChronoFresh customers
        if (Configuration::get('CHRONOPOST_GENERAL_ACCOUNTTYPE') === self::CHRONOFRESH_TYPE_ID) {
            Configuration::updateValue('CHRONOPOST_SATURDAY_ACTIVE', 'no');
        }

        return true;
    }

    public function getContent()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $html = '';
        if (Tools::isSubmit('submitChronoConfig')) {
            if ($this->postValidation() && $this->postProcess()) {
                if (count($this->_errors)) {
                    foreach ($this->_errors as $error) {
                        $html .= $error;
                    }
                }
                $html .= Module::displayConfirmation($this->l('Settings updated.'));
            }
        }

        return $html . $this->displayForm();
    }

    public function displayForm()
    {
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/modal.css', 'all');
        $printMode = array(
            'PDF' => $this->l('PDF file'),
            'THE' => $this->l('Thermal printer'),
            'SPD' => $this->l('PDF without delivery proof')
            //'SER' =>'Imprimante thermique Chronopost'
        );

        $unitCoef = array(
            'KG' => '1',
            'G'  => '0.001'
        );

        $dropOffModes = array(
            $this->l('Pick-up and automatic lockers') => 'P',
            $this->l('Only Pick-up')                  => 'C'
        );

        $carriers_tpl = [];
        foreach (array_keys(self::$carriersDefinitions) as $code) {
            $carriers_tpl[$code] = $this->carrierForm($code);
        }

        $saturday_whitelist = ['CHRONO10', 'CHRONO13', 'CHRONO13_INSTANCE', 'CHRONO18'];
        $saturday_selected = json_decode(Configuration::get('CHRONOPOST_SATURDAY_CARRIERS'));
        if ($saturday_selected === null) {
            $saturday_selected = [];
        }

        $saturday_available_carriers = [];
        foreach (self::$carriersDefinitions as $code => $chrono_carrier) {
            if (!in_array($code, $saturday_whitelist)) {
                continue;
            }
            $carrierId = Configuration::get("CHRONOPOST_{$code}_ID");
            if (!$carrierId || $carrierId < 1) {
                continue;
            }
            $carrier = Carrier::getCarrierByReference($carrierId);
            $saturday_available_carriers[] = [
                'id'       => $carrier->id,
                'label'    => $carrier->name,
                'selected' => in_array($carrier->id, $saturday_selected)
            ];
        }

        $ws = new webservicesHelper();
        $availChronoFreshCodes = $ws->getChronofreshCodes();
        $availChronoFreshProducts = [];
        foreach ($availChronoFreshCodes as $code) {
            $key = array_search(
                $code,
                array_column(self::$carriersDefinitions['CHRONOFRESH']['products'], 'code')
            );
            if (is_int($key)) {
                $availChronoFreshProducts[] = self::$carriersDefinitions['CHRONOFRESH']['products'][$key];
            }
        }

        $chronofresh_dlc = Configuration::get('CHRONOPOST_CHRONOFRESH_DLC') ?: '3';

        // smarty-chain !
        $this->context->smarty->assign(
            array(
                'chronofresh_products'        => $availChronoFreshProducts,
                'post_uri'                    => $_SERVER['REQUEST_URI'],
                'chronopost_secret'           => Configuration::get('CHRONOPOST_SECRET'),
                'print_modes'                 => $printMode,
                'selected_print_mode'         => Configuration::get('CHRONOPOST_GENERAL_PRINTMODE'),
                'weights'                     => $unitCoef,
                'selected_weight'             => Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF'),
                'module_dir'                  => _MODULE_DIR_,
                'account_type'                => Configuration::get('CHRONOPOST_GENERAL_ACCOUNTTYPE'),
                'general_accounts'            => json_decode(Configuration::get('CHRONOPOST_GENERAL_ACCOUNTS'), 1),
                'saturday_active'             => Configuration::get('CHRONOPOST_SATURDAY_ACTIVE'),
                'saturday_display_customer'   => Configuration::get('CHRONOPOST_SATURDAY_CUSTOMER'),
                'saturday_supplement'         => Configuration::get('CHRONOPOST_SATURDAY_SUPPLEMENT'),
                'saturday_checked'            => Configuration::get('CHRONOPOST_SATURDAY_CHECKED'),
                'saturday_available_carriers' => $saturday_available_carriers,
                'day_start'                   => $this->dayField('day_start', 4),
                'hour_start'                  => $this->hourField('hour_start', 18),
                'minute_start'                => $this->minuteField('minute_start'),
                'day_rdv_on'                  => $this->dayField('day_on', 0, 'rdv'),
                'hour_rdv_on'                 => $this->hourField('hour_on', 0, 'rdv'),
                'minute_rdv_on'               => $this->minuteField('minute_on', 0, 'rdv'),
                'day_rdv_close_start'         => $this->dayField('day_close_st', 0, 'rdv'),
                'hour_rdv_close_start'        => $this->hourField('hr_close_st', 0, 'rdv'),
                'minute_rdv_close_start'      => $this->minuteField('min_close_st', 0, 'rdv'),
                'day_rdv_close_end'           => $this->dayField('day_close_end', 0, 'rdv'),
                'hour_rdv_close_end'          => $this->hourField('hr_close_end', 0, 'rdv'),
                'minute_rdv_close_end'        => $this->minuteField('min_close_end', 0, 'rdv'),
                'day_end'                     => $this->dayField('day_end', 5),
                'hour_end'                    => $this->hourField('hour_end', 16),
                'minute_end'                  => $this->minuteField('minute_end'),
                'carriers_tpl'                => $carriers_tpl,
                'rdv_delay'                   => Configuration::get('CHRONOPOST_RDV_DELAY'),
                'map_enabled'                 => Configuration::get('CHRONOPOST_MAP_ENABLED'),
                'corsica_supplement'          => Configuration::get('CHRONOPOST_CORSICA_SUPPLEMENT'),
                'quickcost_enabled'           => Configuration::get('CHRONOPOST_QUICKCOST_ENABLED'),
                'quickcost_supplement'        => Configuration::get('CHRONOPOST_QUICKCOST_SUPPLEMENT'),
                'quickcost_product'           => Configuration::get('CHRONOPOST_QUICKCOST_PRODUCT'),
                'advalorem_enabled'           => Configuration::get('CHRONOPOST_ADVALOREM_ENABLED'),
                'advalorem_minvalue'          => Configuration::get('CHRONOPOST_ADVALOREM_MINVALUE'),
                'bal_enabled'                 => Configuration::get('CHRONOPOST_BAL_ENABLED'),
                'rdv_price1'                  => Configuration::get('CHRONOPOST_RDV_PRICE1'),
                'rdv_price2'                  => Configuration::get('CHRONOPOST_RDV_PRICE2'),
                'rdv_price3'                  => Configuration::get('CHRONOPOST_RDV_PRICE3'),
                'rdv_price4'                  => Configuration::get('CHRONOPOST_RDV_PRICE4'),
                'rdv_state1'                  => Configuration::get('CHRONOPOST_RDV_STATE1'),
                'rdv_state2'                  => Configuration::get('CHRONOPOST_RDV_STATE2'),
                'rdv_state3'                  => Configuration::get('CHRONOPOST_RDV_STATE3'),
                'rdv_state4'                  => Configuration::get('CHRONOPOST_RDV_STATE4'),
                'shipper_form'                => $this->generateChronoForm('shipper'),
                'customer_form'               => $this->generateChronoForm('customer'),
                'return_form'                 => $this->generateChronoForm('return'),
                'return_default'              => Configuration::get('CHRONOPOST_RETURN_DEFAULT'),
                'sameday_hour_end'            => $this->hourField('sameday_hour_end', 15, 'sameday'),
                'sameday_minute_end'          => $this->minuteField('sameday_minute_end', 00, 'sameday'),
                'drop_modes'                  => $dropOffModes,
                'selected_drop_mode'          => Configuration::get('CHRONOPOST_MAP_DROPMODE'),
                'chronofresh_dlc'             => $chronofresh_dlc,
            )
        );

        return $this->context->smarty->fetch(__DIR__ . '/views/templates/admin/config.tpl');
    }

    /**
     * For retro-compatibility
     *
     * @param $order
     *
     * @return int
     */
    public static function getIdOrderCarrier($order)
    {
        if (version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
            return $order->getIdOrderCarrier();
        }

        return (int)Db::getInstance()->getValue('
            SELECT `id_order_carrier`
            FROM `' . _DB_PREFIX_ . 'order_carrier`
            WHERE `id_order` = ' . (int)$order->id);
    }

    /**
     * For retro-compatibility
     *
     * @param Order  $order
     * @param string $shippingNumber
     *
     * @return int
     */
    public static function setWsShippingNumber($order, $shippingNumber)
    {
        if (version_compare(_PS_VERSION_, '1.6.0.9', '>=')) {
            return $order->setWsShippingNumber($shippingNumber);
        }

        $idOrderCarrier = Db::getInstance()->getValue('SELECT `id_order_carrier` FROM `' . _DB_PREFIX_ . 'order_carrier` WHERE `id_order` = ' . (int)$order->id);
        if ($idOrderCarrier) {
            $orderCarrier = new OrderCarrier($idOrderCarrier);
            $orderCarrier->tracking_number = $shippingNumber;
            $orderCarrier->update();
        } else {
            $order->shipping_number = $shippingNumber;
        }

        return true;
    }

    public static function getIdOrderInvoice($order)
    {
        return (int)Db::getInstance()->getValue('
            SELECT `id_order_invoice`
            FROM `' . _DB_PREFIX_ . 'order_invoice`
            WHERE `id_order` = ' . (int)$order->id);
    }

    /**
     * @param int $orderId
     *
     * @return array An array with all tracking numbers
     * @throws PrestaShopDatabaseException
     */
    public static function getAllTrackingNumbers($orderId)
    {
        $LTHistory = [];
        $LTRequest = DB::getInstance()->executeS(
            'SELECT lt, lt_reference, type FROM '
            . _DB_PREFIX_ . 'chrono_lt_history WHERE id_order = ' . (int)$orderId . ' AND `cancelled` IS NULL'
        );

        foreach ($LTRequest as $LT) {
            $LTHistory[] = array(
                'type'         => $LT['type'],
                'lt'           => $LT['lt'],
                'lt_reference' => $LT['lt_reference'],
            );
        }

        return $LTHistory;
    }

    public static function getAccountTrackingNumber($orderId, $trackingNumber)
    {
        $accounts = [];
        $result = DB::getInstance()->executeS(
            'SELECT account_number FROM '
            . _DB_PREFIX_ . 'chrono_lt_history WHERE id_order = ' . (int)$orderId . ' AND lt = \'' . pSQL($trackingNumber) . '\' AND `cancelled` IS NULL 
        ');

        foreach ($result as $account) {
            $accounts[] = $account['account_number'];
        }

        return $accounts;
    }

    public static function getAccountInformationByAccountNumber($accountNumber)
    {
        if (!is_numeric($accountNumber)) {
            return false;
        }

        $accounts = json_decode(Configuration::get('CHRONOPOST_GENERAL_ACCOUNTS'), 1);
        foreach ($accounts as $account) {
            if ($account['account'] == $accountNumber) {
                return $account;
            }
        }

        return false;
    }

    public static function getCodeFromCarrier($idReference)
    {
        foreach (self::$carriersDefinitions as $productCode => $data) {
            if ($idReference == Configuration::get('CHRONOPOST_' . $productCode . '_ID')) {
                return $productCode;
            }
        }

        return false;
    }

    public function getSaturdayCarrierIds()
    {
        /** @var Carrier $carrier */
        $saturdayIds = [];
        $carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, CARRIERS_MODULE);
        $saturday_selected = json_decode(Configuration::get('CHRONOPOST_SATURDAY_CARRIERS'));
        foreach ($carriers as $carrier) {
            if (!self::isChrono($carrier['id_carrier'])) {
                continue;
            }

            if (is_array($saturday_selected) && !in_array($carrier['id_carrier'], $saturday_selected)) {
                continue;
            }

            if (self::gettingReadyForSaturday((object)$carrier, true)) {
                $saturdayIds[] = (int)$carrier['id_carrier'];
            }
        }

        return $saturdayIds;
    }
}
