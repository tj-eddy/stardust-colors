<?php

require_once 'Chronofresh.php';

class webservicesHelper
{
    const CHRONOPOST_REVERSE_R = '4R'; // for Chronopost Reverse 9
    const CHRONOPOST_REVERSE_S = '4S'; // for Chronopost Reverse 10
    const CHRONOPOST_REVERSE_T = '4T'; // for Chronopost Reverse 13
    const CHRONOPOST_REVERSE_U = '4U'; // for Chronopost Reverse 18
    const CHRONOPOST_REVERSE_DEFAULT = '01'; // for Chronopost Reverse 18
    const CHRONOPOST_REVERSE_RELAIS_EUROPE = '3T'; // for Chronopost Reverse RelaisEurope
    const CHRONOPOST_REVERSE_TOSHOP = '5Y'; // for Chronopost Reverse TOSHOP
    const CHRONOPOST_REVERSE_TOSHOP_EUROPE = '6C'; // for Chronopost Reverse TOSHOP EUROPE

    const CHRONOPOST_REVERSE_R_SERVICE = '885'; // for Chronopost Reverse 9
    const CHRONOPOST_REVERSE_S_SERVICE = '180'; // for Chronopost Reverse 10
    const CHRONOPOST_REVERSE_T_SERVICE = '898'; // for Chronopost Reverse 13
    const CHRONOPOST_REVERSE_U_SERVICE = '835'; // for Chronopost Reverse 18
    const CHRONOPOST_REVERSE_DEFAULT_SERVICE = '226';  // for Chronopost Reverse 18
    const CHRONOPOST_REVERSE_RELAY_EUROPE_SERVICE = '332'; // for Chronopost Reverse RelaisEurope
    const CHRONOPOST_REVERSE_TOSHOP_SERVICE = '1'; // for Chronopost Reverse Toshop
    const CHRONOPOST_REVERSE_TOSHOP_EUROPE_SERVICE = '332'; // for Chronopost Reverse Toshop Europe

    const CHRONOPOST_EXPRESS = '17';

    protected $contractMethods = [];
    protected $methodsAllowed = [];
    protected static $method_contracts = [];

    /* Calcul codePruct and codeService for Reverse (return) */

    /**
     * @param Address $adress
     *
     * @return int|string
     */
    public function getReturnProductCode($adress)
    {
        $productCodes = $this->getMethods($adress);
        $productReturnCodes = $this->getReturnProductCodesAllowed($productCodes);
        sort($productReturnCodes, SORT_STRING);

        foreach ($this->getMatriceReturnCode() as $code => $combinaisonCodes) {
            if (in_array($productReturnCodes, $combinaisonCodes)) {
                return $code;
            }
        }

        return static::CHRONOPOST_REVERSE_DEFAULT;
    }

    protected function getReturnProductCodesAllowed($productCodes)
    {
        $possibleReturnProductCode = array(
            static::CHRONOPOST_REVERSE_R,
            static::CHRONOPOST_REVERSE_S,
            static::CHRONOPOST_REVERSE_T,
            static::CHRONOPOST_REVERSE_U,
            static::CHRONOPOST_REVERSE_RELAIS_EUROPE,
            static::CHRONOPOST_REVERSE_TOSHOP,
            static::CHRONOPOST_REVERSE_TOSHOP_EUROPE,
        );

        $returnProductCode = [];
        foreach ($productCodes as $code) {
            if (in_array($code, $possibleReturnProductCode)) {
                array_push($returnProductCode, $code);
            }

            if ($code === self::CHRONOPOST_EXPRESS) {
                $returnProductCode[] = static::CHRONOPOST_REVERSE_RELAIS_EUROPE;
            }
        }

        return (sizeof($returnProductCode) > 0) ? $returnProductCode : array(static::CHRONOPOST_REVERSE_DEFAULT);
    }

