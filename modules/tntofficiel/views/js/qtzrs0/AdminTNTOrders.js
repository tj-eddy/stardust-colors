/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

// On DOM Ready.
window.document.addEventListener('DOMContentLoaded', function () {

    // Show loader on click on bulk action.
    jQuery('#form-order').on(
        'click',
        ['.bulk-actions ul.dropdown-menu li a[onclick^="sendBulkAction"][onclick*="submitBulkupdateOrderStatusorder"]',
        '.bulk-actions ul.dropdown-menu li a[onclick^="sendBulkAction"][onclick*="submitBulkupdateDeliveredorder"]'
        ].join(', '),
        function () {
            TNTOfficiel_PageSpinner();
        }
    );

    // Correcting action URL for bulk processing in orders list.
    // ex: Selecting order list and click bulk BT, then click bulk manifest but act like bulk BT.
    jQuery('#form-order button').on('click', function () {
        var $elmtForm = jQuery("#form-order");
        var strAttrAction = $elmtForm.attr('action');
        if (strAttrAction) {
            strAttrAction = strAttrAction
                .replace('&submitBulkgetManifestorder', '')
                .replace('&submitBulkgetBTorder', '');
            $elmtForm.attr('action', strAttrAction);
        }
    });

});
