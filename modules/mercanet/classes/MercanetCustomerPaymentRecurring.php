<?php
/**
 * 1961-2017 BNP Paribas
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1961-2017 BNP Paribas
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MercanetCustomerPaymentRecurring extends ObjectModel
{

    const ID_STATUS_ACTIVE = 1;
    const ID_STATUS_PAUSE = 2;
    const ID_STATUS_EXPIRED = 3;

    /**
     * ID
     * @var integer
     */
    public $id_mercanet_customer_payment_recurring;

    /**
     * ID PRODUCT
     * @var integer
     */
    public $id_product;

    /**
     * ID TAX RULES GROUP
     * @var integer
     */
    public $id_tax_rules_group;

    /**
     * ID ORDER
     * @var integer
     */
    public $id_order;

    /**
     * ID CUSTOMER
     * @var integer
     */
    public $id_customer;

    /**
     * ID MERCANET TRANSACTION
     * @var int
     */
    public $id_mercanet_transaction;

    /**
     * STATUS
     * @var integer
     */
    public $status;

    /**
     * PRICE TAX EXCLUDE
     * @var integer
     */
    public $amount_tax_exclude;

    /**
     * NUMBER OF OCCURENCES
     * @var integer
     */
    public $number_occurences;

    /**
     * CURRENT OCCURENCE
     * @var float
     */
    public $current_occurence;

    /**
     * PERIODICITY
     * @var string
     */
    public $periodicity;

    /**
     * DATE ADD
     * @var date
     */
    public $date_add;

    /**
     * LAST SCHEDULE
     * @var date
     */
    public $last_schedule;

    /**
     * LAST SCHEDULE
     * @var date
     */
    public $next_schedule;

    /**
     * id current used specific price
     * @var integer
     */
    public $current_specific_price = 0;

    /**
     * id cart for reorder paused currency
     * @var integer
     */
    public $id_cart_paused_currency = 0;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_customer_payment_recurring',
        'primary' => 'id_mercanet_customer_payment_recurring',
        'multilang' => false,
        'fields' => array(
            'id_product' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'id_tax_rules_group' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'id_order' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'id_customer' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'id_mercanet_transaction' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'status' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'amount_tax_exclude' => array(
                'type' => self::TYPE_FLOAT,
                'required' => true,
            ),
            'number_occurences' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'current_occurence' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'periodicity' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
            ),
            'date_add' => array(
                'type' => self :: TYPE_DATE,
                'required' => false,
            ),
            'last_schedule' => array(
                'type' => self :: TYPE_DATE,
                'required' => false,
            ),
            'next_schedule' => array(
                'type' => self :: TYPE_DATE,
                'required' => false,
            ),
            'current_specific_price' => array(
                'type' => self :: TYPE_INT,
                'required' => false,
            ),
            'id_cart_paused_currency' => array(
                'type' => self :: TYPE_INT,
                'required' => false,
            )
        )
    );

    /**
     * Check if Schedule exist
     */
    public static function isScheduleAlreadyCreated($id_order)
    {
        $result = Db::getInstance()->getRow(
            'SELECT `id_mercanet_customer_payment_recurring`
            FROM `'._DB_PREFIX_.'mercanet_customer_payment_recurring`
            WHERE `id_order` = '.pSQL((int)$id_order)
        );

        if (!empty($result)) {
            return true;
        }

        return false;
    }

    public static function getScheduleByOrderId($id_order)
    {
        return Db::getInstance()->executeS(
            '
            SELECT *
            FROM `'._DB_PREFIX_.'mercanet_transaction` mt
            LEFT JOIN `'._DB_PREFIX_.'mercanet_customer_payment_recurring` mcpr ON (mcpr.`id_order` = mt.`id_order` OR mcpr.`id_order` = mt.`id_order_recurring`)
            WHERE mt.`id_order` = '.pSQL((int)$id_order)
        );
    }

    public static function getScheduleFormattedByOrderId($id_order)
    {
        $schedules = self::getScheduleByOrderId((int)$id_order);
        if (empty($schedules)) {
            return false;
        }

        $status = MercanetCustomerPaymentRecurring::getStatus();
        $periodicities = MercanetPaymentRecurring::getPeriodicities();

        foreach ($schedules as $id_schedule => $schedule) {
            if (empty($schedule['id_order'])) {
                unset($schedules[(int)$id_schedule]);
                continue;
            }
            $schedules[(int)$id_schedule]['product_name'] = self::getProductNameByOrderId((int)$schedule['id_order'], (int)$schedule['id_product']);
            $tax_rules_group = new TaxRulesGroup((int)$schedule['id_tax_rules_group']);
            $schedules[(int)$id_schedule]['tax_rules_group_name'] = $tax_rules_group->name;
            $schedules[(int)$id_schedule]['status_name'] = $status[$schedule['status']]['name'];
            $periodicity_key = array_search($schedule['periodicity'], array_column($periodicities, 'days'));
            $schedules[(int)$id_schedule]['periodicity_name'] = $periodicities[$periodicity_key + 1]['name'];
        }

        return $schedules;
    }

    public static function getOrdersFormattedByOrderId($id_order)
    {
        $id_order_recurring = Db::getInstance()->getValue(
            '
            SELECT mt.id_order_recurring
            FROM `'._DB_PREFIX_.'mercanet_transaction` mt
            LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = mt.`id_order` OR o.`id_order` = mt.`id_order_recurring`)
            WHERE mt.id_order_recurring <> 0
            AND mt.`id_order` = '.pSQL((int)$id_order)
        );

        if (!empty($id_order_recurring)) {
            $orders = Db::getInstance()->executeS(
                '
                SELECT o.id_order, o.total_paid_tax_incl, o.id_currency, osl.name as state_name, o.date_add
                FROM `'._DB_PREFIX_.'mercanet_transaction` mt
                LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = mt.`id_order`)
                LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (osl.`id_order_state` = o.`current_state` AND osl.id_lang = '.pSQL((int)Configuration::get('PS_LANG_DEFAULT')).')
                WHERE mt.`id_order_recurring` = '.pSQL((int)$id_order_recurring)
            );
        } else {
            $orders = Db::getInstance()->executeS(
                '
                SELECT o.id_order, o.total_paid_tax_incl, o.id_currency, osl.name as state_name, o.date_add
                FROM `'._DB_PREFIX_.'mercanet_transaction` mt
                LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = mt.`id_order`)
                LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (osl.`id_order_state` = o.`current_state` AND osl.id_lang = '.pSQL((int)Configuration::get('PS_LANG_DEFAULT')).')
                WHERE mt.`id_order_recurring` = '.pSQL((int)$id_order)
            );
        }

        return $orders;
    }

    /**
     * Retrieve all or one status
     */
    public static function getStatus($id_status = null, $only_value = false)
    {
        $module = Module::getInstanceByName('mercanet');
        $status = array(
            self::ID_STATUS_ACTIVE => array(
                'name' => Translate::getModuleTranslation($module, 'Active', 'MercanetCustomerPaymentRecurring'),
                'value' => self::ID_STATUS_ACTIVE,
            ),
            self::ID_STATUS_PAUSE => array(
                'name' => Translate::getModuleTranslation($module, 'Paused', 'MercanetCustomerPaymentRecurring'),
                'value' => self::ID_STATUS_PAUSE,
            ),
            self::ID_STATUS_EXPIRED => array(
                'name' => Translate::getModuleTranslation($module, 'Expired', 'MercanetCustomerPaymentRecurring'),
                'value' => self::ID_STATUS_EXPIRED,
            ),
        );

        if (!is_null($id_status) && isset($status[(int)$id_status])) {
            if ($only_value == true) {
                return $status[(int)$id_status]['value'];
            }
            return $status[(int)$id_status];
        }

        return $status;
    }

    /**
     * Get the product name
     */
    public static function getProductNameByOrderId($id_order, $id_product)
    {
        return Db::getInstance()->getValue(
            '
            SELECT `product_name`
            FROM `'._DB_PREFIX_.'order_detail`
            WHERE `id_order` = '.pSQL((int)$id_order).'
            AND `product_id` = '.pSQL((int)$id_product)
        );
    }

    /**
     * Retrieve the schedule to capture
     */
    public static function getSchedulesToCapture()
    {
        $module = Module::getInstanceByName('mercanet');
        return Db::getInstance()->executeS(
            '
            SELECT mcpr.*, mt.*, o.*
            FROM `'._DB_PREFIX_.'mercanet_customer_payment_recurring` mcpr
            LEFT JOIN `'._DB_PREFIX_.'mercanet_transaction` mt ON (mt.`id_mercanet_transaction` = mcpr.`id_mercanet_transaction`)
            LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = mcpr.`id_order`)
            WHERE DATEDIFF(NOW(), `next_schedule`) >= 0
            AND o.`module` = \''.pSQL($module->name).'\'
            AND mcpr.`status` = '.pSQL((int)self::ID_STATUS_ACTIVE)
        );
    }

    /**
     * Close the recurring payments finished
     */
    public static function closePaymentRecurring()
    {
        $payments = Db::getInstance()->executeS(
            '
            SELECT `id_mercanet_customer_payment_recurring` as `id`
            FROM `'._DB_PREFIX_.'mercanet_customer_payment_recurring`
            WHERE `status` = '.pSQL((int)self::ID_STATUS_ACTIVE).'
            AND `current_occurence` >= `number_occurences`'
        );

        foreach ($payments as $payment) {
            $payment_recurring = new MercanetCustomerPaymentRecurring((int)$payment['id']);
            $payment_recurring->next_schedule = $payment_recurring->last_schedule;
            $payment_recurring->status = self::ID_STATUS_EXPIRED;
            $payment_recurring->save();
        }
    }
    /*
     * check if customer have at least on recurring payment
     */

    public static function hasRecurringPayment($id_customer = 0)
    {
        return Db::getInstance()->getValue(
            '
            SELECT `id_customer`
            FROM `'._DB_PREFIX_.'mercanet_customer_payment_recurring`
            WHERE `id_customer` = '.pSQL((int)$id_customer)
        );
    }
    /*
     * Check if recurring payment is in given state
     */

    public static function hasOneInState($id_customer = 0, $state = 0)
    {
        if ((int)$id_customer && (int)$state) {
            return Db::getInstance()->getValue(
                '
                SELECT `id_customer`
                FROM `'._DB_PREFIX_.'mercanet_customer_payment_recurring`
                WHERE `id_customer` = '.pSQL((int)$id_customer).' AND status = '.pSQL((int)$state)
            );
        }
        return false;
    }
    /*
     * get all recurring payment for given customer
     */

    public static function getAllRecurringPayment($id_customer = 0)
    {
        return Db::getInstance()->executeS(
            '
            SELECT *
            FROM `'._DB_PREFIX_.'mercanet_customer_payment_recurring`
            WHERE `id_customer` = '.pSQL((int)$id_customer).'
            ORDER BY id_order'
        );
    }
    /*
     * Stop all active recurring payment for given customer
     */

    public static function stopAllRecurringPayment($id_customer = 0)
    {
        if ((int)$id_customer) {
            $customer = new Customer((int)$id_customer);
            Db::getInstance()->execute(
                '
                UPDATE `'._DB_PREFIX_.'mercanet_customer_payment_recurring`
                SET `status` = '.pSQL((int)self::ID_STATUS_EXPIRED).'
                WHERE `id_customer` = '.pSQL((int)$id_customer).' AND `status` = '.pSQL((int)self::ID_STATUS_ACTIVE)
            );
            $module = Module::getInstanceByName('mercanet');
            if (Validate::isEmail(Configuration::get('PS_SHOP_EMAIL'))) {
                $data = array(
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname,
                    '{email}' => $customer->email,
                    '{customer_id}' => $customer->id,
                );
                Mail::Send(
                    (int)Context::getContext()->language->id,
                    'mercanet_payment_recurring_stop',
                    Translate::getModuleTranslation(
                        $module,
                        'A customer has cancel his reccuring payment',
                        'MercanetCustomerPaymentRecurring'
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
    }
    /*
     * Check if recurring payment is reorder
     */

    public static function isPausedRecurringPayment($id_customer = 0, $id_product = 0, $id_cart = 0)
    {
        return Db::getInstance()->getValue(
            '
            SELECT `id_mercanet_customer_payment_recurring`
            FROM `'._DB_PREFIX_.'mercanet_customer_payment_recurring`
            WHERE `id_customer` = '.pSQL((int)$id_customer).' AND `id_product` = '.pSQL((int)$id_product).' AND `id_cart_paused_currency` = '.pSQL((int)$id_cart).''
        );
    }
    
    /*
    * calculate number of occurrence late
    */

    public function getLateRecurringOccurence()
    {
        $date = date('Y-m-d');
        $date_schedule = Tools::substr($this->last_schedule, 0, 10);
        $date_last_schedule = date($date_schedule);
        $datetime1 = date_create($date);
        $datetime2 = date_create($date_last_schedule);
   
        $diff_unit = '%a';
        if ($this->periodicity == 'M') {
            $diff_unit = '%m';
        }
        $interval = date_diff($datetime1, $datetime2);
        if ($interval->format('%R'.$diff_unit) < 0) {
            $diff_abs = abs($interval->format('%R'.$diff_unit));
            $diff = (int)$diff_abs / (int)$this->number_occurences;
            if ((int)$diff > 0) {
                return (int)$diff;
            }
        }
        return false;
    }
    /*
    * calculate number of occurrence late
    */

    public function isAnticipatedPayment()
    {
        $label = $this->l('Active');
        $label = $this->l('Paused');
        $label = $this->l('Expired');
        if ($this->status == self::ID_STATUS_ACTIVE) {
            $date = date('Y-m-d');
            $date_schedule = Tools::substr($this->next_schedule, 0, 10);
            $date_next_schedule = date($date_schedule);
            $datetime1 = date_create($date);
            $datetime2 = date_create($date_next_schedule);
            $diff_unit = '%a';
            $interval = date_diff($datetime1, $datetime2);
            if ($interval->format('%R'.$diff_unit) >= 0) {
                return true;
            }
        }
        return false;
    }
}
