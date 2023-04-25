$(document).ready(function () {
    if (Array.isArray(lt_history_link) === false) {
        var lt_numbers = '';
        $.each(lt_history_link, function (index, value) {
            lt_numbers += value['type'] + '<a href="' + value['link'] + '">' + index + '</a> ' +
                '(<a href="" data-lt="' + value['lt'] + '" data-lt-reference="' + value['lt_reference'] + '" class="printSkybill">Imprimer</a>|' +
                '<a href="" data-lt="' + value['lt'] + '" class="cancelSkybill">Annuler</a>)' +
                '<br>';
        });

        if ($('span.shipping_number_show').length) {
            $('span.shipping_number_show a').remove();
            $('span.shipping_number_show').each(function () {
                $(this).html(lt_numbers);
                return false;
            });
        } else if ($('#orderShippingTabContent td:not(.text-right) a').length) {
            $('#orderShippingTabContent td:not(.text-right) a').replaceWith(lt_numbers);
        }

        setInactive();
    }

    $("input[name='return']").on('click', function (e) {
        e.preventDefault();
        $(this).prop('disabled', true);
        $("#chronoSubmitButton").prop('disabled', true);
        var orderId = $("input[name='orderid']").val();
        var weight = "";
        var length = "";
        var width = "";
        var height = "";

        $('input[name="height[]"]').each(function (index) {
            height += "&height[" + index + "]=" + $(this).val();
        });

        $('input[name="weight[]"]').each(function (index) {
            weight += "&weight[" + index + "]=" + $(this).val();
        });

        $('input[name="length[]"]').each(function (index) {
            length += "&length[" + index + "]=" + $(this).val();
        });

        $('input[name="width[]"]').each(function (index) {
            width += "&width[" + index + "]=" + $(this).val();
        });

        $.ajax({
            url: path + '/async/checkColis.php?orderId=' + orderId + weight + length + width + height,
            success: function (data) {
                var result = JSON.parse(data);
                if (result['error'] !== 0) {
                    var message = "<div class='alert alert-danger'><button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button><div class='alert-text'>" + result['message'] + "</div></div>";
                    if ($("#ajaxBox").length === 0) {
                        $("<div id='ajaxBox'>").insertAfter($("#ajax_confirmation"));
                    }

                    $("#ajaxBox").html(message);

                    if ($("#content").length) {
                        $("#ajaxBox").prependTo($("#content"));
                    }

                    $("#ajaxBox").show();
                    $("html, body").animate({scrollTop: 0}, 500);
                    resetFormButtons();
                    return false;
                }

                $('<input />').attr('type', 'hidden')
                    .attr('name', "return")
                    .attr('value', "true")
                    .appendTo('#chrono_form');
                $("#chrono_form").submit();
                $(this).prop('disabled', true);
            }
        });

        return true;
    });

    $("input[name=\"create\"]").on('click', function (e) {
        e.preventDefault();
        checkDimensions();
    });

    function checkDimensions() {
        var orderId = $("input[name='orderid']").val();
        var weight = "";
        var length = "";
        var width = "";
        var height = "";

        $('input[name="height[]"]').each(function (index) {
            height += "&height[" + index + "]=" + $(this).val();
        });

        $('input[name="weight[]"]').each(function (index) {
            weight += "&weight[" + index + "]=" + $(this).val();
        });

        $('input[name="length[]"]').each(function (index) {
            length += "&length[" + index + "]=" + $(this).val();
        });

        $('input[name="width[]"]').each(function (index) {
            width += "&width[" + index + "]=" + $(this).val();
        });

        $.ajax({
            url: path + '/async/checkColis.php?orderId=' + orderId + weight + length + width + height,
            success: function (data) {
                var result = JSON.parse(data);
                if (result['error'] !== 0) {
                    var message = "<div class='alert alert-danger'><button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button><div class='alert-text'>" + result['message'] + "</div></div>";
                    if ($("#ajaxBox").length === 0) {
                        $("<div id='ajaxBox'>").insertAfter($("#ajax_confirmation"));
                    }

                    $("#ajaxBox").html(message);

                    if ($("#content").length) {
                        $("#ajaxBox").prependTo($("#content"));
                    }

                    $("#ajaxBox").show();

                    $("html, body").animate({scrollTop: 0}, 500);
                    resetFormButtons();

                    return false;
                }

                $("#chrono_form").submit();
                $(this).prop('disabled', true);
            }
        });
    }

    $("#chronoSubmitButton").on('click', function (e) {
        e.preventDefault();
        setInactive();
        $(this).prop('disabled', true);
        $('input[name="return"]').prop('disabled', true);

        if (lt_history.length) {
            e.preventDefault();

            var pdf_path = [];
            for (var i = 0; i < lt_history.length; i++) {
                pdf_path.push("/skybills/" + lt_history[i]['lt'] + ".pdf");
            }

            $.ajax({
                type: "POST",
                url: path + "/async/mergeSkybillPdf.php",
                data: {pdfs: pdf_path},
                success: function (response) {
                    reEnableFormButtons();
                    window.open(path + response);
                }
            });

            return false;
        } else if (lt) {
            UrlExists(path + "/skybills/" + lt + ".pdf", function (status) {
                if (status === 200) {
                    e.preventDefault();
                    document.location.href = path + "/skybills/" + lt + ".pdf";

                    return false;
                } else if (status === 404) {
                    checkDimensions();
                }
            });
        } else {
            checkDimensions();
        }
        return true;
    });
    
    $('#return_method').on('change', function(e){
        $('#return_method_contract').val($(this.options[this.selectedIndex]).data('contract'));
    });

    $("#chrono_form").on('submit', function (e) {
        setTimeout(function () {
            alert('Lettre de transport bien créée');
            window.location.reload();
        }, 3000);
    });

    $(".cancelSkybill").on('click', function (e) {
        e.preventDefault();

        var lt_number = $(this).data('lt');

        if (confirm("Êtes-vous sûr de vouloir annuler cet envoi ? La lettre de transport associée sera inutilisable.")) {
            let current_order = null;
            if ($("#shipping_carrier").data('order')) {
                current_order = $("#shipping_carrier").data('order');
            } else if (id_order) {
                current_order = id_order;
            }

            $.get(path + "/async/cancelSkybill.php", {
                skybill: lt_number,
                shared_secret: chronopost_secret,
                id_order: current_order
            }).done(function () {
                alert('Lettre de transport bien annulée.');
                location.reload();
            });
        }
    });

    $(".printSkybill").on('click', function (e) {
        e.preventDefault();
        var lt_number = $(this).data('lt');
        var lt_reference_number = $(this).data('lt-reference');
        if (lt_reference_number.length) {
            lt_number = lt_reference_number;
        }

        document.location.href = path + "/skybills/" + lt_number + ".pdf";

        return false;
    });

    function prepareDimensions(el) {
        var total_requested = el.val();
        if (total_requested <= 0) {
            total_requested = 1;
            $("#multiOne").val(1);
        }

        // Disable insurance if multiple packages
        if ($('#advalorem').length) {
            if (total_requested > 1) {
                $('#advalorem').attr('disabled', 'disabled');
                $('#advalorem_value').attr('disabled', 'disabled');
            } else {
                $('#advalorem').removeAttr('disabled');
                $('#advalorem_value').removeAttr('disabled');
            }
        }

        var total = $('#dimensions > div').length;
        var total_needed = total_requested - total;
        for (var i = 0; i < total_needed; i++) {
            $("#dimensions").append("<div class='dimensions-group'>" + dimensionsHtml + "</div>");
        }

        if (total_needed < 0) {
            var groups = $(".dimensions-group");
            for (var j = 1; j <= total; j++) {
                if (j > total_requested) {
                    var index = parseInt(j - 1);
                    groups.get(index).remove();
                }
            }
        }

        $('.chrono-datepicker').datetimepicker(
            {
                format: 'YYYY-MM-DD',
                sideBySide: true,
                icons: {
                    time: 'time',
                    date: 'date',
                    up: 'up',
                    down: 'down',
                },
            },
        );
    }

    var dimensionsElement = $("#dimensions .dimensions-group").first().clone();
    dimensionsElement.find('.datepicker input').addClass('chrono-datepicker');
    var dimensionsHtml = dimensionsElement.html();

    prepareDimensions($("#multiOne"));

    $("#multiOne").on('change', function (e) {
        prepareDimensions($(this));
    });

    chronopostSubmitButtonTxt = $("#chronoSubmitButton").val();
    chronopostReturnButtonTxt = $("#chrono_form input[name=return]").val();
});

var chronopostSubmitButtonTxt;
var chronopostReturnButtonTxt;

function setInactive() {
    $("#chronoSubmitButton").val("Ré-imprimer l'étiquette Chronopost");
}

function reEnableFormButtons() {
    var submitButton = $("#chronoSubmitButton");
    var returnButton = $("#chrono_form input[name=return]");
    submitButton.removeAttr('disabled');
    returnButton.removeAttr('disabled');
}

function resetFormButtons() {
    var submitButton = $("#chronoSubmitButton");
    var returnButton = $("#chrono_form input[name=return]");
    submitButton.val(chronopostSubmitButtonTxt);
    submitButton.removeAttr('disabled');
    returnButton.val(chronopostReturnButtonTxt);
    returnButton.removeAttr('disabled');
}

function UrlExists(url, cb){
    jQuery.ajax({
        url:      url,
        dataType: 'text',
        type:     'GET',
        complete:  function(xhr){
            if(typeof cb === 'function')
                cb.apply(this, [xhr.status]);
        }
    });
}
