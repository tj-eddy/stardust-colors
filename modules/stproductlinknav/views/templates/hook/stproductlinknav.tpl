{if $nav_products['prev'] || $nav_products['next']}
	<section id="product_link_nav_wrap">
	{foreach $nav_products as $nav => $product}
		{if $product}
			<div class="product_link_nav with_preview"> 
			    <a href="{$product.url}" title="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name}{/if}"><i class="fto-{if $nav=='prev'}left{/if}{if $nav=='next'}right{/if}-open-3"></i>
				    <div class="product_link_nav_preview">
				        <img src="{if isset($product.cover.bySize.small_default.url)}{$product.cover.bySize.small_default.url}{/if}" alt="{if isset($product.cover.legend) && !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name}{/if}" width="{if isset($product.cover.bySize.small_default.width)}{$product.cover.bySize.small_default.width}{/if}" height="{if isset($product.cover.bySize.small_default.height)}{$product.cover.bySize.small_default.height}{/if}"/>
				    </div>
			    </a>
			</div>
		{/if}
	{/foreach}
	</section>
{/if}