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
{if isset($success) && !empty($success)}
    <div class="alert alert-success">
        <button class="close" data-dismiss="alert" type="button">×</button>
        {$success|escape:'htmlall':'UTF-8'}
    </div>
{/if}
<div id="mercanet-text-branded" class="panel">
    <div class="row mercanet-header">
        <img src="{$module_dir|escape:'html':'UTF-8'}views/img/mercanet_logo.png" class="col-xs-6 col-md-4 text-center" id="payment-logo" />
        <div class="col-xs-6 col-md-7">
            <h4>{l s='Online secure payments for your e-commerce website' mod='mercanet'}</h4>
            <dl>
                <dd>{l s='With Mercanet you easily cash in payments on your e-commerce site and develop your sales benefiting from the following advantages' mod='mercanet'}:</dd>
                <dt class="text-branded">&middot; {l s='Multiple payments options as  French and international credit and debit cards (CB, Visa, MasterCard, American Express, Paylib, MasterPass)' mod='mercanet'}</dt>
                <dt class="text-branded">&middot; {l s='A payment page optimized for mobile transactions (responsive design)' mod='mercanet'}</dt>
                <dt class="text-branded">&middot; {l s='The 3D-Secure system and other fraud prevention options  to secure your customers’ transactions' mod='mercanet'}</dt>
                <dt class="text-branded">&middot; {l s='Real time monitoring of your transactions' mod='mercanet'}</dt>
                <dt class="text-branded">&middot; {l s='A unique and recognized interlocutor: BNP Paribas' mod='mercanet'}</dt>
            </dl>
            <h4>{l s='Installation:' mod='mercanet'}</h4>
            <p>{l s='To configure your module, please read the following steps:' mod='mercanet'}</p>
            <ol>
                <li>{l s='Enter the activation key that you get from Mercanet Support by mail' mod='mercanet'}</li>
                <li>{l s='Enter your merchant ID and your secret key (you can find it in Mercanet Download interface)' mod='mercanet'}</li>
                <li>{l s='Configure the payments methods and options you need' mod='mercanet'}</li>
                <li>{l s='Make a test payment' mod='mercanet'}</li>
            </ol>
            <h5>{l s='Start selling through your website!' mod='mercanet'}</h5>
            <p>{l s='For any question, you can use the user guide  attached in the .zip file.' mod='mercanet'}</p>

        </div>
        <div class="col-xs-12 col-md-2 text-left">
            <h4 class="text-branded">{l s='Contact us:' mod='mercanet'}</h4>
            <dl>
                <dt class="title-text-branded">&middot;{l s='Mercanet Support' mod='mercanet'}</dt>
                <dd>{l s='0825 84 34 14 (Service 0.15€ / min + cost)*' mod='mercanet'} <br> {l s='Monday to Friday – From 8h to 17h (excl. public holidays)' mod='mercanet'}</dd>
                <dd>
                    <em class="text-muted small">
                        * {l s='Call Price from a landline in France, excluding any additional costs on your service provider' mod='mercanet'}
                    </em>
                </dd>
                <dd>{l s='or' mod='mercanet'} <a href="mailto:assistance.mercanet@bnpparibas.com">{l s='assistance.mercanet@bnpparibas.com' mod='mercanet'}</a></dd>
                <dt class="title-text-branded">&middot;{l s='Sales department' mod='mercanet'}</dt>
                <dd>{l s='For any changes or request about your offer, please feel free to contact your BNP Paribas account manager by mail or phone.' mod='mercanet'}</dd>
            </dl>
        </div>
    </div>
</div>
<script type="text/javascript">
    /********************************************************************
     ON LOAD
     ********************************************************************/
    $(document).ready(function () {
        var i = 0;
        $(".nav-tabs li a").each(function (index) {
            if (this.href.indexOf('{$tab|escape:'htmlall':'UTF-8'}') > 1) {
                i = index;
            }
        });
        $('.nav-tabs li:eq(' + i + ') a').tab('show');

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

        $('.fancybox_nx_payments').fancybox({
            'type': 'iframe',
            'width': '50%',
            'height': '50%',
            'afterClose': function () {
                resetNxPaymentsList();
            }
        });
    }

    function resetNxPaymentsList() {
        location.reload();
    }
</script>