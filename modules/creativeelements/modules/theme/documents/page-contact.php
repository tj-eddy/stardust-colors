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

use CE\ModulesXThemeXDocumentsXThemePageDocument as ThemePageDocument;

class ModulesXThemeXDocumentsXPageContact extends ThemePageDocument
{
    public function getName()
    {
        return 'page-contact';
    }

    public static function getTitle()
    {
        return __('Contact Page');
    }

    protected function getRemoteLibraryConfig()
    {
        $config = parent::getRemoteLibraryConfig();

        $config['category'] = '';
        $config['type'] = 'page';
        $config['default_route'] = 'templates/pages';

        return $config;
    }
}
