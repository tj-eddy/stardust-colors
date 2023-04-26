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

use CE\CoreXDynamicTagsXDataTag as DataTag;
use CE\ModulesXDynamicTagsXModule as Module;

class ModulesXCatalogXTagsXProductAddToCart extends DataTag
{
    const ACTION = 'addToCart';

    public function getName()
    {
        return 'product-add-to-cart';
    }

    public function getTitle()
    {
        return __('Add to Cart');
    }

    public function getGroup()
    {
        return Module::ACTION_GROUP;
    }

    public function getCategories()
    {
        return [Module::URL_CATEGORY];
    }

    public function _registerControls()
    {
        $this->addControl(
            'id_product',
            [
                'label' => __('Product'),
                'type' => ControlsManager::SELECT2,
                'label_block' => true,
                'select2options' => [
                    'placeholder' => __('Current Product'),
                    'ajax' => [
                        'get' => 'Products',
                        'url' => Helper::getAjaxProductsListLink(),
                    ],
                ],
            ]
        );
    }

    public function getValue(array $options = [])
    {
        $settings = $this->getSettings();

        if (!$settings['id_product']) {
            unset($settings['id_product']);
        }

        return Plugin::$instance->frontend->createActionHash(static::ACTION, $settings);
    }
}
