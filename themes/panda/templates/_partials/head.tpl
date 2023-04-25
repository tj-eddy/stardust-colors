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
{block name='head_charset'}
  <meta charset="utf-8">
{/block}
{block name='head_ie_compatibility'}
  <meta http-equiv="x-ua-compatible" content="ie=edge">
{/block}

{block name='head_seo'}
  <title>{block name='head_seo_title'}{$page.meta.title}{/block}</title>
  <meta name="description" content="{block name='head_seo_description'}{$page.meta.description}{/block}">
  <meta name="keywords" content="{block name='head_seo_keywords'}{$page.meta.keywords}{/block}">
  {if $page.meta.robots !== 'index'}
    <meta name="robots" content="{$page.meta.robots}">
  {/if}
  {if $page.canonical}
    <link rel="canonical" href="{$page.canonical}">
  {/if}
  {block name='head_hreflang'}
    {if isset($urls.alternative_langs)}
      {foreach from=$urls.alternative_langs item=pageUrl key=code}
            <link rel="alternate" href="{$pageUrl}" hreflang="{if $language.language_code == $code}x-default{else}{$code}{/if}">
      {/foreach}
    {/if}
  {/block}
  {if isset($listing.pagination.pages)}
    {foreach from=$listing.pagination.pages item="page"}
      {if $page.clickable && $page.type === 'previous'}
      <link rel="prev" href="{$page.url}" />
      {elseif $page.clickable && $page.type === 'next'}
      <link rel="next" href="{$page.url}" />
      {/if}
    {/foreach}
  {/if}
{/block}

<!--st begin -->
{block name='head_viewport'}
{if isset($sttheme.responsive) && $sttheme.responsive && (!$sttheme.enabled_version_swithing || $sttheme.version_switching==0)}
    <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1.0" />
{/if}
{/block}
<!--st end -->
{block name='head_icons'}
  <link rel="icon" type="image/vnd.microsoft.icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
  <link rel="shortcut icon" type="image/x-icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
  <!--st begin -->
  {if isset($sttheme.icon_iphone_180) && $sttheme.icon_iphone_180}
  <link rel="apple-touch-icon" sizes="180x180" href="{$sttheme.icon_iphone_180}?{$sttheme.favicon_update_time}" />
  {/if}
  {if isset($sttheme.icon_iphone_16) && $sttheme.icon_iphone_16}
  <link rel="icon" type="image/png" sizes="16x16" href="{$sttheme.icon_iphone_16}?{$sttheme.favicon_update_time}" />
  {/if}
  {if isset($sttheme.icon_iphone_32) && $sttheme.icon_iphone_32}
  <link rel="icon" type="image/png" sizes="32x32" href="{$sttheme.icon_iphone_32}?{$sttheme.favicon_update_time}" />
  {/if}
  {if isset($sttheme.site_webmanifest) && $sttheme.site_webmanifest}
  <link rel="manifest" href="{$sttheme.site_webmanifest}?{$sttheme.favicon_update_time}">
  {/if}
  {if isset($sttheme.icon_iphone_svg) && $sttheme.icon_iphone_svg}
  <link rel="mask-icon" href="{$sttheme.icon_iphone_svg}?{$sttheme.favicon_update_time}" color="{if $sttheme.favicon_svg_color}{$sttheme.favicon_svg_color}{else}#e54d26{/if}">
  {/if}
  {if isset($sttheme.browserconfig) && $sttheme.browserconfig}
  <meta name="msapplication-config" content="{$sttheme.browserconfig}?{$sttheme.favicon_update_time}">
  {/if}
  {if isset($sttheme.browser_theme_color) && $sttheme.browser_theme_color}
  <meta name="theme-color" content="{$sttheme.browser_theme_color}">
  {/if}
{/block}
<!--st end -->
{block name='stylesheets'}
  {include file="_partials/stylesheets.tpl" stylesheets=$stylesheets}
{/block}

{block name='javascript_head'}
  {include file="_partials/javascript.tpl" javascript=$javascript.head vars=$js_custom_vars}
{/block}
<!--st end -->
{block name='hook_header'}
  {$HOOK_HEADER nofilter}
{/block}
{if isset($sttheme.head_code) && $sttheme.head_code}{$sttheme.head_code nofilter}{/if}
{block name='hook_extra'}{/block}