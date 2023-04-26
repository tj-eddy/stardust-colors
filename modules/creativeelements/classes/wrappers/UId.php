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

/**
 * Unique Identifier
 */
class UId
{
    const REVISION = 0;
    const TEMPLATE = 1;
    const CONTENT = 2;
    const PRODUCT = 3;
    const CATEGORY = 4;
    const MANUFACTURER = 5;
    const SUPPLIER = 6;
    const CMS = 7;
    const CMS_CATEGORY = 8;
    const YBC_BLOG_POST = 9;
    const XIPBLOG_POST = 10;
    const STBLOG_POST = 11;
    const ADVANCEBLOG_POST = 12;
    const PRESTABLOG_POST = 13;
    /** @deprecated */
    const SIMPLEBLOG_POST = 14;
    const PSBLOG_POST = 15;
    const HIBLOG_POST = 16;
    const THEME = 17;
    const TVCMSBLOG_POST = 18;

    public $id;
    public $id_type;
    public $id_lang;
    public $id_shop;

    private static $models = [
        'CERevision',
        'CETemplate',
        'CEContent',
        'Product',
        'Category',
        'Manufacturer',
        'Supplier',
        'CMS',
        'CMSCategory',
        'Ybc_blog_post_class',
        'XipPostsClass',
        'StBlogClass',
        'BlogPosts',
        'NewsClass',
        'SimpleBlogPost',
        'PsBlogBlog',
        'HiBlogPost',
        'CETheme',
        'TvcmsPostsClass',
    ];
    private static $admins = [
        'AdminCEEditor',
        'AdminCETemplates',
        'AdminCEContent',
        'AdminProducts',
        'AdminCategories',
        'AdminManufacturers',
        'AdminSuppliers',
        'AdminCmsContent',
        'AdminCmsContent',
        'AdminModules',
        'AdminXipPost',
        'AdminStBlog',
        'AdminBlogPosts',
        'AdminModules',
        'AdminSimpleBlogPosts',
        'AdminPsblogBlogs',
        'AdminModules',
        'AdminCEThemes',
        'AdminTvcmsPost',
    ];
    private static $modules = [
        self::YBC_BLOG_POST => 'ybc_blog',
        self::XIPBLOG_POST => 'xipblog',
        self::STBLOG_POST => 'stblog',
        self::ADVANCEBLOG_POST => 'advanceblog',
        self::PRESTABLOG_POST => 'prestablog',
        self::SIMPLEBLOG_POST => 'ph_simpleblog',
        self::PSBLOG_POST => 'psblog',
        self::HIBLOG_POST => 'hiblog',
        self::TVCMSBLOG_POST => 'tvcmsblog',
    ];
    private static $shop_ids = [];

    public static $_ID;

    public function __construct($id, $id_type, $id_lang = null, $id_shop = null)
    {
        $this->id = abs((int) $id);
        $this->id_type = abs($id_type % 100);

        if ($this->id_type <= self::TEMPLATE) {
            $this->id_lang = 0;
            $this->id_shop = 0;
        } else {
            is_null($id_lang) && $id_lang = \Context::getContext()->language->id;

            $this->id_lang = abs($id_lang % 100);
            $this->id_shop = $id_shop ? abs($id_shop % 100) : 0;
        }
    }

    public function getModel()
    {
        if (empty(self::$models[$this->id_type])) {
            throw new \RuntimeException('Unknown ObjectModel');
        }

        return self::$models[$this->id_type];
    }

    public function getAdminController()
    {
        if (empty(self::$admins[$this->id_type])) {
            throw new \RuntimeException('Unknown AdminController');
        }
        if ((int) \Tools::getValue('footerProduct')) {
            return self::$admins[self::PRODUCT];
        }

        return self::$admins[$this->id_type];
    }

    public function getModule()
    {
        return isset(self::$modules[$this->id_type]) ? self::$modules[$this->id_type] : '';
    }

