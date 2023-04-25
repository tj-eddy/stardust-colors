{if $dlc_default}
    <div class="input-group fixed-width-md center">
        <input type="text" class="filter chrono-datepicker date-input form-control"
               name="dlc[{$id_order|escape:'htmlall':'UTF-8'}]" value="{$dlc_default}"/>
        <span class="input-group-addon">
            <i class="icon-calendar"></i>
        </span>
    </div>
{/if}
