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

class ModulesXCatalogXTagsXProductImages extends DataTag
{
    const REMOTE_RENDER = true;

    public function getName()
    {
        return 'product-images';
    }

    public function getTitle()
    {
        return __('Product Images');
    }

    public function getGroup()
    {
        return Module::CATALOG_GROUP;
    }

    public function getCategories()
    {
        return [Module::GALLERY_CATEGORY];
    }

    protected function _registerControls()
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

        $image_size_options = GroupControlImageSize::getAllImageSizes('products');

        $this->addControl(
            'image_size',
            [
                'label' => __('Image Size'),
                'label_block' => true,
                'type' => ControlsManager::SELECT,
                'options' => $image_size_options,
                'default' => key($image_size_options),
            ]
        );

        $this->addControl(
            'show_caption',
            [
                'label' => __('Caption'),
                'type' => ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
                'default' => 'yes',
            ]
        );
    }

    public function getValue(array $options = [])
    {
        $context = \Context::getContext();
        $settings = $this->getSettings();
        $items = [];
        $product = !$settings['id_product'] && $context->controller instanceof \ProductController
            ? $context->controller->getProduct()
            : new \Product($settings['id_product'], false, $context->language->id)
        ;
        if (!$images = $product->getImages($context->language->id)) {
            return $items;
        }
        $imageRetriever = new \PrestaShop\PrestaShop\Adapter\Image\ImageRetriever($context->link);

        foreach ($images as &$image) {
            $legend = $image['legend'];

            if (!$image = $imageRetriever->getImage($product, $image['id_image'])) {
                continue;
            }
            $bySize = $image['bySize'][$settings['image_size']];
            $items[] = [
                'image' => [
                    'id' => '',
                    'url' => $bySize['url'],
                    'alt' => $legend,
                    'width' => $bySize['width'],
                    'height' => $bySize['height'],
                ],
                'link' => [
                    'url' => Helper::getProductImageLink($image),
                ],
                'caption' => $settings['show_caption'] ? $legend : '',
            ];
        }

        return $items;
    }
}
