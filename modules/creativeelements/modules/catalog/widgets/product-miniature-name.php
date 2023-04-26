<?php
/**
 * Creative Elements - live PageBuilder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */

namespace CE;

defined('_PS_VERSION_') or exit;

include_once __DIR__ . '/product-name.php';

class WidgetProductMiniatureName extends WidgetProductName
{
    public function getName()
    {
        return 'product-miniature-name';
    }

    protected function _registerControls()
    {
        parent::_registerControls();

        $this->updateControl('link_to', [
            'default' => 'custom',
        ]);

        $this->updateControl('header_size', [
            'default' => 'h3',
        ]);

        $this->updateControl('title_multiline', [
            'default' => '',
        ]);
    }
}
