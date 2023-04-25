{*
* 2007-2012 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 17677 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if !$timeago}
    {dateFormat date=$date_add full=0}
{elseif !count($timeago)}
    {l s='Just now' d='Shop.Theme.Panda'}
{else}
    {if key($timeago)=='y'}
        {$timeago['y']} {if $timeago['y']>1}{l s='Years'  d='Shop.Theme.Panda'}{else}{l s='Year'  d='Shop.Theme.Panda'}{/if}
    {elseif key($timeago)=='m'}
        {$timeago['m']} {if $timeago['m']>1}{l s='Months'  d='Shop.Theme.Panda'}{else}{l s='Month'  d='Shop.Theme.Panda'}{/if}
    {elseif key($timeago)=='w'}
        {$timeago['w']} {if $timeago['w']>1}{l s='Weeks'  d='Shop.Theme.Panda'}{else}{l s='Week'  d='Shop.Theme.Panda'}{/if}
    {elseif key($timeago)=='d'}
        {$timeago['d']} {if $timeago['d']>1}{l s='Days'  d='Shop.Theme.Panda'}{else}{l s='Day'  d='Shop.Theme.Panda'}{/if}
    {elseif key($timeago)=='h'}
        {$timeago['h']} {if $timeago['h']>1}{l s='Hours'  d='Shop.Theme.Panda'}{else}{l s='Hour'  d='Shop.Theme.Panda'}{/if}
    {elseif key($timeago)=='i'}
        {$timeago['i']} {if $timeago['i']>1}{l s='Minutes'  d='Shop.Theme.Panda'}{else}{l s='Minute'  d='Shop.Theme.Panda'}{/if}
    {/if}
    &nbsp;{l s='ago'  d='Shop.Theme.Panda'}
{/if}