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

use CE\ModulesXCatalogXDocumentsXProduct as ProductDocument;

class ModulesXCatalogXDocumentsXProductMiniature extends ProductDocument
{
    public function getName()
    {
        return 'product-miniature';
    }

    public static function getTitle()
    {
        return __('Product Miniature');
    }

    protected static function getEditorPanelCategories()
    {
        $categories = [
            'product-elements' => [
                'title' => __('Product Miniature'),
            ],
        ];

        $categories += parent::getEditorPanelCategories();

        return $categories;
    }

    protected function getRemoteLibraryConfig()
    {
        $config = parent::getRemoteLibraryConfig();

        $config['category'] = 'miniature';

        return $config;
    }

    public function getCssWrapperSelector()
    {
        return '.elementor.elementor-' . uidval($this->getMainId())->toDefault();
    }

    public function getWpPreviewUrl()
    {
        $main_post_id = $this->getMainId();
        $uid = UId::parse($main_post_id);
        $context = \Context::getContext();
        $category = new \Category($context->shop->id_category, $uid->id_lang);
        $url = \Tools::url($context->link->getCategoryLink($category), http_build_query([
            'id_employee' => $context->employee->id,
            'cetoken' => \Tools::getAdminTokenLite('AdminCEThemes'),
            'id_miniature' => $uid->id,
        ]));

        /*
         * Document "PrestaShop preview" URL.
         *
         * Filters the PrestaShop preview URL.
         *
         * @since 2.0.0
         *
         * @param string   $url  PrestaShop preview URL
         * @param Document $this The document instance
         */
        $url = apply_filters('elementor/document/urls/wp_preview', $url, $this);

        return $url;
    }

    protected function _registerControls()
    {
        parent::_registerControls();

        $this->startInjection([
            'of' => 'preview_id',
            'at' => 'before',
        ]);

        $this->addResponsiveControl(
            'preview_width',
            [
                'label' => __('Width') . ' (px)',
                'type' => ControlsManager::NUMBER,
                'min' => 150,
                'default' => 360,
                'tablet_default' => 360,
                'mobile_default' => 360,
            ]
        );

        $this->endInjection();

        $this->startControlsSection(
            'section_style',
            [
                'label' => __('Background'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->startControlsTabs('tabs_background');

        $this->startControlsTab(
            'tab_background_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addGroupControl(
            GroupControlBackground::getType(),
            [
                'name' => 'background',
                'selector' => '{{WRAPPER}} .elementor-section-wrap',
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_background_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addGroupControl(
            GroupControlBackground::getType(),
            [
                'name' => 'background_hover',
                'selector' => '{{WRAPPER}} .elementor-section-wrap:hover',
            ]
        );

        $this->addControl(
            'background_hover_transition',
            [
                'label' => __('Transition Duration'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 0.3,
                ],
                'range' => [
                    'px' => [
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'separator' => [
                    '{{WRAPPER}} .elementor-section-wrap' => '--e-background-transition-duration: {{SIZE}}s;',
                ],
                'condition' => [
                    'background_hover_background!' => '',
                ],
                'separator' => 'before',
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->endControlsSection();

        $this->startControlsSection(
            'section_border',
            [
                'label' => __('Border'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->startControlsTabs('tabs_border');

        $this->startControlsTab(
            'tab_border_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addGroupControl(
            GroupControlBorder::getType(),
            [
                'name' => 'border',
                'selector' => '{{WRAPPER}} .elementor-section-wrap',
            ]
        );

        $this->addResponsiveControl(
            'border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-section-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlBoxShadow::getType(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}} .elementor-section-wrap',
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_border_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addGroupControl(
            GroupControlBorder::getType(),
            [
                'name' => 'border_hover',
                'selector' => '{{WRAPPER}} .elementor-section-wrap:hover',
            ]
        );

        $this->addResponsiveControl(
            'border_radius_hover',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-section-wrap:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlBoxShadow::getType(),
            [
                'name' => 'box_shadow_hover',
                'selector' => '{{WRAPPER}} .elementor-section-wrap:hover',
            ]
        );

        $this->addControl(
            'border_hover_transition',
            [
                'label' => __('Transition Duration'),
                'type' => ControlsManager::SLIDER,
                'separator' => 'before',
                'default' => [
                    'size' => 0.3,
                ],
                'range' => [
                    'px' => [
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'background_hover_background',
                            'operator' => '!==',
                            'value' => '',
                        ],
                        [
                            'name' => 'border_hover_border',
                            'operator' => '!==',
                            'value' => '',
                        ],
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-section-wrap' => '--e-border-transition-duration: {{SIZE}}s;',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->endControlsSection();

        $this->startControlsSection(
            'section_advanced',
            [
                'label' => __('Advanced'),
                'tab' => ControlsManager::TAB_ADVANCED,
            ]
        );

        $this->addResponsiveControl(
            'margin',
            [
                'label' => __('Margin'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'allowed_dimensions' => 'vertical',
                'placeholder' => [
                    'top' => '',
                    'right' => 'auto',
                    'bottom' => '',
                    'left' => 'auto',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addResponsiveControl(
            'padding',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'css_classes',
            [
                'label' => __('CSS Classes'),
                'type' => ControlsManager::TEXT,
                'label_block' => false,
                'title' => __('Add your custom class WITHOUT the dot. e.g: my-class'),
            ]
        );

        $this->addControl(
            'overflow',
            [
                'label' => __('Overflow'),
                'type' => ControlsManager::SELECT,
                'default' => 'hidden',
                'options' => [
                    '' => __('Default'),
                    'hidden' => __('Hidden'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-section-wrap' => 'overflow: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsSection();

        Plugin::$instance->controls_manager->addCustomCssControls($this);
    }

    public function printSmartyElementsWithWrapper(&$elements_data)
    {
        $wrapper_tag = $this->getSettings('content_wrapper_html_tag') ?: 'div';
        $container = $this->getContainerAttributes();

        if ($css_classes = $this->getSettings('css_classes')) {
            $container['class'] .= " $css_classes";
        } else {
            $container['class'] .= '{if !empty($productClasses)} {$productClasses}{/if}';
        }
        if (strpos($container['data-elementor-settings'], '{') !== false) {
            $container['data-elementor-settings'] = "{literal}{$container['data-elementor-settings']}{/literal}";
        }
        $uid = $this->getMainId();
        $article = [
            'class' => 'elementor-section-wrap',
            'data-id-product' => '{$product.id_product}',
            'data-id-product-attribute' => '{$product.id_product_attribute}',
        ]; ?>
        {* Generated by Creative Elements, do not modify it *}
        {ce_enqueue_miniature(<?php echo $uid; ?>)}
        <<?php echo $wrapper_tag; ?> <?php echo Utils::renderHtmlAttributes($container); ?>>
            <article <?php echo Utils::renderHtmlAttributes($article); ?>>
            <?php
            foreach ($elements_data as &$element_data) {
                if ($element = Plugin::$instance->elements_manager->createElementInstance($element_data)) {
                    $element->printElement();
                }
            } ?>
            </article>
        </<?php echo $wrapper_tag; ?>>
        <?php
    }
}
