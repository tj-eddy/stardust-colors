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

class ModulesXCatalogXControlsXSelectCategory extends ControlSelect2
{
    const CONTROL_TYPE = 'select_category';

    private static $_categories = [];

    public function getType()
    {
        return self::CONTROL_TYPE;
    }

    public static function getCategories()
    {
        if (is_admin() && !self::$_categories) {
            $id_lang = \Context::getContext()->language->id;

            foreach (\Category::getSimpleCategories($id_lang) as &$category) {
                self::$_categories[$category['id_category']] = "#{$category['id_category']} {$category['name']}";
            }
        }

        return self::$_categories;
    }

    protected function getDefaultSettings()
    {
        return [
            'options' => self::getCategories(),
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
