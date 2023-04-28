<?php


class WBPPostsHelper
{

	public static function getCacheID($hook_name, WBPHookOptions $hook_options, $id_lang)
	{
		if (!empty($hook_options))
			$params_serialised = json_encode($hook_options);
		else
			$params_serialised = '';

		if (_PS_VERSION_ < 1.6)
			$cache_id = md5($hook_name.$params_serialised.$id_lang);
		else
			$cache_id = 'WBPP_'.md5('WBP_'.$hook_name.$params_serialised.$id_lang);
		return $cache_id;
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

}