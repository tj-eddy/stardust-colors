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

class MercanetNxPayment extends ObjectModel
{
    /**
     * ID
     * @var integer
     */
    public $id_mercanet_nx_payment;

    /**
     * Minimum amount
     * @var float
     */
    public $minimum_amount;

    /**
     * Maximum amount
     * @var float
     */
    public $maximum_amount;

    /**
     * Number
     * @var integer
     */
    public $number;

    /**
     * Periodicity
     * @var integer
     */
    public $periodicity;

    /**
     * First payment
     * @var float
     */
    public $first_payment;

    /**
     * Method name
     * @var string
     */
    public $method_name;

    /**
     * Active
     * @var boolean
     */
    public $active;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_nx_payment',
        'primary' => 'id_mercanet_nx_payment',
        'multilang' => true,
        'fields' => array(
            'method_name' => array(
                'type' => self :: TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'required' => true,
            ),
            'minimum_amount' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => false
            ),
            'maximum_amount' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => false
            ),
            'number' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true
            ),
            'periodicity' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false
            ),
            'first_payment' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'
            )
        )
    );

    /**
     * Retrieve all the NX payment
     */
    public static function getAllMercanetNxPayment($id_lang = null)
    {
        if (empty($id_lang)) {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }
        $sql = '
            SELECT mnp.*, mnpl.`method_name`
            FROM `'._DB_PREFIX_.'mercanet_nx_payment` mnp
            LEFT JOIN `'._DB_PREFIX_.'mercanet_nx_payment_lang` mnpl ON (mnpl.`id_mercanet_nx_payment` = mnp.`id_mercanet_nx_payment`)
            WHERE mnpl.`id_lang` = '.pSQL((int)$id_lang)
        ;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Retrieve all the NX payment available
     */
    public static function getAvailablePayments($id_lang = null)
    {
        if (empty($id_lang)) {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }

        // Amount in EURO
        $amount = MercanetApi::getConvertedAmount(
            (float)Context::getContext()->cart->getOrderTotal(),
            new Currency((int)Context::getContext()->cart->id_currency),
            new Currency((int)Currency::getIdByNumericIsoCode((int)Configuration::get('MERCANET_EURO_ISO_CODE_NUM')))
        );

        $sql = '
            SELECT mnp.*, mnpl.`method_name`
            FROM `'._DB_PREFIX_.'mercanet_nx_payment` mnp
            LEFT JOIN `'._DB_PREFIX_.'mercanet_nx_payment_lang` mnpl ON (mnpl.`id_mercanet_nx_payment` = mnp.`id_mercanet_nx_payment`)
            WHERE mnpl.`id_lang` = '.pSQL((int)$id_lang).'
			AND mnp.`minimum_amount` <= "'.pSQL((float)$amount).'"
			AND (mnp.`maximum_amount` >= "'.pSQL((float)$amount).'" OR mnp.`maximum_amount` = 0)
			AND mnp.active
        ';
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Check if the cart amount is in the range of the minimum / maximum amount
     */
    public static function isCartAmountInRanges($amount)
    {
        if (empty($amount)) {
            return false;
        }
        $result = Db::getInstance()->getValue('
			SELECT *
			FROM `'._DB_PREFIX_.'mercanet_nx_payment`
			WHERE `minimum_amount` <= "'.pSQL((float)$amount).'"
			AND (`maximum_amount` >= "'.pSQL((float)$amount).'" OR `maximum_amount` = 0)
			AND active
			
		');

        if (!empty($result)) {
            return true;
        }

        return false;
    }
}
