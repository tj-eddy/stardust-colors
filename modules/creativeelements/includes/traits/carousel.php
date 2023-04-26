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

trait CarouselTrait
{
    public function getScriptDepends()
    {
        return ['swiper'];
    }

    protected function registerCarouselSection(array $args = [])
    {
        $default_slides_count = isset($args['default_slides_count']) ? $args['default_slides_count'] : 3;
        $variable_width = isset($args['variable_width']) ? ['variable_width' => ''] : [];

        $this->startControlsSection(
            'section_additional_options',
            [
                'label' => __('Carousel'),
            ]
        );

        $this->addControl(
            'default_slides_count',
            [
                'type' => ControlsManager::HIDDEN,
                'default' => (int) $default_slides_count,
                'frontend_available' => true,
            ]
        );

        $options = range(1, 10);
        $options = array_combine($options, $options);

        $this->addResponsiveControl(
            'slides_to_show',
            [
                'label' => __('Slides to Show'),
                'type' => ControlsManager::SELECT2,
                'select2options' => [
                    'tags' => true,
                    'placeholder' => __('Default'),
                ],
                'options' => $options,
                'classes' => 'select2-numeric',
                'frontend_available' => true,
                'condition' => $variable_width,
            ]
        );

        $this->addResponsiveControl(
            'slides_to_scroll',
            [
                'label' => __('Slides to Scroll'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    '' => __('Default'),
                ] + $options,
                'description' => __('Set how many slides are scrolled per swipe.'),
                'frontend_available' => true,
                'condition' => [
                    'slides_to_show!' => '1',
                    'center_mode' => '',
                ] + $variable_width,
                'device_args' => [
                    ControlsStack::RESPONSIVE_TABLET => [
                        'condition' => [
                            'slides_to_show_tablet!' => '1',
                            'center_mode_tablet' => '',
                        ] + $variable_width,
                    ],
                    ControlsStack::RESPONSIVE_MOBILE => [
                        'condition' => [
                            'slides_to_show_mobile!' => ['', '1'],
                            'center_mode_mobile' => '',
                        ] + $variable_width,
                    ],
                ],
            ]
        );

        $this->addResponsiveControl(
            'center_mode',
            [
                'label' => __('Center Mode'),
                'type' => ControlsManager::SWITCHER,
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'navigation',
            [
                'label' => __('Navigation'),
                'type' => ControlsManager::SELECT,
                'default' => 'both',
                'options' => [
                    'both' => __('Arrows and Dots'),
                    'arrows' => __('Arrows'),
                    'dots' => __('Dots'),
                    'none' => __('None'),
                ],
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'additional_options',
            [
                'label' => __('Additional Options'),
                'type' => ControlsManager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->addResponsiveControl(
            'autoplay',
            [
                'label' => __('Autoplay'),
                'type' => ControlsManager::SWITCHER,
                'default' => 'yes',
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'pause_on_hover',
            [
                'label' => __('Pause on Hover'),
                'type' => ControlsManager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'autoplay' => 'yes',
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
                    'autoplay' => 'yes',
                ],
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'autoplay_speed',
            [
                'label' => __('Autoplay Speed'),
                'type' => ControlsManager::NUMBER,
                'default' => 5000,
                'condition' => [
                    'autoplay' => 'yes',
                ],
                'frontend_available' => true,
            ]
        );

        $this->addResponsiveControl(
            'infinite',
            [
                'label' => __('Infinite Loop'),
                'type' => ControlsManager::SWITCHER,
                'default' => 'yes',
                'tablet_default' => 'yes',
                'mobile_default' => 'yes',
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'effect',
            [
                'label' => __('Effect'),
                'type' => ControlsManager::SELECT,
                'default' => 'slide',
                'options' => [
                    'slide' => __('Slide'),
                    'fade' => __('Fade'),
                    'cube' => __('Cube'),
                    'flip' => __('Flip'),
                    'coverflow' => __('Coverflow'),
                ],
                'condition' => [
                    'slides_to_show' => '1',
                    'center_mode' => '',
                ],
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'speed',
            [
                'label' => __('Animation Speed') . ' (ms)',
                'type' => ControlsManager::NUMBER,
                'default' => 500,
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'direction',
            [
                'label' => __('Direction'),
                'type' => ControlsManager::SELECT,
                'default' => 'ltr',
                'options' => [
                    'ltr' => __('Left'),
                    'rtl' => __('Right'),
                ],
                'frontend_available' => true,
            ]
        );

        $this->endControlsSection();
    }

    protected function registerNavigationStyleSection()
    {
        $this->startControlsSection(
            'section_style_navigation',
            [
                'label' => __('Navigation'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'navigation' => ['arrows', 'dots', 'both'],
                ],
            ]
        );

        $this->addControl(
            'heading_style_arrows',
            [
                'label' => __('Arrows'),
                'type' => ControlsManager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->addControl(
            'arrows_position',
            [
                'label' => __('Position'),
                'type' => ControlsManager::SELECT,
                'default' => 'inside',
                'options' => [
                    'inside' => __('Inside'),
                    'outside' => __('Outside'),
                ],
                'prefix_class' => 'elementor-arrows-position-',
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->addResponsiveControl(
            'arrows_size',
            [
                'label' => __('Size'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 60,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->addControl(
            'arrows_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->addControl(
            'heading_style_dots',
            [
                'label' => __('Dots'),
                'type' => ControlsManager::HEADING,
                'condition' => [
                    'navigation' => ['dots', 'both'],
                ],
            ]
        );

        $this->addControl(
            'dots_position',
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
                    'navigation' => ['dots', 'both'],
                ],
            ]
        );

        $this->addResponsiveControl(
            'dots_size',
            [
                'label' => __('Size'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 5,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'navigation' => ['dots', 'both'],
                ],
            ]
        );

        $this->addControl(
            'dots_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}};',
                ],
                'condition' => [
                    'navigation' => ['dots', 'both'],
                ],
            ]
        );

        $this->endControlsSection();
    }

    protected function renderCarousel(array &$settings, array &$slides)
    {
        if (!$slides) {
            return;
        }
        $this->addRenderAttribute('carousel', 'class', 'swiper-wrapper');

        $show_dots = in_array($settings['navigation'], ['dots', 'both']);
        $show_arrows = in_array($settings['navigation'], ['arrows', 'both']); ?>
        <div class="elementor-carousel-wrapper swiper-container" dir="<?php echo esc_attr($settings['direction']); ?>">
            <div <?php $this->printRenderAttributeString('carousel'); ?>>
                <?php echo implode('', $slides); ?>
            </div>
        <?php if (count($slides) > 1) { ?>
            <?php if ($show_dots) { ?>
                <div class="swiper-pagination"></div>
            <?php } ?>
            <?php if ($show_arrows) { ?>
                <div class="elementor-swiper-button elementor-swiper-button-prev">
                    <i class="ceicon-chevron-left" aria-hidden="true"></i>
                    <span class="elementor-screen-only"><?php _e('Previous'); ?></span>
                </div>
                <div class="elementor-swiper-button elementor-swiper-button-next">
                    <i class="ceicon-chevron-right" aria-hidden="true"></i>
                    <span class="elementor-screen-only"><?php _e('Next'); ?></span>
                </div>
            <?php } ?>
        <?php } ?>
        </div>
        <?php
    }
}
