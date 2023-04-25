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

class MercanetWallet extends ObjectModel
{
    /**
     * ID
     * @var string
     */
    public $id_mercanet_wallet;

    /**
     * Customer ID
     * @var interger
     */
    public $id_customer;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_wallet',
        'primary' => 'id_mercanet_wallet',
        'fields' => array(
            'id_mercanet_wallet' => array(
                'type' => self::TYPE_STRING,
                'unique' => true,
                'required' => true
            ),
            'id_customer' => array(
                'type' => self :: TYPE_INT,
                'required' => true,
            ),
        )
    );

    /**
     * Get the customer wallet ID, if empty, we generate one
     */
    public static function getCustomerWalletId($id_customer)
    {
        if (empty($id_customer) && $id_customer == 0) {
            return false;
        }
        $wallet_id = Db::getInstance()->getValue('
			SELECT `id_mercanet_wallet`
			FROM `'._DB_PREFIX_.'mercanet_wallet`
			WHERE `id_customer` = '.pSQL((int)$id_customer).'
        ');

        if (empty($wallet_id)) {
            $wallet_id = self::generateCustomerWalletId((int)$id_customer);
        }

        return $wallet_id;
    }

    /**
     * Generate a Random Wallet
     */
    public static function generateCustomerWalletId($id_customer)
    {
        if (empty($id_customer) && $id_customer == 0) {
            return false;
        }
        $wallet = new MercanetWallet();
        $wallet->id_mercanet_wallet = self::generateRandomString((int)$id_customer);
        $wallet->id_customer = (int)$id_customer;
        $wallet->save();

        return $wallet->id_mercanet_wallet;
    }

    /**
     * Generate random string
     */
    public static function generateRandomString($id_customer, $length = 21)
    {
        if (empty($id_customer) && $id_customer == 0) {
            return false;
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = Tools::strlen($characters);
        $customer_length = Tools::strlen($id_customer);

        $random_wallet = $id_customer;
        for ($i = 0; $i < ($length - $customer_length); $i++) {
            $random_wallet .= $characters[rand(0, $characters_length - 1)];
        }
        return $random_wallet;
    }
}
