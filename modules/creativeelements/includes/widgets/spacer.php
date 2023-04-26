<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks, Elementor
 * @copyright 2019-2023 WebshopWorks.com & Elementor.com
 * @license   https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CE;

defined('_PS_VERSION_') or exit;

/**
 * Elementor spacer widget.
 *
 * Elementor widget that inserts a space that divides various elements.
 *
 * @since 1.0.0
 */
class WidgetSpacer extends WidgetBase
{
    /**
     * Get widget name.
     *
     * Retrieve spacer widget name.
     *
     * @since 1.0.0
     *
     * @return string Widget name
     */
    public function getName()
    {
        return 'spacer';
    }

    /**
     * Get widget title.
     *
     * Retrieve spacer widget title.
     *
     * @since 1.0.0
     *
     * @return string Widget title
     */
    public function getTitle()
    {
        return __('Spacer');
    }

    /**
     * Get widget icon.
     *
     * Retrieve spacer widget icon.
     *
     * @since 1.0.0
     *
     * @return string Widget icon
     */
    public function getIcon()
    {
        return 'eicon-spacer';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the spacer widget belongs to.
     *
     * Used to determine where to display the widget in the editor.
     *
     * @since 1.0.0
     *
     * @return array Widget categories
     */
    public function getCategories()
    {
        return ['basic'];
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
        return ['space'];
    }

    /**
     * Register spacer widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     */
    protected function _registerControls()
    {
        $this->startControlsSection(
            'section_spacer',
            [
                'label' => __('Spacer'),
            ]
        );

        $this->addResponsiveControl(
            'space',
            [
                'label' => __('Space'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 50,
                ],
                'size_units' => ['px', 'vh', 'em'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 600,
                    ],
                    'em' => [
                        'min' => 0.1,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-spacer-inner' => 'height: {{SIZE}}{{UNIT}};',
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
    }

    /**
     * Render spacer widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     */
    protected function render()
    {
        ?>
        <div class="elementor-spacer">
            <div class="elementor-spacer-inner"></div>
        </div>
        <?php
    }

    /**
     * Render spacer widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since 2.9.0
     */
    protected function contentTemplate()
    {
        ?>
        <div class="elementor-spacer">
            <div class="elementor-spacer-inner"></div>
        </div>
        <?php
    }
}
