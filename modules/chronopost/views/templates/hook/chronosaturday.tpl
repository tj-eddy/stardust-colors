<script type="text/javascript">
    const SATURDAY_IDS = {$saturday_ids|json_encode};
    const SATURDAY_SUPPLEMENT_ENABLED = {if $saturday_supplement_enabled} true{else} false{/if};
</script>

<div id="saturday_delivery"
        {if !$is_saturday_carrier || !$saturday_supplement_enabled}
            style="display: none"
        {/if}
>
    <h3>{l s='Saturday delivery is available' mod='chronopost'}</h3>
    <div class="row">
        <p class="alert col-lg-6">{l s='Check the following box to enable the saturday delivery option (Totals will be updated in the next step)' mod='chronopost'}</p>
        <div class="alert col-lg-6">
            <div class="input-group">
                <p class="carrier-price">
                    {if $saturday_supplement && $saturday_supplement > 1}
                        + {$saturday_supplement} €
                    {else}
                        {l s='Free'}
                    {/if}
                    <input style="vertical-align: middle; margin-left: 5px" type="checkbox" class="control"
                           name="saturday_delivery"
                            {if $saturday_supplement_enabled && $cart_supplement_enabled}
                                checked="checked"
                            {/if}
                    />
                </p>
            </div>
        </div>
    </div>
</div>
