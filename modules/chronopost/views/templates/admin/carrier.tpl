<div class="form-group {if $shared_carrier}shared_carrier{/if} {if $is_fresh}show_fresh{else}show_chronopost{/if}" data-carrier-group="{$selected}">
    <label class="control-label col-lg-3">{l s='Carrier for' mod='chronopost'} {$code_label|escape:'htmlall':'UTF-8'}</label>
    <div class="col-lg-9">
        <select name="chronoparams[{$code|escape:'htmlall':'UTF-8'}][id]">
            <option value="-1">{l s='Do not activate' mod='chronopost'}</option>

            {foreach from=$carriers item=carrier}
                <option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}"{if $selected==$carrier.id_reference} selected{/if}>
                    {$carrier.name|escape:'htmlall':'UTF-8'}
                </option>
            {/foreach}
        </select>
    </div>
</div>

<div class="form-group {if $shared_carrier}shared_carrier{/if} {if $is_fresh}show_fresh{else}show_chronopost{/if}" data-carrier-group="{$selected}">
    <label class="control-label col-lg-3">{l s='Contrat' mod='chronopost'}</label>
    <div class="col-lg-9">
        <select name="chronoparams[{$code|escape:'htmlall':'UTF-8'}][account]">
            <option value="-1">{if empty($available_accounts)}{l s='No available contract' mod='chronopost'}{/if}</option>
            {foreach from=$available_accounts item=account name=accounts}
                <option value="{$account['account']|escape:'htmlall':'UTF-8'}" {if $default_account==$account['account'] } selected {/if}>{$account['accountname']|escape:'htmlall':'UTF-8'}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="form-group {if $shared_carrier}shared_carrier{/if} {if $is_fresh}show_fresh{else}show_chronopost{/if}" data-carrier-group="{$selected}">
    <div class="col-lg-3"></div>
    <div class="col-lg-9 text-right">
        <button {if empty($available_accounts)}disabled="disabled"{/if} class="createCarrier btn btn-default"
                value="{$code|escape:'htmlall':'UTF-8'}">
            <i class="icon-plus"></i> {l s='Create new carrier' mod='chronopost'}
        </button>
    </div>
</div>
