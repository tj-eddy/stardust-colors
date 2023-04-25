{*
* 1961-2016 BNP Paribas
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Quadra Informatique <modules@quadra-informatique.fr>
*  @copyright 1961-2016 BNP Paribas
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}
{if !empty($schedules) || !empty($transactions) || !empty($recurring_schedules)}
    <div class="panel">
        <fieldset>
            <legend><img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}mercanet/logo.gif">{$module_name|escape:'htmlall':'UTF-8'}</legend>
                {if isset($errors) && $errors}
                <p class="alert alert-danger">
                    {foreach from=$errors key=k item=error}
                        {$error|escape:'htmlall':'UTF-8'}<br/>
                    {/foreach}
                </p>
            {/if}
            {* RECURRING *}
            {if !empty($recurring_schedules)}
                <div>
                    <h2>{l s='Recurring payment' mod='mercanet'}</h2>
                        <table class="table" width="100%" cellspacing="0" cellpadding="0" id="mercanet_recurring_table">
                            <thead>
                                <tr>
                                    <th>{l s='Product' mod='mercanet'}</th>
                                    <th>{l s='Amount (Pre-tax)' mod='mercanet'}</th>
                                    <th>{l s='Tax Rules Group' mod='mercanet'}</th>
                                    <th>{l s='Occurrence' mod='mercanet'}</th>
                                    <th>{l s='Occurrence between payment' mod='mercanet'}</th>
                                    <th>{l s='Periodicity' mod='mercanet'}</th>
                                    <th>{l s='Last Schedule' mod='mercanet'}</th>
                                    <th>{l s='Next Schedule' mod='mercanet'}</th>
                                    <th>{l s='Status' mod='mercanet'}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$recurring_schedules item=row}
                                    <tr>
                                        <td>
                                            {$row.product_name|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {$row.amount_tax_exclude|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {$row.tax_rules_group_name|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {$row.current_occurence|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {$row.number_occurences|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {$row.periodicity_name|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {dateFormat date=$row.last_schedule|escape:'htmlall':'UTF-8' full=false}
                                        </td>
                                        <td>
                                            {dateFormat date=$row.next_schedule|escape:'htmlall':'UTF-8' full=false}
                                        </td>
                                        <td>
                                            {$row.status_name|escape:'htmlall':'UTF-8'}
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                </div>
            {/if}

            {* LINKED ORDERS *}
            {if !empty($recurring_orders)}
                <div>
                    <h2>{l s='Recurring orders' mod='mercanet'}</h2>
                        <table class="table" width="100%" cellspacing="0" cellpadding="0" id="mercanet_recurring_order_table">
                            <thead>
                                <tr>
                                    <th>{l s='Order no.' mod='mercanet'}</th>
                                    <th>{l s='Status' mod='mercanet'}</th>
                                    <th>{l s='Amount' mod='mercanet'}</th>
                                    <th>{l s='Date add' mod='mercanet'}</th>
                                    <th>{l s='' mod='mercanet'}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$recurring_orders item=row}
                                    <tr>
                                        <td>
                                            {$row.id_order|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {$row.state_name|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {displayPrice price=$row.total_paid_tax_incl currency=$row.id_currency}
                                        </td>
                                        <td>
                                            {dateFormat date=$row.date_add|escape:'htmlall':'UTF-8' full=false}
                                        </td>
                                        <td>
                                            <a href="{$link->getAdminLink('AdminOrders')|escape:'htmlall':'UTF-8'}&vieworder&id_order={$row.id_order|escape:'htmlall':'UTF-8'}">
                                                <i class="icon-eye-open"></i>
                                                {l s='See the order' mod='mercanet'}
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                </div>
            {/if}

            {* SCHEDULES *}
            {if !empty($schedules)}
                <div>
                    <h2>{l s='Schedules' mod='mercanet'}</h2>

                    <table class="table" width="100%" cellspacing="0" cellpadding="0" id="mercanet_schedule_table">
                        <thead>
                            <tr>
                                <th>{l s='Card' mod='mercanet'}</th>
                                <th>{l s='Date' mod='mercanet'}</th>
                                <th>{l s='Order ID' mod='mercanet'}</th>
                                <th>{l s='Transaction ID' mod='mercanet'}</th>
                                <th>{l s='PAN' mod='mercanet'}</th>
                                <th>{l s='Amount' mod='mercanet'}</th>
                                <th>{l s='Due Date' mod='mercanet'}</th>
                                <th>{l s='Capture Date' mod='mercanet'}</th>
                                <th>{l s='State' mod='mercanet'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$schedules item=row}
                                <tr {if !empty($row.payment_mean_brand)|escape:'htmlall':'UTF-8'}class="schedule_captured"{/if}>
                                    <td>
                                        {if !empty($row.payment_mean_brand)|escape:'htmlall':'UTF-8'}
                                            <img width="30px;" src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}mercanet/views/img/{$row.payment_mean_brand|escape:'htmlall':'UTF-8'}.png">
                                        {/if}
                                    </td>
                                    <td>{dateFormat date=$row.date_add|escape:'htmlall':'UTF-8' full=false}</td>
                                    <td>{$row.id_order|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.transaction_reference|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.masked_pan|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.amount|escape:'htmlall':'UTF-8'}</td>
                                    <td>{dateFormat date=$row.date_to_capture|escape:'htmlall':'UTF-8' full=false}</td>
                                    <td>{dateFormat date=$row.date_capture|escape:'htmlall':'UTF-8' full=false}</td>
                                    <td>
                                        {if $row.state}
                                            {$row.state|escape:'htmlall':'UTF-8'}
                                        {else}
                                            {if $row.captured == true}
                                                {l s='Captured' mod='mercanet'}
                                            {else}
                                                {l s='Waiting' mod='mercanet'}
                                            {/if}
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>

                </div>
            {/if}

            {* TRANSACTIONS *}
            {if !empty($transactions)}
                <div>
                    <h2>{l s='Transactions' mod='mercanet'}</h2>
                    <table class="table" width="100%" cellspacing="0" cellpadding="0" id="mercanet_transaction_history_table">
                        {*<colgroup>
                        <col width="15%"/>
                        <col width="12%"/>
                        <col width="15%"/>
                        <col width="10%"/>
                        <col width="10%"/>
                        <col width=""/>
                        </colgroup>*}
                        <thead>
                            <tr>
                                <th>{l s='Card' mod='mercanet'}</th>
                                <th>{l s='Date' mod='mercanet'}</th>
                                <th>{l s='Order ID' mod='mercanet'}</th>
                                <th>{l s='Authorisation ID' mod='mercanet'}</th>
                                <th>{l s='Transaction ID' mod='mercanet'}</th>
                                <th>{l s='PAN' mod='mercanet'}</th>
                                <th>{l s='Amount' mod='mercanet'}</th>
                                <th>{l s='Type' mod='mercanet'}</th>
                                <th>{l s='Response message' mod='mercanet'}</th>
                                <th>{l s='Acquirer message' mod='mercanet'}</th>
                                <th>{l s='Complementary message' mod='mercanet'}</th>
                                <th>{l s='Actions' mod='mercanet'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$transactions item=row}
                                <tr>
                                    <td>
                                        {if !empty($row.payment_mean_brand)|escape:'htmlall':'UTF-8'}
                                            <img width="30px;" src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}mercanet/views/img/{$row.payment_mean_brand|escape:'htmlall':'UTF-8'}.png">
                                        {/if}
                                    </td>
                                    <td>{dateFormat date=$row.transaction_date_time|escape:'htmlall':'UTF-8' full=true}</td>
                                    <td>{$row.id_order|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.authorisation_id|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.transaction_reference|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.masked_pan|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.amount|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.transaction_type|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.response_message|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.acquirer_response_message|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$row.complementary_message|escape:'htmlall':'UTF-8'}</td>
                                    <td>
                                        <a href="#div_rd_{$row.id_mercanet_transaction|escape:'htmlall':'UTF-8'}" id="rd_5" class='raw_data_btn'><i class="icon-search-plus"></i></a>
                                    </td>
                            <div id="div_rd_{$row.id_mercanet_transaction|escape:'htmlall':'UTF-8'}" class="raw_data_div">
                                <h2>{l s='Mercanet Raw Data' mod='mercanet'}</h2>
                                {foreach $row.raw_data as $data}
                                    <!-- Specifics case -->
                                    {if $data@key == 'complementaryInfo'}
                                        {$data@key|escape:'htmlall':'UTF-8'} : {$data|htmlspecialchars} <br/>
                                    {else}
                                        {$data@key|escape:'htmlall':'UTF-8'} : {$data|escape:'htmlall':'UTF-8'} <br/>
                                    {/if}
                                {/foreach}
                            </div>
                            </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>

                {* REFUND *}
                {if $refund_tri == true}
                    <div class="mercanet-admin refund">
                        <strong>{l s='Transaction refund' mod='mercanet'}</strong>
                        {if isset($slips) && !empty($slips)}
                            <form action="{$currentIndex|escape:'htmlall':'UTF-8'}&id_order={$order->id|escape:'htmlall':'UTF-8'}&vieworder&token={$smarty.get.token|escape:'htmlall':'UTF-8'}" method="post" autocomplete="off">
                                <div style="margin-bottom:5px;">
                                    <ul>
                                        <li>{l s='Select a transaction and a slip to refund:' mod='mercanet'}</li>
                                    </ul>
                                    <p>
                                        {if $refundable_transaction}
                                            <select name="mercanet_refund_transaction" style="width:25%; margin: 1px;">
                                                <option value="">{l s='Select a transaction' mod='mercanet'}</option>
                                                {foreach from=$refundable_transaction item=transaction}
                                                    <option value="{$transaction.transaction_reference|escape:'htmlall':'UTF-8'}">{$transaction.transaction_reference|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
                                            </select>
                                            <select name="mercanet_refund_slip" style="width:25%; margin: 1px;">
                                                <option value="">{l s='Select a slip' mod='mercanet'}</option>
                                                {foreach from=$slips item=slip}
                                                    <option value="{$slip.id_order_slip|escape:'htmlall':'UTF-8'}">[{l s='Slip #' mod='mercanet'}{$slip.id_order_slip|escape:'htmlall':'UTF-8'}] {displayPrice price=$slip.amount currency=$order->id_currency}</option>
                                                {/foreach}
                                            </select>
                                            <input type="submit" name="submitMercanetRefund" value="{l s='Refund' mod='mercanet'}" class="button" />
                                        {else}
                                            {l s='There is no refundable transaction.' mod='mercanet'}
                                        {/if}
                                    </p>
                                </div>
                            </form>
                        {else}
                            <ul>
                                <li>{l s='Please create a refundable slip before refund transaction.' mod='mercanet'}</li>
                            </ul>
                        {/if}
                    </div>
                {/if}
            {/if}
        </fieldset>

    </div>

    <script type="text/javascript">
        /********************************************************************
         ON LOAD
         ********************************************************************/
        $(document).ready(function () {
            $('.raw_data_btn').on('click', function () {
                var id = this.id;
                $("#" + id).fancybox();
            });

            resetBind();
        });

        // Fancybox
        function resetBind()
        {
            $('.fancybox').fancybox({
                'type': 'iframe',
                'width': '50%',
                'height': '50%',
            });

        }

    </script>
{/if}
