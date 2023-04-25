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
 * Class AdminCarrierSettingController
 */
class AdminCarrierSettingController extends ModuleAdminController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        // Bootstrap enable.
        $this->bootstrap = true;
        // Apply renderForm method if updatecarrier in URL parameters.
        $this->table = 'carrier';
        $this->className = 'Carrier';
        $this->lang = false;
        $this->explicitSelect = true;
        $this->addRowAction('edit');
        $this->allow_export = false;
        $this->deleted = false;

        parent::__construct();

        $this->page_header_toolbar_title = sprintf($this->l('Set up %s delivery services'), TNTOfficiel::CARRIER_NAME);

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        $arrArgIntShopIDList = Shop::getContextListShopID();
        $arrIntShopIDList = array_map('intval', $arrArgIntShopIDList);

        $this->_select = '
        a.id_reference AS id_reference,
        a.active AS active,
        a.name AS name_carrier
        ';

        $this->_join = '
        LEFT JOIN `'._DB_PREFIX_.'tntofficiel_carrier` c ON (c.`id_carrier` = a.`id_carrier`)
        JOIN `'._DB_PREFIX_.'shop` s ON (s.`id_shop` = c.`id_shop`)
         ';

        $this->_where = 'AND a.deleted = 0 AND c.id_shop IN ('.implode(',', $arrIntShopIDList).')';

        // If context is Shop (for multistore).
        if ($objTNTContextAccountModel->id_shop > 0) {
            // Filter using carrier that are available for the current account in this shop.
            $strShopIDList = implode(',', $arrIntShopIDList);
            $strCarrierAvailableList =
                '\''.implode('\',\'', array_keys($objTNTContextAccountModel->availabilities())).'\'';

            $this->_where = <<<SQL
AND a.deleted = 0 AND c.id_shop IN (${strShopIDList})
-- Filter using available carrier.
AND CONCAT(`account_type`,':',`carrier_type`,':',`carrier_code1`,':',`carrier_code2`) IN (${strCarrierAvailableList})
SQL;
        }


        $this->_orderBy = 'id_reference';
        $this->_orderWay = 'ASC';
        $this->_use_found_rows = true;


        $this->fields_list = array(
            'id_carrier' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'carrier_label' => array(
                'title' => sprintf($this->l('%s delivery service'), TNTOfficiel::CARRIER_NAME),
                'callback' => 'getCarrierLabel',
                'search' => false,
                'orderby' => false,
                'filter_key' => 'a!name',
            ),
            'name_carrier' => array(
                'title' => $this->l('Carrier'),
                //'orderby' => false,
                'filter_key' => 'a!name',
            ),
            'name_shop_custom' => array(
                'title' => $this->l('Shop'),
                'filter_key' => 's!name',
            ),
        );
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

        $this->toolbar_title = array(
            $this->l('Select the carrier for which you want to update the pricing'),
        );

        $this->toolbar_btn = array(
            //'back' => array()
        );

        $this->page_header_toolbar_btn = array();

        $this->show_page_header_toolbar = true;

        parent::initPageHeaderToolbar();

        $this->context->smarty->assign(array(
            'help_link' => null,
        ));
    }

    /**
     * @param $idTntCarrier
     *
     * @return mixed
     */
    public static function getCarrierLabel($echo, $tr)
    {
        TNTOfficiel_Logstack::log();

        // Unused but inherited argument.
        $echo === $echo;

        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($tr['id_carrier']);

        if ($objTNTCarrierModel === null) {
            return null;
        }

        return $objTNTCarrierModel->getCarrierInfos()->label;
    }

    /**
     * {@inheritdoc}
     */
    public function createTemplate($tpl_name)
    {
        TNTOfficiel_Logstack::log();

        if (file_exists($this->getTemplatePath().$tpl_name) && $this->viewAccess()) {
            return $this->context->smarty->createTemplate($this->getTemplatePath().$tpl_name, $this->context->smarty);
        }

        return parent::createTemplate($tpl_name);
    }

    /**
     * Load script.
     */
    public function setMedia($isNewTheme = false)
    {
        TNTOfficiel_Logstack::log();

        parent::setMedia(false);

        $this->module->addJS('AdminCarrierSetting.js');
    }

    public function renderList()
    {
        TNTOfficiel_Logstack::log();

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();
        // If no account available for this context.
        if ($objTNTContextAccountModel === null) {
            return false;
        }

        // Form Helper.
        $objHelperForm = new HelperForm();

        // Form Structure used as parameter for Helper 'generateForm' method.
        $arrFormStruct = array();
        // Form Values used for Helper 'fields_value' property.
        $arrFieldsValue = array();

        //$objHelperForm->base_folder = 'helpers/form/';
        $objHelperForm->base_tpl = 'AdminCarrierSetting.tpl';

        // Module using this form.
        $objHelperForm->module = $this->module;
        // Controller name.
        $objHelperForm->name_controller = TNTOfficiel::MODULE_NAME;
        // Token.
        $objHelperForm->token = Tools::getAdminTokenLite('AdminCarrierSetting');
        // Form action attribute.
        $objHelperForm->currentIndex = AdminController::$currentIndex.'&configure='.TNTOfficiel::MODULE_NAME;


        // Language.
        $objHelperForm->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $objHelperForm->allow_employee_form_lang = (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
            : 0
        );


        // Smarty assign().
        // /modules/<MODULE>/views/templates/admin/_configure/helpers/form/form.tpl
        // extends /<ADMIN>/themes/default/template/helpers/form/form.tpl
        $objHelperForm->tpl_vars['tntofficiel'] = array();

        /*
         * Create Carrier Form
         */

        // Display warning message in the module list for account authentification.
        if ($objTNTContextAccountModel->getAuthValidatedDateTime() === null) {
            $this->warnings[] = $this->l('To create carriers, the authentication must be validated on the account setting page.');
        } else {
            $strIDFormCarrierCreate = 'submit'.TNTOfficiel::MODULE_NAME.'CarrierCreate';
            $arrFormCarrierCreate = $this->getFormCarrierCreate($strIDFormCarrierCreate, $arrFieldsValue);

            $arrFormStruct[$strIDFormCarrierCreate] = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('SETTING TNT DELIVERY SERVICES'),
                    ),
                    'input' => $arrFormCarrierCreate['input'],
                    'description' =>
                        $this->l('A carrier will be created in Prestashop for each of the selected shops. They will then be editable independently.')
                        .'<br />'.$this->l('At the creation, each of the carriers will be named according to the following format: name of the service (ex: 9:00 Express in company)'),
                    'submit' => array(
                        'title' => $this->l('Create the carriers'),
                        'class' => 'btn btn-default pull-right',
                        'name' => $strIDFormCarrierCreate,
                    ),
                ),
            );
        }


        // Set all form fields values.
        $objHelperForm->fields_value = $arrFieldsValue;

        // Global Submit ID.
        //$objHelperForm->submit_action = 'submit'.TNTOfficiel::MODULE_NAME;
        // Get generated forms.
        $strDisplayForms = $objHelperForm->generateForm($arrFormStruct);


        /*
         * Disabled carrier.
         */

        $arrContextTNTCarrierIDList = array_keys(TNTOfficielCarrier::getContextCarrierModelList());

        $arrFormMessagesCarriers = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        $arrCarrierDisabled = array();

        foreach ($arrContextTNTCarrierIDList as $intCarrierID) {
            $objPSCarrier = TNTOfficielCarrier::getPSCarrier($intCarrierID);
            // If carrier object available and not active.
            if ($objPSCarrier !== null && !$objPSCarrier->active) {
                $arrCarrierDisabled[] = $intCarrierID;
            }
        }

        if (count($arrCarrierDisabled) > 0) {
            $arrFormMessagesCarriers['warning'][] = sprintf(
                $this->l('Disabled carrier(s) : %s.'),
                'ID '.implode(', ', $arrCarrierDisabled)
            );
        }

        $arrFormCarriersMessageHTML = TNTOfficiel_Tools::getAlertHTML($arrFormMessagesCarriers);
        $strFormCarriersMessageHTML = '<div class="maxwidth-layout">'.implode('', $arrFormCarriersMessageHTML).'</div>';

        $strInfoCarrierConfig = '<div class="maxwidth-layout text-right clearfix"><a class="_blank" href='
            .$this->context->link->getAdminLink('AdminCarriers').'>'
            .$this->l('Other carrier modifications ((de) activation, deletion, naming ...) can be found on the Transporters page')
            .'</a></div>';

        $this->content = $strDisplayForms.parent::renderList().$strFormCarriersMessageHTML.$strInfoCarrierConfig;

        return '';
    }

    /**
     * Get the Carrier creation form data for Helper.
     *
     * @return array
     */
    private function getFormCarrierCreate($strArgIDForm, &$arrRefFieldsValue)
    {
        TNTOfficiel_Logstack::log();

        $arrFormMessagesServiceTnt = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        // Input values.
        $strArgServiceTnt = pSQL(Tools::getValue('TNTOFFICIEL_SERVICE_TNT'));

        // Displayed values.
        $arrRefFieldsValue['TNTOFFICIEL_SERVICE_TNT'] = null;
        if (Tools::isSubmit($strArgIDForm)) {
            $arrRefFieldsValue['TNTOFFICIEL_SERVICE_TNT'] = $strArgServiceTnt;
        }

        $arrCarrierAvailabilities = $objTNTContextAccountModel->availabilities();

        $arrCarrierCode1MapHours = array(
            'N' => '08',
            'A' => '09',
            'T' => '10',
            'M' => '12',
            'J' => '13',
            'P' => '18',
        );

        $objAllCarrierAvailabilities = array();
        foreach ($arrCarrierAvailabilities as $strID => $arrCarrierAvailable) {
            $strCarrierLabel = TNTOfficielCarrier::getCarrierLabel(
                $arrCarrierAvailable['accountType'],
                $arrCarrierAvailable['carrierType'],
                $arrCarrierAvailable['carrierCode1']
            );

            $strSortKeyHour = $arrCarrierAvailable['carrierCode1'];
            if (array_key_exists($arrCarrierAvailable['carrierCode1'], $arrCarrierCode1MapHours)) {
                $strSortKeyHour = $arrCarrierCode1MapHours[$arrCarrierAvailable['carrierCode1']];
            }
            $strSortKey = $arrCarrierAvailable['accountType'].$arrCarrierAvailable['carrierType'].$strSortKeyHour
                .$strCarrierLabel.$arrCarrierAvailable['carrierCode2'];

            $objAllCarrierAvailabilities[$strSortKey] = (object)array(
                'name' => $strCarrierLabel,
                //'name' => $arrCarrierAvailable['carrierLabel'],
                'id' => $strID,
            );
        }
        // Sort key.
        ksort($objAllCarrierAvailabilities);

        // If form submitted.
        if (Tools::isSubmit($strArgIDForm)) {
            $arrCarrierCreate = explode(':', $strArgServiceTnt);
            $shopAdded = false;
            foreach ($objTNTContextAccountModel->getPSShopList() as $intShopID => $objPSShop) {
                $boolExist = TNTOfficielCarrier::isExist(
                    $intShopID,
                    $arrCarrierCreate[0],
                    $arrCarrierCreate[1],
                    $arrCarrierCreate[2],
                    $arrCarrierCreate[3]
                );
                if ($boolExist) {
                    $shopAdded = true;
                    break;
                }
            }

            // RG-13
            // Si aucun transporteur n'existe déjà pour ce service de livraison pour les boutiques sélectionnées
            // OU au moins une boutique appartient à la sélection
            if ($shopAdded == true
                || count($objTNTContextAccountModel->getPSShopList()) == 0
            ) {
                $arrFormMessagesServiceTnt['error']['RG-13'] =
                    $this->l('At least one of the selected stores is already associated with this TNT service. To modify the associated pricing, please use the list of TNT carriers in the box at the bottom of the page. Otherwise please modify the selection of shops and try again.');
            }

            if (count($arrFormMessagesServiceTnt['error']) === 0) {
                $arrCarrierCreated = $objTNTContextAccountModel->createCarrier(
                    $arrCarrierCreate[0],
                    $arrCarrierCreate[1],
                    $arrCarrierCreate[2],
                    $arrCarrierCreate[3]
                );
                if (is_array($arrCarrierCreated)) {
                    $strInitCarrierJSON = Tools::jsonEncode($arrCarrierCreated);
                    $strInitCarrierEncode = TNTOfficiel_Tools::B64URLDeflate($strInitCarrierJSON);

                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminCarrierSetting', false)
                        .'&id_carrier='.$arrCarrierCreated[0]
                        .'&updatecarrier&init_carrier='.urlencode($strInitCarrierEncode)
                        .'&token='.Tools::getAdminTokenLite('AdminCarrierSetting')
                    );
                }
            }
        }

        return array(
            'input' => array(
                array(
                    'type' => 'html',
                    'name' => 'html_data',
                    'html_content' => implode('', TNTOfficiel_Tools::getAlertHTML($arrFormMessagesServiceTnt))
                        .'<div><i class="icon-cogs"></i> <strong>'
                        .sprintf(
                            $this->l('Configuration: Create a new %s delivery service for selected shops'),
                            TNTOfficiel::CARRIER_NAME
                        )
                        .'</strong></div>',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Select the associated TNT service :'),
                    'name' => 'TNTOFFICIEL_SERVICE_TNT',
                    'class_label' => "col-lg-5",
                    'class_type' => "col-lg-8 col-md-10 col-xs-12",
                    'required' => true,
                    'options' => array(
                        'query' => $objAllCarrierAvailabilities,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
            ),
            'message' => $arrFormMessagesServiceTnt,
        );
    }

    /**
     * Get the Price list form data for Helper.
     *
     * @return array
     */
    private function getFormPriceList($strArgIDFormPriceZone, &$arrRefFieldsValue)
    {
        TNTOfficiel_Logstack::log();

        $arrFormMessagesZones = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        // RG-13
        if (/*!(
            //Aucun transporteur n'existe déjà pour ce service de livraison pour les boutiques sélectionnées
            $shopAdded == true ||
            //Au moins une boutique appartient à la sélection
            count($objTNTContextAccountModel->getPSShopList()) == 0
            )
            // Et en mode création
            &&*/
            Tools::getValue('init_carrier')
        ) { //RG-13
            $arrFormMessagesZones['success']['save'] =
                $this->l('The carrier (s) are well established, thank you for entering the pricing for this / these new carriers');
        }
        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID(Tools::getValue('id_carrier'));

        // Input values.
        $strArgSpecificPrice = pSQL(Tools::getValue('TNTOFFICIEL_ZONES_ENABLED'));
        $strArgCloningSpecificPrice = pSQL(Tools::getValue('TNTOFFICIEL_ZONES_CLONING_ENABLED'));
        $arrZonesConfigPost = array_intersect_key(
            (array)Tools::getValue('TNTOFFICIEL_ZONES_CONF'),
            array(array(), array(), array())
        );

        // Displayed values.
        $arrRefFieldsValue['TNTOFFICIEL_ZONES_ENABLED'] = $objTNTCarrierModel->isZonesEnabled();
        $arrRefFieldsValue['TNTOFFICIEL_ZONES_CLONING_ENABLED'] = $objTNTCarrierModel->isZonesCloningEnabled();
        $arrRefFieldsValue['arrZonesConfList'] = $objTNTCarrierModel->getZonesConf();

        if (Tools::isSubmit($strArgIDFormPriceZone)) {
            // Display ZonesConf
            if ($strArgSpecificPrice) {
                foreach ($arrZonesConfigPost as $intZoneConfID => &$arrZoneConf) {
                    $arrMessagesZonesKeyLabel = array(
                        0 => 'Zone par défaut',
                        1 => 'Zone tarifaire 1',
                        2 => 'Zone tarifaire 2',
                    );
                    $strZoneKeyLabel = $arrMessagesZonesKeyLabel[$intZoneConfID];

                    $strRangeType = pSQL($arrZoneConf['strRangeType']);
                    $fltRangeWeightPricePerKg = pSQL($arrZoneConf['fltRangeWeightPricePerKg']);
                    $fltRangeWeightLimitMax = pSQL($arrZoneConf['fltRangeWeightLimitMax']);
                    $strOutOfRangeBehavior = pSQL($arrZoneConf['strOutOfRangeBehavior']);
                    $fltHRAAdditionalCost = pSQL($arrZoneConf['fltHRAAdditionalCost']);
                    $fltMarginPercent = pSQL($arrZoneConf['fltMarginPercent']);

                    $arrRangeWeightListCol1 = array();
                    if (array_key_exists('arrRangeWeightListCol1', $arrZoneConf)
                        && is_array($arrZoneConf['arrRangeWeightListCol1'])
                    ) {
                        $arrRangeWeightListCol1 = $arrZoneConf['arrRangeWeightListCol1'];
                    }
                    $arrRangeWeightListCol2 = array();
                    if (array_key_exists('arrRangeWeightListCol2', $arrZoneConf)
                        && is_array($arrZoneConf['arrRangeWeightListCol2'])
                    ) {
                        $arrRangeWeightListCol2 = $arrZoneConf['arrRangeWeightListCol2'];
                    }

                    $arrRangePriceListCol1 = array();
                    if (array_key_exists('arrRangePriceListCol1', $arrZoneConf)
                        && is_array($arrZoneConf['arrRangePriceListCol1'])
                    ) {
                        $arrRangePriceListCol1 = $arrZoneConf['arrRangePriceListCol1'];
                    }
                    $arrRangePriceListCol2 = array();
                    if (array_key_exists('arrRangePriceListCol2', $arrZoneConf)
                        && is_array($arrZoneConf['arrRangePriceListCol2'])
                    ) {
                        $arrRangePriceListCol2 = $arrZoneConf['arrRangePriceListCol2'];
                    }

                    $arrZoneConf['arrRangeWeightList'] = array_combine(
                        $arrRangeWeightListCol1,
                        $arrRangeWeightListCol2
                    );
                    $arrZoneConf['arrRangePriceList'] = array_combine(
                        $arrRangePriceListCol1,
                        $arrRangePriceListCol2
                    );

                    $arrRangeWeightList = array();
                    $arrRangePriceList = array();

                    if ($strRangeType == 'weight') {
                        // RG-26 remove lines too much
                        foreach ($arrRangeWeightListCol1 as $key => $weightCol1) {
                            if ($weightCol1 == '' and $arrRangeWeightListCol2[$key] == '') {
                                unset($arrRangeWeightListCol1[$key]);
                                unset($arrRangeWeightListCol2[$key]);
                            }
                        }
                        if ($arrRangeWeightListCol1
                            && $arrRangeWeightListCol2
                        ) {
                            $arrRangeWeightList = array_combine($arrRangeWeightListCol1, $arrRangeWeightListCol2);
                        }
                    } else {
                        // RG-26 remove lines too much
                        foreach ($arrRangePriceListCol1 as $key => $weightCol1) {
                            if ($weightCol1 == '' and $arrRangePriceListCol2[$key] == '') {
                                unset($arrRangePriceListCol1[$key]);
                                unset($arrRangePriceListCol2[$key]);
                            }
                        }
                        if ($arrRangePriceListCol1
                            && $arrRangePriceListCol2
                        ) {
                            $arrRangePriceList = array_combine($arrRangePriceListCol1, $arrRangePriceListCol2);
                        }
                    }

                    if ($intZoneConfID === 0) {
                        // Error message if specific price is enabled
                        // RG-24
                        if ($strRangeType == 'weight') {
                            $firstCol1 = $arrRangeWeightListCol1[0];
                            $firstCol2 = $arrRangeWeightListCol2[0];
                        } else {
                            $firstCol1 = $arrRangePriceListCol1[0];
                            $firstCol2 = $arrRangePriceListCol2[0];
                        }
                        if ((empty($firstCol1) and $firstCol1 != '0') or (empty($firstCol2) and $firstCol2 != '0')) {
                            $arrFormMessagesZones['error'][$strZoneKeyLabel]['list-required'] =
                                $this->l('At least one port cost range must be set for the default zone, please check the entered information.');
                        }
                        //------RG-24
                    }
                    if ($strRangeType == 'weight') {
                        $cols1 = $arrRangeWeightListCol1;
                        $cols2 = $arrRangeWeightListCol2;
                        // RG-19
                        if (!empty($fltRangeWeightPricePerKg)
                            and !(is_numeric($fltRangeWeightPricePerKg)
                                and (Tools::strlen(Tools::substr(strrchr($fltRangeWeightPricePerKg, "."), 7)) == 0)
                            )
                        ) {
                            $arrFormMessagesZones['error'][$strZoneKeyLabel]['RG-19-priceSupp'] =
                                $this->l('The "extra kilogram price" must be a number with up to 6 decimals, and using the point as a separator, please check the information entered.');
                        }
                        // RG-18
                        if (!empty($fltRangeWeightLimitMax)
                            and !(is_numeric($fltRangeWeightLimitMax)
                                and (Tools::strlen(Tools::substr(strrchr($fltRangeWeightLimitMax, "."), 2)) == 0)
                            )
                        ) {
                            $arrFormMessagesZones['error'][$strZoneKeyLabel]['RG-18-limite'] =
                                $this->l('The "limit" must be a number with 1 decimal place, and using the point as a separator, please check the information entered.');
                        }
                    } else {
                        $cols1 = $arrRangePriceListCol1;
                        $cols2 = $arrRangePriceListCol2;
                    }

                    // RG-19
                    if (!empty($fltHRAAdditionalCost)
                        and !(is_numeric($fltHRAAdditionalCost)
                            and (Tools::strlen(Tools::substr(strrchr($fltHRAAdditionalCost, "."), 7)) == 0)
                        )
                    ) {
                        $arrFormMessagesZones['error'][$strZoneKeyLabel]['RG-19-difficultArea'] =
                            $this->l('The field "Hard to reach areas" must be a number with 6 decimal places maximum, and using the point as a separator, please check the information entered.');
                    }
                    // RG-30
                    $arrHRAZipcodeList = TNTOfficielCarrier::getHRAZipCodeList();
                    if (!is_array($arrHRAZipcodeList) && !empty($fltHRAAdditionalCost)) {
                        $arrFormMessagesZones['error'][$strZoneKeyLabel]['RG-30-difficultArea'] =
                            $this->l('The file defining the Hard Access Zones can not be found, please click on the import button to download them.');
                    }
                    // RG-20
                    if (!empty($fltMarginPercent)
                        and !(is_numeric($fltMarginPercent)
                            and (Tools::strlen(Tools::substr(strrchr($fltMarginPercent, "."), 3)) == 0)
                            and $fltMarginPercent >= 0 and $fltMarginPercent <= 100
                        )
                    ) {
                        $arrFormMessagesZones['error'][$strZoneKeyLabel]['RG-20-marge'] =
                            $this->l('The field "additional margin" must be a positive number to two decimal places between 0 and 100, using the point as a decimal point, please check the information entered.');
                    }
                    foreach ($cols1 as $key => $col1) {
                        // RG-18
                        if ($strRangeType == 'weight'
                            and !empty($col1)
                            and !(is_numeric($col1) and (Tools::strlen(Tools::substr(strrchr($col1, "."), 2)) == 0))
                        ) {
                            $arrFormMessagesZones['error'][$strZoneKeyLabel]['RG-18-borne-superieure'] =
                                $this->l('The "upper bound" must be a number with 1 decimal place, and using the point as separator, please check the entered information.');
                        }
                        // RG-19
                        if ($strRangeType == 'price'
                            and !empty($col1)
                            and !(is_numeric($col1) and (Tools::strlen(Tools::substr(strrchr($col1, "."), 7)) == 0))
                        ) {
                            $arrFormMessagesZones['error'][$strZoneKeyLabel]['RG-19-borne-superieure'] =
                                $this->l('The "upper bound" must be a number with up to 6 decimal places, and using the point as a separator, please check the entered information.');
                        }
                        // RG-25
                        if (empty($col1)
                            and $col1 != '0'
                            and !empty($cols2[$key])
                        ) {
                            $arrFormMessagesZones['error'][$strZoneKeyLabel]['list-required'] =
                                $this->l('At least one port cost range is not completely entered, please check the information entered.');
                        }
                    }
                    foreach ($cols2 as $key => $col2) {
                        // RG-19
                        if (!empty($col2)
                            and !(is_numeric($col2) and (Tools::strlen(Tools::substr(strrchr($col2, "."), 7)) == 0))
                        ) {
                            $arrFormMessagesZones['error'][$strZoneKeyLabel]['RG-19-prix'] =
                                $this->l('The "price" must be a number with up to 6 decimals, and using the point as a separator, please check the information entered.');
                        }
                        // RG-25
                        if (empty($col2)
                            and $col2 != '0'
                            and !empty($cols1[$key])
                        ) {
                            $arrFormMessagesZones['error'][$strZoneKeyLabel]['list-required'] =
                                $this->l('At least one port cost range is not completely entered, please check the information entered.');
                            //break;
                        }
                    }

                    // RG-29
                    //to delete empty lines
                    $cols1Values = array_values(array_filter($cols1));
                    $sortedCols1 = $cols1Values;
                    sort($sortedCols1);
                    // if list cols1 not null and not sorted or have same value
                    if ($cols1 and $cols1Values !== $sortedCols1 or !(array_unique($cols1) == $cols1)) {
                        $arrFormMessagesZones['error'][$strZoneKeyLabel]['RG-29'] =
                            $this->l('Shipping cost ranges are not entered in ascending order, please check the information entered.');
                    }
                    //-----RG-29

                    // No error, then save zone config.
                    if (!array_key_exists($strZoneKeyLabel, $arrFormMessagesZones['error'])
                        || count($arrFormMessagesZones['error'][$strZoneKeyLabel]) === 0
                    ) {
                        // foreach multi shop save all zones (3zones)
                        $strInitCarrierEncode = (string)Tools::getValue('init_carrier');
                        $strInitCarrierJSON = TNTOfficiel_Tools::B64URLInflate($strInitCarrierEncode);
                        $arrInitCarrier = Tools::jsonDecode($strInitCarrierJSON, true);
                        // Add
                        if ($arrInitCarrier) {
                            foreach ($arrInitCarrier as $intCarrierID) {
                                $objTNTCarrierInitModel = TNTOfficielCarrier::loadCarrierID($intCarrierID);
                                $objTNTCarrierInitModel->setZoneRangeType($intZoneConfID, $strRangeType);
                                $objTNTCarrierInitModel->setZoneRangeWeightList(
                                    $intZoneConfID,
                                    $arrRangeWeightList,
                                    $fltRangeWeightPricePerKg,
                                    $fltRangeWeightLimitMax
                                );
                                $objTNTCarrierInitModel->setZoneRangePriceList($intZoneConfID, $arrRangePriceList);
                                $objTNTCarrierInitModel->setZoneOutOfRangeBehavior(
                                    $intZoneConfID,
                                    $strOutOfRangeBehavior
                                );
                                $objTNTCarrierInitModel->setZoneHRAAdditionalCost(
                                    $intZoneConfID,
                                    $fltHRAAdditionalCost
                                );
                                $objTNTCarrierInitModel->setZoneMarginPercent($intZoneConfID, $fltMarginPercent);
                            }
                        } else {
                            // Add for a single shop or edit without cloning
                            $objTNTCarrierModel->setZoneRangeType($intZoneConfID, $strRangeType);
                            if ($strRangeType == 'weight') {
                                $objTNTCarrierModel->setZoneRangeWeightList(
                                    $intZoneConfID,
                                    $arrRangeWeightList,
                                    $fltRangeWeightPricePerKg,
                                    $fltRangeWeightLimitMax
                                );
                            } else {
                                $objTNTCarrierModel->setZoneRangePriceList($intZoneConfID, $arrRangePriceList);
                            }
                            $objTNTCarrierModel->setZoneOutOfRangeBehavior($intZoneConfID, $strOutOfRangeBehavior);
                            $objTNTCarrierModel->setZoneHRAAdditionalCost($intZoneConfID, $fltHRAAdditionalCost);
                            $objTNTCarrierModel->setZoneMarginPercent($intZoneConfID, $fltMarginPercent);
                            // Cloning
                            $arrCarriersSelected = Tools::getValue('carriersSelected');
                            if (is_array($arrCarriersSelected)) {
                                foreach ($arrCarriersSelected as $intCarrierID) {
                                    $objTNTCarrierSelectedModel = TNTOfficielCarrier::loadCarrierID($intCarrierID);
                                    $objTNTCarrierSelectedModel->setZoneRangeType($intZoneConfID, $strRangeType);
                                    if ($strRangeType == 'weight') {
                                        $objTNTCarrierSelectedModel->setZoneRangeWeightList(
                                            $intZoneConfID,
                                            $arrRangeWeightList,
                                            $fltRangeWeightPricePerKg,
                                            $fltRangeWeightLimitMax
                                        );
                                    } else {
                                        $objTNTCarrierSelectedModel->setZoneRangePriceList(
                                            $intZoneConfID,
                                            $arrRangePriceList
                                        );
                                    }
                                    $objTNTCarrierSelectedModel->setZoneOutOfRangeBehavior(
                                        $intZoneConfID,
                                        $strOutOfRangeBehavior
                                    );
                                    $objTNTCarrierSelectedModel->setZoneHRAAdditionalCost(
                                        $intZoneConfID,
                                        $fltHRAAdditionalCost
                                    );
                                    $objTNTCarrierSelectedModel->setZoneMarginPercent(
                                        $intZoneConfID,
                                        $fltMarginPercent
                                    );
                                }
                            }
                        }
                    }
                }
            }
            $arrRefFieldsValue['TNTOFFICIEL_ZONES_ENABLED'] = $strArgSpecificPrice;
            $arrRefFieldsValue['TNTOFFICIEL_ZONES_CLONING_ENABLED'] = $strArgCloningSpecificPrice;
            $arrRefFieldsValue['arrZonesConfList'] = $arrZonesConfigPost;

            if (count($arrFormMessagesZones['error']) === 0) {
                // foreach multi shop save zonesEnabled
                $strInitCarrierEncode = (string)Tools::getValue('init_carrier');
                $strInitCarrierJSON = TNTOfficiel_Tools::B64URLInflate($strInitCarrierEncode);
                $arrInitCarrier = Tools::jsonDecode($strInitCarrierJSON, true);
                if ($arrInitCarrier) {
                    foreach ($arrInitCarrier as $intCarrierID) {
                        $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($intCarrierID);
                        $objTNTCarrierModel->setZonesEnabled($strArgSpecificPrice);
                        $objTNTCarrierModel->setZonesCloningEnabled($strArgCloningSpecificPrice);
                    }
                } else {
                    $objTNTCarrierModel->setZonesEnabled($strArgSpecificPrice);
                    $objTNTCarrierModel->setZonesCloningEnabled($strArgCloningSpecificPrice);
                }

                $arrFormMessagesZones['success']['save'] = $this->l('The data is correctly saved.');
            }
        }
        // 6.3.3.1
        $arrFormMessagesZones['info'][] =
            $this->l('If any of the items ordered online has a weight greater than 20 kg, TNT delivery services will not be offered for deliveries to home and to a partner merchant.');
        $arrFormMessagesZones['info'][] =
            $this->l('If any of the items ordered online weighs more than 30 kg, DTT delivery services will not be offered.');

        if (count($arrFormMessagesZones['error']) > 0) {
            unset($arrFormMessagesZones['success']);
        }

        // foreach multi shop get Carrier model name
        $strInitCarrierEncode = (string)Tools::getValue('init_carrier');
        $strInitCarrierJSON = TNTOfficiel_Tools::B64URLInflate($strInitCarrierEncode);
        $arrInitCarrier = Tools::jsonDecode($strInitCarrierJSON, true);
        if (!$arrInitCarrier) {
            $arrInitCarrier = (array)Tools::getValue('id_carrier');
        }

        $arrCarrierInfoList = array();
        $arrZonesInfoList = array(
            'showTab' => array(
                '0' => false,
                '1' => false,
                '2' => false,
            ),
            'html' => array(
                '0' => '',
                '1' => '',
                '2' => '',
            ),
            'shop' => array(),
        );

        if ($arrInitCarrier) {
            foreach ($arrInitCarrier as $intCarrierID) {
                $objTNTCarrierModel = TNTOfficielCarrier::loadCarrierID($intCarrierID, false);
                $strShopName = $objTNTCarrierModel->getPSShop()->name;
                $arrCarrierInfoList[$intCarrierID] = array(
                    'shop' => $strShopName,
                    'carrier' => $objTNTCarrierModel->getCarrierInfos()->label,
                    'tax' => $objTNTCarrierModel->getTaxInfos(),
                );

                $objTNTAccountModel = $objTNTCarrierModel->getTNTAccountModel();
                if (!array_key_exists($strShopName, $arrZonesInfoList['shop'])) {
                    $arrZonesInfoList['shop'][$strShopName] = array(
                        '0' => $objTNTAccountModel->getZoneDefaultDepartments(),
                        '1' => $objTNTAccountModel->getZone1Departments(),
                        '2' => $objTNTAccountModel->getZone2Departments(),
                    );
                    foreach ($arrZonesInfoList['shop'][$strShopName] as $z => $av) {
                        foreach ($av as $dn => $di) {
                            $arrZonesInfoList['shop'][$strShopName][$z][$dn] = sprintf('%s (%s)', $dn, $di);
                        }
                        $arrZonesInfoList['shop'][$strShopName][$z] = implode(', ', $arrZonesInfoList['shop'][$strShopName][$z]);
                    }
                }
            }
            foreach ($arrZonesInfoList['shop'] as $strShopName => $arrZoneInfoShop) {
                foreach ($arrZoneInfoShop as $z => $sv) {
                    $arrZonesInfoList['showTab'][$z] = $arrZonesInfoList['showTab'][$z] || ($sv !== '');
                }
            }

            foreach ($arrZonesInfoList['shop'] as $strShopName => $arrZoneInfoShop) {
                foreach ($arrZoneInfoShop as $z => $sv) {
                    $arrZonesInfoList['html'][$z] .= '<li>'.sprintf(
                        '%s: %s',
                        '<b>'.$strShopName.'</b>',
                        (($sv !== '') ? $sv.'.' : '-')
                    ).'</li>';
                }
            }
        }

        // Pass trough fileds values
        $arrRefFieldsValue['arrZonesInfoList'] = $arrZonesInfoList;

        $strHTMLServices = '';
        foreach ($arrCarrierInfoList as $arrCarrierInfoItem) {
            $strHTMLTax = '<li>'.sprintf(
                $this->l('Taxes %s : The VAT applied in %s is %s (%s).'),
                '<b>'.$arrCarrierInfoItem['tax']['group'].'</b>',
                '<b>'.$arrCarrierInfoItem['tax']['country'].'</b>',
                '<b>'.number_format($arrCarrierInfoItem['tax']['rate'], 2, ',', ' ').'%'.'</b>',
                $arrCarrierInfoItem['tax']['name']
            ).'</li>';

            $strHTMLServices .= '<li>'.sprintf(
                $this->l('%s on %s :'),
                '<b>'.$arrCarrierInfoItem['carrier'].'</b>',
                '<b>'.$arrCarrierInfoItem['shop'].'</b>'
            ).'<ul>'.$strHTMLTax.'</ul>'.'</li>';
        }
        $strHTMLServices = '<ul>'.$strHTMLServices.'</ul>';

        return array(
            'input' => array(
                array(
                    'type' => 'html',
                    'name' => 'html_data',
                    'html_content' => implode('', TNTOfficiel_Tools::getAlertHTML($arrFormMessagesZones)),
                ),
                array(
                    'type' => 'html',
                    'name' => 'html_data',
                    'html_content' => '<div class="current-service">'
                        .$this->l('TNT service being modified').' :'.$strHTMLServices
                        .'</div>',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use the specific TNT grid for setting the tarification rate?'),
                    'name' => 'TNTOFFICIEL_ZONES_ENABLED',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Apply the pricing of this service to other shops and/or other TNT services?'),
                    'name' => 'TNTOFFICIEL_ZONES_CLONING_ENABLED',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ),
                    ),
                ),
            ),
            'message' => $arrFormMessagesZones,
        );
    }

    /**
     * @param $productA
     * @param $productB
     *
     * @return int
     */
    public static function cmp($productA, $productB)
    {
        TNTOfficiel_Logstack::log();

        return strcmp($productA["shop"], $productB["shop"]);
    }

    /**
     * @param $productA
     * @param $productB
     *
     * @return int
     */
    public static function cmpService($productA, $productB)
    {
        TNTOfficiel_Logstack::log();

        return strcmp($productA["service_label"], $productB["service_label"]);
    }

    public function renderForm()
    {
        TNTOfficiel_Logstack::log();

        $objHelperForm = new HelperForm();
        $arrFieldsValue = array();

        /*
         * Create Price Zone Form
         */

        // Form Structure used as parameter for Helper 'generateForm' method.
        $arrFormStruct = array();

        $strIDFormPriceZone = 'submit'.TNTOfficiel::MODULE_NAME.'CarrierCreate';
        $arrFormPriceList = $this->getFormPriceList($strIDFormPriceZone, $arrFieldsValue);
        $arrFormInputZoneOnglet = $arrFormPriceList['input'];

        //$objHelperForm->base_folder = 'helpers/form/';
        $objHelperForm->base_tpl = 'AdminPriceSetting.tpl';

        // Module using this form.
        $objHelperForm->module = $this->module;
        // Controller name.
        $objHelperForm->name_controller = TNTOfficiel::MODULE_NAME;
        // Token.
        $objHelperForm->token = Tools::getAdminTokenLite('AdminCarrierSetting');
        // Form action attribute.
        // Input values.
        $objHelperForm->currentIndex = AdminController::$currentIndex
            .'&id_carrier='.Tools::getValue('id_carrier')
            .'&updatecarrier'
            .'&init_carrier='.Tools::getValue('init_carrier');


        // Language.
        $objHelperForm->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $objHelperForm->allow_employee_form_lang = (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') :
            0
        );

        $arrCarrierCloningList = array();
        // In edit mode return list carrier except the current carrier (edited carrier)
        if (!(Tools::getValue('init_carrier'))) {
            $arrCarrierCloningList = TNTOfficielCarrier::getContextCarrierModelList();
            unset($arrCarrierCloningList[Tools::getValue('id_carrier')]);
        }

        $arrCarrierCloningTableList = array();
        $arrCarriersSelected = Tools::getValue('carriersSelected');
        if (!is_array($arrCarriersSelected)) {
            $arrCarriersSelected = array();
        }
        foreach ($arrCarrierCloningList as $intCarrierID => $objTNTCarrierModel) {
            $objPSShop = $objTNTCarrierModel->getPSShop();
            $arrCarrierCloningTableList[] = array(
                'checkedValue' => in_array($intCarrierID, $arrCarriersSelected),
                'carrier_id' => $intCarrierID,
                'service_label' => $objTNTCarrierModel->getCarrierInfos()->label,
                'carrier_name' => $objTNTCarrierModel->getName(),
                'shop' => $objPSShop->name,
            );
        }

        // CC triée sur la boutique puis sur le service TNT.
        usort($arrCarrierCloningTableList, array(__CLASS__, 'cmpService'));
        usort($arrCarrierCloningTableList, array(__CLASS__, 'cmp'));

        $arrFormStruct[$strIDFormPriceZone] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Pricing Setting for Delivery Services'),
                ),
                'input' => $arrFormInputZoneOnglet,
                'buttons' => array(
                    'back' => array(
                        'title' => $this->l('Back to list'),
                        'href' => $this->context->link->getAdminLink('AdminCarrierSetting'),
                        'icon' => 'process-icon-back',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => $strIDFormPriceZone,
                ),
            ),
        );

        // /modules/<MODULE>/views/templates/admin/_configure/helpers/form/form.tpl
        // extends /<ADMIN>/themes/default/template/helpers/form/form.tpl
        $objHelperForm->tpl_vars['tntofficiel'] = array(
            'arrZonesConfList' => $arrFieldsValue['arrZonesConfList'],
            'arrZonesInfoList' => $arrFieldsValue['arrZonesInfoList'],
            'arrCarrierCloningTableList' => $arrCarrierCloningTableList,
        );

        // Set all form fields values.
        $objHelperForm->fields_value = $arrFieldsValue;

        // Global Submit ID.
        //$objHelperForm->submit_action = 'submit'.TNTOfficiel::MODULE_NAME;
        // Get generated forms.
        $strZoneForms = $objHelperForm->generateForm($arrFormStruct);


        /*
         * Disabled carrier.
         */

        $arrObjTNTCarrierModelList = TNTOfficielCarrier::getContextCarrierModelList();

        $arrFormMessagesCarriers = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        $arrCarrierDisabled = array();

        foreach ($arrObjTNTCarrierModelList as $intCarrierID => $objTNTCarrierModel) {
            $objPSCarrier = TNTOfficielCarrier::getPSCarrier($intCarrierID);
            // If carrier object available and not active.
            if ($objPSCarrier !== null && !$objPSCarrier->active) {
                $arrCarrierDisabled[] = $intCarrierID;
            }
        }

        if (count($arrCarrierDisabled) > 0) {
            $arrFormMessagesCarriers['warning'][] = sprintf(
                $this->l('Disabled carrier(s) : %s.'),
                'ID '.implode(', ', $arrCarrierDisabled)
            );
        }

        return $strZoneForms.parent::renderForm();
    }
}
