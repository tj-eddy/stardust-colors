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
use CE\ModulesXCatalogXControlsXSelectCategory as SelectCategory;
use CE\ModulesXDynamicTagsXModule as Module;

class ModulesXCatalogXTagsXCategoryImages extends DataTag
{
    const REMOTE_RENDER = true;

    public function getName()
    {
        return 'category-images';
    }

    public function getTitle()
    {
        return __('Category Images');
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
            'id_category',
            [
                'label' => __('Parent Category'),
                'label_block' => true,
                'type' => SelectCategory::CONTROL_TYPE,
                'select2options' => [
                    'allowClear' => false,
                ],
                'extend' => [
                    '0' => __('Current Category') . ' / ' . __('Default'),
                ],
                'default' => 0,
            ]
        );

        $image_size_options = GroupControlImageSize::getAllImageSizes('categories');

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

        if ($settings['id_category'] > 0) {
            $id_category = $settings['id_category'];
        } elseif ($context->controller instanceof \CategoryController) {
            $id_category = $context->controller->getCategory()->id;
        } elseif ($context->controller instanceof \ProductController) {
            $id_category = $context->controller->getProduct()->id_category_default;
        } else {
            $id_category = $context->shop->id_category;
        }
        $categories = \Category::getChildren($id_category, $context->language->id, true, $context->shop->id);
        $image_size = $settings['image_size'];
        $image_type = $this->getControls('image_size')['options'][$image_size];
        $width = (int) substr($image_type, strrpos($image_type, '(') + 1);
        $height = (int) substr($image_type, strrpos($image_type, '×') + 2);
        $items = [];

        foreach ($categories as &$cat) {
            $items[] = [
                'image' => [
                    'id' => '',
                    'url' => $context->link->getCatImageLink($cat['link_rewrite'], $cat['id_category'], $image_size),
                    'alt' => $cat['name'],
                    'width' => $width,
                    'height' => $height,
                ],
                'link' => [
                    'url' => $context->link->getCategoryLink(
                        $cat['id_category'],
                        $cat['link_rewrite'],
                        $context->language->id,
                        null,
                        $context->shop->id
                    ),
                ],
                'caption' => $settings['show_caption'] ? $cat['name'] : '',
            ];
        }

        return $items;
    }
}
