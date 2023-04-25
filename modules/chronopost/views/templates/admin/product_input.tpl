{if $available_products}
    <select id="chrono_product" name="chrono_product[{$id_order|escape:'htmlall':'UTF-8'}][]">
        {foreach from=$available_products item=product}
            <option value="{$product['code']|escape:'htmlall':'UTF-8'}">{$product['label']|escape:'htmlall':'UTF-8'}</option>
        {/foreach}
    </select>

    <script type="text/javascript">
        $('[name="chrono_product[{$id_order|escape:'htmlall':'UTF-8'}][]"] option[value="{$quickcost_product}"]').prop('selected', true);
    </script>
{/if}
