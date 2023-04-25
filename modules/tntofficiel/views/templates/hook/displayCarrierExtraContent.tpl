{**
* TNT OFFICIAL MODULE FOR PRESTASHOP.
*
* @author    Inetum <inetum.world>
* @copyright 2016-2021 Inetum, 2016-2021 TNT
* @license   https://opensource.org/licenses/MIT MIT License
*}

<div id="TNTOfficielCarrierExtra{$objTNTCarrierModel->id_carrier|escape:'htmlall':'UTF-8'}" class="tntofficiel-delivery-option">
    {assign var='objTNTCarrierModelInfos' value=$objTNTCarrierModel->getCarrierInfos()}
    <span class="tntofficiel-description">
        {* VALIDATOR: This variable is HTML content. Do not escape. *}
        {$objTNTCarrierModelInfos->description nofilter}
        {if property_exists($objTNTCarrierModelInfos, 'description2')}
        <br /><span class="tntofficiel-description2">
            {* VALIDATOR: This variable is HTML content. Do not escape. *}
            {$objTNTCarrierModelInfos->description2 nofilter}
        </span>
        {/if}
        {if property_exists($objTNTCarrierModelInfos, 'reference')}
            <br />
            {* VALIDATOR: This variable is HTML content. Do not escape. *}
            {$objTNTCarrierModelInfos->reference nofilter}
        {/if}
    </span>
    {if !empty($strDueDate)}
    <span class="tntofficiel-edd">
        {l s='Estimated delivery date' mod='tntofficiel'} :&nbsp;{date('d/m/Y', strtotime($strDueDate))|escape:'htmlall':'UTF-8'}
    </span>
    {/if}
    {assign var='item_info' value=''}
    {if {$objTNTCarrierModel->carrier_type} == 'DROPOFFPOINT' and isset($deliveryPoint.xett)}
        {assign var='item_info' value=$deliveryPoint}
    {elseif {$objTNTCarrierModel->carrier_type} == 'DEPOT' and isset($deliveryPoint.pex)}
        {assign var='item_info' value=$deliveryPoint}
    {/if}
    {if isset($item_info) and $item_info != ''}
        {include sprintf('module:%s/views/templates/front/displayAjaxSaveProductInfo.tpl', TNTOfficiel::MODULE_NAME) item=$item_info carrier_type=$objTNTCarrierModel->carrier_type}
        <div class="shipping-method-info-details">
            <button type="button" class="btn button button-tntofficiel-small tntofficiel-shipping-method-info-select">
                <span>{l s='Change' mod='tntofficiel'}</span>
            </button>
        </div>
    {elseif $objTNTCarrierModel->carrier_type|strstr:"DROPOFFPOINT" or $objTNTCarrierModel->carrier_type|strstr:"DEPOT" }
        <div class="shipping-method-info-details">
            <button type="button" class="btn button button-tntofficiel-small tntofficiel-shipping-method-info-select">
                <span>{l s='Select' mod='tntofficiel'}</span>
            </button>
        </div>
    {/if}
</div>