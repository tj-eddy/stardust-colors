{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}

  {hook h='displayPaymentTop'}
  
  {* used by javascript to correctly handle cart updates when we are on payment step (eg vouchers added) *}
  <div style="display:none" class="js-cart-payment-step-refresh"></div>

  {if !empty($display_transaction_updated_info)}
  <p class="cart-payment-step-refreshed-info">
    {l s='Transaction amount has been correctly updated' d='Shop.Theme.Checkout'}
  </p>
  {/if}

  {if isset($is_free) && $is_free}
    <p>{l s='No payment needed for this order' d='Shop.Theme.Checkout'}</p>
  {/if}
  <div class="payment-options {if isset($is_free) && $is_free}hidden-xs-up{/if}">
    {foreach from=$payment_options item="module_options"}
      {foreach from=$module_options item="option"}
          <div
                  id="{$option.id}-additional-information"
                  class="js-additional-information definition-list additional-information{if $option.id != $selected_payment_option} ps-hidden {/if}"
          >
              {$option.additionalInformation nofilter}
          </div>
      {/foreach}
    {foreachelse}
      <div class="alert alert-danger">{l s='Unfortunately, there are no payment method available.' d='Shop.Theme.Checkout'}</div>
    {/foreach}
  </div>


  {if $show_final_summary}
    {include file='checkout/_partials/order-final-summary.tpl'}
  {/if}



  {hook h='displayPaymentByBinaries'}

  <div class="modal" id="modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <button type="button" class="st_modal_close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}">
          <span aria-hidden="true">&times;</span>
        </button>
        <div class="js-modal-content general_border p-2"></div>
      </div>
    </div>
  </div>
{/block}
