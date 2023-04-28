<?php

class WBPConfigController extends WBPControllerCore
{
	public $sibling;

	/** @var WBPConfigHookController */
	private $controller_config_hook;

	public function __construct($sibling)
	{
		parent::__construct($sibling);
		$this->initialiseControllers();
	}

	private function initialiseControllers()
	{
		$this->controller_config_hook = new WBPConfigHookController($this->sibling);
	}

	public function renderEmptyForm()
	{
		return '<form></form>';
	}

	private function _getShortCodeGeneratorWidget()
	{
		$this->sibling->smarty->assign(array(
			'controller_name' => Context::getContext()->controller->php_self
		));
		return $this->sibling->display($this->sibling->file, 'views/templates/admin/shortcode.tpl');
	}

	public function renderCmsTipForm()
	{
		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('CMS short code generator'),
					'icon' => 'icon-cogs'
				),
				'description' => 'Generate short code for your CMS pages here, copy and paste iinto your CMS page content to display blog posts',
				'input' => array(
					array(
						'name' => 'wbp_limit',
						'type' => 'text',
						'label' => 'blog post limit',
						'class' => 'fixed-width-xs',
					),
					array(
						'prefix' => 'comma seperated wordpress categories',
						'name' => 'wbp_category_filter',
						'type' => 'text',
						'label' => 'Category Filter'
					),
					array(
						'name' => '',
						'type' => 'html',
						'label' => 'Short Code',
						'html_content' => $this->_getShortCodeGeneratorWidget()
					)
				)
			)
		);
		$this->setupHelperForm();
		//$this->helper_form->token = Tools::getAdminTokenLite('AdminModules');
		$this->helper_form->currentIndex .= '&form_wbp_hook_options_process&updateconfiguration'; //help route the form submit back to this controller

		/* Populate the form values */
		$this->helper_form->tpl_vars['fields_value']['wbp_limit'] = 0;
		$this->helper_form->tpl_vars['fields_value']['wbp_category_filter'] = '';
		return $this->helper_form->generateForm(array($fields));
	}

	private function renderBlogUrlForm()
	{
		$languages = $this->sibling->context->controller->getLanguages();
		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Blog URL'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'name' => 'wbp_blog_url',
						'type' => 'text',
						'label' => 'Blog URL',
						'lang' => true
					),

					array(
						'name' => 'wbp_link_new_tab',
						'type' => 'select',
						'label' => 'Open blog posts in new window/tab?',
						'class' => 'fixed-width-xs',
						'options' => array(
							'query' => array(
								array(
									'id_option' => '1',
									'name' => 'Yes'
								),
								array(
									'id_option' => '0',
									'name' => 'No'
								)
							),
							'id' => 'id_option',
							'name' => 'name'
						)
					),
				),

				'submit' => array(
					'title' => $this->l('Save'),
					'class' => 'btn btn-default pull-right',
					'action' => ''
				)
			)
		);
		$this->setupHelperForm();
		$this->helper_form->currentIndex .= '&form_wbp_blog_url_process&updateconfiguration'; //help route the form submit back to this controller

		/* Populate the form values */
		foreach ($languages as $language)
			$this->helper_form->tpl_vars['fields_value']['wbp_blog_url'][$language{'id_lang'}] = Configuration::get('WBP_blog_url', $language['id_lang']);

		$this->helper_form->tpl_vars['fields_value']['wbp_link_new_tab'] = Configuration::get('wbp_link_new_tab');

		return $this->helper_form->generateForm(array($fields));
	}

	private function rendercacheForm()
	{
		$this->setupHelperForm();
		$this->helper_form->currentIndex .= '&updateconfiguration&form_wbp_clear_cache';

		$fields = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Cache'),
					'icon' => 'icon-list'
				),
			'submit' => array(
				'title' => $this->l('Clear cached posts'),
				'class' => 'btn btn-primary pull-left',
				'name' => 'submit_',
			)),
		);
		$this->helper_form->tpl_vars['fields_value']['id_list'] = Tools::getValue('id_list');
		return $this->helper_form->generateForm(array($fields));
	}


	private function renderHooksList()
	{
		$this->setupHelperList('Displayable Hooks');

		$hooks_list = array(
			array(
				'hook_name' => 'displayHome',
				'status' => WBPModel::getHookOptions('displayHome')->enabled
			),
			array(
				'hook_name' => 'displayLeftColumn',
				'status' => WBPModel::getHookOptions('displayLeftColumn')->enabled
			),
			array(
				'hook_name' => 'displayRightColumn',
				'status' => WBPModel::getHookOptions('displayRightColumn')->enabled
			),
			array(
				'hook_name' => 'displayWBPPostsCustom',
				'status' => WBPModel::getHookOptions('displayWBPPostsCustom')->enabled
			),
		);

		$columns = array(
			'hook_name' => array(
				'title' => $this->l('Display Hook'),
				'width' => 140,
				'type' => 'text',
			),
			'status' => array(
				'title' => $this->l('Status'),
				'width' => 140,
				'type' => 'text',
			)
		);

		$this->helper_list->identifier = 'hook_name';
		$this->helper_list->show_toolbar = false;
		$this->helper_list->actions = array('edit', 'delete');
		$this->helper_list->simple_header = false;
		$return = '';
		$return .= $this->helper_list->generateList($hooks_list, $columns);
		return $return;
	}

	public function route()
	{
		if (Tools::getIsset('updateconfiguration') || Tools::getIsset('hook_name'))
			return $this->controller_config_hook->route();

		// default (config home page)
		$return = '';
		$return .= $this->rendercacheForm();
		$return .= $this->renderBlogUrlForm();
		$return .= $this->renderEmptyForm();
		$return .= $this->renderHooksList();
		$return .= $this->renderCmsTipForm();
		return $return;
	}
}