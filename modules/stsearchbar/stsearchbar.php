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

class StSearchbar extends Module implements WidgetInterface
{
    private $templateFile = array();
    private $_html = '';
    public $fields_form;
    public $fields_value;
    public $validation_errors = array();
    private $_hooks = array();
    private $_as_results = array();

	public function __construct()
	{
		$this->name = 'stsearchbar';
		$this->tab = 'search_filter';
		$this->version = '1.6.7';
		$this->author = 'SUNNYTOO.COM';
		$this->need_instance = 0;
		$this->bootstrap     = true;

		parent::__construct();
        
		$this->displayName = $this->getTranslator()->trans('Search bar mod', array(), 'Modules.Stsearchbar.Admin');
		$this->description = $this->getTranslator()->trans('Adds a quick search field to your website.', array(), 'Modules.Stsearchbar.Admin');
		$this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->templateFile = array(
            'module:stsearchbar/views/templates/hook/stsearchbar.tpl',
            'module:stsearchbar/views/templates/hook/header.tpl'
            );

        $this->_align =  array(
                array(
                    'id' => 'quick_search_simple_0',
                    'value' => 0,
                    'label' => '<img src="'.$this->_path.'views/img/a_0.jpg" />'),
                array(
                    'id' => 'quick_search_simple_2',
                    'value' => 2,
                    'label' => '<img src="'.$this->_path.'views/img/a_2.gif" />'),
                /*array(
                    'id' => 'quick_search_simple_3',
                    'value' => 1,
                    'label' => '<img src="'.$this->_path.'views/img/a_3.gif" />'),*/
                array(
                    'id' => 'quick_search_simple_4',
                    'value' => 4,
                    'label' => '<img src="'.$this->_path.'views/img/a_4.gif" />'),
                array(
                    'id' => 'quick_search_simple_5',
                    'value' => 5,
                    'label' => '<img src="'.$this->_path.'views/img/a_5.gif" />'),
                array(
                    'id' => 'quick_search_simple_6',
                    'value' => 6,
                    'label' => '<img src="'.$this->_path.'views/img/a_6.gif" />'),
            );
	}
    private function initAsResults()
    {
        $this->_as_results = array(
                array(
                    'id' => 'index',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Product image', array(), 'Modules.Stsearchbar.Admin'),
                ),
                array(
                    'id' => 'category',
                    'val' => '2',
                    'name' => $this->getTranslator()->trans('Product name', array(), 'Modules.Stsearchbar.Admin'),
                ),
                array(
                    'id' => 'product',
                    'val' => '4',
                    'name' => $this->getTranslator()->trans('Product price', array(), 'Modules.Stsearchbar.Admin'),
                ),
            );
    }
    private function initHookArray()
    {
        $this->_hooks = array(
            'Hooks' => array(
                array(
        			'id' => 'displayNav1',
        			'val' => '1',
        			'name' => $this->getTranslator()->trans('Topbar left - displayNav1', array(), 'Admin.Theme.Panda'),
                    // 'sin' => '1',
        		),
                array(
                    'id' => 'displayNav3',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Topbar center - displayNav3', array(), 'Admin.Theme.Panda'),
                    // 'sin' => '1',
                ),
        		array(
        			'id' => 'displayNav2',
        			'val' => '1',
        			'name' => $this->getTranslator()->trans('Topbar right - displayNav2', array(), 'Admin.Theme.Panda'),
                    // 'sin' => '1',
        		),
                array(
                    'id' => 'displayHeaderLeft',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Header left', array(), 'Admin.Theme.Panda'),
                    // 'sin' => '1',
                ),
                array(
                    'id' => 'displayHeaderCenter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Header center', array(), 'Admin.Theme.Panda'),
                    // 'sin' => '1',
                ),
                array(
                    'id' => 'displayTop',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Header right(Header top)', array(), 'Admin.Theme.Panda'),
                    // 'sin' => '1',
                ),
        		array(
        			'id' => 'displayHeaderBottom',
        			'val' => '1',
        			'name' => $this->getTranslator()->trans('Header right bottom', array(), 'Admin.Theme.Panda'),
                    // 'sin' => '1',
        		),
                array(
                    'id' => 'displayMainMenuWidget',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Main menu widget', array(), 'Admin.Theme.Panda'),
                    // 'sin' => '1',
                ),
                array(
                    'id' => 'displayMainMenu',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Main menu', array(), 'Admin.Theme.Panda'),
                    // 'sin' => '1',
                ),
                /*array(
                    'id' => 'displayMobileBar',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Mobile Bar', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayMobileBarLeft',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Mobile Bar left', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayMobileBarCenter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Mobile Bar center', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displayMobileBarBottom',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Mobile Bar bottom', array(), 'Admin.Theme.Panda'),
                ),*/
                array(
                    'id' => 'displayMobileNav',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayMobileNav', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'id' => 'displaySearch',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displaySearch', array(), 'Admin.Theme.Panda'),
                ),
            )
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
                $val = (int)Tools::getValue($key.'_'.$value['id']);
                $this->_processHook($key, $value['id'], $val);
                if (isset($value['ref']) && $value['ref'])
                    $this->_processHook($key, $value['ref'], $val);
            }
        }
        // clear module cache to apply new data.
        Cache::clean('hook_module_list');
    }
    
    private function _processHook($key='', $hook='', $value=1)
    {
        if (!$key || !$hook)
            return false;
        $rs = true;
        $id_hook = Hook::getIdByName($hook);
        if ($value)
        {
            if ($id_hook && Hook::getModulesFromHook($id_hook, $this->id))
                return $rs;
            if (!$this->isHookableOn($hook))
                $this->validation_errors[] = $this->getTranslator()->trans('This module cannot be transplanted to ', array(), 'Admin.Theme.Panda').$hook;
            else
                $rs = $this->registerHook($hook, Shop::getContextListShopID());
        }
        else
        {
            if($id_hook && Hook::getModulesFromHook($id_hook, $this->id))
            {
                $rs = $this->unregisterHook($id_hook, Shop::getContextListShopID());
                $rs &= $this->unregisterExceptions($id_hook, Shop::getContextListShopID());
            } 
        }
        return $rs;
    }

	public function install()
	{
		if (!parent::install() 
			|| !$this->registerHook('displayHeaderCenter') 
			|| !$this->registerHook('displayheader') 
            || !$this->registerHook('displaySideBar')
			|| !$this->registerHook('displayMobileNav')
            || !$this->registerHook('filterProductSearch')
			|| !Configuration::updateValue('ST_QUICK_SEARCH_SIMPLE', 0)
            || !Configuration::updateValue('ST_QUICK_SEARCH_WIDTH', '280px')
            || !Configuration::updateValue('ST_QUICK_SEARCH_AS', 1)
            || !Configuration::updateValue('ST_QUICK_SEARCH_AS_MIN', 1)
            || !Configuration::updateValue('ST_QUICK_SEARCH_AS_HEIGHT', 0)
            || !Configuration::updateValue('ST_QUICK_SEARCH_MOBILE', 0)
            || !Configuration::updateValue('ST_QUICK_SEARCH_AS_SIZE', 6)
            || !Configuration::updateValue('ST_QUICK_SEARCH_AS_RESULTS', 7)
            || !Configuration::updateValue('ST_QUICK_SEARCH_BORDER_RADIUS', 0)
            || !Configuration::updateValue('ST_QUICK_SEARCH_INPUT_BORDER_COLOR', '')
            || !Configuration::updateValue('ST_QUICK_SEARCH_POPSEARCH_HEIGHT', 0)
            || !Configuration::updateValue('ST_QUICK_SEARCH_POPSEARCH_INPUT_HEIGHT', 0)
            || !Configuration::updateValue('ST_QUICK_SEARCH_POPSEARCH_INPUT_FS', 0)
            || !Configuration::updateValue('ST_QUICK_SEARCH_POPSEARCH_BG', '')
            || !Configuration::updateValue('ST_QUICK_SEARCH_POPSEARCH_INPUT_BORDER_COLOR', '')
            || !Configuration::updateValue('ST_QUICK_SEARCH_POPSEARCH_INPUT_BG_COLOR', '')
            || !Configuration::updateValue('ST_QUICK_SEARCH_POPSEARCH_INPUT_COLOR', '')
            || !Configuration::updateValue('ST_QUICK_SEARCH_HEADER_STYLE', 0)
            || !Configuration::updateValue('ST_QUICK_SEARCH_HEADER_ICON_SIZE', 0)
            || !Configuration::updateValue('ST_QUICK_SEARCH_HEADER_TEXT_SIZE', 0)
        )
				return false;

        $languages = Language::getLanguages(false);
        $placeholder = array();
        foreach ($languages as $language) {
            $placeholder[$language['id_lang']] = 'Recherche';
        }
        Configuration::updateValue('ST_QUICK_SEARCH_PLACEHOLDER', $placeholder);

		return true;
	}


    public function getContent()
	{
	    $this->initHookArray();
        $this->initAsResults();
	    $this->initFieldsForm();
		if (isset($_POST['savestsearchbar']))
		{
            foreach($this->fields_form as $form)
                foreach($form['form']['input'] as $field)
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
                                default:
                                    $value = '';
                                break;
                            }
                            Configuration::updateValue('ST_'.strtoupper($field['name']), $value);
                        }
                        else
                            Configuration::updateValue('ST_'.strtoupper($field['name']), $value);
                        if('ST_'.strtoupper($field['name'])=='ST_QUICK_SEARCH_AS_MIN')
                            Configuration::updateValue('PS_SEARCH_MINWORDLEN',$value);
                    }
            $simple = (int)(Tools::getValue('quick_search_simple'));
            Configuration::updateValue('ST_QUICK_SEARCH_SIMPLE', $simple);
            if($simple==4 || $simple==5)
                $this->registerHook('displaySideBar');
            if($simple==6)
                $this->registerHook('displayFullWidthBottom');
            else
                $this->unregisterHook('displayFullWidthBottom');
            $languages = Language::getLanguages(false);
            $placeholder = array();
            foreach ($languages as $language) {
                $placeholder[$language['id_lang']] = Tools::getValue('placeholder_'.$language['id_lang']);
            }
            Configuration::updateValue('ST_QUICK_SEARCH_PLACEHOLDER', $placeholder);

            $results = 0;
            foreach($this->_as_results as $v)
                $results += (int)Tools::getValue('quick_search_as_results_'.$v['id']);
            Configuration::updateValue('ST_QUICK_SEARCH_AS_RESULTS', $results);

            if(count($this->validation_errors))
                $this->_html .= $this->displayError(implode('<br/>',$this->validation_errors));
            else 
            {
                $this->saveHook();
                $this->_html .= $this->displayConfirmation($this->getTranslator()->trans('Settings updated', array(), 'Admin.Theme.Panda'));
            }
            $this->_clearCache('*');
            $this->initFieldsForm();
        }
        
		$helper = $this->initForm();
		return $this->_html.$helper->generateForm($this->fields_form);
	}

    protected function initFieldsForm()
    {
		$this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->displayName,
                'icon' => 'icon-cogs'
			),
            'input' => array(
                array(
                    'type' => 'html',
                    'id'   => 'quick_search_simple',
                    'label' => $this->getTranslator()->trans('Style:', array(), 'Admin.Theme.Panda'),
                    'name' => $this->BuildRadioUI($this->_align, 'quick_search_simple', (int)Configuration::get('ST_QUICK_SEARCH_SIMPLE')),
                ),

                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('How to display in the header:', array(), 'Modules.Stsearchbar.Admin'),
                    'name' => 'quick_search_header_style',
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
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Icon size in the header:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_header_icon_size',
                    'prefix' => 'px',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-sm'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Text size in the header:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_header_text_size',
                    'prefix' => 'px',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-sm'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Search box width in the header:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_width',
                    'validation' => 'isAnything',
                    'class' => 'fixed-width-lg',
                    'desc' => array(
                            $this->getTranslator()->trans('The vaule must have units,  Sample vaules: 200px, 350px.', array(), 'Modules.Stsearchbar.Admin'),
                            $this->getTranslator()->trans('If search box is beside the logo and in Style 1 then you can use values like these: 50%, 90%', array(), 'Modules.Stsearchbar.Admin'),
                        ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Auto suggestion:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_as',
                    'default_value' => 0,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'quick_search_as_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'quick_search_as_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->getTranslator()->trans('Elements in the search result', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_as_results',
                    'values' => array(
                        'query' => $this->_as_results,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'desc' => $this->getTranslator()->trans('Choose content displayed on ajax search results', array(), 'Modules.Stsearchbar.Admin'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('How many items in the search result:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_as_size',
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Minimum number of characters required to trigger autosuggest:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_as_min',
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Length of product name:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_name_len',
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Maximum height of the suggestions container in pixels:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_as_height',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('Leave it empty and 0 for no limit.', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('How to display search box on mobile header:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_mobile',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'quick_search_mobile_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('A search icon', array(), 'Modules.Stsearchbar.Admin')
                        ),
                        array(
                            'id' => 'quick_search_mobile_1',
                            'value' => 1,
                            'label' => $this->getTranslatOr()->trans('An input file. This is easy for customers to search ', array(), 'Modules.Stsearchbar.Admin')
                        ),
                    ),
                    'validation' => 'isUnsignedInt',
                ),   
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Placeholder:', array(), 'Admin.Theme.Panda'),
                    'name' => 'placeholder',
                    'validation' => 'isAnything',
                    'lang' => true,
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Border color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_input_border_color',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Border radius:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_border_radius',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                ),
			),
			'submit' => array(
				'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions')
			)
		);
        
        $this->fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Popup search box', array(), 'Admin.Theme.Panda'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Height:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_popsearch_height',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Search box height:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_popsearch_input_height',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Search box font size:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_popsearch_input_fs',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Background color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_popsearch_bg',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Border color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_popsearch_input_border_color',
                    'size' => 33,
                    'validation' => 'isColor',
                    'desc' => $this->getTranslator()->trans('Set it to the same color as the background to make the border invisiable.', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Search field background color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_popsearch_input_bg_color',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Search field text color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'quick_search_popsearch_input_color',
                    'size' => 33,
                    'validation' => 'isColor',
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions')
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
    }
    public function BuildRadioUI($array, $name, $checked_value = 0)
    {
        $html = '';
        foreach($array AS $key => $value)
        {
            $html .= '<label><input type="radio"'.($checked_value==$value['value'] ? ' checked="checked"' : '').' value="'.$value['value'].'" id="'.(isset($value['id']) ? $value['id'] : $name.'_'.$value['value']).'" name="'.$name.'">'.(isset($value['label'])?$value['label']:'').'</label>';
            if (($key+1) % 8 == 0)
                $html .= '<br />';
        }
        return $html;
    }
    public function hookFilterProductSearch($param){
        if(isset($param['searchVariables']['products']) && $param['searchVariables']['js_enabled'] && !empty($param['searchVariables']['products']) && Configuration::get('ST_QUICK_SEARCH_NAME_LEN')){
            foreach($param['searchVariables']['products'] as $key=>$product){
                $param['searchVariables']['products'][$key]->name=substr($product->name,0,(int)Configuration::get('ST_QUICK_SEARCH_NAME_LEN'));
            }
        }
        return;
    }
    protected function initForm()
	{
	    $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
        $helper->module = $this;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'savestsearchbar';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

        foreach($this->_as_results as $v)
            $helper->tpl_vars['fields_value']['quick_search_as_results_'.$v['id']] = (int)$v['val']&(int)Configuration::get('ST_QUICK_SEARCH_AS_RESULTS');

		return $helper;
	}

    private function getConfigFieldsValues()
    {        
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
        $fields_values['quick_search_name_len'] = Configuration::get('ST_QUICK_SEARCH_NAME_LEN');
        $fields_values['quick_search_width'] = Configuration::get('ST_QUICK_SEARCH_WIDTH');
        $fields_values['quick_search_as'] = Configuration::get('ST_QUICK_SEARCH_AS');
        $fields_values['quick_search_as_size'] = Configuration::get('ST_QUICK_SEARCH_AS_SIZE');
        $fields_values['quick_search_as_min'] = Configuration::get('ST_QUICK_SEARCH_AS_MIN');
        $fields_values['quick_search_as_height'] = Configuration::get('ST_QUICK_SEARCH_AS_HEIGHT');
        $fields_values['quick_search_mobile'] = Configuration::get('ST_QUICK_SEARCH_MOBILE');
        $fields_values['quick_search_input_border_color'] = Configuration::get('ST_QUICK_SEARCH_INPUT_BORDER_COLOR');
        $fields_values['quick_search_border_radius'] = Configuration::get('ST_QUICK_SEARCH_BORDER_RADIUS');
        $fields_values['quick_search_popsearch_height'] = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_HEIGHT');
        $fields_values['quick_search_popsearch_input_height'] = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_HEIGHT');
        $fields_values['quick_search_popsearch_input_fs'] = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_FS');
        $fields_values['quick_search_popsearch_bg'] = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_BG');
        $fields_values['quick_search_popsearch_input_border_color'] = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_BORDER_COLOR');
        $fields_values['quick_search_popsearch_input_bg_color'] = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_BG_COLOR');
        $fields_values['quick_search_popsearch_input_color'] = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_COLOR');
        $fields_values['quick_search_header_style'] = (int)Configuration::get('ST_QUICK_SEARCH_HEADER_STYLE');
        $fields_values['quick_search_header_icon_size'] = (int)Configuration::get('ST_QUICK_SEARCH_HEADER_ICON_SIZE');
        $fields_values['quick_search_header_text_size'] = (int)Configuration::get('ST_QUICK_SEARCH_HEADER_TEXT_SIZE');
        
        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            $fields_values['placeholder'][$language['id_lang']] = Configuration::get('ST_QUICK_SEARCH_PLACEHOLDER', $language['id_lang']);
        }

        return $fields_values;
    }
	
    /*public function hookDisplayMobileBar($params)
    {
        if(Configuration::get('ST_QUICK_SEARCH_MOBILE'))
        {
            $this->smarty->assign($this->getWidgetVariables('displayMobileBar', $params));
            return $this->display(__FILE__, 'stsearchbar.tpl');
        }
        else
            return $this->display(__FILE__, 'stsearchbar-mobilebar.tpl');
    }
    public function hookDisplayMobileBarCenter($params){
        return $this->hookDisplayMobileBar($params);
    }
    public function hookDisplayMobileBarLeft($params){
        return $this->hookDisplayMobileBar($params);
    }
    public function hookDisplayMobileBarBottom($params){
        return $this->hookDisplayMobileBar($params);
    }*/

    public function hookDisplaySideBar($params)
    {
        $this->smarty->assign($this->getWidgetVariables(null, $params));
        return $this->display(__FILE__, 'stsearchbar-side.tpl');
    }
    protected function stGetCacheId($key='')
	{
		$cache_id = parent::getCacheId();
		return $cache_id.'_'.$key;
	}
    
    private function _check_single()
    {
        $msg = '';
        $count = 0;
        foreach($this->_hooks AS $values)
            foreach($values AS $value)
                if (isset($value['sin']) && $value['sin'])
                {
                    $id_hook = Hook::getIdByName($value['id']);
                    if ($id_hook && Hook::getModulesFromHook($id_hook, $this->id))
                        $msg .= '<br/>'.++$count.') '.$value['name'];
                }
        return $count > 1 ? $this->displayError($this->getTranslator()->trans('This module was transplanted to these hooks at the same time. You should not have done that. This module can only be transplante to one of them, otherwise the front page might be messed up.', array(), 'Modules.Stsearchbar.Admin').$msg): '';
    }

    public function hookDisplayHeader($params)
    {
        $this->context->controller->addJS($this->_path.'views/js/jquery.autocomplete.js');
        $this->context->controller->addJS($this->_path.'views/js/stsearchbar.js');
        
        if ((!Module::isInstalled('steasybuilder') || !Module::isEnabled('steasybuilder')) && !$this->isCached('module:stsearchbar/views/templates/hook/header.tpl', $this->getCacheId()))
        {   
            $custom_css = '';
            if (Configuration::get('ST_QUICK_SEARCH_WIDTH'))
                $custom_css .= '#st_header .search_widget_block{width:'.Configuration::get('ST_QUICK_SEARCH_WIDTH').';}';
            if (Configuration::get('ST_QUICK_SEARCH_AS_HEIGHT'))
                $custom_css .= '.autocomplete-suggestions{max-height:'.(int)Configuration::get('ST_QUICK_SEARCH_AS_HEIGHT').'px;}';
            if ($quick_search_input_border_color = Configuration::get('ST_QUICK_SEARCH_INPUT_BORDER_COLOR'))
            {
                $custom_css .= '#st_header .search_widget_form_inner.input-group-with-border{border-color:'.$quick_search_input_border_color.';}';
                $custom_css .= '#st_header .search_widget_btn.btn{border-color:'.$quick_search_input_border_color.';}';
            }
            if ($quick_search_border_radius = Configuration::get('ST_QUICK_SEARCH_BORDER_RADIUS'))
            {
                $custom_css .= '#st_header .search_widget_form_inner.input-group-with-border{border-radius:'.$quick_search_border_radius.'px;}';
                $custom_css .= '#st_header .search_widget_form_inner.input-group-with-border .form-control{border-top-left-radius:'.$quick_search_border_radius.'px;border-bottom-left-radius:'.$quick_search_border_radius.'px;}';
                $custom_css .= '.is_rtl #st_header .search_widget_form_inner.input-group-with-border .form-control{border-radius:'.$quick_search_border_radius.'px;border-top-left-radius:0;border-bottom-left-radius:0;}';
                $custom_css .= '#st_header .search_widget_btn{border-top-right-radius:'.$quick_search_border_radius.'px;border-bottom-right-radius:'.$quick_search_border_radius.'px;}';
            }
            $quick_search_popsearch_height = (int)Configuration::get('ST_QUICK_SEARCH_POPSEARCH_HEIGHT');
            if ($quick_search_popsearch_height)
                $custom_css .= '.popsearch{height:'.$quick_search_popsearch_height.'px;}';
            $quick_search_popsearch_input_height = (int)Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_HEIGHT');
            if($quick_search_popsearch_input_height>$quick_search_popsearch_height)
                $quick_search_popsearch_input_height = $quick_search_popsearch_height;
            if ($quick_search_popsearch_height)
                $custom_css .= '.popsearch input.form-control.search_widget_text{height:'.$quick_search_popsearch_input_height.'px;}';
            if ($quick_search_popsearch_input_fs = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_FS'))
                $custom_css .= '.popsearch input.form-control.search_widget_text{font-size:'.$quick_search_popsearch_input_fs.'px;}';
            if ($quick_search_popsearch_bg = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_BG'))
                $custom_css .= '.popsearch{background:'.$quick_search_popsearch_bg.';}';
            if ($quick_search_popsearch_input_border_color = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_BORDER_COLOR'))
            {
                $custom_css .= '.popsearch .search_widget_form_inner.input-group-with-border{border-color:'.$quick_search_popsearch_input_border_color.';}';
                $custom_css .= '.popsearch .search_widget_btn.btn{border-color:'.$quick_search_popsearch_input_border_color.';}';
            }
            if ($quick_search_popsearch_input_bg_color = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_BG_COLOR')){
                $custom_css .= '.popsearch .search_widget_text{background-color:'.$quick_search_popsearch_input_bg_color.';}';
                $custom_css .= '.popsearch .search_widget_btn{background-color:'.$quick_search_popsearch_input_bg_color.';}';
            }
            if ($quick_search_popsearch_input_color = Configuration::get('ST_QUICK_SEARCH_POPSEARCH_INPUT_COLOR')){
                $custom_css .= '.popsearch .search_widget_text{color:'.$quick_search_popsearch_input_color.';}';
                $custom_css .= '.popsearch .search_widget_btn{color:'.$quick_search_popsearch_input_color.';}';
                $custom_css .= '.popsearch .popsearch_close{color:'.$quick_search_popsearch_input_color.';}';
                $color_arr = self::hex2rgb($quick_search_popsearch_input_color);
                $custom_css .= '.popsearch .search_widget_text::placeholder{color:rgba('.$color_arr[0].', '.$color_arr[1].', '.$color_arr[2].', 0.8);}';
            }

            if($icon_size = Configuration::get('ST_QUICK_SEARCH_HEADER_ICON_SIZE')){
                $custom_css .= '.stsearchbar_link .header_item .header_icon_btn_icon i{font-size:'.$icon_size.'px;}';
            }
            if($text_size = Configuration::get('ST_QUICK_SEARCH_HEADER_TEXT_SIZE')){
                $custom_css .= '.stsearchbar_link .header_item .header_icon_btn_text{font-size:'.$text_size.'px;}';
            }
            if($custom_css)
                $this->smarty->assign('custom_css', preg_replace('/\s\s+/', ' ', $custom_css));
        }

        Media::addJsDef(array(
            'quick_search_as_size' => (int)Configuration::get('ST_QUICK_SEARCH_AS_SIZE'),
            'quick_search_as_min' => (int)Configuration::get('ST_QUICK_SEARCH_AS_MIN'),
            'quick_search_as' => (bool)Configuration::get('ST_QUICK_SEARCH_AS'),
        ));
        return $this->fetch('module:stsearchbar/views/templates/hook/header.tpl', $this->getCacheId());
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
    public function getWidgetVariables($hookName, array $configuration = [])
    {
        $widgetVariables = array(
            'search_controller_url' => $this->context->link->getPageLink('search', null, null, null, false, null, true),
            'quick_search_simple' => ($hookName=='displayMobileNav' || $hookName=='displaySearch' || $hookName=='displaySideBar') ? 0 : Configuration::get('ST_QUICK_SEARCH_SIMPLE'),
            'quick_search_as_results' => Configuration::get('ST_QUICK_SEARCH_AS_RESULTS'),
            'quick_search_header_style' => (int)Configuration::get('ST_QUICK_SEARCH_HEADER_STYLE'),
            'quick_search_placeholder' => Configuration::get('ST_QUICK_SEARCH_PLACEHOLDER', $this->context->language->id),
        );

        if (!array_key_exists('search_string', $this->context->smarty->getTemplateVars())) {
            $widgetVariables['search_string'] = '';
        }

        return $widgetVariables;
    }

    public function renderWidget($hookName, array $configuration = [])
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->fetch($this->templateFile[0]);
    }

    public function get_prefix()
    {
        return 'ST_QUICK_SEARCH_';
    }

    public function hookDisplayFullWidthBottom($params)
    {
        $this->smarty->assign($this->getWidgetVariables(null, $params));
        return $this->fetch('module:stsearchbar/views/templates/hook/popsearch.tpl');
    }
}

