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

class ModulesXCatalogXControlsXSelectSupplier extends ControlSelect2
{
    const CONTROL_TYPE = 'select_supplier';

    private static $_suppliers;

    public function getType()
    {
        return self::CONTROL_TYPE;
    }

    public static function getSuppliers()
    {
        if (is_admin() && null === self::$_suppliers) {
            self::$_suppliers = [];
            $id_lang = \Context::getContext()->language->id;

            foreach (\Supplier::getLiteSuppliersList($id_lang) as &$supplier) {
                self::$_suppliers[$supplier['id']] = "#{$supplier['id']} {$supplier['name']}";
            }
        }

        return self::$_suppliers ?: [];
    }

    protected function getDefaultSettings()
    {
        return [
            'options' => self::getSuppliers(),
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
