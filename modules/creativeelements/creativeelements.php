<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

define('_CE_VERSION_', '2.9.14');
define('_CE_PATH_', _PS_MODULE_DIR_ . 'creativeelements/');
define('_CE_URL_', (defined('_PS_BO_ALL_THEMES_DIR_') ? _MODULE_DIR_ : 'modules/') . 'creativeelements/');
define('_CE_ASSETS_PATH_', _CE_PATH_ . 'views/');
define('_CE_ASSETS_URL_', _CE_URL_ . 'views/');
define('_CE_TEMPLATES_', _CE_PATH_ . 'views/templates/');

const _CE_CLASSES_ = [
    'CEAssetManager' => 'classes/assets/CEAssetManager.php',
    'CERevision' => 'classes/CERevision.php',
    'CETemplate' => 'classes/CETemplate.php',
    'CETheme' => 'classes/CETheme.php',
    'CEContent' => 'classes/CEContent.php',
    'CEFont' => 'classes/CEFont.php',
    'CEIconSet' => 'classes/CEIconSet.php',
];
spl_autoload_register(function ($class) {
    isset(_CE_CLASSES_[$class]) && require _CE_PATH_ . _CE_CLASSES_[$class];
});
require_once _CE_PATH_ . 'classes/CESmarty.php';
require_once _CE_PATH_ . 'includes/plugin.php';

class CreativeElements extends Module
{
    const VIEWED_PRODUCTS_LIMIT = 100;

    protected static $controller;

    protected static $tplOverride;

    protected static $overrides = [
        'Category',
        'CmsCategory',
        'Manufacturer',
        'Supplier',
    ];

    public $controllers = [
        'ajax',
        'preview',
    ];

    public function __construct($name = null, Context $context = null)
    {
        $this->name = 'creativeelements';
        $this->tab = 'content_management';
        $this->version = '2.9.14';
        $this->author = 'WebshopWorks';
        $this->module_key = '7a5ebcc21c1764675f1db5d0f0eacfe5';
        $this->ps_versions_compliancy = ['min' => '1.7.4', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->displayName = $this->l('Creative Elements - live Theme & Page Builder');
        $this->description = $this->l('The most advanced frontend drag & drop page builder. Create high-end, pixel perfect websites at record speeds. Any theme, any page, any design.');
        parent::__construct($this->name, null);

        $this->checkThemeChange();
    }

    public function install()
    {
        require_once _CE_PATH_ . 'classes/CEDatabase.php';

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        CEDatabase::initConfigs();

        if (!CEDatabase::createTables()) {
            $this->_errors[] = Db::getInstance()->getMsgError();

            return false;
        }

        if ($res = parent::install() && CEDatabase::updateTabs()) {
            foreach (CEDatabase::getHooks() as $hook) {
                $res = $res && $this->registerHook($hook, null, 1);
            }
        }

        return $res;
    }

    public function uninstall()
    {
        foreach (Tab::getCollectionFromModule($this->name) as $tab) {
            $tab->delete();
        }

        return parent::uninstall();
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all) && Db::getInstance()->update(
            'tab',
            ['active' => 1],
            "module = 'creativeelements' AND class_name != 'AdminCEEditor'"
        );
    }

    public function disable($force_all = false)
    {
        return Db::getInstance()->update(
            'tab',
            ['active' => 0],
            "module = 'creativeelements'"
        ) && parent::disable($force_all);
    }

    public function addOverride($classname)
    {
        try {
            return parent::addOverride($classname);
        } catch (Exception $ex) {
            return false;
        }
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminCEThemes'));
    }

