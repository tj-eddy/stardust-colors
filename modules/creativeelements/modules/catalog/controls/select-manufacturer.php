<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */

namespace CE;

defined('_PS_VERSION_') or exit;

class ModulesXCatalogXControlsXSelectManufacturer extends ControlSelect2
{
    const CONTROL_TYPE = 'select_manufacturer';

    private static $_manufacturers;

    public function getType()
    {
        return self::CONTROL_TYPE;
    }

    public static function getManufacturers()
    {
        if (is_admin() && null === self::$_manufacturers) {
            self::$_manufacturers = [];
            $id_lang = \Context::getContext()->language->id;

            foreach (\Manufacturer::getLiteManufacturersList($id_lang) as &$manufacturer) {
                self::$_manufacturers[$manufacturer['id']] = "#{$manufacturer['id']} {$manufacturer['name']}";
            }
        }

        return self::$_manufacturers ?: [];
    }

    protected function getDefaultSettings()
    {
        return [
            'options' => self::getManufacturers(),
            'multiple' => false,
            'select2options' => [],
            'extend' => [],
        ];
    }

    public function contentTemplate()
    {
        echo '<# $.extend( data.options, data.extend ) #>';

        parent::contentTemplate();
    }
}
