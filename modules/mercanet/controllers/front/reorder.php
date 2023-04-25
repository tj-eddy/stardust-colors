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

class MercanetReorderModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        if (!$this->context->customer->isLogged(true)) {
            Tools::redirect('index.php');
        }
        parent::initContent();
    }

    /**
     * PostProcess
     */
    public function postProcess()
    {
        // If the cart is not empty, we redirect to order STEP
        $products = $this->context->cart->getProducts();
        if (!empty($products)) {
            Tools::redirect('index.php?controller=order&step=0');
        }
        // Reorder
        $order = new Order((int)Tools::getValue('id_order'));
        $this->reorder((int)$order->id, (int)$order->id_customer);
        // Redirect into order
        Tools::redirect('index.php?controller=order&step=0');
    }

    /**
     * Reorder an order
     * @param integer $id_order
     * @param integer $id_customer
     * @return boolean
     */
    protected function reorder($id_order, $id_customer)
    {
        if (!(bool)$id_order || !(bool)$id_customer) {
            return false;
        }
        $old_cart = new Cart(Order::getCartIdStatic($id_order, $id_customer));

        if ($old_cart->id_customer != Context::getContext()->customer->id) {
            return true;
        }

        $duplication = $old_cart->duplicate();
        if (!$duplication || !Validate::isLoadedObject($duplication['cart'])) {
            $this->errors[] = Tools::displayError('Sorry. We cannot renew your order.');
        } elseif (!$duplication['success']) {
            $this->errors[] = Tools::displayError('Some items are no longer available, and we are unable to renew your order.');
        } else {
            $this->context->cookie->id_cart = $duplication['cart']->id;
            $this->context->cookie->write();
        }
    }
}
