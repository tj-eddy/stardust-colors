{**
* TNT OFFICIAL MODULE FOR PRESTASHOP.
*
* @author    Inetum <inetum.world>
* @copyright 2016-2021 Inetum, 2016-2021 TNT
* @license   https://opensource.org/licenses/MIT MIT License
*}

<div id="formAdminParcelsPanel" class="panel card">
    <div class="panel-heading card-header">
        <i class="icon-tnt"></i>
        {l s='parcels' mod='tntofficiel'} <span class="badge">{$arrObjTNTParcelModelList|@count}</span> {if $strPickUpNumber}<span class="badge">{l s='Pickup number: ' mod='tntofficiel'} {$strPickUpNumber|escape:'htmlall':'UTF-8'}</span>{/if}
        <span class="badge">{l s='Total weight: ' mod='tntofficiel'} <span id="total-weight">0</span> {l s='Kg' mod='tntofficiel'}</span>
        {if $isAccountInsuranceEnabled}
        <span class="badge">{l s='Total Insurance: ' mod='tntofficiel'} <span id="total-insurance_amount">0</span> {l s='€' mod='tntofficiel'}</span>
        {/if}
    </div>
    <div class="table-responsive">
        <table class="table" id="parcelsTable">
            <thead>
            <tr>
                <th class="fixed-width-xs"><span class="title_box ">{l s='N°' mod='tntofficiel'}</span></th>
                <th class="fixed-width-sm"><span class="title_box">{l s='weight' mod='tntofficiel'}</span></th>
                {if $isAccountInsuranceEnabled}
                <th class="fixed-width-sm"><span class="title_box">{l s='Insurance Amount' mod='tntofficiel'}</span></th>
                {/if}
                <th><span class="title_box">{l s='tracking number' mod='tntofficiel'}</span></th>
                <th><span class="title_box">{l s='status' mod='tntofficiel'}</span></th>
                <th><span class="title_box text-right">{if $isExpeditionCreated}{l s='PDL' mod='tntofficiel'}{/if}</span></th>
            </tr>
            </thead>
            <tbody id="parcelsTbody">
            {foreach from=$arrObjTNTParcelModelList item=objTNTParcelModel key=intTNTParcelIndex}
                <tr class="current-edit hidden-print" id="row-parcel-{$objTNTParcelModel->id|intval}">
                    <td>
                        <div class="input-group">
                            {$intTNTParcelIndex + 1|intval}
                        </div>
                    </td>
                    <td>
                        <div class="input-group fixed-width-sm" style="float:left;margin-right:3px;">
                            <input id="parcelWeight-{$objTNTParcelModel->id|intval}"
                                   value="{$objTNTParcelModel->weight|escape:'htmlall':'UTF-8'}"
                                   class="form-control fixed-width-sm"
                                   {if $isExpeditionCreated}disabled="disabled"{/if}
                            />
                        </div>
                    </td>
                    {if $isAccountInsuranceEnabled}
                    <td>
                        <div class="input-group fixed-width-sm" style="float:left;margin-right:3px;">
                            <input name="parcelInsuranceAmount" id="parcelInsuranceAmount-{$objTNTParcelModel->id|intval}"
                                   value="{$objTNTParcelModel->insurance_amount|escape:'htmlall':'UTF-8'}"
                                   class="form-control fixed-width-sm parcelInsuranceAmount"
                                   {if $isExpeditionCreated}disabled="disabled"{/if}
                            />
                        </div>
                    </td>
                    {/if}
                    <td>
                        {if $objTNTParcelModel->parcel_number != ''}
                            {if $objTNTParcelModel->tracking_url != ''}
                                <a href="{$objTNTParcelModel->tracking_url|escape:'html':'UTF-8'}" target="_blank">
                                    {$objTNTParcelModel->parcel_number|escape:'htmlall':'UTF-8'}
                                    <i class="icon-external-link"></i>
                                </a>
                            {else}
                                {$objTNTParcelModel->parcel_number|escape:'htmlall':'UTF-8'}
                            {/if}
                        {else}
                            -
                        {/if}
                    </td>
                    <td>
                        {if $objTNTParcelModel->stage_id > 0}
                            <span class="label color_field"
                                  style="color:white;background-color:{$objTNTParcelModel->getStageColor()|escape:'html':'UTF-8'};">
                        {/if}
                            {$objTNTParcelModel->getStageLabel()|escape:'htmlall':'UTF-8'}
                        {if $objTNTParcelModel->stage_id > 0}
                            </span>
                        {/if}
                    </td>
                    <td class="actions">
                        {if $isExpeditionCreated}
                            {if $objTNTParcelModel->pod_url != ''}
                                <a href="{$objTNTParcelModel->pod_url|escape:'html':'UTF-8'}" target="_blank">
                                    <button class="btn btn-default" >
                                        <i class="icon-search"></i>
                                        <span>{l s='see' mod='tntofficiel'}</span>
                                    </button>
                                </a>
                            {else}
                                -
                            {/if}
                        {else}
                            <div id="parcelError-{$objTNTParcelModel->id|intval}" class="fixed-width-xl pull-left text-left" style="display: none">
                                <div class="alert alert-danger alert-danger-small">
                                    <p></p>
                                </div>
                            </div>
                            <div id="parcelSuccess-{$objTNTParcelModel->id|intval}" class="fixed-width-xl pull-left text-left" style="display: none">
                                <div class="alert alert-success alert-danger-small">
                                    <p>{l s='Update successful' mod='tntofficiel'}</p>
                                </div>
                            </div>
                            <button class="btn btn-primary updateParcel" value="{$objTNTParcelModel->id|intval}">
                                <span>{l s='Update' mod='tntofficiel'}</span>
                            </button>&nbsp;
                            <button class="btn btn-primary removeParcel" value="{$objTNTParcelModel->id|intval}">
                                <span>{l s='Delete' mod='tntofficiel'}</span>
                            </button>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
    <div class="row row-margin-bottom row-margin-top">
        <div class="col-lg-7">
        </div>
        <div class="col-lg-5">
            {if !$isExpeditionCreated}
                <a href="#addParcelFancyBox" id="fancyBoxAddParcelLink">
                    <button class="btn btn-default pull-right" id="addParcel">
                        <i class="icon-plus-sign"></i>
                        {l s='add' mod='tntofficiel'}
                    </button>
                </a>
            {elseif $isUpdateParcelsStateAllowed}
                <a class="btn btn-default tntofficiel-action-updateOrderStateDeliveredParcels"
                   href="javascript:void(0);"
                   style="float: right;"
                   title="Actualiser l'état des colis"
                ><i class="icon-refresh"></i> {l s='refresh' mod='tntofficiel'}</a>
            {/if}
        </div>
    </div>

    <div style="display:none">
        <div class="bootstrap" id="addParcelFancyBox">
            <h1 class="page-subheading">{l s='add parcel' mod='tntofficiel'}</h1>
            <div class="alert alert-danger alert-danger-small" id="addParcelError" style="display: none">
                <p id="addParcelErrorMessage"></p>
            </div>
            <div class="form-group">
                <label for="weight">{l s='parcel weight' mod='tntofficiel'}</label>
                <input class="form-control validate" type="text" id="addParcelWeight">
            </div>

            <p class="text-right">
                <button type="submit" name="submitAddParcel" id="submitAddParcel" class="btn btn-default">
                <span>
                    {l s='save' mod='tntofficiel'}
                    <i class="icon-chevron-right right"></i>
                </span>
                </button>
            </p>
        </div>
    </div>
</div>