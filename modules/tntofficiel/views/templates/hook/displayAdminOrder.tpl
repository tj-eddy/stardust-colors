{**
* TNT OFFICIAL MODULE FOR PRESTASHOP.
*
* @author    Inetum <inetum.world>
* @copyright 2016-2021 Inetum, 2016-2021 TNT
* @license   https://opensource.org/licenses/MIT MIT License
*}

<div class="row"><div class="col-lg-12">
<div id="TNTOfficelAdminOrdersViewOrder" class="panel card">

    <div class="panel-heading card-header">
        <i class="icon-tnt"></i>
        {l s='TNT' mod='tntofficiel'}
    </div>

    <div class="card-body">

{if !$boolDisplayNew}
    <div id="TNTOfficielOrderWellButton" class="well info-block mb-2 hidden-print">
        {if $isExpeditionCreated}
            <a class="btn btn-default"
               href="{$link->getAdminLink('AdminTNTOrders')|escape:'html':'UTF-8'}&amp;action=downloadBT&amp;id_order={$objPSOrder->id|intval}"
               title="{$strBTLabelName|escape:'html':'UTF-8'}"
               target="_blank"
            >
                <i class="icon-tnt"></i>
                {l s='TNT Transport Ticket' mod='tntofficiel'}
            </a>
        {else}
            <span class="span label label-inactive">
                <i class="icon-remove"></i>
                {l s='TNT Transport Ticket' mod='tntofficiel'}
            </span>
        {/if}
        &nbsp;
        <a class="btn btn-default"
           href="{$link->getAdminLink('AdminTNTOrders')|escape:'html':'UTF-8'}&amp;action=getManifest&amp;id_order={$objPSOrder->id|intval}"
           title="{l s='Manifest' mod='tntofficiel'}"
        >
            <i class="icon-tnt"></i>
            {l s='TNT Manifest' mod='tntofficiel'}
        </a>
        &nbsp;
        {if $isExpeditionCreated}
            <a class="btn btn-default"
               href="javascript:void(0);"
               onclick="window.open('{$link->getAdminLink('AdminTNTOrders')|escape:'html':'UTF-8'}&amp;action=tracking&amp;ajax=true&amp;orderId={$objPSOrder->id|intval}', 'Tracking', 'menubar=no, scrollbars=yes, top=100, left=100, width=900, height=600');"
            >
                <i class="icon-tnt"></i>
                {l s='TNT Tracking' mod='tntofficiel'}
            </a>
        {else}
            <span class="span label label-inactive">
                <i class="icon-remove"></i>
                {l s='TNT Tracking' mod='tntofficiel'}
            </span>
        {/if}
        &nbsp;
    </div>
{/if}

    <div class="">
        <div class="row">
            <div id="TNTOfficielSection2" class="col-lg-7">

                {include '../admin/displayOrderDeliveryPoint.tpl' isExpeditionCreated=$isExpeditionCreated strDeliveryPointType=$strDeliveryPointType strDeliveryPointCode=$strDeliveryPointCode arrDeliveryPoint=$arrDeliveryPoint objPSAddressDelivery=$objPSAddressDelivery}

            </div>
            <div id="TNTOfficielSection3" class="col-lg-5">

                {include '../admin/displayOrderReceiverInfo.tpl' isExpeditionCreated=$isExpeditionCreated arrFormReceiverInfoValidate=$arrFormReceiverInfoValidate}

            </div>
        </div>
    </div>


    <div class="">
        <div class="row">
            <div class="col-lg-7">

                {include '../admin/displayAjaxUpdateOrderStateDeliveredParcels.tpl' isExpeditionCreated=$isExpeditionCreated strPickUpNumber=$strPickUpNumber arrObjTNTParcelModelList=$arrObjTNTParcelModelList}

            </div>
            <div class="col-lg-5">

                <div id="formAdminShippingDatePanel" class="panel card">
                    <div class="panel-heading card-header">
                        <i class="icon-calendar"></i>
                        {l s='Shipping date' mod='tntofficiel'}
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="parcelsTable">
                            <thead>
                            <tr>
                                <th><span class="title_box ">{l s='Shipping date' mod='tntofficiel'}</span></th>
                                <th><span class="title_box ">{l s='Due date' mod='tntofficiel'}</span></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="input-group fixed-width-xl" style="float:left;margin-right:3px;">
                                        <input type="text" name="shipping_date" id="shipping_date" value="" class="form-control">
                                        <span class="input-group-addon">
                                            <i class="icon-calendar-empty"></i>
                                        </span>
                                    </div>
                                    <div id="delivery-date-error" class="input-group" style="display: none">
                                        <div class="alert alert-danger alert-danger-small">
                                            <p>{l s='La date n\'est pas valide' mod='tntofficiel'}</p>
                                        </div>
                                    </div>
                                    <div id="delivery-date-success" class="input-group" style="display: none">
                                        <div class="alert alert-success alert-danger-small">
                                            <p>{l s='La date est valide' mod='tntofficiel'}</p>
                                        </div>
                                    </div>
                                </td>
                                <td id="due-date">
                                    {$dueDate|escape:'htmlall':'UTF-8'}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    </div>

</div>
</div></div>

<script type="text/javascript">

    // On DOM Ready.
    //window.document.addEventListener('DOMContentLoaded', 
    (function () {

        {literal}
        window.TNTOfficiel.order.isTNT = true;
        window.TNTOfficiel.order.isDirectAddressCheck = {/literal}{if $boolDirectAddressCheck}true{else}false{/if}{literal};
        window.TNTOfficiel.order.isExpeditionCreated = {/literal}{if $isExpeditionCreated}true{else}false{/if}{literal};
        window.TNTOfficiel.order.intOrderID = {/literal}{$objPSOrder->id|intval|escape:'javascript':'UTF-8'}{literal};
        window.TNTOfficiel.order.intCarrierID = {/literal}{$objPSOrder->id_carrier|intval|escape:'javascript':'UTF-8'}{literal};
        window.TNTOfficiel.order.isCarrierDeliveryPoint = {/literal}{if $strDeliveryPointType !== null}true{else}false{/if}{literal};
        {/literal}

        window.startDateAdminOrder = 0;
    {if $intTSFirstAvailableDate}
        window.startDateAdminOrder = new Date("{$intTSFirstAvailableDate|escape:'javascript':'UTF-8'}"*1000);
    {/if}
    {if $intTSShippingDate}
        window.shippingDateAdminOrder = new Date("{$intTSShippingDate|escape:'javascript':'UTF-8'}"*1000);
    {/if}

    })();

</script>