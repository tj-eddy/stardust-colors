<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Tools
{
    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Get variable type for dump.
     *
     * @param $args
     *
     * @return string
     */
    public static function dumpType($mxdArgValue, $arrExclude = array('NULL', 'boolean', 'integer', 'string'))
    {
        // Get type.
        $strType = gettype($mxdArgValue);

        if (in_array($strType, $arrExclude)) {
            return '';
        }

        if (is_object($mxdArgValue)) {
            $strType = '('.$strType.')'.get_class($mxdArgValue);
        } elseif (is_resource($mxdArgValue)) {
            $strType = '('.$strType.')'.get_resource_type($mxdArgValue);
        } else {
            $strType = '('.$strType.')';
        }

        return $strType;
    }

    /**
     * Get variable safe.
     * Prevent circular reference, etc ...
     *
     * @param $mxdArgValue
     *
     * @return mixed. null if unable to serialize or encode to JSON.
     */
    public static function getSafe($mxdArgValue)
    {
        try {
            // If unable to serialize.
            serialize($mxdArgValue);
            // If unable to encode to JSON.
            $strTestJSON = json_encode($mxdArgValue);
            if (!is_string($strTestJSON) || ($mxdArgValue !== '' && $strTestJSON === '')) {
                return null;
            }
        } catch (Exception $objException) {
            return null;
        }

        return $mxdArgValue;
    }

    /**
     * Get memory usage estimation.
     *
     * @param $mxdArgValue
     *
     * @return int
     */
    public static function getMem($mxdArgValue)
    {
        $intMemStart = memory_get_usage();

        try {
            // Temporary assignment is required.
            $strTmp = unserialize(serialize($mxdArgValue));
        } catch (Exception $objException) {
            $strTmp = null;
        }

        return memory_get_usage() - $intMemStart;
    }

    /**
     * Get variable safe for dump usage.
     * Prevent out of memory, circular reference and others recursive endless loop.
     *
     * @param     $mxdArgValue
     * @param int $intArgMemLimit Default dump memory limit is 1 Mib.
     * @param int $intArgMaxDepth
     *
     * @return mixed
     */
    public static function dumpSafe($mxdArgValue, $intArgMemLimit = 1048576, $intArgMaxDepth = 4)
    {
        $intMemLimit = (int)$intArgMemLimit;
        $intMaxDepth = (int)$intArgMaxDepth;
        --$intMaxDepth;

        $strType = gettype($mxdArgValue);
        $arrScalarType = array('NULL', 'boolean', 'integer', 'string');
        $boolIsScalar = in_array($strType, $arrScalarType);

        if ($intMaxDepth < 0 && !$boolIsScalar) {
            return '…';
        } elseif ((is_object($mxdArgValue) || is_array($mxdArgValue))) {
            try {
                $arrValueSafe = array();
                $intPropCount = max(count((array)$mxdArgValue), 1);
                $intPropMemLimit = $intMemLimit / $intPropCount;
                foreach ($mxdArgValue as $k => $mxdPropItem) {
                    $intPropMemSize = TNTOfficiel_Tools::getMem($mxdPropItem);
                    $arrValueSafe[$k] = '…';
                    if ($intPropMemSize <= $intPropMemLimit) {
                        $arrValueSafe[$k] = TNTOfficiel_Tools::dumpSafe(
                            $mxdPropItem,
                            $intPropMemLimit,
                            $intMaxDepth
                        );
                    }
                }

                return array(TNTOfficiel_Tools::dumpType($mxdArgValue) => $arrValueSafe);
            } catch (Exception $objException) {
                return '…E';
            }
        }

        $intMemSize = TNTOfficiel_Tools::getMem($mxdArgValue);
        $mxdArgValueSafe = '…';
        if ($intMemSize <= $intMemLimit) {
            $mxdArgValueSafe = TNTOfficiel_Tools::getSafe($mxdArgValue);
        }

        return ($boolIsScalar ? $mxdArgValueSafe : array(
            TNTOfficiel_Tools::dumpType($mxdArgValue) => $mxdArgValueSafe,
        ));
    }

    /**
     * Encode to JSON.
     *
     * @param array $mxdArgValue
     * @param int   $intArgPettyLevel
     *
     * @return string
     */
    public static function encJSON($mxdArgValue, $intArgPettyLevel = 3)
    {
        $flagJSONEncode = 0;
        if ($intArgPettyLevel > 0) {
            $flagJSONEncode |= defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0;
        }
        // Unescape.
        $flagJSONEncode |= defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0;
        $flagJSONEncode |= defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0;
        // Display 0.0
        $flagJSONEncode |= defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0;

        //$flagJSONEncode |= defined('JSON_PARTIAL_OUTPUT_ON_ERROR') ? JSON_PARTIAL_OUTPUT_ON_ERROR : 0;
        //$flagJSONEncode |= defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0;

        // PHP < 5.3 return null if second parameter is used.
        $strJSON = ($flagJSONEncode === 0 ? json_encode($mxdArgValue) : json_encode($mxdArgValue, $flagJSONEncode));

        if ($intArgPettyLevel > 0 && $intArgPettyLevel <= 2) {
            // indent to 2 spaces.
            $strJSON = preg_replace_callback('/(^|\n)(\ ++)/ui', array('TNTOfficiel_Tools', 'cbIndentSpace'), $strJSON);
            // before }
            $strJSON = preg_replace('/(?<![\}\]])\n\s*+(?=\}(?!$))/ui', '', $strJSON);
            // before }
            $strJSON = preg_replace('/(?<![\}])\n\s*+(?=\},)/ui', '', $strJSON);
            // before {
            $strJSON = preg_replace('/(?<=\[|,)\n\s*+(?=\{)/ui', '', $strJSON);
            // after }
            $strJSON = preg_replace('/(?<=\})\n\s*+(?=\])/ui', '', $strJSON);
            // after }
            $strJSON = preg_replace('/(?<=\})\n\s++(?=}(?!\]))/ui', '', $strJSON);
            // before ]
            $strJSON = preg_replace('/\n\s*+(?=\])/ui', '', $strJSON);
        }
        if ($intArgPettyLevel > 0 && $intArgPettyLevel <= 1) {
            $strJSON = preg_replace('/(?<=,)\n\s*+/ui', ' ', $strJSON);
            $strJSON = preg_replace('/(?<=\{|\[)\n\s*+/ui', '', $strJSON);
            $strJSON = preg_replace('/\n\s*+(?=\})/ui', '', $strJSON);
        }

        return $strJSON;
    }

    /**
     * Callback.
     *
     * @param $arrArgMatches
     *
     * @return string
     */
    public static function cbIndentSpace($arrArgMatches)
    {
        return $arrArgMatches[1].str_repeat(' ', (int)(strlen($arrArgMatches[2]) / 2));
    }

    /**
     * Get Controller Name.
     *
     * @param $objArgController
     *
     * @return mixed
     */
    public static function getControllerName($objArgController)
    {
        //$strControllerName = Tools::strtolower(get_class($objArgController));

        if (isset($objArgController->controller_name)) {
            $strControllerName = $objArgController->controller_name;
        } else {
            $strControllerName = $objArgController->php_self;
        }

        $strControllerName = preg_replace('/[^a-z0-9_]+/ui', '', $strControllerName);
        $strControllerName = Tools::strtolower($strControllerName.'controller');

        return $strControllerName;
    }

    /**
     * Get Bootstrap HTML alert.
     *
     * @param type $arrArgAlert
     *
     * @return string
     */
    public static function getAlertHTML($arrArgAlert)
    {
        // Define message sort.
        $arrAlertHTML = array(
            'info' => null,
            'warning' => null,
            'success' => null,
            'error' => null,
        );

        foreach ($arrArgAlert as $strAlertType => $arrAlertMsg) {
            if (count($arrAlertMsg) > 0) {
                foreach ($arrAlertMsg as $k => $a) {
                    if (is_array($a)) {
                        $arrAlertMsg[$k] = $k.": ".implode("\n ", $a);
                    }
                }

                $arrAlertMsg = array_map('htmlentities', $arrAlertMsg);
                if ($strAlertType == 'info') {
                    $arrAlertHTML[$strAlertType] = '<div class="alert alert-info">'
                        .(count($arrAlertMsg) === 1 ?
                            array_shift($arrAlertMsg) : ('<ul><li>'.implode('</li><li>', $arrAlertMsg).'</li></ul>'))
                        .'</div>';
                } elseif ($strAlertType == 'warning') {
                    $arrAlertHTML[$strAlertType] = '<div class="alert alert-warning">'
                        .(count($arrAlertMsg) === 1 ?
                            array_shift($arrAlertMsg) : ('<ul><li>'.implode('</li><li>', $arrAlertMsg).'</li></ul>'))
                        .'</div>';
                } elseif ($strAlertType == 'success') {
                    $arrAlertHTML[$strAlertType] = '<div class="alert alert-success">'
                        .(count($arrAlertMsg) === 1 ?
                            array_shift($arrAlertMsg) : ('<ul><li>'.implode('</li><li>', $arrAlertMsg).'</li></ul>'))
                        .'</div>';
                } elseif ($strAlertType == 'error') {
                    $arrAlertHTML[$strAlertType] = '<div class="alert alert-danger">'
                        .(count($arrAlertMsg) === 1 ?
                            array_shift($arrAlertMsg) : ('<ul><li>'.implode('</li><li>', $arrAlertMsg).'</li></ul>'))
                        .'</div>';
                }
            }

            if (array_key_exists($strAlertType, $arrAlertHTML) && !$arrAlertHTML[$strAlertType]) {
                unset($arrAlertHTML[$strAlertType]);
            }
        }

        return $arrAlertHTML;
    }

    /**
     * Validate a Fixed Phone (FR,MC only).
     *
     * @param string $strArgISOCode    The ISO Country Code.
     * @param string $strArgPhoneFixed The Fixed Phone Number.
     *
     * @return bool|string The Formated Fixed Phone String if valid, else false.
     */
    public static function validateFixedPhone($strArgISOCode, $strArgPhoneFixed)
    {
        if (!is_string($strArgISOCode)) {
            return false;
        }
        if (!is_string($strArgPhoneFixed)) {
            return false;
        }

        // Format par pays.
        $arrPhoneFormatCountry = array(
            'FR' => array(
                'strCoutryCode' => '33',
                'strTrunkp' => '0',
                'strFixed' => '([1234589])([0-9]{8})',
            ),
            'MC' => array(
                'strCoutryCode' => '377',
                'strTrunkp' => '',
                'strFixed' => '([89])([0-9]{7})',
            ),
        );

        $strISOCode = Tools::strtoupper($strArgISOCode);
        if (!array_key_exists($strISOCode, $arrPhoneFormatCountry)) {
            return false;
        }

        // Check allowed character.
        if (!Validate::isPhoneNumber($strArgPhoneFixed)) {
            return false;
        }

        // Get Country Data.
        $arrPhoneFormat = $arrPhoneFormatCountry[$strISOCode];
        // Cleaning Phone Input.
        $strPhoneFixedClean = preg_replace('/[^+0-9()]/ui', '', $strArgPhoneFixed);
        // Root.
        $strRoot = '(?:(?:(?:\+|00)'.$arrPhoneFormat['strCoutryCode']
            .'(?:\('.$arrPhoneFormat['strTrunkp'].'\))?)|'.$arrPhoneFormat['strTrunkp'].')';

        if (preg_match('/^'.$strRoot.'('.$arrPhoneFormat['strFixed'].')$/ui', $strPhoneFixedClean, $matches)) {
            $strPhoneFixedID = $arrPhoneFormat['strTrunkp'].$matches[1];
            $strPhoneFixedIDLength = Tools::strlen($strPhoneFixedID);

            if ($strPhoneFixedIDLength < 1 || $strPhoneFixedIDLength > 63) {
                return false;
            }

            return $strPhoneFixedID;
        }

        return false;
    }

    /**
     * Validate a Mobile Phone (FR,MC only).
     *
     * @param string $strArgCountryISO  The ISO Country Code.
     * @param string $strArgPhoneMobile The Mobile Phone Number.
     *
     * @return bool|string The Formated Mobile Phone String if valid, else false.
     */
    public static function validateMobilePhone($strArgCountryISO, $strArgPhoneMobile)
    {
        if (!is_string($strArgCountryISO)) {
            return false;
        }
        if (!is_string($strArgPhoneMobile)) {
            return false;
        }

        // Format par pays.
        $arrPhoneFormatCountry = array(
            'FR' => array(
                'strCoutryCode' => '33',
                'strTrunkp' => '0',
                'strMobile' => '([67])([0-9]{8})',
            ),
            'MC' => array(
                'strCoutryCode' => '377',
                'strTrunkp' => '',
                'strMobile' => '(?:([34])([0-9]{7})|([6])([0-9]{8}))',
            ),
        );

        $strCountryISO = Tools::strtoupper(trim($strArgCountryISO));
        if (!array_key_exists($strCountryISO, $arrPhoneFormatCountry)) {
            return false;
        }

        // Check allowed character.
        if (!Validate::isPhoneNumber($strArgPhoneMobile)) {
            return false;
        }

        // Get Country Data.
        $arrPhoneFormat = $arrPhoneFormatCountry[$strCountryISO];
        // Cleaning Phone Input.
        $strPhoneMobileClean = preg_replace('/[^+0-9()]/ui', '', $strArgPhoneMobile);
        // Root.
        $strRoot = '(?:(?:(?:\+|00)'.$arrPhoneFormat['strCoutryCode']
            .'(?:\('.$arrPhoneFormat['strTrunkp'].'\))?)|'.$arrPhoneFormat['strTrunkp'].')';

        if (preg_match('/^'.$strRoot.'('.$arrPhoneFormat['strMobile'].')$/ui', $strPhoneMobileClean, $matches)) {
            $strPhoneMobileID = $arrPhoneFormat['strTrunkp'].$matches[1];
            $strPhoneMobileIDLength = Tools::strlen($strPhoneMobileID);

            if ($strPhoneMobileIDLength < 1 || $strPhoneMobileIDLength > 63) {
                return false;
            }

            return $strPhoneMobileID;
        }

        return false;
    }

    /**
     * Create a new directory with default index.php file.
     * Don't do log here.
     *
     * @param array $arrArgDirectoryList an array of directories.
     *
     * @return bool
     */
    public static function makeDirectory($arrArgDirectoryList, $strRoot = '')
    {
        $strIndexFileContent = <<<PHP
<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Location: ../');
exit;
PHP;

        $arrDirectoryList = (array)$arrArgDirectoryList;

        foreach ($arrDirectoryList as $strDirectory) {
            // If directory do not exist, create it.
            $boolSuccess = true;

            if (!is_string($strDirectory)) {
                continue;
            }

            // Add final separator.
            if (Tools::substr($strDirectory, -1) !== DIRECTORY_SEPARATOR) {
                $strDirectory .= DIRECTORY_SEPARATOR;
            }

            $strPath = $strRoot.$strDirectory;

            // If directory do not exist, create it.
            if (!is_dir($strPath)) {
                $intUMask = umask(0);
                $boolSuccess = mkdir($strPath, 0770, true) && $boolSuccess;
                umask($intUMask);

                if (!$boolSuccess) {
                    return false;
                }
            }

            $strFileName = $strPath.'index.php';
            // If index file does not exist, create it.
            if (!file_exists($strFileName)) {
                touch($strFileName);
                @chmod($strFileName, 0660);

                $rscFile = fopen($strFileName, 'w');
                if ($rscFile === false) {
                    return false;
                }
                fwrite($rscFile, $strIndexFileContent);
                fclose($rscFile);
            }
        }

        return true;
    }

    /**
     * Remove a list of files or directories.
     *
     * @param       $strModuleDirSrc
     * @param array $arrRemoveFileList
     * @param array $arrRemoveDirList
     *
     * @return bool
     */
    public static function removeFiles($strModuleDirSrc, $arrRemoveFileList = array(), $arrRemoveDirList = array())
    {
        foreach ($arrRemoveFileList as $strFile) {
            $strFQFile = $strModuleDirSrc.$strFile;

            try {
                // Delete file if exist.
                if (file_exists($strFQFile)) {
                    Tools::deleteFile($strFQFile);
                }
            } catch (Exception $objException) {
                TNTOfficiel_Logger::logException($objException);

                return false;
            }
        }

        foreach ($arrRemoveDirList as $strDir) {
            $strFQDir = $strModuleDirSrc.$strDir;

            try {
                // Delete dir if exist.
                if (file_exists($strFQDir)) {
                    Tools::deleteDirectory($strFQDir);
                }
            } catch (Exception $objException) {
                TNTOfficiel_Logger::logException($objException);

                return false;
            }
        }

        return true;
    }

    /**
     * Generate an archive containing all the logs files
     *
     * @return string.
     */
    public static function getZip($strArgPath, $arrArgAllowedExt = array())
    {
        if (!extension_loaded('zip')) {
            TNTOfficiel_Logger::logException(new Exception(sprintf('PHP Zip extension is required')));

            return false;
        }

        $strZipFileName = 'logs_tmp.zip';
        $strZipLocation = $strArgPath.$strZipFileName;

        // Remove existing file archive.
        if (file_exists($strZipLocation)) {
            unlink($strZipLocation);
        }

        $objZipArchive = new ZipArchive();
        $objZipArchive->open($strZipLocation, ZipArchive::CREATE);

        foreach ($arrArgAllowedExt as $strExt) {
            $arrFiles = Tools::scandir($strArgPath, $strExt, '', true);
            foreach ($arrFiles as $strFileName) {
                $strFileLocation = $strArgPath.$strFileName;
                if (file_exists($strFileLocation)) {
                    $objZipArchive->addFromString($strFileName, Tools::file_get_contents($strFileLocation));
                }
            }
        }
        $objZipArchive->close();

        $strZipContent = file_get_contents($strZipLocation);

        // Remove existing file archive.
        if (file_exists($strZipLocation)) {
            unlink($strZipLocation);
        }

        return $strZipContent;
    }

    /**
     * Get string  binary size in bytes.
     *
     * @param $strArgSubject
     *
     * @return int
     */
    public static function strByteLength($strArgSubject)
    {
        return strlen($strArgSubject);
    }

    /**
     * Split a string in array of strings with a maximum of chars.
     *
     * @param string $strArgSubject
     * @param int    $intArgLength
     *
     * @return array
     */
    public static function strSplitter($strArgSubject, $intArgLength = 32)
    {
        $intLength = (int)$intArgLength;
        $strSubject = trim((string)$strArgSubject);

        if (!($intLength > 0) || Tools::strlen($strSubject) <= $intLength) {
            return array(
                $strSubject,
            );
        }

        $arrResult = array();

        while (Tools::strlen($strSubject) > 0) {
            $rxpSplitter = '/^\s*+([^\n]{0,'.$intLength.'})(:?\s+([^\n]*?))?\s*$/ui';
            if (preg_match($rxpSplitter, $strSubject, $arrBackRefList) === 1) {
                // warp line.
                $arrResult[] = $arrBackRefList[1];
                if (array_key_exists(3, $arrBackRefList)) {
                    $strSubject = $arrBackRefList[3];
                } else {
                    $strSubject = '';
                }
            } else {
                // cut word.
                $arrResult[] = Tools::substr($strSubject, 0, $intLength);
                $strSubject = Tools::substr($strSubject, $intLength);
            }
        }

        return $arrResult;
    }

    /**
     * Convert input string for compatibility with webservice.
     * Input:
     * a-z0-9àâäéèêëîïôöùûüñ²&"#'{}()[]|_\/ç^@°=+-£$¤%µ*<>?,.;:§!
     * Â’€ê^²ç#~&まa-z0-9âéïùñ
     * `²'|"/°-£¤µ*,.;:§()[]
     * Output:
     * a-z0-9aaaeeeeiioouuun²&"#'{}()[]|_\/c^@°=+-£$¤%µ*<>?,.;:§! A'Ee^²c#-&maa-z0-9aeiun '²'|"/°-£¤µ*,.;:§()[]
     *
     * @param $strArgInput
     *
     * @return mixed
     */
    public static function translitASCII($strArgInput, $intLength = 0)
    {
        $strRegExp = <<<'REGEXP'
/[^a-z0-9àâäéèêëîïôöùûüñ£$¤%µ*<>?,.;:§!²&"#'|_\\\/ç^@°=+{}()\[\]\-]++/ui
REGEXP;

        $strNoControlChars = preg_replace('/[\p{Cn}]++/u', '?', $strArgInput);

        // PHP 7+
        $boolExistTransliterator = function_exists('transliterator_transliterate');
        if ($boolExistTransliterator) {
            $stASCII = transliterator_transliterate('Any-Latin; Latin-ASCII;', $strNoControlChars);
        } else {
            $arrRegExTranslitMap = array(
                '/[            ]/u' => ' ',
                '/[©]/u' => '(C)',
                '/[«≪]/u' => '<<',
                '/[­˗‐‑‒–—―−﹘﹣－]/u' => '-',
                '/[®]/u' => '(R)',
                '/[»≫]/u' => '>>',
                '/[¼]/u' => ' 1/4',
                '/[½]/u' => ' 1/2',
                '/[¾]/u' => ' 3/4',
                '/[ÀÁÂÃÄÅĀĂĄǍǞǠǺȀȂȦȺΆΑḀẠẢẤẦẨẪẬẮẰẲẴẶÅＡ]/u' => 'A',
                '/[ÆǢǼ]/u' => 'AE',
                '/[ÇĆĈĊČƇȻḈℂℭⅭＣ]/u' => 'C',
                '/[ÈÉÊËĒĔĖĘĚƐȄȆȨɆΈΉΕΗḔḖḘḚḜẸẺẼẾỀỂỄỆℰＥ]/u' => 'E',
                '/[ÌÍÎÏĨĪĬĮİƖƗǏȈȊɪΊΙΪḬḮỈỊℐℑⅠＩ]/u' => 'I',
                '/[ÐĎĐƉƊƋΔḊḌḎḐḒⅅⅮＤ]/u' => 'D',
                '/[ÑŃŅŇŊƝǸɴΝṄṆṈṊℕＮ]/u' => 'N',
                '/[ÒÓÔÕÖØŌŎŐƠǑǪǬǾȌȎȪȬȮȰΌΏΟΩṌṎṐṒỌỎỐỒỔỖỘỚỜỞỠỢΩＯ]/u' => 'O',
                '/[×⁎﹡＊]/u' => '*',
                '/[ÙÚÛÜŨŪŬŮŰŲƯǓǕǗǙǛȔȖɄṲṴṶṸṺỤỦỨỪỬỮỰＵ]/u' => 'U',
                '/[ÝŶŸƳȲɎʏΎΥΫϒϓϔẎỲỴỶỸỾＹ]/u' => 'Y',
                '/[ÞΘϴ]/u' => 'TH',
                '/[ß]/u' => 'ss',
                '/[àáâãäåāăąǎǟǡǻȁȃȧάαḁẚạảấầẩẫậắằẳẵặａ]/u' => 'a',
                '/[æǣǽ]/u' => 'ae',
                '/[çćĉċčƈȼɕḉⅽｃ]/u' => 'c',
                '/[èéêëēĕėęěȅȇȩɇɛέήεηϵḕḗḙḛḝẹẻẽếềểễệℯⅇｅ]/u' => 'e',
                '/[ìíîïĩīĭįıǐȉȋɨͺΐίιϊḭḯỉịℹⅈⅰｉ]/u' => 'i',
                '/[ðďđƌȡɖɗδḋḍḏḑḓⅆⅾｄ]/u' => 'd',
                '/[ñńņňŋƞǹȵɲɳνṅṇṉṋｎ]/u' => 'n',
                '/[òóôõöøōŏőơǒǫǭǿȍȏȫȭȯȱοωόώṍṏṑṓọỏốồổỗộớờởỡợℴｏ]/u' => 'o',
                '/[÷⁄∕／]/u' => '/',
                '/[ùúûüũūŭůűųưǔǖǘǚǜȕȗʉṳṵṷṹṻụủứừửữựｕ]/u' => 'u',
                '/[ýÿŷƴȳɏΰυϋύẏẙỳỵỷỹỿｙ]/u' => 'y',
                '/[þθϑ]/u' => 'th',
                '/[ĜĞĠĢƓǤǦǴɢʛΓḠＧ]/u' => 'G',
                '/[ĝğġģǥǧǵɠɡγḡℊｇ]/u' => 'g',
                '/[ĤĦȞʜḢḤḦḨḪℋℍＨ]/u' => 'H',
                '/[ĥħȟɦɧḣḥḧḩḫẖℎｈ]/u' => 'h',
                '/[Ĳ]/u' => 'IJ',
                '/[ĳ]/u' => 'ij',
                '/[ĴɈＪ]/u' => 'J',
                '/[ĵǰȷɉɟʝϳⅉｊ]/u' => 'j',
                '/[ĶƘǨΚḰḲḴKＫ]/u' => 'K',
                '/[ķƙǩκϰḱḳḵｋ]/u' => 'k',
                '/[ĸʠｑ]/u' => 'q',
                '/[ĹĻĽĿŁȽʟΛḶḸḺḼℒⅬＬ]/u' => 'L',
                '/[ĺļľŀłƚȴɫɬɭλḷḹḻḽℓⅼｌ]/u' => 'l',
                '/[ŉ]/u' => '\'n',
                '/[Œɶ]/u' => 'OE',
                '/[œ]/u' => 'oe',
                '/[ŔŖŘȐȒɌʀΡṘṚṜṞℛℜℝＲ]/u' => 'R',
                '/[ŕŗřȑȓɍɼɽɾρϱṙṛṝṟｒ]/u' => 'r',
                '/[ŚŜŞŠȘΣϷϹϺṠṢṤṦṨＳ]/u' => 'S',
                '/[śŝşšſșȿʂςσϲϸϻṡṣṥṧṩẛẜẝｓ]/u' => 's',
                '/[ŢŤŦƬƮȚȾΤṪṬṮṰＴ]/u' => 'T',
                '/[ţťŧƫƭțȶʈτṫṭṯṱẗｔ]/u' => 't',
                '/[ŴẀẂẄẆẈＷ]/u' => 'W',
                '/[ŵẁẃẅẇẉẘｗ]/u' => 'w',
                '/[ŹŻŽƵȤΖẐẒẔℤℨＺ]/u' => 'Z',
                '/[źżžƶȥɀʐʑζẑẓẕｚ]/u' => 'z',
                '/[ƀƃɓβϐḃḅḇｂ]/u' => 'b',
                '/[ƁƂɃʙΒḂḄḆℬＢ]/u' => 'B',
                '/[ƑḞℱＦ]/u' => 'F',
                '/[ƒḟｆ]/u' => 'f',
                '/[ƕ]/u' => 'hv',
                '/[Ƣ]/u' => 'OI',
                '/[ƣ]/u' => 'oi',
                '/[ƤΠṔṖℙＰ]/u' => 'P',
                '/[ƥπϖṕṗｐ]/u' => 'p',
                '/[ƲṼṾỼⅤＶ]/u' => 'V',
                '/[ǄǱ]/u' => 'DZ',
                '/[ǅǲ]/u' => 'Dz',
                '/[ǆǳʣʥ]/u' => 'dz',
                '/[Ǉ]/u' => 'LJ',
                '/[ǈ]/u' => 'Lj',
                '/[ǉ]/u' => 'lj',
                '/[Ǌ]/u' => 'NJ',
                '/[ǋ]/u' => 'Nj',
                '/[ǌ]/u' => 'nj',
                '/[Ǯ]/u' => 'Ʒ',
                '/[ǯ]/u' => 'ʒ',
                '/[ȸ]/u' => 'db',
                '/[ȹ]/u' => 'qp',
                '/[ɱμḿṁṃⅿｍ]/u' => 'm',
                '/[ʋṽṿỽⅴｖ]/u' => 'v',
                '/[ʦ]/u' => 'ts',
                '/[ʪ]/u' => 'ls',
                '/[ʫ]/u' => 'lz',
                '/[ʹʻʼʽˈʹ‘’‛′＇]/u' => '\'',
                '/[ʺ“”‟″＂]/u' => '"',
                '/[˂‹﹤＜]/u' => '<',
                '/[˃›﹥＞]/u' => '>',
                '/[˄ˆ＾]/u' => '^',
                '/[ˋ｀]/u' => '`',
                '/[ː﹕：]/u' => ':',
                '/[˖﹢＋]/u' => '+',
                '/[˜～]/u' => '~',
                '/[̀]/u' => '̀',
                '/[́]/u' => '́',
                '/[̓]/u' => '̓',
                '/[̈́]/u' => '̈́',
                '/[;﹔；]/u' => ';',
                '/[·]/u' => '·',
                '/[ΜḾṀṂℳⅯＭ]/u' => 'M',
                '/[ΞẊẌⅩＸ]/u' => 'X',
                '/[Φ]/u' => 'PH',
                '/[Χ]/u' => 'CH',
                '/[Ψ]/u' => 'PS',
                '/[ξẋẍℌⅹｘ]/u' => 'x',
                '/[φϕ]/u' => 'ph',
                '/[χ]/u' => 'ch',
                '/[ψ]/u' => 'ps',
                '/[ẞ]/u' => 'SS',
                '/[Ỻ]/u' => 'LL',
                '/[ỻ]/u' => 'll',
                '/[‖∥]/u' => '||',
                '/[‚﹐﹑，]/u' => ',',
                '/[„]/u' => ',,',
                '/[․﹒．]/u' => '.',
                '/[‥]/u' => '..',
                '/[…]/u' => '...',
                '/[‼]/u' => '!!',
                '/[⁅﹝［]/u' => '[',
                '/[⁆﹞］]/u' => ']',
                '/[⁇]/u' => '??',
                '/[⁈]/u' => '?!',
                '/[⁉]/u' => '!?',
                '/[₠]/u' => 'CE',
                '/[₢]/u' => 'Cr',
                '/[₣]/u' => 'Fr.',
                '/[₤]/u' => 'L.',
                '/[₧]/u' => 'Pts',
                '/[₹]/u' => 'Rs',
                '/[₺]/u' => 'TL',
                '/[℀]/u' => 'a/c',
                '/[℁]/u' => 'a/s',
                '/[℅]/u' => 'c/o',
                '/[℆]/u' => 'c/u',
                '/[№]/u' => 'No',
                '/[ℚＱ]/u' => 'Q',
                '/[℞]/u' => 'Rx',
                '/[℡]/u' => 'TEL',
                '/[℻]/u' => 'FAX',
                '/[⅓]/u' => ' 1/3',
                '/[⅔]/u' => ' 2/3',
                '/[⅕]/u' => ' 1/5',
                '/[⅖]/u' => ' 2/5',
                '/[⅗]/u' => ' 3/5',
                '/[⅘]/u' => ' 4/5',
                '/[⅙]/u' => ' 1/6',
                '/[⅚]/u' => ' 5/6',
                '/[⅛]/u' => ' 1/8',
                '/[⅜]/u' => ' 3/8',
                '/[⅝]/u' => ' 5/8',
                '/[⅞]/u' => ' 7/8',
                '/[⅟]/u' => ' 1/',
                '/[Ⅱ]/u' => 'II',
                '/[Ⅲ]/u' => 'III',
                '/[Ⅳ]/u' => 'IV',
                '/[Ⅵ]/u' => 'VI',
                '/[Ⅶ]/u' => 'VII',
                '/[Ⅷ]/u' => 'VIII',
                '/[Ⅸ]/u' => 'IX',
                '/[Ⅺ]/u' => 'XI',
                '/[Ⅻ]/u' => 'XII',
                '/[ⅱ]/u' => 'ii',
                '/[ⅲ]/u' => 'iii',
                '/[ⅳ]/u' => 'iv',
                '/[ⅵ]/u' => 'vi',
                '/[ⅶ]/u' => 'vii',
                '/[ⅷ]/u' => 'viii',
                '/[ⅸ]/u' => 'ix',
                '/[ⅺ]/u' => 'xi',
                '/[ⅻ]/u' => 'xii',
                '/[∖﹨＼]/u' => '\\',
                '/[∣｜]/u' => '|',
                '/[﹖？]/u' => '?',
                '/[﹗！]/u' => '!',
                '/[﹙（]/u' => '(',
                '/[﹚）]/u' => ')',
                '/[﹛｛]/u' => '{',
                '/[﹜｝]/u' => '}',
                '/[﹟＃]/u' => '#',
                '/[﹠＆]/u' => '&',
                '/[﹦＝]/u' => '=',
                '/[﹩＄]/u' => '$',
                '/[﹪％]/u' => '%',
                '/[﹫＠]/u' => '@',
                '/[０]/u' => '0',
                '/[１]/u' => '1',
                '/[２]/u' => '2',
                '/[３]/u' => '3',
                '/[４]/u' => '4',
                '/[５]/u' => '5',
                '/[６]/u' => '6',
                '/[７]/u' => '7',
                '/[８]/u' => '8',
                '/[９]/u' => '9',
                '/[＿]/u' => '_',
                '/[｟]/u' => '((',
                '/[｠]/u' => '))',
            );

            $stASCII = preg_replace(array_keys($arrRegExTranslitMap), $arrRegExTranslitMap, $strNoControlChars);
        }

        $arrRegExMap = array(
            '/[~]/u' => '-',
            '/[’`]/u' => '\'',
            '/[€]/u' => 'E',
        );

        $stASCIICompat = preg_replace(array_keys($arrRegExMap), $arrRegExMap, $stASCII);

        $stASCIIFilter = preg_replace($strRegExp, ' ', $stASCIICompat);
        $stASCIIFilterTrim = trim($stASCIIFilter);

        $stASCIIFilterFinal = $stASCIIFilterTrim;
        if ($intLength > 0) {
            $stASCIIFilterFinal = Tools::substr($stASCIIFilterTrim, 0, $intLength);
        }

        return $stASCIIFilterFinal;
    }

    /**
     * @param $mxdArgValue
     *
     * @return string
     */
    public static function serialize($mxdArgValue)
    {
        $strSerializedValue = serialize($mxdArgValue);

        return $strSerializedValue;
    }

    /**
     * @param string $strArgSerializedValue
     * @param bool   $object
     *
     * @return mixed
     */
    public static function unserialize($strArgSerializedValue, $object = false)
    {
        $mxdValue = Tools::unSerialize($strArgSerializedValue, $object);

        return $mxdValue;
    }

    /**
     * Used for PDF Label.
     *
     * @param $strArgValue
     *
     * @return string
     */
    public static function encodeBase64($strArgValue)
    {
        return (string)base64_encode($strArgValue);
    }

    /**
     * @param $strArgValue
     *
     * @return string
     */
    public static function decodeBase64($strArgValue)
    {
        return (string)base64_decode($strArgValue);
    }

    /**
     * Used for DeliveryPoint Data.
     *
     * @param $strArgInflateValue
     *
     * @return string
     */
    public static function deflate($strArgInflateValue)
    {
        return (string)base64_encode(gzdeflate($strArgInflateValue));
    }

    /**
     * @param $strArgDeflateValue
     *
     * @return string
     */
    public static function inflate($strArgDeflateValue)
    {
        return (string)gzinflate(base64_decode($strArgDeflateValue));
    }

    /**
     * @param $strArgData
     *
     * @return bool|string
     */
    public static function B64URLDeflate($strArgData)
    {
        // Must be a string.
        if (!is_string($strArgData)) {
            return false;
        }
        // Compress GZ.
        $strCompress = gzdeflate($strArgData);
        if (!is_string($strCompress) || $strCompress === '') {
            return false;
        }
        // Encode Base64.
        $strEncode = base64_encode($strCompress);
        if (!is_string($strEncode) || $strEncode === '') {
            return false;
        }

        // URL friendly.
        $strEncode = strtr($strEncode, '+/', '-_');
        $strDeflateB64URL = rtrim($strEncode, '=');

        return $strDeflateB64URL;
    }

    /**
     * TNTOfficiel_Tools::inflate
     *
     * @param $strArgDeflateB64URL
     *
     * @return bool|string
     */
    public static function B64URLInflate($strArgDeflateB64URL)
    {
        // Must be a non empty string.
        if (!is_string($strArgDeflateB64URL) || $strArgDeflateB64URL === '') {
            return false;
        }

        // URL friendly revert.
        $strEncode = strtr($strArgDeflateB64URL, '-_', '+/');
        $strEncode = str_pad($strEncode, strlen($strEncode) % 4, '=', STR_PAD_RIGHT);

        // Decode Base64.
        $strCompress = base64_decode($strEncode);
        if (!is_string($strCompress) || $strCompress === ''
            || $strEncode !== base64_encode($strCompress)
        ) {
            return false;
        }
        // Decompress GZ.
        $strData = gzinflate($strCompress);
        if (!is_string($strData)) {
            return false;
        }

        return $strData;
    }

    /**
     * Donwload an existing file or content.
     *
     * @param string      $strFileLocation
     * @param string|null $strContent
     * @param string      $strContentType
     *
     * @return bool false if error.
     */
    public static function download($strFileLocation, $strContent = null, $strContentType = 'application/octet-stream')
    {
        // File location must be a string.
        if (!is_string($strFileLocation)) {
            return false;
        }
        // If content, must be a string.
        if ($strContent !== null && !is_string($strContent)) {
            return false;
        }
        // If no content, file must exist.
        if ($strContent === null && !file_exists($strFileLocation)) {
            return false;
        }

        // End output buffer.
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        // Set header.
        ob_start();
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-type: '.$strContentType);
        header('Content-Disposition: attachment; filename="'.basename($strFileLocation).'"');
        header('Content-Transfer-Encoding: binary');
        ob_end_flush();

        // Output content.
        if ($strContent !== null) {
            echo $strContent;
        } else {
            readfile($strFileLocation);
        }

        // We want to be sure that download content is the last thing this controller will do.
        exit;
    }

    /**
     * Get DateTime object from a validated timestamp, string or DateTime.
     *
     * @param string|int|DateTime $mxdArgDateTime
     *
     * @return null|DateTime
     */
    public static function getDateTime($mxdArgDateTime)
    {
        TNTOfficiel_Logstack::log();

        // Check DateTime object.
        if ($mxdArgDateTime instanceof DateTime) {
            return clone $mxdArgDateTime;
        }

        // Check datetime string.
        if (is_string($mxdArgDateTime)
            && preg_match(
                '/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/ui',
                $mxdArgDateTime,
                $arrBackRef
            ) === 1
            && checkdate((int)$arrBackRef[2], (int)$arrBackRef[3], (int)$arrBackRef[1])
        ) {
            $objDateTimeCheck = DateTime::createFromFormat('Y-m-d H:i:s', $mxdArgDateTime);
            if (is_object($objDateTimeCheck)) {
                if ($mxdArgDateTime === $objDateTimeCheck->format('Y-m-d H:i:s')) {
                    return $objDateTimeCheck;
                }
            }
        }

        // Check date string.
        if (is_string($mxdArgDateTime)
            && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/ui', $mxdArgDateTime, $arrBackRef) === 1
            && checkdate((int)$arrBackRef[2], (int)$arrBackRef[3], (int)$arrBackRef[1])
        ) {
            $objDateCheck = DateTime::createFromFormat('Y-m-d', $mxdArgDateTime);
            if (is_object($objDateCheck)) {
                $objDateCheck->modify('midnight');
                if ($mxdArgDateTime === $objDateCheck->format('Y-m-d')) {
                    return $objDateCheck;
                }
            }
        }

        // Check time string.
        if (is_string($mxdArgDateTime)
            && preg_match('/^([0-9]{2}):([0-9]{2}):([0-9]{2})$/ui', $mxdArgDateTime, $arrBackRef) === 1
        ) {
            $objTimeCheck = DateTime::createFromFormat('H:i:s', $mxdArgDateTime);
            if (is_object($objTimeCheck)) {
                if ($mxdArgDateTime === $objTimeCheck->format('H:i:s')) {
                    return $objTimeCheck;
                }
            }
        }

        // String timestamp to int.
        if (is_string($mxdArgDateTime)
            && preg_match('/^[0-9]+$/ui', $mxdArgDateTime) === 1
        ) {
            $mxdArgDateTime = (int)$mxdArgDateTime;
        }

        // Check timestamp.
        if (is_int($mxdArgDateTime) && $mxdArgDateTime >= 0) {
            //$objTimeStampCheck = DateTime::createFromFormat('U', (string)$mxdArgDateTime);
            try {
                $objTimeStampCheck = new DateTime('@'.$mxdArgDateTime);
            } catch (Exception $objException) {
                $objTimeStampCheck = null;
            }
            if (is_object($objTimeStampCheck)) {
                if ($mxdArgDateTime === (int)$objTimeStampCheck->format('U')) {
                    return $objTimeStampCheck;
                }
            }
        }

        return null;
    }

    /**
     * Check if a date is in weekday (not weekend).
     *
     * @param $mxdArgDateTime
     *
     * @return bool|null
     */
    public static function isWeekDay($mxdArgDateTime)
    {
        TNTOfficiel_Logstack::log();

        $objDateTime = TNTOfficiel_Tools::getDateTime($mxdArgDateTime);
        if ($objDateTime === null) {
            return null;
        }

        $objDateTimeWeekDay = clone $objDateTime;
        $objDateTimeWeekDay->modify('previous weekday')->modify('next weekday');

        return ($objDateTime == $objDateTimeWeekDay);
    }

    /**
     * Get the first weekday from date, offset n weekdays.
     *
     * @param        $mxdArgDateTime Current date.
     * @param int    $intDaysOffset  0 for first available week days.
     * @param string $strArgFormat
     *
     * @return null
     */
    public static function getFirstWeekDay($mxdArgDateTime, $strArgFormat = 'U', $intDaysOffset = 0)
    {
        TNTOfficiel_Logstack::log();

        $objDateTime = TNTOfficiel_Tools::getDateTime($mxdArgDateTime);
        if ($objDateTime === null) {
            return null;
        }

        $objDateTimeWeekDay = clone $objDateTime;
        $objDateTimeWeekDay->modify('previous weekday')->modify('next weekday');
        if ($intDaysOffset < 0 || $intDaysOffset > 0) {
            $objDateTimeWeekDay->modify($intDaysOffset.' weekday');
        }

        return TNTOfficiel_Tools::getDateTimeFormat($objDateTimeWeekDay, $strArgFormat);
    }

    /**
     * Get the date, offset n weekdays.
     *
     * @param        $mxdArgDateTime Current date.
     * @param int    $intDaysOffset  1 for next available week days.
     * @param string $strArgFormat
     *
     * @return null
     */
    public static function getNextWeekDay($mxdArgDateTime, $strArgFormat = 'U', $intDaysOffset = 1)
    {
        TNTOfficiel_Logstack::log();

        $objDateTime = TNTOfficiel_Tools::getDateTime($mxdArgDateTime);
        if ($objDateTime === null) {
            return null;
        }

        $objDateTimeNextWeekDay = clone $objDateTime;
        if ($intDaysOffset < 0 || $intDaysOffset > 0) {
            $objDateTimeNextWeekDay->modify('midnight')->modify($intDaysOffset.' weekday');
        }

        return TNTOfficiel_Tools::getDateTimeFormat($objDateTimeNextWeekDay, $strArgFormat);
    }

    /**
     * Check if a date is today or in future.
     *
     * @param $mxdArgDateTime
     *
     * @return bool|null
     */
    public static function isTodayOrLater($mxdArgDateTime)
    {
        TNTOfficiel_Logstack::log();

        $objDateTime = TNTOfficiel_Tools::getDateTime($mxdArgDateTime);
        if ($objDateTime === null) {
            return null;
        }

        $objDateTimeDay = clone $objDateTime;
        $objDateTimeDay->modify('midnight');

        $objDateTimeToday = new DateTime('midnight');

        return ($objDateTimeDay >= $objDateTimeToday);
    }

    /**
     * Get a formatted date (default is timestamp).
     *
     * @param string|int|DateTime $mxdArgDateTime
     * @param string              $strArgFormat
     * @param string|int|null     $objArgDefault
     *
     * @return null|int|string
     */
    public static function getDateTimeFormat($mxdArgDateTime, $strArgFormat = 'U', $objArgDefault = null)
    {
        TNTOfficiel_Logstack::log();

        $objDateTime = TNTOfficiel_Tools::getDateTime($mxdArgDateTime);
        if ($objDateTime === null) {
            $objDateTime = TNTOfficiel_Tools::getDateTime($objArgDefault);
        }

        if ($objDateTime !== null) {
            $strDateTimeFormat = $objDateTime->format($strArgFormat);
            if ($strArgFormat === 'U') {
                return (int)$strDateTimeFormat;
            }

            return $strDateTimeFormat;
        }

        return null;
    }

    /**
     * Compare last update timestamp with a refresh delay, to current timestamp.
     *
     * @param string|int|DateTime $mxdArgLastUpdate
     * @param int                 $intArgRefreshDelay
     *
     * @return bool|null true if last update timestamp is outdated. null if invalid date.
     */
    public static function isExpired($mxdArgLastUpdate, $intArgRefreshDelay = 0)
    {
        TNTOfficiel_Logstack::log();

        $mxdArgLastUpdate = TNTOfficiel_Tools::getDateTimeFormat($mxdArgLastUpdate);
        if ($mxdArgLastUpdate === null) {
            return null;
        }

        $intLastUpdate = (int)$mxdArgLastUpdate;
        if (!($intLastUpdate >= 0)) {
            $intLastUpdate = 0;
        }

        $intRefreshDelay = (int)$intArgRefreshDelay;
        if (!($intRefreshDelay >= 0)) {
            $intRefreshDelay = 0;
        }

        $objDateTimeNow = new DateTime('now');
        $intTSNow = (int)$objDateTimeNow->format('U');

        // If delay is passed.
        if ($intTSNow >= ($intLastUpdate + $intRefreshDelay)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $strArgURL
     * @param array  $arrArgOptions
     *
     * @return array
     */
    public static function cURLRequest($strArgURL, $arrArgOptions = null)
    {
        TNTOfficiel_Logstack::log();

        $strURL = trim($strArgURL);

        $strCACertFilename = _PS_CACHE_CA_CERT_FILE_;
        $intCACertTimestamp = file_exists($strCACertFilename) ? @filemtime($strCACertFilename) : 0;

        $arrResult = array(
            'options' => array(
                // Check server certificate's name against host.
                // 0: disable, 2: enable.
                CURLOPT_SSL_VERIFYHOST => 0,
                // Check server peer's certificate authenticity through certification authority (CA) for SSL/TLS.
                CURLOPT_SSL_VERIFYPEER => $intCACertTimestamp > 0,
                // Path to Certificate Authority (CA) bundle.
                // https://curl.haxx.se/docs/caextract.html
                // https://curl.haxx.se/ca/cacert.pem
                // Default : ini_get('curl.cainfo') PHP 5.3.7+
                // Alternative : ini_get('openssl.cafile') PHP 5.6+
                CURLOPT_CAINFO => $strCACertFilename,
                // Start a new cookie session (ignore all previous cookies session)
                CURLOPT_COOKIESESSION => true,
                // Follow HTTP 3xx redirects.
                CURLOPT_FOLLOWLOCATION => true,
                // Max redirects allowed.
                CURLOPT_MAXREDIRS => 8,
                // curl_exec return response string instead of true (no direct output).
                CURLOPT_RETURNTRANSFER => true,
                // Include response header in output.
                //CURLOPT_HEADER => false,
                // Include request header ?
                //CURLINFO_HEADER_OUT => false,
                // HTTP code >= 400 considered as error. Use curl_error (curl_exec return false ?).
                //CURLOPT_FAILONERROR => true,
                // Proxy.
                //CURLOPT_PROXY => $strProxy
                //CURLOPT_PROXYUSERPWD => 'user:password',
                //CURLOPT_PROXYAUTH => 1,
                //CURLOPT_PROXYPORT => 80,
                //CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
                // Timeout for connection to the server.
                CURLOPT_CONNECTTIMEOUT => TNTOfficiel::REQUEST_CONNECTTIMEOUT,
                // Timeout global.
                CURLOPT_TIMEOUT => TNTOfficiel::REQUEST_TIMEOUT,
            ),
            'response' => null,
            'info' => array(
                'http_code' => 0,
            ),
            'error' => null,
        );

        if (is_array($arrArgOptions)) {
            $arrResult['options'] = $arrArgOptions + $arrResult['options'];
        }

        // Check extension.
        if (!extension_loaded('curl')) {
            $objException = new Exception(sprintf('PHP cURL extension is required'));
            TNTOfficiel_Logger::logException($objException);
            // Communication Error.
            $arrResult['response'] = false;
            $arrResult['error'] = 'PHP cURL extension is required';

            return $arrResult;
        }

        $rscCURLHandler = curl_init();

        foreach ($arrResult['options'] as $intCURLConst => $mxdValue) {
            // May warn if open_basedir or deprecated safe_mode set.
            if ((ini_get('safe_mode') || ini_get('open_basedir'))
                && $intCURLConst === CURLOPT_FOLLOWLOCATION
            ) {
                continue;
            }
            curl_setopt($rscCURLHandler, $intCURLConst, $mxdValue);
        }

        curl_setopt($rscCURLHandler, CURLOPT_URL, $strURL);

        // curl_exec return false on error.
        $arrResult['response'] = curl_exec($rscCURLHandler);
        $arrResult['info'] = curl_getinfo($rscCURLHandler);
        $arrResult['error'] = curl_error($rscCURLHandler);

        curl_close($rscCURLHandler);

        return $arrResult;
    }

    /**
     * select, show, explain or describe queries.
     *
     * @param string $strArgSQL
     * @param bool   $boolArgUseCache
     *
     * @return array|string string on error.
     */
    public static function getDbSelect($strArgSQL, $boolArgUseCache = true)
    {
        TNTOfficiel_Logstack::log();

        $objDB = Db::getInstance();
        $arrDBResult = null;
        $objException = null;

        try {
            // Get.
            $arrDBResult = $objDB->executeS($strArgSQL, true, $boolArgUseCache);
            if ($arrDBResult === false) {
                $objException = new Exception($objDB->getMsgError());
            }
        } catch (Exception $objException) {
            // Exception processed next.
        }

        if ($objException !== null) {
            TNTOfficiel_Logger::logException($objException);

            return $objException->getMessage();
        }

        return $arrDBResult;
    }

    /**
     * create table, alter table, etc. queries.
     *
     * @param string $strArgSQL
     * @param bool   $boolArgUseCache
     *
     * @return true|string string on error.
     */
    public static function getDbExecute($strArgSQL, $boolArgUseCache = true)
    {
        TNTOfficiel_Logstack::log();

        $objDB = Db::getInstance();
        $boolDBResult = null;
        $objException = null;

        try {
            // Get.
            $boolDBResult = $objDB->execute($strArgSQL, $boolArgUseCache);
            if ($boolDBResult === false) {
                $objException = new Exception($objDB->getMsgError());
            }
        } catch (Exception $objException) {
            // Exception processed next.
        }

        if ($objException !== null) {
            TNTOfficiel_Logger::logException($objException);

            return $objException->getMessage();
        }

        return $boolDBResult;
    }

    /**
     * Check if a table name exist.
     *
     * @param $strArgTableName
     *
     * @return bool|string. true if exist, false if not exist, string on error.
     */
    public static function isTableExist($strArgTableName)
    {
        TNTOfficiel_Logstack::log();

        // Test if table exist.
        $strSQLTableExist = <<<SQL
SHOW TABLES LIKE '${strArgTableName}';
SQL;

        // Get table (cache must be disabled).
        $arrDBResult = TNTOfficiel_Tools::getDbSelect($strSQLTableExist, false);
        if (!is_array($arrDBResult)) {
            return null;
        }

        // if table if exist.
        if (count($arrDBResult) === 1) {
            return true;
        }

        return false;
    }

    /**
     * Check if columns name list exist.
     *
     * @param $strArgTableName
     * @param $arrArgStrColumnNameList
     *
     * @return bool|string. true if exist, false if not exist, string on error.
     */
    public static function isTableColumnsExist($strArgTableName, $arrArgStrColumnNameList)
    {
        TNTOfficiel_Logstack::log();

        // List columns in table.
        $strSQLTableColumns = <<<SQL
SHOW COLUMNS FROM `${strArgTableName}`;
SQL;

        if (TNTOfficiel_Tools::isTableExist($strArgTableName) !== true) {
            return false;
        }

        // Get existing columns (cache must be disabled).
        $arrDBResultColumns = TNTOfficiel_Tools::getDbSelect($strSQLTableColumns, false);
        if (!is_array($arrDBResultColumns)) {
            return null;
        }

        $arrStrColumnNameExistingList = array();
        foreach ($arrDBResultColumns as $arrRowColumns) {
            if (array_key_exists('Field', $arrRowColumns)) {
                $arrStrColumnNameExistingList[] = $arrRowColumns['Field'];
            }
        }

        $arrStrColumnNameMissingList = array();

        // Search columns.
        $arrStrColumnNameSearchList = (array)$arrArgStrColumnNameList;
        foreach ($arrStrColumnNameSearchList as $strColumnNameSearch) {
            if (!in_array($strColumnNameSearch, $arrStrColumnNameExistingList)) {
                $arrStrColumnNameMissingList[] = $strColumnNameSearch;
            }
        }

        // Missing columns.
        if (count($arrStrColumnNameMissingList) > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param $arrArg
     * @param $arrArgOrderKey
     *
     * @return array. Sorted array.
     */
    public static function arrayOrderKey($arrArg, $arrArgOrderKey)
    {
        // List of sorted selected key.
        $arrKeyExistSort = array_intersect_key(array_flip($arrArgOrderKey), $arrArg);
        // List of unsorted key left.
        $arrKeyUnExistUnSort = array_diff_key($arrArg, $arrKeyExistSort);
        // Append unsorted list to sorted.
        $arrOrdered = array_merge($arrKeyExistSort, $arrArg) + $arrKeyUnExistUnSort;

        return $arrOrdered;
    }

    /**
     * @return array
     */
    public static function getPHPConfig()
    {
        /*
         * User
         */

        $arrUser = posix_getpwuid(posix_geteuid());

        /*
         * Environment
         */

        $arrEnv = array(
            'http_proxy' => getenv('http_proxy'),
            'https_proxy' => getenv('https_proxy'),
            'ftp_proxy' => getenv('ftp_proxy'),
        );

        /*
         * Constant
         */

        $arrPHPConstants = array(
            'PHP_OS' => PHP_OS,
            'PHP_VERSION' => PHP_VERSION,
            'PHP_SAPI' => PHP_SAPI,
            'PHP_INT_SIZE (bits)' => PHP_INT_SIZE * 8,
        );

        /*
         * Extension
         */

        $arrPHPExtensions = array_intersect_key(
            array_flip(get_loaded_extensions()),
            array(
                'curl' => true,
                'soap' => true,
                'session' => true,
                'mcrypt' => true,
                'mhash' => true,
                'mbstring' => true,
                'iconv' => true,
                'zip' => true,
                'zlib' => true,
                'dom' => true,
                'xml' => true,
                'SimpleXML' => true,
                'Zend OPcache' => true,
                'ionCube Loader' => true,
            )
        );

        /*
         * Configuration
         */

        $arrPHPConfigurationDefault = array(
            // php
            'max_execution_time' => '30',
            'memory_limit' => '128M',
            'magic_quotes' => 'Off',
            'magic_quotes_gpc' => 'Off',
            'max_input_vars' => '8192',
            // core - file uploads
            'upload_max_filesize' => '4M',
            // core - language options
            'disable_functions' => '',
            'disable_classes' => '',
            // core - paths and directories
            'open_basedir' => '',
            // core - data handling
            'register_globals' => 'Off',
            // safe mode
            'safe_mode' => '',
            'safe_mode_gid' => '',
            'safe_mode_exec_dir' => '',
            'safe_mode_include_dir' => '',
            // filesystem
            'allow_url_fopen' => 'On',
            'allow_url_include' => 'Off',
            'default_socket_timeout' => '60',
            // opcache
            'opcache.enable' => 'true',
        );

        $arrPHPConfiguration = array_intersect_key(ini_get_all(null, false), $arrPHPConfigurationDefault);
        $arrPHPConfiguration = TNTOfficiel_Tools::arrayOrderKey(
            $arrPHPConfiguration,
            array_keys($arrPHPConfigurationDefault)
        );

        if (array_key_exists('open_basedir', $arrPHPConfiguration)) {
            $arrPHPConfiguration['open_basedir'] = explode(PATH_SEPARATOR, $arrPHPConfiguration['open_basedir']);
        }

        /*
         * Time
         */

        $arrPHPTime = array(
            'date_default_timezone_set' => date_default_timezone_get(),
            'date.timezone' => ini_get('date.timezone'),
            'date' => date('Y-m-d H:i:s P T (e)'),
        );


        return array(
            'user' => $arrUser,
            'env' => $arrEnv,
            'constants' => $arrPHPConstants,
            'extensions' => $arrPHPExtensions,
            'configuration' => $arrPHPConfiguration,
            'time' => $arrPHPTime,
        );
    }

    public static function getPSConfig()
    {
        /*
         * Constant
         */

        //$__constants = get_defined_constants(true);
        $arrPSConstant = array(
            '_PS_VERSION_' => _PS_VERSION_,
            '_PS_JQUERY_VERSION_' => _PS_JQUERY_VERSION_,

            '_PS_MODE_DEV_' => _PS_MODE_DEV_,
            '_PS_DEBUG_PROFILING_' => _PS_DEBUG_PROFILING_,

            '_PS_MAGIC_QUOTES_GPC_' => _PS_MAGIC_QUOTES_GPC_,
            '_PS_USE_SQL_SLAVE_' => _PS_USE_SQL_SLAVE_,

            '_PS_CACHE_ENABLED_' => _PS_CACHE_ENABLED_,
            '_PS_CACHING_SYSTEM_' => _PS_CACHING_SYSTEM_,

            '_PS_DEFAULT_THEME_NAME_' => _PS_DEFAULT_THEME_NAME_,
            '_PS_THEME_DIR_' => _PS_THEME_DIR_,
            //'_PS_THEME_OVERRIDE_DIR_' => _PS_THEME_OVERRIDE_DIR_,
            //'_PS_THEME_MOBILE_DIR_' => _PS_THEME_MOBILE_DIR_,
            //'_PS_THEME_MOBILE_OVERRIDE_DIR_' => _PS_THEME_MOBILE_OVERRIDE_DIR_,
            //'_PS_THEME_TOUCHPAD_DIR_' => _PS_THEME_TOUCHPAD_DIR_

            '_PS_CACHE_CA_CERT_FILE_' => _PS_CACHE_CA_CERT_FILE_,
        );

        /*
         * Context
         */

        $flagShopContext = Shop::getContext();
        $arrConstShopContext = array();

        if ($flagShopContext & Shop::CONTEXT_SHOP) {
            $arrConstShopContext[] = 'Shop::CONTEXT_SHOP';
        }
        if ($flagShopContext & Shop::CONTEXT_GROUP) {
            $arrConstShopContext[] = 'Shop::CONTEXT_GROUP';
        }
        if ($flagShopContext & Shop::CONTEXT_ALL) {
            $arrConstShopContext[] = 'Shop::CONTEXT_ALL';
        }

        $arrPSContext = array(
            'Context::getContext()->shop->id' => Context::getContext()->shop->id,
            'Context::getContext()->shop->id_shop_group' => Context::getContext()->shop->id_shop_group,
            'Shop::getContext()' => $arrConstShopContext,
            'Shop::isFeatureActive()' => Shop::isFeatureActive(),
            'Shop::getContextShopGroupID()' => (int)Shop::getContextShopGroupID(),
            'Shop::getContextShopID()' => (int)Shop::getContextShopID(),
        );

        /*
         * Configuration
         */

        $arrPSConfig = Configuration::getMultiple(
            array(

                /*
                 * Carrier
                 */

                /* Shipping */

                'PS_SHIPPING_HANDLING',
                'PS_SHIPPING_FREE_PRICE',
                'PS_SHIPPING_FREE_WEIGHT',

                'PS_CARRIER_DEFAULT',
                'PS_CARRIER_DEFAULT_SORT',
                'PS_CARRIER_DEFAULT_ORDER',


                /*
                 * Localization
                 */

                /* Localization */

                'PS_LANG_DEFAULT',
                'PS_COUNTRY_DEFAULT',
                'PS_CURRENCY_DEFAULT',

                'PS_WEIGHT_UNIT',   // kg
                'PS_DISTANCE_UNIT',
                'PS_VOLUME_UNIT',
                'PS_DIMENSION_UNIT',

                'PS_LOCALE_LANGUAGE',
                'PS_LOCALE_COUNTRY',

                /* Country */

                'PS_RESTRICT_DELIVERED_COUNTRIES',

                /* Taxes */

                'PS_TAX',
                'PS_TAX_DISPLAY',
                'PS_TAX_ADDRESS_TYPE',
                'PS_USE_ECOTAX',
                'PS_ECOTAX_TAX_RULES_GROUP_ID',


                /*
                 * Preferences
                 */

                /* General */

                'PS_SSL_ENABLED',
                'PS_SSL_ENABLED_EVERYWHERE',
                'PS_PRICE_ROUND_MODE',
                'PS_ROUND_TYPE',
                'PS_PRICE_DISPLAY_PRECISION',
                'PS_MULTISHOP_FEATURE_ACTIVE',
                // PS 1.6.1.16+
                'PS_API_KEY',

                /* Order */

                // General
                //'PS_ORDER_PROCESS_TYPE',
                'PS_GUEST_CHECKOUT_ENABLED',
                'PS_DISALLOW_HISTORY_REORDERING',
                'PS_PURCHASE_MINIMUM',
                'PS_SHIP_WHEN_AVAILABLE',
                'PS_CONDITIONS',
                // Multi-Shipping (deprecated)
                'PS_ALLOW_MULTISHIPPING',
                // Gift Wrapping
                'PS_GIFT_WRAPPING',
                'PS_GIFT_WRAPPING_PRICE',
                'PS_GIFT_WRAPPING_TAX_RULES_GROUP',
                'PS_RECYCLABLE_PACK',

                /* Product */

                'PS_ATTRIBUTE_ANCHOR_SEPARATOR',
                'PS_ORDER_OUT_OF_STOCK',
                'PS_STOCK_MANAGEMENT',
                'PS_ADVANCED_STOCK_MANAGEMENT',

                /* Customer */

                'PS_REGISTRATION_PROCESS_TYPE',
                'PS_ONE_PHONE_AT_LEAST',
                'PS_B2B_ENABLE',

                /* SEO & URL */

                'PS_REWRITING_SETTINGS',
                'PS_ALLOW_ACCENTED_CHARS_URL',
                'PS_CANONICAL_REDIRECT',

                /* Stores */
                'PS_SHOP_NAME',
                'PS_SHOP_EMAIL',
                'PS_SHOP_PHONE',


                /*
                 * Advanced Parameters
                 */

                /* Performance */

                // Compile template 0: never 1: if template updated, 2: on each call.
                'PS_SMARTY_FORCE_COMPILE',
                // Smarty cache enabled ?
                'PS_SMARTY_CACHE',
                // If enabled, cache using filesystem or mysql ?
                'PS_SMARTY_CACHING_TYPE',
                // Clear cache never or everytime ?
                'PS_SMARTY_CLEAR_CACHE',

                'PS_DISABLE_OVERRIDES',

                'PS_CSS_THEME_CACHE',
                'PS_JS_THEME_CACHE',
                'PS_HTML_THEME_COMPRESSION',
                'PS_JS_HTML_THEME_COMPRESSION',
                'PS_JS_DEFER',
                'PS_HTACCESS_CACHE_CONTROL',

                'PS_CIPHER_ALGORITHM',

            )
        );

        return array(
            'constants' => $arrPSConstant,
            'context' => $arrPSContext,
            'configuration' => $arrPSConfig,
        );
    }
}