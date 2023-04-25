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

class MercanetAcquirerResponseCode extends ObjectModel
{
    /**
     * ID
     * @var integer
     */
    public $id_mercanet_acquirer_response_code;

    /**
     * Message
     * @var float
     */
    public $message;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_acquirer_response_code',
        'primary' => 'id_mercanet_acquirer_response_code',
        'multilang' => true,
        'fields' => array(
            'id_mercanet_acquirer_response_code' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            ),
            'message' => array(
                'type' => self :: TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'required' => true,
            ),
        )
    );

    /**
     * Get the message by code
     */
    public static function getMessageByCode($code, $id_lang = null)
    {
        if (empty($id_lang)) {
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
        }
        return Db::getInstance()->getValue('
			SELECT `message`
			FROM `'._DB_PREFIX_.'mercanet_acquirer_response_code_lang`
			WHERE `id_lang` = '.pSQL((int)$id_lang).'
			AND `id_mercanet_acquirer_response_code` = "'.pSQL($code).'"
		');
    }
}
