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

/**
 * Email Subscription widget
 *
 * @since 1.0.0
 */
class WidgetEmailSubscription extends WidgetBase
{
    protected $context;

    protected $translator;

    protected $locale;

    protected $gdpr;

    protected $gdpr_msg;

    protected $gdpr_cfg;

    /**
     * Get widget name.
     *
     * @since 1.0.0
     *
     * @return string Widget name
     */
    public function getName()
    {
        return 'email-subscription';
    }

    /**
     * Get widget title.
     *
     * @since 1.0.0
     *
     * @return string Widget title
     */
    public function getTitle()
    {
        return __('Email Subscription');
    }

    /**
     * Get widget icon.
     *
     * @since 1.0.0
     *
     * @return string Widget icon
     */
    public function getIcon()
    {
        return 'eicon-email-field';
    }

    /**
     * Get widget categories.
     *
     * Used to determine where to display the widget in the editor.
     *
     * @since 1.0.0
     *
     * @return array Widget categories
     */
    public function getCategories()
    {
        return ['premium'];
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
        return ['email', 'subscribe', 'signup', 'newsletter'];
    }

    public function getModuleLink($module)
    {
        return empty($this->context->employee) ? '#' : $this->context->link->getAdminLink('AdminModules') . '&configure=' . $module;
    }

    protected function trans($id, array $params = [], $domain = null, $locale = null)
    {
        try {
            return $this->translator->trans($id, $params, $domain, $locale ?: $this->locale);
        } catch (\Exception $ex) {
            return $id;
        }
    }

    /**
     * Register email subscription widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     */
    protected function _registerControls()
    {
        $this->startControlsSection(
            'section_email_subscription',
            [
                'label' => __('Form Fields'),
            ]
        );

        $this->addResponsiveControl(
            'layout',
            [
                'label' => __('Layout'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'inline' => __('Inline'),
                    'multiline' => __('Stacked'),
                ],
                'default' => 'inline',
                'tablet_default' => 'inline',
                'mobile_default' => 'inline',
                'prefix_class' => 'elementor%s-layout-',
            ]
        );

        $this->addControl(
            'input_height',
            [
                'label' => __('Size'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} input[type=email]' => 'height: {{SIZE}}{{UNIT}}; padding: 0 calc({{SIZE}}{{UNIT}} / 3);',
                    '{{WRAPPER}} button[type=submit]' => 'height: {{SIZE}}{{UNIT}}; padding: 0 calc({{SIZE}}{{UNIT}} / 3);',
                ],
            ]
        );

