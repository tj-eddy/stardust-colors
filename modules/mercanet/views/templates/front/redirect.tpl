{*
* 1961-2016 BNP Paribas
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Quadra Informatique <modules@quadra-informatique.fr>
*  @copyright 1961-2016 BNP Paribas
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  
*}
{capture name=path}{$module_display_name|escape:'htmlall':'UTF-8'}{/capture}
{if $use_iframe == true}
    <iframe id="mercanet-iframe" onload="resizeIframe(this)" src="{$link->getModuleLink('mercanet', 'iframe', ['data_mercanet' => $data_mercanet, 'seal' => $seal], true)|escape:'htmlall':'UTF-8'}" scrolling="yes" frameborder="0">
    </iframe>
    <div class="clearfix"></div>
    <script type="text/javascript">
        function resizeIframe(obj) {
            var min_height = 200;
            obj.style.height = obj.contentWindow.document.body.scrollHeight + min_height +'px';
        }
    </script>
{else}
    <div>
        <h3>{l s='You will be redirected to payment platform in a few seconds.' mod='mercanet'}</h3>
        <p>
            <a href="javascript:void(0)" onclick="document.getElementById('mercanet_form').submit();">{l s='Please click here if you are not automatically redirected.' mod='mercanet'}</a>
        </p>
        <form id="mercanet_form" method="POST" action="{$url_mercanet|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="Data" value="{$data_mercanet|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" name="Encode" value="base64" />
            <input type="hidden" name="InterfaceVersion" value="{$interface_version|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" name="Seal" value="{$seal|escape:'htmlall':'UTF-8'}" />
        </form>
    </div>
    <script type="text/javascript">
        {literal}
            window.onload = function () {
                document.getElementById('mercanet_form').submit();
            };
        {/literal}
    </script>
{/if}