<?php

class WBPConfigHookController extends WBPControllerCore {

	private function renderMain()
	{
		$columns = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('General Options'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'name' => 'wbp_enabled',
						'type' => 'select',
						'label' => 'Show blog posts for this hook?',
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
					array(
						'name' => 'wbp_limit',
						'type' => 'text',
						'label' => 'How many blog posts to display?',
						'class' => 'fixed-width-xs',
					),
					array(
						'name' => 'wbp_category_filter',
						'type' => 'text',
						'label' => 'Category Filter'
					),
					array(
						'name' => 'wbp_hook_name',
						'type' => 'hidden'
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'class' => 'btn btn-default pull-right',
					'name' => 'submit_',
				)
			),
		);
		$this->setupHelperForm();
		//$this->helper_form->token = Tools::getAdminTokenLite('AdminModules');
		$this->helper_form->currentIndex .= '&form_wbp_hook_options_process&updateconfiguration'; //help route the form submit back to this controller

		/* Populate the form values */
		$hook_options = WBPModel::getHookOptions(Tools::getValue('hook_name'));

		$this->helper_form->tpl_vars['fields_value']['wbp_enabled'] = $hook_options->enabled;
		$this->helper_form->tpl_vars['fields_value']['wbp_limit'] = $hook_options->limit;
		$this->helper_form->tpl_vars['fields_value']['wbp_category_filter'] = $hook_options->category_filter;
		$this->helper_form->tpl_vars['fields_value']['wbp_hook_name'] = Tools::getValue('hook_name');
		return $this->helper_form->generateForm(array($columns));
	}

	public static function explodeCategoryFilter($category_filter_string)
	{
		$category_filter_string = trim($category_filter_string, ' ');
		$category_filter_string = trim($category_filter_string, ',');
		$category_filter_array = explode(',', $category_filter_string);

		if (is_array($category_filter_array))
		{
			foreach ($category_filter_array as &$item)
				$item = trim($item, ' ');
			return $category_filter_array;
		}
		else
			return array();
	}

	private function processHookOptions()
	{
		$hook_options = new WBPHookOptions();
		$hook_options->hook_name = Tools::getValue('wbp_hook_name');
		$hook_options->enabled = (int)Tools::getValue('wbp_enabled');
		$hook_options->category_filter = Tools::getValue('wbp_category_filter');
		$hook_options->limit = (int)Tools::getValue('wbp_limit');
				
		$languages = Language::getLanguages();

		foreach ($languages as $language)
		{
			$blog_items_collection = WBPModel::getPostsFromRss(Configuration::get('WBP_blog_url', $language['id_lang']), 500, $this->explodeCategoryFilter($hook_options->category_filter));
			WBPModel::savePostsToHook($blog_items_collection, $hook_options, $language['id_lang']);
		}
	}

	private function processBlogUrl()
	{
		$languages = Language::getLanguages();
		$urls = array();
		foreach ($languages as $language)
			$urls[$language['id_lang']] = Tools::getValue('wbp_blog_url_'.$language['id_lang']);
		Configuration::updateValue('WBP_blog_url', $urls);
		Configuration::updateValue('wbp_link_new_tab', (int)Tools::getValue('wbp_link_new_tab'));
	}

	public function clearCache()
	{
		WBPModel::deleteCachedPosts();
	}

	public function route()
	{
		//default (display configuration page)

		if (Tools::getIsset('form_wbp_hook_options_process'))
		{
			$this->processHookOptions();
			$this->redirect('');
		}

		if (Tools::getIsset('form_wbp_blog_url_process'))
		{
			$this->processBlogUrl();
			$this->redirect('');
		}

		if (Tools::getIsset('form_wbp_clear_cache'))
		{
			$this->clearCache();
			$this->redirect('');
		}
		return $this->renderMain();
	}

}
