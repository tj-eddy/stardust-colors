{foreach $product.tags as $tag}
      <a href="{url entity='search' params=['tag' => $tag|urlencode]}" title="{l s='More about' d='Shop.Theme.Panda'} {$tag}" target="_top">{$tag}</a>{if !$tag@last}, {/if}
  {/foreach}