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
 * Class TNTOfficielParcel
 */
class TNTOfficielParcel extends ObjectModel
{
    // Parcels tracking URL.
    const TRACKING_URL = 'https://www.tnt.fr/public/suivi_colis/recherche/visubontransport.do?bonTransport=';

    // Final delivered stage.
    const STAGE_DELIVERED = 5;

    // id_tntofficiel_parcel
    public $id;

    public $id_order;
    public $weight;
    public $insurance_amount;
    public $tracking_url;
    public $parcel_number;
    public $pod_url;
    public $stage_id;
    public $stage_status;
    public $stage_events;
    public $stage_date;
    public $delivery_date;

    public static $definition = array(
        'table' => 'tntofficiel_parcels',
        'primary' => 'id_tntofficiel_parcel',
        'fields' => array(
            'id_order' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedId',
            ),
            'weight' => array(
                'type' => ObjectModel::TYPE_FLOAT,
                'validate' => 'isFloat',
            ),
            'insurance_amount' => array(
                'type' => ObjectModel::TYPE_FLOAT,
                'validate' => 'isFloat',
            ),
            'tracking_url' => array(
                'type' => ObjectModel::TYPE_STRING,
            ),
            'parcel_number' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 16,
            ),
            'pod_url' => array(
                'type' => ObjectModel::TYPE_STRING,
            ),
            'stage_id' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedId',
            ),
            'stage_status' => array(
                'type' => ObjectModel::TYPE_STRING,
            ),
            'stage_events' => array(
                'type' => ObjectModel::TYPE_STRING,
            ),
            'stage_date' => array(
                'type' => ObjectModel::TYPE_DATE,
                'validate' => 'isDateFormat',
            ),
            'delivery_date' => array(
                'type' => ObjectModel::TYPE_DATE,
                'validate' => 'isDateFormat',
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

        $strTableName = $strTablePrefix.TNTOfficielParcel::$definition['table'];

        // If table exist.
        if (TNTOfficiel_Tools::isTableExist($strTableName) === true) {
            // Update table.
            TNTOfficielParcel::upgradeTables010004();
            TNTOfficielParcel::upgradeTables010005();
            TNTOfficielParcel::upgradeTables010010();
        } else {
            // Create table.
            $strSQLCreateParcel = <<<SQL
CREATE TABLE IF NOT EXISTS `${strTableName}` (
`id_tntofficiel_parcel`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`id_order`                      INT(10) UNSIGNED NOT NULL,
`weight`                        DECIMAL(20,6) NOT NULL DEFAULT '0.000000',
`insurance_amount`              DECIMAL(20,6) NOT NULL DEFAULT '0.000000',
`tracking_url`                  TEXT,
`parcel_number`                 VARCHAR(16),
`pod_url`                       TEXT,
`stage_id`                      INT(10) UNSIGNED,
`stage_status`                  TEXT NULL,
`stage_events`                  TEXT NULL,
`stage_date`                    DATETIME NOT NULL DEFAULT '0000-00-00',
`delivery_date`                 DATETIME NOT NULL DEFAULT '0000-00-00',
-- Key.
PRIMARY KEY (`id_tntofficiel_parcel`)
) ENGINE = ${strTableEngine} DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';
SQL;

            $boolDBResult = TNTOfficiel_Tools::getDbExecute($strSQLCreateParcel);
            if (is_string($boolDBResult)) {
                TNTOfficiel_Logger::logInstall($strLogMessage.' : '.$boolDBResult, false);

                return false;
            }

            TNTOfficiel_Logger::logInstall($strLogMessage);
        }

        return TNTOfficielParcel::checkTables();
    }

    /**
     * Upgrade table.
     *
     * @return bool
     */
    public static function upgradeTables010004()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;
        $strTableName = $strTablePrefix.TNTOfficielParcel::$definition['table'];

        // Upgrade table.
        $strSQLTableParcelAddColumns = <<<SQL
