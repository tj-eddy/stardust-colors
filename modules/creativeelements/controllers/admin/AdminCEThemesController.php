<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

class AdminCEThemesController extends ModuleAdminController
{
    public $bootstrap = true;

    protected $type;

    protected $_defaultOrderBy = 'title';

    public function __construct()
    {
        parent::__construct();

        if ((Tools::getIsset('updatece_theme') || Tools::getIsset('addce_theme')) && Shop::getContextShopID() === null) {
            $this->displayWarning(
                $this->trans('You are in a multistore context: any modification will impact all your shops, or each shop of the active group.', [], 'Admin.Catalog.Notification')
            );
        }

        if ($type = Tools::getValue('type')) {
            if ('template' === $type) {
                unset($this->context->cookie->submitFilterce_theme);
                unset($this->context->cookie->cethemesce_themeFilter_type);
            } else {
                $this->context->cookie->submitFilterce_theme = 1;
                $this->context->cookie->cethemesce_themeFilter_type = $type;
            }
        }

        $this->type = $type ?: $this->context->cookie->cethemesce_themeFilter_type;

        if ('kit' === $this->type) {
            $this->table = 'ce_template';
            $this->identifier = 'id_ce_template';
            $this->className = 'CETemplate';
            $this->action_link = CESmarty::get(_CE_TEMPLATES_ . 'admin/admin.tpl', 'ce_action_link');
            $this->_where = "AND a.type = 'kit'";

            $this->fields_list = [
                'id_ce_template' => [
                    'title' => $this->trans('ID', [], 'Admin.Global'),
                    'class' => 'fixed-width-xs',
                    'align' => 'center',
                ],
                'title' => [
                    'title' => $this->trans('Title', [], 'Admin.Global'),
                ],
                'type' => [
                    'title' => $this->trans('Type', [], 'Admin.Catalog.Feature'),
                    'class' => 'fixed-width-xl',
                    'type' => 'select',
                    'list' => [
                        'kit' => $this->l('Theme Style'),
                    ],
                    'filter_key' => 'type',
                ],
                'date_add' => [
                    'title' => $this->trans('Created on', [], 'Modules.Facetedsearch.Admin'),
                    'class' => 'fixed-width-lg',
                    'type' => 'datetime',
                ],
                'date_upd' => [
                    'title' => $this->l('Modified on'),
                    'class' => 'fixed-width-lg',
                    'type' => 'datetime',
                ],
                'active' => [
                    'title' => $this->trans('Status', [], 'Admin.Global'),
                    'class' => 'fixed-width-xs',
                    'align' => 'center',
                    'active' => 'status',
                    'type' => 'bool',
                ],
            ];
        } else {
            $this->table = 'ce_theme';
            $this->identifier = 'id_ce_theme';
            $this->className = 'CETheme';
            $this->lang = true;

            $table_shop = _DB_PREFIX_ . $this->table . '_shop';
            $this->_select = 'sa.*';
            $this->_join = "LEFT JOIN $table_shop sa ON sa.id_ce_theme = a.id_ce_theme AND b.id_shop = sa.id_shop";
            $this->_where = 'AND sa.id_shop = ' . (int) $this->context->shop->id;

            $this->fields_list = [
                'id_ce_theme' => [
                    'title' => $this->trans('ID', [], 'Admin.Global'),
                    'class' => 'fixed-width-xs',
                    'align' => 'center',
                ],
                'title' => [
                    'title' => $this->trans('Title', [], 'Admin.Global'),
                ],
                'type' => [
                    'title' => $this->trans('Type', [], 'Admin.Catalog.Feature'),
                    'class' => 'fixed-width-xl',
                    'type' => 'select',
                    'list' => [
                        'header' => $this->l('Header'),
                        'footer' => $this->l('Footer'),
                        'page' => $this->l('Page'),
                        'page-index' => $this->l('Home Page'),
                        'page-contact' => $this->l('Contact Page'),
                        'prod' => $this->l('Product'),
                        'product' => $this->l('Product Page'),
                        'product-quick-view' => $this->l('Quick View'),
                        'product-miniature' => $this->l('Product Miniature'),
                        'page-not-found' => $this->l('404 Page'),
                    ],
                    'filter_key' => 'type',
                ],
                'date_add' => [
                    'title' => $this->trans('Created on', [], 'Modules.Facetedsearch.Admin'),
                    'filter_key' => 'sa!date_add',
                    'class' => 'fixed-width-lg',
                    'type' => 'datetime',
                ],
                'date_upd' => [
                    'title' => $this->l('Modified on'),
                    'filter_key' => 'sa!date_upd',
                    'class' => 'fixed-width-lg',
                    'type' => 'datetime',
                ],
                'active' => [
                    'title' => $this->trans('Status', [], 'Admin.Global'),
                    'filter_key' => 'sa!active',
                    'class' => 'fixed-width-xs',
                    'align' => 'center',
                    'active' => 'status',
                    'type' => 'bool',
                ],
            ];
        }

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Delete selected', [], 'Admin.Notifications.Info'),
                'icon' => 'icon-trash text-danger',
                'confirm' => $this->trans('Delete selected items?', [], 'Admin.Notifications.Info'),
            ],
        ];

        $this->fields_options['theme_settings'] = [
            'class' => 'ce-theme-panel',
            'icon' => 'icon-cog',
            'title' => $this->l('Theme Settings'),
            'fields' => [
                'elementor_active_kit' => [
                    'title' => $this->l('Theme Style'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array_merge(
                        [
                            ['value' => '', 'name' => $this->trans('Add new', [], 'Admin.Actions')],
                        ],
                        CETemplate::getKitOptions()
                    ),
                ],
                'CE_PAGE_INDEX' => [
                    'title' => $this->l('Home Page'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array_merge(
                        [
                            ['value' => '', 'name' => $this->l('Default')],
                        ],
                        CETheme::getOptions('page-index', $this->context->language->id, $this->context->shop->id)
                    ),
                ],
                'CE_PRODUCT' => [
                    'title' => $this->l('Product Page'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array_merge(
                        [
                            ['value' => '', 'name' => $this->l('Default')],
                        ],
                        CETheme::getOptions('product', $this->context->language->id, $this->context->shop->id)
                    ),
                ],
                'CE_HEADER' => [
                    'title' => $this->l('Header'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array_merge(
                        [
                            ['value' => '', 'name' => $this->l('Default')],
                        ],
                        CETheme::getOptions('header', $this->context->language->id, $this->context->shop->id)
                    ),
                ],
                'CE_PAGE_CONTACT' => [
                    'title' => $this->l('Contact Page'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array_merge(
                        [
                            ['value' => '', 'name' => $this->l('Default')],
                        ],
                        CETheme::getOptions('page-contact', $this->context->language->id, $this->context->shop->id)
                    ),
                ],
                'CE_PRODUCT_QUICK_VIEW' => [
                    'title' => $this->l('Quick View'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array_merge(
                        [
                            ['value' => '', 'name' => $this->l('Default')],
                        ],
                        CETheme::getOptions('product-quick-view', $this->context->language->id, $this->context->shop->id)
                    ),
                ],
                'CE_FOOTER' => [
                    'title' => $this->l('Footer'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array_merge(
                        [
                            ['value' => '', 'name' => $this->l('Default')],
                        ],
                        CETheme::getOptions('footer', $this->context->language->id, $this->context->shop->id)
                    ),
                ],
                'CE_PAGE_NOT_FOUND' => [
                    'title' => $this->l('404 Page'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array_merge(
                        [
                            ['value' => '', 'name' => $this->l('Default')],
                        ],
                        CETheme::getOptions('page-not-found', $this->context->language->id, $this->context->shop->id)
                    ),
                ],
                'CE_PRODUCT_MINIATURE' => [
                    'title' => $this->l('Product Miniature'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => array_merge(
                        [
                            ['value' => '', 'name' => $this->l('Default')],
                        ],
                        CETheme::getOptions('product-miniature', $this->context->language->id, $this->context->shop->id)
                    ),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
    }

    protected function processUpdateOptions()
    {
        parent::processUpdateOptions();

        if (!Configuration::get('elementor_active_kit')) {
            $id_kit = substr(CE\Plugin::instance()->kits_manager->getActiveId(), 0, -6);
            ${'_POST'}['elementor_active_kit'] = $id_kit;
            $this->fields_options['theme_settings']['fields']['elementor_active_kit']['list'][] = [
                'value' => $id_kit,
                'name' => "#$id_kit {$this->l('Default')}",
            ];
        }
    }

    public function initHeader()
    {
        parent::initHeader();

        $id_lang = $this->context->language->id;
        $link = $this->context->link->getAdminLink('AdminCEThemes');
        $new = Tools::getIsset('addce_theme');
        $tabs = &$this->context->smarty->tpl_vars['tabs']->value;

        foreach ($tabs as &$tab0) {
            foreach ($tab0['sub_tabs'] as &$tab1) {
                if ('AdminParentCEContent' !== $tab1['class_name']) {
                    continue;
                }
                foreach ($tab1['sub_tabs'] as &$tab2) {
                    if ('AdminCEThemes' !== $tab2['class_name']) {
                        continue;
                    }
                    $sub_tabs = &$tab2['sub_tabs'];
                    $tab = Tab::getTab($id_lang, Tab::getIdFromClassName('AdminCEThemes'));

                    $tab['name'] = $this->l('Template');
                    $tab['current'] = $new || (!$this->type || 'template' === $this->type) && !$this->object;
                    $tab['href'] = "$link&type=template";
                    $sub_tabs[] = $tab;

                    $type = $this->object ? $this->object->type : $this->type;

                    $tab['name'] = $this->l('Header');
                    $tab['current'] = !$new && 'header' === $type;
                    $tab['href'] = "$link&type=header";
                    $sub_tabs[] = $tab;

                    $tab['name'] = $this->l('Footer');
                    $tab['current'] = !$new && 'footer' === $type;
                    $tab['href'] = "$link&type=footer";
                    $sub_tabs[] = $tab;

                    $tab['name'] = $this->l('Page');
                    $tab['current'] = !$new && strpos($type, 'page') === 0;
                    $tab['href'] = "$link&type=page";
                    $sub_tabs[] = $tab;

                    $tab['name'] = $this->l('Product');
                    $tab['current'] = !$new && 'product' === $type;
                    $tab['href'] = "$link&type=product";
                    $sub_tabs[] = $tab;

                    $tab['name'] = $this->l('Quick View');
                    $tab['current'] = !$new && 'product-quick-view' === $type;
                    $tab['href'] = "$link&type=product-quick-view";
                    $sub_tabs[] = $tab;

                    $tab['name'] = $this->l('Miniature');
                    $tab['current'] = !$new && 'product-miniature' === $type;
                    $tab['href'] = "$link&type=product-miniature";
                    $sub_tabs[] = $tab;

                    $tab['name'] = $this->l('Theme Style');
                    $tab['current'] = !$new && 'kit' === $type;
                    $tab['href'] = "$link&type=kit";
                    $sub_tabs[] = $tab;

                    return;
                }
            }
        }
    }

    public function initToolBarTitle()
    {
        if ('add' === $this->display) {
            $this->page_header_toolbar_title = $this->l('Add New');
        } elseif ('edit' === $this->display) {
            $this->page_header_toolbar_title = 'kit' === $this->type
                ? sprintf($this->l('Edit %s'), $this->l('Theme Style'))
                : $this->l('Edit Template');
        } else {
            $this->page_header_toolbar_title = $this->l('Theme Builder');
        }

        $this->context->smarty->assign('icon', 'icon-list');

        $this->toolbar_title[] = $this->l('kit' === $this->type ? 'List' : 'Templates List');
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display) || 'options' === $this->display) {
            $this->page_header_toolbar_btn["add{$this->table}"] = [
                'href' => self::$currentIndex . "&add{$this->table}&token={$this->token}",
                'desc' => $this->trans('Add new', [], 'Admin.Actions'),
                'icon' => 'process-icon-new',
            ];
        }
        parent::initPageHeaderToolbar();
    }

    public function initModal()
    {
        // Prevent modals
    }

    public function initContent()
    {
        $this->context->smarty->assign('current_tab_level', 3);

        return parent::initContent();
    }

    public function processFilter()
    {
        $type = Tools::getValue('type', $this->context->cookie->cethemesce_themeFilter_type);

        if ('page' === $type) {
            // Trick for type filtering, use LIKE instead of =
            $this->fields_list['type']['type'] = 'text';
        }
        parent::processFilter();

        $this->fields_list['type']['type'] = 'select';
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

        // Translate template types
        if (!empty($this->_list)) {
            $type = &$this->fields_list['type']['list'];

            foreach ($this->_list as &$row) {
                empty($type[$row['type']]) or $row['type'] = $type[$row['type']];
            }
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        empty($this->action_link) or $this->addRowAction('export');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function displayExportLink($token, $id, $name = null)
    {
        $link = $this->context->link->getAdminLink('AdminCEEditor') . '&' . http_build_query([
            'ajax' => 1,
            'action' => 'elementor_library_direct_actions',
            'library_action' => 'export_template',
            'source' => 'local',
            'template_id' => "{$id}010000",
        ]);

        return sprintf($this->action_link, Tools::safeOutput($link), '_self', 'mail-forward', $this->trans('Export', [], 'Admin.Actions'));
    }

    protected function getThemeType()
    {
        $theme_types = [
            ['value' => '', 'label' => $this->l('Select...')],
            ['value' => 'header', 'label' => $this->l('Header')],
            ['value' => 'footer', 'label' => $this->l('Footer')],
            ['value' => 'page-index', 'label' => $this->l('Home Page')],
            ['value' => 'page-contact', 'label' => $this->l('Contact Page')],
            ['value' => 'product', 'label' => $this->l('Product Page')],
            ['value' => 'product-quick-view', 'label' => $this->l('Quick View')],
            ['value' => 'product-miniature', 'label' => $this->l('Product Miniature')],
            ['value' => 'page-not-found', 'label' => $this->l('404 Page')],
        ];
        if (!empty($this->object->type)) {
            return array_filter($theme_types, function ($option) {
                return $this->object->type === $option['value'];
            });
        }

        return $theme_types;
    }

    public function renderForm()
    {
        $kit = 'kit' === $this->type;
        $col = !$kit && count(Language::getLanguages(false, false, true)) > 1 ? 9 : 7;

        version_compare(_PS_VERSION_, '1.7.8', '<') or --$col;

        $this->fields_form = [
            'legend' => [
                'title' => $this->l($kit ? 'Theme Style' : 'Template'),
                'icon' => 'icon-edit',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->trans('Title', [], 'Admin.Global'),
                    'name' => 'title',
                    'lang' => !$kit,
                    'col' => $col,
                ],
                [
                    'type' => 'select',
                    'label' => $this->trans('Type', [], 'Admin.Catalog.Feature'),
                    'name' => 'type',
                    'required' => true,
                    'options' => [
                        'id' => 'value',
                        'name' => 'label',
                        'query' => $this->getThemeType(),
                    ],
                    'col' => 3,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l($kit ? 'Style' : 'Content'),
                    'name' => 'content',
                    'lang' => !$kit,
                    'col' => $col,
                    'class' => 'hidden',
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Status', [], 'Admin.Global'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', [], 'Admin.Global'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', [], 'Admin.Global'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->trans('Save', [], 'Admin.Actions'),
            ],
            'buttons' => [
                'save_and_stay' => [
                    'type' => 'submit',
                    'title' => $this->trans('Save and stay', [], 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'name' => "submitAdd{$this->table}AndStay",
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        if ($kit) {
            $this->fields_form['input'][1]['options']['query'] = [
                ['value' => 'kit', 'label' => $this->l('Theme Style')],
            ];
        } elseif (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = [
                'type' => 'shop',
                'label' => $this->trans('Shop association', [], 'Admin.Global'),
                'name' => 'checkBoxShopAsso',
            ];
        }

        return parent::renderForm();
    }

    protected function l($string, $module = 'creativeelements', $addslashes = false, $htmlentities = true)
    {
        $js = $addslashes || !$htmlentities;
        $str = Translate::getModuleTranslation($module, $string, '', null, $js, _CE_LOCALE_);

        return $htmlentities ? $str : stripslashes($str);
    }
}