    /**
     * Get shop ID list where the object is allowed
     *
     * @param bool $all Get all or just by shop context
     *
     * @return array
     */
    public function getShopIdList($all = false)
    {
        if ($this->id_type <= self::TEMPLATE) {
            return [0];
        }
        if (isset(self::$shop_ids[$this->id_type][$this->id])) {
            return self::$shop_ids[$this->id_type][$this->id];
        }
        isset(self::$shop_ids[$this->id_type]) or self::$shop_ids[$this->id_type] = [];

        $ids = [];
        $model = $this->getModel();
        $def = &$model::${'definition'};
        $db = \Db::getInstance();
        $table = $db->escape(_DB_PREFIX_ . $def['table'] . '_shop');
        $primary = $db->escape($def['primary']);
        $id = (int) $this->id;
        $ctx_ids = implode(', ', $all ? \Shop::getShops(true, null, true) : \Shop::getContextListShopID());
        $rows = $db->executeS(
            "SELECT id_shop FROM $table WHERE $primary = $id AND id_shop IN ($ctx_ids)"
        );
        if ($rows) {
            foreach ($rows as &$row) {
                $ids[] = $row['id_shop'];
            }
        }

        return self::$shop_ids[$this->id_type][$this->id] = $ids;
    }

    public function getDefaultShopId()
    {
        return ($ids = $this->getShopIdList()) ? $ids[0] : 0;
    }

    /**
     * Get UId list by shop context
     *
     * @param bool $strict Collect only from allowed shops
     *
     * @return array
     */
    public function getListByShopContext($strict = false)
    {
        if ($this->id_shop || $this->id_type <= self::TEMPLATE) {
            return ["$this"];
        }
        $list = [];
        $ids = $strict ? $this->getShopIdList() : \Shop::getContextListShopID();

        foreach ($ids as $id_shop) {
            $this->id_shop = $id_shop;
            $list[] = "$this";
        }
        $this->id_shop = 0;

        return $list;
    }

    /**
     * Get Language ID list of CE built contents
     *
     * @return array
     */
    public function getBuiltLangIdList()
    {
        $ids = [];

        if (self::TEMPLATE === $this->id_type) {
            $ids[] = 0;
        } elseif (self::CONTENT === $this->id_type || self::THEME === $this->id_type) {
            foreach (\Language::getLanguages(false) as $lang) {
                $ids[] = (int) $lang['id_lang'];
            }
        } else {
            $id_shop = $this->id_shop ?: $this->getDefaultShopId();
            $uids = self::getBuiltList($this->id, $this->id_type, $id_shop);

            empty($uids[$id_shop]) or $ids = array_keys($uids[$id_shop]);
        }

        return $ids;
    }

    public function toDefault()
    {
        $id_shop = $this->id_shop ?: $this->getDefaultShopId();

        return sprintf('%d%02d%02d%02d', $this->id, $this->id_type, $this->id_lang, $id_shop);
    }

    public function __toString()
    {
        return sprintf('%d%02d%02d%02d', $this->id, $this->id_type, $this->id_lang, $this->id_shop);
    }

    public static function parse($id)
    {
        if ($id instanceof UId) {
            return $id;
        }
        if (!is_numeric($id) || strlen($id) <= 6) {
            return false;
        }

        return new self(
            substr($id, 0, -6),
            substr($id, -6, 2),
            substr($id, -4, 2),
            substr($id, -2)
        );
    }

    public static function getTypeId($model)
    {
        $model = strtolower($model);

        return 'cms_category' === $model
            ? self::CMS_CATEGORY
            : array_search($model, array_map('strtolower', self::$models))
        ;
    }

    /**
     * Get UId list of CE built contents grouped by shop(s)
     *
     * @param int $id
     * @param int $id_type
     * @param int|null $id_shop
     *
     * @return array
     */
    public static function getBuiltList($id, $id_type, $id_shop = null)
    {
        $uids = [];
        $table = _DB_PREFIX_ . 'ce_meta';
        $shop = null === $id_shop ? '__' : '%02d';
        $__id = sprintf("%d%02d__$shop", $id, $id_type, $id_shop);
        $rows = \Db::getInstance()->executeS(
            "SELECT id FROM $table WHERE id LIKE '$__id' AND name = '_elementor_edit_mode'"
        );
        if ($rows) {
            foreach ($rows as &$row) {
                $uid = self::parse($row['id']);
                isset($uids[$uid->id_shop]) or $uids[$uid->id_shop] = [];
                $uids[$uid->id_shop][$uid->id_lang] = $uid;
            }
        }

        return $uids;
    }
}

