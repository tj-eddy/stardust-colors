<div class="grid-input">
    <input type="checkbox" name="shipSaturday[{$id_order|escape:'htmlall':'UTF-8'}][]"
           {if $saturday!=1}disabled{/if}
            {if $saturday_ok==1}checked{/if}
            {if $saturday_supplement_enabled && $saturday_ok}onclick="return false" title="{l s='Option selected during checkout' mod='chronopost'}" {/if}
           value="1" class="noborder" id="shipSaturday">
</div>
