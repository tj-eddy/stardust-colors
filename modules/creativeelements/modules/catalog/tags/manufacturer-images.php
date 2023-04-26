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

class ModulesXCatalogXTagsXManufacturerImages extends DataTag
{
    const REMOTE_RENDER = true;

    public function getName()
    {
        return 'manufacturer-images';
    }

    public function getTitle()
    {
        return __('Brand Images');
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
        $image_size_options = GroupControlImageSize::getAllImageSizes('manufacturers');

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
        $manufacturers = \Manufacturer::getManufacturers(false, $context->language->id);
        $image_size = $this->getSettings('image_size');
        $image_type = $this->getControls('image_size')['options'][$image_size];
        $width = (int) substr($image_type, strrpos($image_type, '(') + 1);
        $height = (int) substr($image_type, strrpos($image_type, '×') + 2);
        $caption = $this->getSettings('show_caption');
        $items = [];

        foreach ($manufacturers as &$manufacturer) {
            $items[] = [
                'image' => [
                    'id' => '',
                    'url' => $context->link->getManufacturerImageLink($manufacturer['id_manufacturer'], $image_size),
                    'alt' => $manufacturer['name'],
                    'width' => $width,
                    'height' => $height,
                ],
                'link' => [
                    'url' => $context->link->getManufacturerLink(
                        $manufacturer['id_manufacturer'],
                        $manufacturer['link_rewrite'],
                        $context->language->id,
                        $context->shop->id
                    ),
                ],
                'caption' => $caption ? $manufacturer['name'] : '',
            ];
        }

        return $items;
    }
}