        $this->addControl(
            'heading_email',
            [
                'type' => ControlsManager::HEADING,
                'label' => __('Email'),
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'placeholder',
            [
                'label' => __('Placeholder'),
                'type' => ControlsManager::TEXT,
                'placeholder' => $this->email_placeholder,
            ]
        );

        $this->addResponsiveControl(
            'input_align',
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
                ],
                'selectors' => [
                    '{{WRAPPER}} input[type=email]' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'heading_button',
            [
                'type' => ControlsManager::HEADING,
                'label' => __('Button'),
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'button_type',
            [
                'label' => __('Type'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'classic' => __('Classic'),
                    '' => __('Default'),
                    'primary' => __('Primary'),
                    'secondary' => __('Secondary'),
                ],
                'default' => 'classic',
                'prefix_class' => 'elementor-button-',
            ]
        );

        $this->addControl(
            'button',
            [
                'label' => __('Text'),
                'type' => ControlsManager::TEXT,
                'placeholder' => $this->button_placeholder,
            ]
        );

        $this->addResponsiveControl(
            'button_align',
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
                'prefix_class' => 'elementor%s-align-',
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'layout',
                            'value' => 'multiline',
                        ],
                        [
                            'name' => 'layout_tablet',
                            'value' => 'multiline',
                        ],
                        [
                            'name' => 'layout_mobile',
                            'value' => 'multiline',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'selected_icon',
            [
                'label' => __('Icon'),
                'label_block' => false,
                'type' => ControlsManager::ICONS,
                'skin' => 'inline',
                'fa4compatibility' => 'icon',
                'recommended' => [
                    'ce-icons' => [
                        'caret-right',
                        'angle-right',
                        'chevron-right',
                        'arrow-right',
                        'long-arrow-right',
                    ],
                    'fa-solid' => [
                        'right-to-bracket',
                        'arrow-right-to-bracket',
                        'right-long',
                        'arrow-right-long',
                        'arrow-right',
                        'chevron-right',
                        'caret-right',
                        'angle-right',
                        'angles-right',
                        'square-caret-right',
                        'circle-chevron-right',
                        'circle-arrow-right',
                        'circle-right',
                        'paper-plane',
                    ],
                    'fa-regular' => [
                        'square-caret-right',
                        'circle-right',
                        'paper-plane',
                    ],
                ],
            ]
        );

        $this->addControl(
            'icon_align',
            [
                'label' => __('Icon Position'),
                'type' => ControlsManager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => __('Before'),
                    'right' => __('After'),
                ],
                'condition' => [
                    'selected_icon[value]!' => '',
                ],
            ]
        );

        $this->addControl(
            'icon_indent',
            [
                'label' => __('Icon Spacing'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .elementor-button-text' => 'flex-grow: min(0, {{SIZE}})',
                ],
                'condition' => [
                    'selected_icon[value]!' => '',
                ],
            ]
        );

        $this->addControl(
            'view',
            [
                'label' => __('View'),
                'type' => ControlsManager::HIDDEN,
                'default' => 'traditional',
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_additional_options',
            [
                'label' => __('Additional Options'),
                'type' => ControlsManager::SECTION,
            ]
        );

        $this->addControl(
            'configure_module',
            [
                'label' => __('Email Subscription'),
                'type' => ControlsManager::BUTTON,
                'text' => '<i class="eicon-external-link-square"></i>' . __('Configure'),
                'link' => [
                    'url' => $this->getModuleLink('ps_emailsubscription'),
                    'is_external' => true,
                ],
            ]
        );

        empty($this->gdpr) or $this->addControl(
            'configure_gdpr',
            [
                'label' => __('GDPR'),
                'type' => ControlsManager::BUTTON,
                'text' => '<i class="eicon-external-link-square"></i>' . __('Configure'),
                'link' => [
                    'url' => $this->gdpr_cfg,
                    'is_external' => true,
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_form_style',
            [
                'label' => __('Form'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addResponsiveControl(
            'align',
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
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-widget-container, {{WRAPPER}} .elementor-field-label' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->addResponsiveControl(
            'max_width',
            [
                'label' => __('Max Width'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1600,
                    ],
                    '%' => [
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} form' => 'max-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_input_style',
            [
                'label' => __('Email'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'input_typography',
                'label' => __('Typography'),
                'scheme' => SchemeTypography::TYPOGRAPHY_3,
                'selector' => '{{WRAPPER}} input[type=email]',
            ]
        );

        $this->startControlsTabs('tabs_input_colors');

        $this->startControlsTab(
            'tab_input_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addControl(
            'input_text_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type=email]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} input[type=email]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} input[type=email]:-ms-input-placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} input[type=email]::-ms-input-placeholder ' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'input_background_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type=email]' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'input_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type=email]' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_input_focus',
            [
                'label' => __('Focus'),
            ]
        );

        $this->addControl(
            'input_focus_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type=email]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} input[type=email]::placeholder:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} input[type=email]:-ms-input-placeholder:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} input[type=email]::-ms-input-placeholder:focus ' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'input_background_focus_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type=email]:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'input_focus_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type=email]:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->addControl(
            'input_border_width',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} input[type=email]' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->addResponsiveControl(
            'input_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} input[type=email]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'input_padding',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} input[type=email]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_button_style',
            [
                'label' => __('Button'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addResponsiveControl(
            'button_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]' => 'margin: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0;',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'button_typography',
                'label' => __('Typography'),
                'scheme' => SchemeTypography::TYPOGRAPHY_4,
                'selector' => '{{WRAPPER}} button[type=submit]',
            ]
        );

        $this->startControlsTabs('tabs_button_colors');

        $this->startControlsTab(
            'tab_button_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addControl(
            'button_text_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'button_background_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'button_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_button_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addControl(
            'button_hover_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'button_background_hover_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'button_hover_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'hover_animation',
            [
                'label' => __('Animation'),
                'label_block' => false,
                'type' => ControlsManager::HOVER_ANIMATION,
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->addControl(
            'button_border_width',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->addResponsiveControl(
            'button_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'button_padding',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} button[type=submit]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_gdpr_style',
            [
                'label' => __('GDPR'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'view' => $this->gdpr ? 'traditional' : 'hide',
                ],
            ]
        );

        $this->addControl(
            'row_gap',
            [
                'label' => __('Rows Gap'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-type-gdpr' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'heading_style_label',
            [
                'type' => ControlsManager::HEADING,
                'label' => __('Label'),
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'label_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} label.elementor-field-label' => 'color: {{VALUE}};',
                ],
                'scheme' => [
                    'type' => SchemeColor::getType(),
                    'value' => SchemeColor::COLOR_3,
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'label_typography',
                'scheme' => SchemeTypography::TYPOGRAPHY_3,
                'selector' => '{{WRAPPER}} label.elementor-field-label',
            ]
        );

        $this->addControl(
            'heading_style_checkbox',
            [
                'type' => ControlsManager::HEADING,
                'label' => __('Checkbox'),
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'checkbox_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 5,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'selectors' => !$this->gdpr ? [] : [
                    '{{WRAPPER}} input[type=checkbox]' => 'margin: 0 {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_messages_style',
            [
                'label' => __('Messages'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addControl(
            'messages_position',
            [
                'label' => __('Position'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'before' => __('Before'),
                    'after' => __('After'),
                ],
                'default' => 'after',
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'messages_typography',
                'scheme' => SchemeTypography::TYPOGRAPHY_2,
                'selector' => '{{WRAPPER}} .elementor-message',
            ]
        );

        $this->addControl(
            'heading_style_success',
            [
                'type' => ControlsManager::HEADING,
                'label' => __('Success'),
            ]
        );

        $this->addControl(
            'success_message_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-message.elementor-message-success' => 'color: {{COLOR}};',
                ],
            ]
        );

        $this->addControl(
            'heading_style_error',
            [
                'type' => ControlsManager::HEADING,
                'label' => __('Error'),
            ]
        );

        $this->addControl(
            'error_message_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-message.elementor-message-danger' => 'color: {{COLOR}};',
                ],
            ]
        );

        $this->endControlsSection();
    }

    /**
     * Render email subscription widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     */
    protected function render()
    {
        $settings = $this->getSettingsForDisplay();

        $this->addRenderAttribute('form', [
            'action' => $this->context->link->getModuleLink('creativeelements', 'ajax', [], null, null, null, true),
            'method' => 'post',
            'data-msg' => $settings['messages_position'],
        ]);
        $this->addRenderAttribute('email', [
            'placeholder' => $settings['placeholder'] ?: $this->email_placeholder,
            'inputmode' => 'email',
        ]);
        $this->addRenderAttribute('button', 'class', ['elementor-button', 'elementor-size-sm']);

        if ($settings['hover_animation']) {
            $this->addRenderAttribute('button', 'class', 'elementor-animation-' . $settings['hover_animation']);
        } ?>
        <form class="elementor-email-subscription" <?php $this->printRenderAttributeString('form'); ?>>
            <input type="hidden" name="action" value="0">
            <div class="elementor-field-type-subscribe">
                <input type="email" name="email" class="elementor-field elementor-field-textual" <?php $this->printRenderAttributeString('email'); ?> required>
                <button type="submit" name="submitNewsletter" value="1" <?php $this->printRenderAttributeString('button'); ?>>
                    <span class="elementor-button-content-wrapper">
                    <?php if ($icon = IconsManager::getBcIcon($settings, 'icon', ['aria-hidden' => 'true'])) { ?>
                        <span class="elementor-align-icon-<?php echo esc_attr($this->getSettings('icon_align')); ?>"><?php echo $icon; ?></span>
                    <?php } ?>
                    <?php if (trim($settings['button']) || !$settings['button']) { ?>
                        <span class="elementor-button-text"><?php echo $settings['button'] ?: $this->button_placeholder; ?></span>
                    <?php } ?>
                    </span>
                </button>
            </div>
        <?php if ($this->gdpr) { ?>
            <div class="elementor-field-type-gdpr">
                <label class="elementor-field-label">
                    <input type="checkbox" name="<?php echo $this->gdpr; ?>" value="1" required><span class="elementor-checkbox-label"><?php echo $this->gdpr_msg; ?></span>
                </label>
            </div>
        <?php } ?>
        </form>
        <?php
    }

    /**
     * Render email subscription widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since 2.9.0
     */
    protected function contentTemplate()
    {
        ?>
        <# var placeholder = settings.placeholder || <?php echo json_encode($this->email_placeholder); ?> #>
        <form class="elementor-email-subscription">
            <div class="elementor-field-type-subscribe">
                <input type="email" placeholder="{{ placeholder }}" class="elementor-field elementor-field-textual" required>
                <button type="submit" class="elementor-button elementor-size-sm elementor-animation-{{ settings.hover_animation }}">
                    <span class="elementor-button-content-wrapper">
                    <# if (settings.icon || settings.selected_icon.value) { #>
                        <span class="elementor-button-icon elementor-align-icon-{{ settings.icon_align }}">
                            {{{ elementor.helpers.getBcIcon(view, settings, 'icon', {'aria-hidden': true}) }}}
                        </span>
                    <# } #>
                    <# if (settings.button.trim() || !settings.button) { #>
                        <span class="elementor-button-text">{{ settings.button || <?php echo json_encode($this->button_placeholder); ?> }}</span>
                    <# } #>
                    </span>
                </button>
            </div>
        <?php if ($this->gdpr) { ?>
            <div class="elementor-field-type-gdpr">
                <label class="elementor-field-label">
                    <input type="checkbox"><span class="elementor-checkbox-label"><?php echo $this->gdpr_msg; ?></span>
                </label>
            </div>
        <?php } ?>
        </form>
        <?php
    }

    public function __construct($data = [], $args = [])
    {
        $this->context = \Context::getContext();
        $this->translator = $this->context->getTranslator();

        $id_lang = (int) \Tools::getValue('id_lang');
        $lang = $id_lang ? new \Language($id_lang) : null;
        $this->locale = isset($lang->locale) ? $lang->locale : null;
        $this->email_placeholder = $this->trans('Your email address', [], 'Shop.Forms.Labels');
        $this->button_placeholder = $this->trans('Subscribe', [], 'Shop.Theme.Actions');

        $this->initGDPR($id_lang);

        parent::__construct($data, $args);
    }

    protected function initGDPR($id_lang)
    {
        empty($id_lang) && $id_lang = $this->context->language->id;

        if (\Module::isEnabled('psgdpr') && \Module::getInstanceByName('psgdpr') &&
            call_user_func('GDPRConsent::getConsentActive', $id_module = \Module::getModuleIdByName('ps_emailsubscription'))
        ) {
            $this->gdpr = 'psgdpr_consent_checkbox';
            $this->gdpr_msg = call_user_func('GDPRConsent::getConsentMessage', $id_module, $id_lang);
            $this->gdpr_cfg = $this->getModuleLink('psgdpr&page=dataConsent');
        } elseif (\Module::isEnabled('gdprpro') && \Configuration::get('gdpr-pro_consent_newsletter_enable')) {
            $this->gdpr = 'gdpr_consent_chkbox';
            $this->gdpr_msg = \Configuration::get('gdpr-pro_consent_newsletter_text', $id_lang);
            $this->gdpr_cfg = empty($this->context->employee) ? '#' : $this->context->link->getAdminLink('AdminGdprConfig');
        }

        // Strip <p> tags from GDPR message
        empty($this->gdpr_msg) or $this->gdpr_msg = preg_replace('~</?p\b.*?>~i', '', $this->gdpr_msg);
    }
}
