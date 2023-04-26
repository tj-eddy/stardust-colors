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

class WidgetProductVariants extends WidgetBase
{
    const REMOTE_RENDER = true;

    /**
     * Get widget name.
     *
     * @since 2.5.8
     *
     * @return string Widget name
     */
    public function getName()
    {
        return 'product-variants';
    }

    /**
     * Get widget title.
     *
     * @since 2.5.8
     *
     * @return string Widget title
     */
    public function getTitle()
    {
        return __('Product Variations');
    }

    /**
     * Get widget icon.
     *
     * @since 2.5.8
     *
     * @return string Widget icon
     */
    public function getIcon()
    {
        return 'eicon-select';
    }

    /**
     * Get widget categories.
     *
     * @since 2.5.8
     *
     * @return array Widget categories
     */
    public function getCategories()
    {
        return ['product-elements'];
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     *
     * @since 2.1.0
     *
     * @return array Widget keywords
     */
    public function getKeywords()
    {
        return ['shop', 'store', 'product', 'variations', 'variants'];
    }

    /**
     * Register product variations widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 2.5.8
     */
    protected function _registerControls()
    {
        $this->startControlsSection(
            'section_variations_style',
            [
                'label' => __('Product Variations'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addControl(
            'layout',
            [
                'label' => __('Layout'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'inline' => __('Inline'),
                    'stacked' => __('Stacked'),
                    'table' => __('Table'),
                ],
                'default' => 'stacked',
                'prefix_class' => 'ce-product-variants--layout-',
                'render_type' => 'template',
            ]
        );

        $this->addControl(
            'show_value',
            [
                'label' => __('Value'),
                'type' => ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__label::after' => 'content: ":"',
                ],
                'condition' => [
                    'layout' => 'stacked',
                ],
                'render_type' => 'template',
            ]
        );

        $this->addControl(
            'space_between',
            [
                'label' => __('Space Between'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__item' => 'margin: 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0',
                    '{{WRAPPER}} .ce-product-variants' => 'margin: 0 -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}} 0',
                ],
            ]
        );

        $this->startControlsTabs('tabs_label');

        $this->startControlsTab(
            'tab_label',
            [
                'label' => __('Label'),
            ]
        );

        $this->addControl(
            'label_inline',
            [
                'label' => __('Inline'),
                'type' => ControlsManager::SWITCHER,
                'return_value' => 'inline',
                'prefix_class' => 'ce-product-variants--label-',
                'condition' => [
                    'layout' => 'inline',
                ],
            ]
        );

        $this->addControl(
            'label_vertical_align',
            [
                'label' => __('Vertical Align'),
                'type' => ControlsManager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'flex-start' => [
                        'title' => __('Start'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => __('Center'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'flex-end' => [
                        'title' => __('End'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__item' => 'align-items: {{VALUE}}',
                ],
                'condition' => [
                    'layout!' => 'stacked',
                    'label_inline!' => '',
                ],
            ]
        );

        $this->addControl(
            'label_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}}.ce-product-variants--layout-stacked .ce-product-variants__label' => 'display: inline-block; margin-bottom: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}}.ce-product-variants--layout-inline:not(.ce-product-variants--label-inline) .ce-product-variants__label' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                    'body:not(.lang-rtl) {{WRAPPER}}.ce-product-variants--label-inline .ce-product-variants__label' => 'margin-right: {{SIZE}}{{UNIT}}',
                    'body.lang-rtl {{WRAPPER}}.ce-product-variants--label-inline .ce-product-variants__label' => 'margin-left: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'layout!' => 'table',
                ],
            ]
        );

        $this->addResponsiveControl(
            'label_width',
            [
                'label' => __('Min Width'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'max' => 500,
                    ],
                ],
                'default' => [
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__label' => 'min-width: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'layout' => 'table',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .ce-product-variants__label',
            ]
        );

        $this->addControl(
            'label_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__label' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_value',
            [
                'label' => __('Value'),
                'condition' => [
                    'layout' => 'stacked',
                    'show_value!' => '',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'value_typography',
                'selector' => '{{WRAPPER}} .ce-product-variants__value',
            ]
        );

        $this->addControl(
            'value_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__value' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        is_admin() && $this->addControl(
            'configure',
            [
                'label' => __('Product Attributes'),
                'type' => ControlsManager::BUTTON,
                'text' => '<i class="eicon-external-link-square"></i>' . __('Configure'),
                'link' => [
                    'url' => \Context::getContext()->link->getAdminLink('AdminAttributesGroups'),
                    'is_external' => true,
                ],
                'separator' => 'before',
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_select_style',
            [
                'label' => __('Drop-down List'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addControl(
            'select_size',
            [
                'label' => __('Size'),
                'type' => ControlsManager::SELECT,
                'default' => 'sm',
                'options' => [
                    'xs' => __('Extra Small'),
                    'sm' => __('Small'),
                    'md' => __('Medium'),
                    'lg' => __('Large'),
                    'xl' => __('Extra Large'),
                ],
            ]
        );

        $this->addControl(
            'select_width',
            [
                'label' => __('Width'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'select_typography',
                'selector' => '{{WRAPPER}} select.elementor-field',
            ]
        );

        $this->startControlsTabs('tabs_style_select');

        $this->startControlsTab(
            'tab_select_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addControl(
            'select_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'select_bg_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'select_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'default' => '#818a91',
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_select_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addControl(
            'select_color_hover',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'select_bg_color_hover',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'select_border_color_hover',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->addControl(
            'select_border_width',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field' => 'border-style: solid; border-width: {{SIZE}}{{UNIT}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'select_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addResponsiveControl(
            'select_padding',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} select.elementor-field' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlBoxShadow::getType(),
            [
                'name' => 'select_box_shadow',
                'selector' => '{{WRAPPER}} select.elementor-field',
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_radio_style',
            [
                'label' => __('Radio Buttons'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addControl(
            'radio_space_between',
            [
                'label' => __('Space Between'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'selectors' => [
                    'body:not(.lang-rtl) {{WRAPPER}} .ce-product-variants__options label' => 'margin: 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0',
                    'body:not(.lang-rtl) {{WRAPPER}} .ce-product-variants__options' => 'margin: 0 -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}} 0',
                    'body.lang-rtl {{WRAPPER}} .ce-product-variants__options label' => 'margin: 0 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}}',
                    'body.lang-rtl {{WRAPPER}} .ce-product-variants__options' => 'margin: 0 0 -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'radio_min_width',
            [
                'label' => __('Min Width'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__option' => 'min-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'radio_typography',
                'selector' => '{{WRAPPER}} .ce-product-variants__option',
            ]
        );

        $this->startControlsTabs('tabs_style_radio');

        $this->startControlsTab(
            'tab_radio_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addControl(
            'radio_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__option' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'radio_bg_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__option' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'radio_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'default' => '#818a91',
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__option' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_radio_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addControl(
            'radio_color_hover',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} label:hover .ce-product-variants__option' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'radio_bg_color_hover',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} label:hover .ce-product-variants__option' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'radio_border_color_hover',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} label:hover .ce-product-variants__option' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_radio_active',
            [
                'label' => __('Active'),
            ]
        );

        $this->addControl(
            'radio_color_active',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input:checked ~ .ce-product-variants__option' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'radio_bg_color_active',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input:checked ~ .ce-product-variants__option' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'radio_border_color_active',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'default' => '#5bc0de',
                'selectors' => [
                    '{{WRAPPER}} input:checked ~ .ce-product-variants__option' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->addControl(
            'radio_border_width',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__option' => 'border-style: solid; border-width: {{SIZE}}{{UNIT}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'radio_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__option' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addResponsiveControl(
            'radio_padding',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::DIMENSIONS,
                'default' => [
                    'top' => 5,
                    'right' => 10,
                    'bottom' => 5,
                    'left' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__option' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlBoxShadow::getType(),
            [
                'name' => 'radio_box_shadow',
                'selector' => '{{WRAPPER}} .ce-product-variants__option',
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_pattern_style',
            [
                'label' => __('Color/Texture Buttons'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addControl(
            'pattern_space_between',
            [
                'label' => __('Space Between'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'selectors' => [
                    'body:not(.lang-rtl) {{WRAPPER}} .ce-product-variants__patterns label' => 'margin: 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0',
                    'body:not(.lang-rtl) {{WRAPPER}} .ce-product-variants__patterns' => 'margin: 0 -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}} 0',
                    'body.lang-rtl {{WRAPPER}} .ce-product-variants__patterns label' => 'margin: 0 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}}',
                    'body.lang-rtl {{WRAPPER}} .ce-product-variants__patterns' => 'margin: 0 0 -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->startControlsTabs('tabs_style_pattern');

        $this->startControlsTab(
            'tab_pattern_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addControl(
            'pattern_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'default' => '#818a91',
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__pattern' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'pattern_border_width',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__pattern' => 'border-style: solid; border-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'pattern_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__pattern' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'pattern_padding',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__pattern' => 'padding: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_pattern_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addControl(
            'pattern_border_color_hover',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} label:hover .ce-product-variants__pattern' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'pattern_border_width_hover',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} label:hover .ce-product-variants__pattern' => 'border-style: solid; border-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'pattern_border_radius_hover',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} label:hover .ce-product-variants__pattern' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'pattern_padding_hover',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} label:hover .ce-product-variants__pattern' => 'padding: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_pattern_active',
            [
                'label' => __('Active'),
            ]
        );

        $this->addControl(
            'pattern_border_color_active',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'default' => '#5bc0de',
                'selectors' => [
                    '{{WRAPPER}} input:checked ~ .ce-product-variants__pattern' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'pattern_border_width_active',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} input:checked ~ .ce-product-variants__pattern' => 'border-style: solid; border-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'pattern_border_radius_active',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} input:checked ~ .ce-product-variants__pattern' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'pattern_padding_active',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} input:checked ~ .ce-product-variants__pattern' => 'padding: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->addResponsiveControl(
            'pattern_size',
            [
                'label' => __('Size'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 30,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ce-product-variants__pattern' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->addGroupControl(
            GroupControlBoxShadow::getType(),
            [
                'name' => 'pattern_box_shadow',
                'selector' => '{{WRAPPER}} .ce-product-variants__pattern',
            ]
        );

        $this->endControlsSection();
    }

    /**
     * Get HTML wrapper class.
     *
     * Retrieve the widget container class.
     *
     * @since 2.5.8
     */
    protected function getHtmlWrapperClass()
    {
        return parent::getHtmlWrapperClass() . ' elementor-overflow-hidden';
    }

    /**
     * Render product variations widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 2.5.8
     */
    protected function render()
    {
        $settings = $this->getSettingsForDisplay();
        $context = \Context::getContext();
        $groups = &$context->smarty->tpl_vars['groups']->value; ?>
        <div class="ce-product-variants">
        <?php foreach ($groups as $id_attr_group => $group) { ?>
            <?php if (!empty($group['attributes'])) { ?>
                <div class="ce-product-variants__item">
                    <span class="ce-product-variants__label"><?php echo esc_html($group['name']); ?></span>
                <?php if ('stacked' === $settings['layout'] && $settings['show_value']) { ?>
                    <span class="ce-product-variants__value">
                    <?php foreach ($group['attributes'] as $value) { ?>
                        <?php echo $value['selected'] ? $value['name'] : ''; ?>
                    <?php } ?>
                    </span>
                <?php } ?>
                <?php if ('select' === $group['group_type']) { ?>
                    <div class="ce-product-variants__select">
                        <select class="elementor-field elementor-field-textual elementor-size-<?php echo esc_attr($settings['select_size']); ?>" oninput="$(this.form[this.name]).val(this.value)"
                            form="add-to-cart-or-refresh" name="group[<?php echo (int) $id_attr_group; ?>]" data-product-attribute="<?php echo (int) $id_attr_group; ?>">
                        <?php foreach ($group['attributes'] as $id_attr => $group_attr) { ?>
                            <option <?php echo $group_attr['selected'] ? 'selected' : ''; ?> value="<?php echo (int) $id_attr; ?>"><?php echo esc_html($group_attr['name']); ?></option>
                        <?php } ?>
                        </select>
                    </div>
                <?php } elseif ('radio' === $group['group_type']) { ?>
                    <div class="ce-product-variants__options">
                    <?php foreach ($group['attributes'] as $id_attr => $group_attr) { ?>
                        <label class="ce-product-variants__radio-wrapper" aria-label="<?php echo esc_attr($group_attr['name']); ?>">
                            <input class="ce-product-variants__radio" form="add-to-cart-or-refresh" type="radio" <?php echo $group_attr['selected'] ? 'checked' : ''; ?>
                                name="group[<?php echo (int) $id_attr_group; ?>]" value="<?php echo (int) $id_attr; ?>" data-product-attribute="<?php echo (int) $id_attr_group; ?>">
                            <span class="ce-product-variants__option"><?php echo esc_html($group_attr['name']); ?></span>
                        </label>
                    <?php } ?>
                    </div>
                <?php } elseif ('color' === $group['group_type']) { ?>
                    <div class="ce-product-variants__patterns">
                    <?php foreach ($group['attributes'] as $id_attr => $group_attr) { ?>
                        <label class="ce-product-variants__radio-wrapper" title="<?php echo esc_attr($group_attr['name']); ?>">
                            <input class="ce-product-variants__radio" form="add-to-cart-or-refresh" type="radio" <?php echo $group_attr['selected'] ? 'checked' : ''; ?>
                                name="group[<?php echo (int) $id_attr_group; ?>]" value="<?php echo (int) $id_attr; ?>" data-product-attribute="<?php echo (int) $id_attr_group; ?>">
                        <?php if ($group_attr['html_color_code']) { ?>
                            <span class="ce-product-variants__pattern ce-product-variants__color" style="background-color: <?php echo esc_attr($group_attr['html_color_code']); ?>"></span>
                        <?php } elseif ($group_attr['texture']) { ?>
                            <span class="ce-product-variants__pattern ce-product-variants__texture" style="background-image: url(<?php echo esc_attr($group_attr['texture']); ?>)"></span>
                        <?php } ?>
                        </label>
                    <?php } ?>
                    </div>
                <?php } ?>
                </div>
            <?php } ?>
        <?php } ?>
        </div>
        <?php
    }

    public function renderPlainContent()
    {
    }
}
