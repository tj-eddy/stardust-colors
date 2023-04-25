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

class MercanetHistory extends ObjectModel
{
    /**
     * ID
     * @var integer
     */
    public $id_mercanet_history;

    /**
     * ID transaction
     * @var integer
     */
    public $id_mercanet_transaction;

    /**
     * ID response code
     * @var integer
     */
    public $id_mercanet_response_code;

    /**
     * ID acquirer response code
     * @var integer
     */
    public $id_mercanet_acquirer_response_code;

    /**
     * ID complementary code
     * @var integer
     */
    public $id_mercanet_complementary_code;

    /**
     * Date
     * @var integer
     */
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mercanet_history',
        'primary' => 'id_mercanet_history',
        'fields' => array(
            'id_mercanet_transaction' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            ),
            'id_mercanet_response_code' => array(
                'type' => self::TYPE_STRING,
                'required' => false
            ),
            'id_mercanet_acquirer_response_code' => array(
                'type' => self::TYPE_STRING,
                'required' => false
            ),
            'id_mercanet_complementary_code' => array(
                'type' => self::TYPE_STRING,
                'required' => false
            ),
            'date_add' => array(
                'type' => self :: TYPE_DATE,
                'required' => false,
            )
        )
    );

    /**
     * Get all transactions for an order
     */
    public static function getTransactionsByOrderId($id_order)
    {
        if (empty($id_order)) {
            return false;
        }
        $context = Context::getContext();
        $translated = self::isTranslatedInThisIdLang((int)$context->employee->id_lang);
        if ($translated == true) {
            $id_lang = (int)$context->employee->id_lang;
        } else {
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
        }

        $results = Db::getInstance()->executeS('
			SELECT mt.*, mrcl.`message` as `response_message`, marcl.`message` as `acquirer_response_message`, mccl.`message` as `complementary_message`, mh.`date_add`
			FROM `'._DB_PREFIX_.'mercanet_history` mh
			LEFT JOIN `'._DB_PREFIX_.'mercanet_transaction` mt ON (mt.`id_mercanet_transaction` = mh.`id_mercanet_transaction`)
			LEFT JOIN `'._DB_PREFIX_.'mercanet_response_code_lang` mrcl ON (mrcl.`id_mercanet_response_code` = mh.`id_mercanet_response_code` AND mrcl.`id_lang` = '.pSQL((int)$id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'mercanet_acquirer_response_code_lang` marcl ON (marcl.`id_mercanet_acquirer_response_code` = mh.`id_mercanet_acquirer_response_code` AND marcl.`id_lang` = '.pSQL((int)$id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'mercanet_complementary_code_lang` mccl ON (mccl.`id_mercanet_complementary_code` = mh.`id_mercanet_complementary_code` AND mccl.`id_lang` = '.pSQL((int)$id_lang).')
			WHERE mt.id_order = '.pSQL((int)$id_order).'
			ORDER BY mh.`date_add` ASC
		');

        if (empty($results)) {
            return false;
        }

        // Split the data raw into an Array
        foreach ($results as $key_result => $result) {
            $row_datas = explode('<br>', $result['raw_data']);

            if (!empty($row_datas)) {
                // Init the results index raw_data
                $results[$key_result]['raw_data'] = array();

                foreach ($row_datas as $key_data => $datas) {
                    // Explode the data, ex: ResponseCode:45
                    $data = explode(':', $datas);

                    if (!empty($data)) {
                        // If there is only 2 values >> key=value
                        if (count($data) <= 2 && count($data) > 1) {
                            $label = (isset($data[0])) ? $data[0] : $key_data;
                            $value = (isset($data[1])) ? $data[1] : 'Not Found';
                        } elseif (count($data) > 1) {
                            $label = (isset($data[0])) ? $data[0] : $key_data;
                            $value = '';

                            // Create the value
                            foreach ($data as $key_raw => $raw_data) {
                                if ($key_raw == 0) {
                                    continue;
                                }

                                if ($key_raw == 1) {
                                    $value .= $raw_data;
                                } else {
                                    $value .= ':'.$raw_data;
                                }
                            }
                        }

                        // Add the result to the raw_data
                        $results[$key_result]['raw_data'][$label] = $value;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Check if the messages are translated in a language
     */
    public static function isTranslatedInThisIdLang($id_lang)
    {
        if (empty($id_lang)) {
            return false;
        }

        $result = Db::getInstance()->getValue('
			SELECT *
			FROM `'._DB_PREFIX_.'mercanet_response_code_lang`
			WHERE id_lang = '.pSQL((int)$id_lang).'
        ');

        if (!empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * Return the formated response code - message
     * @param type $id_mercanet_transaction
     * @param type $id_lang
     */
    public static function getResponseCodeMessageByTransactionId($id_mercanet_transaction, $id_lang = null)
    {
        if (empty($id_lang)) {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }

        $result = Db::getInstance()->getRow('
            SELECT mrcl.`id_mercanet_response_code`, mrcl.`message`
            FROM `'._DB_PREFIX_.'mercanet_history` mh
			LEFT JOIN `'._DB_PREFIX_.'mercanet_response_code_lang` mrcl ON (mrcl.`id_mercanet_response_code` = mh.`id_mercanet_response_code`)
			WHERE mrcl.`id_lang` = '.pSQL((int)$id_lang).'
			AND mh.`id_mercanet_transaction` = '.pSQL($id_mercanet_transaction).'
        ');

        if (!empty($result)) {
            return $result['id_mercanet_response_code'].' -'.$result['message'];
        }

        return '--';
    }
}