function absint($num)
{
    if ($num instanceof UId) {
        return $num;
    }
    $absint = preg_replace('/\D+/', '', $num);

    return $absint ?: 0;
}

function get_user_meta($user_id, $key = '', $single = false)
{
    return get_post_meta($user_id, '_u_' . $key, $single);
}

function update_user_meta($user_id, $key, $value, $prev_value = '')
{
    return update_post_meta($user_id, '_u_' . $key, $value, $prev_value);
}

function get_the_ID()
{
    if (!UId::$_ID && $uid_preview = \CreativeElements::getPreviewUId(false)) {
        UId::$_ID = $uid_preview;
    }
    if (UId::$_ID) {
        return UId::$_ID;
    }
    $controller = \Context::getContext()->controller;

    if ($controller instanceof \AdminCEEditorController ||
        $controller instanceof \CreativeElementsPreviewModuleFrontController
    ) {
        $id_key = \Tools::getIsset('editor_post_id') ? 'editor_post_id' : 'template_id';

        return UId::parse(\Tools::getValue('uid', \Tools::getValue($id_key)));
    }

    return false;
}

function get_preview_post_link($post = null, array $args = [], $relative = true)
{
    $uid = uidval($post);
    $ctx = \Context::getContext();
    $admin = $uid->getAdminController();
    $id_shop = $uid->id_shop ?: $uid->getDefaultShopId();
    $args['id_employee'] = $ctx->employee->id;
    $args[strpos($admin, 'AdminCE') === 0 ? 'cetoken' : 'adtoken'] = \Tools::getAdminTokenLite($admin);
    $args['preview_id'] = $uid->toDefault();

    switch ($uid->id_type) {
        case UId::REVISION:
            throw new \RuntimeException('TODO');
        case UId::TEMPLATE:
            $type = \CETemplate::getTypeById($uid->id);
            $id_shop = $ctx->shop->id;
            // no break
        case UId::THEME:
            isset($type) or $type = \CETheme::getTypeById($uid->id);
            $id_lang = $uid->id_lang ?: $ctx->language->id;

            if ('product' === $type || 'product-quick-view' === $type || 'product-miniature' === $type) {
                $document = Plugin::$instance->documents->getDocOrAutoSave($uid, get_current_user_id());
                $settings = $document->getData('settings');

                empty($settings['preview_id']) or $prod = new \Product($settings['preview_id'], false, $id_lang);

                if (empty($prod->id)) {
                    $prods = \Product::getProducts($id_lang, 0, 1, 'date_upd', 'DESC', false, true);
                    $prod = new \Product(!empty($prods[0]['id_product']) ? $prods[0]['id_product'] : null, false, $id_lang);
                }
                $prod_attr = empty($prod->cache_default_attribute) ? 0 : $prod->cache_default_attribute;
                empty($prod->active) && ($args['preview'] = 1) && $args['adtoken'] = \Tools::getAdminTokenLite('AdminProducts');

                $link = $ctx->link->getProductLink($prod, null, null, null, $id_lang, $id_shop, $prod_attr, false, $relative);
            } elseif ('page-contact' === $type) {
                $link = $ctx->link->getPageLink('contact', null, $id_lang, null, false, $id_shop, $relative);
            } elseif ('page-not-found' === $type) {
                $link = $ctx->link->getPageLink('pagenotfound', null, $id_lang, null, false, $id_shop, $relative);
            } elseif ('page-index' === $type || 'header' === $type || 'footer' === $type) {
                $link = $ctx->link->getPageLink('index', null, $id_lang, null, false, $id_shop, $relative);

                \Configuration::get('PS_REWRITING_SETTINGS') && $link = preg_replace('~[^/]+$~', '', $link);
            } else {
                $link = $ctx->link->getModuleLink('creativeelements', 'preview', [], null, null, null, $relative);
            }
            break;
        case UId::CONTENT:
            $hook = strtolower(\CEContent::getHookById($uid->id));

            if (in_array($hook, Helper::$productHooks)) {
                if ($id_product = (int) \Tools::getValue('footerProduct')) {
                    $args['footerProduct'] = $id_product;
                    $prod = new \Product($id_product, false, $uid->id_lang, $id_shop);
                } else {
                    $prods = \Product::getProducts($uid->id_lang, 0, 1, 'date_upd', 'DESC', false, true);
                    $prod = new \Product(!empty($prods[0]['id_product']) ? $prods[0]['id_product'] : null, false, $uid->id_lang);
                }
                $prod_attr = empty($prod->cache_default_attribute) ? 0 : $prod->cache_default_attribute;
                empty($prod->active) && ($args['preview'] = 1) && $args['adtoken'] = \Tools::getAdminTokenLite('AdminProducts');

                $link = $ctx->link->getProductLink($prod, null, null, null, $uid->id_lang, $id_shop, $prod_attr, false, $relative);
                break;
            }
            $page = 'index';

            if (strpos($hook, 'shoppingcart') !== false) {
                $page = 'cart';
                $args['action'] = 'show';
            } elseif ('displayleftcolumn' === $hook || 'displayrightcolumn' === $hook) {
                $layout = 'r' !== $hook[7] ? 'layout-left-column' : 'layout-right-column';
                $layouts = $ctx->shop->theme->get('theme_settings')['layouts'];
                unset($layouts['category']);

                if ($key = array_search($layout, $layouts)) {
                    $page = $key;
                } elseif ($key = array_search('layout-both-columns', $layouts)) {
                    $page = $key;
                }
            } elseif ('displaynotfound' === $hook) {
                $page = 'search';
            } elseif ('displaymaintenance' === $hook) {
                $args['maintenance'] = 1;
            }
            $link = $ctx->link->getPageLink($page, null, $uid->id_lang, null, false, $id_shop, $relative);

            if ('index' === $page && \Configuration::get('PS_REWRITING_SETTINGS')) {
                // Remove rewritten URL if exists
                $link = preg_replace('~[^/]+$~', '', $link);
            }
            break;
        case UId::PRODUCT:
            $prod = new \Product($uid->id, false, $uid->id_lang, $id_shop);
            $prod_attr = !empty($prod->cache_default_attribute) ? $prod->cache_default_attribute : 0;
            empty($prod->active) && $args['preview'] = 1;

            $link = $ctx->link->getProductLink($prod, null, null, null, $uid->id_lang, $id_shop, $prod_attr, false, $relative);
            break;
        case UId::CATEGORY:
            $link = $ctx->link->getCategoryLink($uid->id, null, $uid->id_lang, null, $id_shop, $relative);
            break;
        case UId::CMS:
            $link = $ctx->link->getCmsLink($uid->id, null, null, $uid->id_lang, $id_shop, $relative);
            break;
        case UId::YBC_BLOG_POST:
            $link = \Module::getInstanceByName('ybc_blog')->getLink('blog', ['id_post' => $uid->id], $uid->id_lang);
            break;
        case UID::XIPBLOG_POST:
            $link = call_user_func('XipBlog::xipBlogPostLink', ['id' => $uid->id]);
            break;
        case UId::STBLOG_POST:
            $post = new \StBlogClass($uid->id, $uid->id_lang);

            $link = $ctx->link->getModuleLink('stblog', 'article', [
                'id_st_blog' => $uid->id,
                'id_blog' => $uid->id,
                'rewrite' => $post->link_rewrite,
            ], null, $uid->id_lang, null, $relative);
            break;
        case UId::ADVANCEBLOG_POST:
            $post = new \BlogPosts($uid->id, $uid->id_lang);
            $args['blogtoken'] = $args['adtoken'];
            unset($args['adtoken']);

            $link = $ctx->link->getModuleLink('advanceblog', 'detail', [
                'id' => $uid->id,
                'post' => $post->link_rewrite,
            ], null, $uid->id_lang, null, $relative);
            break;
        case UId::PRESTABLOG_POST:
            $post = new \NewsClass($uid->id, $uid->id_lang);
            empty($post->actif) && $args['preview'] = \Module::getInstanceByName('prestablog')->generateToken($uid->id);

            $link = call_user_func('PrestaBlog::prestablogUrl', [
                'id' => $uid->id,
                'seo' => $post->link_rewrite,
                'titre' => $post->title,
                'id_lang' => $uid->id_lang,
            ]);
            break;
        case UId::SIMPLEBLOG_POST:
            $post = new \SimpleBlogPost($uid->id, $uid->id_lang, $uid->id_shop);
            $cat = new \SimpleBlogCategory($post->id_simpleblog_category, $uid->id_lang, $uid->id_shop);

            $link = call_user_func('SimpleBlogPost::getLink', $post->link_rewrite, $cat->link_rewrite);
            break;
        case UId::PSBLOG_POST:
            $post = new \PsBlogBlog($uid->id, $uid->id_lang, $uid->id_shop);

            $link = call_user_func('PsBlogHelper::getInstance')->getBlogLink([
                'id_psblog_blog' => $post->id,
                'link_rewrite' => $post->link_rewrite,
            ]);
            break;
        case UId::HIBLOG_POST:
            $post = new \HiBlogPost($uid->id, $uid->id_lang, $uid->id_shop);

            $link = \Module::getInstanceByName('hiblog')->getPostURL($post->friendly_url);
            break;
        case UID::TVCMSBLOG_POST:
            $link = call_user_func('TvcmsBlog::tvcmsBlogPostLink', [
                'id' => $uid->id,
                'rewrite' => call_user_func('TvcmsPostsClass::getTheRewrite', $uid->id),
            ]);
            break;
        default:
            $method = "get{$uid->getModel()}Link";

            $link = $ctx->link->$method($uid->id, null, $uid->id_lang, $id_shop, $relative);
            break;
    }

    return explode('#', $link)[0] . (strrpos($link, '?') === false ? '?' : '&') . http_build_query($args);
}

