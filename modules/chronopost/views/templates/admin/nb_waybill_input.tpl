<input type="number"
       name="multi[{$id_order|escape:'htmlall':'UTF-8'}]"
       value="{$nbwb|escape:'htmlall':'UTF-8'}"
       {if !$multiParcelsAvailable}disabled="disabled"{/if}
       class="fixed-width-xs nbLT {if $is_fresh_carrier}is-chronofresh{/if}"
       min="1"
       max="100"/>