    /**
     * Get return service code
     * Commented sections are not supported yet
     *
     * @param string $code
     *
     * @return string
     */
    public function getReturnServiceCode($code)
    {
        switch ($code) {
            case static::CHRONOPOST_REVERSE_R:
                return static::CHRONOPOST_REVERSE_R_SERVICE;
            case static::CHRONOPOST_REVERSE_S:
                return static::CHRONOPOST_REVERSE_S_SERVICE;
            case static::CHRONOPOST_REVERSE_T:
                return static::CHRONOPOST_REVERSE_T_SERVICE;
            case static::CHRONOPOST_REVERSE_U:
                return static::CHRONOPOST_REVERSE_U_SERVICE;
            case static::CHRONOPOST_REVERSE_RELAIS_EUROPE:
                return static::CHRONOPOST_REVERSE_RELAY_EUROPE_SERVICE;
            case static::CHRONOPOST_REVERSE_TOSHOP:
                return static::CHRONOPOST_REVERSE_TOSHOP_SERVICE;
            case static::CHRONOPOST_REVERSE_TOSHOP_EUROPE:
                return static::CHRONOPOST_REVERSE_TOSHOP_EUROPE_SERVICE;
            default:
                return static::CHRONOPOST_REVERSE_DEFAULT_SERVICE;
        }
    }


    public function getReturnServiceLabel($code)
    {
        switch ($code) {
            case static::CHRONOPOST_REVERSE_R:
                return 'C9 REVERSE';
            case static::CHRONOPOST_REVERSE_S:
                return 'C10 REVERSE';
            case static::CHRONOPOST_REVERSE_T:
                return 'C13 REVERSE';
            case static::CHRONOPOST_REVERSE_U:
                return 'C18 REVERSE';
            case static::CHRONOPOST_REVERSE_RELAIS_EUROPE:
                return 'RELAIS EUROPE REVERSE';
            case static::CHRONOPOST_REVERSE_TOSHOP:
                return '2SHOP REVERSE';
            case static::CHRONOPOST_REVERSE_TOSHOP_EUROPE:
                return '2SHOP REVERSE EUROPE';
            case static::CHRONOPOST_REVERSE_DEFAULT:
                return 'C13 DEFAULT';
            default :
                return $code;
        }
    }

    public function getMatriceReturnCode()
    {
        return [
            static::CHRONOPOST_REVERSE_R             => [
                [static::CHRONOPOST_REVERSE_R],
                [static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_U]
            ],
            static::CHRONOPOST_REVERSE_S             => [
                [static::CHRONOPOST_REVERSE_S],
                [static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_S],
                [static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_U],
                [static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_U]
            ],
            static::CHRONOPOST_REVERSE_U             => [
                [static::CHRONOPOST_REVERSE_U]
            ],
            static::CHRONOPOST_REVERSE_RELAIS_EUROPE => [
                [static::CHRONOPOST_REVERSE_RELAIS_EUROPE]
            ],
            static::CHRONOPOST_REVERSE_T             => [
                [static::CHRONOPOST_REVERSE_T],
                [static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_T],
                [static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_T],
                [static::CHRONOPOST_REVERSE_T, static::CHRONOPOST_REVERSE_U],
                [static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_T],
                [static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_T, static::CHRONOPOST_REVERSE_U],
                [static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_T, static::CHRONOPOST_REVERSE_U],
                [
                    static::CHRONOPOST_REVERSE_R,
                    static::CHRONOPOST_REVERSE_S,
                    static::CHRONOPOST_REVERSE_T,
                    static::CHRONOPOST_REVERSE_U
                ]
            ],
            static::CHRONOPOST_REVERSE_TOSHOP        => [
                [static::CHRONOPOST_REVERSE_TOSHOP]
            ],
            static::CHRONOPOST_REVERSE_TOSHOP_EUROPE        => [
                [static::CHRONOPOST_REVERSE_TOSHOP_EUROPE]
            ],
            static::CHRONOPOST_REVERSE_DEFAULT       => [
                [static::CHRONOPOST_REVERSE_DEFAULT]
            ]
        ];
    }

