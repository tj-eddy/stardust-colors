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

class MercanetOrderReference extends ObjectModel
{
    /**
     * Object Cart ID
     * @var integer
     */
    public $id_cart;

    /**
     * Mercanet Order Reference
     * @var string
     */
    public $order_reference;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_order_reference',
        'primary' => 'id_cart',
        'fields' => array(
            'id_cart' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true
            ),
            'order_reference' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            )
        )
    );

    /**
     * Add the reference at one cart
     * @param int $id_cart
     * @param string $reference
     * @return string
     */
    public static function addCartReference($id_cart, $reference)
    {
        $already_add = self::getReferenceByCartId((int)$id_cart);
        if (empty($already_add)) {
            Db::getInstance()->insert('mercanet_order_reference', array(
                'id_cart' => pSQL((int)$id_cart),
                'reference' => pSQL($reference)));
            return $reference;
        } else {
            return $already_add;
        }
    }

    /**
    *   Retrieve the Order Reference
    *   @return string
    */
    public static function getReferenceByCartId($id_cart)
    {
        $sql = 'SELECT `reference`
			FROM `'._DB_PREFIX_.'mercanet_order_reference` 
			WHERE `id_cart` ='.pSQL((int)$id_cart);
        return Db::getInstance()->getValue($sql);
    }

    /**
     * Retrieve the cart id
     * @return int
     */
    public static function getIdCartByReference($reference)
    {
        $sql = 'SELECT `id_cart`
			FROM `'._DB_PREFIX_.'mercanet_order_reference` 
			WHERE `reference` ="'.pSQL($reference).'"';

        return Db::getInstance()->getValue($sql);
    }
}
