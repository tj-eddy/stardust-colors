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

require_once(dirname(__FILE__).'/classes/StWishListClass.php');
class StWishlist extends Module implements WidgetInterface
{
    private $templateFile = array();

    public  $fields_list;
    public  $fields_value;
    public  $fields_form;
    public static $position=array();
	private $_html = '';
    public $_prefix_st = 'ST_WISHLIST_';
    public $validation_errors = array();
    private $_custom_id = '';
    private $_hooks = array();
    protected $_tabs = array();    
    protected $templatePath;
	public function __construct()
	{
		$this->name          = 'stwishlist';
		$this->tab           = 'front_office_features';
		$this->version       = '1.0.1';
		$this->author        = 'SUNNYTOO.COM';
		$this->need_instance = 0;
        $this->bootstrap     = true;

		parent::__construct();
        $this->initHookArray();
		$this->displayName   = $this->getTranslator()->trans('Wishlist block', array(), 'Modules.Stwishlist.Admin');
		$this->description   = $this->getTranslator()->trans('Dispaly wishlist buttons on your store.', array(), 'Modules.Stwishlist.Admin');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->controllers = array('mywishlist');

        $this->templateFile = array(
            'module:stwishlist/views/templates/hook/link.tpl',
            'module:stwishlist/views/templates/hook/fly.tpl'
            );
        
        self::$position = array(
            10 => array(
                'id' => 'pos_10',
                'value' => 10,
                'label' => $this->getTranslator()->trans('NO', array(), 'Admin.Theme.Panda'),
            ),
            0 => array(
                'id' => 'pos_0',
                'value' => 0,
                'label' => $this->getTranslator()->trans('In flyout', array(), 'Admin.Theme.Panda'),
            ),
            1 => array(
                'id' => 'pos_1',
                'value' => 1,
                'label' => $this->getTranslator()->trans('Top left corner of the product image', array(), 'Admin.Theme.Panda'),
            ),
            2 => array(
                'id' => 'pos_2',
                'value' => 2,
                'label' => $this->getTranslator()->trans('Top center of the product image', array(), 'Admin.Theme.Panda'),
            ),
            3 => array(
                'id' => 'pos_3',
                'value' => 3,
                'label' => $this->getTranslator()->trans('Top right corner of the product image', array(), 'Admin.Theme.Panda'),
            ),
            4 => array(
                'id' => 'pos_4',
                'value' => 4,
                'label' => $this->getTranslator()->trans('Center left of the product image', array(), 'Admin.Theme.Panda'),
            ),
            5 => array(
                'id' => 'pos_5',
                'value' => 5,
                'label' => $this->getTranslator()->trans('Center center of the product image', array(), 'Admin.Theme.Panda'),
            ),
            6 => array(
                'id' => 'pos_6',
                'value' => 6,
                'label' => $this->getTranslator()->trans('Center right of the product image', array(), 'Admin.Theme.Panda'),
            ),
            7 => array(
                'id' => 'pos_7',
                'value' => 7,
                'label' => $this->getTranslator()->trans('Bottom left corner of the product image', array(), 'Admin.Theme.Panda'),
            ),
            8 => array(
                'id' => 'pos_8',
                'value' => 8,
                'label' => $this->getTranslator()->trans('Bottom center of the product image', array(), 'Admin.Theme.Panda')
            ),
            9 => array(
                'id' => 'pos_9',
                'value' => 9,
                'label' => $this->getTranslator()->trans('Bottom right corner of the product image', array(), 'Admin.Theme.Panda')
            ),
        ); 
        $this->templatePath = 'module:'.$this->name.'/views/templates/hook/';
	}
    
    protected function initTabNames()
    {
        $this->_tabs = array(
            array('id'  => '0,1,6,3,7', 'name' => $this->getTranslator()->trans('General settings', array(), 'Admin.Theme.Panda')),
            array('id'  => '2', 'name' => $this->getTranslator()->trans('Wishlist products', array(), 'Modules.Stwishlist.Admin')),
        );
    }
    