    /**
     * @param string $contract Contract number
     *
     * @return array|bool
     */
    public function getMethodsForContract($contract)
    {
        if (!isset($this->contractMethods[$contract])) {
            $contract_infos = Chronopost::getAccountInformationByAccountNumber($contract);

            $default_address = new Address();
            $country = Country::getByIso($this->getConfigurationShipperInfo('country'));
            $default_address->city = $this->getConfigurationShipperInfo('city');
            $default_address->postcode = $this->getConfigurationShipperInfo('zipcode');
            $default_address->id_country = $country;

            $methodsAvailable = [];

            // Produits disponibles pour l'addresse configurée
            $methodsAvailable = array_merge($this->getMethods($default_address, $contract_infos), $methodsAvailable);

            // Produits disponibles en France Metropolitaine
            $this->methodsAllowed = false;
            $default_address->city = "Paris";
            $default_address->postcode = "75001";
            $default_address->id_country = Country::getByIso("FR");
            $methodsAvailable = array_merge($this->getMethods($default_address, $contract_infos), $methodsAvailable);

            // Produits disponibles pour les DOM
            $this->methodsAllowed = false;
            $default_address->city = "Saint-Denis";
            $default_address->postcode = "97400";
            $default_address->id_country = Country::getByIso("RE");
            $methodsAvailable = array_merge($this->getMethods($default_address, $contract_infos), $methodsAvailable);

            // Produits disponibles pour l'Europe
            $this->methodsAllowed = false;
            $default_address->city = "Berlin";
            $default_address->postcode = "10112";
            $default_address->id_country = Country::getByIso("DE");
            $this->contractMethods[$contract] = array_unique(array_merge(
                $this->getMethods($default_address, $contract_infos),
                $methodsAvailable
            ), SORT_REGULAR);

            // Produits disponibles pour l'Europe à partir de la Belgique
            // Certains produits sont disponibles pour la Belgique mais pas pour l'Allemagne
            $this->methodsAllowed = false;
            $default_address->city = "Bruxelles";
            $default_address->postcode = "1000";
            $default_address->id_country = Country::getByIso("BE");
            $this->contractMethods[$contract] = array_unique(array_merge(
                $this->getMethods($default_address, $contract_infos),
                $methodsAvailable
            ), SORT_REGULAR);
        }

        return $this->contractMethods[$contract];
    }

    /**
     * @param string $code Chronopost code
     *
     * @return array
     */
    protected function getContractsForMethod($code)
    {
        if (!isset(self::$method_contracts[$code])) {
            self::$method_contracts[$code] = [];

            $contracts = json_decode(Configuration::get('CHRONOPOST_GENERAL_ACCOUNTS'), 1);
            if (!is_array($contracts)) {
                return [];
            }

            foreach ($contracts as $contractInfos) {
                $methods = $this->getMethodsForContract($contractInfos['account']);
                if ($methods && in_array($code, $methods)) {
                    self::$method_contracts[$code][$contractInfos['account']] = $contractInfos;
                }
            }
        }

        return self::$method_contracts[$code];
    }

    public function getContractsForProduct($carrierDefinition)
    {
        $productContracts = [];

        if (!isset($carrierDefinition['products'])) {
            $carrierDefinition['products'] = array(array('code' => $carrierDefinition['product_code']));
        }

        foreach ($carrierDefinition['products'] as $product) {
            /** @noinspection AdditionOperationOnArraysInspection */
            $productContracts += $this->getContractsForMethod($product['code']);
        }

        return $productContracts;
    }

    public function getChronofreshCodes()
    {
        $freshProducts = Chronofresh::getProducts();
        $contract = json_decode(Configuration::get('CHRONOPOST_CHRONOFRESH_ACCOUNT'), 1);
        if (!$contract || $contract == -1) {
            return [];
        }

        $contract_infos = Chronopost::getAccountInformationByAccountNumber($contract);
        $methods = $this->getMethodsForContract($contract_infos['account']);
        $availFresh = array_intersect($freshProducts, $methods);
        if ($availFresh) {
            return $availFresh;
        }

        return [];
    }

