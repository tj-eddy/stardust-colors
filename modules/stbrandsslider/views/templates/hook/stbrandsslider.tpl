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
<!-- MODULE st stbrandsslider -->
<div id="brands_slider_container_{$hook_hash}" class="brands_slider_container {if $hide_mob == 1} hidden-md-down {elseif $hide_mob == 2} hidden-lg-up {/if} block products_container {if $video_mpfour} video_bg_block {/if}" 
{if $has_background_img && $speed} data-stellar-background-ratio="{$speed}" data-stellar-vertical-offset="{(int)$bg_img_v_offset}" {/if}
{if $video_mpfour} data-vide-bg="mp4: {$video_mpfour}{if $video_webm}, webm: {$video_webm}{/if}{if $video_ogg}, ogv: {$video_ogg}{/if}{if $video_poster}, poster: {$video_poster}{/if}" data-vide-options="loop: {if $video_loop}true{else}false{/if}, muted: {if $video_muted}true{else}false{/if}, position: 50% {(int)$video_v_offset}%" {/if}>
{if isset($homeverybottom) && $homeverybottom}<div class="wide_container"><div class="container">{/if}
<section id="brands_slider_{$hook_hash}" class="brands_slider">

    <div class="row flex_lg_container flex_stretch ">
        {if isset($brands) && count($brands)}
        {if isset($custom_content) && $custom_content && $custom_content.10.width}
            <div class="col-lg-{$custom_content.10.width}">
            {$custom_content.10.content nofilter}
            </div>
        {/if}
        <div class="col-lg-{if isset($custom_content) && $custom_content}{12-$custom_content.10.width-$custom_content.30.width}{else}12{/if} products_slider"> <!-- to do what if the sum of left and right contents larger than 12 -->
        

    {if $title_position!=3 && $display_as_grid!=1}
        <div class="title_block flex_container title_align_{(int)$title_position} title_style_{(int)$sttheme.heading_style} {if isset($sub_title) && $sub_title} st_has_sub_title {/if}">
            <div class="flex_child title_flex_left"></div>
            <a href="{url entity='manufacturer'}" class="title_block_inner" title="{if isset($title) && $title}{$title}{else}{l s='Product Brands' d='Shop.Theme.Panda'}{/if}">{if isset($title) && $title}{$title}{else}{l s='Product Brands' d='Shop.Theme.Panda'}{/if}</a>
            <div class="flex_child title_flex_right"></div>
            {if $direction_nav==1 && isset($brands) && $brands}
                <div class="swiper-button-tr {if $hide_direction_nav_on_mob} hidden-md-down {/if}"><div class="swiper-button swiper-button-outer swiper-button-prev"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div><div class="swiper-button swiper-button-outer swiper-button-next"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div></div>        
            {/if}
        </div>
    {if isset($sub_title) && $sub_title}<div class="slider_sub_title">{$sub_title nofilter}</div>{/if}
    {elseif $direction_nav==1}
        {$direction_nav=5}
    {/if}

    {if isset($custom_content) && $custom_content}{$custom_content.1.content nofilter}{/if}

    {assign var='is_lazy' value=(isset($lazy_load) && $lazy_load)}
    <div class="block_content {if $is_lazy} lazy_swiper{/if}">
        {if $display_as_grid==1}
            <div class="featured_categories_list row">
            {foreach $brands as $key=>$brand}
                {assign var="curr_index" value=$key}
                {assign var="curr_iteration" value=$key+1}
                <div class="{if $pro_per_fw}col-fw-{(12/$pro_per_fw)|replace:'.':'-'}{/if}  {if $pro_per_xxl}col-xxl-{(12/$pro_per_xxl)|replace:'.':'-'}{/if}  {if $pro_per_xl}col-xl-{(12/$pro_per_xl)|replace:'.':'-'}{/if} col-lg-{(12/$pro_per_lg)|replace:'.':'-'} col-md-{(12/$pro_per_md)|replace:'.':'-'} col-sm-{(12/$pro_per_sm)|replace:'.':'-'} col-{(12/$pro_per_xs)|replace:'.':'-'}  featured_categories_item">
                    <div class="cate_outer_box">
                        <div class="cate_first_box">
                            <a href="{$brand.url}" title="{$brand.name}" class="fc_cat_image">
                                <picture>
                                {if isset($stwebp) && isset($stwebp.brand_default) && $stwebp.brand_default}
                                <!--[if IE 9]><video style="display: none;"><![endif]-->
                                <source {if $is_lazy}data-{/if}srcset="{$brand.image|regex_replace:'/\.jpg$/':'.webp'}
                                    {if $sttheme.retina && $brand.image_2x},{$brand.image_2x|regex_replace:'/\.jpg$/':'.webp'} 2x{/if}"
                                    title="{$brand.name}"
                                    type="image/webp"
                                    >
                                <!--[if IE 9]></video><![endif]-->
                                {/if}
                                <img 
                                {if $is_lazy}data-src{else}src{/if}="{$brand.image}"
                                {if $sttheme.retina && $brand.image_2x}
                                {if $is_lazy}data-srcset{else}srcset{/if}="{$brand.image_2x} 2x"
                                {/if}
                                alt="{$brand.name}" width="{$manufacturerSize.width}" height="{$manufacturerSize.height}" class="{if $is_lazy} cate_pro_lazy {/if} front-image" />
                                </picture>
                            </a>
                        </div>
                        <div class="pro_second_box">
                            {if $show_brand_name}
                            <h3 class="s_title_block "><a href="{$brand.url}" title="{$brand.name}">{$brand.name}</a></h3>
                            {/if}
                            {if $show_brand_desc == 1}
                            <div class="product-desc display_sd">{$brand.short_description|strip_tags:'UTF-8'|truncate:100:'...'}</div>
                            {elseif $show_brand_desc == 2}
                            <div class="product-desc display_sd">{$brand.short_description nofilter}</div>
                            {/if}
                        </div>
                    </div>
                </div>
            {/foreach}
            </div>
            {else}
        <div class="swiper-container products_sldier_swiper {if $direction_nav>1} swiper-button-lr {if !isset($from_eb) || !$from_eb} swiper-small-button {/if} {if $direction_nav==6 || $direction_nav==7} swiper-navigation-circle {elseif $direction_nav==4 || $direction_nav==5} swiper-navigation-rectangle {elseif $direction_nav==8 || $direction_nav==9} swiper-navigation-arrow {elseif $direction_nav==2 || $direction_nav==3} swiper-navigation-full {/if} {if $direction_nav==2 || $direction_nav==4 || $direction_nav==6|| $direction_nav==8} swiper-navigation_visible {/if}{/if}" {if $sttheme.is_rtl} dir="rtl" {/if}>
            <div class="swiper-wrapper">
            	{foreach $brands as $brand}
                <div class="brands_slider_wrap swiper-slide">
                    <div class="pro_outer_box">
                	<a href="{$brand.url}" title="{$brand.name}" class="brands_slider_item product_img_link {if $is_lazy} is_lazy {/if}">
                        <picture>
                        {if isset($stwebp) && isset($stwebp.brand_default) && $stwebp.brand_default}
                        <!--[if IE 9]><video style="display: none;"><![endif]-->
                          <source {if $is_lazy}data-{/if}srcset="{$brand.image|regex_replace:'/\.jpg$/':'.webp'}
                            {if $sttheme.retina && $brand.image_2x},{$brand.image_2x|regex_replace:'/\.jpg$/':'.webp'} 2x{/if}"
                            title="{$brand.name}"
                            type="image/webp"
                            >
                        <!--[if IE 9]></video><![endif]-->
                        {/if}
                        <img 
                        {if $is_lazy}data-src{else}src{/if}="{$brand.image}"
                        {if $sttheme.retina && $brand.image_2x}
                          {if $is_lazy}data-srcset{else}srcset{/if}="{$brand.image_2x} 2x"
                        {/if}
                         alt="{$brand.name}" width="{$manufacturerSize.width}" height="{$manufacturerSize.height}" class="{if $is_lazy} swiper-lazy {/if} front-image" />
                        </picture>
                    </a>
                    {if $show_brand_name || $show_brand_desc}
                        <div class="pro_second_box">
                        {if $show_brand_name}
                        <h3 class="s_title_block "><a href="{$brand.url}" title="{$brand.name}">{$brand.name}</a></h3>
                        {/if}
                        {if $show_brand_desc == 1}
                        <div class="product-desc">{$brand.short_description|strip_tags:'UTF-8'|truncate:100:'...'}</div>
                        {elseif $show_brand_desc == 2}
                        <div class="product-desc">{$brand.short_description}</div>
                        {/if}
                        </div>
                    {/if}
                    </div>
                </div>
                {/foreach}
            </div>

            {if $direction_nav>1}
                <div class="swiper-button swiper-button-outer swiper-button-next {if $hide_direction_nav_on_mob} hidden-md-down {/if}"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
                <div class="swiper-button swiper-button-outer swiper-button-prev {if $hide_direction_nav_on_mob} hidden-md-down {/if}"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
            {/if}
            {if $control_nav}
            <div class="swiper-pagination {if $control_nav==2} swiper-pagination-st-custom {elseif $control_nav==4} swiper-pagination-st-round {/if} {if $hide_control_nav_on_mob} hidden-md-down {/if}"></div>
            {/if}
        </div>
        {/if}
    </div>
    {if $display_as_grid!=1}
        {include file="catalog/slider/script.tpl" block_name="#brands_slider_container_{$hook_hash}" is_product_slider=0}
        {/if}
            
            {if isset($view_more) && $view_more}<div class="product_view_more_box text-center"><a href="{url entity='manufacturer'}" class="btn btn-default btn-more-padding btn-large" title="{l s='View more' d='Shop.Theme.Panda'}">{l s='View more' d='Shop.Theme.Panda'}</a></div>{/if}

            {if isset($custom_content) && $custom_content}{$custom_content.2.content nofilter}{/if}
        </div>
        {if isset($custom_content) && $custom_content && $custom_content.30.width}
            <div class="col-lg-{$custom_content.30.width}">
            {$custom_content.30.content nofilter}
            </div>
        {/if}
    {else}
        <div class="block_content">{l s='No items' d='Shop.Theme.Panda'}</div>
    {/if}
    </div>
</section>

{if isset($homeverybottom) && $homeverybottom}</div></div>{/if}
</div>
<!-- /MODULE st stbrandsslider -->