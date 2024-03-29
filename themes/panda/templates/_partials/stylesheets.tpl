{**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{foreach $stylesheets.external as $stylesheet}
  <link rel="stylesheet" href="{$stylesheet.uri}" media="{$stylesheet.media}">
{/foreach}

{foreach $stylesheets.inline as $stylesheet}
  <style>
    {$stylesheet.content}
  </style>
{/foreach}


{if isset($sttheme.custom_css) && count($sttheme.custom_css)}
  {foreach $sttheme.custom_css as $css}
  <link href="{$css.url}" id="{$css.id}" rel="stylesheet" media="{$sttheme.custom_css_media}" />
  {/foreach}
{/if}
<style>
  @font-face {
    font-family: AllerDisplay-regular;
    font-weight: 600;
    font-display: swap;
    src: url({$urls.css_url}AllerDisplay.ttf);
  }
  @font-face {
    font-family: ProximaNovaCondensedBold;
    font-weight: 600;
    font-display: swap;
    src: url({$urls.css_url}ProximaNovaCondensedBold.ttf);
  }

  @font-face {
    font-family: GothamMedium;
    font-weight: 600;
    font-display: swap;
    src: url({$urls.css_url}GothamMediumRegular.ttf);
  }


  @font-face {
    font-family: GothamBook;
    font-weight: 600;
    font-display: swap;
    src: url({$urls.css_url}GothamBookRegular.otf);
  }

  @font-face {
    font-family: GothamBold;
    font-weight: 600;
    font-display: swap;
    src: url({$urls.css_url}GothamBold.otf);
  }

</style>
