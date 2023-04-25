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

        for (var i = 0; i < value; i++) {
            $(weight_input_parent).append(weight_input);
            $(width_input_parent).append(width_input);
            $(height_input_parent).append(height_input);
            $(length_input_parent).append(length_input);
        }
    }
});
