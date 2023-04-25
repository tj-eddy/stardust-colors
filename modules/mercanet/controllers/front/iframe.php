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

class MercanetIframeModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $this->display_column_right = false;
        $this->display_column_left = false;
        $this->display_header = false;
        $this->display_footer = false;
        parent::initContent();

        $params = Tools::getValue('data_mercanet');
        $seal = Tools::getValue('seal');

        $this->context->smarty->assign(array(
            'data_mercanet' => $params,
            'interface_version' => Configuration::get('MERCANET_INTERFACE_VERSION'),
            'seal' => $seal,
            'url' => $this->getServerUrl(),
            'secure_key' => Context::getContext()->customer->secure_key,
        ));
        $this->setTemplate('iframe.tpl');
    }

    /**
     * Get the server URL to use to contact BNP Mercanet
     * @return URL
     */
    protected function getServerUrl()
    {
        // TEST
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true) {
            return Configuration::get('MERCANET_TEST_PAYMENT_PAGE_URL');
        }

        // PRODUCTION
        return Configuration::get('MERCANET_PAYMENT_PAGE_URL');
    }
}