function uidval($var, $fallback = -1)
{
    if (null === $var) {
        return get_the_ID();
    }
    if ($var instanceof UId) {
        return $var;
    }
    if ($var instanceof WPPost) {
        return $var->uid;
    }
    if (is_numeric($var)) {
        return UId::parse($var);
    }
    if ($fallback !== -1) {
        return $fallback;
    }
    throw new \RuntimeException('Can not convert to UId');
}

function get_edit_post_link($post_id)
{
    $uid = uidval($post_id);
    $ctx = \Context::getContext();
    $id = $uid->id;
    $model = $uid->getModel();
    $admin = $uid->getAdminController();

    switch ($uid->id_type) {
        case UId::REVISION:
            throw new \RuntimeException('TODO');
        case UId::YBC_BLOG_POST:
            $link = $ctx->link->getAdminLink($admin) . '&' . http_build_query([
                'configure' => 'ybc_blog',
                'tab_module' => 'front_office_features',
                'module_name' => 'ybc_blog',
                'control' => 'post',
                'id_post' => $id,
            ]);
            break;
        case UId::PRESTABLOG_POST:
            $link = $ctx->link->getAdminLink($admin) . "&configure=prestablog&editNews&idN=$id";
            break;
        case UId::CONTENT:
            if (\Tools::getIsset('footerProduct')) {
                $id = (int) \Tools::getValue('footerProduct');
                $model = 'Product';
                $admin = 'AdminProducts';
            }
            // no break;
        default:
            $def = &$model::${'definition'};
            $args = [
                $def['primary'] => $id,
                "update{$def['table']}" => 1,
            ];
            $link = $ctx->link->getAdminLink($admin, true, $args) . '&' . http_build_query($args);
            break;
    }

    return $link;
}
