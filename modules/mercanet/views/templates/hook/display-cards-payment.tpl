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

{* ONE TIME PAYMENT *}
{if isset($one_time) && $one_time == true} 
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div id="mercanet-errors">
            </div>

            <p class="payment_module" id="mercanet_payment_button">
                <a id="mercanet-submit-card" href="{$link->getModuleLink('mercanet', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{$one_time_payment_name|escape:'htmlall':'UTF-8'}">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" alt="{$one_time_payment_name|escape:'htmlall':'UTF-8'}" width="57" height="57" />
                    {$one_time_payment_name|escape:'htmlall':'UTF-8'}
                </a>
            </p>
            <div id="mercanet-display-cards">
                <form id='mercanet-form' method='post' action='{$link->getModuleLink('mercanet', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}'>
                    {if !empty($cards_mif)}
                        <div class="mercanet-display-card">
                            <button name="mercanet_card" value="{$cards_mif_string|escape:'htmlall':'UTF-8'}" class="button_cards_mif">
                                {foreach $cards_mif as $card}                  
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />                                               
                                {/foreach}
                            </button> 
                        </div>
                    {/if}

                    {foreach $cards as $card}
                        {*if $card == $nxcb_name}
                        {if $cart_amount >= $nxcb_min_amount && $cart_amount <= $nxcb_max_amount}
                        <!--div class="mercanet-display-card">
                        <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                        </button>
                        </div!-->
                        {/if}
                        {else*}
                        {if !in_array($card, $cards_mif)}
                            {if $card == $f3cb_name}
                                {if $cart_amount >= $f3cb_min_amount && $cart_amount <= $f3cb_max_amount}
                                    <div class="mercanet-display-card">
                                        <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                        </button>
                                    </div>
                                {/if}
                            {elseif $card == $f4cb_name}
                                {if $cart_amount >= $f4cb_min_amount && $cart_amount <= $f4cb_max_amount}
                                    <div class="mercanet-display-card">
                                        <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                        </button>
                                    </div>
                                {/if}
                            {else}
                                <div class="mercanet-display-card">
                                    <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                    </button>
                                </div>
                            {/if}
                        {/if}
                    {/foreach}
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
{/if}

{* NX PAYMENT *}
{if isset($nx_time) && $nx_time == true}
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div id="mercanet-nx-errors">
            </div>
            <div class="payment_module payment_mercanet" id="mercanet_nx_payment_button">
                <p>
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" alt="{$nx_time_payment_name|escape:'htmlall':'UTF-8'}" width="57" height="57" />
                    {$nx_time_payment_name|escape:'htmlall':'UTF-8'}
                </p>
                <div id="nx_payments">
                    <form id='mercanet-nx-form' method='post' action='{$link->getModuleLink('mercanet', 'redirect', ['nx_time' => true], true)|escape:'htmlall':'UTF-8'}'>
                        <input type="hidden" id="id_mercanet_nx_payment" name="id_nx_payment" />
                        <ul class="ul-cards-mif">
                            {foreach $nx_time_payments as $nx_payment}
                                <li>
                                    <a href="{$link->getModuleLink('mercanet', 'redirect', ['nx_time' => true, 'id_nx_payment' => $nx_payment.id_mercanet_nx_payment|escape:'htmlall':'UTF-8'], true)|escape:'htmlall':'UTF-8'}" title="{$nx_payment.method_name|escape:'htmlall':'UTF-8'}">
                                        {$nx_payment.method_name|escape:'htmlall':'UTF-8'}
                                    </a>

                                    {if !empty($cards_mif)}
                                        <div class="mercanet-display-card">
                                            <button name="mercanet_card" onclick="submitNxForm({$nx_payment.id_mercanet_nx_payment|escape:'htmlall':'UTF-8'});" value="{$cards_mif_string|escape:'htmlall':'UTF-8'}" class="button_cards_mif">
                                                {foreach $cards_mif as $card}                  
                                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />                                               
                                                {/foreach}
                                            </button> 
                                        </div>
                                    {/if}

                                    {*

                                    // START : Members of $cards_nx and $cards_mif cannot be other than MercanetApi::$CARDS_MIF 's, so I commented part below
                                    
                                    <div id="mercanet_nx_cards">
                                    {foreach $cards_nx as $card}
                                    {*if $card == $nxcb_name}
                                    {if $cart_amount >= $nxcb_min_amount && $cart_amount <= $nxcb_max_amount}
                                    <div class="mercanet-display-card">
                                    <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                    </button>
                                    </div>
                                    {/if}
                                    {else*}
                                    {*if $card == $f3cb_name}
                                    {if $cart_amount >= $f3cb_min_amount && $cart_amount <= $f3cb_max_amount}
                                    <div class="mercanet-display-card">                                                      
                                    <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                    </button>
                                    </div>
                                    {/if}
                                    {elseif $card == $f4cb_name}
                                    {if $cart_amount >= $f4cb_min_amount && $cart_amount <= $f4cb_max_amount}
                                    <div class="mercanet-display-card">
                                    <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                    </button>
                                    </div>
                                    {/if}
                                    {else}
                                    <div class="mercanet-display-card">
                                    <button onclick="submitNxForm({$nx_payment.id_mercanet_nx_payment|escape:'htmlall':'UTF-8'});" name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                    </button>
                                    </div>
                                    {/if}                                         
                                    {/foreach}
                                    <div class="clearfix"></div>
                                    </div>
                                      
                                    // END of comment
                                    
                                    *}   

                                </li>
                            {/foreach}
                        </ul>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        function submitNxForm(id) {
            $('#id_mercanet_nx_payment').val(id);
        }
    </script>
{/if}

