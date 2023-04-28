{if isset($wbp_posts)}
    <div id="wbpblogposts" class="blocpost">
        <div class="products_block col-sm-12 contentblock">
            <h1 class="last_blog_title">{l s='DERNIERS ARTICLES DU BLOG' mod='wordpressblogposts'}</h1>
            <p>
                {l s="Retrouvez nos astuces,test,tuto et conseils de pro afin d'utiliser au mieux nos produits Stardust Colors" d="Shop.Theme.Panda"}
            </p>
            <img src="{$modules_dir}wordpressblogposts/img/femmes.png" alt=""/>
            {assign "col_size_md" "6"}
            {if $wbp_posts|@count lte 2}
                {assign "col_size_md" "6"}
            {/if}
            {if $wbp_posts|@count gt 2}
                {assign "col_size_md" "4"}
            {/if}
            <ul class="featured_categories_list row">
                {foreach from=$wbp_posts item=post name=wbp_posts}
                    {if $post->post_image == ""}
                        {continue}
                    {/if}
                    <li class="chqcategorie">
                       {* <span class="dateclass">{$post->dateLang}</span>
						<a href="{$post->url|escape:'htmlall':'UTF-8'}"
						   {if $wbp_link_new_tab}target="blank"{/if}
						   title="{$post->title|escape:'htmlall':'UTF-8'}"><span>></span>
							{$post->title|escape:'htmlall':'UTF-8'}
                        </a>*}
						<a class="custom-card" href="{$post->url}" title="{$post->title|escape:'htmlall':'UTF-8'}" style="border: 0;">
							<div class="category-thumb">
								<img src="{$post->post_image}" alt="{$post->title|escape:'htmlall':'UTF-8'}"  class="replace-2x img-responsive" />
							</div>
						</a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
{/if}