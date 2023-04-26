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
class Order extends OrderCore
{
    /**
     * MERCANET OVERRIDE
     * Create a reference if the cart doesn't have one, else return the reference
     * @return string
     */
    /*
    * module: mercanet
    * date: 2023-04-26 07:52:47
    * version: 1.6.12
    */
    public static function generateReference()
    {
        $id_cart = (int)Context::getContext()->cart->id;
        $reference = self::getReferenceByCartId($id_cart);
        if (empty($reference)) {
            $reference = Tools::strtoupper(Tools::passwdGen(9));
        }
        return $reference;
    }
    /**
     * Retrieve the pregenerate reference
     * @param int $id_cart
     * @return boolean | string
     */
    /*
    * module: mercanet
    * date: 2023-04-26 07:52:47
    * version: 1.6.12
    */
    public static function getReferenceByCartId($id_cart)
    {
        if (empty($id_cart)) {
            return false;
        }
        $sql = 'SELECT `reference`
			FROM `'._DB_PREFIX_.'mercanet_order_reference` 
			WHERE `id_cart` ='.pSQL((int)$id_cart);
        return Db::getInstance()->getValue($sql);
    }
}
