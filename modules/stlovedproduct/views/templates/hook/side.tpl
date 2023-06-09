{*
* 2007-2014 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- MODULE st stlovedproduct -->
<nav class="st-menu" id="side_loved">
	<div class="st-menu-header flex_container">
		<h3 class="st-menu-title">{l s='Loved' d='Shop.Theme.Panda'}</h3>
    	<a href="javascript:;" class="close_right_side" title="{l s='Close' d='Shop.Theme.Panda'}"><i class="fto-cancel-2"></i></a>
	</div>
	<div id="side_loved_block" class="pad_10">
			{assign var='has_love_items' value=0}
			{if isset($products) && count($products)}
        		<h3 class="page_heading">{l s='Loved products' d='Shop.Theme.Panda'}</h3>
		        {$has_love_items=1}  
		        <div class="base_list_line medium_list">
				{foreach $products as $product}
		            {include file="catalog/_partials/miniatures/product-slider-item-compact.tpl" lazy_load=false}
		        {/foreach}	
		        </div>
			{/if}
			{if isset($blogs) && count($blogs)}
        		<h3 class="page_heading">{l s='Loved articles' d='Shop.Theme.Panda'}</h3>
		        {$has_love_items=1}  
		        <div class="base_list_line medium_list">
				{foreach $blogs as $blog}
		            {include file="module:stblog/views/templates/slider/simple.tpl" lazy_load=false}
		        {/foreach}
		        </div>
			{/if}
			{if !$has_love_items}
				<div class="loved_products_no_products">
					{l s='No items' d='Shop.Theme.Panda'}
				</div>
			{else}
		        <div class="text-center m-t-1">
		        	<a href="{url entity='module' name='stlovedproduct' controller='myloved'}" class="btn btn-default btn-more-padding" title="{l s='View all' d='Shop.Theme.Panda'}" rel="nofollow">{l s='View all' d='Shop.Theme.Panda'}</a>
		        </div>
			{/if}
	</div>
</nav>
<!-- /MODULE st stlovedproduct -->