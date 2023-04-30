<!-- MODULE st stshoppingcart -->
<div class="blockcart dropdown_wrap top_bar_item shopping_cart_style_{$block_cart_style} {if $block_cart_style==4} header_icon_btn_3 {/if} clearfix" data-refresh-url="{$refresh_url}">{strip}
  <a href="{$cart_url}" title="{l s='View my shopping cart' d='Shop.Theme.Panda'}" rel="nofollow" class="st_shopping_cart dropdown_tri header_item {if $click_on_header_cart} rightbar_tri {/if}" data-name="side_products_cart" data-direction="open_bar_{if $click_on_header_cart==2}left{else}right{/if}">
    {capture name="block_cart_info"}
      {if $block_cart_info&1}
      <span class="cart_text mar_r4">{l s='Shopping cart' d='Shop.Theme.Panda'}</span>
      {/if}
      {if $block_cart_info&2}
      <span class="item_cart">
          <span class="ajax_cart_quantity mar_r4">{$cart.products_count}</span>
          <span class="ajax_cart_product_txt mar_r4">{l s='item(s)' d='Shop.Theme.Panda'}</span>
      </span>
      {/if}
      {if ($block_cart_info&1 || $block_cart_info&2 ) && $block_cart_info&8}
      <span class="ajax_cart_split mar_r4">{l s='-' d='Shop.Theme.Panda'}</span>
      {/if}
      {if $block_cart_info&8}
      <span class="ajax_cart_total mar_r4">{$cart.totals.total.value}</span>
      {/if}
    {/capture}
    {if $block_cart_style==4 || $block_cart_style==3 || $block_cart_style==2}
      <span class="header_icon_btn_icon header_v_align_m {if $block_cart_style!=4} mar_r4 {/if}">
        <i class="fto-glyph icon_btn"></i>
        {if $block_cart_info&4}<span class="ajax_cart_quantity amount_circle {if $cart.products_count > 9} dozens {/if}">{$cart.products_count}</span>{/if}
      </span>
      <span class="header_icon_btn_text header_v_align_m">
        {$smarty.capture.block_cart_info nofilter}
      </span>
    {else}
    <div class="flex_container">
      <div class="ajax_cart_bag mar_r4 d-none">
        {if $block_cart_info&4}<span class="ajax_cart_quantity amount_circle {if $cart.products_count > 9} dozens {/if}">{$cart.products_count}</span>{/if}
        <span class="ajax_cart_bg_handle"></span>
      </div>
      {$smarty.capture.block_cart_info nofilter}
    </div>
    {/if}
  </a>
  {strip}
  {if $hover_display_cp}
  <div class="dropdown_list cart_body {if $hover_display_cp==1 && !$cart.products_count} no_show_empty {/if}">
    <div class="dropdown_box">
      {include file='module:stshoppingcart/views/templates/hook/stshoppingcart-list.tpl' from="dropdown"}
    </div>
  </div>
  {/if}
</div>
<!-- /MODULE st stshoppingcart -->
