<?php
/**
 * 1961-2019 BNP Paribas
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1961-2019 BNP Paribas
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MercanetWalletModuleFrontController extends ModuleFrontController
{
    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */
    public function postProcess()
    {
        $data = MercanetApi::getWalletData();
        $this->context->smarty->assign(array(
            'url' => $this->getServerUrl(),
            'interface_version' => Configuration::getGlobalValue('MERCANET_WT_INTERFACE_VERSION'),
            'request_date_time' => gmdate("Y-m-d", time())."T".gmdate("H:i:s", time())."+00:00",
            'data' => MercanetApi::getRawData($data),
            'seal' => MercanetApi::buildSeal($data),
            'secure_key' => Context::getContext()->customer->secure_key,
            'module_display_name' => $this->module->displayName,
            'wallet_id' => MercanetWallet::getCustomerWalletId((int)$this->context->customer->id),
        ));

        return $this->setTemplate('wallet.tpl');
    }

    protected function displayError($message, $description = false)
    {
        /**
         * Create the breadcrumb for your ModuleFrontController.
         */
        $this->context->smarty->assign(
            'path',
            '<a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Payment').'</a><span class="navigation-pipe">&gt;</span>'.$this->module->l('Error')
        );
        /**
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('error.tpl');
    }

    /**
     * Get the server URL to use to contact BNP Mercanet
     * @return URL
     */
    protected function getServerUrl()
    {
        // TEST
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true) {
            return Configuration::get('MERCANET_WT_URL_TEST');
        }

        // PRODUCTION
        return Configuration::get('MERCANET_WT_URL');
    }
}
