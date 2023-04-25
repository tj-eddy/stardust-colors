<?php

class ChronopostLT
{

    /**
     * @return array
     */
    public function checkAccount($accountNumber, $errors)
    {
        $account = Chronopost::getAccountInformationByAccountNumber($accountNumber);
        if (Tools::strlen($account['account']) < 8) {
            $errors[] = 'Erreur : veuillez configurer le module avant de procéder à l\'édition des étiquettes.';
        }

        $service = new QuickcostServiceWSService();
        $quick = new quickCost();
        $quick->accountNumber = $account['account'];
        $quick->password = $account['password'];
        $quick->depCode = '92500';
        $quick->arrCode = '75001';
        $quick->weight = '1';
        $quick->productCode = '1';
        $quick->type = 'D';

        $result = $service->quickCost($quick);

        $loginValid = true;
        if ($result->return->errorCode === 3) {
            $loginValid = false;
        }

        if (!$loginValid) {
            $errors[] = 'Erreur : le contrat Chronopost utilisé n\'est pas valide.';
        }

        return $errors;
    }

    /**
     * Get recipient
     *
     * @param $a
     * @param $cust
     * @param $isReturn
     *
     * @return recipientValue
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getRecipient($a, $cust, $isReturn)
    {
        $recipient = new recipientValue();
        $recipient->recipientAdress1 = Tools::substr($a->address1, 0, 35);
        $recipient->recipientAdress2 = Tools::substr($a->address2, 0, 35);
        $recipient->recipientCity = Tools::substr($a->city, 0, 30);
        $recipient->recipientCivility = 'M';
        $recipient->recipientContactName = Tools::substr($a->firstname . ' ' . $a->lastname, 0, 35);
        $c = new Country($a->id_country);
        $recipient->recipientCountry = Chronopost::maybeCountryMapping($c->iso_code);
        $recipient->recipientName = Tools::substr($a->company, 0, 35);
        $recipient->recipientName2 = Tools::substr($a->firstname . ' ' . $a->lastname, 0, 35);
        $recipient->recipientZipCode = $a->postcode;
        $recipient->recipientPhone = $a->phone_mobile == null ? $a->phone : $a->phone_mobile;
        $recipient->recipientMobilePhone = $a->phone_mobile;
        $recipient->recipientEmail = $cust->email;

        if ($isReturn) {
            if (Tools::getValue('return_address') == chronopost::$RETURN_ADDRESS_RETURN) {
                $addressKey = 'RETURN';
            } elseif (Tools::getValue('return_address') == chronopost::$RETURN_ADDRESS_INVOICE) {
                $addressKey = 'CUSTOMER';
            } elseif (Tools::getValue('return_address') == chronopost::$RETURN_ADDRESS_SHIPPING) {
                $addressKey = 'SHIPPER';
            }

            $recipient->recipientAdress1 = Configuration::get('CHRONOPOST_' . $addressKey . '_ADDRESS');
            $recipient->recipientAdress2 = Configuration::get('CHRONOPOST_' . $addressKey . '_ADDRESS2');
            $recipient->recipientCity = Configuration::get('CHRONOPOST_' . $addressKey . '_CITY');
            $recipient->recipientCivility = Configuration::get('CHRONOPOST_' . $addressKey . '_CIVILITY');
            $recipient->recipientContactName = Configuration::get('CHRONOPOST_' . $addressKey . '_CONTACTNAME');
            $recipient->recipientCountry = Chronopost::maybeCountryMapping(Configuration::get('CHRONOPOST_' . $addressKey . '_COUNTRY'));
            $recipient->recipientName = Configuration::get('CHRONOPOST_' . $addressKey . '_NAME');
            $recipient->recipientName2 = Configuration::get('CHRONOPOST_' . $addressKey . '_NAME2');
            $recipient->recipientZipCode = Configuration::get('CHRONOPOST_' . $addressKey . '_ZIPCODE');
            $recipient->recipientPhone = null;
            $recipient->recipientMobilePhone = null;
            $recipient->recipientEmail = null;
        }

        return $recipient;
    }

    /**
     * Get customer
     *
     * @return customerValue
     */
    private function getCustomer()
    {
        $customer = new customerValue();
        $customer->customerAdress1 = Configuration::get('CHRONOPOST_CUSTOMER_ADDRESS');
        $customer->customerAdress2 = Configuration::get('CHRONOPOST_CUSTOMER_ADDRESS2');
        $customer->customerCity = Configuration::get('CHRONOPOST_CUSTOMER_CITY');
        $customer->customerCivility = Configuration::get('CHRONOPOST_CUSTOMER_CIVILITY');
        $customer->customerContactName = Configuration::get('CHRONOPOST_CUSTOMER_CONTACTNAME');
        $customer->customerCountry = Chronopost::maybeCountryMapping(Configuration::get('CHRONOPOST_CUSTOMER_COUNTRY'));
        $customer->customerName = Configuration::get('CHRONOPOST_CUSTOMER_NAME');
        $customer->customerName2 = Configuration::get('CHRONOPOST_CUSTOMER_NAME2');
        $customer->customerZipCode = Configuration::get('CHRONOPOST_CUSTOMER_ZIPCODE');

        return $customer;
    }

