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

class MercanetNotification
{
    
    private $source = '';

    public function notify($raw_data, $seal, $source, $base64 = false)
    {
        // If data or seal are missing, return false
        if (empty($raw_data) || empty($seal)) {
            $message = 'Data empty =>';
            $message .= ' Params: ';
            $message .= $raw_data;
            $message .= ' Seal: ';
            $message .= $seal;
            MercanetLogger::log($message, MercanetLogger::LOG_ERROR, MercanetLogger::FILE_DEBUG);
            return false;
        }

        // Check if seal is valid
        $is_sealed_valid = MercanetApi::verifySeal($raw_data, $seal);

        // If no valid, return flase
        if ((bool)$is_sealed_valid == false) {
            $message = 'Seal invalid =>';
            $message .= ' Params: ';
            $message .= $raw_data;
            $message .= ' Seal: ';
            $message .= $seal;
            MercanetLogger::log($message, MercanetLogger::LOG_ERROR, MercanetLogger::FILE_DEBUG);
            return false;
        }
        if ($base64) {
            $raw_data = base64_decode($raw_data);
        }
        // Transform raw data into an array
        $data = MercanetApi::getDataFromRawData($raw_data);

        if (!is_array($data)) {
            $message = 'Data invalid , data is not an array =>';
            $message .= ' Params: ';
            $message .= $data;
            $message .= ' Seal: ';
            $message .= $seal;
            MercanetLogger::log($message, MercanetLogger::LOG_ERROR, MercanetLogger::FILE_DEBUG);
            return false;
        }

        // Transform the return context raw data
        $return_context = MercanetApi::getDataFromRawData($data['returnContext'], ',');
        if (!isset($return_context['id_cart'])) {
            $message = 'Data invalid , id cart is not set =>';
            $message .= ' Params: ';
            $message .= implode(
                ', ',
                array_map(
                    function ($v, $k) {
                        return $k.'='.$v;
                    },
                    $data,
                    array_keys($data)
                )
            );
            MercanetLogger::log($message, MercanetLogger::LOG_ERROR, MercanetLogger::FILE_DEBUG);
            return false;
        }

        if ($data['responseCode'] == Configuration::getGlobalValue('MERCANET_CANCEL_RC')) {
            $message = 'Data cancelled by response code =>';
            $message .= ' Params: ';
            $message .= implode(
                ', ',
                array_map(
                    function ($v, $k) {
                        return $k.'='.$v;
                    },
                    $data,
                    array_keys($data)
                )
            );
            MercanetLogger::log($message, MercanetLogger::LOG_INFO, MercanetLogger::FILE_DEBUG);
            return false;
        }
        // to avoid double order we check if the source script is the one marked in db
        $is_exists = Db::getInstance()->getRow('
            SELECT *
            FROM `'._DB_PREFIX_.'mercanet_reference_payed` where order_reference = "'.pSQL($data['transactionReference']).'" AND source = "'.pSQL($source).'"');
        if (!$is_exists) {
            return false;
        }
        // Init
        $this->context = Context::getContext();
        $this->module_name = 'mercanet';
        $this->data = $data;
        $this->source = $source;
        $this->context->currency = new Currency(Currency::getIdByNumericIsoCode((int)$this->data['currencyCode']));
        $this->context->cart = new Cart((int)$return_context['id_cart']);
        $this->context->cart->id_currency = Currency::getIdByNumericIsoCode((int)$this->data['currencyCode']);
        $this->context->customer = new Customer((int)$this->context->cart->id_customer);
        $id_order = Order::getOrderByCartId((int)$this->context->cart->id);
        $this->context->order = new Order((int)$id_order);
        $this->amount = Tools::ps_round((float)$this->data['amount'] / 100, 2);
        $this->payment_name = Module::getModuleName($this->module_name);
        $this->module = Module::getInstanceByName($this->module_name);
        // Important to have the translation
        $this->module->l('Simple Payment');
        $this->module->l('One click');
        $this->module->l('Recurring Payment');

        // Set if first Recurring Payment
        if (isset($return_context['is_recurring']) && $return_context['is_recurring'] == true) {
            $data['paymentPattern'] = 'RECURRING';
        }

        // Create the order only if it's not already created
        if (isset($data['paymentPattern'])) {
            // Create order
            $paymentMeanBrand = (!empty($this->data['paymentMeanBrand'])) ? $this->data['paymentMeanBrand'] : "N/A";
            switch ($data['paymentPattern']) {
                // NX Order
                case 'INSTALMENT':
                    $this->nx_payment = new MercanetNxPayment((int)$return_context['id_nx_payment']);
                    $this->payment_name = $this->nx_payment->method_name[$this->context->cart->id_lang];
                    if (isset($data['paymentMeanId']) && $data['paymentMeanId'] != 'null') {
                        $this->payment_name .= ' (One Click)';
                    }
                    $this->createScheduleOrder();
                    break;

                // Recurring Order
                case 'RECURRING':
                    $this->payment_name .= ' - '.Translate::getModuleTranslation($this->module, 'Recurring Payment', 'MercanetNotification').' - '. $paymentMeanBrand;
                    $this->createSimpleOrder();
                    // check if order is good to create initial schedule
                    if ($this->data['responseCode'] == '00' && (float)$this->amount == (float)$this->context->cart->getOrderTotal()) {
                        $this->createRecurringSchedule();
                    }
                    break;

                // Recurring Payment
                case 'RECURRING_PAYMENT':
                    $this->payment_name .= ' - '.Translate::getModuleTranslation($this->module, 'Recurring Payment', 'MercanetNotification').' - '. $paymentMeanBrand;
                    $this->createRecurringOrder();
                    $this->updateRecurringSchedule((int)$return_context['id_schedule']);
                    break;

                // Simple Order
                default:
                    // Name of the payment
                    if (isset($data['paymentMeanId']) && $data['paymentMeanId'] != 'null') {
                        $this->payment_name .= ' - '.Translate::getModuleTranslation($this->module, 'Simple Payment (One Click)', 'MercanetNotification').' - '. $paymentMeanBrand;
                    } else {
                        $this->payment_name .= ' - '.Translate::getModuleTranslation($this->module, 'Simple Payment', 'MercanetNotification').' - '. $paymentMeanBrand;
                    }
                    $this->createSimpleOrder();
                    break;
            }
        }
    }

    /**
     * Create a simple order
     */
    private function createSimpleOrder()
    {
        $id_order = Order::getOrderByCartId((int)$this->context->cart->id, (int)$this->context->cart->id_shop);
        $this->context->order = new Order((int)$id_order);
        
        // Log
        $message = 'Data before payment validation => ';
        $message .= 'Customer: '.$this->context->customer->id.' '.$this->context->customer->firstname.' '.$this->context->customer->lastname;
        $message .= ' || ';
        $message .= ' Order: ';
        $message .= (!empty($id_order)) ? 'Yes: '.$id_order : 'None';
        $message .= ' || ';
        $message .= ' URI: ';
        $message .= $_SERVER["REQUEST_URI"];
        $message .= ' || ';
        $message .= ' Params: ';
        $message .= implode(
            ', ',
            array_map(
                function ($v, $k) {
                    return $k.'='.$v;
                },
                $this->data,
                array_keys($this->data)
            )
        );

        MercanetLogger::log($message, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);


        //  Check if the cart is already in the loop
        /* if (MercanetOrderQueue::checkOrderCreated($this->context->cart->id, $this->source) == true) {
          return true;
          } */

        $epsilon = 0.00001;
        // If payment success and same amount
        if ($this->data['responseCode'] == '00' && (abs((float)$this->amount - (float)$this->context->cart->getOrderTotal()) < $epsilon)) {
            $status = (int)_PS_OS_PAYMENT_;
        } else {
            $status = (int)_PS_OS_ERROR_;
        }
        
        if(!$this->context->cart->OrderExists()) {
            $this->module->validateOrder(
                (int)$this->context->cart->id, $status, (float)$this->amount, (string)$this->payment_name,
                null, null, (int)$this->context->cart->id_currency, false, $this->context->customer->secure_key
            );
            $id_order = MercanetApi::getOrderByCartId((int)$this->context->cart->id, (int)$this->context->cart->id_shop);
            $this->context->order = new Order((int)$id_order);
        } else {
            $id_order = MercanetApi::getOrderByCartId((int)$this->context->cart->id, (int)$this->context->cart->id_shop);
            $this->context->order = new Order((int)$id_order);
            $this->context->order->current_state = $status;
            $this->context->order->payment =  $this->payment_name;
            $this->context->order->save();            
            
            $history = new OrderHistory();
            $history->id_order = (int)$this->context->order->id;
            $history->changeIdOrderState($status, (int)($this->context->order->id));                     
            $carrier = new Carrier($this->context->order->id_carrier, $this->context->order->id_lang);
            $templateVars = array();
            if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $this->context->order->shipping_number) {
                $templateVars = array('{followup}' => str_replace('@', $this->context->order->shipping_number, $carrier->url));
            }
            $history->addWithemail(true, $templateVars);
        }
        
        $message = 'Data after payment validation => ';
        $message .= 'Customer: '.$this->context->customer->id.' '.$this->context->customer->firstname.' '.$this->context->customer->lastname;
        $message .= ' || ';
        $message .= ' Order: ';
        $message .= (!empty($id_order)) ? 'Yes: '.$id_order : 'None';
        $message .= ' || ';
        $message .= ' URI: ';
        $message .= $_SERVER["REQUEST_URI"];
        $message .= ' || ';
        $message .= ' Params: ';
        $message .= implode(
            ', ',
            array_map(
                function ($v, $k) {
                    return $k.'='.$v;
                },
                $this->data,
                array_keys($this->data)
            )
        );

        MercanetLogger::log($message, MercanetLogger::LOG_INFO, MercanetLogger::FILE_DEBUG);
        // Register Order Queue
        //MercanetOrderQueue::updateOrderQueue((int)$this->context->cart->id, (int)$this->context->order->id);
        // Register transaction
        $this->registerTransaction();

        // Add private message
        $this->addNewPrivateMessage();
    }