ALTER TABLE `${strTableName}`
    ADD COLUMN `stage_id`           INT(10) UNSIGNED NOT NULL AFTER `pod_url`,
    ADD COLUMN `stage_events`       TEXT NULL AFTER `stage_id`,
    ADD COLUMN `stage_date`         DATETIME NOT NULL DEFAULT '0000-00-00' AFTER `stage_events`;
SQL;

        $arrRequireColumnsList = array('pod_url');
        $arrMissingColumnsList = array('stage_id', 'stage_events', 'stage_date');

        // If table exist, but not some columns.
        if (TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrRequireColumnsList) === true
            && TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrMissingColumnsList) === false
        ) {
            // Update table.
            $boolDBResult = TNTOfficiel_Tools::getDbExecute($strSQLTableParcelAddColumns);
            if (is_string($boolDBResult)) {
                TNTOfficiel_Logger::logInstall($strLogMessage.' : '.$boolDBResult, false);

                return false;
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Upgrade table.
     *
     * @return bool
     */
    public static function upgradeTables010005()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;
        $strTableName = $strTablePrefix.TNTOfficielParcel::$definition['table'];

        // Upgrade table.
        $strSQLTableParcelAddColumns = <<<SQL
ALTER TABLE `${strTableName}`
    ADD COLUMN `stage_status`       TEXT NULL AFTER `stage_id`,
    ADD COLUMN `delivery_date`      DATETIME NOT NULL DEFAULT '0000-00-00' AFTER `stage_date`;
SQL;

        $arrRequireColumnsList = array('stage_id', 'stage_date');
        $arrMissingColumnsList = array('stage_status', 'delivery_date');

        // If table exist, but not some columns.
        if (TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrRequireColumnsList) === true
            && TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrMissingColumnsList) === false
        ) {
            // Update table.
            $boolDBResult = TNTOfficiel_Tools::getDbExecute($strSQLTableParcelAddColumns);
            if (is_string($boolDBResult)) {
                TNTOfficiel_Logger::logInstall($strLogMessage.' : '.$boolDBResult, false);

                return false;
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Upgrade table.
     *
     * @return bool
     */
    public static function upgradeTables010010()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;
        $strTableName = $strTablePrefix.TNTOfficielParcel::$definition['table'];

        // Upgrade table.
        $strSQLTableParcelAddColumns = <<<SQL
ALTER TABLE `${strTableName}`
    ADD COLUMN `insurance_amount`   DECIMAL(20,6) NOT NULL DEFAULT '0.000000' AFTER `weight`;
SQL;

        $arrRequireColumnsList = array('weight');
        $arrMissingColumnsList = array('insurance_amount');

        // If table exist, but not some columns.
        if (TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrRequireColumnsList) === true
            && TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrMissingColumnsList) === false
        ) {
            // Update table.
            $boolDBResult = TNTOfficiel_Tools::getDbExecute($strSQLTableParcelAddColumns);
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
        $strTableName = $strTablePrefix.TNTOfficielParcel::$definition['table'];
        $arrColumnsList = array_keys(TNTOfficielParcel::$definition['fields']);

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
     * @param bool $null_values
     * @param bool $auto_date
     *
     * @return bool
     */
    public function save($null_values = false, $auto_date = true)
    {
        try {
            return parent::save($null_values, $auto_date);
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);
        }

        return false;
    }

    /**
     * Remove a parcel.
     *
     * @return bool
     */
    public function delete()
    {
        try {
            return parent::delete();
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);
        }

        return false;
    }

    /**
     * Load existing object model or optionally create a new one with a new ID.
     *
     * @param $intArgParcelID
     * @param bool $boolArgCreate
     *
     * @return TNTOfficielParcel|null
     */
    public static function loadParcelID($intArgParcelID = null)
    {
        TNTOfficiel_Logstack::log();

        $intParcelID = (int)$intArgParcelID;
        // Create.
        if ($intParcelID === 0) {
            // Create a new TNT account entry.
            $objTNTParcelModelCreate = new TNTOfficielParcel(null);
            $objTNTParcelModelCreate->save();
            $intParcelID = (int)$objTNTParcelModelCreate->id;
            unset($objTNTParcelModelCreate);
        }

        // No new account ID.
        if (!($intParcelID > 0)) {
            return null;
        }

        $strEntityID = $intParcelID.'-'.(int)null.'-'.(int)null;
        // If already loaded.
        if (array_key_exists($strEntityID, TNTOfficielParcel::$arrLoadedEntities)) {
            $objTNTParcelModel = TNTOfficielParcel::$arrLoadedEntities[$strEntityID];
            // Check.
            if (Validate::isLoadedObject($objTNTParcelModel)
                && (int)$objTNTParcelModel->id === $intParcelID
            ) {
                return $objTNTParcelModel;
            }
        }

        // Load existing TNT parcel entry.
        // or reload after create, to get default DB values after creation.
        $objTNTParcelModel = new TNTOfficielParcel($intParcelID);

        // Check.
        if (!Validate::isLoadedObject($objTNTParcelModel)
            || (int)$objTNTParcelModel->id !== $intParcelID
        ) {
            return null;
        }

        $objTNTParcelModel->id = (int)$objTNTParcelModel->id;
        $objTNTParcelModel->id_order = (int)$objTNTParcelModel->id_order;
        $objTNTParcelModel->weight = (float)$objTNTParcelModel->weight;
        $objTNTParcelModel->insurance_amount = (float)$objTNTParcelModel->insurance_amount;
        $objTNTParcelModel->stage_id = (int)$objTNTParcelModel->stage_id;

        TNTOfficielParcel::$arrLoadedEntities[$strEntityID] = $objTNTParcelModel;

        return $objTNTParcelModel;
    }

    /**
     * Search for a list of existing parcel object model, via an order ID.
     *
     * @param int $intArgOrderID
     *
     * @return array list of TNTOfficielParcel model found.
     */
    public static function searchOrderID($intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        $arrObjTNTParcelModelList = array();

        $intOrderID = (int)$intArgOrderID;

        // If no order ID.
        if (!($intOrderID > 0)) {
            return $arrObjTNTParcelModelList;
        }

        try {
            // Search row for order ID.
            $objDbQuery = new DbQuery();
            $objDbQuery->select('*');
            $objDbQuery->from(TNTOfficielParcel::$definition['table']);
            $objDbQuery->where('id_order = '.$intOrderID);

            $arrDBResult = TNTOfficiel_Tools::getDbSelect($objDbQuery);
            // If row found.
            if (is_array($arrDBResult) && count($arrDBResult) > 0) {
                foreach ($arrDBResult as $arrValue) {
                    if ($intOrderID === (int)$arrValue['id_order']) {
                        $intParcelID = (int)$arrValue['id_tntofficiel_parcel'];
                        $objTNTParcelModel = TNTOfficielParcel::loadParcelID($intParcelID);
                        if ($objTNTParcelModel !== null) {
                            $arrObjTNTParcelModelList[] = $objTNTParcelModel;
                        }
                    }
                }
            }
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);
        }

        return $arrObjTNTParcelModelList;
    }

    /**
     *
     * @param float $fltArgWeight
     *
     * @return bool
     */
    public function setWeight($fltArgWeight)
    {
        TNTOfficiel_Logstack::log();

        $this->weight = max(round((float)$fltArgWeight, 1, PHP_ROUND_HALF_UP), 0.1);

        return $this->save();
    }

    /**
     *
     * @param float $fltArgInsuranceAmount
     *
     * @return bool
     */
    public function setInsuranceAmount($fltArgInsuranceAmount)
    {
        TNTOfficiel_Logstack::log();

        $this->insurance_amount = max((float)$fltArgInsuranceAmount, 0.0);

        return $this->save();
    }

    /**
     * Update a parcel weight, matching an order ID.
     *
     * @param type $intArgOrderID
     * @param type $fltArgWeight
     *
     * @return string|bool string if error.
     */
    public function updateParcel($intArgOrderID, $fltArgWeight, $fltArgInsuranceAmount = null)
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)$intArgOrderID;
        $fltWeight = (float)$fltArgWeight;
        $fltInsuranceAmount = null;
        if ($fltArgInsuranceAmount >= 0) {
            $fltInsuranceAmount = (float)$fltArgInsuranceAmount;
        }

        // Load TNT order.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        // If fail.
        if ($objTNTOrderModel === null) {
            return sprintf('Unable to load TNTOfficielOrder #%s', $intOrderID);
        }

        // If order ID already set, check match.
        if ($this->id_order > 0 && $this->id_order !== $intOrderID) {
            return sprintf('Unable to match TNTOfficielOrder #%s', $intOrderID);
        }

        // Load an existing TNT carrier.
        $objTNTCarrierModel = $objTNTOrderModel->getTNTCarrierModel();
        // If fail or carrier is not from TNT module.
        if ($objTNTCarrierModel === null) {
            return sprintf('Unable to load TNTOfficielCarrier #%s', $objTNTCarrierModel->id_carrier);
        }

        $this->id_order = $intOrderID;

        //
        $fltMaxPackageWeight = $objTNTCarrierModel->getMaxPackageWeight();
        if ($fltWeight > $fltMaxPackageWeight) {
            return sprintf('Le poids d\'un colis ne peut dépasser %s Kg', $fltMaxPackageWeight);
        }

        $boolResult = $this->setWeight($fltWeight);

        //
        if ($objTNTOrderModel->isAccountInsuranceEnabled()
            && $fltInsuranceAmount !== null
        ) {
            $fltMaxInsuranceAmount = 25000.0;
            if ($fltInsuranceAmount > 0.0 && $fltInsuranceAmount < 1.0) {
                return 'Le montant minimum d\'assurance d\'un colis est de 1 €';
            }
            if ($fltInsuranceAmount > $fltMaxInsuranceAmount
            ) {
                return sprintf('Le montant maximum d\'assurance d\'un colis est de %s €', $fltMaxInsuranceAmount);
            }
            // Get the parcels.
            $arrObjTNTParcelModelList = $objTNTOrderModel->getTNTParcelModelList();
            $fltTotalInsuranceAmount = 0.0;
            /** @var $objTNTParcelModel TNTOfficielParcel */
            foreach ($arrObjTNTParcelModelList as $objTNTParcelModel) {
                // Add weight for the parcels.
                //$fltTotalWeight += $objTNTParcelModel->weight;
                if ($objTNTParcelModel->id === $this->id) {
                    $fltTotalInsuranceAmount += $fltInsuranceAmount;
                } else {
                    $fltTotalInsuranceAmount += $objTNTParcelModel->insurance_amount;
                }
            }

            if ($fltTotalInsuranceAmount > $fltMaxInsuranceAmount) {
                return sprintf('Le montant total d\'assurance des colis ne peut dépasser %s €', $fltMaxInsuranceAmount);
            }

            $boolResult = $boolResult && $this->setInsuranceAmount($fltInsuranceAmount);
        }

        if (!$boolResult) {
            return sprintf('Unable to save TNTOfficielParcel #%s', $this->id);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isTrackingFrozen()
    {
        TNTOfficiel_Logstack::log();

        // If stage date is valid and more than 2 days ago, tracking state is frozen.
        return (TNTOfficiel_Tools::isExpired($this->delivery_date, 2*24*60*60) === true);
    }

    /**
     * Get and store the parcel tracking state.
     *
     * @param int $intArgRefreshDelay 2 hours by default.
     *
     * @return bool. false if error.
     */
    public function updateTrackingState($intArgRefreshDelay = 7200)
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)$this->id_order;

        // If no parcel number, nothing to update.
        if (!is_string($this->parcel_number) || Tools::strlen($this->parcel_number) === 0) {
            return true;
        }

        // If stage date is valid and not expired.
        if (TNTOfficiel_Tools::isExpired($this->stage_date, $intArgRefreshDelay) === false) {
            return true;
        }

        if ($this->isTrackingFrozen()) {
            return true;
        }

        // Load TNT order.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        // If fail.
        if ($objTNTOrderModel === null) {
            return false;
        }

        $objTNTCarrierAccountModel = $objTNTOrderModel->getTNTAccountModel();
        // If no account available for this order's carrier.
        if ($objTNTCarrierAccountModel === null) {
            return false;
        }

        $arrResult = $objTNTCarrierAccountModel->trackingByConsignment($this->parcel_number);
        $arrResultParcelTracking = $arrResult['arrParcelTracking'];

        // No status, no tracking.
        if (!array_key_exists('shortStatus', $arrResultParcelTracking)) {
            // May happen few minutes after expeditionCreation.
            return true;
        }

        // Save the update date and time.
        $this->stage_date = date('Y-m-d H:i:s');

        // Save the parcel Proof Of Delivery URL.
        if (is_string($arrResultParcelTracking['proofDeliveryURL'])
            && Tools::strlen($arrResultParcelTracking['proofDeliveryURL']) > 0
        ) {
            $this->pod_url = $arrResultParcelTracking['proofDeliveryURL'];
        }

        // Save the parcel status.
        $intParcelStageCode = TNTOfficielParcel::getStageCode($arrResultParcelTracking['shortStatus']);
        $this->stage_id = $intParcelStageCode;

        // If delivery_date not set and stage id is delivered state.
        if (TNTOfficiel_Tools::getDateTimeFormat($this->delivery_date) === null
            && $this->stage_id === TNTOfficielParcel::STAGE_DELIVERED
        ) {
            // Store the final date for final status.
            $this->delivery_date = $this->stage_date;

            // Use deliveryDate if available.
            if (array_key_exists('deliveryDate', $arrResultParcelTracking['events'])
                && TNTOfficiel_Tools::getDateTime($arrResultParcelTracking['events']['deliveryDate']) !== null
            ) {
                $this->delivery_date = $arrResultParcelTracking['events']['deliveryDate'];
            }
        }

        // If delivery_date set and stage id is not delivered state (tracking is no more frozen).
        if (TNTOfficiel_Tools::getDateTimeFormat($this->delivery_date) !== null
            && $this->stage_id !== TNTOfficielParcel::STAGE_DELIVERED
        ) {
            $this->delivery_date = '0000-00-00 00:00:00';
        }

        // Save the current status.
        $this->stage_status = Tools::jsonEncode(array(
            'statusCode' => $arrResultParcelTracking['statusCode'],
            'shortStatus' => $arrResultParcelTracking['shortStatus'],
            'longStatus' => $arrResultParcelTracking['longStatus'],
        ));

        // Save the events history.
        $this->stage_events = Tools::jsonEncode($arrResultParcelTracking['events']);

        // Save.
        return $this->save();
    }

    /**
     * Get the parcel delivery stage code from a short status.
     *
     * @param null $strArgShortStatus
     *
     * @return int
     */
    public static function getStageCode($strArgShortStatus = null)
    {
        TNTOfficiel_Logstack::log();

        // Save the parcel status.
        $intParcelStageCode = 0;
        if (is_string($strArgShortStatus)) {
            $arrMappingShortStatus = array(
                'En attente' => 1,
                '--' => 2,
                'En cours d\'acheminement' => 3,
                'En cours de livraison' => 4,
                'En Agence TNT' => 4, // ??
                'En agence TNT' => 4,
                'En dépôt restant' => 4,
                'Enlevé au dépôt' => TNTOfficielParcel::STAGE_DELIVERED, // ??
                'Récupéré à l\'agence TNT' => TNTOfficielParcel::STAGE_DELIVERED,
                'Récupéré sur l\'agence TNT' => TNTOfficielParcel::STAGE_DELIVERED,
                'Livré' => TNTOfficielParcel::STAGE_DELIVERED,
                'En attente de vos instructions' => 6,
                'En attente d\'instructions' => 6,
                'Incident de livraison' => 6,
                'Incident intervention' => 6,
                'Colis refusé par le destinataire' => 6,
                'Livraison reprogrammée' => 6,
                'Prise de rendez-vous en cours' => 6,
                'Problème douane' => 6,
                'Retourné à l\'expéditeur' => 7,
            );

            $intParcelStageCode = 1;
            if (array_key_exists($strArgShortStatus, $arrMappingShortStatus)) {
                $intParcelStageCode = $arrMappingShortStatus[$strArgShortStatus];
            }
        }

        return $intParcelStageCode;
    }

    /**
     * Get the stage list.
     *
     * @return array
     */
    public function getStageList()
    {
        TNTOfficiel_Logstack::log();

        $intParcelStageCode = $this->stage_id;

        // Last status in List.
        $strStatusLast = 'Livré';
        if ($intParcelStageCode === 6) {
            // En attente, refusé, douane.
            $strStatusLast = 'Incident';
        } elseif ($intParcelStageCode === 7) {
            $strStatusLast = 'Retourné à l\'expéditeur';
        }

        // Status List for display (max 5 status).
        return array(
            1 => 'Colis chez l\'expéditeur',
            2 => 'Ramassage du Colis',
            3 => 'Acheminement',
            4 => 'Livraison en cours',
            5 => $strStatusLast,
        );
    }

    /**
     * Get the current stage label.
     *
     * @return string
     */
    public function getStageLabel()
    {
        TNTOfficiel_Logstack::log();

        $intParcelStageCode = $this->stage_id;
        if ($intParcelStageCode > TNTOfficielParcel::STAGE_DELIVERED) {
            $intParcelStageCode = TNTOfficielParcel::STAGE_DELIVERED;
        }

        $arrStageList = $this->getStageList();

        $strStatusLabel = '-';
        if (array_key_exists($intParcelStageCode, $arrStageList)) {
            $strStatusLabel = $arrStageList[$intParcelStageCode];
        }

        return $strStatusLabel;
    }

    /**
     * Get the current stage color.
     *
     * @return string
     */
    public function getStageColor()
    {
        TNTOfficiel_Logstack::log();

        $intParcelStageCode = $this->stage_id;

        if ($intParcelStageCode < TNTOfficielParcel::STAGE_DELIVERED) {
            // En cours de livraison
            return '#00BBFF';
        } elseif ($intParcelStageCode === TNTOfficielParcel::STAGE_DELIVERED) {
            // Livré
            return '#33E133';
        } elseif ($intParcelStageCode === 6) {
            // Problème de livraison
            return '#FF3333';
        } elseif ($intParcelStageCode > 6) {
            // Retourné à l'expéditeur
            return '#FFAB24';
        }
    }

    /**
     * Get the stage event history list.
     *
     * @return array
     */
    public function getStageEvents()
    {
        TNTOfficiel_Logstack::log();

        $arrHistory = array();

        $arrParcelStateList = array(
            1 => 'request',
            2 => 'process',
            3 => 'arrival',
            4 => 'deliveryDeparture',
            5 => 'delivery',
        );

        $arrParcelTrackingState = array(
            'request' => 'Colis chez l\'expéditeur',
            'process' => 'Ramassage du colis',
            'arrival' => 'Acheminement du colis',
            'deliveryDeparture' => 'Livraison du colis en cours',
            'delivery' => 'Livraison du colis',
        );

        $arrParcelEvents = Tools::jsonDecode($this->stage_events, true);

        if (is_array($arrParcelEvents)) {
            foreach ($arrParcelStateList as $idx => $strState) {
                if ((isset($arrParcelEvents[$strState.'Center'])
                        || isset($arrParcelEvents[$strState.'Date'])
                    )
                    && Tools::strlen($arrParcelEvents[$strState.'Date'])
                ) {
                    $arrHistory[$idx] = array(
                        'label' => (isset($arrParcelTrackingState[$strState]) ?
                            $arrParcelTrackingState[$strState] : ''
                        ),
                        'date' => (isset($arrParcelEvents[$strState.'Date']) ?
                            $arrParcelEvents[$strState.'Date'] : ''
                        ),
                        'center' => (isset($arrParcelEvents[$strState.'Center']) ?
                            $arrParcelEvents[$strState.'Center'] : ''
                        ),
                    );
                }
            }
        }

        return $arrHistory;
    }

    /**
     * Get the stage status.
     *
     * @return array
     */
    public function getStageStatus()
    {
        $arrParcelStatus = Tools::jsonDecode($this->stage_status, true);
        if (is_array($arrParcelStatus)) {
            return $arrParcelStatus;
        }

        return array();
    }
}
