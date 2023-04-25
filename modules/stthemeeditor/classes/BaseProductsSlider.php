<?php
require_once(dirname(__FILE__).'/BaseSlider.php');
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
class BaseProductsSlider extends BaseSlider implements WidgetInterface
{
    public $title;
    public $url_entity;
	function __construct()
	{
        parent::__construct();
    }
    protected function initTabNames()
    {
        $this->_tabs = array(
            array('id'  => '0,4', 'name' => $this->getTranslator()->trans('General settings', array(), 'Admin.Theme.Panda')),
            array('id'  => '1,5', 'name' => $this->getTranslator()->trans('Homepage', array(), 'Admin.Theme.Panda')),
            array('id'  => '2', 'name' => $this->getTranslator()->trans('Left or right column', array(), 'Admin.Theme.Panda')),
            array('id'  => '3', 'name' => $this->getTranslator()->trans('Footer', array(), 'Admin.Theme.Panda')),
        );
    }
    protected function initHookArray()
    {
        $this->_hooks = array(
            'Hooks' => array(
                array(
                    'id' => 'displayFullWidthTop',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayFullWidthTop', array(), 'Admin.Theme.Panda'),
                    'is_full_width' => 1,
                ),
                array(
                    'id' => 'displayFullWidthTop2',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayFullWidthTop2', array(), 'Admin.Theme.Panda'),
                    'is_full_width' => 1,
                ),
                array(
                    'id' => 'displayHomeTop',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHomeTop', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayHome',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHome', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayHomeLeft',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHomeLeft', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayHomeRight',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHomeRight', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayHomeFirstQuarter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHomeFirstQuarter', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayHomeSecondQuarter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHomeSecondQuarter', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayHomeThirdQuarter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHomeThirdQuarter', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayHomeFourthQuarter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHomeFourthQuarter', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayHomeBottom',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHomeBottom', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayFullWidthBottom',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayFullWidthBottom', array(), 'Admin.Theme.Panda'),
                    'is_full_width' => 1,
                ),
                array(
                    'id' => 'displayFooterBefore',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayFullWidthBottom2(Footer before)', array(), 'Admin.Theme.Panda'),
                    'is_full_width' => 1,
                ),
                array(
                    'id' => 'displayOrderConfirmation2',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayOrderConfirmation2', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayFooterProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayFooterProduct', array(), 'Admin.Theme.Panda')
                ),
                array(
                    'id' => 'displayOrderConfirmation1',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayOrderConfirmation1', array(), 'Admin.Theme.Panda')
                ),
                array(
                    'id' => 'displayMiddleProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayMiddleProduct', array(), 'Admin.Theme.Panda')
                ),
                array(
                    'id' => 'displayProductDescRightColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Right side of Product description', array(), 'Admin.Theme.Panda'),
                    'is_column'=>1,
                ),
                array(
                    'id' => 'displayCrossSellingShoppingCart',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Empty shopping cart', array(), 'Admin.Theme.Panda'),
                ),
            ),
            'Column' => array(
                array(
                    'id' => 'displayLeftColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Left column except the produt page', array(), 'Admin.Theme.Panda'),
                    'is_column'=>1,
                ),
                array(
                    'id' => 'displayRightColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Right column except the produt page', array(), 'Admin.Theme.Panda'),
                    'is_column'=>1,
                ),
                array(
                    'id' => 'displayLeftColumnProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Left column on the product page only', array(), 'Admin.Theme.Panda'),
                    'is_column'=>1,
                ),
                array(
                    'id' => 'displayRightColumnProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Right column on the product page only', array(), 'Admin.Theme.Panda'),
                    'is_column'=>1,
                ),
                array(
                    'id' => 'displayStBlogLeftColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Blog left column', array(), 'Admin.Theme.Panda'),
                    'is_column'=>1,
                    'is_blog'=>1,
                ),
                array(
                    'id' => 'displayStBlogRightColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Blog right column', array(), 'Admin.Theme.Panda'),
                    'is_column'=>1,
                    'is_blog'=>1,
                )
            ),
            'Footer' => array(
                array(
                    'id' => 'displayStackedFooter1',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Stacked footer 1', array(), 'Admin.Theme.Panda'),
                    'is_stacked_footer'=>1,
                    'is_footer'=>1,
                ),
                array(
                    'id' => 'displayStackedFooter2',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Stacked footer 2', array(), 'Admin.Theme.Panda'),
                    'is_stacked_footer'=>1,
                    'is_footer'=>1,
                ),
                array(
                    'id' => 'displayStackedFooter3',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Stacked footer 3', array(), 'Admin.Theme.Panda'),
                    'is_stacked_footer'=>1,
                    'is_footer'=>1,
                ),
                array(
                    'id' => 'displayStackedFooter4',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Stacked footer 4', array(), 'Admin.Theme.Panda'),
                    'is_stacked_footer'=>1,
                    'is_footer'=>1,
                ),
                array(
                    'id' => 'displayStackedFooter5',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Stacked footer 5', array(), 'Admin.Theme.Panda'),
                    'is_stacked_footer'=>1,
                    'is_footer'=>1,
                ),
                array(
                    'id' => 'displayStackedFooter6',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Stacked footer 6', array(), 'Admin.Theme.Panda'),
                    'is_stacked_footer'=>1,
                    'is_footer'=>1,
                ),        
                array(
                    'id' => 'displayFooter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayFooter', array(), 'Admin.Theme.Panda'),
                    'is_footer'=>1,
                ),
                array(
                    'id' => 'displayFooterAfter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayFooterAfter', array(), 'Admin.Theme.Panda'),
                    'is_footer'=>1,
                ),
                array(
                    'id' => 'displayCheckoutBottom',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayCheckoutBottom', array(), 'Admin.Theme.Panda'),
                    'is_footer'=>1,
                )
            )
        );
    }
	function install()
	{
        return parent::install()
    		&& $this->registerHook('addproduct')
    		&& $this->registerHook('updateproduct')
    		&& $this->registerHook('deleteproduct')
            && $this->registerHook('categoryUpdate')
            && $this->registerHook('vcBeforeInit')// For VC module
            && Configuration::updateValue($this->_prefix_st.'COUNTDOWN_ON', 1)
            && Configuration::updateValue($this->_prefix_st.'COUNTDOWN_ON_COL', 1);
	}
    public function initFieldsForm()
    {
        $fields = $this->getFormFields();
        $fields['setting']['out_of_stock_on']=array(
            'type' => 'radio',
            'label' => $this->getTranslator()->trans('Don\'t show out-of-stock products', array(), 'Admin.Theme.Panda'),
            'name' => 'out_of_stock_on',
            'default_value' => 0,
            'values' => array(
                array(
                    'id' => 'out_of_stock_on_0',
                    'value' => 0,
                    'label' => $this->getTranslator()->trans('Show all products.', array(), 'Admin.Theme.Panda')),
                array(
                    'id' => 'out_of_stock_on_1',
                    'value' => 1,
                    'label' => $this->getTranslator()->trans('Hide out-of-stock products.', array(), 'Admin.Theme.Panda')),
                array(
                    'id' => 'out_of_stock_on_2',
                    'value' => 2,
                    'label' => $this->getTranslator()->trans('Hide products if their default combinations are out-of-stock.', array(), 'Admin.Theme.Panda')),
            ),
            'validation' => 'isUnsignedInt',
        );
        $fields['setting']['title_io'] = array(
                'type' => 'radio',
                'label' => $this->getTranslator()->trans('How to display block title when this block has custom content on its left or right side:', array(), 'Admin.Theme.Panda'),
                'name' => 'title_io',
                'default_value' => 0,
                'values' => array(
                    array(
                        'id' => 'title_io_inner',
                        'value' => 0,
                        'label' => $this->getTranslator()->trans('Above products.', array(), 'Admin.Theme.Panda')),
                    array(
                        'id' => 'title_io_outter',
                        'value' => 1,
                        'label' => $this->getTranslator()->trans('Above this whole block.', array(), 'Admin.Theme.Panda')),
                ),
                'validation' => 'isUnsignedInt',
                'desc' => $this->getTranslator()->trans('The Advanced custom content module can put custom content to this block. You can use this setting to decide where the title shoud be displayed.', array(), 'Admin.Theme.Panda'),
            );
        $fields['setting']['title_bottom_margin'] = array(
                'type' => 'text',
                'label' => $this->getTranslator()->trans('Title bottom margin:', array(), 'Admin.Theme.Panda'),
                'name' => 'title_bottom_margin',
                'validation' => 'isNullOrUnsignedId',
                'prefix' => 'px',
                'class' => 'fixed-width-lg',
                'desc' => $this->getTranslator()->trans('Set a small vaule to this field when you want to add a sub header to this block using the Advanced custom content module.', array(), 'Admin.Theme.Panda'),
            );
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Settings', array(), 'Admin.Theme.Panda'),
                'icon'  => 'icon-cogs'
            ),
            'input' => $fields['setting'],
            'submit' => array(
				'title' => $this->getTranslator()->trans('Save all', array(), 'Admin.Theme.Panda'),
			),
        );
        
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Slider on homepage', array(), 'Admin.Theme.Panda'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                'countdown_on' => array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Display countdown timers:', array(), 'Admin.Theme.Panda'),
                    'name' => 'countdown_on',
                    'is_bool' => true,
                    'default_value' => 1,
                    'desc' => $this->getTranslator()->trans('Make sure the Coundown module is installed & enabled.', array(), 'Admin.Theme.Panda'),
                    'values' => array(
                        array(
                            'id' => 'countdown_on_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'countdown_on_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'validation' => 'isBool',
                ),
			),
			'submit' => array(
				'title' => $this->getTranslator()->trans('Save all', array(), 'Admin.Theme.Panda'),
			),
		);
        $option = array(
            'spacing' => (int)Configuration::get($this->_prefix_st.'SPACING_BETWEEN'),
            'per_lg'  => (int)Configuration::get($this->_prefix_stsn.'PRO_PER_LG'),
            'per_xl'  => (int)Configuration::get($this->_prefix_stsn.'PRO_PER_XL'),
            'per_xxl' => (int)Configuration::get($this->_prefix_stsn.'PRO_PER_XXL'),
            'page'    => 'index',
        );
        $fields['home_slider']['image_type']['desc'] = $this->calcImageWidth($option);
        $this->fields_form[1]['form']['input'] = $fields['home_slider']+$this->fields_form[1]['form']['input'];
        
        $this->fields_form[2]['form'] = array(
			'legend' => array(
				'title' => $this->getTranslator()->trans('Slider on left column/right column/X quarters', array(), 'Admin.Theme.Panda'),
                'icon'  => 'icon-cogs'
			),
			'input' => array(
                array(
					'type' => 'hidden',
					'name' => 'move_col',
                    'default_value' => 1,
                    'validation' => 'isBool',
				),
                'countdown_on_col' => array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Display countdown timers:', array(), 'Admin.Theme.Panda'),
                    'name' => 'countdown_on_col',
                    'is_bool' => true,
                    'default_value' => 1,
                    'desc' => $this->getTranslator()->trans('Make sure the Coundown module is installed & enabled.', array(), 'Admin.Theme.Panda'),
                    'values' => array(
                        array(
                            'id' => 'countdown_on_col_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'countdown_on_col_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'validation' => 'isBool',
                ),
			),
			'submit' => array(
				'title' => $this->getTranslator()->trans('Save all', array(), 'Admin.Theme.Panda')
			),
		);
        $this->fields_form[2]['form']['input'] = $fields['column']+$this->fields_form[2]['form']['input'];
        
        $this->fields_form[3]['form'] = array(
			'legend' => array(
				'title' => $this->getTranslator()->trans('Footer', array(), 'Admin.Theme.Panda'),
                'icon' => 'icon-cogs'
			),
			'input' => $fields['footer'],
			'submit' => array(
				'title' => $this->getTranslator()->trans('Save all', array(), 'Admin.Theme.Panda')
			),
		);
        
        $this->fields_form[4]['form'] = array(
			'legend' => array(
				'title' => $this->getTranslator()->trans('Hook manager', array(), 'Admin.Theme.Panda'),
                'icon' => 'icon-cogs'
			),
            'description' => $this->getTranslator()->trans('Check the hook that you would like this module to display on.', array(), 'Admin.Theme.Panda').
            '<br/><a href="'._MODULE_DIR_.'stthemeeditor/img/hook_into_hint.jpg" target="_blank" >'.$this->getTranslator()->trans('Click here to see hook position', array(), 'Admin.Theme.Panda').'</a>.',
			'input' => $fields['hook'],
			'submit' => array(
				'title' => $this->getTranslator()->trans('Save all', array(), 'Admin.Theme.Panda')
			),
		); 
    }
    public function hookDisplayHeader($params)
    {
        $template = 'module:stthemeeditor/views/templates/slider/header.tpl';
        if (!$this->isCached($template, $this->stGetCacheId('header')))
        {
            $classname = $this->name.'_container';
            
            $custom_css = '';
    
            $spacing_between = Configuration::get($this->_prefix_st.'SPACING_BETWEEN');
            if(Configuration::get($this->_prefix_st.'GRID')==1 && ($spacing_between || $spacing_between===0 || $spacing_between==='0'))
            {
                $custom_css .= '.'.$classname.' .product_list.grid .product_list_item{padding-left:'.ceil($spacing_between/2).'px;padding-right:'.floor($spacing_between/2).'px;}';
                $custom_css .= '.'.$classname.' .product_list.grid{margin-left:-'.ceil($spacing_between/2).'px;margin-right:-'.floor($spacing_between/2).'px;}';
            }
            
            $group_css = '';
            if ($bg_color = Configuration::get($this->_prefix_st.'BG_COLOR'))
                $group_css .= 'background-color:'.$bg_color.';';
            if ($bg_img = Configuration::get($this->_prefix_st.'BG_IMG'))
            {
                $this->fetchMediaServer($bg_img);
                $group_css .= 'background-image: url('.$bg_img.');';
            }
            elseif ($bg_pattern = Configuration::get($this->_prefix_st.'BG_PATTERN'))
            {
                $img = _MODULE_DIR_.'stthemeeditor/patterns/'.$bg_pattern.'.png';
                $img = $this->context->link->protocol_content.Tools::getMediaServer($img).$img;
                $group_css .= 'background-image: url('.$img.');background-repeat: repeat;';
            }
            if($group_css)
                $custom_css .= '.'.$classname.'.products_container{'.$group_css.'}';
            /*if ($bg_img_v_offset = (int)Configuration::get($this->_prefix_st.'BG_IMG_V_OFFSET')) {
                $custom_css .= '.'.$classname.'.products_container{background-position:center -'.$bg_img_v_offset.'px;}';
            }*/
    
            if ($top_padding = (int)Configuration::get($this->_prefix_st.'TOP_PADDING'))
                $custom_css .= '.'.$classname.'.products_container  .products_slider{padding-top:'.$top_padding.'px;}';
            if ($bottom_padding = (int)Configuration::get($this->_prefix_st.'BOTTOM_PADDING'))
                $custom_css .= '.'.$classname.'.products_container  .products_slider{padding-bottom:'.$bottom_padding.'px;}';
    
            $top_margin = Configuration::get($this->_prefix_st.'TOP_MARGIN');
            if($top_margin || $top_margin===0 || $top_margin==='0')
                $custom_css .= '.'.$classname.'.products_container{margin-top:'.$top_margin.'px;}';
            $bottom_margin = Configuration::get($this->_prefix_st.'BOTTOM_MARGIN');
            if($bottom_margin || $bottom_margin===0 || $bottom_margin==='0')
                $custom_css .= '.'.$classname.'.products_container{margin-bottom:'.$bottom_margin.'px;}';
    
            if ($title_font_size = (int)Configuration::get($this->_prefix_st.'TITLE_FONT_SIZE'))
                 $custom_css .= '.'.$classname.'.products_container .title_block_inner{font-size:'.$title_font_size.'px;}';
    
            if ($title_color = Configuration::get($this->_prefix_st.'TITLE_COLOR'))
                $custom_css .= '.'.$classname.'.products_container .title_block_inner{color:'.$title_color.';}';
            if ($title_hover_color = Configuration::get($this->_prefix_st.'TITLE_HOVER_COLOR'))
                $custom_css .= '.'.$classname.'.products_container .title_block_inner:hover{color:'.$title_hover_color.';}';
        
    
            $heading_bottom_border = Configuration::get($this->_prefix_st.'TITLE_BOTTOM_BORDER');
            if($heading_bottom_border || $heading_bottom_border===0 || $heading_bottom_border==='0')
            {
                $custom_css .= '.'.$classname.'.products_container .title_style_0,.'.$classname.'.products_container .title_style_0 .title_block_inner{border-bottom-width:'.$heading_bottom_border.'px;}.'.$classname.'.products_container .title_style_0 .title_block_inner{margin-bottom:-'.$heading_bottom_border.'px;}';
                $custom_css .= '.'.$classname.'.products_container .title_style_1 .flex_child, .'.$classname.'.products_container .title_style_3 .flex_child{border-bottom-width:'.$heading_bottom_border.'px;}';
                $custom_css .= '.'.$classname.'.products_container .title_style_2 .flex_child{border-bottom-width:'.$heading_bottom_border.'px;border-top-width:'.$heading_bottom_border.'px;}';
            }
            
            if(Configuration::get($this->_prefix_st.'TITLE_BOTTOM_BORDER_COLOR'))
                $custom_css .='.'.$classname.'.products_container .title_style_0, .'.$classname.'.products_container .title_style_1 .flex_child, .'.$classname.'.products_container .title_style_2 .flex_child, .'.$classname.'.products_container .title_style_3 .flex_child{border-bottom-color: '.Configuration::get($this->_prefix_st.'TITLE_BOTTOM_BORDER_COLOR').';}.'.$classname.'.products_container .title_style_2 .flex_child{border-top-color: '.Configuration::get($this->_prefix_st.'TITLE_BOTTOM_BORDER_COLOR').';}';  
            if(Configuration::get($this->_prefix_st.'TITLE_BOTTOM_BORDER_COLOR_H'))
                $custom_css .='.'.$classname.'.products_container .title_style_0 .title_block_inner{border-color: '.Configuration::get($this->_prefix_st.'TITLE_BOTTOM_BORDER_COLOR_H').';}';  
            
            $title_bottom_margin = Configuration::get($this->_prefix_st.'TITLE_BOTTOM_MARGIN');
            if($title_bottom_margin || $title_bottom_margin===0 || $title_bottom_margin==='0')
                $custom_css .= '.'.$classname.'.products_container .title_block{margin-bottom:'.$title_bottom_margin.'px;}';
    
            if ($text_color = Configuration::get($this->_prefix_st.'TEXT_COLOR'))
                $custom_css .= '.'.$classname.' .ajax_block_product .s_title_block a,
                .'.$classname.' .ajax_block_product .old_price,
                .'.$classname.' .ajax_block_product .product_desc{color:'.$text_color.';}';
    
            if ($price_color = Configuration::get($this->_prefix_st.'PRICE_COLOR'))
                $custom_css .= '.'.$classname.' .ajax_block_product .price{color:'.$price_color.';}';
            if ($link_hover_color = Configuration::get($this->_prefix_st.'LINK_HOVER_COLOR'))
                $custom_css .= '.'.$classname.' .ajax_block_product .s_title_block a:hover{color:'.$link_hover_color.';}';
    
            if ($grid_bg = Configuration::get($this->_prefix_st.'GRID_BG'))
                $custom_css .= '.'.$classname.' .pro_outer_box .pro_second_box{background-color:'.$grid_bg.';}';
            if ($grid_hover_bg = Configuration::get($this->_prefix_st.'GRID_HOVER_BG'))
                $custom_css .= '.'.$classname.' .pro_outer_box:hover .pro_second_box{background-color:'.$grid_hover_bg.';}';
    
            //.block is used to make bg take effect, .swiper-button-lr, .swiper-button-tr hui fu gai
            //.swiper-button-tr only here. For product sliders' title is above.
            if ($direction_color = Configuration::get($this->_prefix_st.'DIRECTION_COLOR'))
                $custom_css .= '.'.$classname.'.block .products_slider .swiper-button, .'.$classname.'.block .swiper-button-tr .swiper-button{color:'.$direction_color.';}';
            if ($direction_color_hover = Configuration::get($this->_prefix_st.'DIRECTION_COLOR_HOVER'))
                $custom_css .= '.'.$classname.'.block .products_slider .swiper-button:hover, .'.$classname.'.block .swiper-button-tr .swiper-button:hover{color:'.$direction_color_hover.';}';
            if ($direction_color_disabled = Configuration::get($this->_prefix_st.'DIRECTION_COLOR_DISABLED'))
                $custom_css .= '.'.$classname.'.block .products_slider .swiper-button.swiper-button-disabled, .'.$classname.' .products_slider .swiper-button.swiper-button-disabled:hover, .'.$classname.'.block .swiper-button-tr .swiper-button.swiper-button-disabled, .'.$classname.'.block .swiper-button-tr .swiper-button.swiper-button-disabled:hover{color:'.$direction_color_disabled.';}';
            
            if ($direction_bg = Configuration::get($this->_prefix_st.'DIRECTION_BG'))
                $custom_css .= '.'.$classname.'.block .products_slider .swiper-button, .'.$classname.'.block .swiper-button-tr .swiper-button{background-color:'.$direction_bg.';}';
            if ($direction_hover_bg = Configuration::get($this->_prefix_st.'DIRECTION_HOVER_BG'))
                $custom_css .= '.'.$classname.'.block .products_slider .swiper-button:hover, .'.$classname.'.block .swiper-button-tr .swiper-button:hover{background-color:'.$direction_hover_bg.';}';
            if ($direction_disabled_bg = Configuration::get($this->_prefix_st.'DIRECTION_DISABLED_BG'))
                $custom_css .= '.'.$classname.'.block .products_slider .swiper-button.swiper-button-disabled, .'.$classname.'.block .products_slider .swiper-button.swiper-button-disabled:hover, .'.$classname.'.block .swiper-button-tr .swiper-button.swiper-button-disabled, .'.$classname.'.block .swiper-button-tr .swiper-button.swiper-button-disabled:hover{background-color:'.$direction_disabled_bg.';}';
            /*else
                $custom_css .= '.'.$classname.' .products_slider .swiper-button.swiper-button-disabled, .'.$classname.' .products_slider .swiper-button.swiper-button-disabled:hover, .'.$classname.'.block .swiper-button-tr .swiper-button.swiper-button-disabled, .'.$classname.'.block .swiper-button-tr .swiper-button.swiper-button-disabled:hover{background-color:transparent;}';*/
    
            if ($pag_nav_bg = Configuration::get($this->_prefix_st.'PAG_NAV_BG')){
                $custom_css .= '.'.$classname.' .swiper-pagination-bullet,.'.$classname.' .swiper-pagination-progress{background-color:'.$pag_nav_bg.';}';
                $custom_css .= '.'.$classname.' .swiper-pagination-st-round .swiper-pagination-bullet{background-color:transparent;border-color:'.$pag_nav_bg.';}';
                $custom_css .= '.'.$classname.' .swiper-pagination-st-round .swiper-pagination-bullet span{background-color:'.$pag_nav_bg.';}';
            }
            if ($pag_nav_bg_hover = Configuration::get($this->_prefix_st.'PAG_NAV_BG_HOVER')){
                $custom_css .= '.'.$classname.' .swiper-pagination-bullet-active, .'.$classname.' .swiper-pagination-progress .swiper-pagination-progressbar{background-color:'.$pag_nav_bg_hover.';}';
                $custom_css .= '.'.$classname.' .swiper-pagination-st-round .swiper-pagination-bullet.swiper-pagination-bullet-active{background-color:'.$pag_nav_bg_hover.';border-color:'.$pag_nav_bg_hover.';}';
                $custom_css .= '.'.$classname.' .swiper-pagination-st-round .swiper-pagination-bullet.swiper-pagination-bullet-active span{background-color:'.$pag_nav_bg_hover.';}';
            }
            
            if($custom_css)
                $this->smarty->assign('custom_css', preg_replace('/\s\s+/', ' ', $custom_css));
        }
        return $this->fetch($template, $this->stGetCacheId('header'));
    }
    public function _prepareHook($ext='')
    {
        $ext = $ext ? '_'.strtoupper($ext) : '';
        
        $slideshow      = Configuration::get($this->_prefix_st.'SLIDESHOW'.$ext);
        $s_speed        = Configuration::get($this->_prefix_st.'S_SPEED'.$ext);
        $a_speed        = Configuration::get($this->_prefix_st.'A_SPEED'.$ext);
        $pause_on_hover = Configuration::get($this->_prefix_st.'PAUSE_ON_HOVER'.$ext);
        $pause_on_enter = Configuration::get($this->_prefix_st.'PAUSE_ON_ENTER'.$ext);
        $reverse_direction = Configuration::get($this->_prefix_st.'REVERSE_DIRECTION'.$ext);
        $rewind_nav     = Configuration::get($this->_prefix_st.'REWIND_NAV'.$ext);
        $lazy_load      = Configuration::get($this->_prefix_st.'LAZY'.$ext);
        $move           = Configuration::get($this->_prefix_st.'MOVE'.$ext);
        $items          = Configuration::get($this->_prefix_st.'ITEMS_COL');
        $hide_mob       = Configuration::get($this->_prefix_st.'HIDE_MOB'.$ext);
        $aw_display     = Configuration::get($this->_prefix_st.'AW_DISPLAY'.$ext);
        $display_sd     = Configuration::get($this->_prefix_st.'DISPLAY_SD');
       

        $tmpProduct=$this->getProducts($ext);

        if($rewind_nav && (int)Configuration::get($this->_prefix_st.'PRO_PER_FW')>count($tmpProduct)){
            $rewind_nav=false;
        }
        $this->smarty->assign(array(
            'products'              => $tmpProduct,
            'add_prod_display'      => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
            'slider_slideshow'      => $slideshow,
            'slider_s_speed'        => $s_speed,
            'slider_a_speed'        => $a_speed,
            'slider_pause_on_hover' => $pause_on_hover,
            'slider_pause_on_enter' => $pause_on_enter,
            'slider_reverse_direction' => $reverse_direction,
            'rewind_nav'            => $rewind_nav,
            'lazy_load'             => $lazy_load,
            'slider_move'           => $move,
            'slider_items'          => $items ? $items : 4,
            'hide_mob'              => (int)$hide_mob,
            'display_sd'            => (int)$display_sd,
            'aw_display'            => (int)$aw_display,
            'display_as_grid'       => Configuration::get($this->_prefix_st.'GRID'),
            'title_position'        => Configuration::get($this->_prefix_st.'TITLE_ALIGN'),
            'title_io'              => Configuration::get($this->_prefix_st.'TITLE_IO'),
            'direction_nav'         => Configuration::get($this->_prefix_st.'DIRECTION_NAV'),
            'hide_direction_nav_on_mob' => Configuration::get($this->_prefix_st.'HIDE_DIRECTION_NAV_ON_MOB'),
            'control_nav'           => Configuration::get($this->_prefix_st.'CONTROL_NAV'),
            'hide_control_nav_on_mob' => Configuration::get($this->_prefix_st.'HIDE_CONTROL_NAV_ON_MOB'),
            'spacing_between'       => Configuration::get($this->_prefix_st.'SPACING_BETWEEN'),
            'countdown_on'          => Configuration::get($this->_prefix_st.'COUNTDOWN_ON'.$ext),
            'display_pro_col'       => Configuration::get($this->_prefix_st.'DISPLAY_PRO_COL'),
            
            'has_background_img'    => ((int)Configuration::get($this->_prefix_st.'BG_PATTERN') || Configuration::get($this->_prefix_st.'BG_IMG')) ? 1 : 0,
            'speed'                 => Configuration::get($this->_prefix_st.'SPEED'),
            'bg_img_v_offset'       => (int)Configuration::get($this->_prefix_st.'BG_IMG_V_OFFSET'),

            'video_mpfour'          => Configuration::get($this->_prefix_st.'VIDEO_MPFOUR'),
            'video_webm'            => Configuration::get($this->_prefix_st.'VIDEO_WEBM'),
            'video_ogg'             => Configuration::get($this->_prefix_st.'VIDEO_OGG'),
            'video_loop'            => Configuration::get($this->_prefix_st.'VIDEO_LOOP'),
            'video_muted'           => Configuration::get($this->_prefix_st.'VIDEO_MUTED'),
            'video_v_offset'        => Configuration::get($this->_prefix_st.'VIDEO_V_OFFSET'),

            'view_more'             => (int)Configuration::get($this->_prefix_st.'VIEW_MORE'),
		));
        return true;
    }
    function getContent()
    {
        $this->initHookArray();
        $this->initTabNames();
        parent::getContent();
        Media::addJsDef(array(
            'module_name' => $this->name,
        ));
        $this->context->controller->addCSS(_MODULE_DIR_.'stthemeeditor/views/css/admin-slider.css');
        $this->context->controller->addJS(_MODULE_DIR_.'stthemeeditor/views/js/admin.js');
        $helper = $this->initForm();
        $this->smarty->assign(array(
            'bo_tabs' => $this->_tabs,
            'bo_tab_content' => $helper->generateForm($this->fields_form),
        ));
        
        return $this->_html.$this->fetch(_PS_MODULE_DIR_.'stthemeeditor/views/templates/hook/bo_tab_layout.tpl');
    }
	public function hookDisplayHome($params, $func = '', $flag=0)
	{
        $hook_hash = $this->getHookHash(($func ? $func : __FUNCTION__).(isset($params['st_time']) ? $params['st_time'] : ""));
        $template = 'module:stthemeeditor/views/templates/slider/homepage.tpl';
	    if ($func === false || !$this->isCached($template, $this->stGetCacheId($hook_hash)))
    	{
            $this->_prepareHook();

            $custom_content = Hook::exec('displayModuleCustomContent', array('type'=>2,'identify'=>$this->name), null, true);

            $this->smarty->assign(array(
                'column_slider'    => false,
                'homeverybottom'   => ($flag==2 ? true : false),
                'hook_hash'        => $hook_hash,
                'module'           => $this->name,
                'title'            => $this->title,
                'url_entity'       => $this->url_entity,
                'pro_per_fw'       => Configuration::get($this->_prefix_stsn.'PRO_PER_FW'),
                'pro_per_xxl'      => Configuration::get($this->_prefix_stsn.'PRO_PER_XXL'),
                'pro_per_xl'       => Configuration::get($this->_prefix_stsn.'PRO_PER_XL'),
                'pro_per_lg'       => Configuration::get($this->_prefix_stsn.'PRO_PER_LG'),
                'pro_per_md'       => Configuration::get($this->_prefix_stsn.'PRO_PER_MD'),
                'pro_per_sm'       => Configuration::get($this->_prefix_stsn.'PRO_PER_SM'),
                'pro_per_xs'       => Configuration::get($this->_prefix_stsn.'PRO_PER_XS'),

                'has_background_img'       => ((int)Configuration::get($this->_prefix_st.'BG_PATTERN') || Configuration::get($this->_prefix_st.'BG_IMG')) ? 1 : 0,
                'speed'                    => Configuration::get($this->_prefix_st.'SPEED'),
                'bg_img_v_offset'          => (int)Configuration::get($this->_prefix_st.'BG_IMG_V_OFFSET'),
                
                'image_type'          => Configuration::get($this->_prefix_st.'IMAGE_TYPE'),

                'custom_content' => $custom_content && array_key_exists('steasycontent', $custom_content) ? $custom_content['steasycontent'] : false,
            ));
        }
		return $this->fetch($template, $func === false ? null : $this->stGetCacheId($hook_hash));
	}
    public function hookDisplayFooter($params, $func = '')
    {
        $hook_hash = $this->getHookHash(($func ? $func : __FUNCTION__).(isset($params['st_time']) ? $params['st_time'] : ""));
        $template = 'module:stthemeeditor/views/templates/slider/footer.tpl';
	    if ($func === false || !$this->isCached($template, $this->stGetCacheId($hook_hash)))
	    {
            $this->smarty->assign(array(
                'products' => $this->getProducts('fot'),
                'add_prod_display'  => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
                'hide_mob'          => Configuration::get($this->_prefix_st.'HIDE_MOB_FOT'),
                'aw_display'        => Configuration::get($this->_prefix_st.'AW_DISPLAY_FOT'),
                'footer_wide'       => Configuration::get($this->_prefix_st.'FOOTER_WIDE'),
                'module'            => $this->name,
                'title'             => $this->title,
                'url_entity'        => $this->url_entity,
                'hook_hash'         => $hook_hash
            ));    
	    }
		return $this->fetch($template, $func === false ? null : $this->stGetCacheId($hook_hash));
    }
	public function hookDisplayLeftColumn($params, $func = '')
	{
	    $hook_hash = $this->getHookHash(($func ? $func : __FUNCTION__).(isset($params['st_time']) ? $params['st_time'] : ""));
        $template = 'module:stthemeeditor/views/templates/slider/homepage.tpl';
	    if ($func === false || !$this->isCached($template, $this->stGetCacheId($hook_hash)))
        {
            $this->_prepareHook('col');
            
            $this->smarty->assign(array(
                'tpl_module_name'       => $this->name,
                'column_slider'         => true,
                'homeverybottom'        => false,
                'hook_hash'             => $hook_hash,
                'title'                 => $this->title,
                'url_entity'            => $this->url_entity,
                'module'                => $this->name,
                'no_google_rich_snippets' => Dispatcher::getInstance()->getController()=='product',
            ));
        }
		return $this->fetch($template, $func === false ? null : $this->stGetCacheId($hook_hash));
	}
    public function hookAddProduct($params)
	{
	   $this->clearSliderCache();
	}
	public function hookUpdateProduct($params)
	{
	   $this->clearSliderCache();
	}
	public function hookDeleteProduct($params)
	{
	   $this->clearSliderCache();
	}
    public function hookCategoryUpdate($params)
    {
        $this->clearSliderCache();
    }
    public function getConfigFieldsValues()
    {
        $fields_values = parent::getConfigFieldsValues();
        $fields_values['title_io'] = Configuration::get($this->_prefix_st.'TITLE_IO');
        $fields_values['title_bottom_margin'] = Configuration::get($this->_prefix_st.'TITLE_BOTTOM_MARGIN');
        $fields_values['countdown_on'] = Configuration::get($this->_prefix_st.'COUNTDOWN_ON');
        $fields_values['countdown_on_col'] = Configuration::get($this->_prefix_st.'COUNTDOWN_ON_COL');
        $fields_values['move_col'] = 1;
        $fields_values['out_of_stock_on'] = Configuration::get($this->_prefix_st.'OUT_OF_STOCK_ON');
        return $fields_values;
    }
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return ;
    }
    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (strpos($hookName, 'display') !== false) {
            return $this->hookDisplayHome($configuration, $this->getHookHash($hookName));
        }
        return ;
    }
    public function hookvcBeforeInit()
    {
        JsComposer::add_shortcode($this->name, array($this,'vc_shortcode_init'));
        if(isset($this->context->controller->admin_webpath) && !empty($this->context->controller->admin_webpath)) {
            $this->vc_map_init();
        }  
    }
    public function vc_shortcode_init($atts, $content = null)
    {
        extract(JsComposer::shortcode_atts(array(
            'hook_name' => '',
            'st_time' => '',
            ), $atts));
        if(!isset($this->vc_hooks[$hook_name]))
            return ;
        $hook = 'hook'.ucfirst($this->vc_hooks[$hook_name]);
        if (method_exists($this, $hook)) {
            return $this->$hook(array('st_time'=>$st_time));
        }
    }
    function vc_map_init()
    {
        $vc_hooks = array();
        foreach ($this->vc_hooks as $key => $value) {
            $vc_hooks[$key] = $key;
        }
        $params = array(
            'name' => $this->displayName,
            'base' => $this->name,
            'icon' => 'icon-panda',
            'category' => 'Panda',
            'params' => array(
                array(
                    'type' => 'dropdown',
                    'holder' => 'div',
                    'class' => 'hide_in_vc_editor',
                    'heading' => $this->getTranslator()->trans('How to display', array(), 'Admin.Theme.Panda'),
                    'param_name' => 'hook_name',
                    'value' => $vc_hooks,
                    'std' => 'Block'
                ),
                array(
                    'type' => 'textfield',
                    'holder' => 'div',
                    'param_name' => 'st_time',
                    'value' => time(),
                ),
                array(
                    'type' => 'html',
                    'param_name' => 'st_conf_link_box',
                    'code' => '<a href="'.$this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'" target="_blank" class="st_conf_link">'.$this->getTranslator()->trans('Go to the module to change settings.', array(), 'Admin.Theme.Panda').'</a>',
                ),
                array(
                    'type' => 'html',
                    'param_name' => 'st_refeash_link_box',
                    'code' => '<a href="javascript:;" class="st_refeash_link">'.$this->getTranslator()->trans('Refresh this window to get new data.', array(), 'Admin.Theme.Panda').'</a>',
                ),
            )
        );
        vc_map($params);
    }
}