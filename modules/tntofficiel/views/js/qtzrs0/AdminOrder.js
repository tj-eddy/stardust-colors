/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

// On DOM Ready.
window.document.addEventListener('DOMContentLoaded', function () {

    // If not an order with TNT carrier.
    if (!window.TNTOfficiel.order.isTNT) {
        return;
    }

    var $elmtOrderPanel = jQuery('#tabOrder').parents('.panel').first();
    var $elmtCustomerPanel = jQuery('#tabAddresses').parents('.panel').first();
    var $elmtTNTOfficielPanel = jQuery('#TNTOfficelAdminOrdersViewOrder');

    var $elmtTNTOfficielOrderWellButton = $elmtTNTOfficielPanel.find('#TNTOfficielOrderWellButton');

    var $elmtTNTOfficielCustomerAdressShippingTabPane = jQuery('#TNTOfficielOrderReceiverInfo');
    if ($elmtTNTOfficielCustomerAdressShippingTabPane.length !== 1) {
        $elmtTNTOfficielCustomerAdressShippingTabPane = $elmtCustomerPanel.find('#addressShipping');
    }

    /**
     * Button (BT,Tracking)
     */

    var $elmtOrderPanelFirstWell = $elmtOrderPanel.children('.well');
    // Move them to upper.
    if ($elmtOrderPanelFirstWell.length === 1
        && $elmtTNTOfficielOrderWellButton.length === 1
    ) {
        $elmtTNTOfficielOrderWellButton.removeClass().css('margin', '8px 0 0');
        $elmtOrderPanelFirstWell.append($elmtTNTOfficielOrderWellButton);
    }


    // Disable delivery address Modification for DROPOFFPOINT or DEPOT.
    if (window.TNTOfficiel.order.isCarrierDeliveryPoint) {
        jQuery('#addressShipping form :input').attr('disabled', true);
        jQuery('#addressShipping a').css('cursor', 'not-allowed');
        jQuery('#addressShipping a').on('click', function (objEvent) {
            objEvent.preventDefault();
        });

        jQuery('#addressShipping [name="submitAddressShipping"]').parents('form').first().hide();
        jQuery('#addressShipping .well').first().hide();
        jQuery('#map-delivery-point-canvas').replaceWith(jQuery('#map-delivery-canvas'));
    }



    /**
     * Delivery Point.
     */

    var $elmtTNTOfficielS2 = $elmtTNTOfficielPanel.find('#TNTOfficielSection2');
    if ($elmtTNTOfficielCustomerAdressShippingTabPane.length === 1
        && $elmtTNTOfficielS2.length === 1
    ) {
        $elmtTNTOfficielCustomerAdressShippingTabPane.append($elmtTNTOfficielS2.html());
        $elmtTNTOfficielS2.remove();
    }



    // Click on DROPOFFPOINT or DEPOT address displayed in delivery option.
    jQuery(window.document).on('click', '.tntofficiel-shipping-method-info-select', function (objEvent) {

        TNTOfficiel_XHRBoxDeliveryPoints(window.TNTOfficiel.order.intCarrierID);

        objEvent.stopImmediatePropagation();
        objEvent.preventDefault();
        return false;
    });



    /**
     * Carrier Additional Information.
     */

    var $elmtTNTOfficielTAI = $elmtTNTOfficielPanel.find('#TNTOfficielSection3');
    if ($elmtTNTOfficielCustomerAdressShippingTabPane.length === 1
        && $elmtTNTOfficielTAI.length === 1
    ) {
        $elmtTNTOfficielCustomerAdressShippingTabPane.append($elmtTNTOfficielTAI.html());
        $elmtTNTOfficielTAI.remove();
    }



    /**
     * Parcel / Pickup
     */

    jQuery('#formAdminParcelsPanel').on('change', "input[id*='parcelWeight-']", function () {
        var nbrParcelWeight = parseFloat(jQuery(this).val());
        if (nbrParcelWeight.toFixed(1) === '0.0') {
            nbrParcelWeight = 0.1;
        }
        jQuery(this).val(nbrParcelWeight.toFixed(1));
    });
    jQuery('#formAdminParcelsPanel').on('change', "input[id*='parcelInsuranceAmount-']", function () {
        var nbrParcelInsuranceAmount = parseFloat(jQuery(this).val());
        jQuery(this).val(nbrParcelInsuranceAmount.toFixed(2));
    });

    jQuery('#formAdminParcelsPanel')
    .on('click', '.removeParcel:submit', function (objEvent) {
        removeParcel(jQuery(this).val());
    })
    .on('click', '.updateParcel:submit', function (objEvent) {
        updateParcel(jQuery(this).val());
    });


    jQuery('a#fancyBoxAddParcelLink').fancybox({
        "afterClose": function () {
            jQuery("#addParcelFancyBox #addParcelError").hide();
            jQuery("#addParcelWeight").val("");
        },
        "transitionIn": 'elastic',
        "transitionOut": 'elastic',
        "type": 'inline',
        "speedIn": 600,
        "speedOut": 200,
        "overlayShow": false,
        "autoDimensions": true,
        "autoCenter": false,
        "helpers": {
            overlay: {
                closeClick: false,
                locked: false
            }
        }
    });

    jQuery('#TNTOfficelAdminOrdersViewOrder')
    .on('click', '.tntofficiel-action-updateOrderStateDeliveredParcels', function (objEvent) {
        var objData = {};
        objData['orderId'] = window.TNTOfficiel.order.intOrderID;

        jQuery(objEvent.currentTarget).addClass('disabled');

        var objJqXHR = TNTOfficiel_AJAX({
            "url": window.TNTOfficiel.link.back.module.updateOrderStateDeliveredParcels,
            "method": 'POST',
            "dataType": 'json',
            "data": objData,
            "async": true
        });

        objJqXHR
        .done(function (objResponseJSON, strTextStatus, objJqXHR) {
            if (objResponseJSON.error) {
                showErrorMessage(jQuery('<span>'+window.TNTOfficiel.translate.back.updateFailRetryStr+'</span>').text());
                //showErrorMessage(jQuery('<span>'+objResponseJSON.error+'</span>').text());
                return;
            }

            // Update content.
            jQuery('#formAdminParcelsPanel').replaceWith(objResponseJSON.template);
            showSuccessMessage(jQuery('<span>'+window.TNTOfficiel.translate.back.updateSuccessfulStr+'</span>').text());

            if (objResponseJSON.delivered) {
                // Reload to display new order status.
                TNTOfficiel_PageSpinner();
                window.location.reload();
            }
        })
        .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
            //window.location.reload();
        })
        .always(function () {
            jQuery(objEvent.currentTarget).removeClass('disabled');
        });

        objEvent.stopPropagation();
        objEvent.preventDefault();
        return false;
    });

    // Click on FancyBox submit to add a parcel.
    jQuery(window.document).on('click', '.fancybox-inner #addParcelFancyBox #submitAddParcel:submit', function (objEvent) {
        jQuery("#addParcelFancyBox #addParcelError").hide();

        var fltArgWeight = jQuery("#addParcelWeight").val();

        //check if the weight value is valid
        if (isNaN(fltArgWeight) || (fltArgWeight <= 0)) {
            jQuery('#addParcelFancyBox #addParcelErrorMessage').html('Le poids n\'est pas valide');
            jQuery('#addParcelFancyBox #addParcelError').show();
        } else {
            addParcel(fltArgWeight);
        }
    });

    /*
     * Picking date
     */

    jQuery('#shipping_date').datepicker({
        "minDate": window.startDateAdminOrder,
        "prevText": '',
        "nextText": '',
        "dateFormat": 'dd/mm/yy',
        "beforeShowDay": function(t) {
            // The date is invalid before the current date.
            if (t < window.startDateAdminOrder) {
                return [false, ''];
            }

            // The date is invalid on weekends.
            var arrWeek = jQuery.datepicker.noWeekends(t);
            if (!arrWeek[0]) {
                return arrWeek;
            }

            // The date is valid.
            return [true, ''];
        },
        "onSelect": function () {
            jQuery('#delivery-date-error, #delivery-date-success').hide();
            var objData = {};
            objData['orderId'] = window.TNTOfficiel.order.intOrderID;
            objData['shippingDate'] = jQuery('#shipping_date').val();

            var objJqXHR = TNTOfficiel_AJAX({
                "url": window.TNTOfficiel.link.back.module.checkShippingDateValidUrl,
                "method": 'POST',
                "dataType": 'json',
                "data": objData,
                "async": true
            });

            objJqXHR
            .done(function (objResponseJSON, strTextStatus, objJqXHR) {
                if (objResponseJSON.strResponseMsgError && objResponseJSON.strResponseMsgError.length) {
                    jQuery('#delivery-date-error p').html(objResponseJSON.strResponseMsgError);
                    jQuery('#delivery-date-error').show();

                    return;
                } else if (objResponseJSON.strResponseMsgWarning && objResponseJSON.strResponseMsgWarning.length) {
                    jQuery('#delivery-date-success p').html(objResponseJSON.strResponseMsgWarning);
                    jQuery('#delivery-date-success').show();
                } else {
                    jQuery('#delivery-date-success p').html('La date est valide.');
                    jQuery('#delivery-date-success').show();
                }

                if (objResponseJSON.dueDate) {
                    jQuery('#due-date').html(objResponseJSON.dueDate);
                }
            })
            .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
                jQuery('#delivery-date-error p').html(
                    'Une erreur s\'est produite, merci de réessayer dans quelques minutes.'
                );
                jQuery('#delivery-date-error').show();
            });
        }
    });

    if (typeof window.shippingDateAdminOrder != 'undefined') {
        jQuery('#shipping_date').datepicker('setDate', window.shippingDateAdminOrder);
    }
    if (window.TNTOfficiel.order.isExpeditionCreated) {
        jQuery('#shipping_date').datepicker('option', 'disabled', true);
    }

    updateTotalWeight();
    updateTotalInsuranceAmount();
});





