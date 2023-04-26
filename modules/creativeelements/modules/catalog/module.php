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

use CE\CoreXBaseXModule as BaseModule;
use CE\CoreXFilesXCSSXPost as PostCSS;
use CE\ModulesXCatalogXControlsXSelectCategory as SelectCategory;
use CE\ModulesXCatalogXControlsXSelectManufacturer as SelectManufacturer;
use CE\ModulesXCatalogXControlsXSelectSupplier as SelectSupplier;
use CE\ModulesXCatalogXDocumentsXProduct as ProductDocument;

class ModulesXCatalogXModule extends BaseModule
{
    private $document;

    public function getName()
    {
        return 'catalog';
    }

    public function init()
    {
        if (\Tools::getValue('refresh') === 'product' &&
            $id_ce_theme = \Configuration::get(\Tools::getValue('quickview') ? 'CE_PRODUCT_QUICK_VIEW' : 'CE_PRODUCT')
        ) {
            $context = \Context::getContext();

            add_action('elementor/widgets/widgets_registered', function ($manager) {
                $manager->unregisterWidgetType('product-description');
                $manager->unregisterWidgetType('product-description-short');
                $manager->unregisterWidgetType('product-quantity');
                $manager->unregisterWidgetType('product-box');
                $manager->unregisterWidgetType('product-grid');
                $manager->unregisterWidgetType('product-carousel');
            });

            \CreativeElements::renderTheme(
                new UId($id_ce_theme, UId::THEME, $context->language->id, $context->shop->id)
            );
        }
    }

    public function registerControls($controls_manager)
    {
        $controls_manager->registerControl(SelectCategory::CONTROL_TYPE, new SelectCategory());
        $controls_manager->registerControl(SelectManufacturer::CONTROL_TYPE, new SelectManufacturer());
        $controls_manager->registerControl(SelectSupplier::CONTROL_TYPE, new SelectSupplier());
    }

    public function getProductWidgets()
    {
        return [
            'product-name',
            'product-badges',
            'product-images',
            'product-image',
            'product-price',
            'product-rating',
            'product-meta',
            'product-description-short',
            'product-variants',
            'product-stock',
            'product-quantity',
            'product-add-to-cart',
            'product-description',
            'product-add-to-wishlist',
            'product-features',
            'product-attachments',
            'product-sale-countdown',
            'manufacturer-image',
            'product-brand-image',
            'product-share',
            'product-block',
        ];
    }

    public function getProductMiniatureWidgets()
    {
        if (!did_action('ce/css-file/global/before_render') && \Tools::getValue('render') !== 'widget') {
            Plugin::$instance->widgets_manager->unregisterWidgetType('breadcrumb');
            Plugin::$instance->widgets_manager->unregisterWidgetType('product-box');
            Plugin::$instance->widgets_manager->unregisterWidgetType('product-grid');
            Plugin::$instance->widgets_manager->unregisterWidgetType('product-carousel');
        }

        add_action('elementor/element/common/_section_transform/after_section_end', function (ControlsStack $element) {
            $element->updateControl(
                '_transform_trigger_hover',
                [
                    'options' => [
                        '' => __('Widget'),
                        'column' => __('Column'),
                        'section' => __('Section'),
                        'miniature' => __('Miniature'),
                    ],
                ]
            );
        });

        return [
            'product-miniature-name',
            'product-badges',
            'product-miniature-image',
            'product-miniature-price',
            'product-miniature-rating',
            'product-sale-countdown',
            'manufacturer-image',
            'product-meta',
            'product-description-short',
            'product-miniature-variants',
            'product-stock',
            'product-miniature-add-to-cart',
            'product-features',
            'product-add-to-wishlist',
            'product-share',
            'product-miniature-box',
        ];
    }

    public function initWidgets($widgets_manager, $force = false)
    {
        if (!$this->shouldRegister() && !$force) {
            return;
        }

        if ('all' === $force) {
            $widgets = array_merge($this->getProductWidgets(), $this->getProductMiniatureWidgets());
        } else {
            $widgets = 'miniature' === $force || $this->document && $this->document->getTemplateType() === 'product-miniature'
                ? $this->getProductMiniatureWidgets()
                : $this->getProductWidgets()
            ;
        }

        foreach ($widgets as &$widget) {
            $class_name = str_replace('-', '', $widget);
            $class_name = __NAMESPACE__ . '\Widget' . $class_name;

            if (class_exists($class_name)) {
                continue;
            }

            include __DIR__ . "/widgets/$widget.php";

            $widgets_manager->registerWidgetType(new $class_name());
        }
    }

