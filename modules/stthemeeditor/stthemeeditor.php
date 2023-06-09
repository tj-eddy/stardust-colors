<?php
/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    ST-themes <hellolee@gmail.com>
*  @copyright 2007-2017 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*/

if (!defined('_PS_VERSION_'))
    exit;
        
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;

class StThemeEditor extends Module implements WidgetInterface
{	
    protected static $access_rights = 0775;
    public $imgtype = array('jpg', 'gif', 'jpeg', 'png', 'svg', 'ico');
    public $defaults;
    public $tabs;
    private $_html;
    private $_config_folder;
    private $_hooks;
    private $_font_inherit = 'inherit';
    public $fields_form; 
    public $fields_value;
    public $_category_sortby;
    public $_product_ways;
    public $_clear_list_view;
    public $validation_errors = array();
    private $theme_valid;
    private $systemFonts = array("Helvetica","Arial","Verdana","Georgia","Tahoma","Times New Roman","sans-serif","IRANSans","IRANSansF","Vazir","VazirF");
    private $googleFonts;
    private $lang_array = array('welcome','welcome_logged','welcome_link','copyright_text','search_label','newsletter_label');
    public $module_font = array('stbanner','steasycontent','stswiper','stowlcarousel','stpagebanner','ststickers','stcountdown','stblogeditor');
    public static $cache_product_images = array();
    protected static $cache_vals = array();
    private $has_easybuilder = null;
    private $has_eb_trans_panda = null;
    
    public static $items = array(
		array('id' => 2, 'name' => '2'),
		array('id' => 3, 'name' => '3'),
		array('id' => 4, 'name' => '4'),
		array('id' => 5, 'name' => '5'),
		array('id' => 6, 'name' => '6'),
    );
    public static $textTransform = array(
		array('id' => 0, 'name' => 'none'),
		array('id' => 1, 'name' => 'uppercase'),
		array('id' => 2, 'name' => 'lowercase'),
		array('id' => 3, 'name' => 'capitalize'),
    );
    public static $width_map = array(
        array('id'=>1, 'name'=>'1/12'),
        array('id'=>2, 'name'=>'2/12'),
        array('id'=>3, 'name'=>'3/12'),
        array('id'=>4, 'name'=>'4/12'),
        array('id'=>5, 'name'=>'5/12'),
        array('id'=>6, 'name'=>'6/12'),
        array('id'=>7, 'name'=>'7/12'),
        array('id'=>8, 'name'=>'8/12'),
        array('id'=>9, 'name'=>'9/12'),
        array('id'=>10, 'name'=>'10/12'),
        array('id'=>11, 'name'=>'11/12'),
        array('id'=>12, 'name'=>'12/12'),
    );
    public static $border_style_map = array(
        array('id'=>0,  'name'=>'None'),
        array('id'=>11, 'name'=>'Full width, 1px height'),
        array('id'=>12, 'name'=>'Full width, 2px height'),
        array('id'=>13, 'name'=>'Full width, 3px height'),
        array('id'=>14, 'name'=>'Full width, 4px height'),
        array('id'=>15, 'name'=>'Full width, 5px height'),
        array('id'=>16, 'name'=>'Full width, 6px height'),
        array('id'=>17, 'name'=>'Full width, 7px height'),
        array('id'=>18, 'name'=>'Full width, 8px height'),
        array('id'=>19, 'name'=>'Full width, 9px height'),
        array('id'=>21, 'name'=>'Boxed width, 1px height'),
        array('id'=>22, 'name'=>'Boxed width, 2px height'),
        array('id'=>23, 'name'=>'Boxed width, 3px height'),
        array('id'=>24, 'name'=>'Boxed width, 4px height'),
        array('id'=>25, 'name'=>'Boxed width, 5px height'),
        array('id'=>26, 'name'=>'Boxed width, 6px height'),
        array('id'=>27, 'name'=>'Boxed width, 7px height'),
        array('id'=>28, 'name'=>'Boxed width, 8px height'),
        array('id'=>29, 'name'=>'Boxed width, 9px height'),
    );
     public static $grid_width = array(
        array('id'=>1, 'name'=> '1/12'),
        array('id'=>1.2, 'name'=> '1.2/12'),
        array('id'=>1.5, 'name'=> '1.5/12'),
        array('id'=>2, 'name'=> '2/12'),
        array('id'=>2.4,'name'=> '2.4/12'),
        array('id'=>3,'name'=> '3/12'),
        array('id'=>4, 'name'=> '4/12'),
        array('id'=>5, 'name'=> '5/12'),
        array('id'=>6, 'name'=> '6/12'),
        array('id'=>7, 'name'=> '7/12'),
        array('id'=>8, 'name'=> '8/12'),
        array('id'=>9, 'name'=> '9/12'),
        array('id'=>10, 'name'=> '10/12'),
        array('id'=>11, 'name'=> '11/12'),
        array('id'=>12, 'name'=> '12/12'),
    );
    public $theme_repository;
	public function __construct()
	{
		$this->name = 'stthemeeditor';
        $this->tab = 'front_office_features';
        $this->version = '2.8.0';
        $this->author = 'SUNNYTOO.COM';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];

        $this->bootstrap = true;
        parent::__construct();

		$this->displayName = $this->getTranslator()->trans('Theme editor', array(), 'Modules.Stthemeeditor.Admin');
		$this->description = $this->getTranslator()->trans('Allows to change theme design', array(), 'Modules.Stthemeeditor.Admin');
        
        $this->googleFonts = include(dirname(__FILE__).'/googlefonts.php');
        $this->_config_folder = _PS_MODULE_DIR_.$this->name.'/config/';
        if($custom_fonts_string = Configuration::get('STSN_CUSTOM_FONTS'))
        {
            $custom_fonts_arr = explode(',', $custom_fonts_string);
            foreach ($custom_fonts_arr as $font)
                if(trim($font))
                    $this->systemFonts[] = $font;
        }

        //
        $this->theme_repository = (new ThemeManagerBuilder($this->context, Db::getInstance()))->buildRepository();
        
        $this->tabs = array(
                array('id'  => '99', 'name' => $this->getTranslator()->trans('Dashboard', array(), 'Modules.Stthemeeditor.Admin')),
                array('id'  => '0,23', 'name' => $this->getTranslator()->trans('General', array(), 'Admin.Theme.Panda')),
                array('id'  => '30,4,39', 'name' => $this->getTranslator()->trans('Header & Mobile Header', array(), 'Admin.Theme.Panda')),
                array('id'  => '37', 'name' => $this->getTranslator()->trans('Sticky header & Menu', array(), 'Modules.Stthemeeditor.Admin')),
                array('id'  => '16,61,35,38', 'name' => $this->getTranslator()->trans('Product pages', array(), 'Admin.Theme.Panda')),
                array('id'  => '1', 'name' => $this->getTranslator()->trans('Category pages', array(), 'Admin.Theme.Panda')),
                array('id'  => '2,31,32,33,20,36,40', 'name' => $this->getTranslator()->trans('Colors', array(), 'Admin.Theme.Panda')),//41
                array('id'  => '3,27,29,28,65', 'name' => $this->getTranslator()->trans('Font', array(), 'Admin.Theme.Panda')),
                // array('id'  => '15,24,25,26', 'name' => $this->getTranslator()->trans('Stickers', array(), 'Modules.Stthemeeditor.Admin')),
                array('id'  => '5,51,52,53,21', 'name' => $this->getTranslator()->trans('Menu', array(), 'Admin.Theme.Panda')),
                array('id'  => '6', 'name' => $this->getTranslator()->trans('Body', array(), 'Admin.Theme.Panda')),
                array('id'  => '7,8,9,10', 'name' => $this->getTranslator()->trans('Footer', array(), 'Admin.Theme.Panda')),
                array('id'  => '62', 'name' => $this->getTranslator()->trans('Login page', array(), 'Admin.Theme.Panda')),
                array('id'  => '63', 'name' => $this->getTranslator()->trans('Checkout page', array(), 'Admin.Theme.Panda')),
                array('id'  => '64,66', 'name' => $this->getTranslator()->trans('Other pages', array(), 'Admin.Theme.Panda')),
                array('id'  => '60', 'name' => $this->getTranslator()->trans('Logo', array(), 'Admin.Theme.Panda')),
                array('id'  => '11,12,13', 'name' => $this->getTranslator()->trans('Product sliders', array(), 'Modules.Stthemeeditor.Admin')),
                array('id'  => '14', 'name' => $this->getTranslator()->trans('Custom codes', array(), 'Modules.Stthemeeditor.Admin')),
                array('id'  => '18', 'name' => $this->getTranslator()->trans('Favicon', array(), 'Modules.Stthemeeditor.Admin')),
            );
        $this->_category_sortby = array(
            array(
                'id' => 'price_asc',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Price: Lowest first', array(), 'Modules.Stthemeeditor.Admin')
            ),
            array(
                'id' => 'price_desc',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Price: Highest first', array(), 'Modules.Stthemeeditor.Admin')
            ),
            array(
                'id' => 'name_asc',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Product Name: A to Z', array(), 'Modules.Stthemeeditor.Admin')
            ),
            array(
                'id' => 'name_desc',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Product Name: Z to A', array(), 'Modules.Stthemeeditor.Admin')
            ),
            array(
                'id' => 'quantity_desc',
                'val' => '1',
                'name' => $this->getTranslator()->trans('In stock', array(), 'Modules.Stthemeeditor.Admin')
            ),
            array(
                'id' => 'reference_asc',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Reference: Lowest first', array(), 'Modules.Stthemeeditor.Admin')
            ),
            array(
                'id' => 'reference_desc',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Reference: Highest first', array(), 'Modules.Stthemeeditor.Admin')
            ),
        );
        $this->_product_ways = array(
                array(
                    'id' => 'grid',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Grid', array(), 'Admin.Theme.Panda')
                ),
                array(
                    'id' => 'list',
                    'val' => '2',
                    'name' => $this->getTranslator()->trans('List', array(), 'Admin.Theme.Panda')
                ),
                /*array(
                    'id' => 'small_list',
                    'val' => '4',
                    'name' => $this->getTranslator()->trans('List with small product images', array(), 'Admin.Theme.Panda')
                ),*/
            );
        $this->_clear_list_view = array(
                array(
                    'id' => 'image',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Product image', array(), 'Admin.Theme.Panda')
                ),
                array(
                    'id' => 'desc',
                    'val' => '2',
                    'name' => $this->getTranslator()->trans('Product description', array(), 'Admin.Theme.Panda')
                ),
                array(
                    'id' => 'add_to_cart',
                    'val' => '4',
                    'name' => $this->getTranslator()->trans('Add to cart button', array(), 'Admin.Theme.Panda')
                ),
                array(
                    'id' => 'price',
                    'val' => '8',
                    'name' => $this->getTranslator()->trans('Price', array(), 'Admin.Theme.Panda')
                ),
                array(
                    'id' => 'buttons',
                    'val' => '16',
                    'name' => $this->getTranslator()->trans('Buttons, like "add to wishlist", "quick view"', array(), 'Admin.Theme.Panda')
                ),
            );
        $this->defaults = array(
            'responsive'                              => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            'responsive_max'                          => array('exp'=>1,'val'=>1),
            'boxstyle'                                => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'version_switching'                       => array('exp'=>1,'val'=>0),
            'welcome'                                 => array('exp'=>1,'val'=>array('1'=>'Welcome')),
            'welcome_logged'                          => array('exp'=>1,'val'=>array('1'=>'Welcome')),
            'welcome_link'                            => array('exp'=>1,'val'=>array('1'=>'')),
            'product_view'                            => array('exp'=>1,'val'=>0),
            'product_view_mobile'                     => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'product_view_swither'                    => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            'pro_spacing_grid'                        => array('exp'=>1,'val'=>15),
            'products_per_page'                       => array('exp'=>1,'val'=>8), //Presatshop's theme installation program would change the default value of 'PS_PRODUCTS_PER_PAGE' to 8, so no need to change the value in this module's install function
            'infinite_scroll'                         => array('exp'=>1,'val'=>0, 'smarty_val'=>1, 'js_val'=>1),
            'cate_pro_lazy'                           => array('exp'=>1,'val'=>1, 'smarty_val'=>1, 'js_val'=>1),
            'sticky_column'                           => array('exp'=>1,'val'=>0, 'js_val'=>1),
            'filter_position'                         => array('exp'=>1,'val'=>0, 'smarty_val'=>1, 'js_val'=>1),
            'copyright_text'                          => array('exp'=>0,'val'=>array(1=>'&COPY; '.date('Y').' Powered by Presta Shop&trade;. All Rights Reserved'),'esc'=>1),
            /*'search_label'                          => array('exp'=>1,'val'=>array(1=>'Search here')),
            'newsletter_label'                        => array('exp'=>1,'val'=>array(1=>'Your e-mail')),*/ 
            'footer_img'                              => array('exp'=>1,'val'=>'img/payment-options.png'), 
            /*'icon_iphone_57'                          => array('exp'=>1,'val'=>'img/touch-icon-iphone-57.png'), 
            'icon_iphone_72'                          => array('exp'=>1,'val'=>'img/touch-icon-iphone-72.png'), 
            'icon_iphone_114'                         => array('exp'=>1,'val'=>'img/touch-icon-iphone-114.png'), 
            'icon_iphone_144'                         => array('exp'=>1,'val'=>'img/touch-icon-iphone-144.png'),*/ 
            'icon_iphone_16'                         => array('exp'=>1,'val'=>$this->_path.'img/favicon-16.png'), 
            'icon_iphone_32'                         => array('exp'=>1,'val'=>$this->_path.'img/favicon-32.png'), 
            'icon_iphone_150'                         => array('exp'=>1,'val'=>$this->_path.'img/favicon-150.png'), 
            'icon_iphone_180'                         => array('exp'=>1,'val'=>$this->_path.'img/favicon-180.png'), 
            'icon_iphone_192'                         => array('exp'=>1,'val'=>$this->_path.'img/favicon-192.png'), 
            'icon_iphone_512'                         => array('exp'=>1,'val'=>$this->_path.'img/favicon-512.png'), 
            'icon_iphone_svg'                         => array('exp'=>1,'val'=>$this->_path.'img/favicon-svg.svg'), 
            'favicon_svg_color'                    => array('exp'=>1,'val'=>'','smarty_val'=>1),
            'browser_theme_color'                    => array('exp'=>1,'val'=>'','smarty_val'=>1),
            'favicon_update_time'                    => array('exp'=>1,'val'=>'','smarty_val'=>1),

            'custom_css'                              => array('exp'=>0,'val'=>'','esc'=>1), 
            'custom_js'                               => array('exp'=>0,'val'=>'','esc'=>1), 
            'tracking_code'                           => array('exp'=>0,'val'=>'','esc'=>1), 
            'head_code'                               => array('exp'=>0,'val'=>'','esc'=>1), 
            'google_rich_snippets'                    => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'display_tax_label'                       => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'flyout_buttons'                          => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'flyout_buttons_style'                => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'flyout_buttons_on_mobile'                => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'length_of_product_name'                  => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'product_name_transform'                  => array('exp'=>1,'val'=>3),
            'pro_name_size'                           => array('exp'=>1,'val'=>0),
            'logo_position'                           => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'logo_height'                             => array('exp'=>1,'val'=>0),
            'logo_width'                              => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'logo_width_sticky_header'                => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'logo_width_mobile_header'                => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'logo_width_sticky_mobile_header'         => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'megamenu_position'                       => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            // 'animation'                            => array('exp'=>1,'val'=>0),
            'transparent_header'                      => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'block_spacing'                           => array('exp'=>1,'val'=>0),
            'sticky_option'                           => array('exp'=>1,'val'=>3,'js_val'=>2,'smarty_val'=>2),
            'sticky_topbar'                           => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'sticky_primary_header'                   => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'sticky_displaybanner'                   => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            //font
            "font_text"                               => array('exp'=>1,'val'=>''),
            "font_body_size"                          => array('exp'=>1,'val'=>0),
            "font_price"                              => array('exp'=>1,'val'=>''),
            "font_price_size"                         => array('exp'=>1,'val'=>0),
            "font_old_price_size"                     => array('exp'=>1,'val'=>0),
            "pro_name"                                => array('exp'=>1,'val'=>''),
            "font_heading"                            => array('exp'=>1,'val'=>'Vollkorn:400'),
            // "font_heading_weight"                  => array('exp'=>1,'val'=>0),
            "font_heading_trans"                      => array('exp'=>1,'val'=>1),
            "font_heading_size"                       => array('exp'=>1,'val'=>0),
            "footer_heading_size"                     => array('exp'=>1,'val'=>0),
            'heading_bottom_border'                   => array('exp'=>1,'val'=>2),
            'heading_bottom_border_color'             => array('exp'=>1,'val'=>''),
            'heading_bottom_border_color_h'           => array('exp'=>1,'val'=>''),
            'heading_style'                           => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            "heading_bg_img"                          => array('exp'=>1,'val'=>''),
            "heading_bg_pattern"                      => array('exp'=>1,'val'=>0), 
            'heading_column_bottom_border'            => array('exp'=>1,'val'=>2),
            'heading_column_bg'                       => array('exp'=>1,'val'=>''),
            /*
            "font_title"                              => array('exp'=>1,'val'=>'Vollkorn:400'),
            "font_title_weight"                       => array('exp'=>1,'val'=>0),
            "font_title_trans"                        => array('exp'=>1,'val'=>1),
            "font_title_size"                         => array('exp'=>1,'val'=>''),
            */
            "font_menu"                               => array('exp'=>1,'val'=>'Vollkorn:400'),
            "second_font_menu"                        => array('exp'=>1,'val'=>''),
            "third_font_menu"                         => array('exp'=>1,'val'=>''),
            "ver_font_menu"                           => array('exp'=>1,'val'=>''),
            // "font_menu_weight"                     => array('exp'=>1,'val'=>0),
            "font_menu_trans"                         => array('exp'=>1,'val'=>1),
            "font_menu_size"                          => array('exp'=>1,'val'=>0),
            "second_font_menu_size"                   => array('exp'=>1,'val'=>0),
            "third_font_menu_size"                    => array('exp'=>1,'val'=>0),
            "ver_font_menu_size"                      => array('exp'=>1,'val'=>0),
            "st_menu_height"                          => array('exp'=>1,'val'=>0),
            "font_cart_btn"                           => array('exp'=>1,'val'=>'Vollkorn:400'),
            "font_latin_support"                      => array('exp'=>1,'val'=>0),
            "font_cyrillic_support"                   => array('exp'=>1,'val'=>0),
            "font_vietnamese"                         => array('exp'=>1,'val'=>0),
            "font_greek_support"                      => array('exp'=>1,'val'=>0),
            "font_arabic_support"                     => array('exp'=>1,'val'=>0),
            //style
            'display_comment_rating'                  => array('exp'=>1,'val'=>1),
            'display_category_title'                  => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'display_category_desc'                   => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'display_category_image'                  => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'display_subcate'                         => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'display_pro_attr'                        => array('exp'=>1,'val'=>0),
            'product_secondary'                       => array('exp'=>1,'val'=>1),
            'show_brand_logo'                         => array('exp'=>1,'val'=>2,'smarty_val'=>1),
            'product_tabs'                            => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'product_tabs_style'                            => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'product_acc_style'                            => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'remove_product_details_tab'                            => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'display_cate_desc_full'                  => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'show_short_desc_on_grid'                 => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'display_color_list'                      => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'pro_list_display_brand_name'             => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'pro_list_display_reference'              => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'pro_display_category_name'               => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'display_pro_tags'                        => array('exp'=>1,'val'=>2,'smarty_val'=>1),
            'product_gallerys'                        => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            // 'pro_gallery_top_per_view'                        => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'product_thumbnails'                        => array('exp'=>1,'val'=>0,'smarty_val'=>1, 'js_val'=>1), //will be modified in the product-cover-thumbnails.tpl
            'product_thumbnails_mobile'                        => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'gallery_thumbnails_width_v'                        => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'gallery_thumbnails_width_vpx'                        => array('exp'=>1,'val'=>0),
            'gallery_thumbnails_height_v'                        => array('exp'=>1,'val'=>0),
            'grid_thumbnails_width'                        => array('exp'=>1,'val'=>0),
            //footer
            'bottom_spacing'                          => array('exp'=>1,'val'=>0),
            'footer_border_color'                     => array('exp'=>1,'val'=>''),
            'footer_border'                           => array('exp'=>1,'val'=>0),
            'second_footer_color'                     => array('exp'=>1,'val'=>''),
            'footer_primary_color'                    => array('exp'=>1,'val'=>''),
            'footer_color'                            => array('exp'=>1,'val'=>''),
            'footer_tertiary_color'                   => array('exp'=>1,'val'=>''),
            'footer_link_primary_color'               => array('exp'=>1,'val'=>''),
            'footer_link_color'                       => array('exp'=>1,'val'=>''),
            'footer_link_tertiary_color'              => array('exp'=>1,'val'=>''),
            'second_footer_link_color'                => array('exp'=>1,'val'=>''),
            'footer_link_primary_hover_color'         => array('exp'=>1,'val'=>''),
            'footer_link_hover_color'                 => array('exp'=>1,'val'=>''),
            'footer_link_tertiary_hover_color'        => array('exp'=>1,'val'=>''),
            'second_footer_link_hover_color'          => array('exp'=>1,'val'=>''),
            'footer_tertiary_border'                  => array('exp'=>1,'val'=>0),
            'footer_tertiary_border_color'            => array('exp'=>1,'val'=>''),
            
            'footer_top_border_color'                 => array('exp'=>1,'val'=>''),
            'footer_top_border'                       => array('exp'=>1,'val'=>0),
            'footer_top_bg'                           => array('exp'=>1,'val'=>''),
            'footer_top_con_bg'                       => array('exp'=>1,'val'=>''),
            "f_top_bg_img"                            => array('exp'=>1,'val'=>''),
            "f_top_bg_fixed"                          => array('exp'=>1,'val'=> 0),
            "f_top_bg_repeat"                         => array('exp'=>1,'val'=>0), 
            "f_top_bg_position"                       => array('exp'=>1,'val'=>0), 
            "f_top_bg_pattern"                        => array('exp'=>1,'val'=>0), 
            'footer_bg_color'                         => array('exp'=>1,'val'=>'#F2F2F2'),
            'footer_con_bg_color'                     => array('exp'=>1,'val'=>''),
            "footer_bg_img"                           => array('exp'=>1,'val'=>''),
            "footer_bg_fixed"                         => array('exp'=>1,'val'=>0),
            "footer_bg_repeat"                        => array('exp'=>1,'val'=>0), 
            "footer_bg_position"                      => array('exp'=>1,'val'=>0), 
            "footer_bg_pattern"                       => array('exp'=>1,'val'=>0), 
            'footer_secondary_bg'                     => array('exp'=>1,'val'=>''),
            'footer_secondary_con_bg'                 => array('exp'=>1,'val'=>''),
            "f_secondary_bg_img"                      => array('exp'=>1,'val'=>''),
            "f_secondary_bg_fixed"                    => array('exp'=>1,'val'=> 0),
            "f_secondary_bg_repeat"                   => array('exp'=>1,'val'=>0), 
            "f_secondary_bg_position"                 => array('exp'=>1,'val'=>0), 
            "f_secondary_bg_pattern"                  => array('exp'=>1,'val'=>0), 
            'footer_info_border_color'                => array('exp'=>1,'val'=>'#DADADA'),
            'footer_info_border'                      => array('exp'=>1,'val'=>11),
            'footer_info_bg'                          => array('exp'=>1,'val'=>''),
            'footer_info_con_bg'                      => array('exp'=>1,'val'=>''),
            "f_info_bg_img"                           => array('exp'=>1,'val'=>''),
            "f_info_bg_fixed"                         => array('exp'=>1,'val'=> 0),
            "f_info_bg_repeat"                        => array('exp'=>1,'val'=>0), 
            "f_info_bg_position"                      => array('exp'=>1,'val'=>0), 
            "f_info_bg_pattern"                       => array('exp'=>1,'val'=>0), 
            //header
            'top_spacing'                             => array('exp'=>1,'val'=>0),
            'header_bottom_spacing'                   => array('exp'=>1,'val'=>12),
            'header_text_color'                       => array('exp'=>1,'val'=>''),
            'topbar_text_color'                       => array('exp'=>1,'val'=>''),
            'header_text_trans'                       => array('exp'=>1,'val'=>1),
            'btn_trans'                               => array('exp'=>1,'val'=>3),
            'header_link_hover_color'                 => array('exp'=>1,'val'=>''),
            'topbar_link_hover_color'                 => array('exp'=>1,'val'=>''),
            'header_link_hover_bg'                    => array('exp'=>1,'val'=>''),
            'dropdown_hover_color'                    => array('exp'=>1,'val'=>''),
            'dropdown_bg_color'                       => array('exp'=>1,'val'=>''),
            "header_topbar_bg"                        => array('exp'=>1,'val'=>''), 
            "topbar_b_border"                         => array('exp'=>1,'val'=>0), 
            "topbar_b_border_color"                   => array('exp'=>1,'val'=>''), 
            //"header_topbar_bc"                      => array('exp'=>1,'val'=>''),
            "header_topbar_sep_type"                  => array('exp'=>1,'val'=>'horizontal-s', 'smarty_val' => 1),
            "header_topbar_sep"                       => array('exp'=>1,'val'=>''),
            'header_bg_color'                         => array('exp'=>1,'val'=>''),
            'header_con_bg_color'                     => array('exp'=>1,'val'=>''),
            "header_bg_img"                           => array('exp'=>1,'val'=>''),
            "header_bg_repeat"                        => array('exp'=>1,'val'=>0), 
            "header_bg_position"                      => array('exp'=>1,'val'=>0), 
            "header_bg_pattern"                       => array('exp'=>1,'val'=>0),   
            "topbar_height"                           => array('exp'=>1,'val'=>0),  
            //body
            "body_bg_color"                           => array('exp'=>1,'val'=>''),
            "body_con_bg_color"                       => array('exp'=>1,'val'=>''),
            "body_bg_img"                             => array('exp'=>1,'val'=>''),
            "body_bg_repeat"                          => array('exp'=>1,'val'=>0), 
            "body_bg_position"                        => array('exp'=>1,'val'=>0), 
            "body_bg_fixed"                           => array('exp'=>1,'val'=>0),
            "body_bg_cover"                           => array('exp'=>1,'val'=>0),
            "body_bg_pattern"                         => array('exp'=>1,'val'=>0), 
            'main_con_bg_color'                       => array('exp'=>1,'val'=>''),
            'base_border_color'                       => array('exp'=>1,'val'=>''),
            'form_bg_color'                           => array('exp'=>1,'val'=>''),
            'pro_grid_bg'                             => array('exp'=>1,'val'=>''),
            'pro_grid_hover_bg'                       => array('exp'=>1,'val'=>'#f2f2f2'),
            'side_panel_bg'                           => array('exp'=>1,'val'=>''),
            'side_panel_heading'                      => array('exp'=>1,'val'=>''),
            'side_panel_heading_bg'                   => array('exp'=>1,'val'=>''),
            'side_panel_heading_border'                   => array('exp'=>1,'val'=>''),
            //crossselling
            'cs_title'                                => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'cs_direction_nav'                        => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'cs_hide_direction_nav_on_mob'            => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'cs_control_nav'                          => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'cs_hide_control_nav_on_mob'              => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'cs_slideshow'                            => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'cs_lazy'                                 => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'cs_s_speed'                              => array('exp'=>1,'val'=>7000,'smarty_val'=>1),
            'cs_a_speed'                              => array('exp'=>1,'val'=>400,'smarty_val'=>1),
            'cs_pause_on_hover'                       => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'cs_loop'                                 => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'cs_move'                                 => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'cs_per_fw'                               => array('exp'=>1,'val'=>7,'smarty_val'=>1),
            'cs_per_xxl'                              => array('exp'=>1,'val'=>6,'smarty_val'=>1),
            'cs_per_xl'                               => array('exp'=>1,'val'=>5,'smarty_val'=>1),
            'cs_per_lg'                               => array('exp'=>1,'val'=>5,'smarty_val'=>1),
            'cs_per_md'                               => array('exp'=>1,'val'=>4,'smarty_val'=>1),
            'cs_per_sm'                               => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'cs_per_xs'                               => array('exp'=>1,'val'=>2,'smarty_val'=>1),
            'cs_spacing_between_fw'                               => array('exp'=>1,'val'=>7,'smarty_val'=>1),
            'cs_spacing_between_xxl'                              => array('exp'=>1,'val'=>6,'smarty_val'=>1),
            'cs_spacing_between_xl'                               => array('exp'=>1,'val'=>5,'smarty_val'=>1),
            'cs_spacing_between_lg'                               => array('exp'=>1,'val'=>5,'smarty_val'=>1),
            'cs_spacing_between_md'                               => array('exp'=>1,'val'=>4,'smarty_val'=>1),
            'cs_spacing_between_sm'                               => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'cs_spacing_between'                      => array('exp'=>1,'val'=>16,'smarty_val'=>1),
            'cs_image_type'                           => array('exp'=>1,'val'=>'','smarty_val'=>1),
            'cs_scrollbar_nav'                        => array('exp'=>1,'val'=>'','smarty_val'=>1),
            //productcategory
            'pc_title'                                => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'pc_direction_nav'                        => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'pc_hide_direction_nav_on_mob'            => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'pc_control_nav'                          => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'pc_hide_control_nav_on_mob'              => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'pc_slideshow'                            => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'pc_lazy'                                 => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'pc_s_speed'                              => array('exp'=>1,'val'=>7000,'smarty_val'=>1),
            'pc_a_speed'                              => array('exp'=>1,'val'=>400,'smarty_val'=>1),
            'pc_pause_on_hover'                       => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'pc_loop'                                 => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'pc_move'                                 => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'pc_per_fw'                               => array('exp'=>1,'val'=>7,'smarty_val'=>1),
            'pc_per_xxl'                              => array('exp'=>1,'val'=>6,'smarty_val'=>1),
            'pc_per_xl'                               => array('exp'=>1,'val'=>5,'smarty_val'=>1),
            'pc_per_lg'                               => array('exp'=>1,'val'=>5,'smarty_val'=>1),
            'pc_per_md'                               => array('exp'=>1,'val'=>4,'smarty_val'=>1),
            'pc_per_sm'                               => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'pc_per_xs'                               => array('exp'=>1,'val'=>2,'smarty_val'=>1),
            'pc_spacing_between'                      => array('exp'=>1,'val'=>16,'smarty_val'=>1),
            'pc_image_type'                           => array('exp'=>1,'val'=>'','smarty_val'=>1),
            'pc_scrollbar_nav'                        => array('exp'=>1,'val'=>'','smarty_val'=>1),
            //accessories
            /*'ac_title'                              => array('exp'=>1,'val'=>0),
            'ac_direction_nav'                        => array('exp'=>1,'val'=>1),
            'ac_control_nav'                          => array('exp'=>1,'val'=>0),
            'ac_slideshow'                            => array('exp'=>1,'val'=>0),
            'ac_lazy'                                 => array('exp'=>1,'val'=>0),
            'ac_s_speed'                              => array('exp'=>1,'val'=>7000),
            'ac_a_speed'                              => array('exp'=>1,'val'=>400),
            'ac_pause_on_hover'                       => array('exp'=>1,'val'=>1),
            'ac_loop'                                 => array('exp'=>1,'val'=>0),
            'ac_move'                                 => array('exp'=>1,'val'=>0),
            'ac_per_xl'                               => array('exp'=>1,'val'=>6),
            'ac_per_lg'                               => array('exp'=>1,'val'=>5),
            'ac_per_md'                               => array('exp'=>1,'val'=>4),
            'ac_per_sm'                               => array('exp'=>1,'val'=>3),
            'ac_per_xs'                               => array('exp'=>1,'val'=>2),
            'ac_per_xxs'                              => array('exp'=>1,'val'=>1),*/
            //color
            'text_color'                              => array('exp'=>1,'val'=>''),
            'link_color'                              => array('exp'=>1,'val'=>''),
            's_title_block_color'                     => array('exp'=>1,'val'=>''),
            'link_hover_color'                        => array('exp'=>1,'val'=>''),
            /*'breadcrumb_color'                        => array('exp'=>1,'val'=>''),
            'breadcrumb_hover_color'                  => array('exp'=>1,'val'=>''),
            'breadcrumb_bg'                           => array('exp'=>1,'val'=>''),
            'breadcrumb_border'                           => array('exp'=>1,'val'=>''),
            'breadcrumb_border_height'                           => array('exp'=>1,'val'=>''),*/
            'price_color'                             => array('exp'=>1,'val'=>''),
            'old_price_color'                         => array('exp'=>1,'val'=>''),
            'hide_discount'                         => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'discount_color'                          => array('exp'=>1,'val'=>''),
            'discount_bg'                             => array('exp'=>1,'val'=>''),
            'icon_color'                              => array('exp'=>1,'val'=>''),
            'icon_hover_color'                        => array('exp'=>1,'val'=>''),
            'icon_bg_color'                           => array('exp'=>1,'val'=>''),
            'icon_hover_bg_color'                     => array('exp'=>1,'val'=>''),
            'icon_disabled_color'                     => array('exp'=>1,'val'=>''),
            'right_panel_border'                      => array('exp'=>1,'val'=>''),
            'starts_color'                            => array('exp'=>1,'val'=>''),
            'circle_number_color'                     => array('exp'=>1,'val'=>''),
            'circle_number_bg'                        => array('exp'=>1,'val'=>''),
            'block_headings_color'                    => array('exp'=>1,'val'=>''),
            'column_block_headings_color'             => array('exp'=>1,'val'=>''),
            'headings_color'                          => array('exp'=>1,'val'=>''),
            'f_top_h_color'                           => array('exp'=>1,'val'=>''),
            'footer_h_color'                          => array('exp'=>1,'val'=>''),
            'f_secondary_h_color'                     => array('exp'=>1,'val'=>''),
            //button
            'btn_color'                               => array('exp'=>1,'val'=>''),
            'btn_hover_color'                         => array('exp'=>1,'val'=>''),
            'btn_bg_color'                            => array('exp'=>1,'val'=>''),
            'btn_hover_bg_color'                      => array('exp'=>1,'val'=>''),
            'btn_border_color'                        => array('exp'=>1,'val'=>''),
            'btn_hover_border_color'                        => array('exp'=>1,'val'=>''),
            'p_btn_color'                           => array('exp'=>1,'val'=>''),
            'p_btn_hover_color'                       => array('exp'=>1,'val'=>''),
            'p_btn_bg_color'                          => array('exp'=>1,'val'=>''),
            'p_btn_hover_bg_color'                    => array('exp'=>1,'val'=>''),
            'btn_fill_animation'                      => array('exp'=>1,'val'=>3),
            //menu
            'menu_color'                              => array('exp'=>1,'val'=>''),
            'menu_bg_color'                           => array('exp'=>1,'val'=>''),
            'top_extra_bg_color'                           => array('exp'=>1,'val'=>''),
            'top_extra_top_spacing'                           => array('exp'=>1,'val'=>''),
            'top_extra_bottom_spacing'                           => array('exp'=>1,'val'=>''),
            'top_extra_bottom_border_color'                           => array('exp'=>1,'val'=>''),
            'top_extra_bottom_border'                           => array('exp'=>1,'val'=>0),
            'menu_hover_color'                        => array('exp'=>1,'val'=>''),
            'menu_hover_bg'                           => array('exp'=>1,'val'=>''),
            'second_menu_color'                       => array('exp'=>1,'val'=>''),
            'second_menu_hover_color'                 => array('exp'=>1,'val'=>''),
            'third_menu_color'                        => array('exp'=>1,'val'=>''),
            'third_menu_hover_color'                  => array('exp'=>1,'val'=>''),
            'menu_mob_items1_color'                   => array('exp'=>1,'val'=>''),
            'menu_mob_items2_color'                   => array('exp'=>1,'val'=>''),
            'menu_mob_items3_color'                   => array('exp'=>1,'val'=>''),
            'menu_mob_items1_bg'                      => array('exp'=>1,'val'=>''),
            'menu_mob_items2_bg'                      => array('exp'=>1,'val'=>''),
            'menu_mob_items3_bg'                      => array('exp'=>1,'val'=>''),
            'menu_multi_bg'                           => array('exp'=>1,'val'=>''),
            'menu_multi_bg_hover'                     => array('exp'=>1,'val'=>''),
            'menu_ver_title_width'                    => array('exp'=>1,'val'=>0),
            'menu_ver_title_align'                    => array('exp'=>1,'val'=>0),
            'menu_ver_title'                          => array('exp'=>1,'val'=>''),
            'menu_ver_open'                           => array('exp'=>1,'val'=> 0),
            'menu_ver_sub_style'                      => array('exp'=>0,'val'=> 0),
            'menu_ver_hover_title'                    => array('exp'=>1,'val'=> ''),
            'menu_ver_bg'                             => array('exp'=>1,'val'=> ''),
            'menu_ver_hover_bg'                       => array('exp'=>1,'val'=> ''),
            'menu_ver_item_color'                     => array('exp'=>1,'val'=> ''),
            'menu_ver_item_hover_color'               => array('exp'=>1,'val'=> ''),
            'menu_ver_item_bg'                        => array('exp'=>1,'val'=> ''),
            'menu_ver_item_hover_bg'                  => array('exp'=>1,'val'=> ''),
            'menu_bottom_border'                      => array('exp'=>1,'val'=>2),
            'menu_bottom_border_color'                => array('exp'=>1,'val'=>'#444444'),
            'menu_bottom_border_hover_color'          => array('exp'=>1,'val'=>'#E54D26'),
            'c_menu_color'                            => array('exp'=>1,'val'=>''),
            'c_menu_bg_color'                         => array('exp'=>1,'val'=>''),
            'c_menu_hover_color'                      => array('exp'=>1,'val'=>''),
            'c_menu_hover_bg'                         => array('exp'=>1,'val'=>''),
            'c_menu_border_color'                     => array('exp'=>1,'val'=>''),
            'c_menu_border_hover_color'               => array('exp'=>1,'val'=>''),
            //sticker
            /*'new_color'                               => array('exp'=>1,'val'=>'#999999'),
            'new_style'                               => array('exp'=>1,'val'=>0),
            'new_border_color'                        => array('exp'=>1,'val'=>'#999999'),
            'new_bg_color'                            => array('exp'=>1,'val'=>'#ffffff'),
            'new_bg_img'                              => array('exp'=>1,'val'=>''),
            'new_stickers_width'                      => array('exp'=>1,'val'=>''),
            'new_stickers_top'                        => array('exp'=>1,'val'=>10),
            'new_stickers_right'                      => array('exp'=>1,'val'=>10),
            'sale_color'                              => array('exp'=>1,'val'=>'#E54D28'),
            'sale_style'                              => array('exp'=>1,'val'=>0),
            'sale_border_color'                       => array('exp'=>1,'val'=>'#E54D28'),
            'sale_bg_color'                           => array('exp'=>1,'val'=>'#ffffff'),
            'sale_bg_img'                             => array('exp'=>1,'val'=>''),
            'sale_stickers_width'                     => array('exp'=>1,'val'=>''),
            'sale_stickers_top'                       => array('exp'=>1,'val'=>10),
            'sale_stickers_left'                      => array('exp'=>1,'val'=>10),
            'discount_percentage'                     => array('exp'=>1,'val'=>1),
            'price_drop_border_color'                 => array('exp'=>1,'val'=>''),
            'price_drop_bg_color'                     => array('exp'=>1,'val'=>''),
            'price_drop_color'                        => array('exp'=>1,'val'=>''),
            'price_drop_bottom'                       => array('exp'=>1,'val'=>30),
            'price_drop_right'                        => array('exp'=>1,'val'=>0),
            'price_drop_width'                        => array('exp'=>1,'val'=>0),*/
            
            /*'sold_out'                                => array('exp'=>1,'val'=>0),
            'sold_out_color'                          => array('exp'=>1,'val'=>''),
            'sold_out_bg_color'                       => array('exp'=>1,'val'=>''),
            'sold_out_bg_img'                         => array('exp'=>1,'val'=>''),*/
            //
            'cart_icon'                               => array('exp'=>1,'val'=>'59496'),
            'wishlist_icon'                           => array('exp'=>1,'val'=>'59616'),
            'love_icon'                               => array('exp'=>1,'val'=>''),
            'compare_icon'                            => array('exp'=>1,'val'=>'59422'),
            'quick_view_icon'                         => array('exp'=>1,'val'=>'59403'),
            'view_icon'                               => array('exp'=>1,'val'=>'59416'),
            'sign_icon'                               => array('exp'=>1,'val'=>'59472'),
            'viewed_icon'                             => array('exp'=>1,'val'=>'59514'),
            'spin_icon'                             => array('exp'=>1,'val'=>''),
            //
            'pro_tab_color'                           => array('exp'=>1,'val'=>''),
            'pro_tab_active_color'                    => array('exp'=>1,'val'=>''),
            'pro_tab_bg'                              => array('exp'=>1,'val'=>''),
            'pro_tab_hover_bg'                        => array('exp'=>1,'val'=>''),
            'pro_tab_border_clolor'                        => array('exp'=>1,'val'=>''),
            'pro_tab_active_bg'                       => array('exp'=>1,'val'=>''),
            'pro_tab_content_bg'                      => array('exp'=>1,'val'=>''),
            //
            'cate_sortby'                             => array('exp'=>1,'val'=> ''),
            'cate_sortby_name'                        => array('exp'=>1,'val'=> ''),
            //
            /*'category_per_xl_3'                 => array('exp'=>1,'val'=>3),
            'category_per_lg_3'                   => array('exp'=>1,'val'=>3),
            'category_per_md_3'                   => array('exp'=>1,'val'=>3),
            'category_per_sm_3'                   => array('exp'=>1,'val'=>2),
            'category_per_xs_3'                   => array('exp'=>1,'val'=>2),
            'category_per_xxs_3'                  => array('exp'=>1,'val'=>1),
            
            'category_per_xl_2'                   => array('exp'=>1,'val'=>4),
            'category_per_lg_2'                   => array('exp'=>1,'val'=>4),
            'category_per_md_2'                   => array('exp'=>1,'val'=>4),
            'category_per_sm_2'                   => array('exp'=>1,'val'=>3),
            'category_per_xs_2'                   => array('exp'=>1,'val'=>2),
            'category_per_xxs_2'                  => array('exp'=>1,'val'=>1),*/
            
            'category_per_fw'                   => array('exp'=>1,'val'=>5,'smarty_val'=>1),
            'category_per_xxl'                  => array('exp'=>1,'val'=>4,'smarty_val'=>1),
            'category_per_xl'                   => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'category_per_lg'                   => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'category_per_md'                   => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'category_per_sm'                   => array('exp'=>1,'val'=>2,'smarty_val'=>1),
            'category_per_xs'                   => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            
            /*'hometab_pro_per_xl'                    => array('exp'=>1,'val'=>4),
            'hometab_pro_per_lg'                      => array('exp'=>1,'val'=>4),
            'hometab_pro_per_md'                      => array('exp'=>1,'val'=>4),
            'hometab_pro_per_sm'                      => array('exp'=>1,'val'=>3),
            'hometab_pro_per_xs'                      => array('exp'=>1,'val'=>2),
            'hometab_pro_per_xxs'                     => array('exp'=>1,'val'=>1),*/
            
            'pro_thumnbs_per_fw'                      => array('exp'=>1,'val'=>2,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_xxl'                     => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_xl'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_lg'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_md'                      => array('exp'=>1,'val'=>3,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_sm'                      => array('exp'=>1,'val'=>2,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_xs'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>2),

            'pro_thumnbs_per_odd_fw'                      => array('exp'=>1,'val'=>0,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_odd_xxl'                     => array('exp'=>1,'val'=>0,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_odd_xl'                      => array('exp'=>1,'val'=>0,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_odd_lg'                      => array('exp'=>1,'val'=>0,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_odd_md'                      => array('exp'=>1,'val'=>0,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_odd_sm'                      => array('exp'=>1,'val'=>0,'smarty_val'=>1,'js_val'=>2),
            'pro_thumnbs_per_odd_xs'                      => array('exp'=>1,'val'=>0,'smarty_val'=>1,'js_val'=>2),

            'pro_kk_per_fw'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            'pro_kk_per_xxl'                     => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            'pro_kk_per_xl'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            'pro_kk_per_lg'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            'pro_kk_per_md'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            'pro_kk_per_sm'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            'pro_kk_per_xs'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),

            'pro_kk_color'                     => array('exp'=>1,'val'=>''),
            'pro_kk_bg'                     => array('exp'=>1,'val'=>''),
            'pro_kk_maxratio'                     => array('exp'=>1,'val'=>'','smarty_val'=>1),
            
            'packitems_pro_per_xl'                    => array('exp'=>1,'val'=>4),
            'packitems_pro_per_lg'                    => array('exp'=>1,'val'=>4),
            'packitems_pro_per_md'                    => array('exp'=>1,'val'=>4),
            'packitems_pro_per_sm'                    => array('exp'=>1,'val'=>3),
            'packitems_pro_per_xs'                    => array('exp'=>1,'val'=>2),
            'packitems_pro_per_xxs'                   => array('exp'=>1,'val'=>1),
            
            'categories_per_fw'                       => array('exp'=>1,'val'=>7,'smarty_val'=>1,'js_val'=>1),
            'categories_per_xxl'                      => array('exp'=>1,'val'=>6,'smarty_val'=>1,'js_val'=>1),
            'categories_per_xl'                       => array('exp'=>1,'val'=>5,'smarty_val'=>1,'js_val'=>1),
            'categories_per_lg'                       => array('exp'=>1,'val'=>5,'smarty_val'=>1,'js_val'=>1),
            'categories_per_md'                       => array('exp'=>1,'val'=>4,'smarty_val'=>1,'js_val'=>1),
            'categories_per_sm'                       => array('exp'=>1,'val'=>3,'smarty_val'=>1,'js_val'=>1),
            'categories_per_xs'                       => array('exp'=>1,'val'=>2,'smarty_val'=>1,'js_val'=>1),
            //1.6
            'category_show_all_btn'                   => array('exp'=>1,'val'=>0),
            'enable_zoom'                             => array('exp'=>1,'val'=>2,'smarty_val'=>1,'js_val'=>2),
            'enable_thickbox'                         => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>2),
            'thumbs_direction_nav'                    => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'pro_main_slider_arrow'                    => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            
            // 'breadcrumb_width'                     => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'megamenu_width'                          => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            //
            'flyout_buttons_color'                    => array('exp'=>1,'val'=>''),
            'flyout_buttons_hover_color'              => array('exp'=>1,'val'=>''),
            'flyout_buttons_bg'                       => array('exp'=>1,'val'=>''),
            'flyout_buttons_hover_bg'                 => array('exp'=>1,'val'=>''),
            'flyout_separators_color'                 => array('exp'=>1,'val'=>''),
            //
            'retina'                                  => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            'yotpo_sart'                              => array('exp'=>1,'val'=>0),   
            'retina_logo'                             => array('exp'=>0,'val'=>''),  
            'mobile_logo'                   => array('exp'=>0,'val'=> ''),  
            'mobile_logo_width'                      => array('exp'=>0,'val'=>0,'smarty_val'=>1),  
            'mobile_logo_height'                      => array('exp'=>0,'val'=>0,'smarty_val'=>1), 
            'navigation_pipe'                         => array('exp'=>1,'val'=>'>','esc'=>1,'smarty_val'=>1),
            'big_next'                                => array('exp'=>1,'val'=>'','smarty_val'=>1),
            'big_next_color'                          => array('exp'=>1,'val'=>''),
            'big_next_hover_color'                    => array('exp'=>1,'val'=>''),
            'big_next_bg'                             => array('exp'=>1,'val'=>''),
            'big_next_hover_bg'                       => array('exp'=>1,'val'=>''),
            'display_add_to_cart'                     => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'mobile_add_to_cart'                      => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            //
            'cart_icon_border_color'                  => array('exp'=>1,'val'=>''),
            'cart_icon_bg_color'                      => array('exp'=>1,'val'=>''),
            'cart_number_color'                       => array('exp'=>1,'val'=>''),
            'cart_number_bg_color'                    => array('exp'=>1,'val'=>''),
            'cart_number_border_color'                => array('exp'=>1,'val'=>''),
            //
            'mob_cart_icon_border_color'              => array('exp'=>1,'val'=>''),
            'mob_cart_icon_bg_color'                  => array('exp'=>1,'val'=>''),
            'mob_cart_number_color'                   => array('exp'=>1,'val'=>''),
            'mob_cart_number_bg_color'                => array('exp'=>1,'val'=>''),
            'mob_cart_number_border_color'            => array('exp'=>1,'val'=>''),
            
            'ps_tr_prev_next_color'                   => array('exp'=>1,'val'=>''),
            'ps_tr_prev_next_color_hover'             => array('exp'=>1,'val'=>''),
            'ps_tr_prev_next_color_disabled'          => array('exp'=>1,'val'=>''),
            'ps_tr_prev_next_bg'                      => array('exp'=>1,'val'=>''),
            'ps_tr_prev_next_bg_hover'                => array('exp'=>1,'val'=>''),
            'ps_tr_prev_next_bg_disabled'             => array('exp'=>1,'val'=>''),
            'ps_lr_prev_next_color'                   => array('exp'=>1,'val'=>''),
            'ps_lr_prev_next_color_hover'             => array('exp'=>1,'val'=>''),
            'ps_lr_prev_next_color_disabled'          => array('exp'=>1,'val'=>''),
            'ps_lr_prev_next_bg'                      => array('exp'=>1,'val'=>''),
            'ps_lr_prev_next_bg_hover'                => array('exp'=>1,'val'=>''),
            'ps_lr_prev_next_bg_disabled'             => array('exp'=>1,'val'=>''),
            'ps_pag_nav_bg'                           => array('exp'=>1,'val'=>''),
            'ps_pag_nav_bg_hover'                     => array('exp'=>1,'val'=>''),
            
            'pagination_color'                        => array('exp'=>1,'val'=>''),
            'pagination_color_hover'                  => array('exp'=>1,'val'=>''),
            'pagination_color_disabled'               => array('exp'=>1,'val'=>''),
            'pagination_bg'                           => array('exp'=>1,'val'=>''),
            'pagination_bg_hover'                     => array('exp'=>1,'val'=>''),
            'pagination_bg_disabled'                  => array('exp'=>1,'val'=>''),
            
            'display_pro_condition'                   => array('exp'=>1,'val'=>0, 'smarty_val'=>1),
            'display_pro_reference'                   => array('exp'=>1,'val'=>1, 'smarty_val'=>1),
            
            'pro_border_size'                         => array('exp'=>1,'val'=>0),
            'pro_border_color'                        => array('exp'=>1,'val'=>''),
            'pro_border_color_hover'                       => array('exp'=>1,'val'=>''),

            'pro_shadow_effect'                       => array('exp'=>1,'val'=>0),
            'pro_h_shadow'                            => array('exp'=>1,'val'=>0),
            'pro_v_shadow'                            => array('exp'=>1,'val'=>0),
            'pro_shadow_blur'                         => array('exp'=>1,'val'=>4),
            'pro_shadow_color'                        => array('exp'=>1,'val'=>'#000000'),
            'pro_shadow_opacity'                      => array('exp'=>1,'val'=>0.1),
            
            'menu_title'                              => array('exp'=>1,'val'=>0),
            'display_love'                            => array('exp'=>1,'val'=>0, 'smarty_val'=>1),
            'flyout_wishlist'                         => array('exp'=>1,'val'=>0, 'smarty_val'=>1),
            'flyout_quickview'                        => array('exp'=>1,'val'=>1, 'smarty_val'=>1),
            'flyout_comparison'                       => array('exp'=>1,'val'=>0),
            'flyout_share'                            => array('exp'=>1,'val'=>1, 'smarty_val'=>1),
            
            'sticky_bg'                               => array('exp'=>1,'val'=>''),
            'sticky_opacity'                          => array('exp'=>1,'val'=>0.95),
            'transparent_header_bg'                   => array('exp'=>1,'val'=>''),
            'transparent_header_opacity'              => array('exp'=>1,'val'=>0.4),
            'transparent_mobile_header'               => array('exp'=>1,'val'=> 0, 'smarty_val'=>1),
            'transparent_mobile_header_color'         => array('exp'=>1,'val'=>''),
            'transparent_mobile_header_bg'            => array('exp'=>1,'val'=>''),
            'transparent_mobile_header_opacity'       => array('exp'=>1,'val'=>0.4),
            
            'pro_lr_prev_next_color'                  => array('exp'=>1,'val'=>''),
            'pro_lr_prev_next_color_hover'            => array('exp'=>1,'val'=>''),
            'pro_lr_prev_next_color_disabled'         => array('exp'=>1,'val'=>''),
            'pro_lr_prev_next_bg'                     => array('exp'=>1,'val'=>''),
            'pro_lr_prev_next_bg_hover'               => array('exp'=>1,'val'=>''),
            'pro_lr_prev_next_bg_disabled'            => array('exp'=>1,'val'=>''),
            'pro_lr_pag_nav_bg'                       => array('exp'=>1,'val'=>''),
            'pro_lr_pag_nav_bg_hover'                 => array('exp'=>1,'val'=>''),
            
            'fullwidth_topbar'                        => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'fullwidth_header'                        => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            
            'header_bottom_border_color'              => array('exp'=>1,'val'=>''),
            'header_bottom_border'                    => array('exp'=>1,'val'=>0),
            'use_view_more_instead'                   => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            
            'sticky_mobile_header'                    => array('exp'=>1,'val'=>2,'js_val'=>1,'smarty_val'=>1),
            'sticky_mobile_header_height'             => array('exp'=>1,'val'=>0,'js_val'=>1),
            'sticky_mobile_header_color'              => array('exp'=>1,'val'=>''),
            'sticky_mobile_header_text_bg'            => array('exp'=>1,'val'=>''),
            'sticky_mobile_header_background'         => array('exp'=>1,'val'=>''),
            'sticky_mobile_header_background_opacity' => array('exp'=>1,'val'=>0.95),
            'use_mobile_header'                       => array('exp'=>1,'val'=>1,'smarty_val'=>1,'js_val'=>1),
            
            'boxed_shadow_effect'                     => array('exp'=>1,'val'=>1),
            'boxed_h_shadow'                          => array('exp'=>1,'val'=>0),
            'boxed_v_shadow'                          => array('exp'=>1,'val'=>0),
            'boxed_shadow_blur'                       => array('exp'=>1,'val'=>3),
            'boxed_shadow_color'                      => array('exp'=>1,'val'=>'#000000'),
            'boxed_shadow_opacity'                    => array('exp'=>1,'val'=>0.1),
            
            'slide_lr_column'                         => array('exp'=>1,'val'=>1,'smarty_val'=>1),
            'pro_image_column_md'                     => array('exp'=>1,'val'=>4,'smarty_val'=>1,'js_val'=>1),
            'pro_primary_column_md'                   => array('exp'=>1,'val'=>5,'smarty_val'=>1),
            'pro_secondary_column_md'                 => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'pro_image_column_sm'                     => array('exp'=>1,'val'=>4,'smarty_val'=>1),
            'pro_primary_column_sm'                   => array('exp'=>1,'val'=>5,'smarty_val'=>1),
            'pro_secondary_column_sm'                 => array('exp'=>1,'val'=>3,'smarty_val'=>1),
            'pro_desc_secondary_column_sm'                 => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'pro_desc_secondary_column_md'                 => array('exp'=>1,'val'=>0,'smarty_val'=>1),
            'custom_fonts'                            => array('exp'=>1,'val'=>''),
            
            'submemus_animation'                      => array('exp'=>1,'val'=>0,'js_val'=>2),
            'submemus_action'                      => array('exp'=>0,'val'=>0,'js_val'=>2),
            
            'primary_btn_color'                       => array('exp'=>1,'val'=>''),
            'primary_btn_hover_color'                 => array('exp'=>1,'val'=>''),
            'primary_btn_bg_color'                    => array('exp'=>1,'val'=>''),
            'primary_btn_hover_bg_color'              => array('exp'=>1,'val'=>''),
            'primary_btn_hover_border_color'              => array('exp'=>1,'val'=>''),
            'primary_btn_border_color'                => array('exp'=>1,'val'=>''),     
            
            'menu_icon_with_text'                     => array('exp'=>1,'val'=>0, 'smarty_val' => 1),        
            'pro_img_hover_scale'                     => array('exp'=>1,'val'=>0, 'smarty_val' => 1),        
            'pro_show_print_btn'                      => array('exp'=>1,'val'=>0),        
            'pro_main_image_trans'                    => array('exp'=>1,'val'=>0, 'smarty_val' => 1),   
            
            "font_product_name"                       => array('exp'=>1,'val'=>''),
            "font_product_name_trans"                 => array('exp'=>1,'val'=>1),
            "font_product_name_size"                  => array('exp'=>1,'val'=>0),     
            "font_product_name_color"                 => array('exp'=>1,'val'=>0), 
            
            "f_top_fullwidth"                         => array('exp'=>1,'val'=>0, 'smarty_val'=>1),     
            "footer_fullwidth"                        => array('exp'=>1,'val'=>0, 'smarty_val'=>1),     
            "f_secondary_fullwidth"                   => array('exp'=>1,'val'=>0, 'smarty_val'=>1),     
            "f_info_fullwidth"                        => array('exp'=>1,'val'=>0, 'smarty_val'=>1),     
            "f_info_center"                           => array('exp'=>1,'val'=>0, 'smarty_val' => 1),     
            
            "f_top_h_align"                           => array('exp'=>1,'val'=>0),  
            "footer_h_align"                          => array('exp'=>1,'val'=>0),  
            "f_secondary_h_align"                     => array('exp'=>1,'val'=>0),
            'pro_quantity_input'                      => array('exp'=>1,'val'=>2, 'smarty_val' => 1,'js_val'=>1),
            'sticky_header_height'                    => array('exp'=>1,'val'=>0),
            
            'left_column_size_xl'                     => array('exp'=>1,'val'=>3, 'smarty_val' => 2),
            'left_column_size_lg'                     => array('exp'=>1,'val'=>3, 'smarty_val' => 2),
            'left_column_size_md'                     => array('exp'=>1,'val'=>3, 'smarty_val' => 2),
            'left_column_size_sm'                     => array('exp'=>1,'val'=>4, 'smarty_val' => 2),
            'left_column_size_xs'                     => array('exp'=>1,'val'=>6, 'smarty_val' => 2),
            'left_column_size_xxs'                     => array('exp'=>1,'val'=>8, 'smarty_val' => 2),
            'right_column_size_xl'                    => array('exp'=>1,'val'=>3, 'smarty_val' => 2),
            'right_column_size_lg'                    => array('exp'=>1,'val'=>3, 'smarty_val' => 2),
            'right_column_size_md'                    => array('exp'=>1,'val'=>3, 'smarty_val' => 2),
            'right_column_size_sm'                     => array('exp'=>1,'val'=>4, 'smarty_val' => 2),
            'right_column_size_xs'                     => array('exp'=>1,'val'=>6, 'smarty_val' => 2),
            'right_column_size_xxs'                     => array('exp'=>1,'val'=>8, 'smarty_val' => 2),
            
            'header_left_alignment'                   => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'header_center_alignment'                 => array('exp'=>1,'val'=>1, 'smarty_val' => 1),
            'header_right_alignment'                  => array('exp'=>1,'val'=>2, 'smarty_val' => 1),
            'header_right_bottom_alignment'           => array('exp'=>1,'val'=>2, 'smarty_val' => 1),
            
            'sticky_shadow_blur'                      => array('exp'=>1,'val'=>4),
            'sticky_shadow_color'                     => array('exp'=>1,'val'=>'#000000'),
            'sticky_shadow_opacity'                   => array('exp'=>1,'val'=>0.1),

            'quarter_1'                                 => array('exp'=>1,'val'=>3,'smarty_val' => 1),
            'quarter_2'                                 => array('exp'=>1,'val'=>3,'smarty_val' => 1),
            'quarter_3'                                 => array('exp'=>1,'val'=>3,'smarty_val' => 1),
            'quarter_4'                                 => array('exp'=>1,'val'=>3,'smarty_val' => 1),

            'stacked_footer_column_1'                   => array('exp'=>1,'val'=>3,'smarty_val' => 1),
            'stacked_footer_column_2'                   => array('exp'=>1,'val'=>3,'smarty_val' => 1),
            'stacked_footer_column_3'                   => array('exp'=>1,'val'=>3,'smarty_val' => 1),
            'stacked_footer_column_4'                   => array('exp'=>1,'val'=>3,'smarty_val' => 1),
            'stacked_footer_column_5'                   => array('exp'=>1,'val'=>0,'smarty_val' => 1),
            'stacked_footer_column_6'                   => array('exp'=>1,'val'=>0,'smarty_val' => 1),

            'popup_vertical_fit'                        => array('exp'=>1,'val'=>0, 'js_val' => 1),
            // 'pro_popup_vertical_fit'                  => array('exp'=>1,'val'=>1, 'smarty_val' => 1),

            'pro_tm_slider'                             => array('exp'=>1,'val'=>0, 'smarty_val' => 1, 'js_val' => 1),
            'pro_tm_slider_cate'                        => array('exp'=>1,'val'=>0, 'smarty_val' => 1, 'js_val' => 1),
            'product_buy'                               => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'product_buy_button'                        => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'buy_now'                        => array('exp'=>1,'val'=>0, 'smarty_val' => 1, 'js_val' => 1),
            'pro_block_align'                           => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'product_page_layout'                           => array('exp'=>1,'val'=>0, 'smarty_val' => 1),

            'sticky_filter_bg'                          => array('exp'=>1,'val'=>''),
            'sticky_filter_bg_opacity'                  => array('exp'=>1,'val'=>0.95),

            'pro_page_first_full_screen'                => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'pro_page_second_full_screen'                => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'pro_page_third_full_screen'                => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'pro_page_first_bg'                         => array('exp'=>1,'val'=>''),
            'pro_page_second_bg'                        => array('exp'=>1,'val'=>''),
            'cate_pro_image_type'                       => array('exp'=>0,'val'=>''),
            'gallery_image_type'                        => array('exp'=>0,'val'=>'medium_default'),
            'thumb_image_type'                          => array('exp'=>0,'val'=>'cart_default'),
            'gallery_spacing'                           => array('exp'=>1,'val'=>20, 'smarty_val' => 1),
            //
            'auth_padding_top'                          => array('exp'=>1,'val'=>38),
            'auth_padding_bottom'                       => array('exp'=>1,'val'=>38),
            'auth_layout'                               => array('exp'=>1,'val'=>1, 'smarty_val' => 1),
            'auth_bg_color'                             => array('exp'=>1,'val'=>''),
            'auth_con_bg_color'                         => array('exp'=>1,'val'=>''),
            'auth_bg_img'                               => array('exp'=>1,'val'=>''),
            'auth_bg_repeat'                            => array('exp'=>1,'val'=>0), 
            'auth_bg_position'                          => array('exp'=>1,'val'=>0), 
            'auth_bg_pattern'                           => array('exp'=>1,'val'=>0), 
            'auth_btn_color'                            => array('exp'=>1,'val'=>''),
            'auth_btn_hover_color'                      => array('exp'=>1,'val'=>''),
            'auth_btn_bg_color'                         => array('exp'=>1,'val'=>''),
            'auth_btn_hover_bg_color'                   => array('exp'=>1,'val'=>''),
            'auth_heading_align'                        => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'auth_heading'                              => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'auth_heading_color'                        => array('exp'=>1,'val'=>''),
            'auth_heading_bg'                           => array('exp'=>1,'val'=>''),
            'auth_login_width'                          => array('exp'=>1,'val'=>6, 'smarty_val' => 1),

            'checkout_bg'                               => array('exp'=>1,'val'=>''),
            'checkout_con_bg'                           => array('exp'=>1,'val'=>''),
            'cms_font_size'                             => array('exp'=>1,'val'=>''),
            'cms_title'                                 => array('exp'=>1,'val'=>0, 'smarty_val' => 1),

            'remove_products_variable'                  => array('exp'=>0,'val'=>0),
            'hide_gender'                  => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'checkout_same_header'                  => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'checkout_same_footer'                  => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'main_menu_spacing_lg'                     => array('exp'=>1,'val'=>0),
            'main_menu_spacing_md'                     => array('exp'=>1,'val'=>0),
            'cms_h1_size'                     => array('exp'=>0,'val'=>0),
            'cms_h2_size'                     => array('exp'=>0,'val'=>0),
            'cms_h3_size'                     => array('exp'=>0,'val'=>0),

            'pro_available_color'                     => array('exp'=>1,'val'=>''),
            'pro_unavailable_color'                     => array('exp'=>1,'val'=>''),
            'pro_last_items'                     => array('exp'=>1,'val'=>''),

            'font_main_price_size'                     => array('exp'=>1,'val'=>0),
            'btn_font_size'                     => array('exp'=>1,'val'=>0),
            'flyout_font_size'                     => array('exp'=>1,'val'=>0),
            'drop_down'                     => array('exp'=>1,'val'=>0, 'smarty_val' => 1),
            'lazyload_main_gallery'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1, 'js_val' => 1),
            'brand_page_image'                     => array('exp'=>0,'val'=>1, 'smarty_val' => 1),
            'brand_page_short_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'brand_page_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'supplier_page_image'                     => array('exp'=>0,'val'=>1, 'smarty_val' => 1),
            'supplier_page_short_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'supplier_page_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'second_price_total'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'product_views'                     => array('exp'=>0,'val'=>3, 'smarty_val' => 1, 'js_val' => 1),
            'clear_list_view'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'list_view_align'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'list_view_proportion'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'truncate_cate_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 2),
            'showless_cate_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 2),
            'truncate_cate_desc_height_mobile'                     => array('exp'=>0,'val'=>0),
            'truncate_cate_desc_height_desktop'                     => array('exp'=>0,'val'=>0),
            'truncate_short_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 2),
            'showless_short_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 2),
            'truncate_short_desc_height_mobile'                     => array('exp'=>0,'val'=>0),
            'truncate_short_desc_height_desktop'                     => array('exp'=>0,'val'=>0),
            'truncate_pro_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 2),
            'showless_pro_desc'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 2),
            'truncate_pro_desc_height_mobile'                     => array('exp'=>0,'val'=>0),
            'truncate_pro_desc_height_desktop'                     => array('exp'=>0,'val'=>0),
            'pagination_jump_to'                     => array('exp'=>0,'val'=>0),
            'product_name_at_top'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'infinite_blank'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 1),
            'hide_header'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 2),
            'enable_number_per_page'                     => array('exp'=>0,'val'=>0),
            'number_per_page'                     => array('exp'=>0,'val'=>'20,40,60,10000'),
            'transparent_header_text'                     => array('exp'=>0,'val'=>''),
            'product_gallery_fullscreen_mobile'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 2),
            //page loop
            'pro_main_image_loop'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 0),
            'product_summary_location'                     => array('exp'=>0,'val'=>0, 'smarty_val' => 0),

            'show_hide_add_to_cart'                     => array('exp'=>1,'val'=>0,'smarty_val'=>0),//20220309
            "product_reference_code_color"                              => array('exp'=>1,'val'=>''),
            "product_reference_code_font"                         => array('exp'=>1,'val'=>''),
            "product_reference_code_font_size"                         => array('exp'=>1,'val'=>0),

            "product_available_font"                         => array('exp'=>1,'val'=>''),//20220314
            "product_available_font_size"                         => array('exp'=>1,'val'=>''),
            "pro_available_color_bg"                         => array('exp'=>1,'val'=>''),
            "pro_unavailable_color_bg"                         => array('exp'=>1,'val'=>''),
            "pro_last_items_bg"                         => array('exp'=>1,'val'=>''),

            'cs_reverse_direction'                       => array('exp'=>1,'val'=>0,'smarty_val'=>0),
            'pc_reverse_direction'                       => array('exp'=>1,'val'=>0,'smarty_val'=>0),
            'cs_pause_on_enter'                       => array('exp'=>1,'val'=>0,'smarty_val'=>0),
            'pc_pause_on_enter'                       => array('exp'=>1,'val'=>0,'smarty_val'=>0),

        );
        
        $this->_hooks = array(
            array('displayNav2','Navigation','Left side of navigation',1),
            array('displayNav3','Navigation','Center of navigation',1),
            array('displayCategoryFooter','displayCategoryFooter','Display some specific informations on the category page',1),
            array('displayCategoryHeader','displayCategoryHeader','Display some specific informations on the category page',1),
            array('displayMainMenu','displayMainMenu','MainMenu',1),
            array('displayProductLeftColumn','displayProductLeftColumn','Product left column',1),
            array('displayProductCenterColumn','displayProductCenterColumn','Product center column',1),
            array('displayProductRightColumn','displayProductRightColumn','Product right column',1),

            array('displayProductNameRight','displayProductNameRight','Right side of product name',1),
            array('displayUnderProductName','displayUnderProductName','Under product name',1),
            array('displayProductPriceRight','displayProductPriceRight','Right side of price',1),
            array('displayProductCartRight','displayProductCartRight','Right side of cart',1),

            /*array('displayFooterPrimary','displayFooterPrimary','Footer primary',1),
            array('displayFooterTertiary','displayFooterTertiary','Footer tertiary',1),*/

            array('displayStackedFooter1','displayStackedFooter1','Stacked Footer 1',1),
            array('displayStackedFooter2','displayStackedFooter2','Stacked Footer 2',1),
            array('displayStackedFooter3','displayStackedFooter3','Stacked Footer 3',1),
            array('displayStackedFooter4','displayStackedFooter4','Stacked Footer 4',1),
            array('displayStackedFooter5','displayStackedFooter5','Stacked Footer 5',1),
            array('displayStackedFooter6','displayStackedFooter6','Stacked Footer 6',1),

            array('displayModuleCustomContent','displayModuleCustomContent','Put custom content to other modules',1),
            array('displayCMSExtra','displayCMSExtra','Put custom content to cms pages',1),

            array('displayFooterBottomRight','displayFooterBottomRight','Footer bottom right',1),
            array('displayFooterBottomLeft','displayFooterBottomLeft','Footer bottom left',1),
            array('displayHomeLeft','displayHomeLeft','Home left',1),
            array('displayHomeRight','displayHomeRight','Home right',1),
            array('displayHomeTop','displayHomeTop','Home page top',1),
            array('displayHomeBottom','displayHomeBottom','Hom epage bottom',1),
            array('displayHeaderLeft','displayHeaderLeft','Left-hand side of the header',1),
            array('displayManufacturerHeader','displayManufacturerHeader','Display some specific informations on the manufacturer page',1),
            array('displayHeaderCenter','displayHeaderCenter','Header center',1),
            array('displayRightBar','displayRightBar','Right bar',1),
            array('displayLeftBar','displayLeftBar','Left bar',1),
            array('displaySideBar','displaySideBar','Side bar',1),
            // array('displayBottomColumn','displayBottomColumn','Bottom column',1),
            array('displayFullWidthTop','displayFullWidthTop','Full width top',1),
            array('displayFullWidthBottom','displayFullWidthBottom','Full width bottom',1),
            array('displayHomeFirstQuarter','displayHomeFirstQuarter','Home page first quarter',1),
            array('displayHomeSecondQuarter','displayHomeSecondQuarter','Home page second quarter',1),
            array('displayHomeThirdQuarter','displayHomeThirdQuarter','Home page third quarter',1),
            array('displayHomeFourthQuarter','displayHomeFourthQuarter','Home page fourth quarter',1),
            array('displayHeaderBottom','displayHeaderBottom','Header bottom',1),
            array('displayMobileBar','displayMobileBar','Mobile bar',1),
            array('displayMobileBarCenter','displayMobileBarCenter','Mobile bar center',1),
            array('displayMobileBarLeft','displayMobileBarLeft','Mobile bar left',1),
            array('actionObjectStBlogClassAddAfter','actionObjectStBlogClassAddAfter','Blog add',1),
            array('actionObjectStBlogClassUpdateAfter','actionObjectStBlogClassUpdateAfter','Blog update',1),
            array('actionObjectStBlogClassDeleteAfter','actionObjectStBlogClassDeleteAfter','Blog delete',1),
            array('actionAdminStBlogFormModifier','actionAdminStBlogFormModifier','Blog form',1),
            array('displayFullWidthTop2','displayFullWidthTop2','Full width top 2',1),
            array('displayMobileNav','displayMobileNav','Mobile menu',1),
            array('displayMainMenuWidget','displayMainMenuWidget','Menu widgets',1),
            array('displayComingSoon','displayComingSoon','Coming soon page',1),
            array('displaySlogan1','displaySlogan1','Slogan beside the logo',1),
            array('displaySlogan2','displaySlogan2','Slogan under the logo',1),

            array('displayCheckoutHeader','displayCheckoutHeader','Checkout page header',1),
            array('displayCheckoutMobileNav','displayCheckoutMobileNav','Checkout page mobile header',1),
            array('displayAboveContactForm','displayAboveContactForm','At the top of contact form',1),
            array('displayContactFormRight','displayContactFormRight','At the right side of contact form',1),
            array('displayBelowContactForm','displayBelowContactForm','At the bottom of contact form',1),

            array('displayMiddleProduct','displayMiddleProduct','Between product main section and product description tab',1),

            array('displayFullWidthCategoryHeader','displayFullWidthCategoryHeader','Display some specific informations on the category page',1),
            array('displayFullWidthCategoryFooter','displayFullWidthCategoryFooter','Display some specific informations on the category page',1),
            
            array('displayUnderProductVariants','displayUnderProductVariants','Under the product variants block',1),
            array('displayProductDescRightColumn','displayProductDescRightColumn','Right side of prouct description',1),
        );
	}
	
	public function install()
	{
	    if ( $this->_addHook() &&
            parent::install() && 
            $this->registerHook('header') && 
            $this->registerHook('actionStAssemble') &&
            $this->registerHook('actionShopDataDuplication') &&
            $this->registerHook('displayRightColumnProduct') &&
            $this->registerHook('actionProductSearchAfter') &&
            $this->_useDefault()
        ){
            if ($id_hook = Hook::getIdByName('displayHeader'))
                $this->updatePosition($id_hook, 0, 1);
            // $this->add_quick_access();
            // $this->clear_class_index();
            Configuration::updateValue('jscomposer_status', '1');
            return true;
        }
        return false;
	}
	
    private function _addHook()
	{
        $res = true;
        foreach($this->_hooks as $v)
        {
            if(!$res)
                break;
            if (!Validate::isHookName($v[0]))
                continue;
                
            $id_hook = Hook::getIdByName($v[0]);
    		if (!$id_hook)
    		{
    			$new_hook = new Hook();
    			$new_hook->name = pSQL($v[0]);
    			$new_hook->title = pSQL($v[1]);
    			$new_hook->description = pSQL($v[2]);
    			$new_hook->position = pSQL($v[3]);
    			$new_hook->add();
    			$id_hook = $new_hook->id;
    			if (!$id_hook)
    				$res = false;
    		}
            else
            {
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'hook` set `title`="'.$v[1].'", `description`="'.$v[2].'", `position`="'.$v[3].'" where `id_hook`='.$id_hook);
            }
        }
		return $res;
	}

	private function _removeHook()
	{
	    $sql = 'DELETE FROM `'._DB_PREFIX_.'hook` WHERE ';
        foreach($this->_hooks as $v)
            $sql .= ' `name` = "'.$v[0].'" OR';
		return Db::getInstance()->execute(rtrim($sql,'OR').';');
	}
    
	public function uninstall()
	{
	    if(!parent::uninstall() ||
            !$this->_deleteConfiguration()
        )
			return false;
		return true;
	}
    
    private function _deleteConfiguration()
    {
        $res = true;
        foreach($this->defaults as $k=>$v)
            $res &= Configuration::deleteByName('STSN_'.strtoupper($k));
        return $res;
    }
	
    private function _useDefault($html = false, $id_shop_group = null, $id_shop = null)
    {
        $res = true;
        foreach($this->defaults as $k=>$v)
		    $res &= Configuration::updateValue('STSN_'.strtoupper($k), $v['val'], $html, $id_shop_group, $id_shop);
        return $res;
    }
    private function _usePredefinedStore($store = '', $file = '')
    {
        $res = true;
        
        if(!$store && !$file)
            return false;
        
        if ($file)
            $config_file = $this->_config_folder.$file;
        else
            $config_file = $this->_config_folder.'predefined_'.$store.'.xml';
        if (!file_exists($config_file))
            return $this->displayError('"'.$config_file.'"'.$this->getTranslator()->trans(' file isn\'t exists.', array(), 'Modules.Stthemeeditor.Admin'));
        
        $xml = @simplexml_load_file($config_file);
        
        if ($xml === false)
            return $this->displayError($this->getTranslator()->trans('Fetch configuration file content failed', array(), 'Modules.Stthemeeditor.Admin'));
        
        $languages = Language::getLanguages(false);
        
        $module_data = array();
                
        foreach($xml->children() as $k => $v)
        {
            if ($k == 'module_data' && (string)$v) {
                $module_data = unserialize(base64_decode((string)$v));
            }
            if (!key_exists($k, $this->defaults))
                continue;
            if (in_array($k, $this->lang_array))
            {
                $text_lang = array();
                $default = '';
                foreach($xml->$k->children() AS $_k => $_v)
                {
                    $id_lang = str_replace('lang_', '', $_k);
                    $text_lang[$id_lang] = (string)$_v;
                    if (!$default)
                        $default = $text_lang[$id_lang];
                }
                foreach($languages AS $language)
                    if (!key_exists($language['id_lang'], $text_lang))
                        $text_lang[$language['id_lang']] = $default;
                
                $this->defaults[$k]['val'] = $text_lang;
            }
            else
                $this->defaults[$k]['val'] = (string)$v;
        }
        foreach($this->defaults as $k=>$v)
		    $res &= Configuration::updateValue('STSN_'.strtoupper($k), $v['val']);
        
        // Import module data.
        if ($module_data && !Tools::getValue('color_only')) {
            include_once(dirname(__FILE__).'/classes/DemoStore.php');
            $demo = new DemoStore($module_data, $this->context);
            $demo->import_modules();    
        }
        
        if($res)
        {
            $this->writeCss();
            Tools::clearSmartyCache();
        }
        return $res;
    }
    public function uploadCheckAndGetName($name)
    {
		$type = strtolower(substr(strrchr($name, '.'), 1));
        if(!in_array($type, $this->imgtype))
            return false;
        $filename = Tools::encrypt($name.sha1(microtime()));
		while (file_exists(_PS_UPLOAD_DIR_.$this->name.'/'.$filename.'.'.$type)) {
            $filename .= rand(10, 99);
        } 
        return $filename.'.'.$type;
    }
    private function _checkImageDir($dir)
    {
        $result = '';
        if (!file_exists($dir))
        {
            $success = @mkdir($dir, self::$access_rights, true)
						|| @chmod($dir, self::$access_rights);
            if(!$success)
                $result = $this->displayError('"'.$dir.'" '.$this->getTranslator()->trans('An error occurred during new folder creation', array(), 'Admin.Theme.Panda'));
        }

        if (!is_writable($dir))
            $result = $this->displayError('"'.$dir.'" '.$this->getTranslator()->trans('directory isn\'t writable.', array(), 'Admin.Theme.Panda'));
        
        return $result;
    }
    	
	public function getContent()
	{
        require_once(_PS_MODULE_DIR_.$this->name.'/classes/ThemeLicense.php');
        $license = ThemeLicense::getInstance($this);
        $this->theme_valid = $license->GoumaimaIsValid();
        Media::addJsDef(array(
            'module_name' => $this->name,
            'st_upgrade_warning_1' => $this->getTranslator()->trans('Warning: If you have modified any theme files directly, you will lose your modifications, you need to re-do them after upgrade. Do you want to continue ?', array(), 'Modules.Stthemeeditor.Admin'),
            'st_upgrade_warning_2' => $this->getTranslator()->trans('Have you made a full backup your site including your site files and your database? Click the [Yes] button to continue.', array(), 'Modules.Stthemeeditor.Admin'),
            'st_upgrade_step_0' => $this->getTranslator()->trans('Step 1: check update environment', array(), 'Modules.Stthemeeditor.Admin'),
        ));
		$this->context->controller->addCSS(($this->_path).'views/css/admin.css');
        $this->context->controller->addJS(($this->_path).'views/js/admin.js');
        $this->_html .= '<script type="text/javascript">var stthemeeditor_base_uri = "'.__PS_BASE_URI__.'";var stthemeeditor_refer = "'.(int)Tools::getValue('ref').'";var systemFonts = \''.implode(',',$this->systemFonts).'\'; var googleFontsString=\''.json_encode($this->googleFonts).'\';</script>';
        $this->forceUpgradeConfiguration();
        if (Tools::isSubmit('ajax_products_list'))
        {
            $this->getAjaxProductsList();
        }
        if (Tools::isSubmit('resetstthemeeditor'))
        {
            $this->_useDefault();
            $this->writeCss();
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
        }
        if (Tools::isSubmit('exportstthemeeditor'))
        {
            $this->_html .= $this->export();
        }
        if (Tools::isSubmit('downloadstthemeeditor'))
        {
            $file = Tools::getValue('file');
            if (file_exists($this->_config_folder.$file))
            {
                if (ob_get_length() > 0)
					ob_end_clean();

				ob_start();
				header('Pragma: public');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Cache-Control: public');
				header('Content-Description: File Transfer');
				header('Content-type:text/xml');
				header('Content-Disposition: attachment; filename="'.$file.'"');
				ob_end_flush();
				readfile($this->_config_folder.$file);
				exit;
            }
        }
        if (Tools::isSubmit('uploadstthemeeditor'))
        {
            if (isset($_FILES['xml_config_file_field']) && $_FILES['xml_config_file_field']['tmp_name'] && !$_FILES['xml_config_file_field']['error'])
            {
                $error = '';
                $folder = $this->_config_folder;
                if (!is_dir($folder))
                    $error = $this->displayError('"'.$folder.'" '.$this->getTranslator()->trans('directory isn\'t exists.', array(), 'Admin.Theme.Panda'));
                elseif (!is_writable($folder))
                    $error = $this->displayError('"'.$folder.'" '.$this->getTranslator()->trans('directory isn\'t writable.', array(), 'Admin.Theme.Panda'));
                
                $file = date('YmdHis').'_'.(int)Shop::getContextShopID().'.xml';
                if (!move_uploaded_file($_FILES['xml_config_file_field']['tmp_name'], $folder.$file))
                    $error = $this->displayError($this->getTranslator()->trans('Upload config file failed.', array(), 'Modules.Stthemeeditor.Admin'));
                else
                {
                    $res = $this->_usePredefinedStore('', $file);
                    if ($res !== 1)
                        $this->_html .= $res;
                    else
                        $this->_html .= $this->displayConfirmation($this->getTranslator()->trans('Imported data success.', array(), 'Admin.Theme.Panda'));
                }   
            }
        }
        if (Tools::isSubmit('predefineddemostorestthemeeditor') && Tools::getValue('predefineddemostorestthemeeditor'))
        {
            $res = $this->_usePredefinedStore(Tools::getValue('predefineddemostorestthemeeditor'));
            if ($res !== 1)
                $this->_html .= $this->displayError($this->getTranslator()->trans('Error occurred while import configuration:', array(), 'Modules.Stthemeeditor.Admin')).$res;
            else
            {
                $this->writeCss();
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&conf=4&token='.Tools::getAdminTokenLite('AdminModules'));    
            }
        }
        if(Tools::getValue('act')=='delete_image' && $field=Tools::getValue('field'))
        {
            $field = strtoupper($field);
            $themeeditor = new StThemeEditor();
            /*20140920
            $image  = Configuration::get('STSN_'.$field);
        	if (Configuration::get('STSN_'.$field))
                if (file_exists(_PS_UPLOAD_DIR_.$image))
                    @unlink(_PS_UPLOAD_DIR_.$image);
                elseif(file_exists(_PS_MODULE_DIR_.'stthemeeditor/'.$image) && strpos($image, $field) === false)
                    @unlink(_PS_MODULE_DIR_.'stthemeeditor/'.$image);
            */
        	Configuration::updateValue('STSN_'.$field, '');
            $themeeditor->writeCss();
            $result['r'] = true;
            die(json_encode($result));
        }
        if(Tools::getValue('act') == 'update_theme' && Tools::getValue('ajax') == 1)
        {
            $title_tpl = '<p class="alert alert-info step-%s">Step %s: %s<a href="#" class="btn st_active"></a></p>';
            $result = array(
                'r' => false,
                'm' => '',
                'next' => 0,
            );
            $next = (int)Tools::getValue('next');
            $res = $license->upgrade($next);
            $next++;
            if (count($res['msg']) >= 1) {
                $msg_tpl = '<div class="bootstrap"><div class="wrap_message alert-info">
                <table class="table" cellpadding="0" cellspacing="0">
                <tbody>%s</tbody></table></div></div>';
                $html = '';
                foreach($res['msg'] as $msg) {
                    $html .= '<tr><td>'.$msg['m'].'</td><td><img src="../img/admin/'.($msg['r'] ? 'enabled' : 'disabled').'.gif"></td></tr>';
                }
                $result['m'] = sprintf($msg_tpl, $html);
            } elseif (count($res['msg']) == 1) {
                if ($res['r']) {
                    $result['m'] = $this->displayConfirmation($res['msg'][0]['m']);
                } else {
                    $result['m'] = $this->displayError($res['msg'][0]['m']);
                }
            }
            $result['r'] = $res['r'];
            $result['next'] = $res['next'];
            if ($res['next'] && $res['next_title']) {
               $result['m'] .= sprintf($title_tpl, $next, $next+1, $res['next_title']);
            }
            die(json_encode($result));
        }
        if(Tools::getValue('act') == 'check_update' && Tools::getValue('ajax') == 1)
        {
            $result = array(
                'r' => false,
                'm' => $this->displayConfirmation($this->getTranslator()->trans('Your theme version is up to date.', array(), 'Modules.Stthemeeditor.Admin')),
            );
            $remote_version = $license->checkUpdate(true);
            if ($remote_version === null) {
                $result['m'] = $this->displayError($this->getTranslator()->trans('Unable to check update.', array(), 'Modules.Stthemeeditor.Admin'));
            }
            if($remote_version){
                $result['r'] = true;
                $result['m'] = $this->displayConfirmation($this->getTranslator()->trans('A new version %ver% is available.', array('%ver%'=>$remote_version), 'Modules.Stthemeeditor.Admin'));
            }
            die(json_encode($result));
        }
        if(Tools::getValue('act') == 'check_files' && Tools::getValue('ajax') == 1)
        {
            $result = array(
                'r' => false,
                'm' => $this->displayConfirmation($this->getTranslator()->trans('No theme files were modified.', array(), 'Modules.Stthemeeditor.Admin')),
            );
            $check_res = $license->checkFiles();
            if($check_res === false){
                if (!ConfigurationTest::test_fopen() && !extension_loaded('curl')) {
                    $result['m'] = $this->displayError($this->getTranslator()->trans('It requrie PHP\'s "allow_url_fopen" option is turned on, or cURL is installed.', array(), 'Modules.Stthemeeditor.Admin'));
                } else {
                    $result['m'] = $this->displayError($this->getTranslator()->trans('Download checksum file failed.', array(), 'Modules.Stthemeeditor.Admin'));    
                }
            }elseif(is_array($check_res) && $check_res){
                $result['r'] = true;
                $html = '';
                if ($check_res['changed']) {
                    $html .= $this->getTranslator()->trans('<ul class="file_list changed"><li class="heading">Files were changed:</li><li>%s%</li></ul>', array('%s%'=>implode('</li><li>', $check_res['changed'])), 'Modules.Stthemeeditor.Admin');
                }
                if ($check_res['deleted']) {
                    $html .= $this->getTranslator()->trans('<ul class="file_list deleted"><li class="heading">Files were deleted:</li><li class="heading"></li><li>%s%</li></ul>', array('%s%'=>implode('</li><li>', $check_res['deleted'])), 'Modules.Stthemeeditor.Admin');
                }
                $result['m'] = $this->displayError($html);
            }
            die(json_encode($result));
        }
        if(Tools::getValue('act') == 'backup_files' && Tools::getValue('ajax') == 1)
        {
            $link = trim($this->context->link->getBaseLink(), '/')  . $this->_path . 'config/file-backup-log.html';
            $result = array(
                'r' => false,
                'm' => $this->displayConfirmation($this->getTranslator()->trans('File backup successfully, please <a href="%s" target="_blank">click here</a> to see the details.', array('%s' => $link), 'Modules.Stthemeeditor.Admin')),
            );
            if(!$license->filesBackup()){
                $result['m'] = $this->displayError($this->getTranslator()->trans('File backup failed, please <a href="%s" target="_blank">click here</a> to see the details.', array('%s' => $link), 'Modules.Stthemeeditor.Admin'));
            }
            die(json_encode($result));
        }
        if(Tools::getValue('act') == 'dereg_purchase_code' && Tools::getValue('ajax') == 1)
        {
            $result = array(
                'r' => false,
                'm' => $this->displayError($this->getTranslator()->trans('Unable to de-registration.', array(), 'Modules.Stthemeeditor.Admin')),
            );
            if (Tools::getValue('local')) {
                $license->updateGoumaima();
                $result['r'] = true;
                $result['m'] = $this->displayConfirmation($this->getTranslator()->trans('De-registered successfully.', array(), 'Modules.Stthemeeditor.Admin'));
                die(json_encode($result));
            }
            if ($r = $license->unRegisterLicense()) {
                if ($rs == ThemeLicense::ERROR_IN_ALL_STORE) {
                    $result['m'] = $this->displayError($this->getTranslator()->trans('You can\'t de-register the theme on "All store", please select a store and retry it.', array(), 'Modules.Stthemeeditor.Admin'));
                } else {
                    $license->updateGoumaima();
                    $result['r'] = $r;
                    $result['m'] = $this->displayConfirmation($this->getTranslator()->trans('De-registered successfully.', array(), 'Modules.Stthemeeditor.Admin'));
                    if ($r !== true) {
                        $result['m'] .= $this->displayError($this->getTranslator()->trans('But the purchase code wasn\'t removed from the remote server for the net interruption, you can un-register it under My account > downloads on ST-Themes manually.', array(), 'Modules.Stthemeeditor.Admin'));
                    }
                }
            }
            die(json_encode($result));
        }
        if(Tools::getValue('act') == 'reg_purchase_code' && Tools::getValue('ajax') == 1)
        {
            $pc = Tools::getValue('pc');
            $pc = trim($pc);
            $result = array(
                'r' => true,
                'm' => $this->displayConfirmation($this->getTranslator()->trans('Registered successfully, please reload the page.', array(), 'Modules.Stthemeeditor.Admin')),
                'pc' => '',
                'needs_upgrade' => false,
            );
            if (!$pc) {
                $result['r'] = false;
                $result['m'] = $this->displayError($this->getTranslator()->trans('Purchase code is required.', array(), 'Modules.Stthemeeditor.Admin'));
                die(json_encode($result));
            }
            // valid purchase code
            if (!$license->validateLicense($pc)) {
                $result['r'] = false;
                $result['m'] = $this->displayError($this->getTranslator()->trans('Invalidate purchase code.', array(), 'Modules.Stthemeeditor.Admin'));
            } elseif (($rs = $license->registerLicense($pc)) !== true) {
                // Register purchase code
                $result['r'] = false;
                $result['m'] = $this->displayError($this->getTranslator()->trans('Theme registration failed, if the purchase code was registered by other domain, please de-register it then retry.', array(), 'Modules.Stthemeeditor.Admin'));
                // In all store.
                if ($rs == ThemeLicense::ERROR_IN_ALL_STORE) {
                    $result['m'] = $this->displayError($this->getTranslator()->trans('You can\'t register the theme under the "All store", please select a store and retry it.', array(), 'Modules.Stthemeeditor.Admin'));
                } elseif ($rs == ThemeLicense::API_CALL_EXCEOPTION) {
                    // Net or parse error.
                    $result['m'] = $this->displayError($this->getTranslator()->trans('Can\'t connect to the server.', array(), 'Modules.Stthemeeditor.Admin'));
                } elseif ($rs !== false) {
                    // Error from remote server.
                    $result['m'] = $this->displayError($rs);
                } else {
                    $result['m'] = $this->displayError($this->getTranslator()->trans('Theme registration failed.', array(), 'Modules.Stthemeeditor.Admin'));
                }
                $result['m'] .= $this->displayError($this->getTranslator()->trans('Please enable the maintenance mode temporarily, then you can use all features, and send me your backoffice access and FTP to helloleemj@gmail.com to get help quickly, sorry for the inconvenient.', array(), 'Modules.Stthemeeditor.Admin'));
            } else {
                $license->updateGoumaima($pc);
                $result['pc'] = $license->getGoumaima(true);
                $result['needs_upgrade'] = $license->checkUpdate();
            }
            die(json_encode($result));
        }
        
        $this->fields_form = include(_PS_MODULE_DIR_.$this->name.'/StThemeEditorForm.php');
        if(isset($_POST['savestthemeeditor']))
		{
            $res = true;
            if (isset($_POST['custom_css']) && $_POST['custom_css'])
                $_POST['custom_css'] = str_replace('\\', '¤', $_POST['custom_css']);
            if (isset($_POST['custom_js']) && $_POST['custom_js'])
                $_POST['custom_js'] = str_replace('\\', '¤', $_POST['custom_js']);
            foreach($this->fields_form as $form)
                foreach($form['form']['input'] as $field)
                    if(isset($field['validation']))
                    {
                        $ishtml = ($field['validation']=='isAnything') ? true : false;
                        $errors = array();       
                        $value = Tools::getValue($field['name']);
                        if (isset($field['required']) && $field['required'] && $value==false && (string)$value != '0')
        						$errors[] = Tools::displayError(sprintf('Field "%s" is required.', $field['label']));
                        elseif($value)
                        {
                            $field_validation = $field['validation'];
        					if (!Validate::$field_validation($value)){
        						$errors[] = Tools::displayError(sprintf('Field "%s" is invalid.', $field['label']));
                            }
                        }
        				// Set default value
        				if ($value === false && isset($field['default_value']))
        					$value = $field['default_value'];
                            
                        if(count($errors))
                        {
                            $this->validation_errors = array_merge($this->validation_errors, $errors);
                        }
                        elseif($value==false)
                        {
                            switch($field['validation'])
                            {
                                case 'isUnsignedId':
                                case 'isUnsignedInt':
                                case 'isInt':
                                case 'isBool':
                                    $value = 0;
                                break;
                                /*case 'isNullOrUnsignedId':
                                    $value = $value==='0' || $value===0 ? '0' : '';
                                break;*/
                                default:
                                    $value = '';
                                break;
                            }
                            Configuration::updateValue('STSN_'.strtoupper($field['name']), $value);
                        }
                        else
                            Configuration::updateValue('STSN_'.strtoupper($field['name']), $value, $ishtml);
                    }
            //
            Configuration::updateValue('STSN_PRODUCT_SECONDARY', 1);
            if(Configuration::get('STSN_NAVIGATION_PIPE'))
                Configuration::updateValue('PS_NAVIGATION_PIPE', Configuration::get('STSN_NAVIGATION_PIPE'));
            if(Configuration::get('STSN_PRODUCTS_PER_PAGE'))
                Configuration::updateValue('PS_PRODUCTS_PER_PAGE', Configuration::get('STSN_PRODUCTS_PER_PAGE'));

            $this->updateWelcome();
            $this->updateCopyright();
            /*$this->updateSearchLabel();
            $this->updateNewsletterLabel();*/
            $this->updateCatePerRow();

            //This code has to be put under the $this->updateCatePerRow();
            $pro_image_column_md=Configuration::get('STSN_PRO_IMAGE_COLUMN_MD');
            $pro_primary_column_md=Configuration::get('STSN_PRO_PRIMARY_COLUMN_MD');
            $pro_secondary_column_md=Configuration::get('STSN_PRO_SECONDARY_COLUMN_MD');
            $pro_image_column_sm=Configuration::get('STSN_PRO_IMAGE_COLUMN_SM');
            $pro_primary_column_sm=Configuration::get('STSN_PRO_PRIMARY_COLUMN_SM');
            $pro_secondary_column_sm=Configuration::get('STSN_PRO_SECONDARY_COLUMN_SM');
            $pro_desc_secondary_column_sm=Configuration::get('STSN_PRO_DESC_SECONDARY_COLUMN_SM');
            $pro_desc_secondary_column_md=Configuration::get('STSN_PRO_DESC_SECONDARY_COLUMN_MD');
            if($pro_image_column_md+$pro_primary_column_md>=12)
            {
                Configuration::updateValue('STSN_PRO_PRIMARY_COLUMN_MD', (12-$pro_image_column_md));
                Configuration::updateValue('STSN_PRO_SECONDARY_COLUMN_MD', 0);
            }
            elseif($pro_image_column_md+$pro_primary_column_md+$pro_secondary_column_md>12)
                Configuration::updateValue('STSN_PRO_SECONDARY_COLUMN_MD', (12-$pro_image_column_md-$pro_primary_column_md));
            if($pro_image_column_sm+$pro_primary_column_sm>=12)
            {
                Configuration::updateValue('STSN_PRO_PRIMARY_COLUMN_SM', (12-$pro_image_column_sm));
                Configuration::updateValue('STSN_PRO_SECONDARY_COLUMN_SM', 0);
            }
            elseif($pro_image_column_sm+$pro_primary_column_sm+$pro_secondary_column_sm>12)
                Configuration::updateValue('STSN_PRO_SECONDARY_COLUMN_SM', (12-$pro_image_column_sm-$pro_primary_column_sm));
            //

            Configuration::updateValue('STSN_CART_ICON', Tools::getValue('cart_icon'));
            Configuration::updateValue('STSN_LOVE_ICON', Tools::getValue('love_icon'));
            Configuration::updateValue('STSN_WISHLIST_ICON', Tools::getValue('wishlist_icon'));
            Configuration::updateValue('STSN_COMPARE_ICON', Tools::getValue('compare_icon'));
            Configuration::updateValue('STSN_QUICK_VIEW_ICON', Tools::getValue('quick_view_icon'));
            Configuration::updateValue('STSN_VIEW_ICON', Tools::getValue('view_icon'));
            Configuration::updateValue('STSN_SIGN_ICON', Tools::getValue('sign_icon'));
            Configuration::updateValue('STSN_VIEWED_ICON', Tools::getValue('viewed_icon'));
            Configuration::updateValue('STSN_SPIN_ICON', Tools::getValue('spin_icon'));
            
            $this->_checkImageDir(_PS_UPLOAD_DIR_.$this->name.'/');
            //to do upload images to /upload folder
            $bg_array = array('body','header','f_top','footer','f_secondary','f_info', 'auth');
            foreach($bg_array as $v)
            {
    			if (isset($_FILES[$v.'_bg_image_field']) && isset($_FILES[$v.'_bg_image_field']['tmp_name']) && !empty($_FILES[$v.'_bg_image_field']['tmp_name'])) 
                {
    				if ($error = ImageManager::validateUpload($_FILES[$v.'_bg_image_field'], Tools::convertBytes(ini_get('upload_max_filesize'))))
					   $this->validation_errors[] = $error;
                    else 
                    {
                        $footer_image = $this->uploadCheckAndGetName($_FILES[$v.'_bg_image_field']['name']);
                        if(!$footer_image)
                            $this->validation_errors[] = $this->l('Image format not recognized');
    					if (!move_uploaded_file($_FILES[$v.'_bg_image_field']['tmp_name'], _PS_UPLOAD_DIR_.$this->name.'/'.$footer_image))
    						$this->validation_errors[] = $this->l('Error move uploaded file');
                        else
                        {
    					   Configuration::updateValue('STSN_'.strtoupper($v).'_BG_IMG', $this->name.'/'.$footer_image);
                        }
    				}
    			}
            }
            
            if (isset($_FILES['footer_image_field']) && isset($_FILES['footer_image_field']['tmp_name']) && !empty($_FILES['footer_image_field']['tmp_name'])) 
            {
                if ($error = ImageManager::validateUpload($_FILES['footer_image_field'], Tools::convertBytes(ini_get('upload_max_filesize'))))
                    $this->validation_errors[] = $error;
                else 
                {
                    $footer_image = $this->uploadCheckAndGetName($_FILES['footer_image_field']['name']);
                    if(!$footer_image)
                        $this->validation_errors[] = $this->l('Image format not recognized');
                    else if (!move_uploaded_file($_FILES['footer_image_field']['tmp_name'], _PS_UPLOAD_DIR_.$this->name.'/'.$footer_image))
                        $this->validation_errors[] = $this->l('Error move uploaded file');
                    else
                    {
                       Configuration::updateValue('STSN_FOOTER_IMG', $this->name.'/'.$footer_image);
                    }
                }
            }
            if (isset($_FILES['retina_logo_image_field']) && isset($_FILES['retina_logo_image_field']['tmp_name']) && !empty($_FILES['retina_logo_image_field']['tmp_name'])) 
            {
                if ($error = ImageManager::validateUpload($_FILES['retina_logo_image_field'], Tools::convertBytes(ini_get('upload_max_filesize'))))
                    $this->validation_errors[] = $error;
                else 
                {
                    $retina_logo = $this->uploadCheckAndGetName($_FILES['retina_logo_image_field']['name']);
                    if(!$retina_logo)
                        $this->validation_errors[] = $this->l('Image format not recognized');
                    else if (!move_uploaded_file($_FILES['retina_logo_image_field']['tmp_name'], _PS_UPLOAD_DIR_.$this->name.'/'.$retina_logo))
                        $this->validation_errors[] = $this->l('Error move uploaded file');
                    else
                    {
                       Configuration::updateValue('STSN_RETINA_LOGO', $this->name.'/'.$retina_logo);
                    }
                }
            }
            if (isset($_FILES['mobile_logo_image_field']) && isset($_FILES['mobile_logo_image_field']['tmp_name']) && !empty($_FILES['mobile_logo_image_field']['tmp_name'])) 
            {
                $imagesize = array();
                $imagesize = @getimagesize($_FILES['mobile_logo_image_field']['tmp_name']);
                if (!empty($imagesize)){
                    if ($error = ImageManager::validateUpload($_FILES['mobile_logo_image_field'], Tools::convertBytes(ini_get('upload_max_filesize'))))
                        $this->validation_errors[] = $error;
                    else 
                    {
                        $mobile_logo = $this->uploadCheckAndGetName($_FILES['mobile_logo_image_field']['name']);
                        if(!$mobile_logo)
                            $this->validation_errors[] = $this->l('Image format not recognized');
                        else if (!move_uploaded_file($_FILES['mobile_logo_image_field']['tmp_name'], _PS_UPLOAD_DIR_.$this->name.'/'.$mobile_logo))
                            $this->validation_errors[] = $this->l('Error move uploaded file');
                        else
                        {
                           Configuration::updateValue('STSN_MOBILE_LOGO', $this->name.'/'.$mobile_logo);
                           Configuration::updateValue('STSN_MOBILE_LOGO_WIDTH', $imagesize[0]);
                           Configuration::updateValue('STSN_MOBILE_LOGO_HEIGHT', $imagesize[1]);
                        }
                    }
                }else{
                    $this->validation_errors[] = $this->l('Can not get image size.');
                }
            }

            $favicon_update_time = '';
            $iphone_icon_array = array('16', '32', '150', '180', '192', '512', 'svg');
            foreach($iphone_icon_array as $v)
            {
        			if (isset($_FILES['icon_iphone_'.$v.'_field']) && isset($_FILES['icon_iphone_'.$v.'_field']['tmp_name']) && !empty($_FILES['icon_iphone_'.$v.'_field']['tmp_name'])) 
                    {
        				$error = '';

                        if ($v=='svg'){
                            if(!ImageManager::isCorrectImageFileExt($_FILES['icon_iphone_'.$v.'_field']['name'],array('svg')) || preg_match('/\%00/', $_FILES['icon_iphone_'.$v.'_field']['name']))
                                $error=$this->l('Wrong file type for the "Pinned tab icon for Safari (SVG)" field, a .svg file is expected.');
                        }
                        else
                            $error = ImageManager::validateUpload($_FILES['icon_iphone_'.$v.'_field'], Tools::convertBytes(ini_get('upload_max_filesize')), array('png'));

                        if($error)
    					   $this->validation_errors[] = $error;
                        else 
                        {
                            $icon = $this->uploadCheckAndGetName($_FILES['icon_iphone_'.$v.'_field']['name']);
                            if(!$icon)
                                $this->validation_errors[] = $this->l('Image format not recognized');
                            else if (!move_uploaded_file($_FILES['icon_iphone_'.$v.'_field']['tmp_name'], _PS_UPLOAD_DIR_.$this->name.'/'.$icon))
                                $this->validation_errors[] = $this->l('Error move uploaded file');
                            else
                            {
                                Configuration::updateValue('STSN_ICON_IPHONE_'.strtoupper($v), $this->name.'/'.$icon);
                                $favicon_update_time = time();
                            }
        				}
        			}
            }
            $this->generateBrowserConfig($favicon_update_time);
            $this->generateWebmanifest($favicon_update_time);

            $product_views = 0;
            foreach($this->_product_ways as $v)
                $product_views += (int)Tools::getValue('product_views_'.$v['id']);
            Configuration::updateValue('STSN_PRODUCT_VIEWS', $product_views);

            $clear_list_view = 0;
            foreach($this->_clear_list_view as $v)
                $clear_list_view += (int)Tools::getValue('clear_list_view_'.$v['id']);
            Configuration::updateValue('STSN_CLEAR_LIST_VIEW', $clear_list_view);

            if(count($this->validation_errors))
                $this->_html .= $this->displayError(implode('<br/>',$this->validation_errors));
            else
            {
                $this->writeCss();
                $this->_html .= $this->displayConfirmation($this->getTranslator()->trans('Settings updated', array(), 'Admin.Theme.Panda'));
            } 
        }
        
        if (Tools::isSubmit('deleteimagestthemeeditor'))
        {
            if($identi = Tools::getValue('identi'))
            {
                $identi = strtoupper($identi);
                $image  = Configuration::get('STSN_'.$identi);
            	if (Configuration::get('STSN_'.$identi))
                    if (file_exists(_PS_UPLOAD_DIR_.$this->name.'/'.$image))
		                @unlink(_PS_UPLOAD_DIR_.$this->name.'/'.$image);
                    elseif(file_exists($this->_path.$image))
                        @unlink($this->_path.$image);
            	Configuration::updateValue('STSN_'.$identi, '');
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&conf=7&ref='.(int)Tools::getValue('ref').'&token='.Tools::getAdminTokenLite('AdminModules'));  
             }else
                $this->_html .= $this->displayError($this->getTranslator()->trans('An error occurred while delete image.', array(), 'Admin.Theme.Panda'));
        }
        $this->initDropListGroup();
		$helper = $this->initForm();
        Media::addJsDef(array(
            'id_tab_index' => Tools::getValue('id_tab_index', 0),
        ));

        if($this->isEbTransPandaInstalled()){
            $_str_tips=$this->getTranslator()->trans('You\'ve installed the Easy builder, use its front-end theme editor feature to change theme settings. Most of your changes here will not take effect anymore.', array(), 'Modules.Stthemeeditor.Admin');
            $_str_tips.='<a href="'. $this->context->link->getAdminLink('AdminStEasyBuilder',true,[],['settings_all'=>1]) .'">'.$this->getTranslator()->trans('On Click',array(), 'Modules.Stthemeeditor.Admin').'</a>';
            
            $this->_html .= $this->displayError($_str_tips);
        }

        return $this->_html.$this->initToolbarBtn().'<div class="tabbable row stthemeeditor">'.$this->initTab().'<div id="stthemeeditor" class="col-xs-12 col-lg-10 tab-content">'.$helper->generateForm($this->fields_form).'</div></div>';
	}
    //some newly added configuratoins must have a inital value
    public function forceUpgradeConfiguration(){
        if(!Configuration::get('STSN_LEFT_COLUMN_SIZE_XL'))
            Configuration::updateValue('STSN_LEFT_COLUMN_SIZE_XL', Configuration::get('STSN_LEFT_COLUMN_SIZE_LG'));
        if(!Configuration::get('STSN_RIGHT_COLUMN_SIZE_XL'))
            Configuration::updateValue('STSN_RIGHT_COLUMN_SIZE_XL', Configuration::get('STSN_RIGHT_COLUMN_SIZE_LG'));
    }
    public function generateBrowserConfig($favicon_update_time){
        if($favicon_update_time){
            if($browserconfig_icon = Configuration::get('STSN_ICON_IPHONE_150')){
                $icons=$this->getFavicon($browserconfig_icon,'icon_iphone_150');
                $browserconfig = '<?xml version="1.0" encoding="UTF-8"?><browserconfig><msapplication><tile><square150x150logo src="'.$this->context->link->getMediaLink($icons).'"/></tile></msapplication></browserconfig>';
                $this->_checkImageDir(_PS_UPLOAD_DIR_.$this->name.'/'.$this->context->shop->id.'/');
                @file_put_contents(_PS_UPLOAD_DIR_.$this->name.'/'.$this->context->shop->id.'/browserconfig.xml', $browserconfig);
            }
            Configuration::updateValue('STSN_FAVICON_UPDATE_TIME', $favicon_update_time);
        }
        return true;
    }
    public function generateWebmanifest($favicon_update_time){
        if($favicon_update_time){
            $icon_iphone_192 = Configuration::get('STSN_ICON_IPHONE_192');
            $icon_iphone_512 = Configuration::get('STSN_ICON_IPHONE_512');
            if($icon_iphone_192 || $icon_iphone_512){
                $site_webmanifest = array(
                    'name' => Configuration::get('PS_SHOP_NAME'),
                    'short_name' => Configuration::get('PS_SHOP_NAME'),
                    'icons' => array(),
                    'theme_color' => Configuration::get('STSN_BROWSER_THEME_COLOR') ? Configuration::get('STSN_BROWSER_THEME_COLOR') : '#ffffff',
                    'background_color' => Configuration::get('STSN_BROWSER_THEME_COLOR') ? Configuration::get('STSN_BROWSER_THEME_COLOR') : '#ffffff',
                    'display' => 'standalone',
                    'start_url' => $this->context->shop->getBaseURI(),
                );
                if($icon_iphone_192){
                    $icons=$this->getFavicon($icon_iphone_192,'icon_iphone_192');
                    $site_webmanifest['icons'][] = array(
                        'src' => $this->context->link->getMediaLink($icons),
                        'sizes' => '192x192',
                        'type' => 'image/png',
                    );
                }
                    
                if($icon_iphone_512){
                    $icons=$this->getFavicon($icon_iphone_512,'icon_iphone_512');
                    $site_webmanifest['icons'][] = array(
                        'src' => $this->context->link->getMediaLink($icons),
                        'sizes' => '512x512',
                        'type' => 'image/png',
                    );
                }
                    
                $this->_checkImageDir(_PS_UPLOAD_DIR_.$this->name.'/'.$this->context->shop->id.'/');
                @file_put_contents(_PS_UPLOAD_DIR_.$this->name.'/'.$this->context->shop->id.'/site.webmanifest', json_encode($site_webmanifest));
            }
            Configuration::updateValue('STSN_FAVICON_UPDATE_TIME', $favicon_update_time);
        }
        return true;
    }

    public function initDropListGroup()
    {
        // $this->fields_form[0]['form']['input']['hometab_pro_per']['name'] = $this->BuildDropListGroup($this->findCateProPer(4));
        $this->fields_form[0]['form']['input']['left_column_size']['name'] = $this->BuildDropListGroup($this->findCateProPer(14),0,12,true);
        $this->fields_form[0]['form']['input']['right_column_size']['name'] = $this->BuildDropListGroup($this->findCateProPer(15),0,12,true);
        $this->fields_form[1]['form']['input']['categories_per']['name'] = $this->BuildDropListGroup($this->findCateProPer(6),1,12);
        $this->fields_form[1]['form']['input']['category_per']['name'] = $this->BuildDropListGroup($this->findCateProPer(1),1,6);
        /*$this->fields_form[1]['form']['input']['category_per_2']['name'] = $this->BuildDropListGroup($this->findCateProPer(2));
        $this->fields_form[1]['form']['input']['category_per_3']['name'] = $this->BuildDropListGroup($this->findCateProPer(3));*/
        $this->fields_form[11]['form']['input']['cs_pro_per']['name'] = $this->BuildDropListGroup($this->findCateProPer(7),1,12);
        $this->fields_form[12]['form']['input']['pc_pro_per']['name'] = $this->BuildDropListGroup($this->findCateProPer(8),1,12);
        // $this->fields_form[13]['form']['input']['ac_pro_per']['name'] = $this->BuildDropListGroup($this->findCateProPer(9));
        // $this->fields_form[16]['form']['input']['packitems_pro_per']['name'] = $this->BuildDropListGroup($this->findCateProPer(5));
        $this->fields_form[16]['form']['input']['pro_thumnbs_per']['name'] = $this->BuildDropListGroup($this->findCateProPer(10),1,6);
        $this->fields_form[16]['form']['input']['pro_kk_per']['name'] = $this->BuildDropListGroup($this->findCateProPer(20),1,6);
        $this->fields_form[16]['form']['input']['pro_image_column']['name'] = $this->BuildDropListGroup($this->findCateProPer(11),1,11);
        $this->fields_form[16]['form']['input']['pro_primary_column']['name'] = $this->BuildDropListGroup($this->findCateProPer(12),1,11);
        $this->fields_form[16]['form']['input']['pro_secondary_column']['name'] = $this->BuildDropListGroup($this->findCateProPer(13),0,11);
        $this->fields_form[16]['form']['input']['pro_desc_secondary_column']['name'] = $this->BuildDropListGroup($this->findCateProPer(19),0,11);
        $this->fields_form[7]['form']['input']['stacked_footer_column']['name'] = $this->BuildDropListGroup($this->findCateProPer(16),0,12,true);
        $this->fields_form[0]['form']['input']['quarter']['name'] = $this->BuildDropListGroup($this->findCateProPer(17),0,12);
        $this->fields_form[5]['form']['input']['main_menu_spacing']['name'] = $this->BuildDropListGroup($this->findCateProPer(18),0,20);
    }
    
    public function updateWelcome() {
		$languages = Language::getLanguages(false);
		$welcome = $welcome_logged  = $welcome_link = array();
        $defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
		foreach ($languages as $language)
		{
            $welcome[$language['id_lang']] = Tools::getValue('welcome_'.$language['id_lang']) ? Tools::getValue('welcome_'.$language['id_lang']) : Tools::getValue('welcome_'.$defaultLanguage->id);
			$welcome_logged[$language['id_lang']] = Tools::getValue('welcome_logged_'.$language['id_lang']) ? Tools::getValue('welcome_logged_'.$language['id_lang']) : Tools::getValue('welcome_logged_'.$defaultLanguage->id);
			$welcome_link[$language['id_lang']] = Tools::getValue('welcome_link_'.$language['id_lang']) ? Tools::getValue('welcome_link_'.$language['id_lang']) : Tools::getValue('welcome_link_'.$defaultLanguage->id);
		}
        Configuration::updateValue('STSN_WELCOME_LINK', $welcome_link);
        Configuration::updateValue('STSN_WELCOME', $welcome);
        Configuration::updateValue('STSN_WELCOME_LOGGED', $welcome_logged);
	}
    public function updateCopyright() {
		$languages = Language::getLanguages();
		$result = array();
        $defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
		foreach ($languages as $language)
			$result[$language['id_lang']] = Tools::getValue('copyright_text_' . $language['id_lang']) ? Tools::getValue('copyright_text_'.$language['id_lang']) : Tools::getValue('copyright_text_'.$defaultLanguage->id);

        /*if(!$result[$defaultLanguage->id])
            $this->validation_errors[] = Tools::displayError('The field "Copyright text" is required at least in '.$defaultLanguage->name);
		else*/
            Configuration::updateValue('STSN_COPYRIGHT_TEXT', $result, true);
	}
    /*public function updateSearchLabel() {
		$languages = Language::getLanguages();
		$result = array();
        $defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
		foreach ($languages as $language)
			$result[$language['id_lang']] = Tools::getValue('search_label_' . $language['id_lang']) ? Tools::getValue('search_label_' . $language['id_lang']) : Tools::getValue('search_label_'.$defaultLanguage->id);

        if(!$result[$defaultLanguage->id])
            $this->validation_errors[] = Tools::displayError('The field "Search label" is required at least in '.$defaultLanguage->name);
		else
            Configuration::updateValue('STSN_SEARCH_LABEL', $result);
	}        
    public function updateNewsletterLabel() {
        $languages = Language::getLanguages();
        $result = array();
        $defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
        foreach ($languages as $language)
            $result[$language['id_lang']] = Tools::getValue('newsletter_label_' . $language['id_lang']) ? Tools::getValue('newsletter_label_' . $language['id_lang']) : Tools::getValue('newsletter_label_'.$defaultLanguage->id);

        if(!$result[$defaultLanguage->id])
            $this->validation_errors[] = Tools::displayError('The field "Newsletter label" is required at least in '.$defaultLanguage->name);
        else
            Configuration::updateValue('STSN_NEWSLETTER_LABEL', $result);
    }*/     
    public function updateCatePerRow() {
		$arr = $this->findCateProPer();
        foreach ($arr as $key => $value)
            foreach ($value as $v)
            {
                $gv = Tools::getValue($v['id']);
                if ($gv!==false)
                    Configuration::updateValue('STSN_'.strtoupper($v['id']), $gv);
            }
	}
    public function initForm()
    {
        $footer_img = Configuration::get('STSN_FOOTER_IMG');
		if ($footer_img != "") {
            $this->fields_form[0]['form']['input']['payment_icon']['image'] = $this->getImageHtml((strpos($footer_img, '/img/cms/') === 0 ? rtrim(__PS_BASE_URI__, '/') : ($footer_img==$this->defaults["footer_img"]['val'] ? $this->_path : _THEME_PROD_PIC_DIR_ )).$footer_img,'footer_img');
		}
        $retina_logo = Configuration::get('STSN_RETINA_LOGO');
        if ($retina_logo != "") {
            $this->fields_form[4]['form']['input']['retina_logo_image_field']['image'] = $this->getImageHtml((strpos($retina_logo, '/img/cms/') === 0 ? rtrim(__PS_BASE_URI__, '/') : _THEME_PROD_PIC_DIR_).$retina_logo,'retina_logo');
        }
        $mobile_logo = Configuration::get('STSN_MOBILE_LOGO');
        if ($mobile_logo != "") {
            $this->fields_form[4]['form']['input']['mobile_logo_image_field']['image'] = $this->getImageHtml((strpos($mobile_logo, '/img/cms/') === 0 ? rtrim(__PS_BASE_URI__, '/') : _THEME_PROD_PIC_DIR_).$mobile_logo,'mobile_logo');
        }
		/*if (Configuration::get('STSN_ICON_IPHONE_57') != "") {
            $iphone_57 = Configuration::get('STSN_ICON_IPHONE_57');
		    $this->fields_form[18]['form']['input']['icon_iphone_57_field']['image'] = $this->getImageHtml(($iphone_57 != $this->defaults['icon_iphone_57']['val'] ? _THEME_PROD_PIC_DIR_ : $this->_path).$iphone_57,'icon_iphone_57');
		}
		if (Configuration::get('STSN_ICON_IPHONE_72') != "") {
            $iphone_72 = Configuration::get('STSN_ICON_IPHONE_72');
		    $this->fields_form[18]['form']['input']['icon_iphone_72_field']['image'] = $this->getImageHtml(($iphone_72 != $this->defaults['icon_iphone_72']['val'] ? _THEME_PROD_PIC_DIR_ : $this->_path).$iphone_72,'icon_iphone_72');
		}
		if (Configuration::get('STSN_ICON_IPHONE_114') != "") {
            $iphone_114 = Configuration::get('STSN_ICON_IPHONE_114');
		    $this->fields_form[18]['form']['input']['icon_iphone_114_field']['image'] = $this->getImageHtml(($iphone_114 != $this->defaults['icon_iphone_114']['val'] ? _THEME_PROD_PIC_DIR_ : $this->_path).$iphone_114,'icon_iphone_114');
		}
		if (Configuration::get('STSN_ICON_IPHONE_144') != "") {
            $iphone_144 = Configuration::get('STSN_ICON_IPHONE_144');
		    $this->fields_form[18]['form']['input']['icon_iphone_144_field']['image'] = $this->getImageHtml(($iphone_144 != $this->defaults['icon_iphone_144']['val'] ? _THEME_PROD_PIC_DIR_ : $this->_path).$iphone_144,'icon_iphone_114');
		}*/
        $iphone_icon_array = array('16', '32', '150', '180', '192', '512', 'svg');
        foreach($iphone_icon_array as $v)
        {
            if ($icon = Configuration::get('STSN_ICON_IPHONE_'.strtoupper($v))) {
                $icons=$this->getFavicon($icon,'icon_iphone_'.$v);
                $this->fields_form[18]['form']['input']['icon_iphone_'.$v.'_field']['image'] = $this->getImageHtml($icons,'icon_iphone_'.$v);
            }
        }      
        if (Configuration::get('STSN_HEADER_BG_IMG') != "") {
            $this->fields_form[4]['form']['input']['header_bg_image_field']['image'] = $this->getImageHtml(_THEME_PROD_PIC_DIR_.Configuration::get('STSN_HEADER_BG_IMG'), 'header_bg_img');
        }
		if (Configuration::get('STSN_HEADING_BG_IMAGE') != "") {
		    $this->fields_form[4]['form']['input']['heading_bg_image_field']['image'] = $this->getImageHtml(_THEME_PROD_PIC_DIR_.Configuration::get('STSN_HEADING_BG_IMAGE'), 'heading_bg_image');
		}
		if (Configuration::get('STSN_BODY_BG_IMG') != "") {
		    $this->fields_form[6]['form']['input']['body_bg_image_field']['image'] = $this->getImageHtml(_THEME_PROD_PIC_DIR_.Configuration::get('STSN_BODY_BG_IMG'),'body_bg_img');
		}
		if (Configuration::get('STSN_F_TOP_BG_IMG') != "") {
		    $this->fields_form[7]['form']['input']['f_top_bg_image_field']['image'] = $this->getImageHtml(_THEME_PROD_PIC_DIR_.Configuration::get('STSN_F_TOP_BG_IMG'),'f_top_bg_img');
		}
		if (Configuration::get('STSN_FOOTER_BG_IMG') != "") {
		    $this->fields_form[8]['form']['input']['footer_bg_image_field']['image'] = $this->getImageHtml(_THEME_PROD_PIC_DIR_.Configuration::get('STSN_FOOTER_BG_IMG'),'footer_bg_img');
		}
		if (Configuration::get('STSN_F_SECONDARY_BG_IMG') != "") {
		    $this->fields_form[9]['form']['input']['f_secondary_bg_image_field']['image'] = $this->getImageHtml(_THEME_PROD_PIC_DIR_.Configuration::get('STSN_F_SECONDARY_BG_IMG'),'f_secondary_bg_img');
		}
        if (Configuration::get('STSN_F_INFO_BG_IMG') != "") {
            $this->fields_form[10]['form']['input']['f_info_bg_image_field']['image'] = $this->getImageHtml(_THEME_PROD_PIC_DIR_.Configuration::get('STSN_F_INFO_BG_IMG'),'f_info_bg_img');
        } 
		if (Configuration::get('STSN_AUTH_BG_IMG') != "") {
		    $this->fields_form[62]['form']['input']['auth_bg_image_field']['image'] = $this->getImageHtml(_THEME_PROD_PIC_DIR_.Configuration::get('STSN_AUTH_BG_IMG'),'auth_bg_img');
		} 
        
        foreach (array('font_text'=>3, 'font_heading'=>27, 'pro_name'=>23, 'font_price'=>28, 'font_menu'=>5, 'second_font_menu'=>5, 'third_font_menu'=>5, 'font_cart_btn'=>28, 'font_product_name'=>16, 'ver_font_menu'=>53) as $font=>$wf) {
            if ($font_menu_string = Configuration::get('STSN_'.strtoupper($font))) {
                $font_menu = explode(":", $font_menu_string);
                $font_menu = $font_menu[0];
                $font_menu_key = str_replace(' ', '_', $font_menu);
            }
            else
            {
                $font_menu_key = $font_menu = $this->_font_inherit;
            }
            if(array_key_exists($font_menu_key, $this->googleFonts))
            {
                $font_menu_array = array(
                    $font_menu.':700' => '700',
                    $font_menu.':italic' => 'italic',
                    $font_menu.':700italic' => '700italic',
                );
                foreach ($this->googleFonts[$font_menu_key]['variants'] as $g) {
                    $font_menu_array[$font_menu.':'.$g] = $g;
                }
                foreach($font_menu_array AS $value){
                    $this->fields_form[$wf]['form']['input'][$font]['options']['query'][] = array(
                            'id'=> $font_menu.':'.($value=='regular' ? '400' : $value),
                            'name'=> $value,
                        );
                }
            }
            else
            {
                $this->fields_form[$wf]['form']['input'][$font]['options']['query'] = array(
                    array('id'=> $font_menu,'name'=>'Normal'),
                    array('id'=> $font_menu.':700','name'=>'Bold'),
                    array('id'=> $font_menu.':italic','name'=>'Italic'),
                    array('id'=> $font_menu.':700italic','name'=>'Bold & Italic'),
                    array('id'=> $font_menu.':100','name'=>'100'),
                    array('id'=> $font_menu.':100italic','name'=>'100 & Italic'),
                    array('id'=> $font_menu.':300','name'=>'300'),
                    array('id'=> $font_menu.':300italic','name'=>'300 & Italic'),
                    array('id'=> $font_menu.':500','name'=>'500'),
                    array('id'=> $font_menu.':500italic','name'=>'500 & Italic'),
                    array('id'=> $font_menu.':600','name'=>'600'),
                    array('id'=> $font_menu.':600italic','name'=>'600 & Italic'),
                    array('id'=> $font_menu.':800','name'=>'800'),
                    array('id'=> $font_menu.':800italic','name'=>'800 & Italic'),
                    array('id'=> $font_menu.':900','name'=>'900'),
                    array('id'=> $font_menu.':900italic','name'=>'900 & Italic'),
                );
            }  
        }
        
        /*$cate_sortby_html = '';
        if(Configuration::get('STSN_CATE_SORTBY') && ($arr = explode('¤', Configuration::get('STSN_CATE_SORTBY'))))
        {
            foreach($arr AS $value)
            {
                if (!$value)
                    continue;
                $name = '';
                foreach($this->_category_sortby AS $sortby)
                {
                    if ($sortby['id'] == $value)
                    {
                        $name = $sortby['name'];
                        break;
                    }
                }
                $cate_sortby_html .= '<li id="#'.$value.'_li" class="form-control-static"><button type="button" class="delSortby btn btn-default" name="'.$value.'"><i class="icon-remove text-danger"></i></button>&nbsp;<span>'.$name.'</span></li>';
            }
                
        }
        $this->fields_form[1]['form']['input']['cate_sortby_name']['desc'] = '<a id="add_cate_sortby" class="btn btn-default btn-block fixed-width-md" href="javascript:;">Add</a><br/><p>If you didn\'t add any items here, all items will display on the front page.</p><ul id="curr_cate_sortby">'.$cate_sortby_html.'</ul>';*/

        $image_types_arr = array(
            array('id' => '', 'name' => $this->getTranslator()->trans('--', array(), 'Admin.Theme.Panda')),
        );
        $imagesTypes = ImageType::getImagesTypes('products');
        foreach ($imagesTypes as $k=>$imageType) {
            if(Tools::substr($imageType['name'],-3)=='_2x')
                continue;
            $image_types_arr[] = array('id' => $imageType['name'], 'name' => $imageType['name'].'('.$imageType['width'].'x'.$imageType['height'].')');
        }
        $this->fields_form[1]['form']['input']['cate_pro_image_type']['options']['query'] = $image_types_arr;
        $this->fields_form[16]['form']['input']['gallery_image_type']['options']['query'] = $image_types_arr;
        $this->fields_form[16]['form']['input']['thumb_image_type']['options']['query'] = $image_types_arr;
        $this->fields_form[11]['form']['input']['cs_image_type']['options']['query'] = $image_types_arr;
        $this->fields_form[12]['form']['input']['pc_image_type']['options']['query'] = $image_types_arr;
        
        $pro_spacing_grid = Configuration::get('STSN_PRO_SPACING_GRID');
        $option = array(
            'spacing' => $pro_spacing_grid || $pro_spacing_grid===0 || $pro_spacing_grid==='0' ? (int)$pro_spacing_grid : 15,
            'per_lg'  => (int)Configuration::get('STSN_CATEGORY_PER_LG'),
            'per_xl'  => (int)Configuration::get('STSN_CATEGORY_PER_XL'),
            'per_xxl' => (int)Configuration::get('STSN_CATEGORY_PER_XXL'),
            'page'    => 'category',
        );
        $this->fields_form[1]['form']['input']['cate_pro_image_type']['desc'] = $this->calcImageWidth($option);
        
        $option = array(
            'spacing' => (int)Configuration::get('STSN_GALLERY_SPACING'),
            'per_lg'  => (int)Configuration::get('STSN_PRO_IMAGE_COLUMN_MD'),
            'per_xl'  => (int)Configuration::get('STSN_PRO_IMAGE_COLUMN_MD'),
            'per_xxl' => (int)Configuration::get('STSN_PRO_IMAGE_COLUMN_MD'),
            'page'    => 'product',
            'for_product_gallery'   => 1,
        );
        $this->fields_form[16]['form']['input']['gallery_image_type']['desc'] = $this->calcImageWidth($option);
        
        $option = array(
            'spacing' => (int)Configuration::get('STSN_CS_SPACING_BETWEEN'),
            'per_lg'  => (int)Configuration::get('STSN_CS_PER_LG'),
            'per_xl'  => (int)Configuration::get('STSN_CS_PER_XL'),
            'per_xxl' => (int)Configuration::get('STSN_CS_PER_XXL'),
            'page'    => 'product',
        );
        $this->fields_form[11]['form']['input']['cs_image_type']['desc'] = $this->calcImageWidth($option);
        
        $option = array(
            'spacing' => (int)Configuration::get('STSN_PC_SPACING_BETWEEN'),
            'per_lg'  => (int)Configuration::get('STSN_PC_PER_LG'),
            'per_xl'  => (int)Configuration::get('STSN_PC_PER_XL'),
            'per_xxl' => (int)Configuration::get('STSN_PC_PER_XXL'),
            'page'    => 'product',
        );
        $this->fields_form[12]['form']['input']['pc_image_type']['desc'] = $this->calcImageWidth($option);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
        $helper->module = $this;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'savestthemeeditor';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		); 
        
		return $helper;
	}
    public function fontOptions() {
        $system = $google = array();
        foreach($this->systemFonts as $v)
            $system[] = array('id'=>$v,'name'=>$v);
        foreach($this->googleFonts as $v)
            $google[] = array('id'=>$v['family'],'name'=>$v['family']);
        $module = new StThemeEditor();
        return array(
            array('name'=>$module->getTranslator()->trans('System Web fonts', array(), 'Admin.Theme.Panda'),'query'=>$system),
            array('name'=>$module->getTranslator()->trans('Google Web Fonts', array(), 'Admin.Theme.Panda'),'query'=>$google),
        );
	}
    public function getPatterns($amount=28,$type='')
    {
        $html = '';
        foreach(range(1,$amount) as $v)
            $html .= '<div class="parttern_wrap '.($type=='heading_bg' ? ' repeat_x ' : '').'" style="background-image:url('.$this->_path.'patterns'.($type ? '/'.$type : '').'/'.$v.'.png);"><span>'.$v.'</span></div>';
        $html .= '<div>'.$this->getTranslator()->trans('Pattern credits', array(), 'Modules.Stthemeeditor.Admin').':<a href="http://subtlepatterns.com" target="_blank">subtlepatterns.com</a></div>';
        return $html;
    }
    public function getPatternsArray($amount=28)
    {
        $arr = array();
        for($i=1;$i<=$amount;$i++)
            $arr[] = array('id'=>$i,'name'=>$i); 
        return $arr;   
    }
    public function writeCss()
    {
        // Clear translation cache files when the first use theme.
        $file = $this->local_path.'config/theme-'.$this->version;
        if (!file_exists($file)) {
            @file_put_contents($file, '');
            @rename(_PS_CACHE_DIR_.'translations', _PS_CACHE_DIR_.'translations-'.Sha1($file));
        }
        
        $id_shop = (int)Shop::getContextShopID();
        $is_responsive = 1;
        $responsive_max = $this->getResponsiveMax();
        $css = $res_css = '';

        $fontText = $fontHeading = $fontPrice = $fontMenu = $secondFontMenu = $thirdFontMenu = $fontCartBtn = $fontProductName = $verFontMenu = $proName = '';
        $fontTextWeight = $fontHeadingWeight = $fontPriceWeight = $fontMenuWeight = $secondFontMenuWeight = $thirdFontMenuWeight = $fontCartBtnWeight = $fontProductNameWeight = $verFontMenuWeight = $proNameWeight = '';
        $fontTextStyle = $fontHeadingStyle = $fontPriceStyle = $fontMenuStyle = $secondFontMenuStyle = $thirdFontMenuStyle = $fontCartBtnStyle = $fontProductNameStyle = $verFontMenuStyle = $proNameStyle = '';

        if($fontTextString = Configuration::get('STSN_FONT_TEXT'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $fontTextString, $fontTextArr);
            $fontText = $fontTextArr[1][0];
            $fontTextArr[2][0] && $fontTextWeight = 'font-weight:'.$fontTextArr[2][0].';';
            $fontTextArr[3][0] && $fontTextStyle = 'font-style:'.$fontTextArr[3][0].';';
        }
        if($fontHeadingString = Configuration::get('STSN_FONT_HEADING'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $fontHeadingString, $fontHeadingArr);
            $fontHeading = $fontHeadingArr[1][0];
            $fontHeadingArr[2][0] && $fontHeadingWeight = 'font-weight:'.$fontHeadingArr[2][0].';';
            $fontHeadingArr[3][0] && $fontHeadingStyle = 'font-style:'.$fontHeadingArr[3][0].';';
        }
        if($proNameString = Configuration::get('STSN_PRO_NAME'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $proNameString, $proNameArr);
            $proName = $proNameArr[1][0];
            $proNameArr[2][0] && $proNameWeight = 'font-weight:'.$proNameArr[2][0].';';
            $proNameArr[3][0] && $proNameStyle = 'font-style:'.$proNameArr[3][0].';';
        }
        if($fontProductNameString = Configuration::get('STSN_FONT_PRODUCT_NAME'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $fontProductNameString, $fontProductNameArr);
            $fontProductName = $fontProductNameArr[1][0];
            $fontProductNameArr[2][0] && $fontProductNameWeight = 'font-weight:'.$fontProductNameArr[2][0].';';
            $fontProductNameArr[3][0] && $fontProductNameStyle = 'font-style:'.$fontProductNameArr[3][0].';';
        }
        if($fontPriceString = Configuration::get('STSN_FONT_PRICE'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $fontPriceString, $fontPriceArr);
            $fontPrice = $fontPriceArr[1][0];
            $fontPriceArr[2][0] && $fontPriceWeight = 'font-weight:'.$fontPriceArr[2][0].';';
            $fontPriceArr[3][0] && $fontPriceStyle = 'font-style:'.$fontPriceArr[3][0].';';
        }
        if($fontMenuString = Configuration::get('STSN_FONT_MENU'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $fontMenuString, $fontMenuArr);
            $fontMenu = $fontMenuArr[1][0];
            $fontMenuArr[2][0] && $fontMenuWeight = 'font-weight:'.$fontMenuArr[2][0].';';
            $fontMenuArr[3][0] && $fontMenuStyle = 'font-style:'.$fontMenuArr[3][0].';';
        }
        if($secondFontMenuString = Configuration::get('STSN_SECOND_FONT_MENU'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $secondFontMenuString, $secondFontMenuArr);
            $secondFontMenu = $secondFontMenuArr[1][0];
            $secondFontMenuArr[2][0] && $secondFontMenuWeight = 'font-weight:'.$secondFontMenuArr[2][0].';';
            $secondFontMenuArr[3][0] && $secondFontMenuStyle = 'font-style:'.$secondFontMenuArr[3][0].';';
        }
        if($thirdFontMenuString = Configuration::get('STSN_THIRD_FONT_MENU'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $thirdFontMenuString, $thirdFontMenuArr);
            $thirdFontMenu = $thirdFontMenuArr[1][0];
            $thirdFontMenuArr[2][0] && $thirdFontMenuWeight = 'font-weight:'.$thirdFontMenuArr[2][0].';';
            $thirdFontMenuArr[3][0] && $thirdFontMenuStyle = 'font-style:'.$thirdFontMenuArr[3][0].';';
        }
        if($fontCartBtnString = Configuration::get('STSN_FONT_CART_BTN'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $fontCartBtnString, $fontCartBtnArr);
            $fontCartBtn = $fontCartBtnArr[1][0];
            $fontCartBtnArr[2][0] && $fontCartBtnWeight = 'font-weight:'.$fontCartBtnArr[2][0].';';
            $fontCartBtnArr[3][0] && $fontCartBtnStyle = 'font-style:'.$fontCartBtnArr[3][0].';';
        }
        if($verFontMenuString = Configuration::get('STSN_VER_FONT_MENU'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $verFontMenuString, $verFontMenuArr);
            $verFontMenu = $verFontMenuArr[1][0];
            $verFontMenuArr[2][0] && $verFontMenuWeight = 'font-weight:'.$verFontMenuArr[2][0].';';
            $verFontMenuArr[3][0] && $verFontMenuStyle = 'font-style:'.$verFontMenuArr[3][0].';';
        }
        //when in full screen and no responsive, a min width 940 will be apply to container.
        if($responsive_max==3)
            $css .='.container, .boxed_page_wrapper{max-width: 100%;width: 100%;}';
        
        //set a max width to ensure images staying sharpe
        $cate_pro_image_type_size = Image::getSize(Configuration::get('STSN_CATE_PRO_IMAGE_TYPE') ? Configuration::get('STSN_CATE_PRO_IMAGE_TYPE') : 'home_default');
        $css .='.product_list.list .ajax_block_product .pro_first_box{max-width: '.$cate_pro_image_type_size['width'].'px;}';  
        // $res_css .='@media (max-width: 767px) {.product_list.list .ajax_block_product .pro_first_box{width: '.$cate_pro_image_type_size['width'].'px;}}';  
        $css .='.product_list.list .ajax_block_product .pro_second_box{margin-left: '.($cate_pro_image_type_size['width']+20).'px;}';  
        $css .='.is_rtl .product_list.list .ajax_block_product .pro_second_box{margin-left:0;margin-right: '.($cate_pro_image_type_size['width']+20).'px;}';  
        //$res_css .='@media (max-width: 767px) {.product_list.list .ajax_block_product .pro_second_box{margin-left: 0px;}}';  using important instead
        $imagesTypes = ImageType::getImagesTypes('products');
        $gallery_image_type = Configuration::get('STSN_GALLERY_IMAGE_TYPE') ? Configuration::get('STSN_GALLERY_IMAGE_TYPE') : 'medium_default';

        $pro_thumnbs_per_fw = Configuration::get('STSN_PRO_THUMNBS_PER_FW');
        $pro_thumnbs_per_xxl = Configuration::get('STSN_PRO_THUMNBS_PER_XXL');
        $pro_thumnbs_per_xl = Configuration::get('STSN_PRO_THUMNBS_PER_XL');
        $pro_thumnbs_per_lg = Configuration::get('STSN_PRO_THUMNBS_PER_LG');
        $pro_thumnbs_per_md = Configuration::get('STSN_PRO_THUMNBS_PER_MD');
        $pro_thumnbs_per_sm = Configuration::get('STSN_PRO_THUMNBS_PER_SM');
        $pro_thumnbs_per_xs = Configuration::get('STSN_PRO_THUMNBS_PER_XS');
        $product_thumbnails = Configuration::get('STSN_PRODUCT_THUMBNAILS');
        $gallery_spacing = (int)Configuration::get('STSN_GALLERY_SPACING');

        foreach ($imagesTypes as $k=>$imageType) {
            $css .='.products_sldier_swiper .ajax_block_product .pro_outer_box.'.$imageType['name'].', .product_list.grid .ajax_block_product .pro_outer_box.'.$imageType['name'].'{max-width: '.$imageType['width'].'px;}'; 

            if($imageType['name']==$gallery_image_type)
            {
                if($responsive_max==3 && $pro_thumnbs_per_fw)
                    $default_pro_thumbs_per = $pro_thumnbs_per_fw;
                else
                {
                    if($responsive_max==2)
                        $default_pro_thumbs_per = $pro_thumnbs_per_xxl;
                    else if($responsive_max>=1)
                        $default_pro_thumbs_per = $pro_thumnbs_per_xl;
                    else
                        $default_pro_thumbs_per = $pro_thumnbs_per_lg;
                }
                $pro_thumbs_class_name = ($product_thumbnails==1 || $product_thumbnails==2 || $product_thumbnails==7 ? ' .pro_gallery_top_inner' : '');

                $css .='.images-container'.$pro_thumbs_class_name.', .images-container .pro_gallery_thumbs_horizontal{margin-left: auto;margin-right:auto;}';
                $css .='.images-container.pro_number_1'.$pro_thumbs_class_name.', .images-container.pro_number_1 .pro_gallery_thumbs_horizontal{max-width: '.$imageType['width'].'px;}
                    .images-container.pro_number_2'.$pro_thumbs_class_name.', .images-container.pro_number_2 .pro_gallery_thumbs_horizontal{max-width: '.($imageType['width']*($default_pro_thumbs_per<2 ? $default_pro_thumbs_per : 2)+(($default_pro_thumbs_per<2 ? $default_pro_thumbs_per : 2)-1)*$gallery_spacing).'px;}
                    .images-container.pro_number_3'.$pro_thumbs_class_name.', .images-container.pro_number_3 .pro_gallery_thumbs_horizontal{max-width: '.($imageType['width']*($default_pro_thumbs_per<3 ? $default_pro_thumbs_per : 3)+(($default_pro_thumbs_per<3 ? $default_pro_thumbs_per : 3)-1)*$gallery_spacing).'px;}
                    .images-container.pro_number_4'.$pro_thumbs_class_name.', .images-container.pro_number_4 .pro_gallery_thumbs_horizontal{max-width: '.($imageType['width']*($default_pro_thumbs_per<4 ? $default_pro_thumbs_per : 4)+(($default_pro_thumbs_per<4 ? $default_pro_thumbs_per : 4)-1)*$gallery_spacing).'px;}
                    .images-container.pro_number_5'.$pro_thumbs_class_name.', .images-container.pro_number_5 .pro_gallery_thumbs_horizontal{max-width: '.($imageType['width']*($default_pro_thumbs_per<5 ? $default_pro_thumbs_per : 5)+(($default_pro_thumbs_per<5 ? $default_pro_thumbs_per : 5)-1)*$gallery_spacing).'px;}
                    .images-container.pro_number_6'.$pro_thumbs_class_name.', .images-container.pro_number_6 .pro_gallery_thumbs_horizontal{max-width: '.($imageType['width']*($default_pro_thumbs_per<6 ? $default_pro_thumbs_per : 6)+(($default_pro_thumbs_per<6 ? $default_pro_thumbs_per : 6)-1)*$gallery_spacing).'px;}';

                if($responsive_max==3 && $pro_thumnbs_per_fw)
                    $res_css .='@media (max-width: 1599px) {
                    .images-container.pro_number_xxl_1'.$pro_thumbs_class_name.', .images-container.pro_number_xxl_1 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']) > 1540 ? 1540 : ($imageType['width'])).'px;}
                    .images-container.pro_number_xxl_2'.$pro_thumbs_class_name.', .images-container.pro_number_xxl_2 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xxl<2 ? $pro_thumnbs_per_xxl : 2)+(($pro_thumnbs_per_xxl<2 ? $pro_thumnbs_per_xxl : 2)-1)*$gallery_spacing) > 1540 ? 1540 : ($imageType['width']*($pro_thumnbs_per_xxl<2 ? $pro_thumnbs_per_xxl : 2)+(($pro_thumnbs_per_xxl<2 ? $pro_thumnbs_per_xxl : 2)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xxl_3'.$pro_thumbs_class_name.', .images-container.pro_number_xxl_3 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xxl<3 ? $pro_thumnbs_per_xxl : 3)+(($pro_thumnbs_per_xxl<3 ? $pro_thumnbs_per_xxl : 3)-1)*$gallery_spacing) > 1540 ? 1540 : ($imageType['width']*($pro_thumnbs_per_xxl<3 ? $pro_thumnbs_per_xxl : 3)+(($pro_thumnbs_per_xxl<3 ? $pro_thumnbs_per_xxl : 3)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xxl_4'.$pro_thumbs_class_name.', .images-container.pro_number_xxl_4 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xxl<4 ? $pro_thumnbs_per_xxl : 4)+(($pro_thumnbs_per_xxl<4 ? $pro_thumnbs_per_xxl : 4)-1)*$gallery_spacing) > 1540 ? 1540 : ($imageType['width']*($pro_thumnbs_per_xxl<4 ? $pro_thumnbs_per_xxl : 4)+(($pro_thumnbs_per_xxl<4 ? $pro_thumnbs_per_xxl : 4)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xxl_5'.$pro_thumbs_class_name.', .images-container.pro_number_xxl_5 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xxl<5 ? $pro_thumnbs_per_xxl : 5)+(($pro_thumnbs_per_xxl<5 ? $pro_thumnbs_per_xxl : 5)-1)*$gallery_spacing) > 1540 ? 1540 : ($imageType['width']*($pro_thumnbs_per_xxl<5 ? $pro_thumnbs_per_xxl : 5)+(($pro_thumnbs_per_xxl<5 ? $pro_thumnbs_per_xxl : 5)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xxl_6'.$pro_thumbs_class_name.', .images-container.pro_number_xxl_6 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xxl<6 ? $pro_thumnbs_per_xxl : 6)+(($pro_thumnbs_per_xxl<6 ? $pro_thumnbs_per_xxl : 6)-1)*$gallery_spacing) > 1540 ? 1540 : ($imageType['width']*($pro_thumnbs_per_xxl<6 ? $pro_thumnbs_per_xxl : 6)+(($pro_thumnbs_per_xxl<6 ? $pro_thumnbs_per_xxl : 6)-1)*$gallery_spacing)).'px;}
                }';
                if($responsive_max>=2)
                    $res_css .='@media (max-width: 1439px) {
                    .images-container.pro_number_xl_1'.$pro_thumbs_class_name.', .images-container.pro_number_xl_1 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']) > 1380 ? 1380 : ($imageType['width'])).'px;}
                    .images-container.pro_number_xl_2'.$pro_thumbs_class_name.', .images-container.pro_number_xl_2 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xl<2 ? $pro_thumnbs_per_xl : 2)+(($pro_thumnbs_per_xl<2 ? $pro_thumnbs_per_xl : 2)-1)*$gallery_spacing) > 1380 ? 1380 : ($imageType['width']*($pro_thumnbs_per_xl<2 ? $pro_thumnbs_per_xl : 2)+(($pro_thumnbs_per_xl<2 ? $pro_thumnbs_per_xl : 2)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xl_3'.$pro_thumbs_class_name.', .images-container.pro_number_xl_3 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xl<3 ? $pro_thumnbs_per_xl : 3)+(($pro_thumnbs_per_xl<3 ? $pro_thumnbs_per_xl : 3)-1)*$gallery_spacing) > 1380 ? 1380 : ($imageType['width']*($pro_thumnbs_per_xl<3 ? $pro_thumnbs_per_xl : 3)+(($pro_thumnbs_per_xl<3 ? $pro_thumnbs_per_xl : 3)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xl_4'.$pro_thumbs_class_name.', .images-container.pro_number_xl_4 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xl<4 ? $pro_thumnbs_per_xl : 4)+(($pro_thumnbs_per_xl<4 ? $pro_thumnbs_per_xl : 4)-1)*$gallery_spacing) > 1380 ? 1380 : ($imageType['width']*($pro_thumnbs_per_xl<4 ? $pro_thumnbs_per_xl : 4)+(($pro_thumnbs_per_xl<4 ? $pro_thumnbs_per_xl : 4)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xl_5'.$pro_thumbs_class_name.', .images-container.pro_number_xl_5 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xl<5 ? $pro_thumnbs_per_xl : 5)+(($pro_thumnbs_per_xl<5 ? $pro_thumnbs_per_xl : 5)-1)*$gallery_spacing) > 1380 ? 1380 : ($imageType['width']*($pro_thumnbs_per_xl<5 ? $pro_thumnbs_per_xl : 5)+(($pro_thumnbs_per_xl<5 ? $pro_thumnbs_per_xl : 5)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xl_6'.$pro_thumbs_class_name.', .images-container.pro_number_xl_6 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xl<6 ? $pro_thumnbs_per_xl : 6)+(($pro_thumnbs_per_xl<6 ? $pro_thumnbs_per_xl : 6)-1)*$gallery_spacing) > 1380 ? 1380 : ($imageType['width']*($pro_thumnbs_per_xl<6 ? $pro_thumnbs_per_xl : 6)+(($pro_thumnbs_per_xl<6 ? $pro_thumnbs_per_xl : 6)-1)*$gallery_spacing)).'px;}
                }';
                if($responsive_max>=1)
                    $res_css .='@media (max-width: 1219px) {
                    .images-container.pro_number_lg_1'.$pro_thumbs_class_name.', .images-container.pro_number_lg_1 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']) > 1160 ? 1160 : ($imageType['width'])).'px;}
                    .images-container.pro_number_lg_2'.$pro_thumbs_class_name.', .images-container.pro_number_lg_2 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_lg<2 ? $pro_thumnbs_per_lg : 2)+(($pro_thumnbs_per_lg<2 ? $pro_thumnbs_per_lg : 2)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_lg<2 ? $pro_thumnbs_per_lg : 2)+(($pro_thumnbs_per_lg<2 ? $pro_thumnbs_per_lg : 2)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_lg_3'.$pro_thumbs_class_name.', .images-container.pro_number_lg_3 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_lg<3 ? $pro_thumnbs_per_lg : 3)+(($pro_thumnbs_per_lg<3 ? $pro_thumnbs_per_lg : 3)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_lg<3 ? $pro_thumnbs_per_lg : 3)+(($pro_thumnbs_per_lg<3 ? $pro_thumnbs_per_lg : 3)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_lg_4'.$pro_thumbs_class_name.', .images-container.pro_number_lg_4 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_lg<4 ? $pro_thumnbs_per_lg : 4)+(($pro_thumnbs_per_lg<4 ? $pro_thumnbs_per_lg : 4)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_lg<4 ? $pro_thumnbs_per_lg : 4)+(($pro_thumnbs_per_lg<4 ? $pro_thumnbs_per_lg : 4)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_lg_5'.$pro_thumbs_class_name.', .images-container.pro_number_lg_5 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_lg<5 ? $pro_thumnbs_per_lg : 5)+(($pro_thumnbs_per_lg<5 ? $pro_thumnbs_per_lg : 5)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_lg<5 ? $pro_thumnbs_per_lg : 5)+(($pro_thumnbs_per_lg<5 ? $pro_thumnbs_per_lg : 5)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_lg_6'.$pro_thumbs_class_name.', .images-container.pro_number_lg_6 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_lg<6 ? $pro_thumnbs_per_lg : 6)+(($pro_thumnbs_per_lg<6 ? $pro_thumnbs_per_lg : 6)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_lg<6 ? $pro_thumnbs_per_lg : 6)+(($pro_thumnbs_per_lg<6 ? $pro_thumnbs_per_lg : 6)-1)*$gallery_spacing)).'px;}
                }';
                $res_css .='@media (max-width: 991px) {
                    .images-container.pro_number_md_1'.$pro_thumbs_class_name.', .images-container.pro_number_md_1 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']) > 1160 ? 1160 : ($imageType['width'])).'px;}
                    .images-container.pro_number_md_2'.$pro_thumbs_class_name.', .images-container.pro_number_md_2 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_md<2 ? $pro_thumnbs_per_md : 2)+(($pro_thumnbs_per_md<2 ? $pro_thumnbs_per_md : 2)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_md<2 ? $pro_thumnbs_per_md : 2)+(($pro_thumnbs_per_md<2 ? $pro_thumnbs_per_md : 2)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_md_3'.$pro_thumbs_class_name.', .images-container.pro_number_md_3 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_md<3 ? $pro_thumnbs_per_md : 3)+(($pro_thumnbs_per_md<3 ? $pro_thumnbs_per_md : 3)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_md<3 ? $pro_thumnbs_per_md : 3)+(($pro_thumnbs_per_md<3 ? $pro_thumnbs_per_md : 3)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_md_4'.$pro_thumbs_class_name.', .images-container.pro_number_md_4 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_md<4 ? $pro_thumnbs_per_md : 4)+(($pro_thumnbs_per_md<4 ? $pro_thumnbs_per_md : 4)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_md<4 ? $pro_thumnbs_per_md : 4)+(($pro_thumnbs_per_md<4 ? $pro_thumnbs_per_md : 4)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_md_5'.$pro_thumbs_class_name.', .images-container.pro_number_md_5 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_md<5 ? $pro_thumnbs_per_md : 5)+(($pro_thumnbs_per_md<5 ? $pro_thumnbs_per_md : 5)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_md<5 ? $pro_thumnbs_per_md : 5)+(($pro_thumnbs_per_md<5 ? $pro_thumnbs_per_md : 5)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_md_6'.$pro_thumbs_class_name.', .images-container.pro_number_md_6 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_md<6 ? $pro_thumnbs_per_md : 6)+(($pro_thumnbs_per_md<6 ? $pro_thumnbs_per_md : 6)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_md<6 ? $pro_thumnbs_per_md : 6)+(($pro_thumnbs_per_md<6 ? $pro_thumnbs_per_md : 6)-1)*$gallery_spacing)).'px;}
                }';
                $res_css .='@media (max-width: 767px) {
                    .images-container.pro_number_sm_1'.$pro_thumbs_class_name.', .images-container.pro_number_sm_1 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']) > 1160 ? 1160 : ($imageType['width'])).'px;}
                    .images-container.pro_number_sm_2'.$pro_thumbs_class_name.', .images-container.pro_number_sm_2 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_sm<2 ? $pro_thumnbs_per_sm : 2)+(($pro_thumnbs_per_sm<2 ? $pro_thumnbs_per_sm : 2)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_sm<2 ? $pro_thumnbs_per_sm : 2)+(($pro_thumnbs_per_sm<2 ? $pro_thumnbs_per_sm : 2)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_sm_3'.$pro_thumbs_class_name.', .images-container.pro_number_sm_3 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_sm<3 ? $pro_thumnbs_per_sm : 3)+(($pro_thumnbs_per_sm<3 ? $pro_thumnbs_per_sm : 3)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_sm<3 ? $pro_thumnbs_per_sm : 3)+(($pro_thumnbs_per_sm<3 ? $pro_thumnbs_per_sm : 3)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_sm_4'.$pro_thumbs_class_name.', .images-container.pro_number_sm_4 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_sm<4 ? $pro_thumnbs_per_sm : 4)+(($pro_thumnbs_per_sm<4 ? $pro_thumnbs_per_sm : 4)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_sm<4 ? $pro_thumnbs_per_sm : 4)+(($pro_thumnbs_per_sm<4 ? $pro_thumnbs_per_sm : 4)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_sm_5'.$pro_thumbs_class_name.', .images-container.pro_number_sm_5 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_sm<5 ? $pro_thumnbs_per_sm : 5)+(($pro_thumnbs_per_sm<5 ? $pro_thumnbs_per_sm : 5)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_sm<5 ? $pro_thumnbs_per_sm : 5)+(($pro_thumnbs_per_sm<5 ? $pro_thumnbs_per_sm : 5)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_sm_6'.$pro_thumbs_class_name.', .images-container.pro_number_sm_6 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_sm<6 ? $pro_thumnbs_per_sm : 6)+(($pro_thumnbs_per_sm<6 ? $pro_thumnbs_per_sm : 6)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_sm<6 ? $pro_thumnbs_per_sm : 6)+(($pro_thumnbs_per_sm<6 ? $pro_thumnbs_per_sm : 6)-1)*$gallery_spacing)).'px;}
                }';
                $res_css .='@media (max-width: 479px) {
                    .images-container.pro_number_xs_1'.$pro_thumbs_class_name.', .images-container.pro_number_xs_1 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']) > 1160 ? 1160 : ($imageType['width'])).'px;}
                    .images-container.pro_number_xs_2'.$pro_thumbs_class_name.', .images-container.pro_number_xs_2 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xs<2 ? $pro_thumnbs_per_xs : 2)+(($pro_thumnbs_per_xs<2 ? $pro_thumnbs_per_xs : 2)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_xs<2 ? $pro_thumnbs_per_xs : 2)+(($pro_thumnbs_per_xs<2 ? $pro_thumnbs_per_xs : 2)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xs_3'.$pro_thumbs_class_name.', .images-container.pro_number_xs_3 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xs<3 ? $pro_thumnbs_per_xs : 3)+(($pro_thumnbs_per_xs<3 ? $pro_thumnbs_per_xs : 3)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_xs<3 ? $pro_thumnbs_per_xs : 3)+(($pro_thumnbs_per_xs<3 ? $pro_thumnbs_per_xs : 3)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xs_4'.$pro_thumbs_class_name.', .images-container.pro_number_xs_4 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xs<4 ? $pro_thumnbs_per_xs : 4)+(($pro_thumnbs_per_xs<4 ? $pro_thumnbs_per_xs : 4)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_xs<4 ? $pro_thumnbs_per_xs : 4)+(($pro_thumnbs_per_xs<4 ? $pro_thumnbs_per_xs : 4)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xs_5'.$pro_thumbs_class_name.', .images-container.pro_number_xs_5 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xs<5 ? $pro_thumnbs_per_xs : 5)+(($pro_thumnbs_per_xs<5 ? $pro_thumnbs_per_xs : 5)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_xs<5 ? $pro_thumnbs_per_xs : 5)+(($pro_thumnbs_per_xs<5 ? $pro_thumnbs_per_xs : 5)-1)*$gallery_spacing)).'px;}
                    .images-container.pro_number_xs_6'.$pro_thumbs_class_name.', .images-container.pro_number_xs_6 .pro_gallery_thumbs_horizontal{max-width: '.(($imageType['width']*($pro_thumnbs_per_xs<6 ? $pro_thumnbs_per_xs : 6)+(($pro_thumnbs_per_xs<6 ? $pro_thumnbs_per_xs : 6)-1)*$gallery_spacing) > 1160 ? 1160 : ($imageType['width']*($pro_thumnbs_per_xs<6 ? $pro_thumnbs_per_xs : 6)+(($pro_thumnbs_per_xs<6 ? $pro_thumnbs_per_xs : 6)-1)*$gallery_spacing)).'px;}
                }';
            } 
        }
        $css .= '.st_image_scrolling_item{margin-bottom:'.$gallery_spacing.'px;}';
        $css .= '.st_image_scrolling_wrap .st_image_scrolling_item {padding-left:'.ceil($gallery_spacing/2).'px;padding-right:'.floor($gallery_spacing/2).'px;}';
        $css .= '.st_image_scrolling_wrap .row{margin-left:-'.ceil($gallery_spacing/2).'px;margin-right:-'.floor($gallery_spacing/2).'px;}';

        $imagesTypes = ImageType::getImagesTypes('categories');
        foreach ($imagesTypes as $k=>$imageType) {
            $css .='.featured_categories_item .pro_outer_box.'.$imageType['name'].'{max-width: '.$imageType['width'].'px;}'; 
        }

        if($fontText)
    	   $css .='body{'.($fontText != $this->_font_inherit ? 'font-family:'.(preg_match("/[\d\s_]/", $fontText) ? '"'.$fontText.'"' : $fontText).', Tahoma, sans-serif, Arial;' : '').$fontTextWeight.$fontTextStyle.'}';
        if(Configuration::get('STSN_FONT_BODY_SIZE'))
            $css .='body{font-size: '.Configuration::get('STSN_FONT_BODY_SIZE').'px;}';  


        /*if($this->hasEasyBuilder()){
            $css .= 'body{'.$this->getFontCssWp('steb_body_fonts').'}';
        }*/

    	if($fontPrice)
        	$css .='.price,#our_price_display,.old_price,.sale_percentage{'.($fontPrice != $this->_font_inherit ? 'font-family:"'.$fontPrice.'", Tahoma, sans-serif, Arial;' : '').$fontPriceWeight.$fontPriceStyle.'}';
        $css .='.btn.ajax_add_to_cart_button, .btn.add-to-cart, .btn.view_button{'.(($fontCartBtn && $fontCartBtn != $this->_font_inherit) ? 'font-family:"'.$fontCartBtn.'", Tahoma, sans-serif, Arial;' : '').$fontCartBtnWeight.$fontCartBtnStyle.'}';
        // $css .= '.btn-default.btn_primary,.btn-small.btn_primary, .btn-medium.btn_primary, .btn-large.btn_primary{text-transform: '.self::$textTransform[(int)Configuration::get('STSN_FONT_HEADING_TRANS')]['name'].';}';
        
        $css_font_heading = $fontHeadingWeight.$fontHeadingStyle.'text-transform: '.self::$textTransform[(int)Configuration::get('STSN_FONT_HEADING_TRANS')]['name'].';'.($fontHeading != $fontText && $fontHeading != $this->_font_inherit ? 'font-family: "'.$fontHeading.'";' : '');
        
        $css_font_heading_size = '';
        if(Configuration::get('STSN_FONT_HEADING_SIZE'))
            $css_font_heading_size .='font-size: '.Configuration::get('STSN_FONT_HEADING_SIZE').'px;';            
            
        $css_font_menu = $css_font_mobile_menu = 'text-transform: '.self::$textTransform[(int)Configuration::get('STSN_FONT_MENU_TRANS')]['name'].';';
        if($fontMenu)
        {
            $css_font_menu .= ($this->_font_inherit != $fontMenu && $fontMenu != $fontText ? 'font-family: "'.$fontMenu.'";' : '').$fontMenuWeight.$fontMenuStyle;
            $this->_font_inherit != $fontMenu && $fontMenu != $fontText && $css_font_mobile_menu .= 'font-family: "'.$fontMenu.'";';
            $css .= '.style_wide .ma_level_1{'.($this->_font_inherit != $fontMenu && $fontMenu != $fontText ? 'font-family: "'.$fontMenu.'";' : '').$fontMenuWeight.$fontMenuStyle.'}';
        }
        if(Configuration::get('STSN_FONT_MENU_SIZE'))
            $css_font_menu .='font-size: '.Configuration::get('STSN_FONT_MENU_SIZE').'px;';
        if($menu_height = (int)Configuration::get('STSN_ST_MENU_HEIGHT'))
            $css .='#st_mega_menu_wrap .ma_level_0, .menu_item{height: '.$menu_height.'px;line-height: '.$menu_height.'px;}';
        //Removed this code .title_block a, .title_block span, from the line below, cause that code makes heading size changes in each module do not take effect.
        $css .= '.title_block .title_block_inner, .page_heading, .product_info_tabs .nav-tabs .nav-link, .pc_slider_block_container .nav-tabs .nav-link, .heading_font{'.$css_font_heading.'}';
        $css .= '.st-menu-title{'.$css_font_heading.'}';
        $css .= '.title_block .title_block_inner, .page_heading {'.$css_font_heading_size.'}';
        $css .= '.st_mega_menu .ma_level_0, .mobile_bar_tri_text, #st_mega_menu_column_mobile .mo_advanced_ma_level_0{'.$css_font_menu.'}'; 
        $css .= '#st_mobile_menu .mo_ma_level_0{'.$css_font_mobile_menu.'}'; 
        $css .= '.style_wide .ma_level_1{text-transform: '.self::$textTransform[(int)Configuration::get('STSN_FONT_MENU_TRANS')]['name'].';}'; 

        if($secondFontMenu)
             $css .= '.style_wide .ma_level_1{'.($secondFontMenu != $fontText && $this->_font_inherit != $secondFontMenu ? 'font-family: "'.$secondFontMenu.'";' : '').$secondFontMenuWeight.$secondFontMenuStyle.'}';
        if(Configuration::get('STSN_SECOND_FONT_MENU_SIZE'))
            $css .= '.style_wide .ma_level_1{font-size: '.Configuration::get('STSN_SECOND_FONT_MENU_SIZE').'px;}';
        if($thirdFontMenu)
             $css .= '.style_wide .mu_level_2 a.ma_item, .stmenu_multi_level a.ma_item,.mo_sub_a{'.($thirdFontMenu != $fontText && $this->_font_inherit != $thirdFontMenu ? 'font-family: "'.$thirdFontMenu.'";' : '').$thirdFontMenuWeight.$thirdFontMenuStyle.'}';
        if(Configuration::get('STSN_THIRD_FONT_MENU_SIZE'))
            $css .= '.style_wide .mu_level_2 a.ma_item, .stmenu_multi_level a.ma_item{font-size: '.Configuration::get('STSN_THIRD_FONT_MENU_SIZE').'px;}';

        if($verFontMenu)
             $css .= '.mv_item{'.($verFontMenu != $fontText && $this->_font_inherit != $verFontMenu ? 'font-family: "'.$verFontMenu.'";' : '').$verFontMenuWeight.$verFontMenuStyle.'}';
        if(Configuration::get('STSN_VER_FONT_MENU_SIZE'))
            $css .= '.mv_item{font-size: '.Configuration::get('STSN_VER_FONT_MENU_SIZE').'px;}';
        
        if($fontProductName)
            $css .='.product_name_wrap .product_name{'.($fontProductName != $this->_font_inherit && $fontProductName != $fontText ? 'font-family:"'.$fontProductName.'";' : '').'}';
        if($fontProductNameWeight)
            $css .='.product_name_wrap .product_name{'.$fontProductNameWeight.'}';
        if($fontProductNameStyle)
            $css .='.product_name_wrap .product_name{'.$fontProductNameStyle.'}';

        if(Configuration::get('STSN_FONT_PRODUCT_NAME_SIZE'))
            $css .='.product_name_wrap .product_name{font-size: '.Configuration::get('STSN_FONT_PRODUCT_NAME_SIZE').'px;}';
        if(Configuration::get('STSN_FONT_PRODUCT_NAME_TRANS'))
            $css .='.product_name_wrap .product_name{text-transform: '.self::$textTransform[(int)Configuration::get('STSN_FONT_PRODUCT_NAME_TRANS')]['name'].';}';
        if(Configuration::get('STSN_FONT_PRODUCT_NAME_COLOR'))
            $css .='.product_name_wrap .product_name{color: '.Configuration::get('STSN_FONT_PRODUCT_NAME_COLOR').';}';
        
        if(Configuration::get('STSN_FONT_PRICE_SIZE'))
            $css .='.ajax_block_product .price{font-size: '.Configuration::get('STSN_FONT_PRICE_SIZE').'px;}'; 
        if(Configuration::get('STSN_FONT_MAIN_PRICE_SIZE'))
            $css .='.product-prices .current-price .price{font-size: '.Configuration::get('STSN_FONT_MAIN_PRICE_SIZE').'px;}';  
        if(Configuration::get('STSN_FONT_OLD_PRICE_SIZE'))
            $css .='.ajax_block_product .regular-price{font-size: '.Configuration::get('STSN_FONT_OLD_PRICE_SIZE').'px;}';     
            
        if(Configuration::get('STSN_FOOTER_HEADING_SIZE'))
            $css .='#footer .title_block .title_block_inner{font-size: '.Configuration::get('STSN_FOOTER_HEADING_SIZE').'px;}';
            
        if(Configuration::get('STSN_BLOCK_HEADINGS_COLOR'))
            $css .='.title_block .title_block_inner,.page_heading, .table-bordered thead th, .heading_color, .card-header{color: '.Configuration::get('STSN_BLOCK_HEADINGS_COLOR').';}';
        if(Configuration::get('STSN_COLUMN_BLOCK_HEADINGS_COLOR'))
            $css .='#left_column .title_block .title_block_inner, #right_column .title_block .title_block_inner{color: '.Configuration::get('STSN_COLUMN_BLOCK_HEADINGS_COLOR').';}';
        /*
        if(Configuration::get('STSN_HEADINGS_COLOR'))
            $css .='.heading,.page-heading,.page-subheading, a.heading,a.page-heading,a.page-subheading{color: '.Configuration::get('STSN_HEADINGS_COLOR').';}';
        */    
        
        $heading_style = (int)Configuration::get('STSN_HEADING_STYLE');

        $heading_bottom_border = (int)Configuration::get('STSN_HEADING_BOTTOM_BORDER');
        $heading_column_bottom_border = (int)Configuration::get('STSN_HEADING_COLUMN_BOTTOM_BORDER');

        if($heading_style==1){
            $css .= '.title_style_1 .flex_child,.title_style_4 .flex_child{border-bottom-width:'.$heading_bottom_border.'px;}';
        }elseif($heading_style==3){
            $css .= '.title_style_3 .flex_child{border-bottom-width:'.$heading_bottom_border.'px;}';
        }elseif($heading_style==2){
            $css .= '.title_style_2 .flex_child{border-top-width:'.$heading_bottom_border.'px;border-bottom-width:'.$heading_bottom_border.'px;}';
        }elseif($heading_style==5){
            $css .= '.title_style_5 .title_block_inner:after{height:'.$heading_bottom_border.'px;}';
        }else{
            $css .= '.title_style_0,.title_style_0 .title_block_inner{border-bottom-width:'.$heading_bottom_border.'px;}.title_style_0 .title_block_inner{margin-bottom:-'.$heading_bottom_border.'px;}';
        }
        
        /*$css .= '#left_column .title_block,#left_column .title_block .title_block_inner, #right_column .title_block,#right_column .title_block .title_block_inner{border-bottom-width:'.$heading_column_bottom_border.'px;}#left_column .title_block .title_block_inner,#right_column .title_block .title_block_inner{margin-bottom:-'.$heading_column_bottom_border.'px;}';*/
        
        if(Configuration::get('STSN_HEADING_BOTTOM_BORDER_COLOR')){
            $css .='.title_style_0, .title_style_1 .flex_child, .title_style_4 .flex_child, .title_style_2 .flex_child, .title_style_3 .flex_child{border-color: '.Configuration::get('STSN_HEADING_BOTTOM_BORDER_COLOR').';}';  
            $css .='.title_style_5 .title_block_inner:after{background-color: '.Configuration::get('STSN_HEADING_BOTTOM_BORDER_COLOR').';}';  
        }
        if(Configuration::get('STSN_HEADING_BOTTOM_BORDER_COLOR_H'))
            $css .='.title_style_0 .title_block_inner{border-color: '.Configuration::get('STSN_HEADING_BOTTOM_BORDER_COLOR_H').';}';  
        if(Configuration::get('STSN_HEADING_COLUMN_BG'))
            $css .='#left_column .title_block,#right_column .title_block{background-color: '.Configuration::get('STSN_HEADING_COLUMN_BG').';padding-left:6px;}.is_rtl #left_column .title_block,.is_rtl #right_column .title_block{padding-right:6px;padding-left:0;}';  

        if (Configuration::get('STSN_HEADING_BG_PATTERN') && (Configuration::get('STSN_HEADING_BG_IMG')==""))
            $css .= '.title_style_0 .flex_child,.title_style_2 .flex_child,.title_style_3 .flex_child{background-image: url(../../patterns/heading_bg/'.Configuration::get('STSN_HEADING_BG_PATTERN').'.png);}';
        if ($bg_image = Configuration::get('STSN_HEADING_BG_IMG')) {
            $this->fetchMediaServer($bg_image);
            $css .= '.title_style_0 .flex_child,.title_style_2 .flex_child,.title_style_3 .flex_child{background-image:url('.$bg_image.');}';
        }
        if(Configuration::get('STSN_F_TOP_H_COLOR'))
            $css .='#footer-primary .title_block .title_block_inner{color: '.Configuration::get('STSN_F_TOP_H_COLOR').';}';
        if(Configuration::get('STSN_F_TOP_H_ALIGN'))
            $css .= '#footer-primary .title_block{ text-align: '.(Configuration::get('STSN_F_TOP_H_ALIGN')==1 ? 'center' : 'right').'; }';
        if(Configuration::get('STSN_FOOTER_H_COLOR'))
            $css .='#footer-secondary .title_block .title_block_inner{color: '.Configuration::get('STSN_FOOTER_H_COLOR').';}';
        if(Configuration::get('STSN_FOOTER_H_ALIGN'))
            $css .= '#footer-secondary .title_block{ text-align: '.(Configuration::get('STSN_FOOTER_H_ALIGN')==1 ? 'center' : 'right').'; }';
        if(Configuration::get('STSN_F_SECONDARY_H_COLOR'))
            $css .='#footer-tertiary .title_block .title_block_inner{color: '.Configuration::get('STSN_F_SECONDARY_H_COLOR').';}';
        if(Configuration::get('STSN_F_SECONDARY_H_ALIGN'))
            $css .= '#footer-tertiary .title_block{ text-align: '.(Configuration::get('STSN_F_SECONDARY_H_ALIGN')==1 ? 'center' : 'right').'; }';
            
        //color
        if(Configuration::get('STSN_TEXT_COLOR'))
            $css .='body,a.text_color{color: '.Configuration::get('STSN_TEXT_COLOR').';}';
        if(Configuration::get('STSN_LINK_COLOR'))
            $css .='a,.link_color, a:focus,
        .additional-information a.link_color
        .dropdown_tri,
        .checkout-step .sttab_2_1 .nav-tabs .nav-link,
        .add_to_love, .add_to_love:hover,
        .add_to_wishlit, .add_to_wishlit:hover,
        .stcompare_add, .stcompare_add:hover{color: '.Configuration::get('STSN_LINK_COLOR').';}';
        if(Configuration::get('STSN_S_TITLE_BLOCK_COLOR'))
            $css .='.ajax_block_product .s_title_block a{color: '.Configuration::get('STSN_S_TITLE_BLOCK_COLOR').';}';

        if($link_hover_color = Configuration::get('STSN_LINK_HOVER_COLOR'))
        {
            //a:focus, a.link_color:focus, button.link_color:focus, .additional-information a.link_color:focus, .btn-link:focus,
            $css .='a:hover, 
            a.link_color:hover, 
            button.link_color:hover,
            .additional-information a.link_color:hover,
            .theme_color, a.theme_color, a.theme_color:hover,
            .st_added,
            .add_to_love.st_added, .add_to_love.st_added:hover,
            .add_to_wishlit.st_added, .add_to_wishlit.st_added:hover,
            .stcompare_add.st_added, .stcompare_add.st_added:hover,
            .dropdown_wrap.open .dropdown_tri,
            .ajax_block_product .s_title_block a:hover,
            .block_blog .s_title_block a:hover,
            .btn-link:hover{color: '.$link_hover_color.';}';
            $css .='a.text_color:hover{color: '.$link_hover_color.';}';
            $css .='.title_block a.title_block_inner:hover{color: '.$link_hover_color.';}';
            $css .= '#st_mega_menu_wrap .ml_level_0.current .ma_level_0,#st_mega_menu_wrap .ma_level_0:hover{border-bottom-color:'.$link_hover_color.';}';
            $css .= '.checkout-step .sttab_2_1 .nav-tabs .nav-link.active, .checkout-step .sttab_2_1 .nav-tabs .nav-link:hover, .checkout-step .sttab_2_1 .nav-tabs .nav-link:focus{color:'.$link_hover_color.';}';
        }

        if(Configuration::get('STSN_PRODUCT_REFERENCE_CODE_COLOR'))
            $css .='.product-reference .pro_extra_info_content.flex_child{color: '.Configuration::get('STSN_PRODUCT_REFERENCE_CODE_COLOR').';}';
        if(Configuration::get('STSN_PRODUCT_REFERENCE_CODE_FONT_SIZE')){
            $css .='.product-reference .pro_extra_info_content.flex_child{font-size: '.Configuration::get('STSN_PRODUCT_REFERENCE_CODE_FONT_SIZE').'px;}'; 
        }
        if($productReferenceCodeString = Configuration::get('STSN_PRODUCT_REFERENCE_CODE_FONT'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $productReferenceCodeString, $productReferenceCodeArr);
            $fontPrice = $productReferenceCodeArr[1][0];
            $css.= '.product-reference .pro_extra_info_content.flex_child{'.($fontPrice != $this->_font_inherit ? 'font-family:"'.$fontPrice.'", Tahoma, sans-serif, Arial;' : '').$productReferenceCodeArr[2][0].$productReferenceCodeArr[3][0].'}';
        }

        if(Configuration::get('STSN_PRICE_COLOR'))
            $css .='.price{color: '.Configuration::get('STSN_PRICE_COLOR').';}';
        if(Configuration::get('STSN_OLD_PRICE_COLOR'))
            $css .='.regular-price{color: '.Configuration::get('STSN_OLD_PRICE_COLOR').';}';
        if(Configuration::get('STSN_DISCOUNT_COLOR'))
            $css .='.discount-percentage,.discount-amount{color: '.Configuration::get('STSN_DISCOUNT_COLOR').';}';
        if(Configuration::get('STSN_DISCOUNT_BG'))
            $css .='.discount-percentage,.discount-amount{background-color: '.Configuration::get('STSN_DISCOUNT_BG').';}';

        if(Configuration::get('STSN_ICON_COLOR'))
            $css .='a.icon_wrap, .icon_wrap,#shopping_cart .ajax_cart_right{color: '.Configuration::get('STSN_ICON_COLOR').';}';
        if(Configuration::get('STSN_ICON_HOVER_COLOR'))
            $css .='a.icon_wrap.active,.icon_wrap.active,a.icon_wrap:hover,.icon_wrap:hover,#searchbox_inner.active #submit_searchbox.icon_wrap,.logo_center #searchbox_inner:hover #submit_searchbox.icon_wrap,#shopping_cart:hover .icon_wrap,#shopping_cart.active .icon_wrap,.myaccount-link-list a:hover .icon_wrap{color: '.Configuration::get('STSN_ICON_HOVER_COLOR').';}';
        if($icon_bg_color = Configuration::get('STSN_ICON_BG_COLOR'))
            $css .='a.icon_wrap, .icon_wrap,#shopping_cart .ajax_cart_right{background-color: '.$icon_bg_color.';}';    
        if($icon_hover_bg_color = Configuration::get('STSN_ICON_HOVER_BG_COLOR'))
        {
            $css .='a.icon_wrap.active,.icon_wrap.active,a.icon_wrap:hover,.icon_wrap:hover,#searchbox_inner.active #submit_searchbox.icon_wrap,.logo_center #searchbox_inner:hover #submit_searchbox.icon_wrap,#shopping_cart:hover .icon_wrap,#shopping_cart.active .icon_wrap,.myaccount-link-list a:hover .icon_wrap{background-color: '.$icon_hover_bg_color.';}';    
            $css .='#submit_searchbox:hover,#searchbox_inner.active #search_query_top,#searchbox_inner.active #submit_searchbox.icon_wrap,.logo_center #searchbox_inner:hover #submit_searchbox.icon_wrap,#shopping_cart.active .icon_wrap,#shopping_cart:hover .icon_wrap{border-color:'.$icon_hover_bg_color.';}';
        }
        if(Configuration::get('STSN_ICON_DISABLED_COLOR'))
            $css .='a.icon_wrap.disabled,.icon_wrap.disabled{color: '.Configuration::get('STSN_ICON_DISABLED_COLOR').';}';
        if($right_panel_border = Configuration::get('STSN_RIGHT_PANEL_BORDER'))
        {
            $css .='#rightbar,#leftbar{border: 1px solid '.$right_panel_border.';}';
            $res_css .= '@media (max-width: 991px) {#sidebar_box{border: 1px solid '.$right_panel_border.';}#rightbar,#leftbar{border:none;}}';
        }
        if(Configuration::get('STSN_STARTS_COLOR'))
            $css .='div.star.star_on:after,div.star.star_hover:after,.rating_box i.light{color: '.Configuration::get('STSN_STARTS_COLOR').';}';
        if(Configuration::get('STSN_CIRCLE_NUMBER_COLOR'))
            $css .='.amount_circle{color: '.Configuration::get('STSN_CIRCLE_NUMBER_COLOR').';}';
        if(Configuration::get('STSN_CIRCLE_NUMBER_BG'))
            $css .='.amount_circle{background-color: '.Configuration::get('STSN_CIRCLE_NUMBER_BG').';}';
          
        if($cart_icon_border_color = Configuration::get('STSN_CART_ICON_BORDER_COLOR'))
            $css .='.ajax_cart_bag, .ajax_cart_bag .ajax_cart_bg_handle{border-color: '.$cart_icon_border_color.';}.st_shopping_cart.header_item .fto-glyph{color: '.$cart_icon_border_color.';}';
        if(Configuration::get('STSN_CART_ICON_BG_COLOR'))
            $css .='.ajax_cart_bag{background-color: '.Configuration::get('STSN_CART_ICON_BG_COLOR').';}';
        if(Configuration::get('STSN_CART_NUMBER_COLOR'))
            $css .='.st_shopping_cart.header_item .amount_circle{color: '.Configuration::get('STSN_CART_NUMBER_COLOR').';}';
        if(Configuration::get('STSN_CART_NUMBER_BG_COLOR'))
            $css .='.st_shopping_cart.header_item .amount_circle{background-color: '.Configuration::get('STSN_CART_NUMBER_BG_COLOR').';}';
        if(Configuration::get('STSN_CART_NUMBER_BORDER_COLOR'))
            $css .='.st_shopping_cart.header_item .amount_circle{border-color: '.Configuration::get('STSN_CART_NUMBER_BORDER_COLOR').';}';

        //button  
        $button_css = $button_hover_css = $primary_button_css = $primary_button_hover_css = '';   
        if(Configuration::get('STSN_BTN_COLOR'))   
            $button_css .='color: '.Configuration::get('STSN_BTN_COLOR').';';
        if(Configuration::get('STSN_BTN_HOVER_COLOR'))   
            $button_hover_css .='color: '.Configuration::get('STSN_BTN_HOVER_COLOR').';';
        if(Configuration::get('STSN_BTN_BORDER_COLOR'))   
            $button_css .='border-color:'.Configuration::get('STSN_BTN_BORDER_COLOR').';';

        $btn_bg_color = Configuration::get('STSN_BTN_BG_COLOR');
        if($btn_bg_color)   
            $button_css .='background-color: '.$btn_bg_color.';';
        /*if($this->hasEasyBuilder()){
            $button_css .= $this->getBackgroundCssWp('steb_btn_bg_color');
        }*/

        /*$btn_hover_bg_color = Configuration::get('STSN_BTN_HOVER_BG_COLOR');
        if(!$btn_hover_bg_color)
            $btn_hover_bg_color = '#444444';
        elseif($btn_hover_bg_color && !$btn_bg_color){
            $button_css .='-webkit-box-shadow: inset 0 0 0 0.01px '.$btn_hover_bg_color.'; box-shadow: inset 0 0 0 0.01px '.$btn_hover_bg_color.';';
        }
        
        $button_hover_css .='border-color:'.$btn_hover_bg_color.';';*/

        $primary_button_css = $primary_button_hover_css = $res_primary_button_hover_css = '';
        if($primary_btn_color = Configuration::get('STSN_PRIMARY_BTN_COLOR'))   
            $primary_button_css .='color: '.$primary_btn_color.';';
        if($primary_btn_hover_color = Configuration::get('STSN_PRIMARY_BTN_HOVER_COLOR'))   
            $primary_button_hover_css .='color: '.$primary_btn_hover_color.';';
        if($primary_btn_bg_color = Configuration::get('STSN_PRIMARY_BTN_BG_COLOR'))   
            $primary_button_css .='background-color: '.$primary_btn_bg_color.';';
        if($primary_btn_border_color = Configuration::get('STSN_PRIMARY_BTN_BORDER_COLOR'))   
            $primary_button_css .='border-color:'.$primary_btn_border_color.';';
        $primary_btn_hover_bg_color = Configuration::get('STSN_PRIMARY_BTN_HOVER_BG_COLOR');
        if($primary_btn_hover_bg_color)   
            $primary_button_hover_css .='background-color: '.$primary_btn_hover_bg_color.';';
        if($primary_btn_hover_border_color = Configuration::get('STSN_PRIMARY_BTN_HOVER_BORDER_COLOR'))   
            $primary_button_hover_css .='border-color: '.$primary_btn_hover_border_color.';';
        /*if($this->hasEasyBuilder()){
            $primary_button_css .= $this->getBackgroundCssWp('steb_primary_btn_bg_color');
            $primary_button_hover_css .= $this->getBackgroundCssWp('steb_primary_btn_hover_bg_color');
        }*/

        $btn_white_hover = '';

        if($btn_hover_bg_color = Configuration::get('STSN_BTN_HOVER_BG_COLOR')){
            $button_hover_css .= '-webkit-box-shadow: none; box-shadow: none;border-color: '.$btn_hover_bg_color.';background-color: '.$btn_hover_bg_color.';';
            $btn_white_hover .= 'border-color:#ffffff;-webkit-box-shadow: none; box-shadow: none;background-color: #ffffff;color:#444444;';
        }
        /*if($this->hasEasyBuilder()){
            $button_hover_css .= $this->getBackgroundCssWp('steb_btn_hover_bg_color');
        }*/
        if($btn_hover_border_color = Configuration::get('STSN_BTN_HOVER_BORDER_COLOR')){
            $button_hover_css .= 'border-color: '.$btn_hover_border_color.';';
        }

        if($p_btn_color = Configuration::get('STSN_P_BTN_COLOR'))   
            $css .= '.btn.btn-default.benabled{color:'.$p_btn_color.';}';
        if($p_btn_hover_color = Configuration::get('STSN_P_BTN_HOVER_COLOR'))   
            $css .= '.btn.btn-default.benabled:hover,.btn.btn-default.benabled:focus{color:'.$p_btn_hover_color.';}';
        if($p_btn_bg_color = Configuration::get('STSN_P_BTN_BG_COLOR'))   
            $css .= '.btn.btn-default.benabled{background-color:'.$p_btn_bg_color.';border-color:'.$p_btn_bg_color.';}';
        if($p_btn_hover_bg_color = Configuration::get('STSN_P_BTN_HOVER_BG_COLOR'))
            $css .= '.btn.btn-default.benabled:hover,.btn.btn-default.benabled:focus{background-color:'.$p_btn_hover_bg_color.';border-color:'.$p_btn_hover_bg_color.';}';
        
        if($button_css)
        {
            $css .= '.btn-default{'.$button_css.'}';
            // $css .= '.btn-default.benabled{'.$button_hover_css.'-webkit-box-shadow: none; box-shadow: none;background-color: '.$btn_hover_bg_color.';}';
        }
        
        if($button_hover_css)
        {
            // $css .= '.btn-default:hover,.btn-default:focus,.itemlist_right .ajax_add_to_cart_button:hover, .itemlist_right .view_button:hover{'.$button_hover_css.'}';
            $css .= '.btn-default:hover,.btn-default:focus{'.$button_hover_css.'}';
            /*$btn_hover_bg_color_arr = self::hex2rgb($btn_hover_bg_color);
            if(is_array($btn_hover_bg_color_arr))
            {
                $css .= '.btn-default.benabled,.btn-default.benabled:hover{'.$button_hover_css.'-webkit-box-shadow: none; box-shadow: none;background-color:'.$btn_hover_bg_color.';}';
                $css .= '.btn-default.benabled:hover{background-color: rgba('.$btn_hover_bg_color_arr[0].','.$btn_hover_bg_color_arr[1].','.$btn_hover_bg_color_arr[2].', 0.8);}';
                $css .= '.btn-default.benabled.btn-full-width:hover{'.$button_hover_css.'-webkit-box-shadow: none; box-shadow: none;background-color: rgba('.$btn_hover_bg_color_arr[0].','.$btn_hover_bg_color_arr[1].','.$btn_hover_bg_color_arr[2].', 0.8);}.mobile_device .btn-default.benabled.btn-mobile-full-width:hover{'.$button_hover_css.'-webkit-box-shadow: none; box-shadow: none;background-color: rgba('.$btn_hover_bg_color_arr[0].','.$btn_hover_bg_color_arr[1].','.$btn_hover_bg_color_arr[2].', 0.8);}@media (max-width: 992px) {.btn-default.benabled.btn-mobile-full-width:hover{'.$button_hover_css.'-webkit-box-shadow: none; box-shadow: none;background-color: rgba('.$btn_hover_bg_color_arr[0].','.$btn_hover_bg_color_arr[1].','.$btn_hover_bg_color_arr[2].', 0.8);}}';
            }*/
        }

        $css .= '.btn-white:hover,.btn-white:focus{'.$btn_white_hover.'}';


        if(Configuration::get('STSN_BTN_TRANS'))
            $css .='.btn,.hover_fly_btn{text-transform: '.self::$textTransform[(int)Configuration::get('STSN_BTN_TRANS')]['name'].';}';

        if($primary_button_css)
            $css .= '.btn.ajax_add_to_cart_button, .btn.add-to-cart{'.$primary_button_css.'}';
        if($primary_button_hover_css)
            $css .= '.btn.ajax_add_to_cart_button:hover, .btn.add-to-cart:hover{'.$primary_button_hover_css.'}';

        if($res_primary_button_hover_css)
            $res_css .= '.btn-default.btn-full-width:hover{'.$res_primary_button_hover_css.'}.mobile_device .btn-default.btn-mobile-full-width:hover{'.$res_primary_button_hover_css.'}@media (max-width: 992px) {.btn-default.btn-mobile-full-width:hover{'.$res_primary_button_hover_css.'}}';


          
        if(Configuration::get('STSN_FLYOUT_BUTTONS_COLOR')){
            $css .='.hover_fly .hover_fly_btn,.hover_fly_0:hover .hover_fly_btn:first-child{color: '.Configuration::get('STSN_FLYOUT_BUTTONS_COLOR').';}';
            $css .='.hover_fly .hover_fly_btn.add_to_love, .hover_fly .hover_fly_btn.add_to_love:hover, .hover_fly .hover_fly_btn.add_to_wishlit, .hover_fly .hover_fly_btn.add_to_wishlit:hover, .hover_fly .hover_fly_btn.stcompare_add, .hover_fly .hover_fly_btn.stcompare_add:hover{color: '.Configuration::get('STSN_FLYOUT_BUTTONS_COLOR').';}';
        }
        if(Configuration::get('STSN_FLYOUT_BUTTONS_HOVER_COLOR'))   
        {
            $css .='.hover_fly_0 .hover_fly_btn:first-child, .hover_fly_0:hover .hover_fly_btn:first-child:hover{color: '.Configuration::get('STSN_FLYOUT_BUTTONS_HOVER_COLOR').';}';
            $css .='.hover_fly .hover_fly_btn:hover{color: '.Configuration::get('STSN_FLYOUT_BUTTONS_HOVER_COLOR').';}';
            $css .='.hover_fly .hover_fly_btn.add_to_love.st_added, .hover_fly .hover_fly_btn.add_to_love.st_added:hover, .hover_fly .hover_fly_btn.add_to_wishlit.st_added, .hover_fly .hover_fly_btn.add_to_wishlit.st_added:hover, .hover_fly .hover_fly_btn.stcompare_add.st_added, .hover_fly .hover_fly_btn.stcompare_add.st_added:hover{color: '.Configuration::get('STSN_FLYOUT_BUTTONS_HOVER_COLOR').';}';
        }
        if(Configuration::get('STSN_FLYOUT_BUTTONS_BG')){
            $css .='.hover_fly, .hover_fly .hover_fly_btn,.hover_fly_0:hover .hover_fly_btn:first-child{background-color: '.Configuration::get('STSN_FLYOUT_BUTTONS_BG').';}';
            $css .='.hover_fly .hover_fly_btn.add_to_love, .hover_fly .hover_fly_btn.add_to_love:hover, .hover_fly .hover_fly_btn.add_to_wishlit, .hover_fly .hover_fly_btn.add_to_wishlit:hover, .hover_fly .hover_fly_btn.stcompare_add, .hover_fly .hover_fly_btn.stcompare_add:hover{background-color: '.Configuration::get('STSN_FLYOUT_BUTTONS_BG').';}';
        }
        if(Configuration::get('STSN_FLYOUT_BUTTONS_HOVER_BG'))   
        {
            $css .='.hover_fly_0 .hover_fly_btn:first-child, .hover_fly_0:hover .hover_fly_btn:first-child:hover{background-color: '.Configuration::get('STSN_FLYOUT_BUTTONS_HOVER_BG').';}';
            $css .='.hover_fly .hover_fly_btn:hover{background-color: '.Configuration::get('STSN_FLYOUT_BUTTONS_HOVER_BG').'!important;}';
            $css .='.hover_fly .hover_fly_btn.add_to_love.st_added, .hover_fly .hover_fly_btn.add_to_love.st_added:hover, .hover_fly .hover_fly_btn.add_to_wishlit.st_added, .hover_fly .hover_fly_btn.add_to_wishlit.st_added:hover, .hover_fly .hover_fly_btn.stcompare_add.st_added, .hover_fly .hover_fly_btn.stcompare_add.st_added:hover{background-color: '.Configuration::get('STSN_FLYOUT_BUTTONS_HOVER_BG').';}';
        }
        
        if(Configuration::get('STSN_FLYOUT_SEPARATORS_COLOR'))   
            $css .='.hover_fly .hover_fly_btn{border-right-color: '.Configuration::get('STSN_FLYOUT_SEPARATORS_COLOR').';border-left-color: '.Configuration::get('STSN_FLYOUT_SEPARATORS_COLOR').';}';
        
        //header
        if($header_text_color = Configuration::get('STSN_HEADER_TEXT_COLOR'))
        {
            $css .= '#header_primary .top_bar_item .header_item, .checkout_mobile_bar_tri{color:'.$header_text_color.';}';
            // $css .= '#mobile_bar_cart_tri .ajax_cart_bag, .cart_mobile_bar_tri .ajax_cart_bag .ajax_cart_bg_handle, .cart_mobile_bar_tri .ajax_cart_bag .amount_circle{border-color:'.$header_text_color.';}.cart_mobile_bar_tri .ajax_cart_bag .amount_circle{color:#ffffff;}';
        }
        
        if(Configuration::get('STSN_TOPBAR_TEXT_COLOR'))
            $css .='#top_bar .top_bar_item .header_item{color:'.Configuration::get('STSN_TOPBAR_TEXT_COLOR').';}';

        if(Configuration::get('STSN_HEADER_TEXT_TRANS'))
            $css .='#st_header .header_item{text-transform: '.self::$textTransform[(int)Configuration::get('STSN_HEADER_TEXT_TRANS')]['name'].';}';
        if($header_link_hover_color = Configuration::get('STSN_HEADER_LINK_HOVER_COLOR'))
            $css .='#header_primary .top_bar_item .header_item:hover,#header_primary .dropdown_wrap.open .dropdown_tri, .checkout_mobile_bar_tri:hover{color:'.$header_link_hover_color.';}';
        if($topbar_link_hover_color = Configuration::get('STSN_TOPBAR_LINK_HOVER_COLOR'))
            $css .='#top_bar .top_bar_item .header_item:hover,#top_bar .dropdown_wrap.open .dropdown_tri{color:'.$topbar_link_hover_color.';}';
        if(Configuration::get('STSN_HEADER_LINK_HOVER_BG'))
            $css .='#top_bar .top_bar_item .header_item:hover,#top_bar .dropdown_wrap.open .dropdown_tri{background-color:'.Configuration::get('STSN_HEADER_LINK_HOVER_BG').';}';
        if(Configuration::get('STSN_DROPDOWN_HOVER_COLOR'))
            $css .='#st_header .dropdown_list li a:hover{color:'.Configuration::get('STSN_DROPDOWN_HOVER_COLOR').';}';   
        if(Configuration::get('STSN_DROPDOWN_BG_COLOR'))
            $css .='#st_header .dropdown_list li a:hover{background-color:'.Configuration::get('STSN_DROPDOWN_BG_COLOR').';}'; 
        if(Configuration::get('STSN_HEADER_TOPBAR_BG'))
            $css .='#top_bar{background-color:'.Configuration::get('STSN_HEADER_TOPBAR_BG').';}'; 
        $topbar_b_border = Configuration::get('STSN_TOPBAR_B_BORDER');
        if($topbar_b_border)
            $css .='#st_header #top_bar '.($topbar_b_border>20 ? ' #top_bar_container' : '').'{border-bottom-width:'.($topbar_b_border%10).'px;border-bottom-style: solid;}';
        else
             $css .='#st_header #top_bar, #st_header #top_bar #top_bar_container{border-bottom:none;}'; 
        if(Configuration::get('STSN_TOPBAR_B_BORDER_COLOR'))
            $css .='#st_header #top_bar, #st_header #top_bar #top_bar_container{border-bottom-color:'.Configuration::get('STSN_TOPBAR_B_BORDER_COLOR').';}'; 
        if(Configuration::get('STSN_HEADER_TOPBAR_SEP'))
            $css .='.nav_bar.vertical-s .top_bar_item:before,.nav_bar.horizontal-s .top_bar_item:before,.nav_bar.space-s .top_bar_item:before,.nav_bar.horizontal-s-fullheight .top_bar_item:before{background-color:'.Configuration::get('STSN_HEADER_TOPBAR_SEP').';}'; 
        if($topbar_height = (int)Configuration::get('STSN_TOPBAR_HEIGHT'))
        {
            $css .= '.nav_bar .header_item{height:'.$topbar_height.'px;line-height:'.$topbar_height.'px;}';
            $css .= '.nav_bar.horizontal-s-fullheight .top_bar_item:before{height:'.$topbar_height.'px;}';
        }

        if($header_bottom_border = Configuration::get('STSN_HEADER_BOTTOM_BORDER'))
            $css .='#header_primary '.($header_bottom_border>20 ? ' #header_primary_container' : '').'{border-bottom-width:'.($header_bottom_border%10).'px;border-bottom-style: solid;}';
        if (Configuration::get('STSN_HEADER_BOTTOM_BORDER_COLOR'))
            $css .= '#header_primary, #header_primary #header_primary_container{border-bottom-color:'.Configuration::get('STSN_HEADER_BOTTOM_BORDER_COLOR').';}';
        
        $sticky_opacity = (float)Configuration::get('STSN_STICKY_FILTER_BG_OPACITY');
        if($sticky_opacity<0 || $sticky_opacity>1)
            $sticky_opacity = 0.95;
        if($sticky_bg = Configuration::get('STSN_STICKY_FILTER_BG'))
        {
            $sticky_bg_arr = self::hex2rgb($sticky_bg );
            if(is_array($sticky_bg_arr))
                $css .='#horizontal_filters.stuck{background: '.$sticky_bg .';background:rgba('.$sticky_bg_arr[0].','.$sticky_bg_arr[1].','.$sticky_bg_arr[2].','.$sticky_opacity.');}';
        }

                    
        //menu
        if(Configuration::get('STSN_MENU_COLOR'))
            $css .='#st_mega_menu_wrap .ma_level_0{color:'.Configuration::get('STSN_MENU_COLOR').';}#main_menu_widgets #search_block_top.quick_search_simple .button-search,#main_menu_widgets .header_item, #main_menu_widgets a.header_item, #main_menu_widgets .header_item a{color:'.Configuration::get('STSN_MENU_COLOR').';}'; 
        if($menu_hover_color = Configuration::get('STSN_MENU_HOVER_COLOR'))
            $css .='#st_mega_menu_wrap .ml_level_0.current .ma_level_0,#st_mega_menu_wrap .ma_level_0:hover{color:'.$menu_hover_color.';border-bottom-color:'.$menu_hover_color.';}'; 
        if(Configuration::get('STSN_MENU_HOVER_BG'))
            $css .='#st_mega_menu_wrap .ml_level_0.current .ma_level_0{background-color:'.Configuration::get('STSN_MENU_HOVER_BG').';}'; 
        
        $sticky_opacity = (float)Configuration::get('STSN_STICKY_OPACITY');
        if($sticky_opacity<0 || $sticky_opacity>1)
            $sticky_opacity = 0.95;
        if($menu_bg_color = Configuration::get('STSN_MENU_BG_COLOR'))
        {
            if(Configuration::get('STSN_MEGAMENU_WIDTH'))
            {
                $css .='.st_mega_menu_container{background-color:'.$menu_bg_color.';}'; 
            }
            else
                $css .='#top_extra_container{background-color:'.$menu_bg_color.';}'; 
           
            $megamenu_bg = self::hex2rgb($menu_bg_color );
            if(is_array($megamenu_bg))
            {
                $css .='#top_extra .st_mega_menu_container.stuck{background: '.$menu_bg_color .';background:rgba('.$megamenu_bg[0].','.$megamenu_bg[1].','.$megamenu_bg[2].','.$sticky_opacity.');}';
                // move to theme.scs $css .='.st_mega_menu_container.stuck #top_extra_container{background:transparent;}';
            }
        }
        if($top_extra_bg_color = Configuration::get('STSN_TOP_EXTRA_BG_COLOR'))
        {
            $css .='#top_extra .st_mega_menu_container{background-color:'.$top_extra_bg_color.';}'; 
        }
        $top_extra_top_spacing = Configuration::get('STSN_TOP_EXTRA_TOP_SPACING');
        if($top_extra_top_spacing || $top_extra_top_spacing===0 || $top_extra_top_spacing==='0')
            $css .='#top_extra .st_mega_menu_container{padding-top:'.(int)$top_extra_top_spacing.'px;}'; 
        $top_extra_bottom_spacing = Configuration::get('STSN_TOP_EXTRA_BOTTOM_SPACING');
        if($top_extra_bottom_spacing || $top_extra_bottom_spacing===0 || $top_extra_bottom_spacing==='0')
            $css .='#top_extra .st_mega_menu_container{padding-bottom:'.(int)$top_extra_bottom_spacing.'px;}'; 

        $menu_bottom_border = (int)Configuration::get('STSN_MENU_BOTTOM_BORDER');
        $css .='#st_mega_menu_wrap .stmenu_sub{border-top-width:'.$menu_bottom_border.'px;}#st_mega_menu_wrap .ma_level_0{margin-bottom:-'.$menu_bottom_border.'px;border-bottom-width:'.$menu_bottom_border.'px;}'; 
        if(Configuration::get('STSN_MEGAMENU_WIDTH'))
            $css .='#top_extra .st_mega_menu_container{border-bottom-width:'.$menu_bottom_border.'px;}'; 
        else
            $css .='.boxed_megamenu #st_mega_menu_wrap{border-bottom-width:'.$menu_bottom_border.'px;}'; 
        $css .='#header_primary #st_mega_menu_wrap{border-bottom-width:'.$menu_bottom_border.'px;}'; 

        if($menu_bottom_border_color = Configuration::get('STSN_MENU_BOTTOM_BORDER_COLOR'))
            $css .='#st_mega_menu_wrap .stmenu_sub{border-top-color:'.$menu_bottom_border_color.';}.st_mega_menu_container, .boxed_megamenu #st_mega_menu_wrap,#header_primary #st_mega_menu_wrap{border-bottom-color:'.$menu_bottom_border_color.';}'; 
        
        if($menu_bottom_border_hover_color = Configuration::get('STSN_MENU_BOTTOM_BORDER_HOVER_COLOR'))
            $css .='#st_mega_menu_wrap .ml_level_0.current .ma_level_0,#st_mega_menu_wrap .ma_level_0:hover{border-bottom-color:'.$menu_bottom_border_hover_color.';}'; 

        if($top_extra_bottom_border = Configuration::get('STSN_TOP_EXTRA_BOTTOM_BORDER'))
            $css .='#top_extra{border-bottom-width:'.($top_extra_bottom_border%10).'px;border-bottom-style: solid;}';
        if (Configuration::get('STSN_TOP_EXTRA_BOTTOM_BORDER_COLOR'))
            $css .= '#top_extra{border-bottom-color:'.Configuration::get('STSN_TOP_EXTRA_BOTTOM_BORDER_COLOR').';}';
        
        if(Configuration::get('STSN_SECOND_MENU_COLOR'))
            $css .='.ma_level_1{color:'.Configuration::get('STSN_SECOND_MENU_COLOR').';}'; 
        if(Configuration::get('STSN_SECOND_MENU_HOVER_COLOR'))
            $css .='.ma_level_1:hover{color:'.Configuration::get('STSN_SECOND_MENU_HOVER_COLOR').';}'; 
        if(Configuration::get('STSN_THIRD_MENU_COLOR'))
            $css .='.ma_level_2, .mu_level_3 a.ma_item{color:'.Configuration::get('STSN_THIRD_MENU_COLOR').';}'; 
        if(Configuration::get('STSN_THIRD_MENU_HOVER_COLOR'))
            $css .='.ma_level_2:hover, .mu_level_3 a.ma_item:hover{color:'.Configuration::get('STSN_THIRD_MENU_HOVER_COLOR').';}'; 
        if(Configuration::get('STSN_MENU_MOB_ITEMS1_COLOR'))
            $css .='#st_mobile_menu .mo_ma_level_0,#st_mobile_menu a.mo_ma_level_0{color:'.Configuration::get('STSN_MENU_MOB_ITEMS1_COLOR').';}';
        if(Configuration::get('STSN_MENU_MOB_ITEMS2_COLOR'))
            $css .='#st_mobile_menu .mo_ma_level_1,#st_mobile_menu a.mo_ma_level_1{color:'.Configuration::get('STSN_MENU_MOB_ITEMS2_COLOR').';}';
        if(Configuration::get('STSN_MENU_MOB_ITEMS3_COLOR'))
            $css .='#st_mobile_menu .mo_ma_level_2,#st_mobile_menu a.mo_ma_level_2{color:'.Configuration::get('STSN_MENU_MOB_ITEMS3_COLOR').';}';
        if(Configuration::get('STSN_MENU_MOB_ITEMS1_BG'))
            $css .='#st_mobile_menu .mo_ml_level_0 > .menu_a_wrap{background-color:'.Configuration::get('STSN_MENU_MOB_ITEMS1_BG').';}';
        if(Configuration::get('STSN_MENU_MOB_ITEMS2_BG'))
            $css .='#st_mobile_menu .mo_mu_level_1 > li{background-color:'.Configuration::get('STSN_MENU_MOB_ITEMS2_BG').';}';
        if(Configuration::get('STSN_MENU_MOB_ITEMS3_BG'))
            $css .='#st_mobile_menu .mo_mu_level_2 > li{background-color:'.Configuration::get('STSN_MENU_MOB_ITEMS3_BG').';}';

        //Multi menu
        if(Configuration::get('STSN_MENU_MULTI_BG'))
            $css .='.stmenu_multi_level .ma_item{background-color:'.Configuration::get('STSN_MENU_MULTI_BG').';}';
        if(Configuration::get('STSN_MENU_MULTI_BG_HOVER'))
            $css .='.stmenu_multi_level .ma_item:hover{background-color:'.Configuration::get('STSN_MENU_MULTI_BG_HOVER').';}';
        
        //Ver menu
        //tom spacing
        if(Configuration::get('STSN_MENU_VER_TITLE_WIDTH'))
            $css .= '#st_ma_0, #st_mega_menu_wrap .stmenu_sub.stmenu_vertical{ min-width: '.Configuration::get('STSN_MENU_VER_TITLE_WIDTH').'px; }';
        $menu_ver_title_align = Configuration::get('STSN_MENU_VER_TITLE_ALIGN');
        $css .= '#st_ma_0{ text-align: '.($menu_ver_title_align==1 ? 'center' : ($menu_ver_title_align ==2 ? 'right' : 'left')).'; }';
        if(Configuration::get('STSN_MENU_VER_TITLE'))
            $css .='#st_mega_menu_wrap #st_ma_0{color:'.Configuration::get('STSN_MENU_VER_TITLE').';}'; 
        if(Configuration::get('STSN_MENU_VER_HOVER_TITLE'))
            $css .='#st_mega_menu_wrap #st_menu_0.current #st_ma_0,#st_mega_menu_wrap #st_ma_0:hover{color:'.Configuration::get('STSN_MENU_VER_HOVER_TITLE').';}'; 
        if(Configuration::get('STSN_MENU_VER_BG'))
            $css .='#st_mega_menu_wrap #st_ma_0{background-color:'.Configuration::get('STSN_MENU_VER_BG').';}'; 
        if(Configuration::get('STSN_MENU_VER_HOVER_BG'))
            $css .='#st_mega_menu_wrap #st_menu_0.current #st_ma_0,#st_mega_menu_wrap #st_ma_0:hover{background-color:'.Configuration::get('STSN_MENU_VER_HOVER_BG').';}'; 
        if(Configuration::get('STSN_MENU_VER_ITEM_COLOR'))
            $css .='.mv_item{color:'.Configuration::get('STSN_MENU_VER_ITEM_COLOR').';}'; 
        if(Configuration::get('STSN_MENU_VER_ITEM_BG'))
            $css .='.mv_level_1{background-color:'.Configuration::get('STSN_MENU_VER_ITEM_BG').';}'; 
        if(Configuration::get('STSN_MENU_VER_ITEM_HOVER_COLOR'))
            $css .='.mv_item:hover{color:'.Configuration::get('STSN_MENU_VER_ITEM_HOVER_COLOR').';}'; 
        if(Configuration::get('STSN_MENU_VER_ITEM_HOVER_BG'))
            $css .='.mv_level_1:hover{background-color:'.Configuration::get('STSN_MENU_VER_ITEM_HOVER_BG').';}';

        //Side menu
        if(Configuration::get('STSN_C_MENU_COLOR'))
            $css .='#st_mega_menu_column_block .ma_level_0, #st_mega_menu_column_mobile .mo_ma_level_0,#st_mega_menu_column_mobile .mo_ma_level_1,#st_mega_menu_column_mobile .mo_ma_level_2{color:'.Configuration::get('STSN_C_MENU_COLOR').';}'; 
        if($menu_hover_color = Configuration::get('STSN_C_MENU_HOVER_COLOR'))
            $css .='#st_mega_menu_column_block .ml_level_0.current .ma_level_0,#st_mega_menu_column_block .ma_level_0:hover,#st_mega_menu_column_mobile .mo_ma_level_0:hover,#st_mega_menu_column_mobile .mo_ma_level_1:hover,#st_mega_menu_column_mobile .mo_ma_level_2:hover{color:'.$menu_hover_color.';}'; 
        if(Configuration::get('STSN_C_MENU_HOVER_BG'))
            $css .='#st_mega_menu_column_block .ml_level_0.current .ma_level_0{background-color:'.Configuration::get('STSN_C_MENU_HOVER_BG').';}'; 
        if(Configuration::get('STSN_C_MENU_BG_COLOR'))
                $css .='#st_mega_menu_column_block{background-color:'.Configuration::get('STSN_C_MENU_BG_COLOR').';}'; 

        if($c_menu_border_color = Configuration::get('STSN_C_MENU_BORDER_COLOR'))
            $css .='#st_mega_menu_column_block .ma_level_0{border-left-color:'.$c_menu_border_color.';}'; 
        
        if(Configuration::get('STSN_C_MENU_BORDER_HOVER_COLOR'))
            $css .='#st_mega_menu_column_block .ml_level_0.current .ma_level_0,#st_mega_menu_column_block .ma_level_0:hover{border-left-color:'.Configuration::get('STSN_C_MENU_BORDER_HOVER_COLOR').';}'; 
        
        //footer
        if($footer_border = Configuration::get('STSN_FOOTER_BORDER'))
            $css .='#footer-secondary '.($footer_border>20 ? ' #footer-secondary-container' : '').'{border-top-width:'.($footer_border%10).'px;border-top-style: solid;}';
        if(Configuration::get('STSN_FOOTER_BORDER_COLOR'))
            $css .='#footer-secondary, #footer-secondary #footer-secondary-container{border-top-color:'.Configuration::get('STSN_FOOTER_BORDER_COLOR').';}';

        if(Configuration::get('STSN_FOOTER_PRIMARY_COLOR')) 
            $css .='#footer-primary, #footer-primary a, #footer-primary .price, #footer-primary .old_price{color:'.Configuration::get('STSN_FOOTER_PRIMARY_COLOR').';}'; 
        if(Configuration::get('STSN_FOOTER_COLOR')) 
            $css .='#footer-secondary, #footer-secondary a, #footer-secondary .price, #footer-secondary .old_price {color:'.Configuration::get('STSN_FOOTER_COLOR').';}'; 
        if(Configuration::get('STSN_FOOTER_TERTIARY_COLOR')) 
            $css .='#footer-tertiary, #footer-tertiary a, #footer-tertiary .price, #footer-tertiary .old_price{color:'.Configuration::get('STSN_FOOTER_TERTIARY_COLOR').';}'; 
        if(Configuration::get('STSN_FOOTER_LINK_PRIMARY_COLOR')) 
            $css .='#footer-primary a{color:'.Configuration::get('STSN_FOOTER_LINK_PRIMARY_COLOR').';}'; 
        if(Configuration::get('STSN_FOOTER_LINK_COLOR')) 
            $css .='#footer-secondary a{color:'.Configuration::get('STSN_FOOTER_LINK_COLOR').';}'; 
        if(Configuration::get('STSN_FOOTER_LINK_TERTIARY_COLOR')) 
            $css .='#footer-tertiary a{color:'.Configuration::get('STSN_FOOTER_LINK_TERTIARY_COLOR').';}'; 
        if(Configuration::get('STSN_FOOTER_LINK_PRIMARY_HOVER_COLOR')) 
            $css .='#footer-primary a:hover{color:'.Configuration::get('STSN_FOOTER_LINK_PRIMARY_HOVER_COLOR').';}';  
        if(Configuration::get('STSN_FOOTER_LINK_HOVER_COLOR')) 
            $css .='#footer-secondary a:hover{color:'.Configuration::get('STSN_FOOTER_LINK_HOVER_COLOR').';}';  
        if(Configuration::get('STSN_FOOTER_LINK_TERTIARY_HOVER_COLOR')) 
            $css .='#footer-tertiary a:hover{color:'.Configuration::get('STSN_FOOTER_LINK_TERTIARY_HOVER_COLOR').';}';  

        if(Configuration::get('STSN_SECOND_FOOTER_COLOR')) 
            $css .='#footer-bottom,#footer-bottom a{color:'.Configuration::get('STSN_SECOND_FOOTER_COLOR').';}'; 
        if(Configuration::get('STSN_SECOND_FOOTER_LINK_COLOR')) 
            $css .='#footer-bottom a{color:'.Configuration::get('STSN_SECOND_FOOTER_LINK_COLOR').';}';     
        if(Configuration::get('STSN_SECOND_FOOTER_LINK_HOVER_COLOR')) 
            $css .='#footer-bottom a:hover{color:'.Configuration::get('STSN_SECOND_FOOTER_LINK_HOVER_COLOR').';}';   
        

        /*if(Configuration::get('STSN_F_TOP_FULLWIDTH'))
        {
            $css .= '#footer-primary .wide_container, #footer-primary .container{max-width: none;}';
            if($is_responsive)
                $res_css .= '@media (min-width: 992px) {#footer-primary .row{padding-right:20px;padding-left:20px;}}';
            else
                $css .= '#footer-primary .row{padding-right:20px;padding-left:20px;}';
        }
        if(Configuration::get('STSN_FOOTER_FULLWIDTH'))
        {
            $css .= '#footer-secondary .wide_container, #footer-secondary .container{max-width: none;}';
            if($is_responsive)
                $res_css .= '@media (min-width: 992px) {#footer-secondary .row{padding-right:20px;padding-left:20px;}}';
            else
                $css .= '#footer-secondary .row{padding-right:20px;padding-left:20px;}';
        }
        if(Configuration::get('STSN_F_SECONDARY_FULLWIDTH'))
        {
            $css .= '#footer-tertiary .wide_container, #footer-tertiary .container{max-width: none;}';
            if($is_responsive)
                $res_css .= '@media (min-width: 992px) {#footer-tertiary .row{padding-right:20px;padding-left:20px;}}';
            else
                $css .= '#footer-tertiary .row{padding-right:20px;padding-left:20px;}';
        }
        if(Configuration::get('STSN_F_INFO_FULLWIDTH'))
        {
            $css .= '#footer-bottom .wide_container, #footer-bottom .container{max-width: none;}';
            if($is_responsive)
                $res_css .= '@media (min-width: 992px) {#footer-bottom .row{padding-right:20px;padding-left:20px;}}';
            else
                $css .= '#footer-bottom .row{padding-right:20px;padding-left:20px;}';
        }*/
        
        if ($body_bg_color = Configuration::get('STSN_BODY_BG_COLOR')){
            $css .= 'body, #body_wrapper,.modal-content,.st-menu,.dropdown_list .dropdown_box{background-color:'.$body_bg_color.';}';
            $css .= '.st_read_more{background: -webkit-linear-gradient(to bottom, rgba(255,255,255,0.001), '.$body_bg_color.' 50%);background: linear-gradient(to bottom, rgba(255,255,255,0.001), '.$body_bg_color.' 50%);}';
        }
        if ($body_con_bg_color = Configuration::get('STSN_BODY_CON_BG_COLOR'))
        {
            $css .= '.boxed_page_wrapper{background-color:'.$body_con_bg_color.';}';
			$css .= '.modal-content{background-color:'.$body_con_bg_color.';}';
            if(Configuration::get('STSN_BOXSTYLE'))
                $css .= '.st_read_more{background: -webkit-linear-gradient(to bottom, rgba(255,255,255,0.001), '.$body_con_bg_color.' 50%);background: linear-gradient(to bottom, rgba(255,255,255,0.001), '.$body_con_bg_color.' 50%);}';
        }
        
        $css .= '#left_column.sidebar_opened,#right_column.sidebar_opened{background-color:'.($body_con_bg_color ? $body_con_bg_color : ($body_bg_color ? $body_bg_color : '#fff')).';}';
        
        if ($side_panel_bg = Configuration::get('STSN_SIDE_PANEL_BG'))
            $css .= '.st-menu{background-color:'.$side_panel_bg.';}';//.st-menu.st-menu-right{background-color:#ffffff;}
        /*if($this->hasEasyBuilder()){
            $button_css .= '.st-menu{'.$this->getBackgroundCssWp('steb_side_panel_bg').'}';
        }*/
        if ($side_panel_heading = Configuration::get('STSN_SIDE_PANEL_HEADING'))
            $css .= '.st-menu-title{color:'.$side_panel_heading.';}';
        if ($side_panel_heading_bg = Configuration::get('STSN_SIDE_PANEL_HEADING_BG'))
            $css .= '.st-menu-header{background-color:'.$side_panel_heading_bg.';}';
        if ($side_panel_heading_border = Configuration::get('STSN_SIDE_PANEL_HEADING_BORDER'))
            $css .= '.st-menu{border-left-color:'.$side_panel_heading_border.';border-right-color:'.$side_panel_heading_border.';}';
        /*if($this->hasEasyBuilder()){
            $button_css .= '.st-menu-title{'.$this->getBackgroundCssWp('steb_side_panel_heading_bg').'}';
        }*/

        /*if (Configuration::get('STSN_MAIN_CON_BG_COLOR'))
            $css .= '.columns-container{background-color:'.Configuration::get('STSN_MAIN_CON_BG_COLOR').';}';*/
        if (Configuration::get('STSN_BODY_BG_PATTERN') && (Configuration::get('STSN_BODY_BG_IMG')==""))
			$css .= '#body_wrapper{background-image: url(../../patterns/'.Configuration::get('STSN_BODY_BG_PATTERN').'.png);}';
        if ($bg_img = Configuration::get('STSN_BODY_BG_IMG')) {
            $this->fetchMediaServer($bg_img);
            $css .= '#body_wrapper{background-image:url('.$bg_img.');}';  
        }
		if (Configuration::get('STSN_BODY_BG_REPEAT')) {
			switch(Configuration::get('STSN_BODY_BG_REPEAT')) {
				case 1 :
					$repeat_option = 'repeat-x';
					break;
				case 2 :
					$repeat_option = 'repeat-y';
					break;
				case 3 :
					$repeat_option = 'no-repeat';
					break;
				default :
					$repeat_option = 'repeat';
			}
			$css .= '#body_wrapper{background-repeat:'.$repeat_option.';}';
		}
		if (Configuration::get('STSN_BODY_BG_POSITION')) {
			switch(Configuration::get('STSN_BODY_BG_POSITION')) {
				case 1 :
					$position_option = 'center top';
					break;
				case 2 :
					$position_option = 'right top';
					break;
				default :
					$position_option = 'left top';
			}
			$css .= '#body_wrapper{background-position: '.$position_option.';}';
		}
        if (Configuration::get('STSN_BODY_BG_FIXED')) {
            $css .= '#body_wrapper{background-attachment: fixed;}';
        }
		if (Configuration::get('STSN_BODY_BG_COVER')) {
			$css .= '#body_wrapper{background-size: cover;}';
		}
        $header_bg_color = Configuration::get('STSN_HEADER_BG_COLOR');
        if ($header_bg_color)
		{
            $header_bg_color_hex = self::hex2rgb($header_bg_color);
            $css .= '.header-container #st_header{background-color:'.$header_bg_color.';}';
            $css .='#st_header.stuck{background: '.$header_bg_color .';background:rgba('.$header_bg_color_hex[0].','.$header_bg_color_hex[1].','.$header_bg_color_hex[2].','.$sticky_opacity.');}';     
            //$css .= 'body#index.mobile_device .transparent-header #st_header{background-color:'.$header_bg_color.';}';
        }

        if(Configuration::get('STSN_TRANSPARENT_HEADER'))
        {
            if($transparent_header_bg = Configuration::get('STSN_TRANSPARENT_HEADER_BG'))
            {
                $transparent_header_opacity = (float)Configuration::get('STSN_TRANSPARENT_HEADER_OPACITY');
                if($transparent_header_opacity<0 || $transparent_header_opacity>1)
                    $transparent_header_opacity = 0.4;

                $transparent_header_bg_hex = self::hex2rgb($transparent_header_bg);
                $css .= 'body#index .transparent-header:not(:hover) #st_header:not(.stuck){background:rgba('.$transparent_header_bg_hex[0].','.$transparent_header_bg_hex[1].','.$transparent_header_bg_hex[2].','.$transparent_header_opacity.');}';      
                //$css .= 'body#index.mobile_device .transparent-header #st_header{background-color:'.$transparent_header_bg.';}';
            }
            else
                $css .= 'body#index .transparent-header #st_header{background:transparent;}';
            
            if($transparent_header_text = Configuration::get('STSN_TRANSPARENT_HEADER_TEXT')){
                $css .= 'body#index .transparent-header:not(:hover) :not(.stuck) .top_bar_item .header_item{color:'.$transparent_header_text.';}';
                $css .= 'body#index .transparent-header:not(:hover) :not(.stuck) #st_mega_menu_wrap .ma_level_0{color:'.$transparent_header_text.';}';
                $css .= 'body#index .transparent-header:not(:hover) :not(.stuck) .steasym_ml_level_0 > .steasym_ma > .steasym_mas, body#index .transparent-header:not(:hover) :not(.stuck) .steasym_ml_level_0 > .steasym_ma > .steasym_arrow_icon, body#index .transparent-header:not(:hover) :not(.stuck) .steasym_mu_level_0 > .top_bar_item .header_item{color:'.$transparent_header_text.';}';
            }
        }
        if(Configuration::get('STSN_TRANSPARENT_MOBILE_HEADER'))
        {
            if($transparent_header_mobile_bg = Configuration::get('STSN_TRANSPARENT_MOBILE_HEADER_BG'))
            {
                $transparent_header_mobile_opacity = (float)Configuration::get('STSN_TRANSPARENT_MOBILE_HEADER_OPACITY');
                if($transparent_header_mobile_opacity<0 || $transparent_header_mobile_opacity>1)
                    $transparent_header_mobile_opacity = 0.4;

                $transparent_header_mobile_bg_hex = self::hex2rgb($transparent_header_mobile_bg);
                $css .= 'body#index .transparent-mobile-header #st_header #mobile_bar{background:rgba('.$transparent_header_mobile_bg_hex[0].','.$transparent_header_mobile_bg_hex[1].','.$transparent_header_mobile_bg_hex[2].','.$transparent_header_mobile_opacity.');}';      
                //$css .= 'body#index.mobile_device .transparent-header #st_header{background-color:'.$transparent_header_bg.';}';
            }
        }
        /*if($transparent_mobile_header_color = Configuration::get('STSN_TRANSPARENT_MOBILE_HEADER_COLOR'))
            $css .= '.transparent-mobile-header #st_header .mobile_bar_tri,.transparent-mobile-header .cart_mobile_bar_tri .ajax_cart_bag i{ color: '.$transparent_mobile_header_color.';}.transparent-mobile-header .cart_mobile_bar_tri .ajax_cart_bag, .transparent-mobile-header .cart_mobile_bar_tri .ajax_cart_bag .ajax_cart_bg_handle, .transparent-mobile-header .cart_mobile_bar_tri .ajax_cart_bag .amount_circle{border-color: '.$transparent_mobile_header_color.';}.transparent-mobile-header .cart_mobile_bar_tri .ajax_cart_bag i{color: '.$transparent_mobile_header_color.';}';*/


        if($sticky_bg = Configuration::get('STSN_STICKY_BG'))
        {
            $sticky_bg_arr = self::hex2rgb($sticky_bg );
            if(is_array($sticky_bg_arr))
                $css .='.st_mega_menu_container.stuck, #st_header.stuck, .steasym_main.stuck{background: '.$sticky_bg .';background:rgba('.$sticky_bg_arr[0].','.$sticky_bg_arr[1].','.$sticky_bg_arr[2].','.$sticky_opacity.');}';
            // $css .='#st_header.stuck .st_mega_menu_container, #st_header.stuck #top_extra_container, #st_header.stuck .wide_container, #st_header.stuck #top_extra .wide_container{background: transparent;}';
        }

        $sticky_shadow_color = Configuration::get('STSN_STICKY_SHADOW_COLOR');
        if(!Validate::isColor($sticky_shadow_color))
            $sticky_shadow_color = '#000000';

        $sticky_shadow_color_arr = self::hex2rgb($sticky_shadow_color);
        if(is_array($sticky_shadow_color_arr))
        {
            $sticky_shadow_opacity = (float)Configuration::get('STSN_STICKY_SHADOW_OPACITY');
            if($sticky_shadow_opacity<0 || $sticky_shadow_opacity>1)
                $sticky_shadow_opacity = 0.1;

            $sticky_shadow_css = '0px 1px '.(int)Configuration::get('STSN_STICKY_SHADOW_BLUR').'px rgba('.$sticky_shadow_color_arr[0].','.$sticky_shadow_color_arr[1].','.$sticky_shadow_color_arr[2].','.$sticky_shadow_opacity.')';
            $css .= '#st_header.stuck, #top_extra .st_mega_menu_container.stuck, .steasym_main.stuck{-webkit-box-shadow: '.$sticky_shadow_css .'; -moz-box-shadow: '.$sticky_shadow_css .'; box-shadow: '.$sticky_shadow_css .'; }';
        }

        if (Configuration::get('STSN_HEADER_CON_BG_COLOR'))
			$css .= '#st_header #header_primary_container{background-color:'.Configuration::get('STSN_HEADER_CON_BG_COLOR').';}';
        if (Configuration::get('STSN_HEADER_BG_PATTERN') && (Configuration::get('STSN_HEADER_BG_IMG')==""))
			$css .= '.header-container #st_header{background-image: url(../../patterns/'.Configuration::get('STSN_HEADER_BG_PATTERN').'.png);}';
        if ($bg_img = Configuration::get('STSN_HEADER_BG_IMG')) {
            $this->fetchMediaServer($bg_img);
            $css .= '.header-container #st_header{background-image:url('.$bg_img.');}';
        }	
		if (Configuration::get('STSN_HEADER_BG_REPEAT')) {
			switch(Configuration::get('STSN_HEADER_BG_REPEAT')) {
				case 1 :
					$repeat_option = 'repeat-x';
					break;
				case 2 :
					$repeat_option = 'repeat-y';
					break;
				case 3 :
					$repeat_option = 'no-repeat';
					break;
				default :
					$repeat_option = 'repeat';
			}
			$css .= '.header-container #st_header{background-repeat:'.$repeat_option.';}';
		}
		if (Configuration::get('STSN_HEADER_BG_POSITION')) {
			switch(Configuration::get('STSN_HEADER_BG_POSITION')) {
				case 1 :
					$position_option = 'center top';
					break;
				case 2 :
					$position_option = 'right top';
					break;
				default :
					$position_option = 'left top';
			}
			$css .= '.header-container #st_header{background-position: '.$position_option.';}';
		}

        if (Configuration::get('STSN_F_TOP_BG_PATTERN') && (Configuration::get('STSN_F_TOP_BG_IMG')==""))
			$css .= '#footer-primary{background-image: url(../../patterns/'.Configuration::get('STSN_F_TOP_BG_PATTERN').'.png);}';
        if ($bg_img = Configuration::get('STSN_F_TOP_BG_IMG')) {
            $this->fetchMediaServer($bg_img);
            $css .= '#footer-primary{background-image:url('.$bg_img.');}';
        }	
		if (Configuration::get('STSN_FOOTER_BG_REPEAT')) {
			switch(Configuration::get('STSN_FOOTER_BG_REPEAT')) {
				case 1 :
					$repeat_option = 'repeat-x';
					break;
				case 2 :
					$repeat_option = 'repeat-y';
					break;
				case 3 :
					$repeat_option = 'no-repeat';
					break;
				default :
					$repeat_option = 'repeat';
			}
			$css .= '#footer-primary{background-repeat:'.$repeat_option.';}';
		}
		if (Configuration::get('STSN_F_TOP_BG_POSITION')) {
			switch(Configuration::get('STSN_F_TOP_BG_POSITION')) {
				case 1 :
					$position_option = 'center top';
					break;
				case 2 :
					$position_option = 'right top';
					break;
				default :
					$position_option = 'left top';
			}
			$css .= '#footer-primary{background-position: '.$position_option.';}';
		}
        if (Configuration::get('STSN_F_TOP_BG_FIXED')) {
            $css .= '#footer-primary{background-attachment: fixed;}';
        }
        if($footer_top_border = Configuration::get('STSN_FOOTER_TOP_BORDER'))
            $css .='#footer-primary '.($footer_top_border>20 ? ' #footer-primary-container' : '').'{border-top-width:'.($footer_top_border%10).'px;border-top-style: solid;}';
        if (Configuration::get('STSN_FOOTER_TOP_BORDER_COLOR'))
            $css .= '#footer-primary, #footer-primary #footer-primary-container{border-top-color:'.Configuration::get('STSN_FOOTER_TOP_BORDER_COLOR').';}';

        if (Configuration::get('STSN_FOOTER_TOP_BG'))
			$css .= '#footer-primary{background-color:'.Configuration::get('STSN_FOOTER_TOP_BG').';}';
        if (Configuration::get('STSN_FOOTER_TOP_CON_BG'))
			$css .= '#footer-primary #footer-primary-container{background-color:'.Configuration::get('STSN_FOOTER_TOP_CON_BG').';}';
            
        if (Configuration::get('STSN_FOOTER_BG_PATTERN') && (Configuration::get('STSN_FOOTER_BG_IMG')==""))
			$css .= '#footer-secondary{background-image: url(../../patterns/'.Configuration::get('STSN_FOOTER_BG_PATTERN').'.png);}';
        if ($bg_img = Configuration::get('STSN_FOOTER_BG_IMG')) {
            $this->fetchMediaServer($bg_img);
            $css .= '#footer-secondary{background-image:url('.$bg_img.');}';
        }			
		if (Configuration::get('STSN_FOOTER_BG_REPEAT')) {
			switch(Configuration::get('STSN_FOOTER_BG_REPEAT')) {
				case 1 :
					$repeat_option = 'repeat-x';
					break;
				case 2 :
					$repeat_option = 'repeat-y';
					break;
				case 3 :
					$repeat_option = 'no-repeat';
					break;
				default :
					$repeat_option = 'repeat';
			}
			$css .= '#footer-secondary{background-repeat:'.$repeat_option.';}';
		}
		if (Configuration::get('STSN_FOOTER_BG_POSITION')) {
			switch(Configuration::get('STSN_FOOTER_BG_POSITION')) {
				case 1 :
					$position_option = 'center top';
					break;
				case 2 :
					$position_option = 'right top';
					break;
				default :
					$position_option = 'left top';
			}
			$css .= '#footer-secondary{background-position: '.$position_option.';}';
		}
        if (Configuration::get('STSN_FOOTER_BG_FIXED')) {
            $css .= '#footer-secondary{background-attachment: fixed;}';
        }
        if (Configuration::get('STSN_FOOTER_BG_COLOR'))
			$css .= '#footer-secondary{background-color:'.Configuration::get('STSN_FOOTER_BG_COLOR').';}';
        if (Configuration::get('STSN_FOOTER_CON_BG_COLOR'))
			$css .= '#footer-secondary #footer-secondary-container{background-color:'.Configuration::get('STSN_FOOTER_CON_BG_COLOR').';}';
            
        if (Configuration::get('STSN_F_SECONDARY_BG_PATTERN') && (Configuration::get('STSN_F_SECONDARY_BG_IMG')==""))
			$css .= '#footer-tertiary{background-image: url(../../patterns/'.Configuration::get('STSN_F_SECONDARY_BG_PATTERN').'.png);}';
        if ($bg_img = Configuration::get('STSN_F_SECONDARY_BG_IMG')) {
            $this->fetchMediaServer($bg_img);
            $css .= '#footer-tertiary{background-image:url('.$bg_img.');}';
        }
		if (Configuration::get('STSN_F_SECONDARY_BG_REPEAT')) {
			switch(Configuration::get('STSN_F_SECONDARY_BG_REPEAT')) {
				case 1 :
					$repeat_option = 'repeat-x';
					break;
				case 2 :
					$repeat_option = 'repeat-y';
					break;
				case 3 :
					$repeat_option = 'no-repeat';
					break;
				default :
					$repeat_option = 'repeat';
			}
			$css .= '#footer-tertiary{background-repeat:'.$repeat_option.';}';
		}
		if (Configuration::get('STSN_F_SECONDARY_BG_POSITION')) {
			switch(Configuration::get('STSN_F_SECONDARY_BG_POSITION')) {
				case 1 :
					$position_option = 'center top';
					break;
				case 2 :
					$position_option = 'right top';
					break;
				default :
					$position_option = 'left top';
			}
			$css .= '#footer-tertiary{background-position: '.$position_option.';}';
		}
        if (Configuration::get('STSN_F_SECONDARY_BG_FIXED')) {
            $css .= '#footer-tertiary{background-attachment: fixed;}';
        }
        if($footer_tertiary_border = Configuration::get('STSN_FOOTER_TERTIARY_BORDER'))
            $css .='#footer-tertiary '.($footer_tertiary_border>20 ? ' #footer-tertiary-container' : '').'{border-top-width:'.($footer_tertiary_border%10).'px;border-top-style: solid;}';
        if (Configuration::get('STSN_FOOTER_TERTIARY_BORDER_COLOR'))
            $css .= '#footer-tertiary, #footer-tertiary #footer-tertiary-container{border-top-color:'.Configuration::get('STSN_FOOTER_TERTIARY_BORDER_COLOR').';}';
        if (Configuration::get('STSN_FOOTER_SECONDARY_BG'))
			$css .= '#footer-tertiary{background-color:'.Configuration::get('STSN_FOOTER_SECONDARY_BG').';}';
        if (Configuration::get('STSN_FOOTER_SECONDARY_CON_BG'))
			$css .= '#footer-tertiary #footer-tertiary-container{background-color:'.Configuration::get('STSN_FOOTER_SECONDARY_CON_BG').';}';
            
                        
        if (Configuration::get('STSN_F_INFO_BG_PATTERN') && (Configuration::get('STSN_F_INFO_BG_IMG')==""))
			$css .= '#footer-bottom{background-image: url(../../patterns/'.Configuration::get('STSN_F_INFO_BG_PATTERN').'.png);}';
        if ($bg_img = Configuration::get('STSN_F_INFO_BG_IMG')) {
            $this->fetchMediaServer($bg_img);
            $css .= '#footer-bottom{background-image:url('.$bg_img.');}';
        }			
		if (Configuration::get('STSN_F_INFO_BG_REPEAT')) {
			switch(Configuration::get('STSN_F_INFO_BG_REPEAT')) {
				case 1 :
					$repeat_option = 'repeat-x';
					break;
				case 2 :
					$repeat_option = 'repeat-y';
					break;
				case 3 :
					$repeat_option = 'no-repeat';
					break;
				default :
					$repeat_option = 'repeat';
			}
			$css .= '#footer-bottom{background-repeat:'.$repeat_option.';}';
		}
		if (Configuration::get('STSN_F_INFO_BG_POSITION')) {
			switch(Configuration::get('STSN_F_INFO_BG_POSITION')) {
				case 1 :
					$position_option = 'center top';
					break;
				case 2 :
					$position_option = 'right top';
					break;
				default :
					$position_option = 'left top';
			}
			$css .= '#footer-bottom{background-position: '.$position_option.';}';
		}
        if (Configuration::get('STSN_F_INFO_BG_FIXED')) {
            $css .= '#footer-bottom{background-attachment: fixed;}';
        }
        if($footer_info_border = Configuration::get('STSN_FOOTER_INFO_BORDER'))
            $css .='#footer-bottom '.($footer_info_border>20 ? ' #footer-bottom-container' : '').'{border-top-width:'.($footer_info_border%10).'px;border-top-style: solid;}';
        if (Configuration::get('STSN_FOOTER_INFO_BORDER_COLOR'))
            $css .= '#footer-bottom, #footer-bottom #footer-bottom-container{border-top-color:'.Configuration::get('STSN_FOOTER_INFO_BORDER_COLOR').';}';
        if (Configuration::get('STSN_FOOTER_INFO_BG'))
            $css .= '#footer-bottom{background-color:'.Configuration::get('STSN_FOOTER_INFO_BG').';}';
        if (Configuration::get('STSN_FOOTER_INFO_CON_BG'))
			$css .= '#footer-bottom #footer-bottom-container{background-color:'.Configuration::get('STSN_FOOTER_INFO_CON_BG').';}';
        
                
        $logo_height = (int)Configuration::get('STSN_LOGO_HEIGHT'); 
        if($logo_height)
            $css .= '#st_header #header_primary_container{height: '.$logo_height.'px;}'; 

        if($sticky_header_height = Configuration::get('STSN_STICKY_HEADER_HEIGHT'))
            $css .= '#st_header.stuck #header_primary_container{height: '.$sticky_header_height.'px;}';

        if(!Configuration::get('STSN_TRANSPARENT_HEADER'))
        {
            $sticky_option = (int)Configuration::get('STSN_STICKY_OPTION');
            if($sticky_option==1 || $sticky_option==3)
                $css .= '.header-container.has_sticky{padding-bottom:'.($menu_height ? $menu_height : 36).'px;}';
            elseif($sticky_option==2 || $sticky_option==4)
                $css .= '.header-container.has_sticky{padding-bottom:'.($logo_height ? $logo_height : 110).'px;}';
        }
        if(Configuration::get('STSN_CART_ICON'))                    
            $css .= '.fto-glyph.icon_btn:before{ content: "\\'.dechex(Configuration::get('STSN_CART_ICON')).'"; }';
        else
            $css .= '.fto-glyph.icon_btn{ display: none; }';
        if(Configuration::get('STSN_WISHLIST_ICON'))
            $css .= '.fto-star.icon_btn:before{ content: "\\'.dechex(Configuration::get('STSN_WISHLIST_ICON')).'"; }';
        /*else
            $css .= '.fto-star.icon_btn{ display: none; }';*/
        if(Configuration::get('STSN_LOVE_ICON'))
            $css .= '.fto-heart-4.icon_btn:before{ content: "\\'.dechex(Configuration::get('STSN_LOVE_ICON')).'"; }';
        /*else
            $css .= '.fto-heart-4.icon_btn{ display: none; }';*/
        if(Configuration::get('STSN_COMPARE_ICON'))
            $css .= '.fto-ajust.icon_btn:before{ content: "\\'.dechex(Configuration::get('STSN_COMPARE_ICON')).'"; }';
        else
            $css .= '.fto-ajust.icon_btn{ display: none; }';
        if(Configuration::get('STSN_QUICK_VIEW_ICON'))
            $css .= '.quick-view .fto-search-1.icon_btn:before{ content: "\\'.dechex(Configuration::get('STSN_QUICK_VIEW_ICON')).'"; }';
        else
            $css .= '.quick-view .fto-search-1.icon_btn{ display: none; }';
        if(Configuration::get('STSN_VIEW_ICON'))
            $css .= '.fto-eye-2.icon_btn:before{ content: "\\'.dechex(Configuration::get('STSN_VIEW_ICON')).'"; }';
        else
            $css .= '.fto-eye-2.icon_btn{ display: none; }';
        if(Configuration::get('STSN_SIGN_ICON'))
            $css .= '.fto-user.icon_btn:before{ content: "\\'.dechex(Configuration::get('STSN_SIGN_ICON')).'"; }';
        // else
        //     $css .= '.fto-user.icon_btn{ display: none; }';
        if(Configuration::get('STSN_VIEWED_ICON'))
            $css .= '.fto-history.icon_btn:before{ content: "\\'.dechex(Configuration::get('STSN_VIEWED_ICON')).'"; }';
        else
            $css .= '.fto-history.icon_btn{ display: none; }';
        if(Configuration::get('STSN_SPIN_ICON'))
            $css .= '.btn-spin.active i:before{ content: "\\'.dechex(Configuration::get('STSN_SPIN_ICON')).'"!important; }';
            
        $pro_tab_prefix = '.product_info_tabs.sttab_block .nav-tabs .nav-link';
        if(Configuration::get('STSN_PRO_TAB_COLOR'))  
            $css .= $pro_tab_prefix.', .pro_more_info .mobile_tab .mobile_tab_name{ color: '.Configuration::get('STSN_PRO_TAB_COLOR').'; }';
        if(Configuration::get('STSN_PRO_TAB_ACTIVE_COLOR'))  
            $css .= $pro_tab_prefix.'.active, '.$pro_tab_prefix.':hover, '.$pro_tab_prefix.':focus, .pro_more_info .mobile_tab .st_open .mobile_tab_name{ color: '.Configuration::get('STSN_PRO_TAB_ACTIVE_COLOR').'; }';
        if(Configuration::get('STSN_PRO_TAB_BG'))  
            $css .= $pro_tab_prefix.', .pro_more_info .mobile_tab .mobile_tab_title{ background-color: '.Configuration::get('STSN_PRO_TAB_BG').'; }.pro_more_info .mobile_tab .mobile_tab_name{ padding-left: 8px; }';
        if($pro_tab_hover_bg = Configuration::get('STSN_PRO_TAB_HOVER_BG'))  
        {
            $css .= '.product_info_tabs.sttab_block.sttab_2_2 .nav-tabs .nav-link.active, .product_info_tabs.sttab_block.sttab_2_2 .nav-tabs .nav-link:hover, .product_info_tabs.sttab_block.sttab_2_2 .nav-tabs .nav-link:focus{ border-top-color: '.$pro_tab_hover_bg.'; }';
            $css .= '.product_info_tabs.sttab_block.sttab_2_3 .nav-tabs .nav-link.active, .product_info_tabs.sttab_block.sttab_2_3 .nav-tabs .nav-link:hover, .product_info_tabs.sttab_block.sttab_2_3 .nav-tabs .nav-link:focus{ border-bottom-color: '.$pro_tab_hover_bg.'; }';
            $css .= '.product_info_tabs.sttab_block.sttab_3_2 .nav-tabs .nav-link.active, .product_info_tabs.sttab_block.sttab_3_2 .nav-tabs .nav-link:hover, .product_info_tabs.sttab_block.sttab_3_2 .nav-tabs .nav-link:focus{ border-left-color: '.$pro_tab_hover_bg.'; }';
        }
        if($pro_tab_border_clolor = Configuration::get('STSN_PRO_TAB_BORDER_CLOLOR')) 
        {
            $css .= '.product_info_tabs.sttab_block.sttab_2_2 .nav-tabs{ border-bottom-color: '.$pro_tab_border_clolor.'; }';
            $css .= '.product_info_tabs.sttab_block.sttab_2_3 .nav-tabs{ border-bottom-color: '.$pro_tab_border_clolor.'; }';
            $css .= '.product_info_tabs.sttab_block.sttab_2_3 .nav-tabs .nav-link{ border-bottom-color: '.$pro_tab_border_clolor.'; }';
            $css .= '.accordion_more_info .mobile_tab .tab-pane{ border-bottom-color: '.$pro_tab_border_clolor.'; }';
        }
        
        if(Configuration::get('STSN_PRO_TAB_ACTIVE_BG'))  
            $css .= $pro_tab_prefix.'.active, '.$pro_tab_prefix.':hover, '.$pro_tab_prefix.':focus, .accordion_more_info .mobile_tab .st_open .mobile_tab_title{ background-color: '.Configuration::get('STSN_PRO_TAB_ACTIVE_BG').'; }';
        if(Configuration::get('STSN_PRO_TAB_CONTENT_BG'))  
            $css .= '.product_info_tabs .tab-pane-body{ background-color: '.Configuration::get('STSN_PRO_TAB_CONTENT_BG').'; }';
        
        if(Configuration::get('STSN_BIG_NEXT_COLOR'))  
            $css .= '.big_page_next{ color: '.Configuration::get('STSN_BIG_NEXT_COLOR').'; }';
        if(Configuration::get('STSN_BIG_NEXT_HOVER_COLOR'))  
            $css .= '.big_page_next:hover{ color: '.Configuration::get('STSN_BIG_NEXT_HOVER_COLOR').'; }';
        if(Configuration::get('STSN_BIG_NEXT_BG'))  
            $css .= '.big_page_next{ background-color: '.Configuration::get('STSN_BIG_NEXT_BG').'; }';
        if(Configuration::get('STSN_BIG_NEXT_HOVER_BG'))  
            $css .= '.big_page_next:hover{ background-color: '.Configuration::get('STSN_BIG_NEXT_HOVER_BG').'; }';
        
        //Top and bottom spacing
        if(Configuration::get('STSN_TOP_SPACING'))  
        {
            $css .= '#body_wrapper{ padding-top: '.Configuration::get('STSN_TOP_SPACING').'px; }';
            $res_css .= '@media (max-width: 991px) {#body_wrapper{ padding-top: 0; }}';
        }
        //similar code in blog editor
        $header_bottom_spacing = Configuration::get('STSN_HEADER_BOTTOM_SPACING');
        $css .= 'body#index .header-container{ margin-bottom: '.$header_bottom_spacing.'px; }';
        // $res_css .= '@media (max-width: 991px) {body#index .header-container { margin-bottom: 0; }}';
        //

        if(Configuration::get('STSN_BOTTOM_SPACING'))  
        {
            $css .= '#body_wrapper{ padding-bottom: '.Configuration::get('STSN_BOTTOM_SPACING').'px; }';
            $res_css .= '@media (max-width: 991px) {#body_wrapper{ padding-bottom: 0; }}';
        }
        if($block_spacing = Configuration::get('STSN_BLOCK_SPACING'))  
            $css .= '.block{ margin-bottom: '.$block_spacing.'px; }';
        //
        if($base_border_color = Configuration::get('STSN_BASE_BORDER_COLOR'))
        {
            $css .= '.general_top_border,
            .general_bottom_border,
            .general_right_border,
            .general_border,
            hr,
            .form-control,
            .bootstrap-touchspin .form-control,
            .category-top-menu ul li,
            .product_list.list .product_list_item,
            .steasy_divider_item,
            .bootstrap-touchspin .btn-touchspin,
            .product-features dl.data-sheet,
            .pro_column_box,
            .input-group-with-border,
            .from_blcok,
            .checkout-step .sttab_2_1 .nav-tabs .nav-link.active, .checkout-step .sttab_2_1 .nav-tabs .nav-link:hover, .checkout-step .sttab_2_1 .nav-tabs .nav-link:focus,
            .checkout-step .sttab_2_1 .nav-tabs,
            .checkout-step .sttab_2_1 .tab-pane-body,
            .base_list_line .line_item,
            .checkout-step,
            .card,.card-footer,.card-header,
            .table-bordered, .table-bordered thead th, .table-bordered th, .table-bordered td,
            .list-group-item,
            .mo_ml_level_0, .mo_sub_li,
            .mobile_tab .tab-pane,
            .modal-header,
            .dropdown_list .dropdown_box,
            .dropdown_list .dropdown_list_ul li,
                    .box,
                    .categories_tree_block li,
                    .content_sortPagiBar .sortPagiBar,
                    .bottom-pagination-content,
                    .pb-center-column #buy_block .box-info-product,
                    .product_extra_info_wrap,
                    .box-cart-bottom .qt_cart_box,
                    .pro_column_list li, 
                    #blog_list_large .block_blog, #blog_list_medium .block_blog,
                    #product_comments_block_tab div.comment,
                    .table-bordered > thead > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > tfoot > tr > td,
                    #create-account_form section, #login_form section,
                    ul.footer_links,
                    #product p#loyalty,
                    #subcategories .inline_list li a.img img,
                    .tags_block .block_content a,
                    #footer .title_block,#footer .st_open .footer_block_content,
                    .cart_body .small_cart_sumary{ border-color: '.$base_border_color.'; }';
            $css .= '#js-product-list .product_list.grid .product_list_item{ border-top-color: '.$base_border_color.'; }';
        }  
        if($form_bg_color = Configuration::get('STSN_FORM_BG_COLOR'))
            $css .= '.general_bg{background-color:'.$form_bg_color.';}';

        if(Configuration::get('STSN_PRO_GRID_BG'))  
            $css .= '.products_sldier_swiper .pro_outer_box .pro_second_box,.product_list.grid .pro_outer_box .pro_second_box{ background-color: '.Configuration::get('STSN_PRO_GRID_BG').'; }';
        if(Configuration::get('STSN_PRO_GRID_HOVER_BG'))  
            $css .= '.products_sldier_swiper .pro_outer_box:hover .pro_second_box,.product_list.grid .pro_outer_box:hover .pro_second_box{ background-color: '.Configuration::get('STSN_PRO_GRID_HOVER_BG').'; }';

        if(Configuration::get('STSN_PS_TR_PREV_NEXT_COLOR'))  
            $css .= '.products_slider .swiper-button-tr .swiper-button{ color: '.Configuration::get('STSN_PS_TR_PREV_NEXT_COLOR').'; }';
        if(Configuration::get('STSN_PS_TR_PREV_NEXT_COLOR_HOVER'))  
            $css .= '.products_slider .swiper-button-tr .swiper-button:hover{ color: '.Configuration::get('STSN_PS_TR_PREV_NEXT_COLOR_HOVER').'; }';
        if(Configuration::get('STSN_PS_TR_PREV_NEXT_COLOR_DISABLED'))  
            $css .= '.products_slider .swiper-button-tr .swiper-button.swiper-button-disabled, .products_slider .swiper-button-tr .swiper-button.swiper-button-disabled:hover{ color: '.Configuration::get('STSN_PS_TR_PREV_NEXT_COLOR_DISABLED').'; }';

        if($ps_tr_prev_next_bg = Configuration::get('STSN_PS_TR_PREV_NEXT_BG')) 
            $css .= '.products_slider .swiper-button-tr .swiper-button{ background-color: '.$ps_tr_prev_next_bg.'; }';
        if($ps_tr_prev_next_bg_hover = Configuration::get('STSN_PS_TR_PREV_NEXT_BG_HOVER')) 
            $css .= '.products_slider .swiper-button-tr .swiper-button:hover{ background-color: '.$ps_tr_prev_next_bg_hover.'; }';
        if ($ps_tr_prev_next_bg_disabled = Configuration::get('STSN_PS_TR_PREV_NEXT_BG_DISABLED'))
            $css .= '.products_slider .swiper-button-tr .swiper-button.swiper-button-disabled, .products_slider .swiper-button-tr .swiper-button.swiper-button-disabled:hover{background-color:'.$ps_tr_prev_next_bg_disabled.';}';
        /*if($this->hasEasyBuilder()){
            $css .= '.products_slider .swiper-button-tr .swiper-button{'.$this->getBackgroundCssWp('steb_ps_tr_prev_next_bg').'}';
            $css .= '.products_slider .swiper-button-tr .swiper-button:hover{'.$this->getBackgroundCssWp('steb_ps_tr_prev_next_bg_hover').'}';
            $css .= '.products_slider .swiper-button-tr .swiper-button.swiper-button-disabled, .products_slider .swiper-button-tr .swiper-button.swiper-button-disabled:hover{'.$this->getBackgroundCssWp('steb_ps_tr_prev_next_bg_disabled').'}';
        }*/
        /*else
            $css .= '.products_slider .swiper-button-tr .swiper-button.swiper-button-disabled, .products_slider .swiper-button-tr .swiper-button.swiper-button-disabled:hover{background-color:transparent;}';*/


        if(Configuration::get('STSN_PS_LR_PREV_NEXT_COLOR'))  
            $css .= '.block .products_slider .swiper-button-lr .swiper-button{ color: '.Configuration::get('STSN_PS_LR_PREV_NEXT_COLOR').'; }';
        if(Configuration::get('STSN_PS_LR_PREV_NEXT_COLOR_HOVER'))  
            $css .= '.block .products_slider .swiper-button-lr .swiper-button:hover{ color: '.Configuration::get('STSN_PS_LR_PREV_NEXT_COLOR_HOVER').'; }';
        if(Configuration::get('STSN_PS_LR_PREV_NEXT_COLOR_DISABLED'))  
            $css .= '.block .products_slider .swiper-button-lr .swiper-button.swiper-button-disabled, .block .products_slider .swiper-button-lr .swiper-button.swiper-button-disabled:hover{ color: '.Configuration::get('STSN_PS_LR_PREV_NEXT_COLOR_DISABLED').'; }';

        if($ps_lr_prev_next_bg = Configuration::get('STSN_PS_LR_PREV_NEXT_BG')) 
            $css .= '.block .products_slider .swiper-button-lr .swiper-button{ background-color: '.$ps_lr_prev_next_bg.'; }';
        if($ps_lr_prev_next_bg_hover = Configuration::get('STSN_PS_LR_PREV_NEXT_BG_HOVER')) 
            $css .= '.block .products_slider .swiper-button-lr .swiper-button:hover{ background-color: '.$ps_lr_prev_next_bg_hover.'; }';
        if ($ps_lr_prev_next_bg_disabled = Configuration::get('STSN_PS_LR_PREV_NEXT_BG_DISABLED'))
            $css .= '.block .products_slider .swiper-button-lr .swiper-button.swiper-button-disabled, .block .products_slider .swiper-button-lr .swiper-button.swiper-button-disabled:hover{background-color:'.$ps_lr_prev_next_bg_disabled.';}';
        /*if($this->hasEasyBuilder()){
            $css .= '.products_slider .swiper-button-tr .swiper-button{'.$this->getBackgroundCssWp('steb_ps_lr_prev_next_bg').'}';
            $css .= '.products_slider .swiper-button-tr .swiper-button:hover{'.$this->getBackgroundCssWp('steb_ps_lr_prev_next_bg_hover').'}';
            $css .= '.products_slider .swiper-button-tr .swiper-button.swiper-button-disabled, .products_slider .swiper-button-tr .swiper-button.swiper-button-disabled:hover{'.$this->getBackgroundCssWp('steb_ps_lr_prev_next_bg_disabled').'}';
        }*/
        /*else
            $css .= '.block .products_slider .swiper-button-lr .swiper-button.swiper-button-disabled, .block .products_slider .swiper-button-lr .swiper-button.swiper-button-disabled:hover{background-color:transparent;}';*/
            
        
        if(Configuration::get('STSN_PS_PAG_NAV_BG'))  
            $css .= '.products_slider .swiper-pagination-bullet{ background-color: '.Configuration::get('STSN_PS_PAG_NAV_BG').'; }';
        if(Configuration::get('STSN_PS_PAG_NAV_BG_HOVER'))  
            $css .= '.products_slider .swiper-pagination-bullet-active{ background-color: '.Configuration::get('STSN_PAG_NAV_BG_HOVER').'; }';


        //
         if ($direction_color = Configuration::get('STSN_PRO_LR_PREV_NEXT_COLOR'))
                $css .= '.pro_gallery_top_container .swiper-button-lr .swiper-button{color:'.$direction_color.';}';
            if ($direction_color_hover = Configuration::get('STSN_PRO_LR_PREV_NEXT_COLOR_HOVER'))
                $css .= '.pro_gallery_top_container .swiper-button-lr .swiper-button:hover{color:'.$direction_color_hover.';}';
            if ($direction_color_disabled = Configuration::get('STSN_PRO_LR_PREV_NEXT_COLOR_DISABLED'))
                $css .= '.pro_gallery_top_container .swiper-button-lr .swiper-button.swiper-button-disabled, .pro_gallery_top_container .swiper-button-lr .swiper-button.swiper-button-disabled:hover{color:'.$direction_color_disabled.';}';
            
            if ($direction_bg = Configuration::get('STSN_PRO_LR_PREV_NEXT_BG'))
                $css .= '.pro_gallery_top_container .swiper-button-lr .swiper-button{background-color:'.$direction_bg.';}';
            if ($direction_hover_bg = Configuration::get('STSN_PRO_LR_PREV_NEXT_BG_HOVER'))
                $css .= '.pro_gallery_top_container .swiper-button-lr .swiper-button:hover{background-color:'.$direction_hover_bg.';}';
            if ($direction_disabled_bg = Configuration::get('STSN_PRO_LR_PREV_NEXT_BG_DISABLED'))
                $css .= '.pro_gallery_top_container .swiper-button-lr .swiper-button.swiper-button-disabled, .pro_gallery_top_container .swiper-button-lr .swiper-button.swiper-button-disabled:hover{background-color:'.$direction_disabled_bg.';}';
            /*else
                $css .= '.pro_gallery_top_container .swiper-button-lr .swiper-button.swiper-button-disabled, .pro_gallery_top_container .swiper-button-lr .swiper-button.swiper-button-disabled:hover{background-color:transparent;}';*/

            if ($pag_nav_bg = Configuration::get('STSN_PRO_LR_PAG_NAV_BG')){
                $css .= '.pro_gallery_top_container .swiper-pagination-bullet,.pro_gallery_top_container .swiper-pagination-progress{background-color:'.$pag_nav_bg.';}';
                $css .= '.pro_gallery_top_container .swiper-pagination-st-round .swiper-pagination-bullet{background-color:transparent;border-color:'.$pag_nav_bg.';}';
                $css .= '.pro_gallery_top_container .swiper-pagination-st-round .swiper-pagination-bullet span{background-color:'.$pag_nav_bg.';}';
            }
            if ($pag_nav_bg_hover = Configuration::get('STSN_PRO_LR_PAG_NAV_BG_HOVER')){
                $css .= '.pro_gallery_top_container .swiper-pagination-bullet-active, .pro_gallery_top_container .swiper-pagination-progress .swiper-pagination-progressbar{background-color:'.$pag_nav_bg_hover.';}';
                $css .= '.pro_gallery_top_container .swiper-pagination-st-round .swiper-pagination-bullet.swiper-pagination-bullet-active{background-color:'.$pag_nav_bg_hover.';border-color:'.$pag_nav_bg_hover.';}';
                $css .= '.pro_gallery_top_container .swiper-pagination-st-round .swiper-pagination-bullet.swiper-pagination-bullet-active span{background-color:'.$pag_nav_bg_hover.';}';
            }

        if(Configuration::get('STSN_PAGINATION_COLOR'))  
            $css .= 'ul.pagination > li > a, ul.pagination > li > span, div.pagination .showall .show_all_products { color: '.Configuration::get('STSN_PAGINATION_COLOR').'; }';
        if(Configuration::get('STSN_PAGINATION_COLOR_DISABLED'))  
            $css .= 'ul.pagination > li.disabled > a, ul.pagination > li.disabled > a:hover,ul.pagination > li.active > a, ul.pagination > li.active > a:hover, ul.pagination > li.disabled > span, ul.pagination > li.disabled > span:hover, ul.pagination > li.active > span, ul.pagination > li.active > span:hover{ color: '.Configuration::get('STSN_PAGINATION_COLOR_DISABLED').'; }';
        if(Configuration::get('STSN_PAGINATION_COLOR_HOVER'))  
            $css .= 'ul.pagination > li > a:hover, ul.pagination > li > span:hover, div.pagination .showall .show_all_products:hover{ color: '.Configuration::get('STSN_PAGINATION_COLOR_HOVER').'; }';

        if(Configuration::get('STSN_PAGINATION_BG'))  
            $css .= 'ul.pagination > li > a, ul.pagination > li > span, div.pagination .showall .show_all_products { background-color: '.Configuration::get('STSN_PAGINATION_BG').'; }';
        if(Configuration::get('STSN_PAGINATION_BG_DISABLED')) 
            $css .= 'ul.pagination > li.disabled > a, ul.pagination > li.disabled > a:hover,ul.pagination > li.active > a, ul.pagination > li.active > a:hover, ul.pagination > li.disabled > span, ul.pagination > li.disabled > span:hover, ul.pagination > li.active > span, ul.pagination > li.active > span:hover{ background-color: '.Configuration::get('STSN_PAGINATION_BG_DISABLED').'; }';
        if(Configuration::get('STSN_PAGINATION_BG_HOVER'))  
            $css .= 'ul.pagination > li > a:hover, ul.pagination > li > span:hover, div.pagination .showall .show_all_products:hover{ background-color: '.Configuration::get('STSN_PAGINATION_BG_HOVER').'; }';




        //product box border
        if($pro_border_size = Configuration::get('STSN_PRO_BORDER_SIZE'))
            $css .= '.ajax_block_product .pro_outer_box{ border-width: '.$pro_border_size.'px; }';
        if($pro_border_color = Configuration::get('STSN_PRO_BORDER_COLOR'))
            $css .= '.ajax_block_product .pro_outer_box{ border-color: '.$pro_border_color.'; }';
        if($pro_border_color_hover = Configuration::get('STSN_PRO_BORDER_COLOR_HOVER'))
            $css .= '.ajax_block_product .pro_outer_box:hover{ border-color: '.$pro_border_color_hover.'; }';
        //Shadow
        if($pro_shadow_effect = Configuration::get('STSN_PRO_SHADOW_EFFECT'))
        {
            $pro_shadow_color = Configuration::get('STSN_PRO_SHADOW_COLOR');
            if(!Validate::isColor($pro_shadow_color))
                $pro_shadow_color = '#000000';

            $pro_shadow_color_arr = self::hex2rgb($pro_shadow_color);
            if(is_array($pro_shadow_color_arr))
            {
                $pro_shadow_opacity = (float)Configuration::get('STSN_PRO_SHADOW_OPACITY');
                if($pro_shadow_opacity<0 || $pro_shadow_opacity>1)
                    $pro_shadow_opacity = 0.1;

                $pro_h_shadow = (int)Configuration::get('STSN_PRO_H_SHADOW');
                $pro_v_shadow = (int)Configuration::get('STSN_PRO_V_SHADOW');
                $pro_shadow_blur = (int)Configuration::get('STSN_PRO_SHADOW_BLUR');
                $shadow_offset = ($pro_v_shadow > $pro_h_shadow ? $pro_v_shadow : $pro_h_shadow) + $pro_shadow_blur;
                $css .= '.products_sldier_swiper{margin: -'.$shadow_offset.'px; padding: '.$shadow_offset.'px; }.product_view_more_box{margin-top:'.$shadow_offset.'px;}';//to do in future. Full height pre and next buttons would become longer .products_sldier_swiper.swiper-button-lr .swiper-button-prev, .products_sldier_swiper.swiper-button-lr .swiper-button-next{}
                $css .= '.products_slider .swiper-button-lr.swiper-navigation_visible>.swiper-button-next, .swiper-button-lr:hover>.swiper-button-next{right:'.$shadow_offset.'px;}.is_rtl .products_slider .swiper-button-lr.swiper-navigation_visible>.swiper-button-next, .is_rtl .swiper-button-lr:hover>.swiper-button-next{left:'.$shadow_offset.'px;}';
                $css .= '.products_slider .swiper-button-lr.swiper-navigation_visible>.swiper-button-prev, .swiper-button-lr:hover>.swiper-button-prev{left:'.$shadow_offset.'px;}.is_rtl .products_slider .swiper-button-lr.swiper-navigation_visible>.swiper-button-prev, .is_rtl .swiper-button-lr:hover>.swiper-button-prev{right:'.$shadow_offset.'px;}';

                $pro_shadow_css = $pro_h_shadow.'px '.$pro_v_shadow.'px '.$pro_shadow_blur.'px rgba('.$pro_shadow_color_arr[0].','.$pro_shadow_color_arr[1].','.$pro_shadow_color_arr[2].','.$pro_shadow_opacity.')';
                if($pro_shadow_effect==2)
                    $css .= '.products_sldier_swiper .ajax_block_product.swiper-slide-visible .pro_outer_box, .product_list.grid .ajax_block_product .pro_outer_box, .product_list.list .ajax_block_product{-webkit-box-shadow: '.$pro_shadow_css .'; -moz-box-shadow: '.$pro_shadow_css .'; box-shadow: '.$pro_shadow_css .'; }';
                else
                    $css .= '.products_sldier_swiper .ajax_block_product.swiper-slide-visible .pro_outer_box:hover, .product_list.grid .ajax_block_product .pro_outer_box:hover, .product_list.list .ajax_block_product:hover{-webkit-box-shadow: '.$pro_shadow_css .'; -moz-box-shadow: '.$pro_shadow_css .'; box-shadow: '.$pro_shadow_css .'; }';
            }
        }
        //Boxed style shadow
        if(Configuration::get('STSN_BOXED_SHADOW_EFFECT'))
        {
            $boxed_shadow_color = Configuration::get('STSN_BOXED_SHADOW_COLOR');
            if(!Validate::isColor($boxed_shadow_color))
                $boxed_shadow_color = '#000000';

            $boxed_shadow_color_arr = self::hex2rgb($boxed_shadow_color);
            if(is_array($boxed_shadow_color_arr))
            {
                $boxed_shadow_opacity = (float)Configuration::get('STSN_BOXED_SHADOW_OPACITY');
                if($boxed_shadow_opacity<0 || $boxed_shadow_opacity>1)
                    $boxed_shadow_opacity = 0.1;

                $boxed_shadow_css = (int)Configuration::get('STSN_BOXED_H_SHADOW').'px '.(int)Configuration::get('STSN_BOXED_V_SHADOW').'px '.(int)Configuration::get('STSN_BOXED_SHADOW_BLUR').'px rgba('.$boxed_shadow_color_arr[0].','.$boxed_shadow_color_arr[1].','.$boxed_shadow_color_arr[2].','.$boxed_shadow_opacity.')';
                $css .= '.boxed_page_wrapper{-webkit-box-shadow: '.$boxed_shadow_css .'; -moz-box-shadow: '.$boxed_shadow_css .'; box-shadow: '.$boxed_shadow_css .'; }';
            }
        }
        else
            $css .= '.boxed_page_wrapper{box-shadow:none;-webkit-box-shadow:none;-moz-box-shadow:none;}';

        //fullwidth
        /*if(Configuration::get('STSN_FULLWIDTH_TOPBAR'))
        {
            $css .= '#top_bar .wide_container, #top_bar .container{max-width: none;}';
            if($is_responsive)
                $res_css .= '@media (min-width: 992px) {#top_bar .row{padding-right:20px;padding-left:20px;}}';
            else
                $css .= '#top_bar .row{padding-right:20px;padding-left:20px;}';
        }
        if(Configuration::get('STSN_FULLWIDTH_HEADER'))
        {
            $css .= '#header_primary .wide_container, #header_primary .container{max-width: none;}';
            if($is_responsive)
                $res_css .= '@media (min-width: 992px) {header#st_header #header_primary_row, #checkout_header_wrap{padding-right:20px;padding-left:20px;}}';
            else
                $css .= 'header#st_header #header_primary_row, #checkout_header_wrap{padding-right:20px;padding-left:20px;}';
        }*/
        if($sticky_mobile_header_height = Configuration::get('STSN_STICKY_MOBILE_HEADER_HEIGHT'))
        {
            $css .= '#mobile_bar_top{ height: '.$sticky_mobile_header_height.'px;}';
            /*$res_css .= '@media only screen and (max-width: 991px) {#st_header.sticky_mh{ padding-bottom: '.$sticky_mobile_header_height.'px;}}.use_mobile_header #st_header.sticky_mh{ padding-bottom: '.$sticky_mobile_header_height.'px;}';*/
        }
        /*if($sticky_mobile_header_color = Configuration::get('STSN_STICKY_MOBILE_HEADER_COLOR'))
            $css .= '#st_header .mobile_bar_tri,.cart_mobile_bar_tri .ajax_cart_bag i, .transparent-mobile-header.has_sticky #st_header .mobile_bar_tri, .transparent-mobile-header.has_sticky .cart_mobile_bar_tri .ajax_cart_bag i{ color: '.$sticky_mobile_header_color.';}.transparent-mobile-header.has_sticky .cart_mobile_bar_tri .ajax_cart_bag, .transparent-mobile-header.has_sticky .cart_mobile_bar_tri .ajax_cart_bag .ajax_cart_bg_handle, .transparent-mobile-header.has_sticky .cart_mobile_bar_tri .ajax_cart_bag .amount_circle{border-color: '.$sticky_mobile_header_color.';}.transparent-mobile-header.has_sticky .cart_mobile_bar_tri .ajax_cart_bag i{color: '.$sticky_mobile_header_color.';}';
        if($sticky_mobile_header_text_bg = Configuration::get('STSN_STICKY_MOBILE_HEADER_TEXT_BG'))
        {
            $sticky_mobile_header_text_bg_hex = self::hex2rgb($sticky_mobile_header_text_bg);
            $css .= '#st_header .mobile_bar_tri{ background: '.$sticky_mobile_header_text_bg.';}';
            $css .= '#st_header .mobile_bar_tri:hover{ background:rgba('.$sticky_mobile_header_text_bg_hex[0].','.$sticky_mobile_header_text_bg_hex[1].','.$sticky_mobile_header_text_bg_hex[2].', 0.8);}';
        }*/

        if($sticky_mobile_header_background = Configuration::get('STSN_STICKY_MOBILE_HEADER_BACKGROUND'))
        {
            $css .= '#st_header #mobile_bar,#st_header #mobile_bar.stuck, body#index .transparent-mobile-header #st_header #mobile_bar.stuck{ background-color: '.$sticky_mobile_header_background.';}';
            if(Configuration::get('STSN_TRANSPARENT_MOBILE_HEADER') && !Configuration::get('STSN_TRANSPARENT_MOBILE_HEADER_BG'))
                $css .= 'body#index .transparent-mobile-header #st_header #mobile_bar{ background-color: transparent;}';

            $sticky_mobile_header_background_opacity = (float)Configuration::get('STSN_STICKY_MOBILE_HEADER_BACKGROUND_OPACITY');
            if($sticky_mobile_header_background_opacity>=0 && $sticky_mobile_header_background_opacity<1)
            {
                $sticky_mobile_header_background_hex = self::hex2rgb($sticky_mobile_header_background);
                $css .= '#st_header #mobile_bar.stuck,body#index .transparent-mobile-header #st_header #mobile_bar.stuck{background-color: '.$sticky_mobile_header_background.';background:rgba('.$sticky_mobile_header_background_hex[0].','.$sticky_mobile_header_background_hex[1].','.$sticky_mobile_header_background_hex[2].','.$sticky_mobile_header_background_opacity.');}';      
            }
        }

       /* if($mob_cart_icon_border_color = Configuration::get('STSN_MOB_CART_ICON_BORDER_COLOR'))
            $css .= '.cart_mobile_bar_tri .ajax_cart_bag, .cart_mobile_bar_tri .ajax_cart_bag .ajax_cart_bg_handle{border-color: '.$mob_cart_icon_border_color.';}.cart_mobile_bar_tri .ajax_cart_bag i{color: '.$mob_cart_icon_border_color.';}';
        if(Configuration::get('STSN_MOB_CART_ICON_BG_COLOR'))
            $css .='.cart_mobile_bar_tri .ajax_cart_bag{background-color: '.Configuration::get('STSN_MOB_CART_ICON_BG_COLOR').';}';
        if(Configuration::get('STSN_MOB_CART_NUMBER_COLOR'))
            $css .='.cart_mobile_bar_tri .ajax_cart_bag .amount_circle{color: '.Configuration::get('STSN_MOB_CART_NUMBER_COLOR').';}';
        if(Configuration::get('STSN_MOB_CART_NUMBER_BG_COLOR'))
            $css .='.cart_mobile_bar_tri .ajax_cart_bag .amount_circle{background-color: '.Configuration::get('STSN_MOB_CART_NUMBER_BG_COLOR').';}';
        if(Configuration::get('STSN_MOB_CART_NUMBER_BORDER_COLOR'))
            $css .='.cart_mobile_bar_tri .ajax_cart_bag .amount_circle{border-color: '.Configuration::get('STSN_MOB_CART_NUMBER_BORDER_COLOR').';}';*/

        if(Configuration::get('STSN_LOGO_WIDTH'))
            $css .='#st_header .shop_logo{width: '.Configuration::get('STSN_LOGO_WIDTH').'px;}';
        if(Configuration::get('STSN_LOGO_WIDTH_STICKY_HEADER'))
            $css .='#st_header.stuck .shop_logo{width: '.Configuration::get('STSN_LOGO_WIDTH_STICKY_HEADER').'px;}';
        if(Configuration::get('STSN_LOGO_WIDTH_MOBILE_HEADER'))
            $css .='#mobile_bar .mobile_logo{width: '.Configuration::get('STSN_LOGO_WIDTH_MOBILE_HEADER').'px;}';
        if(Configuration::get('STSN_LOGO_WIDTH_STICKY_MOBILE_HEADER'))
            $css .='#mobile_bar.stuck .mobile_logo{width: '.Configuration::get('STSN_LOGO_WIDTH_STICKY_MOBILE_HEADER').'px;}';

        
        if(Configuration::get('STSN_GALLERY_THUMBNAILS_WIDTH_VPX'))
            $css .='.images-container-1 .pro_gallery_thumbs_vertical, .images-container-2 .pro_gallery_thumbs_vertical, .images-container-7 .pro_gallery_thumbs_vertical{width: '.Configuration::get('STSN_GALLERY_THUMBNAILS_WIDTH_VPX').'px;}';
        if(Configuration::get('STSN_GALLERY_THUMBNAILS_HEIGHT_V'))
            $css .='.pro_gallery_thumbs_vertical .swiper-container{height: '.Configuration::get('STSN_GALLERY_THUMBNAILS_HEIGHT_V').'px;}';

        $thumb_image_type = Configuration::get('STSN_THUMB_IMAGE_TYPE') ? Configuration::get('STSN_THUMB_IMAGE_TYPE') : 'cart_default';
        $thumb_size = Image::getSize($thumb_image_type);
        if($thumb_size)
            $css .='.pro_gallery_thumbs_vertical .swiper-slide{height: '.((int)$thumb_size['height']+2).'px;}';//2px border

        if(Configuration::get('STSN_GRID_THUMBNAILS_WIDTH'))
            $css .='.pro_gallery_thumbs_horizontal .swiper-slide, .pro_gallery_thumbs_grid .swiper-slide{width: '.Configuration::get('STSN_GRID_THUMBNAILS_WIDTH').'px;}';

        if(Configuration::get('STSN_PRODUCT_NAME_TRANSFORM'))
            $css .='.ajax_block_product .s_title_block a,.pro_column_right .s_title_block a{text-transform: '.self::$textTransform[(int)Configuration::get('STSN_PRODUCT_NAME_TRANSFORM')]['name'].';}';
        if($pro_name_size = (int)Configuration::get('STSN_PRO_NAME_SIZE'))
            $css .='.ajax_block_product .s_title_block,.pro_column_right .s_title_block{font-size: '.$pro_name_size.'px;}';

        if($proName)
            $css .='.ajax_block_product .s_title_block a,.pro_column_right .s_title_block a{'.($proName != $this->_font_inherit && $proName != $fontText ? 'font-family:"'.$proName.'";' : '').'}';
        if($proNameWeight)
            $css .='.ajax_block_product .s_title_block a,.pro_column_right .s_title_block a{'.$proNameWeight.'}';
        if($proNameStyle)
            $css .='.ajax_block_product .s_title_block a,.pro_column_right .s_title_block a{'.$proNameStyle.'}';

        //
        $pro_spacing_grid = Configuration::get('STSN_PRO_SPACING_GRID');
        if($pro_spacing_grid || $pro_spacing_grid===0 || $pro_spacing_grid==='0')
        {
            $css .= '#js-product-list .products.product_list.grid .product_list_item{padding-left:'.ceil($pro_spacing_grid/2).'px;padding-right:'.floor($pro_spacing_grid/2).'px;}';
            $css .= '#js-product-list .products.product_list.grid{margin-left:-'.ceil($pro_spacing_grid/2).'px;margin-right:-'.floor($pro_spacing_grid/2).'px;}';
        }
        if(Configuration::get('STSN_PRO_PAGE_FIRST_BG'))
            $css .='.product_first_section, body.product .breadcrumb_spacing{background-color: '.Configuration::get('STSN_PRO_PAGE_FIRST_BG').';}';
        if(Configuration::get('STSN_PRO_PAGE_SECOND_BG'))
            $css .='.product_second_section{background-color: '.Configuration::get('STSN_PRO_PAGE_SECOND_BG').';}';

        if(Configuration::get('STSN_AUTH_PADDING_TOP'))
            $css .='body#authentication .columns-container{padding-top: '.Configuration::get('STSN_AUTH_PADDING_TOP').'px;}';
        if(Configuration::get('STSN_AUTH_PADDING_BOTTOM'))
            $css .='body#authentication .columns-container{padding-bottom: '.Configuration::get('STSN_AUTH_PADDING_BOTTOM').'px;}';
        if(Configuration::get('STSN_AUTH_BG_COLOR'))
            $css .='body#authentication .columns-container{background-color: '.Configuration::get('STSN_AUTH_BG_COLOR').';}';
        if(Configuration::get('STSN_AUTH_HEADING_COLOR'))
            $css .='.login_form_block .page_heading{color: '.Configuration::get('STSN_AUTH_HEADING_COLOR').';}';
        if(Configuration::get('STSN_AUTH_HEADING_BG'))
        {
            $css .='.login_form_block .page_heading{background-color: '.Configuration::get('STSN_AUTH_HEADING_BG').';}';
            $css .='.login_form_block .page_heading{margin-bottom: 16px;}';
        }
        if(Configuration::get('STSN_AUTH_CON_BG_COLOR'))
            $css .='.login_form_block{background-color: '.Configuration::get('STSN_AUTH_CON_BG_COLOR').';}';
        if (Configuration::get('STSN_AUTH_BG_PATTERN') && (Configuration::get('STSN_AUTH_BG_IMG')==""))
            $css .= 'body#authentication .columns-container{background-image: url(../../patterns/'.Configuration::get('STSN_AUTH_BG_PATTERN').'.png);}';
        if ($bg_img = Configuration::get('STSN_AUTH_BG_IMG')) {
            $this->fetchMediaServer($bg_img);
            $css .= 'body#authentication .columns-container{background-image:url('.$bg_img.');}';
        }
        if (Configuration::get('STSN_AUTH_BG_REPEAT')) {
            switch(Configuration::get('STSN_AUTH_BG_REPEAT')) {
                case 1 :
                    $repeat_option = 'repeat-x';
                    break;
                case 2 :
                    $repeat_option = 'repeat-y';
                    break;
                case 3 :
                    $repeat_option = 'no-repeat';
                    break;
                default :
                    $repeat_option = 'repeat';
            }
            $css .= 'body#authentication .columns-container{background-repeat:'.$repeat_option.';}';
        }
        if (Configuration::get('STSN_AUTH_BG_POSITION')) {
            switch(Configuration::get('STSN_AUTH_BG_POSITION')) {
                case 1 :
                    $position_option = 'center top';
                    break;
                case 2 :
                    $position_option = 'right top';
                    break;
                default :
                    $position_option = 'left top';
            }
            $css .= 'body#authentication .columns-container{background-position: '.$position_option.';}';
        }
        if(Configuration::get('STSN_AUTH_BTN_COLOR'))
            $css .='.login_form_block .btn-primary{color: '.Configuration::get('STSN_AUTH_BTN_COLOR').';}';
        if(Configuration::get('STSN_AUTH_BTN_HOVER_COLOR'))
            $css .='.login_form_block .btn-primary:hover,.login_form_block .btn-primary:focus,.login_form_block .btn-primary:active,.login_form_block .btn-primary.active{color: '.Configuration::get('STSN_AUTH_BTN_HOVER_COLOR').';}';
        if(Configuration::get('STSN_AUTH_BTN_BG_COLOR'))
            $css .='.login_form_block .btn-primary{background-color: '.Configuration::get('STSN_AUTH_BTN_BG_COLOR').';border-color: '.Configuration::get('STSN_AUTH_BTN_BG_COLOR').';}';
        if(Configuration::get('STSN_AUTH_BTN_HOVER_BG_COLOR'))
            $css .='.login_form_block .btn-primary:hover,.login_form_block .btn-primary:focus,.login_form_block .btn-primary:active,.login_form_block .btn-primary.active{background-color: '.Configuration::get('STSN_AUTH_BTN_HOVER_BG_COLOR').';border-color: '.Configuration::get('STSN_AUTH_BTN_HOVER_BG_COLOR').';}';

        if(Configuration::get('STSN_CHECKOUT_BG'))
            $css .='.checkout_wrapper{background-color: '.Configuration::get('STSN_CHECKOUT_BG').';}';
        if($checkout_con_bg = Configuration::get('STSN_CHECKOUT_CON_BG'))
        {
            $css .='.checkout_left_column, .checkout_right_column{background-color: '.$checkout_con_bg.';}';
            $css .='.checkout-step .sttab_2_1 .nav-tabs .nav-link.active, .checkout-step .sttab_2_1 .nav-tabs .nav-link:hover, .checkout-step .sttab_2_1 .nav-tabs .nav-link:focus{border-bottom-color: '.$checkout_con_bg.';}';
        }
        else
            $css .='.checkout-step .sttab_2_1 .nav-tabs .nav-link.active, .checkout-step .sttab_2_1 .nav-tabs .nav-link:hover, .checkout-step .sttab_2_1 .nav-tabs .nav-link:focus{border-bottom-color: #ffffff;}';
        if($cms_font_size = Configuration::get('STSN_CMS_FONT_SIZE'))
            $css .='.cms_content{font-size: '.(round($cms_font_size/12*100,2) / 100).'em;}';
        if($main_menu_spacing_md = Configuration::get('STSN_MAIN_MENU_SPACING_MD'))
            $css .='#st_mega_menu_wrap .ma_level_0{padding-left:'.$main_menu_spacing_md.'px;padding-right:'.$main_menu_spacing_md.'px;}';
        if($main_menu_spacing_lg = Configuration::get('STSN_MAIN_MENU_SPACING_LG'))
            $css .='@media only screen and (min-width:1200px){#st_mega_menu_wrap .ma_level_0{padding-left:'.$main_menu_spacing_lg.'px;padding-right:'.$main_menu_spacing_lg.'px;}}';
            
        if($cms_h1_size = Configuration::get('STSN_CMS_H1_SIZE'))
            $css .='.cms_content h1, .blog_content h1, .product-description h1{font-size:'.$cms_h1_size.'px!important;}';
        if($cms_h2_size = Configuration::get('STSN_CMS_H2_SIZE'))
            $css .='.cms_content h2, .blog_content h2, .product-description h2{font-size:'.$cms_h2_size.'px!important;}';
        if($cms_h3_size = Configuration::get('STSN_CMS_H3_SIZE'))
            $css .='.cms_content h3, .blog_content h3, .product-description h3{font-size:'.$cms_h3_size.'px!important;}';

        if($product_available_font_string = Configuration::get('STSN_PRODUCT_AVAILABLE_FONT'))
        {
            preg_match_all('/^([^:]+):?(\d*)([a-z]*)$/', $product_available_font_string, $product_available_font_arr);
            $fontPrice = $product_available_font_arr[1][0];
            $css.= '.product-available,.product-unavailable,.product-last-items{'.($fontPrice != $this->_font_inherit ? 'font-family:"'.$fontPrice.'", Tahoma, sans-serif, Arial;' : '').$product_available_font_arr[2][0].$product_available_font_arr[3][0].'}';
        }

        if($product_available_font_size = (int)Configuration::get('STSN_PRODUCT_AVAILABLE_FONT_SIZE'))
        {
            $css .='.product-available,.product-unavailable,.product-last-items{font-size:'.($product_available_font_size).'px;}';
        }

        if($pro_available_color = Configuration::get('STSN_PRO_AVAILABLE_COLOR_BG'))
            $css .='.product-available{background-color:'.$pro_available_color.';}';
        if($pro_unavailable_color = Configuration::get('STSN_PRO_UNAVAILABLE_COLOR_BG'))
            $css .='.product-unavailable{background-color:'.$pro_unavailable_color.';}';
        if($pro_last_items = Configuration::get('STSN_PRO_LAST_ITEMS_BG'))
            $css .='.product-last-items{background-color:'.$pro_last_items.';}';

        if($pro_available_color = Configuration::get('STSN_PRO_AVAILABLE_COLOR'))
            $css .='.product-available{color:'.$pro_available_color.';}';
        if($pro_unavailable_color = Configuration::get('STSN_PRO_UNAVAILABLE_COLOR'))
            $css .='.product-unavailable{color:'.$pro_unavailable_color.';}';
        if($pro_last_items = Configuration::get('STSN_PRO_LAST_ITEMS'))
            $css .='.product-last-items{color:'.$pro_last_items.';}';

        if($btn_font_size = (int)Configuration::get('STSN_BTN_FONT_SIZE'))
        {
            $css .='.btn-default, .go, .add-to-cart.btn, .ajax_add_to_cart_button.btn span, .view_button.btn span{font-size:'.$btn_font_size.'px;}';
            $css .='.btn-default i, .go i, .add-to-cart.btn i, .ajax_add_to_cart_button.btn i, .view_button.btn i{font-size:'.($btn_font_size+2).'px;}';
        }
        if($flyout_font_size = (int)Configuration::get('STSN_FLYOUT_FONT_SIZE'))
        {
            $css .='.hover_fly .hover_fly_btn{font-size:'.$flyout_font_size.'px;}';
            $css .='.hover_fly .hover_fly_btn .hover_fly_btn_inner i{font-size:'.($flyout_font_size+2).'px;}';
        }
        if($truncate_cate_desc_height_desktop = (int)Configuration::get('STSN_TRUNCATE_CATE_DESC_HEIGHT_DESKTOP'))
            $css .='.category-description.truncate_cate_desc_2,.manufacturer-description.truncate_cate_desc_2{max-height:'.$truncate_cate_desc_height_desktop.'px;}.category-description.truncate_cate_desc_2 .st_read_more, .manufacturer-description.truncate_cate_desc_2 .st_read_more{height: calc(4000% - '.($truncate_cate_desc_height_desktop*39).'px);}';
        if($truncate_cate_desc_height_mobile = (int)Configuration::get('STSN_TRUNCATE_CATE_DESC_HEIGHT_MOBILE'))
            $css .='@media (max-width: 991px) {.category-description.truncate_cate_desc_1, .manufacturer-description.truncate_cate_desc_1, .category-description.truncate_cate_desc_2, .manufacturer-description.truncate_cate_desc_2{max-height:'.$truncate_cate_desc_height_mobile.'px;}.category-description.truncate_cate_desc_1 .st_read_more, .category-description.truncate_cate_desc_1 .st_read_more, .manufacturer-description.truncate_cate_desc_2 .st_read_more, .manufacturer-description.truncate_cate_desc_2 .st_read_more{height: calc(4000% - '.($truncate_cate_desc_height_mobile*39).'px);}}';
        if($truncate_short_desc_height_desktop = (int)Configuration::get('STSN_TRUNCATE_SHORT_DESC_HEIGHT_DESKTOP'))
            $css .='.product-description-short.truncate_cate_desc_2{max-height:'.$truncate_short_desc_height_desktop.'px;}.product-description-short.truncate_cate_desc_2 .st_read_more{height: calc(4000% - '.($truncate_short_desc_height_desktop*39).'px);}';
        if($truncate_short_desc_height_mobile = (int)Configuration::get('STSN_TRUNCATE_SHORT_DESC_HEIGHT_MOBILE'))
            $css .='@media (max-width: 991px) {.product-description-short.truncate_cate_desc_1, .product-description-short.truncate_cate_desc_2{max-height:'.$truncate_short_desc_height_mobile.'px;}.product-description-short.truncate_cate_desc_1 .st_read_more,.product-description-short.truncate_cate_desc_2 .st_read_more{height: calc(4000% - '.($truncate_short_desc_height_mobile*39).'px);}}';
        if($truncate_pro_desc_height_desktop = (int)Configuration::get('STSN_TRUNCATE_PRO_DESC_HEIGHT_DESKTOP'))
            $css .='.product_description_container.truncate_cate_desc_2{max-height:'.$truncate_pro_desc_height_desktop.'px;}.product_description_container.truncate_cate_desc_2 .st_read_more{height: calc(4000% - '.($truncate_pro_desc_height_desktop*39).'px);}';
        if($truncate_pro_desc_height_mobile = (int)Configuration::get('STSN_TRUNCATE_PRO_DESC_HEIGHT_MOBILE'))
            $css .='@media (max-width: 991px) {.product_description_container.truncate_cate_desc_1, .product_description_container.truncate_cate_desc_2{max-height:'.$truncate_pro_desc_height_mobile.'px;}.product_description_container.truncate_cate_desc_1 .st_read_more,.product_description_container.truncate_cate_desc_2 .st_read_more{height: calc(4000% - '.($truncate_pro_desc_height_mobile*39).'px);}}';

        if($pro_kk_color = Configuration::get('STSN_PRO_KK_COLOR'))
            $css .='.kk_container .swiper-pagination-custom span,.kk_close, .kk_container .swiper-button-lr.swiper-navigation-circle .swiper-button{color:'.$pro_kk_color.';}';
        if($pro_kk_bg = Configuration::get('STSN_PRO_KK_BG')){
            $css .='.kk_container{background-color:'.$pro_kk_bg.';}';

            $pro_kk_bg_hex = self::hex2rgb($pro_kk_bg);
            $css .= '.kk_container .swiper-pagination-custom span,.kk_close, .kk_container .swiper-button-lr.swiper-navigation-circle .swiper-button{background-color: '.$pro_kk_bg.';background:rgba('.$pro_kk_bg_hex[0].','.$pro_kk_bg_hex[1].','.$pro_kk_bg_hex[2].',0.1);}'; 
            $css .= '.kk_close:hover, .kk_container .swiper-button-lr.swiper-navigation-circle .swiper-button:hover{background-color: '.$pro_kk_bg.';background:rgba('.$pro_kk_bg_hex[0].','.$pro_kk_bg_hex[1].','.$pro_kk_bg_hex[2].',0.5);}';  
        }

        //
        $css .= $res_css;
        if (Configuration::get('STSN_CUSTOM_CSS') != "")
			$css .= html_entity_decode(str_replace('¤', '\\', Configuration::get('STSN_CUSTOM_CSS')));
        
        if (Shop::getContext() == Shop::CONTEXT_SHOP)
        {
            $cssFile = $this->local_path."views/css/customer-s".(int)$this->context->shop->getContextShopID().".css";
    		$write_fd = fopen($cssFile, 'w') or die('can\'t open file "'.$cssFile.'"');
    		fwrite($write_fd, $css);
    		fclose($write_fd);

            Media::clearCache();
        }
        if (Configuration::get('STSN_CUSTOM_JS') != "")
		{
		    $jsFile = $this->local_path."views/js/customer".$id_shop.".js";
    		$write_fd = fopen($jsFile, 'w') or die('can\'t open file "'.$jsFile.'"');
    		fwrite($write_fd, html_entity_decode(str_replace('¤', '\\', Configuration::get('STSN_CUSTOM_JS'))));
    		fclose($write_fd);
		}
        else
            if(file_exists($this->local_path.'views/js/customer'.$id_shop.'.js'))
                unlink($this->local_path.'views/js/customer'.$id_shop.'.js');
    }
    
    public static function hex2rgb($hex) {
       $hex = str_replace("#", "", $hex);
    
       if(strlen($hex) == 3) {
          $r = hexdec(substr($hex,0,1).substr($hex,0,1));
          $g = hexdec(substr($hex,1,1).substr($hex,1,1));
          $b = hexdec(substr($hex,2,1).substr($hex,2,1));
       } else {
          $r = hexdec(substr($hex,0,2));
          $g = hexdec(substr($hex,2,2));
          $b = hexdec(substr($hex,4,2));
       }
       $rgb = array($r, $g, $b);
       return $rgb;
    }
    
	public function hookActionShopDataDuplication($params)
    {
        $defaults = $this->defaults;
        $old_group = shop::getGroupFromShop($params['old_id_shop']);
        $new_group = shop::getGroupFromShop($params['new_id_shop']);
        $languages = Language::getLanguages(false);
        foreach($defaults as $k=>$v){
            if(in_array($k, array('copyright_text','welcome','welcome_logged','welcome_link'))){
                $temp = array();
                foreach ($languages as $language)
                {
                    $temp[$language['id_lang']] = Configuration::get('STSN_'.strtoupper($k), $language['id_lang'], $old_group, $params['old_id_shop']);
                }
                Configuration::updateValue('STSN_'.strtoupper($k), $temp, $k=='copyright_text', $new_group, $params['new_id_shop']);
            }else{
                Configuration::updateValue('STSN_'.strtoupper($k), Configuration::get('STSN_'.strtoupper($k), null, $old_group, $params['old_id_shop']), false, $new_group, $params['new_id_shop']);
            }
        }
    }

    protected function getDomain($shared_urls = null)
    {
        $r = '!(?:(\w+)://)?(?:(\w+)\:(\w+)@)?([^/:]+)?(?:\:(\d*))?([^#?]+)?(?:\?([^#]+))?(?:#(.+$))?!i';

        if (!preg_match ($r, Tools::getHttpHost(false, false), $out) || !isset($out[4]))
            return false;

        if (preg_match('/^(((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]|[1-9]).)'.
            '{1}((25[0-5]|2[0-4][0-9]|[1]{1}[0-9]{2}|[1-9]{1}[0-9]|[0-9]).)'.
            '{2}((25[0-5]|2[0-4][0-9]|[1]{1}[0-9]{2}|[1-9]{1}[0-9]|[0-9]){1}))$/', $out[4]))
            return false;
        if (!strstr(Tools::getHttpHost(false, false), '.'))
            return false;

        $domain = false;
        if ($shared_urls !== null)
        {
            foreach ($shared_urls as $shared_url)
            {
                if ($shared_url != $out[4])
                    continue;
                if (preg_match('/^(?:.*\.)?([^.]*(?:.{2,4})?\..{2,3})$/Ui', $shared_url, $res))
                {
                    $domain = '.'.$res[1];
                    break;
                }
            }
        }
        if (!$domain)
            $domain = $out[4];
        return $domain;
    }

    public function hookDisplayHeader($params)
	{
        $id_shop = (int)Shop::getContextShopID();
	    $theme_font = array();
    	$theme_font[] = Configuration::get('STSN_FONT_TEXT');
        $theme_font[] = Configuration::get('STSN_FONT_HEADING');
        $theme_font[] = Configuration::get('STSN_PRO_NAME');
        $theme_font[] = Configuration::get('STSN_FONT_PRODUCT_NAME');
        $theme_font[] = Configuration::get('STSN_FONT_PRICE');
        $theme_font[] = Configuration::get('STSN_FONT_MENU');
        $theme_font[] = Configuration::get('STSN_SECOND_FONT_MENU');
        $theme_font[] = Configuration::get('STSN_THIRD_FONT_MENU');
    	$theme_font[] = Configuration::get('STSN_FONT_CART_BTN');
        $theme_font[] = Configuration::get('STSN_VER_FONT_MENU');
    	//$theme_font[] = Configuration::get('STSN_FONT_TITLE');
        
        $font_latin_support = Configuration::get('STSN_FONT_LATIN_SUPPORT');
        $font_cyrillic_support = Configuration::get('STSN_FONT_CYRILLIC_SUPPORT');
        $font_vietnamese = Configuration::get('STSN_FONT_VIETNAMESE');
        $font_greek_support = Configuration::get('STSN_FONT_GREEK_SUPPORT');
        $font_arabic_support = Configuration::get('STSN_FONT_ARABIC_SUPPORT');
        $font_support = ($font_latin_support || $font_cyrillic_support || $font_vietnamese || $font_greek_support || $font_arabic_support) ? '&subset=' : '';
        $font_latin_support && $font_support .= 'latin,latin-ext,';
        $font_cyrillic_support && $font_support .= 'cyrillic,cyrillic-ext,';
        $font_vietnamese && $font_support .= 'vietnamese,';
        $font_greek_support && $font_support .= 'greek,greek-ext,';
        $font_arabic_support && $font_support .= 'arabic,';
        
        foreach($this->module_font AS $module) {
            if ($module_font = Configuration::get('STSN_FONT_MODULE_'.strtoupper($module))) {
                foreach(explode('|', $module_font) AS $font) {
                    $theme_font[] = $font;
                }
            }
        }
        $theme_font = array_unique($theme_font);
        if(is_array($theme_font) && count($theme_font) && !$this->isEbTransPandaInstalled()) {
            $fonts = array();
            foreach($theme_font as $v)
            {
                $arr = explode(':', $v);
                if(!isset($arr[0]) || !$arr[0] || $arr[0] == $this->_font_inherit || in_array($arr[0], $this->systemFonts))
                    continue;
                $gf_key = preg_replace('/\s/iS','_',$arr[0]);
                if (isset($arr[1]) && isset($this->googleFonts[$gf_key]) && !in_array($arr[1], $this->googleFonts[$gf_key]['variants']))
                    $v = $arr[0];
                $fonts[] = str_replace(' ', '+', $v);
            }
            if ($fonts) {
                $this->context->controller->registerStylesheet('stthemeeditor-google-fonts', $this->context->link->protocol_content."fonts.googleapis.com/css?family=".implode('|', $fonts).($font_support ? rtrim($font_support,',') : ''), ['server' => 'remote']);
            }    
        }
        //
        $vals = $this->getStBasicVals();

        $this->context->controller->addJS($this->_path.'views/js/owl.carousel.js');
        $this->context->controller->addJS($this->_path.'views/js/easyzoom.js');
        // $this->context->controller->addJS($this->_path.'views/js/jquery.stellar.js'); resize de shi hou, scrolltop bao li.
        $this->context->controller->addJS($this->_path.'views/js/jarallax.js');
        $this->context->controller->addJS($this->_path.'views/js/jarallax-video.js');
        $this->context->controller->addJS($this->_path.'views/js/jquery.timeago.js');
        $this->context->controller->addJS($this->_path.'views/js/dragscroll.js');
        $this->context->controller->addJQueryUI('ui.slider');//for faceted v3

        
        if( !Tools::isSubmit('steasybuilder') || ( Tools::isSubmit('steasybuilder') && Tools::getValue('preview')==1 ) )
            foreach ($vals['smarty_val']['responsive_css'] as $css) {
                $this->context->controller->registerStylesheet($css['id'], $css['url'], isset($css['params'])?$css['params']:[]);
            }

        if(!$this->isEbTransPandaInstalled()){
            if (Shop::getContext() == Shop::CONTEXT_SHOP) // why this in the front office, it seems no need
            {
                if(!file_exists($this->local_path.'views/css/customer-s'.$this->context->shop->getContextShopID().'.css'))
                    $this->writeCss();
                $this->context->controller->registerStylesheet('st-custom-css', 'modules/'.$this->name.'/views/css/customer-s'.$this->context->shop->getContextShopID().'.css', ['priority'=>101]);
            }
        }
        //
        $this->context->controller->registerStylesheet('stthemeeditor-animate', 'modules/'.$this->name.'/views/css/animate.min.css');

        // $this->context->controller->addJqueryPlugin('fancybox');
        // 
        
		$this->context->smarty->assign('sttheme', $vals['smarty_val']);
        Media::addJsDef(array('sttheme' => $vals['js_val']));

		return $this->display(__FILE__, 'stthemeeditor-header.tpl');
	}
    // in stthemeeditor and theme-editor-product-page/manager.php
    public function getResponsiveMax(){
        $responsive_max = (int)Configuration::get('STSN_RESPONSIVE_MAX');
        if(!$this->isEbTransPandaInstalled())
            return $responsive_max;
        $steb_content_width = Configuration::get('STEB_CONTENT_WIDTH');
        if(!$steb_content_width)
            return $responsive_max;
        elseif($steb_content_width=='2')
            return 3;
        elseif($steb_content_width=='1'){
            $steb_content_width_px = Tools::unSerialize(Configuration::get('STEB_CONTENT_WIDTH_PX'));
            if(!is_array($steb_content_width_px))
                return $steb_content_width;
            if($steb_content_width_px['size']>=1600)
                return 3;
            elseif($steb_content_width_px['size']>=1440)
                return 2;
            elseif($steb_content_width_px['size']>=1200)
                return 1;
            else
                return 0;
        }

    }
    public function getFullwidth(){
        $responsive_max = (int)Configuration::get('STSN_RESPONSIVE_MAX');
        if(!$this->isEbTransPandaInstalled())
            return $responsive_max==3 ? 1 : 0;
        $steb_content_width = Configuration::get('STEB_CONTENT_WIDTH');
            return $steb_content_width=='2' ? 1 : 0;
    }
    public function isEbTransPandaInstalled(){
        if(!is_null($this->has_eb_trans_panda))
            return $this->has_eb_trans_panda;
        $this->has_eb_trans_panda = Module::isInstalled('steasybuilder') && Module::isEnabled('steasybuilder'); //?steasy_trans_panda
        return $this->has_eb_trans_panda;

    }
    public function getStBasicVals(){
        if (isset(self::$cache_vals) && self::$cache_vals)
            return self::$cache_vals;

        self::$cache_vals = false;

        $vals = array(
            'smarty_val' => array(),
            'js_val' => array(),
            );
        $id_shop = (int)Shop::getContextShopID();
            
        $mobile_detect = $this->context->getMobileDetect();
        $mobile_device = $mobile_detect->isMobile() || $mobile_detect->isTablet();
        $user_agent = $mobile_detect->getUserAgent();
        $is_safari = strpos($user_agent, 'Chrome')===false && stripos($user_agent, 'Safari')!==false;
        
        $list_grid = 0;
        if(Configuration::get('STSN_PRODUCT_VIEW_SWITHER') && isset($_COOKIE['st_list_grid']))
        {
            if($_COOKIE['st_list_grid']=='list')
                $list_grid = 1;
        }
        elseif(($mobile_device && Configuration::get('STSN_PRODUCT_VIEW_MOBILE')) || (!$mobile_device && (Configuration::get('STSN_PRODUCT_VIEW')==1 || Configuration::get('STSN_PRODUCT_VIEW')=='list_view')))
            $list_grid = 1;


        $is_responsive = 1;
        
        $cate_pro_image_type_name = Configuration::get('STSN_CATE_PRO_IMAGE_TYPE') ? Configuration::get('STSN_CATE_PRO_IMAGE_TYPE') : 'home_default';
        $gallery_image_type = Configuration::get('STSN_GALLERY_IMAGE_TYPE') ? Configuration::get('STSN_GALLERY_IMAGE_TYPE') : 'medium_default';
        $thumb_image_type = Configuration::get('STSN_THUMB_IMAGE_TYPE') ? Configuration::get('STSN_THUMB_IMAGE_TYPE') : 'cart_default';
        $responsive_max = $this->getResponsiveMax();
        $fullwidth = $this->getFullwidth();
        $vals['smarty_val'] = array(
            'ps_version' => _PS_VERSION_,
            'is_mobile_device' => $mobile_device,
            'is_safari' => $is_safari,
            'is_rtl' => (int)$this->context->language->is_rtl, 
            'enabled_version_swithing' => 0,
            'version_switching' => 0,
            'list_grid' => $list_grid,
            'currency_iso_code' => $this->context->currency->iso_code, //For products listing page after ajax search, probably can be removed in future.
            'lang_iso_code' => $this->context->language->iso_code, //For products listing page after ajax search, probably can be removed in future.
            'img_prod_url' => Tools::getCurrentUrlProtocolPrefix().Tools::getMediaServer(_THEME_PROD_DIR_)._THEME_PROD_DIR_, //the same as above, got this from frontController getTemplateVarUrls 
            'hover_image' => Module::isInstalled('sthoverimage') && Module::isEnabled('sthoverimage'),
            'is_catalog' => (bool) Configuration::isCatalogMode(),
            'brand_default' => Image::getSize(ImageType::getFormattedName('brand')),//for brand image on product page
            'home_default' => Image::getSize(ImageType::getFormattedName('home')),//for product page
            'cate_pro_image_type_name' => $cate_pro_image_type_name,//to do should check if the selected image type is exsit. this also works for the sthoveimage module
            'cate_pro_image_type_size' => Image::getSize($cate_pro_image_type_name),
            'gallery_image_type' => $gallery_image_type,
            'thumb_image_type' => $thumb_image_type,
            'sidebar_transition' => (int)Configuration::get('ST_SB_SIDEBAR_TRANSITION'),//in case the siebar module is not installed
            'is_ajax' => isset($_SERVER['HTTP_ACCEPT']) ? preg_match(
                '#\bapplication/json\b#',
                $_SERVER['HTTP_ACCEPT']
            ) : false,
            'shop_id' => $this->context->shop->id,
            'from_infi' => Tools::getValue('from_infi')!==false,
            //'tpldir' => _PS_THEME_DIR_.'templates/',
            'responsive_max' => $responsive_max,
            'fullwidth' => $fullwidth,
        );

        $vals['js_val'] = array(
            'theme_version' => $this->version,
            'ps_version' => _PS_VERSION_,
            'is_rtl' => $vals['smarty_val']['is_rtl'],
            'is_mobile_device' => $vals['smarty_val']['is_mobile_device'],
            'gallery_image_type' => $vals['smarty_val']['gallery_image_type'],
            'thumb_image_type' => $vals['smarty_val']['thumb_image_type'],
            'responsive_max' => $responsive_max,
            'fullwidth' => $fullwidth,
        );
        foreach($this->defaults as $k=>$v)
        {
            if(isset($v['smarty_val'])){
                if($v['smarty_val']==2){
                    $tpm=Configuration::get('STSN_'.strtoupper($k));
                    if(strpos($tpm,'.')){
                        $vals['smarty_val'][$k]=$tpm+0;
                    }else{
                        $vals['smarty_val'][$k] = (int)$tpm;
                    }
                }else{
                    $vals['smarty_val'][$k] = Configuration::get('STSN_'.strtoupper($k));
                }
            }
            if(isset($v['js_val'])){
                if($v['js_val']==2){
                    $tpm=Configuration::get('STSN_'.strtoupper($k));
                    if(strpos($tpm,'.')){
                        $vals['js_val'][$k]=$tpm+0;
                    }else{
                        $vals['js_val'][$k] = (int)$tpm;
                    }
                }else{
                    $vals['js_val'][$k] = Configuration::get('STSN_'.strtoupper($k));
                }
            }
        }

        if($vals['smarty_val']['product_gallerys'] && Dispatcher::getInstance()->getController()=='product' && $id_product = Tools::getValue('id_product'))
        {
            $image = $this->getProductImages($id_product,null,true);
            $vals['smarty_val']['pro_images'] = $image;
            $vals['js_val']['pro_images'] = $image;
        }

        //moved from displayHeader 1.7.3.1
        $cookie_domain = $this->getDomain();
        $cookie_path = trim($this->context->shop->physical_uri, '/\\').'/';
        if ($cookie_path[0] != '/') $cookie_path = '/'.$cookie_path;
        $cookie_path = rawurlencode($cookie_path);
        $cookie_path = str_replace('%2F', '/', $cookie_path);
        $cookie_path = str_replace('%7E', '~', $cookie_path);
        
        if (!$cookie_path)
            $cookie_path = '/';
        if (!$cookie_domain)
            $cookie_domain = null;

        $footer_img_src = $retina_logo_src = $mobile_logo_src = '';
        $footer_img_link = Configuration::get('STSN_FOOTER_IMG');
        if($footer_img_link)
        {
           $footer_img_src = (strpos($footer_img_link, '/img/cms/') === 0 ? rtrim(__PS_BASE_URI__, '/') : ($footer_img_link==$this->defaults["footer_img"]['val'] ? $this->_path : _THEME_PROD_PIC_DIR_ )).$footer_img_link;
           $footer_img_src = $this->context->link->getMediaLink($footer_img_src);
        }
        $retina_logo = Configuration::get('STSN_RETINA_LOGO');
        if($vals['smarty_val']['retina'] && $retina_logo)
        {
           $retina_logo_src = $this->context->link->getMediaLink((strpos($retina_logo, '/img/cms/') === 0 ? rtrim(__PS_BASE_URI__, '/') : _THEME_PROD_PIC_DIR_).$retina_logo);
        }
        $mobile_logo = Configuration::get('STSN_MOBILE_LOGO');
        if($mobile_logo)
        {
           $mobile_logo_src = $this->context->link->getMediaLink((strpos($mobile_logo, '/img/cms/') === 0 ? rtrim(__PS_BASE_URI__, '/') : _THEME_PROD_PIC_DIR_).$mobile_logo);
        }

        $extra_smarty_val = array(
            //In case someone who forgot to disable the default moblie theme
            'st_logo_image_width' => Configuration::get('SHOP_LOGO_WIDTH'),
            'st_logo_image_height' => Configuration::get('SHOP_LOGO_HEIGHT'),
            'body_has_background' => (Configuration::get('STSN_BODY_BG_COLOR') || Configuration::get('STSN_BODY_BG_PATTERN') || Configuration::get('STSN_BODY_BG_IMG')),
            'welcome' => Configuration::get('STSN_WELCOME', $this->context->language->id),
            'welcome_logged' => Configuration::get('STSN_WELCOME_LOGGED', $this->context->language->id),
            'welcome_link' => Configuration::get('STSN_WELCOME_LINK', $this->context->language->id),
            // 'customer_group_without_tax' => Group::getPriceDisplayMethod($this->context->customer->id_default_group),
            'copyright_text' => html_entity_decode(Configuration::get('STSN_COPYRIGHT_TEXT', $this->context->language->id)),
            //to do these urls should be formated by the getMediaServer fucntion
            'footer_img_src' => $footer_img_src, 
            'retina_logo_src' => $retina_logo_src, 
            'mobile_logo_src' => $mobile_logo_src, 
            /*'icon_iphone_57' => Configuration::get('STSN_ICON_IPHONE_57') ? $this->context->link->getMediaLink((Configuration::get('STSN_ICON_IPHONE_57') != $this->defaults['icon_iphone_57']['val'] ? _THEME_PROD_PIC_DIR_ : $this->_path).Configuration::get('STSN_ICON_IPHONE_57')) : '',
            'icon_iphone_72' => Configuration::get('STSN_ICON_IPHONE_72') ? $this->context->link->getMediaLink((Configuration::get('STSN_ICON_IPHONE_72') != $this->defaults['icon_iphone_72']['val'] ? _THEME_PROD_PIC_DIR_ : $this->_path).Configuration::get('STSN_ICON_IPHONE_72')) : '',
            'icon_iphone_114' => Configuration::get('STSN_ICON_IPHONE_114') ? $this->context->link->getMediaLink((Configuration::get('STSN_ICON_IPHONE_114') != $this->defaults['icon_iphone_114']['val'] ? _THEME_PROD_PIC_DIR_ : $this->_path).Configuration::get('STSN_ICON_IPHONE_114')) : '',
            'icon_iphone_144' => Configuration::get('STSN_ICON_IPHONE_144') ? $this->context->link->getMediaLink((Configuration::get('STSN_ICON_IPHONE_144') != $this->defaults['icon_iphone_144']['val'] ? _THEME_PROD_PIC_DIR_ : $this->_path).Configuration::get('STSN_ICON_IPHONE_144')) : '',*/

            'tracking_code' =>  html_entity_decode(Configuration::get('STSN_TRACKING_CODE')),
            'head_code' =>  html_entity_decode(Configuration::get('STSN_HEAD_CODE')),

            'cookie_domain' =>  $cookie_domain,
            'cookie_path' =>  $cookie_path,
        );

        $iphone_icon_array = array('16', '32', '180','svg');
        foreach($iphone_icon_array as $v)
        {
            $icon = Configuration::get('STSN_ICON_IPHONE_'.strtoupper($v));
            $icons=$this->getFavicon($icon,'icon_iphone_'.$v);
            $extra_smarty_val['icon_iphone_'.$v] = $icon ? $icons : '';
        } 
        if(Configuration::get('STSN_ICON_IPHONE_150')){
            if (!file_exists(_PS_UPLOAD_DIR_.$this->name.'/'.$this->context->shop->id.'/browserconfig.xml'))
                $this->generateBrowserConfig(time());
            $extra_smarty_val['browserconfig'] = $this->context->link->getMediaLink(_THEME_PROD_PIC_DIR_.$this->name.'/'.$this->context->shop->id.'/browserconfig.xml');
        }
        if(Configuration::get('STSN_ICON_IPHONE_192') || Configuration::get('STSN_ICON_IPHONE_512')){
            if(!file_exists(_PS_UPLOAD_DIR_.$this->name.'/'.$this->context->shop->id.'/site.webmanifest'))
                $this->generateWebmanifest(time());
            $extra_smarty_val['site_webmanifest'] = _THEME_PROD_PIC_DIR_.$this->name.'/'.$this->context->shop->id.'/site.webmanifest';
        }

        $extra_js_val = array(
            'cookie_domain' =>  $cookie_domain,
            'cookie_path' =>  $cookie_path,
            'drop_down' =>  (int)Configuration::get('STSN_DROP_DOWN'),
        );
        $lang_js_val = array(
            'magnificpopup_tprev' =>  $this->getTranslator()->trans('Previous', array(), 'Shop.Theme.Panda'),
            'magnificpopup_tnext' =>  $this->getTranslator()->trans('Next', array(), 'Shop.Theme.Panda'),
            'magnificpopup_tcounter' =>  $this->getTranslator()->trans('%curr% of %total%', array(), 'Shop.Theme.Panda'),
        );
        $vals['smarty_val'] = array_merge($extra_smarty_val, $vals['smarty_val']);
        $vals['js_val'] = array_merge($extra_js_val, $lang_js_val, $vals['js_val']);

        if(!$this->isEbTransPandaInstalled() && file_exists($this->local_path.'views/js/customer'.$id_shop.'.js'))
        {
            $custom_js_path = $this->_path.'views/js/customer'.$this->context->shop->getContextShopID().'.js?'.rand(1000, 9999);
            $vals['smarty_val']['custom_js'] = context::getContext()->link->protocol_content.Tools::getMediaServer($custom_js_path).$custom_js_path;
        }
        
        $vals['smarty_val']['custom_css'] = array();
        $vals['smarty_val']['custom_css_media'] = 'all';
        /*if (Shop::getContext() == Shop::CONTEXT_SHOP)
        {
            $custom_css_path = $this->_path.'views/css/customer-s'.$this->context->shop->getContextShopID().'.css?'.rand(1000, 9999);
            $vals['smarty_val']['custom_css'][] = ['id'=>'st-custom-css', 'url'=>context::getContext()->link->protocol_content.Tools::getMediaServer($custom_css_path).$custom_css_path];
        }*/

        $responsive_css = [];
        // if($is_responsive && (!$vals['smarty_val']['enabled_version_swithing'] || $vals['smarty_val']['version_switching']==0))
        if($is_responsive)
        {
            $responsive_css[] = ['id'=>'stthemeeditor-responsive', 'url'=>'assets/css/responsive.css'];
            if ($this->context->language->is_rtl)
                $responsive_css[] = ['id'=>'stthemeeditor-rtl-responsive', 'url'=>'assets/css/rtl-responsive.css', 'params'=>['priority' => 901]];//after rtl.css
            if($vals['smarty_val']['responsive_max'])
                $responsive_css[] = ['id'=>'stthemeeditor-responsive-lg', 'url'=>'assets/css/responsive-lg.css'];
            else
                $responsive_css[] = ['id'=>'stthemeeditor-responsive-lg-max', 'url'=>'assets/css/responsive-lg-max.css'];
        }else{
            $responsive_css[] = ['id'=>'stthemeeditor-responsiveness', 'url'=>'assets/css/responsiveness.css'];
            if($vals['smarty_val']['responsive_max']>=1)
                $responsive_css[] = ['id'=>'stthemeeditor-responsiveness-xl', 'url'=>'assets/css/responsiveness-xl.css'];
            if($vals['smarty_val']['responsive_max']>=2)
                $responsive_css[] = ['id'=>'stthemeeditor-responsiveness-xxl', 'url'=>'assets/css/responsiveness-xxl.css'];
        }

        // if($is_responsive && (!$vals['smarty_val']['enabled_version_swithing'] || $vals['smarty_val']['version_switching']==0))
        if($is_responsive)
        {
            if($vals['smarty_val']['responsive_max']>=1)
                $responsive_css[] = ['id'=>'stthemeeditor-responsive-xl', 'url'=>'assets/css/responsive-xl.css'];
            if($vals['smarty_val']['responsive_max']==1)
                $responsive_css[] = ['id'=>'stthemeeditor-responsive-xl-max', 'url'=>'assets/css/responsive-xl-max.css'];

            if($vals['smarty_val']['responsive_max']>=2)
            {
                $responsive_css[] = ['id'=>'stthemeeditor-responsive-xl-min', 'url'=>'assets/css/responsive-xl-min.css'];
                $responsive_css[] = ['id'=>'stthemeeditor-responsive-xxl', 'url'=>'assets/css/responsive-xxl.css'];
            }
            if($vals['smarty_val']['responsive_max']==2)
                $responsive_css[] = ['id'=>'stthemeeditor-responsive-xxl-max', 'url'=>'assets/css/responsive-xxl-max.css'];

            if($vals['smarty_val']['responsive_max']>=3)
            {
                $responsive_css[] = ['id'=>'stthemeeditor-responsive-xxl-min', 'url'=>'assets/css/responsive-xxl-min.css'];
                $responsive_css[] = ['id'=>'stthemeeditor-responsive-fw', 'url'=>'assets/css/responsive-fw.css'];
            }
        }
        $vals['smarty_val']['responsive_css'] = $responsive_css;

        if( Tools::isSubmit('steasybuilder') && !Tools::isSubmit('preview') )
            $vals['smarty_val']['custom_css'] = array_merge($vals['smarty_val']['custom_css'], array_map(function($css){
                    if(file_exists(_PS_THEME_DIR_.$css['url']))
                        $css['url'] = Context::getContext()->link->protocol_content.Tools::getMediaServer(_PS_THEME_URI_.$css['url'])._PS_THEME_URI_.$css['url'];
                    else
                        $css['url'] = Context::getContext()->link->protocol_content.Tools::getMediaServer(_PS_PARENT_THEME_URI_.$css['url'])._PS_PARENT_THEME_URI_.$css['url'];

                    return $css;
                }, $responsive_css));

        self::$cache_vals = $vals;
        return $vals;
    }
	protected function stGetCacheId($key,$name = null)
	{
		$cache_id = parent::getCacheId($name);
		return $cache_id.'_'.$key;
	}
    public function hookDisplayRightColumnProduct($params)
    {        
	    if(!Module::isInstalled('blockviewed') || !Module::isEnabled('blockviewed'))
            return false;
            
		$id_product = (int)Tools::getValue('id_product');
        if(!$id_product)
            return false;
            
		$productsViewed = (isset($params['cookie']->viewed) && !empty($params['cookie']->viewed)) ? array_slice(array_reverse(explode(',', $params['cookie']->viewed)), 0, Configuration::get('PRODUCTS_VIEWED_NBR')) : array();

		if ($id_product && !in_array($id_product, $productsViewed))
		{
			if(isset($params['cookie']->viewed) && !empty($params['cookie']->viewed))
		  		$params['cookie']->viewed .= ',' . (int)$id_product;
			else
		  		$params['cookie']->viewed = (int)$id_product;
		}
        return false;
    }
    
    public function initTab()
    {
        $html = '<div class="st_sidebar col-xs-12 col-lg-2"><ul class="nav nav-tabs">';
        foreach($this->tabs AS $i => $tab) {
            if (!$this->theme_valid && $i > 0) {
                continue;
            }
            $html .= '<li class="nav-item"><a href="javascript:;" title="'.$tab['name'].'" data-fieldset="'.$tab['id'].'">'.$tab['name'].'</a></li>';
        }
        $html .= '</ul></div>';
        return $html;
    }
    public function initToolbarBtn()
    {
        $token = Tools::getAdminTokenLite('AdminModules');
        $toolbar_btn = array(
            'demo_1' => array(
                'desc' => $this->getTranslator()->trans('Demo 1', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_13' => array(
                'desc' => $this->getTranslator()->trans('Demo 2', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_3' => array(
                'desc' => $this->getTranslator()->trans('Demo 3', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_4' => array(
                'desc' => $this->getTranslator()->trans('Demo 4', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_8' => array(
                'desc' => $this->getTranslator()->trans('Demo 5', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_6' => array(
                'desc' => $this->getTranslator()->trans('Demo 6', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_2' => array(
                'desc' => $this->getTranslator()->trans('Demo 7', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_16' => array(
                'desc' => $this->getTranslator()->trans('Demo 8', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_12' => array(
                'desc' => $this->getTranslator()->trans('Demo 9', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_15' => array(
                'desc' => $this->getTranslator()->trans('Demo 10', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_14' => array(
                'desc' => $this->getTranslator()->trans('Demo 11', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_17' => array(
                'desc' => $this->getTranslator()->trans('Demo 13', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_18' => array(
                'desc' => $this->getTranslator()->trans('Demo 14', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_9' => array(
                'desc' => $this->getTranslator()->trans('Demo 15', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_10' => array(
                'desc' => $this->getTranslator()->trans('Demo 16', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_5' => array(
                'desc' => $this->getTranslator()->trans('Demo 17', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_11' => array(
                'desc' => $this->getTranslator()->trans('Demo 18', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            'demo_7' => array(
                'desc' => $this->getTranslator()->trans('Demo 19', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),
            /*'demo_20' => array(
                'desc' => $this->getTranslator()->trans('Demo 20', array(), 'Modules.Stthemeeditor.Admin'),
                'type' => 'dropdown',
            ),*/
            'export' => array(
                'desc' => $this->getTranslator()->trans('Export', array(), 'Modules.Stthemeeditor.Admin'),
                'class' => 'icon-share',
                'type' => 'button',
                'js' => '',
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&export'.$this->name.'&token='.$token,
            ),
        );
        $html = '<div class="panel st_toolbtn clearfix"><ul class="nav nav-pills">';
        foreach($toolbar_btn AS $k => $btn)
        {
            $desc = $btn['desc'];
            if ($btn['type'] == 'dropdown') {
                $html .= '<li class="nav-item dropdown">
                	<a href="javascript:void(0)" class="dropdown-toggle btn btn-default" data-toggle="dropdown">'.$desc.'<i class="icon-caret-down"></i></a>
                	<ul class="dropdown-menu">
                	<li>
                		<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&predefineddemostore'.$this->name.'='.$k.'&token='.$token.'" 
                        onclick="if (confirm(\''.$this->getTranslator()->trans('Importing %s%, are your sure?', array('%s%'=>$desc), 'Admin.Theme.Panda').'\')){return true;}else{event.preventDefault();}"
                        title="'.$this->getTranslator()->trans('All data from ', array(), 'Modules.Stthemeeditor.Admin').$desc.'">
                		<i class="icon-chevron-right icon-fw"></i>'.$this->getTranslator()->trans('Import all data', array(), 'Modules.Stthemeeditor.Admin').'
                		</a>
                	</li>
                    <li>
                		<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&predefineddemostore'.$this->name.'='.$k.'&color_only=1&token='.$token.'" 
                        onclick="if (confirm(\''.$this->getTranslator()->trans('Importing colors only from %s%, are your sure?', array('%s%'=>$desc), 'Admin.Theme.Panda').'\')){return true;}else{event.preventDefault();}"
                        title="'.$this->getTranslator()->trans('Colors are set on the Theme editor module from ', array(), 'Modules.Stthemeeditor.Admin').$desc.'">
                		<i class="icon-chevron-right icon-fw"></i>'.$this->getTranslator()->trans('Import colors only', array(), 'Modules.Stthemeeditor.Admin').'
                		</a>
                	</li>
                	</ul>
                </li>';
            } else {
                $html .= '
                <li class="nav-item"><a id="desc-configuration-'.$k.'" class="boolbtn-'.$k.' btn btn-default" onclick="return import_demo(this)" href="'.$btn['href'].'" title="'.$desc.'">
                <span>
                <i class="'.$btn['class'].'"></i> '.$desc.'</span></a></li>';    
            }
        }
        $html .= '</ul>';
        $html .= '<form class="defaultForm form-horizontal" action="'.AdminController::$currentIndex.'&configure='.$this->name.'&upload'.$this->name.'&token='.$token.'" method="post" enctype="multipart/form-data">
            <div class="form-group">
            <label class="control-label col-lg-2">'.$this->getTranslator()->trans('Upload a custom configuration file:', array(), 'Modules.Stthemeeditor.Admin').'</label>
            <div class="col-lg-10">
            <div class="form-group">
            	<div class="col-sm-6">
            		<input id="xml_config_file_field" type="file" name="xml_config_file_field" class="hide">
            		<div class="dummyfile input-group">
            			<span class="input-group-addon"><i class="icon-file"></i></span>
            			<input id="xml_config_file_field-name" type="text" name="filename" readonly="">
            			<span class="input-group-btn">
            				<button id="xml_config_file_field-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
            					<i class="icon-folder-open"></i> '.$this->getTranslator()->trans('Choose a .xml file', array(), 'Modules.Stthemeeditor.Admin').'</button>
            			</span>
            		</div>
                    <button type="submit" value="1" name="uploadconfig" id="uploadconfig" class="btn btn-default" data="'.$this->getTranslator()->trans('Your current module settings will be overridden, are your sure?', array(), 'Modules.Stthemeeditor.Admin').'"><i class="icon icon-upload"></i> '.$this->getTranslator()->trans('Upload and import the file', array(), 'Modules.Stthemeeditor.Admin').'</button>
            	</div>
            </div>
            </div>
            </div>
            </form>
            <div class="alert alert-info"><p>'.$this->getTranslator()->trans('1. IMPORTANT Upload folders in the "One click demo import" folder into the root folder of your Prestashop installation before importing.', array(), 'Modules.Stthemeeditor.Admin').'</p><p>'.$this->getTranslator()->trans('2. Click "Demo x" buttons to import predefined demos. You have two choises, one is importing all data, another is improting colors only.', array(), 'Modules.Stthemeeditor.Admin').'</p><p></p><p>'.$this->getTranslator()->trans('3. "Featured categories slider" module and "Product slider for each category" module can not be expored/imported, because of categories are differnt from sites to sites.', array(), 'Modules.Stthemeeditor.Admin').'</p><p>'.$this->getTranslator()->trans('4. Sample accounts will be appied to these modules "Facebook page plugin", "Twitter Embedded Timelines" and "Instagram block", so you are going to use your account instead after importing.', array(), 'Modules.Stthemeeditor.Admin').'</p></div>';
        $html .= '</div>';
        return $html;
    }
    private function getConfigFieldsValues()
    {
        $fields_values = array();
        foreach($this->defaults as $k=>$v)
        {
            $fields_values[$k] = Configuration::get('STSN_'.strtoupper($k));
            if (isset($v['esc']) && $v['esc'])
                $fields_values[$k] = html_entity_decode($fields_values[$k]);
        }
        
        if (isset($fields_values['custom_css']) && $fields_values['custom_css'])
            $fields_values['custom_css'] = str_replace('¤', '\\', $fields_values['custom_css']); 
        if (isset($fields_values['custom_js']) && $fields_values['custom_js'])
            $fields_values['custom_js'] = str_replace('¤', '\\', $fields_values['custom_js']); 
        
        $languages = Language::getLanguages(false);
        $fields_values['welcome'] = $fields_values['welcome_logged'] = $fields_values['welcome_link'] = $fields_values['copyright_text'] = array(); 
		foreach ($languages as $language)
        {
            $fields_values['welcome'][$language['id_lang']] = Configuration::get('STSN_WELCOME', $language['id_lang']);
            $fields_values['welcome_logged'][$language['id_lang']] = Configuration::get('STSN_WELCOME_LOGGED', $language['id_lang']);
            $fields_values['welcome_link'][$language['id_lang']] = Configuration::get('STSN_WELCOME_LINK', $language['id_lang']);
            $fields_values['copyright_text'][$language['id_lang']] = Configuration::get('STSN_COPYRIGHT_TEXT', $language['id_lang']);
            /*$fields_values['search_label'][$language['id_lang']] = Configuration::get('STSN_SEARCH_LABEL', $language['id_lang']);
            $fields_values['newsletter_label'][$language['id_lang']] = Configuration::get('STSN_NEWSLETTER_LABEL', $language['id_lang']);*/
        }
        
        $font_text_string = Configuration::get('STSN_FONT_TEXT');
        $font_text_string && $font_text_string = explode(":", $font_text_string);
        $fields_values['font_text_list'] = $font_text_string ? $font_text_string[0] : '';
        
        $font_heading_string = Configuration::get('STSN_FONT_HEADING');
        $font_heading_string && $font_heading_string = explode(":", $font_heading_string);
        $fields_values['font_heading_list'] = $font_heading_string ? $font_heading_string[0] : '';

        $pro_name_string = Configuration::get('STSN_PRO_NAME');
        $pro_name_string && $pro_name_string = explode(":", $pro_name_string);
        $fields_values['pro_name_list'] = $pro_name_string ? $pro_name_string[0] : '';

        $font_product_name_string = Configuration::get('STSN_FONT_PRODUCT_NAME');
        $font_product_name_string && $font_product_name_string = explode(":", $font_product_name_string);
        $fields_values['font_product_name_list'] = $font_product_name_string ? $font_product_name_string[0] : '';
        
        $font_price_string = Configuration::get('STSN_FONT_PRICE');
        $font_price_string && $font_price_string = explode(":", $font_price_string);
        $fields_values['font_price_list'] = $font_price_string ? $font_price_string[0] : '';

        $product_reference_code_font_string = Configuration::get('STSN_PRODUCT_REFERENCE_CODE_FONT',null,null,null,$this->_font_inherit);
        $product_reference_code_font_string && $product_reference_code_font_string = explode(":", $product_reference_code_font_string);
        $fields_values['product_reference_code_font_list'] = $product_reference_code_font_string ? $product_reference_code_font_string[0] : '';

        $product_available_font_string = Configuration::get('STSN_PRODUCT_AVAILABLE_FONT',null,null,null,$this->_font_inherit);
        $product_available_font_string && $product_available_font_string = explode(":", $product_available_font_string);
        $fields_values['product_available_font_list'] = $product_available_font_string ? $product_available_font_string[0] : '';

        $font_menu_string = Configuration::get('STSN_FONT_MENU');
        $font_menu_string && $font_menu_string = explode(":", $font_menu_string);
        $fields_values['font_menu_list'] = $font_menu_string ? $font_menu_string[0] : '';
        
        $second_font_menu_string = Configuration::get('STSN_SECOND_FONT_MENU');
        $second_font_menu_string && $second_font_menu_string = explode(":", $second_font_menu_string);
        $fields_values['second_font_menu_list'] = $second_font_menu_string ? $second_font_menu_string[0] : '';
        
        $third_font_menu_string = Configuration::get('STSN_THIRD_FONT_MENU');
        $third_font_menu_string && $third_font_menu_string = explode(":", $third_font_menu_string);
        $fields_values['third_font_menu_list'] = $third_font_menu_string ? $third_font_menu_string[0] : '';
        
        $font_cart_btn_string = Configuration::get('STSN_FONT_CART_BTN');
        $font_cart_btn_string && $font_cart_btn_string = explode(":", $font_cart_btn_string);
        $fields_values['font_cart_btn_list'] = $font_cart_btn_string ? $font_cart_btn_string[0] : '';
        
        $ver_font_menu_string = Configuration::get('STSN_VER_FONT_MENU');
        $ver_font_menu_string && $ver_font_menu_string = explode(":", $ver_font_menu_string);
        $fields_values['ver_font_menu_list'] = $ver_font_menu_string ? $ver_font_menu_string[0] : '';

        foreach($this->_product_ways as $v) {
            $fields_values['product_views_'.$v['id']] = (int)$v['val']&(int)Configuration::get('STSN_PRODUCT_VIEWS');
        }
        foreach($this->_clear_list_view as $v) {
            $fields_values['clear_list_view_'.$v['id']] = (int)$v['val']&(int)Configuration::get('STSN_CLEAR_LIST_VIEW');
        }

        return $fields_values;
    }
    public function BuildDropListGroup($group,$start=1,$end=6,$decimal=false)
    {
        if(!is_array($group) || !count($group))
            return false;

        $html = '<div class="row">';
        foreach($group AS $key => $k)
        {
             if($key==3)
                 $html .= '</div><div class="row">';

             $html .= '<div class="col-xs-4 col-sm-3"><label '.(isset($k['tooltip']) ? ' data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="'.$k['tooltip'].'" ':'').'>'.$k['label'].'</label>'.
             '<select name="'.$k['id'].'" 
             id="'.$k['id'].'" 
             class="'.(isset($k['class']) ? $k['class'] : 'fixed-width-md').'"'.
             (isset($k['onchange']) ? ' onchange="'.$k['onchange'].'"':'').' >';
            
            for ($i=$start; $i <= $end; $i++){
                $html .= '<option value="'.$i.'" '.(Configuration::get('STSN_'.strtoupper($k['id'])) == $i ? ' selected="selected"':'').'>'.$i.'</option>';
            }
            if($decimal)
            {
                $html .= '<option value="1.2" '.(Configuration::get('STSN_'.strtoupper($k['id'])) == 1.2 ? ' selected="selected"':'').'>1.2</option>';
                $html .= '<option value="1.5" '.(Configuration::get('STSN_'.strtoupper($k['id'])) == 1.5 ? ' selected="selected"':'').'>1.5</option>';
                $html .= '<option value="2.4" '.(Configuration::get('STSN_'.strtoupper($k['id'])) == 2.4 ? ' selected="selected"':'').'>2.4</option>';
            }
                                
            $html .= '</select></div>';
        }
        return $html.'</div>';
    }
    public function findCateProPer($k=null)
    {
        $proper = array(
            1 => array(
                array(
                    'id' => 'category_per_fw',
                    'label' => $this->getTranslator()->trans('Full screen', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (Full screen)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'category_per_xxl',
                    'label' => $this->getTranslator()->trans('Width > 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'category_per_xl',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'category_per_lg',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'category_per_md',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'category_per_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (<768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'category_per_xs',
                    'label' => $this->getTranslator()->trans('Extra small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<480px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
            ),
            5 => array(
                array(
                    'id' => 'packitems_pro_per_xl',
                    'label' => $this->getTranslator()->trans('Width > 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'packitems_pro_per_lg',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'packitems_pro_per_md',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'packitems_pro_per_sm',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'packitems_pro_per_xs',
                    'label' => $this->getTranslator()->trans('Small small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'packitems_pro_per_xxs',
                    'label' => $this->getTranslator()->trans('Extra small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (>480px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
            ),
            6 => array(
                array(
                    'id' => 'categories_per_fw',
                    'label' => $this->getTranslator()->trans('Full screen', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (Full screen)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'categories_per_xxl',
                    'label' => $this->getTranslator()->trans('Width > 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'categories_per_xl',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'categories_per_lg',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'categories_per_md',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'categories_per_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (<768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'categories_per_xs',
                    'label' => $this->getTranslator()->trans('Extra small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<480px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
            ),
            7 => array(
                array(
                    'id' => 'cs_per_fw',
                    'label' => $this->getTranslator()->trans('Full screen', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (Full screen)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'cs_per_xxl',
                    'label' => $this->getTranslator()->trans('Width > 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'cs_per_xl',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'cs_per_lg',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'cs_per_md',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'cs_per_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (<768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'cs_per_xs',
                    'label' => $this->getTranslator()->trans('Extra small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<480px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
            ),
            8 => array(
                array(
                    'id' => 'pc_per_fw',
                    'label' => $this->getTranslator()->trans('Full screen', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (Full screen)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pc_per_xxl',
                    'label' => $this->getTranslator()->trans('Width > 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pc_per_xl',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pc_per_lg',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pc_per_md',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pc_per_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (<768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pc_per_xs',
                    'label' => $this->getTranslator()->trans('Extra small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<480px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
            ),
            10 => array(
                array(
                    'id' => 'pro_thumnbs_per_fw',
                    'label' => $this->getTranslator()->trans('Full screen', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (Full screen)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_thumnbs_per_xxl',
                    'label' => $this->getTranslator()->trans('Width > 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_thumnbs_per_xl',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_thumnbs_per_lg',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_thumnbs_per_md',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_thumnbs_per_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_thumnbs_per_xs',
                    'label' => $this->getTranslator()->trans('Extra small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<480px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
            ),
            20 => array(
                array(
                    'id' => 'pro_kk_per_fw',
                    'label' => $this->getTranslator()->trans('Full screen', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (Full screen)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_kk_per_xxl',
                    'label' => $this->getTranslator()->trans('Width > 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_kk_per_xl',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_kk_per_lg',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_kk_per_md',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_kk_per_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'pro_kk_per_xs',
                    'label' => $this->getTranslator()->trans('Extra small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<480px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
            ),
            11 => array(
                array(
                    'id' => 'pro_image_column_md',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                /*array(
                    'id' => 'pro_image_column_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>992px) and (<=992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),*/
            ),
            12 => array(
                array(
                    'id' => 'pro_primary_column_md',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                /*array(
                    'id' => 'pro_primary_column_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>992px) and (<=992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),*/
            ),
            13 => array(
                array(
                    'id' => 'pro_secondary_column_md',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                /*array(
                    'id' => 'pro_secondary_column_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>992px) and (<=992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),*/
            ),
            19 => array(
                array(
                    'id' => 'pro_desc_secondary_column_md',
                    'label' => $this->getTranslator()->trans('Large devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Desktops (>992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                /*array(
                    'id' => 'pro_desc_secondary_column_sm',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>992px) and (<=992px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),*/
            ),
            14 => array(
                array(
                    'id' => 'left_column_size_xl',
                    'label' => $this->getTranslator()->trans('Width > 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'left_column_size_lg',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'left_column_size_md',
                    'label' => $this->getTranslator()->trans('Width > 992px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 992px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'left_column_size_sm',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'left_column_size_xs',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'left_column_size_xxs',
                    'label' => $this->getTranslator()->trans('Extra small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<480px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
            ),
            15 => array(
                array(
                    'id' => 'right_column_size_xl',
                    'label' => $this->getTranslator()->trans('Width > 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1440px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'right_column_size_lg',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'right_column_size_md',
                    'label' => $this->getTranslator()->trans('Width > 992px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 992px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'right_column_size_sm',
                    'label' => $this->getTranslator()->trans('Medium devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Tablets (>768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'right_column_size_xs',
                    'label' => $this->getTranslator()->trans('Small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<768px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'right_column_size_xxs',
                    'label' => $this->getTranslator()->trans('Extra small devices', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Phones (<480px)', array(), 'Modules.Stthemeeditor.Admin'),
                ),
            ),
            16 => array(
                array(
                    'id' => 'stacked_footer_column_1',
                    'label' => $this->getTranslator()->trans('Column 1', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
                array(
                    'id' => 'stacked_footer_column_2',
                    'label' => $this->getTranslator()->trans('Column 2', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
                array(
                    'id' => 'stacked_footer_column_3',
                    'label' => $this->getTranslator()->trans('Column 3', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
                array(
                    'id' => 'stacked_footer_column_4',
                    'label' => $this->getTranslator()->trans('Column 4', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
                array(
                    'id' => 'stacked_footer_column_5',
                    'label' => $this->getTranslator()->trans('Column 5', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
                array(
                    'id' => 'stacked_footer_column_6',
                    'label' => $this->getTranslator()->trans('Column 6', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
            ),
            17 => array(
                array(
                    'id' => 'quarter_1',
                    'label' => $this->getTranslator()->trans('First quarter', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
                array(
                    'id' => 'quarter_2',
                    'label' => $this->getTranslator()->trans('Second quarter', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
                array(
                    'id' => 'quarter_3',
                    'label' => $this->getTranslator()->trans('Third quarter', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
                array(
                    'id' => 'quarter_4',
                    'label' => $this->getTranslator()->trans('Fourth quarter', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => '',
                ),
            ),
            18 => array(
                array(
                    'id' => 'main_menu_spacing_lg',
                    'label' => $this->getTranslator()->trans('Width > 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width larger than 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),
                array(
                    'id' => 'main_menu_spacing_md',
                    'label' => $this->getTranslator()->trans('Width > 992px and <= 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                    'tooltip' => $this->getTranslator()->trans('Screen width between 992px and 1200px', array(), 'Modules.Stthemeeditor.Admin'),
                ),                
            ),
        );
        return ($k!==null && isset($proper[$k])) ? $proper[$k] : $proper;
    }
    
    public function getImageHtml($src, $id)
    {
        $html = '';
        if ($src && $id)
            $html .= '
			<img src="'.$src.'" class="img_preview">
            <p>
                <a data-field="'.$id.'" href="javascript:;" class="btn btn-default st_delete_image"><i class="icon-trash"></i> '.$this->getTranslator()->trans('Delete', array(), 'Modules.Stthemeeditor.Admin').'</a>
			</p>
            ';
        return $html;    
    }
    
    public function get_fontello()
    {
        $res= array(
            'css' => '',
            'theme_name' => '',
            'module_name' => $this->_path,
            'classes' => array(),
        );

        $icons = $icons_css = null;
        if (file_exists(_PS_THEME_DIR_.'font-fontello/config.json')){
            $icons = \json_decode(\Tools::file_get_contents(_PS_THEME_DIR_.'font-fontello/config.json'));
            if( file_exists(_PS_THEME_DIR_.'font-fontello/icons.scss')  )
                $icons_css = Tools::file_get_contents(_PS_THEME_DIR_.'font-fontello/icons.scss');
            $res['theme_name'] = _THEME_NAME_;
        }
        elseif (_PS_PARENT_THEME_DIR_ && file_exists(_PS_PARENT_THEME_DIR_.'font-fontello/config.json')){
            $icons = \json_decode(\Tools::file_get_contents(_PS_PARENT_THEME_DIR_.'font-fontello/config.json'));
            if( file_exists(_PS_PARENT_THEME_DIR_.'font-fontello/icons.scss')  )
                $icons_css = Tools::file_get_contents(_PS_PARENT_THEME_DIR_.'font-fontello/icons.scss');
            $res['theme_name'] = _PARENT_THEME_NAME_;
        }

        if($icons && is_array($icons->glyphs))
            foreach ($icons->glyphs as $icon) {
                $name = 'fto-'.$icon->css;
                if (in_array($icon->css, ['try','export','sync']))
                    $name .= '-icon';
                $res['classes'][$icon->code] = $name;
            }
        if ($icons_css)
            $res['css'] .= $icons_css;

        return $res;
    }
    
    public function export($return = false)
    {
        $result = '';
        $exports = array();
        
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP)
            return $this->displayError($this->getTranslator()->trans('Please select a store to export configurations.', array(), 'Modules.Stthemeeditor.Admin'));
        
        $folder = $this->_config_folder;
        if (!is_dir($folder))
            return $this->displayError('"'.$folder.'" '.$this->getTranslator()->trans('directory isn\'t exists.', array(), 'Modules.Stthemeeditor.Admin'));
        elseif (!is_writable($folder))
            return $this->displayError('"'.$folder.'" '.$this->getTranslator()->trans('directory isn\'t writable.', array(), 'Modules.Stthemeeditor.Admin'));
        
        $file = date('YmdH').'_'.(int)Shop::getContextShopID().'.xml';
        
        foreach($this->defaults AS $k => $value)
            if (is_array($value) && isset($value['exp']) && $value['exp'] == 1)
                $exports[$k] = Configuration::get('STSN_'.strtoupper($k));
        
        $languages = Language::getLanguages(false);
        foreach($this->lang_array AS $value)
            if (key_exists($value, $exports))
                foreach ($languages as $language)
                    $exports[$value][$language['id_lang']] = Configuration::get('STSN_'.strtoupper($value), $language['id_lang']);
        
        $editor = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><!-- Copyright Sunnytoo.com --><stthemeeditor></stthemeeditor>');
        foreach($exports AS $key => $value)
        {
            if (in_array($key, $this->lang_array) && is_array($value))
            {
                $lang_text = $editor->addChild($key);
                foreach($value AS $id_lang => $v)
                    $lang_text->addChild('lang_'.$id_lang, Tools::htmlentitiesUTF8($v));
            }
            else
                $editor->addChild($key, $value);
        }
        
        // Export module settings.
        include_once(dirname(__FILE__).'/classes/DemoStore.php');
        $demo = new DemoStore(array(), $this->context);
        $module_data = $demo->export_modules();
        if ($module_data) {
            $editor->addChild('module_data', base64_encode(serialize($module_data)));
        }
        
        $content = $editor->asXML();
        if ($return) {
            return $content;
        }
        if (!file_put_contents($folder.$file, $content))
            return $this->displayError($this->getTranslator()->trans('Create config file failed.', array(), 'Modules.Stthemeeditor.Admin'));
        else
        {
            $link = '<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&download'.$this->name.'&file='.$file.'">'._MODULE_DIR_.$this->name.'/config/'.$file.'</a>';
            return $this->displayConfirmation($this->getTranslator()->trans('Generate config file successfully, Click the link to download : ', array(), 'Modules.Stthemeeditor.Admin').$link);
        }   
    }
    
    public function add_quick_access()
    {
        if(!Db::getInstance()->getRow('SELECT id_quick_access FROM '._DB_PREFIX_.'quick_access WHERE link LIKE "%configure=stthemeeditor%"') && class_exists('QuickAccess'))
        {
            $quick_access = new QuickAccess();
            //to do the link is wrong
            $quick_access->link = 'index.php?controller=AdminModules&configure=stthemeeditor';
            $quick_access->new_window = 0;
            foreach (Language::getLanguages(false) as $lang)
            {
				$quick_access->name[$lang['id_lang']] = $this->getTranslator()->trans('Theme editor', array(), 'Modules.Stthemeeditor.Admin');
            }
            $quick_access->add();
        }
        return true;
    }
    
    public function clear_class_index()
    {
        $file = _PS_CACHE_DIR_.'class_index.php';
        file_exists($file) && @unlink($file);
        return true;
    }
    public function renderWidget($hookName, array $configuration = [])
    {
        return false;
    }

    public function getWidgetVariables($hookName, array $configuration = [])
    {
        return false;
    }
    public function hookActionProductSearchAfter($params){
        $vals = $this->getStBasicVals();
        $this->context->smarty->assign('sttheme', $vals['smarty_val']);
        return ;
    }
    public function hookActionProductSearchComplete($params){
        $this->hookActionProductSearchAfter($params);
        return ;
    }
    /** 
     * Move product language image to a specify folder when regenerate thumbnails.
     * To be compatible with different size for product langue image.
     */
    public function hookActionOnImageResizeAfter($params)
    {
        $tracing = debug_backtrace();
        if (!isset($tracing[4]['function']) || $tracing[4]['function'] != '_regenerateNoPictureImages') {
            return false;
        }
        if (!isset($params['dst_file']) || !$params['dst_file']) {
            return false;
        }
        $file = $params['dst_file'];
        $name = basename($file);
        $dest = dirname($file).'/s-'.(int)$this->context->shop->id.'/';
        if (!is_dir($dest)) {
            $ret = @mkdir($dest, self::$access_rights, true) || @chmod($dest, self::$access_rights);
            if (!$ret) {
                return false;
            }
        }
        if (file_exists($dest.$name)) {
            @unlink($dest.$name);
        }
        @rename($file, $dest.$name);
    }
    public function hookActionStAssemble(&$product)
    {
        $image = $this->getProductImages($product['id_product'], $product['link_rewrite']);

        if(!is_array($image) || !count($image))
            return false;

        return array(
            'images' => $image,
            );
    }
    public function getProductImages($id_product, $link_rewrite=null, $product_gallerys=false)
    {
        if (isset(self::$cache_product_images[$id_product]))
            return self::$cache_product_images[$id_product];

        self::$cache_product_images[$id_product] = array();
        
        if(!$link_rewrite){
            $rewrite_info = product::getUrlRewriteInformations(Tools::getValue('id_product'));
            foreach ($rewrite_info as $value) {
                if($value['id_lang']==$this->context->language->id)
                    $link_rewrite = $value['link_rewrite'];
            }
        }
        $image_types = ImageType::getImagesTypes('products', true);

        $images = Db::getInstance()->executeS('
            SELECT image_shop.`cover`, i.`id_image`, il.`legend`, i.`position`
            FROM `'._DB_PREFIX_.'image` i
            '.Shop::addSqlAssociation('image', 'i').'
            LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$this->context->language->id.')
            WHERE i.`id_product` = '.(int)$id_product.'
            ORDER BY `position`'
        );
        foreach($images AS $image)
        {
            $urls  = [];
            foreach ($image_types as $image_type) {
                $urls[$image_type['name']] = array(
                    'url' => $this->context->link->getImageLink(
                        $link_rewrite,
                        $image['id_image'],
                        $image_type['name']
                    ),
                    'width'     => (int)$image_type['width'],
                    'height'    => (int)$image_type['height'],
                );
            }

            $image['bySize'] = $urls;
            //if it is needed to get width and height
            self::$cache_product_images[$id_product][] = $image;
        }
        return self::$cache_product_images[$id_product];
    }
    
    public function calcImageWidth($option = array())
    {
        $spacing = 16;
        $page_width = 1200;
        $per_xxl = 5;
        $per_xl = 4;
        $per_lg = 4;
        $per_count = $per_xl;
        $left_width = $right_width = 0;
        $page = 'index';
        if (isset($option['spacing']) && $option['spacing']) {
            $spacing = (int)$option['spacing'];
        }
        if (isset($option['per_xxl']) && (int)$option['per_xxl'] > 1) {
            $per_xxl = (int)$option['per_xxl'];
        }
        if (isset($option['per_xl']) && (int)$option['per_xl'] > 1) {
            $per_xl = (int)$option['per_xl'];
        }
        if (isset($option['per_lg']) && (int)$option['per_lg'] > 1) {
            $per_lg = (int)$option['per_lg'];
        }
        if (isset($option['page']) && $option['page']) {
            $page = $option['page'];
        }
        switch($this->getResponsiveMax())
        {
            case 0:
                $page_width = 980;
                $per_count = $per_lg;
                $left_width = (int)Configuration::get('STSN_LEFT_COLUMN_SIZE_MD');
                $right_width = (int)Configuration::get('STSN_RIGHT_COLUMN_SIZE_MD');
                break;
            case 1:
                $page_width = 1200;
                $per_count = $per_xl;
                $left_width = (int)Configuration::get('STSN_LEFT_COLUMN_SIZE_LG');
                $right_width = (int)Configuration::get('STSN_RIGHT_COLUMN_SIZE_LG');
                break;
            case 2:
                $page_width = 1440;
                $per_count = $per_xxl;
                $left_width = (int)Configuration::get('STSN_LEFT_COLUMN_SIZE_XL');
                $right_width = (int)Configuration::get('STSN_RIGHT_COLUMN_SIZE_XL');
                break;
            default:
                break;
        }
        
        $theme_repository = (new ThemeManagerBuilder($this->context, Db::getInstance()))->buildRepository();
        $theme = $theme_repository->getInstanceByName($this->context->shop->theme->getName());
        $colum_width = 0;
        if ($theme->get('theme_settings.layouts.'.$page) == 'layout-left-column') {
            $colum_width = round($page_width * $left_width / 12, 2);
        } elseif ($theme->get('theme_settings.layouts.'.$page) == 'layout-right-column') {
            $colum_width = round($page_width * $right_width / 12, 2);
        } elseif ($theme->get('theme_settings.layouts.'.$page) == 'layout-both-columns') {
            $colum_width = round($page_width * $left_width / 12, 2) + round($page_width * $right_width / 12, 2);
        }
        
        if (isset($option['for_product_gallery']) && $option['for_product_gallery']) {
            $per_width = floor(($page_width - $colum_width)*$per_count/12);
            $thumb_width = (int)Configuration::get('STSN_PRODUCT_THUMBNAILS');
            if ($thumb_width == 1 || $thumb_width == 2 || $thumb_width == 7) {
                $thumb_width = Configuration::get('STSN_GALLERY_THUMBNAILS_WIDTH_VPX');
                $per_width = $per_width - $thumb_width;
            }
            $per_width = $per_width - 30 - 16;
        } else {
            $per_width = floor(($page_width - 2 * 15 - ($per_count - 1) * $spacing - $colum_width)/$per_count);    
        }
        return $this->getTranslator()->trans('Save your changes first. Recommended width for the current image type is %s% px', array('%s%'=>'<strong>'.$per_width.'</strong>'), 'Admin.Theme.Panda');
    }
    public function fetchMediaServer(&$image)
    {
        $image = _THEME_PROD_PIC_DIR_.$image;
        $image = context::getContext()->link->protocol_content.Tools::getMediaServer($image).$image;
    }
    public function getAjaxProductsList()
    {
        $query = Tools::getValue('q', false);
        if (!$query || $query == '' || strlen($query) < 1) {
            die();
        }
        if ($pos = strpos($query, ' (ref:')) {
            $query = substr($query, 0, $pos);
        }

        $excludeIds = Tools::getValue('excludeIds', false);
        if ($excludeIds && $excludeIds != 'NaN') {
            $excludeIds = implode(',', array_map('intval', explode(',', $excludeIds)));
        } else {
            $excludeIds = '';
        }

        // Excluding downloadable products from packs because download from pack is not supported
        $forceJson = Tools::getValue('forceJson', false);
        $disableCombination = Tools::getValue('disableCombination', false);
        $excludeVirtuals = (bool)Tools::getValue('excludeVirtuals', true);
        $exclude_packs = (bool)Tools::getValue('exclude_packs', true);

        $context = Context::getContext();

        $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, image_shop.`id_image` id_image, il.`legend`, p.`cache_default_attribute`
                FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = '.(int)$context->language->id.Shop::addSqlRestrictionOnLang('pl').')
                LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                    ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id.')
                LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$context->language->id.')
                WHERE (pl.name LIKE \'%'.pSQL($query).'%\' OR p.reference LIKE \'%'.pSQL($query).'%\')'.
                (!empty($excludeIds) ? ' AND p.id_product NOT IN ('.$excludeIds.') ' : ' ').
                ($excludeVirtuals ? 'AND NOT EXISTS (SELECT 1 FROM `'._DB_PREFIX_.'product_download` pd WHERE (pd.id_product = p.id_product))' : '').
                ($exclude_packs ? 'AND (p.cache_is_pack IS NULL OR p.cache_is_pack = 0)' : '').
                ' GROUP BY p.id_product';

        $items = Db::getInstance()->executeS($sql);

        if ($items && ($disableCombination ||$excludeIds)) {
            $results = [];
            foreach ($items as $item) {
                if (!$forceJson) {
                    $item['name'] = str_replace('|', '&#124;', $item['name']);
                    $results[] = trim($item['name']).(!empty($item['reference']) ? ' (ref: '.$item['reference'].')' : '').'|'.(int)($item['id_product']);
                } else {
                    $results[] = array(
                        'id' => $item['id_product'],
                        'name' => $item['name'].(!empty($item['reference']) ? ' (ref: '.$item['reference'].')' : ''),
                        'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                        'image' => str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], 'home_default')),
                    );
                }
            }

            if (!$forceJson) {
                echo implode("\n", $results);
            } else {
                echo json_encode($results);
            }
        } elseif ($items) {
            // packs
            $results = array();
            foreach ($items as $item) {
                // check if product have combination
                if (Combination::isFeatureActive() && $item['cache_default_attribute']) {
                    $sql = 'SELECT pa.`id_product_attribute`, pa.`reference`, ag.`id_attribute_group`, pai.`id_image`, agl.`name` AS group_name, al.`name` AS attribute_name,
                                a.`id_attribute`
                            FROM `'._DB_PREFIX_.'product_attribute` pa
                            '.Shop::addSqlAssociation('product_attribute', 'pa').'
                            LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
                            LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                            LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                            LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$context->language->id.')
                            LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)$context->language->id.')
                            LEFT JOIN `'._DB_PREFIX_.'product_attribute_image` pai ON pai.`id_product_attribute` = pa.`id_product_attribute`
                            WHERE pa.`id_product` = '.(int)$item['id_product'].'
                            GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
                            ORDER BY pa.`id_product_attribute`';

                    $combinations = Db::getInstance()->executeS($sql);
                    if (!empty($combinations)) {
                        foreach ($combinations as $k => $combination) {
                            $results[$combination['id_product_attribute']]['id'] = $item['id_product'];
                            $results[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                            !empty($results[$combination['id_product_attribute']]['name']) ? $results[$combination['id_product_attribute']]['name'] .= ' '.$combination['group_name'].'-'.$combination['attribute_name']
                            : $results[$combination['id_product_attribute']]['name'] = $item['name'].' '.$combination['group_name'].'-'.$combination['attribute_name'];
                            if (!empty($combination['reference'])) {
                                $results[$combination['id_product_attribute']]['ref'] = $combination['reference'];
                            } else {
                                $results[$combination['id_product_attribute']]['ref'] = !empty($item['reference']) ? $item['reference'] : '';
                            }
                            if (empty($results[$combination['id_product_attribute']]['image'])) {
                                $results[$combination['id_product_attribute']]['image'] = str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $combination['id_image'], 'home_default'));
                            }
                        }
                    } else {
                        $results[] = array(
                            'id' => $item['id_product'],
                            'name' => $item['name'],
                            'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                            'image' => str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], 'home_default')),
                        );
                    }
                } else {
                    $results[] = array(
                        'id' => $item['id_product'],
                        'name' => $item['name'],
                        'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                        'image' => str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], 'home_default')),
                    );
                }
            }
            echo json_encode(array_values($results));
        } else {
            echo json_encode([]);
        }
        die;
    }
    
    public function getFavicon($icon,$key){
        if(!$icon || $icon=='')
            return '';
        $icons='';
        if($icon != $this->defaults[$key]['val'] && strpos($icon, 'img/') !== 0){
            if((strpos($icon, '/img/cms/') === 0 || strpos($icon, '/upload/stthemeeditor/') === 0)){
                $icons=rtrim(__PS_BASE_URI__, '/').$icon;
            }else{
                $icons=_THEME_PROD_PIC_DIR_.$icon;
            }
        }else{
            if(strpos($icon, '/modules/stthemeeditor/img/') === 0){
                $icons=$icon;
            }else{
                $icons=$this->_path.$icon;
            }
            
        }
        return $icons;
    }

}