    public function hookDisplayBackOfficeHeader($params = [])
    {
        Configuration::get("PS_ALLOW_HTML_\x49FRAME") or Configuration::updateValue("PS_ALLOW_HTML_\x49FRAME", 1);

        // Handle migrate
        if ((Configuration::getGlobalValue('ce_migrate') || Tools::getIsset('CEMigrate')) &&
            Db::getInstance()->executeS("SHOW TABLES LIKE '%_ce_meta'")
        ) {
            require_once _CE_PATH_ . 'classes/CEMigrate.php';
            CEMigrate::registerJavascripts();
        }

        $footer_product = '';
        preg_match('~/([^/]+)/(\d+)/edit\b~', $_SERVER['REQUEST_URI'], $req);
        $controller = strtolower(Tools::getValue('controller'));

        switch ($controller) {
            case 'admincetemplates':
                $id_type = CE\UId::TEMPLATE;
                $id = (int) Tools::getValue('id_ce_template');
                break;
            case 'admincethemes':
                $id_type = Tools::getIsset('id_ce_template') ? CE\UId::TEMPLATE : CE\UId::THEME;
                $id = (int) Tools::getValue('id_ce_template', Tools::getValue('id_ce_theme'));
                break;
            case 'admincecontent':
                $id_type = CE\UId::CONTENT;
                $id = (int) Tools::getValue('id_ce_content');
                break;
            case 'admincmscontent':
                if ($req && 'category' === $req[1] || Tools::getIsset('addcms_category') || Tools::getIsset('updatecms_category')) {
                    $id_type = CE\UId::CMS_CATEGORY;
                    $id = (int) Tools::getValue('id_cms_category', $req ? $req[2] : 0);
                    break;
                }
                $id_type = CE\UId::CMS;
                $id = (int) Tools::getValue('id_cms', $req ? $req[2] : 0);
                break;
            case 'adminproducts':
                $id_type = CE\UId::PRODUCT;
                $id = (int) Tools::getValue('id_product', basename(explode('?', $_SERVER['REQUEST_URI'])[0]));

                require_once _CE_PATH_ . 'classes/CEContent.php';
                $footer_product = new CE\UId(CEContent::getFooterProductId($id), CE\UId::CONTENT, 0, $this->context->shop->id);
                break;
            case 'admincategories':
                $id_type = CE\UId::CATEGORY;
                $id = (int) Tools::getValue('id_category', $req ? $req[2] : 0);
                break;
            case 'adminmanufacturers':
                $id_type = CE\UId::MANUFACTURER;
                $id = (int) Tools::getValue('id_manufacturer', $req ? $req[2] : 0);
                break;
            case 'adminsuppliers':
                $id_type = CE\UId::SUPPLIER;
                $id = (int) Tools::getValue('id_supplier', $req ? $req[2] : 0);
                break;
            case 'adminxippost':
                $id_type = CE\UId::XIPBLOG_POST;
                $id = (int) Tools::getValue('id_xipposts');
                break;
            case 'adminstblog':
                $id_type = CE\UId::STBLOG_POST;
                $id = (int) Tools::getValue('id_st_blog');
                break;
            case 'adminblogposts':
                if ('advanceblog' === $this->context->controller->module->name) {
                    $id_type = CE\UId::ADVANCEBLOG_POST;
                    $id = (int) Tools::getValue('id_post');
                }
                break;
            case 'adminpsblogblogs':
                $id_type = CE\UId::PSBLOG_POST;
                $id = (int) Tools::getValue('id_psblog_blog');
                break;
            case 'admintvcmspost':
                $id_type = CE\UId::TVCMSBLOG_POST;
                $id = (int) Tools::getValue('id_tvcmsposts');
                break;
            case 'adminmodules':
                $configure = strtolower(Tools::getValue('configure'));

                if ('ybc_blog' === $configure && Tools::getValue('control') === 'post') {
                    $id_type = CE\UId::YBC_BLOG_POST;
                    $id = (int) Tools::getValue('id_post');
                    break;
                }
                if ('prestablog' === $configure && Tools::getIsset('editNews')) {
                    $id_type = CE\UId::PRESTABLOG_POST;
                    $id = (int) Tools::getValue('idN');
                    break;
                }
                if ('hiblog' === $configure) {
                    $id_type = CE\UId::HIBLOG_POST;
                    $id = 0;
                    $hideEditor = [];
                    break;
                }
                break;
            case 'adminmaintenance':
                $id_type = CE\UId::CONTENT;
                $id = CEContent::getMaintenanceId();

                $uids = CE\UId::getBuiltList($id, $id_type, $this->context->shop->id);
                $hideEditor = empty($uids) ? $uids : array_keys($uids[$this->context->shop->id]);
                break;
        }

        if (isset($id)) {
            self::$controller = $this->context->controller;
            self::$controller->addJQuery();
            self::$controller->js_files[] = $this->_path . 'views/js/admin-ce.js?v=' . _CE_VERSION_;
            self::$controller->css_files[$this->_path . 'views/css/admin-ce.css?v=' . _CE_VERSION_] = 'all';

            $uid = new CE\UId($id, $id_type, 0, Shop::getContext() === Shop::CONTEXT_SHOP ? $this->context->shop->id : 0);

            isset($hideEditor) or $hideEditor = $uid->getBuiltLangIdList();

            $display_suppliers = Configuration::get('PS_DISPLAY_SUPPLIERS');
            $display_manufacturers = version_compare(_PS_VERSION_, '1.7.7', '<')
                ? $display_suppliers
                : Configuration::get('PS_DISPLAY_MANUFACTURERS');

            Media::addJsDef([
                'ceAdmin' => [
                    'uid' => "$uid",
                    'hideEditor' => $hideEditor,
                    'footerProduct' => "$footer_product",
                    'i18n' => [
                        'edit' => str_replace("'", '’', $this->l('Edit with Creative Elements')),
                        'save' => str_replace("'", '’', $this->l('Please save the form before editing with Creative Elements')),
                        'error' => str_replace("'", '’', $this->getErrorMsg()),
                    ],
                    'editorUrl' => Tools::safeOutput($this->context->link->getAdminLink('AdminCEEditor') . '&uid='),
                    'languages' => Language::getLanguages(true, $uid->id_shop),
                    'editSuppliers' => (int) $display_suppliers,
                    'editManufacturers' => (int) $display_manufacturers,
                ],
            ]);
            $this->context->smarty->assign('edit_width_ce', $this->context->link->getAdminLink('AdminCEEditor'));
        }

        return $this->context->smarty->fetch(_CE_TEMPLATES_ . 'hook/backoffice_header.tpl');
    }

