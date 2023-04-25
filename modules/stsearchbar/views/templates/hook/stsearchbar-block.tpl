<div class="search_widget_block {if isset($headerclass)} {$headerclass} {/if}">
<div class="search_widget" data-search-controller-url="{$search_controller_url}">
	<form method="get" action="{$search_controller_url}" class="search_widget_form">
		<input type="hidden" name="controller" value="search">
		<div class="search_widget_form_inner input-group round_item js-parent-focus input-group-with-border">
	      <input type="text" class="form-control search_widget_text js-child-focus" name="s" value="{$search_string}" placeholder="{if isset($quick_search_placeholder) && $quick_search_placeholder}{$quick_search_placeholder}{else}{l s='Search our catalog' d='Shop.Theme.Panda'}{/if}">
	      <span class="input-group-btn">
	        <button class="btn btn-search btn-no-padding btn-spin search_widget_btn link_color icon_btn" type="submit"><i class="fto-search-1"></i></button>
	      </span>
	    </div>

	</form>
	<div class="search_results {if $quick_search_as_results&1} search_show_img {/if}{if $quick_search_as_results&2} search_show_name {/if}{if $quick_search_as_results&4} search_show_price {/if}"></div>
	<a href="javascript:;" title="{l s='More products.' d='Shop.Theme.Panda'}" rel="nofollow" class="display_none search_more_products go">{l s='Click for more products.' d='Shop.Theme.Panda'}</a>
	<div class="display_none search_no_products">{l s='No produts were found.' d='Shop.Theme.Panda'}</div>
</div>
</div>
