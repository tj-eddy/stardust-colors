<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_ . 'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

/**
 * Class TNTOfficielCache
 */
class TNTOfficielCache extends ObjectModel
{
    // Delete entries expired x days ago.
    const TIME_CLEAN_EXPIRED = 60*60*24*2;
    // Delete if at least x expired entries.
    const TIME_CLEAN_ATLEAST = 1024;

    // id_tntofficiel_cache
    public $id;

    public $str_key;
    public $str_value;
    public $int_ts;

    public static $definition = array(
        'table' => 'tntofficiel_cache',
        'primary' => 'id_tntofficiel_cache',
        'fields' => array(
            'str_key' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 255,
                'required' => true
            ),
            'str_value' => array(
                'type' => ObjectModel::TYPE_STRING
                /*, 'validate' => 'isSerializedArray', 'size' => 65535*/
            ),
            'int_ts' => array(
                'type' => ObjectModel::TYPE_INT
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

        $strTableName = $strTablePrefix.TNTOfficielCache::$definition['table'];

        // Create table.
        $strSQLCreateCache = <<<SQL
CREATE TABLE IF NOT EXISTS `${strTableName}` (
    `id_tntofficiel_cache`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `str_key`                       VARCHAR(255) NOT NULL,
    `str_value`                     TEXT NULL,
    `int_ts`                        INT(10) UNSIGNED NOT NULL,
-- Key.
    PRIMARY KEY (`id_tntofficiel_cache`),
    UNIQUE INDEX `str_key` (`str_key`)
) ENGINE = ${strTableEngine} DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';
SQL;

        $boolDBResult = TNTOfficiel_Tools::getDbExecute($strSQLCreateCache);
        if (is_string($boolDBResult)) {
            TNTOfficiel_Logger::logInstall($strLogMessage.' : '.$boolDBResult, false);

            return false;
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        // Flush cache.
        TNTOfficielCache::truncate();

        return TNTOfficielCache::checkTables();
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
        $strTableName = $strTablePrefix.TNTOfficielCache::$definition['table'];
        $arrColumnsList = array_keys(TNTOfficielCache::$definition['fields']);

        return (TNTOfficiel_Tools::isTableColumnsExist($strTableName, $arrColumnsList) === true);
    }

    /**
     * Constructor.
     */
    public function __construct($intArgID = null, $intArgLangID = null, $intArgShopID = null)
    {
        TNTOfficiel_Logstack::log();

        parent::__construct($intArgID, $intArgLangID, $intArgShopID);
    }

    /**
     * Load existing object model or optionally create a new one for it's ID.
     *
     * @param $strArgKey
     * @param bool $boolArgCreate
     * @param null $intArgLangID
     * @param null $intArgShopID
     *
     * @return mixed|null|TNTOfficielCache
     *
     * @throws PrestaShopDatabaseException
     */
    public static function loadCacheKey($strArgKey, $boolArgCreate = false, $intArgLangID = null, $intArgShopID = null)
    {
        TNTOfficiel_Logstack::log();

        $strKey = (string)$strArgKey;

        $strEntityID = '_' . $strKey . '-' . (int)$intArgLangID . '-' . (int)$intArgShopID;

        // No new cache Key.
        if (preg_match('/^[0-9a-z_:]+$/ui', $strKey) !== 1) {
            return null;
        }

        if (array_key_exists($strEntityID, TNTOfficielCache::$arrLoadedEntities)) {
            $objTNTCacheModel = TNTOfficielCache::$arrLoadedEntities[$strEntityID];
            // Check.
            if (Validate::isLoadedObject($objTNTCacheModel)
                && (string)$objTNTCacheModel->str_key === $strKey
            ) {
                return $objTNTCacheModel;
            }
        }

        // Search row for cache ID.
        $objDbQuery = new DbQuery();
        $objDbQuery->select('*');
        $objDbQuery->from(TNTOfficielCache::$definition['table']);
        $objDbQuery->where('str_key = \''.pSQL($strKey).'\'');

        $arrDBResult = TNTOfficiel_Tools::getDbSelect($objDbQuery);

        // If row found and match cache ID.
        if (is_array($arrDBResult) && count($arrDBResult) === 1
            && $strKey === (string)$arrDBResult[0]['str_key']
        ) {
            // Load existing TNT cache entry.
            $objTNTCacheModel = new TNTOfficielCache(
                (int)$arrDBResult[0]['id_tntofficiel_cache'],
                $intArgLangID,
                $intArgShopID
            );
        } elseif ($boolArgCreate === true) {
            // Clean.
            TNTOfficielCache::clean();

            // Create a new TNT cache entry.
            $objTNTCacheModel = new TNTOfficielCache(null, $intArgLangID, $intArgShopID);
            $objTNTCacheModel->str_key = $strKey;
            $objTNTCacheModel->int_ts = time();
            $objTNTCacheModel->save();

            // Reload to get default DB values after creation.
            $objTNTCacheModel = TNTOfficielCache::loadCacheKey($strKey, false, $intArgLangID, $intArgShopID);
        } else {
            // TNTOfficielCache data not found for $strKey

            return null;
        }

        // Check.
        if (!Validate::isLoadedObject($objTNTCacheModel)
            || (string)$objTNTCacheModel->str_key !== $strKey
        ) {
            return null;
        }

        TNTOfficielCache::$arrLoadedEntities[$strEntityID] = $objTNTCacheModel;

        return $objTNTCacheModel;
    }

    /**
     * Delete expired entries.
     *
     * @return int
     */
    public static function clean()
    {
        TNTOfficiel_Logstack::log();

        // Search for expired row.
        $objDbQuery = new DbQuery();
        $objDbQuery->select('COUNT(*) as `expired`');
        $objDbQuery->from(TNTOfficielCache::$definition['table']);
        $objDbQuery->where('int_ts < '.(time()-TNTOfficielCache::TIME_CLEAN_EXPIRED));

        $arrDBResult = TNTOfficiel_Tools::getDbSelect($objDbQuery);

        $intExpired = 0;
        if (is_array($arrDBResult) && count($arrDBResult) === 1) {
            $intExpired = (int)$arrDBResult[0]['expired'];
            if ($intExpired > TNTOfficielCache::TIME_CLEAN_ATLEAST) {
                Db::getInstance()->delete(
                    TNTOfficielCache::$definition['table'],
                    'int_ts < '.(time()-TNTOfficielCache::TIME_CLEAN_EXPIRED)
                );
            }
        }

        return $intExpired;
    }

    /**
     * Delete all entries (truncate like).
     *
     * @return mixed
     */
    public static function truncate()
    {
        TNTOfficiel_Logstack::log();

        return Db::getInstance()->delete(TNTOfficielCache::$definition['table'], 'int_ts > 0');
    }

    /**
     * Check if a valid entry exist.
     *
     * @param string $strArgKey
     *
     * @return bool|null true if available, false if unavailable, null if unable to load entry.
     */
    public static function isStored($strArgKey)
    {
        $objCache = TNTOfficielCache::loadCacheKey($strArgKey);
        if ($objCache === null) {
            return null;
        }

        if ($objCache->int_ts > time()) {
            return true;
        }

        return false;
    }

    /**
     * Store an entry.
     *
     * @param string $strArgKey
     * @param mixed $mxdArgValue
     * @param int $intArgTTL
     *
     * @return bool|null true if stored, false if not stored, null if unable to create entry.
     */
    public static function store($strArgKey, $mxdArgValue, $intArgTTL)
    {
        // No data, no store.
        if ($mxdArgValue === null) {
            return false;
        }

        // TTL must be an integer greater than 0.
        if ($intArgTTL !== (int)$intArgTTL
            || $intArgTTL <= 0
        ) {
            return false;
        }

        $objCache = TNTOfficielCache::loadCacheKey($strArgKey, true);
        if ($objCache === null) {
            return null;
        }

        $strSerializedValue = TNTOfficiel_Tools::serialize($mxdArgValue);

        // Check binary size in bytes.
        $intSerializedLength = TNTOfficiel_Tools::strByteLength($strSerializedValue);
        if ($intSerializedLength > 65535) {
            TNTOfficiel_Logger::logException(new Exception(sprintf('Unable to store cache. Size exceed 65535 bytes.')));

            return false;
        }

        $objCache->str_value = $strSerializedValue;
        $objCache->int_ts = time() + $intArgTTL;

        return $objCache->save();
    }

    /**
     * Get an entry.
     *
     * @param string $strArgKey
     *
     * @return mixed|null null if no data available.
     */
    public static function retrieve($strArgKey)
    {
        $objCache = TNTOfficielCache::loadCacheKey($strArgKey);
        if ($objCache === null) {
            return null;
        }

        if ($objCache->int_ts > time()) {
            return TNTOfficiel_Tools::unserialize($objCache->str_value, true);
        }

        return null;
    }

    /**
     * Get a str_key identifier.
     *
     * @param string $strArgFQClassName
     * @param string $strFunctionName
     * @param array $arrArgParameters
     *
     * @return string
     */
    public static function getKeyIdentifier($strArgFQClassName, $strFunctionName, $arrArgParameters)
    {
        $strCNLS = strrchr($strArgFQClassName, '\\');
        $strUQCN = ($strCNLS === false ? $strArgFQClassName : Tools::substr($strCNLS, 1));

        $strCacheKey = $strUQCN.'::'.$strFunctionName.'_'.sha1(TNTOfficiel_Tools::serialize($arrArgParameters));

        return $strCacheKey;
    }

    /**
     * Gets the number of seconds between the current time and next midnight.
     *
     * @return int
     */
    public static function getSecondsUntilMidnight()
    {
        $intTSNow = time();
        $intTSMidnight = strtotime('tomorrow midnight');
        $intSecondsUntilMidnight = $intTSMidnight - $intTSNow;

        return $intSecondsUntilMidnight;
    }



    /**
     * @return bool
     */
    private static function getPSCacheEnabled()
    {
        return (defined('_PS_CACHE_ENABLED_') ? !!_PS_CACHE_ENABLED_ : false);
    }
}
