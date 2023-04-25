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

class AdminMercanettransactionController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = 'mercanet';
        $this->table = 'mercanet_transaction';
        $this->className = 'MercanetTransaction';
        $this->bootstrap = true;
        $this->add = false;
        $this->list_no_link = false;
        $this->tab_modules_list = false;
        parent::__construct();
        $this->context = Context::getContext();
        $this->_select = ' mh.*';
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'mercanet_history` mh ON (mh.`id_mercanet_transaction` = a.`id_mercanet_transaction`)';
        $this->link = new Link();
        $this->fields_list = array(
            'id_mercanet_transaction' => array(
                'title' => $this->l('Id'),
                'align' => 'left',
                'type' => 'int',
                'width' => 30,
            ),
            'id_order' => array(
                'title' => $this->module->l('Order ID'),
                'align' => 'center',
                'type' => 'int',
                'width' => 128,
                'callback' => 'getOrderLink'
            ),
            'authorisation_id' => array(
                'title' => $this->module->l('Authorisation ID'),
                'align' => 'center',
                'type' => 'string',
                'width' => 64,
            ),
            'capture_mode' => array(
                'title' => $this->module->l('Capture Mode'),
                'align' => 'center',
                'type' => 'string',
                'width' => 64,
            ),
            'masked_pan' => array(
                'title' => $this->module->l('Card'),
                'align' => 'center',
                'type' => 'string',
                'width' => 64,
            ),
            'amount' => array(
                'title' => $this->module->l('Amount'),
                'align' => 'center',
                'type' => 'float',
                'width' => 64,
            ),
            'payment_mean_brand' => array(
                'title' => $this->module->l('Payment Mean Brand'),
                'align' => 'center',
                'type' => 'string',
                'width' => 64,
            ),
            'payment_mean_type' => array(
                'title' => $this->module->l('Payment Mean Type'),
                'align' => 'center',
                'type' => 'string',
                'width' => 64,
            ),
            'transaction_date_time' => array(
                'title' => $this->module->l('Transaction Date'),
                'align' => 'center',
                'type' => 'date',
            ),
            'id_mercanet_response_code' => array(
                'title' => $this->module->l('Response'),
                'align' => 'left',
                'type' => 'string',
                'width' => 140,
                'callback' => 'getResponseCode'
            ),
            'id_mercanet_acquirer_response_code' => array(
                'title' => $this->module->l('Acquirer Response'),
                'align' => 'left',
                'type' => 'string',
                'width' => 140,
                'callback' => 'getAcquirerResponseCode'
            ),
            'id_mercanet_complementary_code' => array(
                'title' => $this->module->l('Complementary Response'),
                'align' => 'left',
                'type' => 'string',
                'width' => 140,
                'callback' => 'getComplementaryCode'
            ),
            /* 'raw_data' => array(
              'title' => $this->module->l('Raw Data'),
              'align' => 'center',
              'type' => 'string',
              'width' => 64,
              ), */
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
     * Return the response message
     * @param type $code
     * @param type $row
     * @return type
     */
    public function getResponseCode($code)
    {
        return $code.' -'.MercanetResponseCode::getMessageByCode($code);
    }

    /**
     * Return the response message
     * @param type $code
     * @param type $row
     * @return type
     */
    public function getAcquirerResponseCode($code)
    {
        return $code.' -'.MercanetAcquirerResponseCode::getMessageByCode($code);
    }

    /**
     * Return the response message
     * @param type $code
     * @param type $row
     * @return type
     */
    public function getComplementaryCode($code)
    {
        return $code.' -'.MercanetComplementaryCode::getMessageByCode($code);
    }

    /**
     * No Toolbar
     * @return type
     */
    public function initToolbar()
    {
        return false;
    }
}
