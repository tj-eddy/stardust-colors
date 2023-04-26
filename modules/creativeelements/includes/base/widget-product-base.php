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

use CE\ModulesXCatalogXControlsXSelectCategory as SelectCategory;
use CE\ModulesXCatalogXControlsXSelectManufacturer as SelectManufacturer;
use CE\ModulesXCatalogXControlsXSelectSupplier as SelectSupplier;

abstract class WidgetProductBase extends WidgetBase
{
    const REMOTE_RENDER = true;

    const FLAGS = [
        'sale' => 'discount',
        'new' => 'new',
        'pack' => 'pack',
        'out' => 'out_of_stock',
        'online' => 'online-only',
    ];

    protected $context;

    protected $catalog;

    protected $show_prices;

    protected $imageSize;

    protected $currency;

    protected $usetax;

    public function __construct($data = [], $args = [])
    {
        $this->context = \Context::getContext();
        $this->catalog = \Configuration::get('PS_CATALOG_MODE');
        $this->show_prices = !\Configuration::isCatalogMode();
        $this->imageSize = \ImageType::getFormattedName('home');
        $this->loading = strrpos($this->getName(), 'carousel') === false ? 'lazy' : 'auto';

        if (is_admin()) {
            isset($this->context->customer->id) or $this->context->customer = new \Customer();
        }
        parent::__construct($data, $args);
    }

    public function getCategories()
    {
        return ['premium'];
    }

    public function getSkinTemplate($skin)
    {
        if (preg_match('/^product-(\d+)\d{6}$/', $skin, $m)) {
            $uid = new UId($m[1], UId::THEME, $this->context->language->id, $this->context->shop->id);
            $path = "catalog/_partials/miniatures/product-$uid.tpl";

            if (file_exists(_CE_TEMPLATES_ . "front/theme/$path")) {
                return $path;
            }
        }
        $path = "catalog/_partials/miniatures/$skin.tpl";

        return (
            file_exists(_CE_TEMPLATES_ . "front/theme/$path") ||
            file_exists(_PS_THEME_DIR_ . "templates/$path") ||
            _PARENT_THEME_NAME_ && file_exists(_PS_PARENT_THEME_DIR_ . "templates/$path")
        ) ? $path : '';
    }

    protected function getSkinOptions()
    {
        static $opts;

        if (is_admin() && null === $opts) {
            $_uid = sprintf('%02d%02d%02d', UId::THEME, $this->context->language->id, $this->context->shop->id);
            $themes = \CETheme::getOptions('product-miniature', $this->context->language->id, $this->context->shop->id);
            $skins = [
                'product' => __('Default'),
            ];
            foreach ($themes as $theme) {
                $skins["product-{$theme['value']}$_uid"] = $theme['name'];
            }
            $pattern = 'templates/catalog/_partials/miniatures/*product*.tpl';
            $tpls = array_merge(
                _PARENT_THEME_NAME_ ? glob(_PS_PARENT_THEME_DIR_ . $pattern) : [],
                glob(_PS_THEME_DIR_ . $pattern),
                glob(_CE_TEMPLATES_ . "front/theme/catalog/_partials/miniatures/product-*$_uid.tpl")
            );
            $opts = [];

            foreach ($tpls as $tpl) {
                $opt = basename($tpl, '.tpl');
                $opts[$opt] = isset($skins[$opt]) ? $skins[$opt] : \Tools::ucFirst($opt);
            }
            $opts['custom'] = __('Custom');
            unset($opts['pack-product']);
        }

        return $opts ?: [];
    }

    protected function getListingOptions()
    {
        $opts = [
            'category' => __('Featured Products'),
            'prices-drop' => __('Prices Drop'),
            'new-products' => __('New Products'),
            'best-sales' => __('Best Sales'),
            'related' => __('Related Products'),
            'viewed' => __('Recently Viewed'),
            'manufacturer' => __('Products by Brand'),
            'supplier' => __('Products by Supplier'),
            'products' => __('Custom Products'),
        ];
        if ($this->catalog) {
            unset($opts['best-sales']);
        }

        return $opts;
    }

