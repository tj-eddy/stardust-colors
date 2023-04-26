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
 * Elementor select control.
 *
 * A base control for creating select control. Displays a simple select box.
 * It accepts an array in which the `key` is the option value and the `value` is
 * the option name.
 *
 * @since 1.0.0
 */
class ControlSelect extends BaseDataControl
{
    /**
     * Get select control type.
     *
     * Retrieve the control type, in this case `select`.
     *
     * @since 1.0.0
     *
     * @return string Control type
     */
    public function getType()
    {
        return 'select';
    }

    /**
     * Get select control default settings.
     *
     * Retrieve the default settings of the select control. Used to return the
     * default settings while initializing the select control.
     *
     * @since 2.0.0
     *
     * @return array Control default settings
     */
    protected function getDefaultSettings()
    {
        return [
            'options' => [],
        ];
    }

    /**
     * Render select control output in the editor.
     *
     * Used to generate the control HTML in the editor using Underscore JS
     * template. The variables for the class are available using `data` JS
     * object.
     *
     * @since 1.0.0
     */
    public function contentTemplate()
    {
        $control_uid = $this->getControlUid(); ?>
        <div class="elementor-control-field">
            <# if ( data.label ) {#>
                <label for="<?php echo $control_uid; ?>" class="elementor-control-title">{{{ data.label }}}</label>
            <# } #>
            <div class="elementor-control-input-wrapper elementor-control-unit-5">
                <select id="<?php echo $control_uid; ?>" data-setting="{{ data.name }}">
                <#
                    var printOptions = function( options ) {
                        _.each( options, function( option_title, option_value ) { #>
                                <option value="{{ option_value }}">{{{ option_title }}}</option>
                        <# } );
                    };

                    if ( data.groups ) {
                        for ( var groupIndex in data.groups ) {
                            var groupArgs = data.groups[ groupIndex ];
                                if ( groupArgs.options ) { #>
                                    <optgroup label="{{ groupArgs.label }}">
                                        <# printOptions( groupArgs.options ) #>
                                    </optgroup>
                                <# } else if ( _.isString( groupArgs ) ) { #>
                                    <option value="{{ groupIndex }}">{{{ groupArgs }}}</option>
                                <# }
                        }
                    } else {
                        printOptions( data.options );
                    }
                #>
                </select>
            </div>
        </div>
        <# if ( data.description ) { #>
            <div class="elementor-control-field-description">{{{ data.description }}}</div>
        <# } #>
        <?php
    }
}
