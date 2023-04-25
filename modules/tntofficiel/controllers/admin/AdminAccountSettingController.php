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
 * Class AdminAccountSettingController
 */
class AdminAccountSettingController extends ModuleAdminController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        // Bootstrap enable.
        $this->bootstrap = true;

        parent::__construct();

        $this->page_header_toolbar_title = sprintf($this->l('Configure %s'), TNTOfficiel::CARRIER_NAME);
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

        $this->module->addJS('AdminAccountSetting.js');
    }

    /**
     * Display page.
     */
    public function renderList()
    {
        TNTOfficiel_Logstack::log();

        parent::renderList();

        // Display warning message in the module list for weight unit.
        if (Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT')) !== 'kg') {
            $this->warnings[] = sprintf(
                $this->l('The supported weight unit is \'kg\', but is currently \'%s\'.'),
                Configuration::get('PS_WEIGHT_UNIT')
            );
        }

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
        $objHelperForm->base_tpl = 'AdminAccountSetting.tpl';

        // Module using this form.
        $objHelperForm->module = $this->module;
        // Controller name.
        $objHelperForm->name_controller = TNTOfficiel::MODULE_NAME;
        // Token.
        $objHelperForm->token = Tools::getAdminTokenLite('AdminAccountSetting');
        // Form action attribute.
        $objHelperForm->currentIndex = AdminController::$currentIndex.'&configure='.TNTOfficiel::MODULE_NAME;

        // Language.
        $objHelperForm->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $objHelperForm->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;


        // Smarty assign().
        // /modules/<MODULE>/views/templates/admin/_configure/helpers/form/form.tpl
        // extends /<ADMIN>/themes/default/template/helpers/form/form.tpl
        $objHelperForm->tpl_vars['tntofficiel'] = array(
            'srcTNTLogoImage' => $this->module->getPathUri().'views/img/logo/500x100.png',
            'hrefExportLog' => $this->context->link->getAdminLink('AdminTNTOrders').'&action=downloadLogs',
            'langExportLog' => $this->l('Export logs'),
            'hrefManualPDF' => 'http://www.tnt.fr/Telechargements/cit/manuel-prestashop-1.7.pdf',
            // $this->module->getPathUri().'manuel-prestashop.pdf',
            'langManualPDF' => $this->l('Installation manual'),
        );

        /*
         * Configuration Form
         */

        $arrFormInputAccountConfig = array();
        $arrAllMessageList = array();

        $arrFormHR = array(
            array(
                'type' => 'html',
                'name' => 'html_data',
                'html_content' => '<div class="col-md-12"><hr /></div>',
            ),
        );

        $strIDFormAccountConfig = 'submit'.TNTOfficiel::MODULE_NAME.'AccountConfig';

        $boolWasValidated = $objTNTContextAccountModel->getAuthValidatedDateTime();
        // Form auth.
        $arrFormAuth = $this->getFormAccountAuth($strIDFormAccountConfig, $arrFieldsValue);

        $boolNowValidated = $objTNTContextAccountModel->getAuthValidatedDateTime();

        // If submit and auth change to validated, do not POST empty field for the displayed section.
        if (Tools::isSubmit($strIDFormAccountConfig)
            && $boolWasValidated === null && $boolNowValidated !== null
        ) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminAccountSetting'));
            exit;
        }

        $arrFormInputAccountConfig = array_merge($arrFormInputAccountConfig, $arrFormAuth['input']);
        $arrAllMessageList[] = $arrFormAuth['message'];

        if ($boolNowValidated !== null) {
            // Form sender.
            $arrFormSender = $this->getFormAccountSender($strIDFormAccountConfig, $arrFieldsValue);
            // Form Pickup.
            $arrFormPickup = $this->getFormAccountPickup($strIDFormAccountConfig, $arrFieldsValue);
            // Form Zone.
            $arrFormZone = $this->getFormAccountZone($strIDFormAccountConfig, $arrFieldsValue);
            // Form OrderState.
            $arrFormOrderState = $this->getFormAccountOrderState($strIDFormAccountConfig, $arrFieldsValue);

            $arrFormInputAccountConfig = array_merge(
                $arrFormInputAccountConfig,
                $arrFormHR,
                $arrFormSender['input'],
                $arrFormHR,
                $arrFormPickup['input'],
                $arrFormHR,
                $arrFormZone['input'],
                $arrFormHR,
                $arrFormOrderState['input']
            );

            $arrAllMessageList[] = $arrFormSender['message'];
            $arrAllMessageList[] = $arrFormPickup['message'];
            $arrAllMessageList[] = $arrFormZone['message'];
            $arrAllMessageList[] = $arrFormOrderState['message'];
        }

        if (Shop::getContext() === Shop::CONTEXT_SHOP) {
            $arrFormInputContext = array(
                array(
                    'type' => 'html',
                    'name' => 'html_data',
                    'html_content' => '<input type="hidden" name="AdminConfigContextShop" value="1" />',
                ),
            );

            $arrFormInputAccountConfig = array_merge(
                $arrFormInputAccountConfig,
                $arrFormInputContext
            );
        }

        $arrFormRequiredAccountConfig = array();
        $arrFormRequiredAccountConfigLabel = array();
        if (Tools::isSubmit($strIDFormAccountConfig)) {
            foreach ($arrFormInputAccountConfig as $arrField) {
                if (array_key_exists('required', $arrField) && $arrField['required'] === true) {
                    if (array_key_exists($arrField['name'], $arrFieldsValue)
                        && $arrFieldsValue[$arrField['name']] === ''
                    ) {
                        // Red highlight if error on field.
                        $arrFormRequiredAccountConfig[] = $arrField['name'];
                        // List required fields names for error message.
                        $arrFormRequiredAccountConfigLabel[] = $arrField['label'];
                    }
                }
            }

            foreach ($arrAllMessageList as $arrErrorCopy) {
                foreach ($arrErrorCopy['error'] as $mxdFieldName => $strErrorMsg) {
                    if (is_string($mxdFieldName)) {
                        // Red highlight if error on field.
                        $arrFormRequiredAccountConfig[] = $mxdFieldName;
                    }
                }
            }
        }

        $objHelperForm->tpl_vars['tntofficiel'] += array(
            'errorFields' => $arrFormRequiredAccountConfig,
        );

        /*
         * Merge messages.
         */

        $arrFormMessageAccountConfig = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        if (Tools::isSubmit($strIDFormAccountConfig)) {
            foreach ($arrFormMessageAccountConfig as $strType => $arrValue) {
                foreach ($arrAllMessageList as $arrAlertCopy) {
                    if (array_key_exists($strType, $arrAlertCopy)) {
                        foreach ($arrAlertCopy[$strType] as $mxdFieldName => $strValueCopy) {
                            if (is_string($mxdFieldName)) {
                                $arrFormMessageAccountConfig[$strType][$mxdFieldName] = $strValueCopy;
                            } else {
                                $arrFormMessageAccountConfig[$strType][] = $strValueCopy;
                            }
                        }
                    }
                }
            }

            // Add required message.
            if (count($arrFormRequiredAccountConfigLabel) === 1) {
                $arrFormMessageAccountConfig['error'][$arrFormRequiredAccountConfig[0]] = sprintf(
                    $this->l('The field "%s" is mandatory, please check the information entered.'),
                    implode(', ', $arrFormRequiredAccountConfigLabel)
                );
            } elseif (count($arrFormRequiredAccountConfigLabel) > 1) {
                $arrFormMessageAccountConfig['error'][] = sprintf(
                    $this->l('The fields "%s" are obligatory, please check the entered information.'),
                    implode(', ', $arrFormRequiredAccountConfigLabel)
                );
            }
        }

        if (count($arrFormMessageAccountConfig['error']) > 0) {
            // If error, do not display success.
            unset($arrFormMessageAccountConfig['success']);
        } elseif (count($arrFormMessageAccountConfig['success']) > 0) {
            // Use only one common success message.
            $arrFormMessageAccountConfig['success'] = array(
                $this->l('Settings updated.'),
            );
        }

        $arrFormMessageAccountConfig['info'][] =
            $this->l('Before entering the configuration data for your module, be sure to have selected the context and the stores on which you want to apply this configuration.');

        // HTML Formatting.
        $arrFormAccountConfigMessageInput = array();
        $arrFormAccountConfigMessageHTML = TNTOfficiel_Tools::getAlertHTML($arrFormMessageAccountConfig);
        if (count($arrFormAccountConfigMessageHTML) > 0) {
            $arrFormAccountConfigMessageInput = array(
                array(
                    'type' => 'html',
                    'name' => implode('', $arrFormAccountConfigMessageHTML),
                ),
            );
        }

        // Add form.
        $arrFormStruct[$strIDFormAccountConfig] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('MERCHANT ACCOUNT SETTING'),
                ),
                'input' => array_merge($arrFormAccountConfigMessageInput, $arrFormInputAccountConfig),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => $strIDFormAccountConfig,
                ),
            ),
        );

        // Set all form fields values.
        $objHelperForm->fields_value = $arrFieldsValue;

        // Global Submit ID.
        //$objHelperForm->submit_action = 'submit'.TNTOfficiel::MODULE_NAME;
        // Get generated forms.
        $strDisplayForms = $objHelperForm->generateForm($arrFormStruct);

        $this->content = $strDisplayForms;

        /*
         * HRA (Footer)
         */

        return '';
    }

    /**
     * Get the Account Auth form data for Helper.
     *
     * @return array
     */
    private function getFormAccountAuth($strArgIDForm, &$arrRefFieldsValue)
    {
        TNTOfficiel_Logstack::log();

        $arrFormMessagesAuth = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        // Input values.
        // ObjectModel self escape data with formatValue(). Do not double escape using pSQL.
        $strArgAccountNumber = (string)Tools::getValue('TNTOFFICIEL_ACCOUNT_NUMBER');
        $strArgAccountLogin = (string)Tools::getValue('TNTOFFICIEL_ACCOUNT_LOGIN');
        $strArgAccountPassword = (string)Tools::getValue('TNTOFFICIEL_ACCOUNT_PASSWORD');

        // Displayed values.
        $arrRefFieldsValue['TNTOFFICIEL_ACCOUNT_NUMBER'] = $objTNTContextAccountModel->account_number;
        $arrRefFieldsValue['TNTOFFICIEL_ACCOUNT_LOGIN'] = $objTNTContextAccountModel->account_login;
        $arrRefFieldsValue['TNTOFFICIEL_ACCOUNT_PASSWORD'] = TNTOfficielAccount::PASSWORD_REPLACE;
        if (Tools::isSubmit($strArgIDForm)) {
            $arrRefFieldsValue['TNTOFFICIEL_ACCOUNT_NUMBER'] = $strArgAccountNumber;
            $arrRefFieldsValue['TNTOFFICIEL_ACCOUNT_LOGIN'] = $strArgAccountLogin;
        }

        // If store request (else simple check).
        if (Tools::isSubmit($strArgIDForm)
            // and account invalid or change are made in form.
            && ($objTNTContextAccountModel->getAuthValidatedDateTime() === null
                || ($objTNTContextAccountModel->account_number !== $strArgAccountNumber
                    || $objTNTContextAccountModel->account_login !== $strArgAccountLogin
                    || ($objTNTContextAccountModel->getAccountPassword() !== sha1($strArgAccountPassword)
                        && $strArgAccountPassword !== TNTOfficielAccount::PASSWORD_REPLACE
                    )
                )
            )
        ) {
            /*
             * Save
             */

            // If not empty, save account for validation request.
            if ($strArgAccountNumber && $strArgAccountLogin && $strArgAccountPassword) {
                $objTNTContextAccountModel->setAccountLogin($strArgAccountLogin);
                $objTNTContextAccountModel->setAccountNumber($strArgAccountNumber);
                if ($strArgAccountPassword !== TNTOfficielAccount::PASSWORD_REPLACE) {
                    $objTNTContextAccountModel->setAccountPassword($strArgAccountPassword);
                }
                // Validate the TNT credentials.
                $mxdStateValidation = $objTNTContextAccountModel->updateAuthValidation();
                if ($mxdStateValidation === null) {
                    $arrFormMessagesAuth['warning'][] = $this->l('A connection error occurred.');
                } elseif (!$mxdStateValidation) {
                    $arrFormMessagesAuth['error'][] =
                        $this->l('The "Login myTNT", "MyTNT password" and "TNT account number" identifiers are not recognized by TNT, please check the information entered.');
                } else {
                    // Save and also for each shop in account context.
                    $objTNTContextAccountModel->saveContextShop();
                    $arrFormMessagesAuth['success'][] = $this->l('Account Settings updated.');
                }
            }
        } else {
            $mxdStateValidation = $objTNTContextAccountModel->updateAuthValidation();
            if ($mxdStateValidation === null) {
                $arrFormMessagesAuth['warning'][] = $this->l('A connection error occurred.');
            } elseif (!$mxdStateValidation) {
                $arrFormMessagesAuth['error'][] =
                    $this->l('The "Login myTNT", "MyTNT password" and "TNT account number" identifiers are not recognized by TNT, please check the information entered.');
            }
        }

        return array(
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('TNT account number'),
                    'name' => 'TNTOFFICIEL_ACCOUNT_NUMBER',
                    'maxlength' => 8,
                    'size' => 6,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('MyTNT Login'),
                    'name' => 'TNTOFFICIEL_ACCOUNT_LOGIN',
                    'maxlength' => 128,
                    'size' => 6,
                    'required' => true,
                ),
                array(
                    'type' => 'password',
                    'label' => $this->l('MyTNT password'),
                    'name' => 'TNTOFFICIEL_ACCOUNT_PASSWORD',
                    'size' => 6,
                    'required' => true,
                ),
            ),
            'message' => $arrFormMessagesAuth,
        );
    }

    /**
     * Get the Account Sender form data for Helper.
     *
     * @return array
     */
    private function getFormAccountSender($strArgIDForm, &$arrRefFieldsValue)
    {
        TNTOfficiel_Logstack::log();

        $arrFormMessagesSender = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        // Input values.
        // ObjectModel self escape data with formatValue(). Do not double escape using pSQL.
        $strSenderCompany = TNTOfficiel_Tools::translitASCII((string)Tools::getValue('TNTOFFICIEL_SOCIETE'), 32);
        $strSenderAddress1 = TNTOfficiel_Tools::translitASCII((string)Tools::getValue('TNTOFFICIEL_ADRESSE_1'), 32);
        $strSenderAddress2 = TNTOfficiel_Tools::translitASCII((string)Tools::getValue('TNTOFFICIEL_ADRESSE_2'), 32);
        $strSenderZipCode = trim((string)Tools::getValue('TNTOFFICIEL_CODE_POSTAL'));
        $strSenderCity = trim((string)Tools::getValue('TNTOFFICIEL_VILLE'));
        $strSenderFirstName = TNTOfficiel_Tools::translitASCII((string)Tools::getValue('TNTOFFICIEL_PRENOM'), 32);
        $strSenderLastName = TNTOfficiel_Tools::translitASCII((string)Tools::getValue('TNTOFFICIEL_NOM'), 32);
        $strSenderEmail = trim((string)Tools::getValue('TNTOFFICIEL_MAIL'));
        $strSenderPhone = trim((string)Tools::getValue('TNTOFFICIEL_TELEPHONE'));

        // Displayed values.
        $arrRefFieldsValue['TNTOFFICIEL_SOCIETE'] = $objTNTContextAccountModel->sender_company;
        $arrRefFieldsValue['TNTOFFICIEL_ADRESSE_1'] = $objTNTContextAccountModel->sender_address1;
        $arrRefFieldsValue['TNTOFFICIEL_ADRESSE_2'] = $objTNTContextAccountModel->sender_address2;
        $arrRefFieldsValue['TNTOFFICIEL_CODE_POSTAL'] = $objTNTContextAccountModel->sender_zipcode;
        $arrRefFieldsValue['TNTOFFICIEL_VILLE'] = $objTNTContextAccountModel->sender_city;
        $arrRefFieldsValue['TNTOFFICIEL_PRENOM'] = $objTNTContextAccountModel->sender_firstname;
        $arrRefFieldsValue['TNTOFFICIEL_NOM'] = $objTNTContextAccountModel->sender_lastname;
        $arrRefFieldsValue['TNTOFFICIEL_MAIL'] = $objTNTContextAccountModel->sender_email;
        $arrRefFieldsValue['TNTOFFICIEL_TELEPHONE'] = $objTNTContextAccountModel->sender_phone;
        if (Tools::isSubmit($strArgIDForm)) {
            $arrRefFieldsValue['TNTOFFICIEL_SOCIETE'] = $strSenderCompany;
            $arrRefFieldsValue['TNTOFFICIEL_ADRESSE_1'] = $strSenderAddress1;
            $arrRefFieldsValue['TNTOFFICIEL_ADRESSE_2'] = $strSenderAddress2;
            $arrRefFieldsValue['TNTOFFICIEL_CODE_POSTAL'] = $strSenderZipCode;
            $arrRefFieldsValue['TNTOFFICIEL_VILLE'] = $strSenderCity;
            $arrRefFieldsValue['TNTOFFICIEL_PRENOM'] = $strSenderFirstName;
            $arrRefFieldsValue['TNTOFFICIEL_NOM'] = $strSenderLastName;
            $arrRefFieldsValue['TNTOFFICIEL_MAIL'] = $strSenderEmail;
            $arrRefFieldsValue['TNTOFFICIEL_TELEPHONE'] = $strSenderPhone;
        }

        /*
         * Validate the Sender and return error messages.
         */

        $arrResultCitiesGuide = $objTNTContextAccountModel->citiesGuide(
            'FR',
            $arrRefFieldsValue['TNTOFFICIEL_CODE_POSTAL'],
            $arrRefFieldsValue['TNTOFFICIEL_VILLE']
        );

        // If store request (else simple check).
        if (Tools::isSubmit($strArgIDForm)) {
            $arrResultCitiesGuide = $objTNTContextAccountModel->citiesGuide('FR', $strSenderZipCode, $strSenderCity);

            // Unsupported country or communication error is considered true to prevent
            // always invalid address form and show error "unknow postcode" on Front-Office checkout.
            $boolPostCodeIsValid = (!$arrResultCitiesGuide['boolIsCountrySupported']
                || $arrResultCitiesGuide['boolIsRequestComError']
                || count($arrResultCitiesGuide['arrCitiesNameList']) > 0
            );
            $boolCityIsValid = (!$arrResultCitiesGuide['boolIsCountrySupported']
                || $arrResultCitiesGuide['boolIsRequestComError']
                || $arrResultCitiesGuide['boolIsCityNameValid']
            );
            if ($strSenderZipCode && !$boolPostCodeIsValid) {
                $arrFormMessagesSender['error']['TNTOFFICIEL_CODE_POSTAL'] =
                    $this->l('The postal code indicated is not valid, please check the information entered.');
            }
            if ($strSenderCity && !$boolCityIsValid) {
                $arrFormMessagesSender['error']['TNTOFFICIEL_VILLE'] =
                    $this->l('The city shown is not valid, please check the information entered.');
            }
            if ($strSenderZipCode && $boolPostCodeIsValid) {
                $arrRefFieldsValue['TNTOFFICIEL_CODE_POSTAL'] = $arrResultCitiesGuide['strZipCode'];
            }
            if ($strSenderCity && $boolCityIsValid) {
                $arrRefFieldsValue['TNTOFFICIEL_VILLE'] = $arrResultCitiesGuide['strCity'];
            }


            // Auto formatting.
            $strSenderZipCode = $arrResultCitiesGuide['strZipCode'];
            $strSenderCity = $arrResultCitiesGuide['strCity'];

            if (!Validate::isEmail($strSenderEmail)) {
                $arrFormMessagesSender['error']['TNTOFFICIEL_MAIL'] =
                    $this->l('The format of the email is invalid, please check the information entered.');
            }

            $strSenderMobilePhone = TNTOfficiel_Tools::validateMobilePhone('FR', $strSenderPhone);
            $strSenderFixedPhone = TNTOfficiel_Tools::validateFixedPhone('FR', $strSenderPhone);
            // Cleaned Phone.
            $strSenderPhone = ($strSenderMobilePhone !== false ? $strSenderMobilePhone : $strSenderFixedPhone);

            if ($strSenderPhone === false) {
                $arrFormMessagesSender['error']['TNTOFFICIEL_TELEPHONE'] =
                    $this->l('The phone format is not valid, please check the information entered.');
            } else {
                $arrRefFieldsValue['TNTOFFICIEL_TELEPHONE'] = $strSenderPhone;
            }

            /*
             * Save
             */

            // If no errors.
            if (count($arrFormMessagesSender['error']) === 0) {
                $objTNTContextAccountModel->setSenderCompany($strSenderCompany);
                $objTNTContextAccountModel->setSenderAddress1($strSenderAddress1);
                $objTNTContextAccountModel->setSenderAddress2($strSenderAddress2);
                $objTNTContextAccountModel->setSenderZipCode($strSenderZipCode);
                $objTNTContextAccountModel->setSenderCity($strSenderCity);
                $objTNTContextAccountModel->setSenderFirstName($strSenderFirstName);
                $objTNTContextAccountModel->setSenderLastName($strSenderLastName);
                $objTNTContextAccountModel->setSenderEMail($strSenderEmail);
                $objTNTContextAccountModel->setSenderPhone($strSenderPhone);
                // Save and also for each shop in account context.
                $objTNTContextAccountModel->saveContextShop();

                $arrFormMessagesSender['success'][] = $this->l('Sender Settings updated.');
            }
        }

        $objAllZipCodeCities = array();
        foreach ($arrResultCitiesGuide['arrCitiesNameList'] as $strCities) {
            $objAllZipCodeCities[] = (object)array(
                'name' => $strCities,
                'id' => $strCities,
            );
        }


        return array(
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Company'),
                    'name' => 'TNTOFFICIEL_SOCIETE',
                    'maxlength' => 32,
                    'size' => 6,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Address'),
                    'name' => 'TNTOFFICIEL_ADRESSE_1',
                    'maxlength' => 32,
                    'size' => 6,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Address supplement'),
                    'name' => 'TNTOFFICIEL_ADRESSE_2',
                    'maxlength' => 32,
                    'size' => 6,
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Zip Code'),
                    'name' => 'TNTOFFICIEL_CODE_POSTAL',
                    'maxlength' => 10,
                    'size' => 6,
                    'required' => true,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('City'),
                    'name' => 'TNTOFFICIEL_VILLE',
                    'maxlength' => 32,
                    'required' => true,
                    //'class' => 'col-md-6',
                    'options' => array(
                        'query' => $objAllZipCodeCities,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('First name'),
                    'name' => 'TNTOFFICIEL_PRENOM',
                    'maxlength' => 32,
                    'size' => 6,
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Last name'),
                    'name' => 'TNTOFFICIEL_NOM',
                    'maxlength' => 32,
                    'size' => 6,
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Email'),
                    'name' => 'TNTOFFICIEL_MAIL',
                    'maxlength' => 80,
                    'size' => 6,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Phone'),
                    'name' => 'TNTOFFICIEL_TELEPHONE',
                    'maxlength' => 32,
                    'size' => 6,
                    'required' => true,
                ),
            ),
            'message' => $arrFormMessagesSender,
        );
    }

    /**
     * Get the Account Pickup form data for Helper.
     *
     * @return array
     */
    private function getFormAccountPickup($strArgIDForm, &$arrRefFieldsValue)
    {
        TNTOfficiel_Logstack::log();

        $arrFormMessagesPickup = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        $arrHeureDriver = array();
        $arrMinuteDriver = array();
        $arrHeureClosing = array();
        $arrMinuteClosing = array();

        for ($i = 8; $i < 23; $i++) {
            $value = Tools::strlen($i) < 2 ? '0'.$i : $i;
            array_push(
                $arrHeureDriver,
                array(
                    'idheure' => $i,
                    'name' => $value,
                )
            );
        }

        for ($j = 0; $j < 60; $j++) {
            $valueMinute = Tools::strlen($j) < 2 ? '0'.$j : $j;
            array_push(
                $arrMinuteDriver,
                array(
                    'idminute' => $j,
                    'name' => $valueMinute,
                )
            );
        }

        for ($i = 15; $i < 24; $i++) {
            $value = Tools::strlen($i) < 2 ? '0'.$i : $i;
            array_push(
                $arrHeureClosing,
                array(
                    'idheure' => $i,
                    'name' => $value,
                )
            );
        }

        for ($j = 0; $j < 60; $j++) {
            $valueMinute = Tools::strlen($j) < 2 ? '0'.$j : $j;
            array_push(
                $arrMinuteClosing,
                array(
                    'idminute' => $j,
                    'name' => $valueMinute,
                )
            );
        }


        // Input values.
        // ObjectModel self escape data with formatValue(). Do not double escape using pSQL.
        $strPickupLabelType = (string)Tools::getValue('TNTOFFICIEL_ETIQUETTE');
        $strPreparationDays = (string)Tools::getValue('TNTOFFICIEL_DELAI_PREPARATION');
        $strDeliveryNotification = (string)Tools::getValue('TNTOFFICIEL_NOTIFICATION');
        $strDeliveryInsurance = (string)Tools::getValue('TNTOFFICIEL_INSURANCE');
        $strDisplayEDD = (string)Tools::getValue('TNTOFFICIEL_DATE_PREVISIONNELLE');

        $strPickupType = (string)Tools::getValue('TNTOFFICIEL_TYPE_RAMASSAGE');
        $strPickupHourDriver = (string)Tools::getValue('TNTOFFICIEL_HEURE_RAMASSAGE_DRIVER');
        $strPickupMinuteDriver = sprintf("%02s", (string)Tools::getValue('TNTOFFICIEL_MINUTE_RAMASSAGE_DRIVER'));
        $strPickupHourClosing = (string)Tools::getValue('TNTOFFICIEL_HEURE_RAMASSAGE_CLOSING');
        $strPickupMinuteClosing = sprintf("%02s", (string)Tools::getValue('TNTOFFICIEL_MINUTE_RAMASSAGE_CLOSING'));
        $strPickupDisplayNumber = (string)Tools::getValue('TNTOFFICIEL_AFFICHAGE_RAMASSAGE');
        $strAPIGoogleMapKey = (string)Tools::getValue('TNTOFFICIEL_GMAP_API_KEY');

        // Displayed values.
        $arrRefFieldsValue['TNTOFFICIEL_ETIQUETTE'] = $objTNTContextAccountModel->pickup_label_type;
        $arrRefFieldsValue['TNTOFFICIEL_DELAI_PREPARATION'] = ($objTNTContextAccountModel->pickup_preparation_days ?
            $objTNTContextAccountModel->pickup_preparation_days :
            '0'
        );
        $arrRefFieldsValue['TNTOFFICIEL_NOTIFICATION'] = $objTNTContextAccountModel->delivery_notification;
        $arrRefFieldsValue['TNTOFFICIEL_INSURANCE'] = $objTNTContextAccountModel->delivery_insurance;
        $arrRefFieldsValue['TNTOFFICIEL_DATE_PREVISIONNELLE'] = $objTNTContextAccountModel->delivery_display_edd;

        $arrRefFieldsValue['TNTOFFICIEL_TYPE_RAMASSAGE'] = $objTNTContextAccountModel->pickup_type;

        $arrRefFieldsValue['TNTOFFICIEL_HEURE_RAMASSAGE_DRIVER'] =
            $objTNTContextAccountModel->getPickupDriverTime()->format('H');
        $arrRefFieldsValue['TNTOFFICIEL_MINUTE_RAMASSAGE_DRIVER'] =
            $objTNTContextAccountModel->getPickupDriverTime()->format('i');
        $arrRefFieldsValue['TNTOFFICIEL_HEURE_RAMASSAGE_CLOSING'] =
            $objTNTContextAccountModel->getPickupClosingTime()->format('H');
        $arrRefFieldsValue['TNTOFFICIEL_MINUTE_RAMASSAGE_CLOSING'] =
            $objTNTContextAccountModel->getPickupClosingTime()->format('i');

        $arrRefFieldsValue['TNTOFFICIEL_AFFICHAGE_RAMASSAGE'] = $objTNTContextAccountModel->pickup_display_number;
        $arrRefFieldsValue['TNTOFFICIEL_GMAP_API_KEY'] = $objTNTContextAccountModel->api_google_map_key;


        /*
         * Validate the Pickup and return error messages.
         */

        // If store request (else simple check).
        if (Tools::isSubmit($strArgIDForm)) {
            $arrRefFieldsValue['TNTOFFICIEL_ETIQUETTE'] = $strPickupLabelType;
            $arrRefFieldsValue['TNTOFFICIEL_DELAI_PREPARATION'] = $strPreparationDays;
            $arrRefFieldsValue['TNTOFFICIEL_NOTIFICATION'] = $strDeliveryNotification;
            $arrRefFieldsValue['TNTOFFICIEL_INSURANCE'] = $strDeliveryInsurance;
            $arrRefFieldsValue['TNTOFFICIEL_DATE_PREVISIONNELLE'] = $strDisplayEDD;
            $arrRefFieldsValue['TNTOFFICIEL_TYPE_RAMASSAGE'] = Tools::getValue('TNTOFFICIEL_TYPE_RAMASSAGE');
            $arrRefFieldsValue['TNTOFFICIEL_HEURE_RAMASSAGE_DRIVER'] =
                Tools::getValue('TNTOFFICIEL_HEURE_RAMASSAGE_DRIVER');
            $arrRefFieldsValue['TNTOFFICIEL_MINUTE_RAMASSAGE_DRIVER'] =
                Tools::getValue('TNTOFFICIEL_MINUTE_RAMASSAGE_DRIVER');
            $arrRefFieldsValue['TNTOFFICIEL_HEURE_RAMASSAGE_CLOSING'] =
                Tools::getValue('TNTOFFICIEL_HEURE_RAMASSAGE_CLOSING');
            $arrRefFieldsValue['TNTOFFICIEL_MINUTE_RAMASSAGE_CLOSING'] =
                Tools::getValue('TNTOFFICIEL_MINUTE_RAMASSAGE_CLOSING');
            $arrRefFieldsValue['TNTOFFICIEL_AFFICHAGE_RAMASSAGE'] = $strPickupDisplayNumber;
            $arrRefFieldsValue['TNTOFFICIEL_GMAP_API_KEY'] = $strAPIGoogleMapKey;

            // Check.
            if (!ctype_digit($strPreparationDays) || (int)$strPreparationDays > 30) {
                $arrFormMessagesPickup['error']['TNTOFFICIEL_DELAI_PREPARATION'] =
                    $this->l('The "Preparation time" must be a positive integer less than or equal to 30 days, please check the information entered.');
            }

            /*
             * Save
             */

            // If no errors.
            if (count($arrFormMessagesPickup['error']) === 0) {
                $objTNTContextAccountModel->setPickupLabelType($strPickupLabelType);
                $objTNTContextAccountModel->pickup_preparation_days = $strPreparationDays;
                $objTNTContextAccountModel->delivery_notification = $strDeliveryNotification;
                $objTNTContextAccountModel->delivery_insurance = $strDeliveryInsurance;
                $objTNTContextAccountModel->delivery_display_edd = $strDisplayEDD;

                $objTNTContextAccountModel->setPickupType($strPickupType);

                $objTNTContextAccountModel->pickup_driver_time =
                    DateTime::createFromFormat('H:i', $strPickupHourDriver.':'.$strPickupMinuteDriver)->format('H:i');
                $objTNTContextAccountModel->pickup_closing_time =
                    DateTime::createFromFormat('H:i', $strPickupHourClosing.':'.$strPickupMinuteClosing)->format('H:i');

                $objTNTContextAccountModel->pickup_display_number = $strPickupDisplayNumber;
                $objTNTContextAccountModel->api_google_map_key = $strAPIGoogleMapKey;

                // Save and also for each shop in account context.
                $objTNTContextAccountModel->saveContextShop();

                $arrFormMessagesPickup['success'][] = $this->l('Settings updated.');
            }
        }


        $strAPIKGMDesc = $this->l('This is the API key to use the mapping service of Google Maps.').' '
            .$this->l('From your own Google account, you need to generate a key containing the "Maps JavaScript API and Geocoding API" APIs.')
            .'<br />'
            .sprintf(
                $this->l('To generate or obtain more information about this key, %sclick here%s.'),
                '<a target="_blank"
                    href="https://console.developers.google.com/apis/credentials/wizard?api=maps_backend"
                 >',
                '</a>'
            );

        return array(
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Print format of labels'),
                    'name' => 'TNTOFFICIEL_ETIQUETTE',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'STDA4',
                                'name' => $this->l('Standard A4 printer'),
                            ),
                            array(
                                'id' => 'THERMAL',
                                'name' => $this->l('Thermal printer on your labels 4"x6"'),
                            ),
                            array(
                                'id' => 'THERMAL,NO_LOGO',
                                'name' => $this->l('Thermal printer on labels with TNT logo'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Order preparation time (in days)'),
                    'name' => 'TNTOFFICIEL_DELAI_PREPARATION',
                    'required' => true,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Send delivery notifications to your recipients (mails and sms)'),
                    'name' => 'TNTOFFICIEL_NOTIFICATION',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('View the estimated delivery date to customers'),
                    'name' => 'TNTOFFICIEL_DATE_PREVISIONNELLE',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display the Insurance option in TNT order management'),
                    'name' => 'TNTOFFICIEL_INSURANCE',
                    'required' => false,
                    'is_bool' => true,
                    'desc' => $this->l('Activate this option if you want to be able to insure your shipment before issuing the transport vouchers.'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Pickup type'),
                    'name' => 'TNTOFFICIEL_TYPE_RAMASSAGE',
                    'required' => false,
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'REGULAR',
                                'name' => $this->l('Regular'),
                            ),
                            array(
                                'id' => 'OCCASIONAL',
                                'name' => $this->l('Occasional'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Driving time of the driver'),
                    'class' => 'ramassage col-md-6',
                    'name' => 'TNTOFFICIEL_HEURE_RAMASSAGE_DRIVER',
                    'required' => false,
                    'options' => array(
                        'query' => $arrHeureDriver,
                        'id' => 'idheure',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'class' => 'ramassage col-md-6',
                    'name' => 'TNTOFFICIEL_MINUTE_RAMASSAGE_DRIVER',
                    'required' => false,
                    'options' => array(
                        'query' => $arrMinuteDriver,
                        'id' => 'idminute',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Closure hour'),
                    'class' => 'ramassage col-md-6',
                    'name' => 'TNTOFFICIEL_HEURE_RAMASSAGE_CLOSING',
                    'required' => false,
                    'options' => array(
                        'query' => $arrHeureClosing,
                        'id' => 'idheure',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'class' => 'ramassage col-md-6',
                    'name' => 'TNTOFFICIEL_MINUTE_RAMASSAGE_CLOSING',
                    'required' => false,
                    'options' => array(
                        'query' => $arrMinuteClosing,
                        'id' => 'idminute',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display the pickup number in the list of shipping labels'),
                    'name' => 'TNTOFFICIEL_AFFICHAGE_RAMASSAGE',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Google Maps API Key'),
                    'name' => 'TNTOFFICIEL_GMAP_API_KEY',
                    'size' => 6,
                    'desc' => $strAPIKGMDesc,
                    'required' => false,
                ),
            ),
            'message' => $arrFormMessagesPickup,
        );
    }

    private function getFormAccountZone($strArgIDForm, &$arrRefFieldsValue)
    {
        TNTOfficiel_Logstack::log();

        $arrFormMessagesZone = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();

        // Input values.
        $arrZone1IDList = Tools::getValue('TNTOFFICIEL_ZONE_1');
        $arrZone2IDList = Tools::getValue('TNTOFFICIEL_ZONE_2');

        // Displayed values.
        $arrRefFieldsValue['TNTOFFICIEL_ZONE_1[]'] = $objTNTContextAccountModel->getZone1Departments();
        $arrRefFieldsValue['TNTOFFICIEL_ZONE_2[]'] = $objTNTContextAccountModel->getZone2Departments();
        if (Tools::isSubmit($strArgIDForm)) {
            $arrRefFieldsValue['TNTOFFICIEL_ZONE_1[]'] = $arrZone1IDList;
            $arrRefFieldsValue['TNTOFFICIEL_ZONE_2[]'] = $arrZone2IDList;
        }

        /*
         * Validate the Zone and return error messages.
         */

        // If form submitted.
        if (Tools::isSubmit($strArgIDForm)) {
            // Check if zones have the same department.
            $boolValid = $objTNTContextAccountModel->setZoneDepartments($arrZone1IDList, $arrZone2IDList);

            // Save and also for each shop in account context.
            $objTNTContextAccountModel->saveContextShop();

            // If no errors.
            if (!$boolValid) {
                $arrFormMessagesZone['error'][] =
                    $this->l('All departments in the fields "Regional fees zone 1" and "Regional fees zone 2" must be separate, please check the information entered.');
            }
        }


        // Department List.
        $arrZoneAllDepartments = $objTNTContextAccountModel->getZoneAllDepartments();
        $objAllDefaultDepartments = array();
        foreach ($arrZoneAllDepartments as $strDepartmentName => $strDepartmentNumber) {
            $objAllDefaultDepartments[] = (object)array(
                'name' => $strDepartmentNumber.' - '.$strDepartmentName,
                'id' => $strDepartmentNumber,
            );
        }

        $strZone2Desc =
            $this->l('Enter the department numbers for which you want to apply a specific pricing (ex: 01,18,75,95). You can set the rates in the "Setting TNT Services" tab.');

        return array(
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => sprintf($this->l('Regional fees zone %s'), 1),
                    'name' => 'TNTOFFICIEL_ZONE_1',
                    'size' => 6,
                    'required' => false,
                    'class' => 'chosen col-md-6',
                    'multiple' => true,
                    'options' => array(
                        'query' => $objAllDefaultDepartments,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => sprintf($this->l('Regional fees zone %s'), 2),
                    'name' => 'TNTOFFICIEL_ZONE_2',
                    'size' => 6,
                    'required' => false,
                    'class' => 'chosen col-md-6',
                    'multiple' => true,
                    'options' => array(
                        'query' => $objAllDefaultDepartments,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'desc' => $strZone2Desc,
                ),
            ),
            'message' => $arrFormMessagesZone,
        );
    }

    private function getFormAccountOrderState($strArgIDForm, &$arrRefFieldsValue)
    {
        TNTOfficiel_Logstack::log();

        $arrFormMessagesStatus = array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
        );

        $objTNTContextAccountModel = TNTOfficielAccount::loadContextShop();


        // Defalut values.
        $intOSShipmentSaveID = $objTNTContextAccountModel->os_shipment_save_id;
        $intOSShipmentAfterID = $objTNTContextAccountModel->os_shipment_after_id;
        $intOSParcelTakenInCharge = $objTNTContextAccountModel->os_parcel_takenincharge_id;
        $intOSParcelAllDeliveredID = $objTNTContextAccountModel->os_parcel_alldelivered_id;
        $intOSParcelAllDeliveredToPointID = $objTNTContextAccountModel->os_parcel_alldeliveredtopoint_id;
        $boolOSParcelCheckEnable = $objTNTContextAccountModel->os_parcel_check_enable;
        $intOSParcelCheckRate = $objTNTContextAccountModel->os_parcel_check_rate;

        if (Tools::isSubmit($strArgIDForm)) {
            // Input Values.
            $intOSShipmentSaveID = (int)Tools::getValue('TNTOFFICIEL_OS_SHIPMENT_SAVE');
            $intOSShipmentAfterID = (int)Tools::getValue('TNTOFFICIEL_OS_SHIPMENT_AFTER');
            $intOSParcelTakenInCharge = (int)Tools::getValue('TNTOFFICIEL_OS_PARCEL_TAKENINCHARGE');
            $intOSParcelAllDeliveredID = (int)Tools::getValue('TNTOFFICIEL_OS_PARCEL_ALLDELIVERED');
            $intOSParcelAllDeliveredToPointID = (int)Tools::getValue('TNTOFFICIEL_OS_PARCEL_ALLDELIVEREDTOPOINT');
            $boolOSParcelCheckEnable = (bool)Tools::getValue('TNTOFFICIEL_OS_PARCEL_CHECK_ENABLE');
            $intOSParcelCheckRate = (int)Tools::getValue('TNTOFFICIEL_OS_PARCEL_CHECK_RATE');

            if ($intOSShipmentSaveID !== 0
                && $intOSShipmentAfterID === $intOSShipmentSaveID
            ) {
                $arrFormMessagesStatus['error']['TNTOFFICIEL_OS_SHIPMENT_AFTER'] =
                    $this->l('The status to be applied automatically after the creation of the transport voucher must be different from the status that triggers the creation of the transport voucher.');
            }

            /*
             * Save
             */

            // If no errors.
            if (count($arrFormMessagesStatus['error']) === 0) {
                $objTNTContextAccountModel->setOSShipmentSaveID($intOSShipmentSaveID);
                $objTNTContextAccountModel->setOSShipmentAfterID($intOSShipmentAfterID);
                $objTNTContextAccountModel->setOSParcelTakenInChargeID($intOSParcelTakenInCharge);
                $objTNTContextAccountModel->setOSParcelAllDeliveredID($intOSParcelAllDeliveredID);
                $objTNTContextAccountModel->setOSParcelAllDeliveredToPointID($intOSParcelAllDeliveredToPointID);
                $objTNTContextAccountModel->setOSParcelCheckEnable($boolOSParcelCheckEnable);
                if ($boolOSParcelCheckEnable) {
                    $objTNTContextAccountModel->setOSParcelCheckRate($intOSParcelCheckRate);
                } else {
                    // If disabled, select saved, not submitted.
                    $intOSParcelCheckRate = $objTNTContextAccountModel->os_parcel_check_rate;
                }
                // Save and also for each shop in account context.
                $objTNTContextAccountModel->saveContextShop();

                $arrFormMessagesStatus['success'][] = $this->l('OrderState Settings updated.');
            }
        }

        // Displayed values.
        $arrRefFieldsValue['TNTOFFICIEL_OS_SHIPMENT_SAVE'] = $intOSShipmentSaveID;
        $arrRefFieldsValue['TNTOFFICIEL_OS_SHIPMENT_AFTER'] = $intOSShipmentAfterID;
        $arrRefFieldsValue['TNTOFFICIEL_OS_PARCEL_TAKENINCHARGE'] = $intOSParcelTakenInCharge;
        $arrRefFieldsValue['TNTOFFICIEL_OS_PARCEL_ALLDELIVERED'] = $intOSParcelAllDeliveredID;
        $arrRefFieldsValue['TNTOFFICIEL_OS_PARCEL_ALLDELIVEREDTOPOINT'] = $intOSParcelAllDeliveredToPointID;
        $arrRefFieldsValue['TNTOFFICIEL_OS_PARCEL_CHECK_ENABLE'] = $boolOSParcelCheckEnable;
        $arrRefFieldsValue['TNTOFFICIEL_OS_PARCEL_CHECK_RATE'] = $intOSParcelCheckRate;

        // Get all non deleted order state, ordered by name.
        $arrStatusList = OrderState::getOrderStates((int)$this->context->language->id);

        /*
         * Predefined OrderState to include if exist.
         */

        $arrIntOrderStateIDShipmentSave = array(
            (int)Configuration::get('PS_OS_PREPARATION'),
            (int)Configuration::get('PS_OS_SHIPPING'),
        );
        $arrIntOrderStateIDShipmentAfter = array(
            (int)Configuration::get('TNTOFFICIEL_OS_READYFORPICKUP'),
        );
        $arrIntOrderStateIDParcelTakInCharge = array(
            (int)Configuration::get('TNTOFFICIEL_OS_TAKENINCHARGE'),
        );
        $arrIntOrderStateIDParcelAllDelivered = array(
            (int)Configuration::get('PS_OS_DELIVERED'),
        );
        $arrIntOrderStateIDParcelAllDeliveredToPoint = array(
            (int)Configuration::get('TNTOFFICIEL_OS_DELIVEREDTOPOINT'),
        );

        /*
         * OrderState list for select.
         */

        $arrOrderStateOptionsShipmentSave = array(
            0 => array(
                'id' => 0,
                'name' => 'Aucun (désactivé)'
            )
        );
        $arrOrderStateOptionsShipmentAfter = array(
            0 => array(
                'id' => 0,
                'name' => 'Aucun (désactivé)'
            )
        );
        $arrOrderStateOptionsParcelTakeInCharge = array(
            0 => array(
                'id' => 0,
                'name' => 'Aucun (désactivé)'
            )
        );
        $arrOrderStateOptionsParcelAllDelivered = array(
            0 => array(
                'id' => 0,
                'name' => 'Aucun (désactivé)'
            )
        );
        $arrOrderStateOptionsParcelAllDeliveredToPoint = array(
            0 => array(
                'id' => 0,
                'name' => 'Aucun (désactivé)'
            )
        );

        // For each existing OrderState.
        foreach ($arrStatusList as $arrStatusItem) {
            // If an original or delivery flagged or create by this module or not from
            if ($arrStatusItem['unremovable']
                || $arrStatusItem['delivery']
                || $arrStatusItem['module_name'] === TNTOfficiel::MODULE_NAME
                || $arrStatusItem['module_name'] === ''
            ) {
                $intOSID = (int)$arrStatusItem['id_order_state'];

                // Trigger shipment label creation.
                // If OrderState in preset array.
                // Accept manually created (removable, not module owned) OrderState delivery flagged.
                // (Exclude selected SHIPMENT_AFTER ID).
                if (in_array($intOSID, $arrIntOrderStateIDShipmentSave)
                    || (!$arrStatusItem['unremovable']
                        && $arrStatusItem['delivery']
                        && !$arrStatusItem['module_name']
                    )
                ) {
                    $arrOrderStateOptionsShipmentSave[$intOSID] = array(
                        'id' => $intOSID,
                        'name' => $arrStatusItem['name']
                    );
                }
                // After shipment label creation.
                // If OrderState in preset array.
                // Accept manually created (removable, not module owned) OrderState delivery flagged.
                // (Exclude selected SHIPMENT_SAVE ID).
                if (in_array($intOSID, $arrIntOrderStateIDShipmentAfter)
                    || (!$arrStatusItem['unremovable']
                        && $arrStatusItem['delivery']
                        && !$arrStatusItem['module_name']
                    )
                ) {
                    $arrOrderStateOptionsShipmentAfter[$intOSID] = array(
                        'id' => $intOSID,
                        'name' => $arrStatusItem['name']
                    );
                }
                // If OrderState in preset array.
                if (in_array($intOSID, $arrIntOrderStateIDParcelTakInCharge)) {
                    $arrOrderStateOptionsParcelTakeInCharge[$intOSID] = array(
                        'id' => $intOSID,
                        'name' => $arrStatusItem['name']
                    );
                }
                // If OrderState in preset array.
                if (in_array($intOSID, $arrIntOrderStateIDParcelAllDelivered)) {
                    $arrOrderStateOptionsParcelAllDelivered[$intOSID] = array(
                        'id' => $intOSID,
                        'name' => $arrStatusItem['name']
                    );
                }
                // If OrderState in preset array.
                if (in_array($intOSID, $arrIntOrderStateIDParcelAllDeliveredToPoint)) {
                    $arrOrderStateOptionsParcelAllDeliveredToPoint[$intOSID] = array(
                        'id' => $intOSID,
                        'name' => $arrStatusItem['name']
                    );
                }
            }
        }

        ksort($arrOrderStateOptionsShipmentSave);
        ksort($arrOrderStateOptionsShipmentAfter);
        ksort($arrOrderStateOptionsParcelTakeInCharge);
        ksort($arrOrderStateOptionsParcelAllDelivered);
        ksort($arrOrderStateOptionsParcelAllDeliveredToPoint);

        return array(
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Order status triggering creation of the shipping label'),
                    'name' => 'TNTOFFICIEL_OS_SHIPMENT_SAVE',
                    'required' => false,
                    'options' => array(
                        'query' => $arrOrderStateOptionsShipmentSave,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Order status to apply automatically after creation of the shipping label'),
                    'name' => 'TNTOFFICIEL_OS_SHIPMENT_AFTER',
                    'desc' => $this->l('Select an order status only if you wish to apply a specific one as soon as the shipping label is created.'),
                    'required' => false,
                    'options' => array(
                        'query' => $arrOrderStateOptionsShipmentAfter,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Order status to apply when a parcel is taken in charge by carrier'),
                    'name' => 'TNTOFFICIEL_OS_PARCEL_TAKENINCHARGE',
                    'desc' => $this->l('Select an order status only if you wish to apply a specific one as soon as a parcel is taken in charge by the carrier.'),
                    'required' => false,
                    'options' => array(
                        'query' => $arrOrderStateOptionsParcelTakeInCharge,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'html',
                    'name' => 'html_data',
                    'html_content' => '<div class="col-md-12"><hr /></div>',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable automatic update of parcels delivery status'),
                    'name' => 'TNTOFFICIEL_OS_PARCEL_CHECK_ENABLE',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Time interval beetween automatic parcels delivery status updates'),
                    'name' => 'TNTOFFICIEL_OS_PARCEL_CHECK_RATE',
                    'required' => false,
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => '10800',
                                'name' => sprintf($this->l('%sh'), (int)(10800/(60*60))),
                            ),
                            array(
                                'id' => '21600',
                                'name' => sprintf($this->l('%sh'), (int)(21600/(60*60))),
                            ),
                            array(
                                'id' => '32400',
                                'name' => sprintf($this->l('%sh'), (int)(32400/(60*60))),
                            ),
                            array(
                                'id' => '43200',
                                'name' => sprintf($this->l('%sh'), (int)(43200/(60*60))),
                            ),
                            array(
                                'id' => '86400',
                                'name' => sprintf($this->l('%sh'), (int)(86400/(60*60))),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Order status to apply when all parcels are delivered to final receiver')
                        .' '.'                                                                ',
                    'name' => 'TNTOFFICIEL_OS_PARCEL_ALLDELIVERED',
                    'required' => false,
                    'options' => array(
                        'query' => $arrOrderStateOptionsParcelAllDelivered,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Order status to apply when all parcels are delivered to partner merchant or TNT agency'),
                    'name' => 'TNTOFFICIEL_OS_PARCEL_ALLDELIVEREDTOPOINT',
                    'required' => false,
                    'options' => array(
                        'query' => $arrOrderStateOptionsParcelAllDeliveredToPoint,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
            ),
            'message' => $arrFormMessagesStatus,
        );
    }
}
