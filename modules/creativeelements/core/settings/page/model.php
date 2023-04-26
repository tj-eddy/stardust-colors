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

use CE\CoreXSettingsXBaseXCssModel as CSSModel;

/**
 * Elementor page settings model.
 *
 * Elementor page settings model handler class is responsible for registering
 * and managing Elementor page settings models.
 *
 * @since 1.6.0
 */
class CoreXSettingsXPageXModel extends CSSModel
{
    /**
     * Wrapper post object.
     *
     * Holds an instance of `WPPost` containing the post object.
     *
     * @since 1.6.0
     *
     * @var WPPost
     */
    private $post;

    /**
     * @var WPPost
     */
    private $post_parent;

    /**
     * Model constructor.
     *
     * Initializing Elementor page settings model.
     *
     * @since 1.6.0
     *
     * @param array $data Optional. Model data. Default is an empty array
     */
    public function __construct(array $data = [])
    {
        $this->post = get_post($data['id']);

        if (!$this->post) {
            $this->post = new WPPost((object) []);
        }

        if (wp_is_post_revision($this->post->ID)) {
            $this->post_parent = get_post($this->post->post_parent);
        } else {
            $this->post_parent = $this->post;
        }

        parent::__construct($data);
    }

    /**
     * Get model name.
     *
     * Retrieve page settings model name.
     *
     * @since 1.6.0
     *
     * @return string Model name
     */
    public function getName()
    {
        return 'page-settings';
    }

    /**
     * Get model unique name.
     *
     * Retrieve page settings model unique name.
     *
     * @since 1.6.0
     *
     * @return string Model unique name
     */
    public function getUniqueName()
    {
        return $this->getName() . '-' . $this->post->ID;
    }

    /**
     * Get CSS wrapper selector.
     *
     * Retrieve the wrapper selector for the page settings model.
     *
     * @since 1.6.0
     *
     * @return string CSS wrapper selector
     */
    public function getCssWrapperSelector()
    {
        $document = Plugin::$instance->documents->get($this->post_parent->ID);

        return $document->getCssWrapperSelector();
    }

    /**
     * Get panel page settings.
     *
     * Retrieve the panel setting for the page settings model.
     *
     * @since 1.6.0
     *
     * @return array Panel settings {
     *               @var string $title The panel title
     *               }
     */
    public function getPanelPageSettings()
    {
        $document = Plugin::$instance->documents->get($this->post->ID);

        return [
            /* translators: %s: Document title */
            'title' => sprintf(__('%s Settings'), $document::getTitle()),
        ];
    }

    /**
     * On export post meta.
     *
     * When exporting data, check if the post is not using page template and
     * exclude it from the exported Elementor data.
     *
     * @since 1.6.0
     *
     * @param array $element_data Element data
     *
     * @return array Element data to be exported
     */
    public function onExport($element_data)
    {
        if (!empty($element_data['settings']['template'])) {
            /*
             * @var \Elementor\Modules\PageTemplates\Module $page_templates_module
             */
            $page_templates_module = Plugin::$instance->modules_manager->getModules('page-templates');
            $is_elementor_template = (bool) $page_templates_module->getTemplatePath($element_data['settings']['template']);

            if (!$is_elementor_template) {
                unset($element_data['settings']['template']);
            }
        }

        return $element_data;
    }

    /**
     * Register model controls.
     *
     * Used to add new controls to the page settings model.
     *
     * @since 1.6.0
     */
    protected function _registerControls()
    {
        // Check if it's a real model, or abstract (for example - on import )
        if ($this->post->ID) {
            $document = Plugin::$instance->documents->getDocOrAutoSave($this->post->ID);

            if ($document) {
                $controls = $document->getControls();

                foreach ($controls as $control_id => $args) {
                    $this->addControl($control_id, $args);
                }
            }
        }
    }
}
