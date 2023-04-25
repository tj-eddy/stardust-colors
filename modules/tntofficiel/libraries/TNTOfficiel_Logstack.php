<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Logstack
{
    /**
     * @var bool Is debugging enabled ?
     * _PS_MODE_DEV_ should be false to catch all error.
     */
    private static $boolEnable = false;
    /**
     * @var array List of allowed client IP. No client IP means all allowed.
     */
    private static $arrRemoteIPAddressAllow = array();
    /**
     * @var array List of excluded request. Use Get and Post matching.
     */
    private static $arrRequestExclude = array(
        array(
            'get' => array(
                'controller' => 'AdminAjaxFaviconBO',
            ),
            'post' => array(
                'ajax' => 'true',
                'action' => 'GetNotifications',
            )
        ),
    );
    /**
     * @var int Time limit before forcing shutdown. 0 for unlimited.
     */
    private static $intShutdownTimeLimit = 0;

    /**
     * @var int Limit amount of space disk used. 0 for unlimited.
     */
    private static $intFolderSizeLimit = 1024*1024*64;

    /**
     * @var int Backtrace maximum items. 0 to disable backtrace.
     */
    private static $intBackTraceMaxDeepDefault = 0;
    private static $intBackTraceMaxDeepException = 64;
    private static $intBackTraceMaxDeepError = 64;

    /**
     * @var bool
     */
    private static $boolHandlerRegister = false;

    /**
     * @var string
     */
    private $strPath = null;
    /**
     * @var string
     */
    private $strFileLocation = null;

    /**
     * @var array PHP Errors constant name list.
     */
    private static $arrPHPErrorName = array(
        'E_ERROR',
        'E_RECOVERABLE_ERROR',
        'E_WARNING',
        'E_PARSE',
        'E_NOTICE',
        'E_STRICT',
        'E_DEPRECATED', // PHP 5.3+
        'E_CORE_ERROR',
        'E_CORE_WARNING',
        'E_COMPILE_ERROR',
        'E_COMPILE_WARNING',
        'E_USER_ERROR',
        'E_USER_WARNING',
        'E_USER_NOTICE',
        'E_USER_DEPRECATED' // PHP 5.3+
    );
    /**
     * @var array PHP Errors map. Dynamically generated using getPHPErrorType().
     */
    private static $arrPHPErrorMap = null;

    /**
     * @var array JSON Errors constant name list. PHP 5.3.0+.
     */
    private static $arrJSONErrorName = array(
        'JSON_ERROR_NONE',
        'JSON_ERROR_DEPTH',
        'JSON_ERROR_STATE_MISMATCH',
        'JSON_ERROR_CTRL_CHAR',
        'JSON_ERROR_SYNTAX',
        'JSON_ERROR_UTF8', // PHP 5.3.3+
        'JSON_ERROR_RECURSION', // PHP 5.5.0+
        'JSON_ERROR_INF_OR_NAN', // PHP 5.5.0+
        'JSON_ERROR_UNSUPPORTED_TYPE', // PHP 5.5.0+
        'JSON_ERROR_INVALID_PROPERTY_NAME', // PHP 7.0.0+
        'JSON_ERROR_UTF16', // PHP 7.0.0+
    );
    /**
     * @var array JSON Errors map. PHP 5.3.0+.
     */
    private static $arrJSONErrorMap = null;

    /**
     * @var array Error type and message pattern to exclude.
     */
    private static $arrPHPErrorExclude = array(
        'E_WARNING' => array(
            <<<'REGEXP'
/^filemtime\(\): stat failed for/ui
REGEXP
            ,
            <<<'REGEXP'
/^file_exists\(\): open_basedir restriction in effect\./ui
REGEXP
            ,
            <<<'REGEXP'
/^mkdir\(\)\: File exists/ui
REGEXP
            ,
            <<<'REGEXP'
/^file_get_contents\([^\)]++\): failed to open stream: No such file or directory/ui
REGEXP
            ,/*
            <<<'REGEXP'
/^Invalid argument supplied for foreach\(\)/ui
REGEXP
*/
        ),
        'E_USER_DEPRECATED' => array(
            <<<'REGEXP'
/^AdminMarketing is a deprecated tab since version/ui
REGEXP
            ,
            <<<'REGEXP'
/^AdminCarrierWizard is a deprecated tab since version/ui
REGEXP
            ,
            <<<'REGEXP'
/^The Swift_Transport_MailTransport class is deprecated since version/ui
REGEXP
            ,
            <<<'REGEXP'
/^Fetching the "[^"]++" private service or alias is deprecated since Symfony/ui
REGEXP
            ,
            <<<'REGEXP'
/^The "[^"]++" service is private/ui
REGEXP
            ,
            <<<'REGEXP'
