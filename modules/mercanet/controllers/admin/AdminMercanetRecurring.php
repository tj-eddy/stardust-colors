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

class AdminMercanetRecurringController extends ModuleAdminController
{

    public function __construct()
    {
        $this->module = 'mercanet';
        $this->table = 'mercanet_customer_payment_recurring';
        $this->className = 'MercanetCustomerPaymentRecurring';
        $this->bootstrap = true;
        $this->add = false;
        $this->addRowAction('edit');
        $this->list_no_link = false;
        $this->tab_modules_list = false;
        parent::__construct();
        $this->context = Context::getContext();

        $list_status = array(
            1 => $this->l('Active'),
            2 => $this->l('Paused'),
            3 => $this->l('Expired'),
        );
        $this->link = new Link();
        $this->fields_list = array(
            'id_mercanet_customer_payment_recurring' => array(
                'title' => $this->l('Id'),
                'align' => 'left',
                'type' => 'int',
                'width' => 30,
            ),
            'id_product' => array(
                'title' => $this->l('Product ID'),
                'align' => 'center',
                'type' => 'int',
                'width' => 128,
                'callback' => 'getProductLink',
                'search' => false,
            ),
            'id_order' => array(
                'title' => $this->l('Order ID'),
                'align' => 'center',
                'type' => 'int',
                'width' => 64,
                'search' => false,
                'callback' => 'getOrderLink'
            ),
            'id_customer' => array(
                'title' => $this->l('Customer ID'),
                'align' => 'center',
                'type' => 'string',
                'width' => 64,
                'search' => false,
                'callback' => 'getCustomerLink'
            ),
            'amount_tax_exclude' => array(
                'title' => $this->l('Amount Tax excluded'),
                'align' => 'center',
                'type' => 'price',
                'search' => false,
                'width' => 64,
            ),
            'status' => array(
                'filter_key' => 'status',
                'type' => 'select',
                'list' => $list_status,
                'filter_type' => 'int',
                'title' => $this->l('Status'),
                'align' => 'center',
                'callback' => 'getStatusLabel'
            ),
            'next_schedule' => array(
                'title' => $this->l('Next schedule'),
                'align' => 'center',
                'type' => 'date',
                'width' => 64,
            ),
        );
        $this->bulk_actions = array(
            'setpause' => array(
                'text' => $this->l('Pause the recurred payment'),
                'confirm' => $this->l('Change state for the selected items ?')
            ),
            'setstop' => array(
                'text' => $this->l('Stop the recurred payment'),
                'confirm' => $this->l('Change state for the selected items ?')
            ),
            'setactive' => array(
                'text' => $this->l('Active the recurred payment'),
                'confirm' => $this->l('Change state for the selected items ?')
            ),
        );
    }

    /**
     * Return the order link
     * @param type $id_order
     * @param type $row
     * @return type
     */
    public function getOrderLink($id_order)
    {
        $order = new Order((int)$id_order);
        return '<a href="'.$this->link->getAdminLink('AdminOrders').'&vieworder&id_order='.(int)$order->id.'">'.$order->reference.'</a>';
    }

    /**
     * Return the customer link
     * @param type $id_customer
     * @param type $row
     * @return type
     */
    public function getCustomerLink($id_customer)
    {
        $customer = new Customer((int)$id_customer);
        return '<a href="'.$this->link->getAdminLink('AdminCustomers').'&viewcustomer&id_customer='.(int)$customer->id.'">'.$customer->lastname.' '.$customer->firstname.'</a>';
    }

    /**
     * Return the product link
     * @param type $id_product
     * @param type $row
     * @return type
     */
    public function getProductLink($id_product)
    {
        $product_name = Product::getProductName($id_product);
        return '<a href="'.$this->link->getAdminLink('AdminProducts').'&updateproduct&id_product='.(int)$id_product.'">'.$product_name.'</a>';
    }

    /**
     * Return the response message
     * @param type $code
     * @param type $row
     * @return type
     */
    public function getStatusLabel($status)
    {
        $label = MercanetCustomerPaymentRecurring::getStatus($status);
        if ($label) {
            return $label['name'];
        }
        return false;
    }