    protected function getErrorMsg()
    {
        $id_tab = Tab::getIdFromClassName('AdminCEEditor');
        $access = Profile::getProfileAccess($this->context->employee->id_profile, $id_tab);

        if ('1' !== $access['view']) {
            return CE\Helper::transError('You do not have permission to view this.');
        }

        $class = isset(self::$controller->className) ? self::$controller->className : '';

        if (in_array($class, self::$overrides)) {
            $loadObject = new ReflectionMethod(self::$controller, 'loadObject');
            $loadObject->setAccessible(true);

            if (empty($loadObject->invoke(self::$controller, true)->active) && !defined("$class::CE_OVERRIDE")) {
                return $this->l('You can not edit items which are not displayed, because an override file is missing. Please contact us on https://addons.prestashop.com');
            }
        }

        return '';
    }

    public function hookActionFrontControllerAfterInit($params = [])
    {
        // Compatibility fix for PS 1.7.3 - 1.7.6
        $this->hookActionFrontControllerInitAfter($params);
    }

    public function hookActionFrontControllerInitAfter($params = [])
    {
        if (null !== self::$controller) {
            return;
        }
        self::$controller = $this->context->controller;

        $tpl_dir = $this->context->smarty->getTemplateDir();
        array_unshift($tpl_dir, _CE_TEMPLATES_ . 'front/theme/');
        $this->context->smarty->setTemplateDir($tpl_dir);
        $id_miniature = (int) (Tools::getIsset('id_miniature') && self::hasAdminToken('AdminCEThemes')
            ? Tools::getValue('id_miniature')
            : Configuration::get('CE_PRODUCT_MINIATURE')
        );
        if ($id_miniature) {
            $this->context->smarty->assign(
                'CE_PRODUCT_MINIATURE_UID',
                new CE\UId($id_miniature, CE\UId::THEME, $this->context->language->id, $this->context->shop->id)
            );
            CE\Plugin::instance()->frontend->hasElementorInPage(true);
        }

        if (self::$controller instanceof CategoryController) {
            // PS 1.7.8+ fix for 404 page
            $category = self::$controller->getCategory();

            if (!Validate::isLoadedObject($category) || !$category->active) {
                Tools::redirect('index.php?controller=404');
            }
        }
    }

