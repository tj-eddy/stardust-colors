{**
* TNT OFFICIAL MODULE FOR PRESTASHOP.
*
* @author    Inetum <inetum.world>
* @copyright 2016-2021 Inetum, 2016-2021 TNT
* @license   https://opensource.org/licenses/MIT MIT License
*}

<!-- Tnt carriers -->
<div style="display: none;">
    {foreach $arrObjTNTCarrierModelList as $id_carrier => $objTNTCarrierModel}
        {assign var='objTNTCarrierModelInfos' value=$objTNTCarrierModel->getCarrierInfos()}
        {foreach $arrDeliveryOption as $idAddressSelected => $intCarrierIDList}
            {assign var='arrCarrierIDList' value=explode(',',$intCarrierIDList)}
            {assign var='intCarrierID' value=$arrCarrierIDList[0]}
            <div id="TNTOfficielCarrier{$objTNTCarrierModel->id_carrier|escape:'htmlall':'UTF-8'}" class="tntofficiel-delivery-option">
                <span class="tntofficiel-delay">
                    {* VALIDATOR: This variable is HTML content. Do not escape. *}
                    {$objTNTCarrierModelInfos->delay nofilter}
                </span>
            </div>
        {/foreach}
    {/foreach}

    {* if TNT delivery option is selected *}
    {*if $strCarrierTypeSelected && $arrFormReceiverInfoValidate*}
    <div id="extra_address_data" class="card card-block clearfix" data-validated="{$strExtraAddressDataValid|escape:'htmlall':'UTF-8'}" >
        <h3 class="page-subheading">{l s='TNT Additional Address' mod='tntofficiel'}</h3>
        <div class="form-group"><p>
           {l s='Please check the information below or fill in if missing.'  mod='tntofficiel'}
           {l s='If you change the information, click on the button'  mod='tntofficiel'}
           <b>{l s='Validate'  mod='tntofficiel'}</b> {l s='before continuing.'  mod='tntofficiel'}
           {l s='The information entered will be transmitted to the driver to facilitate the delivery of your package.' mod='tntofficiel'}
        </p></div>
        <div class="row">
            <div class="form-group col-xs-12 col-sm-12 col-lg-6">
                <label for="receiver_email">{l s='Email' mod='tntofficiel'} <span class="required"></span></label>
                {* Email *}
                <input class="form-control" type="text" id="receiver_email" name="receiver_email"
                       value="{$arrFormReceiverInfoValidate.fields.receiver_email|escape:'htmlall':'UTF-8'}" />
                {if $arrFormReceiverInfoValidate.fields.receiver_email && array_key_exists('receiver_email', $arrFormReceiverInfoValidate.errors)}
                    <small class="form-text alert-danger error-receiver_email">{$arrFormReceiverInfoValidate.errors.receiver_email|escape:'htmlall':'UTF-8'}</small>
                {/if}
            </div>
            <div class="form-group col-xs-12 col-sm-12 col-lg-6">
                <label for="receiver_mobile">{l s='Cellphone' mod='tntofficiel'} <span class="required"></span></label>
                {* Téléphone portable *}
                <input class="form-control" type="tel" id="receiver_mobile" name="receiver_mobile"
                       value="{$arrFormReceiverInfoValidate.fields.receiver_mobile|escape:'htmlall':'UTF-8'}" />
                {if $arrFormReceiverInfoValidate.fields.receiver_mobile && array_key_exists('receiver_mobile', $arrFormReceiverInfoValidate.errors)}
                    <small class="form-text alert-danger error-receiver_mobile">{$arrFormReceiverInfoValidate.errors.receiver_mobile|escape:'htmlall':'UTF-8'}</small>
                {/if}
            </div>
        </div>
            {* B2C INDIVIDUAL *}
            {*if $strCarrierTypeSelected === 'INDIVIDUAL'*}
        <div class="row">
            <div class="form-group col-xs-12 col-sm-12 col-lg-4">
                <label for="receiver_building">{l s='Building Number' mod='tntofficiel'}</label>
                {* Numéro du bâtiment *}
                <input class="form-control" type="text" id="receiver_building" name="receiver_building"
                       value="{$arrFormReceiverInfoValidate.fields.receiver_building|escape:'htmlall':'UTF-8'}" maxlength="3" />
                {if $arrFormReceiverInfoValidate.fields.receiver_building && array_key_exists('receiver_building', $arrFormReceiverInfoValidate.errors)}
                    <small class="form-text alert-danger error-receiver_building">{$arrFormReceiverInfoValidate.errors.receiver_building|escape:'htmlall':'UTF-8'}.</small>
                {else}
                    <small class="form-text info-receiver_building">3 caractères maximum</small>
                {/if}
            </div>
            <div class="form-group col-xs-12 col-sm-12 col-lg-4">
                <label for="receiver_accesscode">{l s='Intercom Code' mod='tntofficiel'}</label>
                {* Code interphone *}
                <input class="form-control" type="text" id="receiver_accesscode" name="receiver_accesscode"
                       value="{$arrFormReceiverInfoValidate.fields.receiver_accesscode|escape:'htmlall':'UTF-8'}" maxlength="7" />
                {if $arrFormReceiverInfoValidate.fields.receiver_accesscode && array_key_exists('receiver_accesscode', $arrFormReceiverInfoValidate.errors)}
                    <small class="form-text alert-danger error-receiver_accesscode">{$arrFormReceiverInfoValidate.errors.receiver_accesscode|escape:'htmlall':'UTF-8'}.</small>
                {else}
                    <small class="form-text info-receiver_accesscode">7 caractères maximum</small>
                {/if}
            </div>
            <div class="form-group col-xs-12 col-sm-12 col-lg-4">
                <label for="receiver_floor">{l s='Floor' mod='tntofficiel'}</label>
                {* Etage *}
                <input class="form-control" type="text" id="receiver_floor" name="receiver_floor"
                       value="{$arrFormReceiverInfoValidate.fields.receiver_floor|escape:'htmlall':'UTF-8'}" maxlength="2" />
                {if $arrFormReceiverInfoValidate.fields.receiver_floor && array_key_exists('receiver_floor', $arrFormReceiverInfoValidate.errors)}
                    <small class="form-text alert-danger error-receiver_floor">{$arrFormReceiverInfoValidate.errors.receiver_floor|escape:'htmlall':'UTF-8'}.</small>
                {else}
                    <small class="form-text info-receiver_floor">2 caractères maximum</small>
                {/if}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-xs-12 col-sm-12 col-lg-12">
                <label for="receiver_instructions">{l s='Special instructions' mod='tntofficiel'}<small class="form-text info-receiver_instructions">(30 caractères maximum)</small></label>
                {* Etage *}
                <input class="form-control" type="text" id="receiver_instructions" name="receiver_instructions"
                       value="{$arrFormReceiverInfoValidate.fields.receiver_instructions|escape:'htmlall':'UTF-8'}" maxlength="30" />
                {if $arrFormReceiverInfoValidate.fields.receiver_instructions && array_key_exists('receiver_instructions', $arrFormReceiverInfoValidate.errors)}
                    <small class="form-text alert-danger error-receiver_instructions">{$arrFormReceiverInfoValidate.errors.receiver_instructions|escape:'htmlall':'UTF-8'}.</small>
                {else}
                    <small class="info-receiver_instructions">
                        {l s='Indicate here where you want your package to be deposited in case of absence (mailbox, neighbor ...).'  mod='tntofficiel'}
                        {l s='In case of impossibility of delivery, you can give instructions or benefit from a 2nd presentation of the package the next day.'  mod='tntofficiel'}
                    </small>
                {/if}
            </div>
        </div>
        {*/if*}
        <p class="clearfix" style="min-height: 40px;margin: 3ex 0 1ex;clear: both;">
            <span class="required"></span> {l s='Required fields' mod='tntofficiel'}
            <a id="submitAddressExtraData" class="btn button button-tntofficiel-medium pull-right" {if $arrFormReceiverInfoValidate.length === 0} style="display: none;" {/if}>
                <span>{l s='Validate'  mod='tntofficiel'}</span>
            </a>
        </p>
    </div>
    {*/if*}
