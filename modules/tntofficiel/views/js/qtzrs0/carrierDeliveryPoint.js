/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

// On DOM Ready.
window.document.addEventListener('DOMContentLoaded', function () {

    // If not an order with TNT carrier.
    if (window.TNTOfficiel.link.back && !window.TNTOfficiel.order.isTNT) {
        return;
    }

    if (
    // If Google Map API not loaded.
    !(window.google && window.google.maps && window.google.maps.Map)
    // and Google Map config exist.
    && (window.TNTOfficiel && window.TNTOfficiel.config && window.TNTOfficiel.config.google && window.TNTOfficiel.config.google.map)
    ) {
        // Load Google Map API.
        var objJqXHR = TNTOfficiel_AJAX({
            "url": window.TNTOfficiel.config.google.map.url,
            "data": window.TNTOfficiel.config.google.map.data,
            "dataType": 'script',
            "cache": true
        });

        objJqXHR
        .done(function () {
            // Script loaded.
        });
    }

});



// Constructor
function TNTOfficiel_GMapMarkersConstrutor(elmtMapContainer, objGoogleMapsConfig) {
    return this.init(elmtMapContainer, objGoogleMapsConfig);
}

// Prototype
TNTOfficiel_GMapMarkersConstrutor.prototype = {

    // Google Map Default Config.
    objGMapsConfig: {
        "lat": 46.227638,
        "lng": 2.213749,
        "zoom": 4
    },

    // Google Map Object.
    objGMapMap: null,
    // Google Map Markers Area Boundaries.
    objGMapMarkersBounds: null,
    // Google Map Markers Collection.
    arrGMapMarkersCollection: [],
    // Google Map Markers Info Window (Bubble).
    objGMapMarkersInfoWindow: null,

    /**
     * Initialisation.
     */
    init: function init(elmtMapContainer, objGoogleMapsConfig) {
        // Extend Configuration.
        jQuery.extend(this.objGMapsConfig, objGoogleMapsConfig);

        // Google Map Object.
        this.objGMapMap = new window.google.maps.Map(elmtMapContainer, {
            center: new window.google.maps.LatLng(this.objGMapsConfig.lat, this.objGMapsConfig.lng),
            zoom: this.objGMapsConfig.zoom
        });

        // Init Markers.
        this.objGMapMarkersBounds = new window.google.maps.LatLngBounds();
        this.arrGMapMarkersCollection = [];
        this.objGMapMarkersInfoWindow = new window.google.maps.InfoWindow();

        return this;
    },
    /**
     *
     */
    addMarker: function addMarker(fltLatitude, fltLongitude, strTextLabel, strInfoWindowContent, objListeners) {
        var _this = this;

        var objGMapLatLng = new window.google.maps.LatLng(fltLatitude, fltLongitude);

        // Create a new Google Map Marker.
        var objGMapMarker = new window.google.maps.Marker({
            position: objGMapLatLng,
            label: {
                text: strTextLabel,
                color: '#FFFF',
                fontSize: '12px'
            },
            icon: {
                url: window.TNTOfficiel.link.front.shop + 'modules/' + window.TNTOfficiel.module.name + '/views/img/map/marker/blank.png',
                labelOrigin: new google.maps.Point(10, 13)
            }
        });
        // Add Marker to the Google Map.
        objGMapMarker.setMap(this.objGMapMap);
        // Extend Markers Area Boundaries.
        this.objGMapMarkersBounds.extend(objGMapMarker.getPosition() /* objGMapLatLng */);

        //objGMapMarker.getMap();

        // Bind Markers Events.
        jQuery.each(objListeners, function (strEventType, evtCallback) {
            // If callback is a function.
            if (jQuery.type(evtCallback) === 'function') {
                // Set Marker Event Listeners and Bind this to callback.
                objGMapMarker.addListener(strEventType, jQuery.proxy(function (objGMapEvent) {
                    // Default click action is to show InfoWindow (if any).
                    if (strEventType === 'click') {
                        this.objGMapMarkersInfoWindow.close();
                        if (strInfoWindowContent) {
                            this.objGMapMarkersInfoWindow.setContent(strInfoWindowContent);
                            this.objGMapMarkersInfoWindow.open(this.objGMapMap /* objGMapMarker.getMap() */, objGMapMarker);
                        }
                        // Adjust zoom min/max range.
                        objGMapMarker.map.setZoom(Math.max(Math.min(17, objGMapMarker.map.getZoom()),10));
                        // Update the Google Maps size.
                        this.trigger('resize', this.objGMapMap);
                        // Go to marker position.
                        objGMapMarker.map.panTo(objGMapMarker.getPosition());
                    }

                    return evtCallback.call(this, objGMapEvent);
                }, _this));
            }
        });

        // Add Marker to collection.
        this.arrGMapMarkersCollection.push(objGMapMarker);

        return objGMapMarker;
    },
    /**
     *
     */
    fitBounds: function () {
        // Fit Boundaries to display all markers.
        if (this.arrGMapMarkersCollection.length > 0) {
            this.objGMapMap.fitBounds(this.objGMapMarkersBounds);
        }

        // Bind event to callback to execute only once.
        window.google.maps.event.addListenerOnce(this.objGMapMap, 'bounds_changed', function () {
            // this === this.objGMapMap
            this.setZoom(Math.min(17, this.getZoom()));
        });

        // Update the Google Maps size.
        this.trigger('resize', this.objGMapMap);

        return this;
    },
    /**
     *
     */
    trigger: function (strEventType, objBind) {
        window.google.maps.event.trigger(objBind, strEventType);

        return this;
    }
};


