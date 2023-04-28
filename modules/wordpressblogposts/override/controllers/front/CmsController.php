<?php

class CmsController extends CmsControllerCore
{
	public function initContent()
	{
		$params = array();
		$wbp_module = Module::getInstanceByName('wordpressblogposts');

		preg_match_all('/\[wordpressblogposts([^\]]*)\]/', $this->cms->content, $matches);

		if (!empty($matches[1]))
		{
			$i = 0;
			foreach ($matches[1] as $match)
			{
				$params_tmp = explode(',', $match);
				if (!empty($params_tmp))
				{
					foreach ($params_tmp as $param)
					{
						$arr_parts = explode('=', $param);
						$key = trim($arr_parts[0], ' ');
						$value = trim($arr_parts[1], "'");
						$params[$key] = $value;
					}
					$this->cms->content = str_replace($matches[0][$i], $posts = $wbp_module->hookDisplayWBPPostsCustom($params), $this->cms->content);
				}
				$i++;
			}
		}
		parent::initContent();
	}
}