/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

// On DOM Ready.
window.document.addEventListener('DOMContentLoaded', function () {

    /*
     * Form
     */

    var boolPreventUnsavedChange = false;
    jQuery('form#configuration_form')
    .on('change', function (objEvent) {
        boolPreventUnsavedChange = true;
    })
    .on('submit', function(objEvent) {
        // don't ask, do it.
        boolPreventUnsavedChange = false;

        var boolContextShop = jQuery('input:hidden[name="AdminConfigContextShop"]').length === 1;
        var strConfirmMessage = jQuery('<span>' + window.TNTOfficiel.translate.back.confirmApplyContext + '</span>').text();

        if (!boolContextShop) {
            var boolConfirm = window.confirm(strConfirmMessage);
            if (!boolConfirm) {
                objEvent.preventDefault();
                return false;
            }
        }
    });

    jQuery(window)
    .on('beforeunload unload', function(objEvent) {
        if (boolPreventUnsavedChange) {
            // Chrome force the behavior and display a confirm box :
            // Leave site ? Changes that you made may not be saved.
            var boolConfirm = window.confirm('Changes that you made may not be saved.');

            objEvent.stopPropagation();
            if (!boolConfirm) {
                objEvent.preventDefault();

                return false;
            }

            return true;
        }
    });

    /*
     * Zipcode & City
     */

    jQuery('#TNTOFFICIEL_CODE_POSTAL')
    .on('keyup change', function() {
        var
            $elmtZipCode = jQuery('#TNTOFFICIEL_CODE_POSTAL'),
            $elmtCities = jQuery('#TNTOFFICIEL_VILLE'),
            strInputZipCode = $elmtZipCode.val(),
            strInputCity = $elmtCities.val()
        ;

        // Do not perform a check if the postcode or the city is not entered.
        if (strInputZipCode.length === 5)
        {
            if ($elmtCities.data('zipCode') !== strInputZipCode) {
                $elmtCities.data('zipCode', strInputZipCode);
                // Get the cities list matching the postcode.
                var objJqXHR = TNTOfficiel_AJAX({
                    "url": window.TNTOfficiel.link.back.module.selectPostcodeCities,
                    "method": 'POST',
                    "dataType": 'json',
                    "data": {
                        "zipcode": strInputZipCode,
                        "city": strInputCity
                    },
                    "async": false
                });

                objJqXHR
                .done(function (objResponseJSON, strTextStatus, objJqXHR) {
                    // handle the response from the ajax request.
                    $elmtZipCode.val(objResponseJSON.strZipCode);
                    $elmtCities.data('zipCode', objResponseJSON.strZipCode);
                    $elmtCities.empty().prop('disabled', false);
                    jQuery.each(objResponseJSON.arrCitiesNameList, function (index, strCity) {
                        if (typeof strCity === 'string') {
                            $elmtCities.append(
                                jQuery('<option value="'+strCity+'" '+(objResponseJSON.strCity===strCity?'selected="selected"':'')+'>'+strCity+'</option>')
                            );
                        }
                    });
                });
            }
        }
        else
        {
            $elmtCities.data('zipCode', null);
            $elmtCities.empty().prop('disabled', true);
        }
    });

    /*
     * Type ramassage.
     */

    function displayByPassageType($this) {
        if ($this.val() == 'REGULAR') {
            $('#TNTOFFICIEL_HEURE_RAMASSAGE_DRIVER').parent().parent().removeClass("hidden");
            $('#TNTOFFICIEL_HEURE_RAMASSAGE_CLOSING').parent().parent().addClass("hidden");
            $('#TNTOFFICIEL_AFFICHAGE_RAMASSAGE_on').prop('disabled', true);
            $('#TNTOFFICIEL_AFFICHAGE_RAMASSAGE_off').prop('disabled', true);
            $('#TNTOFFICIEL_AFFICHAGE_RAMASSAGE_off').prop('checked', true);
        } else {
            $('#TNTOFFICIEL_HEURE_RAMASSAGE_DRIVER').parent().parent().addClass("hidden");
            $('#TNTOFFICIEL_HEURE_RAMASSAGE_CLOSING').parent().parent().removeClass("hidden");
            $('#TNTOFFICIEL_AFFICHAGE_RAMASSAGE_on').prop('disabled', false);
            $('#TNTOFFICIEL_AFFICHAGE_RAMASSAGE_off').prop('disabled', false);
        }
    }

    // onLoad
    var $this = $('#TNTOFFICIEL_TYPE_RAMASSAGE');
    displayByPassageType($this);

    $this.on('change', function() {
        displayByPassageType($this);
        if ($(this).val() != 'REGULAR') {

            var html = '<option value="0">00</option>' +
                '<option value="1">01</option>' + '<option value="2">02</option>' +
                '<option value="3">03</option>' + '<option value="4">04</option>' +
                '<option value="5">05</option>' + '<option value="6">06</option>' +
                '<option value="7">07</option>';

            $("#TNTOFFICIEL_HEURE_RAMASSAGE option:first").before(html);
            $("#TNTOFFICIEL_HEURE_RAMASSAGE").append('<option value="23">23</option>');
        } else {
            $("#TNTOFFICIEL_HEURE_RAMASSAGE option[value='0']").remove();
            $("#TNTOFFICIEL_HEURE_RAMASSAGE option[value='1']").remove();
            $("#TNTOFFICIEL_HEURE_RAMASSAGE option[value='2']").remove();
            $("#TNTOFFICIEL_HEURE_RAMASSAGE option[value='3']").remove();
            $("#TNTOFFICIEL_HEURE_RAMASSAGE option[value='4']").remove();
            $("#TNTOFFICIEL_HEURE_RAMASSAGE option[value='5']").remove();
            $("#TNTOFFICIEL_HEURE_RAMASSAGE option[value='6']").remove();
            $("#TNTOFFICIEL_HEURE_RAMASSAGE option[value='7']").remove();
            $("#TNTOFFICIEL_HEURE_RAMASSAGE option[value='23']").remove();
        }
    });

    /*
     * Zones
     */

    // Re init using custom placeholder.
    jQuery('select[name="TNTOFFICIEL_ZONE_1[]"], select[name="TNTOFFICIEL_ZONE_2[]"]')
    .chosen('destroy')
    .chosen({
        "placeholder_text_multiple": ' ...',
        "width": '100%'
    });

    var $elmtSelectZone1 = jQuery('select[name="TNTOFFICIEL_ZONE_1[]"]');
    var $elmtSelectZone2 = jQuery('select[name="TNTOFFICIEL_ZONE_2[]"]');
    var arrZone1Values = $elmtSelectZone1.val();
    var arrZone2Values = $elmtSelectZone2.val();

    // Init excluding options in Zone2 from values in Zone1.
    if (arrZone1Values !== null) {
        jQuery.each(arrZone1Values, function (index, value) {
            if (typeof value === 'string') {
                $elmtSelectZone2.find('option[value="'+value+'"]').prop('disabled', true);
            }
        });
    }
    // Init excluding options in Zone1 from values in Zone2.
    if ( arrZone2Values !== null ) {
        jQuery.each(arrZone2Values, function (index, value) {
            if (typeof value === 'string') {
                $elmtSelectZone1.find('option[value="'+value+'"]').prop('disabled', true);
            }
        } );
    }

    jQuery('select[name="TNTOFFICIEL_ZONE_1[]"], select[name="TNTOFFICIEL_ZONE_2[]"]')
    // Updating select for exclusion.
    .trigger('chosen:updated')
    .on('change', function (objEvt, objSet) {
        var $elmtSelectZone1 = jQuery('select[name="TNTOFFICIEL_ZONE_1[]"]');
        var $elmtSelectZone2 = jQuery('select[name="TNTOFFICIEL_ZONE_2[]"]');

        // Set option exclusion.
        if (objSet.selected) {
            if (this === $elmtSelectZone1[0]) {
                $elmtSelectZone2.find('option[value="'+objSet.selected+'"]').prop('disabled', true);
            }
            if (this === $elmtSelectZone2[0]) {
                $elmtSelectZone1.find('option[value="'+objSet.selected+'"]').prop('disabled', true);
            }
        }

        // Set option inclusion.
        if (objSet.deselected) {
            if (this === $elmtSelectZone1[0]) {
                $elmtSelectZone2.find('option[value="'+objSet.deselected+'"]').prop('disabled', false);
            }
            if (this === $elmtSelectZone2[0]) {
                $elmtSelectZone1.find('option[value="'+objSet.deselected+'"]').prop('disabled', false);
            }
        }

        // Update select.
        $elmtSelectZone1.trigger('chosen:updated');
        $elmtSelectZone2.trigger('chosen:updated');
    });

    /*
     * Statut
     */

    function swithParcelCheckEnable()
    {
        var boolDisabled = ($('input:radio[name="TNTOFFICIEL_OS_PARCEL_CHECK_ENABLE"]:checked').val() === '0');

        if (boolDisabled) {
            $('#TNTOFFICIEL_OS_PARCEL_CHECK_RATE').prop('disabled', true);
            $('#TNTOFFICIEL_OS_PARCEL_CHECK_RATE option:selected').text('');
        } else {
            $('#TNTOFFICIEL_OS_PARCEL_CHECK_RATE').prop('disabled', false);
            $('#TNTOFFICIEL_OS_PARCEL_CHECK_RATE option:selected')
                .text(($('#TNTOFFICIEL_OS_PARCEL_CHECK_RATE option:selected').val()/(60*60))+'h');
        }
    }

    swithParcelCheckEnable();
    $('input:radio[name="TNTOFFICIEL_OS_PARCEL_CHECK_ENABLE"]')
        .on('change', swithParcelCheckEnable);

});