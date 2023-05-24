{if isset($wbp_posts)}
    <div id="wbpblogposts" class="blocpost">
        <div class="products_block col-sm-12 contentblock">
            <h1 class="last_blog_title">{l s='DERNIERS ARTICLES DU BLOG' mod='wordpressblogposts'}</h1>
            <p>
                {l s="Retrouvez nos astuces,test,tuto et conseils de pro afin d'utiliser au mieux nos produits Stardust Colors" d="Shop.Theme.Panda"}
            </p>
            {assign "col_size_md" "6"}
            {if $wbp_posts|@count lte 2}
                {assign "col_size_md" "6"}
            {/if}
            {if $wbp_posts|@count gt 2}
                {assign "col_size_md" "4"}
            {/if}
            <ul class="container-blog row">
                {foreach from=$wbp_posts item=post name=wbp_posts}
                    {if $post->post_image != ""}
                       {* <li class="col last_blog_img">
                            <a class="custom-card" href="{$post->url}" title="{$post->title|escape:'htmlall':'UTF-8'}"
                               style="border: 0;">
                                <div class="category-thumb">
                                    <img src="{$post->post_image}" alt="{$post->title|escape:'htmlall':'UTF-8'}"
                                         class="replace-2x img-responsive"/>
                                </div>
                            </a>
                        </li> *}
                        <div class="card">
                            <div class="card-header">
                                <img src="{$post->post_image}" alt="" />
                            </div>
                            <div class="card-body">
                                <span class="tag">{l s="Publié le"} : {$post->pub_date|date_format:"%D"}</span>
                                <h4>{$post->title|escape:'htmlall':'UTF-8'}</h4>
                                <p>
                                    {$post->description|truncate:200:"..."}
                                </p>
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </ul>
        </div>
    </div>
{/if}