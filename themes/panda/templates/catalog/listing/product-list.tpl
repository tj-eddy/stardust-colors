{**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{extends file=$layout}


{block name='content'}
  <section id="main">

    {block name='product_list_header'}
      <h1 class="page_heading mb-3">{$listing.label}</h1>
    {/block}

    {if isset($stpbf)}
      {$sttheme.product_view_swither=0}
      {if !empty($stpbf.cate_pro_image_type)}{assign var="pro_image_type" value=$stpbf.cate_pro_image_type}{/if}
      {assign var="is_lazy" value=$stpbf.cate_pro_lazy}
      {$sttheme.list_grid=(int)$stpbf.product_view}
      {if $stpbf.mobile_device}{$sttheme.list_grid=(int)$stpbf.product_view_mobile}{/if}
      {if is_array($stpbf.clear_list_view) && count($stpbf.clear_list_view)}{$sttheme.clear_list_view=array_sum($stpbf.clear_list_view)}{/if}
      {$sttheme.list_view_align=(int)$stpbf.list_view_align}
      {$sttheme.list_view_proportion=(int)$stpbf.list_view_proportion}
      {$sttheme.infinite_scroll=(int)$stpbf.infinite_scroll}
      {$sttheme.infinite_blank=(int)$stpbf.infinite_blank}
    {/if}
    <section id="products">
      {if $listing.products|count}

        {block name='product_list_active_filters'}
          {$listing.rendered_active_filters nofilter}
        {/block}

        {block name='above_product_list'}
          {if isset($listing.rendered_facets) && $sttheme.filter_position}
          <div id="horizontal_filters_wrap">
            <div id="horizontal_filters" class="horizontal_filters{if $sttheme.filter_position==2 || $sttheme.filter_position==3}_dropdown{/if} collapse {if $sttheme.filter_position==1} show{/if}" aria-expanded="{if $sttheme.filter_position>1}false{else}true{/if}">
                {$listing.rendered_facets nofilter}
            </div>
          </div>
          {/if}
        {/block}

        <div id="product-list-top-wrap">
          {block name='product_list_top'}
            {include file='catalog/_partials/products-top.tpl' listing=$listing}
          {/block}
        </div>


        <div id="product-list-wrap">
          {block name='product_list'}
            {include file='catalog/_partials/products.tpl' listing=$listing}
          {/block}
        </div>

        <div id="js-product-list-bottom-wrap">
          {block name='product_list_bottom'}
            {include file='catalog/_partials/products-bottom.tpl' listing=$listing}
          {/block}
        </div>

      {else}
        <div id="js-product-list-top"></div>

        <div id="js-product-list">
          <article class="alert alert-warning" role="alert" data-alert="warning">
          {l s='There are no products on the category.' d='Shop.Theme.Panda'}
          </article>
        </div>

        <div id="js-product-list-bottom"></div>
      {/if}
    </section>

    {block name='product_list_footer'}
    
    {/block}

  </section>
{/block}