    protected function registerListingControls($limit = 'limit')
    {
        $this->addControl(
            'heading',
            [
                'label' => __('Heading'),
                'type' => ControlsManager::TEXT,
                'label_block' => true,
                'placeholder' => __('Enter your title'),
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'heading_size',
            [
                'label' => __('HTML Tag'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'h2',
            ]
        );

        $this->addControl(
            'listing',
            [
                'label' => __('Listing'),
                'type' => ControlsManager::SELECT,
                'default' => 'category',
                'options' => $this->getListingOptions(),
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'products',
            [
                'type' => ControlsManager::REPEATER,
                'item_actions' => [
                    'add' => [
                        'product' => Helper::getAjaxProductsListLink(),
                        'placeholder' => __('Add Product'),
                    ],
                    'duplicate' => false,
                ],
                'prevent_empty' => false,
                'fields' => [
                    [
                        'name' => 'id',
                        'type' => ControlsManager::HIDDEN,
                        'default' => '',
                    ],
                ],
                'title_field' => '<# var prodImg = elementor.getProductImage( id ), prodName = elementor.getProductName( id ); #>' .
                    '<# if ( prodImg ) { #><img src="{{ prodImg }}" class="ce-repeater-thumb"><# } #>' .
                    '<# if ( prodName ) { #><span title="{{ prodName }}">{{{ prodName }}}</span><# } #>',
                'condition' => [
                    'listing' => 'products',
                ],
            ]
        );

        $this->addControl(
            'related_product_id',
            [
                'label' => __('Product'),
                'type' => ControlsManager::SELECT2,
                'label_block' => true,
                'select2options' => [
                    'placeholder' => __('Current Product'),
                    'ajax' => [
                        'get' => 'Products',
                        'url' => Helper::getAjaxProductsListLink(),
                    ],
                ],
                'condition' => [
                    'listing' => 'related',
                ],
            ]
        );

        $this->addControl(
            'category_id',
            [
                'label' => __('Category'),
                'label_block' => true,
                'type' => SelectCategory::CONTROL_TYPE,
                'select2options' => [
                    'allowClear' => false,
                ],
                'extend' => [
                    '0' => __('Current Category') . ' / ' . __('Default'),
                ],
                'default' => 0,
                'condition' => [
                    'listing' => 'category',
                ],
            ]
        );

        $this->addControl(
            'manufacturer_id',
            [
                'label' => __('Brand'),
                'label_block' => true,
                'type' => SelectManufacturer::CONTROL_TYPE,
                'select2options' => [
                    'allowClear' => false,
                ],
                'extend' => [
                    '0' => __('Products with the same brand'),
                ],
                'default' => 0,
                'condition' => [
                    'listing' => 'manufacturer',
                ],
            ]
        );

        $this->addControl(
            'supplier_id',
            [
                'label' => __('Supplier'),
                'label_block' => true,
                'type' => SelectSupplier::CONTROL_TYPE,
                'select2options' => [
                    'allowClear' => false,
                ],
                'extend' => [
                    '0' => __('Products with the same supplier'),
                ],
                'default' => 0,
                'condition' => [
                    'listing' => 'supplier',
                ],
            ]
        );

        $this->addControl(
            'order_by',
            [
                'label' => __('Order By'),
                'type' => ControlsManager::SELECT,
                'default' => 'position',
                'options' => [
                    'name' => __('Name'),
                    'price' => __('Price'),
                    'position' => __('Popularity'),
                    'quantity' => __('Sales Volume'),
                    'date_add' => __('Arrival'),
                    'date_upd' => __('Update'),
                ],
                'condition' => [
                    'listing!' => ['related', 'viewed', 'products'],
                ],
            ]
        );

        $this->addControl(
            'order_dir',
            [
                'label' => __('Order Direction'),
                'type' => ControlsManager::SELECT,
                'default' => 'asc',
                'options' => [
                    'asc' => __('Ascending'),
                    'desc' => __('Descending'),
                ],
                'condition' => [
                    'listing!' => ['related', 'viewed', 'products'],
                ],
            ]
        );

        $this->addControl(
            'randomize',
            [
                'label' => __('Randomize'),
                'type' => ControlsManager::SWITCHER,
                'condition' => [
                    'listing' => ['category', 'viewed', 'products', 'related'],
                ],
            ]
        );

        $this->addControl(
            $limit,
            [
                'label' => __('Product Limit'),
                'type' => ControlsManager::NUMBER,
                'min' => 1,
                'default' => 8,
                'condition' => [
                    'listing!' => 'products',
                ],
            ]
        );
    }

    protected function registerMiniatureSections()
    {
        $this->startControlsSection(
            'section_content',
            [
                'label' => __('Content'),
                'condition' => [
                    'skin' => 'custom',
                ],
            ]
        );

        $image_size_options = GroupControlImageSize::getAllImageSizes('products');

        if (empty($image_size_options[$this->imageSize])) {
            // Set first image size as default when home doesn't exists
            $this->imageSize = key($image_size_options);
        }

        $this->addResponsiveControl(
            'image_size',
            [
                'label' => __('Image Size'),
                'type' => ControlsManager::SELECT,
                'options' => &$image_size_options,
                'default' => $this->imageSize,
            ]
        );

        $this->addControl(
            'show_second_image',
            [
                'label' => __('Second Image'),
                'description' => __('Show second image on hover if exists'),
                'type' => ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
            ]
        );

        $this->addControl(
            'show_category',
            [
                'label' => __('Category'),
                'type' => ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
            ]
        );

        $this->addControl(
            'show_description',
            [
                'label' => __('Description'),
                'type' => ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
            ]
        );

        $this->addControl(
            'description_length',
            [
                'label' => __('Max. Length'),
                'type' => ControlsManager::NUMBER,
                'min' => 1,
                'condition' => [
                    'show_description!' => '',
                ],
            ]
        );

        $this->addControl(
            'show_regular_price',
            [
                'label' => __('Regular Price'),
                'type' => $this->catalog ? ControlsManager::HIDDEN : ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
                'default' => 'yes',
            ]
        );

        $this->addControl(
            'show_atc',
            [
                'label' => __('Add to Cart'),
                'type' => $this->catalog ? ControlsManager::HIDDEN : ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
                'default' => 'yes',
            ]
        );

        $this->addControl(
            'show_qv',
            [
                'label' => __('Quick View'),
                'type' => ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
                'default' => 'yes',
            ]
        );

        $this->addControl(
            'show_badges',
            [
                'label' => __('Badges'),
                'type' => ControlsManager::SELECT2,
                'options' => [
                    'sale' => __('Sale'),
                    'new' => __('New'),
                    'pack' => __('Pack'),
                    'out' => __('Out-of-Stock'),
                    'online' => __('Online only'),
                ],
                'default' => ['sale', 'new', 'pack', 'out', 'online'],
                'label_block' => true,
                'multiple' => true,
            ]
        );

        $this->addControl(
            'heading_label',
            [
                'label' => __('Label'),
                'type' => ControlsManager::HEADING,
                'condition' => [
                    'show_badges!' => [],
                ],
            ]
        );

        $this->addControl(
            'badge_sale_text',
            [
                'label' => __('Sale'),
                'type' => ControlsManager::TEXT,
                'placeholder' => __('Default'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_badges',
                            'operator' => 'contains',
                            'value' => 'sale',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'badge_new_text',
            [
                'label' => __('New'),
                'type' => ControlsManager::TEXT,
                'placeholder' => __('Default'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_badges',
                            'operator' => 'contains',
                            'value' => 'new',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'badge_pack_text',
            [
                'label' => __('Pack'),
                'type' => ControlsManager::TEXT,
                'placeholder' => __('Default'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_badges',
                            'operator' => 'contains',
                            'value' => 'pack',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'badge_out_text',
            [
                'label' => __('Out-of-Stock'),
                'type' => ControlsManager::TEXT,
                'placeholder' => __('Default'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_badges',
                            'operator' => 'contains',
                            'value' => 'out',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'badge_online_text',
            [
                'label' => __('Online only'),
                'type' => ControlsManager::TEXT,
                'placeholder' => __('Default'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_badges',
                            'operator' => 'contains',
                            'value' => 'online',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'title_display',
            [
                'label' => __('Title Display'),
                'type' => ControlsManager::CHOOSE,
                'options' => WidgetHeading::getDisplaySizes(),
                'style_transfer' => true,
            ]
        );

        $this->addControl(
            'title_tag',
            [
                'label' => __('Title HTML Tag'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'separator' => 'before',
                'default' => 'h3',
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_atc',
            [
                'label' => __('Add to Cart'),
                'condition' => [
                    'skin' => 'custom',
                    'show_atc' => $this->catalog ? 'hide' : 'yes',
                ],
            ]
        );

        $this->addControl(
            'atc_type',
            [
                'label' => __('Type'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    '' => __('Default'),
                    'primary' => __('Primary'),
                    'secondary' => __('Secondary'),
                ],
                'default' => 'primary',
                'style_transfer' => true,
            ]
        );

        $this->addControl(
            'atc_text',
            [
                'label' => __('Text'),
                'type' => ControlsManager::TEXT,
                'default' => __('Add to Cart'),
            ]
        );

        $this->addControl(
            'atc_align',
            [
                'label' => __('Alignment'),
                'label_block' => false,
                'type' => ControlsManager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'prefix_class' => 'elementor-atc--align-',
                'default' => 'justify',
            ]
        );

        $this->addControl(
            'atc_size',
            [
                'label' => __('Size'),
                'type' => ControlsManager::CHOOSE,
                'toggle' => false,
                'options' => WidgetButton::getButtonSizes(),
                'default' => 'xs',
                'style_transfer' => true,
            ]
        );

        $this->addControl(
            'selected_atc_icon',
            [
                'label' => __('Icon'),
                'type' => ControlsManager::ICONS,
                'fa4compatibility' => 'atc_icon',
                'recommended' => [
                    'ce-icons' => [
                        'cart-light',
                        'cart-medium',
                        'cart-solid',
                        'trolley-light',
                        'trolley-medium',
                        'trolley-solid',
                        'trolley-bold',
                        'basket-light',
                        'basket-medium',
                        'basket-solid',
                        'bag-light',
                        'bag-medium',
                        'bag-solid',
                        'bag-rounded-o',
                        'bag-rounded',
                        'bag-trapeze-o',
                        'bag-trapeze',
                    ],
                    'fa-solid' => [
                        'bag-shopping',
                        'basket-shopping',
                        'cart-shopping',
                        'cart-plus',
                    ],
                ],
            ]
        );

        $this->addControl(
            'atc_icon_align',
            [
                'label' => __('Icon Position'),
                'type' => ControlsManager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => __('Before'),
                    'right' => __('After'),
                ],
                'condition' => [
                    'selected_atc_icon[value]!' => '',
                ],
            ]
        );

        $this->addControl(
            'atc_icon_indent',
            [
                'label' => __('Icon Spacing'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'condition' => [
                    'selected_atc_icon[value]!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc .elementor-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .elementor-atc .elementor-button-text' => 'flex-grow: min(0, {{SIZE}})',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_qv',
            [
                'label' => __('Quick View'),
                'condition' => [
                    'skin' => 'custom',
                    'show_qv!' => '',
                ],
            ]
        );

        $this->addControl(
            'qv_text',
            [
                'label' => __('Text'),
                'type' => ControlsManager::TEXT,
                'default' => __('Quick View'),
            ]
        );

        $this->addControl(
            'selected_qv_icon',
            [
                'label' => __('Icon'),
                'type' => ControlsManager::ICONS,
                'fa4compatibility' => 'qv_icon',
                'recommended' => [
                    'ce-icons' => [
                        'search-light',
                        'search-medium',
                        'search-glint',
                        'search-minimal',
                        'loupe',
                        'magnifier',
                    ],
                    'fa-solid' => [
                        'magnifying-glass',
                        'magnifying-glass-plus',
                        'expand',
                        'up-right-and-down-left-from-center',
                        'maximize',
                        'eye',
                    ],
                    'fa-regular' => [
                        'eye',
                    ],
                ],
            ]
        );

        $this->addControl(
            'qv_icon_align',
            [
                'label' => __('Icon Position'),
                'type' => ControlsManager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => __('Before'),
                    'right' => __('After'),
                ],
                'condition' => [
                    'selected_qv_icon[value]!' => '',
                ],
            ]
        );

        $this->addControl(
            'qv_icon_indent',
            [
                'label' => __('Icon Spacing'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'condition' => [
                    'selected_qv_icon[value]!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-quick-view .elementor-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->endControlsSection();
    }

    protected function registerHeadingStyleSection()
    {
        $this->startControlsSection(
            'section_heading_style',
            [
                'label' => __('Heading'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'heading!' => '',
                ],
            ]
        );

        $this->addResponsiveControl(
            'heading_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container > .elementor-heading-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addResponsiveControl(
            'heading_align',
            [
                'label' => __('Alignment'),
                'type' => ControlsManager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container > .elementor-heading-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'heading_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container > .elementor-heading-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'heading_typography',
                'selector' => '{{WRAPPER}} > .elementor-widget-container > .elementor-heading-title',
            ]
        );

        $this->addGroupControl(
            GroupControlTextStroke::getType(),
            [
                'name' => 'text_stroke',
                'selector' => '{{WRAPPER}} > .elementor-widget-container > .elementor-heading-title',
            ]
        );

        $this->addGroupControl(
            GroupControlTextShadow::getType(),
            [
                'name' => 'heading_shadow',
                'selector' => '{{WRAPPER}} > .elementor-widget-container > .elementor-heading-title',
            ]
        );

        $this->endControlsSection();
    }

    protected function registerMiniatureStyleSections()
    {
        $scheme = $this->getName() === 'product-box';

        $this->startControlsSection(
            'section_style_image',
            [
                'label' => __('Image'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'skin' => 'custom',
                ],
            ]
        );

        $this->addControl(
            'hover_animation',
            [
                'label' => __('Hover Animation'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    '' => __('None'),
                    'grow' => __('Grow'),
                    'shrink' => __('Shrink'),
                    'rotate' => __('Rotate'),
                    'grow-rotate' => __('Grow Rotate'),
                    'float' => __('Float'),
                    'sink' => __('Sink'),
                    'bob' => __('Bob'),
                    'hang' => __('Hang'),
                    'buzz-out' => __('Buzz Out'),
                ],
                'prefix_class' => 'elementor-img-hover-',
            ]
        );

        $this->addGroupControl(
            GroupControlBorder::getType(),
            [
                'name' => 'image_border',
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .elementor-image img',
            ]
        );

        $this->addControl(
            'image_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_style_content',
            [
                'label' => __('Content'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'skin' => 'custom',
                ],
            ]
        );

        $this->addControl(
            'content_align',
            [
                'label' => __('Alignment'),
                'type' => ControlsManager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'left' => [
                        'title' => __('Left'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-content' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->addResponsiveControl(
            'content_padding',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'content_min_height',
            [
                'label' => __('Min Height'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-content' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                    ],
                ],
            ]
        );

        $this->startControlsTabs('content_style_tabs');

        $this->startControlsTab(
            'content_style_title',
            [
                'label' => __('Title'),
            ]
        );

        $this->addControl(
            'title_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-title' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'title_multiline',
            [
                'label' => __('Allow Multiline'),
                'type' => ControlsManager::SWITCHER,
                'selectors' => [
                    '{{WRAPPER}} .elementor-title' => 'white-space: normal;',
                ],
            ]
        );

        $this->addControl(
            'title_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'scheme' => !$scheme ? '' : [
                    'type' => SchemeColor::getType(),
                    'value' => SchemeColor::COLOR_1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'title_typography',
                'scheme' => !$scheme ? '' : SchemeTypography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .elementor-title',
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'content_style_category',
            [
                'label' => __('Category'),
                'condition' => [
                    'show_category!' => '',
                ],
            ]
        );

        $this->addControl(
            'category_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-category' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'category_multiline',
            [
                'label' => __('Allow Multiline'),
                'type' => ControlsManager::SWITCHER,
                'selectors' => [
                    '{{WRAPPER}} .elementor-category' => 'white-space: normal;',
                ],
            ]
        );

        $this->addControl(
            'category_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'scheme' => !$scheme ? '' : [
                    'type' => SchemeColor::getType(),
                    'value' => SchemeColor::COLOR_2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-category' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'category_typography',
                'scheme' => !$scheme ? '' : SchemeTypography::TYPOGRAPHY_2,
                'selector' => '{{WRAPPER}} .elementor-category',
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'content_style_description',
            [
                'label' => __('Description'),
                'condition' => [
                    'show_description!' => '',
                ],
            ]
        );

        $this->addControl(
            'description_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-description' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addResponsiveControl(
            'description_line_clamp',
            [
                'label' => __('Max Lines'),
                'type' => ControlsManager::NUMBER,
                'min' => 1,
                'selectors' => [
                    '{{WRAPPER}} .elementor-description' => '-webkit-line-clamp: {{VALUE}}',
                ],
            ]
        );

        $this->addControl(
            'description_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'scheme' => !$scheme ? '' : [
                    'type' => SchemeColor::getType(),
                    'value' => SchemeColor::COLOR_3,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'description_typography',
                'scheme' => !$scheme ? '' : SchemeTypography::TYPOGRAPHY_3,
                'selector' => '{{WRAPPER}} .elementor-description',
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'content_style_price',
            [
                'label' => __('Price'),
            ]
        );

        $this->addControl(
            'price_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-wrapper' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'price_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'scheme' => !$scheme ? '' : [
                    'type' => SchemeColor::getType(),
                    'value' => SchemeColor::COLOR_1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'price_typography',
                'scheme' => !$scheme ? '' : SchemeTypography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .elementor-price-wrapper',
            ]
        );

        $this->addControl(
            'heading_style_regular_price',
            [
                'label' => __('Regular Price'),
                'type' => ControlsManager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_regular_price' => $this->catalog ? 'hide' : 'yes',
                ],
            ]
        );

        $this->addControl(
            'regular_price_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'scheme' => !$scheme ? '' : [
                    'type' => SchemeColor::getType(),
                    'value' => SchemeColor::COLOR_2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-regular' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_regular_price' => $this->catalog ? 'hide' : 'yes',
                ],
            ]
        );

        $this->addResponsiveControl(
            'regular_price_size',
            [
                'label' => _x('Size', 'Typography Control'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-regular' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_regular_price' => $this->catalog ? 'hide' : 'yes',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->endControlsSection();

        $this->startControlsSection(
            'section_style_atc',
            [
                'label' => __('Add to Cart'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'skin' => 'custom',
                    'show_atc' => $this->catalog ? 'hide' : 'yes',
                ],
            ]
        );

        $this->addControl(
            'atc_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'atc_typography',
                'label' => __('Typography'),
                'scheme' => !$scheme ? '' : SchemeTypography::TYPOGRAPHY_4,
                'selector' => '{{WRAPPER}} .elementor-atc .elementor-button',
            ]
        );

        $this->startControlsTabs('atc_style_tabs');

        $this->startControlsTab(
            'atc_style_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addControl(
            'atc_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc .elementor-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'atc_bg_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc .elementor-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'atc_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc .elementor-button' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'atc_style_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addControl(
            'atc_color_hover',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc .elementor-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'atc_bg_color_hover',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc .elementor-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'atc_border_color_hover',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc .elementor-button:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'atc_style_disabled',
            [
                'label' => __('Disabled'),
            ]
        );

        $this->addControl(
            'atc_color_disabled',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} button.elementor-button:not(#e):disabled' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'atc_bg_color_disabled',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} button.elementor-button:disabled' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'atc_border_color_disabled',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} button.elementor-button:disabled' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'atc_cursor_disabled',
            [
                'label' => __('Cursor'),
                'label_block' => false,
                'type' => ControlsManager::CHOOSE,
                'options' => [
                    'default' => [
                        'icon' => 'fas fa-arrow-pointer',
                    ],
                    'not-allowed' => [
                        'icon' => 'eicon-ban',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} button.elementor-button:disabled' => 'cursor: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->addControl(
            'atc_border_width',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc .elementor-button' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
                ],
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'atc_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-atc .elementor-button' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_style_qv',
            [
                'label' => __('Quick View'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'show_qv' => 'yes',
                    'skin' => 'custom',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'qv_typography',
                'label' => __('Typography'),
                'scheme' => !$scheme ? '' : SchemeTypography::TYPOGRAPHY_4,
                'selector' => '{{WRAPPER}} .elementor-quick-view',
            ]
        );

        $this->startControlsTabs('qv_style_tabs');

        $this->startControlsTab(
            'qv_style_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addControl(
            'qv_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-quick-view' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'qv_bg_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-quick-view' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'qv_border_color',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-quick-view' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'qv_style_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addControl(
            'qv_color_hover',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-quick-view:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'qv_bg_color_hover',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-quick-view:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'qv_border_color_hover',
            [
                'label' => __('Border Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-quick-view:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->addControl(
            'qv_border_width',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-quick-view' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
                ],
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'qv_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-quick-view' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_style_badges',
            [
                'label' => __('Badges'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'skin' => 'custom',
                    'show_badges!' => [],
                ],
            ]
        );

        $this->addControl(
            'badges_top',
            [
                'label' => __('Top Distance'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => -20,
                        'max' => 20,
                    ],
                    'em' => [
                        'min' => -2,
                        'max' => 2,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-badges-left' => 'margin-top: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-badges-right' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'badges_left',
            [
                'label' => __('Left Distance'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => -20,
                        'max' => 20,
                    ],
                    'em' => [
                        'min' => -2,
                        'max' => 2,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-badges-left' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'badge_sale_position',
                            'value' => 'left',
                        ],
                        [
                            'name' => 'badge_new_position',
                            'value' => 'left',
                        ],
                        [
                            'name' => 'badge_pack_position',
                            'value' => 'left',
                        ],
                        [
                            'name' => 'badge_out_position',
                            'value' => 'left',
                        ],
                        [
                            'name' => 'badge_online_position',
                            'value' => 'left',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'badges_right',
            [
                'label' => __('Right Distance'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => -20,
                        'max' => 20,
                    ],
                    'em' => [
                        'min' => -2,
                        'max' => 2,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-badges-right' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'badge_sale_position',
                            'value' => 'right',
                        ],
                        [
                            'name' => 'badge_new_position',
                            'value' => 'right',
                        ],
                        [
                            'name' => 'badge_pack_position',
                            'value' => 'right',
                        ],
                        [
                            'name' => 'badge_out_position',
                            'value' => 'right',
                        ],
                        [
                            'name' => 'badge_online_position',
                            'value' => 'right',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'bagdes_spacing',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 150,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 150,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'badges_min_width',
            [
                'label' => __('Min Width'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge' => 'min-width: {{SIZE}}{{UNIT}};',
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 50,
                ],
            ]
        );

        $this->addControl(
            'badges_padding',
            [
                'label' => __('Padding'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->addControl(
            'badges_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'badges_typography',
                'selector' => '{{WRAPPER}} .elementor-badge',
            ]
        );

        $this->startControlsTabs('badge_style_tabs');

        $this->startControlsTab(
            'badge_style_sale',
            [
                'label' => __('Sale'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_badges',
                            'operator' => 'contains',
                            'value' => 'sale',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'badge_sale_position',
            [
                'label' => __('Position'),
                'type' => ControlsManager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'left' => [
                        'icon' => 'eicon-h-align-left',
                        'title' => __('Left'),
                    ],
                    'right' => [
                        'icon' => 'eicon-h-align-right',
                        'title' => __('Right'),
                    ],
                ],
                'default' => 'right',
            ]
        );

        $this->addControl(
            'badge_sale_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-sale' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'badge_sale_bg_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-sale' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'badge_style_new',
            [
                'label' => __('New'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_badges',
                            'operator' => 'contains',
                            'value' => 'new',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'badge_new_position',
            [
                'label' => __('Position'),
                'type' => ControlsManager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'left' => [
                        'icon' => 'eicon-h-align-left',
                        'title' => __('Left'),
                    ],
                    'right' => [
                        'icon' => 'eicon-h-align-right',
                        'title' => __('Right'),
                    ],
                ],
                'default' => 'right',
            ]
        );

        $this->addControl(
            'badge_new_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-new' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'badge_new_bg_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-new' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'badge_style_pack',
            [
                'label' => __('Pack'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_badges',
                            'operator' => 'contains',
                            'value' => 'pack',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'badge_pack_position',
            [
                'label' => __('Position'),
                'type' => ControlsManager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'left' => [
                        'icon' => 'eicon-h-align-left',
                        'title' => __('Left'),
                    ],
                    'right' => [
                        'icon' => 'eicon-h-align-right',
                        'title' => __('Right'),
                    ],
                ],
                'default' => 'right',
            ]
        );

        $this->addControl(
            'badge_pack_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-pack' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'badge_pack_bg_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-pack' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'badge_style_out',
            [
                'label' => __('Out-of-Stock'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_badges',
                            'operator' => 'contains',
                            'value' => 'out',
                        ],
                    ],
                ],
            ]
        );

        $this->addControl(
            'badge_out_position',
            [
                'label' => __('Position'),
                'type' => ControlsManager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'left' => [
                        'icon' => 'eicon-h-align-left',
                        'title' => __('Left'),
                    ],
                    'right' => [
                        'icon' => 'eicon-h-align-right',
                        'title' => __('Right'),
                    ],
                ],
                'default' => 'right',
            ]
        );

        $this->addControl(
            'badge_out_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-out' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'badge_out_bg_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-out' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'badge_style_online',
            [
                'label' => __('Online only'),
            ]
        );

        $this->addControl(
            'badge_online_position',
            [
                'label' => __('Position'),
                'type' => ControlsManager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'left' => [
                        'icon' => 'eicon-h-align-left',
                        'title' => __('Left'),
                    ],
                    'right' => [
                        'icon' => 'eicon-h-align-right',
                        'title' => __('Right'),
                    ],
                ],
                'default' => 'right',
            ]
        );

        $this->addControl(
            'badge_online_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-online' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'badge_online_bg_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-badge-online' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->endControlsSection();
    }

    public function onImport($widget)
    {
        static $id_product;

        if (null === $id_product) {
            $products = \Product::getProducts($this->context->language->id, 0, 1, 'id_product', 'ASC', false, true);
            $id_product = !empty($products[0]['id_product']) ? $products[0]['id_product'] : '';
        }

        // Check Skin
        if (!in_array($widget['settings']['skin'], ['product', 'custom']) &&
            !$this->getSkinTemplate($widget['settings']['skin'])
        ) {
            $widget['settings']['skin'] = 'product';
        }

        // Check Category ID
        if (!empty($widget['settings']['category_id'])) {
            $category = new \Category($widget['settings']['category_id']);

            if (!$category->id) {
                $widget['settings']['category_id'] = $this->context->shop->id_category;
            }
        }

        // Check Manufacturer ID
        if (!empty($widget['settings']['manufacturer_id'])) {
            $manufacturer = new \Manufacturer($widget['settings']['manufacturer_id']);

            if (!$manufacturer->id) {
                $widget['settings']['manufacturer_id'] = 0;
            }
        }

        // Check Supplier ID
        if (!empty($widget['settings']['supplier_id'])) {
            $supplier = new \Supplier($widget['settings']['supplier_id']);

            if (!$supplier->id) {
                $widget['settings']['supplier_id'] = 0;
            }
        }

        // Check Product ID
        if (!empty($widget['settings']['product_id'])) {
            $product = new \Product($widget['settings']['product_id']);

            if (!$product->id) {
                $widget['settings']['product_id'] = $id_product;
            }
        }

        // Check Related Product ID
        if (!empty($widget['settings']['related_product_id'])) {
            $product = new \Product($widget['settings']['related_product_id']);

            if (!$product->id) {
                $widget['settings']['related_product_id'] = $id_product;
            }
        }

        // Check Product IDs
        if (!empty($widget['settings']['products'])) {
            $table = _DB_PREFIX_ . 'product';
            $prods = [];
            $ids = [];

            foreach ($widget['settings']['products'] as &$prod) {
                $ids[] = (int) $prod['id'];
            }
            $ids = implode(', ', $ids);
            $rows = \Db::getInstance()->executeS("SELECT id_product FROM $table WHERE id_product IN ($ids)");

            foreach ($rows as &$row) {
                $prods[$row['id_product']] = true;
            }

            foreach ($widget['settings']['products'] as &$prod) {
                if ($prod['id'] && empty($prods[$prod['id']])) {
                    $prod['id'] = $id_product;
                }
            }
        }

        // Check Product Image Sizes
        $sizes = array_map(function ($size) {
            return $size['name'];
        }, \ImageType::getImagesTypes('products'));

        foreach (['image_size', 'image_size_tablet', 'image_size_mobile'] as $image_size) {
            if (isset($widget['settings'][$image_size]) && !in_array($widget['settings'][$image_size], $sizes)) {
                $home = \ImageType::getFormattedName('home');

                $widget['settings'][$image_size] = in_array($home, $sizes) ? $home : reset($sizes);
            }
        }

        IconsManager::onImportMigration($widget, 'atc_icon', 'selected_atc_icon');
        IconsManager::onImportMigration($widget, 'qv_icon', 'selected_qv_icon');

        return $widget;
    }

    public static function getAccessoriesLight($id_product)
    {
        return \Db::getInstance()->executeS('
            SELECT p.`id_product` FROM ' . _DB_PREFIX_ . 'accessory
            LEFT JOIN ' . _DB_PREFIX_ . 'product p ON (p.`id_product` = id_product_2)
            ' . \Shop::addSqlAssociation('product', 'p') . '
            WHERE id_product_1 = ' . (int) $id_product . ' AND p.active = 1'
        );
    }

    protected function getProduct($id)
    {
        $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductPresenter(
            new \PrestaShop\PrestaShop\Adapter\Image\ImageRetriever($this->context->link),
            $this->context->link,
            new \PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
            new \PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
            $this->context->getTranslator()
        );
        $presenterFactory = new \ProductPresenterFactory($this->context);
        $assembler = new \ProductAssembler($this->context);

        try {
            return ($assembledProduct = $assembler->assembleProduct(['id_product' => $id])) ? $presenter->present(
                $presenterFactory->getPresentationSettings(),
                $assembledProduct,
                $this->context->language
            ) : false;
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function getProducts($listing, $order_by, $order_dir, $limit, $id, $products = [])
    {
        $tpls = [];

        if ('viewed' === $listing) {
            // Recently Viewed
            $products = isset($this->context->cookie->ceViewedProducts)
                ? explode(',', $this->context->cookie->ceViewedProducts)
                : []
            ;
            if ($this->context->controller instanceof \ProductController) {
                $id_product = $this->context->controller->getProduct()->id;

                if ($id_product && in_array($id_product, $products)) {
                    $products = array_diff($products, [$id_product]);
                }
            }
            $products = array_slice($products, 0, $limit);

            if ('rand' === $order_by) {
                shuffle($products);
            }
            foreach ($products as $id_product) {
                if ($product = $this->getProduct($id_product)) {
                    $tpls[] = $product;
                }
            }

            return $tpls;
        }
        if ('products' === $listing) {
            // Custom Products
            if ('rand' === $order_by) {
                shuffle($products);
            }
            foreach ($products as &$product) {
                if ($product['id'] && $product = $this->getProduct($product['id'])) {
                    $tpls[] = $product;
                }
            }

            return $tpls;
        }

        $products = [];
        $translator = $this->context->getTranslator();
        $query = new \PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery();

        if ($this->context->controller instanceof \ProductController) {
            ++$limit;
        }
        $query->setResultsPerPage($limit);
        $query->setQueryType($listing);

        switch ($listing) {
            case 'category':
                if ($id) {
                    $category = new \Category((int) $id);
                } elseif ($this->context->controller instanceof \ProductController) {
                    $category = new \Category((int) $this->context->controller->getProduct()->id_category_default);
                } elseif ($this->context->controller instanceof \CategoryController) {
                    $category = $this->context->controller->getCategory();
                } else {
                    $category = new \Category($this->context->shop->id_category);
                }
                $searchProvider = new \PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider($translator, $category);

                $query->setSortOrder(
                    'rand' === $order_by
                    ? \PrestaShop\PrestaShop\Core\Product\Search\SortOrder::random()
                    : new \PrestaShop\PrestaShop\Core\Product\Search\SortOrder('product', $order_by, $order_dir)
                );
                break;
            case 'prices-drop':
                $searchProvider = new \PrestaShop\PrestaShop\Adapter\PricesDrop\PricesDropProductSearchProvider($translator);
                $query->setSortOrder(new \PrestaShop\PrestaShop\Core\Product\Search\SortOrder('product', $order_by, $order_dir));
                break;
            case 'new-products':
                $searchProvider = new \PrestaShop\PrestaShop\Adapter\NewProducts\NewProductsProductSearchProvider($translator);
                $query->setSortOrder(new \PrestaShop\PrestaShop\Core\Product\Search\SortOrder('product', $order_by, $order_dir));
                break;
            case 'best-sales':
                $searchProvider = new \PrestaShop\PrestaShop\Adapter\BestSales\BestSalesProductSearchProvider($translator);
                $query->setSortOrder(new \PrestaShop\PrestaShop\Core\Product\Search\SortOrder('product', $order_by, $order_dir));
                break;
            case 'related':
                if ($id) {
                    $products = self::getAccessoriesLight($id);
                } elseif ($this->context->controller instanceof \ProductController) {
                    $products = self::getAccessoriesLight($this->context->controller->getProduct()->id);
                } elseif ($this->context->controller instanceof \CartController) {
                    $cart = $this->context->controller->cart_presenter->present($this->context->cart, true);
                    $i = count($cart['products']);

                    $exclude_ids = array_unique(array_map(function ($product) {
                        return $product->id;
                    }, $cart['products']));

                    while ($i--) {
                        $related_products = self::getAccessoriesLight($cart['products'][$i]->id);

                        foreach ($related_products as &$related_product) {
                            if (!in_array($related_product['id_product'], $exclude_ids)) {
                                $products[] = $related_product;
                                $exclude_ids[] = $related_product['id_product'];
                            }
                        }
                        if (count($products) > $limit) {
                            break;
                        }
                    }
                }
                if ('rand' === $order_by) {
                    shuffle($products);
                }
                if (count($products) > $limit) {
                    $products = array_slice($products, 0, $limit);
                }
                break;
            case 'manufacturer':
                if (!$id && $this->context->controller instanceof \ProductController) {
                    $id = $this->context->controller->getProduct()->id_manufacturer;
                }
                $manufacturer = new \Manufacturer((int) $id);
                $searchProvider = new \PrestaShop\PrestaShop\Adapter\Manufacturer\ManufacturerProductSearchProvider($translator, $manufacturer);
                $query->setSortOrder(new \PrestaShop\PrestaShop\Core\Product\Search\SortOrder('product', $order_by, $order_dir));
                break;
            case 'supplier':
                if (!$id && $this->context->controller instanceof \ProductController) {
                    $id = $this->context->controller->getProduct()->id_supplier;
                }
                $supplier = new \Supplier((int) $id);
                $searchProvider = new \PrestaShop\PrestaShop\Adapter\Supplier\SupplierProductSearchProvider($translator, $supplier);
                $query->setSortOrder(new \PrestaShop\PrestaShop\Core\Product\Search\SortOrder('product', $order_by, $order_dir));
                break;
        }

        if ('category' === $listing && !$id && $this->context->controller instanceof \CartController) {
            $cart = $this->context->controller->cart_presenter->present($this->context->cart, true);

            $category_ids = array_unique(array_map(function ($product) {
                return $product->id_category_default;
            }, $cart['products']));

            $exclude_ids = array_unique(array_map(function ($product) {
                return $product->id;
            }, $cart['products']));

            $productSearchContext = new \PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext($this->context);

            foreach ($category_ids as $id_category) {
                $category = new \Category($id_category);
                $searchProvider = new \PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider($translator, $category);
                $result = $searchProvider->runQuery($productSearchContext, $query);

                foreach ($result->getProducts() as $product) {
                    if (!in_array($product['id_product'], $exclude_ids)) {
                        $products[] = $product;
                        $exclude_ids[] = $product['id_product'];

                        if (count($products) > $limit) {
                            break 2;
                        }
                    }
                }
            }
        } elseif ('related' !== $listing && isset($searchProvider)) {
            $result = $searchProvider->runQuery(new \PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext($this->context), $query);
            $products = $result->getProducts();
        }

        if ($this->context->controller instanceof \ProductController) {
            $current_product_id = $this->context->controller->getProduct()->id;
            $products = array_filter($products, function ($product) use ($current_product_id) {
                return $product['id_product'] != $current_product_id;
            });
            if (count($products) === $limit) {
                array_pop($products);
            }
        }

        $assembler = new \ProductAssembler($this->context);
        $presenterFactory = new \ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
            new \PrestaShop\PrestaShop\Adapter\Image\ImageRetriever($this->context->link),
            $this->context->link,
            new \PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
            new \PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
            $translator
        );

        foreach ($products as &$product) {
            $tpls[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($product),
                $this->context->language
            );
        }

        return $tpls;
    }

    /**
     * Use this method to return the result of a product miniature template.
     *
     * @since 1.0.0
     *
     * @param array $settings
     * @param $product
     *
     * @return string
     */
    protected function fetchMiniature(array &$settings, $product)
    {
        $title_tag = $settings['title_tag'];
        $display_class = !empty($settings['title_display']) ? " ce-display-{$settings['title_display']}" : '';
        $image_size = !empty($settings['image_size']) ? $settings['image_size'] : $this->imageSize;
        $cover = $product['cover'] ?: Helper::getNoImage();
        $cover_url = [
            'desktop' => $cover['bySize'][$image_size]['url'],
        ];

        if (!empty($settings['image_size_tablet']) && $settings['image_size_tablet'] !== $image_size) {
            $cover_url['tablet'] = $cover['bySize'][$settings['image_size_tablet']]['url'];
        }
        if (!empty($settings['image_size_mobile']) && $settings['image_size_mobile'] !== $settings['image_size_tablet']) {
            $cover_url['mobile'] = $cover['bySize'][$settings['image_size_mobile']]['url'];
        }
        $cover_alt = !empty($product['cover']['legend']) ? $product['cover']['legend'] : $product['name'];

        if (!empty($settings['show_description'])) {
            $description = strip_tags($product['description_short']);

            if (!empty($settings['description_length']) && \Tools::strlen($description) > $settings['description_length']) {
                $description = rtrim(\Tools::substr($description, 0, \Tools::strpos($description, ' ', $settings['description_length'])), '-,.') . '…';
            }
        }
        $this->setRenderAttribute('article', [
            'data-id-product' => $product['id_product'],
            'data-id-product-attribute' => $product['id_product_attribute'],
        ]);

        ob_start(); ?>
        <article class="elementor-product-miniature" <?php $this->printRenderAttributeString('article'); ?>>
            <a class="elementor-product-link" href="<?php echo esc_attr($product['url']); ?>">
                <div class="elementor-image">
                    <picture class="elementor-cover-image">
                    <?php if (isset($cover_url['mobile'])) { ?>
                        <source media="(max-width: 767px)" srcset="<?php echo esc_attr($cover_url['mobile']); ?>">
                    <?php } ?>
                    <?php if (isset($cover_url['tablet'])) { ?>
                        <source media="(max-width: 991px)" srcset="<?php echo esc_attr($cover_url['tablet']); ?>">
                    <?php } ?>
                        <img src="<?php echo esc_attr($cover_url['desktop']); ?>" loading="<?php echo $this->loading; ?>" alt="<?php echo esc_attr($cover_alt); ?>"
                            width="<?php echo (int) $cover['bySize'][$image_size]['width']; ?>" height="<?php echo (int) $cover['bySize'][$image_size]['height']; ?>">
                    </picture>
        <?php
        if (!empty($settings['show_second_image']) && !empty($product['images'])) {
            foreach ($product['images'] as $image) {
                if ($image['id_image'] != $cover['id_image']) {
                    ?>
                    <picture class="elementor-second-image">
                    <?php if (isset($cover_url['mobile'])) { ?>
                        <source media="(max-width: 767px)" srcset="<?php echo esc_attr($image['bySize'][$settings['image_size_mobile']]['url']); ?>">
                    <?php } ?>
                    <?php if (isset($cover_url['tablet'])) { ?>
                        <source media="(max-width: 991px)" srcset="<?php echo esc_attr($image['bySize'][$settings['image_size_tablet']]['url']); ?>">
                    <?php } ?>
                        <img src="<?php echo esc_attr($image['bySize'][$image_size]['url']); ?>" loading="lazy" alt="<?php echo esc_attr($image['legend']); ?>"
                            width="<?php echo (int) $image['bySize'][$image_size]['width']; ?>" height="<?php echo (int) $image['bySize'][$image_size]['height']; ?>">
                    </picture>
                    <?php
                    break;
                }
            }
        } ?>
                <?php if (!empty($settings['show_qv'])) { ?>
                    <div class="elementor-button elementor-quick-view" data-link-action="quickview">
                        <span class="elementor-button-content-wrapper">
                        <?php if ($qv_icon = IconsManager::getBcIcon($settings, 'qv_icon', ['aria-hidden' => 'true'])) { ?>
                            <span class="elementor-button-icon elementor-align-icon-<?php echo $settings['qv_icon_align']; ?>"><?php echo $qv_icon; ?></span>
                        <?php } ?>
                            <span class="elementor-button-text"><?php echo $settings['qv_text']; ?></span>
                        </span>
                    </div>
                <?php } ?>
                </div>
            <?php foreach (['left', 'right'] as $position) { ?>
                <div class="elementor-badges-<?php echo $position; ?>">
                <?php foreach ($settings['show_badges'] as $badge) { ?>
                    <?php if ($position === $settings["badge_{$badge}_position"] && !empty($product['flags'][self::FLAGS[$badge]])) { ?>
                        <div class="elementor-badge elementor-badge-<?php echo $badge; ?>">
                            <?php echo $settings["badge_{$badge}_text"] ?: $product['flags'][self::FLAGS[$badge]]['label']; ?>
                        </div>
                    <?php } ?>
                <?php } ?>
                </div>
            <?php } ?>
                <div class="elementor-content">
                <?php if (!empty($settings['show_category'])) { ?>
                    <h4 class="elementor-category"><?php echo $product['category_name']; ?></h4>
                <?php } ?>
                    <<?php echo $title_tag; ?> class="elementor-title<?php echo esc_attr($display_class); ?>"><?php echo $product['name']; ?></<?php echo $title_tag; ?>>
                <?php if (!empty($description)) { ?>
                    <div class="elementor-description"><?php echo $description; ?></div>
                <?php } ?>
                <?php if ($this->show_prices && $product['show_price']) { ?>
                    <div class="elementor-price-wrapper">
                    <?php if (!empty($product['has_discount']) && !empty($settings['show_regular_price'])) { ?>
                        <span class="elementor-price-regular"><?php echo $product['regular_price']; ?></span>
                    <?php } ?>
                        <span class="elementor-price"><?php echo $product['price']; ?></span>
                    </div>
                <?php } ?>
                </div>
            </a>
        <?php if ($settings['show_atc'] && $this->show_prices && $product['show_price']) { ?>
            <form class="elementor-atc<?php echo !empty($settings['atc_type']) ? " elementor-button-{$settings['atc_type']}" : ''; ?>" action="<?php echo esc_attr($product['add_to_cart_url']); ?>">
                <input type="hidden" name="qty" value="<?php echo max(1, $product[
                    !empty($product['product_attribute_minimal_quantity']) ? 'product_attribute_minimal_quantity' : 'minimal_quantity'
                ]); ?>">
                <button type="submit" class="elementor-button elementor-size-<?php echo $settings['atc_size']; ?>"
                    data-button-action="add-to-cart"<?php echo $product['add_to_cart_url'] ? '' : ' disabled'; ?>>
                    <span class="elementor-button-content-wrapper">
                    <?php if ($atc_icon = IconsManager::getBcIcon($settings, 'atc_icon', ['aria-hidden' => 'true'])) { ?>
                        <span class="elementor-atc-icon elementor-align-icon-<?php echo $settings['atc_icon_align']; ?>"><?php echo $atc_icon; ?></span>
                    <?php } ?>
                        <span class="elementor-button-text"><?php echo $settings['atc_text']; ?></span>
                    </span>
                </button>
            </form>
        <?php } ?>
        </article>
        <?php
        return ob_get_clean();
    }

    protected function _addRenderAttributes()
    {
        parent::_addRenderAttributes();

        if ($this->getSettings('skin') !== 'custom') {
            if ($wrapfix = Helper::getWrapfix()) {
                $this->addRenderAttribute('_wrapper', 'class', $wrapfix);
            }
        }
    }

    public function renderPlainContent()
    {
    }
}
