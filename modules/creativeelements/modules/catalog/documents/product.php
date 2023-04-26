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

use CE\ModulesXThemeXDocumentsXThemePageDocument as ThemePageDocument;

class ModulesXCatalogXDocumentsXProduct extends ThemePageDocument
{
    public function getName()
    {
        return 'product';
    }

    public static function getTitle()
    {
        return __('Product Page');
    }

    protected static function getEditorPanelCategories()
    {
        $categories = [
            'product-elements' => [
                'title' => __('Product'),
            ],
        ];

        $categories += parent::getEditorPanelCategories();

        return $categories;
    }

    protected function _registerControls()
    {
        parent::_registerControls();

        $this->startControlsSection(
            'preview_settings',
            [
                'label' => __('Preview Settings'),
                'tab' => ControlsManager::TAB_SETTINGS,
            ]
        );

        if (is_admin()) {
            $prods = \Product::getProducts(\Context::getContext()->language->id, 0, 1, 'date_upd', 'DESC', false, true);
        }

        $this->addControl(
            'preview_id',
            [
                'type' => ControlsManager::SELECT2,
                'label' => __('Product'),
                'label_block' => true,
                'select2options' => [
                    'placeholder' => __('Loading') . '...',
                    'allowClear' => false,
                    'ajax' => [
                        'get' => 'Products',
                        'url' => Helper::getAjaxProductsListLink(),
                    ],
                ],
                'default' => !empty($prods[0]['id_product']) ? $prods[0]['id_product'] : 1,
                'export' => false,
            ]
        );

        $this->addControl(
            'apply_preview',
            [
                'type' => ControlsManager::BUTTON,
                'label' => __('Apply & Preview'),
                'label_block' => true,
                'show_label' => false,
                'text' => __('Apply & Preview'),
                'separator' => 'none',
                'event' => 'ceThemeBuilder:ApplyPreview',
            ]
        );

        $this->endControlsSection();
    }
}