/**
 * remove a parcel
 * @param rowNumber
 */
function removeParcel(parcelId)
{
    var objData = {
        "parcelId": parcelId
    };
    var parcelCount = getParcelRowCount();

    if (parcelCount <= 1) {
        jQuery('#parcelError-' + parcelId + ' p').html(window.TNTOfficiel.translate.back.atLeastOneParcelStr);
        jQuery('#parcelError-' + parcelId).show();
    } else {
        var objJqXHR = TNTOfficiel_AJAX({
            "url": window.TNTOfficiel.link.back.module.removeParcelUrl,
            "method": 'POST',
            "dataType": 'json',
            "data": objData,
            "async": true
        });

        objJqXHR
        .done(function (objResponseJSON, strTextStatus, objJqXHR) {
            jQuery('#row-parcel-' + parcelId).remove();
            updateTotalWeight();
            updateTotalInsuranceAmount();
        });
    }
}

/**
 * Update a parcel
 * @param parcelId
 */
function updateParcel(parcelId)
{
    jQuery('#parcelError-' + parcelId + ', #parcelSuccess-' + parcelId).hide();

    var $elmtInputWeight = jQuery('#parcelWeight-' + parcelId);
    var $elmtInputInsurance = jQuery('#parcelInsuranceAmount-' + parcelId);
    var isAccountInsuranceEnabled = jQuery('#total-insurance_amount').length === 1;

    var objData = {};
    objData['parcelId'] = parcelId;
    objData['weight'] = $elmtInputWeight.val();
    if (isAccountInsuranceEnabled) {
        objData['parcelInsuranceAmount'] = $elmtInputInsurance.val();
    }
    objData['orderId'] = window.TNTOfficiel.order.intOrderID;

    if (isNaN(objData['weight'])
        || objData['weight'] <= 0
    ) {
        jQuery('#parcelError-' + parcelId + ' p').html('Le poids n\'est pas valide');
        jQuery('#parcelError-' + parcelId).show();
    } else if (
        ('parcelInsuranceAmount' in objData)
        && (isNaN(objData['parcelInsuranceAmount'])
            || objData['parcelInsuranceAmount'] < 0)
    ) {
        jQuery('#parcelError-' + parcelId + ' p').html('Le montant n\'est pas valide');
        jQuery('#parcelError-' + parcelId).show();
    } else {
        var objJqXHR = TNTOfficiel_AJAX({
            "url": window.TNTOfficiel.link.back.module.updateParcelUrl,
            "method": 'POST',
            "dataType": 'json',
            "data": objData,
            "async": true
        });

        objJqXHR
        .done(function (objResponseJSON, strTextStatus, objJqXHR) {
            if (objResponseJSON.error) {
                jQuery('#parcelError-' + parcelId + ' p').html(objResponseJSON.error);
                jQuery('#parcelError-' + parcelId).show();
            } else {
                $elmtInputWeight.val(objResponseJSON.weight);
                $elmtInputInsurance.val(objResponseJSON.insurance_amount);
                jQuery('#parcelSuccess-' + parcelId).show();
                updateTotalWeight();
                updateTotalInsuranceAmount();
            }
        })
        .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
            //window.location.reload();
        });
    }
}

