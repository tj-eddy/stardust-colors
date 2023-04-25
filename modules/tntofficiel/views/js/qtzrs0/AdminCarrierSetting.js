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

    if (jQuery('#TNTOFFICIEL_ZONES_ENABLED_on').length > 0 ) {
        var boolPreventUnsavedChange = false;
        jQuery('form#configuration_form')
        .on('change', function (objEvent) {
            boolPreventUnsavedChange = true;
        })
        .on('submit', function(objEvent) {
            // don't ask, do it.
            boolPreventUnsavedChange = false;
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
    }

    // Display/Hide Zone part
    function displayZoneBloc(name) {
        var selectVal = document.querySelector('input[name='+name+']:checked');
        if (selectVal) {
            if (selectVal.value == 0) {
                $('#tab-zone').addClass('hidden');
            } else {
                $('#tab-zone').removeClass('hidden');
            }
        }
    }

    // onLoad
    var name = 'TNTOFFICIEL_ZONES_ENABLED';
    displayZoneBloc(name);
    // onChange
    $("input[name="+name+"]:radio").change(function () {
        displayZoneBloc(name);
    });

    // Display/Hide Cloning list RG-32
    function displayCloningBloc(nameClone) {
        var selectVal = document.querySelector('input[name='+nameClone+']:checked');
        if (selectVal) {
            if (selectVal.value == 0) {
                $('#tab-cloning').addClass('hidden');
            } else {
                $('#tab-cloning').removeClass('hidden');
            }
        }
    }
    // RG-32
    // onLoad
    var nameClone = 'TNTOFFICIEL_ZONES_CLONING_ENABLED';
    displayCloningBloc(nameClone);
    // onChange
    $("input[name="+nameClone+"]:radio").change(function () {
        displayCloningBloc(nameClone);
    });
    //-----RG-32


    // Display/Hide Fields by Type of calcul fee (prix | poids)
    function displayFiledsZone($this) {
        var currentZone = $this.closest('.tab-pane');
        if ($this.val() == 'price') {
            currentZone.find('#field_price_sup').addClass('hidden');
            currentZone.find('#field_limit').addClass('hidden');
            currentZone.find('table tr:first th:first').html('Sera appliqué lorsque le prix TTC est < (€)');
            // display|hide the table(arrRangePriceList|arrRangeWeightList) by type
            currentZone.find('table #tab_price').removeClass('hidden');
            currentZone.find('table #tab_weight').addClass('hidden');
        } else {
            currentZone.find('#field_price_sup').removeClass('hidden');
            currentZone.find('#field_limit').removeClass('hidden');
            currentZone.find('table tr:first th:first').html('Sera appliqué lorsque le poids est =< (kg)');
            // display|hide the table(arrRangePriceList|arrRangeWeightList) by type
            currentZone.find('table #tab_weight').removeClass('hidden');
            currentZone.find('table #tab_price').addClass('hidden');
        }
    }
    var $fieldType = $('.TNTOFFICIEL_ZONES_TYPE');
    $fieldType.on('change', function() {
        displayFiledsZone($(this));
    });

    // add/delete a row in list Price on Zone bloc
    $('.add_row').click(function() {
        var intMaxTR = 128;
        var intMaxEmptyTR = 10;

        var elmtCurrentTab = $(this).closest('.tab-pane');
        var intTabKey = elmtCurrentTab.attr('id').replace(/^\S*?([0-9]*)$/gi, '$1') | 0;
        var strTypeSelect = ((elmtCurrentTab.find('.TNTOFFICIEL_ZONES_TYPE').val() === 'weight') ? 'weight' : 'price');
        var strTypeFee = ((strTypeSelect === 'weight') ? 'arrRangeWeightListCol' : 'arrRangePriceListCol');
        var strInputColName1 = 'TNTOFFICIEL_ZONES_CONF['+intTabKey+']['+strTypeFee+'1][]';
        var strInputColName2 = 'TNTOFFICIEL_ZONES_CONF['+intTabKey+']['+strTypeFee+'2][]';

        var elmtInputColName1 = $('input[name="'+strInputColName1+'"]');
        if (elmtInputColName1.length >= intMaxTR) {
            return;
        }
        if (elmtInputColName1.filter(function () {return $(this).val() === '';}).length >= intMaxEmptyTR) {
            return;
        }

        var elmtTBodyVisible = elmtCurrentTab.find('tbody[id="tab_'+strTypeSelect+'"]');
        var intLastTR = elmtTBodyVisible.find('tr:last').attr('id').replace(/^\S*?([0-9]*)$/gi, '$1') | 0;

        elmtTBodyVisible.find('#addr'+intLastTR).html('\
<td>\
    <div class="col-sm-6 col-sm-offset-3">\
        <input name="'+strInputColName1+'" type="text" class="form-control" />\
    </div>\
</td><td>\
    <div class="col-sm-6 col-sm-offset-3">\
        <input name="'+strInputColName2+'" type="text" class="form-control" />\
    </div>\
</td><td>\
    <a class="delete_row pull-right btn btn-default"><i class="icon-minus"></i></a>\
</td>'
        );

        elmtTBodyVisible.append('<tr id="addr'+(intLastTR+1)+'"></tr>');
    });

    $('.tab-pane').off('change.tntofficiel').on('change.tntofficiel', 'input[name^="TNTOFFICIEL_ZONES_CONF"]', function() {
        var field = $(this).attr('name').replace(/^TNTOFFICIEL_ZONES_CONF\[([0-9]+)\]\[([^\]]+)\]\S*$/gi, '$2');
        var obj = {
            'arrRangeWeightListCol1': 1,
            'arrRangeWeightListCol2': 6,
            'fltRangeWeightPricePerKg': 6,
            'fltRangeWeightLimitMax': 1,
            'arrRangePriceListCol1': 6,
            'arrRangePriceListCol2': 6,
            'fltHRAAdditionalCost': 6,
            'fltMarginPercent': 2
        };
        if (!(field in obj)) {
            return;
        }

        var strValRaw = $(this).val();
        if (strValRaw === '') {
            $(this).parent().removeClass('has-error');
            return;
        }
        var strVal = strValRaw.replace(',','.').replace(/^\s+|\s+$/gi,'');
        var nbrVal = parseFloat(strVal);
        var strValFixed = nbrVal.toFixed(obj[field]);
        if ((!(nbrVal >= 0)) || strValFixed.replace(/[0-9\.]/gi, '').length > 0) {
            $(this).parent().addClass('has-error');
            return;
        } else {
            $(this).parent().removeClass('has-error');
        }

         var strNbr = strValFixed.replace(/0+$/gi, '').replace(/\.$/gi, '');

        $(this).val(strNbr);
    });

    $('.delete_row').live('click', function() {
        $(this).closest('tr').remove();
    });

    // RG-26 remove lines too much
    $('#configuration_form_submit_btn').click(function () {
        $(".tab-content tbody tr").each(function() {
            if ($(this).find('td:first input').val() == '' && $(this).find('td:eq(1) input').val() == '') {
                $(this).remove();
            }
        });
    })
});