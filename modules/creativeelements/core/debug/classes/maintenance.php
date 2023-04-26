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

use CE\CoreXDebugXClassesXInspectionBase as InspectionBase;

class CoreXDebugXClassesXMaintenance extends InspectionBase
{
    public function run()
    {
        return \Configuration::get('PS_SHOP_ENABLE') || in_array(\Tools::getRemoteAddr(), explode(',', \Configuration::get('PS_MAINTENANCE_IP')));
    }

    public function getName()
    {
        return 'maintenance';
    }

    public function getMessage()
    {
        return __('The shop is in maintenance mode, please whitelist your IP') . '. ';
    }

    public function getHelpDocText()
    {
        return __('Configure');
    }

    public function getHelpDocUrl()
    {
        return \Context::getContext()->link->getAdminLink('AdminMaintenance');
    }
}
