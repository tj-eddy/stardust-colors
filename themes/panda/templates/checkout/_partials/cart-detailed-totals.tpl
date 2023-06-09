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
 {block name='cart_detailed_totals'}
<div class="cart-detailed-totals">

  <div class="card-block">
    {foreach from=$cart.subtotals item="subtotal"}
      {if $subtotal && $subtotal.value|count_characters > 0 && $subtotal.type !== 'tax'}
        <div class="cart-summary-line clearfix" id="cart-subtotal-{$subtotal.type}">
          <span class="label{if 'products' === $subtotal.type} js-subtotal{/if}">
            {if 'products' == $subtotal.type}
              {$cart.summary_string}
            {else}
              {$subtotal.label}
            {/if}
          </span>
          <div class="value price">
            {if 'discount' == $subtotal.type}-&nbsp;{/if}{$subtotal.value}
            {if $subtotal.type === 'shipping'}
              <div class="shipping_sub_total_details">{hook h='displayCheckoutSubtotalDetails' subtotal=$subtotal}</div>
            {/if}
          </div>          
        </div>
      {/if}
    {/foreach}
  </div>

  {block name='cart_voucher'}
    {include file='checkout/_partials/cart-voucher.tpl'}
  {/block}

  <hr>

  <div class="card-block">
    {if isset($cart.subtotals.tax.label) && $cart.subtotals.tax.label !== null}
    {if $sttheme.second_price_total}
    <div class="cart-summary-line clearfix cart-total-excl-tax">
      <span class="label">{$cart.totals.total_excluding_tax.label}</span>
      <span class="value price">{$cart.totals.total_excluding_tax.value}</span>
    </div>
    {/if}
    <div class="cart-summary-line clearfix">
      <span class="label">{$cart.subtotals.tax.label}</span>
      <span class="value price">{$cart.subtotals.tax.value}</span>
    </div>
    {/if}
    {if isset($cart.subtotals.tax.label) && $cart.subtotals.tax.label !== null && $sttheme.second_price_total}
    <div class="cart-summary-line clearfix cart-total">
      <span class="label">{$cart.totals.total_including_tax.label}</span>
      <span class="value price fs_lg font-weight-bold">{$cart.totals.total_including_tax.value}</span>
    </div>
    {else}
    <div class="cart-summary-line clearfix cart-total">
      <span class="label">{$cart.totals.total.label} {$cart.labels.tax_short}</span>
      <span class="value price fs_lg font-weight-bold">{$cart.totals.total.value}</span>
    </div>
    {/if}
  </div>

  <hr>
</div>
{/block}