    public function hookHeader()
    {
        // Compatibility fix for PS 1.7.7.x upgrade
        return $this->hookDisplayHeader();
    }

    public function hookDisplayHeader()
    {
        $this->hookActionFrontControllerInitAfter();

        $plugin = CE\Plugin::instance();
        CE\did_action('template_redirect') or CE\do_action('template_redirect');

        $id_kit = (int) Configuration::get('elementor_active_kit');
        $id_pqv = (int) Configuration::get('CE_PRODUCT_QUICK_VIEW');

        if ($id_kit > 0) {
            $this->context->smarty->tpl_vars['page']->value['body_classes']["ce-kit-$id_kit"] = 1;

            $plugin->frontend->hasElementorInPage(true);
        } elseif ($id_pqv) {
            $plugin->frontend->hasElementorInPage(true);
        }

        if (self::$controller instanceof ProductController) {
            $this->addViewedProduct(self::$controller->getProduct()->id);

            if ($id_pqv && Tools::getValue('action') === 'quickview') {
                CE\UId::$_ID = new CE\UId($id_pqv, CE\UId::THEME, $this->context->language->id, $this->context->shop->id);

                self::skipOverrideLayoutTemplate();
                $this->context->smarty->assign('CE_PRODUCT_QUICK_VIEW_ID', $id_pqv);
            }
        }

        $uid_preview = self::getPreviewUId(false);

        if ($uid_preview && CE\UId::CONTENT === $uid_preview->id_type) {
            Tools::getIsset('maintenance') && $this->displayMaintenancePage();
        }

        // PS fix: OverrideLayoutTemplate hook doesn't exec on forbidden page
        http_response_code() !== 403 or $this->hookOverrideLayoutTemplate();
    }

    protected function addViewedProduct($id_product)
    {
        $products = isset($this->context->cookie->ceViewedProducts)
            ? explode(',', $this->context->cookie->ceViewedProducts)
            : []
        ;
        if (in_array($id_product, $products)) {
            $products = array_diff($products, [$id_product]);
        }
        array_unshift($products, (int) $id_product);

        while (count($products) > self::VIEWED_PRODUCTS_LIMIT) {
            array_pop($products);
        }
        $this->context->cookie->ceViewedProducts = implode(',', $products);

        if (Tools::getValue('action') === 'quickview') {
            $this->context->cookie->write();
        }
    }

    public static function skipOverrideLayoutTemplate()
    {
        self::$tplOverride = '';
    }