    /**
     * Get shipper
     *
     * @param $a
     * @param $cust
     * @param $isReturn
     *
     * @return shipperValue
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getShipper($a, $cust, $isReturn)
    {
        $shipper = new shipperValue();
        $shipper->shipperAdress1 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS');
        $shipper->shipperAdress2 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS2');
        $shipper->shipperCity = Configuration::get('CHRONOPOST_SHIPPER_CITY');
        $shipper->shipperCivility = Configuration::get('CHRONOPOST_SHIPPER_CIVILITY');
        $shipper->shipperContactName = Configuration::get('CHRONOPOST_SHIPPER_CONTACTNAME');
        $shipper->shipperCountry = Chronopost::maybeCountryMapping(Configuration::get('CHRONOPOST_SHIPPER_COUNTRY'));
        $shipper->shipperName = Configuration::get('CHRONOPOST_SHIPPER_NAME');
        $shipper->shipperName2 = Configuration::get('CHRONOPOST_SHIPPER_NAME2');
        $shipper->shipperZipCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');
        $shipper->shipperPhone = Configuration::get('CHRONOPOST_SHIPPER_PHONE');
        if (!$shipper->shipperPhone) {
            $shipper->shipperPhone = Configuration::get('CHRONOPOST_SHIPPER_MOBILE');
        }

        if ($isReturn) {
            $shipper = new shipperValue();
            $shipper->shipperAdress1 = Tools::substr($a->address1, 0, 35);
            $shipper->shipperAdress2 = Tools::substr($a->address2, 0, 35);
            $shipper->shipperEmail = $cust->email;
            $shipper->shipperCity = Tools::substr($a->city, 0, 30);
            $shipper->shipperCivility = 'M';
            $shipper->shipperContactName = Tools::substr($a->firstname . ' ' . $a->lastname, 0, 35);
            $c = new Country($a->id_country);
            $shipper->shipperCountry = Chronopost::maybeCountryMapping($c->iso_code);
            $shipper->shipperPhone = $a->phone_mobile == null ? $a->phone : $a->phone_mobile;
            $shipper->shipperName = Tools::substr($a->company, 0, 35);
            $shipper->shipperName2 = Tools::substr($a->firstname . ' ' . $a->lastname, 0, 35);
            $shipper->shipperZipCode = $a->postcode;
        }

        return $shipper;
    }

    /**
     * Get header
     *
     * @param $account
     *
     * @return headerValue
     */
    private function getHeader($account)
    {
        $header = new headerValue();
        $header->accountNumber = $account['account'];
        $header->subAccount = $account['subaccount'];
        $header->idEmit = 'PREST';

        return $header;
    }

