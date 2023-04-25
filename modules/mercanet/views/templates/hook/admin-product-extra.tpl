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
<div id="mercanet-payment-recurring" class="panel product-tab">
    <h3>{l s='Payment Recurring' mod='mercanet'}</h3>
    {if $feature_abo == false}
        <!-- TODO -->
        {l s='You do not have the access necessary do to recurring payment, if you want to access this option, please contact us at: ' mod='mercanet'}
    {else}
        <!-- TYPE -->
        <div class="form-group">
            <div class="col-lg-1">
                <span class="pull-right"> </span>
            </div>

            <label class="control-label col-lg-3" for="mercanet_type">
                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Type of payment' mod='mercanet'}">
            		{l s='Type' mod='mercanet'}
            	</span>
            </label>
            <div class="col-lg-2">
                <select id="mercanet_type" name="mercanet_type">
                    {foreach $mercanet_types as $mercanet_id_type => $mercanet_type}
                        <option value="{$mercanet_id_type|escape:'htmlall':'UTF-8'}" {if $mercanet_id_type == $mercanet_payment_recurring.type} selected="selected" {/if}>
                            {$mercanet_type|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>

        <!-- PERIODICITY -->
        <div class="form-group">
            <div class="col-lg-1">
                <span class="pull-right"> </span>
            </div>

            <label class="control-label col-lg-3" for="mercanet_periodicity">
                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Periodicity for capture the recurring payment' mod='mercanet'}">
            		{l s='Periodicity' mod='mercanet'}
            	</span>
            </label>
            <div class="col-lg-2">
                <select id="mercanet_periodicity" name="mercanet_periodicity">
                    {foreach $mercanet_periodicities as $mercanet_id_periodicity => $mercanet_periodicity}
                        <option value="{$mercanet_id_periodicity|escape:'htmlall':'UTF-8'}" {if $mercanet_id_periodicity == $mercanet_payment_recurring.periodicity} selected="selected" {/if}>
                            {$mercanet_periodicity.name|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>

        <!-- NUMBER OCCURENCES -->
        <div class="form-group">
            <div class="col-lg-1">
                <span class="pull-right"> </span>
            </div>

            <label class="control-label col-lg-3" for="mercanet_number_occurrences">
                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Number of occurences to do' mod='mercanet'}">
            		{l s='Number of occurrences' mod='mercanet'}
            	</span>
            </label>
            <div class="col-lg-1">
                <input id="mercanet_number_occurrences" name="mercanet_number_occurrences" value="{$mercanet_payment_recurring.number_occurences|escape:'htmlall':'UTF-8'}" type="text">
            </div>
        </div>
        <!-- RECURRING AMOUNT -->
        <div class="form-group">
            <div class="col-lg-1">
                <span class="pull-right"> </span>
            </div>

            <label class="control-label col-lg-3" for="mercanet_recurring_amount">
                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Recurring amount' mod='mercanet'}">
            		{l s='Recurring amount (pre-tax)' mod='mercanet'}
            	</span>
            </label>
            <div class="col-lg-1">
                <input id="mercanet_recurring_amount" name="mercanet_recurring_amount" value="{$mercanet_payment_recurring.recurring_amount|escape:'htmlall':'UTF-8'}" type="text">
            </div>
        </div>

        <!-- Submit -->
        <div class="panel-footer">
            <a class="btn btn-default" href="">
                <i class="process-icon-cancel"></i>
                {l s='Cancel' mod='mercanet'}
            </a>

            <button class="btn btn-default pull-right" type="submit" name="submitAddproduct">
                <i class="process-icon-save"></i>
                {l s='Save' mod='mercanet'}
            </button>
            <button class="btn btn-default pull-right" type="submit" name="submitAddproductAndStay">
                <i class="process-icon-save"></i>
                {l s='Save and stay' mod='mercanet'}
            </button>
        </div>
    {/if}
</div>