    public function hookOverrideLayoutTemplate($params = [])
    {
        if (null !== self::$tplOverride || !self::$controller) {
            return self::$tplOverride;
        }
        self::$tplOverride = '';

        if (self::isMaintenance()) {
            return self::$tplOverride;
        }
        // Page Content
        $controller = self::$controller;
        $tpl_vars = &$this->context->smarty->tpl_vars;
        $front = strtolower(preg_replace('/(ModuleFront)?Controller(Override)?$/i', '', get_class($controller)));
        // PrestaBlog fix for non-default blog URL
        strpos($front, 'prestablog') === 0 && property_exists($controller, 'news') && $front = 'prestablogblog';

        switch ($front) {
            case 'creativeelementspreview':
                $model = self::getPreviewUId(false)->getModel();
                $key = $model::${'definition'}['table'];

                if (isset($tpl_vars[$key]->value['id'])) {
                    $id = $tpl_vars[$key]->value['id'];
                    $desc = ['description' => &$tpl_vars[$key]->value['content']];
                }
                break;
            case 'cms':
                $model = class_exists('CMS') ? 'CMS' : 'CMSCategory';
                $key = $model::${'definition'}['table'];

                if (isset($tpl_vars[$key]->value['id'])) {
                    $id = $tpl_vars[$key]->value['id'];
                    $desc = ['description' => &$tpl_vars[$key]->value['content']];

                    CE\add_action('wp_head', 'print_og_image');
                } elseif (isset($tpl_vars['cms_category']->value['id'])) {
                    $model = 'CMSCategory';
                    $id = $tpl_vars['cms_category']->value['id'];
                    $desc = &$tpl_vars['cms_category']->value;
                }
                break;
            case 'product':
            case 'category':
            case 'manufacturer':
            case 'supplier':
                $model = $front;

                if (isset($tpl_vars[$model]->value['id'])) {
                    $id = $tpl_vars[$model]->value['id'];
                    $desc = &$tpl_vars[$model]->value;
                }
                break;
            case 'ybc_blogblog':
                $model = 'Ybc_blog_post_class';

                if (isset($tpl_vars['blog_post']->value['id_post'])) {
                    $id = $tpl_vars['blog_post']->value['id_post'];
                    $desc = &$tpl_vars['blog_post']->value;

                    if (Tools::getIsset('adtoken') && self::hasAdminToken('AdminModules')) {
                        // override post status for preview
                        $tpl_vars['blog_post']->value['enabled'] = 1;
                    }
                }
                break;
            case 'xipblogsingle':
                $model = 'XipPostsClass';

                if (isset($tpl_vars['xipblogpost']->value['id_xipposts'])) {
                    $id = $tpl_vars['xipblogpost']->value['id_xipposts'];
                    $desc = ['description' => &$tpl_vars['xipblogpost']->value['post_content']];
                }
                break;
            case 'stblogarticle':
                $model = 'StBlogClass';

                if (isset($tpl_vars['blog']->value['id'])) {
                    $id = $tpl_vars['blog']->value['id'];
                    $desc = ['description' => &$tpl_vars['blog']->value['content']];
                    break;
                }
                $blogProp = new ReflectionProperty($controller, 'blog');
                $blogProp->setAccessible(true);
                $blog = $blogProp->getValue($controller);

                if (isset($blog->id)) {
                    $id = $blog->id;
                    $desc = ['description' => &$blog->content];
                }
                break;
            case 'advanceblogdetail':
                $model = 'BlogPosts';

                if (isset($tpl_vars['postData']->value['id_post'])) {
                    $id = $tpl_vars['postData']->value['id_post'];
                    $desc = ['description' => &$tpl_vars['postData']->value['post_content']];
                }
                break;
            case 'prestablogblog':
                $model = 'NewsClass';
                $newsProp = new ReflectionProperty($controller, 'news');
                $newsProp->setAccessible(true);
                $news = $newsProp->getValue($controller);

                if (isset($news->id)) {
                    $id = $news->id;

                    if (isset($tpl_vars['tpl_unique'])) {
                        $desc = ['description' => &$tpl_vars['tpl_unique']->value];
                    } else {
                        $desc = ['description' => &$news->content];
                    }
                }
                break;
            case 'hiblogpostdetails':
                $model = 'HiBlogPost';

                if (isset($tpl_vars['post']->value['id_post'])) {
                    $id = $tpl_vars['post']->value['id_post'];
                    $desc = &$tpl_vars['post']->value;

                    if (Tools::getIsset('adtoken') && self::hasAdminToken('AdminModules')) {
                        // override post status for preview
                        $tpl_vars['post']->value['enabled'] = 1;
                    }
                }
                break;
            case 'tvcmsblogsingle':
                $model = 'TvcmsPostsClass';

                if (isset($tpl_vars['tvcmsblogpost']->value['id_tvcmsposts'])) {
                    $id = $tpl_vars['tvcmsblogpost']->value['id_tvcmsposts'];
                    $desc = ['description' => &$tpl_vars['tvcmsblogpost']->value['post_content']];
                }
                break;
            case 'pm_advancedsearch4searchresults':
                $model = 'category';

                if (isset($tpl_vars[$model]->value['id'])) {
                    $id = $tpl_vars[$model]->value['id'];
                    $desc = &$tpl_vars[$model]->value;
                }
                break;
        }

        if (isset($id)) {
            $uid_preview = self::getPreviewUId();

            if ($uid_preview && $uid_preview->id === (int) $id && $uid_preview->id_type === CE\UId::getTypeId($model)) {
                CE\UId::$_ID = $uid_preview;
            } elseif (!CE\UId::$_ID || in_array(CE\UId::$_ID->id_type, [CE\UId::CONTENT, CE\UId::THEME, CE\UId::TEMPLATE])) {
                CE\UId::$_ID = new CE\UId($id, CE\UId::getTypeId($model), $this->context->language->id, $this->context->shop->id);
            }

            if (CE\UId::$_ID) {
                $this->addBodyClasses('elementor-page', CE\UId::$_ID->toDefault());

                $desc['description'] = CE\apply_filters('the_content', $desc['description']);
            }
        }

        // Theme Builder
        $themes = [
            'header' => Configuration::get('CE_HEADER'),
            'footer' => Configuration::get('CE_FOOTER'),
        ];
        $pages = [
            'index' => 'page-index',
            'contact' => 'page-contact',
            'product' => 'product',
            'pagenotfound' => 'page-not-found',
        ];
        foreach ($pages as $page_type => $theme_type) {
            if ($front === $page_type) {
                $themes[$theme_type] = Configuration::get(self::getThemeVarName($theme_type));
                break;
            }
        }
        $uid = CE\UId::$_ID;
        $uid_preview = self::getPreviewUId(false);

        if ($uid_preview && (CE\UId::THEME === $uid_preview->id_type || CE\UId::TEMPLATE === $uid_preview->id_type)) {
            $preview = self::renderTheme($uid_preview);
            $document = CE\Plugin::$instance->documents->getDocForFrontend($uid_preview);
            $type_preview = $document->getTemplateType();
            $this->context->smarty->assign(self::getThemeVarName($type_preview), $preview);

            if ('product-quick-view' === $type_preview) {
                unset($desc);
                $desc = ['description' => &$preview];
                CE\Plugin::$instance->modules_manager->getModules('catalog')->handleProductQuickView();

                $this->context->smarty->assign('CE_PRODUCT_QUICK_VIEW_ID', $uid_preview->id);
            } elseif ('product-miniature' === $type_preview) {
                unset($desc);
                $desc = ['description' => &$preview];
                CE\Plugin::$instance->modules_manager->getModules('catalog')->handleProductMiniature();

                $this->context->smarty->assign('CE_PRODUCT_MINIATURE_ID', $uid_preview->id);
            } elseif ('product' === $type_preview) {
                $this->context->smarty->assign('CE_PRODUCT_ID', $uid_preview->id);
            } elseif (strpos($type_preview, 'page-') === 0) {
                $desc = ['description' => &$preview];
                CE\add_action('wp_head', 'print_og_image');
            }
            array_search($type_preview, $pages) && $this->addBodyClasses('ce-theme', $uid_preview->id);
            unset($themes[$type_preview]);
        }
        if (isset($pages[$front]) && !empty($themes[$pages[$front]])) {
            $theme_type = $pages[$front];
            $uid_theme = new CE\UId($themes[$theme_type], CE\UId::THEME, $this->context->language->id, $this->context->shop->id);

            if ('product' === $page_type) {
                $this->context->smarty->assign([
                    'CE_PRODUCT_ID' => $uid_theme->id,
                    'CE_PRODUCT' => self::renderTheme($uid_theme),
                ]);
            } else {
                $desc = ['description' => self::renderTheme($uid_theme)];
                $this->context->smarty->assign(self::getThemeVarName($theme_type), $desc['description']);
                CE\add_action('wp_head', 'print_og_image');
            }
            $this->addBodyClasses('ce-theme', $uid_theme->id);
            unset($themes[$theme_type]);
        }

        self::$tplOverride = CE\apply_filters('template_include', self::$tplOverride);

        if (strrpos(self::$tplOverride, 'layout-canvas') !== false) {
            empty($desc) or $this->context->smarty->assign('ce_desc', $desc);
        } else {
            foreach ($themes as $theme_type => $id_ce_theme) {
                empty($id_ce_theme) or $this->context->smarty->assign(
                    self::getThemeVarName($theme_type),
                    self::renderTheme(
                        new CE\UId($id_ce_theme, CE\UId::THEME, $this->context->language->id, $this->context->shop->id)
                    )
                );
            }
        }
        CE\UId::$_ID = $uid;

        return self::$tplOverride;
    }

