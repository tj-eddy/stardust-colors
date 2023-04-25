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

class MercanetValidationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    private $transaction_reference = '';
    
    public function __construct()
    {
        $this->context = Context::getContext();
        
        parent::__construct();
    }

    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();
    }

    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */
    public function postProcess()
    {
        // initialize url data
        if (Tools::getIsset('transaction_reference')) {
            $this->transaction_reference = Tools::getValue('transaction_reference');
            MercanetLogger::log('transaction_reference is in url params : '.$this->transaction_reference.', so we retrieve the params from reference_payed table.', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            $raw_data = MercanetReferencePayed::getReceivedDataByOrderReference($this->transaction_reference);
            
            if (Tools::getIsset('base64')) {
                switch (Tools::getValue('base64')) {
                    case '1':
                        $raw_data = base64_decode($raw_data);
                        break;
                }
            }
            
            $data = MercanetApi::getDataFromRawData($raw_data);

            $urlReturnContext = $data['returnContext'];
            $urlCurrencyCode = $data['currencyCode']; // Not used here in PS 1.6
            $urlAuthorisationId = $data['authorisationId'];
            $urlResponseCode = $data['responseCode'];
        } else {
            MercanetLogger::log('transaction_reference is not in url params. It means the params are in url.', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            $urlReturnContext = Tools::getValue('returnContext');
            $urlCurrencyCode = (int)Tools::getValue('currencyCode'); // Not used here in PS 1.6
            $urlAuthorisationId = Tools::getValue('authorisationId');
            $urlResponseCode = Tools::getValue('responseCode');
        }
        
        MercanetLogger::log('ReturnContext : '.$urlReturnContext.', CurrencyCode : '.$urlCurrencyCode.', AuthorisationId : '.$urlAuthorisationId.', ResponseCode : '.$urlResponseCode, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        
        // If the module is not active anymore, no need to process anything.
        if ($this->module->active == false) {
            die;
        }
        // Init
        $link = new Link();

        // If the sealed has been changed or incorrect
        if (Tools::getIsset('is_sealed') && (bool)Tools::getValue('is_sealed') == false) {
            $this->context->smarty->assign(array(
                'link' => $link,
                'module_display_name' => (string)$this->module->displayName,
            ));

            $this->setTemplate('validation.tpl');
            return;
        }

        // Init Cart & Order
        $return_context = MercanetApi::getDataFromRawData($urlReturnContext, ',');

        $id_cart = $return_context['id_cart'];
        MercanetLogger::log('Instanciate Cart object from id_cart in ReturnContext', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        $cart = new Cart((int)$id_cart);
        
        if (Validate::isLoadedObject($cart)) {
            MercanetLogger::log('Cart object is valid : we put it in Context', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            $this->context->cart = $cart;
        } else {
            MercanetLogger::log('Cart object is NOT valid !', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            Tools::redirect('index.php');
        }
        
        // Si pas de Customer dans le context
        if (!(int)$this->context->customer->id > 0 && $this->transaction_reference !== '') {
            MercanetLogger::log('Customer id is NOT in Context : '.$this->context->customer->id, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            MercanetLogger::log('We gonna find id_order from transaction_reference', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            // On va chercher l'order
            $id_order = MercanetTransaction::getIdOrderFromTransactionReference($this->transaction_reference);
            MercanetLogger::log('id_order is : '.$id_order, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            if (!(int)$id_order > 0) {
                MercanetLogger::log('It seems id_order is not found...', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
                MercanetLogger::log('Maybe notification.php was called first and is creating order, then validation.php was called during the same time and redirect to validation controller but transaction does not exist yet. Waiting for 10 seconds and try again.', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
                sleep(10);
                $id_order = MercanetTransaction::getIdOrderFromTransactionReference($this->transaction_reference);
                MercanetLogger::log('id_order is : '.$id_order, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            }
            
            $order = new Order($id_order);
            
            if (Validate::isLoadedObject($order)) {
                MercanetLogger::log('Order object is valid', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            } else {
                MercanetLogger::log('Order object is NOT valid !', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
                MercanetLogger::log('Redirection to index.php', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
                Tools::redirect('index.php');
            }
            
            MercanetLogger::log('$cart->id_customer : '.$cart->id_customer.', $order->id_customer : '.$order->id_customer, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            // On compare si l'id_customer du Cart et de l'Order sont identiques
            if ($cart->id_customer == $order->id_customer) {
                MercanetLogger::log('id_customer are the same in Cart and Order : we force Customer login and put Customer object in Context.', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
                
                $this->context->customer = new Customer((int)$cart->id_customer);
                $this->context->customer->logged = 1;
                
                // Cookie
                $this->context->cookie->id_customer = intval($this->context->customer->id);
                $this->context->cookie->customer_lastname = $this->context->customer->lastname;
                $this->context->cookie->customer_firstname = $this->context->customer->firstname;
                $this->context->cookie->logged = 1;
                $this->context->cookie->passwd = $this->context->customer->passwd;
                $this->context->cookie->email = $this->context->customer->email;
                $this->context->cart->secure_key = $this->context->customer->secure_key;
                if (Configuration::get('PS_CART_FOLLOWING') AND (empty($this->context->cookie->id_cart) OR Cart::getNbProducts($this->context->cookie->id_cart) == 0)) {
                    $this->context->cookie->id_cart = intval(Cart::lastNoneOrderedCart(intval($this->context->customer->id)));
                }
            } else {
                MercanetLogger::log('id_customer are not the same in Cart and Order !', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            }
        } else {
            MercanetLogger::log('Customer id is in Context : '.$this->context->customer->id, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        }

        // Check if the customer is the real customer
        if (!$this->context->customer->isLogged(true) || $cart->id_customer != $this->context->customer->id) {
            MercanetLogger::log('Customer is NOT logged or $cart->id_customer != $this->context->customer->id', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            if (Configuration::get('MERCANET_CARD_DISPLAY_METHOD') == 'IFRAME') {
                MercanetLogger::log('IFRAME Mode. Set Template to validation-iframe.tpl', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
                $this->context->smarty->assign(array(
                    'link' => new Link(),
                    'redirect_index' => true,
                ));
                $this->setTemplate('validation-iframe.tpl');
            } else {
                MercanetLogger::log('Redirection to index.php', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
                Tools::redirect('index.php');
            }
            return;
        } else {
            MercanetLogger::log('Customer is logged and $cart->id_customer == $this->context->customer->id', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        }

        $id_order = Order::getOrderByCartId((int)$this->context->cart->id);
        // IMPORTANT FOR MULTISHOP
        if (empty($id_order)) {
            $order = new Order((int)MercanetApi::getOrderByCartId($id_cart, $cart->id_shop));
        } else {
            $order = new Order((int)$id_order);
        }
        $this->context->order = $order;

        // Restore the context to process the validation properly.
        MercanetLogger::log('Restore the context to process the validation properly', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        //Context::getContext()->cart = $cart;
        Context::getContext()->customer = new Customer((int)Context::getContext()->cart->id_customer);
        Context::getContext()->currency = new Currency((int)Context::getContext()->cart->id_currency);
        Context::getContext()->language = new Language((int)Context::getContext()->customer->id_lang);

        // Params to send to Order Confirmation
        $params = array(
            'id_cart' => (int)$this->context->cart->id,
            'id_module' => (int)$this->module->id,
            'id_order' => (int)$this->context->order->id,
            'key' => $this->context->customer->secure_key,
            'authorisation_id' => $urlAuthorisationId
        );

        if ($urlResponseCode == Configuration::getGlobalValue('MERCANET_CANCEL_RC')) {
            $url = $link->getPageLink('order', true);
            $redirect_order = true;
        } else {
            $url = $link->getPageLink('order-confirmation', true, (int)$this->context->customer->id_lang, $params, false, $order->id_shop);
            $redirect_order = false;
        }


        // If the Iframe is activated, redirect the parent
        if (Configuration::get('MERCANET_CARD_DISPLAY_METHOD') == 'IFRAME') {
            MercanetLogger::log('IFRAME Mode. Set Template to validation-iframe.tpl', MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            $this->context->smarty->assign(array(
                'params' => $params,
                'link' => new Link(),
                'id_lang' => (int)$this->context->customer->id_lang,
                'id_shop' => (int)$order->id_shop,
                'url' => $url,
                'redirect_order' => $redirect_order,
            ));
            $this->setTemplate('validation-iframe.tpl');
            return;
        } else {
            MercanetLogger::log('Redirect to : '.$url, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            Tools::redirectLink($url);
        }
    }
}
