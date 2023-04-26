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
 * Elementor box shadow control.
 *
 * A base control for creating box shadows control. Displays input fields for
 * horizontal shadow, vertical shadow, shadow blur, shadow spread and shadow
 * color.
 *
 * @since 1.0.0
 */
class ControlBoxShadow extends ControlBaseMultiple
{
    /**
     * Get box shadow control type.
     *
     * Retrieve the control type, in this case `box_shadow`.
     *
     * @since 1.0.0
     *
     * @return string Control type
     */
    public function getType()
    {
        return 'box_shadow';
    }

    /**
     * Get box shadow control default value.
     *
     * Retrieve the default value of the box shadow control. Used to return the
     * default values while initializing the box shadow control.
     *
     * @since 1.0.0
     *
     * @return array Control default value
     */
    public function getDefaultValue()
    {
        return [
            'horizontal' => 0,
            'vertical' => 0,
            'blur' => 10,
            'spread' => 0,
            'color' => 'rgba(0,0,0,0.5)',
        ];
    }

    /**
     * Get box shadow control sliders.
     *
     * Retrieve the sliders of the box shadow control. Sliders are used while
     * rendering the control output in the editor.
     *
     * @since 1.0.0
     *
     * @return array Control sliders
     */
    public function getSliders()
    {
        return [
            'horizontal' => [
                'label' => __('Horizontal'),
                'min' => -100,
                'max' => 100,
            ],
            'vertical' => [
                'label' => __('Vertical'),
                'min' => -100,
                'max' => 100,
            ],
            'blur' => [
                'label' => __('Blur'),
                'min' => 0,
                'max' => 100,
            ],
            'spread' => [
                'label' => __('Spread'),
                'min' => -100,
                'max' => 100,
            ],
        ];
    }

    /**
     * Render box shadow control output in the editor.
     *
     * Used to generate the control HTML in the editor using Underscore JS
     * template. The variables for the class are available using `data` JS
     * object.
     *
     * @since 1.0.0
     */
    public function contentTemplate()
    {
        ?>
        <div class="elementor-shadow-box">
            <div class="elementor-control-field elementor-color-picker-wrapper">
                <label class="elementor-control-title"><?php _e('Color'); ?></label>
                <div class="elementor-control-input-wrapper elementor-control-unit-1">
                    <div class="elementor-color-picker-placeholder"></div>
                </div>
            </div>
        <?php foreach ($this->getSliders() as $slider_name => $slider) { ?>
            <?php $control_uid = $this->getControlUid($slider_name); ?>
            <div class="elementor-shadow-slider elementor-control-type-slider">
                <label for="<?php echo esc_attr($control_uid); ?>" class="elementor-control-title">
                    <?php echo $slider['label']; ?>
                </label>
                <div class="elementor-control-input-wrapper">
                    <div class="elementor-slider" data-input="<?php echo esc_attr($slider_name); ?>"></div>
                    <div class="elementor-slider-input elementor-control-unit-2">
                        <input id="<?php echo esc_attr($control_uid); ?>" data-setting="<?php echo esc_attr($slider_name); ?>"
                            type="number" min="<?php echo esc_attr($slider['min']); ?>" max="<?php echo esc_attr($slider['max']); ?>">
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
        <?php
    }
}
