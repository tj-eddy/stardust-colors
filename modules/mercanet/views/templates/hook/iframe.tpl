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
{* ONE TIME PAYMENT *}
{if isset($one_time) && $one_time == true}
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <p class="payment_module" id="mercanet_payment_button">
                <a href="{$link->getModuleLink('mercanet', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{$one_time_payment_name|escape:'htmlall':'UTF-8'}">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/logo_mercanet.png" alt="{$one_time_payment_name|escape:'htmlall':'UTF-8'}" width="57" height="57" />
                    {$one_time_payment_name|escape:'htmlall':'UTF-8'}
                </a>
            </p>
        </div>
    </div>
{/if}

{* NX PAYMENT *}
{if isset($nx_time) && $nx_time == true}
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div class="payment_module payment_mercanet" id="mercanet_nx_payment_button">
                    <p>
						<img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/logo_mercanet.png" alt="{$nx_time_payment_name|escape:'htmlall':'UTF-8'}" width="57" height="57" />
						{$nx_time_payment_name|escape:'htmlall':'UTF-8'}
					</p>
					<div id="nx_payments">
						<ul>
							{foreach $nx_time_payments as $nx_payment}
								<li>
									<a href="{$link->getModuleLink('mercanet', 'redirect', ['nx_time' => true, 'id_nx_payment'=>{$nx_payment.id_mercanet_nx_payment|escape:'htmlall':'UTF-8'}], true)|escape:'htmlall':'UTF-8'}" title="{$nx_payment.method_name|escape:'htmlall':'UTF-8'}" mod='mercanet'>
										{$nx_payment.method_name|escape:'htmlall':'UTF-8'}
									</a>
								</li>
							{/foreach}
						</ul>
					</div>
            </div>
        </div>
    </div>
{/if}

{* RECURRING PAYMENT *}
{if isset($recurring) && $recurring == true}
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <p class="payment_module" id="mercanet_payment_button">
                <a href="{$link->getModuleLink('mercanet', 'redirect', ['recurring' => true], true)|escape:'htmlall':'UTF-8'}" title="{$recurring_payment_name|escape:'htmlall':'UTF-8'}">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/logo_mercanet.png" alt="{$recurring_payment_name|escape:'htmlall':'UTF-8'}" width="57" height="57" />
                    {$recurring_payment_name|escape:'htmlall':'UTF-8'}
                </a>
            </p>
        </div>
    </div>
{/if}
