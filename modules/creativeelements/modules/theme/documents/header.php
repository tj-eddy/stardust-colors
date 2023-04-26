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

use CE\ModulesXThemeXDocumentsXHeaderFooterBase as HeaderFooterBase;

class ModulesXThemeXDocumentsXHeader extends HeaderFooterBase
{
    public static function getProperties()
    {
        $properties = parent::getProperties();

        $properties['location'] = 'header';

        return $properties;
    }

    public function getName()
    {
        return 'header';
    }

    public static function getTitle()
    {
        return __('Header');
    }
}
