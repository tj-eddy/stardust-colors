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

class ModulesXCatalogXTagsXProductImage extends DataTag
{
    const REMOTE_RENDER = true;

    public function getName()
    {
        return 'product-image';
    }

    public function getTitle()
    {
        return __('Product Image');
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
        $index_options = range(0, 10);
        $index_options[0] = __('Cover');

        $this->addControl(
            'image_index',
            [
                'label' => __('Image'),
                'type' => ControlsManager::SELECT2,
                'select2options' => [
                    'tags' => true,
                    'allowClear' => false,
                ],
                'options' => $index_options,
                'default' => '0',
            ]
        );

        $image_size_options = GroupControlImageSize::getAllImageSizes('products');

        $this->addControl(
            'image_size',
            [
                'label' => __('Image Size'),
                'type' => ControlsManager::SELECT,
                'options' => &$image_size_options,
                'default' => key($image_size_options),
            ]
        );
    }

    protected function registerAdvancedSection()
    {
        $this->startControlsSection(
            'advanced',
            [
                'label' => __('Advanced'),
            ]
        );

        $this->addControl(
            'fallback_image',
            [
                'label' => __('Fallback'),
                'type' => ControlsManager::MEDIA,
            ]
        );

        $this->endControlsSection();
    }

    public function getValue(array $options = [])
    {
        $product = \Context::getContext()->smarty->tpl_vars['product']->value;
        $settings = $this->getSettings();
        $size = $settings['image_size'];
        $index = $settings['image_index'] - 1;

        if ($index < 0 && $product['cover']) {
            $image = $product['cover'];
        } elseif (isset($product['images'][$index])) {
            $image = $product['images'][$index];
        } else {
            return $settings['fallback_image'];
        }

        return [
            'id' => '',
            'url' => $image['bySize'][$size]['url'],
            'width' => $image['bySize'][$size]['width'],
            'height' => $image['bySize'][$size]['height'],
            'alt' => $image['legend'],
        ];
    }

    protected function getSmartyValue(array $options = [])
    {
        $settings = $this->getSettings();
        $fi = $settings['fallback_image'];
        $size = $settings['image_size'];
        $index = (int) $settings['image_index'] - 1;

        if ($index < 0) {
            return [
                'id' => '',
                // Tmp fix: Absolute URLs need to contain "://"
                'url' => '{*://*}' .
                    "{if \$product.cover}{\$product.cover.bySize.$size.url}" . (empty($fi['url']) ? '' :
                    '{else}{Tools::getShopProtocol()}{Tools::getMediaServer($product.id_product)}' .
                        '{$smarty.const.__PS_BASE_URI__}' . $fi['url']) .
                    '{/if}',
                'alt' => '{if $product.cover}{$product.cover.legend}' . (empty($fi['alt']) ? '' : "{else}{$fi['alt']}") . '{/if}',
                'width' => "{if \$product.cover}{\$product.cover.bySize.$image_size.width}" . (empty($fi['width']) ? '' : "{else}{$fi['width']}") . '{/if}',
                'height' => "{if \$product.cover}{\$product.cover.bySize.$image_size.height}" . (empty($fi['height']) ? '' : "{else}{$fi['height']}") . '{/if}',
            ];
        }

        return [
            'id' => '',
            // Tmp fix: Absolute URLs need to contain "://"
            'url' => '{*://*}' .
                "{if isset(\$product.images.$index)}{\$product.images.$index.bySize.$size.url}" . (!empty($fi['url']) ?
                '{else}{Tools::getShopProtocol()}{Tools::getMediaServer($product.id_product)}' .
                    '{$smarty.const.__PS_BASE_URI__}' . $fi['url'] : '') .
                '{/if}',
            'alt' => "{if isset(\$product.images.$index)}{\$product.images.$index.legend}" . (empty($fi['alt']) ? '' : "{else}{$fi['alt']}") . '{/if}',
        ];
    }
}
