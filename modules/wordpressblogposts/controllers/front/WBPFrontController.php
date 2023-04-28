<?php

class WBPFrontController extends WBPControllerCore
{

	protected $sibling;

	public function __construct(&$sibling = null)
	{
		if ($sibling !== null) $this->sibling = &$sibling;
		parent::__construct($sibling);
	}

	private function addMedia()
	{
		$this->sibling->context->controller->addCSS($this->sibling->_path.'views/css/wordpressblogposts.css');
	}
	
	private function _cleanPosts(&$posts)
	{
		foreach ($posts as $post)
		{
			$post->description = html_entity_decode($post->description);
			$post->title = html_entity_decode($post->title);
		}
	}
	

	public function renderDisplayHome($params, $module_file)
	{
		$this->addMedia();
		$options = WBPModel::getHookOptions('displayHome');
		if ($options->enabled)
		{
			$posts = WBPModel::getCachedPostsForHook('displayHome', Context::getContext()->language->id);
			foreach($posts as &$post){
				$post->{'dateLang'} = Tools::displayDate(date("Y-m-d", $post->timestamp), Context::getContext()->language->id, false);
			}
			$this->_cleanPosts($posts);
			$this->sibling->smarty->assign(array(
				'wbp_posts' => $posts,
				'wbp_link_new_tab' => Configuration::get('wbp_link_new_tab')
			));
			return $this->sibling->display($module_file, 'views/templates/front/hook_home.tpl');
		}
	}

	public function renderDisplayLeftColumn($params, $module_file)
	{
		$this->addMedia();
		$options = WBPModel::getHookOptions('displayLeftColumn');
		if ($options->enabled)
		{
			$posts = WBPModel::getCachedPostsForHook('displayLeftColumn', Context::getContext()->language->id);
			$this->_cleanPosts($posts);
			$this->sibling->smarty->assign(array(
				'wbp_posts' => $posts
			));
			return $this->sibling->display($module_file, 'views/templates/front/hook_left_column.tpl');
		}
	}

	public function renderDisplayCustom($params, $module_file)
	{
		$limit = 0;
		$template = 'hook_home.tpl';
		
		if (!empty($params['template']))
			$template = $params['template'];


		$this->addMedia();
		$posts = WBPModel::getCachedPostsForHook('displayWBPPostsCustom', Context::getContext()->language->id, $params);
		$this->_cleanPosts($posts);

		if (empty($posts)) return '';


		if (!empty($params['limit'])) $limit = (int)$params['limit'];
		if ($limit > count($posts)) $limit = count($posts);
			else $limit = 999;
		$posts = array_slice($posts, 0, $limit);
		$this->sibling->smarty->assign(array(
			'wbp_posts' => $posts,
			'wbp_link_new_tab' => Configuration::updateValue('wbp_link_new_tab', (int)Tools::getValue('wbp_link_new_tab'))
		));
		if (!empty($params['template'])) $template = $params['template'];
		return $this->sibling->display($module_file, 'views/templates/front/'.$template);
	}

}