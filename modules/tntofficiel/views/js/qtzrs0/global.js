/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

(function() {
    window.TNTOfficiel = window.TNTOfficiel || {};
    try {
        var a = new Image, b = document.createElement("canvas").getContext("2d");
        a.onload = function() {
            b.drawImage(a, 0, 0);
            window.TNTOfficiel.APNG = 0 === b.getImageData(0, 0, 1, 1).data[3];
        };
        a.src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACGFjVEwAAAABAAAAAcMq2TYAAAANSURBVAiZY2BgYPgPAAEEAQB9ssjfAAAAGmZjVEwAAAAAAAAAAQAAAAEAAAAAAAAAAAD6A+gBAbNU+2sAAAARZmRBVAAAAAEImWNgYGBgAAAABQAB6MzFdgAAAABJRU5ErkJggg==";
    } catch (c) {
    }
})();

function TNTOfficiel_CreatePageSpinner()
{
    // If no spinner created on page.
    if (jQuery('#TNTOfficielLoading').length === 0) {
        // Create spinner to be shown during ajax request.
        jQuery('body').append('\
<div id="TNTOfficielLoading" style="display: none">\
    <img id="loading-image" src="'+window.TNTOfficiel.link.image+'loader/loader-42'+(window.TNTOfficiel.APNG?'.png':'.gif')+'" alt="Loading..."/>\
</div>');
    }

    return jQuery('#TNTOfficielLoading');
}


function TNTOfficiel_PageSpinner(intArgTimeout)
{
    if (!(this instanceof TNTOfficiel_PageSpinner)) {
        return new TNTOfficiel_PageSpinner(intArgTimeout);
    }

    if (!(intArgTimeout != null && intArgTimeout > 1)) {
        intArgTimeout = 16 * 1000;
    }

    this.constructor.hdleList = this.constructor.hdleList || {};
    this.constructor.hdleLength = this.constructor.hdleLength || 0;

    this.show(intArgTimeout);
}

TNTOfficiel_PageSpinner.prototype.show = function (intArgTimeout)
{
    var _this = this;

    TNTOfficiel_CreatePageSpinner();
    jQuery('#TNTOfficielLoading').show();

    this.intTimeout = intArgTimeout;
    this.hdleTimeout = window.setTimeout(function() {
        _this.hide();
    }, this.intTimeout);

    this.constructor.hdleList[this.hdleTimeout] = this;
    ++this.constructor.hdleLength;

    return this;
};

TNTOfficiel_PageSpinner.prototype.hide = function ()
{
    if (!(this.hdleTimeout in this.constructor.hdleList)) {
        return this;
    }

    window.clearTimeout(this.hdleTimeout);
    delete this.constructor.hdleList[this.hdleTimeout];
    --this.constructor.hdleLength;

    if (this.constructor.hdleLength === 0) {
        jQuery('#TNTOfficielLoading').hide();
    }

    return this;
};

function TNTOfficiel_AJAX($objArgAJAXParameters)
{
    // Global jQuery AJAX event, excepted for request with option "global":false.
    $objArgAJAXParameters["global"] = false;

    var objPageSpinner = TNTOfficiel_PageSpinner(8 * 1000);

    var objJqXHR = jQuery.ajax($objArgAJAXParameters);
    objJqXHR
    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
        // console.error(objJqXHR.status + ' ' + objJqXHR.statusText);
        alert(jQuery('<span>'+window.TNTOfficiel.translate.errorConnection+'</span>').text());
    })
    .always(function () {
        objPageSpinner.hide();
    });

    return objJqXHR;
}


function TNTOfficiel_AdminAlert(arrArgAlert, strTitle)
{
    var objAlertType = {
        "success": 'success',
        "warning": 'warning',
        "error": 'danger'
    };

    if (strTitle == null) {
        strTitle = window.TNTOfficiel.module.title;
    }

    for (var strAlertType in objAlertType) {
        var strAlertClass = objAlertType[strAlertType];

        if (arrArgAlert
            && arrArgAlert[strAlertType]
            && arrArgAlert[strAlertType].length > 0
        ) {
            var $elmtAlert = jQuery('\
                <div class="bootstrap">\
                    <div class="alert alert-' + strAlertClass + '" >\
                        <button type="button" class="close" data-dismiss="alert">×</button>\
                        <h4>' + strTitle + '</h4>\
                        <ul class="list-unstyled"></ul>\
                    </div>\
                </div>');

            jQuery.each(arrArgAlert[strAlertType], function (index, value) {
                if (typeof value === 'string') {
                    // trim.
                    value = value.replace(/^\s+|\s+$/gi, '');
                    // If is a translation ID.
                    if (window.TNTOfficiel.translate[value]) {
                        value = jQuery('<span>'+window.TNTOfficiel.translate[value]+'</span>').text();
                    }
                    // If is a translation ID (BO).
                    else if (window.TNTOfficiel.translate.back[value]) {
                        value = jQuery('<span>'+window.TNTOfficiel.translate.back[value]+'</span>').text();
                    }
                    if (!/[.!?]$/gi.test(value)) {
                        value = value+'.';
                    }
                    $elmtAlert.find('ul').append(jQuery('<li></li>').append(window.document.createTextNode(value)));
                }
            });

            if ($elmtAlert.find('ul li').length > 0) {
                jQuery('#content').prepend($elmtAlert);
                // Force to show alert on top of page
                // On load after redirection.
                jQuery(window).on('load', function () {window.setTimeout(function(){jQuery(window).scrollTop(0);}, 1);});
                // Or after a page was loaded.
                jQuery(window).scrollTop(0);
            }
        }
    }
}

// On DOM Ready.
window.document.addEventListener('DOMContentLoaded', function () {

    /*
     Display error.
     */
    if (window.TNTOfficiel && window.TNTOfficiel.alert) {
        TNTOfficiel_AdminAlert(window.TNTOfficiel.alert);
    }

});
