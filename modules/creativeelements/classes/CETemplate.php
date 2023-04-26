<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

class CETemplate extends ObjectModel
{
    public $id_employee;
    public $title;
    public $type;
    public $content;
    public $position;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'ce_template',
        'primary' => 'id_ce_template',
        'fields' => [
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128],
            'type' => ['type' => self::TYPE_STRING, 'validate' => 'isHookName', 'required' => true, 'size' => 64],
            'content' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_INT, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function add($auto_date = true, $null_values = false)
    {
        $this->id_employee = Context::getContext()->employee->id;

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        if ('0000-00-00 00:00:00' === $this->date_add) {
            $this->date_add = date('Y-m-d H:i:s');
        }

        return parent::update($null_values);
    }

    public static function getTypeById($id)
    {
        $db = Db::getInstance();
        $table = $db->escape(_DB_PREFIX_ . static::$definition['table']);
        $primary = $db->escape(static::$definition['primary']);

        return $db->getValue("SELECT type FROM $table WHERE $primary = " . (int) $id);
    }

    public static function getKitOptions()
    {
        $table = _DB_PREFIX_ . 'ce_template';

        return Db::getInstance()->executeS("
            SELECT `id_ce_template` AS `value`, CONCAT('#', `id_ce_template`, ' ', `title`) AS `name` FROM $table
            WHERE `active` = 1 AND `type` = 'kit'
            ORDER BY `title`
        ") ?: [];
    }
}
