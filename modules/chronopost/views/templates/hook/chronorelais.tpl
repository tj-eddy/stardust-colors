<script type="text/javascript">
    var chronodata = [];
    var map_enabled = "{$map_enabled|escape:'javascript':'UTF-8'}";
    var errormessage = "{l s='No pickup point has been selected !\nPlease select a pickup point to continue.' mod='chronopost'}";

    if (typeof relais_map === 'undefined') {
        var relais_map = null;
    }

    if (typeof map_markers === 'undefined') {
      var map_markers = new Array();
    }

    document.addEventListener('DOMContentLoaded', function load() {
      if (!window.jQuery) return setTimeout(load, 50);
      
      if ($('body').hasClass('order-opc')) {
        if (relais_map) {
          relais_map.remove();
          relais_map = mapInit();
        }
        cleanContainers();
        toggleRelaisMap($("#cust_address_clean").val(), $("#cust_codepostal").val(), $("#cust_city").val());
      }
    }, false);
    
    // Translations
    var trad_horaire_ouverture = "{l s='Opening hours' mod='chronopost'}";
    var trad_selectionner = "{l s='Select' mod='chronopost'}";
    var trad_fermer = "{l s='Close' mod='chronopost'}";
    var trad_ferme = "{l s='Closed' mod='chronopost'}";
    var days = [
        "{l s='Monday' mod='chronopost'}",
        "{l s='Tuesday' mod='chronopost'}",
        "{l s='Wednesday' mod='chronopost'}",
        "{l s='Thursday' mod='chronopost'}",
        "{l s='Friday' mod='chronopost'}",
        "{l s='Saturday' mod='chronopost'}",
        "{l s='Sunday' mod='chronopost'}",
    ];
</script>

<input type="hidden" id="cust_address_clean" name="cust_address_clean"
       value="{$cust_address_clean|escape:'javascript':'UTF-8'}"/>

<input type="hidden" id="cust_codepostal" name="cust_codepostal"
       value="{$cust_codePostal|escape:'javascript':'UTF-8'}"/>

<input type="hidden" id="cust_city" name="cust_city" value="{$cust_city|escape:'javascript':'UTF-8'}"/>

<input type="hidden" id="errormessage" name="errormessage"
       value="{l s='No pickup point has been selected !\nPlease select a pickup point to continue.' mod='chronopost'}"/>

<div id="chronorelais_dummy_container" style="{if isset($opc) && $opc!=true}display:none;{/if}" class="container-fluid chronopost">
    <p class="chronorelais_informations italic bold" {if $is_fresh_account==0}style="display:none"{/if}>
        {l s='*Only dry products (room temperature) are eligible for delivery in a relay point. If your basket contains fresh or frozen products, please select home delivery.' mod='chronopost'}
    </p>

    <h3>{l s='Select a pickup point for delivery' mod='chronopost'}</h3>
    <div class="row">
        <div class="chronorelais_informations col-lg-6">{l s='Select a pickup point here below then confirm by choosing \'Select\'' mod='chronopost'}</div>

        <div class="col-lg-6" id="chrono_postcode_controls">
            <div class="input-group">
                <input type="text" name="relais_postcode" class="form-control"
                       value="{$cust_codePostal|escape:'htmlall':'UTF-8'}" id="relais_postcode"/>
                <span class="input-group-btn">
                    <button class="btn btn-info" id="change_postcode"
                            type="button">{l s='Change my postcode' mod='chronopost'}</button>
                  </span>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="chronorelais_map" class="col-xs-12" {if $map_enabled==0}style="display:none"{/if}></div>
    </div>
    <div id="relais_txt_cont">
        <h4>{l s='Closest pickup points' mod='chronopost'}</h4>
        <div id="relais_txt"></div>
    </div>
</div>
