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

class WidgetCategoryTree extends WidgetCategoryBase
{
    const REMOTE_RENDER = true;

    public function getName()
    {
        return 'category-tree';
    }

    public function getTitle()
    {
        return __('Category Tree');
    }

    public function getIcon()
    {
        return 'eicon-toggle';
    }

    public function getCategories()
    {
        return ['premium'];
    }

    protected function _registerControls()
    {
        $this->registerCategoryTreeSection();
    }

    protected function render()
    {
        $settings = $this->getSettings();
        $category = $this->getRootCategory($settings['root_category']);
        $tpl = 'ps_categorytree/views/templates/hook/ps_categorytree.tpl';
        $theme_tpl = _PS_THEME_DIR_ . 'modules/' . $tpl;

        $this->context->smarty->assign([
            'currentCategory' => $category->id,
            'categories' => $this->getCategoryTree($category, $settings),
        ]);

        echo $this->context->smarty->fetch(file_exists($theme_tpl) ? $theme_tpl : _PS_MODULE_DIR_ . $tpl);
    }
}