    private function initHookArray()
    {
        $this->_hooks = array(
            'Header' => array(
                array(
                    'id' => 'displayNav1',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Topbar left - displayNav1', array(), 'Admin.Theme.Panda'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayNav2',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Topbar right - displayNav2', array(), 'Admin.Theme.Panda'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayNav3',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Topbar center - displayNav3', array(), 'Admin.Theme.Panda'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayTop',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayTop', array(), 'Admin.Theme.Panda'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayHeaderCenter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHeaderCenter', array(), 'Admin.Theme.Panda'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayHeaderLeft',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHeaderLeft', array(), 'Admin.Theme.Panda'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayHeaderBottom',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHeaderBottom', array(), 'Admin.Theme.Panda'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayMainMenuWidget',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Main menu widget', array(), 'Admin.Theme.Transformer'),
                    'in_header' => 1,
                ),
            ),
            'Product page' => array(
                array(
                    'id' => 'displayProductNameRight',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductNameRight', array(), 'Admin.Theme.Panda'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayUnderProductName',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayUnderProductName', array(), 'Admin.Theme.Panda'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductPriceRight',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductPriceRight', array(), 'Admin.Theme.Panda'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductCartRight',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductCartRight', array(), 'Admin.Theme.Panda'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayLeftColumnProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayLeftColumnProduct', array(), 'Admin.Theme.Panda'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductLeftColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductLeftColumn', array(), 'Admin.Theme.Panda'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductCenterColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductCenterColumn', array(), 'Admin.Theme.Panda'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductRightColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductRightColumn', array(), 'Admin.Theme.Panda'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayRightColumnProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayRightColumnProduct', array(), 'Admin.Theme.Panda'),
                    'in_product_page' => 1,
                ),
            ),
        );
    }
    
    private function saveHook()
    {
        foreach($this->_hooks AS $key => $values)
        {
            if (!$key)
                continue;
            foreach($values AS $value)
            {
                $id_hook = Hook::getIdByName($value['id']);
                $key = str_replace(' ','_', $key);
                if (Tools::getValue($key.'_'.$value['id']))
                {
                    if ($id_hook && Hook::getModulesFromHook($id_hook, $this->id))
                        continue;
                    if (!$this->isHookableOn($value['id']))
                        $this->validation_errors[] = $this->getTranslator()->trans('This module cannot be transplanted to ', array(), 'Admin.Theme.Panda').$value['id'];
                    else
                        $rs = $this->registerHook($value['id'], Shop::getContextListShopID());
                }
                else
                {
                    if($id_hook && Hook::getModulesFromHook($id_hook, $this->id))
                    {
                        $this->unregisterHook($id_hook, Shop::getContextListShopID());
                        $this->unregisterExceptions($id_hook, Shop::getContextListShopID());
                    } 
                }
            }
        }
        // clear module cache to apply new data.
        Cache::clean('hook_module_list');
    }
            
	public function install()
	{
		$res = parent::install()
	        && $this->installDB()
            && $this->registerHook('displaySideBar') 
            && $this->registerHook('displayHeader')
            && $this->registerHook('customerAccount')
            && $this->registerHook('displayBeforeBodyClosingTag')
            && $this->registerHook('actionProductSearchAfter')
            && $this->registerHook('displayProductCenterColumn')
            && $this->registerHook('actionStAssemble')
            && Configuration::updateValue($this->_prefix_st.'POSITION', 10)
            && Configuration::updateValue($this->_prefix_st.'OFFSET_X', 0)
            && Configuration::updateValue($this->_prefix_st.'OFFSET_Y', 0)
            && Configuration::updateValue($this->_prefix_st.'FONT_SIZE', 0)
            && Configuration::updateValue($this->_prefix_st.'TEXT_COLOR', '')
            && Configuration::updateValue($this->_prefix_st.'TEXT_HOVER_COLOR', '')
            && Configuration::updateValue($this->_prefix_st.'ICON_BG_COLOR', '')
            && Configuration::updateValue($this->_prefix_st.'ICON_PADDING', 0)
            && Configuration::updateValue($this->_prefix_st.'FONT_SIZE_PRO', 0)
            && Configuration::updateValue($this->_prefix_st.'TEXT_COLOR_PRO', '')
            && Configuration::updateValue($this->_prefix_st.'TEXT_HOVER_COLOR_PRO', '')
            /*&& Configuration::updateValue($this->_prefix_st.'BG_COLOR', '')
            && Configuration::updateValue($this->_prefix_st.'BG_HOVER_COLOR', '')
            && Configuration::updateValue($this->_prefix_st.'BG_OPACITY', 0.9)*/
            && Configuration::updateValue($this->_prefix_st.'HEADER_STYLE', 0)
            && Configuration::updateValue($this->_prefix_st.'HEADER_ICON_SIZE', 0)
            && Configuration::updateValue($this->_prefix_st.'HEADER_TEXT_SIZE', 0)
            && Configuration::updateValue($this->_prefix_st.'WITH_NUMBER', 1)
            && Configuration::updateValue($this->_prefix_st.'PRODUCT_STYLE', 0)
            && Configuration::updateValue($this->_prefix_st.'PRO_PER_FW', 1)
            && Configuration::updateValue($this->_prefix_st.'PRO_PER_XXL', 1)
            && Configuration::updateValue($this->_prefix_st.'PRO_PER_XL', 1)
            && Configuration::updateValue($this->_prefix_st.'PRO_PER_LG', 1)
            && Configuration::updateValue($this->_prefix_st.'PRO_PER_MD', 1)
            && Configuration::updateValue($this->_prefix_st.'PRO_PER_SM', 1)
            && Configuration::updateValue($this->_prefix_st.'PRO_PER_XS', 1);
        if ($res)
			foreach(Shop::getShops(false) as $shop)
				$res &= $this->sampleData($shop['id_shop']);
        $this->clearCache();
        return (bool)$res;
	}
	
	/**
	 * Creates tables
	 */
	public function installDB()
	{
		$return = (bool)Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_wishlist` (
               `id_st_wishlist` int(10) unsigned NOT NULL AUTO_INCREMENT,
               `id_customer` int(10) unsigned NOT NULL,
               `token` varchar(64) NOT NULL,
               `name` varchar(64) NOT NULL,
               `counter` int(10) unsigned DEFAULT NULL,
               `id_shop` int(10) unsigned DEFAULT 1,
               `date_add` datetime NOT NULL,
               `date_upd` datetime NOT NULL,
               `default` int(11) NOT NULL DEFAULT 0,
               PRIMARY KEY (`id_st_wishlist`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
            
        $return &= (bool)Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_wishlist_product` (
               `id_st_wishlist_product` int(10) NOT NULL AUTO_INCREMENT,
               `id_st_wishlist` int(10) unsigned NOT NULL DEFAULT 0,
               `id_product` int(10) unsigned NOT NULL DEFAULT 0,
               `id_product_attribute` int(10) unsigned NOT NULL DEFAULT 0,
               `quantity` int(10) unsigned NOT NULL  DEFAULT 0,
               PRIMARY KEY (`id_st_wishlist_product`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
            
        $return &= (bool)Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_wishlist_email` (
			   `id_st_wishlist` int(10) unsigned NOT NULL,
               `email` varchar(128) NOT NULL,
               `date_add` datetime NOT NULL   
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
		
		return $return;
	}
    public function sampleData($id_shop)
    {
        $return = true;
        $customers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT `id_customer`
            FROM `'._DB_PREFIX_.'customer`
            WHERE `id_shop`='.(int)$id_shop
        );
        foreach($customers AS $customer) {
            $wishlist = new StWishListClass();
    		$wishlist->id_shop = $id_shop;
    		$wishlist->name = 'My wishlist';
    		$wishlist->id_customer = (int)$customer['id_customer'];
    		!$wishlist->isDefault($wishlist->id_customer) ? $wishlist->default = 1 : 0;
    		list($us, $s) = explode(' ', microtime());
    		srand($s * $us);
    		$wishlist->token = strtoupper(substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.(int)$customer['id_customer']), 0, 16));
    		$return = $wishlist->add();
        }
        return $return;
    }
	public function uninstall()
	{
	    $this->clearCache();
		// Delete configuration
		return $this->deleteTables() &&
			parent::uninstall();
	}
	/**
	 * deletes tables
	 */
	public function deleteTables()
	{
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'st_wishlist`,`'._DB_PREFIX_.'st_wishlist_product`,`'._DB_PREFIX_.'st_wishlist_email`');
	}

	public function getContent()
	{
	    $this->context->controller->addCSS($this->_path.'views/css/admin.css');
        $this->context->controller->addJS($this->_path.'views/js/admin.js');
        $this->initTabNames();
	    if (Tools::isSubmit('searchCustom')) {
            $this->_custom_id = (int)Tools::getValue('custom_id');
        }
        if (Tools::isSubmit('savestwishlist')) {
            $this->initForm();
            foreach($this->fields_form as $form){
                foreach($form['form']['input'] as $field){
                    if(isset($field['validation']))
                    {
                        $errors = array();       
                        $value = Tools::getValue($field['name']);
                        if (isset($field['required']) && $field['required'] && $value==false && (string)$value != '0')
        						$errors[] = sprintf(Tools::displayError('Field "%s" is required.'), $field['label']);
                        elseif($value)
                        {
                            $field_validation = $field['validation'];
        					if (!Validate::$field_validation($value))
        						$errors[] = sprintf(Tools::displayError('Field "%s" is invalid.'), $field['label']);
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
                                case 'isNullOrUnsignedId':
                                    $value = $value==='0' ? '0' : '';
                                break;
                                default:
                                    $value = '';
                                break;
                            }
                            Configuration::updateValue($this->_prefix_st.strtoupper($field['name']), $value);
                        }
                        else
                            Configuration::updateValue($this->_prefix_st.strtoupper($field['name']), $value);
                    }
                }
            }

            foreach($this->fields_form AS $form) {
                if (isset($form['form']['input']['dropdownlistgroup'])) {
                    $name = $form['form']['input']['dropdownlistgroup']['name'];
                    foreach ($form['form']['input']['dropdownlistgroup']['values']['medias'] as $v)
                    {
                        $t_v = (int)Tools::getValue($name.'_'.$v);
                        if(in_array($t_v, array(7,9,11)))
                            $t_v--;
                        Configuration::updateValue($this->_prefix_st.strtoupper($name.'_'.$v), $t_v);
                    }
                }
            }
            $this->saveHook();
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&conf=4&token='.Tools::getAdminTokenLite('AdminModules'));
        }
        $helper = $this->initForm();
        $this->smarty->assign(array(
            'bo_tabs' => $this->_tabs,
            'bo_tab_content' => $helper->generateForm($this->fields_form).
                '<div class="panel" id="fieldset_2_2">'.$this->renderSearchForm().$this->renderList().'</div>',
        ));

        return $this->_html.$this->display(__FILE__, 'bo_tab_layout.tpl');
	}
    public function renderSearchForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Search for customer ID', array(), 'Modules.Stwishlist.Admin'),
                    'icon' => 'icon-search',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Custom ID:', array(), 'Modules.Stwishlist.Admin'),
                        'name' => 'custom_id',
                        'class' => 'fixed-width-xxl',
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Search', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-refresh',
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->table = $this->table;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'searchCustom';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => array('custom_id' => $this->_custom_id),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }
    protected function initForm()
    {
        $this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->getTranslator()->trans('Settings', array(), 'Admin.Theme.Panda'),
                'icon' => 'icon-cogs'
			),
			'input' => array(
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('How to display the Add to wishlist button:', array(), 'Modules.Stwishlist.Admin'),
                    'name' => 'position',
                    'default_value' => 0,
                    'values' => self::$position,
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Icon color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'text_color',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Active icon color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'text_hover_color',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('How to display wishlist link on header:', array(), 'Modules.Stwishlist.Admin'),
                    'name' => 'header_style',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'header_style_both',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Icon + Text', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'header_style_name',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Text', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'header_style_flag',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Icon', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'header_style_vertical',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Icon + Text (Vertical)', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'header_style_badge',
                            'value' => 4,
                            'label' => $this->getTranslator()->trans('Icon + badge', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'header_style_badge_text',
                            'value' => 5,
                            'label' => $this->getTranslator()->trans('Icon + badge', array(), 'Admin.Theme.Panda')),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Icon size in the header:', array(), 'Admin.Theme.Panda'),
                    'name' => 'header_icon_size',
                    'prefix' => 'px',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-sm'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Text size in the header:', array(), 'Admin.Theme.Panda'),
                    'name' => 'header_text_size',
                    'prefix' => 'px',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-sm'
                ),
                /*array(
                    'type' => 'switch',
                    'label' => $this->getTransLator()->trans('Display the total number of products in wishlists:', array(), 'Modules.Stwishlist.Admin'),
                    'name' => 'with_number',
                    'default_value' => 1,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'with_number_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'with_number_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'validation' => 'isBool',
                ),*/
                /*array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Background color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'bg_color',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Background hover color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'bg_hover_color',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Background opacity:', array(), 'Admin.Theme.Panda'),
                    'name' => 'bg_opacity',
                    'validation' => 'isFloat',
                    'default_value' => 1,
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('From 0.0 (fully transparent) to 1.0 (fully opaque).', array(), 'Admin.Theme.Panda'),
                ),*/
			),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
		);
        $this->fields_form[6]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Settings for wishlist icons on the product image', array(), 'Admin.Theme.Panda'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Offset X:', array(), 'Admin.Theme.Panda'),
                    'name' => 'offset_x',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'validation' => 'isUnsignedInt',
                    'desc' => $this->getTranslator()->trans('Accept positive and negative numbers ', array(), 'Admin.Theme.Panda'),
                ), 
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Offset Y:', array(), 'Admin.Theme.Panda'),
                    'name' => 'offset_y',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'validation' => 'isUnsignedInt',
                    'desc' => $this->getTranslator()->trans('Accept positive and negative numbers ', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Icon size:', array(), 'Admin.Theme.Panda'),
                    'name' => 'font_size',
                    'prefix' => 'px',
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Background color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'icon_bg_color',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Icon block size:', array(), 'Admin.Theme.Panda'),
                    'name' => 'icon_padding',
                    'prefix' => 'px',
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-lg',
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
        );
        $this->fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Product page', array(), 'Admin.Theme.Panda'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('How to display on the product page:', array(), 'Modules.Stwishlist.Admin'),
                    'name' => 'product_style',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'product_style_both',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Icon + Text', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'product_style_name',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Text', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'product_style_flag',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Icon', array(), 'Admin.Theme.Panda')),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Icon size:', array(), 'Admin.Theme.Panda'),
                    'name' => 'font_size_pro',
                    'prefix' => 'px',
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-lg',
                ),
                /*array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Icon color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'text_color_pro',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Active icon color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'text_hover_color_pro',
                    'size' => 33,
                    'validation' => 'isColor',
                ),*/
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
        );
        $this->fields_form[7]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Wishlist Share page ', array(), 'Admin.Theme.Panda'),
                'icon' => 'icon-cogs'
            ),

            'input' => array(
                'dropdownlistgroup' => array(
                    'type' => 'dropdownlistgroup',
                    'label' => $this->getTranslator()->trans('The number of columns:', array(), 'Admin.Theme.Panda'),
                    'name' => 'pro_per',
                    'values' => array(
                            'maximum' => 12,
                            'medias' => array('fw','xxl','xl','lg','md','sm','xs'),
                        ),
                    'desc' => $this->getTranslator()->trans('7, 9 and 11 can not be used in grid view, they will be automatically decreased to 6, 8 and 10. Set a value for the "Full width" drop down list to make this module fullwidth in the fullwidth* hooks, but the value of "Full width" drop down menu would not take effect in grid view.', array(), 'Admin.Theme.Panda'),
                ), 
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
        );
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Hook manager', array(), 'Admin.Theme.Panda'),
                'icon' => 'icon-cogs'
            ),
            'description' => $this->getTranslator()->trans('Check the hook that you would like this module to display on.', array(), 'Admin.Theme.Panda').'<br/><a href="'._MODULE_DIR_.'stthemeeditor/img/hook_into_hint.jpg" target="_blank" >'.$this->getTranslator()->trans('Click here to see hook position', array(), 'Admin.Theme.Panda').'</a>.',
            'input' => array(
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions')
            ),
        );
        
        foreach($this->_hooks AS $key => $values)
        {
            if (!is_array($values) || !count($values))
                continue;
            $this->fields_form[1]['form']['input'][] = array(
					'type' => 'checkbox',
					'label' => $key,
					'name' => $key,
					'lang' => true,
					'values' => array(
						'query' => $values,
						'id' => 'id',
						'name' => 'name'
					)
				);
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $helper->module = $this;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->submit_action = 'savestwishlist';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper;
    }
    public function renderList()
    {
        $fields_list = array(
            'id_st_wishlist' => array(
                'title' => $this->trans('ID', array(), 'Admin.Theme.Panda'),
                'class' => 'fixed-width-sm',
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'id_customer' => array(
                'title' => $this->trans('Customer', array(), 'Admin.Theme.Panda'),
                'class' => 'fixed-width-md',
                'type' => 'text',
                'callback' => 'displayCustomer',
                'callback_object' => 'StWishlist',
                'search' => false,
                'orderby' => false
            ),
            'name' => array(
                'title' => $this->trans('Wishlist name', array(), 'Modules.Stwishlist.Admin'),
                'class' => 'fixed-width-md',
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'token' => array(
                'title' => $this->trans('Products', array(), 'Admin.Theme.Panda'),
                'class' => 'fixed-width-xxl',
                'type' => 'text',
                'callback' => 'displayProduct',
                'callback_object' => 'StWishlist',
                'search' => false,
                'orderby' => false
            ),
        );

        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->trans('Wishlist products', array(), 'Modules.Stwishlist.Admin');
        $helper_list->shopLinkType = '';
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id';
        $helper_list->table = 'merged';
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = array();

        /* Retrieve list data */
        $wishlists = StWishListClass::getCustomWishlist($this->_custom_id);
        $helper_list->listTotal = count($wishlists);

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter'.$helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table.'_pagination')) ? $pagination : 30;
        $wishlists = $this->paginateWishlist($wishlists, $page, $pagination);

        return $helper_list->generateList($wishlists, $fields_list);
    }
    public function paginateWishlist($wishlists, $page = 1, $pagination = 50)
    {
        if (count($wishlists) > $pagination) {
            $wishlists = array_slice($wishlists, $pagination * ($page - 1), $pagination);
        }
        return $wishlists;
    }
    public static function displayProduct($value, $tr)
    {
        $html = '';
        $context = Context::getContext();
        if($products = StWishListClass::getWishlistProducts((int)$tr['id_st_wishlist'])) {
            $html = '<ul>';
            foreach($products AS $product) {
                $id_product_attribute = $product['id_product_attribute'];
                $product = new Product($product['id_product'], false, (int)$context->language->id);
                if ($product->id) {
                    if ($id_product_attribute) {
                        $attr = '';
                        foreach(Product::getAttributesParams($product->id, $id_product_attribute) AS $value) {
                            $attr .= $value['group'].' : '.$value['name'].', ';
                        }
                        $attr && $product->name .= '-'.trim($attr, ',');
                    }
                    $html .= '<li><a href="'.$context->link->getAdminLink('AdminProducts', true, array('id_product' => $product->id, 'updateproduct' => '1')).'" target="_blank">'.$product->name.'['.$product->reference.']</a>'.'</li>';
                }
            }
            $html .= '</ul>';
        }
        return $html;
    }
    public static function displayCustomer($value, $tr)
    {       
        $info = '--';
        $customer = new Customer((int)$value);
        if ($customer->id) {
            $info = $customer->firstname.' '.$customer->lastname;
        }
        return $info;
    }
    public function hookDisplayHeader($params)
    {
        $stwish_pros = array();
        if($this->context->customer->isLogged()){
            $mywish = StWishListClass::getAllProducts($this->context->customer->id);
            foreach ($mywish as $value) {
                $stwish_pros[] = $value['id_product'];
            }
        }
        Media::addJsDef(array(
            'stmywishlist_url' => $this->context->link->getModuleLink('stwishlist', 'mywishlist'),
            'stwish_pros' => array_unique($stwish_pros),
        ));
        $this->context->smarty->assign($this->getWidgetVariables());

        $this->context->controller->addJS(_MODULE_DIR_.'stwishlist/views/js/wishlist.js');

        if (!$this->isCached($this->templatePath.'header.tpl', $this->getCacheId()))
        {
            $postion = Configuration::get($this->_prefix_st.'POSITION');
            $prefix = '.add_to_wishlit.layer_btn';

            $custom_css = '';
            if($postion>0 && $postion<10)
            {
                $offset_x = Configuration::get($this->_prefix_st.'OFFSET_X');
                $offset_y = Configuration::get($this->_prefix_st.'OFFSET_Y');
                switch ($postion) {
                    case 1:
                        $custom_css .= $prefix.'{left:'.$offset_x.'px;}';
                        $custom_css .= $prefix.'{top:'.$offset_y.'px;}';
                        break;
                    case 2:
                        $custom_css .= $prefix.'{left:50%;margin-left:'.$offset_x.'px;}';
                        $custom_css .= $prefix.'{top:'.$offset_y.'px;}';
                        break;
                    case 3:
                        $custom_css .= $prefix.'{right:'.$offset_x.'px;}';
                        $custom_css .= $prefix.'{top:'.$offset_y.'px;}';
                        break;
                    case 4:
                        $custom_css .= $prefix.'{left:'.$offset_x.'px;}';
                        $custom_css .= $prefix.'{top:50%;margin-top:'.$offset_y.'px;}';
                        break;
                    case 5:
                        $custom_css .= $prefix.'{left:50%;margin-left:'.$offset_x.'px;}';
                        $custom_css .= $prefix.'{top:50%;margin-top:'.$offset_y.'px;}';
                        break;
                    case 6:
                        $custom_css .= $prefix.'{right:'.$offset_x.'px;}';
                        $custom_css .= $prefix.'{top:50%;margin-top:'.$offset_y.'px;}';
                        break;
                    case 7:
                        $custom_css .= $prefix.'{left:'.$offset_x.'px;}';
                        $custom_css .= $prefix.'{bottom:'.$offset_y.'px;}';
                        break;
                    case 8:
                        $custom_css .= $prefix.'{left:50%;margin-left:'.$offset_x.'px;}';
                        $custom_css .= $prefix.'{bottom:'.$offset_y.'px;}';
                        break;
                    case 9:
                        $custom_css .= $prefix.'{right:'.$offset_x.'px;}';
                        $custom_css .= $prefix.'{bottom:'.$offset_y.'px;}';
                        break;
                }
            }
            if($font_size = Configuration::get($this->_prefix_st.'FONT_SIZE')){
                $custom_css .= $prefix.'{font-size:'.$font_size.'px;}';
            }
            if($text_color = Configuration::get($this->_prefix_st.'TEXT_COLOR'))
                $custom_css .= '.add_to_wishlit, .add_to_wishlit:hover{color:'.$text_color.';}';
            if($text_hover_color = Configuration::get($this->_prefix_st.'TEXT_HOVER_COLOR'))
                $custom_css .= '.add_to_wishlit.st_added, .add_to_wishlit.st_added:hover{color:'.$text_hover_color.';}';
            if($icon_bg_color = Configuration::get($this->_prefix_st.'ICON_BG_COLOR'))
                $custom_css .= $prefix.'{background:'.$icon_bg_color.';}';
            if($icon_padding = (int)Configuration::get($this->_prefix_st.'ICON_PADDING'))
                $custom_css .= $prefix.'{width:'.$icon_padding.'px;height:'.$icon_padding.'px;line-height:'.$icon_padding.'px;border-radius:100%;}';

            $product_style        = Configuration::get($this->_prefix_st.'PRODUCT_STYLE');
            if($product_style==1){
                $custom_css .= '.wishlist_product i{display:none;}';
            }elseif($product_style==2){
                $custom_css .= '.wishlist_product .btn_text{display:none;}';
            }
            if($font_size_pro = Configuration::get($this->_prefix_st.'FONT_SIZE_PRO')){
                $custom_css .= '.wishlist_product i{font-size:'.$font_size_pro.'px;}';
            }
            if($icon_size = Configuration::get($this->_prefix_st.'HEADER_ICON_SIZE')){
                $custom_css .= '.wishlist_link.top_bar_item .header_icon_btn_icon i{font-size:'.$icon_size.'px;}';
            }
            if($text_size = Configuration::get($this->_prefix_st.'HEADER_TEXT_SIZE')){
                $custom_css .= '.wishlist_link.top_bar_item .header_icon_btn_text{font-size:'.$text_size.'px;}';
            }
            /*if($text_color_pro = Configuration::get($this->_prefix_st.'TEXT_COLOR_PRO')){
                $custom_css .= '.wishlist_product i{color:'.$text_color_pro.';}';
            }
            if($text_hover_color_pro = Configuration::get($this->_prefix_st.'TEXT_HOVER_COLOR_PRO')){
                $custom_css .= '.wishlist_product:hover i, .wishlist_product.st_added i{color:'.$text_hover_color_pro.';}';
            }*/


            $this->smarty->assign('custom_css', preg_replace('/\s\s+/', ' ', $custom_css));
        }
        return $this->fetch($this->templatePath.'header.tpl', $this->getCacheId());
    }
    /*
    public function hookDisplayProductListFunctionalButtons($params)
	{
		if ($this->context->customer->isLogged())
			$this->smarty->assign('wishlists', StWishListClass::getByIdCustomer($this->context->customer->id));

		$this->smarty->assign('product', $params['product']);
		return $this->display(__FILE__, 'stwishlist_button.tpl');
	}
    public function hookProductActions($params)
	{
		$cookie = $params['cookie'];

		$this->smarty->assign(array(
			'id_product' => (int)Tools::getValue('id_product'),
		));

		if (isset($cookie->id_customer))
			$this->smarty->assign(array(
				'wishlists' => StWishListClass::getByIdCustomer($cookie->id_customer),
			));

		return ($this->display(__FILE__, 'stwishlist-extra.tpl'));
	}*/
	protected function stGetCacheId($key,$name = null)
	{
		$cache_id = parent::getCacheId($name);
		return $cache_id.'_'.$key;
	}
	private function clearCache()
	{
        $this->_clearCache('*');
	}
    public function renderWidget($hookName = null, array $configuration = [])
    {
        $this->smarty->assign($this->getWidgetVariables());

        $in_header = $in_product_page = false;
        foreach ($this->_hooks as $sub_hooks) {
            foreach ($sub_hooks as $v) {
                if (Tools::strtolower($v['id'])==Tools::strtolower($hookName)) {
                    $in_header = isset($v['in_header']);
                    $in_product_page = isset($v['in_product_page']);
                    break;
                }
            }
        }

        if($in_product_page) {
            $id_product = (int)Tools::getValue('id_product');
            // To be compatible with the cache module.
            $product = array(
                'id_product' => $id_product,
                'id_product_attribute' => Tools::getValue('id_product_attribute'),
                'count' => StWishListClass::getProductTotal($id_product),
            );
            $classname = '';
            if($this->context->customer->isLogged()){
                $wishlisted = StWishListClass::countProCustomer($this->context->customer->id,$id_product);
                if($wishlisted)
                    $classname .= ' st_added ';
                
            }
            $this->smarty->assign(array(
                'classname' => $classname.' btn_inline wishlist_product',
                'product' => $product,
                'fromnocache' => true,
            ));
        }
        if($in_header) {
            $this->smarty->assign(array(
                'customer_total' => $this->context->customer->id ? StWishListClass::getCustomerTotal($this->context->customer->id) : 0,
            ));
        }

        return $this->fetch($in_header ? $this->templateFile[0] : $this->templateFile[1]);
    }
    
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return array(
            'stwishlist_url' => $this->context->link->getModuleLink('stwishlist', 'mywishlist'),
            'wishlist_header_style'        => Configuration::get($this->_prefix_st.'HEADER_STYLE'),
            'wishlist_with_number'        => Configuration::get($this->_prefix_st.'WITH_NUMBER'),
            'wishlist_product_style'        => Configuration::get($this->_prefix_st.'PRODUCT_STYLE'),
            'wishlist_position'          => Configuration::get($this->_prefix_st.'POSITION'),
        );        
    }
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        return $this->fetch('module:stwishlist/views/templates/hook/go_login.tpl');
    }
    public function hookDisplaySideBar($params)
    {
        $tpl = 'module:stwishlist/views/templates/hook/sidebar.tpl';
        $wishlists = StWishListClass::getByIdCustomer($this->context->customer->id, true);
        if (!count($wishlists)) {
            $wishlist = new StWishListClass();
            $wishlist->id_shop = (int)$this->context->shop->id;
            $wishlist->name = 'My wishlist';
            $wishlist->id_customer = (int)$this->context->customer->id;
            $wishlist->default = 1;
            list($us, $s) = explode(' ', microtime());
            srand($s * $us);
            $wishlist->token = strtoupper(substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.(int)$this->context->customer->id), 0, 16));
            $wishlist->add();
            $wishlists = StWishListClass::getByIdCustomer($this->context->customer->id, true);
        }
        $this->smarty->assign(array(
            'wishlists' => $wishlists,
            ));
        return $this->fetch($tpl);
    }
    
    public function hookDisplayCustomerAccount($params)
	{
		return $this->display(__FILE__, 'my-account.tpl');
	}

	public function hookDisplayMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}
    /*public function hookDisplayMobileBar($params)
    {
        return $this->fetch('module:stwishlist/views/templates/hook/mobilebar.tpl');
    }    
    public function hookDisplayMobileBarLeft($params){
        return $this->hookDisplayMobileBar($params);
    }
    public function hookDisplayMobileBarCenter($params){
        return $this->hookDisplayMobileBar($params);
    }
    public function hookDisplayMobileBarBottom($params){
        return $this->hookDisplayMobileBar($params);
    }*/
    private function getConfigFieldsValues()
    {
        $fields_values = array(
            'pro_per_fw'=> Configuration::get($this->_prefix_st.'PRO_PER_FW'),
            'pro_per_xxl'=> Configuration::get($this->_prefix_st.'PRO_PER_XXL'),
            'pro_per_xl'=> Configuration::get($this->_prefix_st.'PRO_PER_XL'),
            'pro_per_lg'=> Configuration::get($this->_prefix_st.'PRO_PER_LG'),
            'pro_per_md'=> Configuration::get($this->_prefix_st.'PRO_PER_MD'),
            'pro_per_sm'=> Configuration::get($this->_prefix_st.'PRO_PER_SM'),
            'pro_per_xs'=> Configuration::get($this->_prefix_st.'PRO_PER_XS'),
            'position'          => Configuration::get($this->_prefix_st.'POSITION'),
            'offset_x'          => Configuration::get($this->_prefix_st.'OFFSET_X'),
            'offset_y'          => Configuration::get($this->_prefix_st.'OFFSET_Y'),
            'font_size'         => Configuration::get($this->_prefix_st.'FONT_SIZE'),
            'text_color'        => Configuration::get($this->_prefix_st.'TEXT_COLOR'),
            'text_hover_color'  => Configuration::get($this->_prefix_st.'TEXT_HOVER_COLOR'),
            'icon_bg_color'  => Configuration::get($this->_prefix_st.'ICON_BG_COLOR'),
            'icon_padding'  => Configuration::get($this->_prefix_st.'ICON_PADDING'),
            'font_size_pro'         => Configuration::get($this->_prefix_st.'FONT_SIZE_PRO'),
            'text_color_pro'        => Configuration::get($this->_prefix_st.'TEXT_COLOR_PRO'),
            'text_hover_color_pro'  => Configuration::get($this->_prefix_st.'TEXT_HOVER_COLOR_PRO'),
            'bg_color'          => Configuration::get($this->_prefix_st.'BG_COLOR'),
            'bg_hover_color'    => Configuration::get($this->_prefix_st.'BG_HOVER_COLOR'),
            'bg_opacity'        => Configuration::get($this->_prefix_st.'BG_OPACITY'),
            'header_style'        => Configuration::get($this->_prefix_st.'HEADER_STYLE'),
            'header_icon_size'        => Configuration::get($this->_prefix_st.'HEADER_ICON_SIZE'),
            'header_text_size'        => Configuration::get($this->_prefix_st.'HEADER_TEXT_SIZE'),
            'with_number'        => Configuration::get($this->_prefix_st.'WITH_NUMBER'),
            'product_style'        => Configuration::get($this->_prefix_st.'PRODUCT_STYLE'),
        );
        foreach($this->_hooks AS $key => $values)
        {
            if (!$key)
                continue;
            foreach($values AS $value)
            {
                $fields_values[$key.'_'.$value['id']] = 0;
                if($id_hook = Hook::getIdByName($value['id']))
                    if(Hook::getModulesFromHook($id_hook, $this->id))
                        $fields_values[$key.'_'.$value['id']] = 1;
            }
        }
        return $fields_values;        
    }

    public function hookActionProductSearchAfter($params){
        $this->context->smarty->assign($this->getWidgetVariables());
        return ;
    }
    public function hookActionStAssemble($product)
    {
        return array(
            'wished' => StWishListClass::countProCustomer($this->context->customer->id,$product['id_product']),
            );
    }
}