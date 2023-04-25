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
 * Class TNTOfficielReceiver
 */
class TNTOfficielReceiver extends ObjectModel
{
    // id_tntofficiel_receiver
    public $id;

    public $id_address;
    public $receiver_email;
    public $receiver_mobile;
    public $receiver_building;
    public $receiver_accesscode;
    public $receiver_floor;
    public $receiver_instructions;

    public static $definition = array(
        'table' => 'tntofficiel_receiver',
        'primary' => 'id_tntofficiel_receiver',
        'fields' => array(
            'id_address' => array(
                'type' => ObjectModel::TYPE_INT,
                'size' => 10,
                'validate' => 'isUnsignedId',
                'required' => true,
            ),
            'receiver_email' => array(
                'type' => ObjectModel::TYPE_STRING,
                'validate' => 'isEmail',
                'size' => 128,
            ),
            'receiver_mobile' => array(
                'type' => ObjectModel::TYPE_STRING,
                'validate' => 'isPhoneNumber',
                'size' => 32,
            ),
            'receiver_building' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 16,
            ),
            'receiver_accesscode' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 16,
            ),
            'receiver_floor' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 16,
            ),
            'receiver_instructions' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 64,
            ),
        ),
    );

    // cache and prevent race condition.
    private static $arrLoadedEntities = array();

    /**
     * Creates the tables needed by the model.
     *
     * @return bool
     */
    public static function createTables()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = sprintf('%s::%s', __CLASS__, __FUNCTION__);

        $strTablePrefix = _DB_PREFIX_;
        $strTableEngine = _MYSQL_ENGINE_;

        $strTableName = $strTablePrefix.TNTOfficielReceiver::$definition['table'];

        // If table exist.
        if (TNTOfficiel_Tools::isTableExist($strTableName) === true) {
            // Update table.
            TNTOfficielReceiver::upgradeTables();
        } else {
            // Create table.
            $strSQLCreateReceiver = <<<SQL
CREATE TABLE IF NOT EXISTS `${strTableName}` (
    `id_tntofficiel_receiver`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_address`                    INT(10) UNSIGNED NOT NULL,
    `receiver_email`                VARCHAR(128) NOT NULL DEFAULT '',
    `receiver_mobile`               VARCHAR(32) NOT NULL DEFAULT '',
    `receiver_building`             VARCHAR(16) NOT NULL DEFAULT '',
    `receiver_accesscode`           VARCHAR(16) NOT NULL DEFAULT '',
    `receiver_floor`                VARCHAR(16) NOT NULL DEFAULT '',
    `receiver_instructions`         VARCHAR(64) NOT NULL DEFAULT '',
-- Key.
    PRIMARY KEY (`id_tntofficiel_receiver`),
    UNIQUE INDEX `id_address` (`id_address`)
) ENGINE = ${strTableEngine} DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';
SQL;

            $boolDBResult = TNTOfficiel_Tools::getDbExecute($strSQLCreateReceiver);
            if (is_string($boolDBResult)) {
                TNTOfficiel_Logger::logInstall($strLogMessage.' : '.$boolDBResult, false);

                return false;
            }

            TNTOfficiel_Logger::logInstall($strLogMessage);
        }

        return TNTOfficielReceiver::checkTables();
    }

    /**
     * Upgrade table.
     *
     * @return bool
     */
    public static function upgradeTables()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;
        $strTableName = $strTablePrefix.TNTOfficielReceiver::$definition['table'];

        // Upgrade table.
        $strSQLTableReceiverAddColumns = <<<SQL
ALTER TABLE `${strTableName}`
    ADD COLUMN `receiver_instructions`  VARCHAR(64) NOT NULL DEFAULT '' AFTER `receiver_floor`;
SQL;

        $arrRequireColumnsList = array('receiver_floor');
        $arrAddColumnsList = array('receiver_instructions');

        // If table exist, but not some columns.
        if (TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrRequireColumnsList) === true
            && TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrAddColumnsList) === false
        ) {
            // Update table if exist.
            $boolDBResult = TNTOfficiel_Tools::getDbExecute($strSQLTableReceiverAddColumns);
            if (is_string($boolDBResult)) {
                TNTOfficiel_Logger::logInstall($strLogMessage.' : '.$boolDBResult, false);

                return false;
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Check if table and columns exist.
     *
     * @return bool
     */
    public static function checkTables()
    {
        TNTOfficiel_Logstack::log();

        $strTablePrefix = _DB_PREFIX_;
        $strTableName = $strTablePrefix.TNTOfficielReceiver::$definition['table'];
        $arrColumnsList = array_keys(TNTOfficielReceiver::$definition['fields']);

        return (TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrColumnsList) === true);
    }

    /**
     * Constructor.
     */
    public function __construct($intArgID = null)
    {
        TNTOfficiel_Logstack::log();

        parent::__construct($intArgID);
    }

    /**
     * Load existing object model or optionally create a new one for it's ID.
     *
     * @param int $intArgAddressID
     * @param bool $boolArgCreate
     *
     * @return TNTOfficielReceiver|null
     */
    public static function loadAddressID($intArgAddressID, $boolArgCreate = true)
    {
        TNTOfficiel_Logstack::log();

        $intAddressID = (int)$intArgAddressID;

        // No new address ID.
        if (!($intAddressID > 0)) {
            return null;
        }

        $strEntityID = '_'.$intAddressID.'-'.(int)null.'-'.(int)null;
        // If already loaded.
        if (array_key_exists($strEntityID, TNTOfficielReceiver::$arrLoadedEntities)) {
            $objTNTReceiverModel = TNTOfficielReceiver::$arrLoadedEntities[$strEntityID];
            // Check.
            if (Validate::isLoadedObject($objTNTReceiverModel)
                && (int)$objTNTReceiverModel->id_address === $intAddressID
            ) {
                return $objTNTReceiverModel;
            }
        }

        // Search row for address ID.
        $objDbQuery = new DbQuery();
        $objDbQuery->select('*');
        $objDbQuery->from(TNTOfficielReceiver::$definition['table']);
        $objDbQuery->where('id_address = '.$intAddressID);

        $arrDBResult = TNTOfficiel_Tools::getDbSelect($objDbQuery);
        // If row found and match address ID.
        if (is_array($arrDBResult) && count($arrDBResult) === 1
            && $intAddressID === (int)$arrDBResult[0]['id_address']
        ) {
            // Load existing TNT address entry.
            $objTNTReceiverModel = new TNTOfficielReceiver((int)$arrDBResult[0]['id_tntofficiel_receiver']);
        } elseif ($boolArgCreate === true) {
            // Create a new TNT address entry.
            $objTNTReceiverModelCreate = new TNTOfficielReceiver(null);
            $objTNTReceiverModelCreate->id_address = $intAddressID;
            $objTNTReceiverModelCreate->save();
            // Reload to get default DB values after creation.
            $objTNTReceiverModel = TNTOfficielReceiver::loadAddressID($intAddressID, false);
        } else {
            $objException = new Exception(sprintf('TNTOfficielReceiver not found for Address #%s', $intAddressID));
            TNTOfficiel_Logger::logException($objException);

            return null;
        }

        // Check.
        if (!Validate::isLoadedObject($objTNTReceiverModel)
            || (int)$objTNTReceiverModel->id_address !== $intAddressID
        ) {
            return null;
        }

        $objTNTReceiverModel->id = (int)$objTNTReceiverModel->id;
        $objTNTReceiverModel->id_address = (int)$objTNTReceiverModel->id_address;

        TNTOfficielReceiver::$arrLoadedEntities[$strEntityID] = $objTNTReceiverModel;

        return $objTNTReceiverModel;
    }

    /**
     * Search for a list of existing receiver object model, via a customer ID.
     *
     * @param int $intArgCustomerID
     *
     * @return array list of TNTOfficielReceiver model found.
     */
    public static function searchCustomerID($intArgCustomerID)
    {
        TNTOfficiel_Logstack::log();

        $arrObjTNTReceiverModelList = array();

        $intCustomerID = (int)$intArgCustomerID;

        // If no customer ID.
        if (!($intCustomerID > 0)) {
            return $arrObjTNTReceiverModelList;
        }

        // Get enabled ID list of Address from a Customer ID.
        $arrIntAddressIDList = TNTOfficielReceiver::getPSAddressIDList($intCustomerID);
        // If no address ID, no DB Query.
        if (!(count($arrIntAddressIDList) > 0)) {
            return $arrObjTNTReceiverModelList;
        }

        // Search row for customer address ID list.
        $objDbQuery = new DbQuery();
        $objDbQuery->select('*');
        $objDbQuery->from(TNTOfficielReceiver::$definition['table']);
        $objDbQuery->where('id_address IN ('.implode(',', $arrIntAddressIDList).')');

        $arrDBResult = TNTOfficiel_Tools::getDbSelect($objDbQuery);
        // If row found.
        if (is_array($arrDBResult) && count($arrDBResult) > 0) {
            foreach ($arrDBResult as $arrValue) {
                $objTNTReceiverModel = TNTOfficielReceiver::loadAddressID((int)$arrValue['id_address'], false);
                if ($objTNTReceiverModel !== null) {
                    $arrObjTNTReceiverModelList[] = $objTNTReceiverModel;
                }
            }
        }

        return $arrObjTNTReceiverModelList;
    }

    /**
     * Load a Prestashop Address object from ID.
     *
     * @param int $intArgAddressID
     *
     * @return Address|null
     */
    public static function getPSAddress($intArgAddressID)
    {
        TNTOfficiel_Logstack::log();

        // Carrier ID must be an integer greater than 0.
        if (empty($intArgAddressID) || $intArgAddressID != (int)$intArgAddressID || !((int)$intArgAddressID > 0)) {
            return null;
        }

        $intAddressID = (int)$intArgAddressID;

        // Load Address.
        $objPSAddress = new Address($intAddressID);

        // If carrier object not available.
        if (!Validate::isLoadedObject($objPSAddress)
            || (int)$objPSAddress->id !== $intAddressID
        ) {
            return null;
        }

        return $objPSAddress;
    }

    /**
     * Load a Prestashop Customer object from ID.
     *
     * @param int $intArgCustomerID
     *
     * @return Customer|null
     */
    public static function getPSCustomer($intArgCustomerID)
    {
        TNTOfficiel_Logstack::log();

        // Carrier ID must be an integer greater than 0.
        if (empty($intArgCustomerID) || $intArgCustomerID != (int)$intArgCustomerID || !((int)$intArgCustomerID > 0)) {
            return null;
        }

        $intCustomerID = (int)$intArgCustomerID;

        // Load Customer.
        $objPSCustomer = new Customer($intCustomerID);

        // If carrier object not available.
        if (!Validate::isLoadedObject($objPSCustomer)
            || (int)$objPSCustomer->id !== $intCustomerID
        ) {
            return null;
        }

        return $objPSCustomer;
    }

    /**
     * Get enabled ID list of Address from a Customer ID.
     *
     * @param $intArgCustomerID
     *
     * @return array
     */
    public static function getPSAddressIDList($intArgCustomerID)
    {
        TNTOfficiel_Logstack::log();

        $arrIntAddressIDList = array();

        $objPSCustomer = TNTOfficielReceiver::getPSCustomer($intArgCustomerID);
        if ($objPSCustomer !== null) {
            $arrAddressList = $objPSCustomer->getAddresses((int)Configuration::get('PS_LANG_DEFAULT'));
            foreach ($arrAddressList as $arrAddress) {
                $arrIntAddressIDList[] = (int)$arrAddress['id_address'];
            }
        }

        return $arrIntAddressIDList;
    }

    /**
     * Validate Receiver Info.
     *
     * @param string $strArgReceiverEmail
     * @param string $strArgReceiverMobile
     * @param string $strArgReceiverBuilding
     * @param string $strArgReceiverAccesscode
     * @param string $strArgReceiverFloor
     *
     * @return array
     */
    public static function validateReceiverInfo(
        $strArgReceiverEmail,
        $strArgReceiverMobile,
        $strArgReceiverBuilding,
        $strArgReceiverAccesscode,
        $strArgReceiverFloor,
        $strArgReceiverInstructions
    ) {
        TNTOfficiel_Logstack::log();

        $arrFormInput = array(
            'receiver_email' => trim((string)$strArgReceiverEmail),
            'receiver_mobile' => trim((string)$strArgReceiverMobile),
            'receiver_building' => trim((string)$strArgReceiverBuilding),
            'receiver_accesscode' => trim((string)$strArgReceiverAccesscode),
            'receiver_floor' => trim((string)$strArgReceiverFloor),
            'receiver_instructions' => trim((string)$strArgReceiverInstructions),
        );

        $arrFormError = array();

        // Check if email is set and not empty.
        if (!isset($arrFormInput['receiver_email']) || $arrFormInput['receiver_email'] === '') {
            $arrFormError['receiver_email'] = 'L\'email est obligatoire';
        }

        // Check if the email is valid.
        if (!filter_var($arrFormInput['receiver_email'], FILTER_VALIDATE_EMAIL)) {
            // TNTOfficiel.translate.errorInvalidEMail
            $arrFormError['receiver_email'] = 'L\'e-mail saisi n\'est pas valide';
        }

        // Check if mobile phone is set and not empty.
        if (!isset($arrFormInput['receiver_mobile']) || $arrFormInput['receiver_mobile'] === '') {
            $arrFormError['receiver_mobile'] = 'Le Téléphone portable est obligatoire';
        } else {
            $arrFormInput['receiver_mobile'] = preg_replace('/[\s.-]+/ui', '', $arrFormInput['receiver_mobile']);
        }
        // Check if mobile phone is valid.
        $mxdPhoneValidated = TNTOfficiel_Tools::validateMobilePhone('FR', $arrFormInput['receiver_mobile']);
        if ($mxdPhoneValidated === false) {
            // TNTOfficiel.translate.errorInvalidPhoneNumber
            $arrFormError['receiver_mobile'] =
                'Le numéro de téléphone portable doit être de 10 chiffres et commencer par 06 et 07';
        } else {
            $arrFormInput['receiver_mobile'] = $mxdPhoneValidated;
        }

        // If building is set and not empty.
        if (isset($arrFormInput['receiver_building']) && $arrFormInput['receiver_building'] !== '') {
            $mxdBuildingValidated = TNTOfficiel_Tools::translitASCII($arrFormInput['receiver_building']);
            $arrFormInput['receiver_building'] = $mxdBuildingValidated;
        }
        // If accesscode is set and not empty.
        if (isset($arrFormInput['receiver_accesscode']) && $arrFormInput['receiver_accesscode'] !== '') {
            $mxdAccessCodeValidated = TNTOfficiel_Tools::translitASCII($arrFormInput['receiver_accesscode']);
            $arrFormInput['receiver_accesscode'] = $mxdAccessCodeValidated;
        }
        // If floor is set and not empty.
        if (isset($arrFormInput['receiver_floor']) && $arrFormInput['receiver_floor'] !== '') {
            $mxdFloorValidated = TNTOfficiel_Tools::translitASCII($arrFormInput['receiver_floor']);
            $arrFormInput['receiver_floor'] = $mxdFloorValidated;
        }

        // If instructions is set and not empty.
        if (isset($arrFormInput['receiver_instructions']) && $arrFormInput['receiver_instructions'] !== '') {
            $mxdInstructionsValidated = TNTOfficiel_Tools::translitASCII($arrFormInput['receiver_instructions']);
            $arrFormInput['receiver_instructions'] = $mxdInstructionsValidated;
        }


        $arrFieldMaxLength = array(
            'receiver_email' => array(
                'maxlength' => 80,
            ),
            'receiver_mobile' => array(
                'maxlength' => 15,
            ),
            'receiver_building' => array(
                'maxlength' => 3,
            ),
            'receiver_accesscode' => array(
                'maxlength' => 7,
            ),
            'receiver_floor' => array(
                'maxlength' => 2,
            ),
            'receiver_instructions' => array(
                'maxlength' => 30,
            ),
        );

        foreach ($arrFieldMaxLength as $strFieldName => $arrField) {
            if ($arrFormInput[$strFieldName]) {
                if (Tools::strlen($arrFormInput[$strFieldName]) > $arrField['maxlength']) {
                    $arrFormError[$strFieldName] =
                        'Le champ doit être de '.$arrField['maxlength'].' caractères maximum';
                }
            }
        }

        return array(
            'fields' => $arrFormInput,
            'errors' => $arrFormError,
            'length' => count($arrFormError),
        );
    }

    /**
     * Store Receiver Info for an Address ID.
     *
     * @param string $strArgCustomerEmail
     * @param string $strArgCustomerMobile
     * @param string $strArgAddressBuilding
     * @param string $strArgAddressAccesscode
     * @param string $strArgAddressFloor
     *
     * @return array
     */
    public function storeReceiverInfo(
        $strArgCustomerEmail,
        $strArgCustomerMobile,
        $strArgAddressBuilding,
        $strArgAddressAccesscode,
        $strArgAddressFloor,
        $strArgReceiverInstructions
    ) {
        TNTOfficiel_Logstack::log();

        // Validate receiver info.
        $arrFormReceiverInfoValidate = TNTOfficielReceiver::validateReceiverInfo(
            $strArgCustomerEmail,
            $strArgCustomerMobile,
            $strArgAddressBuilding,
            $strArgAddressAccesscode,
            $strArgAddressFloor,
            $strArgReceiverInstructions
        );

        $boolStored = false;
        // If no errors.
        if ($arrFormReceiverInfoValidate['length'] === 0) {
            // Model hydrate using validated fields data.
            // ObjectModel self escape data with formatValue(). Do not double escape using pSQL.
            $this->hydrate($arrFormReceiverInfoValidate['fields']);
            $boolStored = $this->save();
/*
            // Get delivery address of receiver.
            $objPSAddressDelivery = TNTOfficielReceiver::getPSAddress($this->id_address);
            // If delivery address object available.
            if ($objPSAddressDelivery !== null) {
                // If receiver mobile is valid and non empty.
                if (!array_key_exists('receiver_mobile', $arrFormReceiverInfoValidate['errors'])
                    && $this->receiver_mobile
                ) {
                    // If phone field is empty and receiver mobile different from phone_mobile field.
                    if (!$objPSAddressDelivery->phone
                        && $this->receiver_mobile != $objPSAddressDelivery->phone_mobile
                    ) {
                        // Save receiver mobile for next time.
                        $objPSAddressDelivery->phone = $this->receiver_mobile;
                        $objPSAddressDelivery->save();
                    } else if (!$objPSAddressDelivery->phone_mobile
                        && $this->receiver_mobile != $objPSAddressDelivery->phone
                    ) {
                        // Save receiver mobile for next time.
                        $objPSAddressDelivery->phone_mobile = $this->receiver_mobile;
                        $objPSAddressDelivery->save();
                    }
                }
            }
*/
        }

        // Validated and stored in DB.
        $arrFormReceiverInfoValidate['stored'] = $boolStored;

        return $arrFormReceiverInfoValidate;
    }

    /**
     * Find the mobile phone for a customer.
     *
     * @param Address $objArgPSAddressDelivery
     *
     * @return string
     */
    public static function searchPhoneMobile(Address $objArgPSAddressDelivery)
    {
        TNTOfficiel_Logstack::log();

        //$strCountryISO = Country::getIsoById((int)$objArgPSAddressDelivery->id_country);
        $strCountryISO = 'FR';

        // Mobile phone may be in phone field.
        $strAddressPhone = TNTOfficiel_Tools::validateMobilePhone(
            $strCountryISO,
            $objArgPSAddressDelivery->phone
        );

        if (!is_string($strAddressPhone)) {
            $strAddressPhone = TNTOfficiel_Tools::validateMobilePhone(
                $strCountryISO,
                $objArgPSAddressDelivery->phone_mobile
            );
        }

        // Search in Customer receiver info.
        if (!is_string($strAddressPhone)) {
            $strAddressPhone = false;

            $arrObjTNTReceiverList = TNTOfficielReceiver::searchCustomerID($objArgPSAddressDelivery->id_customer);
            foreach ($arrObjTNTReceiverList as $objTNTReceiver) {
                if ($objTNTReceiver->receiver_mobile) {
                    $strAddressPhone = $objTNTReceiver->receiver_mobile;
                    break;
                }
            }
        }

        // Search in others Customer Addresses.
        if (!is_string($strAddressPhone)) {
            $strAddressPhone = false;

            // Get enabled ID list of Address from a Customer ID.
            $arrIntAddressIDList = TNTOfficielReceiver::getPSAddressIDList($objArgPSAddressDelivery->id_customer);
            foreach ($arrIntAddressIDList as $intAddressID) {
                $objPSAddress = TNTOfficielReceiver::getPSAddress($intAddressID);
                if ($objPSAddress === null) {
                    continue;
                }

                //$strCountryISOCheck = Country::getIsoById((int)$objPSAddress->id_country);
                $strCountryISOCheck = 'FR';

                $strAddressPhoneCheck = TNTOfficiel_Tools::validateMobilePhone(
                    $strCountryISOCheck,
                    $objPSAddress->phone
                );
                if (is_string($strAddressPhoneCheck)) {
                    $strAddressPhone = $strAddressPhoneCheck;
                    break;
                }

                $strAddressPhoneCheck = TNTOfficiel_Tools::validateMobilePhone(
                    $strCountryISOCheck,
                    $objPSAddress->phone_mobile
                );
                if (is_string($strAddressPhoneCheck)) {
                    $strAddressPhone = $strAddressPhoneCheck;
                    break;
                }
            }
        }

        if (!is_string($strAddressPhone)) {
            $strAddressPhone = '';
        }

        return $strAddressPhone;
    }
}
