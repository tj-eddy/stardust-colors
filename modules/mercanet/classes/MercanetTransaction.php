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

class MercanetTransaction extends ObjectModel
{
    const PAYMENT = 'PAYMENT';
    const REFUND = 'REFUND';
    const CANCEL = 'CANCEL';
    const ANTICIPATE_REFUND = 'ANTICIPATE_REFUND';

    /**
     * ID
     * @var integer
     */
    public $id_mercanet_transaction;

    /**
     * ID Order
     * @var integer
     */
    public $id_order;

    /**
     * ID Order Slip
     * @var integer
     */
    public $id_order_slip;

    /**
     * ID Order Recurring
     * @var integer
     */
    public $id_order_recurring;

    /**
     * Authorisation ID
     * @var integer
     */
    public $authorisation_id;

    /**
     * Transaction Reference
     * @var string
     */
    public $transaction_reference;

    /**
     * Transaction Type
     * @var string
     */
    public $transaction_type;

    /**
     * Capture Mode
     * @var string
     */
    public $capture_mode;

    /**
     * Card number masked
     * @var string
     */
    public $masked_pan;

    /**
     * Amount of the transaction
     * @var integer
     */
    public $amount;

    /**
     * Payment brand (VISA, MASTERCARD, etc)
     * @var float
     */
    public $payment_mean_brand;

    /**
     * Payment type (CARD, WALLET, etc)
     * @var string
     */
    public $payment_mean_type;

    /**
     * Date time of the transaction
     * @var date
     */
    public $transaction_date_time;

    /**
     * Raw data
     * @var text
     */
    public $raw_data;