/^Implementing "[^"]++" without the "[^"]++" method is deprecated/ui
REGEXP
            ,
            <<<'REGEXP'
/^Tools::displayPrice\(\) is deprecated since version/ui
REGEXP
            ,
            <<<'REGEXP'
/^Tools::displayNumber\(\) is deprecated since version/ui
REGEXP
            ,
            <<<'REGEXP'
/^Cart::getTaxesAverageUsed\(\) is deprecated since version/ui
REGEXP
        ),
        'E_NOTICE' => array(
            <<<'REGEXP'
/^(ArrayObject::)?serialize\(\): &quot;[\S\s]+?&quot; returned as member variable from __sleep\(\) but does not exist$/ui
REGEXP
        )
    );

    /**
     * @var array
     */
    private static $arrLoadedEntities = array();


    /**
     * Construct.
     */
    final private function __construct()
    {
    }

    /**
     * @param $strArgDirectory
     * @param $strArgFileName
     *
     * @return TNTOfficiel_Logstack
     */
    public static function loadLogstack($strArgDirectory = null, $strArgFileName = null)
    {
        $objLogstackCreate = new TNTOfficiel_Logstack();
        $objLogstackCreate->setDirectory($strArgDirectory);
        $objLogstackCreate->setFilename($strArgFileName);

        $strEntityID = $objLogstackCreate->getFileLocation();

        // If already loaded.
        if (array_key_exists($strEntityID, TNTOfficiel_Logstack::$arrLoadedEntities)) {
            unset($objLogstackCreate);
            return TNTOfficiel_Logstack::$arrLoadedEntities[$strEntityID];
        }

        TNTOfficiel_Logstack::$arrLoadedEntities[$strEntityID] = $objLogstackCreate;

        return $objLogstackCreate;
    }

    /**
     * Check if enabled and client IP address allowed to use debug.
     *
     * @return bool
     */
    public static function isReady()
    {
        if (TNTOfficiel_Logstack::$boolEnable !== true) {
            return false;
        }

        $strRemoteIPAddress = array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null;
        $boolIPAllowed = count(TNTOfficiel_Logstack::$arrRemoteIPAddressAllow) === 0
            || in_array($strRemoteIPAddress, TNTOfficiel_Logstack::$arrRemoteIPAddressAllow, true) === true;

        $boolRequestAllowed = true;
        foreach (TNTOfficiel_Logstack::$arrRequestExclude as $arrRequestItem) {
            if (count(array_diff_assoc(
                $arrRequestItem['get'],
                array_intersect_assoc($arrRequestItem['get'], $_GET)
            )) === 0
            && count(array_diff_assoc(
                $arrRequestItem['post'],
                array_intersect_assoc($arrRequestItem['post'], $_POST)
            )) === 0) {
                $boolRequestAllowed = false;
            }
        }

        $boolAllowed = $boolIPAllowed && $boolRequestAllowed;

        if ($boolAllowed === true) {
            // Register handlers if not.
            TNTOfficiel_Logstack::registerHandlers();
        }

        return $boolAllowed;
    }

    /**
     * Get PHP Error name from value.
     *
     * @param int $intArgType
     *
     * @return string
     */
    public static function getPHPErrorType($intArgType)
    {
        // Generate constant name mapping.
        if (TNTOfficiel_Logstack::$arrPHPErrorMap === null) {
            TNTOfficiel_Logstack::$arrPHPErrorMap = array();
            foreach (TNTOfficiel_Logstack::$arrPHPErrorName as $strPHPErrorTypeName) {
                if (defined($strPHPErrorTypeName)) {
                    // Get constant value.
                    $intPHPErrorType = constant($strPHPErrorTypeName);
                    TNTOfficiel_Logstack::$arrPHPErrorMap[ $intPHPErrorType ] = $strPHPErrorTypeName;
                }
            }
        }

        $strPHPErrorType = (string)$intArgType;
        if (array_key_exists($intArgType, TNTOfficiel_Logstack::$arrPHPErrorMap)) {
            $strPHPErrorType = TNTOfficiel_Logstack::$arrPHPErrorMap[ $intArgType ];
        }

        return $strPHPErrorType;
    }


    /**
     * Get JSON Error name from value.
     *
     * @param int $intArgType
     *
     * @return string
     */
    public static function getJSONErrorType($intArgType)
    {
        // Generate constant name mapping.
        if (TNTOfficiel_Logstack::$arrJSONErrorMap === null) {
            TNTOfficiel_Logstack::$arrJSONErrorMap = array();
            foreach (TNTOfficiel_Logstack::$arrJSONErrorName as $strJSONErrorTypeName) {
                if (defined($strJSONErrorTypeName)) {
                    // Get constant value.
                    $intJSONErrorType = constant($strJSONErrorTypeName);
                    TNTOfficiel_Logstack::$arrJSONErrorMap[ $intJSONErrorType ] = $strJSONErrorTypeName;
                }
            }
        }

        $strJSONErrorType = (string)$intArgType;
        if (array_key_exists($intArgType, TNTOfficiel_Logstack::$arrJSONErrorMap)) {
            $strJSONErrorType = TNTOfficiel_Logstack::$arrJSONErrorMap[ $intArgType ];
        }

        return $strJSONErrorType;
    }

    /**
     * Register capture handlers once.
     */
    public static function registerHandlers()
    {
        /*
        $mxdPrevExceptionHandler = true;
        while ($mxdPrevExceptionHandler !== null) {
            // Get previous error handler.
            $mxdPrevExceptionHandler = set_error_handler(array('TNTOfficiel_Logstack', 'error'));
            restore_error_handler();
            //
            if ($mxdPrevExceptionHandler !== null) {
                // Remove previous error handler like ControllerCore::myErrorHandler().
                restore_error_handler();
            }
        }
        set_error_handler(array('TNTOfficiel_Logstack', 'error'));

        $mxdPrevExceptionHandler = true;
        while ($mxdPrevExceptionHandler !== null) {
            // Get previous exception handler.
            $mxdPrevExceptionHandler = set_exception_handler(array('TNTOfficiel_Logstack', 'exception'));
            restore_exception_handler();
            //
            if ($mxdPrevExceptionHandler !== null) {
                // Remove previous exception handler.
                restore_exception_handler();
            }
        }
        set_exception_handler(array('TNTOfficiel_Logstack', 'exception'));
        */

        if (TNTOfficiel_Logstack::$boolHandlerRegister !== true) {
            TNTOfficiel_Logstack::$boolHandlerRegister = true;
            set_error_handler(array('TNTOfficiel_Logstack', 'error'));
            set_exception_handler(array('TNTOfficiel_Logstack', 'exception'));
            register_shutdown_function(array('TNTOfficiel_Logstack', 'shutdown'));
            //set_time_limit(TNTOfficiel_Logstack::$intShutdownTimeLimit);
        }
    }

    /**
     * Get the script start time.
     *
     * @return float
     */
    public static function getStartTime()
    {
        return (float)(array_key_exists('REQUEST_TIME_FLOAT', $_SERVER) ? $_SERVER['REQUEST_TIME_FLOAT'] :
            $_SERVER['REQUEST_TIME']);
    }

    /**
     * Get the root path.
     *
     * @return string
     */
    public static function getRootPath()
    {
        // _PS_ROOT_DIR_.'/log/';
        return _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR;
    }

    /**
     * Get the default directory.
     *
     * @return string
     */
    public static function getDefaultDirectory()
    {
        // Get Optional IP folder.
        $strPathClientIP = '';
        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $strPathClientIP = preg_replace('/[^a-z0-9_]+/ui', '-', $_SERVER['REMOTE_ADDR']).DIRECTORY_SEPARATOR;
        }

        return $strPathClientIP;
    }

    /**
     * Get the default filename.
     *
     * @return string
     */
    public static function getDefaultFilename()
    {
        $floatScriptTime = TNTOfficiel_Logstack::getStartTime();
        $strTimeStampExport = var_export($floatScriptTime, true);
        $strTimeStampMaxWidth = preg_replace('/^([0-9]+(?:\.[0-9]{1,6}))[0-9]*$/ui', '$1', $strTimeStampExport);
        $strTimeStampNum = preg_replace('/\./ui', '', $strTimeStampMaxWidth);
        $strTimeStamp = sprintf('%-016s', $strTimeStampNum);

        $strShopFullContextID = 'G'.(int)Shop::getContextShopGroupID().'S'.(int)Shop::getContextShopID();

        $strFileName = $strTimeStamp.'-'.$strShopFullContextID.'-'
            .preg_replace('/[^a-z0-9_]+/ui', '-', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        return $strFileName;
    }

    /**
     * Set log path directory using root directory.
     *
     * @param string $strArgDirectory. null for default.
     *
     * @return bool
     */
    public function setDirectory($strArgDirectory = null)
    {
        $this->strPath = null;

        // Keep default.
        if (!is_string($strArgDirectory)) {
            return false;
        }

        $strRoot = TNTOfficiel_Logstack::getRootPath();

        $strPath = $strRoot.$strArgDirectory;
        // Add final separator.
        if (Tools::substr($strPath, -1) !== DIRECTORY_SEPARATOR) {
            $strPath .= DIRECTORY_SEPARATOR;
        }

        // Create folder.
        TNTOfficiel_Tools::makeDirectory($strPath);

        $strPath = realpath($strPath);
        // no real path existing.
        if ($strPath === false) {
            return false;
        }

        // Add final separator.
        if (Tools::substr($strPath, -1) !== DIRECTORY_SEPARATOR) {
            $strPath .= DIRECTORY_SEPARATOR;
        }

        // If not a writable directory.
        if (!is_dir($strPath) || !is_writable($strPath)) {
            return false;
        }

        // Save.
        $this->strPath = $strPath;

        return true;
    }

    /**
     * Get the path.
     *
     * @return string|null
     */
    public function getPath()
    {
        // Default path.
        if ($this->strPath === null) {
            $strPath = TNTOfficiel_Logstack::getDefaultDirectory();
            $this->setDirectory($strPath);
        }

        return $this->strPath;
    }

    /**
     * @param string $strArgFileName. null for default.
     *
     * @return bool
     */
    public function setFilename($strArgFileName = null)
    {
        $this->strFileLocation = null;

        $strPath = $this->getPath();

        if ($strPath === null) {
            return false;
        }

        // Keep default.
        if (!is_string($strArgFileName) || $strArgFileName === '') {
            return false;
        }

        // Save.
        $this->strFileLocation = $strPath.$strArgFileName.'.json';

        return true;
    }

    /**
     * Get log filename.
     *
     * @return string|null
     */
    public function getFileLocation()
    {
        // Default filename.
        if ($this->strFileLocation === null) {
            $strFileName = TNTOfficiel_Logstack::getDefaultFilename();
            $this->setFilename($strFileName);
        }

        return $this->strFileLocation;
    }

    /**
     * @param $strArgPath
     * @param array $arrArgAllowedExt
     * @param $intFolderSizeLimit
     *
     * @return array
     */
    public static function removeFiles($strArgPath, $arrArgAllowedExt = array(), $intFolderSizeLimit = 0)
    {
        if (!($intFolderSizeLimit > 0)) {
            return array();
        }

        $arrTime = array();
        foreach ($arrArgAllowedExt as $strExt) {
            $arrFiles = Tools::scandir($strArgPath, $strExt, '', false);
            foreach ($arrFiles as $strFileName) {
                $strFileLocation = $strArgPath.$strFileName;
                if (file_exists($strFileLocation)) {
                    $arrTime[$strFileLocation] = filemtime($strFileLocation);
                }
            }
        }

        arsort($arrTime);
        array_shift($arrTime);

        $fltSize = 0;
        $arrDel = array();
        foreach ($arrTime as $strFileLocation => $intTime) {
            $fltSize += (float)filesize($strFileLocation);
            if ($fltSize > $intFolderSizeLimit) {
                try {
                    // Delete file.
                    $arrDel[] = $strFileLocation;
                    Tools::deleteFile($strFileLocation);
                } catch (Exception $objException) {
                    TNTOfficiel_Logger::logException($objException);
                }
            }
        }

        return $arrDel;
    }

    /**
     * Create log file if do not exist.
     * Log global info at creation.
     *
     * @param array $arrArgWriteInfo
     *
     * @return bool
     */
    public function write($arrArgWriteInfo, $intArgPettyLevel = 2)
    {
        $strFileLocation = $this->getFileLocation();

        if ($strFileLocation === null) {
            return false;
        }

        $strWriteInfo = TNTOfficiel_Tools::encJSON($arrArgWriteInfo, $intArgPettyLevel);

        if (!is_string($strWriteInfo) || $strWriteInfo === '') {
            return false;
        }

        // If file not already exist.
        if (!file_exists($strFileLocation)) {
            // Remove old log file if necessary.
            TNTOfficiel_Logstack::removeFiles(
                dirname($strFileLocation).DIRECTORY_SEPARATOR,
                array('log', 'json'),
                TNTOfficiel_Logstack::$intFolderSizeLimit
            );

            touch($strFileLocation);
            @chmod($strFileLocation, 0660);
        }

        // If file empty.
        if (!(strlen(file_get_contents($strFileLocation)) > 0)) {
            $strWriteInfo = '['.$strWriteInfo;
        } else {
            $rscPointer = fopen($strFileLocation, 'r+');
            // LOCK_EX : EXclusive, to acquire an exclusive lock (for wrinting).
            // LOCK_NB : Non Blocking.
            if (flock($rscPointer, LOCK_EX /*| LOCK_NB*/)) {
                $arrStat = fstat($rscPointer);
                fseek($rscPointer, -1, SEEK_END);
                $strChar = fgetc($rscPointer);
                if ($strChar === ']') {
                    ftruncate($rscPointer, $arrStat['size']-1);
                }
                // LOCK_UN : UNlock, to release a lock (shared or exclusive).
                flock($rscPointer, LOCK_UN);
            } else {
                // No lock (only for LOCK_NB) or error.
            }

            fclose($rscPointer);

            $strWriteInfo = ','.($intArgPettyLevel <= 1 ? "\n" : '').$strWriteInfo;
        }

        $strWriteInfo = $strWriteInfo.']';

        return file_put_contents($strFileLocation, $strWriteInfo, FILE_APPEND | LOCK_EX) > 0;
    }

    /**
     * @param bool $boolArgIncludeType
     * @param bool $boolArgIncludeCookie
     * @param bool $boolArgIncludeConfig
     *
     * @return array
     */
    public static function header($boolArgIncludeType = false, $boolArgIncludeCookie = false, $boolArgIncludeConfig = false)
    {
        $objDateTimeNow = new DateTime('@'.(int)TNTOfficiel_Logstack::getStartTime());
        $objDateTimeNow->setTimezone(new DateTimeZone('UTC'));
        $strDate = $objDateTimeNow->format('Y-m-d H:i:s P T (e)');

        $arrHeaderInfo = array();

        if ($boolArgIncludeType === true) {
            $arrHeaderInfo['type'] = 'Header';
        }

        $arrHeaderInfo += array(
            'date' => $strDate,
            'uri' => array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : null,
            'referer' => array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : null,
            'client' => array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
            'post' => $_POST,
            'get' => $_GET
        );

        if ($boolArgIncludeCookie === true) {
            $arrHeaderInfo['cookie'] = $_COOKIE;
        }

        if ($boolArgIncludeConfig === true) {
            $arrHeaderInfo['config'] = array(
                'php' => TNTOfficiel_Tools::getPHPConfig(),
                'ps' => TNTOfficiel_Tools::getPSConfig()
            );
        }

        return $arrHeaderInfo;
    }

    /**
     * Append stack info.
     *
     * @param null $arrArg
     * @param null $intArgBackTraceMaxDeep
     * @param bool $mxdArgStream
     *
     * @return array|null
     */
    public static function append($arrArg = null, $intArgBackTraceMaxDeep = null, $mxdArgStream = false)
    {
        $intBackTraceMaxDeep = TNTOfficiel_Logstack::$intBackTraceMaxDeepDefault;
        if ( $intArgBackTraceMaxDeep >= 0 ) {
            $intBackTraceMaxDeep = $intArgBackTraceMaxDeep;
        }

        if ($arrArg === null) {
            $arrArg = array();
        }

        if (!is_array($arrArg)) {
            $arrArg = array('raw' => $arrArg);
        }

        // If message, file and line exist, then concat.
        if (array_key_exists('message', $arrArg)
            && array_key_exists('file', $arrArg)
            && array_key_exists('line', $arrArg)
        ) {
            $arrArg['message'] .= ' \''.$arrArg['file'].'\' on line '.$arrArg['line'];
            unset($arrArg['file']);
            unset($arrArg['line']);
        }

        // If no backtrace and auto backtrace set.
        if ($intBackTraceMaxDeep > 0
            && (!array_key_exists('trace', $arrArg) || !is_array($arrArg['trace']))
        ) {
            // Get current one.
            $arrArg['trace'] = debug_backtrace();
        }

        // Process backtrace.
        if (array_key_exists('trace', $arrArg) && is_array($arrArg['trace'])) {
            // Final backtrace.
            $arrTraceStack = array();
            // Backtrace Path.
            //$arrArg['path'] = array();

            // Get each trace, deeper first.
            while ($arrTrace = array_shift($arrArg['trace'])) {
                $intDeepIndex = count($arrTraceStack);
                //$intTraceIndex = count($arrArg['trace']);

                // Get stack with maximum items.
                if ($intDeepIndex >= $intBackTraceMaxDeep) {
                    continue;
                }

                // function
                if (array_key_exists('class', $arrTrace) && is_string($arrTrace['class'])) {
                    // Exclude this class.
                    if (in_array($arrTrace['class'], array(__CLASS__), true)) {
                        continue;
                    }
                    $arrTrace['function'] = $arrTrace['class'].$arrTrace['type'].$arrTrace['function'];
                }
                // file
                if (array_key_exists('file', $arrTrace) && is_string($arrTrace['file'])) {
                    $arrTrace['file'] = '\''.$arrTrace['file'].'\' on line '.$arrTrace['line'];
                } else {
                    $arrTrace['file'] = '[Internal]';
                }

                // arguments
                if (array_key_exists('args', $arrTrace) && is_array($arrTrace['args'])) {
                    $arrTrace['function'] .= '(' . count($arrTrace['args']) . ')';

                    //if (array_key_exists('dump', $arrArg) && is_array($arrArg['dump'])) {
                    //    $arrArg['path'][$intTraceIndex] = $arrTrace['function'] . ' in ' . $arrTrace['file'];
                    //}
                }

                if (array_key_exists('args', $arrTrace) && is_array($arrTrace['args'])) {
                    $arrCallerArgs = array();
                    foreach ($arrTrace['args'] as $k => $mxdTraceArg) {
                        $arrCallerArgs[$k] = TNTOfficiel_Tools::dumpSafe($mxdTraceArg);
                    }
                    $arrTrace['args'] = $arrCallerArgs;

                    // If no arguments.
                    if (count($arrTrace['args']) === 0) {
                        // Remove key (no line output).
                        unset($arrTrace['args']);
                    }
                }

                unset($arrTrace['line']);
                unset($arrTrace['class']);
                unset($arrTrace['type']);
                unset($arrTrace['object']);

                $arrTrace = TNTOfficiel_Tools::arrayOrderKey(
                    $arrTrace,
                    array('file', 'function', 'args')
                );

                // Add trace.
                $arrTraceStack[$intDeepIndex] = $arrTrace;
            }

            //if (count($arrArg['path']) > 0) {
            //    $arrArg['path'] = array_reverse($arrArg['path']);
            //} else {
            //    unset($arrArg['path']);
            //}

            // Save processed backtrace.
            $arrArg['trace'] = $arrTraceStack;

            // Remove backtrace key if empty.
            if (count($arrArg['trace']) === 0) {
                unset($arrArg['trace']);
            }
        }

        // Append time and memory consumption.
        $arrArg += array(
            'time' => microtime(true) - TNTOfficiel_Logstack::getStartTime(),
            'mem' => memory_get_peak_usage() / 1024 / 1024,
        );

        $arrArg = TNTOfficiel_Tools::arrayOrderKey(
            $arrArg,
            array('time', 'mem', 'type', 'message', 'file', 'line', 'raw', 'path', 'dump', 'trace')
        );

        // Specified logger.
        if (is_object($mxdArgStream)
            && get_class($mxdArgStream) === __CLASS__
        ) {
            $mxdArgStream->write($arrArg);
        }

        // Default logger.
        if ($mxdArgStream === false) {
            // Default logger if enabled and client IP allowed.
            if (TNTOfficiel_Logstack::isReady() === true) {
                $objLogstackDefault = TNTOfficiel_Logstack::loadLogstack();
                // If file don't already exist.
                if (!file_exists($objLogstackDefault->getFileLocation())) {
                    $objLogstackDefault->write(TNTOfficiel_Logstack::header(true, true, true));
                }
                $objLogstackDefault->write($arrArg);

                // Timeout.
                if (array_key_exists('time', $arrArg)
                    && TNTOfficiel_Logstack::$intShutdownTimeLimit > 0
                    && $arrArg['time'] > TNTOfficiel_Logstack::$intShutdownTimeLimit
                ) {
                    exit;
                }
            }
        }

        return $arrArg;
    }

    /**
     * Append log info.
     *
     * @param array|null $arrArg
     * @param int|null $intArgBackTraceMaxDeep
     *
     * @return string
     */
    public static function log($arrArg = null, $intArgBackTraceMaxDeep = null, $mxdArgStream = false)
    {
        $intBackTraceMaxDeep = TNTOfficiel_Logstack::$intBackTraceMaxDeepDefault;
        if ($intArgBackTraceMaxDeep > 0) {
            $intBackTraceMaxDeep = $intArgBackTraceMaxDeep;
        }

        if ($arrArg === null && $intBackTraceMaxDeep === 0) {
            return $arrArg;
        }

        return TNTOfficiel_Logstack::append($arrArg, $intBackTraceMaxDeep, $mxdArgStream);
    }

    /**
     * Append dump info without backtrace.
     *
     * @param array|null $arrArg
     *
     * @return string
     */
    public static function dump($arrArg = null, $mxdArgStream = false)
    {
        return TNTOfficiel_Logstack::append(array('dump' => $arrArg), 0, $mxdArgStream);
    }

    /**
     * Append an Exception. Capture an Exception.
     *
     * @param Exception $objArgException
     */
    public static function exception($objArgException, $mxdArgStream = false)
    {
        $arrAppendException = array(
            'type' => get_class($objArgException)
        );

        if ($objArgException->getFile() !== null) {
            $arrAppendException['file'] = $objArgException->getFile();
            $arrAppendException['line'] = $objArgException->getLine();
        }

        $arrAppendException['message'] = array();
        if (get_class($objArgException) === 'SoapFault') {
            $arrAppendException['message'][] = '[' . $objArgException->faultcode . ']';
        }
        $arrAppendException['message'][] = 'Code ' . $objArgException->getCode() . ':';
        $arrAppendException['message'][] = $objArgException->getMessage();
        $arrAppendException['message'] = implode(' ', $arrAppendException['message']);

        $arrAppendException['trace'] = $objArgException->getTrace();

        return TNTOfficiel_Logstack::append(
            $arrAppendException,
            TNTOfficiel_Logstack::$intBackTraceMaxDeepException,
            $mxdArgStream
        );
    }

    /**
     * Append backtrace info.
     *
     * @param null $intArgBackTraceMaxDeep
     *
     * @return string
     */
    public static function backtrace($intArgBackTraceMaxDeep = null, $mxdArgStream = false)
    {
        $intBackTraceMaxDeep = TNTOfficiel_Logstack::$intBackTraceMaxDeepException;
        if ( $intArgBackTraceMaxDeep > 0 ) {
            $intBackTraceMaxDeep = $intArgBackTraceMaxDeep;
        }

        return TNTOfficiel_Logstack::append(array(), $intBackTraceMaxDeep, $mxdArgStream);
    }

    /**
     * Capture an Error.
     *
     * @param int $intArgType
     * @param string $strArgMessage
     * @param string|null $strArgFile
     * @param int $intArgLine
     * @param array $arrArgContext
     * @param bool $boolArgIsLast
     *
     * @return bool
     */
    public static function error(
        $intArgType,
        $strArgMessage,
        $strArgFile = null,
        $intArgLine = 0,
        $arrArgContext = array(),
        $boolArgIsLast = false
    ) {
        $arrLogError = array(
            'type' => $boolArgIsLast ? 'LastError' : 'Error'
        );

        if ($strArgFile !== null) {
            $arrLogError['file'] = $strArgFile;
            $arrLogError['line'] = $intArgLine;
        }

        if ($boolArgIsLast) {
            $arrLogError['trace'] = array();
        }

        $strPHPErrorType = TNTOfficiel_Logstack::getPHPErrorType($intArgType);
        $arrLogError['message'] = 'Type '.$strPHPErrorType.': '.$strArgMessage;

        if (array_key_exists($strPHPErrorType, TNTOfficiel_Logstack::$arrPHPErrorExclude)
            && is_array(TNTOfficiel_Logstack::$arrPHPErrorExclude[$strPHPErrorType])
        ) {
            if (count(TNTOfficiel_Logstack::$arrPHPErrorExclude[$strPHPErrorType]) === 0) {
                return false;
            }
            foreach (TNTOfficiel_Logstack::$arrPHPErrorExclude[$strPHPErrorType] as $strPattern) {
                if (preg_match($strPattern, $strArgMessage) === 1) {
                    return false;
                }
            }
        }

        TNTOfficiel_Logstack::append($arrLogError, TNTOfficiel_Logstack::$intBackTraceMaxDeepError);

        // Internal error handler continues (displays/log error, …)
        return false;
    }

    /**
     * Capture at shutdown.
     */
    public static function shutdown()
    {
        TNTOfficiel_Logstack::lastError();
        TNTOfficiel_Logstack::connectionStatus();
        TNTOfficiel_Logstack::outputBufferStatus();
    }

    /**
     * Append last error for capture at shutdown.
     * Useful at script end for E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, …
     */
    public static function lastError()
    {
        $arrPHPLastError = error_get_last();

        if (is_array($arrPHPLastError)) {
            TNTOfficiel_Logstack::error(
                $arrPHPLastError['type'],
                $arrPHPLastError['message'],
                $arrPHPLastError['file'],
                $arrPHPLastError['line'],
                array(),
                true
            );
        }

        // PHP 5.3.0+
        if (function_exists('json_last_error')) {
            $intTypeJSONLastError = json_last_error();
            $strJSONLastErrorType = TNTOfficiel_Logstack::getJSONErrorType($intTypeJSONLastError);
            if ($strJSONLastErrorType !== 'JSON_ERROR_NONE') {
                // PHP 5.5.0+
                $strJSONLastErrorMessage = function_exists('json_last_error_msg') ? json_last_error_msg() : 'N/A';
                TNTOfficiel_Logstack::error(
                    $strJSONLastErrorType,
                    $strJSONLastErrorMessage,
                    null,
                    0,
                    array(),
                    true
                );
            }
        }

        // PHP 5.3.0+
        if (function_exists('date_get_last_errors')) {
            $arrTypeDateLastError = date_get_last_errors();
            if (is_array($arrTypeDateLastError)) {
                if (array_key_exists('errors', $arrTypeDateLastError)
                    && count($arrTypeDateLastError['errors']) > 0
                ) {
                    $strDateLastErrorType = 'DATE_ERROR';
                    $strDateLastErrorMessage = TNTOfficiel_Tools::encJSON($arrTypeDateLastError['errors']);
                    TNTOfficiel_Logstack::error(
                        $strDateLastErrorType,
                        $strDateLastErrorMessage,
                        null,
                        0,
                        array(),
                        true
                    );
                }
                if (array_key_exists('warnings', $arrTypeDateLastError)
                    && count($arrTypeDateLastError['warnings']) > 0
                ) {
                    $strDateLastErrorType = 'DATE_WARNING';
                    $strDateLastErrorMessage = TNTOfficiel_Tools::encJSON($arrTypeDateLastError['warnings']);
                    TNTOfficiel_Logstack::error(
                        $strDateLastErrorType,
                        $strDateLastErrorMessage,
                        null,
                        0,
                        array(),
                        true
                    );
                }
            }
        }
    }

    /**
     * Append connection status for capture at shutdown.
     */
    public static function connectionStatus()
    {
        // is non normal connection status.
        $intStatus = connection_status();
        // connection_aborted()
        if ($intStatus & 1) {
            TNTOfficiel_Logstack::append(array(
                'type' => 'ConnectionStatus',
                'message' => sprintf('Connection was aborted by user.')
            ));
        }
        if ($intStatus & 2) {
            TNTOfficiel_Logstack::append(array(
                'type' => 'ConnectionStatus',
                'message' => sprintf('Script exceeded maximum execution time.')
            ));
        }
    }

    /**
     * Append output buffer status for capture at shutdown.
     */
    public static function outputBufferStatus()
    {
        $strMsg = 'Output was not sent yet.';

        // is output buffer was sent
        $strOutputBufferFile = null;
        $intOutputBufferLine = null;
        $boolOutputBufferSent = headers_sent($strOutputBufferFile, $intOutputBufferLine);
        if ($boolOutputBufferSent) {
            $strMsg = sprintf('Output was sent in \'%s\' on line %s.', $strOutputBufferFile, $intOutputBufferLine);
        }

        TNTOfficiel_Logstack::append(array(
            'type' => 'OutputBufferStatus',
            'message' => $strMsg,
            'headers' => headers_list(),
            'level' => ob_get_level()
        ), 0);
    }
}