    /**
     * @param Address $address
     *
     * @return array
     */
    public function getMethods($address, $account = null)
    {
        $hashKey = md5(json_encode([$address->postcode, $address->city, $address->id_country]) . json_encode($account));

        try {
            if ($account === null) {
                $account = Chronopost::getAccountInformationByAccountNumber(Tools::getValue('account'));
            }

            if (!$account) {
                throw new Exception("No account informations provided");
            }

            if (isset($this->methodsAllowed[$hashKey])) {
                return $this->methodsAllowed[$hashKey];
            }

            if (!isset($this->methodsAllowed[$hashKey])) {
                $this->methodsAllowed[$hashKey] = [];

                $country = new Country($address->id_country);
                $client = new SoapClient("https://www.chronopost.fr/quickcost-cxf/QuickcostServiceWS?wsdl",
                    array('trace' => 0, 'connection_timeout' => 10)
                );

                $params = array(
                    'accountNumber'  => $account['account'],
                    'password'       => $account['password'],
                    'depCountryCode' => $this->getConfigurationShipperInfo('country'),
                    'depZipCode'     => $this->getConfigurationShipperInfo('zipcode'),
                    'arrCountryCode' => $this->getFilledValue($country->iso_code),
                    'arrZipCode'     => $this->getFilledValue($address->postcode),
                    'arrCity'        => $address->city ? $this->getFilledValue($address->city) : '-',
                    'type'           => 'M',
                    'weight'         => 1
                );

                $webservbt = $client->calculateProducts($params);
                if ($webservbt->return->errorCode == 0 && $webservbt->return->productList) {
                    if (!is_array($webservbt->return->productList)) {
                        $webservbt->return->productList = array($webservbt->return->productList);
                    }

                    foreach ($webservbt->return->productList as $product) {
                        if (is_numeric($product->productCode)) {
                            $product->productCode = sprintf('%02d', $product->productCode);
                        }

                        $this->methodsAllowed[$hashKey][] = $product->productCode;
                    }
                }
            }

            return $this->methodsAllowed[$hashKey];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getConfigurationAccountNumber()
    {
        return Configuration::get('CHRONOPOST_GENERAL_ACCOUNT');
    }

    public function getConfigurationAccountPass()
    {
        return Configuration::get('CHRONOPOST_GENERAL_PASSWORD');
    }

    public function getConfigurationShipperInfo($param)
    {
        return Configuration::get('CHRONOPOST_SHIPPER_' . strtoupper($param));
    }

    public function getFilledValue($value)
    {
        if ($value) {
            return $this->removeaccents(trim($value));
        }

        return '';
    }

    public function removeaccents($string)
    {
        $stringToReturn = str_replace(
            array(
                'à',
                'á',
                'â',
                'ã',
                'ä',
                'ç',
                'è',
                'é',
                'ê',
                'ë',
                'ì',
                'í',
                'î',
                'ï',
                'ñ',
                'ò',
                'ó',
                'ô',
                'õ',
                'ö',
                'ù',
                'ú',
                'û',
                'ü',
                'ý',
                'ÿ',
                'À',
                'Á',
                'Â',
                'Ã',
                'Ä',
                'Ç',
                'È',
                'É',
                'Ê',
                'Ë',
                'Ì',
                'Í',
                'Î',
                'Ï',
                'Ñ',
                'Ò',
                'Ó',
                'Ô',
                'Õ',
                'Ö',
                'Ù',
                'Ú',
                'Û',
                'Ü',
                'Ý',
                '/',
                '\xa8'
            ), array(
            'a',
            'a',
            'a',
            'a',
            'a',
            'c',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'n',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'A',
            'A',
            'A',
            'A',
            'A',
            'C',
            'E',
            'E',
            'E',
            'E',
            'I',
            'I',
            'I',
            'I',
            'N',
            'O',
            'O',
            'O',
            'O',
            'O',
            'U',
            'U',
            'U',
            'U',
            'Y',
            ' ',
            'e'
        ), $string);

        // Remove all remaining other unknown characters
        $stringToReturn = preg_replace('/[^a-zA-Z0-9\-]/', ' ', $stringToReturn);
        $stringToReturn = preg_replace('/^[\-]+/', '', $stringToReturn);
        $stringToReturn = preg_replace('/[\-]+$/', '', $stringToReturn);

        return preg_replace('/[\-]{2,}/', ' ', $stringToReturn);
    }

    /***
     * @param string $contract
     * @return array
     */
    public function getAvailableReturnMethods($contract){
        //Default minimum Chrono 13 return
        $availableMethods = [self::CHRONOPOST_REVERSE_T];
        $methods = $this->getMethodsForContract($contract);
        $matrices = $this->getMatriceReturnCode();

        foreach ($methods as $method){
            if(isset($matrices[$method]) && $method != self::CHRONOPOST_REVERSE_T){
                $availableMethods[] = $method;
            }
        }

        return $availableMethods;
    }

    /**
     * @param array $params
     *
     * @return stdClass
     */
    public static function getReservedSkybill($service, $ltInfo, $params, $mode = null)
    {
        if ($ltInfo->isReturn) {
            $params->mode = $mode;
            if (is_null($params->mode)) {
                $params->mode = Configuration::get('CHRONOPOST_GENERAL_PRINTMODE');
                if (strpos($ltInfo->params->skybillParamsValue->mode, 'PPR') !== false) {
                    $params->mode = 'PPR';
                }
            }
        } else {
            $params->mode = $ltInfo->params->skybillParamsValue->mode;
        }

        return $service->getReservedSkybillWithTypeAndMode($params);
    }
}