</div>



<script type="text/javascript">

    // On DOM Ready.
    window.document.addEventListener('DOMContentLoaded', function () {

        jQuery(window.strTNTOfficieljQSelectorInputRadioTNT).each(function (intIndex, element) {

            var $elmtDeliveryOptionTNTOfficiel = jQuery(this).parents('.delivery-option').first();

            var strTNTClickedCarrierID = jQuery(this).val().split(',')[0];
            var $elmtTNTClickedCarrierDescription = jQuery('#TNTOfficielCarrier'+strTNTClickedCarrierID);

            var $elmtDstDelay = $elmtDeliveryOptionTNTOfficiel.find('.carrier-delay').first();
            var $elmtSrcDelay = $elmtTNTClickedCarrierDescription.find('.tntofficiel-delay').first();

            // If Delay found.
            if ($elmtDstDelay.length === 1) {
                $elmtDstDelay.replaceWith($elmtSrcDelay);
            }

            $elmtTNTClickedCarrierDescription.remove();
        });


        function updateExtraDataDisplay(deliveryOptionContext)
        {
            var $elmtTNTExtraDataForm = jQuery('#extra_address_data');

            var $elmtInputRadioVirtTNTClick = jQuery(window.strTNTOfficieljQSelectorInputRadioTNT)
            .filter(function (intIndex, element) {
                return this === deliveryOptionContext;
            });

            if ($elmtInputRadioVirtTNTClick.length === 1) {
                var intTNTClickedCarrierID = $elmtInputRadioVirtTNTClick.val().split(',')[0] | 0;

                var strTNTClickedAccountType = null;
                var strTNTClickedCarrierType = null;
                if (window.TNTOfficiel.carrier && window.TNTOfficiel.carrier.list[intTNTClickedCarrierID]) {
                    strTNTClickedAccountType = window.TNTOfficiel.carrier.list[intTNTClickedCarrierID].account_type;
                }
                if (window.TNTOfficiel.carrier && window.TNTOfficiel.carrier.list[intTNTClickedCarrierID]) {
                    strTNTClickedCarrierType = window.TNTOfficiel.carrier.list[intTNTClickedCarrierID].carrier_type;
                }

                $elmtTNTExtraDataForm.show();

                if (strTNTClickedAccountType === 'LPSE ESSENTIEL') {
                    jQuery('#receiver_building, #receiver_accesscode, #receiver_floor').parent('.form-group').hide();
                    jQuery('#receiver_instructions').parent('.form-group').show();
                    jQuery('.special-receiver_instructions').show();
                } else {
                    jQuery('#receiver_instructions').parent('.form-group').hide();
                    jQuery('.special-receiver_instructions').hide();
                    if (strTNTClickedCarrierType === 'INDIVIDUAL') {
                        jQuery('#receiver_building, #receiver_accesscode, #receiver_floor').parent('.form-group').show();
                    } else {
                        jQuery('#receiver_building, #receiver_accesscode, #receiver_floor').parent('.form-group').hide();
                    }
                }
            } else if ($elmtTNTExtraDataForm.has(deliveryOptionContext).length !== 1) {
                $elmtTNTExtraDataForm.hide();
            }

            //jQuery('#TNTOfficielCarrierExtra'+intTNTClickedCarrierID).after($elmtTNTExtraDataForm);

            jQuery(window.strTNTOfficieljQSelectorInputRadioTNT)
                .parents('.delivery-option').parents('.delivery-options').after($elmtTNTExtraDataForm);
        }


        jQuery('#receiver_email, #receiver_mobile, #receiver_building, #receiver_accesscode, #receiver_floor, #receiver_instructions')
        .on('change', function (objEvent) {
            objEvent.stopImmediatePropagation();
            objEvent.preventDefault();
            return false;
        });

        updateExtraDataDisplay(jQuery(window.strTNTOfficieljQSelectorInputRadioTNT).filter(':checked')[0]);

        // Click on a checkout step.
        prestashop.on('changedCheckoutStep', function(objParam) {
            var $PersonalInfoStep = jQuery('#checkout-personal-information-step'),
            $AddressesStep = jQuery('#checkout-addresses-step'),
            $DeliveryStep = jQuery('#checkout-delivery-step'),
            $PaymentStep = jQuery('#checkout-payment-step'),
            elmntPersonalInfoStep = $PersonalInfoStep.length ? $PersonalInfoStep[0] : null,
            elmntAddressesStep = $AddressesStep.length ? $AddressesStep[0] : null,
            elmntDeliveryStep = $DeliveryStep.length ? $DeliveryStep[0] : null,
            elmntPaymentStep = $PaymentStep.length ? $PaymentStep[0] : null;

            // Delivery step selected and will be displayed.
            if (elmntDeliveryStep === objParam.event.delegateTarget) {
                // Remove hidden element from payment step, to allow user selection in delivery step.
                jQuery('.js-cart-payment-step-refresh').remove();
            }
        });

        // Click on a TNT carrier.
        prestashop.on('updatedDeliveryForm', function(objParam) {
            //var $elmtInputRadioVirtTNTMatch = jQuery(window.strTNTOfficieljQSelectorInputRadioTNT).filter(':checked');
            var $elmtInputRadioVirtTNTClick = jQuery(window.strTNTOfficieljQSelectorInputRadioTNT)
            .filter( function ( intIndex, element ) {
                return (objParam.deliveryOption.length === 1 && jQuery.contains(objParam.deliveryOption[0], this));
            } );

            var intArgTNTCarrierID = 0;
            if ($elmtInputRadioVirtTNTClick.length === 1) {
                intArgTNTCarrierID = $elmtInputRadioVirtTNTClick.val().split(',')[0] | 0;
            }

            // If selection is TNT.
            if ($elmtInputRadioVirtTNTClick.length !== 1) {
                //var xx = TNTOfficiel_PageSpinner(2 * 1000);
                //xx.hide();
            }

            updateExtraDataDisplay($elmtInputRadioVirtTNTClick[0]);

            // Display pop-in to select delivery point only for DROPOFFPOINT or DEPOT.
            TNTOfficiel_XHRBoxDeliveryPoints(intArgTNTCarrierID);

            TNTOfficiel_updatePaymentDisplay();

            // Remove error message.
            $('#hook-display-before-carrier p.alert-danger').remove();
        });

        // Update on display.
        TNTOfficiel_updatePaymentDisplay();
    });

</script>
