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
<script type="text/javascript">
	if (window.top) {
        {if isset($redirect_order) && $redirect_order == true}
            window.top.location.href = '{$url|escape:'htmlall':'UTF-8'}';
        {else}
            window.top.location.href = '{$link->getPageLink('order-confirmation', true, (int)$id_lang, $params, false, $id_shop|escape:'htmlall':'UTF-8')}';
        {/if}
    }
</script>