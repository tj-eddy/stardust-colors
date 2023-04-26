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
class OrderState extends OrderStateCore
{
    /**
     * Module: Mercanet
     * Prevent to create others payments on change statut order if the order isn't totally paid
     * @param int $id
     * @param int $id_lang
     */
    /*
    * module: mercanet
    * date: 2023-04-26 07:52:47
    * version: 1.6.12
    */
    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
        if (Tools::getValue('id_order') && $this->paid == 1 && Tools::getValue('controller') == 'AdminOrders') {
            $order = new Order(Tools::getValue('id_order'));
            if ($order->module == 'mercanet' && self::getScheduleByOrderId((int)$order->id)) {
                $invoices = $order->getInvoicesCollection();
                foreach ($invoices as $invoice) {
                    $rest = $invoice->getRestPaid();
                    if ($rest > 0) {
                        $this->paid = 0;
                    }
                }
            }
        }
    }
    /**
     * Return true of false if the order have schedule
     * @param int $id_order
     * @return boolean
     */
    /*
    * module: mercanet
    * date: 2023-04-26 07:52:47
    * version: 1.6.12
    */
    public static function getScheduleByOrderId($id_order)
    {
        if (empty($id_order)) {
            return false;
        }
        $result = Db::getInstance()->getRow('
			SELECT ms.*, mt.payment_mean_brand
			FROM `'._DB_PREFIX_.'mercanet_schedule` ms
			LEFT JOIN `'._DB_PREFIX_.'mercanet_transaction` mt ON (mt.`id_mercanet_transaction` = ms.`id_mercanet_transaction`)
			WHERE ms.`id_order` = "'.pSQL((int)$id_order).'"
            ORDER BY ms.date_capture ASC
		');
        if (!empty($result)) {
            return true;
        }
        return false;
    }
}
