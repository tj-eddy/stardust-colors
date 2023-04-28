<?php
if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'/wordpressblogposts/lib/bootstrap.php');

class WordpressBlogPosts extends Module
{
	/** @var WBPConfigController */
	protected $controller_config;

	/** @var WBPFrontController */
	protected $controller_front;

	public $file = __FILE__;

	public function __construct()
	{
		$this->name = 'wordpressblogposts';
		$this->tab = 'others';
		$this->version = '1.1.5';
		$this->author = 'Musaffar Patel';
		$this->need_instance = 0;
		$this->module_key = '66f63d32ef2caee6f1ffdfce179f8766';

		parent::__construct();
		$this->displayName = $this->l('Wordpress Blog Posts');
		$this->description = $this->l('Display latest blog posts from your wordpress blog in Prestashop');
		if (_PS_VERSION_ >= '1.5') $this->bootstrap = true;
		$this->_initialiseControllers();
	}

	private function _initialiseControllers()
	{
		$this->controller_config = new WBPConfigController($this);
		$this->controller_front = new WBPFrontController($this);
	}

	public function install()
	{
		if (parent::install() == false
			|| !$this->registerHook('displayHome')
			|| !$this->registerHook('displayFooterTertiary')
			|| !$this->registerHook('displayLeftColumn')
			|| !$this->registerHook('displayRightColumn')
			|| !$this->registerHook('displayWBPPostsCustom')
			|| !$this->install_module())
			return false;
		return true;
	}

	public function uninstall()
	{
		parent::uninstall();
	}

	public function install_module()
	{
		return true;
	}

	public function getContent()
	{
		$return = '';

		if (Tools::getIsset('configure') && Tools::getValue('configure') == 'wordpressblogposts')
		{
			$return .= $this->controller_config->route();
			return $return;
		}
	}

	/* Hooks */

	public function hookDisplayHome($params)
	{
		return $this->controller_front->renderDisplayHome($params, __FILE__);
	}

	public function hookDisplayFooterTertiary($params){
		return $this->controller_front->renderDisplayHome($params, __FILE__);
	}

	public function hookDisplayLeftColumn($params)
	{
		return $this->controller_front->renderDisplayLeftColumn($params, __FILE__);
	}

	public function hookDisplayRightColumn($params)
	{
		return $this->controller_front->renderDisplayLeftColumn($params, __FILE__);
	}

	public function hookDisplayWBPPostsCustom($params)
	{
		return $this->controller_front->renderDisplayCustom($params, __FILE__);
	}

}