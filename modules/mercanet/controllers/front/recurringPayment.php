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

class MercanetRecurringPaymentModuleFrontController extends ModuleFrontController
{

    /** Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $payment_recurring_list = MercanetCustomerPaymentRecurring::getAllRecurringPayment($this->context->customer->id);
        if (is_array($payment_recurring_list)) {
            foreach ($payment_recurring_list as $key => $value) {
                $recurrent_payment = new MercanetCustomerPaymentRecurring($value['id_mercanet_customer_payment_recurring']);
                $payment_recurring_list[$key]['late'] = (int)$recurrent_payment->getLateRecurringOccurence();
                $payment_recurring_list[$key]['status'] = MercanetCustomerPaymentRecurring::getStatus($value['status']);
                $payment_recurring_list[$key]['can_change_card'] = false;
                if ($value['status'] == MercanetCustomerPaymentRecurring::ID_STATUS_PAUSE || $value['status'] == MercanetCustomerPaymentRecurring::ID_STATUS_ACTIVE) {
                    $payment_recurring_list[$key]['can_change_card'] = true;
                } else {
                    $payment_recurring_list[$key]['late'] = 0;
                }
                $payment_recurring_list[$key]['item_name'] = Product::getProductName($value['id_product']);
                $product = new Product($value['id_product']);
                $order = new Order($value['id_order']);
                $payment_recurring_list[$key]['item_reference'] = $order->reference;
                $payment_recurring_list[$key]['link_rewrite'] = $product->link_rewrite[$this->context->language->id];
                $payment_recurring_list[$key]['item_image'] = 0;
                $images = $product->getImages($this->context->language->id);
                if (is_array($images)) {
                    foreach ($images as $image) {
                        if ($image['cover'] == 1) {
                            $payment_recurring_list[$key]['item_image'] = $image['id_image'];
                            continue;
                        }
                    }
                }
            }
        }
        $can_stop = MercanetCustomerPaymentRecurring::hasOneInState($this->context->customer->id, MercanetCustomerPaymentRecurring::ID_STATUS_ACTIVE);
        $can_change_card = MercanetCustomerPaymentRecurring::hasOneInState($this->context->customer->id, MercanetCustomerPaymentRecurring::ID_STATUS_PAUSE);
        $link = new Link();
        $this->context->smarty->assign(array(
            'payment_recurring_list' => $payment_recurring_list,
            'url' => $link->getModuleLink('mercanet', 'recurringPayment'),
            'can_stop' => $can_stop,
            'can_change_card' => $can_change_card,
        ));
        $this->setTemplate('recurring_payment.tpl');
    }

    public function postProcess()
    {
        if (Tools::getValue('reorder')) {
            if ((int)Tools::getValue('id_recurring')) {
                // get the item recurring
                $mercanet_item_recurring = new MercanetCustomerPaymentRecurring((int)Tools::getValue('id_recurring'));
                if ((int)$mercanet_item_recurring->id) {
                    // Init Cart
                    $this->context->cart = new Cart();

                    // Mandatory
                    if (is_null($this->context->cart->id_lang)) {
                        $this->context->cart->id_lang = $this->context->language->id;
                    }

                    if (is_null($this->context->cart->id_currency)) {
                        $this->context->cart->id_currency = $this->context->currency->id;
                    }

                    if (is_null($this->context->cart->id_customer)) {
                        $this->context->cart->id_customer = $this->context->customer->id;
                    }


                    if (is_null($this->context->cart->id)) {
                        $this->context->cart->add();
                        $this->context->cookie->__set('id_cart', $this->context->cart->id);
                    }

                    // Add the product
                    $this->context->cart->updateQty(
                        (int)1,
                        (int)$mercanet_item_recurring->id_product,
                        null,
                        null,
                        'up',
                        null,
                        new Shop((int)$this->context->cart->id_shop),
                        false
                    );

                    $product = new Product((int)$mercanet_item_recurring->id_product);

                    $qty = 1;
                    if ((int)$mercanet_item_recurring->getLateRecurringOccurence()) {
                        $qty = (int)$mercanet_item_recurring->getLateRecurringOccurence();
                    }
                    
                    $specific_price = new SpecificPrice();
                    $specific_price->id_cart = (int)$this->context->cart->id;
                    $specific_price->id_shop = 0;
                    $specific_price->id_shop_group = 0;
                    $specific_price->id_currency = 0;
                    $specific_price->id_country = 0;
                    $specific_price->id_group = 0;
                    $specific_price->id_customer = (int)$this->context->customer->id;
                    $specific_price->id_product = (int)$mercanet_item_recurring->id_product;
                    $specific_price->id_product_attribute = 0;
                    $specific_price->price = (float)$mercanet_item_recurring->amount_tax_exclude * $qty;
                    $specific_price->from_quantity = 1;
                    $specific_price->reduction = 0;
                    $specific_price->reduction_type = 'amount';
                    $specific_price->from = '0000-00-00 00:00:00';
                    $specific_price->to = '0000-00-00 00:00:00';
                    $specific_price->add();


                    $this->context->cart->save();
                    $mercanet_item_recurring->current_specific_price = $specific_price->id;
                    $mercanet_item_recurring->id_cart_paused_currency = (int)$this->context->cart->id;
                    $mercanet_item_recurring->save();
                    $link = new Link();
                    Tools::redirect($link->getPageLink('order', true));
                }
            }
        }
        $conf = false;
        if (Tools::isSubmit('mercanet_stop_recurring')) {
            MercanetCustomerPaymentRecurring::StopAllRecurringPayment($this->context->customer->id);
            $conf = true;
        }
        $this->context->smarty->assign(array(
            'display_conf' => $conf,
        ));
    }
}
