$(document).ready(function () {
    // Contract type switcher
    $('#chronoconfig').on('change', 'input[name=chronoparams\\[general\\]\\[accounttype\\]]', function () {
        handleContractType();
    });

    function handleContractType() {
        let $el = $('input[name=chronoparams\\[general\\]\\[accounttype\\]]:checked');

        if ($el.val() === '1') {
            $('.show_chronopost').show();
            $('.show_fresh').hide();
        } else if ($el.val() === '2') {
            $('.show_fresh').show();
            $('.show_chronopost').hide();
        }

        $('.shared_carrier').show(); // Always display shared carriers
    }

    handleContractType();

    $(".createCarrier").on('click', function (e) {
        e.preventDefault();

        var carrier = $(this).val();
        var contract = $('select[name=chronoparams\\[' + carrier + '\\]\\[account\\]]').val();

        $("body").addClass("loading");

        $.ajax({
            url: path + '/async/createCarrier.php?shared_secret=' + chronopost_secret + '&code=' + carrier + '&contract=' + contract,
            dataType: 'json'
        }).done(function (data) {
            $("body").removeClass("loading");
            if (!data['success']) {
                if (data['error']) {
                    alert(data['error']);
                    return
                }
                alert(failure_msg);
                return;
            }

            $('#createnewcarrier').attr('value', 'oui');

            alert(success_msg);
            window.location.href = window.location.href;
            return;
        });

        return false;
    });
});
