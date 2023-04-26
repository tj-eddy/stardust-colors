<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks, Elementor
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */

namespace CE;

defined('_PS_VERSION_') or exit;

/** @deprecated 2.5.10 Use `WidgetManufacturerImage` instead. */
class WidgetProductBrandImage extends WidgetManufacturerImage
{
    public function getName()
    {
        return 'product-brand-image';
    }

    public function getCategories()
    {
        return [];
    }
}
