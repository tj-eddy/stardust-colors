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
                                <img src="https://dynaimage.cdn.cnn.com/cnn/q_auto,w_412,c_fill,g_auto,h_232,ar_16:9/http%3A%2F%2Fcdn.cnn.com%2Fcnnnext%2Fdam%2Fassets%2F200305114843-01-edge-hudson-yards-observation-deck.jpg" alt="" />
                            </div>
                            <div class="card-body">
                                <span class="tag tag-pink">Travel</span>
                                <h4>New York City | Layout, People, Economy, Culture, & History</h4>
                                <p>
                                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias
                                    consequuntur sequi suscipit iure fuga ea!
                                </p>
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </ul>
        </div>
    </div>
{/if}