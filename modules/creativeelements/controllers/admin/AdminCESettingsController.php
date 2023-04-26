<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

class AdminCESettingsController extends ModuleAdminController
{
    protected $activate_url = 'https://pagebuilder.webshopworks.com/?connect=activate';

    protected $viewportChanged = false;

    protected $clearCss = false;

    protected $sub_tab;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'CESettings';
        $this->table = 'configuration';
        $this->sub_tab = Tools::getValue('subTab', 'general');

        parent::__construct();

        $this->fields_options['general'] = [
            'tab' => $this->l('General'),
            'icon' => 'icon-cog',
            'title' => $this->l('General Settings'),
            'fields' => [
                'subTab' => [
                    'type' => 'hidden',
                ],
                'elementor_frontend_edit' => [
                    'title' => $this->l('Show Edit Icon on Frontend'),
                    'desc' => $this->l('Displays an edit icon on frontend while employee has active session. By clicking on this icon the live editor will open.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => '1',
                ],
                'elementor_max_revisions' => [
                    'title' => $this->l('Limit Revisions'),
                    'desc' => $this->l('Sets the maximum number of revisions per content.'),
                    'validation' => 'isUnsignedInt',
                    'cast' => 'intval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => [
                        ['value' => 0, 'name' => $this->l('Disable Revision History')],
                        ['value' => 1, 'name' => 1],
                        ['value' => 2, 'name' => 2],
                        ['value' => 3, 'name' => 3],
                        ['value' => 4, 'name' => 4],
                        ['value' => 5, 'name' => 5],
                        ['value' => 10, 'name' => 10],
                        ['value' => 15, 'name' => 15],
                        ['value' => 20, 'name' => 20],
                        ['value' => 25, 'name' => 25],
                        ['value' => 30, 'name' => 30],
                    ],
                ],
                'elementor_disable_color_schemes' => [
                    'title' => $this->l('Disable Default Colors'),
                    'desc' => $this->l('If you prefer to inherit the colors from your theme, you can disable this feature.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => '0',
                    'clearCss' => true,
                ],
                'elementor_disable_typography_schemes' => [
                    'title' => $this->l('Disable Default Fonts'),
                    'desc' => $this->l('If you prefer to inherit the fonts from your theme, you can disable this feature here.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => '0',
                    'clearCss' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $this->fields_options['style'] = [
            'tab' => $this->l('Style'),
            'icon' => 'icon-adjust',
            'title' => $this->l('Style Settings'),
            'fields' => [
                'elementor_default_generic_fonts' => [
                    'title' => $this->l('Default Generic Fonts'),
                    'desc' => $this->l('The list of fonts used if the chosen font is not available.'),
                    'cast' => 'strval',
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                ],
                'elementor_stretched_section_container' => [
                    'title' => $this->l('Stretched Section Fit To'),
                    'desc' => $this->l('Enter parent element selector to which stretched sections will fit to (e.g. #primary / .wrapper / main etc). Leave blank to fit to page width.'),
                    'cast' => 'strval',
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                ],
                'elementor_page_title_selector' => [
                    'title' => $this->l('Page Title Selector'),
                    'desc' => sprintf(
                        $this->l("You can hide the title at document settings. This works for themes that have ”%s” selector. If your theme's selector is different, please enter it above."),
                        'header.page-header'
                    ),
                    'cast' => 'strval',
                    'type' => 'text',
                ],
                'elementor_page_wrapper_selector' => [
                    'title' => $this->l('Content Wrapper Selector'),
                    'desc' => sprintf(
                        $this->l("You can clear margin, padding, max-width from content wrapper at document settings. This works for themes that have ”%s” selector. If your theme's selector is different, please enter it above."),
                        '#content, #wrapper, #wrapper .container'
                    ),
                    'cast' => 'strval',
                    'type' => 'text',
                ],
                'elementor_viewport_lg' => [
                    'title' => $this->l('Tablet Breakpoint'),
                    'desc' => sprintf($this->l('Sets the breakpoint between desktop and tablet devices. Below this breakpoint tablet layout will appear (Default: %dpx).'), 1025),
                    'suffix' => 'px',
                    'validation' => 'isUnsignedInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'class' => 'fixed-width-sm',
                ],
                'elementor_viewport_md' => [
                    'title' => $this->l('Mobile Breakpoint'),
                    'desc' => sprintf($this->l('Sets the breakpoint between tablet and mobile devices. Below this breakpoint mobile layout will appear (Default: %dpx).'), 768),
                    'suffix' => 'px',
                    'validation' => 'isUnsignedInt',
                    'cast' => 'intval',
                    'type' => 'text',
                    'class' => 'fixed-width-sm',
                ],
                'elementor_global_image_lightbox' => [
                    'title' => $this->l('Image Lightbox'),
                    'desc' => $this->l('Open all image links in a lightbox popup window. The lightbox will automatically work on any link that leads to an image file.'),
                    'hint' => $this->l('You can customize the lightbox design by going to: Top-left hamburger icon > Global Settings > Lightbox.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => '1',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $this->fields_options['advanced'] = [
            'tab' => $this->l('Advanced'),
            'icon' => 'icon-cogs',
            'title' => $this->l('Advanced Settings'),
            'info' => CESmarty::sprintf(_CE_TEMPLATES_ . 'admin/admin.tpl', 'ce_alert', 'warning', $this->l(
                'Do not change these options without experience, incorrect settings might break your site.'
            )),
            'fields' => [
                'elementor_css_print_method' => [
                    'title' => $this->l('CSS Print Method'),
                    'desc' => $this->l('Use external CSS files for all generated stylesheets. Choose this setting for better performance (recommended).'),
                    'cast' => 'strval',
                    'type' => 'select',
                    'identifier' => 'value',
                    'list' => [
                        ['value' => 'external', 'name' => $this->l('External File')],
                        ['value' => 'internal', 'name' => $this->l('Internal Embedding')],
                    ],
                    'clearCss' => true,
                ],
                'elementor_exclude_modules' => [
                    'title' => $this->l('Exclude Categories from Module widget'),
                    'type' => 'multiselect',
                    'class' => 'chosen',
                    'cast' => 'json_encode',
                    'auto_value' => false,
                    'identifier' => 'value',
                    'list' => [
                        ['value' => 'administration', 'name' => $this->trans('Administration', [], 'Admin.Modules.Feature')],
                        ['value' => 'advertising_marketing', 'name' => $this->trans('Advertising & Marketing', [], 'Admin.Modules.Feature')],
                        ['value' => 'analytics_stats', 'name' => $this->trans('Analytics & Stats', [], 'Admin.Modules.Feature')],
                        ['value' => 'billing_invoicing', 'name' => $this->trans('Taxes & Invoicing', [], 'Admin.Modules.Feature')],
                        ['value' => 'checkout', 'name' => $this->trans('Checkout', [], 'Admin.Modules.Feature')],
                        ['value' => 'content_management', 'name' => $this->trans('Content Management', [], 'Admin.Modules.Feature')],
                        ['value' => 'customer_reviews', 'name' => $this->trans('Customer Reviews', [], 'Admin.Modules.Feature')],
                        ['value' => 'export', 'name' => $this->trans('Export', [], 'Admin.Actions')],
                        ['value' => 'front_office_features', 'name' => $this->trans('Front office Features', [], 'Admin.Modules.Feature')],
                        ['value' => 'i18n_localization', 'name' => $this->trans('Internationalization & Localization', [], 'Admin.Modules.Feature')],
                        ['value' => 'merchandizing', 'name' => $this->trans('Merchandising', [], 'Admin.Modules.Feature')],
                        ['value' => 'migration_tools', 'name' => $this->trans('Migration Tools', [], 'Admin.Modules.Feature')],
                        ['value' => 'payments_gateways', 'name' => $this->trans('Payments & Gateways', [], 'Admin.Modules.Feature')],
                        ['value' => 'payment_security', 'name' => $this->trans('Site certification & Fraud prevention', [], 'Admin.Modules.Feature')],
                        ['value' => 'pricing_promotion', 'name' => $this->trans('Pricing & Promotion', [], 'Admin.Modules.Feature')],
                        ['value' => 'quick_bulk_update', 'name' => $this->trans('Quick / Bulk update', [], 'Admin.Modules.Feature')],
                        ['value' => 'seo', 'name' => $this->trans('SEO', [], 'Admin.Catalog.Feature')],
                        ['value' => 'shipping_logistics', 'name' => $this->trans('Shipping & Logistics', [], 'Admin.Modules.Feature')],
                        ['value' => 'slideshows', 'name' => $this->trans('Slideshows', [], 'Admin.Modules.Feature')],
                        ['value' => 'smart_shopping', 'name' => $this->trans('Comparison site & Feed management', [], 'Admin.Modules.Feature')],
                        ['value' => 'market_place', 'name' => $this->trans('Marketplace', [], 'Admin.Modules.Feature')],
                        ['value' => 'others', 'name' => $this->trans('Other Modules', [], 'Admin.Modules.Feature')],
                        ['value' => 'mobile', 'name' => $this->trans('Mobile', [], 'Admin.Global')],
                        ['value' => 'dashboard', 'name' => $this->trans('Dashboard', [], 'Admin.Global')],
                        ['value' => 'emailing', 'name' => $this->trans('Emailing & SMS', [], 'Admin.Modules.Feature')],
                        ['value' => 'social_networks', 'name' => $this->trans('Social Networks', [], 'Admin.Modules.Feature')],
                        ['value' => 'social_community', 'name' => $this->trans('Social & Community', [], 'Admin.Modules.Feature')],
                    ],
                ],
                'elementor_load_fontawesome' => [
                    'title' => $this->l('Load FontAwesome Library'),
                    'desc' => $this->l('FontAwesome gives you scalable vector icons that can instantly be customized - size, color, drop shadow, and anything that can be done with the power of CSS.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => '1',
                ],
                'elementor_load_fa4_shim' => [
                    'title' => $this->l('Load Font Awesome 4 Support'),
                    'desc' => $this->l('Font Awesome 4 support is a backward compatibility that makes sure all previously selected Font Awesome 4 icons are displayed correctly while using Font Awesome 6 library.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                ],
                'elementor_load_waypoints' => [
                    'title' => $this->l('Load Waypoints Library'),
                    'desc' => $this->l('Waypoints library is the easiest way to trigger a function when you scroll to an element.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => '1',
                ],
                'elementor_load_swiper' => [
                    'title' => $this->l('Load Swiper Library'),
                    'desc' => $this->l('Swiper is the most modern mobile touch slider with hardware accelerated transitions and amazing native behavior.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'default' => '1',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $this->fields_options['experiments'] = [
            'tab' => $this->l('Experiments'),
            'icon' => 'icon-magic',
            'title' => $this->l('Experiments'),
            'info' => CESmarty::sprintf(_CE_TEMPLATES_ . 'admin/admin.tpl', 'ce_alert', 'info', $this->l(
                "Access new and experimental features from Creative Elements before they're officially released. As these features are still in development, they are likely to change, evolve or even be removed altogether."
            )),
            'fields' => [
                'elementor_remove_hidden' => [
                    'title' => $this->l('Remove Hidden Elements'),
                    'desc' => $this->l('When you hide elements on "Advanced tab / Responsive section" their markup will be removed from DOM.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                ],
                'elementor_visibility' => [
                    'title' => $this->l('Visibility Section'),
                    'desc' => $this->l('If you would like to schedule elements or filter them by selected customer groups, then this feature will be handy. It will appear under Advanced tab.'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['license'] = [
            'icon' => 'process-icon-file icon-file-text',
            'desc' => $this->l('License'),
            'js' => "$('#modal_license').modal()",
        ];
        $this->page_header_toolbar_btn['regenerate-css'] = [
            'icon' => 'process-icon-reload icon-rotate-right',
            'desc' => $this->l('Regenerate CSS'),
            'js' => '//' . Tools::safeOutput(
                $this->l('Styles set in Creative Elements are saved in CSS files. Recreate those files, according to the most recent settings.')
            ),
        ];
        if (Shop::getContext() === Shop::CONTEXT_SHOP) {
            $this->page_header_toolbar_btn['replace-url'] = [
                'icon' => 'process-icon-refresh',
                'desc' => $this->l('Replace URL'),
                'js' => "$('#modal_replace_url').modal()",
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function initModal()
    {
        $ce_license = Configuration::getGlobalValue('CE_LICENSE');

        $this->modals[] = [
            'modal_id' => 'modal_license',
            'modal_class' => 'modal-md',
            'modal_title' => $ce_license
                ? CESmarty::get(_CE_TEMPLATES_ . 'admin/admin.tpl', 'ce_modal_license_status')
                : $this->l('Activate License'),
            'modal_content' => CESmarty::sprintf(
                _CE_TEMPLATES_ . 'admin/admin.tpl',
                'ce_modal_license',
                Tools::safeOutput($this->context->link->getAdminLink('AdminCESettings') . '&action=activate'),
                $this->l(
                    $ce_license
                    ? 'Your website is activated. Want to activate this website by a different license?'
                    : 'Please activate your license to get unlimited access to the template library.'
                ),
                $this->l($ce_license ? 'Switch License' : 'Activate')
            ),
        ];
        $this->modals[] = [
            'modal_id' => 'modal_replace_url',
            'modal_class' => 'modal-md',
            'modal_title' => $this->l('Update Site Address (URL)'),
            'modal_content' => CESmarty::sprintf(
                _CE_TEMPLATES_ . 'admin/admin.tpl',
                'ce_modal_replace_url',
                $this->l('It is strongly recommended that you backup your database before using Replace URL.'),
                $this->l('http://old-url.com'),
                $this->l('http://new-url.com'),
                $this->l('Enter your old and new URLs for your PrestaShop installation, to update all Creative Elements data (Relevant for domain transfers or move to \'HTTPS\').'),
                $this->l('Replace URL')
            ),
        ];
    }

    public function initHeader()
    {
        parent::initHeader();

        $tabs = &$this->context->smarty->tpl_vars['tabs']->value;

        foreach ($tabs as &$tab0) {
            foreach ($tab0['sub_tabs'] as &$tab1) {
                if ('AdminParentCEContent' !== $tab1['class_name']) {
                    continue;
                }
                foreach ($tab1['sub_tabs'] as &$tab2) {
                    if ('AdminCESettings' !== $tab2['class_name']) {
                        continue;
                    }
                    $id = 0;
                    $url = $this->context->link->getAdminLink('AdminCESettings');
                    $sub_tabs = &$tab2['sub_tabs'];

                    foreach ($this->fields_options as $tab => &$options) {
                        $sub_tabs[] = [
                            'id_tab' => --$id,
                            'active' => true,
                            'class_name' => $tab,
                            'current' => $tab === $this->sub_tab,
                            'href' => "$url&subTab=$tab",
                            'name' => $options['tab'],
                        ];
                        $options['class'] = $tab === $this->sub_tab ? '' : 'hidden';
                    }

                    return;
                }
            }
        }
    }

    public function initContent()
    {
        $this->context->smarty->assign('current_tab_level', 3);

        return parent::initContent();
    }

    protected function processActivate()
    {
        $url = $this->context->link->getAdminLink('AdminCESettings');

        if (Tools::getIsset('license')) {
            Configuration::updateGlobalValue('CE_LICENSE', Tools::getValue('license'));
            $url .= '#license';
        } else {
            list($p, $r) = explode('://', CE\wp_referer());
            $encode = 'base64_encode';
            $url = $this->activate_url . '&' . http_build_query([
                'response_type' => 'code',
                'client_id' => substr($encode(_COOKIE_KEY_), 0, 32),
                'auth_secret' => rtrim($encode("$r?" . Tools::passwdGen(23 - strlen($r))), '='),
                'state' => substr($encode($this->module->module_key), 0, 12),
                'redirect_uri' => urlencode($url),
            ]);
        }
        Tools::redirectAdmin($url);
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->css_files[_MODULE_DIR_ . 'creativeelements/views/css/settings.css?v=' . _CE_VERSION_] = 'all';
        $this->js_files[] = _MODULE_DIR_ . 'creativeelements/views/js/settings.js?v=' . _CE_VERSION_;
    }

    protected function processUpdateOptions()
    {
        unset(${'_POST'}['subTab']);

        foreach ($this->fields_options as $tab => &$panel) {
            foreach ($panel['fields'] as $option => &$field) {
                if (('style' === $tab || !empty($field['clearCss'])) &&
                    Configuration::get($option) != Tools::getValue($option)
                ) {
                    $this->clearCss = true;
                    break 2;
                }
            }
        }

        parent::processUpdateOptions();

        if ($this->viewportChanged) {
            CE\Plugin::instance();
            CE\CoreXResponsiveXResponsive::compileStylesheetTemplates();
        }
        empty($this->clearCss) or CE\Plugin::instance()->files_manager->clearCache();
    }

    protected function updateOptionElementorPageTitleSelector($val)
    {
        $val = trim($val);

        if (!empty($val) && Validate::isCleanHtml($val)) {
            Configuration::updateValue('elementor_page_title_selector', $val);
        } else {
            $this->errors[] = $this->trans('Required field', [], 'Shop.Forms.Errors') . ': ' . $this->l('Page Title Selector');
        }
    }

    protected function updateOptionElementorFullWidthSelector($val)
    {
        $val = trim($val);

        if (!empty($val) && Validate::isCleanHtml($val)) {
            Configuration::updateValue('elementor_full_width_selector', $val);
        } else {
            $this->errors[] = $this->trans('Required field', [], 'Shop.Forms.Errors') . ': ' . $this->l('Content Wrapper Selector');
        }
    }

    protected function updateOptionElementorViewportLg($val)
    {
        if (Configuration::get('elementor_viewport_lg') != $val) {
            Configuration::updateValue('elementor_viewport_lg', $val);

            $this->viewportChanged = true;
        }
    }

    protected function updateOptionElementorViewportMd($val)
    {
        if (Configuration::get('elementor_viewport_md') != $val) {
            Configuration::updateValue('elementor_viewport_md', $val);

            $this->viewportChanged = true;
        }
    }

    public function ajaxProcessRegenerateCss()
    {
        CE\Plugin::instance()->files_manager->clearCache();

        CE\wp_send_json_success();
    }

    public function ajaxProcessReplaceUrl()
    {
        $from = trim(Tools::getValue('from'));
        $to = trim(Tools::getValue('to'));

        $is_valid_urls = filter_var($from, FILTER_VALIDATE_URL) && filter_var($to, FILTER_VALIDATE_URL);

        if (!$is_valid_urls) {
            CE\wp_send_json_error(CE\__("The `from` and `to` URL's must be a valid URL"));
        }

        if ($from === $to) {
            CE\wp_send_json_error(CE\__("The `from` and `to` URL's must be different"));
        }

        $db = Db::getInstance();
        $table = _DB_PREFIX_ . 'ce_meta';
        $id = sprintf('%02d', $this->context->shop->id);
        $old = str_replace('/', '\\\/', $from);
        $new = str_replace('/', '\\\/', $to);

        $result = $db->execute("
            UPDATE $table SET `value` = REPLACE(`value`, '$old', '$new')
            WHERE `name` = '_elementor_data' AND `id` LIKE '%$id' AND `value` <> '[]'
        ");

        if (false === $result) {
            CE\wp_send_json_error(CE\__('An error occurred'));
        } else {
            CE\wp_send_json_success(sprintf(CE\__('%d Rows Affected'), $db->affected_rows()));
        }
    }

    protected function l($string, $module = 'creativeelements', $addslashes = false, $htmlentities = true)
    {
        $js = $addslashes || !$htmlentities;
        $str = Translate::getModuleTranslation($module, $string, '', null, $js, _CE_LOCALE_);

        return $htmlentities ? $str : stripslashes($str);
    }
}