/**
 *
 * @param strArgCarrierType
 * @param strDataB64
 * @constructor
 */
var TNTOfficiel_deliveryPointsBox = function (strArgCarrierType, strDataB64)
{
    var _this = this;

    // xett, pex
    this.strRepoType = strArgCarrierType;
    // DROPOFFPOINT, DEPOT.
    this.method = strArgCarrierType;
    // ClassName Plural Prefix.
    this.strClassNameRepoPrefixPlural = strArgCarrierType;
    // ClassName Prefix.
    this.strClassNameRepoPrefix = strArgCarrierType;

    this.strClassNameInfoBlocSelected = 'is-selected';

    this.CSSSelectors = {
        // Popin Content Container.
        // div#DEPOT.DEPOT
        popInContentContainer: '#' + this.method,
        // Popin Header Container.
        // div.DEPOT-header
        popInHeaderContainer: '.' + this.strClassNameRepoPrefixPlural + '-header',

        // Search form CP/Cities.
        // form#DEPOT_form.DEPOT-form
        formSearchRepo: 'form.' + this.strClassNameRepoPrefixPlural + '-form',
        // Repo List Container.
        // ul#DEPOT_list.DEPOT-list
        infoBlocListContainer: '.' + this.strClassNameRepoPrefixPlural + '-list',
        // Google Map Container.
        // div#DEPOT_map.DEPOT-map
        mapContainer: '.' + this.strClassNameRepoPrefixPlural + '-map',

        // All Repo Bloc Item Container.
        // li#DEPOT_item_<INDEX>.DEPOT-item
        infoBlocItemContainerCollection: '.' + this.strClassNameRepoPrefix + '-item',
        // Repo Selected Button Bloc Item.
        // button.DEPOT-item-select
        infoBlocItemButtonSelected: '.' + this.strClassNameRepoPrefix + '-item-select'

        // One Repo Bloc Item Container.
        // li#DEPOT_item_<CODE>.DEPOT-item
        //    '#' + this.method + '_item_' + id
    };

    // dropOffPoints, tntDepots List.
    this.arrRepoList = JSON.parse(TNTOfficiel_inflate(strDataB64)) || null;

    // If invalid repo list.
    if (jQuery.type(this.arrRepoList) !== 'array') {
        // Set empty repo list.
        this.arrRepoList = [];
    }

    // Auto submit on change.
    jQuery(this.CSSSelectors.formSearchRepo).find(':input').not(':submit').on('change', function () {
        var strPostCode = jQuery('#tnt_postcode').val();
        if (jQuery.type(strPostCode) === 'string' && strPostCode.length === 5) {
            jQuery(this).parents('form').first().submit();
        } else {
            // Disable form select, waiting content is loaded.
            jQuery('#tnt_city').prop('disabled', true);
        }
    });

    // On repo form search submit.
    jQuery(this.CSSSelectors.formSearchRepo).on('submit', function (objEvent) {

        var $htmlForm = jQuery(this);

        objEvent.preventDefault();

        // Get state.
        var boolPostCodeChanged = false;
        jQuery('#tnt_postcode').each(function (intIndex, element) {
            if (element.getAttribute('value') !== jQuery(element).val()) {
                boolPostCodeChanged = true;
            }
        });

        if (boolPostCodeChanged) {
            // Disable form input city, waiting content is loaded.
            jQuery('#tnt_city').prop('disabled', true);
        }

        var objLink = window.TNTOfficiel.link.front;
        var arrData = $htmlForm.serializeArray();
        if (window.TNTOfficiel.link.back) {
            objLink = window.TNTOfficiel.link.back;
            arrData.push({name: 'id_order', value: window.TNTOfficiel.order.intOrderID});
        }
        var strData = jQuery.param(arrData);

        // If test is valid, get all data for postcode and city and update the list.
        var objJqXHR = TNTOfficiel_AJAX({
            "url": objLink.module.boxDeliveryPoints,
            "method": 'GET',
            "data": strData,
            "dataType": 'html',
            "cache": false
        });

        // Disable all form input, waiting content is loaded.
        $htmlForm.find(':input').prop('disabled', true);

        objJqXHR
        .done(function (mxdData, strTextStatus, objJqXHR) {
            // Update PopIn Content.
            jQuery(_this.CSSSelectors.popInContentContainer).parent().html(mxdData);
        })
        .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
            window.location.reload();
        });
    });

    // On select button repo info bloc click.
    jQuery(this.CSSSelectors.infoBlocItemButtonSelected).on('click', function () {
        var intMarkerIndex = jQuery(this).parents(_this.CSSSelectors.infoBlocItemContainerCollection).attr('id').split('_').pop();

        var objLink = window.TNTOfficiel.link.front;
        var objData = {
            "product": TNTOfficiel_deflate(JSON.stringify(_this.arrRepoList[intMarkerIndex]))
        };
        if (window.TNTOfficiel.link.back) {
            objLink = window.TNTOfficiel.link.back;
            objData['id_order'] = window.TNTOfficiel.order.intOrderID;
        }

        var objJqXHR = TNTOfficiel_AJAX({
            "url": objLink.module.saveProductInfo,
            "method": 'POST',
            "data": objData,
            "dataType": 'html',
            "cache": false
        });

        objJqXHR
        .done(function (mxdData, strTextStatus, objJqXHR) {
            jQuery.fancybox.close();

            TNTOfficiel_PageSpinner();

            if (!window.TNTOfficiel.link.back) {
                // Delete existing Info.
                jQuery('.tntofficiel-shipping-method-info').remove();
            }

            window.location.reload();
        })
        .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
            window.location.reload();
        });
    });

    /**
     * Calc distance between two GPS coordinate.
     * @param objGMapLatLngA
     * @param objGMapLatLngB
     * @returns {number} Distance (Km).
     */
    this.getGPSDistance = function (objGMapLatLngA, objGMapLatLngB) {
        var a = (objGMapLatLngB.lat() - objGMapLatLngA.lat()) * Math.PI / 180,
            d = (objGMapLatLngB.lng() - objGMapLatLngA.lng()) * Math.PI / 180;
        a = Math.sin(a / 2) * Math.sin(a / 2)
            + Math.cos(objGMapLatLngA.lat() * Math.PI / 180)
            * Math.cos(objGMapLatLngB.lat() * Math.PI / 180)
            * Math.sin(d / 2)
            * Math.sin(d / 2);
        return (12756274 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))) / 1000;
    };

    /**
     * Update Google Maps Marker and Position.
     */
    this.eventGoogleMaps = function () {
        // If Google library available.
        if (window.google && window.google.maps) {
            var objTNTOfficielGMapMarkers = new TNTOfficiel_GMapMarkersConstrutor(
                jQuery(_this.CSSSelectors.mapContainer)[0],
                window.TNTOfficiel.config.google.map.default
            );

            // Prepare and returns data marker to add on the map.
            for (var intMarkerIndex = 0; intMarkerIndex < _this.arrRepoList.length; intMarkerIndex++) {

                var objRepoItem = _this.arrRepoList[intMarkerIndex];

                var strMarkerTextLabel = (intMarkerIndex + 1) + '';
                // Set Marker InfoWindow Content.
                var strInfoWindowContent = '\
                    <ul style="margin: 0;">\
                      ' + (this.strRepoType === 'DROPOFFPOINT' ? '<li>Code: <b>' + objRepoItem.xett + '</b></li>' : '') + '\
                      <li><b>' + objRepoItem.name + '</b></li>\
                      <li>' + (objRepoItem.address ? objRepoItem.address : objRepoItem.address1 + '<br />' + objRepoItem.address2) + '</li>\
                      <li>' + objRepoItem.postcode + ' ' + objRepoItem.city + '</li>\
                    </ul>';

                var strCSSSelectorRepoInfoBloc = '#' + _this.method + '_item_' + intMarkerIndex;

                var objGMapMarker = objTNTOfficielGMapMarkers.addMarker(
                    objRepoItem.latitude,
                    objRepoItem.longitude,
                    strMarkerTextLabel,
                    strInfoWindowContent,
                    {
                        // On Marker Click.
                        "click": jQuery.proxy(function (strCSSSelectorRepoInfoBloc, objGMapEvent) {
                            var strClassNameInfoBlocSelected = 'is-selected',
                                $elmtInfoBlocSelect = jQuery(strCSSSelectorRepoInfoBloc);

                            // Highlight Selected Marker Info.
                            jQuery(_this.CSSSelectors.infoBlocItemContainerCollection + '.' + strClassNameInfoBlocSelected)
                            .removeClass(strClassNameInfoBlocSelected);
                            $elmtInfoBlocSelect.addClass(strClassNameInfoBlocSelected);

                            // The event is the click on marker (not triggered from list).
                            if (objGMapEvent != null) {
                                // Scroll to item
                                _this.scrollY($elmtInfoBlocSelect);
                            }
                        }, null, strCSSSelectorRepoInfoBloc)
                    }
                );

                // On click on info bloc item, trigger click on marker.
                jQuery(strCSSSelectorRepoInfoBloc).off().on('click', jQuery.proxy(function (objGMapMarker) {
                    objTNTOfficielGMapMarkers.trigger('click', objGMapMarker);
                }, null, objGMapMarker));

            }

            objTNTOfficielGMapMarkers.fitBounds();

            var strPostCode = jQuery('#tnt_postcode').val();
            var strCity = jQuery('#tnt_city').val();
            if (strCity.length > 0)
            {
                var objGMapLatLngCenter = new window.google.maps.LatLng(46.227638,2.213749);
                var objGeocoder = new window.google.maps.Geocoder();
                objGeocoder.geocode({'address': strPostCode+', '+strCity}, function(results, status) {
                    if (status === window.google.maps.GeocoderStatus.OK) {
                        var Lat = results[0].geometry.location.lat();
                        var Lng = results[0].geometry.location.lng();
                        objGMapLatLngCenter = new window.google.maps.LatLng(Lat, Lng);
                        // Prepare and returns data marker to add on the map.
                        for (var intMarkerIndex = 0; intMarkerIndex < _this.arrRepoList.length; intMarkerIndex++) {
                            var objRepoItem = _this.arrRepoList[intMarkerIndex];
                            var objGMapLatLngMarker = new window.google.maps.LatLng(objRepoItem.latitude, objRepoItem.longitude);
                            var strCSSSelectorRepoInfoBloc = '#' + _this.method + '_item_' + intMarkerIndex;
                            var strDistance = (_this.getGPSDistance(objGMapLatLngCenter, objGMapLatLngMarker)).toFixed(0) + ' Km';
                            jQuery(strCSSSelectorRepoInfoBloc+' .location-distance').text(strDistance);
                        }
                    } else {
                        for (var intMarkerIndex = 0; intMarkerIndex < _this.arrRepoList.length; intMarkerIndex++) {
                            var strCSSSelectorRepoInfoBloc = '#' + _this.method + '_item_' + intMarkerIndex;
                            //var objGMapLatLngCenter = objTNTOfficielGMapMarkers.objGMapMap.center;
                            jQuery(strCSSSelectorRepoInfoBloc+' .location-distance').remove();
                        }
                    }

                });
            }

        }

    };

    /**
     * Prepare scrollbar for list item.
     */
    this.prepareScrollbar = function () {
        jQuery('#list_scrollbar_container').nanoScroller({
            preventPageScrolling: true
        });
    };

    /**
     * Scroll to item.
     * @param $elmtInfoBlocSelect HtmlElement to scroll to.
     */
    this.scrollY = function ($elmtInfoBlocSelect) {
        var $elmtContainer = jQuery('#list_scrollbar_container'),
            $elmtContent = jQuery('#list_scrollbar_content'),
            intPositionItem = parseInt($elmtInfoBlocSelect.offset().top + $elmtContent.scrollTop() - $elmtContainer.offset().top);
        $elmtContent.scrollTop(intPositionItem);
    };

    /**
     * Update scrollbar and google map position.
     */
    this.displayUpdate = function () {
        this.prepareScrollbar();
        this.eventGoogleMaps();
    };

    // Update !
    this.displayUpdate();

    return this;
};