    protected function addBodyClasses($class, $id)
    {
        $body_classes = &$this->context->smarty->tpl_vars['page']->value['body_classes'];
        $body_classes[$class] = 1;
        $body_classes["$class-$id"] = 1;
    }

    protected function displayMaintenancePage()
    {
        Configuration::set('PS_SHOP_ENABLE', false);
        Configuration::set('PS_MAINTENANCE_IP', '');

        $displayMaintenancePage = new ReflectionMethod($this->context->controller, 'displayMaintenancePage');
        $displayMaintenancePage->setAccessible(true);
        $displayMaintenancePage->invoke($this->context->controller);
    }

    public function hookDisplayMaintenance($params = [])
    {
        if (self::getPreviewUId(false)) {
            http_response_code(200);
            header_remove('Retry-After');
        } else {
            $this->hookActionFrontControllerInitAfter();
            $this->hookDisplayHeader();
        }

        CE\add_filter('the_content', function () {
            $uid = CE\get_the_ID();
            $this->context->smarty->assign('ce_content', new CEContent($uid->id, $uid->id_lang, $uid->id_shop));

            CE\remove_all_filters('the_content');
        }, 0);

        if (!$maintenance = $this->renderContent('displayMaintenance', $params)) {
            return;
        }
        self::$controller->registerJavascript('jquery', 'js/jquery/jquery-1.11.0.min.js');

        $this->context->smarty->assign([
            'iso_code' => $this->context->language->iso_code,
            'favicon' => Configuration::get('PS_FAVICON'),
            'favicon_update_time' => Configuration::get('PS_IMG_UPDATE_TIME'),
        ]);

        return $maintenance;
    }

