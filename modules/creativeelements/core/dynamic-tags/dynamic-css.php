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

use CE\CoreXFilesXCSSXPost as Post;

class CoreXDynamicTagsXDynamicCSS extends Post
{
    protected $post_id_for_data;

    /**
     * Dynamic_CSS constructor.
     *
     * @since 2.0.13
     *
     * @param int $post_id Post ID
     * @param int $post_id_for_data
     */
    public function __construct($post_id, $post_id_for_data)
    {
        $this->post_id_for_data = $post_id_for_data;

        parent::__construct($post_id);
    }

    /**
     * @since 2.0.13
     */
    public function getName()
    {
        return 'dynamic';
    }

    /**
     * @since 2.0.13
     */
    protected function useExternalFile()
    {
        return false;
    }

    /**
     * @since 2.0.13
     */
    protected function getFileHandleId()
    {
        return 'elementor-post-dynamic-' . $this->post_id_for_data;
    }

    /**
     * @since 2.0.13
     */
    protected function getData()
    {
        $document = Plugin::$instance->documents->get($this->post_id_for_data);

        return $document ? $document->getElementsData() : [];
    }

    /**
     * @since 2.0.13
     */
    public function getMeta($property = null)
    {
        // Parse CSS first, to get the fonts list.
        $css = $this->getContent();

        $meta = [
            'status' => $css ? self::CSS_STATUS_INLINE : self::CSS_STATUS_EMPTY,
            'fonts' => $this->getFonts(),
            'css' => $css,
        ];

        if ($property) {
            return isset($meta[$property]) ? $meta[$property] : null;
        }

        return $meta;
    }

    /**
     * @since 2.0.13
     */
    public function addControlsStackStyleRules(ControlsStack $controls_stack, array $controls, array $values, array $placeholders, array $replacements, array $all_controls = null)
    {
        $dynamic_settings = $controls_stack->getSettings('__dynamic__');
        if (!empty($dynamic_settings)) {
            $controls = array_intersect_key($controls, $dynamic_settings);

            $all_controls = $controls_stack->getControls();

            $parsed_dynamic_settings = $controls_stack->parseDynamicSettings($values, $controls);

            foreach ($controls as $control) {
                if (!empty($control['style_fields'])) {
                    $this->addRepeaterControlStyleRules($controls_stack, $control, $values[$control['name']], $placeholders, $replacements);
                }

                if (empty($control['selectors'])) {
                    continue;
                }

                $this->addControlStyleRules($control, $parsed_dynamic_settings, $all_controls, $placeholders, $replacements);
            }
        }

        if ($controls_stack instanceof ElementBase) {
            foreach ($controls_stack->getChildren() as $child_element) {
                $this->renderStyles($child_element);
            }
        }
    }
}
