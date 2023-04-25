{*
*  Module made by Nukium
*
*  @author    Nukium
*  @copyright 2022 Nukium SAS
*  @license   All rights reserved
*
* ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
* ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
* ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
* ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
* ██   ████  ██████  ██   ██ ██  ██████  ██      ██
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*}
<div class="col-xs-12 gls-container">
{if !$gls_error || (isset($gls_error.code) && $gls_error.code == 998 || $gls_error.code == 999)}
    {if $force_gsm}
        <div class="form-group has- gls-mobile">
            <label for="gls-customer-mobile-{$id_carrier}" class="form-control-label">{$customer_mobile_title} {$name_carrier}</label>
            <input type="tel" id="gls-customer-mobile-{$id_carrier}" name="gls_customer_mobile_{$id_carrier}" class="form-control form-control- gls-customer-mobile" value="{$current_customer_mobile}" />
        </div>
    {/if}
    {if $is_relay_carrier}
        <div class="row">
            <div class="col-xs-12">
                <div class="gls-heading bg-primary text-white">
                    {l s='The following GLS Relais are available around your address' mod='nkmgls'}
                    <div class="gls-sub-heading text-white">
                        {l s='You have the choice between the following alternatives:' mod='nkmgls'}
                        <ul>
                            <li>{l s='a Point Relais®, among the approved merchants of the Mondial Relay network' mod='nkmgls'}</li>
                            <li>{l s='a Voisin-Relais of the Pickme network: network of trusted individuals in Paris, selected by our partner Pickme, identified in the list by their first names' mod='nkmgls'}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xs-12">
                <div class="gls-search bg-faded">
                    <a href="#gls-search-form" class="gls-search-form-toggler d-block" data-toggle="collapse">{l s='Find GLS Relais around another address' mod='nkmgls'}</a>
                    <div id="gls-search-form" class="collapse">
                        <div class="form-group input-group mb-0">
                            <input type="search" name="gls_search_postcode" id="gls-search-postcode" class="gls-search-input form-control" placeholder="{l s='Postcode (required)' mod='nkmgls'}" />
                            <input type="search" name="gls_search_city" id="gls-search-city" class="gls-search-input form-control" placeholder="{l s='City (optional)' mod='nkmgls'}" />
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary btn-block gls-search-relay">
                                <i class="material-icons search fa fa-search fa-fw">search</i><span class="btn-text">{l s='Search' mod='nkmgls'}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-xl-5 gls-relay-list"{if !$relay_points} style="display: none;"{/if}>
                {if $relay_points}
                    {assign var="labels" value="ABCDEFGHIJKLMNOPQRSTUVWXYZ"}
                    {foreach from=$relay_points key=k item=v}
                        <div class="row show relay-enable">
                            <div class="col-xs-12">
                                <div class="card gls-relay-infos">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-2 col-sm-2 col-lg-1 col-xl-2 col-select-relay">
                                                    <span class="custom-radio">
                                                        <input type="radio" name="gls_relay" id="gls-relay-{$v->Parcelshop->ParcelShopId}" class="gls-select-relay" data-glsrelayid="{$v->Parcelshop->ParcelShopId}" value="{$v->Parcelshop->ParcelShopId}"{if $current_relay == $v->Parcelshop->ParcelShopId} checked{/if} />
                                                    <span></span>
                                                    </span>
                                            </div>
                                            <label class="col-xs-10 col-sm-7 col-lg-9 col-xl-10" for="gls-relay-{$v->Parcelshop->ParcelShopId}">
                                                <div class="card-title">
                                                    <span class="gls-relay-name"><strong>{$v->Parcelshop->Address->Name1}</strong></span><span class="separator"> - </span><span class="gls-relay-label">{$labels.$k}</span>
                                                </div>
                                                <div class="card-text">
                                                    <div class="gls-relay-address">
                                                        {$v->Parcelshop->Address->Street1}
                                                        <br/>
                                                        {$v->Parcelshop->Address->ZipCode|cat:' '|cat:$v->Parcelshop->Address->City}
                                                    </div>
                                                </div>
                                            </label>
                                            <div class="col-xs-10 offset-xs-2 col-sm-3 offset-sm-0 col-lg-2 col-xl-10 offset-xl-2">
                                                <div class="gls-relay-distance text-muted">
                                                    {$v->AirLineDistance|string_format:"%.2f"} km
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                    <script type="text/javascript">
                        var glsGmapsMarkers = [];
                        {foreach from=$relay_points key=k item=v}
                            {capture name=opening_hours assign=opening_hours}
                            {strip}
                                {if isset($v->Parcelshop->GLSWorkingDay) && $v->Parcelshop->GLSWorkingDay|is_array && $v->Parcelshop->GLSWorkingDay|@count > 0}
                                    <table class="gls-relay-info-open-hours">
                                        <tbody>
                                            {foreach from=$trans_days key=day item=dname}
                                                <tr{if $smarty.now|date_format:"%u" - 1 == $day} class="today"{/if}>
                                                    {if isset($v->Parcelshop->GLSWorkingDay[$day])}
                                                        {assign var=dvalue value=$v->Parcelshop->GLSWorkingDay[$day]}
                                                        <th>{l s=$dname mod='nkmgls'}</th>
                                                        <td>{$dvalue->OpeningHours->Hours->From|substr:0:2}:{$dvalue->OpeningHours->Hours->From|substr:2:2}{literal} - {/literal}
                                                            {if $dvalue->Breaks->Hours->From != ''}
                                                                {$dvalue->Breaks->Hours->From|substr:0:2}:{$dvalue->Breaks->Hours->From|substr:2:2}<br/>{$dvalue->Breaks->Hours->To|substr:0:2}:{$dvalue->Breaks->Hours->To|substr:2:2} - {$dvalue->OpeningHours->Hours->To|substr:0:2}:{$dvalue->OpeningHours->Hours->To|substr:2:2}
                                                            {else}
                                                                {$dvalue->OpeningHours->Hours->To|substr:0:2}:{$dvalue->OpeningHours->Hours->To|substr:2:2}
                                                            {/if}
                                                        </td>
                                                    {else}
                                                        <th>{l s=$dname mod='nkmgls'}</th>
                                                        <td>{l s='Closed' mod='nkmgls'}</td>
                                                    {/if}
                                                </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                {/if}
                            {/strip}
                            {/capture}
                            glsGmapsMarkers.push({ldelim}"lat" : {$v->Parcelshop->GLSCoordinates->Latitude}, "lng" : {$v->Parcelshop->GLSCoordinates->Longitude}, "name" : "{$v->Parcelshop->Address->Name1|escape:'javascript'}", "id" : "{$labels.$k}", "infos" : "{$opening_hours|escape:'javascript' nofilter}"{rdelim});
                        {/foreach}
                    </script>
                {/if}
            </div>
            <div class="col-xs-12 col-xl-7 gls-relay-map"{if !$relay_points} style="display: none;"{/if}>
                <div id="gls-map"></div>
            </div>
        </div>
    {/if}
{/if}

{if $gls_error}
    <div class="alert alert-danger" role="alert">
        <i class="material-icons">error_outline</i><span data-title="{if $gls_error.code}{l s='Code: %s' mod='nkmgls' sprintf=[$gls_error.code]}{/if}" class="alert-text">{$gls_error.message}</span>
    </div>
{/if}

</div>