    public function hookDisplayHome()
    {
        // Do not render hook if home page is overriden
        if (!Configuration::get('CE_PAGE_INDEX')) {
            return $this->renderContent('displayHome');
        }
    }

    public function hookDisplayFooterProduct($params = [])
    {
        return $this->renderContent('displayFooterProduct', $params);
    }

    public function __call($method, $args)
    {
        if (stripos($method, 'hookActionObject') === 0 && stripos($method, 'DeleteAfter') !== false) {
            call_user_func_array([$this, 'hookActionObjectDeleteAfter'], $args);
        } elseif (stripos($method, 'hook') === 0) {
            // render hook only after Header init
            if (null !== self::$tplOverride) {
                return $this->renderContent(substr($method, 4), $args);
            }
        } else {
            throw new Exception('Can not find method: ' . $method);
        }
    }

    public function renderContent($hook_name = null)
    {
        if (!$hook_name) {
            return;
        }
        $out = '';
        $rows = CEContent::getIdsByHook(
            $hook_name,
            $id_lang = $this->context->language->id,
            $id_shop = $this->context->shop->id,
            Tools::getValue('id_product', 0),
            self::getPreviewUId(false)
        );
        if ($rows) {
            $uid = CE\UId::$_ID;

            foreach ($rows as $row) {
                CE\UId::$_ID = new CE\UId($row['id'], CE\UId::CONTENT, $id_lang, $id_shop);

                $out .= CE\apply_filters('the_content', '');
            }
            CE\UId::$_ID = $uid;
        }

        return $out;
    }

    public static function renderTheme($uid)
    {
        CE\UId::$_ID = $uid;

        return CE\apply_filters('the_content', '');
    }

    public function registerHook($hook_name, $shop_list = null, $position = null)
    {
        $res = parent::registerHook($hook_name, $shop_list);

        if ($res && is_numeric($position)) {
            $this->updatePosition(Hook::getIdByName($hook_name), 0, $position);
        }

        return $res;
    }

    public function hookCETemplate($params = [])
    {
        if (empty($params['id']) || !Validate::isLoadedObject($tpl = new CETemplate($params['id'])) || !$tpl->active) {
            return;
        }
        $uid = CE\UId::$_ID;
        CE\UId::$_ID = new CE\UId($params['id'], CE\UId::TEMPLATE);
        $out = CE\apply_filters('the_content', '');
        CE\UId::$_ID = $uid;

        return $out;
    }

