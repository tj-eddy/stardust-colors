<?php

use Monolog\Utils;

if (!defined('_PS_VERSION_'))
	exit;

class WBPModel {

	public static function getPostsFromRss($url, $limit = 500, $category_filters = array(), $hook_options = array())
	{
		
		//d($_SERVER);
		$blog_items_collection = array();
		$feed_url = $url;
		
		if (!empty($hook_options->src))
			$feed_url = $hook_options->src;

		if (!function_exists('curl_init')) die('curl must be installed to fetch blog posts');

		$ch = curl_init($feed_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$data = curl_exec($ch);
		
		curl_close($ch);
		//$doc = new SimpleXmlElement($data, LIBXML_NOCDATA);
		$data = str_replace('<media:thumbnail', '<thumbnail', $data);
		$doc = simplexml_load_string($data, 'SimpleXmlElement', LIBXML_NOCDATA);
		$namespace = 'http://search.yahoo.com/mrss/'; //media tags

		if (empty($doc->channel->item)) return array();

		$cnt = count($doc->channel->item);
		$added = 0;
		for ($i = 0; $i < $cnt; $i++)
		{
			$item = new WBPBlogItem();
			$item->url = (string)$doc->channel->item[$i]->link;
			$item->title = utf8_encode((string)$doc->channel->item[$i]->title);
			$item->description = utf8_encode((string)$doc->channel->item[$i]->description);
			$item->pub_date = (string)$doc->channel->item[$i]->pubDate;
			$item->timestamp = strtotime($item->pub_date);
			$item->featured_image = (string)$doc->channel->item[$i]->image;

			/* if featured image tag not found, see if we can fetch using media rss tags */
			if ($item->featured_image == '')
			{
				if (isset($doc->channel->item[$i]->thumbnail['url']))
				{
					$image_link = $doc->channel->item[$i]->thumbnail['url'];
					$image_link = preg_replace('/([?&])'.'w'.'=[^&]+(&|$)/', '$1', $image_link);  //remove the w= url param
					$item->featured_image = trim($image_link, '?');
				}
				elseif (isset($doc->channel->item[$i]->children($namespace)->thumbnail[0]))
				{
					$image = $doc->channel->item[$i]->children($namespace)->thumbnail[0]->attributes();
					$image_link = $image['url'];
					$image_link = preg_replace('/([?&])'.'w'.'=[^&]+(&|$)/', '$1', $image_link);  //remove the w= url param
					$item->featured_image = trim($image_link, '?');
				}
			}

			/* another attempt */
			if ($item->featured_image == '' && isset($doc->channel->item[$i]->enclosure['url'][0]))
				$item->featured_image = (string)$doc->channel->item[$i]->enclosure['url'][0];

			/* Filter by categories if specified in arguments */
			if (count($category_filters) > 0)
			{
				if ($doc->channel->item[$i]->category)
				{
					$category = $doc->channel->item[$i]->category;
					$count_category = count($doc->channel->item[$i]->category);
					for ($x = 0; $x <= $count_category; $x++)
					{
						if (in_array($category[$x], $category_filters))
						{
							$blog_items_collection[] = $item;
							$added++;
						}						
					}
				}
			}
			else
			{
				$added++;
				$blog_items_collection[] = $item;
			}
			if ($added > $limit) break;
		}
		return $blog_items_collection;
	}

	public static function savePostsToHook($blog_post_collection, WBPHookOptions $hook_options, $id_lang)
	{
		$blog_posts_to_save = json_encode(array_slice($blog_post_collection, 0, $hook_options->limit));
		//$cache_id = md5('WBP_'.$hook_options->hook_name.(int)$id_lang);
		$cache_id = WBPPostsHelper::getCacheID($hook_options->hook_name, $hook_options, $id_lang);

		$options_cache_id = md5('WBPOptions_'.$hook_options->hook_name);
		Configuration::updateValue($cache_id, $blog_posts_to_save);
		if (_PS_VERSION_ < 1.6)
			Configuration::updateValue($options_cache_id, json_encode($hook_options));
		else
			Configuration::updateValue('WBPOptions_'.$options_cache_id, json_encode($hook_options));
	}

	/**
	 * @var WBPHookOptions
	 */
	public static function getHookOptions($hook_name)
	{
		$hook_options = new WBPHookOptions();
		$cache_id = md5('WBPOptions_'.$hook_name);
		if (_PS_VERSION_ < 1.6)
			$options = json_decode(Configuration::get($cache_id));
		else
			$options = json_decode(Configuration::get('WBPOptions_'.$cache_id));

		if (isset($options))
		{
			// make sure we cast stored object as WBPHookOptions when returning
			$hook_options->hook_name = $options->hook_name;
			$hook_options->enabled = $options->enabled;
			$hook_options->category_filter = $options->category_filter;
			$hook_options->limit = $options->limit;
			return $hook_options;
		}
		else
			return $hook_options;
	}

    public static function getRecentsPostByDB( $limit ){ 
        define('WP_USE_THEMES', false);
        define('WP_SCRIPT_ONLY', true);
        
        require(_PS_ROOT_DIR_ . "/blog/wp-blog-header.php");
        header("HTTP/1.1 200 OK");
        //header("Status: 200 All rosy");

        $args = array(
        	'numberposts' => $limit,
        	'offset' => 0,
        	'category' => 0,
        	'orderby' => 'post_date',
        	'order' => 'DESC',
        	'include' => '',
        	'exclude' => '',
        	'meta_key' => '',
        	'meta_value' =>'',
        	'post_type' => 'post',
        	'post_status' => 'publish',
        	'suppress_filters' => true
        );
        $recent_posts = wp_get_recent_posts( $args);
        $posts = array();
        foreach($recent_posts as $post){
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post['ID'] ));
            $posts[] = array(
                'url' => get_permalink($post['ID']),
                'category' => get_the_category($post['ID']),
                'post_image' =>$image[0],
                'title' => $post['post_title'],
                'pub_date' => $post['post_date'],
                'timestamp' => strtotime($post['post_date_gmt']),
                'description' => preg_replace('/(\s\s+|\t|\n)/', ' ',strip_tags(($post['post_content'])))
            );
            dump($posts);
        }die;
        return json_decode(json_encode($posts));
    }


    public static function getCachedPostsForHook($hook_name, $id_lang, $params = array()){
        if ($hook_name == 'displayWBPPostsCustom')
		{
			$hook_options = self::getHookOptions($hook_name);
			if (!empty($params['categories'])) $hook_options->category_filter = $params['categories'];
			if (!empty($params['limit'])) $hook_options->category_filter = $params['limit'];
		}
		else
		{
			$hook_options = new WBPHookOptions();
			$hook_options->hook_name = $hook_name;
			$hook_options->enabled = 1;
			$hook_options->category_filter = (!empty($params['categories']) ? $params['categories'] : '');
			$hook_options->limit = (!empty($params['limit']) ? (int)$params['limit'] : '');
			$hook_options->src = (!empty($params['src']) ? $params['src'] : '');
		}

		$languages = Language::getLanguages();
		$posts = self::getRecentsPostByDB( $hook_options->limit );
		// not yet cached, fetch from rss
		if (!empty($posts) && is_array($posts))
		{
			$posts = array_slice($posts, 0, $hook_options->limit);
			foreach ($posts as &$post) 
			{
				//$post->title = utf8_decode($post->title);
				//$post->description = utf8_decode($post->description);
				$post->pubDate  = utf8_decode($post->pub_date) ;
			}
		}
        return $posts;
    }
    /*
	public static function getCachedPostsForHook($hook_name, $id_lang, $params = array())
	{
		if ($hook_name != 'displayWBPPostsCustom')
		{
			$hook_options = self::getHookOptions($hook_name);
			if (!empty($params['categories'])) $hook_options->category_filter = $params['categories'];
			if (!empty($params['limit'])) $hook_options->category_filter = $params['limit'];
		}
		else
		{
			$hook_options = new WBPHookOptions();
			$hook_options->hook_name = $hook_name;
			$hook_options->enabled = 1;
			$hook_options->category_filter = (!empty($params['categories']) ? $params['categories'] : '');
			$hook_options->limit = (!empty($params['limit']) ? (int)$params['limit'] : '');
			$hook_options->src = (!empty($params['src']) ? $params['src'] : '');
		}

		$languages = Language::getLanguages();
		$cache_id = WBPPostsHelper::getCacheID($hook_name, $hook_options, $id_lang);
		$posts = json_decode(Configuration::get($cache_id));
		
		// not yet cached, fetch from rss
		if (empty($posts))
		{
			foreach ($languages as $language)
			{
				$blog_items_collection = WBPModel::getPostsFromRss(Configuration::get('WBP_blog_url', $language['id_lang']), 500, WBPPostsHelper::explodeCategoryFilter($hook_options->category_filter), $hook_options);
				WBPModel::savePostsToHook($blog_items_collection, $hook_options, $language['id_lang']);
				$posts = $blog_items_collection;
			}
		}
		if (!empty($posts) && is_array($posts))
		{
			$posts = array_slice($posts, 0, $hook_options->limit);
			foreach ($posts as &$post) 
			{
				$post->title = utf8_decode($post->title);
				$post->description = utf8_decode($post->description);
				$post->pubDate  = utf8_decode($post->pub_date) ;
			}
		}
		return $posts;
	}
*/
	public static function getAllHookNames()
	{
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
		return $hooks_list;
	}

	public static function deleteCachedPosts()
	{
		DB::getInstance()->delete('configuration', 'name LIKE "WBPP_%"');
	}

}