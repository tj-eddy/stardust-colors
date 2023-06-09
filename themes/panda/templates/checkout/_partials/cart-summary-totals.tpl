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
<div class="cart-summary-totals card-block">

  {block name='cart_summary_tax'}
    {if isset($cart.subtotals.tax.label) && $cart.subtotals.tax.label !== null && $sttheme.second_price_total}
    <div class="cart-summary-line clearfix cart-total-excl-tax">
      <span class="label">{$cart.totals.total_excluding_tax.label}</span>
      <span class="value price">{$cart.totals.total_excluding_tax.value}</span>
    </div>
    {/if}
  {/block}
  
  {block name='cart_summary_tax'}
    {if isset($cart.subtotals.tax.label) && $cart.subtotals.tax.label !== null}
    <div class="cart-summary-line clearfix">
      <span class="label sub">{$cart.subtotals.tax.label}</span>
      <span class="value sub price">{$cart.subtotals.tax.value}</span>
    </div>
    {/if}
  {/block}

  {block name='cart_summary_total'}
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
  {/block}

</div>
