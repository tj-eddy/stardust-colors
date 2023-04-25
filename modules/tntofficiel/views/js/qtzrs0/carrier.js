/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

// Default is required.
window.strTNTOfficieljQSelectorInputRadioTNT = window.strTNTOfficieljQSelectorInputRadioTNT || '';

function TNTOfficiel_isExtraDataValidated()
{
    return (jQuery('#extra_address_data').length == 0 || (!! jQuery('#extra_address_data').data('validated')));
}

function TNTOfficiel_setExtraDataValidated(boolArgAllow)
{
    // Flag validated.
    jQuery('#extra_address_data').data(
        'validated',
        boolArgAllow == null ? TNTOfficiel_isExtraDataValidated() : !!boolArgAllow
    );

    // Implies update.
    TNTOfficiel_updatePaymentDisplay();
}


function TNTOfficiel_isDeliveryPointValidated()
{
    var $elmtTNTOfficielInputRadioTNTSelected = jQuery(window.strTNTOfficieljQSelectorInputRadioTNT).filter(':checked');
    var intTNTCheckedCarrierID, strTNTCheckedCarrierType;

    if ($elmtTNTOfficielInputRadioTNTSelected.length) {
        intTNTCheckedCarrierID = $elmtTNTOfficielInputRadioTNTSelected.val().split(',')[0] | 0;
        var strTNTClickedCarrierType = null;
        if (window.TNTOfficiel.carrier && window.TNTOfficiel.carrier.list[intTNTCheckedCarrierID]) {
            strTNTClickedCarrierType = window.TNTOfficiel.carrier.list[intTNTCheckedCarrierID].carrier_type;
        }
    }

    var boolHasRepoAddressSelected = $elmtTNTOfficielInputRadioTNTSelected.closest('table').find('.tntofficiel-shipping-method-info').length > 0;
    var boolIsRepoTypeSelected = (
        strTNTCheckedCarrierType === 'DROPOFFPOINT'
        || strTNTCheckedCarrierType === 'DEPOT'
    );

    // If the selected TNT is a delivery point with a selected address.
    // or not a delivery point and no address is selected.
    return (
        (boolIsRepoTypeSelected && boolHasRepoAddressSelected)
        || (!boolIsRepoTypeSelected && !boolHasRepoAddressSelected)
    );
}

/**
 * Get current payment ready state.
 * @returns {boolean}
 * @constructor
 */
function TNTOfficiel_isPaymentReady ()
{
    var arrError = [];

    // Result from async AJAX request.
    var objResult = null;

    var objJqXHR = TNTOfficiel_AJAX({
        "url": window.TNTOfficiel.link.front.module.checkPaymentReady,
        "method": 'POST',
        "dataType": 'json',
        "async": false
    });

    objJqXHR
    .done(function (objResponseJSON, strTextStatus, objJqXHR) {
        objResult = objResponseJSON;
    })
    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
        //console.error(objJqXHR.status + ' ' + objJqXHR.statusText);
    });

    // If no result or has error.
    if (!objResult || objResult.error != null) {
        // Display alert message.
        if (objResult && objResult.error != null) {
            arrError.push(objResult.error);
        } else {
            arrError.push('errorTechnical');
        }

        return arrError;
    }

    // If the selected carrier (core) is not TNT, we don't handle it.
    if (objResult['carrier'] !== window.TNTOfficiel.module.name) {
        return arrError;
    }
/*
    if (!TNTOfficiel_isDeliveryPointValidated()) {
        arrError.push('errorNoDeliveryPointSelected');
    }
*/
    // If extra data form was not filled and validated.
    if (!TNTOfficiel_isExtraDataValidated()) {
        arrError.push('validateAdditionalCarrierInfo');
    }

    return arrError;
}

/**
 * Allow payment by showing or hiding payments options.
 */
function TNTOfficiel_updatePaymentDisplay()
{
    var $elmtInsertBefore =jQuery('#checkout-payment-step .content');

    jQuery('#payment-confirmation :input').removeClass('disabled');
    jQuery('#TNTOfficielHidePayment').remove();

    // if extra data form to fill exist and is validated.
    var arrPaymentReadyError = TNTOfficiel_isPaymentReady();
    if (arrPaymentReadyError.length > 0) {
        var strError = (window.TNTOfficiel.translate[arrPaymentReadyError[0]] || arrPaymentReadyError[0]);

        jQuery('#payment-confirmation :input').addClass('disabled');
        $elmtInsertBefore.before('\
<div id="TNTOfficielHidePayment">\
<p class="alert alert-danger">'+window.TNTOfficiel.module.title+': '+strError+'</p>\
<style type="text/css">\
\
    #checkout-payment-step .content, #checkout-payment-step .content * {\
        display: none !important;\
    }\
\
</style>\
</div>');
    }
}


// On DOM Ready.
window.document.addEventListener('DOMContentLoaded', function () {

    // Click on address displayed in delivery option from DROPOFFPOINT or DEPOT.
    jQuery(window.document).on('click', [
        '.tntofficiel-shipping-method-info',
        '.tntofficiel-shipping-method-info-select'
    ].join(','),
    function (objEvent) {
        var $elmtInputRadioVirtTNTMatch = jQuery(window.strTNTOfficieljQSelectorInputRadioTNT).filter(':checked');
        var strTNTClickedCarrierID = $elmtInputRadioVirtTNTMatch.val().split(',')[0];

        TNTOfficiel_XHRBoxDeliveryPoints(strTNTClickedCarrierID);

        objEvent.stopImmediatePropagation();
        objEvent.preventDefault();
        return false;
    });


    /*
     * Payment Choice.
     */

    // On payment submit.
    jQuery(window.document).on('click', '#payment-confirmation :input', TNTOfficiel_XHRcheckPaymentReady);

});


/*
 * AJAX after a click on payment button.
 * Do check to prevent payment action.
 */
function TNTOfficiel_XHRcheckPaymentReady(objEvent)
{
    // If payment is ready (JS check).
    var arrPaymentReadyError = TNTOfficiel_isPaymentReady();
    if (arrPaymentReadyError.length > 0) {
        var strError = (window.TNTOfficiel.translate[arrPaymentReadyError[0]] || arrPaymentReadyError[0]);
        alert(jQuery('<span>'+strError+'</span>').text());
        // Force to stay on current page.
        window.location.reload();
        // Stop form submit.
        objEvent.preventDefault();

        return false;
    }
}
