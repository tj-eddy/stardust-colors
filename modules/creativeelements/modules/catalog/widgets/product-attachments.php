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

class WidgetProductAttachments extends WidgetIconBox
{
    const REMOTE_RENDER = true;

    public function getName()
    {
        return 'product-attachments';
    }

    public function getTitle()
    {
        return __('Product Attachments');
    }

    public function getIcon()
    {
        return 'eicon-download-button';
    }

    public function getCategories()
    {
        return ['product-elements'];
    }

    public function getKeywords()
    {
        return ['shop', 'store', 'attachment', 'download', 'document', 'product'];
    }

    protected function _registerControls()
    {
        $this->startControlsSection('section_title');

        $this->endControlsSection();

        $this->startControlsSection(
            'section_title_style',
            [
                'label' => __('Title'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'title_text!' => '',
                ],
            ]
        );

        $this->addResponsiveControl(
            'heading_align',
            [
                'label' => __('Alignment'),
                'type' => ControlsManager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-heading-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'heading_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    // Stronger selector to avoid section style from overwriting
                    '{{WRAPPER}}.elementor-widget-heading .elementor-heading-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'heading_typography',
                'selector' => '{{WRAPPER}} .elementor-heading-title',
            ]
        );

        $this->addGroupControl(
            GroupControlTextShadow::getType(),
            [
                'name' => 'heading_text_shadow',
                'selector' => '{{WRAPPER}} .elementor-heading-title',
            ]
        );

        $this->addResponsiveControl(
            'heading_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-heading-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->endControlsSection();

        parent::_registerControls();

        $this->removeControl('section_title');
        $this->removeControl('title_text');
        $this->removeControl('description_text');
        $this->removeControl('link');
        $this->removeControl('position');

        $this->updateControl(
            'section_icon',
            [
                'label' => __('Product Attachments'),
            ]
        );

        $this->addControl(
            'title_text',
            [
                'label' => __('Title'),
                'type' => ControlsManager::TEXT,
                'placeholder' => __('Enter your title'),
                'label_block' => true,
            ],
            [
                'position' => [
                    'of' => 'selected_icon',
                    'at' => 'before',
                ],
            ]
        );

        $this->updateControl(
            'selected_icon',
            [
                'default' => [
                    'value' => 'fas fa-download',
                    'library' => 'fa-solid',
                ],
                'recommended' => [
                    'fa-solid' => [
                        'file-pdf',
                        'download',
                        'file-arrow-down',
                        'cloud-arrow-down',
                        'paperclip',
                        'link',
                    ],
                ],
            ]
        );

        $this->updateControl(
            'icon_size',
            [
                'default' => [
                    'size' => 20,
                ],
            ]
        );

        $this->addResponsiveControl(
            'space_between',
            [
                'label' => __('Space Between'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-attachment:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ],
            [
                'position' => [
                    'of' => 'content_vertical_alignment',
                ],
            ]
        );

        $this->updateControl(
            'heading_title',
            [
                'label' => __('Label'),
            ]
        );

        $this->updateControl('primary_color', ['scheme' => '']);
        $this->updateControl('title_color', ['scheme' => '']);
        $this->updateControl('description_color', ['scheme' => '']);

        $this->updateControl('title_typography_font_family', ['scheme' => '']);
        $this->updateControl('title_typography_font_weight', ['scheme' => '']);

        $this->updateControl('description_typography_font_family', ['scheme' => '']);
        $this->updateControl('description_typography_font_weight', ['scheme' => '']);
    }

    protected function getHtmlWrapperClass()
    {
        return parent::getHtmlWrapperClass() . ' elementor-widget-heading elementor-widget-icon-box' .
            ' elementor-position-' . (is_rtl() ? 'right' : 'left');
    }

    protected function render()
    {
        $product = &\Context::getContext()->smarty->tpl_vars['product']->value;

        if (!$product['attachments']) {
            return;
        }
        $settings = $this->getSettingsForDisplay();

        $this->addRenderAttribute('icon', 'class', [
            'elementor-icon',
            'elementor-animation-' . $settings['hover_animation'],
        ]);
        $icon = IconsManager::getBcIcon($settings, 'icon', ['aria-hidden' => 'true']);

        if ($settings['title_text']) {
            $this->addRenderAttribute('title_text', 'class', 'elementor-heading-title');
            empty($settings['title_display']) or $this->addRenderAttribute('title_text', [
                'class' => $settings['title_display'],
            ]);
            $this->addInlineEditingAttributes('title_text', 'none'); ?>
            <<?php echo $settings['title_size']; ?> <?php $this->printRenderAttributeString('title_text'); ?>>
                <?php echo $settings['title_text']; ?>
            </<?php echo $settings['title_size']; ?>>
            <?php
        }
        foreach ($product['attachments'] as $attachment) {
            $url = \Link::getUrlSmarty([
                'entity' => 'attachment',
                'params' => ['id_attachment' => $attachment['id_attachment']],
            ]); ?>
            <div class="ce-attachment elementor-icon-box-wrapper">
            <?php if ($icon) { ?>
                <div class="elementor-icon-box-icon">
                    <a <?php $this->printRenderAttributeString('icon'); ?> href="<?php echo esc_attr($url); ?>"><?php echo $icon; ?></a>
                </div>
            <?php } ?>
                <div class="elementor-icon-box-content">
                    <a class="elementor-icon-box-title" href="<?php echo esc_attr($url); ?>"><?php echo $attachment['name']; ?></a>
                    <p class="elementor-icon-box-description"><?php echo $attachment['description']; ?></p>
                </div>
            </div>
            <?php
        }
    }

    public function renderPlainContent()
    {
    }

    protected function contentTemplate()
    {
    }
}
