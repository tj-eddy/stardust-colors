function toggleRDVpane(cust_address, codePostal, city, e) {
    var chronordvContainer = $('#chronordv_container');

    var radioInput = $("input.delivery_option_radio:checked").val();
    if (radioInput !== undefined) {
        radioInput = radioInput.slice(0, -1);
    }

    if (radioInput === rdv_carrierID) {
        if (typeof e !== "undefined") {
            e.stopPropagation();
        }

        $('body').on('change', 'input[name="chronoRDVSlot"]', function () {
            var rank = $('input[name="chronoRDVSlot"]:checked').val();
            var fee = $('input[name="chronoRDVSlot"]:checked').attr('data-fee');
            var deliveryDate = $('input[name="chronoRDVSlot"]:checked').attr('data-delivery-date');
            var deliveryDateEnd = $('input[name="chronoRDVSlot"]:checked').attr('data-delivery-date-end');
            var slotCode = $('input[name="chronoRDVSlot"]:checked').attr('data-slot-code');
            var tariffLevel = $('input[name="chronoRDVSlot"]:checked').attr('data-tariff-level');
            associateCreneau(rank, deliveryDate, deliveryDateEnd, slotCode, tariffLevel, transactionID, fee);
        });

        if ($('#checkout-delivery-step').hasClass('js-current-step')) {
            $('input[name="chronoRDVSlot"]:first').click();
        }

        chronordvContainer.show();
        return false;
    }

    chronordvContainer.hide();
}

function associateCreneau(rank, deliveryDate, deliveryDateEnd, slotCode, tariffLevel, transactionID, fee) {
    $.ajax({
        url: path + '/async/storeCreneau.php?rank=' + rank + '&deliveryDate=' + encodeURIComponent(deliveryDate) +
            '&deliveryDateEnd=' + encodeURIComponent(deliveryDateEnd) + '&slotCode=' + encodeURIComponent(slotCode) +
            '&tariffLevel=' + tariffLevel + '&transactionID=' + encodeURIComponent(transactionID) + '&fee=' + fee +
            '&cartID=' + cartID
    });
}

function cleanContainersRdv() {
    var dummyContainer = $('#chronordv_dummy_container');

    // move in DOM to prevent compatibility issues with Common Services' modules
    if ($("#chronordv_container").length > 0) {
        dummyContainer.remove();
    } else {
        dummyContainer.attr('id', 'chronordv_container');
    }

    let checkedDeliveryOption = $("input[name*='delivery_option[']:checked");
    if (checkedDeliveryOption.length === 0 || typeof checkedDeliveryOption === 'undefined') {
        return;
    }

    let checkedCarrier = checkedDeliveryOption.val().substring(0, checkedDeliveryOption.val().indexOf(','));
    if (checkedCarrier === rdv_carrierID) {
        let container = $("[id^=delivery_option]:checked").parents('.delivery-option').children('label');
        if (container.length === 0) {
            container = $('#extra_carrier');
        }

        $('#chronordv_container').detach().insertAfter(container);
    }
}

$(function () {
    if (typeof rdv_carrierID === 'undefined') {
        return false;
    }

    var body = $('body');

    // Clean container on load
    cleanContainersRdv();
    toggleRDVpane(cust_address_clean, cust_codePostal, cust_city);

    // Listener for selection of the chronordv carrier radio button
    body.on('click', '.delivery_options span.custom-radio > input[type=radio], input[name*="delivery_option"]', function (e) {
        cleanContainersRdv();
        toggleRDVpane(cust_address_clean, cust_codePostal, cust_city, e);

        if (parseInt($(this).val()) === parseInt(rdv_carrierID)) {
            if ($('#chronordv_container').length) {
                $('html, body').animate({
                    scrollTop: $('#chronordv_container').offset().top
                }, 500);
            }
        }
    });
});
