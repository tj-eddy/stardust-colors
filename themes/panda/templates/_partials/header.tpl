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
  <div class="container-slick">
    <div class="hero__title" id="animatedHeading">
      <div class="slick-dupe"><span class="hero__title-misc  |  animate"> Livraison sous 24 h en France</span></div>
      <div class="slick-dupe"><span class="hero__title-misc  |  animate">Livraison offerte en France
        métropolitaine pour 500€ d'achats</span></div>
    </div>
  </div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" integrity="sha512-XtmMtDEcNz2j7ekrtHvOVR4iwwaD6o/FUJe6+Zq+HgcCsk3kj4uSQQR8weQ2QVj1o0Pk6PwYLohm206ZzNfubg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    // Get titles from the DOM
    var titleMain  = $("#animatedHeading");
    var titleSubs  = titleMain.find("slick-active");

    if (titleMain.length) {

        titleMain.slick({
            arrows:false,
            autoplay: true,
            slidesToShow: 1,
            centerPadding: "10px",
            draggable: false,
            infinite: true,
            pauseOnHover: false,
            swipe: false,
            touchMove: false,
            vertical: true,
            speed: 2000,
            autoplaySpeed: 2000,
            Transform: true,
            // cssEase: 'cubic-bezier(0.645, 0.045, 0.355, 1.000)',
            adaptiveHeight: true,
        });

        // On init
        $(".slick-dupe").each(function(index, el) {
            $("#animatedHeading").slick('slickAdd', "<div>" + el.innerHTML + "</div>");
        });
    };
</script>
<style>
  /**
 * Title:
 *    Animations
 * Description:
 *    List all the animations of the site in one file
 * Sections:
 *    $. Keyframes
 *    $. Classes
 */

  /* $. Keyframes
  \*----------------------------------------------------------------*/

  @keyframes shrink {
    0% {
      color: green;
      transform: scale(2);
    }

    100% {
      transform: scale(1);
      color: grey;
    }
  }

  @keyframes grow {
    0% {
      transform: scale(1);
      color: grey;
    }

    100% {
      transform: scale(2);
      color: green;
    }
  }

  /* $. Classes
  \*----------------------------------------------------------------*/

  .animate--shrink {
    animation-duration: 1s;
    animation-name: shrink;
    animation-timing-function: "linear";
  }

  .animate--grow {
    animation-duration: 1s;
    animation-timing-function: "linear";
    animation-name: grow;
  }

  /* Slider */

  .slick-slider {
    position: relative;
    display: block;
    box-sizing: border-box;
    touch-callout: none;
    user-select: none;
    touch-action: pan-y;
    tap-highlight-color: transparent;
  }
  .slick-list {
    position: relative;
    overflow: hidden;
    display: block;
    margin: 0;
    padding: 0;

  &:focus {
     outline: none;
   }

  &.dragging {
     cursor: pointer;
     cursor: hand;
   }
  }
  .slick-slider .slick-track,
  .slick-slider .slick-list {
    transform: translate3d(0, 0, 0);
  }

  .slick-track {
    position: relative;
    left: 0;
    top: 0;
    display: block;

  &:before,
  &:after {
     content: "";
     display: table;
   }

  &:after {
     clear: both;
   }

  .slick-loading & {
    visibility: hidden;
  }
  }
  .slick-slide {
    float: left;
    height: 100%;
    min-height: 1px;
  [dir="rtl"] & {
    float: right;
  }
  img {
    display: block;
  }
  &.slick-loading img {
     display: none;
   }

  display: none;

  &.dragging img {
     pointer-events: none;
   }

  .slick-initialized & {
    display: block;
  }

  .slick-loading & {
    visibility: hidden;
  }

  .slick-vertical & {
    display: block;
    height: auto;
    border: 0;
    outline: none;

  &:focus,
  &:active,
  &::selection {
     outline: none !important;
     border: 0 !important;
     box-shadow: none;
   }
  }
  }
  .slick-arrow.slick-hidden {
    display: none;
  }

  .slick-current {
    position: relative;
  }

  .hero__title .slick-slide {
    overflow: hidden;
    padding: 20px 0;
  }

  .hero__title [aria-hidden] {
    transition: 1s;
  }
  /*
  .hero__title [aria-hidden="false"]{
      opacity: 1;
  }

  .hero__title [aria-hidden="true"] {
      opacity: 0;
  }
   */
  .hero__title .slick-current > span {
    box-sizing: border-box;
    display: block;
  @extend .animate--shrink;
  }

  .hero__title .slick-current + .slick-slide > span {
  @extend .animate--grow;
  }

  .no-js .hero__title .slick-dupe:nth-child(2) > span {
    padding: 1em;
  @extend .animate--grow;
  }

  .hero__title-misc {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: grey;
  }

  .container-slick {
    margin: 0 auto;
    width: 100%;.slick-slider
    background: white;;
    padding: 0 40px;
  }

