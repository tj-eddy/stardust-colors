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

class WidgetModule extends WidgetBase
{
    const REMOTE_RENDER = true;

    private static $product_hooks = [
        'displayproductadditionalinfo',
        'displayproductactions',
        'displayfooterproduct',
        'displaycartextraproductactions',
    ];

    public function getName()
    {
        return 'ps-widget-module';
    }

    public function getTitle()
    {
        return __('Module');
    }

    public function getIcon()
    {
        return 'eicon-puzzle-piece';
    }

    public function getCategories()
    {
        return ['premium'];
    }

    public function getKeywords()
    {
        return ['module', 'hook'];
    }

    public static function getModuleConfig($module)
    {
        $iso = \Context::getContext()->language->iso_code;
        $path = _PS_MODULE_DIR_ . "$module/config_$iso.xml";

        if ('en' === $iso || !file_exists($path)) {
            $path = _PS_MODULE_DIR_ . "$module/config.xml";

            if (!file_exists($path)) {
                return null;
            }
        }

        libxml_use_internal_errors(true);
        $config = @simplexml_load_file($path);
        libxml_clear_errors();

        return !$config ? null : (object) [
            'name' => (string) $config->name,
            'displayName' => (string) $config->displayName,
            'author' => (string) $config->author,
            'tab' => (string) $config->tab,
        ];
    }

    protected function getModuleOptions()
    {
        static $modules = [];

        if (!$modules && \Context::getContext()->controller instanceof \AdminCEEditorController) {
            $exclude_tabs = get_option('elementor_exclude_modules', []);
            $table = _DB_PREFIX_ . 'module';
            $inner_join_shop = \Shop::addSqlAssociation('module', 'm');
            $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("
                SELECT m.`name` FROM `$table` m $inner_join_shop
                WHERE m.`active` = 1 AND m.`name` NOT IN ('creativeelements', 'creativepopup', 'layerslider', 'messengerchat')
            ");
            if ($rows) {
                foreach ($rows as &$row) {
                    $mod = self::getModuleConfig($row['name']);

                    if ($mod && !in_array($mod->tab, $exclude_tabs)) {
                        $modules[$mod->name] = $mod->displayName ?: $mod->name;
                    }
                }
            }
        }

        return $modules;
    }

    protected function _registerControls()
    {
        $this->startControlsSection(
            'section_module',
            [
                'label' => __('Module'),
            ]
        );

        $this->addControl(
            'module',
            [
                'label_block' => true,
                'type' => ControlsManager::SELECT2,
                'select2options' => [
                    'placeholder' => __('Select...'),
                ],
                'options' => $this->getModuleOptions(),
            ]
        );

        $this->addControl(
            'hook',
            [
                'label' => __('Hook'),
                'type' => ControlsManager::TEXT,
                'description' => __('Specify the required hook if needed.'),
                'input_list' => [
                    'displayHome',
                    'displayTop',
                    'displayBanner',
                    'displayNav1',
                    'displayNav2',
                    'displayNavFullWidth',
                    'displayTopColumn',
                    'displayLeftColumn',
                    'displayRightColumn',
                    'displayFooterBefore',
                    'displayFooter',
                    'displayFooterAfter',
                    'displayFooterProduct',
                ],
                'condition' => [
                    'module!' => '0',
                ],
            ]
        );

        $this->endControlsSection();
    }

    public static function isInCustomerGroups(\Module $module)
    {
        if (!\Group::isFeatureActive()) {
            return true;
        }

        $context = \Context::getContext();
        $customer = $context->customer;

        if ($customer instanceof \Customer && $customer->isLogged()) {
            $groups = $customer->getGroups();
        } elseif ($customer instanceof \Customer && $customer->isLogged(true)) {
            $groups = [\Configuration::get('PS_GUEST_GROUP')];
        } else {
            $groups = [\Configuration::get('PS_UNIDENTIFIED_GROUP')];
        }

        $table = _DB_PREFIX_ . 'module_group';
        $id_shop = (int) $context->shop->id;
        $id_module = (int) $module->id;
        $id_groups = implode(', ', array_map('intval', $groups));

        return (bool) \Db::getInstance()->getValue(
            "SELECT 1 FROM $table WHERE id_module = $id_module AND id_shop = $id_shop AND id_group IN ($id_groups)"
        );
    }

    protected function renderModule($module, $hook_name, $hook_args = [])
    {
        $res = '';
        try {
            $mod = \Module::getInstanceByName($module);

            if (!empty($mod->active) && self::isInCustomerGroups($mod)) {
                if (\Tools::getValue('render') === 'widget') {
                    try {
                        if (method_exists($mod, 'hookDisplayHeader')) {
                            $mod->hookDisplayHeader();
                        } elseif (method_exists($mod, 'hookHeader')) {
                            $mod->hookHeader();
                        }
                    } catch (\Exception $e) {
                        // do nothing
                    }
                }

                if (in_array(strtolower($hook_name), self::$product_hooks)) {
                    $vars = &\Context::getContext()->smarty->tpl_vars;

                    if (isset($vars['product']->value)) {
                        $hook_args['product'] = $vars['product']->value;
                    }
                    if (stripos($hook_name, 'footer') && isset($vars['category']->value)) {
                        $hook_args['category'] = $vars['category']->value;
                    }
                }

                if (method_exists($mod, "hook$hook_name")) {
                    $res = \Hook::coreCallHook($mod, "hook$hook_name", $hook_args);
                } elseif (method_exists($mod, 'renderWidget')) {
                    $res = \Hook::coreRenderWidget($mod, $hook_name, $hook_args);
                } elseif (method_exists($mod, '__call')) {
                    $res = \Hook::coreCallHook($mod, "hook$hook_name", $hook_args);
                }
            }
        } catch (\Exception $ex) {
            // skip
        }

        return $res;
    }

    protected function render()
    {
        $settings = $this->getSettingsForDisplay();

        if ($settings['module']) {
            echo "<!-- {$settings['module']} -->";
            echo $this->renderModule(
                $settings['module'],
                !empty($settings['hook']) ? $settings['hook'] : 'displayCEWidget'
            );
        }
    }

    public function renderPlainContent()
    {
    }
}