{* RECURRING PAYMENT *}
{if isset($recurring) && $recurring == true}
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div id="mercanet-errors">
            </div>

            <p class="payment_module" id="mercanet_payment_button">
                <a id="mercanet-submit-card" href="{$link->getModuleLink('mercanet', 'redirect', ['recurring' => true], true)|escape:'htmlall':'UTF-8'}" title="{$recurring_payment_name|escape:'htmlall':'UTF-8'}">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" alt="{$recurring_payment_name|escape:'htmlall':'UTF-8'}" width="57" height="57" />
                    {$recurring_payment_name|escape:'htmlall':'UTF-8'}
                </a>
            </p>
            <div id="mercanet-display-cards">
                <form id='mercanet-form' method='post' action='{$link->getModuleLink('mercanet', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}'>
                    {if !empty($cards_mif)}
                        <div class="mercanet-display-card">
                            <button name="mercanet_card" value="{$cards_mif_string|escape:'htmlall':'UTF-8'}" class="button_cards_mif">
                                {foreach $cards_mif as $card}                  
                                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />                                               
                                {/foreach}
                            </button> 
                        </div>
                    {/if}

                    {foreach $cards as $card}
                        {if !in_array($card, $cards_mif)}
                            {if $card == $f3cb_name}
                                {if $cart_amount >= $f3cb_min_amount && $cart_amount <= $f3cb_max_amount}
                                    <div class="mercanet-display-card">
                                        <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                        </button>
                                    </div>
                                {/if}
                            {elseif $card == $f4cb_name}
                                {if $cart_amount >= $f4cb_min_amount && $cart_amount <= $f4cb_max_amount}
                                    <div class="mercanet-display-card">
                                        <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                        </button>
                                    </div>
                                {/if}
                            {else}
                                <div class="mercanet-display-card">
                                    <button name="mercanet_card" value="{$card|escape:'htmlall':'UTF-8'}">
                                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$card|escape:'htmlall':'UTF-8'}.png" alt="{$card|escape:'htmlall':'UTF-8'}" title="{$card|escape:'htmlall':'UTF-8'}" />
                                    </button>
                                </div>
                            {/if} 
                        {/if}
                    {/foreach}

                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
{/if}
