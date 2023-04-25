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
{capture name=path}
	<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
		{l s='My account' mod='mercanet'}
	</a>
	<span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
	<span class="navigation_page">{l s='Mercanet Wallet' mod='mercanet'}</span>
{/capture}

<h1 class="page-heading bottom-indent">{l s='My Wallet' mod='mercanet'}</h1>
<p class="info-title">{l s='If you want to manage your wallet, please click in the button "Manage your wallet" and you will be redirect to Mercanet to manage your wallet.' mod='mercanet'}</p>
<div id="block-wallet" class="block-center">
	{* WALLET *}
	<div class="clearfix main-page-indent">
		<form id="mercanet_wallet_form" method="POST" action="{$url|escape:'htmlall':'UTF-8'}">
			<input type="hidden" name="interfaceVersion" value="{$interface_version|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="requestDateTime" value="{$request_date_time|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="merchantWalletId" value="{$wallet_id|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="data" value="{$data|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" name="Encode" value="base64" />
			<input type="hidden" name="seal" value="{$seal|escape:'htmlall':'UTF-8'}" />
			<button type="submit" form="mercanet_wallet_form" class="btn btn-default button button-medium">
				<span>
					{l s='Manage your wallet' mod='mercanet'}
					<i class="icon-chevron-right right"></i>
				</span>
			</button>
		</form>
	</div>
</div>
<ul class="footer_links clearfix">
	<li>
		<a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
			<span>
				<i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='mercanet'}
			</span>
		</a>
	</li>
	<li>
		<a class="btn btn-default button button-small" href="{$base_dir|escape:'html':'UTF-8'}">
			<span><i class="icon-chevron-left"></i> {l s='Home' mod='mercanet'}</span>
		</a>
	</li>
</ul>