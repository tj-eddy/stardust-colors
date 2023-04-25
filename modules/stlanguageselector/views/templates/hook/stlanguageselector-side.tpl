{*
* 2007-2017 PrestaShop
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
*  @author    ST-themes <hellolee@gmail.com>
*  @copyright 2007-2017 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*}
<!-- MODULE st stlanguageselector -->
<nav class="st-menu" id="side_language">
	<div class="st-menu-header flex_container">
		<h3 class="st-menu-title">{l s='Language switcher' d='Shop.Theme.Panda'}</h3>
    	<a href="javascript:;" class="close_right_side" title="{l s='Close' d='Shop.Theme.Panda'}"><i class="fto-cancel-2"></i></a>
	</div>
	<div id="side_language_block" class="mobile_nav_box">
        <ul class="mo_mu_level_0 mobile_menu_ul">
			{foreach from=$languages key=k item=language}
			<li class="mo_ml_level_0 mo_ml_column">
				<div class="menu_a_wrap">
				<a href="{url entity='language' id=$language.id_lang}" rel="alternate" hreflang="{$language.iso_code}" title="{$language.name_simple}" class="mo_ma_level_0"><img src="{$urls.img_lang_url}{$language.id_lang}.jpg" alt="{$language.iso_code}" width="16" height="11" class="mar_r4" />{$language.name_simple}</a>
				</div>
			</li>
			{/foreach}
		</ul>
	</div>
</nav>
<!-- /MODULE st stlanguageselector -->