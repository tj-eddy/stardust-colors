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

use CE\ModulesXLibraryXDocumentsXLibraryDocument as LibraryDocument;
use CE\TemplateLibraryXSourceLocal as SourceLocal;

/**
 * Elementor section library document.
 *
 * Elementor section library document handler class is responsible for
 * handling a document of a section type.
 */
class ModulesXLibraryXDocumentsXNotSupported extends LibraryDocument
{
    /**
     * Get document properties.
     *
     * Retrieve the document properties.
     *
     * @static
     *
     * @return array Document properties
     */
    public static function getProperties()
    {
        $properties = parent::getProperties();

        // $properties['admin_tab_group'] = '';
        $properties['register_type'] = false;
        $properties['is_editable'] = false;
        $properties['show_in_library'] = false;

        $properties['cpt'] = [
            SourceLocal::CPT,
        ];

        return $properties;
    }

    /**
     * Get document name.
     *
     * Retrieve the document name.
     *
     * @return string Document name
     */
    public function getName()
    {
        return 'not-supported';
    }

    /**
     * Get document title.
     *
     * Retrieve the document title.
     *
     * @static
     *
     * @return string Document title
     */
    public static function getTitle()
    {
        return __('Not Supported');
    }

    public function saveTemplateType()
    {
        // Do nothing.
    }

    // public function printAdminColumnType()

    // public function filterAdminRowActions($actions)
}
