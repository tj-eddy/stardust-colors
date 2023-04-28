{if !empty($wbp_posts)}
	<div id="wbpblogposts" class="left-column">
		<div class="row">
			<div class="col-sm-12">
				<h3>{l s='Latest News & Offers' mod='wordpressblogposts'}</h3>
			</div>
		</div>
		<div class="row">
			{foreach from=$wbp_posts item=post name=wbp_posts}
				<div class="col-xs-12">
					<div class="blog-post">
						<div class="post-image" style="background-image:url('{$post->featured_image|escape:'htmlall':'UTF-8'}')">
							<div class="overlay anim-all-200"></div>
							<a href="{$post->url|escape:'htmlall':'UTF-8'}" class="post-image-link anim-all-200">+</a>
							<div class="post-outbound">
								<span class="col-xs-12 post-title"><a href="{$post->url|escape:'htmlall':'UTF-8'}"><strong>{$post->title|escape:'htmlall':'UTF-8'|truncate:70:"...":true}</strong></a></span>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
{/if}