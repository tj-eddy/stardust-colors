{if $page.page_name =='index'}
<div id="selectprod" {*class="col-sm-4"*}>
	<div class="product-selection">
		<div class="bloctitle">
			<div class="title_cat"><span>{l s='La sélection du moment' mod='derniersproduits'}</span></div>
		</div>
		<div class="clearfix"></div>
		{if isset($oproduits)}
		<div class="row detail_with_img">
			<div class="col-5 detail">
				<h6><a class="product_img_link" href="{$link->getProductLink($oproduits->id)|escape:'html':'UTF-8'}" title="{$oproduits->name|escape:'html':'UTF-8'}" itemprop="url">{$oproduits->name}</a></h6>
				<div class="description">
					{$oproduits->description|strip_tags:false|truncate:200:'...'}
				</div>
				<div class="clear"></div>
				<div class="priceprod">
					<span class="prixnormale">{Tools::displayPrice($oproduits->getPrice(true, $smarty.const.NULL, 2))}</span>
					{if $oproduits->getPriceWithoutReduct() > 0 && isset($oproduits->specificPrice) && $oproduits->specificPrice && isset($oproduits->specificPrice.reduction) && $oproduits->specificPrice.reduction > 0}
					<span class="prixreduc">{Tools::displayPrice($oproduits->getPriceWithoutReduct())}{/if}
				</div>
			</div>
			<div class="imagesprod col-7">
				<a class="product_img_link" href="{$link->getProductLink($oproduits->id)|escape:'html':'UTF-8'}" title="{$oproduits->name|escape:'html':'UTF-8'}" itemprop="url">
					<img  class="replace-2x img-responsive" src="{$link->getImageLink($oproduits->link_rewrite, $imgpro[0]['id_image'], 'specifique_img')|escape:'html':'UTF-8'}" alt="{$oproduits->name|escape:'html':'UTF-8'}" title="{$oproduits->name|escape:'html':'UTF-8'}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} itemprop="image" />
				</a>
			</div>
		</div>
		{else}
			<div>{l s='Aucun produit sélectionné' mod='derniersproduits'}</div>
		{/if}
	</div>
</div>
{/if}

