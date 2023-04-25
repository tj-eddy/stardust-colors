<script>
    var lt = "{$lt|escape:'htmlall':'UTF-8'}";
    var lt_history = {$lt_history};
    var lt_history_link = {$lt_history_link};
    var path = "{$module_uri|escape:'javascript':'UTF-8'}";
    var id_order = "{$id_order|escape:'javascript':'UTF-8'}";
    var chronopost_secret = "{$chronopost_secret|escape:'javascript':'UTF-8'}";
    var chronopost_delayed_errors = {$chronopost_errors};
</script>

<script src="{$module_uri|escape:'htmlall':'UTF-8'}/views/js/adminOrder.js"></script>

<br/>
<div class="card">
    <div class="card-header">
        {l s='Print the Chronopost waybills' mod='chronopost'}
    </div>
    <div class="card-body">
        <form method="POST" action="{$module_uri|escape:'htmlall':'UTF-8'}/postSkybill.php" id="chrono_form">
            {if $bal==1}
                <p style="text-align:center;width:400px"><b>Option BAL activée.</b></p>
            {/if}

            {if $saturday==1}
                <label>{l s='Saturday delivery option' mod='chronopost'}</label>
                <div class="margin-form">
                    <input type="checkbox" name="shipSaturday" value="yes"
                            {if $saturday_ok==1} checked{/if}
                            {if $saturday_ok && $saturday_supplement_enabled} onclick="return false" {/if}/>
                </div>
            {/if}

            <label>{l s='Number of parcels' mod='chronopost'}</label>
            <div class="margin-form">
                <input type="text" name="multiOne" id="multiOne"
                       value="{$nbwb|escape:'htmlall':'UTF-8'}" {if !$multiParcelsAvailable}disabled="disabled"{/if}/>
            </div>
            <br/>

            <label for="account">{l s='Account to use' mod='chronopost'}</label>
            <select id="account" class="form-control custom-select" name="account" {if $account_used }disabled{/if}>
                {foreach from=$available_accounts item=account name=accounts}
                    <option value="{$account['account']|escape:'htmlall':'UTF-8'}"
                            {if $default_account==$account['account'] && !$account_used} selected
                            {elseif $account_used && $account_used==$account['account']} selected
                            {/if}>
                        {$account['accountname']|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
            <br/>

            {if $account_used}<input type="hidden" name="account" value="{$account_used}">{/if}

            <div id="dimensions">
                <div class="dimensions-group">
                    <label></label>
                    <table>
                        <thead>
                        <tr>
                            <th style="text-align: center">Poids</th>
                            <th style="text-align: center">Longueur</th>
                            <th style="text-align: center">Hauteur</th>
                            <th style="text-align: center">Largeur</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><input type="text" name="weight[]" min="0" id="weight" value="{$default_weight}"
                                       class="form-control"/></td>
                            <td><input type="text" name="length[]" min="0" id="length" value="" class="form-control"/>
                            </td>
                            <td><input type="text" name="height[]" min="0" id="height" value="" class="form-control"/>
                            </td>
                            <td><input type="text" name="width[]" min="0" id="width" value="" class="form-control"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {if $to_insure>-1}
                <br/>
                <label for="advalorem">{l s='Shipment insurance' mod='chronopost'}</label>
                <div class="margin-form">
                    <input type="checkbox" id="advalorem" name="advalorem" value="yes" {if $to_insure>0} checked{/if}/>
                </div>
                <label>{l s='Value to insure' mod='chronopost'}</label>
                <div class="margin-form">
                    <input type="text" name="advalorem_value" value="{$to_insure|escape:'htmlall':'UTF-8'}"/>
                </div>
            {/if}

            <br/>
            <label for="account">{l s='Account to use' mod='chronopost'}</label>
            <div class="margin-form">
                <select id="account" class="form-control custom-select" name="account"
                        {if $account_used && !$is_chronofresh}disabled{/if}>
                    {foreach from=$available_accounts item=account name=accounts}
                        <option value="{$account['account']|escape:'htmlall':'UTF-8'}"
                                {if $default_account==$account['account'] && !$account_used} selected
                                {elseif $account_used && $account_used==$account['account']} selected
                                {/if}>
                            {$account['accountname']|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
                {if $account_used}<input type="hidden" name="account" value="{$account_used}">{/if}
            </div>

            {if $use_chronofresh_products}
                <br/>
                <label for="chrono_product">{l s='Product to use' mod='chronopost'}</label>
                <div class="margin-form">
                    <select id="chrono_product" class="form-control custom-select" name="chrono_product">
                        {foreach from=$chronofresh_products item=product}
                            <option value="{$product.code|escape:'htmlall':'UTF-8'}"{if $quickcost_product==$product.code} selected{/if}>
                                {$product.label|escape:'htmlall':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                    {if $product_used}<input type="hidden" name="product_used" value="{$product_used}">{/if}
                </div>
            {/if}

            {if $is_chronofresh}
                <br/>
                <label for="chrono_dlc">{l s='DLC' mod='chronopost'}</label>
                <div class="margin-form">
                    <div class="input-group fixed-width-xl">
                        <input type="text" id="chrono_dlc" name="dlc" class="datepicker" value="{$dlc_default}"/>
                        <div class="input-group-addon">
                            <i class="icon-calendar-o"></i>
                        </div>
                    </div>
                </div>
            {/if}

            {if $return==1}
                <br/>
                <label for="return_address">{l s='Address for return waybill' mod='chronopost'}</label>
                <div class="margin-form">
                    <select id="return_address" class="form-control custom-select" name="return_address">
                        <option value="0"{if $return_default=='0'} selected{/if}>{l s='Return address' mod='chronopost'}</option>
                        <option value="1"{if $return_default=='1'} selected{/if}>{l s='Invoice address' mod='chronopost'}</option>
                        <option value="2"{if $return_default=='2'} selected{/if}>{l s='Shipping address' mod='chronopost'}</option>
                    </select>
                </div>
            {/if}

            <input type="hidden" name="orderid" value="{$id_order|escape:'htmlall':'UTF-8'}"/>

            <p style="text-align:center">
                <input type="hidden" name="shared_secret" value="{$chronopost_secret|escape:'htmlall':'UTF-8'}"/>

                {if $has_lt}
                    <input class="btn btn-default button" style="margin:10px;" type="submit" name="create"
                           value="Créer un nouvel envoi"/>
                {/if}

                <input type="submit" value="{l s='Print waybill' mod='chronopost'}" class="btn btn-primary button"
                       style="margin:10px;" id="chronoSubmitButton"/>

                {if $return==1}
                    <input style="margin:10px;" type="submit" name="return"
                           value="{l s='Print the return waybill' mod='chronopost'}"
                           class="btn btn-default button"/>
                {/if}
        </form>
    </div>
</div>
