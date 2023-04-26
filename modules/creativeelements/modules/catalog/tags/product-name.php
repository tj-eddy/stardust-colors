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

use CE\CoreXDynamicTagsXTag as Tag;
use CE\ModulesXDynamicTagsXModule as Module;

class ModulesXCatalogXTagsXProductName extends Tag
{
    const REMOTE_RENDER = true;

    public function getName()
    {
        return 'product-name';
    }

    public function getTitle()
    {
        return __('Product Name');
    }

    public function getGroup()
    {
        return Module::CATALOG_GROUP;
    }

    public function getCategories()
    {
        return [Module::TEXT_CATEGORY];
    }

    public function render()
    {
        echo \Context::getContext()->smarty->tpl_vars['product']->value['name'];
    }

    protected function renderSmarty()
    {
        echo '{$product.name}';
    }
}
