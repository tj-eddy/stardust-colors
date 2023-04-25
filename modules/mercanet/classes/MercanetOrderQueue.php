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

class MercanetOrderQueue extends ObjectModel
{
    /**
     * ID
     * @var integer
     */
    public $id_mercanet_order_queue;

    /**
     * Id Cart
     * @var integer
     */
    public $id_cart;

    /**
     * Source
     * @var string
     */
    public $source;

    /**
     * Date add
     * @var date
     */
    public $date_add;

    /**
     * Date done
     * @var date
     */
    public $date_done;

    /**
     * Id Order
     * @var integer
     */
    public $id_order;

    const LOOP = 1;

    const MAX_LOOP = 3;

    const SLEEP_TIME = 2;
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_order_queue',
        'primary' => 'id_mercanet_order_queue',
        'multilang' => false,
        'fields' => array(
            'id_cart' => array(
                'type' => self::TYPE_INT,
                'required' => true,
            ),
            'source' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'required' => true,
            ),
            'date_done' => array(
                'type' => self::TYPE_DATE,
                'required' => false,
            ),
            'id_order' => array(
                'type' => self::TYPE_INT,
                'required' => false,
            )
        )
    );

    /**
    * Check if the CART ID already exist
    */
    public static function checkAlreadyExist($raw_data, $source)
    {
        $data = MercanetApi::getDataFromRawData($raw_data);
        $return_context = MercanetApi::getDataFromRawData($data['returnContext'], ',');
        $order_queue = self::getOrderQueueByCartId((int)$return_context['id_cart']);
        // If none has been found, create one
        if (empty($order_queue)) {
            $order_queue = new MercanetOrderQueue();
            $order_queue->id_cart = (int)$return_context['id_cart'];
            $order_queue->source = (string)$source;
            $order_queue->date_add = new DateTime();
            $order_queue->add();
            return false;
        }
        $loop = self::LOOP;
        if (empty($order_queue['id_order'])) {
            while ($loop <= self::MAX_LOOP) {
                // Log
                $message = (string)$source.' | Attemp '.(int)$loop.' for CART ID:'.(int)$return_context['id_cart'];
                MercanetLogger::log($message, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
                // Waiting time
                sleep(self::SLEEP_TIME);
                // Check if the order has been done
                $order_queue = self::getOrderQueueByCartId((int)$return_context['id_cart']);
                if (!empty($order_queue['id_order'])) {
                    return true;
                }
                $loop++;
            }
        }
        return false;
    }

        /**
     * Check if the order has been created or in process
     */
    public static function checkOrderCreated($id_cart, $source)
    {
        $loop = self::LOOP;

        $results = Db::getInstance()->executeS('
            SELECT *
            FROM `'._DB_PREFIX_.'mercanet_order_queue`
            WHERE `id_cart` = '.pSQL((int)$id_cart));

        if (count($results) > 1) {
            switch ($source) {
                case 'notification.php':
                    while ($loop <= self::MAX_LOOP) {
                        // Log
                        $message = 'checkOrderCreated: '.(string)$source.' | Attemp '.(int)$loop.' for CART ID:'.(int)$id_cart;
                        MercanetLogger::log($message, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
                        // Waiting time
                        sleep(self::SLEEP_TIME);
                        // Check if the order has been done
                        $order_queue = self::getOrderQueueByCartId((int)$id_cart);
                        if (!empty($order_queue['id_order']) && $order_queue['id_order'] != 0) {
                            return true;
                        }
                        $loop++;
                    }
                    break;
                case 'validation.php':
                    break;
            }
        }

        return false;
    }
    /**
    * Retrieve the Order Queue By CART ID
    */
    public static function getOrderQueueByCartId($id_cart)
    {
        if (empty($id_cart)) {
            return false;
        }

        return Db::getInstance()->getRow('
            SELECT *
            FROM `'._DB_PREFIX_.'mercanet_order_queue`
            WHERE `id_cart` = '.pSQL((int)$id_cart));
    }

    /**
    * Update the Order Queue with ORDER ID
    */
    public static function updateOrderQueue($id_cart, $id_order)
    {
        $date = new DateTime();
        Db::getInstance()->update('mercanet_order_queue', array('id_order' => (int)$id_order, 'date_done' => $date->format('Y-m-d h:m:s')), 'id_cart ='.(int)$id_cart);
    }
}
