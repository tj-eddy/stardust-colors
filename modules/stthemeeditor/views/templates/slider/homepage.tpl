
{*
* 2007-2017 PrestaShop
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
*  @author    ST-themes <hellolee@gmail.com>
*  @copyright 2007-2017 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*}
{*similar files homepage.tpl stblogfeaturedarticles/views/templates/hook/home.tpl stproductcategoriesslider.tpl stproductcategoriesslider_tab.tpl stfeaturedcategories.tpl ps_crossselling.tpl  ps_categroyproduct.tpl steasycontent-element-6.tpl*}
{if isset($module)}
<!-- MODULE st {$module} -->
{/if}
{assign var="pro_or_blog_slider" value=0}
{if isset($products) && is_array($products) && count($products)}{$pro_or_blog_slider=1}{/if}
{if isset($blogs) && is_array($blogs) && count($blogs) && isset($use_blog) && $use_blog}{$pro_or_blog_slider=2}{/if}
{if isset($homeverybottom) && $homeverybottom && !$pro_per_fw}{assign var="bu_full_width" value=true}{else}{assign var="bu_full_width" value=false}{/if}
{assign var="column_fix" value=""}
{if isset($column_slider) && $column_slider}{$column_fix="_column"}{/if}
{if $title_position==3 && $direction_nav==1}{$direction_nav=5}{/if}
{if $pro_or_blog_slider || (isset($aw_display) && $aw_display)}
<div id="{$module}_container_{$hook_hash}" class="{$module}_container {if $hide_mob == 1} hidden-md-down {elseif $hide_mob == 2} hidden-lg-up {/if} block {if $pro_or_blog_slider==1 && $countdown_on} s_countdown_block {/if} {if !$column_fix && ($has_background_img || $video_mpfour)} jarallax {/if} products_container{$column_fix} {if $column_slider && !isset($is_quarter)} column_block {/if}" 
{if !$column_fix && ($has_background_img || $video_mpfour)} data-jarallax data-speed="{$speed}" {if $video_mpfour} data-video-src="mp4: {$video_mpfour}{if $video_webm}, webm: {$video_webm}{/if}{if $video_ogg}, ogv: {$video_ogg}{/if}" {/if} {/if}
>
{if $bu_full_width}<div class="wide_container">{/if}
{if isset($homeverybottom) && $homeverybottom}<div class="{if $bu_full_width}container{else}container-fluid{/if}">{/if}
<section class="products_section " >

    {if isset($title_io) && $title_io}
        {if $title_position!=3}{include file="module:stthemeeditor/views/templates/slider/heading.tpl"}{/if}
        {if isset($custom_content) && $custom_content}{$custom_content.1.content nofilter}{/if}
    {/if}
    <div class="row flex_lg_container flex_stretch">
        {if isset($custom_content) && $custom_content && $custom_content.10.width}
            {$custom_content.10.content nofilter}
        {/if}
        {if $page.page_name=="index"}
                <div class="col-lg-9 display_as_grid"> <!-- to do what if the sum of left and right contents larger than 12 -->
            {else}
                <div class="col-lg-{if isset($custom_content) && $custom_content}{12-$custom_content.10.width-$custom_content.30.width}{else}12{/if} {if $display_as_grid==1} display_as_grid {elseif $display_as_grid==2} display_as_simple {/if} products_slider"> <!-- to do what if the sum of left and right contents larger than 12 -->
            {/if}

    {if !isset($title_io) || !$title_io}
        {if $title_position!=3}{include file="module:stthemeeditor/views/templates/slider/heading.tpl"}{/if}
        {if isset($custom_content) && $custom_content}{$custom_content.1.content nofilter}{/if}
    {/if}

        {if $pro_or_blog_slider==1}
            {if !$display_as_grid || $column_slider}
            <div class="block_content {if isset($lazy_load) && $lazy_load && !$sttheme.pro_tm_slider} lazy_swiper {/if}">
                {include file="catalog/slider/product-slider.tpl"}
            </div>
            {include file="catalog/slider/script.tpl" block_name="#{$module}_container_{$hook_hash}" one_item_only={count($products)}}
            {elseif $display_as_grid==2}
                {include file="catalog/listing/product-list-simple.tpl" for_f="{$module}"}
            {else}
                {include file="catalog/_partials/miniatures/list-item.tpl" class="{$module}_grid" for_f="{$module}"}
            {/if}
    	{elseif $pro_or_blog_slider==2}
            {if !$display_as_grid || $column_slider}
            <div class="block_content {if isset($lazy_load) && $lazy_load && !$sttheme.pro_tm_slider} lazy_swiper {/if}">
                {include file="module:stblog/views/templates/slider/slider.tpl"}
            </div>
            {include file="catalog/slider/script.tpl" block_name="#{$module}_container_{$hook_hash}" is_product_slider=0 one_item_only={count($blogs)}}
            {else}
                {include file="module:stblog/views/templates/slider/list-item.tpl" for_f="{$module}" }
            {/if}
        {/if}
        {if $pro_or_blog_slider}
            {if isset($view_more) && $view_more==1 && ((isset($title_link) && $title_link) || (isset($url_entity) && $url_entity))}<div class="product_view_more_box text-center"><a href="{if isset($title_link) && $title_link}{$title_link}{else}{url entity=$url_entity}{/if}" class="btn btn-default btn-more-padding btn-large st_slider_view_more" title="{if isset($view_more_text) && $view_more_text}{$view_more_text}{else}{l s='View more' d='Shop.Theme.Panda'}{/if}">{if isset($view_more_text) && $view_more_text}{$view_more_text}{else}{l s='View more' d='Shop.Theme.Panda'}{/if}</a></div>{/if}
        {else}
            <div class="block_content">{l s='No items' d='Shop.Theme.Panda'}</div>
    	{/if}

            {if isset($custom_content) && $custom_content}{$custom_content.2.content nofilter}{/if}
        </div>
        {if $page.page_name=="index"}
            <hr>
            <div class="col-lg-3 display_as_grid products_slider">
                <div class="featured_categories_list">
                    <div style="padding-top: 10%">
                        <a class="custom-card" href="https://preproduction.stardustcolors.com/208-code-couleur-voiture" title="" style="border: 0;">
                            <div class="">
                                <img src="https://preproduction.stardustcolors.com/img/cms/vignette_lateral/vignette-laterale-auto-grande-min.png" alt=""  class="replace-2x img-responsive" />
                            </div>
                        </a>
                    </div>
                    <div style="padding-top: 10%">
                        <a class="custom-card" href="https://preproduction.stardustcolors.com/209-code-couleur-moto" title="" style="border: 0;">
                            <div class="">
                                <img src="https://preproduction.stardustcolors.com/img/cms/vignette_lateral/vignette-laterale-moto-min.png" alt=""  class="replace-2x img-responsive" />
                            </div>
                        </a>
                    </div>
                    <div style="padding-top: 10%">
                        <a class="custom-card" href="https://www.stardustcolors.com/472-model" title="" style="border: 0;">
                            <div class="">
                                <img src="https://preproduction.stardustcolors.com/img/cms/vignette_lateral/vignette-laterale-hikarirc-min.png" alt=""  class="replace-2x img-responsive" />
                            </div>
                        </a>
                    </div>
                    <div style="padding-top: 10%">
                        <a class="custom-card" href="https://preproduction.stardustcolors.com/135-peinture-pour-aerographe" title="" style="border: 0;">
                            <div class="">
                                <img src="https://preproduction.stardustcolors.com/img/cms/vignette_lateral/vignette-laterale-aerographe-min.png" alt=""  class="replace-2x img-responsive" />
                            </div>
                        </a>
                    </div>
                    <div style="padding-top: 10%">
                        <a class="custom-card" href="https://preproduction.stardustcolors.com/413-velo" title="" style="border: 0;">
                            <div class="">
                                <img src="https://preproduction.stardustcolors.com/img/cms/vignette_lateral/vignette-laterale-velo-min.png" alt=""  class="replace-2x img-responsive" />
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        {/if}
        {if isset($custom_content) && $custom_content && $custom_content.30.width}
            {$custom_content.30.content nofilter}
        {/if}
    </div>
</section>
{if isset($homeverybottom) && $homeverybottom}</div>{/if}
{if $bu_full_width}</div>{/if}
</div>
{/if}
{if isset($module)}
<!-- /MODULE st {$module} -->
{/if}