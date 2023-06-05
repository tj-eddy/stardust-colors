{if isset($wbp_posts)}
    <div id="wbpblogposts" class="blocpost">
        <div class="products_block col-sm-12 contentblock">
            <div class="row">
                <div class="col-9"><h1 class="last_blog_title">{l s='DERNIERS ARTICLES DU BLOG' mod='wordpressblogposts'}</h1>
                </div>
                <div class="col-3" style="padding-right: 2%;"><div class="view_more_blog"><a target="_blank" href="/blog">{l s="Voir toutes nos actus"}</a></div></div>
            </div>
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
                        <div class="card">
                            <div class="card-header">
                                <img src="{$post->post_image}" alt="{$post->title|escape:'htmlall':'UTF-8'}" />
                            </div>
                            <div class="card-body">
                                <span class="tag_date_publish">{l s="Publié le"}  {$post->pub_date|date_format:"%d/%m/%Y"}</span>
                                <h4>{$post->title|escape:'htmlall':'UTF-8'}</h4>
                                <p>
                                    {$post->description|truncate:150:"..."}
                                </p>
                                <span class="tag_show_more"><a target="_blank" href="{$post->url}">{l s="Lire la suite"} > </a></span>
                                <div class="blog_flag">
                                    <div class="blog_flag_text">
                                        Actualités
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </ul>
        </div>
    </div>
{/if}