    /**
     * Create a recurring order
     */
    private function createRecurringOrder()
    {
        $id_order = Order::getOrderByCartId((int)$this->context->cart->id);
        if (!empty($id_order)) {
            return;
        }

        $epsilon = 0.00001;        
        // If payment success and same amount
        if ($this->data['responseCode'] == '00' &&  (abs((float)$this->amount - (float)$this->context->cart->getOrderTotal()) < $epsilon)) {
            $status = (int)Configuration::getGlobalValue('MERCANET_RECURRING_OS_PAYMENT');
        } else {
            $status = (int)Configuration::getGlobalValue('MERCANET_RECURRING_OS_ERROR');
        }

        if(!$this->context->cart->OrderExists()) {
            $this->module->validateOrder(
                (int)$this->context->cart->id, $status, (float)$this->amount, (string)$this->payment_name,
                null, array('mercanet_order_recurring' => true), (int)$this->context->cart->id_currency, false, $this->context->customer->secure_key
            );
            $id_order = MercanetApi::getOrderByCartId((int)$this->context->cart->id, (int)$this->context->cart->id_shop);
            $this->context->order = new Order((int)$id_order);
        } else {
            $id_order = MercanetApi::getOrderByCartId((int)$this->context->cart->id, (int)$this->context->cart->id_shop);
            $this->context->order = new Order((int)$id_order);
            $this->context->order->current_state = $status;
            $this->context->order->payment =  $this->payment_name;
            $this->context->order->save();            
            
            $history = new OrderHistory();
            $history->id_order = (int)$this->context->order->id;
            $history->changeIdOrderState($status, (int)($this->context->order->id));                     
            $carrier = new Carrier($this->context->order->id_carrier, $this->context->order->id_lang);
            $templateVars = array();
            if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $this->context->order->shipping_number) {
                $templateVars = array('{followup}' => str_replace('@', $this->context->order->shipping_number, $carrier->url));
            }
            $history->addWithemail(true, $templateVars);
        }

