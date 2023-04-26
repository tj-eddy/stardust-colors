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

class ModulesXCatalogXTagsXManufacturerImage extends DataTag
{
    const REMOTE_RENDER = true;

    public function getName()
    {
        return 'manufacturer-image';
    }

    public function getTitle()
    {
        return __('Brand Image');
    }

    public function getGroup()
    {
        return Module::CATALOG_GROUP;
    }

    public function getCategories()
    {
        return [Module::IMAGE_CATEGORY];
    }

    protected function _registerControls()
    {
        $this->addControl(
            'image_size',
            [
                'label' => __('Image Size'),
                'type' => ControlsManager::SELECT,
                'options' => GroupControlImageSize::getAllImageSizes('manufacturers', true),
            ]
        );
    }

    public function getValue(array $options = [])
    {
        $context = \Context::getContext();
        $vars = $context->smarty->tpl_vars;
        $id_manufacturer = isset($vars['product']) ? $vars['product']->value['id_manufacturer'] : (
            isset($vars['manufacturer']) ? $vars['manufacturer']->value->id : 0
        );

        return [
            'id' => '',
            'url' => $id_manufacturer
                ? $context->link->getManufacturerImageLink($id_manufacturer, $this->getSettings('image_size'))
                : '',
            'alt' => __('Brand'),
        ];
    }

    protected function getSmartyValue(array $options = [])
    {
        $image_size = $this->getSettings('image_size') ?: 'null';

        return [
            'id' => '',
            // tmp fix: Absolute URLs need to be marked with {*://*}
            'url' => '{*://*}' .
                '{if $product.id_manufacturer}' .
                    "{call_user_func([\$link, getManufacturerImageLink], \$product.id_manufacturer, $image_size)}" .
                '{else}' .
                    'data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIGhlaWdodD0nMCcvPg' .
                '{/if}',
            'alt' => __('Brand'),
        ];
    }
}