    public function renderForm()
    {
        $list_status = $this->getStatusList();
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Mercanet Recurring'),
                'icon' => 'icon-file'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'label' => 'id',
                    'name' => 'id_mercanet_customer_payment_recurring'
                ),
                array(
                    'name' => 'status',
                    'type' => 'select',
                    'options' => array(
                        'query' => $list_status,
                        'id' => 'status',
                        'name' => 'label'
                    ),
                    'label' => $this->l('State'),
                    'desc' => $this->l('Change state')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default'
            )
        );
        return parent::renderForm();
    }

    public function renderList()
    {
        return parent::renderList();
    }

    public function postProcess()
    {
        // Bulk actions
        if (Tools::isSubmit('submitBulksetpause'.$this->table)) {
            $array_id = Tools::getValue($this->table.'Box');
            if ($array_id) {
                foreach ($array_id as $element) {
                    $recurring_payment = new MercanetCustomerPaymentRecurring((int)$element);
                    $recurring_payment->status = 2;
                    $recurring_payment->save();
                }
            }
        }

        if (Tools::isSubmit('submitBulksetactive'.$this->table)) {
            $array_id = Tools::getValue($this->table.'Box');
            if ($array_id) {
                foreach ($array_id as $element) {
                    $recurring_payment = new MercanetCustomerPaymentRecurring((int)$element);
                    $recurring_payment->status = 1;
                    $recurring_payment->save();
                }
            }
        }

        if (Tools::isSubmit('submitBulksetstop'.$this->table)) {
            $array_id = Tools::getValue($this->table.'Box');
            if ($array_id) {
                foreach ($array_id as $element) {
                    $recurring_payment = new MercanetCustomerPaymentRecurring((int)$element);
                    $recurring_payment->status = 3;
                    $recurring_payment->save();
                    $module = Module::getInstanceByName('mercanet');
                    $customer = new Customer((int)$recurring_payment->id_customer);
                    $order = new Order((int)$recurring_payment->id_order);
                    if (Validate::isEmail($customer->email)) {
                        $data = array(
                            '{firstname}' => $customer->firstname,
                            '{lastname}' => $customer->lastname,
                            '{email}' => $customer->email,
                            '{customer_id}' => $customer->id,
                            '{order_reference}' => $order->reference,
                        );
                        Mail::Send(
                            (int)Context::getContext()->language->id,
                            'mercanet_payment_recurring_stop_bo',
                            Translate::getModuleTranslation(
                                $module,
                                $this->l('Your reccuring payment has been stopped'),
                                'MercanetCustomerPaymentRecurring'
                            ),
                            $data,
                            $customer->email,
                            $customer->email,
                            null,
                            null,
                            null,
                            null,
                            _PS_MODULE_DIR_.'/mercanet/mails/'
                        );
                    }
                }
            }
        }
        if (Tools::getValue('submitAddmercanet_customer_payment_recurring')) {
            if (Tools::getValue('status') && Tools::getValue('status') == 3) {
                $recurring_payment = new MercanetCustomerPaymentRecurring((int)Tools::getValue('id_mercanet_customer_payment_recurring'));
                if ($recurring_payment->id) {
                    $module = Module::getInstanceByName('mercanet');
                    $customer = new Customer((int)$recurring_payment->id_customer);
                    $order = new Order((int)$recurring_payment->id_order);
                    if (Validate::isEmail($customer->email)) {
                        $data = array(
                            '{firstname}' => $customer->firstname,
                            '{lastname}' => $customer->lastname,
                            '{email}' => $customer->email,
                            '{customer_id}' => $customer->id,
                            '{order_reference}' => $order->reference,
                        );
                        Mail::Send(
                            (int)Context::getContext()->language->id,
                            'mercanet_payment_recurring_stop_bo',
                            Translate::getModuleTranslation(
                                $module,
                                $this->l('Your reccuring payment has been stopped'),
                                'MercanetCustomerPaymentRecurring'
                            ),
                            $data,
                            $customer->email,
                            $customer->email,
                            null,
                            null,
                            null,
                            null,
                            _PS_MODULE_DIR_.'/mercanet/mails/'
                        );
                    }
                }
            }
        }
        return parent::postProcess();
    }

    /**
     * No Toolbar
     * @return type
     */
    public function initToolbar()
    {
        return false;
    }

    public function getStatusList()
    {
        $list_status = array();
        $list_status[0]['status'] = 1;
        $list_status[0]['label'] = $this->l('Active');
        $list_status[1]['status'] = 2;
        $list_status[1]['label'] = $this->l('Paused');
        $list_status[2]['status'] = 3;
        $list_status[2]['label'] = $this->l('Expired');

        return $list_status;
    }
}
