{*
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}


{function name="categories" nodes=[] depth=0}
  {strip}
    {if $nodes|count}
      {assign var="id_parent_parent" value=Ps_CategoryTree::getCategoryParentParent($category.id_parent)}
      {assign var="id_parent_parent_parent" value=Ps_CategoryTree::getCategoryParentParent($id_parent_parent)}
      <ul class="category-sub-menu category-sub-menu">
        {foreach from=$nodes item=node}
          <li  data-depth="{$depth}" class="{if (isset($category) && is_array($category) && isset($category.id) && $category.id==$node.id) || (isset($id_category_current) && $id_category_current==$node.id)} current_cate {/if}">
            <div class="acc_header flex_container {if $node.id == $category.id_parent ||  ($category.id_parent == 2 && $node.id == $category.id_parent)} selected_category  {/if} ">
              <a class="flex_child {if $depth == 0}deph_one{/if}" href="{$node.link}" title="{$node.name}">{$node.name}</a>
              {if $node.children}
                <span class="acc_icon collapsed" data-toggle="collapse" data-target="#exCollapsingNavbar{$node.id}">
                  <i class="fto-plus-2 acc_open fs_xl" {if $node.id == $category.id || $node.id == $category.id_parent} style="display: none"  {/if}></i>
                  <i class="fto-minus acc_close fs_xl" {if $node.id == $category.id || $node.id == $category.id_parent} style="display: block"  {/if}></i>
                </span>
              {/if}
            </div>
            {if $node.children}
              <div class="collapse {if $node.id == $category.id || $node.id == $category.id_parent || $id_parent_parent == $node.id|| $id_parent_parent_parent == $node.id } show  {/if} " id="exCollapsingNavbar{$node.id}">
                {categories nodes=$node.children depth=$depth+1}
              </div>
            {/if}
          </li>

        {/foreach}
      </ul>
    {/if}
  {/strip}
{/function}



{if count($categories.children)}
  <div class="block-categories block column_block">
    <div class="title_block flex_container title_align_0 title_style_{(int)$sttheme.heading_style}">
      <div class="flex_child title_flex_left"></div>
      <a class="title_block_inner noscat" title="{$categories.name}" href="">{l s="Nos catégories"}</a>
      <div class="flex_child title_flex_right"></div>
    </div>
    <div class="block_content">
      <div class="acc_box category-top-menu">
        {categories nodes=$categories.children}
      </div>
    </div>
  </div>
{/if}