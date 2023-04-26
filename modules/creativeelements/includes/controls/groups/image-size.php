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
 * Elementor image size control.
 *
 * A base control for creating image size control. Displays input fields to define
 * one of the default image sizes (thumbnail, medium, medium_large, large) or custom
 * image dimensions.
 *
 * @since 1.0.0
 */
class GroupControlImageSize extends GroupControlBase
{
    /**
     * Fields.
     *
     * Holds all the image size control fields.
     *
     * @since 1.2.2
     * @static
     *
     * @var array Image size control fields
     */
    protected static $fields;

    /**
     * Get image size control type.
     *
     * Retrieve the control type, in this case `image-size`.
     *
     * @since 1.0.0
     * @static
     *
     * @return string Control type
     */
    public static function getType()
    {
        return 'image-size';
    }

    /**
     * Get attachment image HTML.
     *
     * Retrieve the attachment image HTML code.
     *
     * Note that some widgets use the same key for the media control that allows
     * the image selection and for the image size control that allows the user
     * to select the image size, in this case the third parameter should be null
     * or the same as the second parameter. But when the widget uses different
     * keys for the media control and the image size control, when calling this
     * method you should pass the keys.
     *
     * @since 1.0.0
     * @static
     *
     * @param array $settings Control settings
     * @param string $image_key Optional. Settings key for image. Default
     *                          is null. If not defined uses image size key
     *                          as the image key.
     *
     * @return string Image HTML
     */
    public static function getAttachmentImageHtml($settings, $setting_key = 'image', $loading = 'lazy', $class = '')
    {
        if (empty($settings[$setting_key]['url'])) {
            return '';
        }
        $attr = [
            'src="' . esc_attr(Helper::getMediaLink($settings[$setting_key]['url'])) . '"',
            'alt="' . ControlMedia::getImageAlt($settings[$setting_key]) . '"',
        ];
        if ($loading && 'eager' !== $loading && empty($settings[$setting_key]['loading'])) {
            $attr[] = 'loading="' . esc_attr($loading) . '"';
        }
        if ($title = ControlMedia::getImageTitle($settings[$setting_key])) {
            $attr[] = 'title="' . $title . '"';
        }
        if (isset($settings[$setting_key]['width'], $settings[$setting_key]['height'])) {
            $attr[] = 'width="' . (int) $settings[$setting_key]['width'] . '"';
            $attr[] = 'height="' . (int) $settings[$setting_key]['height'] . '"';
        }
        if (!empty($settings['hover_animation'])) {
            $class .= ($class ? ' ' : '') . 'elementor-animation-' . $settings['hover_animation'];
        }
        if ($class) {
            $attr[] = 'class="' . $class . '"';
        }

        $html = '<img ' . implode(' ', $attr) . '>';

        /*
         * Get Attachment Image HTML
         *
         * Filters the Attachment Image HTML
         *
         * @since 2.4.0
         * @param string $html the attachment image HTML string
         * @param array  $settings       Control settings
         * @param string $image_size_key Optional. Settings key for image size
         *                               Default is `image`.
         * @param string $image_key      Optional. Settings key for image. Default
         *                               is null. If not defined uses image size key
         *                               as the image key.
         */
        return apply_filters('elementor/image_size/get_attachment_image_html', $html, $settings, $setting_key, $loading);
    }

    /**
     * Get all image sizes.
     *
     * @since 2.0.0
     *
     * @param string $type Image type [`products`, `categories`, `manufacturers`, `suppliers`, `stores`]
     * @param bool $full Add full size option (Optional)
     *
     * @return array
     */
    public static function getAllImageSizes($type, $full = false)
    {
        static $options = [];

        if (empty($options[$type])) {
            $options[$type] = [];
            $sizes = \ImageType::getImagesTypes($type);

            usort($sizes, function ($a, $b) {
                return ($b['width'] * $b['height']) - ($a['width'] * $a['height']);
            });
            foreach ($sizes as &$size) {
                $options[$type][$size['name']] = "{$size['name']} ({$size['width']} × {$size['height']})";
            }
        }

        return $full ? ['' => _x('Full', 'Image Size Control')] + $options[$type] : $options[$type];
    }

    /**
     * Get attachment image src.
     *
     * Retrieve the attachment image source URL.
     *
     * @since 1.0.0
     * @static
     *
     * @param string $attachment_id The attachment ID
     * @param string $image_size_key Settings key for image size
     * @param array $settings Control settings
     *
     * @return string Attachment image source URL
     */
    public static function getAttachmentImageSrc($attachment_id, $image_size_key, array $settings)
    {
        return false;
    }

    /**
     * Get child default arguments.
     *
     * Retrieve the default arguments for all the child controls for a specific group
     * control.
     *
     * @since 1.2.2
     *
     * @return array Default arguments for all the child controls
     */
    protected function getChildDefaultArgs()
    {
        return [
            'include' => [],
            'exclude' => [],
        ];
    }

    /**
     * Init fields.
     *
     * Initialize image size control fields.
     *
     * @since 1.2.2
     *
     * @return array Control fields
     */
    protected function initFields()
    {
        $fields = [];

        $fields['size'] = [
            'label' => _x('Image Size', 'Image Size Control'),
            'type' => ControlsManager::SELECT,
        ];

        // $fields['custom_dimension'] = [
        //     'label' => _x('Image Dimension', 'Image Size Control'),
        //     'type' => ControlsManager::IMAGE_DIMENSIONS,
        //     'description' => __('You can crop the original image size to any custom size. You can also set a single value for height or width in order to keep the original size ratio.'),
        //     'condition' => [
        //         'size' => 'custom',
        //     ],
        //     'separator' => 'none',
        // ];

        return $fields;
    }

    // protected function prepareFields($fields)

    // private function getImageSizes()

    /**
     * Get default options.
     *
     * Retrieve the default options of the image size control. Used to return the
     * default options while initializing the image size control.
     *
     * @since 1.9.0
     *
     * @return array Default image size control options
     */
    protected function getDefaultOptions()
    {
        return [
            'popover' => false,
        ];
    }
}
