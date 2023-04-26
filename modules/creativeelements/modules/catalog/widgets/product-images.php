<?php
/**
 * Creative Elements - Elementor based PageBuilder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */

namespace CE;

defined('_PS_VERSION_') or exit;

class WidgetProductImages extends WidgetBase
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
        return 'product-images';
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
        return __('Product Images');
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
        return 'eicon-product-images';
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
        return ['shop', 'store', 'image', 'product', 'gallery', 'lightbox'];
    }

    /**
     * Register product images widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 2.5.8
     */
    protected function _registerControls()
    {
        $this->startControlsSection(
            'section_product_images',
            [
                'label' => __('Product Images'),
            ]
        );

        $this->addControl(
            'skin',
            [
                'label' => __('Skin'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'slideshow' => __('Slideshow'),
                    'carousel' => __('Carousel'),
                ],
                'default' => 'slideshow',
                'prefix_class' => 'elementor-skin-',
                'render_type' => 'template',
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'effect',
            [
                'type' => ControlsManager::SELECT,
                'label' => __('Effect'),
                'default' => 'slide',
                'options' => [
                    'slide' => __('Slide'),
                    'fade' => __('Fade'),
                    'cube' => __('Cube'),
                    'flip' => __('Flip'),
                    'coverflow' => __('Coverflow'),
                ],
                'frontend_available' => true,
            ]
        );

        $image_size_options = GroupControlImageSize::getAllImageSizes('products');

        $this->addControl(
            'image_size',
            [
                'label' => __('Image Size'),
                'type' => ControlsManager::SELECT,
                'options' => &$image_size_options,
                'default' => key($image_size_options),
            ]
        );

        $this->addControl(
            'zoom',
            [
                'type' => ControlsManager::SWITCHER,
                'label' => __('Zoom on Hover'),
                'condition' => [
                    'skin' => 'slideshow',
                ],
            ]
        );

        $this->addControl(
            'zoom_scale',
            [
                'label' => __('Scale'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'step' => 0.1,
                        'min' => 1.1,
                        'max' => 5,
                    ],
                ],
                'default' => [
                    'size' => 2,
                ],
                'condition' => [
                    'skin' => 'slideshow',
                    'zoom!' => '',
                ],
            ]
        );

        $this->addControl(
            'centered_slides',
            [
                'label' => __('Centered Slides'),
                'type' => ControlsManager::SWITCHER,
                'frontend_available' => true,
                'condition' => [
                    'skin' => 'carousel',
                ],
            ]
        );

        $this->addControl(
            'heading_thumbs',
            [
                'label' => __('Thumbnails'),
                'type' => ControlsManager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'skin' => 'slideshow',
                ],
            ]
        );

        end($image_size_options);

        $this->addControl(
            'thumb_size',
            [
                'label' => __('Image Size'),
                'type' => ControlsManager::SELECT,
                'options' => &$image_size_options,
                'default' => key($image_size_options),
                'condition' => [
                    'skin' => 'slideshow',
                ],
            ]
        );

        $this->addControl(
            'position',
            [
                'label' => __('Position'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'bottom' => __('Bottom'),
                    'left' => __('Left'),
                    'right' => __('Right'),
                ],
                'default' => 'bottom',
                'prefix_class' => 'elementor-position-',
                'frontend_available' => true,
                'render_type' => 'template',
                'condition' => [
                    'skin' => 'slideshow',
                ],
            ]
        );

        $options = range(1, 10);
        $options = array_combine($options, $options);

        $this->addResponsiveControl(
            'slides_per_view',
            [
                'label' => __('Slides Per View'),
                'type' => ControlsManager::SELECT2,
                'select2options' => [
                    'tags' => true,
                    'placeholder' => __('Default'),
                ],
                'options' => $options,
                'frontend_available' => true,
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'skin',
                            'value' => 'slideshow',
                        ],
                        [
                            'name' => 'effect',
                            'operator' => 'in',
                            'value' => ['slide', 'coverflow'],
                        ],
                    ],
                ],
            ]
        );

        $this->addResponsiveControl(
            'slides_to_scroll',
            [
                'label' => __('Slides to Scroll'),
                'type' => ControlsManager::SELECT,
                'description' => __('Set how many slides are scrolled per swipe.'),
                'options' => [
                    '' => __('Default'),
                ] + $options,
                'frontend_available' => true,
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'skin',
                            'value' => 'slideshow',
                        ],
                        [
                            'name' => 'effect',
                            'operator' => 'in',
                            'value' => ['slide', 'coverflow'],
                        ],
                    ],
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_additional_options',
            [
                'label' => __('Additional Options'),
            ]
        );

        $this->addControl(
            'show_arrows',
            [
                'label' => __('Arrows'),
                'type' => ControlsManager::SWITCHER,
                'default' => 'yes',
                'label_off' => __('Hide'),
                'label_on' => __('Show'),
                'prefix_class' => 'elementor-arrows-',
                'render_type' => 'template',
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'pagination',
            [
                'label' => __('Pagination'),
                'type' => ControlsManager::SELECT,
                'default' => 'bullets',
                'options' => [
                    '' => __('None'),
                    'bullets' => __('Dots'),
                    'fraction' => __('Fraction'),
                    'progressbar' => __('Progress'),
                ],
                'prefix_class' => 'elementor-pagination-type-',
                'render_type' => 'template',
                'frontend_available' => true,
                'condition' => [
                    'skin' => 'carousel',
                ],
            ]
        );

        $this->addControl(
            'speed',
            [
                'label' => __('Transition Duration') . ' (ms)',
                'type' => ControlsManager::NUMBER,
                'default' => 500,
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'autoplay',
            [
                'label' => __('Autoplay'),
                'type' => ControlsManager::SWITCHER,
                'separator' => 'before',
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'autoplay_speed',
            [
                'label' => __('Autoplay Speed') . ' (ms)',
                'type' => ControlsManager::NUMBER,
                'default' => 5000,
                'condition' => [
                    'autoplay!' => '',
                ],
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'pause_on_interaction',
            [
                'label' => __('Pause on Interaction'),
                'type' => ControlsManager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'autoplay!' => '',
                ],
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'loop',
            [
                'label' => __('Infinite Loop'),
                'type' => ControlsManager::SWITCHER,
                'frontend_available' => true,
                'separator' => 'before',
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'skin',
                            'value' => 'carousel',
                        ],
                        [
                            'name' => 'effect',
                            'value' => 'fade',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'overlay',
            [
                'label' => __('Overlay'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    '' => __('None'),
                    'text' => __('Caption'),
                    'icon' => __('Icon'),
                ],
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'selected_icon',
            [
                'label' => __('Icon'),
                'type' => ControlsManager::ICONS,
                'fa4compatibility' => 'icon',
                'recommended' => [
                    'ce-icons' => [
                        'search-light',
                        'search-medium',
                        'search-glint',
                        'search-minimal',
                        'loupe',
                        'magnifier',
                    ],
                    'fa-solid' => [
                        'magnifying-glass',
                        'magnifying-glass-plus',
                        'expand',
                        'up-right-and-down-left-from-center',
                        'maximize',
                        'eye',
                    ],
                    'fa-regular' => [
                        'eye',
                    ],
                ],
                'condition' => [
                    'overlay' => 'icon',
                ],
            ]
        );

        $this->addControl(
            'overlay_animation',
            [
                'label' => __('Animation'),
                'type' => ControlsManager::SELECT,
                'default' => 'fade',
                'options' => [
                    'fade' => 'Fade',
                    'slide-up' => 'Slide Up',
                    'slide-down' => 'Slide Down',
                    'slide-right' => 'Slide Right',
                    'slide-left' => 'Slide Left',
                    'zoom-in' => 'Zoom In',
                ],
                'condition' => [
                    'overlay!' => '',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_product_images_style',
            [
                'label' => __('Product Images'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addResponsiveControl(
            'space_between',
            [
                'label' => __('Space Between'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'desktop_default' => [
                    'size' => 10,
                ],
                'tablet_default' => [
                    'size' => 10,
                ],
                'mobile_default' => [
                    'size' => 10,
                ],
                'frontend_available' => true,
                'condition' => [
                    'skin' => 'carousel',
                    'effect' => ['slide', 'coverflow'],
                ],
            ]
        );

        $this->addResponsiveControl(
            'slideshow_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-position-bottom .elementor-thumbnails-swiper' => 'margin-top: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}}.elementor-position-left .elementor-thumbnails-swiper' => 'margin-right: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}}.elementor-position-right .elementor-thumbnails-swiper' => 'margin-left: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'skin' => 'slideshow',
                ],
            ]
        );

        $this->addResponsiveControl(
            'slideshow_width',
            [
                'type' => ControlsManager::SLIDER,
                'label' => __('Width'),
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 500,
                    ],
                ],
                'default' => [
                    'size' => '80',
                    'unit' => '%',
                ],
                'render_type' => 'template',
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper:nth-child(1)' => 'width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-swiper:nth-child(2)' => 'width: calc(100% - {{SIZE}}{{UNIT}});',
                ],
                'condition' => [
                    'skin' => 'slideshow',
                    'position!' => 'bottom',
                ],
            ]
        );

        $this->addResponsiveControl(
            'slideshow_height',
            [
                'type' => ControlsManager::SLIDER,
                'label' => __('Height'),
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 1000,
                    ],
                ],
                'render_type' => 'template',
                'selectors' => [
                    '{{WRAPPER}} .elementor-main-swiper' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}:not(.elementor-position-bottom) .elementor-thumbnails-swiper' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'slide_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-main-swiper .swiper-slide' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'slide_border_size',
            [
                'label' => __('Border Size'),
                'type' => ControlsManager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .elementor-main-swiper .swiper-slide' => 'border-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'slide_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    '%' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-main-swiper .swiper-slide' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_product_thumbs_style',
            [
                'label' => __('Thumbnails'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'skin' => 'slideshow',
                ],
            ]
        );

        $this->addResponsiveControl(
            'thumb_space_between',
            [
                'label' => __('Space Between'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'desktop_default' => [
                    'size' => 10,
                ],
                'tablet_default' => [
                    'size' => 10,
                ],
                'mobile_default' => [
                    'size' => 10,
                ],
                'frontend_available' => true,
            ]
        );

        $this->startControlsTabs('tabs_style_thumbs');

        $this->startControlsTab(
            'tab_thumb_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addControl(
            'thumb_overlay_color',
            [
                'label' => __('Overlay Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-thumbnails-swiper .swiper-slide:after' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'thumb_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-thumbnails-swiper .swiper-slide' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_thumb_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addControl(
            'thumb_overlay_color_hover',
            [
                'label' => __('Overlay Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-thumbnails-swiper .swiper-slide:hover:after' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'thumb_border_color_hover',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-thumbnails-swiper .swiper-slide:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_thumb_active',
            [
                'label' => __('Active'),
            ]
        );

        $this->addControl(
            'thumb_overlay_color_active',
            [
                'label' => __('Overlay Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-thumbnails-swiper .swiper-slide.swiper-slide-active:after' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'thumb_border_color_active',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-thumbnails-swiper .swiper-slide.swiper-slide-active' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->addControl(
            'thumb_border_size',
            [
                'label' => __('Border Size'),
                'type' => ControlsManager::SLIDER,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .elementor-thumbnails-swiper .swiper-slide' => 'border-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'thumb_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    '%' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-thumbnails-swiper .swiper-slide' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_style_navigation',
            [
                'label' => __('Navigation'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addControl(
            'heading_style_arrows',
            [
                'label' => __('Arrows'),
                'type' => ControlsManager::HEADING,
                'condition' => [
                    'show_arrows!' => '',
                ],
            ]
        );

        $this->addControl(
            'arrows_size',
            [
                'label' => __('Size'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'range' => [
                    'px' => [
                        'min' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button' => 'font-size: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'show_arrows!' => '',
                ],
            ]
        );

        $this->addControl(
            'arrows_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'show_arrows!' => '',
                ],
            ]
        );

        $this->addControl(
            'heading_pagination',
            [
                'label' => __('Pagination'),
                'type' => ControlsManager::HEADING,
                'condition' => [
                    'skin' => 'carousel',
                    'pagination!' => '',
                ],
            ]
        );

        $this->addControl(
            'pagination_position',
            [
                'label' => __('Position'),
                'type' => ControlsManager::SELECT,
                'default' => 'outside',
                'options' => [
                    'outside' => __('Outside'),
                    'inside' => __('Inside'),
                ],
                'prefix_class' => 'elementor-pagination-position-',
                'condition' => [
                    'skin' => 'carousel',
                    'pagination!' => '',
                ],
            ]
        );

        $this->addControl(
            'pagination_size',
            [
                'label' => __('Size'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .swiper-container-horizontal .swiper-pagination-progressbar' => 'height: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .swiper-pagination-fraction' => 'font-size: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'skin' => 'carousel',
                    'pagination!' => '',
                ],
            ]
        );

        $this->addControl(
            'pagination_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet-active, {{WRAPPER}} .swiper-pagination-progressbar-fill' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .swiper-pagination-fraction' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'skin' => 'carousel',
                    'pagination!' => '',
                ],
            ]
        );

        $this->addControl(
            'heading_style_lightbox',
            [
                'label' => __('Lightbox'),
                'type' => ControlsManager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'lightbox_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '#elementor-lightbox-slideshow-{{ID}}' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'lightbox_ui_color',
            [
                'label' => __('UI Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '#elementor-lightbox-slideshow-{{ID}} .dialog-lightbox-close-button, #elementor-lightbox-slideshow-{{ID}} .elementor-swiper-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'lightbox_ui_hover_color',
            [
                'label' => __('UI Hover Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '#elementor-lightbox-slideshow-{{ID}} .dialog-lightbox-close-button:hover, #elementor-lightbox-slideshow-{{ID}} .elementor-swiper-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_overlay',
            [
                'label' => __('Overlay'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'overlay!' => '',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'caption_typography',
                'scheme' => SchemeTypography::TYPOGRAPHY_4,
                'selector' => '{{WRAPPER}} .elementor-carousel-image-overlay',
                'condition' => [
                    'overlay' => 'text',
                ],
            ]
        );

        $this->addControl(
            'icon_size',
            [
                'label' => __('Icon Size'),
                'type' => ControlsManager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .elementor-carousel-image-overlay i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'overlay' => 'icon',
                ],
            ]
        );

        $this->addControl(
            'overlay_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-carousel-image-overlay' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'overlay_background_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-carousel-image-overlay' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsSection();
    }

    public function onImport($widget)
    {
        $sizes = array_map(function ($size) {
            return $size['name'];
        }, \ImageType::getImagesTypes('products'));

        if (isset($widget['settings']['image_size']) && !in_array($widget['settings']['image_size'], $sizes)) {
            $home = \ImageType::getFormattedName('home');

            $widget['settings']['image_size'] = in_array($home, $sizes) ? $home : reset($sizes);
        }

        if (isset($widget['settings']['thumb_size']) && !in_array($widget['settings']['thumb_size'], $sizes)) {
            $small = \ImageType::getFormattedName('small');

            $widget['settings']['thumb_size'] = in_array($small, $sizes) ? $small : end($sizes);
        }

        return $widget;
    }

    /**
     * Render product images widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 2.5.8
     */
    protected function render()
    {
        $settings = $this->getSettingsForDisplay();
        $context = \Context::getContext();
        $id = $this->getId();
        $product = &$context->smarty->tpl_vars['product']->value;
        $images = $product['images'] ?: [Helper::getNoImage()];
        $image_size = $settings['image_size'];
        $thumb_size = $settings['thumb_size'];

        empty($settings['zoom']) or $this->addRenderAttribute('link', [
            'class' => 'swiper-zoom-container',
            'data-swiper-zoom' => $settings['zoom_scale']['size'],
        ]);
        $this->addRenderAttribute('link', 'data-elementor-lightbox-slideshow', $id); ?>
        <div class="elementor-swiper">
            <div class="elementor-main-swiper swiper-container">
                <div class="swiper-wrapper">
                <?php foreach ($images as $i => $image) { ?>
                    <div class="swiper-slide">
                    <?php if (empty($image['id_image'])) { ?>
                        <img class="elementor-carousel-image" src="<?php echo esc_attr($image['bySize'][$image_size]['url']); ?>" alt="<?php echo esc_attr($image['legend']); ?>">
                    <?php } else { ?>
                        <a href="<?php echo esc_attr(Helper::getProductImageLink($image)); ?>" <?php $this->printRenderAttributeString('link'); ?> data-elementor-lightbox-index="<?php echo (int) $i; ?>">
                            <img class="elementor-carousel-image" src="<?php echo esc_attr($image['bySize'][$image_size]['url']); ?>" alt="<?php echo esc_attr($image['legend']); ?>">
                        <?php if ($settings['overlay']) { ?>
                            <div class="elementor-carousel-image-overlay e-overlay-animation-<?php echo esc_attr($settings['overlay_animation']); ?>">
                            <?php if ('text' === $settings['overlay']) { ?>
                                <?php echo $image['legend']; ?>
                            <?php } else { ?>
                                <?php echo IconsManager::getBcIcon($settings, 'icon', ['aria-hidden' => 'false']); ?>
                            <?php } ?>
                            </div>
                        <?php } ?>
                        </a>
                    <?php } ?>
                    </div>
                <?php } ?>
                </div>
            <?php if ($settings['pagination']) { ?>
                <div class="swiper-pagination"></div>
            <?php } ?>
            <?php if ($settings['show_arrows']) { ?>
                <div class="elementor-swiper-button elementor-swiper-button-prev">
                    <i class="ceicon-chevron-left" aria-hidden="true"></i>
                    <span class="elementor-screen-only"><?php _e('Previous'); ?></span>
                </div>
                <div class="elementor-swiper-button elementor-swiper-button-next">
                    <i class="ceicon-chevron-right" aria-hidden="true"></i>
                    <span class="elementor-screen-only"><?php _e('Next'); ?></span>
                </div>
            <?php } ?>
            </div>
        </div>
        <?php if ('slideshow' === $settings['skin']) { ?>
            <div class="elementor-swiper">
                <div class="elementor-thumbnails-swiper swiper-container">
                    <div class="swiper-wrapper">
                    <?php foreach ($images as $i => $image) { ?>
                        <div class="swiper-slide">
                        <?php if (empty($image['id_image'])) { ?>
                            <img class="elementor-carousel-image" src="<?php echo esc_attr($image['bySize'][$thumb_size]['url']); ?>" alt="<?php echo esc_attr($image['legend']); ?>">
                        <?php } else { ?>
                            <a href="<?php echo esc_attr(Helper::getProductImageLink($image)); ?>" data-elementor-lightbox-slideshow="<?php echo $id; ?>" data-elementor-lightbox-index="<?php echo (int) $i; ?>">
                                <img class="elementor-carousel-image" src="<?php echo esc_attr($image['bySize'][$thumb_size]['url']); ?>" alt="<?php echo esc_attr($image['legend']); ?>">
                            </a>
                        <?php } ?>
                        </div>
                    <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php
    }

    public function renderPlainContent()
    {
    }
}