    /**
     * Get specific code
     *
     * @param $skybill
     * @param $a
     * @param $isReturn
     * @param $recipient
     * @param $skybillDetails
     * @param $method
     *
     * @return mixed
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getSpecificCode($skybill, $a, $isReturn, $recipient, $skybillDetails, $method = [])
    {
        // If return from abroad (not France) : 3T
        // If return from France : 4T
        $co = new Country($a->id_country);

        if (isset($method['product_code'])) {
            $skybill->productCode = $method['product_code'];
            $skybill->service = $method['product_service'] ?? $skybill->service;
        } else {

            if ($isReturn && $recipient->recipientCountry == 'FR' && Chronopost::maybeCountryMapping($co->iso_code) !== 'FR') {
                $skybill->productCode = "3T";
                $skybill->service = "332";
            } elseif ($isReturn && Chronopost::maybeCountryMapping($recipient->recipientCountry) === 'FR' && Chronopost::maybeCountryMapping($co->iso_code) === 'FR') {
                $skybill->productCode = "4T";
                // Exception for 2ShopDirect
                if ($skybillDetails['productCode'] === webservicesHelper::CHRONOPOST_REVERSE_TOSHOP) {
                    $skybill->productCode = $skybillDetails['productCode'];
                    $skybill->service = "6";
                }
            }
            else {
                $skybill->productCode = $skybillDetails['productCode'];
                $skybill->service = $skybillDetails['service'];
            }
        }

        if (isset($skybillDetails['as'])) {
            $skybill->as = $skybillDetails['as'];
        }

        return $skybill;
    }

    /**
     * Get time slot
     *
     * @param $params
     * @param $skybillDetails
     *
     * @return mixed
     */
    private function getTimeSlot($params, $skybillDetails)
    {
        if (array_key_exists('timeSlot', $skybillDetails)) {
            $params->scheduledValue = new scheduledValue();
            $params->scheduledValue->appointmentValue = new appointmentValue();
            $params->scheduledValue->appointmentValue->timeSlotStartDate = $skybillDetails['timeSlotStartDate'];
            $params->scheduledValue->appointmentValue->timeSlotEndDate = $skybillDetails['timeSlotEndDate'];
            $params->scheduledValue->appointmentValue->timeSlotTariffLevel = $skybillDetails['timeSlotTariffLevel'];
        }

        return $params;
    }

    /**
     * Get scheduled parameters
     *
     * @param $orderid
     * @param $carrier
     * @param $freshOptions
     *
     * @return array
     * @throws Exception
     */
    private function getScheduledParams($orderid, $carrier, $freshOptions)
    {
        $dlc = null;
        $scheduledParams = null;
        if (Chronofresh::isChronoFreshCarrier($carrier) || Chronofresh::isChronoFreshClassicCarrier($carrier)) {
            $expirationDate = new DateTime($freshOptions[$orderid]['dlc']);
            $scheduledParams = new scheduledValue();
            $scheduledParams->expirationDate = $expirationDate->format('Y-m-d\TH:i:s');
            $dlc = $freshOptions[$orderid]['dlc'];
        }

        return [$dlc, $scheduledParams];
    }

    /**
     * Get ESD parameters
     *
     * @return esdValue
     */
    private function getEsdParams()
    {
        $esd = new esdValue();
        $esd->specificInstructions = 'aucune';
        $esd->ltAImprimerParChronopost = false;
        $esd->nombreDePassageMaximum = 1;
        $esd->height = '';
        $esd->width = '';
        $esd->length = '';

        return $esd;
    }

