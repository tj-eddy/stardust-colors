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

class MercanetPaymentRecurring extends ObjectModel
{

    const ID_TYPE_PAYMENT_SIMPLE = 1;
    const ID_TYPE_PAYMENT_RECURRING = 2;

    /**
     * ID
     * @var integer
     */
    public $id_mercanet_payment_recurring;

    /**
     * ID PRODUCT
     * @var integer
     */
    public $id_product;

    /**
     * TYPE
     * @var integer
     */
    public $type;

    /**
     * PERIODICITY
     * @var string
     */
    public $periodicity;

    /**
     * NUMBER OF OCCURENCES
     * @var integer
     */
    public $number_occurences;

    /**
     * RECURRING AMOUNT
     * @var float
     */
    public $recurring_amount;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_payment_recurring',
        'primary' => 'id_mercanet_payment_recurring',
        'multilang' => false,
        'fields' => array(
            'id_product' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'type' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'periodicity' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isInt',
            ),
            'number_occurences' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'size' => 3,
                'validate' => 'isInt',
            ),
            'recurring_amount' => array(
                'type' => self::TYPE_FLOAT,
                'required' => false,
            ),
        )
    );

    /**
     * Retrieve the configuration for
     * @param  int $id_product
     * @param  int $type
     * @return array
     */
    public static function getPaymentRecurringByProductId($id_product, $type = null)
    {
        if (!is_null($type)) {
            $type = 'AND type = '.pSQL((int)$type);
        }
        return Db::getInstance()->getRow('
            SELECT *
            FROM `'._DB_PREFIX_.'mercanet_payment_recurring`
            WHERE `id_product` = '.pSQL((int)$id_product).'
            '.$type.'
        ');
    }

    /**
     * Retrieve the id of a configuration product
     * @param  int $id_product
     * @param  int $type
     * @return int
     */
    public static function getIdPaymentRecurringByProductId($id_product, $type = null)
    {
        if (!is_null($type)) {
            $type = 'AND type = '.pSQL((int)$type);
        }

        return Db::getInstance()->getValue('
            SELECT id_mercanet_payment_recurring
            FROM `'._DB_PREFIX_.'mercanet_payment_recurring`
            WHERE `id_product` = '.pSQL((int)$id_product).'
            '.$type.'
        ');
    }

    /**
     * Return types of payments
     */
    public static function getTypes()
    {
        $module = Module::getInstanceByName('mercanet');

        return array(
            self::ID_TYPE_PAYMENT_SIMPLE => Translate::getModuleTranslation($module, 'Paiement simple', 'MercanetPaymentRecurring'),
            self::ID_TYPE_PAYMENT_RECURRING => Translate::getModuleTranslation($module, 'Paiement par abonnement', 'MercanetPaymentRecurring')
        );
    }

    /**
     * Return the periodicities
     */
    public static function getPeriodicities()
    {
        $module = Module::getInstanceByName('mercanet');
        return array(
            1 => array(
                'days' => 'D',
                'name' => Translate::getModuleTranslation($module, 'Jour', 'MercanetPaymentRecurring'),
            ),
            2 => array(
                'days' => 'M',
                'name' => Translate::getModuleTranslation($module, 'Mois', 'MercanetPaymentRecurring'),
            )
        );
    }

    public static function isThisProductPaymentRecurring($id_product)
    {
        $result = Db::getInstance()->getRow(
            '
            SELECT *
            FROM `'._DB_PREFIX_.'mercanet_payment_recurring`
            WHERE `id_product` = '.pSQL((int)$id_product)
        );

        if (!empty($result)) {
            if ((int)$result['type'] == (int)self::ID_TYPE_PAYMENT_RECURRING) {
                return true;
            }
        }

        return false;
    }
}
