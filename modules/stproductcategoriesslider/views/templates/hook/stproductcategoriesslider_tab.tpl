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
{*similar files homepage.tpl stblogfeaturedarticles/views/templates/hook/home.tpl stproductcategoriesslider.tpl stproductcategoriesslider_tab.tpl stfeaturedcategories.tpl ps_crossselling.tpl  ps_categroyproduct.tpl steasycontent-element-6.tpl*}
{if isset($product_categories) && count($product_categories)}
    <!-- MODULE st stproductcategoriesslider -->
<div id="pc_slider_block_container_{$hook_hash}" class="pc_slider_block_container block ">
{if isset($homeverybottom) && $homeverybottom}<div class="wide_container"><div class="container">{/if}
<div class="sttab_block sttab_2 sttab_2_3">
    <ul class="nav nav-tabs tab_lg flex_container {if $title_align==1} flex_center {else} flex_left {/if} dragscroll st_scroll_horizontal" role="tablist">
        {*to do what if the first is hidden ? *}
        {foreach $product_categories as $p_c}
        {if (isset($p_c.products) && is_array($p_c.products)) || $p_c.aw_display}
        <li class="nav-item">
          <a class="nav-link{if $p_c@first} active{/if}" data-toggle="tab" role="tab" aria-controls="category_products_container_{$p_c.id_st_product_categories_slider}" href="#category_products_container_{$p_c.id_st_product_categories_slider}" class="{if $p_c.hide_mob == 1} hidden-md-down {elseif $p_c.hide_mob == 2} hidden-lg-up {/if} text-uppercase">{$p_c.name}</a>
        </li>
        {/if}
        {/foreach}
    </ul>
    <div class="tab-content">
        {foreach $product_categories as $p_c}
        {if (isset($p_c.products) && is_array($p_c.products)) || $p_c.aw_display}
        <div role="tabpanel" class="tab-pane {if $p_c@first} active st_open {/if} {if $p_c.hide_mob == 1} hidden-md-down {elseif $p_c.hide_mob == 2} hidden-lg-up {/if} {if $p_c.video_mpfour} video_bg_block {/if}" id="category_products_container_{$p_c.id_st_product_categories_slider}" 
        {if $p_c.bg_img && $p_c.speed!=1} data-stellar-background-ratio="{$p_c.speed}" data-stellar-vertical-offset="{(int)$p_c.bg_img_v_offset}" {/if}
        {if $p_c.video_mpfour} data-vide-bg="mp4: {$p_c.video_mpfour}{if $p_c.video_webm}, video_webm: {$p_c.video_webm}{/if}{if $p_c.video_ogg}, ogv: {$p_c.video_ogg}{/if}{if $p_c.video_poster}, poster: {$p_c.video_poster}{/if}" data-vide-options="video_loop: {if $p_c.video_loop}true{else}false{/if}, video_muted: {if $p_c.video_muted}true{else}false{/if}, position: 50% {(int)$p_c.video_v_offset}%" {/if}>
            <div class="tab-pane-body {if $p_c.countdown_on} s_countdown_block{/if}">
                {if isset($p_c.products) && $p_c.products}
                    {if !$p_c.grid || $column_slider}
                    <div class="block_content products_slider">
                        {include file="catalog/slider/product-slider.tpl" products=$p_c.products
                        lazy_load=$p_c.lazy
                        direction_nav=$p_c.direction_nav
                        control_nav=$p_c.control_nav
                        image_type=$p_c.image_type
                        hide_direction_nav_on_mob=$p_c.hide_direction_nav_on_mob
                        hide_control_nav_on_mob=$p_c.hide_control_nav_on_mob
                        display_sd=$p_c.display_sd
                        display_pro_col=$p_c.display_pro_col
                        slider_items=$p_c.items_col
                        }
                    </div>
                    {assign var="reverse_direction" value=false}
                    {if isset($pc.reverse_direction)}
                        {$reverse_direction=$pc.reverse_direction}
                    {/if}
                    {assign var="pause_on_enter" value=false}
                    {if isset($pc.pause_on_enter)}
                        {$pause_on_enter=$pc.pause_on_enter}
                    {/if}
                    {include file="catalog/slider/script.tpl" block_name="#category_products_container_{$p_c.id_st_product_categories_slider}"
                    slider_s_speed=$p_c.s_speed 
                    slider_slideshow=$p_c.slideshow
                    slider_a_speed=$p_c.a_speed
                    slider_pause_on_hover=$p_c.pause_on_hover
                    slider_reverse_direction=$reverse_direction
                    slider_pause_on_enter=$pause_on_enter
                    rewind_nav=$p_c.rewind_nav
                    lazy_load=$p_c.lazy
                    direction_nav=$p_c.direction_nav
                    control_nav=$p_c.control_nav
                    slider_move=$p_c.move
                    spacing_between=$p_c.spacing_between
                    pro_per_fw=$p_c.pro_per_fw 
                    pro_per_xxl=$p_c.pro_per_xxl 
                    pro_per_xl=$p_c.pro_per_xl 
                    pro_per_lg=$p_c.pro_per_lg 
                    pro_per_md=$p_c.pro_per_md 
                    pro_per_sm=$p_c.pro_per_sm 
                    pro_per_xs=$p_c.pro_per_xs
                    autoplay_stop=!($p_c@first)
                    }

                    {elseif $p_c.grid==2}
                        {include file="catalog/listing/product-list-simple.tpl" products=$p_c.products for_f='pro_cate' id='stproductcategoriesslider_grid'
                        pro_per_fw=$p_c.pro_per_fw 
                        pro_per_xxl=$p_c.pro_per_xxl 
                        pro_per_xl=$p_c.pro_per_xl 
                        pro_per_lg=$p_c.pro_per_lg 
                        pro_per_md=$p_c.pro_per_md 
                        pro_per_sm=$p_c.pro_per_sm
                        pro_per_xs=$p_c.pro_per_xs
                        }
                    {else}
                        {include file="catalog/_partials/miniatures/list-item.tpl" products=$p_c.products class='stproductcategoriesslider_grid' for_f='pro_cate' id='stproductcategoriesslider_grid' 
                        pro_per_fw=$p_c.pro_per_fw 
                        pro_per_xxl=$p_c.pro_per_xxl 
                        pro_per_xl=$p_c.pro_per_xl 
                        pro_per_lg=$p_c.pro_per_lg 
                        pro_per_md=$p_c.pro_per_md 
                        pro_per_sm=$p_c.pro_per_sm 
                        pro_per_xs=$p_c.pro_per_xs
                        image_type=$p_c.image_type
                        display_sd=$p_c.display_sd
                        lazy_load=$p_c.lazy
                        }
                    {/if}
                {else}
                    <p class="warning">{l s='No products' d='Shop.Theme.Panda'}</p>
                {/if}
            </div>
       </div>
       {/if}
       {/foreach}
    </div>
</div>
{if isset($homeverybottom) && $homeverybottom}</div></div>{/if}
</div>
<!-- /MODULE st stproductcategoriesslider -->
{/if}