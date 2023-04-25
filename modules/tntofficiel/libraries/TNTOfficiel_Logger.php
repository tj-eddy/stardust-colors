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
 * Class TNTOfficiel_Logger
 * Used in upgrade, do not rename or remove.
 */
class TNTOfficiel_Logger
{
    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Log install.
     *
     * @param $strArgMessage
     * @param bool $boolArgSuccess
     *
     * @return bool
     */
    public static function logInstall($strArgMessage, $boolArgSuccess = true)
    {
        $objDateTimeNow = new DateTime('now', new DateTimeZone('UTC'));

        $objLogstack = TNTOfficiel_Logstack::loadLogstack('', 'install');

        $arrAppend = array(
            'time' => $objDateTimeNow->format('Y-m-d H:i:s'),
            'success' => $boolArgSuccess,
            'message' => '[i] '.$strArgMessage
        );

        // Log on one line.
        return (bool)$objLogstack->write($arrAppend, 1);
    }

    /**
     * Log uninstall.
     *
     * @param $strArgMessage
     * @param bool $boolArgSuccess
     *
     * @return bool
     */
    public static function logUninstall($strArgMessage, $boolArgSuccess = true)
    {
        $objDateTimeNow = new DateTime('now', new DateTimeZone('UTC'));

        $objLogstack = TNTOfficiel_Logstack::loadLogstack('', 'install');

        $arrAppend = array(
            'time' => $objDateTimeNow->format('Y-m-d H:i:s'),
            'success' => $boolArgSuccess,
            'message' => '[u] '.$strArgMessage
        );

        // Log on one line.
        return (bool)$objLogstack->write($arrAppend, 1);
    }

    /**
     * Log Request error and success.
     *
     * @param $strType
     * @param $strRequestName
     * @param bool $boolArgSuccess
     * @param null $ftArgDelay
     * @param null $strArgMessage
     *
     * @return bool
     */
    public static function logRequest(
        $boolArgSuccess,
        $strArgType,
        $strArgService,
        $strArgAccountNumber,
        $ftArgDelay,
        $objArgException = null
    ) {
        $objDateTimeNow = new DateTime('now', new DateTimeZone('UTC'));
        $objLogstack = TNTOfficiel_Logstack::loadLogstack('request', $objDateTimeNow->format('Y-m-d'));

        $arrAppend = array(
            'time' => $objDateTimeNow->format('H:i:s'),
            'success' => $boolArgSuccess,
            'type' => $strArgType,
            'service' => $strArgService,
            'account' => $strArgAccountNumber,
            'duration' => sprintf('%0.3f', $ftArgDelay),
            'exception' => $objArgException === null ? null : trim(preg_replace('/[[:cntrl:]]+/', ' ', (
                get_class($objArgException) . ' '
                . ((get_class($objArgException) === 'SoapFault') ? '[' . $objArgException->faultcode . '] ' : '')
                . 'Code ' . $objArgException->getCode() . ': ' . $objArgException->getMessage()
            )))
        );

        // Log on one line.
        return (bool)$objLogstack->write($arrAppend, 1);
    }

    /**
     * Log Exception.
     *
     * @param $objArgException
     *
     * @return bool
     */
    public static function logException($objArgException)
    {
        $objDateTimeNow = new DateTime('now', new DateTimeZone('UTC'));
        $objLogstack = TNTOfficiel_Logstack::loadLogstack('error', $objDateTimeNow->format('Y-m-d'));

        $arrHeader = TNTOfficiel_Logstack::header();
        $arrException = TNTOfficiel_Logstack::exception($objArgException, true);
        $arrAppend = array_merge($arrHeader, $arrException);

        // Log on multiple lines.
        return (bool)$objLogstack->write($arrAppend, 2);
    }
}
