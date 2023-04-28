<?php

class WBPCronController extends WBPControllerCore
{

	public function run($module_file_path)
	{
		$hooks_collection = WBPModel::getAllHookNames();
		$languages = Language::getLanguages();

		if (!empty($hooks_collection))
		{
			foreach ($hooks_collection as $hook)
			{
				if ($hook['status'] == 1)
				{
					$hook_options = WBPModel::getHookOptions($hook['hook_name']);
					foreach ($languages as $language)
					{
						$blog_items_collection = WBPModel::getPostsFromRss(Configuration::get('WBP_blog_url', $language['id_lang']), 500, WBPConfigHookController::explodeCategoryFilter($hook_options->category_filter));
						WBPModel::savePostsToHook($blog_items_collection, $hook_options, $language['id_lang']);
					}
				}
			}
		}
		die('cron task complete');
	}
}