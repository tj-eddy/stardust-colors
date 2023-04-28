<?php
class WBPBlogItem
{
	public $url = '';
	public $title = '';
	public $pub_date = '';
	public $timestamp = 0;
	public $description = '';
	public $featured_image = '';
}

class WBPHookOptions
{
	public $hook_name = '';
	public $enabled = false;
	public $limit = 0;
	public $category_filter = '';
}
