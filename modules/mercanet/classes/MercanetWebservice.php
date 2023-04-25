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

class MercanetWebservice
{

    // Services Refund
    const WS_REFUND = 'cashManagement/refund';
    const WS_CANCEL = 'cashManagement/cancel';
    const WS_DIAGNOSTIC = 'diagnostic/getTransactionData';
    // Service Duplicate
    const WS_SERVICE_DUPLICATE = 'cashManagement/duplicate';
    // Interface Version
    const WS_IV_REFUND = 'CR_WS_2.6';
    const WS_IV_DIAGNOSTIC = 'DR_WS_2.3';
    const WS_IV_DUPLICATE = 'CR_WS_2.3';
    // Variables
    const WS_STATUS_TO_CAPTURE = 'TO_CAPTURE';

    /**
     * Return the params for WS getTransactionData
     * @param string $transaction_reference
     * @return boolean | array
     */
    public static function getTransactionDataParams($transaction_reference, $id_shop)
    {
        if (empty($transaction_reference)) {
            return false;
        }
        // Build Params
        $params = array();
        $params['interfaceVersion'] = self::WS_IV_DIAGNOSTIC;
        $params['keyVersion'] = Configuration::get('MERCANET_KEY_VERSION', null, null, (int)$id_shop);
        $params['merchantId'] = Configuration::get('MERCANET_MERCHANT_ID', null, null, (int)$id_shop);
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true && (bool)Configuration::get('MERCANET_TEST_USER') == true) {
            $params['merchantId'] = Configuration::get('MERCANET_TEST_ACCOUNT');
            $params['keyVersion'] = Configuration::get('MERCANET_TEST_KEY_VERSION');
        }
        $params['transactionReference'] = $transaction_reference;
        ksort($params);
        $params['seal'] = MercanetApi::buildSeal($params, true);
        return $params;
    }

    /**
     * Return the params for WS getTransactionData
     * @param string $transaction_reference
     * @return boolean | array
     */
    public static function getRefundParams($currency_iso_code_num, $transaction_reference, $slip_amount, $id_shop)
    {
        if (empty($currency_iso_code_num) || empty($transaction_reference) || empty($slip_amount)) {
            return false;
        }

        // Build Params
        $params = array();
        $params['currencyCode'] = (int)$currency_iso_code_num;
        $params['interfaceVersion'] = MercanetWebservice::WS_IV_REFUND;
        $params['keyVersion'] = Configuration::get('MERCANET_KEY_VERSION', null, null, (int)$id_shop);
        $params['merchantId'] = Configuration::get('MERCANET_MERCHANT_ID', null, null, (int)$id_shop);
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true && (bool)Configuration::get('MERCANET_TEST_USER') == true) {
            $params['merchantId'] = Configuration::get('MERCANET_TEST_ACCOUNT');
            $params['keyVersion'] = Configuration::get('MERCANET_TEST_KEY_VERSION');
        }
        $params['operationAmount'] = (float)$slip_amount * 100;
        $params['operationOrigin'] = 'BATCH';
        $params['transactionReference'] = $transaction_reference;
        ksort($params);
        $params['seal'] = MercanetApi::buildSeal($params, true);

        return $params;
    }

    /**
     * Return the params for WS getTransactionData
     * @param string $transaction_reference
     * @return boolean | array
     */
    public static function getCancelParams($currency_iso_code_num, $transaction_reference, $slip_amount, $id_shop)
    {
        if (empty($currency_iso_code_num) || empty($transaction_reference) || empty($slip_amount)) {
            return false;
        }

        // Build Params
        $params = array();
        $params['currencyCode'] = (int)$currency_iso_code_num;
        $params['interfaceVersion'] = MercanetWebservice::WS_IV_REFUND;
        $params['keyVersion'] = Configuration::get('MERCANET_KEY_VERSION', null, null, (int)$id_shop);
        $params['merchantId'] = Configuration::get('MERCANET_MERCHANT_ID', null, null, (int)$id_shop);
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true && (bool)Configuration::get('MERCANET_TEST_USER') == true) {
            $params['merchantId'] = Configuration::get('MERCANET_TEST_ACCOUNT');
            $params['keyVersion'] = Configuration::get('MERCANET_TEST_KEY_VERSION');
        }
        $params['operationAmount'] = (float)$slip_amount * 100;
        $params['operationOrigin'] = 'BATCH';
        $params['transactionReference'] = $transaction_reference;
        ksort($params);
        $params['seal'] = MercanetApi::buildSeal($params, true);

        return $params;
    }

    /**
     * Refund
     * @param type $id_order
     * @param type $id_mercanet_transaction
     * @param type $id_slip
     * @return boolean
     */
    public static function refund($id_order, $id_mercanet_transaction, $id_slip)
    {
        if (empty($id_order) || empty($id_mercanet_transaction) || empty($id_slip)) {
            return false;
        }

        $order = new Order((int)$id_order);
        $transaction = new MercanetTransaction((int)$id_mercanet_transaction);
        $currency = new Currency((int)$order->id_currency);
        $slip = new OrderSlip((int)$id_slip);

        // DIAGNOSTIC
        $params_diagnostic = MercanetWebservice::getTransactionDataParams($transaction->transaction_reference, (int)$order->id_shop);

        if (empty($params_diagnostic)) {
            return false;
        }

        $result_diagnostic = MercanetWebservice::submitWebService(MercanetWebservice::WS_DIAGNOSTIC, $params_diagnostic);

        if ($result_diagnostic->responseCode != '00') {
            return $result_diagnostic;
        } elseif (!isset($result_diagnostic->transactionStatus)) {
            return $result_diagnostic;
        } elseif ($result_diagnostic->transactionStatus == MercanetWebservice::WS_STATUS_TO_CAPTURE) {
            $service = MercanetWebservice::WS_CANCEL;
            $operation_type = MercanetTransaction::ANTICIPATE_REFUND;
        } else {
            $service = MercanetWebservice::WS_REFUND;
            $operation_type = MercanetTransaction::REFUND;
        }

        // REFUND
        $params_refund = MercanetWebservice::getRefundParams((int)$currency->iso_code_num, $transaction->transaction_reference, (float)$slip->amount, (int)$order->id_shop);
        $result = MercanetWebservice::submitWebService($service, $params_refund);

        // Check Seal
        if (MercanetApi::verifySeal($result, (isset($result->seal)) ? $result->seal : null, true) != true) {
            $result->responseCode = 34;
            $result->message = 'Seal Error';
            return $result;
        }

        // Register Transaction
        if (isset($result->responseCode) && $result->responseCode == '00') {
            MercanetTransaction::registerWebserviceTransaction($order, $transaction, $result, $operation_type, null, $slip->amount, $slip->id);
            if ((isset($result->newAmount))) {
                MercanetSchedule::updateWebserviceScheduleAmount($transaction->transaction_reference, $result->newAmount);
            }

            // Schedules left
            $schedules_left = MercanetSchedule::getSchedulesLeftByOrderId((int)$order->id);

            if (!empty($schedules_left)) {
                foreach ($schedules_left as $schedule_left) {
                    $params_cancel = MercanetWebservice::getCancelParams((int)$currency->iso_code_num, $schedule_left['transaction_reference'], (float)$schedule_left['amount'], (int)$order->id_shop);
                    $result_history = MercanetWebservice::submitWebService(self::WS_CANCEL, $params_cancel);

                    // Check Seal
                    if (MercanetApi::verifySeal($result_history, (isset($result_history->seal)) ? $result_history->seal : null, true) != true) {
                        $result->responseCode = 34;
                        $result->message = 'Seal Error';
                        return $result;
                    }

                    if (isset($result_history->responseCode) && $result_history->responseCode == '00') {
                        MercanetTransaction::registerWebserviceTransaction(
                            $order,
                            $transaction,
                            $result_history,
                            MercanetTransaction::CANCEL,
                            $schedule_left['transaction_reference'],
                            (float)$schedule_left['amount']
                        );
                        $schedule = new MercanetSchedule((int)$schedule_left['id_mercanet_schedule']);
                        $schedule->state = 'Cancelled';
                        $schedule->save();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Send Schedules
     */
    public function sendRecurringSchedules()
    {
        $module = Module::getInstanceByName('mercanet');

        if (!$module->isFeatureActivated('ABO')) {
            return;
        }

        // Get Schedules
        $schedules = MercanetCustomerPaymentRecurring::getSchedulesToCapture();

        if (!empty($schedules)) {
            $this->context = Context::getContext();

            // For each schedule, get the params
            foreach ($schedules as $schedule) {
                // Init
                $this->context->schedule = $schedule;
                $this->context->schedule['data'] = MercanetApi::getDataFromRawData($this->context->schedule['raw_data'], '<br>', ':');
                $this->context->customer = new Customer((int)$this->context->schedule['id_customer']);

                // Generate a cart
                $this->createCart();

                // Get Params
                $this->context->params = $this->getRecurringParams();

                // TODO LOG
                // send result
                $result = MercanetWebservice::submitWebService(self::WS_SERVICE_DUPLICATE, $this->context->params);

                // TODO LOG
                // Treat Result
                if (isset($result->seal) && MercanetApi::verifySeal($result, $result->seal, true) == true) {
                    $this->context->result = (array)$result;
                    $this->addParamsToResult();
                    $notification = new MercanetNotification();
                    $notification->notify(MercanetApi::getRawData($this->context->result), MercanetApi::buildSeal($this->context->result), true, true);
                } else {
                    $message = '[WEBSERVICE][RECURRING]';
                    $message = 'Customer: '.$this->context->customer->id.' '.$this->context->customer->firstname.' '.$this->context->customer->lastname;
                    $message .= ' || ';
                    $message .= ' Params: ';
                    $message .= implode(
                        ', ',
                        array_map(
                            function ($v, $k) {
                                return $k.'='.$v;
                            },
                            $this->context->params,
                            array_keys($this->context->params)
                        )
                    );
                    MercanetLogger::log($message);
                }
            }
        }
    }

    /**
     * Complete the return of Mercanet
     */
    public function addParamsToResult()
    {
        // Copy from schedule
        (!isset($this->context->result['captureDay'])) ? $this->context->result['captureDay'] = $this->context->schedule['data']['captureDay'] : null;
        (!isset($this->context->result['captureMode'])) ? $this->context->result['captureMode'] = $this->context->schedule['data']['captureMode'] : null;
        (!isset($this->context->result['currencyCode'])) ? $this->context->result['currencyCode'] = $this->context->schedule['data']['currencyCode'] : null;
        (!isset($this->context->result['merchantId'])) ? $this->context->result['merchantId'] = $this->context->schedule['data']['merchantId'] : null;
        (!isset($this->context->result['orderChannel'])) ? $this->context->result['orderChannel'] = $this->context->schedule['data']['orderChannel'] : null;
        (!isset($this->context->result['paymentMeanBrand'])) ? $this->context->result['paymentMeanBrand'] = $this->context->schedule['data']['paymentMeanBrand'] : null;
        (!isset($this->context->result['paymentMeanType'])) ? $this->context->result['paymentMeanType'] = $this->context->schedule['data']['paymentMeanType'] : null;
        (!isset($this->context->result['orderRecurringId'])) ? $this->context->result['orderRecurringId'] = $this->context->schedule['id_order'] : null;

        // Copy from data sent
        (!isset($this->context->result['transactionReference'])) ? $this->context->result['transactionReference'] = $this->context->params['transactionReference'] : null;
        (!isset($this->context->result['keyVersion'])) ? $this->context->result['keyVersion'] = $this->context->params['keyVersion'] : null;
        (!isset($this->context->result['amount'])) ? $this->context->result['amount'] = $this->context->params['amount'] : null;
        (!isset($this->context->result['returnContext'])) ? $this->context->result['returnContext'] = $this->context->params['returnContext'] : null;
        (!isset($this->context->result['orderId'])) ? $this->context->result['orderId'] = $this->context->params['orderId'] : null;

        $this->context->result['paymentPattern'] = 'RECURRING_PAYMENT';

        // unset the seal
        unset($this->context->result['seal']);
        ksort($this->context->result);
    }

    /**
     * Return the params for the duplicate
     */
    public function getRecurringParams()
    {
        $params = array();

        // Reference
        $reference = Order::generateReference();
        if (!empty($reference)) {
            $reference = MercanetOrderReference::addCartReference((int)$this->context->cart->id, $reference);
        }

        // Mandatory
        $params['amount'] = MercanetApi::getTotalAmount($this->context->cart) * 100;
        $params['currencyCode'] = trim($this->context->schedule['data']['currencyCode']);
        $params['captureDay'] = trim($this->context->schedule['data']['captureDay']);
        $params['captureMode'] = trim($this->context->schedule['data']['captureMode']);
        $params['customerEmail'] = $this->context->customer->email;
        $params['customerId'] = $this->context->customer->id;
        $params['customerIpAddress'] = (isset($this->context->schedule['data']['customerIpAddress'])) ? trim($this->context->schedule['data']['customerIpAddress']) : null;
        $params['interfaceVersion'] = self::WS_IV_DUPLICATE;
        $params['merchantId'] = Configuration::get('MERCANET_MERCHANT_ID');
        $params['orderChannel'] = (isset($this->context->schedule['data']['orderChannel'])) ? trim($this->context->schedule['data']['orderChannel']) : null;
        $params['orderId'] = $reference;
        $params['returnContext'] = 'id_cart='.(int)$this->context->cart->id;
        $params['returnContext'] .= ',is_recurring_payment=true';
        $params['returnContext'] .= ',id_schedule='.(int)$this->context->schedule['id_mercanet_customer_payment_recurring'];
        $params['fromTransactionReference'] = trim($this->context->schedule['data']['transactionReference']);
        $params['transactionReference'] = MercanetApi::generateRandomReference((int)$this->context->cart->id);
        $params['fromMerchantId'] = trim($this->context->schedule['data']['merchantId']);
        $params['keyVersion'] = Configuration::get('MERCANET_KEY_VERSION');
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true && (bool)Configuration::get('MERCANET_TEST_USER') == true) {
            $params['merchantId'] = Configuration::get('MERCANET_TEST_ACCOUNT');
            $params['keyVersion'] = Configuration::get('MERCANET_TEST_KEY_VERSION');
        }
        ksort($params);
        $params['seal'] = MercanetApi::buildSeal($params, true);

        return $params;
    }

    /**
     * Create the Cart
     */
    public function createCart()
    {
        // Init Cart
        $this->context->cart = new Cart();

        // Mandatory
        if (is_null($this->context->cart->id_lang)) {
            $this->context->cart->id_lang = $this->context->schedule['id_lang'];
        }

        if (is_null($this->context->cart->id_currency)) {
            $this->context->cart->id_currency = $this->context->schedule['id_currency'];
        }

        if (is_null($this->context->cart->id_customer)) {
            $this->context->cart->id_customer = $this->context->schedule['id_customer'];
        }


        if (is_null($this->context->cart->id)) {
            $this->context->cart->add();
            $this->context->cookie->__set('id_cart', $this->context->cart->id);
        }

        // Optional
        $this->context->cart->id_address_delivery = (int)$this->context->schedule['id_address_delivery'];
        $this->context->cart->id_address_invoice = (int)$this->context->schedule['id_address_invoice'];
        $this->context->cart->id_carrier = (int)$this->context->schedule['id_carrier'];
        $this->context->cart->id_shop = (int)$this->context->schedule['id_shop'];
        $this->context->cart->id_shop_group = (int)$this->context->schedule['id_shop_group'];

        $qty = 1;
        $recurring_payment = new MercanetCustomerPaymentRecurring($this->context->schedule['id_mercanet_customer_payment_recurring']);
        if ((int)$recurring_payment->getLateRecurringOccurence()) {
            $qty = (int)$recurring_payment->getLateRecurringOccurence();
        }
        // Add the product
        $this->context->cart->updateQty(
            (int)1,
            (int)$this->context->schedule['id_product'],
            null,
            null,
            'up',
            null,
            new Shop((int)$this->context->cart->id_shop),
            false
        );

        $product = new Product($this->context->schedule['id_product']);

            $specific_price = new SpecificPrice();
            $specific_price->id_cart = (int)$this->context->cart->id;
            $specific_price->id_shop = 0;
            $specific_price->id_shop_group = 0;
            $specific_price->id_currency = 0;
            $specific_price->id_country = 0;
            $specific_price->id_group = 0;
            $specific_price->id_customer = (int)$this->context->customer->id;
            $specific_price->id_product = (int)$this->context->schedule['id_product'];
            $specific_price->id_product_attribute = 0;
            $specific_price->price = (float)$this->context->schedule['amount_tax_exclude'] * $qty;
            $specific_price->from_quantity = 1;
            $specific_price->reduction = 0;
            $specific_price->reduction_type = 'amount';
            $specific_price->from = '0000-00-00 00:00:00';
            $specific_price->to = '0000-00-00 00:00:00';
            $specific_price->add();
            
            $recurring_payment->current_specific_price = $specific_price->id;
            $recurring_payment->save();

        $this->context->cart->save();
    }

    /**
     * Call Mercanet WebService
     * @param type $service
     * @param array $data
     * @param type $sealed
     * @return type
     */
    public static function submitWebService($service, array $data, $sealed = true)
    {
        // Take the WS url
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true) {
            $url_webservice = Configuration::getGlobalValue('MERCANET_WS_URL_TEST').$service;
        } else {
            $url_webservice = Configuration::getGlobalValue('MERCANET_WS_URL').$service;
        }

        // Add the seal
        if ($sealed == false) {
            $data['seal'] = MercanetApi::buildSeal($data);
        }
        ksort($data);

        $data_encoded = Tools::JsonEncode($data);

        // Open cURL session and data are sent to server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_webservice);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_encoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept:application/json'));
        curl_setopt($ch, CURLOPT_PORT, 443);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        // Manage errors
        if ($result == false || $info['http_code'] != 200) {
            //echo "Data receive ko : ".$result;
            if (curl_error($ch)) {
                $result .= "\n".curl_error($ch);
            }
        }

        // Close cURL session
        curl_close($ch);

        return Tools::JsonDecode($result);
    }
}