    public function registerTags()
    {
        $dynamic_tags = Plugin::$instance->dynamic_tags;
        $dynamic_tags->registerTag('CE\ModulesXCatalogXTagsXProductAddToCart');
        $dynamic_tags->registerTag('CE\ModulesXCatalogXTagsXProductBuyNow');
        $dynamic_tags->registerTag('CE\ModulesXCatalogXTagsXProductQuickView');
        $dynamic_tags->registerTag('CE\ModulesXCatalogXTagsXProductImages');
        $dynamic_tags->registerTag('CE\ModulesXCatalogXTagsXCategoryImages');
        $dynamic_tags->registerTag('CE\ModulesXCatalogXTagsXManufacturerImages');
        $dynamic_tags->registerTag('CE\ModulesXCatalogXTagsXCartRuleDateTime');
        $dynamic_tags->registerTag('CE\ModulesXCatalogXTagsXSpecificPriceRuleDateTime');

        if (!$this->shouldRegister()) {
            return;
        }

        $tags = [
            'ProductName',
            'ProductUrl',
            'ProductImage',
            // 'ProductGallery',
            'ProductPrice',
            'ProductRating',
            'ProductMeta',
            // 'ProductStock',
            // 'ProductTerms',
            'ProductDescriptionShort',
            'ProductAvailabilityDateTime',
            // 'CategoryImage',
            'CategoryName',
            'CategoryUrl',
            'ManufacturerName',
            'ManufacturerUrl',
            'ManufacturerImage',
        ];

        foreach ($tags as $tag) {
            $dynamic_tags->registerTag('CE\ModulesXCatalogXTagsX' . $tag);
        }

        if ($this->document && $this->document->getTemplateType() === 'product-miniature') {
            $dynamic_tags->unregisterTag('shortcode');
        }
    }

    private function shouldRegister()
    {
        static $should_register;

        if (null === $should_register) {
            if (!$uid = \CreativeElements::getPreviewUId(false) ?: get_the_ID()) {
                return $should_register = false;
            }
            $should_register = Helper::isAdminImport() ||
                UId::PRODUCT === $uid->id_type && \Configuration::get('CE_PRODUCT') ||
                UId::CONTENT === $uid->id_type && \Configuration::get('CE_PRODUCT') && \Tools::getValue('footerProduct') ||
                in_array($uid->id_type, [UId::THEME, UId::TEMPLATE]) && (
                    $this->document = Plugin::$instance->documents->get($uid)
                ) instanceof ProductDocument;
        }

        return $should_register;
    }

    public function refreshProduct($content)
    {
        if (UId::$_ID->id_type !== UId::THEME) {
            return $content;
        }

        $context = \Context::getContext();
        $id_product = (int) \Tools::getValue('id_product');
        $groups = \Tools::getValue('group');
        $ipa = $groups ? (int) \Product::getIdProductAttributeByIdAttributes($id_product, $groups, true) : null;
        $product = new \Product($id_product, false, $context->language->id, $context->shop->id);
        $product_url = $context->link->getProductLink($product, null, null, null, null, null, $ipa, false, false, true);
        $args = ${'_GET'};
        unset(
            $args['controller'],
            $args['action'],
            $args['id_product'],
            $args['id_product_attribute'],
            $args['rewrite'],
            $args['isolang'],
            $args['id_lang']
        );
        $getProductMinimalQuantity = new \ReflectionMethod($context->controller, 'getProductMinimalQuantity');
        $getProductMinimalQuantity->setAccessible(true);

        wp_send_json([
            'product_content' => $content,
            'product_url' => !$args ? $product_url : str_replace(
                '#',
                (strrpos('?', $product_url) === false ? '?' : '&') . http_build_query($args) . '#',
                $product_url
            ),
            'product_minimal_quantity' => (int) $getProductMinimalQuantity->invoke($context->controller, [
                'id_product_attribute' => $ipa,
            ]),
            'id_product_attribute' => $ipa,
            'product_title' => $product->name,
            'is_quick_view' => \Tools::getValue('quickview'),
        ]);
    }

