{* Fixed ajax refresh attribute to open the details tab if no descriton*}
{if !isset($st_active_pro_tab) && !$product.description}{assign var="st_active_pro_tab" value="product-details"}{/if}
<div role="tabpanel" class="tab-pane {if !$sttheme.remove_product_details_tab && isset($st_active_pro_tab) && $st_active_pro_tab=='product-details'} active {if $sttheme.product_acc_style!=0 || (isset($steasybuilder) && $steasybuilder.is_editing)} st_open {/if} {elseif $sttheme.product_acc_style==2} st_open {/if} {if $sttheme.remove_product_details_tab} product-tab-hide {/if}"
     id="product-details"
     data-product="{$product.embedded_attributes|json_encode}"
  >
    <div class="mobile_tab_title">
        <a href="javascript:;" class="opener"><i class="fto-plus-2 plus_sign"></i><i class="fto-minus minus_sign"></i></a>
        <div class="mobile_tab_name">{l s='Product Details' d='Shop.Theme.Catalog'}</div>
    </div>
    <div class="tab-pane-body">

    {if isset($product.reference_to_display) && $product.reference_to_display neq ''}
      <div class="product-reference">
        <label class="label">{l s='Reference' d='Shop.Theme.Catalog'} </label>
        <span>{$product.reference_to_display}</span>
      </div>
    {/if}

    {block name='product_out_of_stock'}
      <div class="product-out-of-stock">
        {hook h='actionProductOutOfStock' product=$product}
      </div>
    {/block}

    {block name='product_features'}
      {if !isset($product.grouped_features)}{$product.grouped_features = $product.features}{/if}
      {if $product.grouped_features}
        <p class="page_heading">{l s='Data sheet' d='Shop.Theme.Catalog'}</p>
        <section class="product-features">
          {foreach from=$product.grouped_features item=feature}
          {if isset($stpbf) && count($stpbf.features_arr) && !in_array($feature['id_feature'], $stpbf.features_arr)}{continue}{/if}
          <dl class="data-sheet flex_container flex_start">
            <dt class="name {if isset($stpbf) && $stpbf.kv_name_hide} display_none {/if}">{$feature.name}</dt>
            <dd class="value flex_child">{$feature.value|escape:'htmlall'|nl2br nofilter}</dd>
          </dl>
          {/foreach}
      </section>
      {/if}
    {/block}

    {* if product have specific references, a table will be added to product details section *}
    {block name='product_specific_references'}
      {if !empty($product.specific_references)}
        <p class="page_heading">{l s='Specific References' d='Shop.Theme.Catalog'}</p>
        <section class="product-specific-references">
              {foreach from=$product.specific_references item=reference key=key}
              {if isset($stpbf) && count($stpbf.references_arr) && !in_array($key, $stpbf.references_arr)}{continue}{/if}
              <dl class="data-sheet flex_container">
                <dt class="name">{$key}</dt>
                <dd class="value flex_child {if $key=='ean13'} ean13_value {/if}">{$reference}</dd>
              </dl>
              {/foreach}
        </section>
      {/if}
    {/block}

    </div>
</div>