</style>
{block name='header_banner'}
  {capture name="displayBanner"}{hook h="displayBanner"}{/capture}
  {if $smarty.capture.displayBanner}
  <div id="displayBanner" class="header-banner {if !$sttheme.sticky_displaybanner} hide_when_sticky {/if}">
    {$smarty.capture.displayBanner nofilter}
  </div>
  {/if}
{/block}
{block name='header_nav'}
  {capture name="displayNav1"}{hook h="displayNav1"}{/capture}
  {capture name="displayNav2"}{hook h="displayNav2"}{/capture}
  {capture name="displayNav3"}{hook h="displayNav3"}{/capture}
  {if $smarty.capture.displayNav1 || $smarty.capture.displayNav2 || $smarty.capture.displayNav3}
    <div id="top_bar" class="nav_bar {$sttheme.header_topbar_sep_type|default:'vertical-s'} {if !$sttheme.sticky_topbar} hide_when_sticky {/if}" >
      <div class="wide_container_box {if !$sttheme.fullwidth_topbar && $sttheme.responsive_max!=3}wide_container{/if}">
        <div id="top_bar_container" class="{if !$sttheme.fullwidth_topbar && $sttheme.responsive_max!=3}container{else}container-fluid{/if}">
          <div id="top_bar_row" class="flex_container">
            <nav id="nav_left" class="flex_float_left"><div class="flex_box">{$smarty.capture.displayNav1 nofilter}</div></nav>
            <nav id="nav_center" class="flex_float_center"><div class="flex_box">{$smarty.capture.displayNav3 nofilter}</div></nav>
            <nav id="nav_right" class="flex_float_right"><div class="flex_box">{$smarty.capture.displayNav2 nofilter}</div></nav>
          </div>
        </div>          
      </div>
    </div>
  {/if}
{/block}
{block name='mobile_header'}
{*similar code in the checkout/_partials/header.tpl*}
  <section id="mobile_bar" class="animated fast">
    <div class="container">
      <div id="mobile_bar_top" class="flex_container">
        {capture name="mobile_shop_logo"}
          <a class="mobile_logo" href="{$urls.base_url}" title="{$shop.name}">
              <img class="logo" src="{if $sttheme.mobile_logo_src}{$sttheme.mobile_logo_src}{else}{$shop.logo}{/if}" {if $sttheme.retina_logo_src } srcset="{$sttheme.retina_logo_src} 2x"{/if} alt="{$shop.name}"{if $sttheme.mobile_logo_src && $sttheme.mobile_logo_width} width="{$sttheme.mobile_logo_width}"{elseif isset($sttheme.st_logo_image_width) && $sttheme.st_logo_image_width} width="{$sttheme.st_logo_image_width}"{/if}{if $sttheme.mobile_logo_src && $sttheme.mobile_logo_height} height="{$sttheme.mobile_logo_height}"{elseif isset($sttheme.st_logo_image_height) && $sttheme.st_logo_image_height} height="{$sttheme.st_logo_image_height}"{/if}/>
            </a>
        {/capture}
          <div id="mobile_bar_left">
            <div class="flex_container">
              {block name='mobile_header_left'}
            	{if $sttheme.sticky_mobile_header%2!=0}
                  {$smarty.capture.mobile_shop_logo nofilter}
              	{/if}
                {hook h="displayMobileBarLeft"}
              {/block}
            </div>
          </div>
          <div id="mobile_bar_center" class="flex_child">
            <div class="flex_container {if $sttheme.sticky_mobile_header%2==0} flex_center {/if}">{*center content when logo is in center*}
              {block name='mobile_header_center'}
            	{if $sttheme.sticky_mobile_header%2==0}
                  {$smarty.capture.mobile_shop_logo nofilter}
              	{/if}
              {hook h="displayMobileBarCenter"}
              {/block}
            </div>
          </div>
          <div id="mobile_bar_right">
            <div class="flex_container">{block name='mobile_header_right'}{hook h="displayMobileBar"}{/block}</div>
          </div>
      </div>
      <div id="mobile_bar_bottom" class="flex_container">
        {hook h="displayMobileBarBottom"}
      </div>
    </div>
  </section>
{/block}
{block name='header_top'}

