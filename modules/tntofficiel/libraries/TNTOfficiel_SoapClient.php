<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_SoapClient
{
    // OASIS (Organization for the Advancement of Structured Information Standards). WSS (Web Services Security).
    const URL_OASIS_ROOT = 'http://docs.oasis-open.org/wss/2004/01/';
    // SOAP WSDL URL (Web Services Description Language).
    const URL_WSDL = 'https://www.tnt.fr/service/?wsdl';

    private $strAccountNumber = null;
    private $strAccountLogin = null;
    private $strAccountPassword = null;

    private static $arrRequestTimeoutPerServices = array(
        'citiesGuide' => 0.3,
        'feasibility' => 0.8,
        'dropOffPoints' => 0.3,
        'tntDepots' => 0.3,
        'getPickupContext' => 0.8,
        'expeditionCreation' => 2,
        'trackingByConsignment' => 2,
    );

    /**
     * Prevent Construct.
     */
    public function __construct($strArgAccountNumber, $strArgAccountLogin, $strArgAccountPassword)
    {
        TNTOfficiel_Logstack::log();

        $this->strAccountNumber = $strArgAccountNumber;
        $this->strAccountLogin = $strArgAccountLogin;
        $this->strAccountPassword = $strArgAccountPassword;
    }

    /**
     * @param $strArgService
     *
     * @return int
     */
    public static function getRequestTimeout($strArgService)
    {
        TNTOfficiel_Logstack::log();

        $intRequestTimeout = TNTOfficiel::REQUEST_TIMEOUT;
        if (array_key_exists($strArgService, TNTOfficiel_SoapClient::$arrRequestTimeoutPerServices)) {
            $intRequestTimeout = (int)ceil(
                1 + TNTOfficiel_SoapClient::$arrRequestTimeoutPerServices[$strArgService] * 4
            );
        }

        return $intRequestTimeout;
    }

    /**
     * @param        $strArgUserName
     * @param        $strArgPassword
     * @param string $strArgPasswordType
     *
     * @return SoapHeader
     */
    public static function getHeader($strArgUserName, $strArgPassword, $strArgPasswordType = 'PasswordDigest')
    {
        TNTOfficiel_Logstack::log();

        $strURLOASISROOT = TNTOfficiel_SoapClient::URL_OASIS_ROOT;
        $strElementCreated = '';

        $intRand = mt_rand();
        if ($strArgPasswordType !== 'PasswordDigest') {
            $strArgPasswordType = 'PasswordText';
            $strNonce = sha1($intRand);
        } else {
            $strTimestamp = gmdate('Y-m-d\TH:i:s\Z');
            $strArgPassword = base64_encode(
                pack(
                    'H*',
                    sha1(
                        pack('H*', $intRand).
                        pack('a*', $strTimestamp).
                        pack('a*', $strArgPassword)
                    )
                )
            );
            $strNonce = base64_encode(pack('H*', $intRand));

            $strElementCreated = <<<XML
<wsu:Created xmlns:wsu="${strURLOASISROOT}oasis-200401-wss-wssecurity-utility-1.0.xsd">${strTimestamp}</wsu:Created>
XML;
        }

        $strArgUserName = htmlspecialchars($strArgUserName);
        $strArgPassword = htmlspecialchars($strArgPassword);

        $strXMLSecurityHeader = <<<XML
<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="${strURLOASISROOT}oasis-200401-wss-wssecurity-secext-1.0.xsd">
  <wsse:UsernameToken>
    <wsse:Username>${strArgUserName}</wsse:Username>
    <wsse:Password Type="${strURLOASISROOT}oasis-200401-wss-username-token-profile-1.0#${strArgPasswordType}"
    >${strArgPassword}</wsse:Password>
    <wsse:Nonce EncodingType="${strURLOASISROOT}oasis-200401-wss-soap-message-security-1.0#Base64Binary"
    >${strNonce}</wsse:Nonce>
    ${strElementCreated}
  </wsse:UsernameToken>
</wsse:Security>
XML;

        $objSoapHeader = new SoapHeader(
            $strURLOASISROOT.'oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new SoapVar($strXMLSecurityHeader, XSD_ANYXML)
        );

        //$objSoapHeader->mustUnderstand = true;

        return $objSoapHeader;
    }

    /**
     * Request a TNT SOAP service.
     *
     * @param string      $strArgService
     * @param array       $arrArgParams
     * @param string|null $strCacheKey
     * @param int         $intArgTTL
     *
     * @return stdClass SOAP Response, null for Communication Error, false for Authentication Error, SoapFault Exception object for Webservice Error.
     */
    private function request($strArgService, $arrArgParams = array(), $strCacheKey = null, $intArgTTL = 0)
    {
        TNTOfficiel_Logstack::log();

        $boolSuccess = false;
        $objSoapClient = null;
        $objStdClassResponseSOAP = null;
        $objException = null;

        $fltRequestTimeStart = microtime(true);

        // Check if already in cache.
        if (TNTOfficielCache::isStored($strCacheKey)) {
            $objStdClassResponseSOAP = TNTOfficielCache::retrieve($strCacheKey);
        } else {
            // Check extension.
            if (!extension_loaded('soap')) {
                $objExceptionSOAPExt = new Exception(sprintf('PHP SOAP extension is required'));
                TNTOfficiel_Logger::logException($objExceptionSOAPExt);

                // Communication Error.
                return null;
            }

            // Set expiration timeout (in seconds).
            $intRequestTimeout = TNTOfficiel_SoapClient::getRequestTimeout($strArgService);
            $mxdSocketTimeoutRestore = ini_get('default_socket_timeout');
            ini_set('default_socket_timeout', $intRequestTimeout);

            try {
                //if ($strArgService === 'expeditionCreation') {
                //    throw new SoapFault('soap:Server', sprintf('The field \'accountNumber\' is not valid. This account number is not registered.'));
                //}

                $objSoapClient = new SoapClient(
                    TNTOfficiel_SoapClient::URL_WSDL,
                    array(
                        'soap_version' => SOAP_1_1,
                        //'cache_wsdl' => WSDL_CACHE_NONE,
                        'trace' => true,
                        // Throw exceptions on error.
                        //'exceptions' => false,
                        // Compress request and response.
                        //'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                        'stream_context' => stream_context_create(
                            array(
                                // Apply for HTTPS and FTPS.
                                'ssl' => array(
                                    // Path to Certificate Authority (CA) bundle.
                                    'cafile' => _PS_CACHE_CA_CERT_FILE_,
                                    // Check server peer's certificate authenticity through certification authority (CA) for SSL/TLS.
                                    'verify_peer' => true,
                                    // Check server certificate's name against host. PHP 5.6.0+
                                    'verify_peer_name' => false,
                                ),
                                //'http' => array(),
                                /*'https' => array(
                                    'timeout' => 1,
                                    // Force IPV4.
                                    'socket' => array(
                                        'bindto' => '0:0' // PHP 5.1.0+
                                    )
                                )*/
                            )
                        ),
                        // Proxy.
                        //'proxy_host' => '<HOST>', // 'http://<FQDN>',
                        //'proxy_port' => 80,
                        //'proxy_login' => null,
                        //'proxy_password' => null,
                        // Set connection timeout (in seconds).
                        'connection_timeout' => TNTOfficiel::REQUEST_CONNECTTIMEOUT,
                        'user_agent' => 'PHP/SOAP',
                    )
                );

                // Add WS-Security Header
                $objSoapClient->__setSOAPHeaders(
                    TNTOfficiel_SoapClient::getHeader(
                        $this->strAccountLogin,
                        $this->strAccountPassword,
                        'PasswordDigest'
                    )
                );

                // Call.
                $fltRequestTimeStart = microtime(true);
                $objStdClassResponseSOAP = $objSoapClient->__soapCall($strArgService, array($arrArgParams));
                $fltRequestTimeEnd = microtime(true);

                $boolSuccess = true;

                // Cache.
                if (is_string($strCacheKey)) {
                    // TODO : if (is_object($objStdClassResponseSOAP) && get_class($objStdClassResponseSOAP) === 'stdClass')
                    TNTOfficielCache::store($strCacheKey, $objStdClassResponseSOAP, $intArgTTL);
                }
            } catch (Exception $objException) {
                $fltRequestTimeEnd = microtime(true);
            }

            ini_set('default_socket_timeout', $mxdSocketTimeoutRestore);

            // Log request.
            TNTOfficiel_Logger::logRequest(
                $boolSuccess,
                'SOAP',
                $strArgService,
                $this->strAccountNumber,
                $fltRequestTimeEnd - $fltRequestTimeStart,
                $objException
            );

            // Log default.
            TNTOfficiel_Logstack::dump(
                array(
                    'method' => sprintf('%s::%s', __CLASS__, __FUNCTION__),
                    'service' => $strArgService,
                    'account' => $this->strAccountNumber,
                    'duration' => sprintf('%0.3f', $fltRequestTimeEnd - $fltRequestTimeStart),
                    'url' => TNTOfficiel_SoapClient::URL_WSDL,
                    'parameters' => $arrArgParams,
                    'response' => $objStdClassResponseSOAP,
                    'lastRequestHeaders' => $objSoapClient ? $objSoapClient->__getLastRequestHeaders() : null,
                    'lastRequest' => $objSoapClient ? $objSoapClient->__getLastRequest() : null,
                    'lastResponseHeaders' => $objSoapClient ? $objSoapClient->__getLastResponseHeaders() : null,
                    'lastResponse' => $objSoapClient ? $objSoapClient->__getLastResponse() : null,
                    'exception' => $objException === null ? null : TNTOfficiel_Logstack::exception($objException, true),
                )
            );
        }

        if ($objException !== null) {
            // SoapFault Exception.
            if (get_class($objException) === 'SoapFault') {
                if ($objException->faultcode === 'WSDL') {
                    // Communication Error (WSDL).
                    // Connection Timeout.
                    // SOAP-ERROR: Parsing WSDL: Couldn't load from '[^']*' : failed to load external entity "[^"]*"
                    return null;
                } elseif (
                    $objException->faultcode === 'HTTP'
                    && in_array(
                        trim($objException->getMessage()),
                        array(
                            'Could not connect to host', // Connection to Host.
                            'Error Fetching http headers', // Connection Timeout.
                            'Service Temporarily Unavailable',
                            'Internal Server Error',
                        )
                    )
                ) {
                    // Communication Error (HTTP).
                    return null;
                } elseif (preg_match('/^[0-9]{8}$/ui', $this->strAccountNumber) !== 1) {
                    // Authentication Error (Account number).
                    return false;
                } elseif ($objException->faultcode === 'ns1:FailedAuthentication') {
                    // Authentication Error (Email or Password).
                    // Email: 'The security token could not be authenticated or authorized; nested exception is:  org.apache.ws.security.WSSecurityException: User \'[^']*\' was not found.'
                    // Password: 'The security token could not be authenticated or authorized'
                    return false;
                } elseif ($objException->faultcode === 'soap:Server') {
                    $strArgMessage = trim($objException->getMessage());

                    $strPatternErrorAccount = <<<'REGEXP'
/^The field 'accountNumber' is not valid\./ui
REGEXP;
                    // The field 'accountNumber' is not valid. This account number is not registered.
                    $strPatternErrorAccountNotRegistered = <<<'REGEXP'
/This account number is not registered\./ui
REGEXP;
                    if (preg_match($strPatternErrorAccount, $strArgMessage) === 1
                        && preg_match($strPatternErrorAccountNotRegistered, $strArgMessage) !== 1
                    ) {
                        // Authentication Error (Account number).
                        // 'The field \'accountNumber\' is not valid.'
                        // 'The field \'accountNumber\' is not valid. The field \'accountNumber\' has an invalid size. Valid is 8 characters.'
                        return false;
                    }
                }

                // Webservice Error (Unknown).
                return $objException;
            }

            // Log as error for detail.
            TNTOfficiel_Logger::logException($objException);

            // Communication Error (Unknown).
            return null;
        }

        return $objStdClassResponseSOAP;
    }

    /**
     * Check credential validity with authentication.
     * No cache store.
     *
     * @return bool|null
     */
    public function isCorrectAuthentication()
    {
        TNTOfficiel_Logstack::log();

        $arrParamRequest = array(
            'parameters' => array(
                'accountNumber' => $this->strAccountNumber,
                'sender' => array(
                    'zipCode' => '75001',
                    'city' => 'PARIS 01',
                ),
                'receiver' => array(
                    'zipCode' => '75001',
                    'city' => 'PARIS 01',
                    'type' => 'INDIVIDUAL',
                ),
                'shippingDelay' => 0,
            ),
        );

        $objStdClassResponse = $this->request('feasibility', $arrParamRequest);

        // Response error as a Communication error.
        if ($objStdClassResponse instanceof Exception) {
            // Log as error for detail.
            TNTOfficiel_Logger::logException($objStdClassResponse);

            return null;
        }
        // Communication error.
        if ($objStdClassResponse === null) {
            return null;
        }
        // Authentication error.
        if ($objStdClassResponse === false) {
            return false;
        }

        return true;
    }

    /**
     * Get cities from a zipcode.
     * Cleaning PostCode and City Name, then checking City exist for PostCode.
     *
     * @param string      $strCountryISO
     * @param             string      strArgZipCode
     * @param string|null $strArgCity (optional)
     *
     * @return array
     */
    public function citiesGuide($strArgCountryISO, $strArgZipCode, $strArgCity = null)
    {
        TNTOfficiel_Logstack::log();

        /*
         * Input clean.
         */

        $strCountryISO = is_string($strArgCountryISO) ? Tools::strtoupper(trim($strArgCountryISO)) : null;
        $strZipCode = is_string($strArgZipCode) ? trim($strArgZipCode) : null;
        $strCity = is_string($strArgCity) ? trim($strArgCity) : null;

        if (!is_string($strZipCode) || preg_match('/^[0-9]{5}$/ui', $strZipCode) !== 1) {
            $strZipCode = null;
        }

        if ($strZipCode == '75000') {
            $strZipCode = '75001';
        } elseif ($strZipCode == '69000') {
            $strZipCode = '69001';
        } elseif ($strZipCode == '13000') {
            $strZipCode = '13001';
        }

        if (is_string($strCity) && Tools::strlen($strCity) > 0) {
            // Accents.
            $strCity = Tools::htmlentitiesUTF8($strCity);
            $strCity = preg_replace(
                '#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#',
                '\1',
                $strCity
            );
            $strCity = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $strCity);
            $strCity = preg_replace('#&[^;]+;#', '', $strCity);

            // Special Char.
            $strCity = preg_replace('#[^A-Za-z0-9]+#', ' ', $strCity);
            // Space.
            $strCity = trim($strCity);
            // UpperCase.
            $strCity = Tools::strtoupper($strCity);

            // Replacing words.
            if ($strZipCode != '56110' && $strZipCode != '69125') {
                $arrCityWord = explode(' ', $strCity);
                foreach ($arrCityWord as $key => $word) {
                    $arrCityWord[$key] = preg_replace('#^SAINTE$#', 'STE', $arrCityWord[$key]);
                    $arrCityWord[$key] = preg_replace('#^SAINT$#', 'ST', $arrCityWord[$key]);
                }
                $strCity = implode(' ', $arrCityWord);
            }
        } else {
            $strCity = null;
        }

        /*
         * Output default.
         */

        $arrResult = array(
            'boolIsCountrySupported' => false,
            'boolIsRequestComError' => false,
            'strResponseMsgError' => null,
            'strZipCode' => $strZipCode,
            'strCity' => $strCity,
            'boolIsZipCodeCedex' => false,
            'arrCitiesNamePerZipCodeList' => array(),
            'arrCitiesNameList' => array(),
            'boolIsCityNameValid' => false,
        );

        // Only FR country is supported.
        if ($strCountryISO !== 'FR') {
            return $arrResult;
        }

        $arrResult['boolIsCountrySupported'] = true;

        // If zipcode syntax is invalid.
        if ($strZipCode === null) {
            return $arrResult;
        }

        /*
         * Cache
         */

        // Set cache params.
        $arrParamCache = array(
            'countryISO' => $strCountryISO,
            'zipCode' => $strZipCode,
            'cityName' => $strCity,
        );
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParamCache);
        $intTTL = 60 * 60 * 24 * 2;
        $boolCacheStoreEnable = true;

        // Check if already in cache.
        if (TNTOfficielCache::isStored($strCacheKey)) {
            return TNTOfficielCache::retrieve($strCacheKey);
        }

        $arrParamRequest = array(
            'zipCode' => $strZipCode,
        );
        $objStdClassResponse = $this->request('citiesGuide', $arrParamRequest);

        // Authentication error as Communication error.
        if ($objStdClassResponse === false) {
            $objStdClassResponse = null;
        }

        // Communication error.
        if ($objStdClassResponse === null) {
            $arrResult['boolIsRequestComError'] = true;
            // Disable Cache.
            $boolCacheStoreEnable = false;
        }

        // Response error.
        $strResponseError = null;
        if ($objStdClassResponse instanceof Exception) {
            // Get error message.
            $strResponseError = trim(preg_replace('/[[:cntrl:]]+/', ' ', $objStdClassResponse->getMessage()));
            // Set error in result.
            $arrResult['strResponseMsgError'] = $strResponseError;
            // Disable Cache.
            $boolCacheStoreEnable = false;
            // Log as error for detail.
            TNTOfficiel_Logger::logException($objStdClassResponse);
        }

        // Response match expected.
        if (is_object($objStdClassResponse)
            && get_class($objStdClassResponse) === 'stdClass'
            && property_exists($objStdClassResponse, 'City')
        ) {
            // Convert an item to array of one item
            if (is_object($objStdClassResponse->City)
                && property_exists($objStdClassResponse->City, 'name')
                && property_exists($objStdClassResponse->City, 'zipCode')
            ) {
                $objStdClassResponse->City = array(
                    (object)array(
                        'name' => trim($objStdClassResponse->City->name),
                        'zipCode' => trim($objStdClassResponse->City->zipCode),
                    ),
                );
            }

            //
            if (is_array($objStdClassResponse->City)
                && count($objStdClassResponse->City) > 0
            ) {
                foreach ($objStdClassResponse->City as $objItem) {
                    $arrResult['arrCitiesNamePerZipCodeList'][$objItem->zipCode][] = $objItem->name;
                    if ($objItem->zipCode === $strZipCode && $objItem->name === $strCity) {
                        $arrResult['boolIsCityNameValid'] = true;
                    }
                }
            }
        }

        /*
         * CEDEX (Courrier d’Entreprise à Distribution EXceptionnelle).
         */

        if (!array_key_exists($strZipCode, $arrResult['arrCitiesNamePerZipCodeList'])) {
            $strFileLocation = _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/libraries/data/postcode/cedex/'
                .Tools::substr($strZipCode, 0, 2).'.json';
            $strFileContent = Tools::file_get_contents($strFileLocation);
            if ($strFileContent !== false) {
                $arrFileContent = Tools::jsonDecode($strFileContent, true);
                if (array_key_exists($strZipCode, $arrFileContent)) {
                    $arrResult['boolIsZipCodeCedex'] = true;
                    foreach ($arrFileContent[$strZipCode] as $arrItem) {
                        $arrResult['arrCitiesNamePerZipCodeList'][$strZipCode][] = $arrItem['city'];
                        if ($arrItem['city'] === $strCity) {
                            $arrResult['boolIsCityNameValid'] = true;
                        }
                    }
                }
            }
        }

        // Set city list for the requested zipcode.
        if (array_key_exists($strZipCode, $arrResult['arrCitiesNamePerZipCodeList'])) {
            $arrResult['arrCitiesNameList'] = $arrResult['arrCitiesNamePerZipCodeList'][$strZipCode];
        }

        // If no Communication error and no Response error.
        if ($boolCacheStoreEnable) {
            // Cache.
            TNTOfficielCache::store($strCacheKey, $arrResult, $intTTL);
        }

        return $arrResult;
    }

    /**
     * Get dropOffPoints from the zipcode and cityname.
     * Ready for use with estimated delivery date (but unused since WS does not manage it).
     * EDD may be used to exclude delivery points that have a planned temporary closure, or alert user.
     *
     * @param string      $strArgZipCode
     * @param string      $strArgCity
     * @param string|null $strArgEDD (optional) Actually not supported.
     *
     * @return array
     */
    public function dropOffPoints($strArgZipCode, $strArgCity, $strArgEDD = null)
    {
        TNTOfficiel_Logstack::log();

        $arrResultCitiesGuide = $this->citiesGuide('FR', $strArgZipCode, $strArgCity);
        // Auto formatting.
        $strZipCode = $arrResultCitiesGuide['strZipCode'];
        $strCity = $arrResultCitiesGuide['strCity'];

        // Auto-Select
        if (/*$strCity === null*/ !$arrResultCitiesGuide['boolIsCityNameValid']
            && count($arrResultCitiesGuide['arrCitiesNameList']) > 0
        ) {
            $strCity = $arrResultCitiesGuide['arrCitiesNameList'][0];
            $arrResultCitiesGuide['boolIsCityNameValid'] = true;
        }

        // Date Today.
        $objDateTimeToday = new DateTime('midnight');
        // Date Tomorrow.
        $objDateTimeTomorrow = new DateTime('midnight +1 day');
        // Date In one Month.
        $objDateTime1Month = new DateTime('midnight +1 month');

        // Default EDD is tomorrow.
        $strEDD = $objDateTimeTomorrow->format('Y-m-d');
        // Check EDD argument.
        $objDateTimeEDDCheck = TNTOfficiel_Tools::getDateTime($strArgEDD);
        if ($objDateTimeEDDCheck !== null) {
            $strEDD = $strArgEDD;
        }

        $objDateTimeEDD = DateTime::createFromFormat('Y-m-d', $strEDD);
        $objDateTimeEDD->modify('midnight');
        $objDateTimeEDD10 = DateTime::createFromFormat('Y-m-d', $strEDD);
        $objDateTimeEDD10->modify('midnight +10 day');


        // Set cache params.
        $arrParamCache = array(
            'zipCode' => $strZipCode,
            'cityName' => $strCity,
            'EDD' => $strEDD,
        );
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParamCache);
        $intTTL = TNTOfficielCache::getSecondsUntilMidnight();
        $boolCacheStoreEnable = true;

        // Check if already in cache.
        if (TNTOfficielCache::isStored($strCacheKey)) {
            return TNTOfficielCache::retrieve($strCacheKey);
        }

        $arrPointsList = array();

        $objStdClassResponse = null;
        // If zipCode and CityName Valid.
        if (!$arrResultCitiesGuide['boolIsRequestComError']
            && $arrResultCitiesGuide['boolIsCityNameValid']
        ) {
            $arrParamRequest = array(
                'zipCode' => $strZipCode,
                'city' => $strCity,
            );
            $objStdClassResponse = $this->request('dropOffPoints', $arrParamRequest);
        }

        // Authentication error as Communication error.
        if ($objStdClassResponse === false) {
            $objStdClassResponse = null;
        }

        // Communication error.
        $boolIsRequestComError = false;
        if ($objStdClassResponse === null) {
            $boolIsRequestComError = true;
            // Disable Cache.
            $boolCacheStoreEnable = false;
        }

        // Response error.
        $strResponseError = null;
        if ($objStdClassResponse instanceof Exception) {
            // Get error message.
            $strResponseError = trim(preg_replace('/[[:cntrl:]]+/', ' ', $objStdClassResponse->getMessage()));
            // Disable Cache.
            $boolCacheStoreEnable = false;
            // Log as error for detail.
            TNTOfficiel_Logger::logException($objStdClassResponse);
        }

        //
        if (is_object($objStdClassResponse)
            && get_class($objStdClassResponse) === 'stdClass'
            && property_exists($objStdClassResponse, 'DropOffPoint')
        ) {
            // Convert an item to array of one item
            if (is_object($objStdClassResponse->DropOffPoint)
                && property_exists($objStdClassResponse->DropOffPoint, 'xETTCode')
                && property_exists($objStdClassResponse->DropOffPoint, 'name')
            ) {
                if (!property_exists($objStdClassResponse->DropOffPoint, 'message')) {
                    $objStdClassResponse->DropOffPoint->message = '';
                }
                $objStdClassResponse->DropOffPoint = array(
                    (object)array(
                        'xETTCode' => $objStdClassResponse->DropOffPoint->xETTCode,
                        'name' => $objStdClassResponse->DropOffPoint->name,
                        'address1' => $objStdClassResponse->DropOffPoint->address1,
                        'zipCode' => trim($objStdClassResponse->DropOffPoint->zipCode),
                        'city' => trim($objStdClassResponse->DropOffPoint->city),
                        'openingHours' => $objStdClassResponse->DropOffPoint->openingHours,
                        'message' => $objStdClassResponse->DropOffPoint->message,
                        'geolocationURL' => $objStdClassResponse->DropOffPoint->geolocalisationUrl,
                        'longitude' => $objStdClassResponse->DropOffPoint->longitude,
                        'latitude' => $objStdClassResponse->DropOffPoint->latitude,
                    ),
                );
            }

            //
            if (is_array($objStdClassResponse->DropOffPoint)
                && count($objStdClassResponse->DropOffPoint) > 0
            ) {
                foreach ($objStdClassResponse->DropOffPoint as $objDropOffPointInfo) {
                    $objDropOffPointOpeningHours = (object)array(
                        'Monday' => array(),
                        'Tuesday' => array(),
                        'Wednesday' => array(),
                        'Thursday' => array(),
                        'Friday' => array(),
                        'Saturday' => array(),
                        'Sunday' => array(),
                    );

                    foreach ($objDropOffPointInfo->openingHours as $strDay => $objDay) {
                        foreach ($objDay as $strAmPm => $strRange) {
                            // Formatting : "monday": { "pm": "Fermé" }, "tuesday": { "am": "09:00 - 12:00" },
                            // To : "Monday": [], "Tuesday": ["AM": ["09:00", "12:00"]],
                            $strDayUpper = Tools::strtoupper(Tools::substr($strDay, 0, 1))
                                .Tools::substr($strDay, 1);
                            $strAmPmUpper = Tools::strtoupper($strAmPm);
                            if (!in_array($strRange, array('FERME - FERME', 'Fermé'))) {
                                $arrRange = explode(' - ', $strRange);
                                $objDropOffPointOpeningHours->{$strDayUpper}[$strAmPmUpper] = $arrRange;
                            }
                        }
                    }

                    $objDropOffPointInfo->openingHours = $objDropOffPointOpeningHours;

                    /*
                     * Contrainte d'état du relais, date de fermeture et de ré-ouverture.
                     */

                    $strRPState = 'A'; //$objDropOffPointInfo->getState();

                    $strRPDateClosing = ''; //$objDropOffPointInfo->getDateClosing();
                    $objRPDateTimeClosing = DateTime::createFromFormat('Ymd', $strRPDateClosing);
                    $intRPStampClosing = null;
                    if (is_object($objRPDateTimeClosing)) {
                        $objRPDateTimeClosing->modify('midnight');
                        $strRPDateClosingCheck = $objRPDateTimeClosing->format('Ymd');
                        $intRPStampClosing = $objRPDateTimeClosing->format('U');
                    }
                    if (!is_object($objRPDateTimeClosing) || $strRPDateClosing !== $strRPDateClosingCheck) {
                        $objRPDateTimeClosing = DateTime::createFromFormat('U', '0');
                        $objRPDateTimeClosing->modify('midnight');
                    }

                    $strRPDateReOpening = ''; //$objDropOffPointInfo->getDateReopening();
                    $objRPDateTimeReOpening = DateTime::createFromFormat('Ymd', $strRPDateReOpening);
                    $intRPStampReOpening = null;
                    if (is_object($objRPDateTimeReOpening)) {
                        $objRPDateTimeReOpening->modify('midnight');
                        $strRPDateReOpeningCheck = $objRPDateTimeReOpening->format('Ymd');
                        $intRPStampReOpening = $objRPDateTimeReOpening->format('U');
                    }
                    if (!is_object($objRPDateTimeReOpening) || $strRPDateReOpening !== $strRPDateReOpeningCheck) {
                        $objRPDateTimeReOpening = DateTime::createFromFormat('U', '0');
                        $objRPDateTimeReOpening->modify('midnight');
                    }

                    $boolRPEnable = false;

                    if (
                        (
                            // Si Actif
                            $strRPState === 'A'
                            && (
                                (
                                    // si la date de reprise est <= à la date de livraison
                                    $objRPDateTimeReOpening <= $objDateTimeEDD
                                    // et
                                    && (
                                        // si la date de fin est < à la date de livraison
                                        $objRPDateTimeClosing < $objDateTimeEDD
                                        // ou si la date de fin est >= à la date de livraison + 10 jours
                                        || $objRPDateTimeClosing >= $objDateTimeEDD10
                                    )
                                )
                                || (
                                    // Si la date de reprise est >= à la date de fin
                                    $objRPDateTimeReOpening >= $objRPDateTimeClosing
                                    // et si la date de fin est >= à la date de livraison + 10 jours
                                    && $objRPDateTimeClosing >= $objDateTimeEDD10
                                )
                            )
                        )
                        || (
                            // Ou Si Créé ou Réactivé
                            ($strRPState === 'C' || $strRPState === 'R')
                            && (
                                // avec une date de reprise existante
                                $strRPDateReOpening !== ''
                                // et <= à la date de livraison
                                && $objRPDateTimeReOpening <= $objDateTimeEDD
                            )
                        )
                    ) {
                        $boolRPEnable = true;
                    }


                    if ($boolRPEnable === true) {
                        // Closing Date exist.
                        if ($intRPStampClosing !== null) {
                            // Check Closing Date Validity.
                            // et si la date de fermeture n'est pas expirée,
                            // et qu'elle n'est pas plus tard que dans un mois.
                            if (is_object($objRPDateTimeClosing)
                                && $objRPDateTimeClosing->format('U') === $intRPStampClosing
                                && $objRPDateTimeClosing > $objDateTimeToday
                                && $objRPDateTimeClosing <= $objDateTime1Month
                            ) {
                                $intRPStampClosing = $objRPDateTimeClosing->format('d/m/Y');
                            } else {
                                $intRPStampClosing = null;
                            }
                        }

                        // ReOpening Date exist.
                        if ($intRPStampReOpening !== null) {
                            // Check ReOpening Date Validity.
                            // et si la date de réouverture n'est pas expirée,
                            if (is_object($objRPDateTimeReOpening)
                                && $objRPDateTimeReOpening->format('U') === $intRPStampReOpening
                                && $objRPDateTimeReOpening > $objDateTimeToday
                            ) {
                                $intRPStampReOpening = $objRPDateTimeReOpening->format('d/m/Y');
                            } else {
                                $intRPStampReOpening = null;
                            }
                        }

                        $arrPointsList[] = array(
                            'xett' => $objDropOffPointInfo->xETTCode,
                            'name' => $objDropOffPointInfo->name,
                            'city' => trim($objDropOffPointInfo->city),
                            'postcode' => trim($objDropOffPointInfo->zipCode),
                            'address' => $objDropOffPointInfo->address1,
                            'schedule' => $objDropOffPointInfo->openingHours,
                            'latitude' => $objDropOffPointInfo->latitude,
                            'longitude' => $objDropOffPointInfo->longitude,
                            'enabled' => $boolRPEnable,
                            'closing' => $intRPStampClosing,
                            'reopening' => $intRPStampReOpening,
                        );
                    }
                }
            }
        }

        $arrResult = array(
            'boolIsRequestComError' => $boolIsRequestComError,
            'strResponseMsgError' => $strResponseError,
            'strZipCode' => $strZipCode,
            'strCity' => $strCity,
            'arrCitiesNameList' => $arrResultCitiesGuide['arrCitiesNameList'],
            'arrPointsList' => $arrPointsList,
        );

        // If no Communication error and no Response error.
        if ($boolCacheStoreEnable) {
            // Cache.
            TNTOfficielCache::store($strCacheKey, $arrResult, $intTTL);
        }

        return $arrResult;
    }

    /**
     * Get tntDepots from the zipcode and cityname.
     *
     * @param string      $strArgZipCode
     * @param string      $strArgCity
     * @param string|null $strArgEDD (optional) Actually not supported.
     *
     * @return array
     */
    public function tntDepots($strArgZipCode, $strArgCity, $strArgEDD = null)
    {
        TNTOfficiel_Logstack::log();

        $arrResultCitiesGuide = $this->citiesGuide('FR', $strArgZipCode, $strArgCity);
        // Auto formatting.
        $strZipCode = $arrResultCitiesGuide['strZipCode'];
        $strCity = $arrResultCitiesGuide['strCity'];

        // Auto-Select
        if (/*$strCity === null*/ !$arrResultCitiesGuide['boolIsCityNameValid']
            && count($arrResultCitiesGuide['arrCitiesNameList']) > 0
        ) {
            $strCity = $arrResultCitiesGuide['arrCitiesNameList'][0];
            $arrResultCitiesGuide['boolIsCityNameValid'] = true;
        }

        // Date Tomorrow.
        $objDateTimeTomorrow = new DateTime('midnight +1 day');

        // Default EDD is tomorrow.
        $strEDD = $objDateTimeTomorrow->format('Y-m-d');
        // Check EDD argument.
        $objDateTimeEDDCheck = TNTOfficiel_Tools::getDateTime($strArgEDD);
        if ($objDateTimeEDDCheck !== null) {
            $strEDD = $strArgEDD;
        }


        // Set cache params.
        $arrParamCache = array(
            'zipCode' => $strZipCode,
            'cityName' => $strCity,
            'EDD' => $strEDD,
        );
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParamCache);
        $intTTL = TNTOfficielCache::getSecondsUntilMidnight();
        $boolCacheStoreEnable = true;

        // Check if already in cache.
        if (TNTOfficielCache::isStored($strCacheKey)) {
            return TNTOfficielCache::retrieve($strCacheKey);
        }

        $arrPointsList = array();

        $objStdClassResponse = null;
        // If zipCode and CityName Valid.
        if (!$arrResultCitiesGuide['boolIsRequestComError']
            && $arrResultCitiesGuide['boolIsCityNameValid']
        ) {
            $arrParamRequest = array(
                'department' => Tools::substr($strZipCode, 0, 2),
            );
            $objStdClassResponse = $this->request('tntDepots', $arrParamRequest);
        }

        // Authentication error as Communication error.
        if ($objStdClassResponse === false) {
            $objStdClassResponse = null;
        }

        // Communication error.
        $boolIsRequestComError = false;
        if ($objStdClassResponse === null) {
            $boolIsRequestComError = true;
            // Disable Cache.
            $boolCacheStoreEnable = false;
        }

        // Response error.
        $strResponseError = null;
        if ($objStdClassResponse instanceof Exception) {
            // Get error message.
            $strResponseError = trim(preg_replace('/[[:cntrl:]]+/', ' ', $objStdClassResponse->getMessage()));
            // Disable Cache.
            $boolCacheStoreEnable = false;
            // Log as error for detail.
            TNTOfficiel_Logger::logException($objStdClassResponse);
        }

        //
        if (is_object($objStdClassResponse)
            && get_class($objStdClassResponse) === 'stdClass'
            && property_exists($objStdClassResponse, 'DepotInfo')
        ) {
            // Convert an item to array of one item
            if (is_object($objStdClassResponse->DepotInfo)
                && property_exists($objStdClassResponse->DepotInfo, 'pexCode')
                && property_exists($objStdClassResponse->DepotInfo, 'name')
            ) {
                if (!property_exists($objStdClassResponse->DepotInfo, 'message')) {
                    $objStdClassResponse->DepotInfo->message = '';
                }
                $objStdClassResponse->DepotInfo = array(
                    (object)array(
                        'pexCode' => $objStdClassResponse->DepotInfo->pexCode,
                        'name' => $objStdClassResponse->DepotInfo->name,
                        'address1' => $objStdClassResponse->DepotInfo->address1,
                        'address2' => $objStdClassResponse->DepotInfo->address2,
                        'zipCode' => trim($objStdClassResponse->DepotInfo->zipCode),
                        'city' => trim($objStdClassResponse->DepotInfo->city),
                        'openingHours' => $objStdClassResponse->DepotInfo->openingHours,
                        'message' => $objStdClassResponse->DepotInfo->message,
                        'geolocationURL' => $objStdClassResponse->DepotInfo->geolocalisationUrl,
                        'longitude' => $objStdClassResponse->DepotInfo->longitude,
                        'latitude' => $objStdClassResponse->DepotInfo->latitude,
                    ),
                );
            }

            //
            if (is_array($objStdClassResponse->DepotInfo)
                && count($objStdClassResponse->DepotInfo) > 0
            ) {
                foreach ($objStdClassResponse->DepotInfo as $objDepotInfo) {
                    $objDepotInfoOpeningHours = (object)array(
                        'Monday' => array(),
                        'Tuesday' => array(),
                        'Wednesday' => array(),
                        'Thursday' => array(),
                        'Friday' => array(),
                        'Saturday' => array(),
                        'Sunday' => array(),
                    );

                    foreach ($objDepotInfo->openingHours as $strDay => $objDay) {
                        foreach ($objDay as $strAmPm => $strRange) {
                            // Formatting : "monday": { "pm": "Fermé" }, "tuesday": { "am": "09:00 - 12:00" },
                            // To : "Monday": [], "Tuesday": ["AM": ["09:00", "12:00"]],
                            $strDayUpper = Tools::strtoupper(Tools::substr($strDay, 0, 1))
                                .Tools::substr($strDay, 1);
                            $strAmPmUpper = Tools::strtoupper($strAmPm);
                            if (!in_array($strRange, array('FERME - FERME', 'Fermé'))) {
                                $arrRange = explode(' - ', $strRange);
                                $objDepotInfoOpeningHours->{$strDayUpper}[$strAmPmUpper] = $arrRange;
                            }
                        }
                    }

                    $objDepotInfo->openingHours = $objDepotInfoOpeningHours;
                    if (!property_exists($objDepotInfo, 'address2')) {
                        $objDepotInfo->address2 = '';
                    }

                    $arrPointsList[] = array(
                        'pex' => $objDepotInfo->pexCode,
                        'name' => $objDepotInfo->name,
                        'city' => trim($objDepotInfo->city),
                        'postcode' => trim($objDepotInfo->zipCode),
                        'address1' => $objDepotInfo->address1,
                        'address2' => $objDepotInfo->address2,
                        'schedule' => $objDepotInfo->openingHours,
                        'latitude' => $objDepotInfo->latitude,
                        'longitude' => $objDepotInfo->longitude,
                        'enabled' => true,
                        'closing' => null,
                        'reopening' => null,
                    );
                }
            }
        }

        $arrResult = array(
            'boolIsRequestComError' => $boolIsRequestComError,
            'strResponseMsgError' => $strResponseError,
            'strZipCode' => $strZipCode,
            'strCity' => $strCity,
            'arrCitiesNameList' => $arrResultCitiesGuide['arrCitiesNameList'],
            'arrPointsList' => $arrPointsList,
        );

        // If no Communication error and no Response error.
        if ($boolCacheStoreEnable) {
            // Cache.
            TNTOfficielCache::store($strCacheKey, $arrResult, $intTTL);
        }

        return $arrResult;
    }

    /**
     * Get feasible carrier service list.
     *
     * @param string                   $strArgReceiverType
     * @param string                   $strArgSenderZipCode
     * @param string                   $strArgSenderCity
     * @param string                   $strArgReceiverZipCode
     * @param string                   $strArgReceiverCity
     * @param int                      $intArgShippingDelay
     * @param string|int|DateTime|null $mxdArgShippingDate
     *
     * @return array
     */
    public function feasibility(
        $strArgReceiverType = 'INDIVIDUAL',
        $strArgSenderZipCode = '75001',
        $strArgSenderCity = 'PARIS 01',
        $strArgReceiverZipCode = '75001',
        $strArgReceiverCity = 'PARIS 01',
        $intArgShippingDelay = 0,
        $mxdArgShippingDate = null
    ) {
        TNTOfficiel_Logstack::log();

        $arrResultCitiesGuideSender = $this->citiesGuide('FR', $strArgSenderZipCode, $strArgSenderCity);
        // Auto formatting.
        $strSenderZipCode = Tools::substr($arrResultCitiesGuideSender['strZipCode'], 0, 5);
        $strSenderCity = Tools::substr($arrResultCitiesGuideSender['strCity'], 0, 27);

        $arrResultCitiesGuideReceiver = $this->citiesGuide('FR', $strArgReceiverZipCode, $strArgReceiverCity);
        // Auto formatting.
        $strReceiverZipCode = Tools::substr($arrResultCitiesGuideReceiver['strZipCode'], 0, 5);
        $strReceiverCity = Tools::substr($arrResultCitiesGuideReceiver['strCity'], 0, 27);

        // Check Shipping date requested for apply. Default is no date (null).
        $strShippingDate = TNTOfficiel_Tools::getDateTimeFormat($mxdArgShippingDate, 'Y-m-d', null);

        // The date has priority over the delay.
        if ($strShippingDate !== null) {
            $intArgShippingDelay = 0;
        }

        $intShippingDelay = (int)$intArgShippingDelay;
        if (!($intShippingDelay > 0)) {
            $intShippingDelay = 0;
        }

        $arrTNTServiceList = array();

        // Set cache params.
        $arrParamCache = array(
            'parameters' => array(
                //'accountLogin' => $this->strAccountLogin,
                'accountNumber' => $this->strAccountNumber,
                'sender' => array(
                    'zipCode' => $strSenderZipCode,
                    'city' => $strSenderCity,
                ),
                'receiver' => array(
                    'zipCode' => $strReceiverZipCode,
                    'city' => $strReceiverCity,
                    'type' => $strArgReceiverType,
                ),
                'shipping' => $strShippingDate !== null ? $strShippingDate : $intShippingDelay,
            ),
        );
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParamCache);
        $intTTL = 60 * 60 * 4;
        $boolCacheStoreEnable = true;

        // Check if already in cache.
        if (TNTOfficielCache::isStored($strCacheKey)) {
            return TNTOfficielCache::retrieve($strCacheKey);
        }

        $objStdClassResponse = null;
        // If zipCode and CityName Valid for sender and receiver.
        if (!$arrResultCitiesGuideSender['boolIsRequestComError']
            && !$arrResultCitiesGuideReceiver['boolIsRequestComError']
            && $arrResultCitiesGuideSender['boolIsCityNameValid']
            && $arrResultCitiesGuideReceiver['boolIsCityNameValid']
        ) {
            $arrParamRequest = array(
                'parameters' => array(
                    'accountNumber' => $this->strAccountNumber,
                    'sender' => array(
                        'zipCode' => $strSenderZipCode,
                        'city' => $strSenderCity,
                    ),
                    'receiver' => array(
                        'zipCode' => $strReceiverZipCode,
                        'city' => $strReceiverCity,
                        'type' => $strArgReceiverType,
                    ),
                ),
            );
            // Using specified date.
            if ($strShippingDate !== null) {
                $arrParamRequest['parameters']['shippingDate'] = $strShippingDate;
            } else {
                $arrParamRequest['parameters']['shippingDelay'] = $intShippingDelay;
            }

            // If no date or is a week day (weekend is invalid) not in past.
            if ($strShippingDate === null
                || (TNTOfficiel_Tools::isWeekDay($strShippingDate)
                    && TNTOfficiel_Tools::isTodayOrLater($strShippingDate))
            ) {
                $objStdClassResponse = $this->request('feasibility', $arrParamRequest);
            }
        }

        // Authentication error as Communication error.
        if ($objStdClassResponse === false) {
            $objStdClassResponse = null;
        }

        // Communication error.
        $boolIsRequestComError = false;
        if ($objStdClassResponse === null) {
            $boolIsRequestComError = true;
            // Disable Cache.
            $boolCacheStoreEnable = false;
        }

        // Response error.
        $strResponseError = null;
        if ($objStdClassResponse instanceof Exception) {
            // Get error message.
            $strResponseError = trim(preg_replace('/[[:cntrl:]]+/', ' ', $objStdClassResponse->getMessage()));

            // [soap:Server] Code 0: The field 'shippingDate' is not valid.
            $strPatternErrorShippingDate = <<<'REGEXP'
/^The field 'shippingDate' is not valid\./ui
REGEXP;

            // For input string: "[^"]*"; nested exception is java.lang.NumberFormatException: For input string: "[^"]*"
            $strPatternErrorInputString = <<<'REGEXP'
/^For input string: "[^"]*"; nested exception is java/ui
REGEXP;

            if (preg_match($strPatternErrorShippingDate, $strResponseError) === 1) {
                // Enable Cache (explicit).
                $boolCacheStoreEnable = true;
            } elseif (preg_match($strPatternErrorInputString, $strResponseError) === 1) {
                // TODO : Webservice Error (may need retry, but non recursive).
                /*return $this->feasibility(
                    $strArgReceiverType,
                    $strArgSenderZipCode,
                    $strArgSenderCity,
                    $strArgReceiverZipCode,
                    $strArgReceiverCity,
                    $intArgShippingDelay,
                    $mxdArgShippingDate
                );*/
                // Disable Cache.
                $boolCacheStoreEnable = false;
                // Log as error for detail.
                TNTOfficiel_Logger::logException($objStdClassResponse);
            } else {
                // Disable Cache.
                $boolCacheStoreEnable = false;
                // Log as error for detail.
                TNTOfficiel_Logger::logException($objStdClassResponse);
            }
        }

        //
        if (is_object($objStdClassResponse)
            && get_class($objStdClassResponse) === 'stdClass'
            && property_exists($objStdClassResponse, 'Service')
        ) {
            // Convert an item to array of one item
            if (is_object($objStdClassResponse->Service)
                && property_exists($objStdClassResponse->Service, 'serviceLabel')
                && property_exists($objStdClassResponse->Service, 'serviceCode')
            ) {
                $objStdClassResponse->Service = array(
                    (object)array(
                        'serviceCode' => $objStdClassResponse->Service->serviceCode,
                        'serviceLabel' => $objStdClassResponse->Service->serviceLabel,
                        'shippingDate' => $objStdClassResponse->Service->shippingDate,
                        'dueDate' => $objStdClassResponse->Service->dueDate,
                        'saturdayDelivery' => $objStdClassResponse->Service->saturdayDelivery,
                        'afternoonDelivery' => $objStdClassResponse->Service->afternoonDelivery,
                        'insurance' => $objStdClassResponse->Service->insurance,
                        'priorityGuarantee' => $objStdClassResponse->Service->priorityGuarantee,
                    ),
                );
            }

            //
            if (is_array($objStdClassResponse->Service)
                && count($objStdClassResponse->Service) > 0
            ) {
                foreach ($objStdClassResponse->Service as $objService) {
                    $arrTNTServiceList[] = array(
                        'accountType' => TNTOfficielCarrier::getAccountType(
                            $strArgReceiverType,
                            Tools::substr($objService->serviceCode, 1, 1),
                            $objService->serviceLabel
                        ),
                        'carrierType' => $strArgReceiverType,
                        'carrierCode1' => Tools::substr($objService->serviceCode, 0, 1),
                        'carrierCode2' => Tools::substr($objService->serviceCode, 1, 1),
                        'carrierLabel' => $objService->serviceLabel,
                        'shippingDate' => $objService->shippingDate,
                        'dueDate' => $objService->dueDate,
                        'saturdayDelivery' => $objService->saturdayDelivery,
                        'afternoonDelivery' => $objService->afternoonDelivery,
                        'insurance' => $objService->insurance,
                        'priorityGuarantee' => $objService->priorityGuarantee,
                    );
                }
            }
        }

        $arrResult = array(
            'boolIsRequestComError' => $boolIsRequestComError,
            'strResponseMsgError' => $strResponseError,
            'arrTNTServiceList' => $arrTNTServiceList,
        );

        // If no Communication error and no Response error.
        if ($boolCacheStoreEnable) {
            // Cache.
            TNTOfficielCache::store($strCacheKey, $arrResult, $intTTL);
        }

        return $arrResult;
    }

    /**
     * Used only for Occasional pickup type.
     * Get the pickup availabilities.
     *
     * @param string                   $strArgSenderZipCode
     * @param string                   $strArgSenderCity
     * @param string|int|DateTime|null $mxdArgPickupDate
     *
     * @return array
     */
    public function getPickupContext(
        $strArgSenderZipCode = '75001',
        $strArgSenderCity = 'PARIS 01',
        $mxdArgPickupDate = null
    ) {
        TNTOfficiel_Logstack::log();

        $arrResultCitiesGuideSender = $this->citiesGuide('FR', $strArgSenderZipCode, $strArgSenderCity);
        // Auto formatting.
        $strSenderZipCode = Tools::substr($arrResultCitiesGuideSender['strZipCode'], 0, 5);
        $strSenderCity = Tools::substr($arrResultCitiesGuideSender['strCity'], 0, 27);

        // Date Today.
        $objDateTimeToday = new DateTime('midnight');

        // Check Pickup date requested for apply. Default is today.
        $strPickupDate = TNTOfficiel_Tools::getDateTimeFormat($mxdArgPickupDate, 'Y-m-d', $objDateTimeToday);

        // Set cache params.
        $arrParamCache = array(
            'parameters' => array(
                'zipCode' => $strSenderZipCode,
                'city' => $strSenderCity,
                'shipping' => $strPickupDate,
            ),
        );
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParamCache);
        $intTTL = 60 * 60 * 4;
        $boolCacheStoreEnable = true;

        // Check if already in cache.
        if (TNTOfficielCache::isStored($strCacheKey)) {
            return TNTOfficielCache::retrieve($strCacheKey);
        }

        $objStdClassResponse = null;
        // If zipCode and CityName Valid for sender and receiver.
        if (!$arrResultCitiesGuideSender['boolIsRequestComError']
            && $arrResultCitiesGuideSender['boolIsCityNameValid']
        ) {
            $arrParamRequest = array(
                'parameters' => array(
                    'zipCode' => $strSenderZipCode,
                    'city' => $strSenderCity,
                    'shippingDate' => $strPickupDate,
                ),
            );

            $objStdClassResponse = $this->request('getPickupContext', $arrParamRequest);
        }

        // Authentication error as Communication error.
        if ($objStdClassResponse === false) {
            $objStdClassResponse = null;
        }

        // Communication error.
        $boolIsRequestComError = false;
        if ($objStdClassResponse === null) {
            $boolIsRequestComError = true;
            // Disable Cache.
            $boolCacheStoreEnable = false;
        }

        // Response error.
        $strResponseError = null;
        if ($objStdClassResponse instanceof Exception) {
            // Get error message.
            $strResponseError = trim(preg_replace('/[[:cntrl:]]+/', ' ', $objStdClassResponse->getMessage()));
            // Disable Cache.
            $boolCacheStoreEnable = false;
            // Log as error for detail.
            TNTOfficiel_Logger::logException($objStdClassResponse);
            // SoapFault [soap:Server] Code 0: The shipping date exceeds maximum allowed date .
        }

        $arrResult = array(
            'boolIsRequestComError' => $boolIsRequestComError,
            'strResponseMsgError' => $strResponseError,
            'pickupDate' => null,
            'cutOffTime' => null,
            'pickupOnMorning' => null,
        );

        //
        if (is_object($objStdClassResponse)
            && get_class($objStdClassResponse) === 'stdClass'
            && property_exists($objStdClassResponse, 'return')
        ) {
            if (is_object($objStdClassResponse->return)
                && property_exists($objStdClassResponse->return, 'shippingDate')
                && property_exists($objStdClassResponse->return, 'cutOffTime')
            ) {
                $arrResult['pickupDate'] = $objStdClassResponse->return->shippingDate;
                $arrResult['cutOffTime'] = $objStdClassResponse->return->cutOffTime;
                $arrResult['pickupOnMorning'] = $objStdClassResponse->return->pickupOnMorning;
            }
        }

        // If no Communication error and no Response error.
        if ($boolCacheStoreEnable) {
            // Cache.
            TNTOfficielCache::store($strCacheKey, $arrResult, $intTTL);
        }

        return $arrResult;
    }

    /**
     * Create an expedition.
     *
     * @param                     $strArgSenderCompany
     * @param                     $strArgSenderAddress1
     * @param                     $strArgSenderAddress2
     * @param                     $strArgSenderZipCode
     * @param                     $strArgSenderCity
     * @param                     $strArgSenderLastName
     * @param                     $strArgSenderFirstName
     * @param                     $strArgSenderEmail
     * @param                     $strArgSenderPhone
     * @param                     $strArgReceiverType
     * @param                     $strDeliveryPointCode
     * @param                     $strArgReceiverCompany
     * @param                     $strArgReceiverAddress1
     * @param                     $strArgReceiverAddress2
     * @param                     $strArgReceiverZipCode
     * @param                     $strArgReceiverCity
     * @param                     $strArgReceiverLastName
     * @param                     $strArgReceiverFirstName
     * @param                     $strArgReceiverEMail
     * @param                     $strArgReceiverPhone
     * @param                     $strArgReceiverBuilding
     * @param                     $strArgReceiverAccessCode
     * @param                     $strArgReceiverFloor
     * @param                     $boolArgDeliveryNotification
     * @param                     $strArgCarrierCode
     * @param string|int|DateTime $mxdArgPickupDate
     * @param array               $arrArgParcelRequest
     * @param                     $boolIsPickupTypeOccasional
     * @param                     $strArgPickupLabelType
     * @param                     $strArgPickupClosingTime
     * @param                     $boolArgSendPickupRequest
     * @param                     $fltArgPaybackAmount
     *
     * @return array
     */
    public function expeditionCreation(
        $strArgSenderCompany,
        $strArgSenderAddress1,
        $strArgSenderAddress2,
        $strArgSenderZipCode,
        $strArgSenderCity,
        $strArgSenderLastName,
        $strArgSenderFirstName,
        $strArgSenderEmail,
        $strArgSenderPhone,

        $strArgReceiverType,
        $strDeliveryPointCode,
        $strArgReceiverCompany,
        $strArgReceiverAddress1,
        $strArgReceiverAddress2,
        $strArgReceiverZipCode,
        $strArgReceiverCity,
        $strArgReceiverLastName,
        $strArgReceiverFirstName,
        $strArgReceiverEMail,
        $strArgReceiverPhone,
        $strArgReceiverBuilding,
        $strArgReceiverAccessCode,
        $strArgReceiverFloor,
        $strArgReceiverInstructions,
        $boolArgDeliveryNotification,

        $strArgCarrierCode,
        $mxdArgPickupDate,
        array $arrArgParcelRequest,

        $boolIsPickupTypeOccasional,
        $strArgPickupLabelType,
        $strArgPickupClosingTime,
        $boolArgSendPickupRequest,

        $fltArgPaybackAmount
    ) {
        TNTOfficiel_Logstack::log();

        $arrResultCitiesGuideSender = $this->citiesGuide('FR', $strArgSenderZipCode, $strArgSenderCity);
        // Auto formatting.
        $strSenderZipCode = Tools::substr($arrResultCitiesGuideSender['strZipCode'], 0, 5);
        $strSenderCity = Tools::substr($arrResultCitiesGuideSender['strCity'], 0, 27);

        $arrResultCitiesGuideReceiver = $this->citiesGuide('FR', $strArgReceiverZipCode, $strArgReceiverCity);
        // Auto formatting.
        $strReceiverZipCode = Tools::substr($arrResultCitiesGuideReceiver['strZipCode'], 0, 5);
        $strReceiverCity = Tools::substr($arrResultCitiesGuideReceiver['strCity'], 0, 27);

        // Check Pickup date requested for apply. Default is no date (null).
        $strPickupDate = TNTOfficiel_Tools::getDateTimeFormat($mxdArgPickupDate, 'Y-m-d', null);

        // If an address line is greater than 32 chars.
        if (Tools::strlen($strArgReceiverAddress1) > 32 || Tools::strlen($strArgReceiverAddress2) > 32) {
            $strAddressConcat = $strArgReceiverAddress1.' '.$strArgReceiverAddress2;
            $strAddressSingleLine = preg_replace('/[[:cntrl:]]+/', ' ', $strAddressConcat);
            $strAddressCleaned = trim(preg_replace('/[\s]{2,}/', ' ', $strAddressSingleLine));
            // Split to 32 chars.
            $arrReceiverAddress = TNTOfficiel_Tools::strSplitter($strAddressCleaned, 32);
            // If result have no more than 2 lines of address.
            if (is_array($arrReceiverAddress) && count($arrReceiverAddress) < 3) {
                $strArgReceiverAddress1 = '';
                if (array_key_exists(0, $arrReceiverAddress)) {
                    $strArgReceiverAddress1 = $arrReceiverAddress[0];
                }
                $strArgReceiverAddress2 = '';
                if (array_key_exists(1, $arrReceiverAddress)) {
                    $strArgReceiverAddress2 = $arrReceiverAddress[1];
                }
            }
        }

        // Set request params.
        $arrParamRequest = array(
            'parameters' => array(
                //'accountLogin' => $this->strAccountLogin,
                'accountNumber' => $this->strAccountNumber,
                'sender' => array(
                    'name' => TNTOfficiel_Tools::translitASCII($strArgSenderCompany, 32),
                    'address1' => TNTOfficiel_Tools::translitASCII($strArgSenderAddress1, 32),
                    'address2' => TNTOfficiel_Tools::translitASCII($strArgSenderAddress2, 32),
                    'zipCode' => $strSenderZipCode, // 5
                    'city' => $strSenderCity, // 27
                    'contactLastName' => TNTOfficiel_Tools::translitASCII($strArgSenderLastName, 19),
                    'contactFirstName' => TNTOfficiel_Tools::translitASCII($strArgSenderFirstName, 12),
                    'phoneNumber' => $strArgSenderPhone, // 15
                ),
                'receiver' => array(
                    'type' => $strArgReceiverType,
                    // If field typeId is set (type DROPOFFPOINT or DEPOT), address infos below
                    // (name, address1, address2, zipCode, city) are not used, but always auto-filled on label.
                    'typeId' => Tools::strtoupper($strDeliveryPointCode),
                    'name' => TNTOfficiel_Tools::translitASCII($strArgReceiverCompany, 32),
                    'address1' => TNTOfficiel_Tools::translitASCII($strArgReceiverAddress1, 32),
                    'address2' => TNTOfficiel_Tools::translitASCII($strArgReceiverAddress2, 32),
                    'zipCode' => $strReceiverZipCode, // 5
                    'city' => $strReceiverCity, // 27
                    'contactLastName' => TNTOfficiel_Tools::translitASCII($strArgReceiverLastName, 19),
                    'contactFirstName' => TNTOfficiel_Tools::translitASCII($strArgReceiverFirstName, 12),
                    'emailAddress' => $strArgReceiverEMail, // 80
                    'phoneNumber' => $strArgReceiverPhone, // 15
                    'accessCode' => TNTOfficiel_Tools::translitASCII($strArgReceiverAccessCode, 7),
                    'floorNumber' => TNTOfficiel_Tools::translitASCII($strArgReceiverFloor, 2),
                    'buldingId' => TNTOfficiel_Tools::translitASCII($strArgReceiverBuilding, 3),
                    'instructions' => TNTOfficiel_Tools::translitASCII($strArgReceiverInstructions, 60),
                    'sendNotification' => ($boolArgDeliveryNotification ? 1 : 0),
                ),
                'serviceCode' => $strArgCarrierCode,
                'shippingDate' => $strPickupDate,
                // Packages [1,30]
                'quantity' => count($arrArgParcelRequest),
                // Mandatory Saturday delivery request.
                'saturdayDelivery' => 0,
                'parcelsRequest' => (object)array(
                    'parcelRequest' => $arrArgParcelRequest,
                ),
                'labelFormat' => $strArgPickupLabelType,
                //'hazardousMaterial' => null, // LQ, EQ, BB, LB, GM
            ),
        );

        // Unset for mutual exclusive field.
        if ($strArgReceiverInstructions === null) {
            unset($arrParamRequest['parameters']['receiver']['instructions']);
        } else {
            unset($arrParamRequest['parameters']['receiver']['accessCode']);
            unset($arrParamRequest['parameters']['receiver']['floorNumber']);
            unset($arrParamRequest['parameters']['receiver']['buldingId']);
        }

        if ($fltArgPaybackAmount !== null
            && $fltArgPaybackAmount >= 0
            && $fltArgPaybackAmount <= 10000.0
        ) {
            $arrParamRequest['parameters']['paybackInfo'] = array(
                'useSenderAddress' => 1,
                'paybackAmount' => $fltArgPaybackAmount,
                //'name' => null,
                //'address1' => null,
                //'address2' => null,
                //'zipCode' => null,
                //'city' => null,
            );
        }

        // Pickup request is done only for occasional pickup type, and if no pickup request was already done.
        if ($boolIsPickupTypeOccasional && $boolArgSendPickupRequest) {
            $arrResultPickupContext = $this->getPickupContext(
                $strArgSenderZipCode,
                $strArgSenderCity,
                $strPickupDate
            );
            $arrParamRequest['parameters']['shippingDate'] = $arrResultPickupContext['pickupDate'];
            $arrParamRequest['parameters']['pickUpRequest'] = array(
                'media' => 'EMAIL',
                'emailAddress' => Tools::substr($strArgSenderEmail, 0, 80),
                'notifySuccess' => 1,
                'lastName' => $strArgSenderLastName,
                'firstName' => $strArgSenderFirstName,
                'phoneNumber' => $strArgSenderPhone,
                'closingTime' => $strArgPickupClosingTime,
            );
        }

        $objStdClassResponse = null;
        // If zipCode and CityName Valid for sender and receiver.
        if (!$arrResultCitiesGuideSender['boolIsRequestComError']
            && !$arrResultCitiesGuideReceiver['boolIsRequestComError']
            && $arrResultCitiesGuideSender['boolIsCityNameValid']
            && $arrResultCitiesGuideReceiver['boolIsCityNameValid']
        ) {
            $objStdClassResponse = $this->request('expeditionCreation', $arrParamRequest);
        }

        // Authentication error as Communication error.
        if ($objStdClassResponse === false) {
            $objStdClassResponse = null;
        }

        // Communication error.
        $boolIsRequestComError = false;
        if ($objStdClassResponse === null) {
            $boolIsRequestComError = true;
        }

        // Response error.
        $strResponseError = null;
        if ($objStdClassResponse instanceof Exception) {
            // Get error message.
            $strResponseError = trim(preg_replace('/[[:cntrl:]]+/', ' ', $objStdClassResponse->getMessage()));

            // The field 'accountNumber' is not valid. This account number is not registered.
            $strPatternErrorAccountNotRegistered = <<<'REGEXP'
/This account number is not registered\./ui
REGEXP;
/*
            // 'SOAP-ERROR: Encoding: Violation of encoding rules '[^']*' on line [0-9]+'
            $strPatternErrorEncodingViolation = <<<'REGEXP'
/SOAP-ERROR: Encoding: Violation of encoding rules '[^']*' on line [0-9]+/ui
REGEXP;
*/
            if (preg_match($strPatternErrorAccountNotRegistered, $strResponseError) === 1) {
                // Replace WS error message (BO JS identifier).
                $strResponseError = 'accountNotRegisteredStr';
            } else {
                // Log as error for detail.
                TNTOfficiel_Logger::logException($objStdClassResponse);
            }
        }


        $arrResult = array(
            'boolIsRequestComError' => $boolIsRequestComError,
            'strResponseMsgError' => $strResponseError,
            'PDFLabels' => null,
            'parcelResponses' => null,
            'pickupDate' => $arrParamRequest['parameters']['shippingDate'],
            'pickUpNumber' => null,
        );

        //
        if (is_object($objStdClassResponse)
            && get_class($objStdClassResponse) === 'stdClass'
            && property_exists($objStdClassResponse, 'Expedition')
        ) {
            if (is_object($objStdClassResponse->Expedition)
                && property_exists($objStdClassResponse->Expedition, 'parcelResponses')
                && property_exists($objStdClassResponse->Expedition, 'PDFLabels')
            ) {
                // Convert an parcelResponses item to array of one item.
                if (is_object($objStdClassResponse->Expedition->parcelResponses)
                    && property_exists($objStdClassResponse->Expedition->parcelResponses, 'parcelNumber')
                    && property_exists($objStdClassResponse->Expedition->parcelResponses, 'trackingURL')
                ) {
                    $objStdClassResponse->Expedition->parcelResponses = array(
                        (object)array(
                            'sequenceNumber' => $objStdClassResponse->Expedition->parcelResponses->sequenceNumber,
                            'parcelNumber' => $objStdClassResponse->Expedition->parcelResponses->parcelNumber,
                            'trackingURL' => $objStdClassResponse->Expedition->parcelResponses->trackingURL,
                            'stickerNumber' => $objStdClassResponse->Expedition->parcelResponses->stickerNumber,
                        ),
                    );
                }

                $arrResult['PDFLabels'] = $objStdClassResponse->Expedition->PDFLabels;
                $arrResult['parcelResponses'] = $objStdClassResponse->Expedition->parcelResponses;
                if (property_exists($objStdClassResponse->Expedition, 'pickUpNumber')) {
                    $arrResult['pickUpNumber'] = $objStdClassResponse->Expedition->pickUpNumber;
                }
            }
        }

        return $arrResult;
    }

    /**
     * Get a parcel tracking data.
     *
     * @param $strArgParcelNumber
     *
     * @return array
     */
    public function trackingByConsignment($strArgParcelNumber)
    {
        TNTOfficiel_Logstack::log();

        /*
         * Output default.
         */

        $arrResult = array(
            'boolIsRequestComError' => false,
            'strResponseMsgError' => null,
            'arrParcelTracking' => array(),
        );

        /*
         * Cache
         */

        // Set cache params.
        $arrParamCache = array(
            'parcelNumber' => $strArgParcelNumber,
        );
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParamCache);
        // 5 minutes.
        $intTTL = 5 * 60;
        $boolCacheStoreEnable = true;

        // Check if already in cache.
        if (TNTOfficielCache::isStored($strCacheKey)) {
            return TNTOfficielCache::retrieve($strCacheKey);
        }

        $arrParamRequest = array(
            'parcelNumber' => $strArgParcelNumber,
        );

        $objStdClassResponse = null;
        // If parcel number is a string.
        if (is_string($strArgParcelNumber) && Tools::strlen($strArgParcelNumber) > 0) {
            $objStdClassResponse = $this->request('trackingByConsignment', $arrParamRequest);
        }

        // Authentication error as Communication error.
        if ($objStdClassResponse === false) {
            $objStdClassResponse = null;
        }

        // Communication error.
        if ($objStdClassResponse === null) {
            $arrResult['boolIsRequestComError'] = true;
            // Disable Cache.
            $boolCacheStoreEnable = false;
        }

        // Response error.
        $strResponseError = null;
        if ($objStdClassResponse instanceof Exception) {
            // Get error message.
            $strResponseError = trim(preg_replace('/[[:cntrl:]]+/', ' ', $objStdClassResponse->getMessage()));
            // Set error in result.
            $arrResult['strResponseMsgError'] = $strResponseError;
            // Disable Cache.
            $boolCacheStoreEnable = false;
            // Log as error for detail.
            TNTOfficiel_Logger::logException($objStdClassResponse);
        }

        //
        if (is_object($objStdClassResponse)
            && get_class($objStdClassResponse) === 'stdClass'
            && property_exists($objStdClassResponse, 'Parcel')
        ) {
            // Convert an item to array of one item
            if (is_object($objStdClassResponse->Parcel)
                && property_exists($objStdClassResponse->Parcel, 'events')
            ) {
                $strShortStatus = null;
                if (property_exists($objStdClassResponse->Parcel, 'shortStatus')) {
                    $strShortStatus = $objStdClassResponse->Parcel->shortStatus;
                }
                $strLongStatus = null;
                if (property_exists($objStdClassResponse->Parcel, 'longStatus')) {
                    $strLongStatus = $objStdClassResponse->Parcel->longStatus;
                    if (is_array($strLongStatus)) {
                        $strLongStatus = implode(' ', $objStdClassResponse->Parcel->longStatus);
                    }
                    $strLongStatus = trim($strLongStatus);
                }
                $strPODUrl = null;
                if (property_exists($objStdClassResponse->Parcel, 'primaryPODUrl')
                    && isset($objStdClassResponse->Parcel->primaryPODUrl)
                ) {
                    $strPODUrl = $objStdClassResponse->Parcel->primaryPODUrl;
                } elseif (property_exists($objStdClassResponse->Parcel, 'secondaryPODUrl')
                    && isset($objStdClassResponse->Parcel->secondaryPODUrl)
                ) {
                    $strPODUrl = $objStdClassResponse->Parcel->secondaryPODUrl;
                }

                $arrResult['arrParcelTracking'] = array(
                    //'consignmentNumber' => $objStdClassResponse->Parcel->consignmentNumber,
                    //'accountNumber' => $objStdClassResponse->Parcel->accountNumber,
                    //'reference' => $objStdClassResponse->Parcel->reference,
                    // Struct
                    //'sender' => $objStdClassResponse->Parcel->sender,
                    // Struct
                    //'receiver' => $objStdClassResponse->Parcel->receiver,
                    // Struct
                    //'dropOffPoint' => $objStdClassResponse->Parcel->dropOffPoint,
                    //'serviceLabel' => $objStdClassResponse->Parcel->serviceLabel,
                    //'weight' => $objStdClassResponse->Parcel->weight,
                    // Struct
                    'events' => (array)$objStdClassResponse->Parcel->events,
                    'statusCode' => $objStdClassResponse->Parcel->statusCode,
                    'shortStatus' => $strShortStatus,
                    'longStatus' => $strLongStatus,
                    'proofDeliveryURL' => $strPODUrl,
                    //'hazardousMaterial' => $objStdClassResponse->Parcel->hazardousMaterial,
                );
            }
        }

        // If no Communication error and no Response error.
        if ($boolCacheStoreEnable) {
            // Cache.
            TNTOfficielCache::store($strCacheKey, $arrResult, $intTTL);
        }

        return $arrResult;
    }
}