/**
 * Add a parcel
 */
function addParcel(fltArgWeight)
{
    var objJqXHR = TNTOfficiel_AJAX({
        "url": window.TNTOfficiel.link.back.module.addParcelUrl,
        "method": 'POST',
        "dataType": 'json',
        "data": {
            "orderId": window.TNTOfficiel.order.intOrderID,
            "weight": fltArgWeight
        },
        "async": true
    });

    objJqXHR
    .done(function (objResponseJSON, strTextStatus, objJqXHR) {
        if (objResponseJSON.error) {
            jQuery('#addParcelFancyBox #addParcelErrorMessage').html(objResponseJSON.error);
            jQuery('#addParcelFancyBox #addParcelError').show();
        } else {
            jQuery.fancybox.close();
            addRowParcel(objResponseJSON['parcel']);
            updateTotalWeight();
            updateTotalInsuranceAmount();
        }
    })
    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
        //window.location.reload();
    });
}

/**
 * add a row in the parcels table
 */
function addRowParcel(objParcel) {
    var nextRowNumber = getNexttParcelNumber();
    var isAccountInsuranceEnabled = jQuery('#total-insurance_amount').length === 1;
    var strInsuranceHTML = '';
    if (isAccountInsuranceEnabled) {
        strInsuranceHTML = '\
    <td>\
        <div class="input-group fixed-width-sm" style="float:left;margin-right:3px;">\
            <input id="parcelInsuranceAmount-' + objParcel['id'] + '" value="' + objParcel['insurance_amount'] + '" class="form-control fixed-width-sm" /> \
        </div>\
    </td>';
    }

    jQuery('#parcelsTbody').append('\
<tr class="current-edit hidden-print" id="row-parcel-' + objParcel['id'] + '">\
    <td>\
        <div class="input-group">' + nextRowNumber + '</div>\
    </td>\
    <td>\
        <div class="input-group fixed-width-sm" style="float:left;margin-right:3px;">\
            <input id="parcelWeight-' + objParcel['id'] + '" value="' + objParcel['weight'] + '" class="form-control fixed-width-sm" /> \
        </div>\
    </td>'+strInsuranceHTML+'\
    <td>-</td>\
    <td>-</td>\
    <td class="actions">\
        <div id="parcelError-' + objParcel['id'] + '" class="fixed-width-xl pull-left text-left" style="display: none">\
            <div class="alert alert-danger alert-danger-small">\
                <p></p>\
            </div>\
        </div>\
        <div id="parcelSuccess-' + objParcel['id'] + '" class="fixed-width-xl pull-left text-left" style="display: none">\
            <div class="alert alert-success alert-danger-small">\
                <p>' + window.TNTOfficiel.translate.back.updateSuccessfulStr + '</p>\
            </div>\
        </div>\
        <button class="btn btn-primary updateParcel" value="' + objParcel['id'] + '">' + window.TNTOfficiel.translate.back.updateStr + '</button>&nbsp;\
        <button class="btn btn-primary removeParcel" value="' + objParcel['id'] + '">' + window.TNTOfficiel.translate.back.deleteStr + '</button>\
    </td>\
</tr>');

}

/*
 * Add or update total weight
 */
function updateTotalWeight() {
    var sum = 0;
    jQuery('[id*="parcelWeight-"]').each(function () {
        var value = jQuery(this).val();
        // add only if the value is number
        if (!isNaN(value) && value.length != 0) {
            sum += parseFloat(value);
        }
    });
    jQuery('#total-weight').html(sum.toFixed(1));

    return sum;
}

function updateTotalInsuranceAmount() {
    var sum = 0;
    jQuery('[id*="parcelInsuranceAmount-"]').each(function () {
        var value = jQuery(this).val();
        // add only if the value is number
        if (!isNaN(value) && value.length != 0) {
            sum += parseFloat(value);
        }
    });
    jQuery('#total-insurance_amount').html(sum.toFixed(2));

    return sum;
}

function getParcelRowCount() {
    return jQuery('#parcelsTable > #parcelsTbody tr').length++;
}

function getNexttParcelNumber() {
    return parseInt(jQuery('#parcelsTable > #parcelsTbody tr:last-child td:first-child div.input-group').html()) + 1
}