{*similar code in the checkout/_partials/header.tpl*}
  {if !isset($sttheme.hide_header) || !$sttheme.hide_header}
  <div id="header_primary" class="{if !$sttheme.sticky_primary_header} hide_when_sticky {/if}">
    <div class="wide_container_box {if !$sttheme.fullwidth_header && $sttheme.responsive_max!=3}wide_container{/if}">
      <div id="header_primary_container" class="{if !$sttheme.fullwidth_header && $sttheme.responsive_max!=3}container{else}container-fluid{/if}">
        <div id="header_primary_row" class="flex_container {if !isset($sttheme.logo_position) || !$sttheme.logo_position} logo_left {else} logo_center {/if}">
        {capture name="displaySlogan1"}{hook h="displaySlogan1"}{/capture}
        {capture name="displaySlogan2"}{hook h="displaySlogan2"}{/capture}
        {capture name="shop_logo"}
        <div class="logo_box">
          <div class="slogan_horizon">
            <a class="shop_logo" href="{$urls.base_url}" title="{$shop.name}">
                <img class="logo" src="{$shop.logo}" {if $sttheme.retina_logo_src } srcset="{$sttheme.retina_logo_src} 2x"{/if} alt="{$shop.name}"{if isset($sttheme.st_logo_image_width) && $sttheme.st_logo_image_width} width="{$sttheme.st_logo_image_width}"{/if}{if isset($sttheme.st_logo_image_height) && $sttheme.st_logo_image_height} height="{$sttheme.st_logo_image_height}"{/if}/>
            </a>
            {if $smarty.capture.displaySlogan1}<div class="slogan_box_beside">{$smarty.capture.displaySlogan1 nofilter}</div>{/if}
          </div>
          {if $smarty.capture.displaySlogan2}<div class="slogan_box_under">{$smarty.capture.displaySlogan2 nofilter}</div>{/if}
        </div>
        {/capture}
          <div id="header_left" class="">
            <div class="flex_container header_box {if $sttheme.header_left_alignment==1} flex_center {elseif $sttheme.header_left_alignment==2} flex_right {else} flex_left {/if}">
              {if !isset($sttheme.logo_position) || !$sttheme.logo_position}
                  {$smarty.capture.shop_logo nofilter}
              {/if}
              {if isset($HOOK_HEADER_LEFT) && $HOOK_HEADER_LEFT|trim}
                {$HOOK_HEADER_LEFT nofilter}
              {/if}
            </div>
          </div>
            <div id="header_center" class="">
              <div class="flex_container header_box {if $sttheme.header_center_alignment==1} flex_center {elseif $sttheme.header_center_alignment==2} flex_right {else} flex_left {/if}">
              {if isset($sttheme.logo_position) && $sttheme.logo_position}
                {$smarty.capture.shop_logo nofilter}
              {/if}
              {if isset($HOOK_HEADER_CENTER) && $HOOK_HEADER_CENTER|trim}
                  {$HOOK_HEADER_CENTER nofilter}
                {/if}
              </div>
            </div>
          <div id="header_right" class="">
            <div id="header_right_top" class="flex_container header_box {if $sttheme.header_right_alignment==1} flex_center {elseif $sttheme.header_right_alignment==2} flex_right {else} flex_left {/if}">
                {hook h='displayTop'}
            </div>
                <div id="header_right_bottom" class="flex_container header_box {if $sttheme.header_right_bottom_alignment==1} flex_center {elseif $sttheme.header_right_bottom_alignment==2} flex_right {else} flex_left {/if}">
                {if isset($HOOK_HEADER_BOTTOM) && $HOOK_HEADER_BOTTOM|trim}
                    {$HOOK_HEADER_BOTTOM nofilter}
                {/if}
                </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  {/if}
  <div class="nav_full_container {if !$sttheme.sticky_primary_header} hide_when_sticky {/if}">{hook h='displayNavFullWidth'}</div>
{/block}
{block name='header_menu'}
  <div id="easymenu_container" class="easymenu_bar">{hook h="displayEasyMenu"}</div>
  {capture name="displayMainMenu"}{hook h="displayMainMenu"}{/capture}
  {capture name="displayMainMenuWidget"}{hook h="displayMainMenuWidget"}{/capture}
  {assign var='has_widgets' value=0}
  {if isset($smarty.capture.displayMainMenuWidget) && $smarty.capture.displayMainMenuWidget|trim}{$has_widgets=1}{/if}
  {if (isset($smarty.capture.displayMainMenu) && $smarty.capture.displayMainMenu|trim) || $has_widgets}
    <section id="top_extra" class="main_menu_has_widgets_{$has_widgets}">
      <div class="{if !$sttheme.megamenu_width}wide_container boxed_megamenu{/if}">
      <div class="st_mega_menu_container animated fast">
      <div class="container">
        <div id="top_extra_container" class="flex_container {if $sttheme.megamenu_position==1} flex_center {elseif $sttheme.megamenu_position==2} flex_right {/if}">
            {if isset($smarty.capture.displayMainMenu)}{$smarty.capture.displayMainMenu nofilter}{/if}
            {if $has_widgets}
              <div id="main_menu_widgets">
                <div class="flex_box">
                  {if isset($smarty.capture.displayMainMenuWidget)}{$smarty.capture.displayMainMenuWidget nofilter}{/if}
                </div>
              </div>
            {/if}
        </div>
      </div>
      </div>
      </div> 
  </section>
  {/if}
{/block}