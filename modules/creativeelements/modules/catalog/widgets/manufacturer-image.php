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

class WidgetManufacturerImage extends WidgetImage
{
    public function getName()
    {
        return 'manufacturer-image';
    }

    public function getTitle()
    {
        return __('Brand Image');
    }

    public function getIcon()
    {
        return 'eicon-favorite';
    }

    public function getCategories()
    {
        return ['product-elements'];
    }

    public function getKeywords()
    {
        return ['shop', 'store', 'brand', 'manufacturer', 'image', 'picture', 'product'];
    }

    protected function _registerControls()
    {
        parent::_registerControls();

        $this->updateControl(
            'image',
            [
                'label' => '',
                'default' => [
                    'url' => '',
                ],
                'dynamic' => [
                    'default' => Plugin::$instance->dynamic_tags->tagDataToTagText(null, 'manufacturer-image'),
                ],
            ],
            [
                'recursive' => true,
            ]
        );

        $this->addControl(
            'show_caption',
            [
                'label' => __('Caption'),
                'type' => ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
            ],
            [
                'position' => ['of' => 'align'],
            ]
        );

        $this->updateControl(
            'caption',
            [
                'label' => '',
                'dynamic' => [
                    'default' => Plugin::$instance->dynamic_tags->tagDataToTagText(null, 'manufacturer-name'),
                ],
                'condition' => [
                    'show_caption!' => '',
                ],
            ],
            [
                'recursive' => true,
            ]
        );

        $this->updateControl(
            'link_to',
            [
                'options' => [
                    'none' => __('None'),
                    'custom' => __('Brand'),
                ],
                'default' => 'custom',
            ]
        );

        $this->updateControl(
            'link',
            [
                'dynamic' => [
                    'default' => Plugin::$instance->dynamic_tags->tagDataToTagText(null, 'manufacturer-url'),
                ],
            ],
            [
                'recursive' => true,
            ]
        );

        $this->updateControl('text_color', ['scheme' => '']);

        $this->updateControl('caption_typography_font_family', ['scheme' => '']);
        $this->updateControl('caption_typography_font_weight', ['scheme' => '']);
    }

    protected function getHtmlWrapperClass()
    {
        return parent::getHtmlWrapperClass() . ' elementor-widget-image';
    }

    protected function render()
    {
        // Backward compatibility fix
        $this->getSettings('__dynamic__') or $this->setSettings('__dynamic__', [
            'image' => Plugin::$instance->dynamic_tags->tagDataToTagText(null, 'manufacturer-image'),
            'caption' => Plugin::$instance->dynamic_tags->tagDataToTagText(null, 'manufacturer-name'),
            'link' => Plugin::$instance->dynamic_tags->tagDataToTagText(null, 'manufacturer-url'),
        ]);

        parent::render();
    }

    protected function renderSmarty()
    {
        echo '{if $product.id_manufacturer}';

        parent::render();

        echo '{/if}';
    }

    protected function contentTemplate()
    {
        ?>
        <# settings.caption = settings.show_caption && settings.caption #>
        <?php
        parent::contentTemplate();
    }

    public function renderPlainContent()
    {
    }
}
