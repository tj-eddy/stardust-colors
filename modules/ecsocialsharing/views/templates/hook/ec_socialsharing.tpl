{*
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL Ether Création
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL Ether Création is strictly forbidden.
* In order to obtain a license, please contact us: contact@ethercreation.com
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise à une licence commerciale
* concedée par la sociéte Ether Création
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence écrit de la part de la SARL Ether Création est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter la SARL Ether Création a l'adresse: contact@ethercreation.com
* ...........................................................................
*
*    @package ecreduction
*    @author Fiona Godard
*    @copyright Copyright (c) 2010-2014 S.A.R.L Ether Création (http://www.ethercreation.com)
*    @license Commercial license
*}
<!-- Block ec social sharing -->
{block name='ec_social_sharing'}
  {if $social_share_links}
    <div id="ec_socialsharing_product" class="social-sharing">
        {foreach from=$social_share_links item='social_share_link'}
            {if $social_share_link.class=='whatsapp'}
             {if $mobile}
                <a href="{$social_share_link.url|escape:'htmlall':'UTF-8'}" target="_blank"><p class="ec-social-sharing" id="{$social_share_link.id|escape:'htmlall':'UTF-8'}"><i class="icon-{$social_share_link.class|escape:'htmlall':'UTF-8'}"></i></p></a>
             {/if}
            {else}
                <a href="{$social_share_link.url|escape:'htmlall':'UTF-8'}" target="_blank"><p class="ec-social-sharing" id="{$social_share_link.id|escape:'htmlall':'UTF-8'}"><i class="icon-{$social_share_link.class|escape:'htmlall':'UTF-8'}"></i></p></a>
            {/if}
        {/foreach}
    </div>
  {/if}
{/block}
<!-- /Block ec social sharing -->