    /**
     * Create LT
     *
     * @param string $orderid
     * @param array|bool $account
     * @param bool $isReturn
     * @param array $dimensions
     * @param array $freshOptions
     * @param bool $shipSaturday
     * @param array $method
     *
     * @return null
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function createLT(
        $orderid,
        $account = false,
        $isReturn = false,
        $dimensions = array(),
        $freshOptions = array(),
        $shipSaturday = false,
        $method = []
    )
    {
        if (isset($shipSaturday[0]) && $shipSaturday[0] === "1") {
            $shipSaturday = true;
        } else if (isset($shipSaturday[0]) && $shipSaturday[0] === 0) {
            $shipSaturday = false;
        }

        $o = new Order($orderid);
        $a = new Address($o->id_address_delivery);
        $cust = new Customer($o->id_customer);
        $carrier = new Carrier($o->id_carrier);

        // ESD PARAMETERS
        $esd = $this->getEsdParams();

        // RECIPIENT PARAMETERS
        $recipient = $this->getRecipient($a, $cust, $isReturn);

        // SHIPPER PARAMETERS
        $shipper = $this->getShipper($a, $cust, $isReturn);

        // CUSTOMER PARAMETERS
        $customer = $this->getCustomer();

        // HEADER PARAMETERS
        $header = $this->getHeader($account);

        $skybill = new skybillValue();
        $skybill->evtCode = 'DC';
        $skybill->objectType = 'MAR';
        $skybill->productCode = Chronopost::$carriersDefinitions['CHRONO13']['product_code'];
        $skybill->service = '0';

        if (Tools::getIsset('advalorem') && Tools::getValue('advalorem') == 'yes') {
            $skybill->insuredValue = (int)round((float)Tools::getValue('advalorem_value') * 100);
        }

        if (Tools::getIsset('orders')
            && Configuration::get('CHRONOPOST_ADVALOREM_ENABLED') == 1
            && $carrier->id_reference != Configuration::get('CHRONOPOST_TOSHOPDIRECT_ID')
            && $carrier->id_reference != Configuration::get('CHRONOPOST_TOSHOPDIRECT_EUROPE_ID')
        ) {
            $skybill->insuredValue = (int)round((float)chronopost::amountToInsure($orderid) * 100);
        }

        // SKYBILL PARAMS
        $skybillDetails = Chronopost::getSkybillDetails($o, $isReturn, $shipSaturday);
        $skybill = $this->getSpecificCode($skybill, $a, $isReturn, $recipient, $skybillDetails, $method);

        if (Tools::getValue('chrono_product')) {
            $skybill->productCode = Tools::getValue('chrono_product');
        } elseif (isset($freshOptions[$orderid]['chrono_products'][0]) &&
            count($freshOptions[$orderid]['chrono_products'])) {
            $skybill->productCode = $freshOptions[$orderid]['chrono_products'][0];
        }

        // REF PARAMETERS
        $ref = new refValue();
        $ref->recipientRef = $a->postcode;
        if (array_key_exists('recipientRef', $skybillDetails)) {
            $ref->recipientRef = $skybillDetails['recipientRef'];
        }

        $ref->shipperRef = sprintf('%06d', $orderid);

        // PREPARING WS PARAMETERS
        $params = new shippingMultiParcelWithReservationV3();
        $params->version = '3.0';

        if ($carrier->id_reference === Configuration::get('CHRONOPOST_CHRONORDV_ID')) {
            $params = $this->getTimeSlot($params, $skybillDetails);
        }

        $skybill->shipDate = date('Y-m-d\TH:i:s');
        $skybill->shipHour = date('H');
        $skybill->weightUnit = 'KGM';
        $skybill->skybillRank = 1;

        // SKYBILL DIMENSIONS
        $skybill->height = 22.9;
        $skybill->length = 16.2;
        $skybill->width = 0;
        if (!empty($dimensions['widths'][0]) && !empty($dimensions['heights'][0]) && !empty($dimensions['lengths'][0])) {
            $skybill->height = $dimensions['heights'][0];
            $skybill->length = $dimensions['lengths'][0];
            $skybill->width = $dimensions['widths'][0];
        }

        $skybill->weight = 0;
        if (!empty($dimensions['weights'][0])) {
            $coef = Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF');
            $skybill->weight = $dimensions['weights'][0] * $coef;
        }

        // SKYBILL PARAMS
        $skybillParams = new skybillParamsValue();
        $skybillParams->mode = Configuration::get('CHRONOPOST_GENERAL_PRINTMODE');
        $skybillParams->withReservation = 0;

        // SCHEDULED PARAMS
        list($dlc, $scheduledParams) = $this->getScheduledParams($orderid, $carrier, $freshOptions);

        // E-Label specific to 4T product
        if ($isReturn && $skybill->productCode === '4T') {
            // Change skybill params
            $skybillParams->duplicata = 'N';
            $skybillParams->mode = 'SLT|XML|XML2D|PDF';
            $skybillParams->withReservation = 2;

            // Change global params
            $params->modeRetour = 3;
            $params->multiParcel = 'N';
            $params->version = '2.0';

            // Add customer value
            $customer->printAsSender = 'N';
        } elseif ($isReturn && $skybill->productCode === '3T') {
            // Change skybill params
            $skybillParams->duplicata = 'N';
            $skybillParams->mode = 'PPR|XML';
            $skybillParams->withReservation = 2;

            // Change global params
            $params->modeRetour = 1;
            $params->multiParcel = 'N';
            $params->version = '2.0';

            // Add customer value
            $customer->printAsSender = 'N';
        }

        // When using 2shop, the sender should be the customer, not the pickup point
        if ($isReturn && ($skybill->productCode === '5Y' || $skybill->productCode === '6C')) {
            $a = new Address($o->id_address_invoice);
            $shipper = $this->getShipper($a, $cust, $isReturn);
        }

        $params->esdValue = $esd;
        $params->password = $account['password'];
        $params->headerValue = $header;
        $params->shipperValue = $shipper;
        $params->customerValue = $customer;
        $params->recipientValue = $recipient;
        $params->skybillParamsValue = $skybillParams;
        $params->refValue = $ref;
        $params->skybillValue = $skybill;
        $params->numberOfParcel = 1;

        if ($scheduledParams) {
            $params->scheduledValue = $scheduledParams;
        }

        // CALL WS
        $service = new ShippingServiceWSService();
        $r = $service->shippingMultiParcelWithReservationV3($params)->return;
        $r->isReturn = $isReturn;
        $r->params = $params;

        if ($r->errorCode != 0) {
            return null;
        }

        // MAIL::SEND is bugged in 1.5 !
        // http://forge.prestashop.com/browse/PNM-754 (Unresolved as of 2013-04-15)
        // Context fix (it's that easy)
        Context::getContext()->link = new Link();

        if ($isReturn && $skybill->productCode !== '4T') {
            $this->sendReturnMail($o, $r);
        }

        // Store LT for history
        $type = $isReturn ? CHRONO_RETURN_TYPE : CHRONO_SHIP_TYPE;
        $this->saveToChronopostHistory(
            $o,
            $r->resultParcelValue->skybillNumber,
            $skybill->productCode,
            $recipient,
            $account['account'],
            $type,
            null,
            $dlc,
            (isset($skybill->insuredValue) ? (float)$skybill->insuredValue / 100 : 0)
        );

        return $r;
    }

    /**
     * Create multi LT
     *
     * @param string     $orderid
     * @param int        $totalnb
     * @param array|bool $account
     * @param bool       $isReturn
     * @param array      $dimensions
     * @param array      $freshOptions
     * @param bool       $shipSaturday
     *
     * @return mixed
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function createLTMultiColis(
        $orderid,
        $totalnb = 1,
        $account = false,
        $isReturn = false,
        $dimensions = array(),
        $freshOptions = array(),
        $shipSaturday = false
    ) {
        if (isset($shipSaturday[0]) && $shipSaturday[0] === "1") {
            $shipSaturday = true;
        } else if (isset($shipSaturday[0]) && $shipSaturday[0] === 0) {
            $shipSaturday = false;
        }
        
        $o = new Order($orderid);
        $a = new Address($o->id_address_delivery);
        $cust = new Customer($o->id_customer);
        $carrier = new Carrier($o->id_carrier);

        // ESD PARAMETERS
        $esd = $this->getEsdParams();

        // RECIPIENT PARAMETERS
        $recipient = $this->getRecipient($a, $cust, $isReturn);

        // SHIPPER PARAMETERS
        $shipper = $this->getShipper($a, $cust, $isReturn);

        // CUSTOMER PARAMETERS
        $customer = $this->getCustomer();

        // HEADER PARAMETERS
        $header = $this->getHeader($account);

        // SKYBILL PARAMS
        $skybillDetails = Chronopost::getSkybillDetails($o, $isReturn, $shipSaturday);
        $skybills = [];
        for ($i = 1; $i <= $totalnb; $i++) {
            $skybill = new skybillValue();
            $skybill->evtCode = 'DC';
            $skybill->objectType = 'MAR';
            $skybill->bulkNumber = $totalnb;
            $skybill->skybillRank = $i;
            $skybill->productCode = $skybillDetails['productCode'];
            $skybill->service = 0;
            $skybill = $this->getSpecificCode($skybill, $a, $isReturn, $recipient, $skybillDetails);

            if (Tools::getValue('chrono_products')) {
                $chronoProducts = json_decode(stripslashes(Tools::getValue('chrono_products')), true);

                $indice = $i - 1;
                if ($totalnb > 1 && $i > 1) {
                    $indice -= ($i - 1);
                }

                if (isset($chronoProducts[$orderid][$indice])) {
                    $skybill->productCode = $chronoProducts[$orderid][$indice];
                }
            } elseif (Tools::getValue('chrono_product')) {
                $skybill->productCode = Tools::getValue('chrono_product');
            }

            $skybill->shipDate = date('Y-m-d\TH:i:s');
            $skybill->shipHour = date('H');
            $skybill->weightUnit = 'KGM';

            // SKYBILL DIMENSION
            $skybill->height = 22.9;
            $skybill->length = 16.2;
            $skybill->width = 0;
            if (!empty($dimensions['widths'][$i - 1]) && !empty($dimensions['heights'][$i - 1]) &&
                !empty($dimensions['lengths'][$i - 1])) {
                $skybill->height = $dimensions['heights'][$i - 1];
                $skybill->length = $dimensions['lengths'][$i - 1];
                $skybill->width = $dimensions['widths'][$i - 1];
            }

            $skybill->weight = 0;
            if (!empty($dimensions['weights'][$i - 1])) {
                $coef = Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF');
                $skybill->weight = $dimensions['weights'][$i - 1] * $coef;
            }

            array_push($skybills, $skybill);
        }

        // REF PARAMETERS
        $refs = [];
        for ($i = 0; $i < $totalnb; $i++) {
            $ref = new refValue();
            $ref->recipientRef = $a->postcode;
            if (array_key_exists('recipientRef', $skybillDetails)) {
                $ref->recipientRef = $skybillDetails['recipientRef'];
            }

            $ref->shipperRef = sprintf('%06d', $orderid);
            array_push($refs, $ref);
        }

        // PREPARING WS PARAMETERS
        $params = new shippingMultiParcelWithReservationV3();
        $params->version = '3.0';

        if ($carrier->id_reference === Configuration::get('CHRONOPOST_CHRONORDV_ID')) {
            $params = $this->getTimeSlot($params, $skybillDetails);
        }

        // SKYBILL PARAMS
        $skybillParams = new skybillParamsValue();
        $skybillParams->mode = Configuration::get('CHRONOPOST_GENERAL_PRINTMODE');
        $skybillParams->withReservation = 0;

        // SCHEDULED PARAMS
        list($dlc, $scheduledParams) = $this->getScheduledParams($orderid, $carrier, $freshOptions);

        // E-Label specific to 4T product
        if ($isReturn && $skybills[0]->productCode === '4T') {
            // Change skybill params
            $skybillParams->duplicata = 'N';
            $skybillParams->mode = 'SLT|XML|XML2D|PDF';
            $skybillParams->withReservation = 2;

            // Change global params
            $params->modeRetour = 3;
            $params->multiParcel = 'N';
            $params->version = '2.0';

            // Add customer value
            $customer->printAsSender = 'N';
        } elseif ($isReturn && $skybills[0]->productCode === '3T') {
            // Change skybill params
            $skybillParams->duplicata = 'N';
            $skybillParams->mode = 'PPR|XML';
            $skybillParams->withReservation = 2;

            // Change global params
            $params->modeRetour = 1;
            $params->multiParcel = 'N';
            $params->version = '2.0';

            // Add customer value
            $customer->printAsSender = 'N';
        }

        $params->esdValue = $esd;
        $params->password = $account['password'];
        $params->headerValue = $header;
        $params->shipperValue = $shipper;
        $params->customerValue = $customer;
        $params->recipientValue = $recipient;
        $params->skybillParamsValue = $skybillParams;
        $params->numberOfParcel = $totalnb;
        $params->refValue = $refs;
        $params->skybillValue = $skybills;

        if ($scheduledParams) {
            $params->scheduledValue = $scheduledParams;
        }

        // CALL WS
        $service = new ShippingServiceWSService();
        $r = $service->shippingMultiParcelWithReservationV3($params)->return;
        $r->isReturn = $isReturn;
        $r->params = $params;

        // MAIL::SEND is bugged in 1.5 !
        // http://forge.prestashop.com/browse/PNM-754 (Unresolved as of 2013-04-15)
        // Context fix (it's that easy)
        Context::getContext()->link = new Link();

        if ($isReturn && $skybills[0]->productCode !== '4T') {
            $this->sendReturnMail($o, $r);
        }

        $type = $isReturn ? CHRONO_RETURN_TYPE : CHRONO_SHIP_TYPE;
        if (!is_array($r->resultParcelValue)) {
            $this->saveToChronopostHistory(
                $o,
                $r->resultParcelValue->skybillNumber,
                $skybills[0]->productCode,
                $recipient,
                $account['account'],
                $type,
                null,
                $dlc,
                (isset($skybills[0]->insuredValue) ? (int)$skybills[0]->insuredValue : 0)
            );
        } else {
            // Store LT for history
            $reference = null;
            foreach ($r->resultParcelValue as $item) {
                if (!$reference) {
                    $reference = $item->skybillNumber;
                }

                $this->saveToChronopostHistory(
                    $o,
                    $item->skybillNumber,
                    $skybills[0]->productCode,
                    $recipient,
                    $account['account'],
                    $type,
                    $reference,
                    $dlc,
                    (isset($skybills[0]->insuredValue) ? (int)$skybills[0]->insuredValue : 0)
                );
            }
        }

        return $r;
    }

    /**
     * @param $o Order
     * @param $r array WS result
     */
    function sendReturnMail($o, $r)
    {
        $customer = new Customer($o->id_customer);
        $service = new ShippingServiceWSService();
        $params = new getReservedSkybill();
        $params->reservationNumber = $r->reservationNumber;
        $result = $service->getReservedSkybill($params);

        $lt = new stdClass();
        if ($result->return->errorCode == 0 && $result->return->skybill) {
            $lt->pdfEtiquette = base64_decode($result->return->skybill);
            $lt->skybillNumber = $r->resultParcelValue->skybillNumber;
        }

        $template_path = _PS_MODULE_DIR_ . 'chronopost/mails/';
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $template_path = 'mails/';
        }