    public function hookActionObjectDeleteAfter($params = [])
    {
        $model = get_class($params['object']);
        $id_type = CE\UId::getTypeId($model);
        $id_start = sprintf('%d%02d', $params['object']->id, $id_type);

        // Delete meta data
        Db::getInstance()->delete('ce_meta', "id LIKE '{$id_start}____'");

        // Delete CSS files
        array_map('unlink', glob(_CE_PATH_ . "views/css/ce/$id_start????.css", GLOB_NOSORT));
    }

    public function hookActionObjectProductDeleteAfter($params = [])
    {
        $id_product = (int) $params['object']->id;
        $this->hookActionObjectDeleteAfter($params);

        // Delete displayFooterProduct content
        if ($id_ce_content = CEContent::getFooterProductId($id_product)) {
            $content = new CEContent($id_ce_content);
            Validate::isLoadedObject($content) && $content->delete();
        }
        // Remove deleted product ID from page settings
        $ps = _DB_PREFIX_;
        Db::getInstance()->execute("
            UPDATE `{$ps}ce_meta`
            SET `value` = REPLACE(`value`, '\"preview_id\":\"$id_product\"', '\"preview_id\":\"\"')
            WHERE `name` = '_elementor_page_settings' AND `value` LIKE '%\"preview_id\":\"$id_product\"%'
        ");
    }

    public function hookActionProductAdd($params = [])
    {
        if (isset($params['request']) && $params['request']->attributes->get('action') === 'duplicate') {
            $id_product_duplicate = (int) $params['request']->attributes->get('id');
        } elseif (Tools::getIsset('duplicateproduct')) {
            $id_product_duplicate = (int) Tools::getValue('id_product');
        }

        if (isset($id_product_duplicate, $params['id_product']) &&
            $built_list = CE\UId::getBuiltList($id_product_duplicate, CE\UId::PRODUCT)
        ) {
            $db = CE\Plugin::instance()->db;
            $uid = new CE\UId($params['id_product'], CE\UId::PRODUCT, 0);

            foreach ($built_list as $id_shop => &$langs) {
                foreach ($langs as $id_lang => $uid_from) {
                    $uid->id_lang = $id_lang;
                    $uid->id_shop = $id_shop;

                    $db->copyElementorMeta($uid_from, $uid);
                }
            }
        }
    }

    protected function checkThemeChange()
    {
        if (!$theme = Configuration::get('CE_THEME')) {
            Configuration::updateValue('CE_THEME', _THEME_NAME_);
        } elseif (_THEME_NAME_ !== $theme) {
            require_once _CE_PATH_ . 'classes/CEDatabase.php';

            // register missing hooks after changing theme
            foreach (CEDatabase::getHooks() as $hook) {
                $this->registerHook($hook, null, 1);
            }
            Configuration::updateValue('CE_THEME', _THEME_NAME_);
        }
    }

    public static function getPreviewUId($embed = true)
    {
        static $res = null;

        if (null === $res && $res = Tools::getIsset('preview_id') && $uid = CE\UId::parse(Tools::getValue('preview_id'))) {
            $res = self::hasAdminToken($uid->getAdminController()) ? $uid : false;
        }

        return !$embed || Tools::getIsset('ver') ? $res : false;
    }

    public static function hasAdminToken($tab)
    {
        $key = 'AdminBlogPosts' === $tab ? 'blogtoken' : (stripos($tab, 'AdminCE') === 0 ? 'cetoken' : 'adtoken');
        $adtoken = Tools::getAdminToken($tab . (int) Tab::getIdFromClassName($tab) . (int) Tools::getValue('id_employee'));

        return Tools::getValue($key) == $adtoken;
    }

    public static function getThemeVarName($theme_type)
    {
        if ('product' === $theme_type && Tools::getValue('quickview')) {
            return 'CE_PRODUCT_QUICK_VIEW';
        } elseif ('product' === $theme_type && Tools::getValue('miniature')) {
            return 'CE_PRODUCT_MINIATURE';
        }

        return 'CE_' . strtoupper(str_replace('-', '_', $theme_type));
    }

    public static function isMaintenance()
    {
        return !Configuration::get('PS_SHOP_ENABLE') &&
            !in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('PS_MAINTENANCE_IP')));
    }
}
