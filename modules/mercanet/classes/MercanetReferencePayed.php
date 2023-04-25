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

class MercanetReferencePayed extends ObjectModel
{

    /**
     * ID
     * @var string
     */
    public $order_reference;

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
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_reference_payed',
        'primary' => 'order_reference',
        'multilang' => false,
        'fields' => array(
            'order_reference' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
            ),
            'source' => array(
                'type' => self::TYPE_STRING,
                'required' => false,
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'required' => true,
            )
        )
    );

    /**
     * Check if the CART ID already exist
     */
    public static function insertReference($order_reference, $source, $received_datas)
    {
        MercanetLogger::log("($source) insertReference", MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
        
        if ($source == 'notification.php') {
			MercanetLogger::log("($source) pause de 10 secondes", MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
			sleep(10);
			MercanetLogger::log("($source) pause terminée", MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
		}
        
        if ($order_reference) {
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'mercanet_reference_payed` WHERE order_reference = "'.pSQl($order_reference).'"';
            
            $is_exists = Db::getInstance()->getRow($sql);
            
            MercanetLogger::log("($source) sql is_exists = ".$sql, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            MercanetLogger::log("($source) res is_exists = ".json_encode($is_exists), MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            if ($is_exists) {
                return false;
            }
            
            $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.'mercanet_reference_payed` (order_reference, source, date_add, received_data) VALUES ("'.pSQl($order_reference).'", "'.pSQL($source).'", NOW(), "'.pSQL($received_datas).'")';
            
            MercanetLogger::log("($source) SQL insert : ".$sql, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            $res = Db::getInstance()->execute($sql);
            
            MercanetLogger::log("($source) res : ".json_encode($res), MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG);
            
            return $res;
        }
        
        return false;
    }
    
    public static function getReceivedDataByOrderReference($order_reference)
    {
        if ($order_reference) {
            $sql = '
                SELECT `received_data` FROM `'._DB_PREFIX_.'mercanet_reference_payed`
                WHERE `order_reference` = "'.pSQL($order_reference).'";
            ';

            return Db::getInstance()->getValue($sql);
        }
        
        return false;
    }
}
