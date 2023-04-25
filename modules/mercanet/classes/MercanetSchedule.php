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

class MercanetSchedule extends ObjectModel
{
    /**
     * ID
     * @var integer
     */
    public $id_mercanet_schedule;

    /**
     * ID Order
     * @var integer
     */
    public $id_order;

    /**
     * Transaction ID
     * @var integer
     */
    public $id_mercanet_transaction;

    /**
     * Transaction Reference
     * @var string
     */
    public $transaction_reference;

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
     * Date add
     * @var datetime
     */
    public $date_add;

    /**
     * Date Capture
     * @var date
     */
    public $date_capture;

    /**
     * Date Capture
     * @var date
     */
    public $date_to_capture;

    /**
     * Captured
     * @var bool
     */
    public $captured;

    /**
     * State
     * @var bool
     */
    public $state;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_schedule',
        'primary' => 'id_mercanet_schedule',
        'fields' => array(
            'id_order' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true
            ),
            'id_mercanet_transaction' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false
            ),
            'transaction_reference' => array(
                'type' => self :: TYPE_STRING,
                'required' => true,
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
            'date_add' => array(
                'type' => self :: TYPE_DATE,
                'required' => false,
            ),
            'date_to_capture' => array(
                'type' => self :: TYPE_DATE,
                'required' => true,
            ),
            'date_capture' => array(
                'type' => self :: TYPE_DATE,
                'required' => false,
            ),
            'captured' => array(
                'type' => self :: TYPE_BOOL,
                'required' => false,
            ),
            'state' => array(
                'type' => self :: TYPE_STRING,
                'required' => false,
            ),
        )
    );

    /**
     * Return the schedule for a reference
     * @param int $id_order
     * @return boolean | array
     */
    public static function getScheduleByOrderId($id_order)
    {
        if (empty($id_order)) {
            return false;
        }
        return Db::getInstance()->executeS('
			SELECT ms.*, mt.payment_mean_brand
			FROM `'._DB_PREFIX_.'mercanet_schedule` ms
			LEFT JOIN `'._DB_PREFIX_.'mercanet_transaction` mt ON (mt.`id_mercanet_transaction` = ms.`id_mercanet_transaction`)
			WHERE ms.`id_order` = "'.pSQL((int)$id_order).'"
            ORDER BY ms.date_to_capture ASC
		');
    }

    /**
     * Return the schedule for a reference
     * @param string $transaction_reference
     * @return boolean | array
     */
    public static function getScheduleByTransactionReference($transaction_reference)
    {
        if (empty($transaction_reference)) {
            return false;
        }

        return Db::getInstance()->getValue('
			SELECT ms.id_mercanet_schedule
			FROM `'._DB_PREFIX_.'mercanet_schedule` ms
			WHERE ms.`transaction_reference` = "'.pSQL($transaction_reference).'"
            ORDER BY ms.date_to_capture ASC
		');
    }

    /**
     * Check if the schedule already exist for a reference
     * @param string $reference
     * @return boolean | array
     */
    public static function isAlreadyRegistered($reference)
    {
        if (empty($reference)) {
            return false;
        }

        $result = Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'mercanet_schedule`
			WHERE transaction_reference = "'.pSQL($reference).'"
		');

        if (!empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * Return the schedule left by Order ID
     * @param int $id_order
     * @return array
     */
    public static function getSchedulesLeftByOrderId($id_order)
    {
        return Db::getInstance()->executeS('
            SELECT *
            FROM `'._DB_PREFIX_.'mercanet_schedule`
            WHERE id_order = '.pSQL((int)$id_order).'
            AND !captured
        ');
    }

    public static function updateWebserviceScheduleAmount($transaction_reference, $new_amount)
    {

        $id_schedule = MercanetSchedule::getScheduleByTransactionReference($transaction_reference);
        if (empty($id_schedule)) {
            return false;
        }

        $schedule = new MercanetSchedule((int)$id_schedule);

        $schedule->amount = (float)$new_amount / 100;
        $schedule->save();
    }
}