        Mail::Send(
            $o->id_lang,
            'return',
            'Lettre de transport Chronopost pour le retour de votre commande',
            array(
                '{id_order}'  => $o->id,
                '{firstname}' => $customer->firstname,
                '{lastname}'  => $customer->lastname
            ),
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            array(
                'content' => $lt->pdfEtiquette,
                'mime'    => 'application/pdf',
                'name'    => $lt->skybillNumber . '.pdf'
            ),
            null,
            $template_path,
            true
        );
    }

    /**
     * @param      $o
     * @param      $skybillNumber
     * @param      $productCode
     * @param      $recipient
     * @param      $account
     * @param      $type
     * @param null $reference
     * @param null $dlc
     * @param int  $insuredValue
     */
    function saveToChronopostHistory(
        $o,
        $skybillNumber,
        $productCode,
        $recipient,
        $account,
        $type,
        $reference = null,
        $dlc = null,
        $insuredValue = 0
    ) {
        if (!$reference) {
            $reference = $skybillNumber;
        }

        Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'chrono_lt_history` 
        (id_order, lt, lt_reference, lt_dlc, product, zipcode, country, insurance, city, account_number, type, cancelled)
        VALUES (
				' . (int)$o->id . ', 
				"' . pSQL($skybillNumber) . '",
				"' . pSQL($reference) . '",
				"' . pSQL($dlc) . '",
				"' . pSQL($productCode) . '",
				"' . pSQL($recipient->recipientZipCode) . '",
				"' . pSQL(Chronopost::maybeCountryMapping($recipient->recipientCountry)) . '",
				"' . pSQL($insuredValue) . '",
				"' . pSQL($recipient->recipientCity) . '",
				"' . pSQL($account) . '",
				' . (int)$type . ', 
				NULL
			)');

        Chronopost::trackingStatus($o->id, $skybillNumber);
    }
}