    public function handleProductQuickView()
    {
        add_filter('template_include', function () {
            return _CE_TEMPLATES_ . 'front/theme/layouts/layout-canvas.tpl';
        }, 12);

        add_action('wp_footer', function () {
            ?>
            <script>
            // Init Lightbox
            $('html').attr({
                id: 'ce-product-quick-view',
            }).addClass('dialog-widget dialog-lightbox-widget dialog-type-lightbox elementor-lightbox')
            $('body').addClass('dialog-widget-content dialog-lightbox-widget-content');
            $('main').addClass('dialog-message dialog-lightbox-message')
            $('<div class="dialog-close-button dialog-lightbox-close-button"><i class="ceicon-close">')
                .appendTo('.dialog-message');

            // Init Form
            var $form = $('<form>').attr({
                action: prestashop.urls.pages.product,
                method: 'post',
                id: 'add-to-cart-or-refresh',
                style: 'display: none',
            }).prependTo('main');
            $('<input>').attr({
                type: 'hidden',
                name: 'token',
                value: prestashop.static_token,
            }).appendTo($form);
            $('<input>').attr({
                type: 'hidden',
                id: 'product_page_product_id',
                name: 'id_product',
                value: ceFrontend.config.post.id.match(/(\d+)\d{6}/)[1],
            }).appendTo($form);
            $('<input>').attr({
                type: 'hidden',
                id: 'quantity_wanted',
                name: 'qty',
                value: 1,
            }).appendTo($form);
            $('<input>').attr({
                type: 'submit',
                'class': 'ce-add-to-cart',
                'data-button-action': 'add-to-cart',
            }).appendTo($form);

            // Entrance Animation Preview
            if (top.elementor && top.elementor.settings) {
                var previewModalSettings,
                    pageModel = top.elementor.settings.page.model
                    prev = {
                        animation: {
                            desktop: null,
                            tablet: null,
                            mobile: null
                        },
                        duration: null,
                        closeButtonPosition: null
                    };
                pageModel.on('change', previewModalSettings = function () {
                    var device = ceFrontend.getCurrentDeviceMode(),
                        control = device === 'desktop' ? 'entrance_animation' : 'entrance_animation_' + device;
                        animation = pageModel.get(control),
                        duration = pageModel.get('entrance_animation_duration'),
                        closeButtonPosition = pageModel.get('close_button_position');

                    if (animation && (animation !== prev.animation[device] || duration !== prev.duration)) {
                        $('.dialog-message').removeClass([
                            prev.animation.desktop,
                            prev.animation.tablet,
                            prev.animation.mobile,
                            'animated'
                        ].join(' '));

                        setTimeout(function () {
                            $('.dialog-message').addClass(animation + ' animated');
                        });

                        prev.animation[device] = animation;
                        prev.duration = duration;
                    }
                    if (closeButtonPosition !== prev.closeButtonPosition) {
                        $('.dialog-close-button').prependTo(
                            'outside' === closeButtonPosition ? 'body' : '.dialog-message'
                        );
                    }
                });
                top.elementor.on('preview:loaded', previewModalSettings);
            }
            </script>
            <?php
        });
    }

    public function handleProductMiniature()
    {
        add_filter('template_include', function () {
            return _CE_TEMPLATES_ . 'front/theme/layouts/layout-canvas.tpl';
        }, 12);

        add_action('wp_footer', function () {
            ?>
            <style>
            html.elementor-html {
                background: #333;
            }
            body.ui-resizable {
                position: relative;
                background: transparent;
                height: auto;
                min-height: 0;
                max-width: calc(100% - 20px);
            }
            html.elementor-html,
            body > .ui-resizable-handle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            body > .ui-resizable-e {
                right: -7px;
                justify-content: flex-end;
            }
            body > .ui-resizable-w {
                left: -7px;
                justify-content: flex-start;
            }
            body > .ui-resizable-handle:after {
                content: '';
                background: rgba(255, 255, 255, 0.2);
                height: 50px;
                width: 4px;
                border-radius: 3px;
                transition: all .2s ease-in-out;
            }
            body > .ui-resizable-handle:hover:after {
                background-color: rgba(255, 255, 255, 0.6);
                height: 100px;
            }
            body.ui-resizable-resizing > .ui-resizable-handle:after {
                background-color: rgba(255, 255, 255, 0.8);
            }
            body.ui-resizable > main {
                display: flex;
                flex-direction: column;
                position: relative;
                max-height: 100vh;
                overflow: auto;
            }
            .elementor[data-elementor-type="product-miniature"] {
                background: #fff;
            }
            .elementor-editor-column-settings {
                left: 0;
            }
            .elementor-editor-widget-settings {
                right: 0;
            }
            </style>
            <?php
        });
    }

