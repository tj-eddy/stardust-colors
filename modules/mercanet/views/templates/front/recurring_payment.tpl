{*
* 1961-2016 BNP Paribas
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
*  @author    Quadra Informatique <modules@quadra-informatique.fr>
*  @copyright 1961-2016 BNP Paribas
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  
*}
{capture name=path}
    <a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
        {l s='My account' mod='mercanet'}
    </a>
    <span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
    <span class="navigation_page">{l s='My Recurring payments' mod='mercanet'}</span>
{/capture}

<h1 class="page-heading bottom-indent">{l s='My Recurring payments' mod='mercanet'}</h1>
{if isset($can_stop) && $can_stop}
    <p class="info-title">{l s='If you want to stop all of your recurring payment click in the button "Stop all my recurring payment".' mod='mercanet'}</p>
{/if}
<div id="block-wallet" class="block-center">
    <div class="stop_recurring_confirmation">
        <p class="info-title">{l s='Are you sure to stop your current recurring payments ?' mod='mercanet'}</p>
        <div class="text-center">
            <button id="confirm_stop_recurring" type="submit" class="btn btn-default button button-small">
                <span>
                    {l s='Yes' mod='mercanet'}
                </span>
            </button>
            <button id="noconfirm_stop_recurring" type="submit" class="btn btn-default button button-small">
                <span>
                    {l s='No' mod='mercanet'}
                </span>
            </button>
        </div>
    </div>
    {if isset($display_conf) && $display_conf}
        <div class="stop_recurring_confirmation_information">
            <p class="info-title">{l s='All your recurrents payments are now stopped' mod='mercanet'}</p>
        </div>
    {/if}
    {if isset($can_stop) && $can_stop}
        <div class="clearfix main-page-indent">
            <form id="mercanet_stop_recurring_form" method="POST" action="{$url|escape:'htmlall':'UTF-8'}">
                <input type="hidden" name="mercanet_stop_recurring" value="1" />
                <button id="stop_recurring" type="submit" form="mercanet_stop_recurring_form" class="btn btn-default button button-medium">
                    <span>
                        {l s='Stop all my recurring payment' mod='mercanet'}
                        <i class="icon-chevron-right right"></i>
                    </span>
                </button>
            </form>
        </div>
    {/if}
    {if isset($can_change_card) && $can_change_card}
         <p class="info-title">{l s='At least one of your recurring payment has failed on last payment. To continue your reccuring payment you must make a new order to register new payment informations' mod='mercanet'}</p>
          {/if}
        <p class="info-title">{l s='You can change your credit card for recurring payment by clicking "Change credit card for recurring payment".' mod='mercanet'}</p>
        
   
</div>
{if count($payment_recurring_list)}
    <div class="block-center" id="block-history">
        <table id="order-list" class="table table-bordered footab default footable-loaded footable">
            <thead>
                <tr>
                    <th class="first_item footable-first-column" data-sort-ignore="true">{l s='Order' mod='mercanet'}</th>
                    <th data-hide="phone,tablet" class="item footable-sortable" >{l s='Recurring item' mod='mercanet'}</th>
                    <th data-sort-ignore="true" class="item">{l s='Next Payment' mod='mercanet'}</th>
                    <th data-hide="phone,tablet" class="item footable-sortable text-center">{l s='Amount (tax excluded)' mod='mercanet'}</th>
                    <th data-sort-ignore="true" class="item">{l s='State' mod='mercanet'}</th>
                    {if isset($can_change_card) && $can_change_card}
                    <th data-sort-ignore="true" class="item"></th>
                    {/if}
                </tr>
            </thead>
            <tbody>
                {foreach from=$payment_recurring_list item=payment name=myLoop}
                    <tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
                        <td class="history_link bold"><a class="color-myaccount" href="{$link->getPageLink('order-detail', true, NULL, "id_order={$payment.id_order|intval}")|escape:'html':'UTF-8'}">
                                {$payment.item_reference|escape:'html':'UTF-8'}
                            </a></td>
                        <td class="history">{$payment.item_name|escape:'html':'UTF-8'}  <img src="{$link->getImageLink($payment.link_rewrite, $payment.item_image, 'small_default')|escape:'html'}"
                                                                                             height="50" width="50"
                                                                                             alt="{$payment.item_name|escape:'html':'UTF-8'}"/></td>
                        <td class="history_date bold">{dateFormat date=$payment.next_schedule full=0}</td>
                        <td class="history text-center">{convertPrice price=$payment.amount_tax_exclude}</td>
                        <td class="history">{$payment.status.name|escape:'htmlall':'UTF-8'} {if $payment.late > 0}{$payment.late|escape:'html':'UTF-8'} {l s='late payements' mod='mercanet'}{/if}</td>
                        {*if isset($can_change_card) && $can_change_card*}
                        <td class="history text-center">{if $payment.can_change_card} 
                            <div class="change_card"><a href="{$link->getModuleLink('mercanet', 'recurringPayment', ['id_recurring' => $payment.id_mercanet_customer_payment_recurring,'reorder' => true] )|escape:'html':'UTF-8'}">
                        {l s='Change credit card' mod='mercanet'}
                                </a></div>
               {/if}</td>
                        {*/if*}
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{/if}
<ul class="footer_links clearfix">
    <li>
        <a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
            <span>
                <i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='mercanet'}
            </span>
        </a>
    </li>
    <li>
        <a class="btn btn-default button button-small" href="{$base_dir|escape:'html':'UTF-8'}">
            <span><i class="icon-chevron-left"></i> {l s='Home' mod='mercanet'}</span>
        </a>
    </li>
</ul>