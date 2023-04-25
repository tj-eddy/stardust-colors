{**
* TNT OFFICIAL MODULE FOR PRESTASHOP.
*
* @author    Inetum <inetum.world>
* @copyright 2016-2021 Inetum, 2016-2021 TNT
* @license   https://opensource.org/licenses/MIT MIT License
*}

{if isset($item)}
    <div class="tntofficiel-shipping-method-info">
        <div class="shipping-method-info-address">
            {if $carrier_type == 'DROPOFFPOINT'}
                <div class="shipping-method-info-code">Code: <b>{$item.xett|escape:'htmlall':'UTF-8'}</b></div>
            {/if}
            <div class="shipping-method-info-name">{$item.name|escape:'htmlall':'UTF-8'}</div>
            {* For DROPOFFPOINT *}
            {if $carrier_type == 'DROPOFFPOINT'}
                <div class="shipping-method-info-street">{$item.address|escape:'htmlall':'UTF-8'}</div>
            {else}
                {* For DEPOT *}
                <div class="shipping-method-info-street">{$item.address1|escape:'htmlall':'UTF-8'}</div>
                <div class="shipping-method-info-street">{$item.address2|escape:'htmlall':'UTF-8'}</div>
            {/if}
            <div class="shipping-method-info-city">{$item.postcode|escape:'htmlall':'UTF-8'} {$item.city|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="shipping-method-info-details">
            <span class="{$carrier_type|escape:'htmlall':'UTF-8'}-time-title"><strong>{l s='Schedules' mod='tntofficiel'} :</strong></span>
            {foreach from=$item.schedule key=day item=schedule}
                <div class="{$carrier_type|escape:'htmlall':'UTF-8'}-time">
                    <span class="{$carrier_type|escape:'htmlall':'UTF-8'}-time-label">{l s=$day mod='tntofficiel'}:</span>
                    {*{l s='Monday' mod='tntofficiel'}
                    {l s='Tuesday' mod='tntofficiel'}
                    {l s='Wednesday' mod='tntofficiel'}
                    {l s='Thursday' mod='tntofficiel'}
                    {l s='Friday' mod='tntofficiel'}
                    {l s='Saturday' mod='tntofficiel'}
                    {l s='Sunday' mod='tntofficiel'}*}

                    <span class="{$carrier_type|escape:'htmlall':'UTF-8'}-time-value">
                        {if !empty($schedule)}
                            {assign var='i' value=0}
                            {foreach from=$schedule item=part}
                                <span>{' - '|implode:$part|escape:'htmlall':'UTF-8'}</span>
                                {if ($schedule|@count) > 1 and $i < (($schedule|@count) -1)}
                                    <span>{l s='and' mod='tntofficiel'}</span>
                                {/if}
                                {assign var='i' value=$i+1}
                            {/foreach}
                        {else}
                            <span>{l s='Closed' mod='tntofficiel'}</span>
                        {/if}
                    </span>
                </div>
            {/foreach}
        </div>
    </div>
{/if}