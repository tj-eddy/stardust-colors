/* Check if jquery exist */
window.onload = function () {
    if (!window.jQuery) {
        (function () {
            const script = document.createElement("script");
            script.src = '/modules/chronopost/views/js/jquery-1.11.0.min.js';
            script.type = 'text/javascript';
            document.head.appendChild(script);
        })();
    }
}

var marker_group = null;
var defaultPostCode = "75001";

function capitalize(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

/**
 * Submit postCode change and rebuild markers
 */
function postcodeChangeEvent() {
    initRelaisMap(null, $('#relais_postcode').val(), null, cust_country);
}

/**
 * Toogle map when "Relais" carrier is selected
 *
 * @param address
 * @param codePostal
 * @param city
 * @param e
 * @returns {boolean}
 */
function toggleRelaisMap(address, codePostal, city, e) {
    var valueChecked = $("#js-delivery .custom-radio > input[type=radio]:checked").val();
    if (valueChecked !== undefined) {
        valueChecked = valueChecked.slice(0, -1);
    }

    var carrierChecked = $("input[name=id_carrier]:checked").val();
    if (carrierChecked !== undefined) {
        carrierChecked = carrierChecked.slice(0, -1);
    }

    var postCodeControls = $('#chrono_postcode_controls');
    var chronorelaisContainer = $('#chronorelais_container');

    if (valueChecked === CHRONORELAIS_AMBIENT_ID || carrierChecked === CHRONORELAIS_AMBIENT_ID_INT ||
        valueChecked === CHRONORELAIS_ID || carrierChecked === CHRONORELAIS_ID_INT ||
        valueChecked === RELAISEUROPE_ID || carrierChecked === RELAISEUROPE_ID_INT ||
        valueChecked === RELAISDOM_ID || carrierChecked === RELAISDOM_ID_INT ||
        valueChecked === TOSHOPDIRECT_ID || carrierChecked === TOSHOPDIRECT_ID_INT ||
        valueChecked === TOSHOPDIRECT_EUROPE_ID || carrierChecked === TOSHOPDIRECT_EUROPE_ID_INT) {
        if (typeof e !== "undefined") {
            e.stopPropagation();
        }

        // Show Chronorelais controls
        chronorelaisContainer.show();
        postCodeControls.show();

        initRelaisMap(address, codePostal, city, cust_country);
        return false;
    }

    // Hide Chronorelais controls
    chronorelaisContainer.hide();
    postCodeControls.hide();
}

/**
 * Initialize layers and build markers
 *
 * @param address
 * @param codePostal
 * @param city
 * @param country
 */
function initRelaisMap(address, codePostal, city, country) {
    if (typeof codePostal === 'undefined') {
        return;
    }

    // Get Leaflet map.
    if (relais_map === null) {
        relais_map = mapInit();

        if (relais_map === null) {
            return;
        }

        // Initialize imagery
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(relais_map);

        // Add markers layer
        marker_group = L.featureGroup();
        marker_group.addTo(relais_map);
    } else {
        // Clean old markers
        marker_group.clearLayers();
    }

    // Build all markers
    initRelaisMarkers(address, codePostal, city, country);
}

/**
 * Initialize Leaflet map
 *
 * @returns {*}
 */
function mapInit() {
    if ($('#chronorelais_map').length === 0) {
        return null;
    }

    return L.map('chronorelais_map').setView([0, 0], 12);
}

/**
 * Save customer "relais"
 *
 * @param relaisID
 */
function associateRelais(relaisID) {
    if ($("#checkout-delivery-step").hasClass("-current")) {
        $.ajax({
            url: path + '/async/storePointRelais.php?relaisID=' + relaisID + '&cartID=' + cartID + '&customerFirstname=' + encodeURIComponent(cust_firstname) + '&customerLastname=' + encodeURIComponent(cust_lastname)
        });
    }
}

/**
 * Create all "relais" list
 *
 * @param json
 */
function createAllPointRelais(json) {
    var htmlForTxtSection = '';
    var data = JSON.parse(json);

    if (data.errorCode !== 0) {
        alert('Code postal erroné, merci de modifier le code postal dans votre adresse de livraison.');

        // init with original zipcode
        cust_address = defaultPostCode + ", France";
        $('#relais_postcode').val(defaultPostCode);
        initRelaisMap(cust_address, defaultPostCode);

        return;
    }

    if (data.hasOwnProperty("listePointRelais") && data.listePointRelais) {
        var listePointsRelais = data.listePointRelais;

        let associateRelaisId = listePointsRelais[0].identifiant;
        if (listePointsRelais.length !== undefined) {
            for (var i = 0; i < listePointsRelais.length; i++) {
                // COMPAT WITH OLD RECHERCHEBT
                var id = listePointsRelais[i].identifiant;
                var nom = listePointsRelais[i].nom;
                var adresse = listePointsRelais[i].adresse1;
                var postalCode = listePointsRelais[i].codePostal;
                var localite = listePointsRelais[i].localite;

                // Create marker
                createRelaisMarker(listePointsRelais[i]);
                chronodata[id] = listePointsRelais[i];

                // Build corresponding "relais" input
                htmlForTxtSection += '<div class="checkbox"><label><input type="radio" name="chronorelaisSelect" id="bt' + id + '" value="' + id + '"';
                if (i === 0 && data['lastPR'] === '' || data['lastPR'] === id) {
                    htmlForTxtSection += " checked";
                    associateRelaisId = id;
                }
                htmlForTxtSection += '/> ' + nom + ' - ' + adresse + ' - ' + postalCode + ' ' + localite + '</label></div>';
            }
            associateRelais(associateRelaisId);
        } else {
            // COMPAT WITH OLD RECHERCHEBT
            var id = listePointsRelais.identifiant;
            var nom = listePointsRelais.nom;
            var adresse = listePointsRelais.adresse1;
            var postalCode = listePointsRelais.codePostal;
            var localite = listePointsRelais.localite;

            // Create marker
            createRelaisMarker(listePointsRelais);
            chronodata[id] = listePointsRelais;

            // Build corresponding "relais" input
            htmlForTxtSection += '<div class="checkbox"><label><input type="radio" name="chronorelaisSelect" id="bt' + id + '" value="' + id + '"';
            htmlForTxtSection += " checked";
            htmlForTxtSection += '/> ' + nom + ' - ' + adresse + ' - ' + postalCode + ' ' + localite + '</label></div>';
            associateRelais(listePointsRelais.identifiant);
        }

        // Add all relais to list
        $('#relais_txt').html(htmlForTxtSection);

        // Listener for BT select in radio list
        $('body').on('change', 'input[name="chronorelaisSelect"]', function () {
            var relaisId = $('input[name="chronorelaisSelect"]:checked').val();
            associateRelais(relaisId);
            openMarker(relaisId);
        });

        // Open closest marker
        openMarker($('input[name="chronorelaisSelect"]:checked').val());
        relais_map.fitBounds(marker_group.getBounds(), {paddingTopLeft: [0, 350]});
    }
}

/**
 * Open marker corresponding to given "relais" ID
 *
 * @param relaisId
 */
function openMarker(relaisId) {
    if (typeof relaisId !== 'undefined') {
        map_markers[relaisId].openPopup();
    }
}

/**
 * Initialize all markers
 *
 * @param address
 * @param cp
 * @param city
 * @param country
 */
function initRelaisMarkers(address, cp, city, country) {
    let currentCarrier = $('.delivery-options input:checked').val();
    if (city === null) {
        $.ajax({
            url: path + '/async/getPointRelais2.php?city=unknown&codePostal=' + cp + '&country=' + country + '&carrier=' + currentCarrier,
            success: createAllPointRelais
        });
    } else {
        $.ajax({
            url: path + '/async/getPointRelais2.php?codePostal=' + cp + '&address=' + encodeURIComponent(address)
                + '&country=' + country + '&city=' + city + '&carrier=' + currentCarrier,
            success: createAllPointRelais
        });
    }
}

/**
 * Create Leaflet Marker for given "relais"
 *
 * @param relais
 */
function createRelaisMarker(relais) {
    var relaisIcon = L.icon({
        iconUrl: path + '/views/img/postal.png',
        iconSize: [45, 30]
    });

    var marker = L.marker([relais.coordGeolocalisationLatitude, relais.coordGeolocalisationLongitude], {icon: relaisIcon});

    marker.on('click', function (e) {
        $("#bt" + relais.identifiant).prop("checked", true);
    });

    map_markers[relais.identifiant] = marker;
    marker.addTo(marker_group).bindPopup(buildMarkerPopup(relais));
}

/**
 * Build HTML Marker popup for given "relais"
 *
 * @param relais
 * @returns {string}
 */
function buildMarkerPopup(relais) {
    var popup = '<div class="pointRelais"><h4>'
        + relais.nom + '</h4><p>' + relais.adresse1 + '<br/ >' + relais.codePostal
        + ' ' + relais.localite + '</p><h5>' + trad_horaire_ouverture + '</h5><table><tbody>';

    for (var i = 0; i < relais.listeHoraireOuverture.length; i++) {
        var day = relais.listeHoraireOuverture[i];
        if (day.horairesAsString !== null) {
            popup += '<tr class="first_item item"><td>' + capitalize(days[day.jour - 1]) + '</td><td>'
                + day.horairesAsString + '</td></tr>';
        }
    }

    popup += '</tbody></table>'
        + '<p class="text-right"><input type="hidden" name="btID" value="' + relais.identifiant
        + '"/><a class="button_large btselect" href="javascript:;" class="pull-right">' + trad_selectionner + ' »</a></p>'
        + '</div>';

    return popup;
}

// Triggered on BT select from popup
function btSelect() {
    var btID = $(this).parent().children('input').val();
    var mObj = $('#relais_txt input[value=' + btID + ']');
    mObj.click();

    associateRelais(btID);

    $("html, body").animate({scrollTop: $('#relais_txt').offset().top}, 500);
    $("#" + btID).prop('checked', true);
}

function cleanContainers() {
    var dummyContainer = $('#chronorelais_dummy_container');

    // move in DOM to prevent compatibility issues with Common Services' modules
    if ($("#chronorelais_container").length > 0) {
        dummyContainer.remove();
    } else {
        dummyContainer.attr('id', 'chronorelais_container');
    }

    let checkedDeliveryOption = $("input[name*='delivery_option[']:checked");
    if (checkedDeliveryOption.length === 0 || typeof checkedDeliveryOption === 'undefined') {
        return;
    }

    let checkedCarrier = checkedDeliveryOption.val().substring(0, checkedDeliveryOption.val().indexOf(','));
    if (checkedCarrier === CHRONORELAIS_AMBIENT_ID || checkedCarrier === CHRONORELAIS_ID || checkedCarrier === RELAISEUROPE_ID ||
        checkedCarrier === RELAISDOM_ID || checkedCarrier === TOSHOPDIRECT_ID || checkedCarrier === TOSHOPDIRECT_EUROPE_ID) {
        let container = $("[id^=delivery_option]:checked").parents('.delivery-option').children('label');
        if (container.length === 0) {
            container = $('#extra_carrier');
        }

        $('#chronorelais_container').detach().insertAfter(container);
    }
}

$(function () {
    // Return if variable are undefined
    if (typeof CHRONORELAIS_AMBIENT_ID === 'undefined' && typeof CHRONORELAIS_ID === 'undefined' &&
        typeof RELAISEUROPE_ID === 'undefined' && typeof RELAISDOM_ID === 'undefined' &&
        typeof TOSHOPDIRECT_ID === 'undefined' && typeof TOSHOPDIRECT_EUROPE_ID === 'undefined') {
        return false;
    }

    var body = $("body");

    body.on("click", ".gm-style img", function (event) {
        event.preventDefault();

        var deliveryStep = $("#checkout-delivery-step");
        if (!deliveryStep.hasClass("-current")) {
            deliveryStep.addClass("-current");
        }

        if (!deliveryStep.hasClass("js-current-step")) {
            deliveryStep.addClass("js-current-step");
        }
    });

    // Clean container on load
    cleanContainers();
    toggleRelaisMap(cust_address_clean, cust_codePostal, cust_city);

    // Listener for selection of the ChronoRelais carrier radio button
    body.on('click', '#js-delivery .custom-radio > input[type=radio], input[name=id_carrier]', function (e) {
        cleanContainers();
        toggleRelaisMap(cust_address_clean, cust_codePostal, cust_city, e);

        var value = parseInt($(this).val());
        if (value === parseInt(CHRONORELAIS_AMBIENT_ID) || value === parseInt(CHRONORELAIS_ID) ||
            value === parseInt(RELAISEUROPE_ID) || value === parseInt(RELAISDOM_ID) ||
            value === parseInt(TOSHOPDIRECT_ID) || value === parseInt(TOSHOPDIRECT_EUROPE_ID)) {
            if ($('#chronorelais_container').length) {
                $('html, body').animate({
                    scrollTop: $('#chronorelais_container').offset().top
                }, 500);
            }
        }
    });

    // Listener for postcode change
    body.on('click', '#change_postcode', postcodeChangeEvent);

    body.on('keypress keydown keyup', '#relais_postcode', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            e.stopPropagation();
            postcodeChangeEvent();
            return false;
        }
    });

    // Listener for BT select in popup
    body.on("click", ".btselect", function (e) {
        btSelect.call(e.target, e);
    });

    // Listener for cart navigation to next step
    body.on('click', 'input[name=processCarrier]', function () {
        if ($('input[name=id_carrier]:checked').val() == carrierID && !$("input[name=chronorelaisSelect]:checked").val()) {
            alert($("#errormessage").val());
            $("html, body").animate({scrollTop: $('#relais_txt_cont').offset().top}, 500);
            return false;
        }
    });
});
