{**
* TNT OFFICIAL MODULE FOR PRESTASHOP.
*
* @author    Inetum <inetum.world>
* @copyright 2016-2021 Inetum, 2016-2021 TNT
* @license   https://opensource.org/licenses/MIT MIT License
*}

<div id="extra_address_data" class="panel card">
    <div class="panel-heading card-header">
        <i class="icon-tnt"></i> {l s='TNT Additional Address' mod='tntofficiel'}
    </div>
    <div class="clearfix card-body" data-validated="true">
        <div class="row">
            <div class="form-group col-sm-6">
                <label for="receiver_email">{l s='Email' mod='tntofficiel'}</label>
                {* Email *}
                <input class="form-control" type="text" id="receiver_email" name="receiver_email" value="{$arrFormReceiverInfoValidate.fields.receiver_email|escape:'htmlall':'UTF-8'}" {if $isExpeditionCreated}disabled="disabled"{/if} />
                {if $arrFormReceiverInfoValidate.fields.receiver_email && array_key_exists('receiver_email', $arrFormReceiverInfoValidate.errors)}
                    <div class="form-text alert-danger error-receiver_email">{$arrFormReceiverInfoValidate.errors.receiver_email|escape:'htmlall':'UTF-8'}<span class="tiles"></span></div>
                {/if}
            </div>
            <div class="form-group col-sm-6">
                <label for="receiver_mobile">{l s='Cellphone' mod='tntofficiel'}</label>
                {* Téléphone portable *}
                <input class="form-control" type="tel" id="receiver_mobile" name="receiver_mobile" value="{$arrFormReceiverInfoValidate.fields.receiver_mobile|escape:'htmlall':'UTF-8'}" {if $isExpeditionCreated}disabled="disabled"{/if} />
                {if $arrFormReceiverInfoValidate.fields.receiver_mobile && array_key_exists('receiver_mobile', $arrFormReceiverInfoValidate.errors)}
                    <div class="form-text alert-danger error-receiver_mobile">{$arrFormReceiverInfoValidate.errors.receiver_mobile|escape:'htmlall':'UTF-8'}<span class="tiles"></span></div>
                {/if}
            </div>
        </div>
        {if !$isExpeditionCreated}
            <a id="submitAddressExtraData" class="btn button button-tntofficiel-small pull-right">
                <span>{l s='Validate' mod='tntofficiel'}</span>
            </a>
        {/if}
    </div>
</div>