        // TODO LOG
        // Register transaction
        $this->registerTransaction();

        // Add private message
        $this->addNewPrivateMessage();
    }

    /**
     * Update the recurring payment
     */
    private function updateRecurringSchedule($id_schedule)
    {
        MercanetLogger::log("($this->source) ".'bengining of updateRecurringSchedule. $id_schedule : '.$id_schedule);
        
        $customer_payment_recurring = new MercanetCustomerPaymentRecurring((int)$id_schedule);
        
        MercanetLogger::log("($this->source) ".'$customer_payment_recurring : '.MercanetLogger::transformArrayToString((array)$customer_payment_recurring));
        
        MercanetLogger::log("($this->source) ".'call of deleteCurrentSpecificPrice with id_customer_payment_recurring : '.$customer_payment_recurring->id);
        $this->deleteCurrentSpecificPrice($customer_payment_recurring->id);
        if ($this->data['responseCode'] == '00') {
            MercanetLogger::log("($this->source) ".'responseCode is 00');
            
            $qty = 1;
            if ((int)$customer_payment_recurring->getLateRecurringOccurence()) {
                $qty = (int)$customer_payment_recurring->getLateRecurringOccurence();
            }
            $customer_payment_recurring->current_occurence += (int)$qty;
            $last_schedule = new DateTime();
            if ($customer_payment_recurring->isAnticipatedPayment()) {
                $last_schedule = new DateTime($customer_payment_recurring->next_schedule);
            }
            $customer_payment_recurring->last_schedule = $last_schedule->format('Y-m-d h:m:s');
            $next_schedule = $last_schedule->add(new DateInterval('P'.(int)$customer_payment_recurring->number_occurences.(string)$customer_payment_recurring->periodicity));
            $customer_payment_recurring->next_schedule = $next_schedule->format('Y-m-d h:m:s');
            $customer_payment_recurring->status = MercanetCustomerPaymentRecurring::ID_STATUS_ACTIVE;
            
            MercanetLogger::log("($this->source) ".'$customer_payment_recurring is about to be save : '.MercanetLogger::transformArrayToString((array)$customer_payment_recurring));
            
            $customer_payment_recurring->save();
        } else {
            MercanetLogger::log("($this->source) ".'responseCode is not 00. Set statut to pause ('.MercanetCustomerPaymentRecurring::ID_STATUS_PAUSE.')');
            
            $customer_payment_recurring->status = MercanetCustomerPaymentRecurring::ID_STATUS_PAUSE;
            $customer_payment_recurring->save();
            
            // payment failed during first 12 months ?
            $current_date = new DateTime;
            $recurring_init_date = new DateTime($customer_payment_recurring->date_add);
            $date_interval = $recurring_init_date->diff($current_date);
            $total_months = 12 * $date_interval->y + $date_interval->m;

            if ((int)$total_months < 12) {
                MercanetLogger::log("($this->source) ".'payment failed during first 12 months. Send a mail.');
                
                $customer = new Customer($customer_payment_recurring->id_customer);
                $module = Module::getInstanceByName('mercanet');
                $data = array(
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname,
                    '{email}' => $customer->email,
                    '{customer_id}' => $customer->id,
                    '{order_id}' => $customer_payment_recurring->id_order,
                );
                // send email
                Mail::Send(
                    (int)Context::getContext()->language->id,
                    'mercanet_recurring_admin_error',
                    Translate::getModuleTranslation(
                        $module,
                        'A customer has error in his reccuring payment',
                        'MercanetNotification'
                    ),
                    $data,
                    Configuration::get('PS_SHOP_EMAIL'),
                    Configuration::get('PS_SHOP_EMAIL'),
                    null,
                    null,
                    null,
                    null,
                    _PS_MODULE_DIR_.'/mercanet/mails/'
                );
            }
        }

        MercanetLogger::log("($this->source) ".'end of updateRecurringSchedule');
    }

    private function deleteCurrentSpecificPrice($id_customer_payment_recurring)
    {
        if ((int)$id_customer_payment_recurring) {
            Db::getInstance()->execute(
                '
                DELETE FROM `'._DB_PREFIX_.'specific_price`
                WHERE `id_specific_price` = '.pSQL((int)$id_customer_payment_recurring)
            );
        }
        return false;
    }

    /**
     * Create Schedule Order
     */
    private function createScheduleOrder()
    {
        MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] beginning of createRecurringOrder()');
        
        MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] id_cart : '.$this->context->cart->id);
        
        //  Check if the cart is already in the loop
        /* if (MercanetOrderQueue::checkOrderCreated($this->context->cart->id, $this->source) == true) {
          return true;
          } */

        // Create order
        // Log
        $message = 'Data before payment ntimes validation => ';
        $message .= 'Customer: '.$this->context->customer->id.' '.$this->context->customer->firstname.' '.$this->context->customer->lastname;
        $message .= ' || ';
        $message .= ' Order: ';
        $message .= 'None';
        $message .= ' || ';
        $message .= ' URI: ';
        $message .= $_SERVER["REQUEST_URI"];
        $message .= ' || ';
        $message .= ' Params: ';
        $message .= implode(
            ', ',
            array_map(
                function ($v, $k) {
                    return $k.'='.$v;
                },
                $this->data,
                array_keys($this->data)
            )
        );

        MercanetLogger::log($message, MercanetLogger::LOG_INFO, MercanetLogger::FILE_DEBUG);
        $epsilon = 0.00001;
        // If payment success and same amount        
        if ($this->data['responseCode'] == '00' &&  (abs((float)$this->amount - (float)$this->context->cart->getOrderTotal()) < $epsilon)) {
            $status = (int)Configuration::getGlobalValue('MERCANET_NX_OS_PAYMENT');
        } else {
            $status = (int)_PS_OS_ERROR_;
        }
        
        if(!$this->context->cart->OrderExists()) {
            MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] order no exists. Let\'s call validateOrder');
            MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] id_cart : '.$this->context->cart->id.', $status : '.$status.', amount : '.(float)$this->amount.', payment_name : '.(string)$this->payment_name.', id_currency : '.$this->context->cart->id_currency.', secure_key : '.$this->context->customer->secure_key);
            
            $this->module->validateOrder(
                (int)$this->context->cart->id, $status, (float)$this->amount, (string)$this->payment_name,
                null, null, (int)$this->context->cart->id_currency, false, $this->context->customer->secure_key
            );
            MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] validateOrder finished');
            $id_order = MercanetApi::getOrderByCartId((int)$this->context->cart->id, (int)$this->context->cart->id_shop);
            MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] now id_order is '.$id_order);
            $this->context->order = new Order((int)$id_order);
        } else {
            $id_order = MercanetApi::getOrderByCartId((int)$this->context->cart->id, (int)$this->context->cart->id_shop);
            MercanetLogger::log("($this->source) ".'order already exists. id_order : '.$id_order);
            $this->context->order = new Order((int)$id_order);
            $this->context->order->current_state = $status;
            $this->context->order->payment =  $this->payment_name;
            $this->context->order->save();
            
            MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] we updated the order with state and payment_name. Now let\'s create an orderHistory');
            $history = new OrderHistory();
            $history->id_order = (int)$this->context->order->id;
            $history->changeIdOrderState($status, (int)($this->context->order->id));                     
            $carrier = new Carrier($this->context->order->id_carrier, $this->context->order->id_lang);
            $templateVars = array();
            if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $this->context->order->shipping_number) {
                $templateVars = array('{followup}' => str_replace('@', $this->context->order->shipping_number, $carrier->url));
            }
            $history->addWithemail(true, $templateVars);
        }
              
        $amounts_list = preg_split('@;@', $this->data['instalmentAmountsList'], null, PREG_SPLIT_NO_EMPTY);
        $this->amount = $amounts_list[0] / 100;
        if($status !==  (int)_PS_OS_ERROR_) {    
            $this->addPayment($this->amount);  
        }

        // Log
        $message = 'Data after payment ntimes validation => ';
        $message .= 'Customer: '.$this->context->customer->id.' '.$this->context->customer->firstname.' '.$this->context->customer->lastname;
        $message .= ' || ';
        $message .= ' Order: ';
        $message .= (!empty($id_order)) ? 'Yes: '.$id_order : 'None';
        $message .= ' || ';
        $message .= ' URI: ';
        $message .= $_SERVER["REQUEST_URI"];
        $message .= ' || ';
        $message .= ' Params: ';
        $message .= implode(
            ', ',
            array_map(
                function ($v, $k) {
                    return $k.'='.$v;
                },
                $this->data,
                array_keys($this->data)
            )
        );

        MercanetLogger::log($message, MercanetLogger::LOG_INFO, MercanetLogger::FILE_DEBUG);
        // Register Order Queue
        //MercanetOrderQueue::updateOrderQueue((int)$this->context->cart->id, (int)$this->context->order->id);
        // Register transaction
        MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] call registerTransaction()');
        $this->registerTransaction();

        // Register schedule
        MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] call addNewPrivateMessage()');
        $this->registerSchedule();

        // Add private message
        $this->addNewPrivateMessage();
        
        MercanetLogger::log("($this->source) ".'[WEBSERVICE][RECURRING] end of createRecurringOrder()');
    }

    /**
     * Create Recurring Schedule
     */
    protected function createRecurringSchedule()
    {
        $is_already_created = MercanetCustomerPaymentRecurring::isScheduleAlreadyCreated((int)$this->context->order->id);
        if ($is_already_created == false) {
            foreach ($this->context->cart->getProducts() as $product_cart) {
                $product_payment_recurring = MercanetPaymentRecurring::getPaymentRecurringByProductId((int)$product_cart['id_product']);
                // if payment is paused
                if (!empty($product_payment_recurring)) {
                    if ($product_payment_recurring['type'] == 2) {
                        $quantity = 0;
                        $id_pause_payment = MercanetCustomerPaymentRecurring::isPausedRecurringPayment(
                            (int)$this->context->customer->id,
                            (int)$product_cart['id_product'],
                            (int)$this->context->cart->id
                        );

                        $paused_recurring_payment = new MercanetCustomerPaymentRecurring((int)$id_pause_payment);
                        if ((int)$paused_recurring_payment->id) {
                            $qty = 1;
                            if ((int)$paused_recurring_payment->getLateRecurringOccurence()) {
                                $qty = (int)$paused_recurring_payment->getLateRecurringOccurence();
                            }
                            $paused_recurring_payment->current_occurence += (int)$qty;
                            $paused_recurring_payment->id_cart_paused_currency = 0;
                            $paused_recurring_payment->id_order = (int)$this->context->order->id;
                            $paused_recurring_payment->id_mercanet_transaction = (int)$this->transaction->id;
                            $last_schedule = new DateTime();
                            if ($paused_recurring_payment->isAnticipatedPayment()) {
                                $last_schedule = new DateTime($paused_recurring_payment->next_schedule);
                            }
                            $paused_recurring_payment->last_schedule = $last_schedule->format('Y-m-d h:m:s');
                            $next_schedule = $last_schedule->add(new DateInterval('P'.(int)$paused_recurring_payment->number_occurences.(string)$paused_recurring_payment->periodicity));
                            $paused_recurring_payment->next_schedule = $next_schedule->format('Y-m-d h:m:s');
                            $paused_recurring_payment->status = MercanetCustomerPaymentRecurring::ID_STATUS_ACTIVE;
                            $paused_recurring_payment->save();
                            $quantity++;
                        }
                        while ($quantity < $product_cart['cart_quantity']) {
                            $customer_payment_recurring = new MercanetCustomerPaymentRecurring();
                            $customer_payment_recurring->id_product = (int)$product_cart['id_product'];
                            $customer_payment_recurring->id_order = (int)$this->context->order->id;
                            $customer_payment_recurring->id_customer = (int)$this->context->customer->id;
                            $customer_payment_recurring->id_mercanet_transaction = (int)$this->transaction->id;
                            $customer_payment_recurring->id_tax_rules_group = (int)Product::getIdTaxRulesGroupByIdProduct((int)$product_cart['id_product'], $this->context);
                            $customer_payment_recurring->status = (int)MercanetCustomerPaymentRecurring::getStatus((int)MercanetCustomerPaymentRecurring::ID_STATUS_ACTIVE, true);
                            $periodicities = MercanetPaymentRecurring::getPeriodicities();

                            $customer_payment_recurring->periodicity = $periodicities[(int)$product_payment_recurring['periodicity']]['days'];
                            $customer_payment_recurring->amount_tax_exclude = (float)$product_cart['price_with_reduction_without_tax'];
                            if ((float)$product_payment_recurring['recurring_amount']) {
                                $customer_payment_recurring->amount_tax_exclude = (float)$product_payment_recurring['recurring_amount'];
                            }

                            $customer_payment_recurring->number_occurences = (int)$product_payment_recurring['number_occurences'];
                            $customer_payment_recurring->current_occurence = (int)1;
                            $date_schedule = new DateTime();
                            $customer_payment_recurring->date_add = $date_schedule->format('Y-m-d h:m:s');
                            $customer_payment_recurring->last_schedule = $date_schedule->format('Y-m-d h:m:s');
                            $next_schedule = $date_schedule->add(new DateInterval('P'.(int)$customer_payment_recurring->number_occurences.(string)$customer_payment_recurring->periodicity));
                            $customer_payment_recurring->next_schedule = $next_schedule->format('Y-m-d h:m:s');
                            $customer_payment_recurring->add();
                            $quantity++;
                        }
                    }
                }
            }
        }
    }

    /**
     * Register the transaction
     */
    protected function registerTransaction($transaction_type = null)
    {        
        MercanetLogger::log("($this->source) ".'begining of registerTransaction', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        MercanetLogger::log("($this->source) ".'$transaction_type : '.$transaction_type, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        
        if (empty($transaction_type)) {
            MercanetLogger::log("($this->source) ".'$transaction_type is null, set to default : '.MercanetTransaction::PAYMENT, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            $transaction_type = MercanetTransaction::PAYMENT;
        }
        
        MercanetLogger::log("($this->source) ".'transactionReference : '.$this->data['transactionReference'], MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        
        if ($transaction = !MercanetTransaction::getTransactionByReference($this->data['transactionReference'])) {
            MercanetLogger::log("($this->source) ".'can\'t load transaction '.$this->data['transactionReference'].'. So we create one.', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            $this->transaction = new MercanetTransaction();
            $this->transaction->id_order = (int)$this->context->order->id;
            $this->transaction->id_order_slip = (int)0;
            $this->transaction->id_order_recurring = (isset($this->data['orderRecurringId'])) ? $this->data['orderRecurringId'] : null;
            $this->transaction->authorisation_id = (isset($this->data['authorisationId'])) ? $this->data['authorisationId'] : null;
            $this->transaction->transaction_reference = $this->data['transactionReference'];
            $this->transaction->transaction_type = $transaction_type;
            $this->transaction->capture_mode = (string)$this->data['captureMode'];
            $this->transaction->masked_pan = (!empty($this->data['maskedPan'])) ? (string)$this->data['maskedPan'] : "";
            $this->transaction->amount = (float)$this->amount;
            $this->transaction->payment_mean_brand = (!empty($this->data['paymentMeanBrand'])) ? (string)$this->data['paymentMeanBrand'] : "N/A";
            $this->transaction->payment_mean_type =(!empty($this->data['paymentMeanType'])) ? (string)$this->data['paymentMeanType'] : "N/A";
            $this->transaction->transaction_date_time = $this->data['transactionDateTime'];
            $this->transaction->complementary_info = (isset($this->data['complementaryInfo'])) ? $this->data['complementaryInfo'] : null;
            $message = "";
            foreach ($this->data as $key => $value) {
                $message .= $key.': '.$value."<br>";
            }
            $this->transaction->raw_data = $message;

            MercanetLogger::log("($this->source) ".'transaction is about to be saved : '.MercanetLogger::transformArrayToString((array)$this->transaction), MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            $this->transaction->save();
            // History
            MercanetLogger::log("($this->source) ".'call registerTransactionHistory()', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            $this->registerTransactionHistory();
        } else {
            MercanetLogger::log("($this->source) ".'transaction '.$this->data['transactionReference'].' is loaded.', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            $this->transaction = new MercanetTransaction((int)$transaction['id_mercanet_transaction']);
            MercanetLogger::log("($this->source) ".'transaction : '.MercanetLogger::transformArrayToString((array)$this->transaction), MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        }
        
        MercanetLogger::log("($this->source) ".'end of registerTransaction', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
    }

    /**
     * Register the transaction
     */
    protected function registerSchedule()
    {
        $amounts_list = preg_split('@;@', $this->data['instalmentAmountsList'], null, PREG_SPLIT_NO_EMPTY);
        $dates_list = preg_split('@;@', $this->data['instalmentDatesList'], null, PREG_SPLIT_NO_EMPTY);
        $transactions_reference_list = preg_split('@;@', $this->data['instalmentTransactionReferencesList'], null, PREG_SPLIT_NO_EMPTY);

        for ($nb = 0; $nb <= ((int)$this->data['instalmentNumber'] - 1); $nb++) {
            if (!MercanetSchedule::isAlreadyRegistered($transactions_reference_list[$nb])) {
                $schedule = new MercanetSchedule();
                $schedule->id_order = (int)$this->context->order->id;
                $schedule->id_mercanet_transaction = ($nb == 0) ? (int)$this->transaction->id : null;
                $schedule->transaction_reference = $transactions_reference_list[$nb];
                $schedule->masked_pan = ($nb == 0) ? $this->data['maskedPan'] : null;
                $schedule->amount = (float)$amounts_list[$nb] / 100;
                $schedule->date_add = date('Y-m-d h:m:s');
                $date_capture = DateTime::createFromFormat('ymd', $dates_list[$nb]);
                $schedule->date_to_capture = $date_capture->format('Y-m-d');
                $schedule->date_capture = ($nb == 0) ? date('Y-m-d') : null;
                $schedule->captured = ($nb == 0 && $this->data['responseCode'] == '00') ? true : false;
                $schedule->state = ($nb == 0 && $this->data['responseCode'] == '00') ? 'Captured' : 'Waiting';
                $schedule->save();
            }
        }
    }

    protected function addPayment($amount = null)
    {
        if (empty($amount)) {
            $amount = (float)$this->data['amount'] / 100;
        }
        // Add payment
        if ($this->context->order->total_paid_tax_incl > $this->context->order->total_paid_real) {
            $currency = new Currency((int)$this->context->order->id_currency);
            $datetime = new DateTime();
            $transaction_reference = $this->data['transactionReference'];

            if (!$this->context->order->hasInvoice()) {
                $this->context->order->setInvoice(false);
            }

            $invoice = null;
            foreach ($this->context->order->getInvoicesCollection() as $inv) {
                if ($inv->isPaid()) {
                    continue;
                }
                $invoice = $inv;
                break;
            }

            $add_payment = true;
            foreach ($this->context->order->getOrderPayments() as $payment) {
                if ($payment->transaction_id == $transaction_reference) {
                    $add_payment = true;
                }
            }

            if ($add_payment == true) {
                $this->context->order->addOrderPayment(
                    $amount,
                    $this->payment_name,
                    $transaction_reference,
                    $currency,
                    $datetime->format('Y-m-d H:i:s'),
                    $invoice
                );
            }
        }
    }

    /**
     * Register the transaction history
     */
    protected function registerTransactionHistory()
    {
        if (empty($this->transaction->id)) {
            return false;
        }

        $history = new MercanetHistory();
        $history->id_mercanet_transaction = (int)$this->transaction->id;
        $history->id_mercanet_response_code = $this->data['responseCode'];
        if (isset($this->data['acquirerResponseCode'])) {
            $history->id_mercanet_acquirer_response_code = $this->data['acquirerResponseCode'];
        } else {
            $history->id_mercanet_acquirer_response_code = null;
        }

        if (isset($this->data['complementaryCode'])) {
            $history->id_mercanet_complementary_code = $this->data['complementaryCode'];
        } else {
            $history->id_mercanet_complementary_code = null;
        }
        $date_time = new DateTime();
        $history->date_add = $date_time->format('Y-m-d h:m:s');
        $history->save();
    }

    /**
     * Add private message to order
     * @param int $id_order
     * @return boolean
     */
    protected function addNewPrivateMessage()
    {
        if (!(bool)$this->context->order->id) {
            return false;
        }

        $translated = MercanetHistory::isTranslatedInThisIdLang((int)$this->context->language->id);

        if ($translated == true) {
            $id_lang = (int)$this->context->language->id;
        } else {
            $id_lang = Configuration::getGlobalValue('PS_LANG_DEFAULT');
        }

        // Normal Message
        $message = "BNP - Mercanet \n";
        $message .= MercanetResponseCode::getMessageByCode($this->data['responseCode'], (int)$id_lang)."\n";
        $message .= (isset($this->data['acquirerResponseCode'])) ? MercanetAcquirerResponseCode::getMessageByCode($this->data['acquirerResponseCode'], (int)$id_lang)."\n" : null;
        $message .= (isset($this->data['complementaryCode'])) ? MercanetComplementaryCode::getMessageByCode($this->data['complementaryCode'], (int)$id_lang)."\n" : null;

        $new_message = new Message();
        $message = strip_tags($message, '<br>');

        $new_message->message = $message;
        $new_message->id_order = (int)$this->context->order->id;
        $new_message->private = 1;

        return $new_message->add();
    }
}
