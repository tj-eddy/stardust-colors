{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{*
{l s='January'  d='Shop.Theme.Panda'}
{l s='February' d='Shop.Theme.Panda'}
{l s='March'    d='Shop.Theme.Panda'}
{l s='April'    d='Shop.Theme.Panda'}
{l s='May'      d='Shop.Theme.Panda'}
{l s='June'     d='Shop.Theme.Panda'}
{l s='July'     d='Shop.Theme.Panda'}
{l s='August'   d='Shop.Theme.Panda'}
{l s='September' d='Shop.Theme.Panda'}
{l s='October'  d='Shop.Theme.Panda'}
{l s='November' d='Shop.Theme.Panda'}
{l s='December' d='Shop.Theme.Panda'}
*}
<!-- MODULE st stblogarchives -->
<div id="st_blog_block_archives" class="block column_block">
    <div class="title_block flex_container title_align_0 title_style_{(int)$stblog.heading_style}">
        <div class="flex_child title_flex_left"></div>
        <div class="title_block_inner">{l s='Blog archives' d='Shop.Theme.Panda'}</div>
        <div class="flex_child title_flex_right"></div>
    </div>
	<div class="block_content">
    <div class="acc_box category-top-menu">
		<ul class="category-sub-menu">
        {foreach $archives as $archive}
            <li>
                <div class="acc_header flex_container">
                    <a href="{url entity='module' name='stblogarchives' controller='default' params=['m' => $archive.Y]}" title="{$archive.Y2}" class="flex_child">{$archive.Y2}</a>
                    {if $archive.child && $archive.child|count}
                        <span class="acc_icon collapsed" data-toggle="collapse" data-target="#blog_archive_node_{$archive.Y}">
                          <i class="fto-plus-2 acc_open fs_xl"></i>
                          <i class="fto-minus acc_close fs_xl"></i>
                        </span>
                    {/if}
                </div>
                {if $archive.child && $archive.child|count}
                <div class="collapse" id="blog_archive_node_{$archive.Y}">
    			<ul class="category-sub-menu">
                {foreach $archive.child as $ar}
                    <li>
                        <div class="acc_header flex_container">
                            <a href="{url entity='module' name='stblogarchives' controller='default' params=['m' => $ar.Ym]}" title="{l s=$ar.M d='Shop.Theme.Panda'}" class="flex_child">{l s=$ar.M d='Shop.Theme.Panda'} {$archive.Y2}</a>
                        </div>
                    </li>
                {/foreach}
                </ul>
                </div>
                {/if}
            </li>
		{/foreach}
		</ul>
    </div>
	</div>
</div>
<!-- /MODULE st stblogarchives -->