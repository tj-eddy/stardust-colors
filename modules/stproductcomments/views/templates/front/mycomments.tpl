{*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 17677 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{extends file='customer/page.tpl'}
{block name="page_content"}
{if isset($orders) && count($orders)}
{foreach $orders AS $order}
<div class="card card_trans block">
  <div class="card-header">
        <span>{l s='Order reference:' d='Shop.Theme.Panda'}<a href="{$link->getPageLink('order-detail', null, null, ['id_order'=>$order.id_order])}">{$order.reference}</a></span>
        <span>{l s='Payment:' d='Shop.Theme.Panda'}{$order.payment}</span>
        <span>{l s='Date:' d='Shop.Theme.Panda'}{dateFormat date=$order.date_add full=0}</span>
  </div>
  <div class="card-block">
    {if isset($order.detail.products)}
    <div class="base_list_line large_list">
    {foreach $order.detail.products AS $product}
    <div class="line_item row">
        <div class="col-3 col-md-2">
            {if isset($product.cover)}<img src="{if isset($product.cover.bySize.cart_default.url)}{$product.cover.bySize.cart_default.url}{/if}" width="{if isset($product.cover.bySize.cart_default.width)}{$product.cover.bySize.cart_default.width}{/if}" height="{if isset($product.cover.bySize.cart_default.height)}{$product.cover.bySize.cart_default.height}{/if}" alt="{$product.product_name}" />{/if}
        </div>
        <div class="col-7 col-md-6">
            <p class="font-weight-bold">{$product.product_name}</p>
            <div class="mb-1">{l s='Quantity' d='Shop.Theme.Panda'}: {$product.quantity}</div>
            <div class="mb-1">{l s='Reference' d='Shop.Theme.Panda'}: {$product.reference}</div>
        </div>
        <div class="col-12 col-md-4">
            <div class="row">
                <div class="col-3 hidden-md-up"></div>
                <div class="col-4 col-md-6">
                    {$product.total}
                </div>
                <div class="col-5 col-md-6">
                    {if isset($product.st_product_comment) && $product.st_product_comment}
                    <a href="{url entity='module' name='stproductcomments' controller='detail' params=['id_st_product_comment' => $product.st_product_comment.id_st_product_comment]}" title="{l s='View my review' d='Shop.Theme.Panda'}">{l s='View my review' d='Shop.Theme.Panda'}</a>
                    <a href="{url entity='module' name='stproductcomments' controller='mycomments' params=['secure_key'=>$secure_key, 'id_st_product_comment'=>$product.st_product_comment.id_st_product_comment, 'delete_comment'=>1]}" title="{l s='Delete' d='Shop.Theme.Panda'}">{l s='Delete' d='Shop.Theme.Panda'}</a>
                    {elseif $product.order_approved}
                    <a href="{url entity='module' name='stproductcomments' controller='mycomments' params=['secure_key'=>$secure_key, 'add_comment'=>1, 'id_order'=>$product.id_order,'id_order_detail'=>$product.id_order_detail]}" title="{l s='Write a review' d='Shop.Theme.Panda'}">{l s='Write a review' d='Shop.Theme.Panda'}</a>
                    {else}
                    {l s='Order is pending' d='Shop.Theme.Panda'}
                    {/if}
                </div>
            </div>
        </div>
    </div>
    {/foreach}
    </div>
    {else}
        <div class="alert alert-warning" role="alert" data-alert="warning">{l s='No order items' d='Shop.Theme.Panda'}</div>
    {/if}
  </div>
</div>
{/foreach}
{else}
<div class="alert alert-warning" role="alert" data-alert="warning">{l s='No orders' d='Shop.Theme.Panda'}</div>
{/if}
{/block}