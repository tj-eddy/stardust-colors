function toggleSaturdaySelection(id) {
    const saturdayContainer = $('#saturday_delivery');
    if (SATURDAY_IDS.indexOf(parseInt(id)) > -1 && SATURDAY_SUPPLEMENT_ENABLED) {
        saturdayContainer.show();
    } else {
        saturdayContainer.hide();
    }
}

function updateSaturdaySupplement(value) {
    $.ajax({
        url: path + '/async/storeSaturdayOption.php?saturday_supplement=' + value + '&cartID=' + cartID
    });
}

function resetCheckbox() {
    $("input[name='saturday_delivery']").prop("checked", false);
}

$(function () {
    var body = $("body");

    // Set saturday activation status
    var currentSelectedCarrier = $("[id^=delivery_option_]:checked");
    if ($(currentSelectedCarrier).length > 0 && typeof SATURDAY_SUPPLEMENT_ENABLED !== "undefined") {
        currentSelectedCarrier = $(currentSelectedCarrier).val().replace(',', '');
        if (SATURDAY_IDS.indexOf(parseInt(currentSelectedCarrier)) > -1 && SATURDAY_SUPPLEMENT_ENABLED) {
            toggleSaturdaySelection(currentSelectedCarrier);
        }
    }

    // Listener for selection of the ChronoRelais carrier radio button
    body.on('click', '#js-delivery span.custom-radio > input[type=radio], input[name=id_carrier]', function (e) {
        resetCheckbox();
        toggleSaturdaySelection(e.target.value.replace(',', ''));
        $('#saturday_delivery')[0].scrollIntoView();
    });

    // Listen saturday supplement input change
    body.on('change', "input[name='saturday_delivery']", function (e) {
        const value = e.target.checked;
        updateSaturdaySupplement(value);
    });
});
