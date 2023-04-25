{**
* TNT OFFICIAL MODULE FOR PRESTASHOP.
*
* @author    Inetum <inetum.world>
* @copyright 2016-2021 Inetum, 2016-2021 TNT
* @license   https://opensource.org/licenses/MIT MIT License
*}

<p class="clearfix">
    <a class="btn btn-default tntoffciel-action-update-hra" href="javascript:void(0);" ><i class="icon-cogs"></i>
        {l s='Download and update the list of communes in hard-to-access areas' mod='tntofficiel'}</a>
    <br /><a class="_blank" href="{TNTOfficielCarrier::URL_HRA_HELP|escape:'html':'UTF-8'}">
        {l s='Consult the list of communes subject to a supplement difficult access areas.' mod='tntofficiel'}</a>
</p>

<script>

    // On DOM Ready.
    window.document.addEventListener('DOMContentLoaded', function () {

        jQuery(window.document)
        .on('click.'+window.TNTOfficiel.module.name, '.tntoffciel-action-update-hra', function(objEvent) {
            var objJqXHR = TNTOfficiel_AJAX({
                "url": window.TNTOfficiel.link.back.module.updateHRA,
                "method": 'GET',
                "dataType": 'json',
                "async": true
            });

            objJqXHR
            .done(function (objResponseJSON, strTextStatus, objJqXHR) {
                if(objResponseJSON.result) {
                    showSuccessMessage(jQuery('<span>'+window.TNTOfficiel.translate.back.updateSuccessfulStr+'</span>').text());
                } else {
                    showErrorMessage(jQuery('<span>'+window.TNTOfficiel.translate.errorDownloadingHRA+'</span>').text());
                }
            });

            objEvent.stopPropagation();
            objEvent.preventDefault();
            return false;
        });

    });

</script>