    /**
     * Complementary info
     * @var text
     */
    public $complementary_info;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_transaction',
        'primary' => 'id_mercanet_transaction',
        'fields' => array(
            'id_order' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true
            ),
            'id_order_recurring' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false
            ),
            'id_order_slip' => array(
                'type' => self::TYPE_INT,
                'required' => false
            ),
            'authorisation_id' => array(
                'type' => self::TYPE_STRING,
                'required' => false
            ),
            'transaction_reference' => array(
                'type' => self :: TYPE_STRING,
                'required' => true,
            ),
            'transaction_type' => array(
                'type' => self :: TYPE_STRING,
                'required' => true,
            ),
            'capture_mode' => array(
                'type' => self :: TYPE_STRING,
                'required' => false,
            ),
            'amount' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true
            ),
            'masked_pan' => array(
                'type' => self :: TYPE_STRING,
                'required' => false,
            ),
            'payment_mean_brand' => array(
                'type' => self :: TYPE_STRING,
                'required' => true,
            ),
            'payment_mean_type' => array(
                'type' => self :: TYPE_STRING,
                'required' => true,
            ),
            'transaction_date_time' => array(
                'type' => self :: TYPE_DATE,
                'required' => false,
            ),
            'complementary_info' => array(
                'type' => self :: TYPE_HTML,
                'required' => false,
            ),
            'raw_data' => array(
                'type' => self :: TYPE_HTML,
                'required' => true,
            )
        )
    );

    /**
     * Return the transaction for an order id
     * @param int $id_order
     * @return boolean|array
     */
    public static function getTransactionByOrderId($id_order)
    {
        if (empty($id_order)) {
            return false;
        }

        return Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'mercanet_transaction`
			WHERE `id_order` = "'.pSQL((int)$id_order).'"
		');
    }

    /**
     * Return the transaction for a reference
     * @param string $reference
     * @return boolean|array
     */
    public static function getTransactionByReference($reference)
    {
        if (empty($reference)) {
            return false;
        }

        return Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'mercanet_transaction`
			WHERE transaction_reference = "'.pSQL($reference).'"
		');
    }

    /**
     * Return the order slip refundable
     * @param int $id_order
     * @param int $id_customer
     * @return boolean|array
     */
    public static function getOrderRefundableSlip($id_order, $id_customer)
    {
        if (!Validate::isUnsignedId($id_order) || !Validate::isUnsignedId($id_customer)) {
            return false;
        }

        return Db::getInstance()->executeS('
			SELECT *, id_order_slip as transaction_id,id_order_slip as refundable_transaction
			FROM `'._DB_PREFIX_.'order_slip`
			WHERE `id_customer` = '.(int)$id_customer.'
			AND `id_order` = '.(int)$id_order.'
			ORDER BY `date_add` DESC
		');
    }

    /**
     * Get refundable transaction for the order
     * @param string $reference
     * @return array|boolean
     */
    public static function getOrderRefundableTransaction($reference)
    {
        return Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'mercanet_transaction`
			WHERE `id_order` IN (
				SELECT `id_order`
				FROM `'._DB_PREFIX_.'orders`
				WHERE `reference` = \''.pSQL($reference).'\'
			)
            AND transaction_type = "'.pSQL(self::PAYMENT).'"
            AND transaction_reference NOT IN (
                SELECT mt2.`transaction_reference`
				FROM `'._DB_PREFIX_.'mercanet_transaction` mt2
				WHERE mt2.`transaction_reference` = transaction_reference
                AND mt2.id_order_slip
            )
		');
    }
    
    public static function getIdOrderFromTransactionReference($reference)
    {
        if (empty($reference)) {
            return false;
        }
        
        $sql = 'SELECT `id_order` FROM `'._DB_PREFIX_.'mercanet_transaction` WHERE `transaction_reference` = "'.pSQL($reference).'"';
        
        MercanetLogger::log($sql, MercanetLogger::LOG_INFO, MercanetLogger::FILE_DEBUG);
        
        return Db::getInstance()->getValue($sql);
    }

    /**
     * Specific transaction for WS
     * @param type $order
     * @param type $transaction
     * @param type $result
     * @param type $transaction_type
     * @param type $slip
     */
    public static function registerWebserviceTransaction($order, $transaction, $result, $transaction_type, $schedule_transaction_reference = null, $amount = null, $id_order_slip = null)
    {
        $new_transaction = new MercanetTransaction();
        $new_transaction->id_order = (int)$order->id;
        $new_transaction->id_order_slip = (!empty($id_order_slip)) ? (int)$id_order_slip : 0;
        $new_transaction->authorisation_id = (isset($result->authorisationId)) ? $result->authorisationId : null;
        $new_transaction->transaction_reference = (empty($schedule_transaction_reference)) ? $transaction->transaction_reference : $schedule_transaction_reference;
        $new_transaction->transaction_type = $transaction_type;
        $new_transaction->capture_mode = (isset($result->captureMode)) ? $result->captureMode : null;
        $new_transaction->masked_pan = $transaction->masked_pan;
        $new_transaction->amount = (!empty($amount)) ? (float)$amount : 0;
        $new_transaction->payment_mean_brand = (string)$transaction->payment_mean_brand;
        $new_transaction->payment_mean_type = (string)$transaction->payment_mean_type;
        $new_transaction->transaction_date_time = (isset($result->operationDateTime)) ? $result->operationDateTime : date('Y-m-d h:m:s');
        $new_transaction->complementary_info = (isset($result->complementaryInfo)) ? $result->complementaryInfo : null;
        $message = "";
        foreach ($result as $key => $value) {
            $message .= $key.': '.$value."<br>";
        }
        $new_transaction->raw_data = $message;
        $new_transaction->save();
        // Register History Transaction
        $history = new MercanetHistory();
        $history->id_mercanet_transaction = (int)$new_transaction->id;
        $history->id_mercanet_response_code = $result->responseCode;
        if (isset($result->acquirerResponseCode)) {
            $history->id_mercanet_acquirer_response_code = $result->acquirerResponseCode;
        } else {
            $history->id_mercanet_acquirer_response_code = null;
        }

        if (isset($result->complementaryCode)) {
            $history->id_mercanet_complementary_code = $result->complementaryCode;
        } else {
            $history->id_mercanet_complementary_code = null;
        }
        $date_time = new DateTime();
        $history->date_add = $date_time->format('Y-m-d h:m:s');
        $history->save();
    }
}