    private static function filterSmartyCallback($match)
    {
        return preg_match(
            '/^{(' .
                '\$\w+(\.\w+|->\w+)*(\s+nofilter)?|' .
                '(hook|widget|include)(\s+\w+\s*=\s*("(\\\\.|[^"])*"|\'(\\\\.|[^\'])*\'|\$\w+(\.\w+)*|\w+))+' .
            ')\s*}$/s',
            $match[0]
        ) ? $match[0] : str_replace('{', '&#123;', $match[0]);
    }

    private static function filterElementsData(array &$data)
    {
        foreach ($data as &$value) {
            if (!$value) {
                continue;
            } elseif (is_array($value)) {
                self::filterElementsData($value);
            } elseif (is_string($value) && strpos($value, '{') !== false) {
                $value = preg_replace_callback('/{\S[^}]*}?/', [__CLASS__, 'filterSmartyCallback'], $value);
            }
        }
    }

    public function afterSaveDocument($document, array $data)
    {
        if (isset($data['settings']['post_status']) && 'autosave' !== $data['settings']['post_status'] &&
            'product-miniature' === $document->getTemplateType() && UID::THEME === get_the_ID()->id_type
        ) {
            $elements_data = $data['elements'];
            self::filterElementsData($elements_data);

            $orig_id = $document->getMainId();
            $uid = UId::parse($orig_id);
            $main_id = &\Closure::bind(function &() {
                return $this->main_id;
            }, $document, 'CE\CoreXBaseXDocument')->__invoke();

            add_action('elementor/element/after_add_attributes', function ($element) {
                $settings = $element->getRenderAttributes('_wrapper', 'data-settings');

                if ($settings && strpos($settings[0], '{') !== false) {
                    $element->setRenderAttribute('_wrapper', 'data-settings', "{literal}$settings[0]{/literal}");
                }
            });
            \Closure::bind(function () {
                WidgetBase::$render_method = 'renderSmarty';
            }, null, 'CE\WidgetBase')->__invoke();
            \Closure::bind(function () {
                CoreXDynamicTagsXTag::$render_method = 'renderSmarty';
            }, null, 'CE\CoreXDynamicTagsXTag')->__invoke();
            \Closure::bind(function () {
                CoreXDynamicTagsXDataTag::$getter_method = 'getSmartyValue';
            }, null, 'CE\CoreXDynamicTagsXDataTag')->__invoke();

            foreach (\Shop::getContextListShopID() as $id_shop) {
                $uid->id_shop = $id_shop;
                $main_id = (string) $uid;

                ob_start();
                $document->printSmartyElementsWithWrapper($elements_data);
                file_put_contents(
                    _CE_TEMPLATES_ . "front/theme/catalog/_partials/miniatures/product-$main_id.tpl",
                    ob_get_clean()
                );
                $post_css = new PostCSS($uid);
                $post_css->update();
            }
            $main_id = $orig_id;
        }

        return $data;
    }

    public function __construct()
    {
        is_admin() or add_action('template_redirect', [$this, 'init'], 1);

        add_action('elementor/controls/controls_registered', [$this, 'registerControls']);
        add_action('elementor/widgets/widgets_registered', [$this, 'initWidgets']);
        add_action('elementor/dynamic_tags/register_tags', [$this, 'registerTags']);
        add_action('ce/css-file/global/before_render', function () {
            $widgets_manager = Plugin::$instance->widgets_manager;
            // init widgets if needed
            $widgets_manager->getWidgetTypes();

            $this->initWidgets($widgets_manager, 'all');
        });

        if (\Tools::getValue('refresh') === 'product' &&
            \Context::getContext()->controller instanceof \ProductController
        ) {
            add_filter('the_content', [$this, 'refreshProduct'], 999999);
        }

        if (wp_doing_ajax()) {
            add_action('elementor/document/after_save', [$this, 'afterSaveDocument']);
        }
    }
}
