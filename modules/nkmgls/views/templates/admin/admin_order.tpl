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
{if $address_delivery}

    <div class="modal fade" id="glsChangeRelayModal" tabindex="-1" role="dialog" aria-labelledby="glsChangeRelayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if} gls-container">
                        {if !$gls_error || (isset($gls_error.code) && $gls_error.code == 998 || $gls_error.code == 999)}
                            {if $is_relay_carrier}
                                <div class="row">
                                    <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if}">
                                        <div class="gls-heading bg-primary text-white">
                                            {l s='The following GLS Relais are available around your address' mod='nkmgls'}
                                        </div>
                                    </div>
                                    <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if}">
                                        <div class="gls-search bg-faded">
                                            <a href="#gls-search-form" class="gls-search-form-toggler d-block" data-toggle="collapse">{l s='Find GLS Relais around another address' mod='nkmgls'}</a>
                                            <div id="gls-search-form" class="collapse">
                                                <div class="form-group input-group mb-0">
                                                    <input type="search" name="gls_search_postcode" id="gls-search-postcode" class="gls-search-input form-control" placeholder="{l s='Postcode (required)' mod='nkmgls'}" />
                                                    <input type="search" name="gls_search_city" id="gls-search-city" class="gls-search-input form-control" placeholder="{l s='City (optional)' mod='nkmgls'}" />
                                                    <input type="hidden" name="gls_search_country" value="{$address_country_code|escape:'quotes':'UTF-8'}" />
                                                </div>
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-primary btn-block gls-search-relay">
                                                        <i class="material-icons search fa fa-search fa-fw">search</i><span class="btn-text">{l s='Search' mod='nkmgls'}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if} col-lg-5 gls-relay-list"{if !$relay_points} style="display: none;"{/if}>
                                        {if $relay_points}
                                            <form action="" method="post">
                                                {assign var="labels" value="ABCDEFGHIJKLMNOPQRSTUVWXYZ"}
                                                {foreach from=$relay_points key=k item=v }
                                                    <div class="row show relay-enable">
                                                        <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if}">
                                                            <div class="card gls-relay-infos">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="{if $ps_version === '1.7'}col-1{else}col-xs-1{/if} col-select-relay">
                                                                            <span class="custom-radio">
                                                                                <input type="radio" name="gls_relay" id="gls-relay-{$v->Parcelshop->ParcelShopId|escape:'quotes':'UTF-8'}" class="gls-select-relay" data-glsrelayid="{$v->Parcelshop->ParcelShopId|escape:'quotes':'UTF-8'}" value="{$v->Parcelshop->ParcelShopId|escape:'quotes':'UTF-8'}"{if $parcel_shop_id == $v->Parcelshop->ParcelShopId} checked{/if} />
                                                                                <span></span>
                                                                            </span>
                                                                        </div>
                                                                        <label class="{if $ps_version === '1.7'}col-10{else}col-xs-10{/if} col-sm-8" for="gls-relay-{$v->Parcelshop->ParcelShopId|escape:'quotes':'UTF-8'}">
                                                                            <div class="card-title">
                                                                                <span class="gls-relay-name"><strong>{$v->Parcelshop->Address->Name1|escape:'htmlall':'UTF-8'}</strong></span><span class="separator"> - </span><span class="gls-relay-label">{$labels.$k|escape:'htmlall':'UTF-8'}</span>
                                                                            </div>
                                                                            <div class="card-text">
                                                                                <div class="gls-relay-address">
                                                                                    {$v->Parcelshop->Address->Street1|escape:'htmlall':'UTF-8'}
                                                                                    <br/>
                                                                                    {$v->Parcelshop->Address->ZipCode|cat:' '|cat:$v->Parcelshop->Address->City|escape:'htmlall':'UTF-8'}
                                                                                </div>
                                                                            </div>
                                                                        </label>
                                                                        <div class="{if $ps_version === '1.7'}col-10 offset-1 col-sm-3 offset-sm-0{else}col-xs-10 col-xs-offset-1 col-sm-3 col-sm-offset-0{/if} text-sm-right">
                                                                            <div class="gls-relay-distance text-muted">
                                                                                {$v->AirLineDistance|string_format:"%.2f"|escape:'htmlall':'UTF-8'} km
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                {/foreach}
                                            </form>
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
                                                                                <td>{$dvalue->OpeningHours->Hours->From|substr:0:2|escape:'htmlall':'UTF-8'}:{$dvalue->OpeningHours->Hours->From|substr:2:2|escape:'htmlall':'UTF-8'}{literal} - {/literal}
                                                                                    {if $dvalue->Breaks->Hours->From != ''}
                                                                                        {$dvalue->Breaks->Hours->From|substr:0:2|escape:'htmlall':'UTF-8'}:{$dvalue->Breaks->Hours->From|substr:2:2|escape:'htmlall':'UTF-8'}<br/>{$dvalue->Breaks->Hours->To|substr:0:2|escape:'htmlall':'UTF-8'}:{$dvalue->Breaks->Hours->To|substr:2:2|escape:'htmlall':'UTF-8'} - {$dvalue->OpeningHours->Hours->To|substr:0:2|escape:'htmlall':'UTF-8'}:{$dvalue->OpeningHours->Hours->To|substr:2:2|escape:'htmlall':'UTF-8'}
                                                                                    {else}
                                                                                        {$dvalue->OpeningHours->Hours->To|substr:0:2|escape:'htmlall':'UTF-8'}:{$dvalue->OpeningHours->Hours->To|substr:2:2|escape:'htmlall':'UTF-8'}
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
                                    <div class="{if $ps_version === '1.7'}col-12{else}col-xs-12{/if} col-lg-7 gls-relay-map"{if !$relay_points} style="display: none;"{/if}>
                                        <div id="gls-map"></div>
                                    </div>
                                </div>
                            {/if}
                        {/if}
                        {if $gls_error}
                            <div class="alert alert-danger" role="alert">
                                <i class="material-icons">error_outline</i><span data-title="{if $gls_error.code}{l s='Code: %s' mod='nkmgls' sprintf=[$gls_error.code]}{/if}" class="alert-text">{$gls_error.message|escape:'htmlall':'UTF-8'}</span>
                            </div>
                        {/if}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {if empty($parcel_shop_id)}
                        <input type="hidden" name="modal_gls_customer_mobile" id="modal_gls_customer_mobile" value="{$address_delivery->phone_mobile|escape:'quotes':'UTF-8'}">
                    {/if}
                    <input type="hidden" name="modal_gls_id_order" id="modal_gls_id_order" value="{$id_order|escape:'quotes':'UTF-8'}">
                    <button type="button" class="btn btn-tertiary-outline btn-lg" data-dismiss="modal">{l s='Cancel' mod='nkmgls'}</button>
                    <button type="button" class="btn btn-primary btn-lg" id="saveGlsChangeRelay">{l s='Save changes' mod='nkmgls'}</button>
                    <button class="btn btn-primary-reverse onclick btn-lg unbind GlsChangeRelayModalLoader" style="display: none;"></button>
                    <div class="alert alert-danger" role="alert" id="gls-error-modal" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

{/if}