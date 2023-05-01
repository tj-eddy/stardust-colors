<?php
/**
 * 1961-2019 BNP Paribas
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1961-2019 BNP Paribas
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MercanetApi
{

    const CARDS_WITHOUT_TRI_TO_DISABLE = 'BCMC,iDeal,ELV';

    public static $CARDS_AUTHORIZED = array(
        'CB',
        'VISA',
        'MASTERCARD',
        'AMEX'
    );

    /**
     * Get Mercanet Default Parameters
     * @return array
     */
    public static function getDefaultParameters($is_nx = false)
    {
        // Init
        $default_parameters = array();
        $module = Context::getContext()->controller->module;
        $cart = Context::getContext()->cart;
        $customer = Context::getContext()->customer;
        $currency = self::getMercanetCurrency((int)$cart->id_currency);
        $mercanet = new Mercanet();
        // --- Params M --- //
        $default_parameters['amount'] = self::getTotalAmount() * 100;
        $default_parameters['currencyCode'] = $currency->iso_code_num;
        $default_parameters['returnContext'] = 'id_cart='.(int)$cart->id;
        $default_parameters['merchantId'] = Configuration::get('MERCANET_MERCHANT_ID');
        $default_parameters['normalReturnUrl'] = Tools::getHttpHost(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, __PS_BASE_URI__.'modules/mercanet/').'validation.php';
        $default_parameters['automaticResponseUrl'] = Tools::getHttpHost(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, __PS_BASE_URI__.'modules/mercanet/').'notification.php';
        $default_parameters['transactionReference'] = self::generateRandomReference((int)$cart->id);
        $default_parameters['keyVersion'] = Configuration::get('MERCANET_KEY_VERSION');
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true && (bool)Configuration::get('MERCANET_TEST_USER') == true) {
            $default_parameters['merchantId'] = Configuration::get('MERCANET_TEST_ACCOUNT');
            $default_parameters['keyVersion'] = Configuration::get('MERCANET_TEST_KEY_VERSION');
        }
        if ((bool)Configuration::getGlobalValue('MERCANET_LOG_ACCESS') == true) {
            $default_parameters['automaticErrorResponseInitPOST'] = Tools::getHttpHost(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, __PS_BASE_URI__.'modules/mercanet/').'access_error.php';
        }
        // --- Params O --- //
        // Order Reference
        // Generate the futur reference
        // refresh reference in case of first cancelled action
        $sql = 'DELETE
			FROM `'._DB_PREFIX_.'mercanet_order_reference` 
			WHERE `id_cart` ='.pSQL((int)$cart->id);
            Db::getInstance()->execute($sql);
        $reference = Order::generateReference();
        if (!empty($reference)) {
            $reference = MercanetOrderReference::addCartReference((int)$cart->id, $reference);
        }
        $default_parameters['orderId'] = $reference;

        // One click
        if ((bool)Configuration::get('MERCANET_ONE_CLICK_ACTIVE') == true && !$customer->isGuest()) {
            $default_parameters['merchantWalletId'] = MercanetWallet::getCustomerWalletId((int)$customer->id);
        }

        // Capture Mode
        if ($capture_mode = (string)Configuration::get('MERCANET_PAYMENT_VALIDATION_MODE')) {
            $default_parameters['captureMode'] = (string)$capture_mode;
        }

        // By pass receipt page
        if ((bool)Configuration::get('MERCANET_AUTOMATIC_REDIRECT_PAYMENT') == true) {
            $default_parameters['paypageData.bypassReceiptPage'] = 'Y';
        }

        // Notify the customer by Mercanet
        if ((bool)Configuration::get('MERCANET_NOTIFY_CUSTOMER') == true) {
            $default_parameters['customerContact.email'] = self::checkRegexpData('EMAIL', $customer->email, 128);
        }

        // Get country list for prevent fraud
        $country_list = Configuration::get('MERCANET_COUNTRIES_LIST');
        if ($country_list == 'ALL') {
            $default_parameters['fraudData.bypassCtrlList'][] = 'CardCountry';
        } elseif (!empty($country_list)) {
            $default_parameters['fraudData.allowedCardCountryList'] = $country_list;
        } else {
            $default_parameters['fraudData.allowedCardCountryList'] = 'FRA';
        }

        // Get available language from PS and Mercanet
        $langs = $module->getAvailableLanguages();
        $mercanet_lang_default = Configuration::get('MERCANET_DEFAULT_LANG');
        // If customer language found, sent it
        if (array_key_exists((int)$customer->id_lang, $langs)) {
            $default_parameters['customerLanguage'] = $langs[(int)$customer->id_lang]['iso_code'];
        } elseif (!empty($mercanet_lang_default) && array_key_exists((int)Configuration::get('MERCANET_DEFAULT_LANG'), $langs)) {
            $default_language = new Language((int)Configuration::get('MERCANET_DEFAULT_LANG'));
            $default_parameters['customerLanguage'] = $default_language->iso_code;
        }

        // Get allowed and restricted cards
        if (Configuration::get('MERCANET_CARD_ALLOWED') && Configuration::get('MERCANET_CARD_ALLOWED') != 'ALL') {
            $default_parameters['paymentMeanBrandList'] = Configuration::get('MERCANET_CARD_ALLOWED');
        }

        if ($is_nx) {
            if (Configuration::get('MERCANET_CARD_ALLOWED') && Configuration::get('MERCANET_CARD_ALLOWED') != 'ALL') {
                $array_card = explode(',', Configuration::get('MERCANET_CARD_ALLOWED'));
                $authorized_card = array(
                    'CB',
                    'VISA',
                    'MASTERCARD',
                    'AMEX');
                $first = true;
                foreach ($array_card as $card) {
                    if (in_array($card, $authorized_card)) {
                        if ($first) {
                            $default_parameters['paymentMeanBrandList'] = $card;
                            $first = false;
                        } else {
                            $default_parameters['paymentMeanBrandList'] .= ','.$card;
                        }
                    }
                }
            } else {
                $default_parameters['paymentMeanBrandList'] = 'CB,VISA,MASTERCARD,AMEX';
            }
        }

        // preparation parameters Presto
        if ($mercanet->isFeatureActivated('PRE') && !$is_nx) {
            $address_billing = new Address((int)$cart->id_address_invoice);
            $country_billing = new Country((int)$address_billing->id_country);
            $address_shipping = new Address((int)$cart->id_address_delivery);
            $country_shipping = new Country((int)$address_shipping->id_country);
            $default_parameters['captureMode'] = 'IMMEDIATE';
            $default_parameters['captureDay'] = 0;
            $default_parameters['paymentPattern'] = 'ONE_SHOT';
            $default_parameters['paymentMeanData.presto.paymentMeanCustomerId'] = $customer->id;
            if (Configuration::get('MERCANET_PRE_ACTIVE') == true) {
                $presto_amount = self::getConvertedAmount(self::getTotalAmount(), $currency, new Currency((int)Currency::getIdByNumericIsoCode((int)Configuration::get('MERCANET_EURO_ISO_CODE_NUM'))));
                if (($presto_amount >= 1500.01) || ($presto_amount < 150.00)) {
                    $default_parameters['paymentMeanData.presto.financialProduct'] = 'CLA';
                } else {
                    $default_parameters['paymentMeanData.presto.financialProduct'] = 'CCH';
                    $default_parameters['paymentMeanData.presto.prestoCardType'] = 'A';
                }
            } else {
                $default_parameters['paymentMeanData.presto.financialProduct'] = 'CLA';
            }
            $default_parameters['shoppingCartDetail.mainProduct'] = '320';
            $default_parameters['customerContact.firstname'] = self::checkRegexpData('A', $customer->firstname, 50);
            $default_parameters['customerContact.lastname'] = self::checkRegexpData('A', $customer->lastname, 50);
            if ($address_billing->phone) {
                $default_parameters['customerContact.phone'] = self::checkRegexpData('PHONE', $address_billing->phone);
            }
            if ($address_billing->phone_mobile) {
                $default_parameters['customerContact.mobile'] = self::checkRegexpData('PHONE', $address_billing->phone_mobile);
            }
            $default_parameters['customerAddress.addressAdditional1'] = self::checkRegexpData('ANU-R', $address_billing->address1, 50);
            if ($address_billing->address2) {
                $default_parameters['customerAddress.addressAdditional2'] = self::checkRegexpData('ANU-R', $address_billing->address2, 50);
            }
            $default_parameters['customerAddress.zipCode'] = self::checkRegexpData('AN-R', $address_billing->postcode, 10);
            $default_parameters['customerAddress.city'] = self::checkRegexpData('ANU-R', $address_billing->city, 50);
        }

        // preparation parameters fullCB
        if ($mercanet->isFeatureActivated('FCB') && !$is_nx) {
            $iso_convert = $module->getAvailableCountries();
            $address_billing = new Address((int)$cart->id_address_invoice);
            $country_billing = new Country((int)$address_billing->id_country);
            $address_shipping = new Address((int)$cart->id_address_delivery);
            $country_shipping = new Country((int)$address_shipping->id_country);
            $default_parameters['captureMode'] = 'IMMEDIATE';
            $default_parameters['captureDay'] = 0;
            $default_parameters['paymentPattern'] = 'ONE_SHOT';
            $gender = 'M';
            if ($customer->id_gender != 1) {
                $gender = 'Mme';
            }
            $default_parameters['holderContact.title'] = $gender;
            $default_parameters['holderContact.firstname'] = self::checkRegexpData('A', $customer->firstname, 50);
            $default_parameters['holderContact.lastname'] = self::checkRegexpData('A', $customer->lastname, 50);
            if ($address_billing->phone) {
                $default_parameters['holderContact.phone'] = self::checkRegexpData('PHONE', $address_billing->phone);
            }
            if ($address_billing->phone_mobile) {
                $default_parameters['holderContact.mobile'] = self::checkRegexpData('PHONE', $address_billing->phone_mobile);
            }
            $default_parameters['holderContact.email'] = self::checkRegexpData('EMAIL', $customer->email, 128);

            $default_parameters['billingContact.firstname'] = self::checkRegexpData('A', $address_billing->firstname, 50);
            $default_parameters['billingContact.lastname'] = self::checkRegexpData('A', $address_billing->lastname, 50);
            $default_parameters['billingAddress.street'] = self::checkRegexpData('ANU-R', $address_billing->address1, 50);
            if ($address_billing->address2) {
                $default_parameters['billingAddress.addressAdditional1'] = self::checkRegexpData('ANU-R', $address_billing->address2, 50);
            }
            $default_parameters['billingAddress.zipCode'] = self::checkRegexpData('AN-R', $address_billing->postcode, 10);
            $default_parameters['billingAddress.city'] = self::checkRegexpData('ANU-R', $address_billing->city, 50);
            $iso_code = 'FRA';
            if (key_exists($country_billing->iso_code, $iso_convert)) {
                $iso_code = $iso_convert[$country_billing->iso_code]['id'];
            }
            $default_parameters['billingAddress.country'] = $iso_code;

            $default_parameters['deliveryContact.firstname'] = self::checkRegexpData('A', $address_shipping->firstname, 50);
            $default_parameters['deliveryContact.lastname'] = self::checkRegexpData('A', $address_shipping->lastname, 50);
            $default_parameters['deliveryAddress.street'] = self::checkRegexpData('ANU-R', $address_shipping->address1, 50);
            if ($address_shipping->address2) {
                $default_parameters['deliveryAddress.addressAdditional1'] = self::checkRegexpData('ANU-R', $address_shipping->address2, 50);
            }
            $default_parameters['deliveryAddress.zipCode'] = self::checkRegexpData('AN-R', $address_shipping->postcode, 10);
            $default_parameters['deliveryAddress.city'] = self::checkRegexpData('ANU-R', $address_shipping->city, 50);
            $iso_code = 'FRA';
            if (key_exists($country_shipping->iso_code, $iso_convert)) {
                $iso_code = $iso_convert[$country_shipping->iso_code]['id'];
            }
            $default_parameters['deliveryAddress.country'] = $iso_code;
        }

        // Get the Css name
        if (Configuration::get('MERCANET_CSS_THEME_CONFIG')) {
            $default_parameters['templateName'] = Configuration::get('MERCANET_CSS_THEME_CONFIG');
        }

        // Bank Deposit
        if (Configuration::get('MERCANET_BANK_DEPOSIT_NB_DAYS') && Configuration::get('MERCANET_BANK_DEPOSIT_NB_DAYS') > 0) {
            $default_parameters['captureDay'] = (int)Configuration::get('MERCANET_BANK_DEPOSIT_NB_DAYS');
        }

        // Anti Fraud fields
        // 3DS - If the amount is inferior of the minimum amount, by pass 3DS
        if ((bool)Configuration::get('MERCANET_3DS_ACTIVE') == true && $mercanet->isFeatureActivated('3DS')) {
            if ((float)Configuration::get('MERCANET_3DS_MIN_AMOUNT') > 0) {
                $euro_amount = self::getConvertedAmount(self::getTotalAmount(), $currency, new Currency((int)Currency::getIdByNumericIsoCode((int)Configuration::get('MERCANET_EURO_ISO_CODE_NUM'))));
                if ($euro_amount < (float)Configuration::get('MERCANET_3DS_MIN_AMOUNT')) {
                    $default_parameters['fraudData.bypass3DS'] = 'ALL';
                }
            }
        }

        // Country host card
        if (!Configuration::get('MERCANET_PEC_ACTIVE') && $mercanet->isFeatureActivated('PEC')) {
            $default_parameters['fraudData.bypassCtrlList'][] = 'ForeignBinCard';
        }

        // Country address ip
        if (!Configuration::get('MERCANET_PIP_ACTIVE') && $mercanet->isFeatureActivated('PIP')) {
            $default_parameters['fraudData.bypassCtrlList'][] = 'IpCountry';
        }

        //  Simility Ip Card
        if (!Configuration::get('MERCANET_SCP_ACTIVE') && $mercanet->isFeatureActivated('SCP')) {
            $default_parameters['fraudData.bypassCtrlList'][] = 'SimilityIpCard';
        }

        // Authentification 3DS
        if (!Configuration::get('MERCANET_A3D_ACTIVE') && $mercanet->isFeatureActivated('A3D')) {
            $default_parameters['fraudData.bypassCtrlList'][] = '3DSStatus';
        }

        //  Commercial Card and Country
        if (!Configuration::get('MERCANET_CCO_ACTIVE') && $mercanet->isFeatureActivated('CCO')) {
            $default_parameters['fraudData.bypassCtrlList'][] = 'CorporateCard';
        }

        // Ecard
        if (!Configuration::get('MERCANET_CVI_ACTIVE') && $mercanet->isFeatureActivated('CVI')) {
            $default_parameters['fraudData.bypassCtrlList'][] = 'ECard';
        }

        // Black list card
        if (!Configuration::get('MERCANET_LNC_ACTIVE') && $mercanet->isFeatureActivated('LNC')) {
            $default_parameters['fraudData.bypassCtrlList'][] = 'BlackCard';
        }

        // Transaction amount
        if (!Configuration::get('MERCANET_AMT_ACTIVE') && $mercanet->isFeatureActivated('AMT')) {
            $default_parameters['fraudData.bypassCtrlList'][] = 'CapCollarAmount';
        }

        // Current card
        if (!Configuration::get('MERCANET_ECC_ACTIVE') && $mercanet->isFeatureActivated('ECC')) {
            $default_parameters['fraudData.bypassCtrlList'][] = 'VelocityCard';
        }

        // Current ip
        if (!Configuration::get('MERCANET_ECI_ACTIVE') && $mercanet->isFeatureActivated('ECI')) {
            $default_parameters['fraudData.bypassCtrlList'][] = 'VelocityIp';
        }

        if (isset($default_parameters['fraudData.bypassCtrlList']) && !empty($default_parameters['fraudData.bypassCtrlList'])) {
            $default_parameters['fraudData.bypassCtrlList'] = implode(',', $default_parameters['fraudData.bypassCtrlList']);
        }

        ksort($default_parameters);
        
        return $default_parameters;
    }

    /**
     * Add the nx payment parameters
     */
    public static function getNxParameters($params, $id_mercanet_nx_payment)
    {
        if (empty($id_mercanet_nx_payment) && (int)$id_mercanet_nx_payment == 0) {
            return $params;
        }

        $nx_payment = new MercanetNxPayment((int)$id_mercanet_nx_payment);

        if (empty($nx_payment->id)) {
            return $params;
        }
        $amount = (float)$params['amount'] / 100;
        $number = (int)$nx_payment->number;

        // Amounts & Date & References
        $amounts = array();
        $datetime = new DateTime();
        $references = array();

        // Different calcul if there is no first payment
        if ((float)$nx_payment->first_payment == 0) {
            $split_amount = Tools::ps_round(($amount / $number), 2);
            $last_amount = $amount - ($split_amount * ($number - 1));

            for ($nb = 1; $nb <= $number; $nb++) {
                // References
                if ($nb == 1) {
                    $references[$nb] = $params['transactionReference'];
                } else {
                    $references[$nb] = $nb.'schedule'.$params['transactionReference'];
                }

                // Assign amount
                $amounts[$datetime->format('Ymd')] = ($nb == $number) ? $last_amount * 100 : $split_amount * 100;
                // Add periodicity for next schedule
                $datetime->add(new DateInterval('P'.(int)$nx_payment->periodicity.'D'));
            }
        } else {
            // First amount by pourcent of the total amount
            $first_amount = Tools::ps_round($amount * ($nx_payment->first_payment / 100), 2);

            // Remaining amount
            $remaining_amount = ($amount - $first_amount);

            // Split amount to add
            $split_amount = Tools::ps_round($remaining_amount / ($number - 1), 2, PS_ROUND_HALF_DOWN);

            // Last amount to add
            $last_amount = $split_amount + Tools::ps_round($remaining_amount - ($split_amount * ($number - 1)), 2);

            for ($nb = 1; $nb <= $number; $nb++) {
                if ($nb == 1) {
                    $amounts[$datetime->format('Ymd')] = $first_amount * 100;
                    $references[$nb] = $params['transactionReference'];
                } else {
                    $references[$nb] = $nb.'schedule'.$params['transactionReference'];
                    $amounts[$datetime->format('Ymd')] = ($nb == $number) ? $last_amount * 100 : $split_amount * 100;
                }
                // Add periodicity for next schedule
                $datetime->add(new DateInterval('P'.(int)$nx_payment->periodicity.'D'));
            }
        }

        // Assign Nx params
        $params['returnContext'] .= ',id_nx_payment='.(int)$nx_payment->id_mercanet_nx_payment;
        $params['instalmentData.amountsList'] = implode(',', $amounts);
        $params['instalmentData.number'] = (int)$number;
        $params['instalmentData.datesList'] = implode(',', array_keys($amounts));
        $params['instalmentData.transactionReferencesList'] = implode(',', $references);
        $params['paymentPattern'] = 'INSTALMENT';

        ksort($params);
        return $params;
    }

    /**
     * Return the params to recurring payment
     * @var $params array
     */
    public static function getRecurringParameters($params)
    {
        $params['returnContext'] .= ',is_recurring=true';

        return $params;
    }

    /**
     * Wallet Params
     * @return array
     */
    public static function getWalletData($id_customer = null)
    {
        if (empty($id_customer)) {
            $id_customer = Context::getContext()->customer->id;
        }
        $link = new Link();
        $data = array();
        $data['merchantId'] = Configuration::get('MERCANET_MERCHANT_ID');
        $data['normalReturnUrl'] = $link->getModuleLink('mercanet', 'wallet', array(), true);
        $data['requestDateTime'] = gmdate("Y-m-d", time())."T".gmdate("H:i:s", time())."+00:00";
        $data['merchantWalletId'] = MercanetWallet::getCustomerWalletId((int)$id_customer);
        $data['keyVersion'] = Configuration::get('MERCANET_KEY_VERSION');
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true && (bool)Configuration::get('MERCANET_TEST_USER') == true) {
            $data['merchantId'] = Configuration::get('MERCANET_TEST_ACCOUNT');
            $data['keyVersion'] = Configuration::get('MERCANET_TEST_KEY_VERSION');
        }
        ksort($data);
        return $data;
    }

    /**
     * Add the direct card parameters
     */
    public static function getDirectCardParameters($params, $card)
    {
        if (empty($card) || $card == 'ALL') {
            return $params;
        }

        if ($card != Configuration::get('MERCANET_NXCB_NAME')) {
            if (isset($params['paymentMeanData.cetelemNxcb.nxcbTransactionReference1'])) {
                unset($params['paymentMeanData.cetelemNxcb.nxcbTransactionReference1']);
            }

            if (isset($params['paymentMeanData.cetelemNxcb.nxcbTransactionReference2'])) {
                unset($params['paymentMeanData.cetelemNxcb.nxcbTransactionReference2']);
            }
        }

        $params['paymentMeanBrandList'] = $card;
        ksort($params);
        return $params;
    }

    /**
     * Create and return the SEAL
     * @param array $params
     * @param bool $webservice
     * @return string
     */
    public static function buildSeal(array $params, $webservice = false)
    {
        ksort($params);

        if ($webservice == true) {
            if (isset($params['keyVersion'])) {
                unset($params['keyVersion']);
            }
        }

        $raw_data = self::getRawData($params, $webservice);
        $secret_key = Configuration::get('MERCANET_SECRET_KEY');
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true && (bool)Configuration::get('MERCANET_TEST_USER') == true) {
            $secret_key = Configuration::get('MERCANET_TEST_KEY_SECRET');
        }

        if (empty($secret_key)) {
            return false;
        }
        if ($webservice == true) {
            $seal = hash_hmac('sha256', utf8_encode($raw_data), $secret_key);
        } else {
            $seal = hash('sha256', utf8_encode($raw_data.$secret_key));
        }

        return $seal;
    }

    /**
     *
     * @param string $data
     * @param string $seal_received
     * @return boolean
     */
    public static function verifySeal($data, $seal_received, $webservice = false)
    {
        $secret_key = Configuration::get('MERCANET_SECRET_KEY');
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true && (bool)Configuration::get('MERCANET_TEST_USER') == true) {
            $secret_key = Configuration::get('MERCANET_TEST_KEY_SECRET');
        }
        if (empty($secret_key)) {
            return false;
        }

        if ($webservice == true) {
            $data_array = (array)$data;
            if ($data_array['seal']) {
                unset($data_array['seal']);
            }
            ksort($data_array);
            $data = implode('', $data_array);
            $seal = hash_hmac('sha256', utf8_encode($data), $secret_key);
        } else {
            $seal = hash('sha256', utf8_encode($data.$secret_key));
        }
        if ($seal == $seal_received) {
            return true;
        }

        return false;
    }

    /**
     * Return the data raw for Mercanet
     * @param array $params
     * @return string
     */
    public static function getRawData(array $params, $webservice = false)
    {
        if ($webservice == true) {
            return implode('', $params);
        }

        return base64_encode(
            implode(
                '|',
                array_map(
                    function ($v, $k) {
                        return $k.'='.$v;
                    },
                    $params,
                    array_keys($params)
                )
            )
        );
    }

    /**
     * Get Cards configured
     * @return array
     */
    public static function getCards()
    {
        $mercanet_card_allowed = Configuration::get('MERCANET_CARD_ALLOWED');
        if (!empty($mercanet_card_allowed)) {
            if (Configuration::get('MERCANET_CARD_ALLOWED') == 'ALL') {
                $mercanet = new Mercanet();
                $cards = $mercanet->getAvailableCards();
                $cards = array_column($cards, 'id');
                return $cards;
            }

            return explode(',', Configuration::get('MERCANET_CARD_ALLOWED'));
        }
        return false;
    }

    /**
     * Get Cards configured but delete specific card without trigramme
     * @return array
     */
    public static function getCardsWithTrigramme()
    {
        $mercanet = new Mercanet();
        $cards = $mercanet->getAvailableCards();
        $cards_to_disable = explode(',', MercanetApi::CARDS_WITHOUT_TRI_TO_DISABLE);
        foreach ($cards_to_disable as $card) {
            if (isset($cards[$card])) {
                unset($cards[$card]);
            }
        }
        $cards = array_column($cards, 'id');
        return $cards;
    }

    /**
     * Return an array of the raw data
     * @param type $raw_data
     * @return boolean
     */
    public static function getDataFromRawData($raw_data, $delimiter = '|', $assign_delimiter = '=')
    {
        // Return false if no data
        if (empty($raw_data)) {
            return false;
        }

        // Init
        $data = array();

        // Explode and list the result to construct the array
        foreach (explode($delimiter, $raw_data) as $r) {
            $explode = explode($assign_delimiter, $r, 2);
            if (!empty($explode[0])) {
                list($key, $value) = $explode;
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * * Get an order by its cart id
     * @param integer $id_cart
     * @param integer $id_shop
     * @return integer
     */
    public static function getOrderByCartId($id_cart, $id_shop)
    {
        $sql = 'SELECT `id_order`
				FROM `'._DB_PREFIX_.'orders`
				WHERE `id_cart` = '.pSQL((int)$id_cart).'
				AND `id_shop` = '.pSQL((int)$id_shop);

        $result = Db::getInstance()->getRow($sql);

        return isset($result['id_order']) ? $result['id_order'] : false;
    }

    /**
     * Return the Total Amount
     * @param Cart $cart
     * @return Float
     */
    public static function getTotalAmount(Cart $cart = null)
    {
        if (empty($cart)) {
            $cart = Context::getContext()->cart;
        }

        // Currencies Objects
        $currency = new Currency((int)$cart->id_currency);
        $mercanet_currency = self::getMercanetCurrency((int)$currency->id);
        // If currencies are different, we change the context of the currency in PS
        if (!empty($mercanet_currency) && $currency->id != $mercanet_currency->id) {
            Context::getContext()->cart->id_currency = $mercanet_currency->id;
            Context::getContext()->currency = $mercanet_currency;
            $amount = $cart->getOrderTotal();
        } else {
            $amount = $cart->getOrderTotal();
        }

        return $amount;
    }

    /**
     * Convert amount
     * @param float $price
     * @param Currency $from_currency
     * @param Currency $to_currency
     * @return float
     */
    public static function getConvertedAmount($price, Currency $from_currency, Currency $to_currency)
    {
        if ((int)$from_currency->id == (int)$to_currency->id) {
            return $price;
        }

        if ($to_currency->id == (int)Configuration::get('PS_CURRENCY_DEFAULT')) {
            return Tools::ps_round(Tools::convertPrice($price, $from_currency->id, false), 2);
        } else {
            return Tools::ps_round(Tools::convertPrice($price, $to_currency->id, true), 2);
        }
    }

    /**
     * Return the currency allowed by Mercanet and customer account
     * @param int $id_currency
     * @return \Currency|boolean
     */
    public static function getMercanetCurrency($id_currency)
    {
        if (empty($id_currency)) {
            return false;
        }
        $currency = new Currency((int)$id_currency);

        $currencies_list = Configuration::get('MERCANET_CURRENCIES_LIST');

        if (empty($currencies_list)) {
            $mercanet_euro_iso_code_num = Configuration::get('MERCANET_EURO_ISO_CODE_NUM');
            $currencies_list = (!empty($mercanet_euro_iso_code_num)) ? Configuration::get('MERCANET_EURO_ISO_CODE_NUM') : 978;
        }
        $currencies = explode(',', $currencies_list);

        // if the currency is not in the currencies allowed, we return the EURO currency
        if (in_array($currency->iso_code_num, $currencies)) {
            return $currency;
        } else {
            $currency = new Currency(Currency::getIdByNumericIsoCode((is_array($currencies)) ? $currencies[0] : (int)$currencies_list));
            if (empty($currency->id)) {
                $mercanet_euro_iso_code_num = Configuration::get('MERCANET_EURO_ISO_CODE_NUM');
                return new Currency(Currency::getIdByNumericIsoCode((int)(!empty($mercanet_euro_iso_code_num)) ? Configuration::get('MERCANET_EURO_ISO_CODE_NUM') : 978));
            } else {
                return $currency;
            }
        }
    }

    /**
     * Generate random string
     */
    public static function generateRandomReference($id = null, $length = 21)
    {
        if (empty($id)) {
            $id = 0;
        }
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = Tools::strlen($characters);
        $id_length = Tools::strlen($id);

        $random_string = $id;
        for ($i = 0; $i < ($length - $id_length); $i++) {
            $random_string .= $characters[rand(0, $characters_length - 1)];
        }
        return $random_string;
    }

    /**
     * Retire les caractères non autorisé pour chaque type de champs
     * @param string $type
     * @param string $string
     * @return type
     */
    public static function checkRegexpData($type, $string, $length = 0)
    {
        $return_str = str_replace(array(
            "'",
            "’"), "'", $string);
        switch ($type) {
            case 'PHONE':
                $first_occurence = (Tools::substr($return_str, 0, 1) === "+") ? "+" : "";
                $return_str = Tools::substr($first_occurence.preg_replace("/[^0-9]/", "", $return_str), 0, 30);
                break;
            case 'EMAIL':
                if (!filter_var($string, FILTER_VALIDATE_EMAIL)) {
                    $return_str = '';
                }
                break;
            //Indique que les valeurs alphabétiques [aA-zZ] sont acceptées
            case 'A':
                $return_str = preg_replace("/[^a-zA-Z]/", "", $return_str);
                break;
            //Tout caractère est accepté
            case 'ANS':
                break;
            //------------------------------------------------------------------
            //-----------------------RestrictedString---------------------------
            //Indique que seules certaines valeurs alphabétiques [aA-zZ] sont acceptées
            case 'A-R':
                $return_str = preg_replace("/[^a-zA-Z]/", "", $return_str);
                break;
            //Les caractères suivants sont acceptés :
            //  - Alphabétique [aA-zZ]
            //  - Numerique [0-9]
            //  - Spécial _ . + - @
            //  - espace,
            case 'AN-R':
                $return_str = preg_replace("/[^0-9a-zA-Z_+.\-@, ]/", "", $return_str);
                break;
            // RestrictedString
            //Les caractères suivants sont acceptés :
            //  - alphabétique [aA-zZ]
            //  - numerique [0-9]
            //  - spécial " ' ` _ + . - @ ,
            //  - espace
            //  - tout caractère linguistique de toute langue (à â ç é è ê ë î Ï ô ù ...)
            case 'ANU-R':
                $return_str = preg_replace("/[^0-9a-zA-Z\"'_+.\-@,ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝáàâäãåçéèêëíìîïñóòôöõúùûüýÿ ]/", "", $return_str);
                break;
            //------------------------------------------------------------------
        }
        //Cas téléphone spécifique, au cas où ce soit du +33... par exemple
        if ($length != 0 && $type != 'PHONE') {
            $return_str = mb_substr($return_str, 0, $length);
        }
        return $return_str;
    }
}
