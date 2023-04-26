{**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 *}
{$hasAggregateRating = false}
{if !empty($product.productComments.averageRating) && !empty($product.productComments.nbComments)}
	{$hasAggregateRating = true}
	{$ratingValue = $product.productComments.averageRating}
	{$ratingReviewCount = $product.productComments.nbComments}
{elseif !empty($ratings.avg) && !empty($nbComments)}
	{$hasAggregateRating = true}
	{$ratingValue = $ratings.avg}
	{$ratingReviewCount = $nbComments}
{/if}
<script type="application/ld+json">
{
	"@context": "https://schema.org/",
	"@type": "Product",
	"name": "{$product.name}",
	"description": "{$page.meta.description|regex_replace:"/[\r\n]/" : " "}",
	"category": "{$product.category_name}",
{if !empty($product.cover)}
	"image" :"{$product.cover.bySize.home_default.url}",
{/if}
	"sku": "{if $product.reference}{$product.reference}{else}{$product.id}{/if}",
	"mpn": "{if $product.mpn}{$product.mpn}{elseif $product.reference}{$product.reference}{else}{$product.id}{/if}",
{if $product.ean13}
	"gtin13": "{$product.ean13}",
{elseif $product.upc}
	"gtin13": "{$product.upc}",
{/if}
{if $product_manufacturer->name || $shop.name}
	"brand": {
		"@type": "Brand",
		"name": "{if $product_manufacturer->name}{$product_manufacturer->name}{else}{$shop.name}{/if}"
	},
{/if}
{if $hasAggregateRating}
	"aggregateRating": {
		"@type": "AggregateRating",
		"ratingValue": "{$ratingValue|round:1}",
		"reviewCount": "{$ratingReviewCount}"
	},
{/if}
{if !empty($product.weight)}
	"weight": {
			"@context": "https://schema.org",
			"@type": "QuantitativeValue",
			"value": "{$product.weight}",
			"unitCode": "{$product.weight_unit}"
	},
{/if}
{if $product.show_price}
	"offers": {
		"@type": "Offer",
		"priceCurrency": "{$currency.iso_code}",
		"name": "{$product.name|strip_tags:false}",
		"price": "{$product.price_amount}",
		"url": "{$product.url}",
		"priceValidUntil": "{(3600*24*15 + $smarty.now)|date_format:"%Y-%m-%d"}",
	{if $product.images}
		"image": {strip}[
		{foreach from=$product.images item=p_img name="p_img_list"}
			"{$p_img.large.url}"{if !$smarty.foreach.p_img_list.last},{/if}
		{/foreach}
		]{/strip},
	{/if}
		"sku": "{if $product.reference}{$product.reference}{else}{$product.id}{/if}",
		"mpn": "{if $product.mpn}{$product.mpn}{elseif $product.reference}{$product.reference}{else}{$product.id}{/if}",
	{if $product.ean13}
		"gtin13": "{$product.ean13}",
	{else if $product.upc}
		"gtin13": "0{$product.upc}",
	{/if}
	{if $product.condition === 'new'}
		"itemCondition": "https://schema.org/NewCondition",
	{elseif $product.show_condition && $product.condition === 'used'}
		"itemCondition": "https://schema.org/UsedCondition",
	{elseif $product.show_condition && $product.condition === 'refurbished'}
		"itemCondition": "https://schema.org/RefurbishedCondition",
	{/if}
		"availability": "{$product.seo_availability}",
		"seller": {
			"@type": "Organization",
			"name": "{$shop.name}"
		}
	},
{/if}
	"url": "{$product.url}"
}
</script>