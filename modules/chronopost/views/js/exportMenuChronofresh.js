$(document).ready(function () {
    $("input.nbLT").each(function () {
        rowDimensions($(this));
    });

    $("input.nbLT").on('change', function () {
        rowDimensions($(this));
    });

    function rowDimensions(row) {
        var value = row.val();
        if (value < 1) {
            return;
        }

        var account_input_parent = null;
        if (row.hasClass('is-chronofresh')) {
            account_input_parent = row.closest('tr').find('#account').parent();
            var account_input = $(account_input_parent).find('select').first().prop('outerHTML');
            $(account_input_parent).html('');
        }

        var weight_input_parent = row.closest('tr').find('#weight').parent().parent();
        var weight_input = '<div class="grid-input">' + $(weight_input_parent).find('.grid-input').first().html() + '</div>';
        $(weight_input_parent).html('');

        var width_input_parent = row.closest('tr').find('#width').parent().parent();
        var width_input = '<div class="grid-input">' + $(width_input_parent).find('.grid-input').first().html() + '</div>';
        $(width_input_parent).html('');

        var height_input_parent = row.closest('tr').find('#height').parent().parent();
        var height_input = '<div class="grid-input">' + $(height_input_parent).find('.grid-input').first().html() + '</div>';
        $(height_input_parent).html('');

        var length_input_parent = row.closest('tr').find('#length').parent().parent();
        var length_input = '<div class="grid-input">' + $(length_input_parent).find('.grid-input').first().html() + '</div>';
        $(length_input_parent).html('');

        var dlc_input_parent = row.closest('tr').find('.chrono-datepicker').parent().parent();

        for (var i = 0; i < value; i++) {
            if (account_input_parent !== null) {
                $(account_input_parent).append(account_input);
            }

            $(weight_input_parent).append(weight_input);
            $(width_input_parent).append(width_input);
            $(height_input_parent).append(height_input);
            $(length_input_parent).append(length_input);

            $(dlc_input_parent).find('.chrono-datepicker:not(.hasDatepicker)').datepicker({
                prevText: '',
                nextText: '',
                dateFormat: 'yy-mm-dd'
            });
        }
    }
});