/**
 * Open a Fancybox to choose a delivery point (for the products concerned).
 *
 * @param $elmtArgInputRadioVirtTNTChecked
 */
function TNTOfficiel_XHRBoxDeliveryPoints(intArgTNTCarrierID)
{
    var intTNTCarrierID = intArgTNTCarrierID | 0;

    var strTNTCarrierType = null;
    if (window.TNTOfficiel.carrier && window.TNTOfficiel.carrier.list
        && window.TNTOfficiel.carrier.list[intTNTCarrierID]
    ) {
        strTNTCarrierType = window.TNTOfficiel.carrier.list[intTNTCarrierID].carrier_type;
    }

    if (strTNTCarrierType !== 'DROPOFFPOINT'
    && strTNTCarrierType !== 'DEPOT'
    ) {
        return;
    }

    var objLink = window.TNTOfficiel.link.front;
    var objData = {};
    if (window.TNTOfficiel.link.back) {
        objLink = window.TNTOfficiel.link.back;
        objData['id_order'] = window.TNTOfficiel.order.intOrderID;
    }

    var objJqXHR = TNTOfficiel_AJAX({
        "url": objLink.module.boxDeliveryPoints,
        "method": 'POST',
        "dataType": 'html',
        "data": objData
    });

    objJqXHR
    .done(function (strHTMLDeliveryPointsBox, strTextStatus, objJqXHR) {
        if (strHTMLDeliveryPointsBox) {
            if (!!jQuery.prototype.fancybox) {
                jQuery.fancybox.open([
                    {
                        "type": 'inline',
                        "autoScale": true,
                        "autoDimensions": true,
                        "centerOnScroll": true,
                        "maxWidth": 1280,
                        "maxHeight": 768,
                        "fitToView": false,
                        "width": '100%',
                        "height": '100%',
                        "autoSize": false,
                        "closeClick": false,
                        "openEffect": 'none',
                        //"closeEffect": 'none',
                        "wrapCSS": 'fancybox-bgc',
                        "content": strHTMLDeliveryPointsBox,
                        "afterShow": function () {
                            // global object from AJAX HTML Content.
                            window.objTNTOfficiel_deliveryPointsBox.displayUpdate();
                        },
                        "onUpdate": function () {
                            window.objTNTOfficiel_deliveryPointsBox.displayUpdate();
                        },
                        "helpers": {
                            "overlay": {
                                "locked" : true,
                                "closeClick": false // prevents closing when clicking OUTSIDE fancybox.
                            }
                        }
                    }
                ], {
                    "padding": 10
                });
            }
        }
    })
    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
        window.location.reload();
    });
}
