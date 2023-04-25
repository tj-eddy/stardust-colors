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

{if (isset($status) == true) && ($status == 'ok')}
    <h3>{l s='Your order on %s is complete.' sprintf=$shop_name mod='mercanet'}</h3>
    <p>
        - {l s='Amount' mod='mercanet'} : <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
    </p>
    <p>
        - {l s='Reference' mod='mercanet'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
    </p>
    {if isset($authorisation_id)}
        <p>
            - {l s='Authorisation ID' mod='mercanet'} : <span class="reference"><strong>{$authorisation_id|escape:'html':'UTF-8'}</strong></span>
        </p>
    {/if}
    
    {if isset($schedules) && !empty($schedules)}
        <p>
            - {l s='Schedules' mod='mercanet'} : <br>
            <ul class="be2bill-payment-description">
            {foreach $schedules as $schedule}
				<li>
					<span id="amount" class="price">{displayPrice price=$schedule.amount}</span>
					{if $use_taxes == 1}
						{l s='(tax incl.)' mod='mercanet'}
					{/if}
					{l s='will be sold on' mod='mercanet'}
					{dateFormat date=$schedule.date_to_capture full=false}
				</li>
			{/foreach}
            </ul>
        </p>
    {/if}
    
    <p>{l s='An email has been sent with this information.' mod='mercanet'}</p>
    <p>{l s='If you have questions, comments or concerns, please contact our' mod='mercanet'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='mercanet'}</a>
    </p>
{else}
    <h3>{l s='Your order on %s has not been accepted.' sprintf=$shop_name mod='mercanet'}</h3>
    <p>- {l s='Reference' mod='mercanet'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span></p
    <p>{l s='Please, try to order again.' mod='mercanet'}</p>
    <p>{l s='If you have questions, comments or concerns, please contact our' mod='mercanet'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='mercanet'}</a>
    </p>
    <p> 
        <a href="{$link->getModuleLink('mercanet', 'reorder',['id_order' => $id_order], true)|escape:'htmlall':'UTF-8'}" class="button_large">{l s='Order again' mod='mercanet'}</a>
    </p>
{/if}
<hr />